<?php
/*
Package Name: EG-Plugin
Package URI:
Description: Class for WordPress plugins
Version: 1.2.0
Author: Emmanuel GEORJON
Author URI: http://www.emmanuelgeorjon.com/
*/

/*
    Copyright 2009-2010 Emmanuel GEORJON  (email : blog@emmanuelgeorjon.com)

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

if (!function_exists('load_eg_form')) {
	function load_eg_form($pages_list) {
		if (!is_array($pages_list)) $pages_list = array($pages_list);
		return ( (isset($_GET['page']) && in_array($_GET['page'], $pages_list)) || (isset($_POST['action']) && $_POST['action']=='update'));
	}
}

if (!class_exists('EG_Plugin_120')) {

	/**
	  * Class EG_Plugin
	  *
	  * Provide some functions to create a WordPress plugin
	  *
	 */
	Class EG_Plugin_120 {

		var $plugin_name;
		var $plugin_version;
		var $plugin_path;
		var $plugin_url;
		var $plugin_corefile;

		var $stylesheet;
		var $admin_stylesheet;

		var $options_entry;
		var $options;
		var $default_options;

		var $tinyMCE_buttons;
		var $textdomain = '';

		var $wp_version_min = '3.0';
		var $wp_version_max;
		var $wpmu_version_min = '3.0';
		var $wpmu_version_max;

		var $php_version_min = '4.3.0';
		var $php_extensions;
		var $php_options;
		var $requirements_error_msg = '';

		var $pages;
		var $options_page_id;
		var $hook;

		var $debug_msg     = FALSE;
		var $debug_file    = '';
		var $update_notice = '';

		/**
		  * Class contructor for PHP 4 compatibility
		  *
		  * @package EG-Plugins
		  * @return object
		  *
		  */
		function EG_Plugin_120($plugin_name, $version, $core_file, $options_entry, $default_options=FALSE) {

			register_shutdown_function(array(&$this, '__destruct'));
			$this->__construct($plugin_name, $version, $core_file, $options_entry, $default_options);
		} // End of EG_Plugin_121

		/**
		  * Class contructor
		  * Define the plugin url and path. Declare action INIT and HEAD.
		  *
		  * @package EG-Plugins
		  * @return object
		  */
		function __construct($plugin_name, $version, $core_file, $options_entry, $default_options=FALSE) {

			$this->plugin_name     = $plugin_name;
			$this->plugin_version  = $version;
			$this->options_entry   = $options_entry;
			$this->default_options = $default_options;

			/* Define the plugin path */
			$plugin_base_path	   = basename( dirname($core_file) );
			$this->plugin_path 	   = trailingslashit(str_replace('\\', '/', WP_PLUGIN_DIR.'/'.$plugin_base_path));
			$this->plugin_url  	   = plugin_dir_url($core_file);
			$this->plugin_corefile = $core_file;  /* $this->plugin_path.basename($core_file); */
		} // End of __construct

		/**
		 * Class destructor
		 *
		 * @package EG-Plugins
		 * @return boolean true
		 */
		function __destruct() {
			// Nothing
		} // End of __destruct

		/**
		 * Load
		 *
		 * @package EG-Plugins
		 * @return none
		 */
		function load() {

			add_action('plugins_loaded', array(&$this, 'plugins_loaded') );
			add_action('init',           array(&$this, 'init')           );
			add_action('wp_logout',      array(&$this, 'wp_logout')      );

			if (is_admin()) {

				register_activation_hook( $this->plugin_corefile, array(&$this, 'install_upgrade') );
				register_deactivation_hook( $this->plugin_corefile, array(&$this, 'desactivation') );

				add_action('admin_menu',   array( &$this, 'admin_menu')   );
				add_action('admin_init',   array( &$this, 'admin_init')   );
				add_action('admin_header', array( &$this, 'admin_head')   );
				add_action('admin_footer', array( &$this, 'admin_footer') );
				add_action('in_plugin_update_message-' . plugin_basename($this->plugin_corefile),
							array( &$this, 'plugin_update_notice') );
			}
			else {
				add_action('wp_head',   array( &$this, 'head')  );
				add_action('wp_footer', array( &$this, 'footer'));
			}

		} // End of load

		function wp_logout() {
			// Nothing here.
		} // End of wp_logout

		/**
		 * set_stylesheet
		 *
		 * Set stylesheet for public and admin interface
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$stylesheet			stylesheet for the public side
		 * @param	string	$admin_stylesheet	stylesheet for the admin side
		 * @return none
		 */
		function set_stylesheets($stylesheet=FALSE, $admin_stylesheet=FALSE) {
			$this->stylesheet       = $stylesheet;
			$this->admin_stylesheet = $admin_stylesheet;
		} // End of set_stylesheets

		/**
		 * set_update_notice
		 *
		 *
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$msg	Message to add.
		 * @return none
		 */
		function set_update_notice($msg) {
			$this->update_notice = $msg;
		} // End of set_update_notice


		/**
		 * set_debug_mode
		 *
		 * Set debug mode: display messages or not
		 *
		 * @package EG-Plugins
		 *
		 * @param	boolean	$debug_mode		TRUE to display message, FALSE to hide message
		 * @param	string	$debug_file		name of the file where store messages
		 * @return none
		 */
		function set_debug_mode($debug_mode = FALSE, $debug_file='') {
			$this->debug_msg = $debug_mode;
			if ($debug_file != '')
				$this->debug_file = trailingslashit(dirname($this->plugin_corefile)).$debug_file;
		}

		/**
		 * set_textdomain
		 *
		 * Set internationalization parameters
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$textdomain		text domain
		 * @return none
		 */
		function set_textdomain($textdomain='') {
			$this->textdomain = $textdomain;
		} // End of set_textdomain

		/**
		 * set_wp_versions
		 *
		 * Set compliance parameters with WordPress and WordPress MU
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$wp_version_min		minimum version for WordPress
		 * @param	string	$wp_version_max	maximum version for WordPress
		 * @param	string	$wpmu_version_min	minimum version for WordPress MU
		 * @param	string	$wpmu_version_max	maximum version for WordPress MU
		 * @return none
		 */
		function set_wp_versions($wp_version_min, $wp_version_max='', $wpmu_version_min='', $wpmu_version_max='') {
			$this->wp_version_min	= $wp_version_min;
			$this->wp_version_max	= $wp_version_max;
			$this->wpmu_version_min	= $wpmu_version_min;
			$this->wpmu_version_max	= $wpmu_version_max;
		} // End of set_wp_versions

		/**
		 * set_php_version
		 *
		 * Set compliance parameters with WordPress and WordPress MU
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$min_version		minimum version for PHP
		 * @return 	none
		 */
		function set_php_version($version_min, $options_list=FALSE, $extensions_list=FALSE ) {
			$this->php_version_min = $version_min;
			$this->php_options     = $options_list;
			$this->php_extensions  = $extensions_list;
		} // End of set_php_version

		/**
		 * add_tinyMCE_button
		 *
		 * Add a TinyMCE button
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$button_name	Name of the button
		 * @return none
		 */
		function add_tinyMCE_button($button_name, $tinymce_plugin_path, $js_file_name='editor_plugin.js') {
			$index = sizeof($this->tinyMCE_buttons);
			$this->tinyMCE_buttons[$index]->name 	= $button_name;
			$this->tinyMCE_buttons[$index]->js_file = $js_file_name;
			$this->tinyMCE_buttons[$index]->path 	= $tinymce_plugin_path;
		} // End of add_tinyMCE_button

		/**
		 * add_page
		 *
		 * Add a menu and a page
		 *
		 * @package EG-Plugins
		 *
		 * @param
		 * @param
		 * @return none
		 */
		function add_page($page_id, $page_type, $page_title, $menu_title, $access_level,
						  $display_callback, $option_link=FALSE,
						 $load_callback=FALSE, $load_scripts=FALSE) {

			$index 									= sizeof($this->pages);
			$this->pages[$index]->type              = $page_type;
			$this->pages[$index]->page_title        = __($page_title, $this->textdomain);
			$this->pages[$index]->menu_title        = __($menu_title, $this->textdomain);
			$this->pages[$index]->access_level      = $access_level;
			$this->pages[$index]->display_callback  = $display_callback;
			$this->pages[$index]->page_id           = $page_id;
			$this->pages[$index]->load_callback     = $load_callback;
			$this->pages[$index]->load_scripts      = $load_scripts;
			if ($option_link) $this->options_page_id = $page_id;

			return ($index);
		} // End of add_page

		/**
		 * admin_init
		 *
		 * Perform init at admin level (add TinyMCE button ...)
		 *
		 * @package EG-Plugins
		 *
		 * @param	none
		 * @return	none
		 */
		function admin_init() {

			/* --- Get Plugin options --- */
			if (! $this->options) $this->options = get_option($this->options_entry);

			// Add only in Rich Editor mode
			if ( isset($this->tinyMCE_buttons) &&
				 get_user_option('rich_editing') == 'true' ) {
			// && current_user_can('edit_posts') && current_user_can('edit_pages') )  {

				add_filter('tiny_mce_version', 		array(&$this, 'tiny_mce_version'   ));
				add_filter('mce_external_plugins', 	array(&$this, 'add_tinymce_plugin' ));
				add_filter('mce_buttons', 			array(&$this, 'register_button'    ));
			}
		} // End of admin_init

		/**
		 * init
		 *
		 * Perform init
		 *
		 * @package EG-Plugins
		 *
		 * @param	none
		 * @return	none
		 */
		function init() {

			/* --- Get Plugin options --- */
			if (! $this->options) $this->options = get_option($this->options_entry);

			/* --- Load translations file --- */
			load_plugin_textdomain( $this->textdomain, FALSE, basename($this->plugin_path).'/lang');

			$this->include_stylesheets();
		} // End of init

		/**
		 * plugins_loaded
		 *
		 * Call by plugins_loaded WP action
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function plugins_loaded () {

			/* --- Check if WP version is correct --- */
			$this->check_requirements(FALSE);

			/* --- Get Plugin options --- */
			if (! $this->options) $this->options = get_option($this->options_entry);
		} // End of plugins_loaded

		/**
		 * include_stylesheets
		 *
		 * Register plugin style sheets
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function include_stylesheets() {

			if (is_admin()) {
				if ($this->admin_stylesheet !== FALSE &&
					$this->admin_stylesheet != '' &&
					@file_exists($this->plugin_path.$this->admin_stylesheet)) {
					wp_enqueue_style( $this->plugin_name.'_admin_stylesheet', $this->plugin_url.$this->admin_stylesheet);
				}
			}
			else {
				if (isset($this->options['load_css'])) $load_css = $this->options['load_css'];
				else $load_css = 1;

				if ($this->stylesheet && $load_css) {

					if (@file_exists(TEMPLATEPATH.'/'.$this->stylesheet)) {
						wp_enqueue_style( $this->plugin_name.'_stylesheet', get_stylesheet_directory_uri().'/'.$this->stylesheet);
					}
					else {
						if (@file_exists($this->plugin_path.$this->stylesheet))
							wp_enqueue_style( $this->plugin_name.'_stylesheet', $this->plugin_url.$this->stylesheet);
					}
				}
			} /* else is_admin */
		} // End of include_stylesheets

		/**
		 * Implement Head action
		 *
		 * Add links to the style sheet
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function head() {

		} // End of head

		/**
		 * admin_head
		 *
		 * Implement Head action
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function admin_head() {

		} // End of admin_head

		/**
		 * Implement Footer action
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function footer() {
			// Nothing here
		} // End of footer

		/**
		 * admin_footer
		 *
		 * Implement footer admin  actions
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function admin_footer() {

			// Nothing here
		} // End of admin_footer

		/**
		 * Filter_plugin_actions
		 *
		 * Add a "settings" link to access to the option page from the plugin list
		 *
		 * @package EG-Plugins
		 * @param string	$link	list of existings links
		 * @return none
		 */
		function filter_plugin_actions($links) {

			// TODO: use admin_url
			$settings_link = '<a href="'.admin_url('options-general.php?page='.$this->options_page_id).'">' . __('Settings') . '</a>';
			array_unshift( $links, $settings_link );

			return $links;
		} // End of filter_plugin_actions

		/**
		 * register_button()
		 * Insert button in wordpress post editor
		 *
		 * @package EG-Plugins
		 * @param array	$buttons	list of buttons
		 * @return array 	$buttons
		 */
		function register_button($buttons) {

			foreach ($this->tinyMCE_buttons as $value) {
				array_push($buttons, $value->name );
			}
			return $buttons;
		}

		/**
		 * add_tinymce_plugin()
		 * Load the TinyMCE plugin : editor_plugin.js
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return $plugin_array
		 */
		function add_tinymce_plugin($plugin_array) {

			foreach ($this->tinyMCE_buttons as $value) {
				$plugin_array[$value->name] = $this->plugin_url.$value->path.'/'.$value->js_file;
			}
			return $plugin_array;
		}

		function tiny_mce_version($version) {
			return ++$version;
		}

		/**
		 * options_reset
		 *
		 * Reset options to defaults
		 *
		 * @param 	none
		 * @return 	none
		 */
		function options_reset() {

			if (isset($this->default_options)) {
				$this->options            = $this->default_options;
				$this->options['version'] = $this->plugin_version;
				update_option($this->options_entry, $this->options);
			}
		} /* End of options_reset */

		/**
		 * upgrade
		 *
		 * Create or update plugin environment (options, database, files ...)
		 *
		 * @package EG-Plugins
		 * @param 	none
		 * @return  none
		 */
		 /*
			To use this function:
				function insta_upgrade() {
					$current_version = parent::install_upgrade();

					put here your upgrade or install features
				}
		 */
		function install_upgrade() {

			if (! $this->options)
				$this->options = get_option($this->options_entry);

			if ($this->options === FALSE) {

				$previous_options = FALSE;
				// Create option from the defaults
				if ($this->default_options !== FALSE) {
					$this->options = $this->default_options;
				}
				$this->options['version'] = $this->plugin_version;
				add_option($this->options_entry, $this->options);
			} // End of options empty (first install)
			else {

				$previous_options = $this->options;
				$current_version = (isset($this->options['version'])? $this->options['version']: '0.0.0');

				// Plugin previously installed. Check the version and update options
				if (version_compare($current_version, $this->plugin_version, '<')) {
					if ($this->default_options === FALSE) {
						$new_options = $this->options;
					}
					else {
						$new_options = array();
						foreach ($this->default_options as $key => $value) {
							if (isset($this->options[$key])) $new_options[$key] = $this->options[$key];
							else $new_options[$key] = $value;
						}
					}
					$new_options['version'] = $this->plugin_version;
					update_option($this->options_entry, $new_options);
					$this->options = $new_options;
				} // End of version compare
			} // End of options not empty (update)

			return ($previous_options);

		} // End of install_upgrade

		function desactivation() {
			// Nothing

		} // End of desactivation

		/**
		  * get_requirements_msg
		  *
		  * Build requirements string
		  *
		  * @package 	EG-Plugins
		  * @param 		string	name of the software (for example: WP, WP MU, PHP, MySQL)
		  * @param	 	int		version minimum
		  * @param 		int 	version maximum
		  * @return		string	the message
		  */
		function get_requirements_msg($soft, $min, $max=FALSE) {
			$string = '<strong>'.$this->plugin_name.'</strong>';
			if (! $min) {
				$string .= __(' cannot run with ', $this->textdomain).$soft.'.';
			}
			else {
				$string .= __(' requires ', $this->textdomain).
			          $soft.' '.$min.(!$max?__(' and later.', $this->textdomain):'').
					  ($max?__(' to ', $this->textdomain).$max:'').'.';
			}
			return ($string);
		}

		/**
		 * check_php_requirements
		 *
		 * Check PHP version required to run the plugin
		 *
		 * @package EG-Plugin
		 *
		 * @param boolean	display_msg		display error message or not
		 * @return boolean					TRUE if required PHP versio, FALSE if not
		 */
		function check_php_requirements() {
			$value = TRUE;

			if (isset($this->php_version_min) && version_compare(phpversion(), $this->php_version_min, '<')) {
				$value = FALSE;
				$this->requirements_error_msg .= ($this->requirements_error_msg==''?'':'<br />').$this->get_requirements_msg('PHP', $this->php_version_min);
			}
			return ($value);
		} /* End of check_php_requirements */

		/**
		 * check_php_exts
		 *
		 * Check PHP extensions
		 *
		 * @package EG-Plugin
		 *
		 * @param boolean	display_msg			display error message or not
		 * @param array		extensions_list		list of required extensions
		 * @return boolean						TRUE if required PHP versio, FALSE if not
		 */
		function check_php_exts($extensions_list=FALSE) {

			$value = TRUE;
			if (!$extensions_list)
				$extensions_list = $this->php_extensions;

			if ($extensions_list) {
				$loaded_exts = get_loaded_extensions();

				if (!is_array($extensions_list))
					$extensions_list = array($extensions_list);

				$available_exts = array_intersect($extensions_list, $loaded_exts);
				$missing_exts   = array_diff($extensions_list, $available_exts);
				$value          = (sizeof($missing_exts)==0);
			}
			if (!$value) {
				$this->requirements_error_msg .= ($this->requirements_error_msg==''?'':'<br />').sprintf(__('The plugin <strong>%1s</strong> requires the following PHP extensions: %2s',$this->textdomain), $this->plugin_name, implode(', ',$missing_exts));
			}
			return ($value);
		} /* End of check_php_exts */

		/**
		 * check_php_options
		 *
		 * Check PHP options
		 *
		 * @package EG-Plugin
		 *
		 * @param 	boolean		display_msg			display error message or not
		 * @param 	array		extensions_list		list of required extensions
		 * @return 	boolean							TRUE if required PHP versio, FALSE if not
		 */
		function check_php_options($options_list=FALSE) {

			$returned_code = TRUE;
			if (!$options_list) $options_list = $this->php_options;

			if ($options_list) {
				if (!is_array($options_list))
					$options_list = array($options_list);

				foreach ($options_list as $value) {
					if (! ini_get($value)) {
						$missing_options[] = $value;
						$returned_code = FALSE;
					}
				}
			}
			if (!$returned_code) {
				$this->requirements_error_msg .= ($this->requirements_error_msg==''?'':'<br />').sprintf(__('The plugin <strong>%1s</strong> requires the following PHP options: %2s.', $this->textdomain), $this->plugin_name, implode(', ', $missing_options));
			}
			return ($returned_code);
		} /* End of check_php_options */

		/**
		 * check_wp_requirements
		 *
		 * Check WordPress version required to run the plugin
		 *
		 * @package EG-Plugin
		 *
		 * @param boolean	display_msg		display error message or not
		 * @return boolean					TRUE if required WordPress version, FALSE if not
		 */
		function check_wp_requirements() {
			global $wp_version, $wpmu_version;

			// Are we using WP MU ?
			$is_wpmu = (isset($wpmu_version) || (strpos($wp_version, 'wordpress-mu') !== FALSE));

			$value = FALSE;
			if ($is_wpmu) {
				if ($this->wpmu_version_min) {
					$value = version_compare($wpmu_version, $this->wpmu_version_min, '>=');
					if ($this->wpmu_version_max) $value = $value && version_compare($wpmu_version, $this->wpmu_version_max, '<=');
				}
			}
			else {
				$value = version_compare($wp_version, $this->wp_version_min, '>=');
				if ($this->wp_version_max) $value = $value && version_compare($wp_version, $this->wp_version_max, '<=');
			}

			// if $value isn't empty, we have a message to display
			if (!$value) {
				if ($this->requirements_error_msg!='') $this->requirements_error_msg .= '<br />';
				if ($is_wpmu)
					$this->requirements_error_msg .= $this->get_requirements_msg('WordPress MU', $this->wpmu_version_min, $this->wpmu_version_max);
				else
					$this->requirements_error_msg .= $this->get_requirements_msg('WordPress', $this->wp_version_min, $this->wp_version_max);
			}
			return ($value);
		}

		/**
		  * Check_requirements
		  *
		  * Check if the wordpress version meets the plugin requirements.
		  *
		  * @package EG-Plugins
		  * @param 		none
		  * @return 	none
		  */
		function check_requirements($display_msg=TRUE) {

			$value = TRUE;
			if (is_admin() && ($display_msg || strpos($_SERVER['REQUEST_URI'], 'plugins.php')!==FALSE)) {

				$value = $this->check_wp_requirements();
				$value = $value & $this->check_php_requirements();
				$value = $value & $this->check_php_options();
				$value = $value & $this->check_php_exts();

				if (!$value) {
					echo '<div id="message" class="error fade"><p><strong>'.$this->requirements_error_msg.'</strong></p></div>';
				}
			}
			return ($value);
		}

		/**
		  * add_plugin_pages
		  *
		  * Add an option page menu
		  *
		  * @package EG-Plugins
		  * @param 		none
		  * @return 		none
		 */
		function admin_menu() {

			$page_list = array ( 'posts'	=> 'add_posts_page',
								 'options'	=> 'add_options_page',
								 'settings'	=> 'add_options_page',
								 'tools'	=> 'add_management_page',
								 'theme'	=> 'add_theme_page',
								 'users'	=> 'add_users_page',
								 'media'	=> 'add_media_page',
								 'links'	=> 'add_links_page',
								 'pages'	=> 'add_pages_page');

			// Add a new submenu under Options:
			if (isset($this->pages) && sizeof($this->pages)>0) {
				foreach ($this->pages as $id => $page) {

					$hook = call_user_func($page_list[$page->type],
									__($page->page_title, $this->textdomain),
									__($page->menu_title, $this->textdomain),
									$page->access_level,
									$page->page_id,
									array(& $this, $page->display_callback));

					$this->hooks['display'][$page->display_callback][] = $hook;

					if ($page->load_callback !== FALSE) {
						add_action('load-'.$hook, array(&$this, $page->load_callback));
						$this->hooks['load'][$page->load_callback][] = $hook;
					}
					if ($page->load_scripts !== FALSE) {
						add_action('admin_print_scripts-'.$hook, array(&$this, $page->load_scripts));
						$this->hooks['scripts'][$page->load_scripts][] = $hook;
					}

					if ($this->options_page_id == $page->page_id) {
						add_filter( 'plugin_action_links_' . plugin_basename($this->plugin_corefile),
									array( &$this, 'filter_plugin_actions') );
					}

				} // End of foreach
				unset($this->pages);
			} // End of isset this->pages
		} // End of admin_menu

		/**
		  * display_message
		  *
		  * Display message at the top of page
		  *
		  * @package EG-Plugins
		  * @param 	string	$message	message to display
		  * @param 	string	$status	update, error
		  * @return 		none
		  */
		function display_message($message, $status ='') {
			if ( $message != '') {
			?>
				<div id="message" class="<?php echo ($status != '') ? $status :'updated'; ?> fade">
					<p><strong><?php echo $message; ?></strong></p>
				</div>
			<?php
			}
		} // End of display_message


		/**
		 * display_debug_info
		 *
		 * @package EG-Plugin
		 *
		 * @param	string	$msg	message to display
		 * @return 	none
		 */
		function display_debug_info($msg) {

			if ($this->debug_msg) {
				$debug_info = debug_backtrace();
				$output = date('d-M-Y H:i:s').' - '.$debug_info[1]['function'].' - '.$debug_info[2]['function'].' - '.$msg;

				if ($this->debug_file != '')
					file_put_contents($this->debug_file, $output."\n", FILE_APPEND);
				else
					echo $output.'<br />';
			}
		} // End of display_debug_info

		/**
		 * plugin_update_notice
		 *
		 * Display a specific message in the plugin update message.
		 *
		 * @package EG-Plugin
		 *
		 * @param	none
		 * @return 	none
		 */
		function plugin_update_notice() {
			if ($this->update_notice!= '')
				echo '<span class="spam">' .
						strip_tags( __($this->update_notice, $this->textdomain), '<br><a><b><i><span>' ) .
					'</span>';
		} // End of plugin_update_notice

		/**
		 * display_box
		 *
		 * Display a metabox-like in admin interface.
		 *
		 * @package EG-Plugin
		 *
		 * @param	$id			string	id / name of the div containing box
		 * @param	$title		string	title of the box
		 * @param	$content	string	content of the box
		 * @return 	none
		 */
		function display_box($id, $title, $content) {
		?>
			<div id="<?php echo $id; ?>" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class="hndle"><span><?php _e($title, $this->textdomain); ?></span></h3>
				<div class="inside">
					<?php _e($content, $this->textdomain); ?>
				</div>
			</div>
		<?php
		} // End of display_box

	} /* End of class */
} /* End of class_exists */

?>