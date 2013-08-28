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
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		
		// options[0]: Number teasers to show
		// options[1]: Tag to filter
		// options[2] Object filter
		// options[3] Date format
		if (empty($options[2])) {
			$options[2] = sprockets_get_object_options();
			$options[2] = array_keys($options[2]);
			array_shift($options[2]);
			$options[2] = "('" . implode("', '", $options[2]) . "')";
		} else {
			$options[2] = "('" . $options[2] . "')";
		}
			
		// Retrieve last X objects from each module, using the taglink table to minimise queries.
		// tid is used as a quick and dirty proxy for chronological sorting, but it will work so long
		// as you don't go back and retrospectively add more tags to legacy content.
		// $tag_id = FALSE, $module_id = FALSE, $item_type = FALSE, $start = FALSE, $limit = FALSE, 
		// $sort = 'taglink_id', $order = 'DESC'
		$content_objects = $sprockets_taglink_handler->getTaggedItems($options[1], FALSE, $options[2], FALSE, 
				$options[0], $sort = 'taglink_id', 'DESC');
		
		// Generate output
		foreach ($content_objects as $key => $object)
		{
			$content = array();
			$content['title'] = $object->getVar('title');
			$content['description'] = $object->getVar('description');
			$content['date'] = date($options[3], $object->getVar('date', 'e'));
			$content['counter'] = $object->getVar('counter');
			$content['url'] = $object->getItemLink(TRUE);
			$short_url = $object->getVar('short_url');
			if (!empty($short_url)) {
				$content['url'] .= '&amp;title=' . $short_url;
			}
			$content_array[] = $content; 
		}
		
		// Assign to template
		$block['content_array'] = $content_array;
	}
	
	return $block;
}

/**
 * Edit and set options for the recent teasers block
 *
 * @param arrau $options
 * @return string
 */

function sprockets_content_teasers_edit($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');

	$form = $form_select = $criteria = '';
	$sorted = $unsorted = $content_array = $tagList = $objectList = array();
	$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
	icms_loadLanguageFile('sprockets', 'block');
	
	// Parameters: number teasers to show | tag | object | date format
	
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

	// Date format, as per PHP's date() method
	$form .= '<tr><td>Date format (as per PHP date() function): </td>';	
	$form .= '<td>' . '<input type="text" name="options[3]" value="' . $options[3]
		. '" /></td></tr>';
	$form .= '</table>';

	return $form;
}