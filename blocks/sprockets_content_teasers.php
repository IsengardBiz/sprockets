<?php
/**
 * New tagged content (recent items from across modules) block file
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
 * Prepares recent teasers block for display
 *
 * @param array $options
 * @return string
 */
function sprockets_content_teasers_show($options) {
	include_once(ICMS_ROOT_PATH . '/modules/sprockets/include/common.php');
	
	// Check Sprockets is installed and active (otherwise do nothing)
	if (icms_get_module_status("sprockets"))
	{		
		// Initialise
		$tag_id = $sql = '';
		$block = $content_objects = $content_array = array();
		$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		$script_name = getenv("SCRIPT_NAME");
		
		// Get a tag buffer to minimise queries
		$criteria = icms_buildCriteria(array('label_type' => 0));
		$tagList = $sprockets_tag_handler->getList($criteria);
		
		// Check if the block is set for dynamic tag filtering. If so, sanitise the tag_id 
		// parameter, check the tag actually exists and override the manual filter preference
		if ($options[6]) {
			 if (isset($_GET['tag_id'])) {
				$clean_tag_id = intval($_GET['tag_id']);
				$options[1] = array_key_exists($clean_tag_id, $tagList) ? $clean_tag_id : 0;
			 } else {
				 $options[1] = 0;
			 }
		}
		
		/*
		 * Determine the relative path to the web root, required for display of uploaded images.
		 * I don't like to call $GLOBALS['xoops'] but due to differences between Linux and 
		 * Windows servers finding the relative path to root is a lot more complicated than you'd
		 * like to think. 
		 */
		$script_name = $GLOBALS['xoops']->urls['phpself'];
		$base_name = basename($script_name);
		// Block is being displayed on home page without a start module (start = none)
		if (strpos($script_name, '/modules/') === FALSE) {
			switch ($base_name) {
				case "index.php":
					$document_root = str_replace('index.php', '', $script_name);
				break;
			case "index.html":
					$document_root = str_replace('index.html', '', $script_name);
				break;
			case "index.htm":
					$document_root = str_replace('index.htm', '', $script_name);
				break;
			}
		} else { // Block is being displayed on a module page
			$document_root = substr($script_name, 0, strpos($script_name, "/modules/"));
		}
		// options[0]: Number teasers to show
		// options[1]: Tag to filter
		// options[2]: Object filter - note that options are limited by the module preferences
		// options[3]: Position of teaser image (0 = no image, 1 = left, 2 = right)
		// options[4]: Size of teaser image (pixels)
		// options[5]: Display in teaser or compact (list) mode
		// options[6]: Allow dynamic tag filtering (overrides manual tag selection)
		if (empty($options[2])) {
			//$options[2] = sprockets_get_object_options();
			//$options[2] = array_keys($options[2]);
			//array_shift($options[2]); // Get rid of the null option
			$options[2] = icms_getConfig("client_objects", "sprockets");
		} else {
			$options[2] = array(0 => $options[2]);
		}

		// Retrieve last X content items from each module (note: They are returned as arrays not
		// as objects in order to minimise resource use).
		// // 0 items to show | 1 tags | 2 objects | 3 image position | 4 image size | 5 display mode
		// [0] => 5 [1] => 0 [2] => Array ( [0] => article ) [3] => 1 [4] => 150 [5] => 1
		// $tag_id = FALSE, $module_id = FALSE, $item_type = FALSE, $start = FALSE, $sort = FALSE, 
		// $sort = 'taglink_id', $order = 'DESC'
		/**
		 * Throws error when a permitted objects preference is set for an object type that doesn't
		 * exist on the system (eg. module not installed).
		 */
		$content_objects = $sprockets_taglink_handler->getTaggedItems($options[1], FALSE, 
				$options[2], 0, $options[0], $sort = 'DESC');
		
		// Remove the first item in $content_objects (count of results, which is not required)
		unset($content_objects[0]);
		
		// Prepare objects for display (includes assignment of type-specific subtemplates)
		if (!empty($content_objects)) {
			$content_objects = $sprockets_tag_handler->prepareClientItemsForDisplay($content_objects);
		}
		
		//////////////////////////////////////////////////////
		//////////////////// TAG ASSEMBLY ////////////////////
		//////////////////////////////////////////////////////
		
		// Display in teaser mode - need to assemble tags
		$count = count($content_objects);
		if ($options[5] && $count) {
			// Build a tag buffer for lightweight lookups
			$tag_buffer = $sprockets_tag_handler->getTagBuffer();

			// Build a taglink buffer (use combination of item and iid to identify distinct rows)
			$sql = $result = '';
			$sql = "SELECT DISTINCT `item`,`iid`, `tid` FROM " 
					. icms::$xoopsDB->escape($sprockets_taglink_handler->table)
					. " INNER JOIN " . icms::$xoopsDB->escape($sprockets_tag_handler->table)
					. " ON " . icms::$xoopsDB->escape($sprockets_taglink_handler->table) 
					. ".tid = " . icms::$xoopsDB->escape($sprockets_tag_handler->table)
					. ".tag_id WHERE ";
			foreach ($content_objects as $item) {
				$count--;
				$sql .= " (`item` = '" . icms::$xoopsDB->escape($item['item']) 
						. "' AND `iid` = '" . intval($item['iid'])
						. "' AND " . icms::$xoopsDB->escape($sprockets_tag_handler->table)
						. ".label_type = '0')";
				if ($count > 0) {
					$sql .= " OR ";
				}
			}

			// Retrieve the results and sort by i) item and ii) iid for easy retrieval
			$tag_info = array();
			$result = icms::$xoopsDB->query($sql);
			if (!$result) {
					echo 'Error in block';
					exit;
			} else {
				while ($row = icms::$xoopsDB->fetchArray($result)) {
					if (!isset($tag_info[$row['item']], $tag_info)) {
						$tag_info[$row['item']] = array();
					}
					if (!isset($tag_info[$row['item']][$row['iid']], $tag_info[$row['item']])) {
						$tag_info[$row['item']][$row['iid']] = array();
					}
					$tag_info[$row['item']][$row['iid']][] = '<a href="' . SPROCKETS_URL
							. 'tag.php?tag_id=' . $row['tid'] . '">' . $tag_buffer[$row['tid']]
							. '</a>';
				}
			}
			// Iterate through content items appending the sorted tags and correcting the image path
			foreach ($content_objects as &$obj) {
				if (isset($obj['iid'], $tag_info[$obj['item']])) {
					$obj['tags'] = implode(', ', $tag_info[$obj['item']][$obj['iid']]);
				}
				if (isset($obj['image'])) {
					if (!empty($obj['image'])) {
						$obj['image'] = $document_root . $obj['image'];
					}
				}
			}
		}
		
		//////////////////////////////////////////////////////////
		//////////////////// END TAG ASSEMBLY ////////////////////
		//////////////////////////////////////////////////////////
		
		// Assign to template - and yes there is some hardcoded CSS, that's because the stylesheet
		// info gets killed off in cached blocks due to some very ancient bug
		$block['content_array'] = $content_objects;
		if ($options[3] == 1) {
			$block['sprockets_teaser_image_position'] = 'float:left;margin:0em 1em 1em 0em;';
		} elseif ($options[3] == 2) {
			$block['sprockets_teaser_image_position'] = 'float:right;margin:0em 0em 1em 1em;';
		}
		$block['sprockets_teaser_image_size'] = $options[4];
		$block['sprockets_teaser_display_mode'] = $options[5]; // 0 = simple list, 1 = teasers
	}
	
	return $block;
}

/**
 * Edit and set options for the recent teasers block
 *
 * @param array $options
 * @return string
 */

function sprockets_content_teasers_edit($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');

	$form = $form_select = $criteria = '';
	$sorted = $unsorted = $content_array = $tagList = $objectList = array();
	$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
	icms_loadLanguageFile('sprockets', 'block');
	
	// Parameters: number teasers to show | tag | object | image position | image size
	
	// Number of teasers to show
	$form = '<table>';
	$form .= '<tr><td>Number of teasers to display: </td>';
	$form .= '<td>' . '<input type="text" name="options[0]" value="' . $options[0] . '" /></td></tr>';

	// Filter by tag
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_RECENT_TAG . '</td>';
	// Parameters icms_form_elements_Select: ($caption, $name, $value = null, $size = 1, $multiple = FALSE
	$form_select = new icms_form_elements_Select('', 'options[1]', $options[1], '1', FALSE);
	$criteria = icms_buildCriteria(array('label_type' => '0'));
	$tagList = $sprockets_tag_handler->getList($criteria);
	$tagList = array(0 => _MB_SPROCKETS_CONTENT_TEASERS_ALL) + $tagList;
	$form_select->addOptionArray($tagList);
	$form .= '<td>' . $form_select->render() . '</td></tr>';
	
	// Objects to include
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_OBJECTS_TO_INCLUDE . ': </td>';
	// icms_form_elements_Select options: $caption, $name, $value = null, $size = 1, $multiple = false
	$form_select2 = new icms_form_elements_Select('', 'options[2]', $options[2], '1', FALSE);
	$objectList = sprockets_get_object_options();
	$form_select2->addOptionArray($objectList);
	$form .= '<td>' . $form_select2->render() . '</td></tr>';

	// Position of teaser images
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_POSITION_TEASER_IMAGES . ': </td>';
	$form_select3 = new icms_form_elements_Select('', 'options[3]', $options[3], '1', FALSE);
	$form_select3->addOptionArray(array(0 => _MB_SPROCKETS_CONTENT_RECENT_NONE, 
		1 => _MB_SPROCKETS_CONTENT_RECENT_LEFT, 2 => _MB_SPROCKETS_CONTENT_RECENT_RIGHT));
	$form .= '<td>' . $form_select3->render() . '</td></tr>';
	
	// Size of teaser image (automatically resized and cached by Smarty plugin)
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_WIDTH_TEASERS_PIXELS . ': </td>';
	$form .= '<td><input type="text" name="options[4]" value="' . $options[4] . '" /></td></tr>';
	
	// Display mode (teasers vs simple list)
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_DISPLAY_MODE . ': </td>';
	$form_select4 = new icms_form_elements_Select('', 'options[5]', $options[5], '1', FALSE);
	$form_select4->addOptionArray(array(0 => _MB_SPROCKETS_CONTENT_RECENT_LIST, 1 => _MB_SPROCKETS_CONTENT_RECENT_TEASERS));
	$form .= '<td>' . $form_select4->render() . '</td></tr>';
	
	// Activate dynamic tag filtering (overrides the manual tag filter, using tag_id page request parameter instead)
	$form .= '<tr><td>' . _MB_SPROCKETS_CONTENT_DYNAMIC_FILTER_OVERRIDE . ': </td>';
	$form_select5 = new icms_form_elements_Select('', 'options[6]', $options[6], '1', FALSE);
	$form_select5->addOptionArray(array(0 => _MB_SPROCKETS_CONTENT_NO, 1 => _MB_SPROCKETS_CONTENT_YES));
	$form .= '<td>' . $form_select5->render() . '<td></tr>';
	$form .= '</table>';

	return $form;
}