<?php
/**
 * New content (recent items from across modules) block file
 *
 * This file holds the functions needed to edit/view the 'recent content' block
 *
 * @copyright	http://smartfactory.ca The SmartFactory
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		marcan aka Marc-Andre Lanciault <marcan@smartfactory.ca>
 * @author		Madfish <simon@isengard.biz>
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**
 * Prepares recent content block for display
 *
 * @param array $options
 * @return string
 */
function sprockets_content_recent_show($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');
	
	$sql = $criteria = $spotlight_id = '';
	$module_options = $taglink_object_array = $content_ids = $content_object_array = 
		$combined_content_array = $rows = array();
	
	$sprockets_archive_handler = icms_getModuleHandler('archive',
		basename(dirname(dirname(__FILE__))), 'sprockets');
	$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))),
		'sprockets');
	$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
		basename(dirname(dirname(__FILE__))), 'sprockets');
	
	$criteria = new icms_db_criteria_Compo();
	if ($options[1]) {
		$criteria->add(new icms_db_criteria_Item('tid', $options[1]));
		
	}
	$criteria->setSort('taglink_id');
	$criteria->setOrder('DESC');
	$criteria->setLimit($options[0]);
	
	$taglink_object_array = $sprockets_taglink_handler->getObjects($criteria);
	unset($criteria);
	
	// get a list of compatible modules
	$installed_modules = $sprockets_archive_handler->getModuleNames();
	
	/** 
	 * Its simpler just to do a separate query for each module, perhaps a more efficient way
	 * can be found later on. Each compatible module needs to have its primary content object added
	 * to this array, which is used to retrieve appropriate handlers.
	 */
	
	$item_array = array(
		'news' => 'article',
		'podcast' => 'soundtrack',
		'library' => 'publication',
		'catalogue' => 'item',
		'projects' => 'project',
		'partners' => 'partner'
		);

	foreach ($installed_modules as $module_key => $module_name) {
		
		$id_string = '';
		$content_ids[$module_name] = array();
		$id_field = $item_array[$module_name] . '_id';
		$content_handler = icms_getModuleHandler($item_array[$module_name], $module_name,
				$module_name);

		/**
		 * Need to sort the taglinks into their respective modules and objects, in order to set up 
		 * queries. Taglinks know their module ID (mid), tag ID (tid), item ID (iid) and type 
		 * (eg. 'article'). The results have been filtered by tag ID already, but need to be sorted by
		 * module ID. If more complex modules are introduced later on, it may become necessary to sort 
		 * by object type as well. However, at the moment each module only has one content object so 
		 * this issue can be ignored.
		 */

		foreach ($taglink_object_array as $key => $taglink) {
			if ($taglink->id() == $options[5]) {
				$spotlight_id = $taglink->getVar('taglink_id');
			}
			if ($taglink->getVar('mid') == $module_key) {
				$content_ids[$module_name][$taglink->id()] = $taglink->getVar('iid');
			}
		}
		
		if (count($content_ids[$module_name]) >0) {
			
			// construct a string of IDs to use as a $criteria
			$id_string = "(" . implode(",", $content_ids[$module_name]) . ")";

			// retrieve the relevant content objects		
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item($id_field, $id_string, 'IN')); // this applies the tag filter
			$criteria->add(new icms_db_criteria_Item('online_status', 1)); // only show content marked online
			$criteria->setSort('date');
			$criteria->setOrder('DESC');
			$criteria->setLimit($options[0]); // get more than we need, in case some is marked offline

			$content_object_array[$module_name] = $content_handler->getObjects($criteria, TRUE, TRUE);

			// Append the content objects to the combined content array
			$combined_content_array = array_merge($combined_content_array, $content_object_array[$module_name]);
		} else {
			unset($content_ids[$module_name]);
		}
	}

	$sorted = $unsorted = array();
	
	// sort the combined module content by date
	foreach ($combined_content_array as $key => $contentObj) {
		$unsorted[$key] = $contentObj->getVar('date', 'e');
	}
	arsort($unsorted);
	foreach ($unsorted as $key => $value) {
		$sorted[$key] = $combined_content_array[$key];
	}
	$combined_content_array = $sorted;
	
	// truncate the array to the number of content items specified in block preferences ($options[0])
	$contents_length = count($combined_content_array);
	if ($contents_length > $options[0]) {
		array_splice($combined_content_array, $options[0], ($contents_length - $options[0]));
	}
	
	$block['sprockets_recent_content'] = $combined_content_array;

	// check if spotlight mode is active, and if spotlight article has already been retrieved
	if ($options[4] == TRUE && (count($block['sprockets_recent_content']) > 0)) {	
		if (array_key_exists($spotlight_id, $block['sprockets_recent_content'])) {
			$spotlightObj = $block['sprockets_recent_content'][$spotlight_id];
			unset($block['sprockets_recent_content'][$spotlight_id]);
		} elseif ($options[5] == 0) {
			$spotlightObj = array_shift($block['sprockets_recent_content']);
		} else {
			
			// the stored ID is actually the taglink ID, need to recover the taglink to get spotlight
			$taglinkObj = $sprockets_taglink_handler->get($options[5]);	
			$spotlightObj = $taglinkObj->getLinkedObject();
			
			$trim = array_pop($block['sprockets_recent_content']);
		}
		// prepare spotlight content for display
		$block['sprockets_recent_content_spotlight_title'] = $spotlightObj->getItemLink();
		$block['sprockets_recent_content_spotlight_description'] = $spotlightObj->getVar('description');
		$block['sprockets_recent_content_spotlight_link'] = $spotlightObj->getItemLink();
		$block['sprockets_recent_content_title'] = _MB_SPROCKETS_RECENT_CONTENT_TITLE;
	}

	// prepare for display
	foreach ($block['sprockets_recent_content'] as &$content) {
		$title = $content->getVar('title', 'e');

		// trim the title if its length exceeds the block preferences
		if (strlen($title) > $options[3]) {
			$content->setVar('title', substr($title, 0, ($options[3] - 3)) . '...');
		}
		
		// formats timestamp according to the block options
		$date = $content->getVar('date', 'e');
		$date = date($options[2], $date);
		
		$content = $content->toArray();
		$content['date'] = $date;
	}

	return $block;
}

