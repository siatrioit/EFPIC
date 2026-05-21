<?php
/**
 * Pro settings
 *
 * @since 1.1.0
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Add Pro settings to general.
 *
 * @since 1.1.0
 *
 * @param array The settings array
 * @return array The filtered settings array
 */
function efpic_pro_add_general_settings( $settings ) {
	// Save efpic_love setting for re-adding it later
	$temp = $settings['general']['settings']['efpic_love'];
	unset( $settings['general']['settings']['efpic_love'] );

	// Add default expiration time setting
	$settings['general']['settings']['expiration_length'] = [
		'type' => 'number',
		'label' => __( 'Default expiration time', 'efpic-pro' ),
		'description' => __( 'Time span after which a collection expires in days. (Expiration can be activated per collection.)', 'efpic-pro' ),
		'default' => 30,
		'min' => 1,
		'step' => 1
	];

	// Adjust expiration setting wording
	$settings['general']['settings']['expiration']['description'] = __( 'New collections will be set to automatically expire.', 'efpic-pro' );

	// Add after approval setting
	$settings['general']['settings']['after_approval'] = [
		'type' => 'html',
		'output' => 'efpic_pro_after_approval_setting',
		'title' => __( 'After approving a collection', 'efpic-pro' ),
		'default' => [
			'redirect_timer' => 'no-redirect',
			'after_approval_message' => '<h1>' . __( 'Thank you!', 'efpic-pro' ) . '</h1><p>' . __( 'The collection has been approved and the photographer has been notified.', 'efpic-pro' ) . '</p><p>' . __( 'You can now close this browser window.', 'efpic-pro' ). '</p>',
			'target_url' => '',
		],
		'validation' => 'efpic_pro_validate_after_approval',
	];

	// Re-add efpic_love setting
	$settings['general']['settings']['efpic_love'] = $temp;

	return $settings;
}
	
add_filter( 'efpic_settings', 'efpic_pro_add_general_settings' );


/**
 * The after approval setting HTML output.
 *
 * @since 1.1.0
 *
 * @return string The HTML output
 */
function efpic_pro_after_approval_setting() {
	ob_start();

	// Get individual option variables
	extract( get_option( 'efpic_after_approval' ) );
?>
<fieldset class="efpic_settings__settings-item efpic_settings__settings-item__after-approval">
	<h2><?php _e( 'After Approving a Collection', 'efpic-pro' ); ?></h2>
	<p class="efpic-settings__item"><label for="redirect_timer"><?php _e( 'Time to redirect', 'efpic-pro' ); ?><br /><span class="description"><?php _e( 'Set a time or disable', 'efpic-pro' ); ?>.</span></label>
	<span class="efpic-settings__input-wrap">
		<select name="efpic_after_approval[redirect_timer]" id="redirect_timer">
			<option value="no-redirect" <?php selected( $redirect_timer, 'no-redirect' ); ?>><?php _e( 'No redirect', 'efpic-pro' ); ?></option>
			<option value="immediately" <?php selected( $redirect_timer, 'immediately' ); ?>><?php _e( 'Immediately – don\'t show approval message', 'efpic-pro' ); ?></option>
			<option value="5" <?php selected( $redirect_timer, '5' ); ?>><?php _e( '5 seconds', 'efpic-pro' ); ?></option>
			<option value="10" <?php selected( $redirect_timer, '10' ); ?>><?php _e( '10 seconds', 'efpic-pro' ); ?></option>
		</select>
	</span></p>
	<p class="efpic-settings__item"><label for="afterapprovalmessage"><?php _e( 'After approval message', 'efpic-pro' ); ?><br /><span class="description"><?php _e( 'Displayed, once the client has approved a collection.', 'efpic-pro' ); ?></span></label>
		<?php wp_enqueue_editor(); ?>
		<textarea id="afterapprovalmessage" name="efpic_after_approval[after_approval_message]" autocomplete="off"><?php echo $after_approval_message; ?></textarea>
		<script>
		jQuery(document).ready( function() {
		var settings = {
			tinymce: {
				toolbar1:"formatselect,bold,italic,link",
				block_formats: "Title=h1;Paragraph=p;",
				statusbar: false,
				height: 180,
			},
			quicktags: false
		};
		wp.editor.initialize( 'afterapprovalmessage', settings );
		});
		</script>
	</p>
	<p class="efpic-settings__item"><label for="redirect"><?php _e( 'Target URL', 'efpic-pro' ); ?><br /><?php /* translators: %s is a URL */?><span class="description"><?php echo sprintf( __( 'Where the client is redirected after approving a collection. Defaults to %s', 'efpic-pro' ), esc_url( get_home_url() ) ); ?></span></label> <span class="efpic-settings__input-wrap"><input type="text" name="efpic_after_approval[target_url]" id="redirect" placeholder="" value="<?php echo htmlspecialchars( $target_url ); ?>" /></span></p>
</fieldset>
<?php
	return ob_get_clean();
}


/**
 * Validate after approval setting.
 *
 * @since 1.1.0
 *
 * @param array $args The after approval field values
 * @return array The validated (and sanitized) values
 */
function efpic_pro_validate_after_approval( $args ) {
	// Only run this once
	if ( did_action( 'validate_after_approval' ) ) {
		return $args;
	}
	do_action( 'validate_after_approval' );

	// Sanitize redirect timer
	if ( ! empty( $args['redirect_timer'] ) AND ( $args['redirect_timer'] == 'no-redirect' OR $args['redirect_timer'] == 'immediately' OR $args['redirect_timer'] == '5' OR $args['redirect_timer'] == '10' ) ) {
		$args['redirect_timer'] = $args['redirect_timer'];
	}
	else {
		$args['redirect_timer'] = 'no-redirect';
	}

	// Sanitize target_url
	if ( ! empty( $args['target_url'] ) ) {
		$args['target_url'] = esc_url_raw( $args['target_url'] );
	}

	return $args;
}


/**
 * Add Pro settings to design/appearance.
 *
 * @since 1.1.0
 *
 * @param array The settings array
 * @return array The filtered settings array
 */
function efpic_pro_add_design_settings( $settings ) {
	// Logo
	$settings['design-appearance']['settings']['logo'] = [
		'type' => 'html',
		'output' => 'efpic_pro_logo_setting',
		'title' => __( 'Logo', 'efpic-pro' ),
		'default' => '',
	];

	// Site title
	$settings['design-appearance']['settings']['site_title'] = [
		'type' => 'checkbox',
		'label' => __( 'Show site title', 'efpic-pro' ),
		/* translators: %s = The site title */
		'description' => sprintf( __( 'Display the site title &quot;%s&quot; above the collection title.', 'efpic-pro' ), get_bloginfo( 'name' ) ),
		'default' => 'off',
	];

	// Primary color
	$settings['design-appearance']['settings']['primary_color'] = [
		'type' => 'html',
		'output' => 'efpic_pro_primary_color_setting',
		'title' => __( 'Color', 'efpic-pro' ),
		'label' => __( 'Define primary color', 'efpic-pro' ),
		'description' => __( 'Define the color that is used for buttons and highlighting selected images.', 'efpic-pro' ),
		'default' => '',
		'validation' => 'efpic_pro_validate_primary_color'
	];

	// Font
	$settings['design-appearance']['settings']['font'] = [
		'type' => 'html',
		'output' => 'efpic_pro_font_setting',
		'title' => __( 'Font', 'efpic-pro' ),
		'default' => [
			'font_method' => 'efpic-standard-font',
			'font' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;',
			'external_font_name' => '',
			'external_font_code' => '',
			'external_font_vendor' => '',
			'external_font_kit_id' => '',
			'external_font_family_parameter' => ''
		],
		'validation' => 'efpic_validate_font'
	];

	// File name composition
	$settings['design-appearance']['settings']['file_name_comp'] = [
		'type' => 'html',
		'output' => 'efpic_pro_file_name_comp_setting',
		'title' => __( 'Color', 'efpic-pro' ),
		'label' => __( 'Define primary color', 'efpic-pro' ),
		'description' => __( 'Define the color that is used for buttons and highlighting selected images.', 'efpic-pro' ),
		'default' => '[number] [filename]',
		'validation' => 'efpic_pro_validate_file_name_comp'
	];
	
	// Image size
	$settings['design-appearance']['settings']['image_size'] = [
		'type' => 'html',
		'output' => 'efpic_pro_image_size_setting',
		'default' => 'medium',
	];

	return $settings;
}
	
add_filter( 'efpic_settings', 'efpic_pro_add_design_settings' );


/**
 * The logo setting HTML output.
 *
 * @since 1.1.0
 */
function efpic_pro_logo_setting() {
	ob_start();

	$logo = get_option( 'efpic_logo' );
?>
	<fieldset class="efpic_settings__settings-item">
		<h2><?php _e( 'Logo', 'efpic-pro' ); ?></h2>
		<div class="logo-setting-wrap">
		<?php
			if ( ! empty( get_option( 'efpic_logo' ) ) ) {
				echo '<img src="' . get_option( 'efpic_logo' ) . '" class="preview-logo" alt="" />';
			}
		?>
		</div>
		<?php
			$replace_remove = '';
			$upload = '';
			if ( ! empty( $logo ) ) {
				$replace_remove = ' active';
			}
			else {
				$upload = ' active';
			}
		?>
		<p class="ui-replace-remove<?php echo $replace_remove; ?>">
			<a class="button add_logo" href="#"><?php _e( 'Replace Logo', 'efpic-pro' ); ?></a> <a class="button" id="remove_logo" href="#"><?php _e( 'Remove Logo', 'efpic-pro' ); ?></a>
		</p>
		<p class="ui-upload<?php echo $upload; ?>">
			<a class="button add_logo" href="#"><?php _e( 'Upload Logo', 'efpic-pro' ); ?></a>
		</p>

		<input type="hidden" name="efpic_logo" id="logo" value="<?php if ( ! empty( get_option('efpic_logo' ) ) ) { echo get_option( 'efpic_logo' ); } ?>" />
	</fieldset>
<?php
	return ob_get_clean();
}


/**
 * The primary color setting HTML output.
 *
 * @since 1.1.0
 *
 * @return string The HTML output
 */
function efpic_pro_primary_color_setting() {
	ob_start();
?>
<fieldset class="efpic_settings__settings-item">
	<h2><?php _e( 'Color', 'efpic-pro' ); ?></h2>
	<?php $primary_color = get_option( 'efpic_primary_color', '' ); ?>
	<p><label for="primary_color"><?php _e( 'Define primary color', 'efpic-pro' ); ?><br /><span class="description"><?php _e( 'Define the color that is used for buttons and highlighting selected images.', 'efpic-pro' ); ?></span></label> <input type="text" name="efpic_primary_color" id="primary_color" class="color-field" value="<?php echo $primary_color; ?>" /></p>
</fieldset>
<?php
	return ob_get_clean();
}


