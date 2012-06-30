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
	
	if (isset($_POST['op']) && $_POST['op'] == 'changedField' && in_array($_POST['changedField'],
		array('parent_id'))) {
		
		// Disallow setting own ID as parent
		if ($_POST['parent_id'] == $tagObj->id()) {
			$_POST['parent_id'] = $tagObj->getVar('parent_id');
		}
		
		/**
		 * Parent category: Check if the category has been relocated underneath one of its own 
		 * children. If this happens, that child category (only) needs to have its parent_id 
		 * updated to the next highest level, otherwise the category hierarchy will be destroyed. 
		 * This effectively means the child is promoted to be the parent node of this branch.
		 * This issue must be dealt with here, if you wait to build a category tree in beforeSave()
		 * the hierarchy is already mangled.
		 */
		
		include_once ICMS_ROOT_PATH . '/modules/sprockets/include/angry_tree.php';

		$tag_id = $categoryTree = '';
		$categoryObjArray = $allChildCategories = $newParentCategory = array();
		$criteria = new icms_db_criteria_Compo();

		// Exclude labels that are only tags (not categories)
		$criteria->add(new icms_db_criteria_Item('label_type', 0, '!='));
		$categoryObjArray = $sprockets_tag_handler->getObjects($criteria);

		// Get a category tree
		$categoryTree = new IcmsPersistableTree($categoryObjArray, 'tag_id', 'parent_id');

		// Check if the category has any children
		$allChildCategories = $categoryTree->getAllChild($tagObj->id());
		
		// Check if the category has been reassigned as a child of its own subcategory
		if (count($allChildCategories) > 0) {
			
			// Get the next highest parent_id
			$parent_id = $tagObj->getVar('parent_id');
			
			// Check if the proposed parent_id matches a $tagObj child
			foreach ($allChildCategories as $key => $child) {
				if ($_POST['parent_id'] == $key) {
					
					// Update that child's parent ID to next highest parent
					$child->setVar('parent_id', $parent_id);
					$sprockets_tag_handler->insert($child);
				}
			}
		}
		
		$controller = new IcmsPersistableController($sprockets_tag_handler);
		$controller->postDataToObject($tagObj);
	}
	
	// Set label type as CATEGORY, hide label_type field to stop user changing it
	$tagObj->setVar('label_type', '1');
	$tagObj->hideFieldFromForm('label_type');
	$tagObj->hideFieldFromForm('mid');
	$tagObj->showFieldOnForm('navigation_element');
		
	if (!$tagObj->isNew()){
				
		$sprocketsModule->displayAdminMenu(1, _AM_SPROCKETS_TAGS . " > " . _CO_ICMS_EDITING);
		$sform = $tagObj->getForm(_AM_SPROCKETS_TAG_EDIT, 'addtag');
		$sform->assign($icmsAdminTpl);

	} else {
		$sprocketsModule->displayAdminMenu(1, _AM_SPROCKETS_TAGS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $tagObj->getForm(_AM_SPROCKETS_TAG_CREATE, 'addtag');
		$sform->assign($icmsAdminTpl);

	}
	$icmsAdminTpl->display('db:sprockets_admin_tag.html');
}

include_once("admin_header.php");

if (icms_get_module_status("sprockets"))
{
	$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))),
		'sprockets');

	$clean_op = '';

	/** Create a whitelist of valid values */
	$valid_op = array ('mod','changedField','addtag', 'toggleStatus', 'toggleNavigationElement', 'del',
		'');

	if (isset($_GET['op'])) $clean_op = htmlentities($_GET['op']);
	if (isset($_POST['op'])) $clean_op = htmlentities($_POST['op']);

	// Sanitise the tag_id
	$clean_tag_id = isset($_GET['tag_id']) ? (int) $_GET['tag_id'] : 0 ;

	if (in_array($clean_op,$valid_op,TRUE)){
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
				$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/category.php';
				if ($status == 0) {
					redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_TAG_RSS_DISABLED);
				} else {
					redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_TAG_RSS_ENABLED);
				}

			break;

		case "toggleNavigationElement":
			$status = $ret = '';
			$status = $sprockets_tag_handler->toggleStatus($clean_tag_id, 'navigation_element');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/category.php';
			if ($status == 0) {
				redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_TAG_NAVIGATION_DISABLED);
			} else {
				redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_TAG_NAVIGATION_ENABLED);
			}

			break;

		case "del":

			$controller = new icms_ipf_Controller($sprockets_tag_handler);
			$tagObj = $sprockets_tag_handler->get($clean_tag_id);
			if ($tagObj->getVar('label_type', 'e') !== '0') {
				$warning = _AM_SPROCKETS_CATEGORY_DELETE_CAUTION;
			} else {
				$warning = '';
			}
			$controller->handleObjectDeletion($warning);

			break;

		default:

			icms_cp_header();
			$sprocketsModule->displayAdminMenu(1, _AM_SPROCKETS_TAGS);

			// If no op is set, but there is a (valid) tag_id, display a single object
			if ($clean_tag_id) {
				$tagObj = $sprockets_tag_handler->get($clean_tag_id);
				if ($tagObj->id()) {
					$tagObj->displaySingleObject();
				}
			}

			// Restrict content to categories only (no tags)
			$criteria = icms_buildCriteria(array('label_type' => '1'));

			$objectTable = new icms_ipf_view_Table($sprockets_tag_handler, $criteria, $actions = array());
			$objectTable->addCustomAction('edit_category_action');
			$objectTable->addCustomAction('delete_category_action');
			$objectTable->addColumn(new icms_ipf_view_Column('title', 'left', FALSE,
					'category_admin_titles', basename(dirname(dirname(__FILE__)))));
			$objectTable->addColumn(new icms_ipf_view_Column('mid'));
			$objectTable->addcolumn(new icms_ipf_view_Column('navigation_element', 'left', FALSE,
					'category_admin_navigation_element', basename(dirname(dirname(__FILE__)))));
			$objectTable->addcolumn(new icms_ipf_view_Column('rss', 'left', FALSE, 
					'category_admin_rss', basename(dirname(dirname(__FILE__))),
					_AM_SPROCKETS_TAG_RSS_FEED));
			$objectTable->addFilter('mid', 'module_filter');
			$objectTable->addFilter('navigation_element', 'navigation_element_filter');
			$objectTable->addfilter('rss', 'rss_filter');
			$objectTable->addQuickSearch('title');
			$objectTable->addIntroButton('addtag', 'category.php?op=mod', _AM_SPROCKETS_CATEGORY_CREATE);

			$icmsAdminTpl->assign('sprockets_tag_table', $objectTable->fetch());
			$icmsAdminTpl->display('db:sprockets_admin_tag.html');

			break;
	  }
	}
}

icms_cp_footer();

/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */