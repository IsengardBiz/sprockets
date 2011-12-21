<?php
/**
* Admin page to manage taglinks
*
* List, add, edit and delete taglink objects
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

/**
 * Edit a Taglink
 *
 * @param int $taglink_id Taglinkid to be edited
*/
function edittaglink($taglink_id = 0)
{
	global $sprockets_taglink_handler, $icmsAdminTpl;

	$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

	$taglinkObj = $sprockets_taglink_handler->get($taglink_id);

	if (!$taglinkObj->isNew()){
		$sprocketsModule->displayAdminMenu(2, _AM_SPROCKETS_TAGLINKS . " > " . _CO_ICMS_EDITING);
		$sform = $taglinkObj->getForm(_AM_SPROCKETS_TAGLINK_EDIT, 'addtaglink');
		$sform->assign($icmsAdminTpl);

	} else {
		$sprocketsModule->displayAdminMenu(2, _AM_SPROCKETS_TAGLINKS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $taglinkObj->getForm(_AM_SPROCKETS_TAGLINK_CREATE, 'addtaglink');
		$sform->assign($icmsAdminTpl);

	}
	$icmsAdminTpl->display('db:sprockets_admin_taglink.html');
}

include_once("admin_header.php");

$sprockets_taglink_handler = icms_getModuleHandler('taglink');

$clean_op = '';


/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */

$valid_op = array ('mod','changedField','addtaglink','del','');

if (isset($_GET['op'])) $clean_op = htmlentities($_GET['op']);
if (isset($_POST['op'])) $clean_op = htmlentities($_POST['op']);

// sanitise the taglink_id
$clean_taglink_id = isset($_GET['taglink_id']) ? (int) $_GET['taglink_id'] : 0 ;

if (in_array($clean_op,$valid_op,true)){
  switch ($clean_op) {
  	case "mod":
  	case "changedField":

		icms_cp_header();
  		edittaglink($clean_taglink_id);
  		break;

  	case "addtaglink":

		include_once ICMS_ROOT_PATH."/kernel/icmspersistablecontroller.php";
		$controller = new IcmsPersistableController($sprockets_taglink_handler);
  		$controller->storeFromDefaultForm(_AM_SPROCKETS_TAGLINK_CREATED,
			_AM_SPROCKETS_TAGLINK_MODIFIED);

  		break;

  	case "del":

  	    include_once ICMS_ROOT_PATH."/kernel/icmspersistablecontroller.php";
        $controller = new IcmsPersistableController($sprockets_taglink_handler);
  		$controller->handleObjectDeletion();

  		break;

  	default:

		/**
		 * The taglink_table is only included for development/debugging purposes, to enable it
		 * uncomment the taglink menu tab in admin/menu.php
		 */

  		icms_cp_header();

  		$sprocketsModule->displayAdminMenu(2, _AM_SPROCKETS_TAGLINKS);

  		$objectTable = new icms_ipf_view_Table($sprockets_taglink_handler);
  		$objectTable->addColumn(new icms_ipf_view_Column('tid'));
		$objectTable->addColumn(new icms_ipf_view_Column('mid'));
		$objectTable->addColumn(new icms_ipf_view_Column('iid'));
		$objectTable->addColumn(new icms_ipf_view_Column('item'));

  		$objectTable->addIntroButton('addtaglink', 'taglink.php?op=mod',
				_AM_SPROCKETS_TAGLINK_CREATE);
  		$icmsAdminTpl->assign('sprockets_taglink_table', $objectTable->fetch());
  		$icmsAdminTpl->display('db:sprockets_admin_taglink.html');
  		break;
  }
  icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */