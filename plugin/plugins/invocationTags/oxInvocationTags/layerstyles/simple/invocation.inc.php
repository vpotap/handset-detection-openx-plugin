<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                                |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                |
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
$Id: invocation.inc.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/


// Define constant used to place code generator
define('phpAds_adLayerLoaded', true);


// Register input variables
MAX_commonRegisterGlobalsArray(array('target', 'align', 'padding', 'closebutton', 'backcolor', 'bordercolor',
					   'valign', 'closetime', 'shifth', 'shiftv', 'nobg', 'noborder'));


/**
 *
 * Layerstyle for invocation tag plugin
 *
 */
class Plugins_oxInvocationTags_Adlayer_Layerstyles_Simple_Invocation extends Plugins_InvocationTags_OxInvocationTags_adlayer
{

    /*-------------------------------------------------------*/
    /* Place ad-generator settings                           */
    /*-------------------------------------------------------*/

    function placeLayerSettings ()
    {
    	global $align, $valign, $closetime, $padding;
    	global $shifth, $shiftv, $closebutton;
    	global $backcolor, $bordercolor;
    	global $nobg, $noborder;
    	global $tabindex;

    	if (!isset($align)) $align = 'right';
    	if (!isset($valign)) $valign = 'top';
    	if (!isset($closetime)) $closetime = '-';
    	if (!isset($padding)) $padding = '2';
    	if (!isset($shifth)) $shifth = 0;
    	if (!isset($shiftv)) $shiftv = 0;
    	if (!isset($closebutton)) $closebutton = 'f';
    	if (!isset($backcolor)) $backcolor = '#FFFFFF';
    	if (!isset($bordercolor)) $bordercolor = '#000000';
    	if (!isset($nobg)) $nobg = 'f';
    	if (!isset($noborder)) $noborder = 'f';

    	$buffer = '';

    	$buffer .= "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    	$buffer .= "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='" . OX::assetPath() . "/images/break-el.gif' height='1' width='100%'></td></tr>";
    	$buffer .= "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Horizontal alignment")."</td><td width='370'>";
    	$buffer .= "<select name='align' style='width:175px;' tabindex='".($tabindex++)."'>";
    		$buffer .= "<option value='left'".($align == 'left' ? ' selected' : '').">".$this->translate("Left")."</option>";
    		$buffer .= "<option value='center'".($align == 'center' ? ' selected' : '').">".$this->translate("Center")."</option>";
    		$buffer .= "<option value='right'".($align == 'right' ? ' selected' : '').">".$this->translate("Right")."</option>";
    	$buffer .= "</select>";
    	$buffer .= "</td></tr>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Vertical alignment")."</td><td width='370'>";
    	$buffer .= "<select name='valign' style='width:175px;' tabindex='".($tabindex++)."'>";
    		$buffer .= "<option value='top'".($valign == 'top' ? ' selected' : '').">".$this->translate("Top")."</option>";
    		$buffer .= "<option value='middle'".($valign == 'middle' ? ' selected' : '').">".$this->translate("Middle")."</option>";
    		$buffer .= "<option value='bottom'".($valign == 'bottom' ? ' selected' : '').">".$this->translate("Bottom")."</option>";
    	$buffer .= "</select>";
    	$buffer .= "</td></tr>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Show close button")."</td><td width='370'>";
    	$buffer .= "<select name='closebutton' style='width:175px;' tabindex='".($tabindex++)."'>";
    		$buffer .= "<option value='t'".($closebutton == 't' ? ' selected' : '').">".$GLOBALS['strYes']."</option>";
    		$buffer .= "<option value='f'".($closebutton == 'f' ? ' selected' : '').">".$GLOBALS['strNo']."</option>";
    	$buffer .= "</select>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Automatically close after")."</td><td width='370'>";
    		$buffer .= "<input class='flat' type='text' name='closetime' size='' value='".(isset($closetime) ? $closetime : '-')."' style='width:60px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrSeconds']."</td></tr>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$buffer .= "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    	$buffer .= "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='" . OX::assetPath() . "/images/break-el.gif' height='1' width='100%'></td></tr>";
    	$buffer .= "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Banner padding")."</td><td width='370'>";
    		$buffer .= "<input class='flat' type='text' name='padding' size='' value='".$padding."' style='width:60px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrPixels']."</td></tr>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Horizontal shift")."</td><td width='370'>";
    		$buffer .= "<input class='flat' type='text' name='shifth' size='' value='".$shifth."' style='width:60px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrPixels']."</td></tr>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Vertical shift")."</td><td width='370'>";
    		$buffer .= "<input class='flat' type='text' name='shiftv' size='' value='".$shiftv."' style='width:60px;' tabindex='".($tabindex++)."'> ".$GLOBALS['strAbbrPixels']."</td></tr>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$this->settings_cp_map();

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Background color")."</td><td width='370'>";
    		$buffer .= "<table border='0' cellspacing='0' cellpadding='0'>";
    		$buffer .= "<tr><td width='22'>";
    		$buffer .= "<table border='0' cellspacing='1' cellpadding='0' bgcolor='#000000'><tr>";
    		$buffer .= "<td id='backcolor_box' bgcolor='".$backcolor."'><img src='" . OX::assetPath() . "/images/spacer.gif' width='16' height='16'></td>";
    		$buffer .= "</tr></table></td><td>";
    		$buffer .= "<input type='text' class='flat' name='backcolor' size='10' maxlength='7' tabindex='".($tabindex++)."' value='".$backcolor."' onFocus='current_cp = this; current_cp_oldval = this.value; current_box = backcolor_box' onChange='c_update()'".($nobg == 't' ? ' disabled' : '').">";
    		$buffer .= "</td><td align='right' width='218'>";
    		$buffer .= "<div id='backDiv'".($nobg == 't' ? " style='display: none'" : '')." onMouseOver='current_cp = backcolor; current_box = backcolor_box' onMouseOut='current_cp = null'><img src='" . OX::assetPath() . "/images/colorpicker.png' width='193' height='18' align='absmiddle' usemap='#colorpicker' border='0'><img src='" . OX::assetPath() . "/images/spacer.gif' width='22' height='1'></div>";
    		$buffer .= "</td></tr></table>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td width='200'>".$this->translate("Border color")."</td><td width='370'>";
    		$buffer .= "<table border='0' cellspacing='0' cellpadding='0'>";
    		$buffer .= "<tr><td width='22'>";
    		$buffer .= "<table border='0' cellspacing='1' cellpadding='0' bgcolor='#000000'><tr>";
    		$buffer .= "<td id='bordercolor_box' bgcolor='".$bordercolor."'><img src='" . OX::assetPath() . "/images/spacer.gif' width='16' height='16'></td>";
    		$buffer .= "</tr></table></td><td>";
    		$buffer .= "<input type='text' class='flat' name='bordercolor' size='10' maxlength='7' tabindex='".($tabindex++)."' value='".$bordercolor."' onFocus='current_cp = this; current_cp_oldval = this.value; current_box = bordercolor_box' onChange='c_update()'".($noborder == 't' ? ' disabled' : '').">";
    		$buffer .= "</td><td align='right' width='218'>";
    		$buffer .= "<div id='borderDiv'".($noborder == 't' ? " style='display: none'" : '')." onMouseOver='current_cp = bordercolor; current_box = bordercolor_box' onMouseOut='current_cp = null'><img src='" . OX::assetPath() . "/images/colorpicker.png' width='193' height='18' align='absmiddle' usemap='#colorpicker' border='0'><img src='" . OX::assetPath() . "/images/spacer.gif' width='22' height='1'></div>";
            $buffer .= "</td></tr></table>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td colspan='2'>";
    	$buffer .= "<input type='checkbox' name='nobg' value='t' tabindex='".($tabindex++)."' onClick='this.form.backcolor.disabled=this.checked;backDiv.style.display=this.checked?\"none\":\"\"'".($nobg == 't' ? ' checked' : '').">&nbsp;";
    	$buffer .= 'Transparent background';
    	$buffer .= "</td></tr>";
    	$buffer .= "<tr><td width='30'>&nbsp;</td>";
    	$buffer .= "<td colspan='2'>";
    	$buffer .= "<input type='checkbox' name='noborder' value='t' tabindex='".($tabindex++)."' onClick='this.form.bordercolor.disabled=this.checked;borderDiv.style.display=this.checked?\"none\":\"\"'".($noborder == 't' ? ' checked' : '').">&nbsp;";
    	$buffer .= 'No border';
    	$buffer .= "</td></tr>";
    	$buffer .= "<tr><td width='30'><img src='" . OX::assetPath() . "/images/spacer.gif' height='5' width='100%'></td></tr>";

    	return $buffer;
    }



    /*-------------------------------------------------------*/
    /* Place ad-generator settings                           */
    /*-------------------------------------------------------*/

    function generateLayerCode(&$mi)
    {
    	$conf = $GLOBALS['_MAX']['CONF'];

    	global $align, $valign, $closetime, $padding;
    	global $shifth, $shiftv, $closebutton;
    	global $backcolor, $bordercolor;
    	global $nobg, $noborder;

    	$mi->parameters[] = 'layerstyle=simple';
    	$mi->parameters[] = 'align='.(isset($align) ? $align : 'right');
    	$mi->parameters[] = 'valign='.(isset($valign) ? $valign : 'top');
    	$mi->parameters[] = 'padding='.(isset($padding) ? (int)$padding : '2');

    	if (!empty($mi->charset)) {
    	    $mi->parameters[] = 'charset='.urlencode($mi->charset);
    	}
    	if (isset($closetime) && $closetime > 0) {
    		$mi->parameters[] = 'closetime='.$closetime;
    	}
    	if (isset($padding)) {
    		$mi->parameters[] = 'padding='.$padding;
    	}
    	if (isset($shifth)) {
    		$mi->parameters[] = 'shifth='.$shifth;
    	}
    	if (isset($shiftv)) {
    		$mi->parameters[] = 'shiftv='.$shiftv;
    	}
    	if (isset($closebutton)) {
    		$mi->parameters[] = 'closebutton='.$closebutton;
    	}
    	if (isset($backcolor)) {
    		$mi->parameters[] = 'backcolor='.substr($backcolor, 1);
    	}
    	if (isset($bordercolor)) {
    		$mi->parameters[] = 'bordercolor='.substr($bordercolor, 1);
    	}
    	if (isset($nobg)) {
    		$mi->parameters[] = 'nobg='.$nobg;
    	}
    	if (isset($noborder)) {
    		$mi->parameters[] = 'noborder='.$noborder;
    	}

    	$scriptUrl = "http:".MAX_commonConstructPartialDeliveryUrl($conf['file']['layer']);
    	if (sizeof($mi->parameters) > 0) {
    		$scriptUrl .= "?".implode ("&", $mi->parameters);
    	}

	$userid = OA_Permission::getUserId();
	$host = 'http://'. $_SERVER['HTTP_HOST'];
	$filepath = $host. "/plugins/invocationTags/oxInvocationTags/handset.php?indicator=$userid&amp;phpfile=al.php&amp;";

	$d = explode("?", $scriptUrl);
	$url = $filepath. $d['1'];

    	$buffer = "<script type='text/javascript'><!--//<![CDATA[
   var ox_u = '{$url}';
   if (document.context) ox_u += '&context=' + escape(document.context);
   document.write(\"<scr\"+\"ipt type='text/javascript' src='\" + ox_u + \"'></scr\"+\"ipt>\");
//]]>--></script>";
    	return $buffer;
    }



    /*-------------------------------------------------------*/
    /* Return $show var for generators                       */
    /*-------------------------------------------------------*/

    function getlayerShowVar ()
    {
    	return array (
            'spacer'      => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
    		'what'        => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
    		//'acid'        => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
    		'campaignid'  => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
    		'target'      => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
    		'source'      => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
    		'charset'     => MAX_PLUGINS_INVOCATION_TAGS_STANDARD,
    		'layerstyle'  => MAX_PLUGINS_INVOCATION_TAGS_CUSTOM,
    		'layercustom' => MAX_PLUGINS_INVOCATION_TAGS_CUSTOM
    	);
    }



    /*-------------------------------------------------------*/
    /* Dec2Hex                                               */
    /*-------------------------------------------------------*/

    function toHex($d)
    {
    	return strtoupper(sprintf("%02x", $d));
    }



    /*-------------------------------------------------------*/
    /* Add scripts and map for color pickers                 */
    /*-------------------------------------------------------*/

    function settings_cp_map()
    {
    	static $done = false;

    	if (!$done)
    	{
    		$done = true;
    ?>
    <script type="text/javascript">
    <!--// <![CDATA[
    var current_cp = null;
    var current_cp_oldval = null;
    var current_box = null;

    function c_pick(value)
    {
    	if (current_cp)
    	{
    		current_cp.value = value;
    		c_update();
    	}
    }

    function c_update()
    {
    	if (!current_cp.value.match(/^#[0-9a-f]{6}$/gi))
    	{
    		current_cp.value = current_cp_oldval;
    		return;
    	}

    	current_cp.value.toUpperCase();
    	current_box.style.backgroundColor = current_cp.value;
    }

    // ]]> -->
    </script>
    <?php
    		echo "<map name=\"colorpicker\">\n";

    		$x = 2;

    		for($i=1; $i <= 255*6; $i+=8)
    		{
    			if($i > 0 && $i <=255 * 1)
    				$incColor='#FF'.$this->toHex($i).'00';
    			elseif ($i>255*1 && $i <=255*2)
    				$incColor='#'.$this->toHex(255-($i-255)).'FF00';
    			elseif ($i>255*2 && $i <=255*3)
    				$incColor='#00FF'.$this->toHex($i-(2*255));
    			elseif ($i>255*3 && $i <=255*4)
    				$incColor='#00'.$this->toHex(255-($i-(3*255))).'FF';
    			elseif ($i>255*4 && $i <=255*5)
    				$incColor='#'.$this->toHex($i-(4*255)).'00FF';
    			elseif ($i>255*5 && $i <255*6)
    				$incColor='#FF00' . $this->toHex(255-($i-(5*255)));

    			echo "<area shape='rect' coords='$x,0,".($x+1).",9' alt='' href='javascript:c_pick(\"$incColor\")' />\n"; $x++;
    		}

    		$x = 2;

    		for($j = 0; $j < 255; $j += 1.34)
    		{
    			$i = round($j);
    			$incColor = '#'.$this->toHex($i).$this->toHex($i).$this->toHex($i);
    			echo "<area shape='rect' coords='$x,11,".($x+1).",20' alt='' href='javascript:c_pick(\"$incColor\")' />\n"; $x++;
    		}

    		echo "</map>";
    	}
    }
}

?>