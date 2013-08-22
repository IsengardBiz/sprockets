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

// What modules/objects do you want to draw content from?
$modules_and_objects = array(
	0 => array('news', 'article'),
	1 => array('library' => 'publication')
	);

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

// Initialise
$sql = '';
$content = array();
$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');

// Retrieve last X objects from each module, using the taglink table to minimise queries.
// tag_id is used as a quick and dirty proxy for chronological sorting, but it will work so long
// as you don't go back and retrospectively add more tags to legacy content.
$content = $sprockets_taglink_handler->getTaggedItems($tag_id, FALSE, FALSE, FALSE, $items_to_display, 
		$sort = 'taglink_id', $order = 'DESC');

// Generate output

foreach ($content as $key => $value)
{
	echo '<div>';
	echo '<div class="imgleft"><a href="' . $value['itemUrl'] . '" title="' . $value['title'] 
			. '" alt="' . $value['title'] . '">' . $value['item'] . '</a>';
	echo '</div>';
	echo '<h3>' . $value['itemLink'] . '</h3>';
	echo '<p>' . $value['description'] . '</p>';
	echo '</div>';
}