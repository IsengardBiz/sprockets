<?php
/**
* About page of the module
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

include_once("admin_header.php");

include_once(ICMS_ROOT_PATH . "/kernel/icmsmoduleabout.php");
$aboutObj = new IcmsModuleAbout();
$aboutObj->render();