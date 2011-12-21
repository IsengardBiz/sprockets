<?php
/**
* Footer page included at the end of each page on user side of the mdoule
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

$icmsTpl->assign("sprockets_adminpage", sprockets_getModuleAdminLink());
$icmsTpl->assign("sprockets_is_admin", $sprockets_isAdmin);
$icmsTpl->assign('sprockets_url', SPROCKETS_URL);
$icmsTpl->assign('sprockets_images_url', SPROCKETS_IMAGES_URL);

$xoTheme->addStylesheet(SPROCKETS_URL . 'module'.(( defined("_ADM_USE_RTL")
	&& _ADM_USE_RTL )?'_rtl':'').'.css');

include_once(ICMS_ROOT_PATH . '/footer.php');