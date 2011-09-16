<?php
/*
Plugin Name: EG-Archives
Plugin URI: http://www.emmanuelgeorjon.com/en/plugin-eg-archives-1745
Description: Enhanced archive widget.
Version: 2.0.0
Author: Emmanuel GEORJON
Author URI: http://www.emmanuelgeorjon.com/
*/

/*  Copyright 2009-2011  Emmanuel GEORJON  (email : blog@emmanuelgeorjon.com)

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

define('EGARW_TEXTDOMAIN',  		'eg-archives' );
define('EG_ARCHIVES_WIDGET_ID',   	'eg_archives' );
define('EG_ARCHIVES_STYLESHEET',  	'eg-archives.css' );
define('EGARW_OPTIONS', 			'EG-Archives-Options');
define('EGARW_VERSION', 			'2.0.0');
define('EGARW_COREFILE',			__FILE__);


$EGARW_DEFAULT_OPTIONS = array(
	'load_css' 					=> 1,
	'uninstall_del_options'		=> 0,
	'disable_default_widget'	=> 0
);

$EG_ARCHIVE_FIELDS = array(
		'title'    => array( 'type' => 'ftext',  'label' => 'Title'),
		'type' 	   => array( 'type' => 'select', 'label' => 'Group by',
			'list' => array('yearly'      => 'Yearly',
							'monthly'     => 'Monthly',
							'mixed'	      => 'Yearly/Monthly',
							'mixed_month' => 'Yearly/Monthly with only month displayed',
							'weekly'      => 'Weekly',
							'daily'       => 'Daily',
							'postbypost'  => 'Post by post')),
		'pivot'    => array('type' => 'numeric', 'label' => 'Pivot year'),
		'limit'    => array('type' => 'numeric', 'label' => 'Limit'),
		'format'   => array('type' => 'select',  'label' => 'Format',
			'list' => array('html' => 'Html list', 'option' => 'Dropdown list')),
		'show_post_count' => array('type' => 'select','label' => 'Show post count',	'list' => array('0' => 'No', '1' => 'Yes')),
		'columns'  => array( 'type' => 'select', 'label' => 'Number of columns', 'list' => array( 1 => '1', 2 => '2'))
);

$EG_ARCHIVE_DEFAULT_VALUES = array(
		'title' 		  => 'Archives',
		'type' 			  => 'mixed',
		'pivot' 		  => date('Y'),
		'limit'			  => 0,
		'format' 		  => 'html',
		'show_post_count' => 0,
		'columns'		  => 1
);

/**
 * eg_get_archives
 *
 * Display or return archive links based on type and format.
 *
 * Arguments are the same than the original wp_get_archives function
 * Just add a parameter 'pivot', and a type = 'mixed'
 *
 * @package EG-Archives
 *
 * @param  string|array $args
 * @return string		archives list
 *
 */
function eg_get_archives($args = '') {
	global $wpdb, $wp_locale;
	global $EG_ARCHIVE_DEFAULT_VALUES;

	// ---- Analyze and extract parameters ----
	$defaults = array_merge( $EG_ARCHIVE_DEFAULT_VALUES,
				array( 'before' => '', 'after' => '', 'echo' => 1)
		);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$output = '';
	// Mixed mode specified ?
	if ($type != 'mixed' && $type != 'mixed_month') {

		// No => call the standard wordpress function wp_get_archives
		$r['echo'] = 0;
		if ($r['limit'] == 0) unset($r['limit']);

		$output = wp_get_archives($r);
	} // End of standard type
	else {
	
		// Begin of year/month archive list
		if ($limit == 0) $limit = '';
		if ( '' != $limit ) {
			$limit = absint($limit);
			$limit = ' LIMIT '.$limit;
		}
		$query = "SELECT DISTINCT YEAR(post_date) AS year, MONTH(post_date) AS month, count(ID) as posts FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC $limit";

		// Manage cache: keep the result of the query
		$key = md5($query);
		$cache = wp_cache_get( 'eg_get_archives' , 'eg_archives');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults  = $wpdb->get_results($query);
			$cache[$key] = $arcresults;
			wp_cache_add( 'eg_get_archives', $cache, 'eg_archives' );
		} else {
			$arcresults = $cache[$key];
		}
		// Build the archive list
		if ( $arcresults ) {

			foreach ( (array) $arcresults as $arcresult ) {
				if (! isset($year_counts[$arcresult->year])) $year_counts[$arcresult->year] = 0;
				$year_counts[$arcresult->year] += $arcresult->posts;
			}

			$afterafter   = $after;
			$current_year = '';
			foreach ( (array) $arcresults as $arcresult ) {
				if ($arcresult->year < $pivot) {
					// Display list yearly
					if ($current_year != $arcresult->year) {
						$url  = get_year_link($arcresult->year);
						$text = sprintf('%d', $arcresult->year);
						if ($show_post_count)
							$after = '&nbsp;('.$year_counts[$arcresult->year].')' . $afterafter;
						$output       .= get_archives_link($url, $text, $format, $before, $after);
						$current_year  = $arcresult->year;
					}
				}
				else {
					// Display list monthly
					$url = get_month_link( $arcresult->year, $arcresult->month );
					if ($type == 'mixed_month')
						$text = sprintf(__('%1$s'), $wp_locale->get_month($arcresult->month));
					else
						$text = sprintf(__('%1$s %2$d'), $wp_locale->get_month($arcresult->month), $arcresult->year);
					if ( $show_post_count )
						$after = '&nbsp;('.$arcresult->posts.')' . $afterafter;
					$output .= get_archives_link($url, $text, $format, $before, $after);
				}
			}
		}
	}
	if ($columns > 1 && $format == 'html') {
		// preg_match_all('/(<li \w*>)(.*)(<\/li>)/ismxU', $output, $item_list);
		$item_list   = explode('</li>', $output);
		$item_number = sizeof($item_list)-1;
		$middle      = ceil($item_number / 2);
		$number      = 1;
		$output      = '';
		foreach ($item_list as $item) {
			if (trim($item)!='') {
				$output .= $item.'</li>';
				if ($number++ >= $middle) {
					$number = 0;
					$output .= "\n".'</ul><ul>';
				} // new columns
			} // if item is not empty
		} // For each
	} // if columns>1

	// Display or return list
	if ( $echo )
		echo $output;
	else
		return $output;
} // End of eg_get_archives

if (!class_exists('EG_Widget_202')) {
	require_once('lib/eg-widgets280.inc.php');
}

Class EG_Archives_Widget extends EG_Widget_202 {

	function EG_Archives_Widget() {
		global $EG_ARCHIVE_FIELDS;
		global $EG_ARCHIVE_DEFAULT_VALUES;

		$widget_ops = array('classname' => 'widget_archives', 'description' => 'Advanced archives widget' );
		$this->WP_Widget(EG_ARCHIVES_WIDGET_ID, 'EG-Archives', $widget_ops);
		$this->set_options(EGARW_TEXTDOMAIN, EGARW_COREFILE, 900 );
		$this->set_form($EG_ARCHIVE_FIELDS, $EG_ARCHIVE_DEFAULT_VALUES, TRUE );
	} // End of constructor

	function widget($args, $instance) {

		extract($args, EXTR_SKIP);
		$values = wp_parse_args( (array) $instance, $this->default_values );
		$output= '';

		if ($this->is_visible($values)) {

			$values['echo'] = 0;
			$output = eg_get_archives($values);

			if ($output != '') {
				if ($values['format'] == 'html') {
					$output = ($values['columns']<2?'':'<div class="eg-archives-columns">').'<ul>'.$output.'</ul>'.($values['columns']<2?'':'</div>');
				}
				else {
					$output = '<select name="eg-archive-dropdown" onChange="document.location.href=this.options[this.selectedIndex].value;">'.
						'<option value="">'.__('Select entry', $this->textdomain).'</option>'.
						$output.
						'</select>';
				}

				echo $before_widget.
					($values['title']!= ''?$before_title.__($values['title'], $this->textdomain).$after_title:'').
					$output.
					$after_widget;
			} // End of $output != ''
		} // End of is_visible

	} // End of function widget

} // End of class EG_Archive_Widget

/**
 * eg_archives_uninstall
 *
 * Delete option of the plugin during uninstallation
 *
 * @package EG-Archives
 *
 * @param 	none
 * @return	none
 */
function eg_archives_uninstall() {
	$options = get_option(EGARW_OPTIONS);
	if ( isset($options) && $options['uninstall_del_options']) {
		delete_option(EGARW_OPTIONS);
	}
} // End of eg_archives_uninstall


function eg_archives_unregister_default_widget() {
	unregister_widget('WP_Widget_Archives');
}

function eg_archives_init() {

	$plugin_options = get_option(EGARW_OPTIONS);
	if ($plugin_options['disable_default_widget'])
		add_action('widgets_init', 'eg_archives_unregister_default_widget');
	register_widget('EG_Archives_Widget');

	if (! is_admin()) {

		$plugin_url  = plugin_dir_url(__FILE__);
		$load_css    = (isset($plugin_options['load_css'])?$plugin_options['load_css']:1);

		if (defined('EG_ARCHIVES_STYLESHEET') && $load_css) {
			if (@file_exists(TEMPLATEPATH.'/'.EG_ARCHIVES_STYLESHEET)) {
				wp_enqueue_style( EG_ARCHIVES_WIDGET_ID.'_stylesheet', get_stylesheet_directory_uri().'/'.EG_ARCHIVES_STYLESHEET);
			}
			else {
				wp_enqueue_style( EG_ARCHIVES_WIDGET_ID.'_stylesheet', $plugin_url.EG_ARCHIVES_STYLESHEET);
			}
		} // End of if load_css
	} // End of not is_admin
} // End of eg_archives_widgets_init

add_action('init', 'eg_archives_init', 1);
register_uninstall_hook (__FILE__, 'eg_archives_uninstall' );

if (is_admin()) {
	require_once('eg-archives-admin.inc.php');
}

?>