<?php
/**
* Configuring the amdin side menu for the module
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

$i = 0;

$adminmenu[$i]['title'] = _MI_SPROCKETS_TAGS;
$adminmenu[$i]['link'] = 'admin/tag.php';

$i++;
$adminmenu[$i]['title'] = _MI_SPROCKETS_RIGHTS;
$adminmenu[$i]['link'] = 'admin/rights.php';

// Included for development/debugging purpose only, uncomment to access taglinks table

/**$i++;
$adminmenu[$i]['title'] = _MI_SPROCKETS_TAGLINKS;
$adminmenu[$i]['link'] = 'admin/taglink.php';*/

$i++;
$adminmenu[$i]['title'] = _MI_SPROCKETS_ARCHIVE;
$adminmenu[$i]['link'] = 'admin/archive.php';

$i++;
$adminmenu[$i]['title'] = _MI_SPROCKETS_CSS_EDITOR;
$adminmenu[$i]['link'] = 'admin/css_editor.php';

global $icmsConfig, $sprocketsConfig;

$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

if (isset($sprocketsModule)) {

	$i = -1;

	$i++;
	$headermenu[$i]['title'] = _PREFERENCES;
	$headermenu[$i]['link'] = '../../system/admin.php?fct=preferences&amp;op=showmod&amp;mod='
		. $sprocketsModule->getVar('mid');
	$i++;
	$headermenu[$i]['title'] = _MI_SPROCKETS_TEMPLATES;
	$headermenu[$i]['link'] = '../../system/admin.php?fct=tplsets&op=listtpl&tplset='
		. $icmsConfig['template_set'] . '&moddir=' . $sprocketsModule->getVar('dirname');

	$i++;
	$headermenu[$i]['title'] = _CO_ICMS_UPDATE_MODULE;
	$headermenu[$i]['link'] = ICMS_URL . '/modules/system/admin.php?fct=modulesadmin&op=update&module='
		. $sprocketsModule->getVar('dirname');

	$i++;
	$headermenu[$i]['title'] = _MODABOUT_ABOUT;
	$headermenu[$i]['link'] = ICMS_URL . '/modules/sprockets/admin/about.php';
}