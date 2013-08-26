<?php

/**
* Experimental custom PHP block to display a chronological stream of teasers from several compatible modules
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2013
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

///////////////////////////////////////////////////////
//////////////////// Configuration ////////////////////
///////////////////////////////////////////////////////

// How many items do you want to display in the block?
$items_to_display = '10';

// Specify a tag_id if you want to filter the results by tag (FALSE for no filtering)
$tag_id = FALSE;

// What types of content do you want to include in the block?
$object_types = array(
	0 => 'item', // Catalogue module
	1 => 'event', // Events module
	2 => 'publication', // Library module
	3 => 'article', // News module
	4 => 'partner', // Partner module
	5 => 'programme', // Programme module
	6 => 'soundtrack', // Podcast module
	7 => 'project' // Project module
	);

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

// Check Sprockets is installed and active (otherwise do nothing)
if (icms_get_module_status("sprockets"))
{
	// Initialise
	$sql = '';
	$content = array();
	$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
	
	// Make an array of item types to pass to getTaggedItems() (easier for handling modules with more
	// than one object type, such as podcast
	if (!empty($object_types)) {
			$object_types = "('" . implode("','", $object_types) . "')";
	} else {
		$object_types = FALSE;
	}

	// Retrieve last X objects from each module, using the taglink table to minimise queries.
	// tag_id is used as a quick and dirty proxy for chronological sorting, but it will work so long
	// as you don't go back and retrospectively add more tags to legacy content.
	$content = $sprockets_taglink_handler->getTaggedItems($tag_id, FALSE, $object_types, FALSE, $items_to_display, 
			$sort = 'taglink_id', $order = 'DESC');

	// Generate output
	foreach ($content as $key => $value)
	{
		$title = $value->getVar('title');
		$description = $value->getVar('description');
		$url = $value->getItemLink(TRUE);
		$short_url = $value->getVar('short_url');
		if (!empty($short_url)) {
			$url .= '&amp;title=' . $short_url;
		}
		if ($title && $description) {
			echo '<div>';
			echo '<h3><a href="' . $url . '">' . $title . '</a></h3>';
			if ($description) {
				echo '<p>' . $description . '</p>';
			}
			echo '</div>';
		}
	}
}