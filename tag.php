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

include_once 'header.php';

$xoopsOption['template_main'] = 'sprockets_tag.html';
include_once ICMS_ROOT_PATH . '/header.php';

// Sanitise the tag_id and start (pagination) parameters
$untagged_content = FALSE; // Flag indicating that UNTAGGED content should be returned
if (isset($_GET['tag_id'])) {
	if ($_GET['tag_id'] == 'untagged') {
		$untagged_content = TRUE;
	}
}
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']): 0 ;
$clean_start = isset($_GET['start']) ? intval($_GET['start']) : 0;

// Get tag/taglink handlers
$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');

// Initialise a containers for taglink and content objects
$content_count = 0;
$taglinkObjects = array();
$taglinkObjectsSortedByType = array();
$combinedContentObjects = array();

// Get relative path to document root for this ICMS install
$directory_name = basename(dirname(__FILE__));
$script_name = getenv("SCRIPT_NAME");
$document_root = str_replace('modules/' . $directory_name . '/tag.php', '', $script_name);

// Retrieve untagged content
if ($untagged_content) {
	$tagObj = $sprockets_tag_handler->create(); // Inflate an empty "Untagged" tag object
	$tagObj->setVar('title', _CO_SPROCKETS_TAG_UNTAGGED_CONTENT);
	$tagObj->setVar('description', _CO_SPROCKETS_TAG_UNTAGGED_CONTENT_DSC);	
	$icmsTpl->assign('sprockets_tag', $tagObj->toArray());
	$combinedContentObjects = $sprockets_taglink_handler->getUntaggedContent(
		FALSE, // Module ID
		icms_getConfig("client_objects", "sprockets"), // Permitted client objects set in prefs
		$clean_start, // Pagination control
		icms_getConfig("pagination_limit", "sprockets")); // Pagination limit
} else {
	// Retrieve tagged content (or all content, if tag_id == 0)
	if ($clean_tag_id) {
		$tagObj = $sprockets_tag_handler->get($clean_tag_id);
		if($tagObj && !$tagObj->isNew()) {
		$icmsTpl->assign('sprockets_tag', $tagObj->toArray());
		}
	}
	// $tag_id = FALSE, $module_id = FALSE, $item_type = FALSE, $start = FALSE, $limit = FALSE, $sort = 'DESC'
	$combinedContentObjects = $sprockets_taglink_handler->getTaggedItems(
		$clean_tag_id, // Tag ID
		FALSE, // Module ID
		icms_getConfig("client_objects", "sprockets"), // Permitted client objects set in prefs
		$clean_start, // Pagination control
		icms_getConfig("pagination_limit", "sprockets")); // Pagination limit
}

