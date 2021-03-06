<?php

/*
+---------------------------------------------------------------------------+
| OpenX v2.8                                             |
| ==========                            |
|                                                                           |
| Copyright (c) 2003-2009 OpenX Limited                                     |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id: banner-edit.php 34688 2009-04-01 16:18:28Z andrew.hill $
*/

// Require the initialisation file
require_once '../../../../init.php';

// Required files
require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/OA/Creative/File.php';
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/lib/max/other/common.php';
require_once MAX_PATH . '/lib/max/other/html.php';
require_once MAX_PATH . '/lib/OA/Admin/UI/component/Form.php';
require_once MAX_PATH . '/lib/OA/Maintenance/Priority.php';

require_once LIB_PATH . '/Plugin/Component.php';
require_once MAX_PATH . '/lib/OA/Admin/TemplatePlugin.php';
require_once 'handsetdetection.config.php';
require_once 'handsetdetection.mobileconfig.php';

$htmltemplate = MAX_commonGetValueUnslashed('htmltemplate');

// Register input variables
phpAds_registerGlobalUnslashed(
     'alink'
    ,'alink_chosen'
    ,'alt'
    ,'alt_imageurl'
    ,'asource'
    ,'atar'
    ,'adserver'
    ,'bannertext'
    ,'campaignid'
    ,'checkswf'
    ,'clientid'
    ,'comments'
    ,'description'
    ,'ext_bannertype'
    ,'height'
    ,'imageurl'
    ,'keyword'
    ,'message'
    ,'replaceimage'
    ,'replacealtimage'
    ,'status'
    ,'statustext'
    ,'type'
    ,'submit'
    ,'target'
    ,'transparent'
    ,'upload'
    ,'url'
    ,'weight'
    ,'width'
);

/*-------------------------------------------------------*/
/* Client interface security                             */
/*-------------------------------------------------------*/
OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER, OA_ACCOUNT_ADVERTISER);
OA_Permission::enforceAccessToObject('clients',   $clientid);
OA_Permission::enforceAccessToObject('campaigns', $campaignid);

if (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) {
    OA_Permission::enforceAllowed(OA_PERM_BANNER_EDIT);
    OA_Permission::enforceAccessToObject('banners', $bannerid);
} else {
    OA_Permission::enforceAccessToObject('banners', $bannerid, true);
}

/*
storage type / media type
sql gif
sql png
sql jpeg
sql swf
sql mov
web gif
web png
web jpeg
web swf
web mov
url gif
url png
url jpeg
url swf
url mov
txt text
html html
*/

/*-------------------------------------------------------*/
/* HTML framework                                        */
/*-------------------------------------------------------*/

//decide whether this is add or edit, get banner data or initialise it
if ($bannerid != '') {
    // Fetch the data from the database
    $doBanners = OA_Dal::factoryDO('banners');
    if ($doBanners->get($bannerid)) {
        $row = $doBanners->toArray();
    }

    // Set basic values
    $type               = $row['storagetype'];
    $ext_bannertype     = $row['ext_bannertype'];
    $hardcoded_links    = array();
    $hardcoded_targets  = array();
    $hardcoded_sources  = array();

    if (empty($ext_bannertype)) {
        if ($type == 'html') {
            $ext_bannertype = 'bannerTypeHtml:oxHtml:genericHtml';
        } elseif ($type == 'txt') {
            $ext_bannertype = 'bannerTypeText:oxText:genericText';
        }
    }
    // Check for hard-coded links
    if (!empty($row['parameters'])) {
        $aSwfParams = unserialize($row['parameters']);
        if (!empty($aSwfParams['swf'])) {
            foreach ($aSwfParams['swf'] as $iKey => $aSwf) {
                $hardcoded_links[$iKey]   = $aSwf['link'];
                $hardcoded_targets[$iKey] = $aSwf['tar'];
                $hardcoded_sources[$iKey] = '';
            }
        }
    }
    if (!empty($row['filename'])) {
        $row['replaceimage'] = "f"; //select keep image by default
    }

    if (!empty($row['alt_filename'])) {
        $row['replacealtimage'] = "f"; //select keep backup image by default
    }

    $row['hardcoded_links'] = $hardcoded_links;
    $row['hardcoded_targets'] = $hardcoded_targets;
    $row['hardcoded_sources'] = $hardcoded_sources;
    $row['clientid']   = $clientid;
}
else {
    // Set default values for new banner
    $row['bannerid']     = '';
    $row['campaignid']   = $campaignid;
    $row['clientid']     = $clientid;
    $row['alt']          = '';
    $row['status']       = '';
    $row['bannertext']   = '';
    $row['url']          = "http://";
    $row['target']       = '';
    $row['imageurl']     = "http://";
    $row['width']        = '';
    $row['height']       = '';
    $row['htmltemplate'] = '';
    $row['description']  = '';
    $row['comments']     = '';
    $row['contenttype']  = '';
    $row['adserver']     = '';
    $row['keyword']      = '';
    $row["weight"]       = $pref['default_banner_weight'];

    $row['hardcoded_links'] = array();
    $row['hardcoded_targets'] = array();
}
if ($ext_bannertype)
{
    list($extension, $group, $plugin) = explode(':', $ext_bannertype);
    $oComponent = &OX_Component::factory($extension, $group, $plugin);
    //  we may want to use the ancestor class for some sort of generic functionality
    if (!$oComponent)
    {
        $oComponent = OX_Component::getFallbackHandler($extension);
    }
    $formDisabled = (!$oComponent || !$oComponent->enabled);
}
if ((!$ext_bannertype) && $type && (!in_array($type, array('sql','web','url','html','txt'))))
{
    list($extension, $group, $plugin) = explode('.',$type);
    $oComponent = &OX_Component::factoryByComponentIdentifier($extension,$group,$plugin);
    $formDisabled = (!$oComponent || !$oComponent->enabled);
    if ($oComponent)
    {
        $ext_bannertype = $type;
        $type = $oComponent->getStorageType();
    }
    else
    {
        $ext_bannertype = '';
        $type = '';
    }
}


// If adding a new banner or used storing type is disabled
// determine which bannertype to show as default
# To delete banners options, erase from here.
$show_sql   = $conf['allowedBanners']['sql'];
$show_web   = $conf['allowedBanners']['web'];
// $show_url   = $conf['allowedBanners']['url'];
// $show_html  = $conf['allowedBanners']['html'];
// $show_txt   = $conf['allowedBanners']['text'];

if (isset($type) && $type == "sql")      $show_sql     = true;
if (isset($type) && $type == "web")      $show_web     = true;
// if (isset($type) && $type == "url")      $show_url     = true;
// if (isset($type) && $type == "html")     $show_html    = true;
// if (isset($type) && $type == "txt")      $show_txt     = true;

$bannerTypes = array();
if ($show_web) {
    $bannerTypes['web']['web'] = $GLOBALS['strWebBanner'];
}
if ($show_sql) {
    $bannerTypes['sql']['sql'] = $GLOBALS['strMySQLBanner'];
}
// if ($show_url) {
//     $bannerTypes['url']['url']= $GLOBALS['strURLBanner'];
// }
// if ($show_html) {
//     $aBannerTypeHtml = OX_Component::getComponents('bannerTypeHtml');
//     foreach ($aBannerTypeHtml AS $tmpComponent)
//     {
//         $componentIdentifier = $tmpComponent->getComponentIdentifier();
//         $bannerTypes['html'][$componentIdentifier] = $tmpComponent->getOptionDescription();
//     }
// }
// if ($show_txt) {
//     $aBannerTypeText = OX_Component::getComponents('bannerTypeText');
//     foreach ($aBannerTypeText AS $tmpComponent)
//     {
//         $componentIdentifier = $tmpComponent->getComponentIdentifier();
//         $bannerTypes['text'][$componentIdentifier] = $tmpComponent->getOptionDescription();
//     }
// }

if (!$type)
{
//     if ($show_txt)     $type = "txt";
//     if ($show_html)    $type = "html";
//     if ($show_url)     $type = "url";
    if ($show_sql)     $type = "sql";
    if ($show_web)     $type = "web";
}

//build banner form
$form = buildBannerForm($type, $row, $oComponent, $formDisabled);
$valid = $form->validate();
if ($valid && $oComponent && $oComponent->enabled)
{
    $valid = $oComponent->validateForm($form);
}
if ($valid)
{
    //process submitted values
    processForm($bannerid, $form, $oComponent, $formDisabled);
}
else { //either validation failed or form was not submitted, display the form
    displayPage($bannerid, $campaignid, $clientid, $bannerTypes, $row, $type, $form, $ext_bannertype, $formDisabled);
}



function displayPage($bannerid, $campaignid, $clientid, $bannerTypes, $row, $type, $form, $ext_bannertype, $formDisabled=false)
{
    // Initialise some parameters
    $pageName = basename($_SERVER['PHP_SELF']);
    $aEntities = array('clientid' => $clientid, 'campaignid' => $campaignid, 'bannerid' => $bannerid);

    $entityId = OA_Permission::getEntityId();
    if (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) {
        $entityType = 'advertiser_id';
    } else {
        $entityType = 'agency_id';
    }

    // Display navigation
    $aOtherCampaigns = Admin_DA::getPlacements(array($entityType => $entityId));
    $aOtherBanners = Admin_DA::getAds(array('placement_id' => $campaignid), false);
$title = 'Upload Mobile Banners'; # Title of head and Pages.
$oHeaderModel = new OA_Admin_UI_Model_PageHeaderModel($title);
phpAds_PageHeader('campaigns-banners', $oHeaderModel);
//     MAX_displayNavigationBanner($pageName, $aOtherCampaigns, $aOtherBanners, $aEntities);

    //actual page content - type chooser and form
    /*-------------------------------------------------------*/
    /* Main code                                             */
    /*-------------------------------------------------------*/
//     $oTpl = new OA_Admin_Template('banner-edits.html');
    $oTpl = new OA_Plugin_Template('mobile-banner-edit.html');

    $oTpl->assign('clientId',  $clientid);
    $oTpl->assign('campaignId',  $campaignid);
    $oTpl->assign('bannerId',  $bannerid);
    $oTpl->assign('bannerTypes', $bannerTypes);
    $oTpl->assign('bannerType', ($ext_bannertype ? $ext_bannertype : $type));
    $oTpl->assign('bannerHeight', $row["height"]);
    $oTpl->assign('bannerWidth', $row["width"]);
    $oTpl->assign('disabled', $formDisabled);
    $oTpl->assign('form', $form->serialize());


    $oTpl->display();

    /*********************************************************/
    /* HTML framework                                        */
    /*********************************************************/
    phpAds_PageFooter();
}


function buildBannerForm($type, $row, &$oComponent=null, $formDisabled=false)
{
GLOBAL $bannerLabels;
    //-- Build forms
    $form = new OA_Admin_UI_Component_Form("bannerForm", "POST", $_SERVER['PHP_SELF'], null, array("enctype"=>"multipart/form-data"));
    $form->forceClientValidation(true);
    $form->addElement('hidden', 'clientid', $row['clientid']);
    $form->addElement('hidden', 'campaignid', $row['campaignid']);
    $form->addElement('hidden', 'bannerid', $row['bannerid']);
    $form->addElement('hidden', 'type', $type);
    $form->addElement('hidden', 'status', $row['status']);

    if ($row['contenttype'] == 'swf' && empty($row['alt_contenttype']) && empty($row['alt_imageurl'])) {
        $form->addElement('custom', 'banner-backup-note', null, null);
    }

    $form->addElement('header', 'header_basic', $GLOBALS['strBasicInformation']);
    if (OA_Permission::isAccount(OA_ACCOUNT_ADMIN) || OA_Permission::isAccount(OA_ACCOUNT_MANAGER)) {
        $form->addElement('text', 'description', $GLOBALS['strName']);
    }
    else {
        $form->addElement('static', 'description', $GLOBALS['strName'], $row['description']);
    }

    //local banners
    if ($type == 'sql' || $type == 'web') {
        if ($type == 'sql') {
            $header = $form->createElement('header', 'header_sql', $GLOBALS['strMySQLBanner']." -  banner creative");
        }
        else {
            $header = $form->createElement('header', 'header_sql', $GLOBALS['strWebBanner']." -  banner creative");
        }
        $header->setAttribute('icon', 'icon-banner-stored.gif');
        $form->addElement($header);

        $imageName = _getContentTypeIconImageName($row['contenttype']);
        $size = _getBannerSizeText($type, $row['filename']);

##########################
############
# Upload Multiple Banners- First Field for normal banner
############
##########################
        addUploadGroup($form, $row,
            array(
                'uploadName' => 'upload[normal]',
                'radioName' => 'replaceimage',
                'imageName'  => $imageName,
                'fileName'  => $row['filename'],
                'fileSize'  => $size,
                'newLabel'  => $GLOBALS['strNewBannerFile'],
                'updateLabel'  => $GLOBALS['strUploadOrKeep'],
                'handleSWF' => true
              )
        );

        $header = $form->createElement('header', 'mobile_header',"Select images for Mobile banners against each category (Image size must match)");
        $form->addElement($header);
        $form->addElement('static', 'mobile_categories', 'Categories');

##########################
############
# ADd Upload Input Fields
############
##########################
        addUploadGroup($form, $row,
            array(
                'uploadName' => 'upload[extralarge]',
                'radioName' => 'replaceimage',
                'imageName'  => $imageName,
                'fileName'  => $row['filename'],
                'fileSize'  => $size,
                'newLabel'  => $bannerLabels['extralarge']['strNewBannerFile'],
                'updateLabel'  => $GLOBALS['strUploadOrKeep'],
                'handleSWF' => true
              )
        );
        addUploadGroup($form, $row,
            array(
                'uploadName' => 'upload[large]',
                'radioName' => 'replaceimage',
                'imageName'  => $imageName,
                'fileName'  => $row['filename'],
                'fileSize'  => $size,
                'newLabel'  => $bannerLabels['large']['strNewBannerFile'],
                'updateLabel'  => $GLOBALS['strUploadOrKeep'],
                'handleSWF' => true
              )
        );
        addUploadGroup($form, $row,
            array(
                'uploadName' => 'upload[medium]',
                'radioName' => 'replaceimage',
                'imageName'  => $imageName,
                'fileName'  => $row['filename'],
                'fileSize'  => $size,
                'newLabel'  => $bannerLabels['medium']['strNewBannerFile'],
                'updateLabel'  => $GLOBALS['strUploadOrKeep'],
                'handleSWF' => true
              )
        );
        addUploadGroup($form, $row,
            array(
                'uploadName' => 'upload[small]',
                'radioName' => 'replaceimage',
                'imageName'  => $imageName,
                'fileName'  => $row['filename'],
                'fileSize'  => $size,
                'newLabel'  => $bannerLabels['small']['strNewBannerFile'],
                'updateLabel'  => $GLOBALS['strUploadOrKeep'],
                'handleSWF' => true
              )
        );
##########################
############
# End Upload Input Fields
############
##########################

        if ($row['contenttype'] == 'swf') {
            $altImageName = _getContentTypeIconImageName($row['alt_contenttype']);
            $altSize = _getBannerSizeText($type, $row['alt_filename']);

            addUploadGroup($form, $row,
                array(
                    'uploadName' => 'uploadalt',
                    'radioName' => 'replacealtimage',
                    'imageName'  => $altImageName,
                    'fileSize'  => $altSize,
                    'fileName'  => $row['alt_filename'],
                    'newLabel'  => $GLOBALS['strNewBannerFileAlt'],
                    'updateLabel'  => $GLOBALS['strUploadOrKeep'],
                    'handleSWF' => false
                  )
            );
        }


        $form->addElement('header', 'header_b_links', "Banner link");
        if (count($row['hardcoded_links']) == 0) {
            $form->addElement('text', 'url', $GLOBALS['strURL']);
            $targetElem = $form->createElement('text', 'target', $GLOBALS['strTarget']);
            $targetElem->setAttribute('maxlength', '16');
            $form->addElement($targetElem);
        }
        else {
            foreach ($row['hardcoded_links'] as $key => $val) {
                $link['text'] = $form->createElement('text', "alink[".$key."]", null);
                $link['text']->setAttribute("style", "width:330px");
                $link['radio'] = $form->createElement('radio', "alink_chosen", null, null, $key);
                $form->addGroup($link, 'url_group', $GLOBALS['strURL'], "", false);

                if (isset($row['hardcoded_targets'][$key])) {
                    $targetElem = $form->createElement('text', "atar[".$key."]", $GLOBALS['strTarget']);
                    $targetElem->setAttribute('maxlength', '16');
                    $form->addElement($targetElem);
                }
                if (count($row['hardcoded_links']) > 1) {
                    $form->addElement('text', "asource[".$key."]", $GLOBALS['strOverwriteSource']);
                }
            }
            $form->addElement('hidden', 'url', $row['url']);
        }
        $form->addElement('header', 'header_b_display', 'Banner display');
        $form->addElement('text', 'alt', $GLOBALS['strAlt']);
        $form->applyFilter('alt', 'phpAds_htmlQuotes');
        $form->addElement('text', 'statustext', $GLOBALS['strStatusText']);
        $form->addElement('text', 'bannertext', $GLOBALS['strTextBelow']);
        $form->applyFilter('bannertext', 'phpAds_htmlQuotes');

        if (!empty($row['bannerid'])) {
            $sizeG['width'] = $form->createElement('text', 'width', $GLOBALS['strWidth'].":");
            $sizeG['width']->setAttribute('onChange', 'oa_sizeChangeUpdateMessage("warning_change_banner_size");');
            $sizeG['width']->setSize(5);

            $sizeG['height'] = $form->createElement('text', 'height', $GLOBALS['strHeight'].":");
            $sizeG['height']->setAttribute('onChange', 'oa_sizeChangeUpdateMessage("warning_change_banner_size");');
            $sizeG['height']->setSize(5);
            $form->addGroup($sizeG, 'size', $GLOBALS['strSize'], "&nbsp;", false);

            //validation rules
            $translation = new OX_Translation();
            $widthRequiredRule = array($translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strWidth'])), 'required');
            $widthPositiveRule = array($translation->translate($GLOBALS['strXGreaterThanZeroField'], array($GLOBALS['strWidth'])), 'min', 1);
            $heightRequiredRule = array($translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strHeight'])), 'required');
            $heightPositiveRule = array($translation->translate($GLOBALS['strXGreaterThanZeroField'], array($GLOBALS['strHeight'])), 'min', 1);
            $numericRule = array($GLOBALS['strNumericField'] , 'numeric');

            $form->addGroupRule('size', array(
                'width' => array($widthRequiredRule, $numericRule, $widthPositiveRule),
                'height' => array($heightRequiredRule, $numericRule, $heightPositiveRule)));
        }
        if (!isset($row['contenttype']) || $row['contenttype'] == 'swf')
        {
            $form->addElement('checkbox', 'transparent', $GLOBALS['strSwfTransparency'], $GLOBALS['strSwfTransparency']);
        }

        //TODO $form->addRule("size", 'Please enter a number', 'numeric'); //this should make all fields in group size are numeric
    }

    //external banners
    if ($type == "url") {
        $header = $form->createElement('header', 'header_txt', $GLOBALS['strURLBanner']);
        $header->setAttribute('icon', 'icon-banner-url.gif');
        $form->addElement($header);

        $form->addElement('text', 'imageurl', $GLOBALS['strNewBannerURL']);

        if ($row['contenttype'] == 'swf') {
            $altImageName = _getContentTypeIconImageName($row['alt_contenttype']);
            $altSize = _getBannerSizeText($type, $row['alt_filename']);

            $form->addElement('text', 'alt_imageurl', $GLOBALS['strNewBannerFileAlt']);
        }

        $form->addElement('header', 'header_b_links', "Banner link");
        $form->addElement('text', 'url', $GLOBALS['strURL']);
        $targetElem = $form->createElement('text', 'target', $GLOBALS['strTarget']);
        $targetElem->setAttribute('maxlength', '16');
        $form->addElement($targetElem);

        $form->addElement('header', 'header_b_display', 'Banner display');
        $form->addElement('text', 'alt', $GLOBALS['strAlt']);
        $form->applyFilter('alt', 'phpAds_htmlQuotes');

        $form->addElement('text', 'statustext', $GLOBALS['strStatusText']);
        $form->addElement('text', 'bannertext', $GLOBALS['strTextBelow']);
        $form->applyFilter('bannertext', 'phpAds_htmlQuotes');

        $sizeG['width'] = $form->createElement('text', 'width', $GLOBALS['strWidth'].":");
        $sizeG['width']->setAttribute('onChange', 'oa_sizeChangeUpdateMessage("warning_change_banner_size");');
        $sizeG['width']->setSize(5);

        $sizeG['height'] = $form->createElement('text', 'height', $GLOBALS['strHeight'].":");
        $sizeG['height']->setAttribute('onChange', 'oa_sizeChangeUpdateMessage("warning_change_banner_size");');
        $sizeG['height']->setSize(5);
        $form->addGroup($sizeG, 'size', $GLOBALS['strSize'], "&nbsp;", false);

        if (!isset($row['contenttype']) || $row['contenttype'] == 'swf')
        {
            $form->addElement('checkbox', 'transparent', $GLOBALS['strSwfTransparency'], $GLOBALS['strSwfTransparency']);
        }

        //validation rules
        $translation = new OX_Translation();
        $widthRequiredRule = array($translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strWidth'])), 'required');
        $widthPositiveRule = array($translation->translate($GLOBALS['strXGreaterThanZeroField'], array($GLOBALS['strWidth'])), 'min', 1);
        $heightRequiredRule = array($translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strHeight'])), 'required');
        $heightPositiveRule = array($translation->translate($GLOBALS['strXGreaterThanZeroField'], array($GLOBALS['strHeight'])), 'min', 1);
        $numericRule = array($GLOBALS['strNumericField'] , 'numeric');

        $form->addGroupRule('size', array(
            'width' => array($widthRequiredRule, $numericRule, $widthPositiveRule),
            'height' => array($heightRequiredRule, $numericRule, $heightPositiveRule)));
    }

    //html & text banners
    if ($oComponent)
    {
        $oComponent->buildForm($form, $row);
    }

    $translation = new OX_Translation();

    //common for all banners
    if (OA_Permission::isAccount(OA_ACCOUNT_ADMIN) || OA_Permission::isAccount(OA_ACCOUNT_MANAGER)) {
        $form->addElement('header', 'header_additional', "Additional data");
        $form->addElement('text', 'keyword', $GLOBALS['strKeyword']);
        $weightElem = $form->createElement('text', 'weight', $GLOBALS['strWeight']);
        $weightElem->setSize(6);
        $form->addElement($weightElem);
        $form->addElement('textarea', 'comments', $GLOBALS['strComments']);
        $weightPositiveRule = $translation->translate($GLOBALS['strXPositiveWholeNumberField'], array($GLOBALS['strWeight']));
        $form->addRule('weight', $weightPositiveRule, 'numeric');
    }


    //we want submit to be the last element in its own separate section
    $form->addElement('controls', 'form-controls');
    $form->addElement('submit', 'submit', 'Save changes');

    //validation rules
    if (OA_Permission::isAccount(OA_ACCOUNT_ADMIN) || OA_Permission::isAccount(OA_ACCOUNT_MANAGER)) {
        $urlRequiredMsg = $translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strName']));
        $form->addRule('description', $urlRequiredMsg, 'required');
    }

    //set banner values
    $form->setDefaults($row);

    foreach ($row['hardcoded_links'] as $key => $val) {
        $swfLinks["alink[".$key."]"] = phpAds_htmlQuotes($val);

        if ($val == $row['url']) {
            $swfLinks['alink_chosen'] = $key;
        }
        if (isset($row['hardcoded_targets'][$key])) {
            $swfLinks["atar[".$key."]"] = phpAds_htmlQuotes($row['hardcoded_targets'][$key]);
        }
        if (count($row['hardcoded_links']) > 1) {
            $swfLinks["asource[".$key."]"] = phpAds_htmlQuotes($row['hardcoded_sources'][$key]);
        }
    }
    $form->setDefaults($swfLinks);
    if ($formDisabled)
    {
        $form->freeze();
    }

    return $form;
}


function addUploadGroup($form, $row, $vars)
{
        $uploadG = array();
        if (isset($vars['fileName']) && $vars['fileName'] != '') {
            $uploadG['radio1'] = $form->createElement('radio', $vars['radioName'], null, (empty($vars['imageName']) ? '' : "<img src='".OX::assetPath()."/images/".$vars['imageName']."' align='absmiddle'> ").$vars['fileName']."<i dir=".$GLOBALS['phpAds_TextDirection'].">(".$vars['fileSize'].")</i>", 'f');
            $uploadG['radio2'] = $form->createElement('radio', $vars['radioName'], null, null, 't');
            $uploadG['upload'] = $form->createElement('file', $vars['uploadName'], null, array("onchange" => "selectFile(this, ".($vars['handleSWF'] ? 'true' : 'false').")", "style" => "width: 250px;"));
            if ($vars['handleSWF']) {
                $uploadG['checkSWF'] = $form->createElement("checkbox", "checkswf", null, $GLOBALS['strCheckSWF']);
                $form->addDecorator('checkswf', 'tag',
                    array('attributes' =>
                        array('id' => 'swflayer', 'style' => 'display:none')));
            }

            $form->addGroup($uploadG, $vars['uploadName'].'_group', $vars['updateLabel'], array("<br>", "", "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"), false);
        }
        else { //add new creative
            $uploadG['hidden'] = $form->createElement("hidden", $vars['radioName'], "t");
            $uploadG['upload'] = $form->createElement('file', $vars['uploadName'], null, array("onchange" => "selectFile(this, ".($vars['handleSWF'] ? 'true' : 'false').")", "size" => 26, "style" => "width: 250px"));
            if ($vars['handleSWF']) {
                $uploadG['checkSWF'] = $form->createElement("checkbox", "checkswf", null, $GLOBALS['strCheckSWF']);
                $form->addDecorator('checkswf', 'tag',
                    array('attributes' =>
                        array('id' => 'swflayer', 'style' => 'display:none')));
            }

            $form->addGroup($uploadG, $vars['uploadName'].'_group', $vars['newLabel'], "<br>", false);
        }
        $form->setDefaults(array("checkswf" => "t")); //TODO does not work??
}











































function processForm($bannerid, $form, &$oComponent, $formDisabled=false)
{
GLOBAL $mobileBannerTypes, $mobileBannerWidthHeight;
$MP = MAX_PATH. "/www/admin/";

    $aFields = $form->exportValues();

    $doBanners = OA_Dal::factoryDO('banners');
    // Get the existing banner details (if it is not a new banner)
    if (!empty($bannerid)) {
        if ($doBanners->get($bannerid)) {
            $aBanner = $doBanners->toArray();
        }
    }

    $aVariables = array();
    $aVariables['campaignid']      = $aFields['campaignid'];
    $aVariables['target']          = isset($aFields['target']) ? $aFields['target'] : '';
    $aVariables['height']          = isset($aFields['height']) ? $aFields['height'] : 0;
    $aVariables['width']           = isset($aFields['width'])  ? $aFields['width'] : 0;
    $aVariables['weight']          = !empty($aFields['weight']) ? $aFields['weight'] : 0;
    $aVariables['adserver']        = !empty($aFields['adserver']) ? $aFields['adserver'] : '';
    $aVariables['alt']             = !empty($aFields['alt']) ? $aFields['alt'] : '';
    $aVariables['bannertext']      = !empty($aFields['bannertext']) ? $aFields['bannertext'] : '';
    $aVariables['htmltemplate']    = !empty($aFields['htmltemplate']) ? $aFields['htmltemplate'] : '';
    $aVariables['description']     = !empty($aFields['description']) ? $aFields['description'] : '';
    $aVariables['imageurl']        = (!empty($aFields['imageurl']) && $aFields['imageurl'] != 'http://') ? $aFields['imageurl'] : '';
    $aVariables['url']             = (!empty($aFields['url']) && $aFields['url'] != 'http://') ? $aFields['url'] : '';
    $aVariables['status']          = ($aFields['status'] != '') ? $aFields['status'] : '';
    $aVariables['statustext']      = !empty($aFields['statustext']) ? $aFields['statustext'] : '';
    $aVariables['storagetype']     = $aFields['type'];
    $aVariables['ext_bannertype']  = $aFields['ext_bannertype'];
    $aVariables['comments']        = $aFields['comments'];

    $aVariables['filename']        = !empty($aBanner['filename']) ? $aBanner['filename'] : '';
    $aVariables['contenttype']     = !empty($aBanner['contenttype']) ? $aBanner['contenttype'] : '';
    
    # Link id, which used to determine the relationship of multiple banners uploaded.
#    $aVariables['linkid']        = md5(time());

    if ($aFields['type'] == 'url') {
        $aVariables['contenttype'] = OA_Creative_File::staticGetContentTypeByExtension($aVariables['imageurl']);
        if (empty($aVariables['contenttype'])) {
            // Assume dynamic urls (i.e. http://www.example.com/foo?bar) are "gif"
            $aVariables['contenttype'] = 'gif';
        }
    } elseif ($aFields['type'] == 'txt') {
        // Text banners should always have a "txt" content type
        $aVariables['contenttype'] = 'txt';
    }

    $aVariables['alt_filename']    = !empty($aBanner['alt_filename']) ? $aBanner['alt_filename'] : '';
    $aVariables['alt_contenttype'] = !empty($aBanner['alt_contenttype']) ? $aBanner['alt_contenttype'] : '';
    $aVariables['alt_imageurl']    = !empty($aFields['alt_imageurl']) ? $aFields['alt_imageurl'] : '';

    if (isset($aFields['keyword']) && $aFields['keyword'] != '') {
        $keywordArray = split('[ ,]+', $aFields['keyword']);
        $aVariables['keyword'] = implode(' ', $keywordArray);
    } else {
        $aVariables['keyword'] = '';
    }

    $editSwf = false;

    // Deal with any files that are uploaded.
    if (!empty($_FILES['upload']) && $aFields['replaceimage'] == 't') { //TODO refactor upload to be a valid quickform elem

###############################################################################################################################################
# Check uploaded file and store values in new $_FILES index.
###############################################################################################################################################
	# If files uploaded, then, check which banner upload, one by one and store in the array.
	$mobileBanners=array(); # store uploaded banners.
	$mobileBannerFiles=array();// Store all uploaded file's name, width, height etc.

	// Store values in a new array, so that we can collect information later
	foreach($mobileBannerTypes as $index => $mobileBannerType){
		if(!empty($_FILES['upload']['name']["$mobileBannerType"])){
			$mobileBanners["$mobileBannerType"]="$mobileBannerType";
			$_FILES["{$mobileBanners[$mobileBannerType]}"]['name']		= $_FILES['upload']['name']["{$mobileBanners[$mobileBannerType]}"];
			$_FILES["{$mobileBanners[$mobileBannerType]}"]['type']		= $_FILES['upload']['type']["{$mobileBanners[$mobileBannerType]}"];
			$_FILES["{$mobileBanners[$mobileBannerType]}"]['tmp_name']	= $_FILES['upload']['tmp_name']["{$mobileBanners[$mobileBannerType]}"];
			$_FILES["{$mobileBanners[$mobileBannerType]}"]['error']		= $_FILES['upload']['error']["{$mobileBanners[$mobileBannerType]}"];
			$_FILES["{$mobileBanners[$mobileBannerType]}"]['size']		= $_FILES['upload']['size']["{$mobileBanners[$mobileBannerType]}"];
		}
		$aFile = null;
		$oFile = OA_Creative_File::factoryUploadedFile("$mobileBannerType");
		if (PEAR::isError($oFile)) {
			phpAds_PageHeader(1);
			phpAds_Die($strErrorOccurred, htmlspecialchars($oFile->getMessage()));
		}
		$oFile->store($aFields['type']);
		$aFile = $oFile->getFileDetails();
		// Store all uploaded file's name, width, height etc.
		if (!empty($aFile)) {
			$mobileBannerFiles["$mobileBannerType"]['filename']		= $aFile['filename'];
			$mobileBannerFiles["$mobileBannerType"]['contenttype']		= $aFile['contenttype'];
			# change mobile size of uploaded banner
			# if Banner is regular banner, then let it use default height and width
			# else change the size as defined in configuration file.
			if($mobileBannerType == 'normal'){
				$mobileBannerFiles["$mobileBannerType"]['width']	= $aFile['width'];
				$mobileBannerFiles["$mobileBannerType"]['height']	= $aFile['height'];
			}else{
				$mobileBannerFiles["$mobileBannerType"]['width']	= $mobileBannerWidthHeight["$mobileBannerType"]['width'];
				$mobileBannerFiles["$mobileBannerType"]['height']	= $mobileBannerWidthHeight["$mobileBannerType"]['height'];
			}
			$mobileBannerFiles["$mobileBannerType"]['pluginversion']	= $aFile['pluginversion'];
			$mobileBannerFiles["$mobileBannerType"]['editswf']		= $aFile['editswf'];
		}
	}

        // Delete the old file for this banner
        if (!empty($aBanner['filename']) && ($aBanner['storagetype'] == 'web' || $aBanner['storagetype'] == 'sql'))
            DataObjects_Banners::deleteBannerFile($aBanner['storagetype'], $aBanner['filename']);
    }

    if (!empty($_FILES['uploadalt']) && $_FILES['uploadalt']['size'] > 0
        &&  $aFields['replacealtimage'] == 't') {

        //TODO: Check image only? - Wasn't enforced before
        $oFile = OA_Creative_File::factoryUploadedFile('uploadalt');
        if (PEAR::isError($oFile)) {
            phpAds_PageHeader(1);
            phpAds_Die($strErrorOccurred, htmlspecialchars($oFile->getMessage()));
        }
        $oFile->store($aFields['type']);
        $aFile = $oFile->getFileDetails();

        if (!empty($aFile)) {
            $aVariables['alt_filename']    = $aFile['filename'];
            $aVariables['alt_contenttype'] = $aFile['contenttype'];
        }
    }

    // Handle SWF transparency
    if ($aVariables['contenttype'] == 'swf') {
        $aVariables['transparent'] = isset($aFields['transparent']) && $aFields['transparent'] ? 1 : 0;
    }

    // Update existing hard-coded links if new file has not been uploaded
    if ($aVariables['contenttype'] == 'swf' && empty($_FILES['upload']['tmp_name'])
        && isset($aFields['alink']) && is_array($aFields['alink']) && count($aFields['alink'])) {
        // Prepare the parameters
        $parameters_complete = array();

        // Prepare targets
        if (!isset($aFields['atar']) || !is_array($aFields['atar'])) {
            $aFields['atar'] = array();
        }

        foreach ($aFields['alink'] as $key => $val) {
            if (substr($val, 0, 7) == 'http://' && strlen($val) > 7) {
                if (!isset($aFields['atar'][$key])) {
                    $aFields['atar'][$key] = '';
                }

                if (isset($aFields['alink_chosen']) && $aFields['alink_chosen'] == $key) {
                    $aVariables['url'] = $val;
                    $aVariables['target'] = $aFields['atar'][$key];
                }

/*
                if (isset($aFields['asource'][$key]) && $aFields['asource'][$key] != '') {
                    $val .= '|source:'.$aFields['asource'][$key];
                }
*/
                $parameters_complete[$key] = array(
                    'link' => $val,
                    'tar'  => $aFields['atar'][$key]
                );
            }
        }

        $parameters = array('swf' => $parameters_complete);
    } else {
        $parameters = null;
    }

    $aVariables['parameters'] = serialize($parameters);
    //TODO: deleting images is not viable because they could still be in use in the delivery cache
    //    // Delete any old banners...
    //    if (!empty($aBanner['filename']) && $aBanner['filename'] != $aVariables['filename']) {
    //        phpAds_ImageDelete($aBanner['storagetype'], $aBanner['filename']);
    //    }
    //    if (!empty($aBanner['alt_filename']) && $aBanner['alt_filename'] != $aVariables['alt_filename']) {
    //        phpAds_ImageDelete($aBanner['storagetype'], $aBanner['alt_filename']);
    //    }

    // Clients are only allowed to modify certain fields, ensure that other fields are unchanged
    if (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) {
        $aVariables['weight']       = $aBanner['weight'];
        $aVariables['description']  = $aBanner['name'];
        $aVariables['comments']     = $aBanner['comments'];
    }

    $insert = (empty($bannerid)) ? true : false;

    if ($oComponent)
    {
        $result = $oComponent->preprocessForm($insert, $bannerid, $aFields, $aVariables);
        if ($result === false)
        {
            // handle error
            return false;
        }
    }

    if ($insert) {
	foreach($mobileBannerTypes as $key=> $mobileBannerType){
		$aVariables['filename']      = $mobileBannerFiles["$mobileBannerType"]['filename'];
		$aVariables['contenttype']   = $mobileBannerFiles["$mobileBannerType"]['contenttype'];
		$aVariables['width']         = $mobileBannerFiles["$mobileBannerType"]['width'];
		$aVariables['height']        = $mobileBannerFiles["$mobileBannerType"]['height'];
		$aVariables['pluginversion'] = $mobileBannerFiles["$mobileBannerType"]['pluginversion'];
		$editSwf                     = $mobileBannerFiles["$mobileBannerType"]['editswf'];
// 		$aVariables['ext_bannertype'] = $mobileBannerType; // Save image type. extra large, large, small, etc.
		$doBanners->setFrom($aVariables);
		$bannerid = $doBanners->insert();
	}
        // Run the Maintenance Priority Engine process
        OA_Maintenance_Priority::scheduleRun();
    } else {
        $doBanners->update();
        // check if size has changed
        if ($aVariables['width'] != $aBanner['width'] || $aVariables['height'] != $aBanner['height']) {
            MAX_adjustAdZones($bannerid);
        }
    }
    if ($oComponent)
    {
        $result = $oComponent->processForm($insert, $bannerid, $aFields, $aVariables);
        if ($result === false)
        {
            // handle error
            // remove rec from banners table?
            return false;
        }
    }
    $translation = new OX_Translation ();
    if ($insert) {
        // Queue confirmation message
        $translated_message = $translation->translate ( $GLOBALS['strBannerHasBeenAdded'], array(
            MAX::constructURL(MAX_URL_ADMIN, 'banner-edit.php?clientid=' .  $aFields['clientid'] . '&campaignid=' . $aFields['campaignid'] . '&bannerid=' . $bannerid),
            htmlspecialchars($aFields['description'])
        ));
        OA_Admin_UI::queueMessage($translated_message, 'local', 'confirm', 0);

        // Determine what the next page is
        if ($editSwf) {
            $nextPage = $MP. "banner-swf.php?clientid=".$aFields['clientid']."&campaignid=".$aFields['campaignid']."&bannerid=$bannerid&insert=true";
        }
        else {
            $nextPage = "campaign-banners.php?clientid=".$aFields['clientid']."&campaignid=".$aFields['campaignid'];
        }
    }
    else {
        // Determine what the next page is
        if ($editSwf) {
            $nextPage = $MP. "banner-swf.php?clientid=".$aFields['clientid']."&campaignid=".$aFields['campaignid']."&bannerid=$bannerid";
        }
        else {
            $translated_message = $translation->translate($GLOBALS['strBannerHasBeenUpdated'],
            array (
                MAX::constructURL ( MAX_URL_ADMIN, 'banner-edit.php?clientid='.$aFields['clientid'].'&campaignid='.$aFields['campaignid'].'&bannerid='.$aFields['bannerid'] ),
                htmlspecialchars ( $aFields ['description'])
            ));
            OA_Admin_UI::queueMessage($translated_message, 'local', 'confirm', 0);
            $nextPage = $MP. "banner-edit.php?clientid=".$aFields['clientid']."&campaignid=".$aFields['campaignid']."&bannerid=$bannerid";
        }
    }

    // Go to the next page
    Header("Location: $nextPage");
    exit;
}


function _getContentTypeIconImageName($contentType)
{
    $imageName = '';
    if (empty($contentType)) {
        return $imageName;
    }

    switch ($contentType) {
        case 'swf':
        case 'dcr':  $imageName = 'icon-filetype-swf.gif'; break;
        case 'jpeg': $imageName = 'icon-filetype-jpg.gif'; break;
        case 'gif':  $imageName = 'icon-filetype-gif.gif'; break;
        case 'png':  $imageName = 'icon-filetype-png.gif'; break;
        case 'rpm':  $imageName = 'icon-filetype-rpm.gif'; break;
        case 'mov':  $imageName = 'icon-filetype-mov.gif'; break;
        default:     $imageName = 'icon-banner-stored.gif'; break;
    }

    return $imageName;
}


function _getBannerSizeText($type, $filename)
{
    $size = phpAds_ImageSize($type, $filename);
    if (round($size / 1024) == 0) {
         $size = $size." bytes";
    }
    else {
         $size = round($size / 1024)." Kb";
    }

    return $size;
}
?>
