<?php
/**
 * efpic Settings
 *
 * Adds our admin menu and the settings page
 *
 * @since 0.5.0
 * @since 2.0.0 Major overhaul
 */
defined( 'ABSPATH' ) OR exit;


/**
 * Register settings menu for efpic.
 *
 * @since 0.5.0
 * @since 2.0.0 Add individual pages for settings
 */
function efpic_plugin_menu() {

	add_menu_page(
		'efpic',
		'efpic',
		efpic_capability(),
		'efpic',
		'',
		'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgNDAgMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiM5OTkiPjxwYXRoIGQ9Ik0yNy45NzYgMy42MzJoOC4zMjR2Ny45OTJoMy43di0xMS42MjRoLTEyLjAyNHYzLjYzMnpNMzYuMjkyIDI4LjM2aC04LjM0MXYzLjY0aDEyLjA0OXYtMTEuNjQ4aC0zLjcwOHY4LjAwOHpNMy43NCAyMC4yNDloLTMuNzR2MTEuNzUxaDExLjk2OXYtMy42NzJoLTguMjI5di04LjA3OXpNMy43NDIgMy42NzRoOC4yMzJ2LTMuNjc0aC0xMS45NzR2MTEuNzU2aDMuNzQydi04LjA4MnpNMjcuMTE4IDYuNDE4bC0xMC42NDcgMTIuOTQ3LTUuMjA0LTUuMDMzLTMuMzYyIDMuMzQ0IDkuMDI3IDguNzY5IDEzLjkwNS0xNy4xMjQtMy43MTktMi45MDN6Ii8+PC9nPjwvc3ZnPg==',
		25
	);

	add_submenu_page(
		'efpic',
		__( 'New Collection', 'efpic' ),
		__( 'New Collection', 'efpic' ),
		efpic_capability(),
		'post-new.php?post_type=efpic_collection',
		'',
		1
	);

	// Use this for the menu entry only
	add_submenu_page(
		'efpic',
		__( 'efpic Settings', 'efpic' ),
		__( 'Settings', 'efpic' ),
		'manage_options',
		'efpic-settings',
		'efpic_load_settings_page',
		20
	);

	// Add individual page for each settings group
	$settings = efpic_get_settings();
	foreach( $settings as $settings_group => $setting ) {
		// Add page
		add_submenu_page(
			'efpic',
			__( 'efpic Settings', 'efpic' ),
			$setting['title'],
			'manage_options',
			'efpic-' . $settings_group,
			'efpic_load_settings_page'
		);
		// Hide page
		add_action( 'admin_head', function() use ( $settings_group ) {
			remove_submenu_page( 'efpic', 'efpic-' . $settings_group );
		} );

		// Mark settings menu item as `current`
		global $submenu;
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'efpic-' . $settings_group ) {
			if ( array_key_exists( 'efpic', $submenu ) ) {
				// Find `efpic-settings` in the submenu array
				$key = array_search( 'efpic-settings', array_column( $submenu['efpic'], 2 ) );
				// Add current class
				$submenu['efpic'][$key][4] = ' current';
			}
		}
	}

	add_submenu_page(
		'efpic',
		__( 'efpic Pro', 'efpic' ),
		__( 'efpic Pro', 'efpic' ),
		'manage_options',
		'efpic-add-ons',
		'efpic_load_add_ons_page',
		100
	);
}

add_action( 'admin_menu', 'efpic_plugin_menu' );


/**
 * Redirect to first settings group page.
 *
 * Redirect from "generic settings" to the first real settings page.
 *
 * @since 2.0.0
 */
function efpic_settings_redirect() {
	if ( isset( $_GET['page'] ) AND $_GET['page'] === 'efpic-settings' ) {
		$settings = efpic_get_settings();
		wp_redirect( admin_url( 'admin.php?page=efpic-' . array_key_first( $settings ) ) );
		exit();
	}
}

add_action( 'admin_init', 'efpic_settings_redirect' );


/**
 * Gather all efpic settings.
 *
 * @since 2.0.0
 *
 * @return array efpic Settings
 */
