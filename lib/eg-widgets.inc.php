<?php
/*
Package Name: EG-Widgets
Plugin URI:
Description:  Abstract class to create and manage widget
Version: 1.0.0
Author: Emmanuel GEORJON
Author URI: http://www.emmanuelgeorjon.com/
*/

/*  Copyright 2009  Emmanuel GEORJON  (email : blog@georjon.eu)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 *  Constant MULTI_WIDGET_MAX_NUMBER
 *
 * Maximum number of widgets allowed
 */
define('MULTI_WIDGET_MAX_NUMBER', 9);

if (!class_exists('EG_Widget_100')) {

	/**
	  *  EG_Widget - Class
	  *
	  * {@internal Missing Long Description}
	  *
	  * @package EG-Widgets
	  *
	  */
	class EG_Widget_100 {

		var $id;
		var $options = FALSE;
		var $default_values;
		var $class_name;
		var $title;
		var $description;
		var $textdomain;
		var $corefile;
		var $cacheexpiration;
		var $current_number;

		var $language_list = array(
			'fr_FR' => 'Fran&ccedil;ais',
			'en_US' => 'English',
			'es_ES' => 'Espa&ntilde;ol',
			'de_DE' => 'Deutsch',
			'it_IT' => 'Italiano'
		);

		/**
		 * EG_Widget() - Constructor
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
		 * @param $id				string		widget_id
		 * @param $title			string		widget title (appears of the left side of widget page)
		 * @param $description		string		description (appears of the left side of widget page)
		 * @param $class_name		string		style to use in the HTML code of the widget
		 * @param $this->textdomain	string		Text domain to use in the widget
		 * @param $widget_variable	boolean		Display widget according parameters or not
		 * @param $current_number	in			default number of widgets
		 * @param $default_values	array		defaults options values
		 *
		 * Structure of $default_values parameter
		 * 	array(
		 *		'field_id' => array(
		 *			'type'    => 'text',
		 *			'label'   => __('label',  $this->textdomain),
		 *			'default' => 'default value'
		 *			),
		 *		'field_id' => array(
		 *			'type'    => 'select',
		 *			'label'   => __('label', $this->textdomain),
		 *			'default' => 'default value',
		 *			'list'    => array( 'option id' => __('option text', $this->textdomain), 'option id' => __('option text', $this->textdomain))
		 *			),
		 *		)
		 *
		 */
		function EG_Widget_100($id,
						$title,
						$description,
						$class_name,
						$textdomain,
						$corefile,
						$cexpiration,
						$widget_variable,
						$default_number,
						$default_values) {

			$this->__construct($id,
						$title,
						$description,
						$class_name,
						$textdomain,
						$corefile,
						$cexpiration,
						$widget_variable,
						$default_number,
						$default_values);
		}

		/**
		 * EG_Widget() - Constructor
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
		 * See EG_Widget()  documentation
		 */
		function __construct($id,
						$title,
						$description,
						$class_name,
						$textdomain,
						$corefile,
						$cexpiration,
						$widget_variable,
						$default_number,
						$default_values) {

			// Initialize parameters
			$this->id              = $id;
			$this->title           = $title;
			$this->class_name      = $class_name;
			$this->description     = $description;
			$this->textdomain      = $textdomain;
			$this->corefile        = $corefile;
			$this->cacheexpiration = $cexpiration;

			if ($default_number < 1) $default_number = 1;
			else if ($default_number > MULTI_WIDGET_MAX_NUMBER) $default_number = MULTI_WIDGET_MAX_NUMBER;

			// Get previously saved options
			$this->options = get_option($id);
			if ($this->options !== FALSE && isset($this->options['number'])) $this->current_number = $this->options['number'];
			else $this->current_number = $default_number;

			// If we want to display widget according parameters, add specific fields
			if ($widget_variable) {
				$default_values[$this->id.'_separator'] = array(
					'type'    => 'separator'
				);
				$default_values[$this->id.'_show_when'] = array(
						'type'    => 'select',
						'label'   => 'Show widget on pages',
						'default' => 'all',
						'list'  => array( 'all'        => 'All',
										  'categories' => 'Categories',
										  'posts'      => 'Posts',
										  'tags'       => 'Tags')
					);
				$default_values[$this->id.'_show_id'] = array(
						'type'    => 'ftext',
						'label'   => 'Show widget, for',
						'default' => ''
					);
				$default_values[$this->id.'_show_lang'] = array(
						'type'    => 'select',
						'label'   => 'Show widget, when',
						'list'    => array_merge( array('all' => ' '), $this->language_list),
						'default' => ''
					);
				$default_values[$this->id.'_hide_lang'] = array(
						'type'    => 'select',
						'label'   => 'Hide widget, when',
						'list'    => array_merge( array('none' => ' '), $this->language_list),
						'default' => ''
					);
			}
			$this->default_values = $default_values;

			// Add action for widgets initialization
			// add_action('widgets_init', array(&$this, 'register'));
		}

		/**
		 * __destruct - Destructor
		 *
		 * @param none
		 */
		function __destruct() {

		}

		function load($load_textdomain=FALSE) {
			register_shutdown_function(array(&$this, '__destruct'));
			if ($load_textdomain) {
				add_action('init', array(&$this, 'init'));
			}
			add_action('widgets_init', array(&$this, 'register'));
		}

		function init() {
			global $wp_version;

			if ($this->textdomain && function_exists('load_plugin_textdomain')) {
				if (version_compare($wp_version, '2.6', '<')) {
					// for WP < 2.6
					load_plugin_textdomain( $this->textdomain, str_replace(ABSPATH,'', $this->corefile).'/lang');
				} else {
					// for WP >= 2.6
					load_plugin_textdomain( $this->textdomain, FALSE , basename(dirname($this->corefile)).'/lang');
				}
			}
		}

		/**
		 * register - Register the widget
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
		 * @param none
		 */
		function register() {

			// Max_number = current number of widgets
			$current_number = $this->current_number;

			if ($current_number == 1) {
				$widget_ops = array('classname' => $this->class_name, 'description' => __($this->description, $this->textdomain) );
				wp_register_sidebar_widget($this->id, __($this->title, $this->textdomain), array(&$this, 'display'), $widget_ops);
				wp_register_widget_control($this->id, __($this->title, $this->textdomain), array(&$this, 'control') );
			}
			else {
				// $dims = array('width' => 460, 'height' => 350);
				$class = array('classname' => 'widget_links');
				for ($i = 1; $i <= MULTI_WIDGET_MAX_NUMBER; $i++) {
					$id   = $this->id.'-'.$i;
					$name = __($this->title, $this->textdomain).' '.$i;
					wp_register_sidebar_widget($id, $name, $i <= $current_number ? array(&$this, 'display') : /* unregister */ '', $class, $i);
					wp_register_widget_control($id, $name, $i <= $current_number ? array(&$this, 'control') : /* unregister */ '', array() /* $dims */, $i);
				}
				add_action('sidebar_admin_setup', array( $this, 'setup'));
				add_action('sidebar_admin_page', array( $this, 'page'));
			}
		}

		/**
		 * action - Display widget (the widget itself)
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
		 * @param	$args	array		before_widget, after_widget, before_title, after_title ...
		 * @param	$number	int		id of the widget (in case of multi-widget only)
		 */
		function display($args, $number = -1) {
			/* function to be surcharged  */
		}

		/**
		 * control - Display and manage the widget control panel
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
		 * @param	$number	int		id of the widget (in case of multi-widget only)
		 */
		function control($number = -1) {

			$submit_button_label = $this->id.'_submit'.($number<0?'':'-'.$number);

			// Get options
			$this->update_widget_options($number);

			// if user click on the submit button ?
			if ( $_POST[$submit_button_label] ) {
				// Get values from the form
				$this->get_form_values($number);
				update_option($this->id, $this->options);
			}
			// Display form
			echo $this->generate_form($number);
		}


		/**
		 * generate_select_form - Generate HTML <select> <option> ...</option></select> code
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
		 * @param	$id		string	id/name of the field
		 * @param	$values	array		list of values
		 * @param	$default	array		default value
		 */
		function generate_select_form($id, $values, $default) {
			$select_string = '<select id="'.$id.'" name="'.$id.'">';
			foreach( $values as $key => $value) {
				if (trim($value) == '') $value = '';
				else $value = __($value, $this->textdomain);
				if ($key == $default) $string = 'selected'; else $string = '';
				$select_string .= '<option '.$string.' value="'.$key.'">'.$value.'</option>';
			}
			$select_string .= '</select>';
			return ($select_string);
		}

		/**
		 * generate_form - Display the widget control panel form
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
 		 * @param	$number	int		id of the widget (in case of multi-widget only)
		 */
		function generate_form($number = -1) {

			if ($number < 0) {
				$option_list = & $this->options;
			}
			else {
				$option_list = & $this->options[$number];
			}
			$fields = $this->default_values;

			$form = '';
			foreach ($fields as $field_name => $field_value) {

				$default_value = (isset($option_list[$field_name])?$option_list[$field_name]:__($field_value['default'], $this->textdomain));
				$item_name = ($number<0?$field_name:$field_name.'-'.$number);

				switch ($field_value['type']) {

					case 'separator':
						$form .= '<hr />';
					break;

					case 'numeric':
						$form .= "\n".'<p><label for="'.$item_name.'">'.__($field_value['label'], $this->textdomain).': '.
						         "\n".'<input type="text" id="'.$item_name.'" name="'.$item_name.'" value="'.$default_value.'" size="10" />'.
								 "\n".'</label></p>';
					break;

					case 'text':
					case 'ftext':
						$form .= "\n".'<p><label for="'.$item_name.'">'.__($field_value['label'], $this->textdomain).': '.
						         "\n".'<input type="text" id="'.$item_name.'" name="'.$item_name.'" value="'.format_to_edit($default_value).'" size="10" />'.
								 "\n".'</label></p>';
					break;
					case 'select':
						$form .= "\n".'<p><label for="'.$item_name.'">'.__($field_value['label'], $this->textdomain).': '.
						         "\n".$this->generate_select_form($item_name, $field_value['list'], $default_value).
								 "\n".'</label></p>';
					break;

					case 'radio':
						$form .= "\n".'<p><label for="'.$item_name.'">'.__($field_value['label'], $this->textdomain).'</label><br />';
						foreach ($field_value['list'] as $key => $value) {
							if ($default_value == $key) $string = 'checked'; else $string = '';
							$form .= "\n".'<input type="radio" id="'.$item_name.'" name="'.$item_name.'" value="'.$key.'" '.$string.' />'.__($value, $this->textdomain).'<br />';
						}
						$form .= "\n".'</p>';
					break;

					case 'checkbox': 
						/*
						if (isset($field_value['list'])) {
							$i = 0;
							$form .= "\n".'<p><label for="'.$item_name.'">'.__($field_value['label'], $this->textdomain).'</label><br />';
							foreach ($field_value['list'] as $key => $value) {
								if ($default_value == $key) $string = 'checked'; else $string = '';
								$form .= "\n".'<input type="checkbox" id="'.$item_name.'" name="'.$item_name.'['.$i.']" value="'.$key.'" '.$string.' />'.__($value, $this->textdomain).'<br />';
								$i++;
							}
							$form .= '</p>';
						}
						else {

						} */
						$form .= "\n".'<p><input type="checkbox" id="'.$item_name.'" name="'.$item_name.'" value="1" '.($default_value==0?'':'checked').' /> <label for="'.$item_name.'">'.__($field_value['label'], $this->textdomain).'</label></p>';
						
						
					break;
				}
			}
			$form .= "\n".'<input type="hidden" id="'.$this->id.'_submit'.($number<0?'':'-'.$number).'" name="'.$this->id.'_submit'.($number<0?'':'-'.$number).'" value="1" />';
			return ($form);
		}

		/**
		 * get_form_values - Get the form values registered by user
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
 		 * @param	$number	int		id of the widget (in case of multi-widget only)
		 */
		function get_form_values($number = -1) {

			if ($number < 0) {
				$option_list = & $this->options;
			}
			else {
				$option_list = & $this->options[$number];
			}
			$fields = $this->default_values;
			foreach( $fields as $field_name => $field_value) {
				if ($number < 0) $item_name = $field_name;
				else $item_name = $field_name.'-'.$number;

				switch ($field_value['type']) {
					case 'ftext':
						if (isset($_POST[$item_name])) $option_list[$field_name] = strip_tags(stripslashes($_POST[$item_name]));
						else $option_list[$field_name] = $field_value['default'];
					break;

					case 'text':
						if (isset($_POST[$item_name])) $option_list[$field_name] = stripslashes($_POST[$item_name]);
						else $option_list[$field_name] = $field_value['default'];
					break;

					case 'numeric':
						$value = $_POST[$item_name];
						if (is_numeric($value)) $option_list[$field_name] = intval($value);
						else $option_list[$field_name] = $field_value['default'];
					break;

					case 'select':
					case 'radio':
						$option_list[$field_name] = $_POST["$item_name"];
					break;

					case 'checkbox':
						if (isset($_POST["$item_name"])) $option_list[$field_name] = 1;
						else $option_list[$field_name] = 0;
					break;
				}
			}
		}

		/**
		 * Get_widget_options - Get previously saved options of the specified widget
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
 		 * @param	$number	int		id of the widget (in case of multi-widget only)
		 */
		function update_widget_options($number = -1) {

			$create_mode = FALSE;
			$update_mode = FALSE;
			if ($this->options === FALSE) {
				$create_mode = TRUE;
				$options = array();
			}

			if ($number <0) {
				$new_options = & $this->options;
			}
			else {
				if (!isset($this->options[$number])) {
					$this->options[$number] = array();
					$update_mode = TRUE;
				}
				$new_options = & $this->options[$number];
			}
			// Update options with the default values
			foreach ($this->default_values as $key => $field) {
				if (!isset($new_options["$key"])) {
					$new_options["$key"] = $field['default'];
					$update_mode = TRUE;
				}
			}
			if ($create_mode) add_option($this->id, $this->options);
			else if ($update_mode) update_option($this->id, $this->options);
		}

		/**
		 * is_visible - Return flag to know if the widget can be displayed or not
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
 		 * @param	$number	int		id of the widget (in case of multi-widget only)
		 */
		function is_visible($number = -1) {
			global $locale;

			// Mono or Multi-widget
			if ($number < 0) {
				// Mono widget
				$option_list = & $this->options;
			} else {
				// Multi-widget
				$option_list = & $this->options[$number];
			}

			// By default: the widget is visible
			$value = TRUE;
			if (isset($option_list[$this->id.'_show_when']) && $option_list[$this->id.'_show_when'] != 'all') {

				// Id or list of id specifided?
				if ($option_list[$this->id.'_show_id'] != '') {
					$id_list = explode(',', $option_list[$this->id.'_show_id']);
				}
				switch ($option_list[$this->id.'_show_when']) {
					case 'categories':
						$value = is_category($id_list);
					break;

					case 'posts':
						$value = is_single($id_list);
					break;

					case 'tags':
						$value = is_tag($id_list);
					break;
				}
			}
			if (isset($option_list[$this->id.'_show_lang']) && $option_list[$this->id.'_show_lang'] != 'all') {
				$value = ($locale == $option_list[$this->id.'_show_lang']);
			}
			if (isset($option_list[$this->id.'_hide_lang']) && $option_list[$this->id.'_hide_lang'] != 'none') {
				$value = ($locale != $option_list[$this->id.'_hide_lang']);
			}
			// echo "==".$locale."==".$option_list[$this->id.'_show_lang']."==".$option_list[$this->id.'_hide_lang']."==".($value?'TRUE':'FALSE')."<br />";
			return ($value);
		} /* End of function is_visible */

		/**
		 * setup - Get values from the widget admin page form, and save them
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
 		 * @param	none
		 */
		function setup() {

			// Run only if user click on the right submit button
			if ( isset($_POST[$this->id.'_number_submit']) ) {

				// get the id
				$number = (int) $_POST[$this->id.'_number'];

				// Filter this id
				if ( $number > MULTI_WIDGET_MAX_NUMBER ) $number = MULTI_WIDGET_MAX_NUMBER;
				if ( $number < 1 ) $number = 1;

				// Ifthe id is different than the previous, then save it
				if ($number != $this->current_number) {
					$this->current_number    = $number;
					$this->options['number'] = $number;
					update_option($this->id, $options);
					$this->register();
				}
			}
		} /* End of function setup */

		/**
		 * page - Display additional form in the widget admin page
		 *
		 * {@internal Missing Long Description}
		 *
		 * @package EG-Widgets
		 *
 		 * @param	none
		 */
		function page() {
		?>
			<div class="wrap">
				<form method="POST">
					<h2><?php _e($this->title, $this->textdomain); ?></h2>
					<p style="line-height: 30px;">
						<?php printf(__('How many %s widgets would you like?', $this->textdomain), $this->title); ?>
					<select id="<?php echo $this->id; ?>_number" name="<?php echo $this->id; ?>_number">
				<?php for ( $i = 1; $i <= MULTI_WIDGET_MAX_NUMBER; ++$i )
						echo '<option value="'.$i.'"'.($this->current_number==$i ? 'selected="selected"' : '').'>'.$i.'</option>';
				?>
					</select>
					<span class="submit"><input type="submit" name="<?php echo $this->id; ?>_number_submit" id="<?php echo $this->id; ?>_number_submit" value="<?php _e('Save', $this->textdomain); ?>" /></span></p>
				</form>
			</div>
		<?php
		} /* End of function page */

	} /* End of Class EG_Widget */

} /* End of if class_exists */

?>