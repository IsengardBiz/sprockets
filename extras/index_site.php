<?php
/**
* Tag page
* 
* Retrieves related (tagged) content from across multiple modules
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

include_once "./mainfile.php";
include_once(ICMS_ROOT_PATH . '/modules/sprockets/include/common.php');

$xoopsOption['template_main'] = 'sprockets_tag.html';
include_once ICMS_ROOT_PATH . '/header.php';

// Sanitise the tag_id and start (pagination) parameters
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']): 0 ;
$clean_start = isset($_GET['start']) ? intval($_GET['start']) : 0;

// Get tag/taglink handlers
$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');

// Initialise a containers for taglink and content objects
$content_count = 0;
$taglinkObjects = array();
$taglinkObjectsSortedByType = array();

// Retrieve the requested tag object
$tagObj = $sprockets_tag_handler->get($clean_tag_id);

// Retrieve cross-module content
$combinedContentObjects = $sprockets_taglink_handler->getTaggedItems(
	$clean_tag_id, // Tag ID
	FALSE, // Module ID
	icms_getConfig("client_objects", "sprockets"), // Permitted client objects set in prefs
	$clean_start, // Pagination control
	icms_getConfig("pagination_limit", "sprockets")); // Pagination limit

// Extract the first element of the array, which contains pagination information
$content_count = array_shift($combinedContentObjects);

// Prepare objects for display (includes assignment of type-specific subtemplates)
$combinedContentObjects = $sprockets_tag_handler->prepareClientObjectsForDisplay($combinedContentObjects);

// Assign content to template, together with relevant module preferences
$icmsTpl->assign('sprockets_tagged_content', $combinedContentObjects);
$icmsTpl->assign('thumbnail_width', icms_getModuleConfig('thumbnail_width', 'sprockets'));
$icmsTpl->assign('thumbnail_height', icms_getModuleConfig('thumbnail_height', 'sprockets'));
$icmsTpl->assign('image_width', icms_getModuleConfig('image_width', 'sprockets'));
$icmsTpl->assign('image_height', icms_getModuleConfig('image_height', 'sprockets'));

// Pagination control
if ($clean_tag_id) {
	$extra_arg = 'tag_id=' . $clean_tag_id;
} else {
	$extra_arg = FALSE;
}

$pagenav = new icms_view_PageNav($content_count, 
	icms_getConfig('pagination_limit', 'sprockets'), $clean_start, 'start', $extra_arg);
$icmsTpl->assign('sprockets_navbar', $pagenav->renderNav());
$icmsTpl->assign('sprockets_module_home', sprockets_getModuleName(TRUE, TRUE));
$icmsTpl->assign('sprockets_display_breadcrumb', $sprocketsConfig['display_breadcrumb']);

// Generate meta for this page. Note: Can't use the icms_ipf_Metagen class here due to a bug. 
// Within the class, the icms::$module variable tests positive as an object but throws a non-object
// error on line 150 if any of its properties are accessed. Work around: Set page meta information
// manually, but it doesn't seem to work?
if($tagObj && !$tagObj->isNew()) {
	$icmsTpl->assign('sprockets_tag', $tagObj->toArray());
	$icmsTpl->assign('icms_meta_title', $tagObj->getVar('title'));
	$icmsTpl->assign('icms_meta_keywords', $tagObj->getVar('meta_keywords', 'n'));
	$icmsTpl->assign('icms_meta_description', $tagObj->getVar('meta_description', 'n'));	
} else { // Display ALL tagged content
	$icmsTpl->assign('sprockets_tag', array('title' => _CO_SPROCKETS_ALL_TAGGED_CONTENT));
	$icmsTpl->assign('icms_meta_title', _CO_SPROCKETS_ALL_TAGGED_CONTENT);
	$icmsTpl->assign('icms_meta_keywords', _CO_SPROCKETS_ALL_TAGGED_CONTENT_KEYWORDS);
	$icmsTpl->assign('icms_meta_description', _CO_SPROCKETS_ALL_TAGGED_CONTENT_DSC);
}

include_once ICMS_ROOT_PATH . '/modules/sprockets/footer.php';