function efpic_get_settings() {
	// Prepare variable
	$settings = [];

	$days = efpic_expiration_length();

	// Start with general settings
	$settings['general'] = [
		'title' => __( 'General', 'efpic' ),
		'description' => __( 'General efpic settings.', 'efpic' ),
		'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#007791" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
		'priority' => 1,
		'settings' => [
			'random_slugs' => [
				'type' => 'checkbox',
				'label' => __( 'Use random URLs for efpic collections', 'efpic' ),
				'description' => __( 'Disable, to use the WordPress default, generating the slug from the title.', 'efpic' ),
				'default' => 'on'
			],
			'expiration' => [
				'type' => 'checkbox',
				'label' => __( 'Expire collections by default', 'efpic' ),
				'description' => sprintf( _n( 'New collections will be set to expire %d day after being sent.', 'New collections will be set to expire %d days after being sent.', $days, 'efpic' ), $days ),
				'default' => 'off'
			],
			'pro-banner' => [
				'banner-id' => 'custom-thank-you-page',
			],
			'efpic_love' => [
				'type' => 'checkbox',
				'label' => '❤️ ' . __( 'Show efpic logo', 'efpic' ),
				'description' => __( 'Spread some efpic love, by displaying our logo in collections and efpic related emails.', 'efpic' ),
				'default' => 'off',
			]
		]
	];

	// Design/appearance settings
	$settings['design-appearance'] = [
		'title' => __( 'Design/Appearance', 'efpic' ),
		'description' => __( 'Configure the look of your collections.', 'efpic' ),
		'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#007791" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
		'priority' => 10,
		'settings' => [
			'theme' => [
				'type' => 'html',
				'output' => 'efpic_settings_theme',
				'label' => __( 'Theme', 'efpic' ),
				'default' => 'dark'
			],
			'pro-banner' => [
				'banner-id' => 'customize-collection',
			]
		]
	];

	// Email settings
	$settings['email'] = [
		'title' => __( 'Email', 'efpic' ),
		'description' => __( 'efpic email settings.', 'efpic' ),
		'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#007791" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
		'priority' => 20,
		'settings' => [
			'send_html_mails' => [
				'type' => 'checkbox',
				'label' => __( 'Use styling in emails', 'efpic' ),
				'description' => __( 'Use beautiful HTML templates when sending emails. Otherwise plain text emails will be sent.', 'efpic' ),
				'default' => 'on'
			],
			'send_password' => [
				'type' => 'checkbox',
				'label' => __( 'Include collection password in email', 'efpic' ),
				'description' => __( 'If you set a collection password, it will be included in the email to the client.', 'efpic' ),
				'default' => 'on'
			],
			'send_reminder' => [
				'type' => 'checkbox',
				'label' => __( 'Send email reminder', 'efpic' ),
				'description' => __( 'If a client started selecting images but did not finally approve the collection, efpic will automatically send a reminder after 24 hours.', 'efpic' ),
				'default' => 'off'
			],
			'pro-banner' => [
				'banner-id' => 'email-message-templates'
			],
			'from_email' => [
				'type' => 'text',
				'label' => __( 'From Email', 'efpic' ),
				'description' => __( 'The email address that emails are sent from.', 'efpic' ),
				'placeholder' => apply_filters( 'wp_mail_from', 'no-reply@' . parse_url( get_site_url(), PHP_URL_HOST ) ),
				'disabled' => efpic_email_setting_check_wp_mail_smtp( 'from_email' ),
				'disabled_hint' => sprintf( __( 'Disabled by WP Mail SMTP\'s "Force From Email" setting. %sChange%s', 'efpic' ), '<a href="' . admin_url( 'admin.php?page=wp-mail-smtp#wp-mail-smtp-setting-row-from_email' ) . '">', '</a>' ),
				'default' => '',
				'validation' => 'efpic_validate_from_email'
			],
			'from_name' => [
				'type' => 'text',
				'label' => __( 'From Name', 'efpic' ),
				'description' => __( 'The name that emails are sent from.', 'efpic' ),
				'placeholder' => apply_filters( 'wp_mail_from_name', get_bloginfo() ),
				'disabled' => efpic_email_setting_check_wp_mail_smtp( 'from_name' ),
				'disabled_hint' => sprintf( __( 'Disabled by WP Mail SMTP\'s "Force From Name" setting. %sChange%s', 'efpic' ), '<a href="' . admin_url( 'admin.php?page=wp-mail-smtp#wp-mail-smtp-setting-row-from_name' ) . '">', '</a>' ),
				'default' => ''
			],
			'notification_email' => [
				'type' => 'text',
				'label' => __( 'Notification Email', 'efpic' ),
				'description' => __( 'The email address <strong>all</strong> notification emails are sent to.', 'efpic' ),
				'hint' => __( 'Defaults to the collection author\'s email address.', 'efpic' ),
				'placeholder' => '',
				'default' => '',
				'validation' => 'efpic_validate_notification_email'
			],
		]
	];

	$settings['security'] = [
		'title' => __( 'Security', 'efpic' ),
		'description' => __( 'Password protect you collections and more.', 'efpic' ),
		'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#007791" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
		'priority' => 30,
		'settings' => [
			'password_by_default' => [
				'type' => 'checkbox',
				'label' => __( 'Password protection by default', 'efpic' ),
				'description' => __( 'A random password will automatically assigned to all new efpic collections.', 'efpic' ),
				'default' => 'off',
			],
			'pro-banner' => [
				'banner-id' => 'collection-protection',
			]
		]
	];

	// Tools/debug settings
	$settings['tools-debug'] = [
		'title' => __( 'Tools/Debug', 'efpic' ),
		'description' => __( 'Debug info and tools.', 'efpic' ),
		'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#007791" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>',
		'priority' => 50,
		'settings' => [
			'debug' => [
				'type' => 'html',
				'output' => 'efpic_settings_debug',
				'label' => 'Debug',
				'description' => '',
				'default' => '',
			]
		]
	];

	// Add image processor setting
	$image_processors = [];
	if ( extension_loaded( 'imagick' ) ) {
		$image_processors['WP_Image_Editor_Imagick'] = [
			'label' => 'WP Image Editor Imagick',
			'description' => __( 'Default processor, sometimes memory issue might cause not all images to be processed correctly. Allows to create PDF preview images.', 'efpic' ),
		];
	};
	if ( extension_loaded( 'gd' ) ) {
		$image_processors['WP_Image_Editor_GD'] = [
			'label' => 'WP Image Editor GD',
			'description' => __( 'Older processor, better at processing lots of images. Not able to create PDF preview images.', 'efpic' ),
		];
	};

	$settings['tools-debug']['settings']['default_image_processor'] = [
		'type' => 'radio',
		'options' => $image_processors,
		'title' =>  __( 'Image processor', 'efpic' ),
		'description' => __( 'Switch between different image processors to improve performance when uploading/importing images.<br /><strong>Please be aware, that this affects all media uploads on your site, not just efpic images.</strong>', 'efpic' ),
		'default' => 'WP_Image_Editor_Imagick'
	];

	$settings = apply_filters( 'efpic_settings', $settings );

	// Sort by priority
	uasort( $settings, function( $a, $b ) {
		if ( empty( $a['priority'] ) ) {
			$a['priority'] = 1000;
		}
		if ( empty( $b['priority'] ) ) {
			$b['priority'] = 1000;
		}
		return $a['priority'] <=> $b['priority'];
	});

	return $settings;
}


/**
 * Register settings.
 *
 * @since 0.7.0
 * @since 2.0.0 Major overhaul, now registering individually for each setting
 */
function efpic_register_settings() {
	$settings = efpic_get_settings();
	foreach( $settings as $option_group => $group_settings ) {
		foreach( $group_settings['settings'] as $name => $setting ) {
			// Skip for pro ads
			if ( $name == 'pro-banner' ) {
				continue;
			}
			$name = 'efpic_' . $name;

			if ( ! empty( $setting['validation'] ) ) {
				$validation = $setting['validation'];
			}
			elseif ( $setting['type'] == 'textarea' ) {
				$validation = 'sanitize_textarea_field';
			} 
			else {
				$validation = 'efpic_settings_validate';
			}

			register_setting( 'efpic_' . $option_group, $name, [ 'sanitize_callback' => "$validation", 'show_in_rest' => false, 'default' => $setting['default'] ] );
		}
	}
}

add_action( 'init', 'efpic_register_settings' );


/**
 * Render checkbox settings field.
 *
 * @since 2.0.0
 *
 * @param string $name The field name
 * @param array $setting The settings array
 * @param string $value The value currently stored in the db for this field
 * @return string HTML output for checkbox settings field
 */
function efpic_checkbox_field( $name, $setting, $value ) {
	ob_start();
	echo '<fieldset class="efpic_settings__settings-item';
	if ( isset( $setting['new'] ) && $setting['new'] === true ) {
		echo ' efpic_settings__settings-item__new" data-new="' . __( 'new', 'efpic' );
	}
	echo '" id="efpic_setting--' . sanitize_key( $name ) . '">';
	if ( ! empty( $setting['title'] ) ) {
		echo '<h2>' . $setting['title'] . '</h2>';
	}
	$disabled = false;
	if ( ! empty( $setting['disabled'] ) && $setting['disabled'] === true ) {
		$disabled = true;
	}
	echo '<p class="efpic-settings__item"><input type="checkbox" id="efpic_' . $name .'" name="efpic_' . $name .'"' . disabled( $disabled, true, false ) . checked( $value, 'on', false ) . ' /> <label for="efpic_' . $name . '" class="after">' . $setting['label'] . '<br /><span class="description">' . $setting['description'] . '</span>';
	if ( $disabled AND ! empty( $setting['disabled_hint'] ) ) {
		echo '<br /><span class="efpic-settings__input__hint"><span class="efpic-settings__input__hint--alert">'. $setting['disabled_hint'] . '</span></span>';
	}
	echo '</label></p>';
	echo '</fieldset>';

	return ob_get_clean();
}


/**
 * Render number settings field.
 * 
 * @since 2.1.0
 * @param string $name The field name
 * @param array $setting The settings array
 * @param string $value The value currently stored in the db for this field
 * @return string HTML output for number settings field
 */
function efpic_number_field( $name, $setting, $value ) {
	ob_start();
	echo '<fieldset class="efpic_settings__settings-item';
	if ( isset( $setting['new'] ) && $setting['new'] === true ) {
		echo ' efpic_settings__settings-item__new" data-new="' . __( 'new', 'efpic' );
	}
	echo '" id="efpic_setting--' . sanitize_key( $name ) . '">';
	if ( ! empty( $setting['title'] ) ) {
		echo '<h2>' . $setting['title'] . '</h2>';
	}
	$disabled = false;
	if ( ! empty( $setting['disabled'] ) && $setting['disabled'] === true ) {
		$disabled = true;
	}
	?>
		<p class="efpic-settings__item">
			<label for="efpic_<?php echo $name; ?>"><?php echo $setting['label']; ?><br /><span class="description"><?php echo $setting['description']; ?><br /></span></label>
			<span class="efpic-settings__input-wrap">
				<input type="number" name="efpic_<?php echo $name; ?>" id="efpic_<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php if ( ! empty( $setting['placeholder'] ) ) { echo $setting['placeholder']; } ?>" <?php disabled( $disabled ); ?> <?php if ( ! empty( $setting['min'] ) ) { echo ' min="' . $setting['min'] . '"'; } ?> <?php if ( ! empty( $setting['max'] ) ) { echo ' max="' . $setting['max'] . '"'; } ?> <?php if ( ! empty( $setting['step'] ) ) { echo ' step="' . $setting['step'] . '"'; } ?> />
				<?php if ( ! empty( $setting['hint'] ) ) { ?>
				<span class="efpic-settings__input__hint"><?php echo $setting['hint']; ?></span>
				<?php } ?>
				<?php
				if ( $disabled AND ! empty( $setting['disabled_hint'] ) ) { echo '<span class="efpic-settings__input__hint"><span class="efpic-settings__input__hint--alert">'. $setting['disabled_hint'] . '</span></span>'; } ?>
			</span>
		</p>
	<?php
	echo '</fieldset>';

	return ob_get_clean();
}


/**
 * Render text settings field.
 * 
 * @since 2.0.0
 * @param string $name The field name
 * @param array $setting The settings array
 * @param string $value The value currently stored in the db for this field
 * @return string HTML output for text settings field
 */
function efpic_text_field( $name, $setting, $value ) {
	$setting = apply_filters( 'efpic_text_field_settings', $setting, $name, $value );
	ob_start();
	echo '<fieldset class="efpic_settings__settings-item';
	if ( isset( $setting['new'] ) && $setting['new'] === true ) {
		echo ' efpic_settings__settings-item__new" data-new="' . __( 'new', 'efpic' );
	}
	echo '" id="efpic_setting--' . sanitize_key( $name ) . '">';
	if ( ! empty( $setting['title'] ) ) {
		echo '<h2>' . $setting['title'] . '</h2>';
	}
	$disabled = false;
	if ( ! empty( $setting['disabled'] ) && $setting['disabled'] === true ) {
		$disabled = true;
	}
	?>
		<p class="efpic-settings__item">
			<label for="efpic_<?php echo $name; ?>"><?php echo $setting['label']; ?><br /><span class="description"><?php echo $setting['description']; ?><br /></span></label>
			<span class="efpic-settings__input-wrap">
				<input type="text" name="efpic_<?php echo $name; ?>" id="efpic_<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php if ( ! empty( $setting['placeholder'] ) ) { echo $setting['placeholder']; } ?>" <?php disabled( $disabled ); ?> />
				<?php if ( ! empty( $setting['hint'] ) ) { ?>
				<span class="efpic-settings__input__hint"><?php echo $setting['hint']; ?></span>
				<?php } ?>
				<?php
				if ( $disabled AND ! empty( $setting['disabled_hint'] ) ) { echo '<span class="efpic-settings__input__hint"><span class="efpic-settings__input__hint--alert">'. $setting['disabled_hint'] . '</span></span>'; } ?>
			</span>
		</p>
	<?php
	echo '</fieldset>';

	return ob_get_clean();
}


/**
 * Render textarea settings field.
 * 
 * @since 3.3.0
 * @param string $name The field name
 * @param array $setting The settings array
 * @param string $value The value currently stored in the db for this field
 * @return string HTML output for textarea settings field
 */
function efpic_textarea_field( $name, $setting, $value ) {
	$setting = apply_filters( 'efpic_textarea_field_settings', $setting, $name, $value );
	ob_start();
	echo '<fieldset class="efpic_settings__settings-item';
	if ( isset( $setting['new'] ) && $setting['new'] === true ) {
		echo ' efpic_settings__settings-item__new" data-new="' . __( 'new', 'efpic' );
	}
	echo '" id="efpic_setting--' . sanitize_key( $name ) . '">';
	if ( ! empty( $setting['title'] ) ) {
		echo '<h2>' . $setting['title'] . '</h2>';
	}
	$disabled = false;
	if ( ! empty( $setting['disabled'] ) && $setting['disabled'] === true ) {
		$disabled = true;
	}
	?>
		<p class="efpic-settings__item">
			<label for="efpic_<?php echo $name; ?>"><?php echo $setting['label']; ?><br /><span class="description"><?php echo $setting['description']; ?><br /></span></label>
			<span class="efpic-settings__input-wrap">
				<textarea name="efpic_<?php echo $name; ?>" id="efpic_<?php echo $name; ?>" placeholder="<?php if ( ! empty( $setting['placeholder'] ) ) { echo $setting['placeholder']; } ?>" <?php disabled( $disabled ); ?>/><?php echo esc_textarea( $value ); ?></textarea>
				<?php if ( ! empty( $setting['hint'] ) ) { ?>
				<span class="efpic-settings__input__hint"><?php echo $setting['hint']; ?></span>
				<?php } ?>
				<?php
				if ( $disabled AND ! empty( $setting['disabled_hint'] ) ) { echo '<span class="efpic-settings__input__hint"><span class="efpic-settings__input__hint--alert">'. $setting['disabled_hint'] . '</span></span>'; } ?>
			</span>
		</p>
	<?php
	echo '</fieldset>';

	return ob_get_clean();
}


/**
 * Render radio button settings field.
 *
 * @since 2.0.0
 *
 * @param string $name The field name
 * @param array $setting The settings array
 * @param string $value The value currently stored in the db for this field
 * @return string HTML output for checkbox settings field
 */
function efpic_radio_field( $name, $setting, $value ) {
	ob_start();
	echo '<fieldset class="efpic_settings__settings-item efpic_settings__settings-item__radio';
	if ( isset( $setting['new'] ) && $setting['new'] === true ) {
		echo ' efpic_settings__settings-item__new" data-new="' . __( 'new', 'efpic' );
	}
	echo '" id="efpic_setting--' . sanitize_key( $name ) . '">';
	if ( ! empty( $setting['title'] ) ) {
		echo '<h2>' . $setting['title'] . '</h2>';
	}
	if ( ! empty( $setting['description'] ) ) {
		echo '<p class="">' . $setting['description'] . '</p>';
	}

	foreach( $setting['options'] as $option => $content ) {
		echo '<p class="efpic_settings__settings-item__radio-wrapper">';
		echo '<input type="radio" id="' . $option . '" name="efpic_' . $name .'" value="' . esc_attr( $option ) . '" ' . checked( $value, $option, false ) . ' /> <label class="after" for="' . $option . '">' . $content['label'];
		if ( ! empty( $content['description'] ) ) {
			echo '<br /><span class="description">' . $content['description'] . '</span>';
		}
		echo '</label>';
		echo '</p>';
	}

	echo '</fieldset>';

	return ob_get_clean();
}


/**
 * Render button settings field.
 *
 * @since 2.0.0
 *
 * @param string $name The field name
 * @param array $setting The settings array
 * @param string $value The value currently stored in the db for this field
 * @return string HTML output for checkbox settings field
 */
function efpic_button_field( $name, $setting, $value ) {
	ob_start();
	echo '<fieldset class="efpic_settings__settings-item efpic_settings__settings-item__button';
	if ( isset( $setting['new'] ) && $setting['new'] === true ) {
		echo ' efpic_settings__settings-item__new" data-new="' . __( 'new', 'efpic' );
	}
	echo '" id="efpic_setting--' . sanitize_key( $name ) . '">';
	if ( ! empty( $setting['title'] ) ) {
		echo '<h2>' . $setting['title'] . '</h2>';
	}
	echo '<p class="efpic-settings__item">
	<span class="description">' . $setting['description'] . '</span><br /><br />
	<button class="button" type="submit" id="efpic_' . $name .'" name="efpic_' . $name .'" value="' . esc_attr( $value ) . '" />' . esc_attr( $setting['label'] ) . '</button></p>';
	echo '</fieldset>';

	return ob_get_clean();
}


/**
 * Add theme setting.
 *
 * @since 2.0.0
 *
 * @param array $options efpic settings
 * @return string HTML output for the theme setting
 */
function efpic_settings_theme() {
	ob_start();
?>
	<fieldset class="efpic_settings__settings-item">
		<h2><?php _e( 'Theme', 'efpic' ); ?></h2>
		<p><?php _e( 'Choose how your collections will be displayed:', 'efpic' ); ?></p>
		<?php
			$themes = [
				'dark' => [
					'name' => __( 'Dark', 'efpic' ),
					'thumbnail' => EFPIC_URL . 'backend/images/dark-theme.png'
				],
				'light' => [
					'name' => __( 'Light', 'efpic' ),
					'thumbnail' => EFPIC_URL . 'backend/images/light-theme.png'
				]
			];

			$custom_themes = apply_filters( 'efpic_themes', [] );

			$themes = array_merge( $themes, $custom_themes );
		?>
		<div class="efpic-settings__theme-wrap">
		<?php
			foreach( $themes as $theme => $theme_data ) {
				if ( empty( $theme_data['thumbnail'] ) ) {
					$theme_data['thumbnail'] = EFPIC_URL . 'backend/images/efpic-theme-picker.svg';
				}
		?>
			<span class="nowrap efpic-settings__theme">
				<input type="radio" class="efpic-radio-image" name="efpic_theme" id="efpic_<?php echo $theme; ?>_theme" value="<?php echo esc_attr( $theme ); ?>" <?php checked( get_option( 'efpic_theme' ), $theme ); ?> />
				<label for="efpic_<?php echo $theme; ?>_theme" class="after"><img class="theme-thumbnail" src="<?php echo $theme_data['thumbnail']; ?>" alt="<?php echo $theme_data['name']; ?>" /> <span class="efpic-settings__theme__title"><?php echo $theme_data['name']; ?></span></label>
			</span>
		<?php
			}
		?>
		</div>
	</fieldset>
<?php
	return ob_get_clean();
}


/**
 * Add debug "setting".
 *
 * @since 2.0.0
 *
 * @return Debug page HTML output
 */
function efpic_settings_debug() {
	ob_start();
?>
	<fieldset class="efpic_settings__settings-item">
		<h2><?php _e( 'Debug Info', 'efpic' ); ?></h2>
		<p>👉 <?php
		/* translators: %s: Opening and closing link tags */
		echo sprintf( __( 'Debug info can be found in %sTools > Site Health%s.', 'efpic' ), '<a href="' . get_admin_url( null, 'site-health.php?tab=debug' ) .'">', '</a>' ); ?></p>

		<p>🛟 <?php
		/* translators: %s: Opening and closing link tags */
		echo sprintf( __( 'When submitting a %ssupport request%s, please use the button below to include your site info.', 'efpic' ), '<a href="mailto:support@efpic.io">', '</a>' ); ?></p>

		<?php
			require_once( ABSPATH . 'wp-admin/includes/class-wp-debug-data.php' );
			$debug_info = new WP_Debug_Data();
		?>
		<div class="efpic-debug-copy-button-wrapper">
			<button type="button" class="button efpic-debug-copy-button" data-clipboard-text="<?php echo esc_attr( $debug_info->format( $debug_info->debug_data(), 'debug' ) ); ?>">
				<?php /* translators: Button text */ _e( 'Copy site info to clipboard', 'efpic' ); ?>
			</button>
			<span class="success hidden" aria-hidden="true"><?php /* translators: Shown,when copying to clipboard was successful */ _e( 'Copied!', 'efpic' ); ?></span>
		</div>
	</fieldset>
<?php
	return ob_get_clean();
}


/**
 * Check if from email/name are defined by WP Mail SMTP.
 *
 * @since 2.0.0
 *
 * @param string $setting The setting to check
 * @return bool Whether the setting is defined or not
 */
function efpic_email_setting_check_wp_mail_smtp( $setting ) {
	$wp_mail_smtp_options = get_option( 'wp_mail_smtp' );
	if ( function_exists( 'wp_mail_smtp' ) AND ! empty( $wp_mail_smtp_options ) ) {
		if ( $setting == 'from_email' && $wp_mail_smtp_options['mail']['from_email_force'] == true ) {
			return true;
		}
		if ( $setting == 'from_name' && $wp_mail_smtp_options['mail']['from_name_force'] == true ) {
			return true;
		}
	}

	return false;
}


/**
 * Load Efpic settings page.
 *
 * Echo settings page header, side navigation fieldsets and submit button.
 *
 * @since 0.7.0
 * @since 2.0.0 Ability to render different settings pages
 */
function efpic_load_settings_page() {
	// Determin which setting to show
	$current_screen = get_current_screen();
	$current_screen = $current_screen->base;
	$settings = efpic_get_settings();
	$temp = str_replace( 'efpic_page_efpic-', '', $current_screen );
	// Fallback to general
	if ( $temp == 'settings' ) {
		$temp = 'general';
		$this_setting = $settings['general'];
	}
	else {
		$this_setting = $settings[str_replace( 'efpic_page_efpic-', '', $current_screen )];
	}
?>
	<input type="checkbox" id="efpic-settings-nav-toggle" />
	<div class="efpic-settings__head-wrapper">
		<span class="efpic-settings__head-line"><img class="efpic-settings__logo" src="<?php echo EFPIC_URL; ?>/backend/images/efpic_logo_dark_w_o_text.png" /><label for="efpic-settings-nav-toggle"><?php _e( 'Settings', 'efpic' ); ?></label></span>
		<nav class="efpic-settings__head-nav">
			<a href="https://efpic.io/docs/"><?php /* translators: Link text */ _e( 'Documentation', 'efpic' ); ?></a>
			<a href="https://efpic.io/support/"><?php /* translators: Link text */ _e( 'Support', 'efpic' ); ?></a>
		</nav>
	</div>
	<div class="wrap">
		<h2 style="display: none;"><!-- h2 headline is necessary for WordPress to properly position admin notices --></h2>
		<?php
		$settings_notifications = get_settings_errors();

		foreach( $settings_notifications as $notification ) {
			echo '<div id="setting-error-' . $notification['code'] . '" class="notice notice-' . $notification['type'] . ' settings-' . $notification['code'] . ' is-dismissible efpic-settings-notice"> 
				<p>' . $notification['message'] . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'efpic' ) . '</span></button></div>';
		}
		?>
		<div class="efpic-settings__wrap">
			<?php do_action( 'efpic_pre_settings' ); ?>
			<nav class="efpic_settings__nav">
				<ul>
					<?php

					foreach( $settings as $key => $setting ) {
						echo '<li class="efpic_settings__nav-item';
						if ( $key === $temp ) {
							echo ' efpic_settings__nav-item__current-item';
						}
						echo '"><a href="' . admin_url( 'admin.php?page=efpic-' . $key ) . '">' . $setting['icon'] . ' ' . $setting['title'] . '</a></li>';
					}
					?>
				</ul>
			</nav>
			<form class="efpic-settings__form" action="options.php" method="post">
				<header class="efpic-settings__form-header">
					<h1 class="efpic-settings__form-headline"><?php echo $this_setting['title']; ?><?php
						if ( ! empty( $this_setting['badge'] ) ) {
							echo '<span class="efpic-settings__form-headline__badge"';
							if ( ! empty( $this_setting['badge_color' ] ) ) {
								echo ' style="background-color: ' . $this_setting['badge_color'] . ';"';
							}
							echo '>' . $this_setting['badge'] . '</span>';
						}
					?></h1>
					<p class="efpic-settings__form-description"><?php echo $this_setting['description']; ?></p>
				</header>
				<?php
					// Render settings fields
					settings_fields( 'efpic_' . $temp );

					foreach( $this_setting['settings'] as $key => $setting ) {
						if ( $key == 'pro-banner' && ! empty( $setting['banner-id'] ) ) {
							echo efpic_settings_pro_box( $setting['banner-id'] );
						}
						elseif ( $setting['type'] == 'checkbox' ) {
							echo efpic_checkbox_field( $key, $setting, get_option( 'efpic_' . $key ) );
						}
						elseif ( $setting['type'] == 'text' ) {
							echo efpic_text_field( $key, $setting, get_option( 'efpic_' . $key ) );
						}
						elseif ( $setting['type'] == 'textarea' ) {
							echo efpic_textarea_field( $key, $setting, get_option( 'efpic_' . $key ) );
						}
						elseif ( $setting['type'] == 'number' ) {
							echo efpic_number_field( $key, $setting, get_option( 'efpic_' . $key ) );
						}
						elseif ( $setting['type'] == 'radio' ) {
							echo efpic_radio_field( $key, $setting, get_option( 'efpic_' . $key ) );
						}
						elseif ( $setting['type'] == 'button' ) {
							echo efpic_button_field( $key, $setting, get_option( 'efpic_' . $key ) );
						}
						elseif ( $setting['type'] == 'html' ) {
							echo $setting['output'](); // Call settings render function here
						}
					}
				?>
					<p class="efpic-settings__save-wrap"><input class="button button-primary efpic-settings__save-button" type="submit" name="save-efpic-settings" value="<?php _e( 'Save Settings', 'efpic'); ?>" /></p>
				
			</form>
		</div><!-- settings-wrap -->
	</div>
<?php
}


/**
 * Default efpic settings validation.
 *
 * @since 0.7.0
 * @since 2.0.0 Just make sure the value is sanitized
 *
 * @param string $value The settings field value
 * @return string The sanatized value
 */
function efpic_settings_validate( $value ) {
	return sanitize_text_field( $value );
}


/**
 * Validate from email setting.
 *
 * @since 2.0.0
 *
 * @param string $email The user entered email address
 * @return string The validated email address
 */
function efpic_validate_from_email( $email ) {
	// Only run validation once:
	if ( did_action( 'validate_from_email' ) ) {
		return $email;
	}
	do_action( 'validate_from_email' );

	if ( ! empty( $email ) AND ! is_email( $email ) ) {
		add_settings_error(
			'efpic',
			'from-email',
			'<strong>' . __( 'From Email', 'efpic' ) . ':</strong> ' . __( 'Please enter a valid email address.', 'efpic' ),
			'error'
		);
		return '';
	}

	return sanitize_email( $email );
}


/**
 * Validate notification email setting.
 *
 * @since 2.0.0
 *
 * @param string $email The user entered email address
 * @return string The validated email address
 */
function efpic_validate_notification_email( $email ) {
	// Only run validation once:
	if ( did_action( 'validate_notification_email' ) ) {
		return $email;
	}
	do_action( 'validate_notification_email' );

	if ( ! empty( $email ) AND ! is_email( $email ) ) {
		add_settings_error(
			'efpic',
			'notification-email',
			'<strong>' . __( 'Notification Email', 'efpic' ) . ':</strong> ' . __( 'Please enter a valid email address.', 'efpic' ),
			'error'
		);
		return '';
	}

	return sanitize_email( $email );
}


/**
 * Upgrade settings to new schema.
 *
 * @since 2.0.0
 */
function efpic_settings_upgrade() {
	// Handle regular efpic settings
	$efpic_settings = get_option( 'efpic_settings' );
	if ( ! empty( $efpic_settings ) && is_array( $efpic_settings ) ) {
		// Save new settings structure
		foreach( $efpic_settings as $setting_name => $value ) {
			add_option( 'efpic_' . $setting_name, $value, '', false );
		}
	}

	// Handle Brand & Customize settings
	$efpic_settings_brand_customize = get_option( 'efpic_settings_brand_customize' );
	if ( ! empty( $efpic_settings_brand_customize ) && is_array( $efpic_settings_brand_customize ) ) {
		// Save new settings structure
		foreach( $efpic_settings_brand_customize as $setting_name => $value ) {
			if ( $setting_name == 'show_blog_title' ) {
				add_option( 'efpic_site_title', $value, '', false );
			}
			elseif ( $setting_name == 'font_method' ) {
				// Update value
				if ( $value == 'efpic-bc-external-font' ) {
					$value = 'efpic-external-font';
				}
				$font = get_option( 'efpic_font' );
				// Use new parameter name ("method" instead of "font_method")
				$font['method'] = $value;
				update_option( 'efpic_font', $font, false );
			}
			elseif ( $setting_name == 'font' ) {
				$font = get_option( 'efpic_font' );
				$font['font'] = $value;
				update_option( 'efpic_font', $font, false );
			}
			elseif ( $setting_name == 'external_font_name' ) {
				$font = get_option( 'efpic_font' );
				$font['external_font_name'] = $value;
				update_option( 'efpic_font', $font, false );
			}
			elseif ( $setting_name == 'external_font_code' ) {
				$font = get_option( 'efpic_font' );
				$font['external_font_vendor'] = $value['vendor'];
				// Google Fonts
				if ( ! empty ( $value['family_parameter'] ) ) {
					$font['external_font_family_parameter'] = $value['family_parameter'];
				}
				// Typekit
				if ( ! empty ( $value['kit_id'] ) ) {
					$font['external_font_kit_id'] = $value['kit_id'];
				}
				update_option( 'efpic_font', $font, false );
			}

			elseif ( $setting_name == 'image_size' ) {
				add_option( 'efpic_image_size', substr( $value, 10 ), '', false );
			}
			elseif ( $setting_name == 'redirect_timer' ) {
				$after_approval = get_option( 'efpic_after_approval' );
				$after_approval['redirect_timer'] = $value;
				update_option( 'efpic_after_approval', $after_approval, false );
			}
			elseif ( $setting_name == 'after_approval_message' ) {
				$after_approval = get_option( 'efpic_after_approval' );
				$after_approval['after_approval_message'] = $value;
				update_option( 'efpic_after_approval', $after_approval, false );
			}
			elseif ( $setting_name == 'redirect' ) {
				$after_approval = get_option( 'efpic_after_approval' );
				$after_approval['target_url'] = $value;
				update_option( 'efpic_after_approval', $after_approval, false );
			}
			else {
				add_option( 'efpic_' . $setting_name, $value, '', false );
			}
		}
	}

	// Handle Import settings
	$efpic_settings_import = get_option( 'efpic_settings_import' );
	if ( ! empty( $efpic_settings_import ) && is_array( $efpic_settings_import ) ) {
		// Save new settings structure
		foreach( $efpic_settings_import as $setting_name => $value ) {
			// Use "keep_files" instead of "keep", etc.
			add_option( 'efpic_import_' . $setting_name, $value . '_files', '', false );
		}
	}

	// Handle Theft Protection settings
	$efpic_settings_theft_protection = get_option( 'efpic_settings_theft_protection' );
	if ( ! empty( $efpic_settings_theft_protection ) && is_array( $efpic_settings_theft_protection ) ) {
		// Save new settings structure
		foreach( $efpic_settings_theft_protection as $setting_name => $value ) {
			if ( $setting_name == 'disable_right_click' ) {
				add_option( 'efpic_disable_right_click', $value, '', false );
			}
			elseif ( $setting_name == 'prevent_hotlinking' ) {
				// do nothing
			}
			else {
				$watermark = get_option( 'efpic_watermark', [] );
				$watermark[$setting_name] = $value;
				update_option( 'efpic_watermark', $watermark, false );
			}
		}
	}

	update_option( 'efpic_settings_version', '2.0.0', false );
}


/**
 * Render Pro banners in settings.
 *
 * @since 2.4.0
 * 
 * @param string $banner_id The banner ID
 */
function efpic_settings_pro_box( $banner_id ) {
	ob_start();
	efpic_render_ad_slot( sanitize_html_class( $banner_id ), 'efpic-pro-box' );
	return ob_get_clean();
}