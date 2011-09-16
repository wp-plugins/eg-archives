<?php
$EGARW_OPTION_FORM = array(
		'menu_type'		=> 'options',
		'title' 		=> 'EG-Archives Settings',
		'header'		=> '',
		'footer'		=> '',
		'icon'			=> '',
		'access_level'	=> 'manage_options',
		'sections'		=> array(
			1 => array(
				'title' 	=> 'Styles',
			),
			2 => array(
				'title' 	=> 'Default Widget',
			),
			3 => array(
				'title' 	=> 'Uninstallation',
			)
		), // End of sections
		'fields'		=> array(
			array('section'	=> 1,
				'name'		=> 'load_css',
				'label'		=> 'Stylesheet',
				'type'		=> 'checkbox',
				'after'		=> 'Automatically load plugins\' stylesheet',
				'desc'		=> 'Check if you want to use the plugin stylesheet file, uncheck if you want to use your own styles, or include styles on the theme stylesheet.'
			),
			array('section'	=> 3,
				'name'		=> 'uninstall_del_options',
				'label'		=> 'Options',
				'type'		=> 'checkbox',
				'after'		=> 'Delete options during uninstallation.',
				'desc'		=> 'Be careful: these actions cannot be cancelled. All plugin\'s options will be deleted while plugin uninstallation.'
			),
			array('section'	=> 2,
				'name'		=> 'disable_default_widget',
				'label'		=> 'Disable widget',
				'type'		=> 'checkbox',
				'after'		=> 'Disable the default WordPress Archive widget'
			)
		) // End of fields
	) // End of form
?>