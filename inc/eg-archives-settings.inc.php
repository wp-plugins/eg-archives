<?php


$this->options_form = new EG_Form_210('egarw_options', 'EG-Archive Settings', $this->options_entry, $this->textdomain, '', '', array(&$this, 'display_sidebar'));

$widget_section_id = $this->options_form->add_section(array('title' 	=> 'Default Widget'));
	$this->options_form->add_field(array(
				'section'	=> $widget_section_id,
				'name'		=> 'disable_default_widget',
				'label'		=> 'Disable widget',
				'type'		=> 'checkbox',
				'after'		=> 'Disable the default WordPress Archive widget')
	);

$style_section_id = $this->options_form->add_section(array('title' 	=> 'Styles'));
	$this->options_form->add_field(array(
				'section'	=> $style_section_id,
				'name'		=> 'load_css',
				'label'		=> 'Stylesheet',
				'type'		=> 'checkbox',
				'after'		=> 'Automatically load plugins\' stylesheet',
				'desc'		=> 'Check if you want to use the plugin stylesheet file, uncheck if you want to use your own styles, or include styles on the theme stylesheet.')
	);
$uninstall_section_id = $this->options_form->add_section(array('title' 	=> 'Uninstallation'));
	$this->options_form->add_field(array(
				'section'	=> $uninstall_section_id,
				'name'		=> 'uninstall_del_options',
				'label'		=> 'Options',
				'type'		=> 'checkbox',
				'after'		=> 'Delete options during uninstallation.',
				'desc'		=> 'Be careful: these actions cannot be cancelled. All plugin\'s options will be deleted while plugin uninstallation.')
	);
?>