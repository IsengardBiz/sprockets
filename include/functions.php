<?php
/**
* Common functions used by the module
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**
 * Get module admin link
 *
 * @todo to be move in icms core
 *
 * @param string $moduleName dirname of the moodule
 * @return string URL of the admin side of the module
 */

function sprockets_getModuleAdminLink($moduleName='sprockets') {
	$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
	if (!$moduleName && (isset ($sprocketsModule) && is_object($sprocketsModule))) {
		$moduleName = $sprocketsModule->getVar('dirname');
	}
	$ret = '';
	if ($moduleName) {
		$ret = "<a href='" . ICMS_URL . "/modules/$moduleName/admin/index.php'>"
			. _MD_SPROCKETS_ADMIN_PAGE . "</a>";
	}
	return $ret;
}

/**
 * @todo to be move in icms core
 */
function sprockets_getModuleName($withLink = TRUE, $forBreadCrumb = FALSE, $moduleName = FALSE) {

	if (!$moduleName) {

		$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
		$moduleName = $sprocketsModule->getVar('dirname');
	}
	
	if (!isset ($sprocketsModule)) {
		return '';
	}

	if (!$withLink) {
		return $sprocketsModule->getVar('name');
	} else {
		$ret = ICMS_URL . '/modules/' . $moduleName . '/';
		return '<a href="' . $ret . '">' . $sprocketsModule->getVar('name') . '</a>';
	}
}

/**
 * List of object/module options for the recent teasers block
 * 
 * @return array $options
 */
function sprockets_get_object_options() {
	$options = array(
		0 => _MB_SPROCKETS_CONTENT_TEASERS_ALL, // All modules
		'article' => _MB_SPROCKETS_CONTENT_TEASERS_ARTICLE, // News module
		//'event' => _MB_SPROCKETS_CONTENT_TEASERS_EVENT, // Events module
		'item' => _MB_SPROCKETS_CONTENT_TEASERS_ITEM, // Catalogue module
		'partner' => _MB_SPROCKETS_CONTENT_TEASERS_PARTNER, // Partner module
		'programme' => _MB_SPROCKETS_CONTENT_TEASERS_PROGRAMME, // Podcast module
		'project' => _MB_SPROCKETS_CONTENT_TEASERS_PROJECT, // Project module
		'publication' => _MB_SPROCKETS_CONTENT_TEASERS_PUBLICATION, // Library module
		'soundtrack' => _MB_SPROCKETS_CONTENT_TEASERS_SOUNDTRACK, // Podcast module
		//'start' => _MB_SPROCKETS_CONTENT_TEASERS_START, // CMS module
	);
	
	return $options;
}