/**
 * Edit and set options for the recent content block
 *
 * @param arrau $options
 * @return string
 */

function sprockets_content_recent_edit($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');

	$sorted = $unsorted = $content_array = array();
	
	// select number of recent articles to display in the block
	$form = '<table><tr>';
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_RECENT_LIMIT . '</td>';
	$form .= '<td>' . '<input type="text" name="options[]" value="' . $options[0] . '" /></td>';
	$form .= '</tr>';

	// optionally display results from a single tag - only if sprockets module is installed
	$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
	if ($sprocketsModule) {
		$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
		$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_RECENT_TAG . '</td>';
		// Parameters XoopsFormSelect: ($caption, $name, $value = null, $size = 1, $multiple = FALSE)
		$form_select = new XoopsFormSelect('', 'options[]', $options[1], '1', FALSE);
		$tagList = $sprockets_tag_handler->getList();
		$tagList = array(0 => 'All') + $tagList;
		$form_select->addOptionArray($tagList);
		$form .= '<td>' . $form_select->render() . '</td></tr>';
	}

	// customise date format string as per PHP's date() method
	$form .= '<td>' . _MB_SPROCKETS_CONTENT_DATE_STRING . '</td>';	
	$form .= '<td>' . '<input type="text" name="options[2]" value="' . $options[2]
		. '" /></td></tr>';
	
	// limit title length
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_TITLE_LENGTH . '</td>';
	$form .= '<td>' . '<input type="text" name="options[3]" value="' . $options[3]
		. '" /></td></tr>';
	
	// activate spotlight feature
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_ACTIVATE_SPOTLIGHT . '</td>';
	$form .= '<td><input type="radio" name="options[4]" value="1"';
		if ($options[4] == 1) {
			$form .= ' checked="checked"';
		}
		$form .= '/>' . _MB_SPROCKETS_CONTENT_YES;
		$form .= '<input type="radio" name="options[4]" value="0"';
		if ($options[4] == 0) {
			$form .= 'checked="checked"';
		}
		$form .= '/>' . _MB_SPROCKETS_CONTENT_NO . '</td></tr>';
		
	// build select box for choosing content to spotlight
	$criteria = new icms_db_criteria_Compo();
	$criteria->setStart(0);
	$criteria->setLimit(50);
	$criteria->setSort('taglink_id');
	$criteria->setOrder('DESC');
	
	// retrieve the taglinks
	$sprockets_taglink_handler = icms_getModuleHandler('taglink',
			basename(dirname(dirname(__FILE__))), 'sprockets');
	
	$taglink_object_array = $sprockets_taglink_handler->getObjects($criteria);

	// sort the taglinks by module and get appropriate handlers
	$sprockets_archive_handler = icms_getModuleHandler('archive',
			basename(dirname(dirname(__FILE__))), 'sprockets');
	$installed_modules = $sprockets_archive_handler->getModuleNames();
	
	$item_array = array('news' => 'article', 'podcast' => 'soundtrack', 'library' => 'publication',
		'catalogue' => 'item', 'projects' => 'project', 'partners' => 'partner');

	/** 
	 * Its simpler just to do a separate query for each module, perhaps a more efficient way
	 * can be found later on. Each compatible module needs to have its primary content object added
	 * to this array, which is used to retrieve appropriate handlers.
	 */

	$combined_content_array = array();
	
	foreach ($installed_modules as $module_key => $module_name) {

		$id_string = '';
		$content_ids[$module_name] = array();
		$id_field = $item_array[$module_name] . '_id';
		$content_handler = icms_getModuleHandler($item_array[$module_name], $module_name,
				$module_name);

		/**
		 * Need to sort the taglinks into their respective modules and objects, in order to set up 
		 * queries. Taglinks know their module ID (mid), tag ID (tid), item ID (iid) and type 
		 * (eg. 'article'). The results have been filtered by tag ID already, but need to be sorted by
		 * module ID. If more complex modules are introduced later on, it may become necessary to sort 
		 * by object type as well. However, at the moment each module only has one content object so 
		 * this issue can be ignored.
		 */

		foreach ($taglink_object_array as $taglink) {
			if ($taglink->getVar('mid') == $module_key) {
				$content_ids[$module_name][$taglink->id()] = $taglink->getItemId();
			}
		}

		if (count($content_ids[$module_name]) >0) {

			// construct a string of IDs to use as a $criteria
			$id_string = "(" . implode(",", $content_ids[$module_name]) . ")";

			// retrieve the relevant content objects			
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item($id_field, $id_string, 'IN')); // this applies the tag filter
			$criteria->add(new icms_db_criteria_Item('online_status', 1)); // only show content marked online
			$criteria->setSort('date');
			$criteria->setOrder('DESC');
			$criteria->setLimit(20); // get more than we need, in case some is marked offline

			$content_object_array[$module_name] = $content_handler->getObjects($criteria, TRUE, TRUE);

			// change the keys of these objects to match their taglink
			$taglink_ids = array_flip($content_ids[$module_name]);

			foreach ($content_object_array[$module_name] as $key => $contentObj) {
				$taglinked_content[$taglink_ids[$contentObj->id()]] = $contentObj;
			}
			$content_object_array[$module_name] = $taglinked_content;
			unset($taglinked_content);

			// combine the objects from different modules - NEED TO PRESERVE THE KEYS
			$combined_content_array = $combined_content_array + $content_object_array[$module_name];
		} else {
			unset($content_ids[$module_name]);
		}
	}

	if (count($combined_content_array) >0) {

		// sort the combined module content by date
		foreach ($combined_content_array as $key => $contentObj) {
			$unsorted[$key] = $contentObj->getVar('date', 'e');
		}
		asort($unsorted);
		foreach ($unsorted as $key => $value) {
			$sorted[$key] = $combined_content_array[$key];
		}

		// truncate the array
		$sorted_length = count($sorted);
		if ($sorted_length > 20) {
			array_splice($sorted, 20, ($sorted_length - 20));
		}

		// prepare for display - but use taglink_id to help set up the handlers on retrieval
		foreach ($sorted as $key => $contentObj) {
			$content_array[$key] = $contentObj->getVar('title', 'e');
		}
	}
	
	// retrieve the content items and concatenate them into a single array
	$content_array = array(0 => '-- Most recent article --') + $content_array;
	
	// build a select box of article titles
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_SPOTLIGHTED_ARTICLE . '</td>';
	// Parameters XoopsFormSelect: ($caption, $name, $value = null, $size = 1, $multiple = FALSE)
	$form_spotlight = new XoopsFormSelect('', 'options[5]', $options[5], '1', FALSE);
	$form_spotlight->addOptionArray($content_array);
	$form .= '<td>' . $form_spotlight->render() . '</td></tr>';
	$form .= '</table>';

	return $form;
}