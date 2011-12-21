<?php
/**
* Common file of the module included on all pages of the module
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

if(!defined("SPROCKETS_DIRNAME")) define("SPROCKETS_DIRNAME",
		$modversion['dirname'] = basename(dirname(dirname(__FILE__))));
if(!defined("SPROCKETS_URL")) define("SPROCKETS_URL", ICMS_URL . '/modules/'
		. SPROCKETS_DIRNAME.'/');
if(!defined("SPROCKETS_ROOT_PATH")) define("SPROCKETS_ROOT_PATH", ICMS_ROOT_PATH.'/modules/' 
		. SPROCKETS_DIRNAME . '/');
if(!defined("SPROCKETS_IMAGES_URL")) define("SPROCKETS_IMAGES_URL", SPROCKETS_URL . 'images/');
if(!defined("SPROCKETS_ADMIN_URL")) define("SPROCKETS_ADMIN_URL", SPROCKETS_URL . 'admin/');

// Include the common language file of the module
icms_loadLanguageFile('sprockets', 'common');

include_once(SPROCKETS_ROOT_PATH . "include/functions.php");

// Creating the module object to make it available throughout the module
$sprocketsModule = icms_getModuleInfo(SPROCKETS_DIRNAME);
if (is_object($sprocketsModule)){
	$sprockets_moduleName = $sprocketsModule->getVar('name');
}

// Find if the user is admin of the module and make this info available throughout the module
$sprockets_isAdmin = icms_userIsAdmin(SPROCKETS_DIRNAME);

// Creating the module config array to make it available throughout the module
$sprocketsConfig = icms_getModuleConfig(SPROCKETS_DIRNAME);

// creating the icmsPersistableRegistry to make it available throughout the module
global $icmsPersistableRegistry;
$icmsPersistableRegistry = IcmsPersistableRegistry::getInstance();