/**
 * Validate primary color settings.
 *
 * @since 1.1.0
 *
 * @param array $args The color setting field value
 * @return array The validated (and sanitized) value
 */
function efpic_pro_validate_primary_color( $color ) {
	if ( ! empty( $color ) ) {
		if ( ! preg_match( '/^#[a-f0-9]{6}$/i', $color ) ) {
			add_settings_error(
				'efpic',
				'from-email',
				'<strong>' . __( 'Color', 'efpic-pro' ) . ':</strong> ' . __( 'Please choose a valid color.', 'efpic-pro' ),
				'error' // success, warning, info
			);

			return '';
		}
	}

	return $color;
}


/**
 * Font setting HTML output.
 *
 * @since 1.1.0
 *
 * @return string The HTML output
 */
function efpic_pro_font_setting() {
	ob_start();

	$defaults = [
		'method' => 'efpic-standard-font',
		'font' => '\'Proxima Nova\', \'proxima-nova\', Helvetica, Arial, sans-serif',
		'external_font_name' => '',
		'external_font_code' => '',
		'external_font_vendor' => '',
		'external_font_kit_id' => '',
		'external_font_family_parameter' => ''
	];
	extract( wp_parse_args( get_option( 'efpic_font', [] ), $defaults ) );
?>
<fieldset class="efpic_settings__settings-item">
	<h2><?php _e( 'Font', 'efpic-pro' ); ?></h2>
	<input type="hidden" class="js-efpic_bc_font_method" name="efpic_font[method]" value="<?php echo $method; ?>" />

	<ul class="radio-tab js-radio-tab">
		<li><a<?php if ( $method == 'efpic-standard-font' ) { echo ' class="active"'; } ?> href="#efpic-standard-font"><?php _e( 'Use standard font', 'efpic-pro' ); ?></a></li>
		<li><a<?php if ( $method == 'efpic-external-font' ) { echo ' class="active"'; } ?> href="#efpic-external-font"><?php _e( 'Use custom/external font', 'efpic-pro' ); ?></a></li>
	</ul><!-- .efpic-sahre-select -->

	<div class="radio-tab-box<?php if ( $method == 'efpic-standard-font' ) { echo ' is-active'; } ?>" id="efpic-standard-font">
		<p>
			<label for="efpic_font"><?php _e( 'Select font', 'efpic-pro'); ?>:<br /><span class="description"><?php _e( 'The displayed font may vary, depending on whether it is installed on a user\'s system.', 'efpic-pro' ); ?> <a class="efpic-help" href="https://efpic.io/docs/pro/brand-customize/#use-custom-fonts">help</a></span></label>
			<select name="efpic_font[font]" id="efpic_font">
				<option value="'Proxima Nova', 'proxima-nova', Helvetica, Arial, sans-serif" <?php selected( $font, '\'Proxima Nova\', \'proxima-nova\', Helvetica, Arial, sans-serif' ); ?>><?php _e( 'efpic default', 'efpic-pro' ); ?></option>
				<option value="Tahoma, Arial, Helvetica, sans-serif" <?php selected( $font, 'Tahoma, Arial, Helvetica, sans-serif' ); ?>>Tahoma</option>
				<option value="Verdana, Geneva, sans-serif" <?php selected( $font, 'Verdana, Geneva, sans-serif' ); ?>>Verdana</option>
				<option value="'Times New Roman', Times, serif" <?php selected( $font, '\'Times New Roman\', Times, serif' ); ?>>Times New Roman</option>
				<option value="Georgia, Utopia, Palatino, 'Palatino Linotype', serif" <?php selected( $font, 'Georgia, Utopia, Palatino, \'Palatino Linotype\', serif' ); ?>>Georgia</option>
				<option value="'Courier New', Courier, monospace" <?php selected( $font, '\'Courier New\', Courier, monospace' ); ?>>Courier New</option>
			</select>
		</p>
	</div>

	<div class="radio-tab-box<?php if ( $method == 'efpic-external-font' ) { echo ' is-active'; } ?>" id="efpic-external-font">
		<p><?php echo sprintf( wp_kses( __( 'We support <a href="%1$s">Google Fonts</a> and <a href="%2$s">Adobe Fonts (Typekit)</a>. Visit the efpic <a href="%3$s">FAQs</a> to see some usage examples.', 'efpic-pro' ), array( 'a' => array( 'href' => array() ) ) ), 'https://fonts.google.com/', 'https://fonts.adobe.com/', 'https://efpic.io/docs/pro/brand-customize/#use-custom-fonts' ); ?></p>
		<p><label for="efpic_external_font_name"><?php _e( 'External Font Name', 'efpic-pro' ); ?>:<br /><span class="description"><?php _e( 'Enter the <code>font-family</code> value.', 'efpic-pro' ); ?></span></label> <input type="text" name="efpic_font[external_font_name]" id="efpic_external_font_name" placeholder="&#x27;Open Sans&#x27;, sans-serif" value="<?php echo esc_html( $external_font_name ); ?>" /></p>
		<p><label for="efpic_external_font_code"><?php _e( 'Embed Code', 'efpic-pro' ); ?>:<br /><span class="description"><?php _e( 'A reference to an external stylesheet or javascript.', 'efpic-pro' ); ?></span></label> <textarea name="efpic_font[external_font_code]" id="efpic_external_font_code" placeholder="&lt;link rel=&quot;preconnect&quot; href=&quot;https://fonts.gstatic.com&quot;&gt;
&lt;link href=&quot;https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&amp;display=swap&quot; rel=&quot;stylesheet&quot;&gt;"><?php
		// Check for possible font vendors
		if ( isset( $external_font_vendor ) ) {

			// Using Typekit
			if ( $external_font_vendor == 'typekit' ) {
				echo '<link rel="stylesheet" href="https://use.typekit.net/' . $external_font_kit_id .'.css" />';
			}

			// Using Google
			elseif ( $external_font_vendor == 'google' ) {
				echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\">\n<link href=\"https://fonts.googleapis.com/css2?family=" . $external_font_family_parameter ."\" rel=\"stylesheet\">";
			}
		} ?></textarea></p>
	</div>
</fieldset>
<?php
	return ob_get_clean();
}


/**
 * Validate font settings.
 *
 * @since 1.1.0
 *
 * @param array $args The font settings field values
 * @return array The validated (and sanitized) values
 */
function efpic_validate_font( $args ) {
	if ( did_action( 'validate_font' ) ) {
		return $args;
	}
	do_action( 'validate_font' );

	if ( isset( $args['method'] ) AND $args['method'] == 'efpic-external-font' ) {
		// Remove trailling semi colon and sanitize
		$args['external_font_name'] = str_replace( ';', '' , sanitize_text_field( $args['external_font_name'] ) );

		// If user is using Typekit:
		if ( strpos( $args['external_font_code'], 'typekit' ) ) {
			// Get kit id
			$matchs = array();
			preg_match( '#(?<=(https://use\.typekit\.net/))([a-z0-9]*)(?=\.css)#i', $args['external_font_code'], $matches );

			// Set variables
			unset( $args['external_font_code'] );

			if ( isset( $matches[0] ) ) {
				$args['external_font_vendor'] = 'typekit';
				$args['external_font_kit_id'] = $matches[0];
			}
		}

		// If user is using Google Fonts
		elseif ( strpos( $args['external_font_code'], 'google' ) ) {
			// Get font string
			$matches = array();
			preg_match( '#(?<=googleapis\.com/css2\?family=)(...*)(?=(\'|") rel=(\'|"))#i', $args['external_font_code'], $matches );

			// Set variables
			unset( $args['external_font_code'] );

			if ( isset( $matches[0] ) ) {
				$args['external_font_vendor'] = 'google';
				$args['external_font_family_parameter'] = $matches[0];
			}
		}

		// No compatible font vendor found, discard input for secuirty reasons
		else {
			unset( $args['external_font_code'] );

			// Message user, that we couldn't find a compatible font vendor
			add_settings_error(
				'efpic',
				'font',
				/* translators: %s = Opening and closing link tags */
				'<strong>' . __( 'Font:', 'efpic-pro' ) . '</strong> ' . sprintf( __( 'We could not recognize the font embed code you entered. Please check our %sFAQs%s for more information on how to use external fonts.', 'efpic-pro' ), '<a href="https://efpic.io/faq#fonts">', '</a>' ),
				'error'
			);
		}
	}
	// Use one of the standard fonts
	else {
		unset( $args['external_font_name'] );
		unset( $args['external_font_code'] );

		// Check if the choice is one that we allow
		if ( ! isset( $args['font'] ) OR ( $args['font'] != '\'Proxima Nova\', \'proxima-nova\', Helvetica, Arial, sans-serif' AND $args['font'] != 'Tahoma, Arial, Helvetica, sans-serif' AND $args['font'] != 'Verdana, Geneva, sans-serif' AND $args['font'] != '\'Times New Roman\', Times, serif' AND $args['font'] != 'Georgia, Utopia, Palatino, \'Palatino Linotype\', serif' AND $args['font'] != '\'Courier New\', Courier, monospace' ) ) {
				// Reset to default, if none of the possible options was submitted
				$args['font'] = '\'Proxima Nova\', \'proxima-nova\', Helvetica, Arial, sans-serif';
		}
	}

	return $args;
}


/**
 * The file name composition setting HTML output.
 *
 * @since 1.1.0
 *
 * @return string The HTML output
 */
function efpic_pro_file_name_comp_setting() {
	ob_start();
?>
<fieldset class="efpic_settings__settings-item">
	<h2><?php _e( 'Image Title', 'efpic-pro' ); ?></h2>

	<p><?php _e( 'Define what will be displayed as image title by dragging properties onto the field below', 'efpic-pro' ); ?>:</p>

	<?php
		$title_sources = array(
			'number' => __( 'number', 'efpic-pro' ),
			'aperture' => __( 'aperture', 'efpic-pro' ),
			'camera' => __( 'camera', 'efpic-pro' ),
			'copyright' => __( 'copyright', 'efpic-pro' ),
			'file_extension' => __( 'file extension', 'efpic-pro' ),
			'filename' => __( 'filename', 'efpic-pro' ),
			'focal_length' => __( 'focal length', 'efpic-pro' ),
			'iso' => __( 'iso', 'efpic-pro' ),
			'shutter_speed' => __( 'shutter speed', 'efpic-pro' ),
			'title' => __( 'title', 'efpic-pro' )
		);
	?>

	<div class="efpic-image-title-sources js-efpic-image-title-sources">
		<span id="number" class="new"><?php echo $title_sources['number']; ?></span>
		<span id="aperture" class="new"><?php echo $title_sources['aperture']; ?></span>
		<span id="camera" class="new"><?php echo $title_sources['camera']; ?></span>
		<span id="copyright" class="new"><?php echo $title_sources['copyright']; ?></span>
		<span id="file_extension" class="new"><?php echo $title_sources['file_extension']; ?></span>
		<span id="filename" class="new"><?php echo $title_sources['filename']; ?></span>
		<span id="focal_length" class="new"><?php echo $title_sources['focal_length']; ?></span>
		<span id="iso" class="new"><?php echo $title_sources['iso']; ?></span>
		<span id="shutter_speed" class="new"><?php echo $title_sources['shutter_speed']; ?></span>
		<span id="title" class="new"><?php echo $title_sources['title']; ?></span>
	</div>

	<div class="efpic-image-title-composition js-efpic-image-title-composition">
		<?php
			$file_name_comp = get_option( 'efpic_file_name_comp', '[number] [filename]' );
			if ( ! empty( $file_name_comp ) ) {
				$tags = explode( ' ', $file_name_comp );

				foreach( $tags as $tag ) {
					echo '<span id="' . substr( $tag, 1, -1 ) . '" class="ui-draggable ui-draggable-handle">' . $title_sources[substr( $tag, 1, -1 )] . '<a class="efpic-title-comp-remove js-efpic-title-comp-remove" href="#">' . __( 'Remove title part', 'efpic-pro' ) . '</a></span>';
				}
			}
		?>
	</div>

	<input type="hidden" class="js-efpic-file-name-comp" name="efpic_file_name_comp" value="<?php echo $file_name_comp; ?>" />
</fieldset>
<?php
	return ob_get_clean();
}


/**
 * Validate file name composition setting.
 *
 * @since 1.1.0
 *
 * @param array $args The file name comp settings field value
 * @return array The validated (and sanitized) value
 */
function efpic_pro_validate_file_name_comp( $comp ) {
	return sanitize_text_field( $comp );
}


/**
 * The image size setting HTML output.
 *
 * @since 1.1.0
 *
 * @return string The HTML output
 */
function efpic_pro_image_size_setting() {
	ob_start();
	$image_size = get_option( 'efpic_image_size' );
?>
<fieldset class="efpic_settings__settings-item">
	<h2><?php _e( 'Image Size', 'efpic-pro' ); ?></h2>
	<p><?php _e( 'Set default thumbnail image size', 'efpic-pro' ); ?>:</p>
	<p>
		<span class="nowrap">
			<input type="radio" class="efpic-radio-image" name="efpic_image_size" id="image_size_small" value="small" <?php checked( $image_size, 'small' ); ?>/>
			<label for="image_size_small" class="after"><img class="theme-thumbnail" src="<?php echo EFPIC_URL; ?>/backend/images/image-size-small.png" alt="<?php _e( 'small', 'efpic-pro' ); ?>" /></label>
		</span>
		<span class="nowrap">
			<input type="radio" class="efpic-radio-image" name="efpic_image_size" id="image_size_medium" value="medium" <?php checked( $image_size, 'medium' ); ?>/>
			<label for="image_size_medium" class="after"><img class="theme-thumbnail" src="<?php echo EFPIC_URL; ?>/backend/images/image-size-medium.png" alt="<?php _e( 'medium', 'efpic-pro' ); ?>" /></label>
		</span>
		<span class="nowrap">
			<input type="radio" class="efpic-radio-image" name="efpic_image_size" id="image_size_large" value="large" <?php checked( $image_size, 'large' ); ?>/>
			<label for="image_size_large" class="after"><img class="theme-thumbnail" src="<?php echo EFPIC_URL; ?>/backend/images/image-size-large.png" alt="<?php _e( 'large', 'efpic-pro' ); ?>" /></label>
		</span>
	</p>
</fieldset>
<?php
	return ob_get_clean();
}


/**
 * Validate image size setting.
 *
 * @since 1.1.0
 *
 * @param array $args The image size settings field value
 * @return array The validated (and sanitized) value
 */
function efpic_pro_validate_image_size( $size ) {
	if ( in_array( $size, [ 'small', 'medium', 'large' ] ) ) {
		return $size;
	}

	return 'medium';
}


/**
 * Add Pro settings to email.
 *
 * @since 1.1.0
 *
 * @param array The settings array
 * @return array The filtered settings array
 */
function efpic_pro_add_email_settings( $settings ) {
	// Using the `_wrap` name to not overwrite templates when saving the email settings page
	$settings['email']['settings']['email_templates_wrap'] = [
		'type' => 'html',
		'output' => 'efpic_pro_email_templates_setting',
		'title' => __( 'Email Templates', 'efpic-pro' ),
		'default' => ''
	];

	return $settings;
}
	
add_filter( 'efpic_settings', 'efpic_pro_add_email_settings' );


/**
 * Email templates setting HTML output.
 *
 * @since 1.1.0
 *
 * @return string The HTML output
 */
function efpic_pro_email_templates_setting() {
	ob_start();
	$efpic_email_templates = get_option( 'efpic_email_templates' );
?>
<fieldset class="efpic_settings__settings-item efpic_settings__settings-item__email-templates">
	<h2><?php _e( 'Email templates', 'efpic-pro' ); ?></h2>
	<div class="efpic-settings__item">
		<span class="efpic-settings__label"><?php _e( 'Email message templates', 'efpic-pro' ); ?><br /><span class="description"><?php _e( 'Create/edit messsage templates. Set one as your default &#x2605;.', 'efpic-pro' ); ?></span></span>
		<div class="efpic-custom-email-text-wrap">
			<table class="efpic-custom-email-messages-table">
				<thead>
					<tr>
						<th title="<?php _e( 'Default Message', 'efpic-pro' ); ?>"></th>
						<th><?php _e( 'Template Name', 'efpic-pro' ); ?></th>
						<th><?php _e( 'Actions', 'efpic-pro' ); ?></th>
					</tr>
				</thead>
			<?php
			if ( is_array( $efpic_email_templates ) AND count( $efpic_email_templates ) >= 1 ) {
				echo '<tbody>';
				// List all the messages
				foreach( $efpic_email_templates as $message ) {
					// Gather classes
					$classes = array();
					if ( isset( $message['message_default'] ) AND $message['message_default'] === true ) {
						$classes[] = 'default-message';
					}
					// Make sure there are no spaces between <tbody> and the <tr>s and </tbody>, otherwise :empty will not work!
					?><tr class="<?php echo implode( ' ', $classes ); ?>">
							<td><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="square" stroke-linejoin="arcs"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></td>
							<td colspan="2">
								<div class="efpic-message-name-title"><?php echo $message['message_name']; ?></div>

								<div class="efpic-message-edit-box">
									<input type="text" class="efpic-message-name" value="<?php echo $message['message_name']; ?>" />
									<textarea class="efpic-message-body"><?php echo $message['message_body']; ?></textarea> <button class="button button-primary js-save-efpic-message"><?php _e( 'Save Message', 'efpic-pro' ); ?></button> <button class="button button js-cancel-efpic-message"><?php _e( 'Cancel', 'efpic-pro' ); ?></button>
								</div>

								<div class="efpic-message-actions">
									<a class="js-edit-efpic-message" href="#"><?php _e( 'Edit', 'efpic-pro' ); ?></a> | <a class="js-delete-efpic-message" href="#"><?php _e( 'Delete', 'efpic-pro' ); ?></a>
								</div>
							</td>
						</tr><?php
					}
				echo '</tbody>';
			}
			else {
				echo '<tbody></tbody>';
			}
				?>
				<tfoot class="no-templates-hint">
					<tr>
						<td></td>
						<td colspan="2"><?php _e( 'You have not saved any message templates yet', 'efpic-pro' ); ?></td>
					</tr>
				</tfoot>
			</table>
			<a class="button js-add-efpic-message" href="#"><?php _e( 'Add New Message', 'efpic-pro' ); ?></a>

			<div class="efpic-message-saving">
				<span class="indicator-saving"><?php _e( 'Saving', 'efpic-pro' ); ?></span>
				<span class="indicator-saved"><?php _e( 'Saved', 'efpic-pro' ); ?></span>
			</div>
		</div><!-- .efpic-custom-email-text-wrap -->
	</div><!-- .efpic-settings__item -->
</fieldset>
<?php
	return ob_get_clean();
}


/**
 * Add Pro settings to security.
 *
 * @since 1.1.0
 *
 * @param array The settings array
 * @return array The filtered settings array
 */
function efpic_pro_add_security_settings( $settings ) {
	// Security settings
	$settings['security']['description'] = __( 'Add a watermark to your images and more.', 'efpic-pro' );

	// Registration
	$settings['security']['settings']['registration_email_required'] = [
		'type' => 'checkbox',
		'label' => __( 'Require email address during client registration', 'efpic-pro' ),
		'description' => __( 'If enabled, clients are required to provide an email address during registration. Otherwise the email field will be optional.', 'efpic-pro' ) . ' <a class="efpic-help" href="https://go.efpic.io/help-client-registation">' . __( 'Help', 'efpic-pro' ) . '</a>',
		'default' => 'on',
		'new' => true
	];

	// Watermark
	$settings['security']['settings']['watermark'] = [
		'type' => 'html',
		'output' => 'efpic_pro_watermark_setting',
		'title' => __( 'Watermark', 'efpic-pro' ),
		'description' => __( 'Watermark', 'efpic-pro' ),
		'default' => [
			'watermark' => '',
			'watermark_by_default' => '',
			'watermark_position' => 'middle-center',
			'watermark_size' => 25,
			'watermark_sizing' => 'proportional',
		],
		'validation' => 'efpic_pro_validate_watermark',
	];

	// Disable right click
	$settings['security']['settings']['disable_right_click'] = [
		'type' => 'checkbox',
		'label' => __( 'Disable right click', 'efpic-pro' ),
		'description' => sprintf( __( 'Disables context menus in efpic collections.<br />Please be aware that this is not an effective method of protection. <a href="%s">Read more</a>', 'efpic-pro' ), 'https://efpic.io/docs/faq/#disable-right-click' ),
		'default' => 'off',
	];

	return $settings;
}
	
add_filter( 'efpic_settings', 'efpic_pro_add_security_settings' );


/**
 * The watermark setting HTML output.
 *
 * @since 1.1.0
 */
function efpic_pro_watermark_setting() {
	ob_start();
	$watermark = get_option( 'efpic_watermark' );
?>
	<fieldset class="efpic_settings__settings-item efpic_settings__settings-item__watermark" id="fs-watermark">
		<h2><?php _e( 'Watermark', 'efpic-pro' ); ?></h2>
		<input type="hidden" name="efpic_watermark[watermark]" id="watermark" value="<?php if ( ! empty( $watermark['watermark'] ) ) { echo $watermark['watermark']; } ?>" />
		<?php
			if ( ! empty( $watermark['watermark'] ) ) {
				$has_watermark = true;
				$replace_remove = ' active';
			}
			else {
				$has_watermark = false;
				$upload = ' active';
			}
		?>

		<?php
			$replace_remove = '';
			$upload = '';
			if ( ! empty( $watermark['watermark'] ) ) {
				$replace_remove = ' active';
			}
			else {
				$upload = ' active';
			}
		?>

		<p class="ui-replace-remove<?php echo $replace_remove; ?>">
			<a class="button add_watermark" href="#"><?php _e( 'Replace Watermark', 'efpic-pro' ); ?></a> <a class="button" id="remove_watermark" href="#"><?php _e( 'Remove Watermark', 'efpic-pro' ); ?></a>
		</p>
		<p class="ui-upload<?php echo $upload; ?>">
			<a class="button add_watermark" href="#"><?php _e( 'Set Watermark', 'efpic-pro' ); ?></a>
		</p>

		<div class="efpic-watermark-size-scaling-wrap js-efpic-watermark-size-scaling-wrap <?php if ( $has_watermark ) { echo ' has-watermark'; } ?>">

			<p class="efpic-settings__item efpic-watermark_by_default_wrap">
				<input type="checkbox" id="watermark_by_default" name="efpic_watermark[watermark_by_default]" <?php checked( $watermark['watermark_by_default'], 'on' ); ?>/> <label for="watermark_by_default" class="after"><?php _e( 'Apply watermark by default', 'efpic-pro' ); ?><br /><span class="description"><?php _e( 'You may change this for each collection before uploading images.' ); ?></span></label>
			</p>

			<div class="efpic-watermark-position-wrap js-watermark-wrap<?php if ( $watermark['watermark_sizing'] == 'fill' ) { echo ' watermark-fill'; } ?>">
				<h3>Position and size</h3>

				<div class="watermark-position">
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="top-left" value="top-left" <?php checked( $watermark['watermark_position'], 'top-left' ); ?>/>
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="top-center" value="top-center" <?php checked( $watermark['watermark_position'], 'top-center' ); ?> />
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="top-right" value="top-right" <?php checked( $watermark['watermark_position'], 'top-right' ); ?> />
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="middle-left" value="middle-left" <?php checked( $watermark['watermark_position'], 'middle-left' ); ?>/>
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="middle-center" value="middle-center" <?php checked( $watermark['watermark_position'], 'middle-center' ); ?>/>
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="middle-right" value="middle-right" <?php checked( $watermark['watermark_position'], 'middle-right' ); ?>/>
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="bottom-left" value="bottom-left" <?php checked( $watermark['watermark_position'], 'bottom-left' ); ?>/>
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="bottom-center" value="bottom-center" <?php checked( $watermark['watermark_position'], 'bottom-center' ); ?>/>
					<input type="radio" class="js-watermark-position" name="efpic_watermark[watermark_position]" id="bottom-right" value="bottom-right" <?php checked( $watermark['watermark_position'], 'bottom-right' ); ?>/>
					<label for="top-left"><?php _e( 'Top Left', 'efpic-pro' ); ?></label>
					<label for="top-center"><?php _e( 'Top Left', 'efpic-pro' ); ?></label>
					<label for="top-right"><?php _e( 'Top Right', 'efpic-pro' ); ?></label>
					<label for="middle-left"><?php _e( 'Middle Left', 'efpic-pro' ); ?></label>
					<label for="middle-center"><?php _e( 'Middle Left', 'efpic-pro' ); ?></label>
					<label for="middle-right"><?php _e( 'Middle Right', 'efpic-pro' ); ?></label>
					<label for="bottom-left"><?php _e( 'Bottom Left', 'efpic-pro' ); ?></label>
					<label for="bottom-center"><?php _e( 'Bottom Left', 'efpic-pro' ); ?></label>
					<label for="bottom-right"><?php _e( 'Bottom Right', 'efpic-pro' ); ?></label>
					<?php
					if ( ! empty( $watermark['watermark'] ) ) {
						echo '<img class="efpic-watermark-positioned js-watermark-positioned" src="' . wp_get_attachment_image_src( $watermark['watermark'], 'full' )[0] . '" alt="watermark" /><img class="watermark-shadow js-watermark-shadow" src="' . wp_get_attachment_image_src( $watermark['watermark'], 'full' )[0] . '" alt="" />';
					}
					?>
				</div>

				<p class="watermark-size-wrap"><input type="range" id="watermark_size" class="watermark-size" name="efpic_watermark[watermark_size]" min="1" max="100" value="<?php echo $watermark['watermark_size']; ?>" /> <span class="watermark-size-percent"><?php echo $watermark['watermark_size']; ?>%</span>

			</div><!-- .watermark-wrap -->

			<div class="efpic-watermark-scaling-wrap">
				<h3>Scaling</h3>
				<p><input type="radio" id="watermark_size_proportional" name="efpic_watermark[watermark_sizing]" value="proportional" <?php checked( $watermark['watermark_sizing'], 'proportional' ); ?> /> <label for="watermark_size_proportional" class="after"><?php _e( 'Proportional', 'efpic-pro' ); ?><br /><span class="description"><?php _e( 'The watermark will be scaled to the chosen percentage of the original image size.', 'efpic-pro' ); ?></span></label></p>

				<p><input type="radio" id="watermark_size_fill" name="efpic_watermark[watermark_sizing]" value="fill" <?php checked( $watermark['watermark_sizing'], 'fill' ); ?> /> <label for="watermark_size_fill" class="after"><?php _e( 'Fill', 'efpic-pro' ); ?><br /><span class="description"><?php _e( 'No scaling. The watermark will be centered and in its original size. Best if your watermark should cover the whole image.', 'efpic-pro' ); ?></span></label></p>
			</div>

		</div><!-- .efpic-watermark-size-scaling-wrap -->
	</fieldset>
	<?php
	return ob_get_clean();
}


