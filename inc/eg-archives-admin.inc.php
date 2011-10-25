<?php

if (! class_exists('EG_Plugin_122')) 
	require(dirname(EGARW_COREFILE).'/lib/eg-plugin.inc.php');

if (eg_detect_page('egarw_options')) {
	require(dirname(EGARW_COREFILE).'/lib/eg-forms.inc.php');
}

if (! class_exists('EG_Archives_Admin')) {

	/**
	 * Class EG_Archives_Admin
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Archives_Admin extends EG_Plugin_122 {

		function display_sidebar() {
			global $locale;
		
			$string = sprintf('<ul>'.
							  '<li><a href="http://wordpress.org/extend/plugins/eg-archives/">%s</a></li>'.
							  '<li><a href="http://wordpress.org/extend/plugins/eg-archives/faq">%s</a></li>'.
							  '<li><a href="http://wordpress.org/tags/eg-archives">%s</a></li>'.
							  '<li><a href="http://wordpress.org/extend/plugins/eg-archives/changelog/">%s</a></li>'.
							  '</ul>',
							__('Plugin\'s homepage', 		$this->textdomain),
							__('Frequently Asked Questions',$this->textdomain),
							__('Support forum', 			$this->textdomain),
							__('Last changes', 				$this->textdomain));
			$this->display_box('links', 'Links', $string);

			$string = '<p>'.__('This plugin required and requires many hours of work. If you use the plugin, and like it, feel free to show your appreciation to the author.', $this->textdomain).'</p>';
			
			$string .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">'.
						'<input type="hidden" name="cmd" value="_donations">'.
						'<input type="hidden" name="business" value="CPCKAJFRB5NNA">'.
						'<input type="hidden" name="lc" value="'.($locale=='fr_FR'?'FR':'US').'">'.
						'<input type="hidden" name="item_number" value="eg-archives">'.
						'<input type="hidden" name="currency_code" value="EUR">'.
						'<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHosted">'.
						'<input type="image" src="https://www.paypalobjects.com/'.($locale=='fr_FR'?'fr_FR':'en_US').'/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="'.__('PayPal - The safer, easier way to pay online!', $this->textdomain).'">'.
						'<img alt="" border="0" src="https://www.paypalobjects.com/'.($locale=='fr_FR'?'fr_FR':'en_US').'/i/scr/pixel.gif" width="1" height="1">'.
						'</form>';
			$this->display_box('paypal', 'Donate', $string);
		} // End of display_sidebar

		
		function admin_menu() {
			$this->add_page( array(
					'id' 				=> 'egarw_options',
					'display_callback'	=> 'options_page')
			);

			parent::admin_menu();
			
			if (class_exists('EG_Form_210')) {
				require($this->path.'inc/eg-archives-settings.inc.php');
			}		
		} // End of admin_menu
		
		/**
		 * options_page
		 *
		 * Display the options page
		 *
		 * @param 	none
		 * @return 	none
		 */
		function options_page() {
			if (isset($this->options_form))
				$this->options_form->display_page($this->options);
		} // End of options_page

	} // End of Class
} // End of if class_exists

$eg_archives_admn = new EG_Archives_Admin('EG-Archives',
											EGARW_VERSION ,
											EGARW_COREFILE,
											EGARW_TEXTDOMAIN,
											EGARW_OPTIONS,
											$EGARW_DEFAULT_OPTIONS);
$eg_archives_admn->set_stylesheets('css/eg-archives-admin.css');
$eg_archives_admn->load();

?>
