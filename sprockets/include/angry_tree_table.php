<?php
/**
 * Contains the classes responsible for displaying a tree table filled with icms_ipf_Object
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		ipf
 * @subpackage  View
 * @since		1.1
 * @author		marcan <marcan@impresscms.org>
 * @version		$Id: icmspersistabletreetable.php 19651 2010-06-26 06:15:15Z malanciault $
 */

if (!defined('ICMS_ROOT_PATH')) die("ImpressCMS root path not defined");

/**
 * icms_ipf_view_Tree base class
 *
 * Base class representing a table for displaying icms_ipf_Object tree objects
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @category	ICMS
 * @package		Ipf
 * @subpackage  View
 * @since		1.1
 * @author		marcan <marcan@impresscms.org>
 * @version		$Id: icmspersistabletreetable.php 19651 2010-06-26 06:15:15Z malanciault $

 */
class icms_ipf_view_Tree extends icms_ipf_view_Table {
	
	public $realTree; // Added: Holds a tree of the icms_ipf_Tree class, used to handle tree operations
	public $clientModule; // Added: The module object for the client module, used to retrieve dirname etc

	// MODIFIED: Call parent constructor, this was failing in original function.
	public function __construct(&$objectHandler, $criteria=false, $actions=array('edit', 'delete'), $userSide=false)
	{
		parent::__construct($objectHandler, $criteria, $actions, $userSide);
		$this->_isTree = true;
	}
	
	// DELETED: public function getChildrenOf() - broken and not needed

	private function createTableRow($object, $level=0) {
		$aObject = array();

		$i=0;

		$aColumns = array();
		$doWeHaveActions = false;

		foreach ($this->_columns as $column) {
			$aColumn = array();

			if ($i==0) {
				$class = "head";
			} elseif ($i % 2 == 0) {
				$class = "even";
			} else {
				$class = "odd";
			}

			if ($column->_customMethodForValue && method_exists($object, $column->_customMethodForValue)) {
				$method = $column->_customMethodForValue;
				// MODIFIED: Need to pass in custom method parameters if they exist.
				if ($column->_param) {
					$value = $object->$method($column->_param);
				} else {
					$value = $object->$method();
				}
			} else {
				/**
				 * If the column is the identifier, then put a link on it
				 */
				if ($column->getKeyName() == $this->_objectHandler->identifierName) {
					$value = $object->getItemLink();
				} else {
					$value = $object->getVar($column->getKeyName());
				}
			}

			$space = '';
			if ($column->getKeyName() == $this->_objectHandler->identifierName) {
				for ($i = 0; $i < $level; $i++) {
					$space = $space . '--';
				}
			}

			if ($space != '') {
				$space .= '&nbsp;';
			}

			$aColumn['value'] = $space . $value;
			$aColumn['class'] = $class;
			$aColumn['width'] = $column->getWidth();
			$aColumn['align'] = $column->getAlign();
			$aColumn['key'] = $column->getKeyName();

			$aColumns[] = $aColumn;
			$i++;
		}

		$aObject['columns'] = $aColumns;

		$class = $class == 'even' ? 'odd' : 'even';
		$aObject['class'] = $class;

		$actions = array();

		// Adding the custom actions if any
		foreach ($this->_custom_actions as $action) {
			if (method_exists($object, $action)) {
				$actions[] = $object->$action();
			}
		}

		$controller = new icms_ipf_Controller($this->_objectHandler);

		if (in_array('edit', $this->_actions)) {
			$actions[] = $controller->getEditItemLink($object, false, true);
		}
		if (in_array('delete', $this->_actions)) {
			$actions[] = $controller->getDeleteItemLink($object, false, true);
		}
		$aObject['actions'] = $actions;

		$this->_tpl->assign('icms_actions_column_width', count($actions) * 30);
		$aObject['id'] = $object->id();
		$this->_aObjects[] = $aObject;

		// This is where the problem is, because getChildrenOf is borked. Better use a tree.
		$childrenObjects = $this->realTree->getFirstChild($object->getVar('tag_id'));

		$this->_hasActions =$this->_hasActions  ? true : count($actions) > 0;

		if ($childrenObjects) {
			$level++;
			foreach ($childrenObjects as $subObject) {
				$this->createTableRow($subObject, $level);
			}
		}
	}

	// Seems to create a additional rows for child objects, underneath the parent. This is the main
	// difference from the ordinary table, which just spits out one row per object. Problem is, its
	// broken
	public function createTableRows() {
		$this->_aObjects = array();
		
		// Construct a tree as it much better for handling tree operations
		$this->realTree = new icms_ipf_Tree($this->_objects, 'tag_id', 'parent_id', $rootId = null);
		
		// Call the client module object, as we'll be needing to ask for dirnames etc
		$first_object_id = key($this->_objects);
		if ($first_object_id) {
			$first_object = $this->_objects[$first_object_id];
			$client_module_id = $first_object->getVar('mid', 'e');
			$this->clientModule = icms::handler("icms_module")->get($client_module_id);
		}
		
		if (count($this->_objects) > 0) {
			// Modified
			foreach ($this->_objects as $object) {
				// Only create a top level row *if* the object is a top level category (parent_id = 0)
				if (!$object->getVar('parent_id')) {
					$this->createTableRow($object);
				}
			}

			$this->_tpl->assign('icms_persistable_objects', $this->_aObjects);
		} else {
			$colspan = count($this->_columns) + 1;
			$this->_tpl->assign('icms_colspan', $colspan);
		}
	}
	
	// Replacement for the above method. It definitely retrieves the objects ok. Modified to remove
	// an uneccessary 'parentid' parameter, which was also causing breakage.
	public function fetchObjects() {
		$ret = $this->_objectHandler->getObjects($this->_criteria, TRUE, TRUE);
		return $ret;

	}
}