// Extract the results count needed to build the pagination control
if ($combinedContentObjects) {
	$content_count = array_shift($combinedContentObjects);
}
if ($content_count) {
	// Prepare objects for display (includes assignment of type-specific subtemplates)
	$combinedContentObjects = $sprockets_tag_handler->prepareClientItemsForDisplay($combinedContentObjects);
	
	// Adjust image file path for subdirectory installs of ICMS (resize Smarty plugin needs fix)
	foreach ($combinedContentObjects as &$object) {
		if (!empty($object['image'])) {
			$object['image'] = $document_root . $object['image'];
		}
	}
	
	/////////////////////////////////////////////////////////////////////////////
	////////// LOOKUP AND APPEND TAGS FOR THE RETURNED CONTENT OBJECTS //////////
	/////////////////////////////////////////////////////////////////////////////
	
	// Tag assembly is not necessary for untagged content!
	if ($untagged_content == FALSE) {

		// Build a tag buffer for lightweight lookups
		$tag_buffer = $sprockets_tag_handler->getTagBuffer();

		// Build a taglink buffer (use combination of item and iid to identify distinct rows)
		$sql = $result = $count = '';
		$count = count($combinedContentObjects);
		$sql = "SELECT DISTINCT `item`,`iid`, `tid` FROM " . $sprockets_taglink_handler->table 
				. " INNER JOIN " . $sprockets_tag_handler->table . " ON "
				. $sprockets_taglink_handler->table . ".tid = " 
				. $sprockets_tag_handler->table . ".tag_id"
				. " WHERE ";
		foreach ($combinedContentObjects as $item) {
			$count--;
			$sql .= " (`item` = '" . $item['item'] 
					. "' AND `iid` = '" . $item['iid']
					. "' AND " . $sprockets_tag_handler->table . ".label_type = '0')";
			if ($count > 0) {
				$sql .= " OR ";
			}
		}

		// Retrieve the results and sort by i) item and ii) iid for easy retrieval
		$tag_info = array();
		$result = icms::$xoopsDB->query($sql);
		if (!$result) {
				echo 'Error';
				exit;
		} else {
			while ($row = icms::$xoopsDB->fetchArray($result)) {
				if (!isset($tag_info[$row['item']], $tag_info)) {
					$tag_info[$row['item']] = array();
				}
				if (!isset($tag_info[$row['item']][$row['iid']], $tag_info[$row['item']])) {
					$tag_info[$row['item']][$row['iid']] = array();
				}
				$tag_info[$row['item']][$row['iid']][] = '<a href="' . $script_name
						. '?tag_id=' . $row['tid'] . '">' . $tag_buffer[$row['tid']] . '</a>';
			}
		}
		// Iterate through content items appending the sorted tags
		foreach ($combinedContentObjects as &$obj) {
			if (isset($obj['iid'], $tag_info[$obj['item']])) {
				$obj['tags'] = implode(', ', $tag_info[$obj['item']][$obj['iid']]);
			}
		}
	}
	//////////////////////////////////////////////////////////
	//////////////////// END TAG ASSEMBLY ////////////////////
	//////////////////////////////////////////////////////////

	// Prepare RSS feed (only for tags with RSS enabled)
	if (isset($tagObj) && !$tagObj->isNew()) {
		if ($tagObj->getVar('rss', 'e') == 1) {
			$icmsTpl->assign('sprockets_rss_link', 'rss.php?tag_id=' . $tagObj->getVar('tag_id', 'e'));
			$icmsTpl->assign('sprockets_rss_title', _CO_SPROCKETS_SUBSCRIBE_RSS_ON
					. $tagObj->getVar('title'));
			$icmsTpl->assign('sprockets_tag_name', $tagObj->getVar('title'));
		}
	}

	// Assign content to template, together with relevant module preferences
	$icmsTpl->assign('sprockets_tagged_content', $combinedContentObjects);
	$icmsTpl->assign('thumbnail_height', icms_getConfig('thumbnail_height', 'sprockets'));
	$icmsTpl->assign('thumbnail_height', icms_getConfig('thumbnail_height', 'sprockets'));
	$icmsTpl->assign('thumbnail_width', icms_getConfig('thumbnail_width', 'sprockets'));
	$icmsTpl->assign('thumbnail_height', icms_getConfig('thumbnail_height', 'sprockets'));
	$icmsTpl->assign('image_width', icms_getConfig('image_width', 'sprockets'));
	$icmsTpl->assign('image_height', icms_getConfig('image_height', 'sprockets'));

	// Pagination control
	if ($clean_tag_id) {
		$extra_arg = 'tag_id=' . $clean_tag_id;
	} else {
		$extra_arg = FALSE;
	}
	$pagenav = new icms_view_PageNav($content_count, 
		icms_getConfig('pagination_limit', 'sprockets'), $clean_start, 'start', $extra_arg);
	$icmsTpl->assign('sprockets_navbar', $pagenav->renderNav());
} else {
	// Nothing to display
	$icmsTpl->assign('sprockets_nothing_to_display', _CO_SPROCKETS_CONTENT_NOTHING_TO_DISPLAY);
}

// Generate meta information for this page
if (isset($tagObj) && !$tagObj->isNew()) {
	$icms_metagen = new icms_ipf_Metagen($tagObj->getVar('title'),
		$tagObj->getVar('meta_keywords','n'), $tagObj->getVar('meta_description', 'n'));
	$icms_metagen->createMetaTags();
} else {
	// Do something generic for 'all tags'
}

$icmsTpl->assign('sprockets_module_home', sprockets_getModuleName(TRUE, TRUE));
$icmsTpl->assign('sprockets_display_breadcrumb', $sprocketsConfig['display_breadcrumb']);

include_once 'footer.php';