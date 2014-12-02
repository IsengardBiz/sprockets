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
		$sql = '';
		$block = $content_objects = $content_array = array();
		$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		
		// Get a tag buffer to minimise queries
		$criteria = icms_buildCriteria(array('label_type' => 0));
		$tagList = $sprockets_tag_handler->getList($criteria);
		
		// options[0]: Number teasers to show
		// options[1]: Tag to filter
		// options[2]: Object filter - note that options are limited by the module preferences
		// options[3]: Position of teaser image (0 = no image, 1 = left, 2 = right)
		// options[4]: Size of teaser image (pixels)
		if (empty($options[2])) {
			$options[2] = sprockets_get_object_options();
			$options[2] = array_keys($options[2]);
			array_shift($options[2]); // Get rid of the null option
		} else {
			$options[2] = array(0 => $options[2]);
		}
			
		// Retrieve last X content items from each module (note: They are returned as arrays not
		// as objects in order to minimise resource use).
		// $tag_id = FALSE, $module_id = FALSE, $item_type = FALSE, $start = FALSE, $limit = FALSE, 
		// $sort = 'taglink_id', $order = 'DESC'
		$content_objects = $sprockets_taglink_handler->getTaggedItems($options[1], FALSE, 
				$options[2], FALSE, $options[0], $sort = 'date', 'DESC');
		
		// Generate output
		if ($options[5]) // Display in teaser mode
		{
			foreach ($content_objects as $key => $object)
			{
				$type = '';
				$content = $tags = $tagLinks = array();
				$content['title'] = $object->getVar('title');
				$content['description'] = $object->getVar('description');
				$content['date'] = date(icms_getConfig('date_format', 'sprockets'), $object->getVar('date', 'e'));
				$content['counter'] = $object->getVar('counter');
				$content['url'] = $object->getItemLink(TRUE);
				$short_url = $object->getVar('short_url');
				if (!empty($short_url)) {
					$content['url'] .= '&amp;title=' . $short_url;
				}

				// Images
				if ($options[3])
				{
					$type = $object->handler->_itemname;
					switch ($type)
					{						
						case "start":				
						case "project":
						case "partner":
							$image = $object->getVar('logo', 'e');
							if (!empty($image)) {
								$content['image'] = $object->getImageDir() . $image;
							}
							break;
						//case "programme":
						//	$image = $object->getVar('image', 'e');
						//	if (!empty($image) {
						//		$content['image'] = $object->getImageDir() . $image;
						//	}
						//	break;
						case "soundtrack":
							$image = $object->getVar('poster_image', 'e');
							if (!empty($image)) {
								$content['image'] = $object->getImageDir() . $image;
							}
							break;
						default: // 'image', used by News 1.17+, Library, Catalogue
							$image = $object->getVar('image', 'e');
							if (!empty($image)) {
								$content['image'] = $object->getImageDir() . $image;
							}
							break;
					}		
				}
				else
				{
					$content['image'] = FALSE;
				};

				// Tags. Query efficiency could be improved later, but with block caching its not too bad
				$tags = $sprockets_taglink_handler->getTagsForObject($object->id(), $object->handler, 0);
				if ($tags) {
					foreach ($tags as $tag) {
						$tagLinks[] = '<a href="' . $object->handler->_moduleUrl . $object->handler->_page . '?tag_id=' 
								. $tag . '">' . $tagList[$tag] . '</a>';
					}
					$content['tags'] = implode(", ", $tagLinks);
				}
				$content_array[] = $content;
			}
		}
		else // Display in simple list mode
		{
			foreach ($content_objects as $key => $object)
			{
				$content = array();
				$content['title'] = $object->getVar('title');
				$content['date'] = date(icms_getConfig('date_format', 'sprockets'),
						$object->getVar('date', 'e'));
				$content['url'] = $object->getItemLink(TRUE);
				$short_url = $object->getVar('short_url');
				if (!empty($short_url)) {
					$content['url'] .= '&amp;title=' . $short_url;
				}
				$content_array[] = $content;
			}
		}
		
		// Assign to template
		$block['content_array'] = $content_array;
		if ($options[3] == 1) {
			$block['sprockets_teaser_image_position'] = 'float:left;margin:0em 1em 1em 0em;';
		} elseif ($options[3] == 2) {
			$block['sprockets_teaser_image_position'] = 'float:right;margin:0em 0em 1em 1em;';
		}
		$block['sprockets_teaser_image_size'] = $options[4];
		$block['sprockets_teaser_display_mode'] = $options[5];
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
	$form .= '<tr><td>Objects to include: </td>';
	$form_select2 = new icms_form_elements_Select('', 'options[2]', $options[2], '1', FALSE);
	$objectList = sprockets_get_object_options();
	$form_select2->addOptionArray($objectList);
	$form .= '<td>' . $form_select2->render() . '</td></tr>';

	// Position of teaser images
	$form .= '<tr><td>Position of teaser images: </td>';
	$form_select3 = new icms_form_elements_Select('', 'options[3]', $options[3], '1', FALSE);
	$form_select3->addOptionArray(array(0 => _MB_SPROCKETS_CONTENT_RECENT_NONE, 
		1 => _MB_SPROCKETS_CONTENT_RECENT_LEFT, 2 => _MB_SPROCKETS_CONTENT_RECENT_RIGHT));
	$form .= '<td>' . $form_select3->render() . '</td></tr>';
	
	// Size of teaser image (automatically resized and cached by Smarty plugin)
	$form .= '<tr><td>Width of teaser image (pixels): </td>';
	$form .= '<td><input type="text" name="options[4]" value="' . $options[4] . '" /></td></tr>';
	
	// Display mode (teasers vs simple list)
	$form .= '<tr><td>Display mode: </td>';
	$form_select4 = new icms_form_elements_Select('', 'options[5]', $options[5], '1', FALSE);
	$form_select4->addOptionArray(array(0 => _MB_SPROCKETS_CONTENT_RECENT_LIST, 1 => _MB_SPROCKETS_CONTENT_RECENT_TEASERS));
	$form .= '<td>' . $form_select4->render() . '</td></tr>';
	$form .= '</table>';

	return $form;
}