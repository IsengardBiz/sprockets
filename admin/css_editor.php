<?php

/**
* Edit the active theme CSS from within the admin control panel
*
* Displays style sheets for the active theme in a text area where you can edit them. Submitting the
* form overwrites style.css with the revised data. Note that this script assumes that your theme css
* files lie in the theme's base directory will not work if they lie elsewhere.
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

include_once("admin_header.php");
icms_cp_header();

$clean_op = $clean_css = $clean_theme = $clean_stylesheet = $valid_theme = $dirty_css
		= $theme_files = $theme_path = '';
		
if (isset($_GET['op'])) $clean_op = htmlentities(trim($_GET['op']));
if (isset($_POST['op'])) $clean_op = htmlentities(trim($_POST['op']));
if (isset($_POST['stylesheet'])) $clean_stylesheet = htmlentities(trim($_POST['stylesheet']));
if (isset($_GET['stylesheet'])) $clean_stylesheet = htmlentities(trim($_GET['stylesheet']));
if (empty($clean_stylesheet))
{
	$clean_stylesheet = 'style.css';
}
// strip html and PHP tags to reduce opportunities for abuse
if (isset($_POST['css'])) $clean_css = htmlentities(strip_tags(trim($_POST['css'])));

// whitelist permitted operations
$valid_op = array ('save', '');
if (in_array($clean_op, $valid_op, TRUE))
{
	global $icmsConfig;
	$theme_path = ICMS_ROOT_PATH . '/themes/' . $icmsConfig['theme_set'] . '/';
	$theme_files = scandir($theme_path);
	
	switch ($clean_op)
	{		
		case "save": // overwrites style.css with the revised style sheet
			
			$sprocketsModule->displayAdminMenu(3, _AM_SPROCKETS_EDIT_CSS);
			
			// compare $clean_stylesheet against whitelist of valid file names to prevent abuse
			foreach ($theme_files as $key => $value)
			{
				if (substr($value, -4, 4) == '.css')
				{
					$valid_stylesheets[] = $value;
				}
			}
			$valid_stylesheet = FALSE;
			if (in_array($clean_stylesheet, $valid_stylesheets))
			{
				$valid_stylesheet = $clean_stylesheet;
			}
			
			if ($clean_css && $valid_stylesheet)
			{
				$result = file_put_contents($theme_path . $valid_stylesheet, $clean_css);
				$ret = '/modules/' . basename(dirname(dirname(__FILE__))) 
							. '/admin/css_editor.php?stylesheet=' . $valid_stylesheet;
				if ($result)
				{
					redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_CSS_SAVE_SUCCESSFUL);
				}
				else
				{
					redirect_header(ICMS_URL . $ret, 2, _AM_SPROCKETS_CSS_SAVE_FAILED);
				}
			}
			
			break;
		
		default: // displays style.css in a form where it can be edited
			
			$sprocketsModule->displayAdminMenu(3, _AM_SPROCKETS_EDIT_CSS);

			$file_handle = $style_css = $theme_select_form = $stylesheet_edit_form
				= $css_list = $valid_stylesheet = '';
			
			///////////////////////////////////////////////////////////////////
			//////////////////// .css files for this theme ////////////////////
			///////////////////////////////////////////////////////////////////
			
			$valid_stylesheets = array();
			
			// prepare a list of .css filenames from the active theme directory
			foreach ($theme_files as $key => $value)
			{
				if (substr($value, -4, 4) == '.css')
				{
					$valid_stylesheets[] = $value;
				}
			}
			
			// Compare $clean_stylesheet against whitelisted filenames (.css in theme directory) to
			// prevent abuse (such as directory traversals and arbitrary file editing).
			if (in_array($clean_stylesheet, $valid_stylesheets))
			{
				$valid_stylesheet = $clean_stylesheet;
			}
			else
			{
				$valid_stylesheet = 'style.css'; // default
			}
			echo '<h2>' . $icmsConfig['theme_set'] . '</h2>';
			echo '<h3>' . _AM_SPROCKETS_CSS_AVAILABLE_STYLE_SHEETS . '</h3>';
			$stylesheet_select_form = '<form action="css_editor.php" method="post">';
			$stylesheet_select_form .= '<select name="stylesheet" onchange="this.form.submit()">';
			foreach($valid_stylesheets as $key => $value)
			{
				if ($valid_stylesheet == $value)
				{
					$stylesheet_select_form .= '<option value="' . $value . '" selected="selected">'
							. $value . '</option>';
				}
				else
				{
					$stylesheet_select_form .= '<option value="' . $value . '">' . $value . '</option>';
				}
			}
			$stylesheet_select_form .= '</select></form>';
			echo $stylesheet_select_form;

			///////////////////////////////////////////////////////////////////
			//////////////////// Style editor form ////////////////////////////
			///////////////////////////////////////////////////////////////////
			
			$style_css = file_get_contents($theme_path . $valid_stylesheet);
			
			// display the selected .css file (default is style.css)
			echo '<h3>' . _AM_SPROCKETS_CSS_EDITING . $valid_stylesheet . '</h3>';
			$stylesheet_edit_form = '<form action="css_editor.php" method="post">';
			$stylesheet_edit_form .= '<textarea name="css" rows="25">';
		    $stylesheet_edit_form .= $style_css;
			$stylesheet_edit_form .= '</textarea><br />';
			$stylesheet_edit_form .= '<input type="hidden" name="op" value="save">';
			$stylesheet_edit_form .= '<input type="hidden" name="stylesheet" value="'
					. $valid_stylesheet . '">';
			$stylesheet_edit_form .= '<input type="submit" name="submit" value="'
					. _AM_SPROCKETS_UPDATE_FILE . '">';
			$stylesheet_edit_form .= '<input type="button" name="Cancel" value="'
					. _AM_SPROCKETS_CANCEL . '" 
				onclick="window.location = \'index.php\'" />';
			$stylesheet_edit_form .= '</form>';
			echo $stylesheet_edit_form;
			
			break;
	}
}

icms_cp_footer();