/**
 * Validate watermark settings.
 *
 * @since 1.1.0
 *
 * @param array $args The watermark settings field values
 * @return array The validated (and sanitized) values
 */
function efpic_pro_validate_watermark( $args ) {
	// Sanitize watermark ID
	if ( ! empty( $args['watermark'] ) ) {
		// Check if image exists
		if ( ! get_post_status( $args['watermark'] ) ) {
			unset( $args['watermark'] );
		}
	}
	else {
		unset( $args['watermark'] );
	}

	// Sanitize watermark size
	if ( ! empty( $args['watermark_size'] ) AND intval( $args['watermark_size'] ) > 1 AND intval( $args['watermark_size'] ) <= 100 ) {
		$args['watermark_size'] = intval( $args['watermark_size'] );
	}
	else {
		unset( $args['watermark'] );
	}

	// Sanitize watermark by default setting
	if ( ! isset( $args['watermark_by_default'] ) OR 'on' != $args['watermark_by_default'] ) {
		$args['watermark_by_default'] = 'off';
	}

	return $args;
}


/**
 * Add Import settings tab.
 *
 * @since 1.1.0
 *
 * @param array The settings array
 * @return array The settings array with Import stuff added
 */
function efpic_pro_add_import_settings( $settings ) {
	// Import settings
	$settings['import'] = [
		'title' => __( 'Import', 'efpic-pro' ),
		'description' => __( 'Import images right from your web server.', 'efpic-pro' ),
		'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#007791" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.2 15c.7-1.2 1-2.5.7-3.9-.6-2-2.4-3.5-4.4-3.5h-1.2c-.7-3-3.2-5.2-6.2-5.6-3-.3-5.9 1.3-7.3 4-1.2 2.5-1 6.5.5 8.8M12 19.8V12M16 17l-4 4-4-4"/></svg>',
		'priority' => 35,
		'settings' => [
			'import_file_handling' => [
				'type' => 'radio',
				'title' =>  __( 'File Handling', 'efpic-pro' ),
				'description' => __( 'Choose what will happen to a source folder after it is successfully imported into a collection:', 'efpic-pro' ),
				'options' => [
					'keep_files' => [
						'label' => __( 'Do nothing', 'efpic-pro' ),
						'description' => __( 'The source folder will stay inside the <code>import</code> folder. You can even import the contained images again (and again).', 'efpic-pro' )
					],
					'move_files' => [
						'label' => __( 'Move files', 'efpic-pro' ),
						'description' => __( 'The source folder will be moved to the <code>_imported</code> folder. You need to clean up this folder regularly, so you don\'t fill up your web hosting space!', 'efpic-pro' )
					],
					'delete_files' => [
						'label' => __( 'Delete files', 'efpic-pro' ),
						'description' => __( 'The source folder and all files it contains will be deleted. <strong>This cannot be undone!</strong>', 'efpic-pro' )
					]
				],
				'default' => 'keep_files'
			]
		]
	];

	return $settings;
}

add_filter( 'efpic_settings', 'efpic_pro_add_import_settings' );


/**
 * Get all the efpic Pro settings.
 *
 * @since 1.1.0
 */
function efpic_pro_get_settings_for_telemetry() {
	$options = [
		'after_approval' => get_option( 'efpic_after_approval' ),
		'logo' => get_option( 'efpic_logo' ),
		'site_title' => get_option( 'efpic_site_title' ),
		'primary_color' => get_option( 'efpic_primary_color' ),
		'font' => get_option( 'efpic_font' ),
		'file_name_comp' => get_option( 'efpic_file_name_comp' ),
		'image_size' => get_option( 'efpic_image_size' ),
		'email_templates' => get_option( 'efpic_email_templates' ),
		'watermark' => get_option( 'efpic_watermark' ),
		'disable_right_click' => get_option( 'efpic_disable_right_click' ),
		'import_file_handling' => get_option( 'efpic_import_file_handling' )
	];

	return $options;
}


/**
 * Remove Pro banners from settings.
 *
 * @since 1.5.0
 *
 * @param array $settings The settings array
 * @return array The settings array including the banners
 */
function efpic_pro_remove_banners( $settings ) {
	unset( $settings['general']['settings']['pro-banner'] );
	unset( $settings['design-appearance']['settings']['pro-banner'] );
	unset( $settings['email']['settings']['pro-banner'] );
	unset( $settings['security']['settings']['pro-banner'] );

	return $settings;
}

add_filter( 'efpic_settings', 'efpic_pro_remove_banners', 11 );