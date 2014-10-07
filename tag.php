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

// Sanitise the tag_id and start parameters
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']): 0 ;
$clean_start = isset($_GET['start']) ? intval($_GET['start']) : 0;

// Get tag/taglink handlers
$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');

// Initialise a containers for taglink and content objects
$taglinkObjects = array();
$taglinkObjectsSortedByType = array();
$combinedContentObjects = array();

// Retrieve the requested tag
$tagObj = $sprockets_tag_handler->get($clean_tag_id);
if($tagObj && !$tagObj->isNew()) {
	$icmsTpl->assign('sprockets_tag', $tagObj->toArray());
	
	// Generate meta information for this page
	$icms_metagen = new icms_ipf_Metagen($tagObj->getVar('title'),
		$tagObj->getVar('meta_keywords','n'), $tagObj->getVar('meta_description', 'n'));
	$icms_metagen->createMetaTags();
	
	// Retrieve cross-module content
	// $tag_id = FALSE, $module_id = FALSE, $item_type = FALSE, $start = FALSE, $limit = FALSE, 
	// $sort = 'taglink_id', $order = 'DESC'
	// Pass in $item_type as an array, getTaggedItems() can handle it already
	$combinedContentObjects = $sprockets_taglink_handler->getTaggedItems(
			$clean_tag_id, // Tag ID
			FALSE, // Module ID
			icms_getConfig("client_objects", "sprockets"), // Permitted client objects set in prefs
			$clean_start, // Pagination control
			icms_getConfig("pagination_limit", "sprockets")); // Pagination limit
			// $sort = 'taglink_id' default
			// $order = 'DESC')); default
	/**
	// Get relevant object handlers for compatible modules. Need to link this to preference system
	$clientObjectHandlers = $sprockets_tag_handler->getClientObjectHandlers();
	
	// Check if there is any content associated with this tag, and of allowed item types
	$criteria = icms_buildCriteria(array('tid' => $clean_tag_id));
	$allowed_client_objects = icms_getConfig('client_objects', 'sprockets');
	if ($allowed_client_objects) {
		foreach ($allowed_client_objects as $key => $object) {
			if (array_key_exists($object, $clientObjectHandlers)) {
				$module = $clientObjectHandlers[$object]->_moduleName;
				if (!icms_get_module_status($module)) {
					unset($allowed_client_objects[$key]);
				}
			}
		}
		$allowed_client_objects = '("' . implode('","', $allowed_client_objects) . '")';
		$criteria->add(new icms_db_criteria_Item('item', $allowed_client_objects, 'IN'));
	}**/
	/**$taglinkObjects = $sprockets_taglink_handler->getObjects($criteria);	
	if(!empty($taglinkObjects)) {
		// Sort taglinks by object name, this will allow the correct handler to be pulled from 
		// $clientObjectHandlers
		foreach ($taglinkObjects as $taglink) {
			$item = $taglink->getVar('item');
			if (!array_key_exists($item, $taglinkObjectsSortedByType)) {
				$taglinkObjectsSortedByType[$item] = array();
			}			
			$taglinkObjectsSortedByType[$item][] = $taglink->getVar('iid');
		}
		// Retrieve the relevant objects from each module and append to the combined content array.
		// If an object's handler is not available this implies that it's parent module has been
		// deactivated and it will not be retrieved. Objects marked offline will not be retrieved.
		$content_count = 0;
		foreach ($taglinkObjectsSortedByType as $type => $value) {
			$itemIDs = '(' . implode(',', $value) . ')';
			if (array_key_exists($type, $clientObjectHandlers)) {
				$criteria = new CriteriaCompo();
				$criteria->add(new icms_db_criteria_Item($type . '_id', $itemIDs, 'IN'));
				$criteria->add(new icms_db_criteria_Item('online_status', 1));
				$criteria->setSort('date');
				$criteria->setOrder('DESC');
				// Count the number of results, this is required by the pagination control
				$content_count += $clientObjectHandlers[$type]->getCount($criteria);
				$criteria->setStart($clean_start);
				$criteria->setLimit(icms_getConfig('pagination_limit', 'sprockets'));
				$content = $clientObjectHandlers[$type]->getObjects($criteria);
				// Load relevant language file
				icms_loadLanguageFile($clientObjectHandlers[$type]->_moduleName, 'common');
				// Append results to the combined content array
				$combinedContentObjects = array_merge($combinedContentObjects, $content);
			}
		}
		**/
		
		// NOTE: The object retrieval mechanism is not efficient. Would be better if could
		// compare object timestamps across modules so only retrieve what is actually needed.
		// Do a big fat manual SQL join (will be ugly though).
		/**
		 * Idea - do a preliminary lookup using item / iid of taglinks joined to the relevant table
		 * date fields. Use this information to sort and truncate the result set BEFORE looking up
		 * the full objects. This will be considerably more efficient. It will also avoid problems
		 * in using human-readable timestamps to sort the results
		 */
		
		// Modules without a date (timestamp) field are:
		// Podcast (on programme objects the 'date' field is an arbitrary string) 
		
		function compare($a, $b) {
			if ($a->getVar('date', 'e') == $b->getVar('date', 'e')) {
				return 0;
			}
			return ($a->getVar('date', 'e') < $b->getVar('date')) ? -1 : 1;
		}	
		usort($combinedContentObjects, "compare");

		// Truncate the combined content array and free up memory (need to create a preference to do
		// this but for now let's use 10 as a hard number)
		$truncatedContent = array_slice($combinedContentObjects, 0, 10);
		unset($combinedContentObjects);
		
		
		// Prepare objects for display (includes assignment of type-specific subtemplates)
		$truncatedContent = $sprockets_tag_handler->prepareClientObjectsForDisplay($truncatedContent);
		
		// Assign content to template, together with relevant module preferences
		$icmsTpl->assign('sprockets_tagged_content', $truncatedContent);
		$icmsTpl->assign('thumbnail_width', icms_getModuleConfig('thumbnail_width', 'sprockets'));
		$icmsTpl->assign('thumbnail_height', icms_getModuleConfig('thumbnail_height', 'sprockets'));
		$icmsTpl->assign('image_width', icms_getModuleConfig('image_width', 'sprockets'));
		$icmsTpl->assign('image_height', icms_getModuleConfig('image_height', 'sprockets'));

		// Pagination control
		$pagenav = new icms_view_PageNav($content_count, 
			icms_getConfig('pagination_limit', 'sprockets'), $clean_start, 'start', FALSE);
		$icmsTpl->assign('sprockets_navbar', $pagenav->renderNav());
		
	/**} else {
		// Nothing to display
		$icmsTpl->assign('sprockets_nothing_to_display', _CO_SPROCKETS_CONTENT_NOTHING_TO_DISPLAY);
	}	**/
} else {
	// Nothing to display
	$icmsTpl->assign('sprockets_nothing_to_display', _CO_SPROCKETS_CONTENT_NOTHING_TO_DISPLAY);
}

$icmsTpl->assign('sprockets_module_home', sprockets_getModuleName(TRUE, TRUE));
$icmsTpl->assign('sprockets_display_breadcrumb', $sprocketsConfig['display_breadcrumb']);

include_once 'footer.php';