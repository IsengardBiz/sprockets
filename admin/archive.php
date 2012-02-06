<?php
/**
 * Admin page to manage archives
 *
 * List, add, edit and delete archive objects. Only one archive object is permitted per module.
 * Archive objects manage responses to incoming OAIPMH queries. They perform no other function and
 * if a site doesn't need OAIPMH functions there is no need to generate one. Strictly optional.
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2010
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		sprockets
 * @version		$Id$
 */

/**
 * Edit an Archive
 *
 * @param int $archive_id Archiveid to be edited
 */
function editarchive($archive_id = 0) {
	global $sprockets_archive_handler, $icmsAdminTpl;

	$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

	$archiveObj = $sprockets_archive_handler->get($archive_id);
	
	if (isset($_POST['op']) && $_POST['op'] == 'changedField' && in_array($_POST['changedField'],
		array('module_id'))) {
		
		// use the module ID to set the appropriate base URL for OAIPMH requests
		$clean_module_id = isset($_POST['module_id']) ? (int) $_POST['module_id'] : 0 ;
		
		if ($clean_module_id !== '0') {
			$module_handler = icms::handler('icms_module');
			$selected_module = &$module_handler->get($clean_module_id);
			$target_directory = $selected_module->getVar('dirname');
			$_POST['base_url'] = ICMS_URL . '/modules/' . $target_directory	. '/oaipmh_target.php';
		}	
		
        $controller = new icms_ipf_Controller($sprockets_archive_handler);		
		$controller->postDataToObject($archiveObj);
	}

	if (!$archiveObj->isNew()) {
		$sprocketsModule->displayAdminMenu(2, _AM_SPROCKETS_ARCHIVES . " > " . _CO_ICMS_EDITING);
		$sform = $archiveObj->getForm(_AM_SPROCKETS_ARCHIVE_EDIT, 'addarchive');
		$sform->assign($icmsAdminTpl);
	} else {
		$sprocketsModule->displayAdminMenu(2, _AM_SPROCKETS_ARCHIVES . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $archiveObj->getForm(_AM_SPROCKETS_ARCHIVE_CREATE, 'addarchive');
		$sform->assign($icmsAdminTpl);
	}
	$icmsAdminTpl->display('db:sprockets_admin_archive.html');
}

include_once("admin_header.php");

$sprockets_archive_handler = icms_getModuleHandler('archive', 
	basename(dirname(dirname(__FILE__))), 'sprockets');

$clean_op = '';

/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */

$valid_op = array ('mod','changedField','addarchive','toggleStatus', 'del','');

if (isset($_GET['op'])) $clean_op = htmlentities($_GET['op']);
if (isset($_POST['op'])) $clean_op = htmlentities($_POST['op']);

// sanitise archive_id
$clean_archive_id = isset($_GET['archive_id']) ? (int) $_GET['archive_id'] : 0 ;

if (in_array($clean_op,$valid_op,TRUE)) {
	switch ($clean_op) {
		case "mod":
		case "changedField":

			icms_cp_header();
			editarchive($clean_archive_id);

			break;
		
		case "addarchive":

			$controller = new icms_ipf_Controller($sprockets_archive_handler);
			$controller->storeFromDefaultForm(_AM_SPROCKETS_ARCHIVE_CREATED,
					_AM_SPROCKETS_ARCHIVE_MODIFIED);

			break;
		
		case "toggleStatus":
		
			$status = $sprockets_archive_handler->toggleStatus($clean_archive_id, 'enable_archive');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/archive.php';
			if ($status == 0) {
				redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_ARCHIVE_DISABLED);
			} else {
				redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_ARCHIVE_ENABLED);
			}
			
		break;

		case "del":

			$controller = new icms_ipf_Controller($sprockets_archive_handler);
			$controller->handleObjectDeletion();

			break;

		default:

			icms_cp_header();
			$sprocketsModule->displayAdminMenu(2, _AM_SPROCKETS_ARCHIVES);
			
			// advise that only one archive object can be created per client module
			echo _CO_SPROCKETS_ONLY_ONE_ARCHIVE_PER_MODULE;

			// if no op is set, but there is a (valid) archive_id, display a single object
			if ($clean_archive_id) {
				$archiveObj = $sprockets_archive_handler->get($clean_archive_id);
				if ($archiveObj->id()) {
					$archiveObj->displaySingleObject();
				}
			}

			$objectTable = new icms_ipf_view_Table($sprockets_archive_handler, FALSE);
			$objectTable->addColumn(new icms_ipf_view_Column('repository_name'));
			$objectTable->addColumn(new icms_ipf_view_Column('module_id'));
			$objectTable->addColumn(new icms_ipf_view_Column('enable_archive'));

			$objectTable->addIntroButton('addarchive', 'archive.php?op=mod',
				_AM_SPROCKETS_ARCHIVE_CREATE);
			
			$icmsAdminTpl->assign('sprockets_archive_table', $objectTable->fetch());
			$icmsAdminTpl->display('db:sprockets_admin_archive.html');

			break;
	}
	icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */