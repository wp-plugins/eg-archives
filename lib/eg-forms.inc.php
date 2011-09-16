<?php
/*
Package Name: EG-Forms
Package URI:
Description: Class for WordPress plugins
Version: 2.0.0
Author: Emmanuel GEORJON
Author URI: http://www.emmanuelgeorjon.com/
*/

/*
    Copyright 2009-2011 Emmanuel GEORJON  (email : blog@emmanuelgeorjon.com)

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

/* Data structure

$form = array(
		'menu_type'		=> ''   posts, options, settings, tools, theme, users, media, links, pages
		'title' 		=> '',
		'header'		=> '',
		'footer'		=> '',
		'icon'			=> '',
		'access_level'	=> 'manage_options',
		'sections'		=> array(
			1 => array(
				'title' 	=> '',
				'header'	=> '',
				'footer'	=> ''
			),
		), // End of sections
		'fields'		=> array(
			array('section'	=> 1,
				'name'		=> '',
				'label'		=> '',
				'after'		=> '',
				'desc'		=> '',
				'type'		=> 'select',  text, password, textarea, checkbox, radio, select, grid_select
				'options'	=> array( )
			)
		)
	);
*/

if (!class_exists('EG_Form_200')) {

	/**
	  * Class EG_Form_200
	  *
	  * Provide some functions to create a WordPress plugin
	  *
	 */
	Class EG_Form_200 {

		var $options_entry;
		var $options_group;
		var $textdomain;
		var $base_url;
		var $form_only;
		var $form;

		var $field_defaults = array(
			'section'	=> 0,
			'name'		=> '',
			'label'		=> '',
			'before'	=> '',
			'after'		=> '',
			'desc'		=> '',
			'type'		=> '',
			'options'	=> '',
			'size'		=> 'regular',
			'status'	=> ''
		);

		var $section_defaults = array(
				'title' 	=> 'General',
				'header'	=> '',
				'footer'	=> ''
		);

		var $page_list = array ( 'posts'	=> 'edit.php',
								 'options'	=> 'options-general.php',
								 'settings'	=> 'options-general.php',
								 'tools'	=> 'tools.php',
								 'theme'	=> 'themes.php',
								 'users'	=> 'users.php',
								/*
								'media'	=> 'add_media_page',
								 'links'	=> 'add_links_page', */
								 'pages'	=> 'edit.php');

		function EG_Form_200($page_id, $options_entry, $textdomain, $base_url, $form, $form_only=FALSE) {

			register_shutdown_function(array(&$this, '__destruct'));
			$this->__construct($page_id, $options_entry, $textdomain, $base_url, $form, $form_only);
		} // End of EG_Form_200

		/**
		  * Class contructor
		  * Define the plugin url and path. Declare action INIT and HEAD.
		  *
		  * @package EG-Forms
		  * @return object
		  */
		function __construct($page_id, $options_entry, $textdomain, $base_url, $form, $form_only=FALSE) {

			$this->options_entry     = $options_entry;
			$this->options_group	 = $page_id.'_group';
			$this->textdomain		 = $textdomain;
			$this->base_url			 = $base_url;
			$this->form_only		 = $form_only;
			$this->form			 	 = $form;

			add_action('admin_init',array( &$this, 'admin_init'));
		} // End of __construct

		/**
		 * Class destructor
		 *
		 * @package EG-Forms
		 *
		 * @return boolean true
		 */
		function __destruct() {
			// Nothing
		} // End of __destruct

		function admin_init() {
			wp_enqueue_style('dashboard');
			wp_enqueue_script( 'dashboard' );
			// wp_enqueue_script( 'postbox' );
			register_setting($this->options_group, $this->options_entry, array(&$this, 'options_validation'));
		} // End of admin_init

		function options_validation($inputs) {
			$all_options = get_option($this->options_entry);

			$validated_inputs = array();
			foreach ($this->form['fields'] as $field) {
				
				// If field exist in plugin options
				$key = $field['name'];
				if ( isset($all_options[$key])) {
					switch ($field['type']) {
						case 'checkbox': 
							if (! isset($inputs[$key])) $validated_inputs[$key] = 0;
							else $validated_inputs[$key] = $inputs[$key];
						break;
						
						case 'grid_select':
							$validated_inputs[$key] = array();
							if (isset($inputs[$key]) && is_array($inputs[$key])) {
								$i=1;
								foreach ($inputs[$key] as $input_value) {
									if ($input_value != '0') $validated_inputs[$key][$i++] = $input_value;
								}
							} // End of isset($inputs[$key]
						break;
						
						default:
							if (isset($inputs[$key])) {
								if (is_array($inputs[$key])) {
									$validated_inputs[$key] = (array)$inputs[$key];
								}
								else {
									if (is_float($inputs[$key])) $validated_inputs[$key] = floatval($inputs[$key]);
									elseif (is_int($inputs[$key])) $validated_inputs[$key] = intval($inputs[$key]);
									else $validated_inputs[$key] = trim(stripslashes($inputs[$key]));
								}
							} // End of isset($inputs[$key]
					} // End of switch
				} // End of field exists in plugin options
			} // End of foreach
			return (wp_parse_args($validated_inputs, $all_options));
		} // End of options_validation

		function display_comment($field, $entry_name, $default_value) {
			echo '<p name="'.$field['name'].'">'.__($field['label'], $this->textdomain).'</p>';
		} // End of display_comment

		function display_radio($field, $entry_name, $default_value) {
			return ($this->display_checkbox($field, $entry_name, $default_value));
		}

		function display_checkbox($field, $entry_name, $default_value) {

			if (! is_array($field['options'])) {
				$string = '<fieldset>'.
					'<legend class="screen-reader-text">'.
					'<span>'.$field['label'].'</span>'.
					'</legend>'.
					'<label for="'.$field['name'].'">'.
					($field['before']==''?'':__($field['before'], $this->textdomain).' ').
					'<input type="'.$field['type'].'" id="'.$field['name'].'" name="'.$entry_name.'" value="1" '.checked(1, $default_value, FALSE).' '.$field['status'].' />'.
					($field['after']==''?'':' '.__($field['after'], $this->textdomain)).
					'</label>'.
					'</fieldset>';
			}
			else {
				$string = '<fieldset>'.
						'<legend class="screen-reader-text">'.
						'<span>'.$field['label'].'</span>'.
						'</legend><table class="eg-forms">';
				foreach ($field['options'] as $key => $value) {
					if ($field['type'] == 'radio') $input_name = $entry_name;
					else $input_name = $entry_name.'['.$key.']';
					if (!is_array($default_value)) {
						$checked = ($key == $default_value?'checked':'');
					}
					else {
						$checked = (in_array($key, $default_value)===FALSE?'':'checked');
					}

					$string .= '<tr><td valign="top">'.
						'<input type="'.$field['type'].'" name="'.$input_name.'" value="'.$key.'" '.$checked.' '.$field['status'].' /></td>'.
						'<td><label for="'.$key.'">'.__($value, $this->textdomain).'</label></td>'.
						'</tr>';
				}
				$string .= '</table></fieldset>';
			}
			return ($string);
		} // End of display_checkbox

		function display_hidden($field, $entry_name, $default_value) {
			$string = '<input type="'.$field['type'].'" name="'.$entry_name.'" id="'.$field['name'].'" value="'.$default_value.'" /> ';
			return ($string);
		} // End of display_hidden

		function display_password($field, $entry_name, $default_value) {
			return ($this->display_text($field, $entry_name, $default_value));
		} // End of display_password

		function display_text($field, $entry_name, $default_value) {
			$string = '<input type="'.$field['type'].'" class="'.$field['size'].'-text" name="'.$entry_name.'" id="'.$field['name'].'" value="'.htmlspecialchars($default_value).'" '.$field['status'].'/> ';
			return ($string);
		} // End of display_text

		function display_textarea($field, $entry_name, $default_value) {
			$string = '<textarea class="'.$field['size'].'-text" name="'.$entry_name.'" id="'.$field['name'].'" '.$field['status'].'>'.htmlspecialchars($default_value).'</textarea>';
			return ($string);
		} // End of display_textarea

		function display_select($field, $entry_name, $default_value) {
			$string = '<select name="'.$entry_name.'" id="'.$field['name'].'" >';
			foreach ($field['options'] as $key => $value) {
				$selected = ($default_value==$key?'selected':'');
				$string .= '<option value="'.$key.'" '.$selected.'>'.($value==''?'':__($value, $this->textdomain)).'</option>';
			}
			$string .= '</select>';
			return ($string);
		} // End of display_textarea

		function display_grid_select($field, $entry_name, $default_value) {
			if (! isset($field['options']['header']) || sizeof($field['options']['header']) == 0 ||
				! isset($field['options']['list'])   || sizeof($field['options']['list'])   == 0) {
				$string = '<p>'.__('No data available', $this->textdomain).'</p>';
			}
			else {
				$string = '<fieldset><legend class="screen-reader-text">'.__($field['label'], $this->textdomain).'</legend><table class="eg-forms"><thead><tr>';
				foreach ($field['options']['header'] as $item) {
					$string .= '<th>'.__($item, $this->textdomain).'</th>';
				}
				$string .= '</tr></thead><tbody>';
				foreach ($field['options']['list'] as $item) {
					$string .= '<tr><td>'.
						'<input type="text" value="'.$item['value'].'" disabled /></td><td>'.
						'<label for="'.$entry_name.'['.$item['value'].']">'.
						'<select name="'.$entry_name.'['.$item['value'].']" id="'.$field['name'].'['.$item['value'].']" >';
					foreach ($item['select'] as $key => $value) {
						if (sizeof($default_value)>0 && 
							isset($default_value[$item['value']]) && 
							$key == $default_value[$item['value']]) $selected = 'selected';
						else $selected = '';
						$string .= '<option value="'.$key.'" '.$selected.'>'.__($value, $this->textdomain).'</option>';
					}
					$string .=	'</select></label></td></tr>';
				}
				$string .= '</tbody></table></fieldset>';
			}
			return ($string);
		} // End of display_grid_select

		function display_field($field_id, $defaults) {
			$field = wp_parse_args($this->form['fields'][$field_id], $this->field_defaults);

			$entry_name = $this->options_entry.'['.$field['name'].']';
			$default_value = $defaults[$field['name']];
			$single_chk = ( in_array($field['type'], array('checkbox', 'radio')) && !is_array($field['options']));

			$string= '<tr valign="top">'.
					'<th scope="row">'.
					($single_chk?__($field['label'], $this->textdomain):'<label for="'.$field['name'].'">'.__($field['label'], $this->textdomain).'</label>').
					'</th>'.
					'<td>'.
					($single_chk || $field['before']==''?'':__($field['before'], $this->textdomain)).
					call_user_func(array(&$this, 'display_'.$field['type']), $field, $entry_name, $default_value).
					($single_chk || $field['after']==''?'':__($field['after'], $this->textdomain)).
					($single_chk || $field['desc']==''?'':'<br />').
					($field['desc']==''?'':'<span class="description">'.__($field['desc'], $this->textdomain)).'</span>';
			$string.='</td></tr>';
			return ($string);
		} // End of display_field

		function display_section($section_id, $pos, $defaults) {

			$section = wp_parse_args($this->form['sections'][$section_id], $this->section_defaults);
		?>
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><?php _e($section['title'], $this->textdomain); ?></h3>

				<div class="inside">
				<?php echo ($section['header']==''?'':'<p>'.__($section['header'], $this->textdomain).'</p>'); ?>
				<table class="form-table">
		<?php
				foreach ($this->form['fields'] as $field_id => $field) {
					if ($field['section'] == $section_id) 
						echo $this->display_field($field_id, $defaults);
				}
		?>
				</table>
				<?php echo ($section['footer']==''?'':'<p>'.__($section['footer'], $this->textdomain).'</p>'); ?>
				<?php submit_button(); ?>
				<div class="clear"></div>
				</div>
			</div>
		<?php
		} // End of display_section


		function display($defaults) {
			if (! $this->form_only) {
		?>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2><?php _e($this->form['title'], $this->textdomain); ?></h2>
				<div id="poststuff" class="metabox-holder <?php echo (has_action('eg_form_sidebar')?'has-right-sidebar':''); ?>" >
		<?php
			if (has_action('eg_form_sidebar')) {
		?>
					<div id="side-info-column" class="inner-sidebar">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php do_action('eg_form_sidebar'); ?>
						</div>
					</div>
		<?php
			} // End of sidebar_callback
		?>					
					<div id="post-body" <?php echo (has_action('eg_form_sidebar')?'class="has-sidebar"':''); ?>>
						<div id="post-body-content" <?php echo (has_action('eg_form_sidebar')?'class="has-sidebar-content"':''); ?>>
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">
		<?php
			}  // End of ! form_only
		?>
							<form method="post"action="options.php" >
								<?php settings_fields($this->options_group); ?>
								<?php echo ($this->form['header']==''?'':'<p>'.__($this->form['header'], $this->textdomain).'</p>'); ?>
					<?php
								$number = 0;
								foreach ($this->form['sections'] as $section_id => $section) {
									if ($number == 0) $pos = 'first';
									else if ($number == sizeof($this->form['sections'])) $pos = 'last';
									else $post = number;
									$this->display_section($section_id, $pos, $defaults);
								}

								echo ($this->form['footer']==''?'':'<p>'.__($this->form['footer'], $this->textdomain).'</p>');
					?>
							</form>
							</div>
			<?php
			if (! $this->form_only) {
			?>
						</div>
					</div>
					<br class="clear" />
				</div>
			</div>
		<?php
			} // End of ! form_only
		} // End of display
	} // End of class
} // End of class_exists

?>