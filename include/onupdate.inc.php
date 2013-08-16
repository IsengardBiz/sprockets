<?php
/**
* File containing onUpdate and onInstall functions for the module
*
* This file is included by the core in order to trigger onInstall or onUpdate functions when needed.
* Of course, onUpdate function will be triggered when the module is updated, and onInstall when
* the module is originally installed. The name of this file needs to be defined in the
* icms_version.php
*
* <code>
* $modversion['onInstall'] = "include/onupdate.inc.php";
* $modversion['onUpdate'] = "include/onupdate.inc.php";
* </code>
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// this needs to be the latest db version
define('SPROCKETS_DB_VERSION', 1);

/**
 * Updates the module
 *
 * @param <type> $module
 * @return bool
 */

function icms_module_update_sprockets($module) {
    return TRUE;
}

/**
 * Authorises image mimetypes commonly required by this module
 */

function authorise_mimetypes() {
	$dirname = basename(dirname(dirname(__FILE__)));
	$extension_list = array('png', 'gif', 'jpg');
	$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
	foreach ($extension_list as $extension) {
		$allowed_modules = array();
		$mimetypeObj = '';

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('extension', $extension));
		$mimetypeObj = array_shift($system_mimetype_handler->getObjects($criteria));

		if ($mimetypeObj) {
			$allowed_modules = $mimetypeObj->getVar('dirname');
			if (empty($allowed_modules)) {
				$mimetypeObj->setVar('dirname', $dirname);
				$mimetypeObj->store();
			} else {
				if (!in_array($dirname, $allowed_modules)) {
					$allowed_modules[] = $dirname;
					$mimetypeObj->setVar('dirname', $allowed_modules);
					$mimetypeObj->store();
				}
			}
		}
	}
}

//////////// DO NOT BREAK THE LINES, THIS CAN MESS UP THE QUERY ON SOME SYSTEMS ///////////

/**
 * Prepares the module for use, authorises mimetypes, sets up directories and inserts license data
 * 
 * @param <type> $module
 * @return bool
 */
function icms_module_install_sprockets($module) {

	// create an uploads directory for images
	$path = ICMS_ROOT_PATH . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
	$directory_exists = $writeable = TRUE;

	// check if upload directory exists, make one if not
	if (!is_dir($path)) {
		$directory_exists = mkdir($path, 0777);
	}

	// authorise some image mimetypes for convenience
	authorise_mimetypes();

	// insert some licenses so that it is ready for use on installation
	$queries = array();
	
	// a generic tag to hold untagged content
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_tag')
		. " (`title`, `description`) values ('Example', 'For testing purposes, replace this with your own tags.')";

	// some common licenses
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`) values ('Copyright, all rights reserved',
                'This work is subject to copyright and all rights are reserved. Contact the creators for permission if you wish to modify or distribute this work.')";	
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution',
                'This license lets others distribute, remix, tweak, and build upon a work, even commercially, as long as they credit the author for the original creation. This is the most accommodating of licenses offered, in terms of what others can do with works licensed under Attribution.', 'http://creativecommons.org/licenses/by/3.0')";
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution Share Alike', 'This license lets others remix, tweak, and build upon a work even for commercial reasons, as long as they credit the author and license their new creations under the identical terms. This license is often compared to open source software licenses. All new works based on the original will carry the same license, so any derivatives will also allow commercial use.',
                'http://creativecommons.org/licenses/by-sa/3.0')";
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution No Derivatives' , 'This license allows for redistribution of a work, commercial and non-commercial, as long as it is passed along unchanged and in whole, with credit to the author.', 'http://creativecommons.org/licenses/by-nd/3.0')";
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution Non-Commercial', 'This license lets others remix, tweak, and build upon a work non-commercially, and although their new works must also acknowledge the author and be non-commercial, they don’t have to license their derivative works on the same terms.', 'http://creativecommons.org/licenses/by-nc/3.0')";
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution Non-Commercial Share Alike', 'This license lets others remix, tweak, and build upon a work non-commercially, as long as they credit the author and license their new creations under the identical terms. Others can download and redistribute the work just like the by-nc-nd license, but they can also translate, make remixes, and produce new stories based on the work. All new work based on the original will carry the same license, so any derivatives will also be non-commercial in nature.',
                'http://creativecommons.org/licenses/by-nc-sa/3.0')";
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`, `identifier`) values ('Creative Commons Attribution Non-Commercial No Derivatives', 'This license is the most restrictive Creative Commons license, allowing redistribution. This license is often called the free  advertising license because it allows others to download the works and share them with others as long as they mention and link back to the author, but they can’t change them in any way or use them commercially.',
                'http://creativecommons.org/licenses/by-nc-nd/3.0')";
	$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`) values ('Public domain', 'Works in the public domain are not subject to restrictions concerning their use or distribution.')";
		$queries[] = "INSERT into " . icms::$xoopsDB->prefix('sprockets_rights')
		. " (`title`, `description`) values ('Copyright, the Publisher',
                'The rights to this work are owned by a third party. Please contact the author/publisher for the terms of distribution, or permission to modify or distribute this work.')";
		

	foreach($queries as $query) {
		$result = icms::$xoopsDB->query($query);
	}
	return TRUE;
}