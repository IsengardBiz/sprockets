<?php
/**
* Admin page to manage tags
*
* List, add, edit and delete tag objects
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

/**
 * Edit a Tag
 *
 * @param int $tag_id Tagid to be edited
*/
function edittag($tag_id = 0)
{
	global $sprockets_tag_handler, $icmsAdminTpl;

	$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
	$tagObj = $sprockets_tag_handler->get($tag_id);
		
	// Set label type as tag, hide category fields
	
	$tagObj->setVar('label_type', '0');
	$tagObj->hideFieldFromForm('label_type');
	$tagObj->hideFieldFromForm('mid');
	$tagObj->hideFieldFromForm('parent_id');
	$tagObj->showFieldOnForm('navigation_element');
		
	if (!$tagObj->isNew()){
				
		$sprocketsModule->displayAdminMenu(0, _AM_SPROCKETS_TAGS . " > " . _CO_ICMS_EDITING);
		$sform = $tagObj->getForm(_AM_SPROCKETS_TAG_EDIT, 'addtag');
		$sform->assign($icmsAdminTpl);

	} else {
		$sprocketsModule->displayAdminMenu(0, _AM_SPROCKETS_TAGS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $tagObj->getForm(_AM_SPROCKETS_TAG_CREATE, 'addtag');
		$sform->assign($icmsAdminTpl);

	}
	$icmsAdminTpl->display('db:sprockets_admin_tag.html');
}

include_once("admin_header.php");

$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))),
	'sprockets');

$clean_op = '';

/** Create a whitelist of valid values */
$valid_op = array ('mod','changedField','addtag', 'toggleStatus', 'toggleNavigationElement', 'del',
	'');

if (isset($_GET['op'])) $clean_op = icms_core_DataFilter::checkVar($_GET['op'], 'str');
if (isset($_POST['op'])) $clean_op = icms_core_Datafilter::checkVar($_POST['op'], 'str');

// Sanitise the tag_id
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0 ;

if (in_array($clean_op, $valid_op, TRUE)){
  switch ($clean_op) {
  	case "mod":	
  	case "changedField":

  		icms_cp_header();
  		edittag($clean_tag_id);
		
  		break;
	
  	case "addtag":

        $controller = new icms_ipf_Controller($sprockets_tag_handler);
  		$controller->storeFromDefaultForm(_AM_SPROCKETS_TAG_CREATED, _AM_SPROCKETS_TAG_MODIFIED);

  		break;
	
	case "toggleStatus":
		
			$status = $sprockets_tag_handler->toggleStatus($clean_tag_id, 'rss');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/tag.php';
			if ($status == 0) {
				redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_TAG_RSS_DISABLED);
			} else {
				redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_TAG_RSS_ENABLED);
			}
			
		break;
		
	case "toggleNavigationElement":
		$status = $ret = '';
		$status = $sprockets_tag_handler->toggleStatus($clean_tag_id, 'navigation_element');
		$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/tag.php';
		if ($status == 0) {
			redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_TAG_NAVIGATION_DISABLED);
		} else {
			redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_TAG_NAVIGATION_ENABLED);
		}
		
		break;

  	case "del":

        $controller = new icms_ipf_Controller($sprockets_tag_handler);
		$tagObj = $sprockets_tag_handler->get($clean_tag_id);
  		$controller->handleObjectDeletion($warning = '');

  		break;

  	default:

  		icms_cp_header();
  		$sprocketsModule->displayAdminMenu(0, _AM_SPROCKETS_TAGS);
		
		// if no op is set, but there is a (valid) tag_id, display a single object
		if ($clean_tag_id) {
			$tagObj = $sprockets_tag_handler->get($clean_tag_id);
			if ($tagObj->id()) {
				$tagObj->displaySingleObject();
			}
		}
		
		// Restrict content to tags only (no categories)
		$criteria = icms_buildCriteria(array('label_type' => '0'));

  		$objectTable = new icms_ipf_view_Table($sprockets_tag_handler, $criteria);
  		$objectTable->addColumn(new icms_ipf_view_Column('title'));
		$objectTable->addcolumn(new icms_ipf_view_Column('navigation_element'));
		$objectTable->addcolumn(new icms_ipf_view_Column('rss', 'left', FALSE, FALSE, FALSE,
				_AM_SPROCKETS_TAG_RSS_FEED));
		$objectTable->addFilter('navigation_element', 'navigation_element_filter');
		$objectTable->addfilter('rss', 'rss_filter');
		$objectTable->addQuickSearch('title');
  		$objectTable->addIntroButton('addtag', 'tag.php?op=mod', _AM_SPROCKETS_TAG_CREATE);

  		$icmsAdminTpl->assign('sprockets_tag_table', $objectTable->fetch());
  		$icmsAdminTpl->display('db:sprockets_admin_tag.html');
		
  		break;
  }
  icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */