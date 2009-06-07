<?php
/*
Plugin Name: EG-Archives
Plugin URI: http://www.emmanuelgeorjon.com/plugin-eg-archives-1745
Description: Enhanced archive widget.
Version: 1.00
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

require_once('lib/eg-widgets.inc.php');

define('EG_ARCHIVE_TEXTDOMAIN', 'eg_archives' );

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

	// ---- Analyze and extract parameters ----
	$defaults = array(
		'type'		=> 'mixed', 
		'limit'		=> '',
		'pivot'		=> date('Y'),
		'format'	=> 'html', 
		'before'	=> '',
		'after'		=> '', 
		'show_post_count' => false,
		'echo' 		=> 1
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$output = '';
	// Mixed mode specified ?
	if ($type != 'mixed') {
		// No => call the standard wordpress function wp_get_archives
		$r['echo'] = 0;
		$output = wp_get_archives($r);
	}
	else {
		// Yes => build the year/month archive list
		if ( '' != $limit ) {
			$limit = absint($limit);
			$limit = ' LIMIT '.$limit;
		}
	
		$query = "SELECT DISTINCT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC";

		// Manage cache: keep the result of the query
		$key = md5($query);
		$cache = wp_cache_get( 'wp_get_archives' , 'eg_archives');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_add( 'wp_get_archives', $cache, 'eg_archives' );
		} else {
			$arcresults = $cache[ $key ];
		}
		// Build the archive list
		if ( $arcresults ) {
			$afterafter = $after;
			$current_year= '';
			foreach ( (array) $arcresults as $arcresult ) {
				if ($arcresult->year < $pivot) {
					// Display list yearly
					if ($current_year != $arcresult->year) {
						$url = get_year_link($arcresult->year);
						$text = sprintf('%d', $arcresult->year);
						if ($show_post_count)
							$after = '&nbsp;('.$arcresult->posts.')' . $afterafter;
						$output .= get_archives_link($url, $text, $format, $before, $after);
						$current_year = $arcresult->year;					
					}
				}
				else {
					// Display list monthly
					$url = get_month_link( $arcresult->year, $arcresult->month );
					$text = sprintf(__('%1$s %2$d'), $wp_locale->get_month($arcresult->month), $arcresult->year);
					if ( $show_post_count )
						$after = '&nbsp;('.$arcresult->posts.')' . $afterafter;
					$output .= get_archives_link($url, $text, $format, $before, $after);
				}
			}
		}
	}
	// Display or return list
	if ( $echo )
		echo $output;
	else
		return $output;
}

if (! class_exists('EG_Archive_Widget')) {

	/**
	 * Class EG_Archive_Widget
	 *
	 * Implement an archive widget with the advanced archives function
	 *
	 */
	Class EG_Archive_Widget extends EG_Widget_100 {

		function display ($args) {
			global $wpdb, $wp_locale;

			if ( $this->is_visible() ) {

				$options = & $this->options;
				extract($args);

				echo $before_widget;
				if ($options['EG_archive_title'] != '') {
					echo $before_title . stripslashes( __($options['EG_archive_title'], $this->textdomain)) . $after_title;
				}

				$archive_list = eg_get_archives(array(
						'type'            => $options['EG_archive_type'],
						'limit'           => $options['EG_archive_limit'],
						'pivot'			  => $options['EG_archive_pivot'],
						'format'          => $options['EG_archive_format'],
						'before'          => $options['EG_archive_before'],
						'after'           => $options['EG_archive_after'],
						'show_post_count' => $options['EG_archive_show_post_count'],
						'echo'            => 0 )
					);

				switch ($options['EG_archive_format']) {
					case 'html':
						echo '<ul>'.
							$archive_list.
							'</ul>';
					break;

					case 'option':
						echo '<select name="eg-archive-dropdown" onChange="document.location.href=this.options[this.selectedIndex].value;">'.
							'<option value="">'.__('Select entry', $this->textdomain).'</option>'.
							$archive_list.
							'</select>';
					break;
				}
				echo $after_widget;
			}
		}
	}
}

$eg_archive = new EG_Archive_Widget('EG_archives',
									'EG Archives',
									'Advanced archives widget',
									'widget_archive',
									EG_ARCHIVE_TEXTDOMAIN,
									__FILE__,
									86400,
									FALSE,
									1,
									array(
										'EG_archive_title' => array(
											'type'    => 'ftext',
											'label'   => 'Title',
											'default' => 'Archive'
										),
										'EG_archive_type' => array(
											'type'    => 'select',
											'label'   => 'Group by',
											'default' => 'mixed',
											'list' => array(
													'yearly' 		=> 'Yearly', 
													'monthly' 		=> 'Monthly', 
													'mixed'			=> 'Yearly/Monthly', 
													'weekly' 		=> 'Weekly', 
													'daily' 		=> 'Daily', 
													'postbypost'	=> 'Post by post')
										),
										'EG_archive_pivot' => array(
											'type'    => 'numeric',
											'label'   => 'Pivot year',
											'default' => date('Y')
										),
										'EG_archive_limit' => array(
											'type'    => 'numeric',
											'label'   => 'Limit',
											'default' => ''
										),
										'EG_archive_format' => array(
											'type'    => 'select',
											'label'   => 'Format',
											'default' => 'html',
											'list'    => array('html' => 'Html list', 'option' => 'Dropdown list'),
										),
										'EG_archive_show_post_count' => array(
											'type'    => 'select',
											'label'   => 'Show post count',
											'default' => '0',
											'list'    => array('0' => 'No', '1' => 'Yes'),
										)
									)
							);
$eg_archive->load(TRUE);

?>