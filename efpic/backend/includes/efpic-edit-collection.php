<?php
/**
 * Efpic edit collection
 *
 * Add our custom metabox, which replaces the default publish actions
 * Also load our custom colleciton edit screen and all its fields
 *
 * @since 0.8.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Register our metabox.
 *
 * @since 0.3.0
 */
function efpic_add_metabox() {
	add_meta_box(
		'efpic-submit-metabox',
		__( 'Collection Status', 'efpic' ),
		'efpic_collection_metabox',
		'efpic_collection',
		'side',
		'high'
	);

	add_meta_box(
		'efpic-collection-history',
		__( 'Collection History', 'efpic' ),
		'efpic_collection_history_metabox',
		'efpic_collection',
		'side',
		'low'
	);
}

add_action( 'add_meta_boxes', 'efpic_add_metabox' );


/**
 * Construct the metabox.
 *
 * @since 0.3.0
 *
 * @param object $post The collection post object
 */
function efpic_collection_metabox( $post ) {

	// Add a nonce field so we can check for it later
	wp_nonce_field( 'efpic_collection_metabox', 'efpic_collection_metabox_nonce' );

	// Get post status
	$post_status = $post->post_status;

	// Load share method
	if ( $post_status == 'delivery-draft' ) {
		$efpic_collection_share_method = get_post_meta( $post->ID, '_efpic_delivery_share_method', true );
	}
	else {
		$efpic_collection_share_method = get_post_meta( $post->ID, '_efpic_collection_share_method', true );
	}
	?>

	<div class="efpic-submit-metabox-inside">
		<div class="proof-status">
		<?php
			// Default status
			$collection_status = array(
				'draft' => 10,
				'open' => 50,
				'closed' => 100,
			);

			// Add support for custom collection status
			$collection_status = apply_filters( 'efpic_collection_status', $collection_status, $post );

			// Sorty by priority
			asort( $collection_status );

			foreach( $collection_status as $key => $value ) {
				$status_output = 'efpic_collection_status_' . $key;
				if ( function_exists( $status_output ) ) {
					echo $status_output( $post );
				}
			}
		?>
		</div>
	</div>

	<div class="efpic-post-options"><?php
	if ( ( 'delivered' == $post_status OR 'approved' == $post_status OR 'sent' == $post_status OR 'expired' == $post_status ) AND empty( $post->post_password ) ) {
		// Show nothing
	}
	elseif ( ( 'delivered' == $post_status OR 'approved' == $post_status OR 'sent' == $post_status OR 'expired' == $post_status ) AND ! empty( $post->post_password ) ) { ?>
		<div class="efpic-option-item">
			<a class="efpic-option-password-protected" href="#efpic-password"><span class="dashicons dashicons-lock"></span><?php _e( 'Show Password', 'efpic' ); ?></a>
			<div id="efpic-password" class="efpic-option-content is-hidden">
				<input type="text" id="post_password" name="post_password" maxlength="20" value="<?php echo $post->post_password; ?>" disabled="disabled" />
			</div>
		</div><?php
	} else {
		// Add passwort automatically
		$password_by_default = get_option( 'efpic_password_by_default' );
		$password = $post->post_password;
		if ( $password_by_default == 'on' AND empty( $password ) AND get_post_status() == 'auto-draft' ) {
			$password = wp_generate_password();
		}
		?>
		<div class="efpic-option-item">
			<a href="#efpic-password"><span class="dashicons dashicons-<?php if ( empty( $password ) ) { echo 'un'; } ?>lock"></span><?php _e( 'Password Protection', 'efpic' ); ?></a>
			<div id="efpic-password" class="efpic-option-content is-hidden">
				<div class="efpic-password-wrap">
					<label for="post_password"><?php _e( 'Enter Password', 'efpic' ); ?>:</label> <input type="text" id="post_password" name="post_password" maxlength="20" value="<?php echo $password; ?>" />
					<a href="#" class="efpic-remove-password js-efpic-remove-password is-hidden"><?php _e( 'Empty Password Field', 'efpic' ); ?></a>
				</div>
				<p class="efpic-hint js-efpic-password-hint-send<?php if ( 'efpic-copy-link' == $efpic_collection_share_method ) { echo ' is-hidden'; } ?>"><?php 

				$send_password_in_email = true;
	
				if ( get_option( 'efpic_send_password' ) != 'on' ) {
					$send_password_in_email = false;
				}

				$send_password_in_email = apply_filters( 'efpic_send_password_in_email', $send_password_in_email );

				if ( $send_password_in_email ) { _e( 'The password will be sent to the client with the email.', 'efpic' ); } else { _e( 'The password will <strong>not</strong> be sent with the email. Make sure to send it to the client separately.', 'efpic' ); } 
				
				?></p>
				<p class="efpic-hint js-efpic-password-hint-rememeber<?php if ( empty( $efpic_collection_share_method ) OR 'efpic-send-email' == $efpic_collection_share_method ) { echo ' is-hidden'; } ?>"><?php _e( 'Don\'t forget to sent the password to your client!', 'efpic' ); ?></p>
			</div>
		</div>
	<?php } ?>

	<?php echo efpic_collection_expiration_option( $post ); ?>

	<?php
		if ( in_array( $post_status, [ 'sent', 'approved', 'expired', 'delivered' ] ) ) { ?>
		<div class="efpic-option-item efpic-copy-collection-url">
			<input type="text" class="efpic-collection-url" name="collection-url" value="<?php the_permalink(); ?>" /><button class="dashicons efpic-copy-collection-url-to-clipboard" id="efpic-copy-collection-url-to-clipboard" title="<?php _e( 'Copy collection URL to clipboard', 'efpic' ); ?>" data-clipboard-target=".efpic-collection-url"><span class="screen-reader-text"><?php _e( 'Copy collection URL to clipboard', 'efpic' ); ?></span></button>
			<div class="efpic-tooltip-copied-url" role="tooltip"><?php _e( 'Copied', 'efpic' ); ?></div>
		</div>
	<?php } ?>

	</div><!-- .efpic-post-options -->

	<div id="submitpost">
		<div id="major-publishing-actions">
		<?php
			if ( get_post_status() == 'delivered' ) {
		?>
				<a class="button js-efpic-edit" href="<?php print wp_nonce_url( admin_url( "post.php?post=" . $post->ID . "&action=edit" ), 'efpic_collection_reopen_' . $post->ID, 'reopen' ); ?>"><?php _e( 'Edit Delivery', 'efpic' ); ?></a>

				<div class="efpic-modal efpic-warning is-hidden" id="js-efpic-edit">
					<div class="efpic-modal-inner">
						<div class="efpic-modal-content">
							<h3><?php _e( 'Caution!', 'efpic' ); ?></h3><p><?php _e ( 'You already delivered this collection to your client.', 'efpic' ); ?></p><p><strong><?php _e ( 'Are you sure you want to make changes?', 'efpic' ); ?></strong></p>
							<div class="efpic-modal-actions">
								<a class="button button-primary" href="<?php print wp_nonce_url( admin_url( "post.php?post=" . $post->ID . "&action=edit" ), 'efpic_collection_reopen_' . $post->ID, 'reopen' ); ?>"><?php _e( 'Yes, I am sure', 'efpic' ); ?></a> <a class="button efpic-cancel-modal js-efpic-cancel-modal" href=""><?php _e( 'Cancel', 'efpic' ); ?></a>
							</div>
						</div>
					</div>
				</div>
		<?php
			}
			elseif ( get_post_status() == 'sent' ) {
		?>
				<a class="button button-primary js-efpic-close" href="<?php print wp_nonce_url( admin_url( "post.php?post=" . $post->ID . "&action=close" ), 'efpic_collection_close_' . $post->ID, 'close' ); ?>"><?php _e( 'Close', 'efpic' ); ?></a>
				<a class="button js-efpic-edit" href="<?php print wp_nonce_url( admin_url( "post.php?post=" . $post->ID . "&action=edit" ), 'efpic_collection_reopen_' . $post->ID, 'reopen' ); ?>"><?php _e( 'Edit', 'efpic' ); ?></a>
		<?php 
			if ( efpic_get_selection_count( $post->ID ) > 0 ) {
		?>
				<a class="button js-efpic-duplicate" data-id="<?php echo $post->ID; ?>" href="<?php echo wp_nonce_url( admin_url( 'post.php?efpic_duplicate_collection=' . $post->ID ), 'efpic_duplicate_collection', 'efpic_duplication_nonce' ); ?>"><?php _e( 'Duplicate', 'efpic' ); ?>&hellip;</a>
		<?php
				echo efpic_get_duplication_modal( $post->ID );
			}
			else {
		?>
				<a class="button" href="<?php echo wp_nonce_url( admin_url( 'post.php?efpic_duplicate_collection=' . $post->ID ), 'efpic_duplicate_collection', 'efpic_duplication_nonce' ); ?>"><?php _e( 'Duplicate', 'efpic' ); ?></a>
		<?php
			}
		?>
				<div class="efpic-modal efpic-warning is-hidden" id="js-efpic-edit">
					<div class="efpic-modal-inner">
						<div class="efpic-modal-content">
							<h3><?php _e( 'Caution!', 'efpic' ); ?></h3><p><?php _e ( 'You already sent this collection to the client.', 'efpic' ); ?></p><p><strong><?php _e ( 'Are you sure you want to make changes?', 'efpic' ); ?></strong></p>
							<div class="efpic-modal-actions">
								<a class="button button-primary" href="<?php print wp_nonce_url( admin_url( "post.php?post=" . $post->ID . "&action=edit" ), 'efpic_collection_reopen_' . $post->ID, 'reopen' ); ?>"><?php _e( 'Yes, I am sure', 'efpic' ); ?></a> <a class="button efpic-cancel-modal js-efpic-cancel-modal" href=""><?php _e( 'Cancel', 'efpic' ); ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="efpic-modal efpic-warning is-hidden" id="js-efpic-close">
					<div class="efpic-modal-inner">
						<div class="efpic-modal-content">
							<h3><?php _e( 'Caution!', 'efpic' ); ?></h3>
							<p><?php _e ( 'You are about to close this collection.', 'efpic' ); ?>
							<?php
							if ( efpic_have_all_clients_approved( $post->ID ) ) {
								_e( 'New clients can no longer register themselves after that.', 'efpic' );
							}
							else {
								_e ( 'Clients can no longer submit their selections after that.', 'efpic' );
							}
							?></p>
							<p><strong><?php _e ( 'Are you sure you want to close this collection?', 'efpic' ); ?></strong></p>
							<div class="efpic-modal-actions">
								<a class="button button-primary" href="<?php print wp_nonce_url( admin_url( "post.php?post=" . $post->ID . "&action=close" ), 'efpic_collection_close_' . $post->ID, 'close' ); ?>"><?php _e( 'Yes, I am sure', 'efpic' ); ?></a> <a class="button efpic-cancel-modal js-efpic-cancel-modal" href=""><?php _e( 'Cancel', 'efpic' ); ?></a>
							</div>
						</div>
					</div>
				</div>

		<?php
			}
			// The wrapping #submitbox is needed for the submit buttons to work
			elseif ( get_post_status() != 'approved' && get_post_status() != 'expired' ) {
		?>
				<span class="efpic-save-button-wrap">
				<?php
				// Adds a submit button to save the collection without sending
				submit_button( __( 'Save', 'efpic' ), 'save-draft large', 'save', false, array( 'id' => 'save-post' ) );
				?>
				<span class="spinner"></span>
				</span>
				<?php
				// Filter initially seleced share method, default is send via email
				$default_share_method = apply_filters( 'efpic_default_share_method_is_email', true );

				if ( ( ! empty( $efpic_collection_share_method ) AND 'efpic-send-email' == $efpic_collection_share_method ) OR ( empty( $efpic_collection_share_method ) AND $default_share_method == true ) ) {
					$button_text = __( 'Send to Client', 'efpic' );
				}
				else {
					$button_text = __( 'Publish', 'efpic' );
				}

				// Adds a submit button to publish and send the collection to the client
				submit_button( $button_text, 'primary large', 'efpic_sendmail', false, array( 'id' => 'publish' ) );

			}
			else {
		?>
				<a class="button" href="<?php print wp_nonce_url( admin_url( "post.php?post=" . $post->ID . "&action=edit" ), 'efpic_collection_reopen_' . $post->ID, 'reopen' ); ?>"><?php _ex( 'Open', 'Button text, to open the collection', 'efpic' ); ?></a>
				<?php if ( efpic_get_selection_count( $post->ID ) > 0 ) { ?>
				<a class="button js-efpic-duplicate" data-id="<?php echo $post->ID; ?>" href="<?php echo wp_nonce_url( admin_url( 'post.php?efpic_duplicate_collection=' . $post->ID ), 'efpic_duplicate_collection', 'efpic_duplication_nonce' ); ?>"><?php _e( 'Duplicate', 'efpic' ); ?>&hellip;</a>
				<?php } else { ?>
					<a class="button" href="<?php echo wp_nonce_url( admin_url( 'post.php?efpic_duplicate_collection=' . $post->ID ), 'efpic_duplicate_collection', 'efpic_duplication_nonce' ); ?>"><?php _e( 'Duplicate', 'efpic' ); ?></a>
		<?php 
			}
		?>
			<?php echo efpic_get_duplication_modal( $post->ID );
		}
			do_action( 'efpic_after_major_publishing_actions', $post ); ?>

			<a class="efpic-delete" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php _e( 'Move to Trash' ); ?></a>
		</div>
	</div>
<?php
}


/**
 * Generate output for draft collection status.
 *
 * @since 1.5.0
 *
 * @param object $post The collection post object.
 * @return string The collection status.
 */
function efpic_collection_status_draft( $post ) {
	$post_status = $post->post_status;
	if ( ! in_array( $post_status, [ 'auto-draft', 'draft' ] ) ) {
		return;
	}

	$status = '<span class="status status--draft">';
	$status .= __( 'Create collection', 'efpic' );
	$status .= '</span>';

	return $status;
}


/**
 * Generate output for open collection status.
 *
 * @since 2.3.0
 *
 * @param object $post The collection post object.
 * @return string The collection status.
 */
function efpic_collection_status_open( $post ) {
	$post_status = $post->post_status;
	if ( ! in_array( $post_status, [ 'open', 'sent', 'publish' ] ) ) {
		return;
	}

	$status = '<span class="status status--open">';
	$status .= _x( 'Open', 'Collection post status', 'efpic' );
	$status .= '</span>';

	$status_meta_output = efpic_collection_status_meta( $post->ID );

	return $status . $status_meta_output;
}


/**
 * Generate output for closed collection status.
 *
 * @since 2.3.0
 *
 * @param object $post The collection post object.
 * @param string The collection status.
 */
function efpic_collection_status_closed( $post ) {
	$post_status = $post->post_status;
	if ( ! in_array( $post_status, [ 'closed', 'approved', 'expired' ] ) ) {
		return;
	}

	$status = '<span class="status status--closed">';
	$status .= __( 'Closed', 'efpic' );
	$status .= '</span>';

	$status_meta_output = efpic_collection_status_meta( $post->ID );

	return $status . $status_meta_output;
}


/**
 * Return the status meta table.
 *
 * @since 2.3.0
 *
 * @param int $collection_id The collection post ID.
 * @return string The collection status meta table.
 */
function efpic_collection_status_meta( $collection_id ) {
	$post_status = get_post_status( $collection_id );
	// Gather status meta
	$status_meta = [];

	// Open time
	$sent_time = efpic_get_collection_history_event_time( $collection_id, 'sent' );

	if ( $sent_time != false && in_array( $post_status, [ 'sent', 'approved', 'expired' ] ) ) {
		$status_meta[] = [
			'label' => __( 'Published', 'efpic' ),
			'data' => wp_date( get_option( 'date_format' ), $sent_time ) . ', ' . wp_date( get_option( 'time_format' ), $sent_time )
		];
	}

	// Number of images
	$num = efpic_get_collection_image_num( $collection_id );
	if ( $num > 0 ) {
		$status_meta[] = [
			'label' => __( 'Images', 'efpic' ),
			'data' => $num
		];
	}

	$expiration = get_post_meta( $collection_id, '_efpic_collection_expiration', true );
	$expiration_time = get_post_meta( $collection_id, '_efpic_collection_expiration_time', true );

	if ( $expiration == true && $expiration_time != false && ! in_array( $post_status, [ 'approved', 'expired', 'delivery-draft', 'delivered' ] ) ) {
		$classes = 'status__meta-table__expiry';
		if ( strtotime( '+1 day', time() ) > $expiration_time ) {
			$classes = add_cssclass( 'status__meta-table__expiry--soon', $classes );
		}
		$status_meta[] = [
			'label' => __( 'Expires', 'efpic' ),
			'data' => wp_date( get_option( 'date_format' ), $expiration_time ) . ', ' . wp_date( get_option( 'time_format' ), $expiration_time ),
			'class' => $classes
		];
	}

	$approval_time = efpic_get_collection_history_event_time( $collection_id, 'closed-manually' );
	$expired_time = efpic_get_collection_history_event_time( $collection_id, 'expired' );

	if ( $approval_time != false && $post_status == 'approved' ) { 
		$status_meta[] = [
			'label' => __( 'Closed', 'efpic' ),
			'data' => wp_date( get_option( 'date_format' ), $approval_time ) . ', ' . wp_date( get_option( 'time_format' ), $approval_time ),
		];
	}
	elseif ( $expired_time != false && $post_status == 'expired' ) {
		$status_meta[] = [
			'label' => __( 'Expired', 'efpic' ),
			'data' => wp_date( get_option( 'date_format' ), $expired_time ) . ', ' . wp_date( get_option( 'time_format' ), $expired_time ),
		];
	}

	// Prepare variable
	$status_meta_output = '';

	// Make it filterable
	$status_meta = apply_filters( 'efpic_status_meta', $status_meta, $collection_id );

	// Build the table
	if ( ! empty( $status_meta ) ) {
		$status_meta_output = '<div class="status__meta"><table class="status__meta-table">';
		foreach ( $status_meta as $meta ) {
			$class = '';
			if ( ! empty( $meta['class'] ) ) {
				$class = $meta['class'];
			}
			$status_meta_output .= '<tr><th class="' . $class . '">' . $meta['label'] . '</th>';
			$status_meta_output .= '<td class="' . $class . '">' . $meta['data'] . '</td></tr>';
		}
		$status_meta_output .= '</table></div>';
	}

	return $status_meta_output;
}


/**
 * Construct main edit screen.
 *
 * @since 0.8.0
 *
 * @param object $post The collection post object
 */
function efpic_main_edit_screen( $post ) {
	if ( $post->post_type != 'efpic_collection' ) {
		return;
	}

	$post_status = $post->post_status;

	// Add our default post status
	$post_statuses = array(
		'approved' => 'efpic_display_approved_view',
		'expired' => 'efpic_display_approved_view',
		'sent' => 'efpic_display_approved_view',
	);

	// Make it possible to add custom edit views per post status via filter
	$post_statuses = apply_filters( 'efpic_edit_screen_post_status', $post_statuses );

	if ( array_key_exists( $post_status, $post_statuses ) ) {
		// Execute callback function to display edit view by post status
		$post_statuses[$post_status]( $post );
	}
	/*
	 * Display draft view by default
	 */
	else {

		efpic_display_draft_view( $post );

		/*
		 * Include collection options
		 */
		$step = 2;

		// Pro modules use this filter to add their options
		$efpic_collection_options = apply_filters( 'efpic_collection_options', [] );

		if ( is_array( $efpic_collection_options ) AND 0 < count( $efpic_collection_options ) ) {

			$efpic_collection_options_output = '';

			// Wrap options in divs
			foreach ( $efpic_collection_options as $key => $option ) {
				$efpic_collection_options_output .= '<div class="efpic-option-set" id="' . $key . '">' . $option . '</div><!-- .efpic-option-set#' . $key . ' -->';
			}

			echo '<div class="efpic-collection-options"><h2><span class="stepcounter">' . $step . '</span>' . __( 'Collection Options', 'efpic' ) . '</h2>' . $efpic_collection_options_output . '</div><!-- .efpic-collection-options -->';

			// Add one to the step number
			$step++;
		}

		efpic_display_pro_hint();

		efpic_display_share_options_form( $post, $step );

	}
}

add_action( 'edit_form_after_title', 'efpic_main_edit_screen' );


/**
 * Display the share options on the collection edit screen.
 *
 * @since 1.5.0
 *
 * @param object $post The collection post object
 * @param int $step The step number on the collection edit screen
 * @param bool $disabled Wether this step is disabled
 */
function efpic_display_share_options_form( $post, $step = 2, $disabled = false ) {

	$efpic_collection_share_method = get_post_meta( $post->ID, '_efpic_collection_share_method', true );

	// Filter initially seleced share method, default is send via email
	$default_share_method = apply_filters( 'efpic_default_share_method_is_email', true );

	// If share method has been saved or is empty and true for default, use send email...
	if ( ( ! empty( $efpic_collection_share_method ) AND 'efpic-send-email' == $efpic_collection_share_method ) OR ( empty( $efpic_collection_share_method ) AND $default_share_method == true ) ) {
		$use_email_method = true;
	}
	else {
		$use_email_method = false;
	}

	// Check type, either delivery or regular collection
	$type = ( 'delivery-draft' == $post->post_status ) ? 'delivery' : 'collection';

	${'efpic_' . $type . '_email_address'} = efpic_get_collection_emails( $post->ID );
	${'efpic_' . $type . '_email_address'} = implode( ', ' , ${'efpic_' . $type . '_email_address'} );

	// Fallback for delivery collections
	if ( $type == 'delivery' ) {
		$temp = get_post_meta( $post->ID, '_efpic_' . $type . '_email_address', true );
		if ( ! empty( $temp ) ) {
			${'efpic_' . $type . '_email_address'} = get_post_meta( $post->ID, '_efpic_' . $type . '_email_address', true );
		}
	}

	${'efpic_' . $type . '_description'} = get_post_meta( $post->ID, '_efpic_' . $type . '_description', true );

	// Try to prefill email address from previous step
	if ( $type == 'delivery' AND empty( $efpic_delivery_email_address ) ) {
		$temp = efpic_get_collection_emails( $post->ID );

		if ( ! empty( $temp ) ) {
			$efpic_delivery_email_address = $temp;
		}
	}

	ob_start();
?>
<div class="efpic-share-options">
<h2><?php if ( ! $disabled ) { ?><span class="stepcounter"><?php echo ( $step ) ?: '2'; ?></span> <?php } ?><?php _e( 'Share Options', 'efpic' ); ?></h2>
	<ul class="efpic-share-select<?php if ( ! $disabled ) { echo ' js-efpic-share-select'; } ?>">
		<li><a<?php if ( $use_email_method ) { echo ' class="active"'; } ?> href="#efpic-send-email"><?php _e( 'Send via email', 'efpic' ); ?></a></li>
		<li><a<?php if ( $use_email_method == false ) { echo ' class="active"'; } ?> href="#efpic-copy-link"><?php _e( 'Copy link &amp; send manually', 'efpic' ); ?></a></li>
	</ul>
	<?php if ( ! $disabled ) { ?>
	<input type="hidden" class="js-efpic_collection_share_method" name="efpic_collection_share_method" value="<?php if ( $use_email_method == false ) { echo 'efpic-copy-link'; } else { echo 'efpic-send-email'; } ?>" />
	<?php } ?>
	<div class="efpic-share-option<?php if ( $use_email_method ) { echo ' is-active'; } ?>" id="efpic-send-email">
		<?php $user_name = get_the_author_meta( 'display_name', $post->post_author ); ?>
		<p><label for="efpic-<?php echo $type; ?>-email-address" title="<?php _e( 'Enter your clients email address', 'efpic' ); ?>"><?php _e( 'Client Email', 'efpic' ); ?>:</label>
			<span class="efpic-share-option-email-field"><?php
					$addresses = array_filter( explode( ', ', ${'efpic_' . $type . '_email_address'} ) );
					foreach ( $addresses as $address ) {
						echo '<span class="email-address">' . $address . '<span class="delete-email-address"></span></span>';
					}
				?><input type="email" id="efpicmultiemailfield" rows="1" spellcheck="false" autocomplete="off" autocapitalize="off" autocorrect="off" tabindex="1" aria-autocomplete="list" aria-haspopup="false" aria-expanded="false" autocomplete="off" list="email-history" name="notASearchField" /></span>
				<?php efpic_the_email_history_datalist(); ?>
			<input type="text" id="efpic-<?php echo $type; ?>-email-address" name="efpic_<?php echo $type; ?>_email_address" value="<?php echo ${'efpic_' . $type . '_email_address'}; ?>" <?php if ( $disabled ) { echo 'disabled="disabled"'; } ?> autocomplete="off" />
		<?php if ( ! efpic_is_pro_active() ) { ?>
		<span class="efpic-multi-client-pro-hint hidden"><span class="efpic-multi-client-pro-hint__icon">🚀</span> <span class="efpic-multi-client-pro-hint__content"><?php
			/* translators: Opening and closing link tags */
			echo sprintf( __( 'Send your collection to multiple clients with %sefpic Pro%s.', 'efpic' ), '<a href="https://efpic.io/pro/?utm_source=efpic_plugin&utm_medium=mutli-client-hint" target="_blank">', '</a>' );
		?></span></span></p>
		<?php } ?>

		<p><label for="efpic-<?php echo $type; ?>-description" title="<?php _e( 'The description will be sent to your client via email.', 'efpic' ); ?>"><?php _e( 'Message', 'efpic' ); ?>:</label>
			<textarea class="js-efpic-collection-description" id="efpic-<?php echo $type; ?>-description" name="efpic_<?php echo $type; ?>_description" cols="60" rows="13"<?php if ( $disabled ) { echo ' disabled="disabled"'; } ?>><?php if ( esc_attr( ${'efpic_' . $type . '_description'} ) ) {
					echo esc_attr( ${'efpic_' . $type . '_description'} );
				} elseif ( 'delivery-draft' == $post->post_status ) {
					$mail_message = sprintf( __( 'Dear Client,&#10;&#10;Your images are ready!&#10;&#10;You can download them by following the link below.&#10;&#10;Sincerely,&#10;%s', 'efpic' ), $user_name );
					$mail_message = apply_filters( 'efpic_delivery_client_mail_message', $mail_message, $user_name );
					echo $mail_message;
				} elseif ( 'auto-draft' == $post->post_status ) {
					$mail_message = sprintf( __( 'Dear Client,&#10;&#10;Please select the photos you like and send your selection back to us. We will start post-production as soon as we have your approval.&#10;&#10;Sincerely,&#10;%s', 'efpic' ), $user_name );
					$mail_message = apply_filters( 'efpic_client_mail_message', $mail_message, $user_name );
					echo $mail_message;
				}
			?></textarea></p>
			<?php
				do_action( 'efpic_after_collection_description' );
			?>
	</div><!-- .efpic-share-option #efpic-send-email -->
	<div class="efpic-share-option<?php if ( $use_email_method == false ) { echo ' is-active'; } ?>" id="efpic-copy-link">
			<p><label for="efpic-collection-link"><?php _e( 'Copy URL', 'efpic' ); ?></label><input type="text" id="efpic-collection-link" name="efpic-collection-link" value="<?php echo get_draft_permalink( $post->ID ); ?>" <?php if ( $disabled ) { echo 'disabled="disabled"'; } ?>/></p>
			<p class="efpic-hint"><?php _e( '<strong>Please note:</strong> efpic will <strong>NOT</strong> send an email. Make sure to copy and send the link to your client manually.', 'efpic' ); ?></p>
	</div><!-- .efpic-share-option #efpic-copy-link -->
</div><!-- .efpic-share-options -->
<?php
	echo ob_get_clean();
}


/**
 * Display the draft view on the collection edit screen.
 *
 * @since 1.5.0
 *
 * @param object $post The collection post object
 */
function efpic_display_draft_view( $post ) {

	// Load the IDs of all uploaded images into an array
	$gallery_data = get_post_meta( $post->ID, '_efpic_collection_gallery_ids', true );
	if ( ! empty( $gallery_data ) ) {
		$gallery_image_ids = explode( ',', $gallery_data );
		$gallery_image_count = count( $gallery_image_ids );
	}
	else {
		$gallery_image_ids = '';
		$gallery_image_count = 0;
	}

	$gallery_class = '';
	if ( ! empty( $gallery_data ) ) {
		$gallery_class = ' efpic-gallery-has-images';
	}
	if ( 10 < $gallery_image_count ) {
		$gallery_class .= ' is-collapsible js-collapsed';
	}

	ob_start();
?>
	<div class="postbox efpic-postbox <?php echo $gallery_class; ?>">
		<div class="efpic-postbox-inner">
			<?php
				$efpic_section_header_1 = __( 'Upload Images', 'efpic' );
				$efpic_section_header_1 = apply_filters( 'efpic_section_header_1', $efpic_section_header_1 );
			?>
			<h2><span class="stepcounter">1</span> <?php echo $efpic_section_header_1; ?></h2>
			<div class="efpic-sort-options-wrapper">
				<select class="efpic-sort-options__select" name="sort-collection">
					<?php /* translators: Default option in a select menu */ ?>
					<option value=""><?php _e( 'Select image order', 'efpic' ); ?>&hellip;</option>
					<?php /* translators: Option in a select menu */ ?>
					<option value="order-by-name-asc"><?php _e( 'Order by name (ASC)', 'efpic' ); ?></option>
					<?php /* translators: Option in a select menu */ ?>
					<option value="order-by-name-desc"><?php _e( 'Order by name (DESC)', 'efpic' ); ?></option>
					<?php /* translators: Option in a select menu */ ?>
					<option value="order-by-created-asc"><?php _e( 'Order by created date (ASC)', 'efpic' ); ?></option>
					<?php /* translators: Option in a select menu */ ?>
					<option value="order-by-created-desc"><?php _e( 'Order by created date (DESC)', 'efpic' ); ?></option>
				</select>
				<?php /* translators: Button text */ ?>
				<button class="efpic-sort-options__button button button-small" name="sort-collection-submit"><?php _e( 'Sort', 'efpic' ); ?></button>
			</div>
			<div class="efpic-gallery-thumbnails">
			<?php

			// Define the drag & drop zone for our uploader
			if ( ! empty( $gallery_image_ids ) ) {

				// Loop through all uploaded images
				foreach ( $gallery_image_ids as $gallery_image_id ) {

					// Load filename
					$img_name = wp_get_attachment_image_src( $gallery_image_id, 'full' );

					// Define the attributes to output with our thumbnails
					$attr = array(
						'title' => basename( $img_name[0] ),
						'draggable' => 'false'
					);

					// Construct the image markup
					echo '<figure>';
					echo '<div class="efpic-gallery-thumbnail-box">';
					echo '<div class="efpic-gallery-thumbnail-box-inner">';
					echo wp_get_attachment_image( $gallery_image_id, 'efpic-small', 0, $attr );
					echo '</div></div></figure>';
				}
				
			}
			?>
			</div><!-- .efpic-gallery-thumbnails -->
			<div class="toggle-efpic-gallery-height">
				<a href="#" class="js-toggle-efpic-gallery-height"><span class="show"><?php _e( 'Show all images', 'efpic' ); ?></span><span class="hide"><?php _e( 'Hide images', 'efpic' ); ?></span></a>
			</div>

			<?php
			$efpic_before_upload = '';
			$efpic_before_upload = apply_filters( 'efpic_before_upload', $efpic_before_upload );
			echo $efpic_before_upload;

			?>
			<div class="efpic-gallery-uploader">
				<input type="text" id="efpic-gallery-ids" name="efpic_gallery_ids" class="hidden" value="<?php echo $gallery_data; ?>">
				<?php wp_nonce_field( 'efpic_gallery_ids', 'efpic_gallery_ids_nonce' ); ?>
				<p class="efpic-drag-info"><?php _e( 'Drag and drop your images here or click the button to upload', 'efpic' ); ?></p>
				<p><a class="button efpic-upload-image-button" href="#"><?php _e( 'Upload / Edit Images', 'efpic' ); ?></a></p>
				<p class="efpic-max-file-size"><?php echo __( 'Maximum upload size', 'efpic' ) . ': ' . size_format( wp_max_upload_size() ); ?> <a class="efpic-help" href="https://efpic.io/docs/faq#maximum-upload-size" target="_blank"><?php _e( 'Help', 'efpic' ); ?></a></p>
			</div><!-- .efpic-gallery-uploader -->
		</div><!-- .efpic-postbox-inner -->
	</div><!-- .postbox.efpic-postbox -->
<?php
	echo ob_get_clean();
}


/**
 * Display the approved view on the collection edit screen.
 *
 * @since 1.5.0
 *
 * @param object $post The post object
 * @param bool $collapsible Wether to make the postbox collapsible
 */
function efpic_display_approved_view( $post, $collapsible = false ) {

	// Gather collection infos
	$collection_status = $post->post_status;
	$efpic_collection_gallery_ids = get_post_meta( $post->ID, '_efpic_collection_gallery_ids', true );

	$gallery_image_ids = [];
	if ( ! empty( $efpic_collection_gallery_ids ) ) {
		$gallery_image_ids = explode( ',', $efpic_collection_gallery_ids );
	}

	// Prepare selection data
	$selection_image_count = 0;
	$selection_image_ids = [];
	$selection_data = get_post_meta( $post->ID, '_efpic_collection_selection', true );
	if ( ! empty( $selection_data ) ) {
		$selection_image_ids = $selection_data['selection'];
		$selection_image_count = ( is_array( $selection_data['selection'] ) ) ? count( $selection_data['selection'] ) : 0;
	}
	$img_filenames = efpic_get_approved_filenames( $post->ID );

	// Get data for mutli client collections and prepare selection data
	$efpic_collection_hashes = get_post_meta( $post->ID, '_efpic_collection_hashes', true );
	$multi_selection_image_ids = [];
	$efpic_multi_selections = [];
	$efpic_multi_selected_by_all = [];
	$efpic_multi_selected_by_no_one = [];
	$efpic_multi_selected_by_all_count = 0; // To determine how many clients have made a selection
	$efpic_multi_approval_messages = [];
	$efpic_multi_approval_fields = [];
	$i = 0;

	// Prepare image votes
	$image_votes = [];
	foreach( $gallery_image_ids as $id ) {
		$image_votes['image_' . $id] = 0;
	}

	// Calculate different selection for multi-client collections
	if ( ! empty( $efpic_collection_hashes ) ) {

		foreach( $efpic_collection_hashes as $key => $hash ) {

			// Fill individual selections
			$selection = get_post_meta( $post->ID, '_efpic_collection_selection_' . $key, true );

			if ( ! empty( $selection ) ) {
				if ( isset( $selection['selection'] ) ) {
					$efpic_multi_selections[$key] = $selection['selection'];
				}

				// Fill filenames
				if ( ! empty( $selection['selection'] ) ) {
					$multi_selection_image_ids = array_merge( $multi_selection_image_ids, $selection['selection'] );
				}

				// Fill selected by all
				if ( $i === 0 ) {
					// Set the selection for the first time
					if ( isset( $selection['selection'] ) AND is_array( $selection['selection'] ) ) {
						$efpic_multi_selected_by_all = $selection['selection'];
					}
					$efpic_multi_selected_by_all_count++;
				}
				else {
					if ( isset( $selection['selection'] ) AND is_array( $selection['selection'] ) ) {
						$efpic_multi_selected_by_all = array_intersect( $efpic_multi_selected_by_all, $selection['selection'] );
						$efpic_multi_selected_by_all_count++;
					}
				}
				$i++;

				// Gather images selected by no one 
				if ( ! empty( $selection['selection'] ) ) {
					$efpic_multi_selected_by_no_one = array_merge( $efpic_multi_selected_by_no_one, $selection['selection'] );
				}
				
				// Fill multi client approval messages
				if ( isset( $selection['approval_message'] ) ) {
					$efpic_multi_approval_messages[$key] = $selection['approval_message'];;
				}

				// Fill multi client approval fields
				if ( isset( $selection['approval_fields'] ) ) {
					$efpic_multi_approval_fields[$key] = $selection['approval_fields'];;
				}

				// Count votes
				if ( ! empty( $selection['selection'] ) ) {
					foreach( $selection['selection'] as $value ) {
						if ( isset( $image_votes['image_' . $value] ) ) {
							$image_votes['image_' . $value]++;
						}
					}
				}
			}
		}


		// Get all the at least once selected images
		$multi_selection_image_ids = array_unique( $multi_selection_image_ids );
		$selection_image_ids = $multi_selection_image_ids;

		// Update selection count (defaults to selected at least once)
		$selection_image_count = count( $multi_selection_image_ids );

		// Get images that are not selected by anyone
		$efpic_multi_selected_by_no_one = array_diff( $gallery_image_ids, array_unique( $efpic_multi_selected_by_no_one ) );

		// Sort by number of votes
		arsort( $image_votes );
	}

	ob_start();

	if ( efpic_has_collection_been_closed( $post->ID ) && ( $collection_status == 'delivery-draft' OR $collection_status == 'delivered' ) ) {?>
	<input type="checkbox" class="efpic-toggle-approved-view-toggle" id="efpic-toggle-approved-view" autocomplete="off" />
	<?php } ?>

	<div class="postbox efpic-postbox<?php if ( $collection_status == 'delivery-draft' OR $collection_status == 'delivered' ) { echo ' efpic-postbox-approved'; } ?>">
		<header class="efpic-postbox-header">
			<h2><?php _e( 'Selection Summary', 'efpic' ); ?>
			<?php if ( $collection_status == 'delivery-draft' OR $collection_status == 'delivered' ) { ?>
			<div class="efpic-toggle-approved-view">
				<label class="efpic-toggle-show-approved-view" for="efpic-toggle-approved-view"><?php _e( 'Show Details', 'efpic' ); ?></label>
				<label class="efpic-toggle-hide-approved-view" for="efpic-toggle-approved-view"><?php _e( 'Hide Details', 'efpic' ); ?></label>
			</div>
			<?php } ?>
			</h2>
			<?php if ( efpic_is_pro_active() && $collection_status != 'delivery-draft' && $collection_status != 'delivered' ) { ?>
			<button class="button js-efpic-add-client">+ <?php /* translators: Button text */ _e( 'Add Client', 'efpic' ); ?></button>
			<?php } ?>
		</header>

		<div class="efpic-postbox-inner">
			<div class="efpic-approval-summary">
				<div class="recipient-wrap">
			<?php 
				// Multi-Client collection
				if ( ! empty( $efpic_collection_hashes ) ) {
					// Sort by time
					uasort( $efpic_collection_hashes, fn( $a, $b ) => $a['time'] <=> $b['time'] );
					// // Sort by status
					uasort( $efpic_collection_hashes, fn( $a, $b ) => $a['status'] <=> $b['status'] );

					// Iterate through clients
					foreach( $efpic_collection_hashes as $key => $hash ) {
						// Gather client data
						$selection = get_post_meta( $post->ID, '_efpic_collection_selection_' . $key, true );
						$time = ( ! empty( $selection['time'] ) ) ? $selection['time'] : $hash['time'];
						$date = wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $time );
						// Set status styles
						$status = $hash['status'];
						switch ( $status ) {
							case 'approved':
								$status_label = __( 'Approved', 'efpic' );
								break;
							case 'failed':
								$status_label = __( 'Failed', 'efpic' );
								break;
							default:
								$status = 'waiting';
								$status_label = __( 'Waiting', 'efpic' );
						}

						$client_selection_data = [
							'ident' => $key,
							'collection_id' => $post->ID,
							'status' => $status,
							'status_label' => $status_label,
							'name' => ( ! empty( $hash['name'] ) ) ? $hash['name'] : '',
							'email' => ( ! empty( $hash['email'] ) ) ? $hash['email'] : '',
							'date' => $date,
							'selection' => $selection,
							'selection_count' => ( ! empty( $selection['selection'] ) ) ? count( $selection['selection'] ) : '',
							'approval_fields' => (! empty( $selection['approval_fields'] ) ) ? $selection['approval_fields'] : [],
						];

						efpic_display_client_summary( $client_selection_data );
					}
				}

				// Legacy fallback: Collections withouth `_efpic_collection_hashes` 
				else {
					$status = $collection_status;
					$email = get_post_meta( $post->ID, '_efpic_collection_email_address', true );
					$selection = get_post_meta( $post->ID, '_efpic_collection_selection', true );
					$date = wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), efpic_get_collection_history_event_time( $post->ID, 'approved' ) );

					switch ( $status ) {
						case 'approved':
							$status_label = 'Approved';
							break;
						default:
							$status = 'waiting';
							$status_label = 'Waiting';
					}

					$client_selection_data = [
						'ident' => '',
						'collection_id' => $post->ID,
						'name' => '',
						'email' => $email,
						'status' => $status,
						'status_label' => $status_label,
						'date' => $date,
						'selection' => $selection,
						'selection_count' => ( ! empty( $selection['selection'] ) ) ? count( $selection['selection'] ) : '',
						'approval_fields' => (! empty( $selection['approval_fields'] ) ) ? $selection['approval_fields'] : [],
					];

					efpic_display_client_summary( $client_selection_data );
				}
			?>
					<div class="recipient recipient--new is-hidden" id="js-new-recipient">
						<div class="recipient__inner">
							<?php wp_nonce_field( 'efpic_add_client', 'efpic_add_client' ); ?>
							<span class="recipient__field-wrap">
								<label class="recipient__label" for="efpic-new-recipient-name"><?php /* translators: Label for name input field */ _e( 'Client name', 'efpic' ); ?></label>
								<input type="text" id="efpic-new-recipient-name" name="efpic-new-recipient-name" />
							</span>
							<span class="recipient__field-wrap">
								<label class="recipient__label" for="efpic-new-recipient-email"><?php /* translators: Label for email input field */ _e( 'Client email address', 'efpic' ); ?></label>
								<input type="email" id="efpic-new-recipient-email" name="efpic-new-recipient-email" />
							</span>
							<button class="button button-primary"><?php /* translators: Button text */ _e( 'Add Client', 'efpic' ); ?></button> <button class="button js-efpic-cancel-new-recipient"><?php /* translators: Button text */ _e( 'Cancel', 'efpic' ); ?></button>
							<?php
								// Check checkbox by default?
								$checked = false;
								$share_method = get_post_meta( $post->ID, '_efpic_collection_share_method', true );
								if ( $share_method == 'efpic-send-email' ) {
									$checked = true;
								}
							?>
							<p class="recipient__send-email-wrap"><input type="checkbox" id="efpic-new-recipient-send-email" name="efpic-new-recipient-send-email" disabled <?php checked( $checked, true ); ?> /> <label for="efpic-new-recipient-send-email"><?php _e( 'Send original message and collection link to this email address.', 'efpic' ); ?></label></p>
						</div><!-- .recipient__inner -->
					</div>
				</div><!-- .recipients-wrap -->

				<?php do_action( 'efpic_after_recipients' ); ?>

			</div><!-- .efpic-approval-summary -->
			<div class="efpic-toolbar">
				<div class="efpic-filter">
					<label for="efpic-table-filter-show"><?php _e( 'Show', 'efpic' ); ?>:</label>
						<?php
							// Switch the option titles, depending on collection status
							if ( $selection_image_count > 0 ) {
								if ( $collection_status == 'sent' ) {
									$filter_options = array(
										'selected' => _x( 'Selected', 'photo filter dropdown', 'efpic' ),
									);
								}
								else {
									$filter_options = array(
										'selected' => _x( 'Approved', 'photo filter dropdown', 'efpic' ),
									);
								}
							}

							// Add multi client filter options
							if ( ! empty( $efpic_collection_hashes ) && count( $efpic_collection_hashes ) > 1 ) {
								if ( $selection_image_count > 0 ) {
									$filter_options['selected-by-all'] = _x( 'Selected by all', 'photo filter dropdown', 'efpic' );
									$filter_options['selected'] = _x( 'Selected at least once', 'photo filter dropdown', 'efpic' );

									// Add filter by individual client
									foreach( $efpic_collection_hashes as $key => $hash ) {
										/* translators %s = client name & email address */
										$filter_options[$key] = sprintf( _x( 'Selected by %s', 'photo filter dropdown', 'efpic' ), efpic_combine_name_email( $hash['name'], $hash['email'] ) );
									}
								}
							}

							// Add 'not selected' filter
							$filter_options['not-selected'] = _x( 'Not selected', 'photo filter dropdown', 'efpic' );
							// Add 'all' filter
							$filter_options['all'] = _x( 'All', 'photo filter dropdown', 'efpic' );

							// Add filter for custom options
							$filter_options = apply_filters( 'efpic_table_filter_options', $filter_options, $post );

							$filename_separator = ( defined( 'EFPIC_FILENAME_SEPARATOR' ) ) ? EFPIC_FILENAME_SEPARATOR : ' ';
							$filename_separator = apply_filters( 'efpic_filename_separator', $filename_separator );
						?>
						<select id="efpic-table-filter-show" autocomplete="off" data-filename-separator="<?php echo $filename_separator; ?>">
						<?php
							foreach( $filter_options as $key => $option ) {
								echo '<option value="' . $key . '">' . $option . '</option>';
							}
						?>
					</select>
					<label for="efpic-copy-filenames"><?php _e( 'Copy Filenames', 'efpic' ); ?>:</label>
					<input id="efpic-copy-filenames" type="text" value="<?php echo $img_filenames; ?>" />
					<span class="button button-primary efpic-copy-to-clipboard<?php if ( empty( $img_filenames ) ) { echo ' disabled'; } ?>" role="button" tabindex="0" data-clipboard-text="<?php echo $img_filenames; ?>"><?php /* translators: Button text */ printf( _n( 'Copy %s Filename', 'Copy Filenames %s', $selection_image_count, 'efpic' ), '<span class="filename-count">(' .number_format_i18n( $selection_image_count ) . ')</span>' ); ?></span>
				</div><!-- .efpic-filter -->

				<div class="efpic-copy">
					<?php
						// Get proof file type
						$proof_file_type = pathinfo( apply_filters( 'efpic_proof_file_name', 'file.txt', $post->ID ), PATHINFO_EXTENSION );
						if ( ! empty( $proof_file_name ) ) {
							$proof_file_type = '.' . $proof_file_type;
						}
					?>
					<a class="button <?php if ( efpic_get_selection_count( $post->ID ) <= 0 ) { echo ' disabled'; } ?>" role="button" tabindex="0" href="<?php if ( efpic_get_selection_count( $post->ID ) > 0 ) { echo admin_url( 'post.php?post=' . $post->ID . '&action=edit&efpic-download=efpic-proof-file' ); } else { echo '#'; } ?>"><?php /* translators: Button text */ _e( 'Download Proof', 'efpic' ); ?> (.<?php echo $proof_file_type; ?>)</a>
				</div><!-- .efpic-copy -->
			</div><!-- .efpic-toolbar -->

			<?php
				/**
				 * Table View
				 */

				// Prepare column array
				$efpic_overview_table_columns = array(
					'thumbnail' => __( 'Thumbnail', 'efpic' ),
					'file' => __( 'File', 'efpic' ),
					'approved' => __( 'Approved', 'efpic' ),
				);

				if ( $collection_status == 'sent' ) {
					$efpic_overview_table_columns['approved'] = __( 'Selected', 'efpic' );
				}

				// Add a filter
				$efpic_overview_table_columns = apply_filters( 'efpic_selection_overview_table_columns', $efpic_overview_table_columns, $post->ID );
			?>
			<div class="efpic-table-wrap">
				<table class="efpic-selection-overview-table js-efpic-selection-overview-table">
					<thead>
						<tr>
						<?php
							foreach( $efpic_overview_table_columns as $column_class => $column_headline ) {
								echo '<th class="' . $column_class . '">' . $column_headline .'</th>';
							}
						?>
						</tr>
					</thead>
					<tbody>
					<?php
					$i = 1;
					foreach ( $gallery_image_ids as $gallery_image_id ) {
						$filename = efpic_get_image_filename( $gallery_image_id );
						$image = wp_get_attachment_image_src( $gallery_image_id, 'efpic-small' );
						$image_classes = array();
						$image_classes[] = 'efpic-selection-table-image';

						// Add classes for selected & not-selected
						if ( ! empty( $efpic_collection_hashes ) AND count( $efpic_multi_selected_by_no_one ) > 0 ) {
							// Check if the current image is in the "no one selected these" array
							if ( in_array( $gallery_image_id, $efpic_multi_selected_by_no_one ) ) {
								$image_classes[] = 'not-selected';
							}
						}
						// If this is no multi-client collection, we do it the easy way: either selected or not
						elseif ( isset( $selection_image_ids ) AND is_array( $selection_image_ids ) ) {
							$image_classes[] = ( in_array( $gallery_image_id, $selection_image_ids ) ) ? 'selected' : 'not-selected';
						}

						// Add class for selected by all, if more than one client has actally made a selection
						if ( $efpic_multi_selected_by_all_count > 1 ) {
							if ( in_array( $gallery_image_id, $efpic_multi_selected_by_all ) ) {
								$image_classes[] = 'selected-by-all';
							}
						}

						// Add classes for images selected by invidual clients
						if ( ! empty( $efpic_collection_hashes ) ) {
							foreach( $efpic_collection_hashes as $key => $hash ) {
								$selection = get_post_meta( $post->ID, '_efpic_collection_selection_' . $key, false );
								foreach( $efpic_multi_selections as $key => $selection ) {
									if ( is_array( $selection) AND in_array( $gallery_image_id, $selection ) ) {
										$image_classes[] = $key;
										$image_classes[] = 'selected';
									}
								}
							}
						}

						/**
						 * Add custom classes to each row using the 'filter_options' array from above
						 * Callback function 'efpic_image_{option name}' to add {option_name} as class
						 */
						foreach( $filter_options as $option_name => $option_title ) {

							// No callback for the default options
							if ( $option_name == 'all' OR $option_name == 'selected' OR $option_name == 'not-selected' ) {
								continue;
							}
							else {
								// Run the callback function 'efpic_image_{option_name}'
								$function = 'efpic_image_' . $option_name;
								if ( function_exists( $function ) ) {
									if ( $function( $gallery_image_id, $post ) ) {
										$image_classes[] = $option_name;
									}
								}
							}
						}

						// Remove duplicates
						$image_classes = array_unique( $image_classes );

						// Get the classes as a space separated string
						$image_classes = implode( ' ', $image_classes );

						ob_start();

						$img_filename = htmlspecialchars( efpic_get_image_filename( $gallery_image_id ) );

						?>

						<tr class="<?php echo $image_classes; ?>" data-filename="<?php echo $img_filename; ?>" <?php if ( is_array( $selection_image_ids ) AND count( $selection_image_ids ) > 0 AND ! in_array( $gallery_image_id, $selection_image_ids ) ) { echo ' style="display: none;"'; } ?>>
							<?php
								// Iterate through table columns
								$column_count = 0;
								foreach( $efpic_overview_table_columns as $key => $value ) {
									// Thumbnail column
									if ( $key == 'thumbnail' ) { ?>
										<td class="thumb"><a href="<?php echo get_the_permalink( $post->ID ) . '#' . $i; ?>" target="_blank"><img src="<?php echo $image[0]; ?>" alt="<?php echo $filename; ?>" /></a></td><?php
									}
									// Filename column
									elseif ( $key == 'file' ) {
										?><td class="file"><?php echo $filename; ?></td><?php
									}
									// Selected column
									elseif ( $key == 'approved' ) { ?>
										<td class="approved">
											<span class="approved-marker"></span>
										<?php
										if ( isset( $image_votes['image_' . $gallery_image_id] ) ) {
											$votes = $image_votes['image_' . $gallery_image_id];
											if ( $votes > 0 ) {
												echo '<span class="selection-count">' . $votes . '</span>';
											}
										}
										?>
										</td>
									<?php }
									// Third party columns
									else {
										/*
										 * Get column content via a filter
										 *
										 * Column is empty by default
										 */
										$column_content = apply_filters( 'efpic_selection_overview_table_column_' . $key . '_content', '', $post->ID, $gallery_image_id, $i );
									?>
										<td class="<?php echo $key; ?>"><?php echo $column_content; ?></td>
									<?php
									}
									$column_count++;
								}
							?>
						</tr>
						<?php
							echo PHP_EOL;
							echo ob_get_clean();
							$i++;
					}
					?>
					</tbody>
				</table>
			</div>
		</div><!-- .efpic-postbox-inner -->
	</div><!-- .postbox.efpic-postbox -->
<?php
	// Modal to remove a client
?>
	<div class="efpic-modal efpic-warning is-hidden" id="js-efpic-remove-client">
		<div class="efpic-modal-inner">
			<div class="efpic-modal-content">
				<h3><?php _e( 'You are about to remove this client', 'efpic' ); ?></h3>
				<p><?php _e( 'All selections by this client will be deleted. This cannot be undone.', 'efpic' ); ?></p>
				<p><strong><?php _e( 'Are you sure, you want to remove this client?', 'efpic' ); ?></strong></p>
				<div class="efpic-modal-actions">
					<a class="button button-primary js-efpic-remove-final" href="#"><?php _e( 'Yes, remove client', 'efpic' ); ?></a> <button class="button efpic-cancel-modal js-efpic-cancel-modal"><?php _e( 'Cancel', 'efpic' ); ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
	echo ob_get_clean();
}


/**
 * Display a client in the selection summary.
 *
 * @since 2.3.0
 *
 * @param array $client_data The client data
 */
function efpic_display_client_summary( $client_data ) {
	extract( $client_data ); 
?>
	<div class="recipient recipient--<?php echo $status; ?><?php if ( empty( $ident ) ) { echo ' recipient--placeholder'; } ?>">
		<div class="recipient__inner">
			<span class="recipient__status-wrap">
				<span class="recipient__status" title="<?php echo $date; ?>"><?php echo $status_label; ?></span>
				<?php if ( $selection_count ) { ?>
				<span class="recipient__selection-count"><?php echo $selection_count; ?></span>
				<?php } ?>
			</span>
			<span class="recipient__email"><?php echo efpic_combine_name_email( $name, $email ); ?></span>
			<?php
			if ( get_post_status( $collection_id ) == 'approved' OR get_post_status( $collection_id ) == 'sent' OR get_post_status( $collection_id ) == 'expired' ) { ?>
			<nav class="recipient__actions">
				<ul>
					<li><a href="<?php echo ( ! empty( $ident ) ) ? esc_url( add_query_arg( 'ident', $ident, get_the_permalink() ) ) : get_the_permalink(); ?>"><?php _e( 'View', 'efpic' ); ?></a></li>
					<?php
					// Action hook to add more recipient actions
					do_action( 'efpic_recipient_actions', $ident, $status, $collection_id );
					?>
				</ul>
			</nav>
			<?php }
				// Legacy: Add the old approval message to the approval fields array
				if ( ! empty( $selection['approval_message'] ) AND $status == 'approved' ) {
					$approval_fields['efpic_approval_message'] = [
						'label' => __( 'Message', 'efpic' ),
						'value' => $selection['approval_message'],
					];
				}

				// Display approval fields
				efpic_display_approval_fields( $approval_fields, $date );
			?>
		</div><!-- .recipient__inner -->
	</div>
<?php
}


/**
 * Add reopen recipient action.
 *
 * @since 2.2.0
 *
 * @param string $ident The ident param for the current recipient
 * @param string $status The collection status for the current recipient
 * @param int $post_id The collection post ID
 */
function efpic_add_recipient_action_reopen( $ident, $status, $post_id ) {
	if ( ( $status == 'approved' OR $status == 'failed' ) && ! empty( $ident ) ) {
	?>
		<li><a href="<?php print wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=edit&client=' . $ident ), 'efpic_collection_reopen_' . $post_id, 'reopen' ); ?>"><?php _e( 'Reopen', 'efpic' ); ?></a></li>
	<?php
	}
}

add_action( 'efpic_recipient_actions', 'efpic_add_recipient_action_reopen', 10, 3 );


/**
 * Add remove recipient action.
 *
 * @since 2.2.0
 *
 * @param string $ident The ident param for the current recipient
 * @param string $status The collection status for the current recipient
 * @param int $post_id The collection post ID
 */
function efpic_add_recipient_action_remove( $ident, $status, $post_id ) {
	if ( ! empty( $ident ) ) {
	?>
		<li><a class="js-efpic-remove-client" href="<?php echo wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=edit&client=' . $ident ), 'efpic_collection_remove_recipient_' . $post_id, 'remove' ); ?>"><?php _e( 'Remove…', 'efpic' ); ?></a></li>
	<?php
	}
}

add_action( 'efpic_recipient_actions', 'efpic_add_recipient_action_remove', 99, 3 );


/**
 * Display approval form fields.
 *
 * @since 1.6.5
 *
 * @param array $approval_fields The fields, containing labels and values
 * @param string $date The date, when the recipient approved the collection
 */
function efpic_display_approval_fields( $approval_fields, $date ) {
	// Check if there are any `values`, before displaying approval fields
	if ( ! empty( array_filter( array_column( $approval_fields, 'value' ) ) ) ) {
		foreach( $approval_fields as $key => $value ) {
			if ( ! empty( $value['value'] ) ) {
			?>
				<div class="recipient__comment" id="approval_field_<?php echo $key; ?>"><strong><?php echo $value['label'] . ':</strong> ';
				if ( ! empty( $value['title'] ) ) {
					echo $value['title'];
				}
				else {
					echo $value['value'];
				}
			?></div>
			<?php
			}
		}
		?>
		<div class="recipient__comment-date"><?php echo $date; ?></div>
		<?php
	}
}


/**
 * Update collection meta data.
 *
 * Save/update image ids, collection hashes, recipient email address(es),
 * description and share method.
 *
 * @since  0.4.0
 * 
 * @param int $post_id The collection post ID
 */
function efpic_update_collection_meta( $post_id ) {

	// Check if nonce is set
	if ( ! isset( $_POST['efpic_gallery_ids_nonce'] ) )
		return $post_id;

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['efpic_gallery_ids_nonce'], 'efpic_gallery_ids' ) )
		return $post_id;

	// If this is an autosave, our form has not been submitted, so we don't want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// Check user permissions
	if ( ! current_user_can( efpic_capability(), $post_id ) ) {
		return $post_id;
	}

	// Run import filter.
	// Should return true, if an import from another source occured.
	$import_done = apply_filters( 'efpic_save_gallery_ids_filter', $post_id, $_REQUEST );

	// Only update gallery id meta, if import didn't happen
	if ( true !== $import_done AND ! empty( $_POST['efpic_gallery_ids'] ) ) {
		// Sanitize data and put the image ids into a variable
		$efpic_gallery_ids = sanitize_text_field( $_POST['efpic_gallery_ids'] );

		// Save the image ID's as custom post meta
		$ids_updated = update_post_meta( $post_id, '_efpic_collection_gallery_ids', $efpic_gallery_ids );

		// Update existing selections if the images have changed
		if ( $ids_updated === true ) {
			efpic_update_client_selections( $post_id, $efpic_gallery_ids );
		}
	}

	// Check if a valid share method is chosen
	if ( ! isset( $_POST['efpic_collection_share_method'] ) OR ( 'efpic-send-email' != $_POST['efpic_collection_share_method'] AND 'efpic-copy-link' != $_POST['efpic_collection_share_method'] ) ) {
		return $post_id;
	}
	elseif ( 'efpic-send-email' == $_POST['efpic_collection_share_method'] ) {

		// Save share method
		update_post_meta( $post_id, '_efpic_collection_share_method', 'efpic-send-email' );

		// Get email address(es)
		$email_addresses = $_POST['efpic_collection_email_address'];
		$email_addresses = explode( ', ', $email_addresses );

		if ( is_array( $email_addresses ) && ! empty( $email_addresses[0] ) ) {
			$email_addresses = array_filter( $email_addresses, 'efpic_validate_email_address' );

			// If this collection gets reopened and sent again, check if we have exsting hashes and re-use them
			$existing_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );

			// Create an individual hash for each email-address
			$collection_hashes = [];

			foreach( $email_addresses as $address ) {
				
				if ( is_array( $existing_hashes ) AND array_search( $address, array_column( $existing_hashes, 'email' ) ) !== false ) {
					// This email address already has a hash
					$index = array_search( $address, array_column( $existing_hashes, 'email' ) );
					$hash = array_keys( $existing_hashes )[$index];
					$collection_hashes[$hash] = [
						'name' => '',
						'email' => $address,
					];
				}
				else {
					$collection_hashes[substr( md5( rand() ), 0, 10 )] = [
						'name' => '',
						'email' => $address,
						'status' => 'sent',
						'time' => time(),
					];
				}
			}

			// Merge new/clients with email and existing clients without email
			if ( ! empty( $existing_hashes ) && is_array( $existing_hashes ) ) {
				$collection_hashes = array_merge( $collection_hashes, $existing_hashes );
			}

			update_post_meta( $post_id, '_efpic_collection_hashes', $collection_hashes );
		}

		// Clean up the collection description
		$efpic_collection_description = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['efpic_collection_description'] ) ) );

		if ( ! empty( $efpic_collection_description ) ) {
			// Update the collection description in the database
			update_post_meta( $post_id, '_efpic_collection_description', $efpic_collection_description );
		}
	}
	else {
		update_post_meta( $post_id, '_efpic_collection_share_method', 'efpic-copy-link' );
	}
}

add_action( 'save_post_efpic_collection', 'efpic_update_collection_meta' );


/**
 * Handle collection image sorting.
 *
 * @since 1.8.0
 *
 * @param int $post_id The collection post ID
 */
function efpic_sort_collection_images( $post_id ) {

	if ( ! empty( $_POST['sort-collection'] ) AND ! empty( $_POST['efpic_gallery_ids'] ) AND isset( $_POST['sort-collection-submit'] ) ) {

		$efpic_collection_images = sanitize_text_field( $_POST['efpic_gallery_ids'] );

		$efpic_gallery_ids = explode( ',', $efpic_collection_images );

		// Save current order to make it undoable
		set_transient( 'efpic_previous_image_order_' . $post_id, $efpic_gallery_ids, DAY_IN_SECONDS );

		$images = [];
		$date_sort_error = false;
		foreach( $efpic_gallery_ids as $image_id ) {
			if ( strpos( $_POST['sort-collection'], 'order-by-name' ) !== false ) {
				// Filename as value
				$images[ $image_id ] = basename( get_attached_file( $image_id ) );
			}
			elseif ( strpos( $_POST['sort-collection'], 'order-by-created' )  !== false ) {
				$temp = wp_get_attachment_metadata( $image_id );
				// Creation date as value
				$images[ $image_id ] = $temp['image_meta']['created_timestamp'];
				if ( $temp['image_meta']['created_timestamp'] == 0 ) {
					$date_sort_error = true;
				}
			}
		}

		// Sort in ascending order
		if ( strpos( $_POST['sort-collection'], 'asc' ) !== false ) {
			asort( $images );
		}
		// Sort in descending order
		elseif ( strpos( $_POST['sort-collection'], 'desc' ) !== false ) {
			arsort( $images );
		}

		$efpic_gallery_ids = implode( ',', array_keys( $images ) );
		update_post_meta( $post_id, '_efpic_collection_gallery_ids', $efpic_gallery_ids );

		// Order has not changed
		if ( $efpic_collection_images == $efpic_gallery_ids ) {
			efpic_add_notification( 'efpic_images_sorted', 'notice notice-info is-dismissible', __( 'Images were already sorted in this order.', 'efpic' ) );
			return;
		}

		$error = '';
		if ( $date_sort_error == true ) {
			/* translators: Admin notice, %s = opening and closing link tags */
			$error = '<br />' . sprintf( __( '<strong>Please note:</strong> At least one of the images does not contain the necessary meta data for date based sorting. %sLearn more%s', 'efpic' ), '<a href="https://efpic.io/docs/faq/#image-order">', '</a>' );
		}

		efpic_add_notification( 'efpic_images_sorted', 'notice notice-success is-dismissible', __( 'Image order adjusted.', 'efpic' ) . ' ' . '<a href="' . add_query_arg( 'collection_id', $post_id, wp_nonce_url( get_edit_post_link(), 'undo_image_order', 'undo_image_order' ) ) . '">' . __( 'Undo', 'efpic' ) . '</a>' . $error );
	}
}

add_action( 'save_post_efpic_collection', 'efpic_sort_collection_images' );


/**
 * Handle collection sorting undo.
 *
 * @since 1.9.1
 */
function efpic_undo_sort_collection_images() {
	if ( ! empty( $_GET['undo_image_order'] ) AND wp_verify_nonce( $_GET['undo_image_order'], 'undo_image_order' ) AND ! empty( $_GET['collection_id'] ) ) {
		$collection_id = (int) $_GET['collection_id'];
		$previous = get_transient( 'efpic_previous_image_order_' . $collection_id );
		if ( ! empty( $previous ) ) {
			$efpic_gallery_ids = implode( ',', $previous );
			update_post_meta( $collection_id, '_efpic_collection_gallery_ids', $efpic_gallery_ids );
			delete_transient( 'efpic_previous_image_order_' . $collection_id );
			add_action( 'admin_notices', function() {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php  _e( 'Image order restored.', 'efpic' ); ?></p>
				</div>
				<?php
			});
		}
	}
}

add_action( 'init', 'efpic_undo_sort_collection_images' );


/**
 * Custom function to re-open an already sent collection.
 *
 * @since 0.3.0
 */
function efpic_collection_reopen() {

	// Check if a "reopen" parameter (the nonce) was set with this request
	if ( isset( $_REQUEST['reopen'] ) AND isset( $_REQUEST['post'] ) ) {

		$post_id = sanitize_key( $_REQUEST['post'] );
		$post_status = get_post_status( $post_id );

		// If it is, save it in a variable
		$reopen_nonce = $_REQUEST['reopen'];

		// Verify the nonce to see if it is a legitimate request
		if ( ! wp_verify_nonce( $reopen_nonce, 'efpic_collection_reopen_' . $post_id ) ) {
			wp_die ( __( 'Security check failed!', 'efpic' ) );
		} else {
			// Check if individual client should be reopened
			// Only allow it, if the collection status is sent, approved or expired
			if ( ! empty( $_GET['client'] ) AND ( in_array( $post_status, [ 'sent', 'approved', 'expired' ] ) ) ) {

				$client = sanitize_key( $_GET['client'] );
				$hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );

				if ( array_key_exists( $client, $hashes ) AND ( $hashes[$client]['status'] == 'approved' OR $hashes[$client]['status'] == 'failed' ) ) {
					$hashes[$client]['status'] = 'sent';
					$hashes[$client]['time'] = time();
					update_post_meta( $post_id, '_efpic_collection_hashes', $hashes );

					// Check if the general collection status needs to be updated as well
					if ( get_post_status( $post_id ) == 'approved' || get_post_status( $post_id ) == 'expired' ) {
						efpic_update_post_status( $post_id, 'sent' );

						// Remove expiration date
						$expiration = get_post_meta( $post_id, '_efpic_collection_expiration', true );
						if ( $expiration == 'on' ) {
							update_post_meta( $post_id, '_efpic_collection_expiration', 'off' );
							delete_post_meta( $post_id, '_efpic_collection_expiration_time' );
						}
					}

					efpic_update_collection_history( $post_id, 'reopened-for-client', efpic_combine_name_email( $hashes[$client]['name'], $hashes[$client]['email'] ) );
				}
				else {
					wp_die( '👀 ' . __( 'Client ID not found.', 'efpic' ) );
				}

				// Add notice that the collection has been reopened for the client
				efpic_add_notification( 'collection_reopened', 'notice notice-success is-dismissible', sprintf( __( 'The collection has been reopened for %s.', 'efpic' ), '<strong>' . $hashes[$client]['email'] . '</strong>' ) );

				$redirect = add_query_arg( [ 
					'post' => $post_id,
					'action' => 'edit',
					'efpic_notification' => 1,
				], admin_url( 'post.php' ) );
				nocache_headers();
				wp_safe_redirect( $redirect );
				exit;
			}

			if ( $post_status == 'delivered' ) {
				efpic_update_post_status( $post_id, 'delivery-draft' );
				efpic_update_collection_history( $post_id, 'reopened-to-delivery-draft' );
			}
			elseif ( $post_status == 'approved' || $post_status == 'expired' ) {
				efpic_update_post_status( $post_id, 'sent' );
				efpic_update_collection_history( $post_id, 'reopened' );

				// Remove expiration date
				update_post_meta( $post_id, '_efpic_collection_expiration', 'off' );
				delete_post_meta( $post_id, '_efpic_collection_expiration_time' );
			}
			else {
				efpic_update_post_status( $post_id, 'draft' );
				efpic_update_collection_history( $post_id, 'reopened-to-draft' );
			}
		}

		// Add notice, that the collection has been reopened 
		efpic_add_notification( 'collection_reopened', 'notice notice-success is-dismissible', __( 'Collection reopened.', 'efpic' ) );

		$redirect = add_query_arg( [ 
			'post' => $post_id,
			'action' => 'edit',
			'efpic_notification' => 1,
		], admin_url( 'post.php' ) );
		nocache_headers();
		wp_safe_redirect( $redirect );
		exit;
	}
}

add_action( 'wp_loaded', 'efpic_collection_reopen' );


/**
 * Remove recipient from a collection.
 *
 * @since 2.2.0
 */
function efpic_collection_remove_recipient() {
	// Check if a "reopen" parameter (the nonce) was set with this request
	if ( ! empty( $_GET['post'] ) && ! empty( $_GET['client'] ) && ! empty( $_GET['remove'] ) ) {

		$post_id = sanitize_key( $_GET['post'] );
		$client_id = sanitize_key( $_GET['client'] );

		// If it is, save it in a variable
		$remove_nonce = $_REQUEST['remove'];

		// Verify the nonce to see if it is a legitimate request
		if ( ! wp_verify_nonce( $remove_nonce, 'efpic_collection_remove_recipient_' . $post_id ) ) {
			wp_die ( __( 'Security check failed!', 'efpic' ) );
		} else {
			// Remove client
			$collection_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );

			$client = efpic_combine_name_email( $collection_hashes[$client_id]['name'], $collection_hashes[$client_id]['email'] );

			// Remove recipient's selection
			delete_post_meta( $post_id, '_efpic_collection_selection_' . $client_id );
			
			// Remove hash
			unset( $collection_hashes[$client_id] );
			update_post_meta( $post_id, '_efpic_collection_hashes', $collection_hashes );

			// Update history
			efpic_update_collection_history( $post_id, 'removed-client', $client );
			
			// Add notice that the client has been removed
			efpic_add_notification( 'remove_client', 'notice notice-success is-dismissible', sprintf( __( 'The client %s was removed from the collection.', 'efpic' ), '<strong>' . $client . '</strong>' ) );
		}

		$redirect = add_query_arg( [ 
			'post' => $post_id,
			'action' => 'edit',
			'efpic_notification' => 1,
		], admin_url( 'post.php' ) );
		nocache_headers();
		wp_safe_redirect( $redirect );
		exit;
	}
}

add_action( 'wp_loaded', 'efpic_collection_remove_recipient' );


/**
 * Add recipient to a sent collection.
 *
 * @since 2.2.0
 */
function efpic_collection_add_recipient() {
	if ( ! empty( $_POST['efpic_add_client'] ) && ! wp_verify_nonce( $_POST['efpic_add_client'], 'efpic_add_client' ) ) {
		return;
	}

	if ( ! empty( $_POST['efpic-new-recipient-name'] ) OR ! empty( $_POST['efpic-new-recipient-email'] ) ) {
		$name = sanitize_text_field( $_POST['efpic-new-recipient-name'] );
		$email = sanitize_email( $_POST['efpic-new-recipient-email'] );
		$post_id = $_POST['post_ID'];

		// Check if recipient exists already
		$collection_emails = efpic_get_collection_emails( $post_id );

		if ( in_array( $email, $collection_emails ) ) {
			efpic_add_notification( 'email_exists', 'error', sprintf( __( '%s is already a client of this collection.', 'efpic' ), '<strong>' . $email . '</strong>' ) );
			return;
		}

		// Get recipients
		$collection_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );
		if ( ! is_array( $collection_hashes ) ) {
			$collection_hashes = [];
		}

		// Check if name already exists
		$index = array_search( $name, array_column( $collection_hashes, 'name' ) );
		if ( empty( $email ) && $index !== false && ! empty( $name ) ) {
			efpic_add_notification( 'name_exists', 'error', sprintf( __( '%s is already a client of this collection.', 'efpic' ), '<strong>' . $name . '</strong>' ) );
			return;
		}

		// Add new recipient to hahses
		$hash = substr( md5( rand() ), 0, 10 );
		$collection_hashes[$hash] = [
			'name' => $name,
			'email' => $email,
			'status' => 'sent',
			'time' => time(),
		];
		update_post_meta( $post_id, '_efpic_collection_hashes', $collection_hashes );

		// Send email to the new client
		if ( ! empty( $email ) && ! empty( $_POST['efpic-new-recipient-send-email'] ) && $_POST['efpic-new-recipient-send-email'] == 'on' ) {
			efpic_send_proofing_email( $email, get_post( $post_id ), $hash );
		}

		// Update post status to `sent`
		efpic_update_post_status( $post_id, 'sent' );

		// Update history
		efpic_update_collection_history( $post_id, 'sent-to-new-client', $email );

		efpic_add_notification( 'client_added', 'notice notice-success is-dismissible', sprintf( __( 'New client %s added to the collection.', 'efpic' ), '<strong>' . efpic_combine_name_email( $name, $email ) . '</strong>' ) );
	}
}

add_action( 'init', 'efpic_collection_add_recipient' );


/**
 * Custom function to close a collection.
 *
 * @since 2.2.0
 */
function efpic_collection_close() {
	if ( isset( $_GET['close'] ) AND isset( $_GET['post'] ) ) {
		$post_id = sanitize_key( $_GET['post'] );

		if ( ! wp_verify_nonce( $_GET['close'], 'efpic_collection_close_' . $post_id ) ) {
			wp_die ( __( 'Security check failed!', 'efpic' ) );
		}

		$post_status = get_post_status( $post_id );
		if ( in_array( $post_status, [ 'sent', 'publish' ] ) ) {
			// Set status to approved
			efpic_update_post_status( $post_id, 'approved' );

			// Add history entry
			efpic_update_collection_history( $post_id, 'closed-manually' );

			// Add notification
			/* translators: Admin notice */
			efpic_add_notification( 'collection_closed', 'notice notice-success is-dismissible', __( 'The collection is now closed.', 'efpic' ) );

			// Run action after a collection has been closed manually
			do_action( 'efpic_collection_has_closed', $post_id );
		}
		elseif( in_array( $post_status, [ 'approved', 'expired' ] ) ) {
			efpic_add_notification( 'collection_closed', 'notice notice-success is-dismissible', __( 'The collection has already been closed.', 'efpic' ) );
		}

		// Redirect back to the collection edit screen
		$redirect = add_query_arg( [ 
			'post' => $post_id,
			'action' => 'edit',
			'efpic_notification' => 1,
		], admin_url( 'post.php' ) );
		nocache_headers();
		wp_safe_redirect( $redirect );
		exit;
	}
}

add_action( 'init', 'efpic_collection_close' );


/**
 * Remove the default "post-submit" metabox.
 *
 * @since 0.3.0
 */
function efpic_remove_submit_metabox() {

	remove_meta_box( 'submitdiv', 'efpic_collection', 'side' );

}

add_action( 'admin_menu', 'efpic_remove_submit_metabox' );


/**
 * Replace regular "Edit Collection" title with actual title.
 *
 * @since 0.3.0
 */
function efpic_replace_edit_screen_title() {
	global $post, $title, $action;

	if ( empty( $post ) OR empty( $action ) ) {
		return;
	}

	if ( $action == 'edit' && $post->post_type == 'efpic_collection' && ( in_array( $post->post_status, [ 'approved', 'sent', 'expired' , 'closed' ] ) ) ) {
		if ( empty( $post->post_title ) ) {
			$title = __( '(no title)', 'efpic' );
		} else {
			$title = $post->post_title;
		}
	}
}

add_action( 'admin_head', 'efpic_replace_edit_screen_title' );


/**
 * Duplicate collection.
 *
 * @since 0.9.4
 *
 * @param int $post_id The collection post ID
 */
function efpic_duplicate_collection( $post_id, $selected = false ) {

	// Get image ids and description from post meta
	$custom_meta = get_post_custom( $post_id );

	// Check if it is actually a collection, and if the required meta is available
	if ( 'efpic_collection' == get_post_type( $post_id ) AND isset( $custom_meta['_efpic_collection_gallery_ids'] ) ) {

		$gallery_ids = $custom_meta['_efpic_collection_gallery_ids'];
		if ( isset( $custom_meta['_efpic_collection_description'] ) ) {
			$collection_description = $custom_meta['_efpic_collection_description'];
		}

		$arg = array(
			'post_type' => 'efpic_collection',
		);

		if ( ! empty( get_the_title( $post_id ) ) ) {
			$arg['post_title'] = __( 'Copy of', 'efpic' ) . ' ' . get_the_title( $post_id );
		}

		// Create new collection
		$new_id = wp_insert_post( $arg );
	}

	// If we succesfully created the new collection...
	if ( isset( $new_id ) AND ! empty( $new_id ) ) {

		// Add the description to the new collection
		if ( isset( $collection_description ) AND is_array( $collection_description ) ) {
			add_post_meta( $new_id, '_efpic_collection_description', $collection_description[0] );
		}

		// Prepare Copying / duplicating images
		$upload_dir = wp_upload_dir();
		$collection_image_dir = trailingslashit( $upload_dir['basedir'] ) . 'efpic/collections/' . $post_id;
		$new_collection_image_dir = trailingslashit( $upload_dir['basedir'] ) . 'efpic/collections/' . $new_id;

		if ( ! is_dir( $new_collection_image_dir ) ) {
			mkdir( $new_collection_image_dir, 0755 );
		}

		// Get selected images
		$selection = efpic_get_selected_images( $post_id );

		// Get final image ids to be copied
		if ( 'selected' == $selected ) {
			$image_ids = $selection;
		}
		elseif ( 'not-selected' == $selected ) {
			$all_image_ids = explode( ',', $gallery_ids[0] );
			$image_ids = array_diff( $all_image_ids, $selection );
		}
		else {
			$image_ids = explode( ',', $gallery_ids[0] );
		}

		// Load images
		$images = get_posts( array(
			'include' => $image_ids,
			'post_status' => 'any',
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'order' => 'ASC'
		) );

		$new_gallery_ids = array();

		foreach( $images as $image ) {

			// Get original file path
			$original_image_path = get_attached_file( $image->ID );

			// Get file name
			$image_name = basename( $original_image_path );

			// Get meta data from original file
			$original_meta = wp_get_attachment_metadata( $image->ID );

			// Copy original file
			if ( copy( $original_image_path, $new_collection_image_dir . '/' . $image_name ) ) {

				// Copy other available sizes as well
				foreach( $original_meta['sizes'] as $thumbnail ) {
					// Check if there is acutally a file
					if ( isset( $thumbnail['file'] ) AND ! empty( $thumbnail['file'] ) ) {
						copy( $collection_image_dir . '/' . $thumbnail['file'], $new_collection_image_dir . '/' . $thumbnail['file'] );
					}
				}

				// Prepare file attachment
				$filetype = wp_check_filetype( basename( $image_name ), null );

				$attachment = array(
					'guid'           => $new_collection_image_dir . '/' . $image_name,
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $image_name ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				// Prevent new thumbnails from being created during this process
				add_filter( 'intermediate_image_sizes_advanced', '__return_false' );

				// Prevent creation of scaled image
				add_filter( 'big_image_size_threshold', '__return_false' );

				// Insert file as new attachment
				$attach_id = wp_insert_attachment( $attachment, $new_collection_image_dir . '/' . $image_name, $new_id );

				// wp_generate_attachment_metadata() depends on this file
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				// Generate the metadata for the new attachment
				$attach_data = wp_generate_attachment_metadata( $attach_id, $new_collection_image_dir . '/' . $image_name );

				// Use the sizes from the original file
				$attach_data['sizes'] = $original_meta['sizes'];

				// Update attachment meta data
				wp_update_attachment_metadata( $attach_id, $attach_data );

				// Add new attachment id to our array
				$new_gallery_ids[] = $attach_id;

			}
			else {
				// Cleanup: delete the new collection we just created
				wp_delete_post( $new_id, true );

				// Report error to the user
				$error = error_get_last();
				efpic_add_notification( 'efpic_duplication_failed', 'notice notice-error is-dismissible', __( 'Duplication failed with the following error: ', 'efpic' ) . $error['message'] );

				// Redirect to collection overview
				wp_redirect( admin_url( 'edit.php?post_type=efpic_collection&efpic_notification=1' ) );
				exit;
			}
		}

		// Create string with image IDs
		$new_gallery_ids = implode( ',', $new_gallery_ids );

		// Save image IDs for our new collection
		add_post_meta( $new_id, '_efpic_collection_gallery_ids', $new_gallery_ids );

		// Redirect to newly created collection edit screen
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
		exit;
	}
	else {
		// Redirect to efpic collection overview, set parameter to show error
		wp_redirect( admin_url( 'edit.php?post_type=efpic_collection&efpic=duplication-error' ) );
		exit;
	}
}

// Run the collection function, if the correct parameter is set
if ( isset( $_REQUEST['efpic_duplicate_collection'] ) AND ! empty( $_REQUEST['efpic_duplicate_collection'] ) AND wp_verify_nonce( $_REQUEST['efpic_duplication_nonce'], 'efpic_duplicate_collection') ) {
	if ( isset( $_REQUEST['efpic_duplicate_collection_selected'] ) AND '1' == $_REQUEST['efpic_duplicate_collection_selected'] ) {
		efpic_duplicate_collection( absint( $_REQUEST['efpic_duplicate_collection'] ), 'selected' );
	}
	elseif ( isset( $_REQUEST['efpic_duplicate_collection_not_selected'] ) AND '1' == $_REQUEST['efpic_duplicate_collection_not_selected'] ) {
		efpic_duplicate_collection( absint( $_REQUEST['efpic_duplicate_collection'] ), 'not-selected' );
	}
	else {
		efpic_duplicate_collection( absint( $_REQUEST['efpic_duplicate_collection'] ) );
	}
}

// Display error message, if duplication failed
if ( isset( $_REQUEST['efpic'] ) AND 'duplication-error' == $_REQUEST['efpic'] ) {
	function efpic_duplication_error_notice() { ?>
		<div class="error notice is-dismissible">
			<p><?php _e( 'Duplication failed. Please try again.', 'efpic' ); ?></p>
		</div>
	<?php }

	add_action( 'admin_notices', 'efpic_duplication_error_notice' );
}


/**
 * Add duplicate as row action item.
 *
 * @since 0.9.4
 */
function efpic_add_duplicate_link( $actions, $post ) {
	if ( 'efpic_collection' == $post->post_type AND ! empty( get_post_meta( $post->ID, '_efpic_collection_gallery_ids', true ) ) AND $post->post_status != 'delivery-draft' AND $post->post_status != 'delivered' ) {

		if ( efpic_get_selection_count( $post->ID ) > 0 ) {
			ob_start();
		?>
			<a class="js-efpic-duplicate" data-id="<?php echo $post->ID; ?>" href="<?php echo wp_nonce_url( admin_url( 'post.php?efpic_duplicate_collection=' . $post->ID ), 'efpic_duplicate_collection', 'efpic_duplication_nonce' ); ?>"><?php _e( 'Duplicate', 'efpic' ); ?>&hellip;</a>
		<?php
			echo efpic_get_duplication_modal( $post->ID );
			$actions['efpic_duplication'] = ob_get_clean();
		}
		else {
			$actions['efpic_duplication'] = '<a href="' . wp_nonce_url( admin_url( 'post.php?efpic_duplicate_collection=' . $post->ID ), 'efpic_duplicate_collection', 'efpic_duplication_nonce' ) . '">'. __( 'Duplicate', 'efpic' ) . '</a>';
		}
	}

	return $actions;
}

add_filter( 'post_row_actions', 'efpic_add_duplicate_link', 10, 2 );



/**
 * Custom function to change post status to delivery-draft.
 *
 * @since 1.5.0
 */
function efpic_collection_delivery() {

	// Check if a "reopen" parameter (the nonce) was set with this request
	if ( isset( $_REQUEST['delivery'] ) AND isset( $_REQUEST['delivery'] ) ) {

		// If it is, save it in a variable
		$delivery_nonce = $_REQUEST['delivery'];

		// Verify the nonce to see if it is a legitimate request
		if ( ! wp_verify_nonce( $_REQUEST['delivery'] ) ) {
			wp_die( __( 'Security check failed!', 'efpic' ) );

		} else {
			if ( ! empty( $_REQUEST['post'] ) ) {
				$post_id = sanitize_key( $_REQUEST['post'] );
				efpic_update_post_status( $post_id, 'delivery-draft' );
				efpic_update_collection_history( $post_id, 'preparing-delivery' );
			}
			else {
				// Create new delivery draft
				$post_id = wp_insert_post( array(
					'post_type' => 'efpic_collection',
					'post_status' => 'delivery-draft'
				) );
			}

			wp_redirect( admin_url( 'post.php?action=edit&post=' . $post_id ) );
			exit;
		}
	}
}

add_action( 'wp_loaded', 'efpic_collection_delivery' );


/**
 * Disable the block editor (aka Gutenberg) for efpic collections.
 *
 * @since 1.2.2
 */
function efpic_disable_block_editor( $use_block_editor, $post_type ) {

	if ( $post_type === 'efpic_collection' ) {
		return false;
	}

	return $use_block_editor;

}

add_filter( 'gutenberg_can_edit_post_type', 'efpic_disable_block_editor', 10, 2 );
add_filter( 'use_block_editor_for_post_type', 'efpic_disable_block_editor', 10, 2 );


/**
 * Display duplication modal.
 *
 * @since 1.3.4
 *
 * @param $post_id The collection post ID
 */
function efpic_get_duplication_modal( $post_id ) {
	// Get number of images
	$efpic_collection_gallery_ids = get_post_meta( $post_id, '_efpic_collection_gallery_ids', true );
	$total_images_num = count( explode( ',', $efpic_collection_gallery_ids ) );

	// Get number of selected images
	$selected_images_num = efpic_get_selection_count( $post_id );

	// Get numner of not-selected images
	$not_selected_images_num = $total_images_num - $selected_images_num;

	ob_start();
	?>

	<div class="efpic-modal is-hidden" id="js-efpic-duplicate-<?php echo $post_id; ?>">
		<div class="efpic-modal-inner">
			<div class="efpic-modal-content">
				<h3><?php _e( 'Duplicate', 'efpic' ); ?>&hellip;</h3>
				<p><input type="radio" class="js-duplication-radio ays-ignore" id="efpic_duplicate_all_<?php echo $post_id; ?>" data-id="<?php echo $post_id; ?>" name="efpic_duplicate_<?php echo $post_id; ?>" value="all" checked="checked" /> <label for="efpic_duplicate_all_<?php echo $post_id; ?>"><?php _e( 'All images', 'efpic' ); ?> <em>(<?php echo $total_images_num; ?>)</em></label></p>
				<p><input type="radio" class="js-duplication-radio ays-ignore" id="efpic_duplicate_selected_<?php echo $post_id; ?>" data-id="<?php echo $post_id; ?>" name="efpic_duplicate_<?php echo $post_id; ?>" value="selected" /> <label for="efpic_duplicate_selected_<?php echo $post_id; ?>"><?php _e( 'Selected images only', 'efpic' ); ?> <em>(<?php echo $selected_images_num; ?>)</em></label></p>
				<?php if ( $not_selected_images_num > 0 ) { ?>
				<p><input type="radio" class="js-duplication-radio ays-ignore" id="efpic_duplicate_not_selected_<?php echo $post_id; ?>" data-id="<?php echo $post_id; ?>" name="efpic_duplicate_<?php echo $post_id; ?>" value="not-selected" /> <label for="efpic_duplicate_not_selected_<?php echo $post_id; ?>"><?php _e( 'Unselected images only', 'efpic' ); ?> <em>(<?php echo $not_selected_images_num; ?>)</em></label></p>
				<?php } ?>
				<input type="hidden" class="ays-ignore" name="efpic_duplication_url_<?php echo $post_id; ?>" value="<?php echo wp_nonce_url( admin_url( 'post.php?efpic_duplicate_collection=' . $post_id ), 'efpic_duplicate_collection', 'efpic_duplication_nonce' ); ?>" />
				<div class="efpic-modal-actions">
					<a class="button button-primary js-efpic-duplication-button-<?php echo $post_id; ?>" href="<?php echo wp_nonce_url( admin_url( 'post.php?efpic_duplicate_collection=' . $post_id ), 'efpic_duplicate_collection', 'efpic_duplication_nonce' ); ?>"><?php _e( 'Duplicate', 'efpic' ); ?></a>
					<a class="button efpic-cancel-modal js-efpic-cancel-modal" href="#"><?php _e( 'Cancel', 'efpic' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<?php
	return ob_get_clean();
}


/**
 * Display expiration option.
 *
 * @since 2.0.0
 *
 * @param int $post_id The collection post ID
 * @param string $post_status The collections post_status
 */
function efpic_collection_expiration_option( $post ) {
	// Only show if collection is a draft
	if ( $post->post_status != 'draft' && $post->post_status != 'auto-draft' ) {
		return;
	}

	$expiration_default = get_option( 'efpic_expiration' );
	$expiration_post_meta = get_post_meta( $post->ID, '_efpic_collection_expiration', true );

	// Use the default
	$expiration = 'off';
	if ( $expiration_default == 'on' && $post->post_status == 'auto-draft') {
		$expiration = 'on';
	}
	// Use collection setting
	elseif ( $expiration_post_meta == 'on' ) {
		$expiration = 'on';
	}

	$days = efpic_expiration_length();

	ob_start();
	?>
	<div class="efpic-option-item">
		<label for="collection_expires">
			<input type="checkbox" name="collection_expires" id="collection_expires" <?php if ( isset ( $expiration ) ) checked( $expiration, 'on' ); ?> />
			<?php echo sprintf( _n( 'Expire after %d day', 'Expire after %d days', $days, 'efpic' ), $days ); ?>
		</label>
	</div>
	<?php
	echo apply_filters( 'efpic_expiration_option', ob_get_clean(), $expiration, $days );
}


/**
 * Save collection expiration date.
 *
 * @since 2.0.0
 *
 * @param int $collection_id The collection post ID
 */
function efpic_save_expiration_option( $collection_id ) {
	if ( ! empty( $_POST['collection_expires'] ) AND $_POST['collection_expires'] == 'on' ) {
		update_post_meta( $collection_id, '_efpic_collection_expiration', 'on' );
		// Caclulate and save the expiration date
		$expiration_date = efpic_calculate_expiration_time();
		update_post_meta( $collection_id, '_efpic_collection_expiration_time', $expiration_date );
	}
	if ( get_post_status( $collection_id ) == 'draft' && empty( $_POST['collection_expires'] ) && ! isset( $_GET['reopen'] ) ) {
		update_post_meta( $collection_id, '_efpic_collection_expiration', 'off' );
		// Delete expiration date
		delete_post_meta( $collection_id, '_efpic_collection_expiration_time' );
	}
}

add_action( 'save_post_efpic_collection', 'efpic_save_expiration_option', 8 );


/**
 * Collection history metabox content.
 *
 * @since 2.2.0
 *
 * @param object $post Collection post object
 */
function efpic_collection_history_metabox( $post ) {
	$history = get_post_meta( $post->ID, '_efpic_collection_history', true );

	if ( ! empty ( $history ) ) {
		$history = array_reverse( $history, true );
		ob_start();
		foreach( $history as $time => $event ) {
			$data = $event['data'];
			echo '<div class="efpic-event">';
			echo '<span class="efpic-event__time">' . wp_date( 'Y-m-d H:i:s', $time ) . '</span>';
			echo '<span class="efpic-event__name">' . efpic_collection_event_prettify( $event['event'] ) . '</span>';
			if ( ! empty( $data ) ) {
				if ( is_array( $data ) ) {
					echo '<span class="efpic-event__data">' . implode( ', ', $data ) . '</span>';
				}
				else {
					echo '<span class="efpic-event__data">' . $data . '</span>';
				}
			}
			echo '</div>';
		}
		$output = apply_filters( 'efpic_collection_history_metabox', ob_get_clean(), $post->ID );
		echo $output;
	}
	else {
		echo '<div class="efpic-event">' . __( 'No events yet.', 'efpic' ) . '</div>';
	}
}


/**
 * Hide collection history meta box by default.
 *
 * @since 2.2.0
 */
function efpic_hide_collection_history_meta_box( $hidden, $screen ) {
	// Make sure we are dealing with the correct screen
	if ( $screen->post_type == 'efpic_collection' && $screen->id == 'efpic_collection' ) {
		$hidden[] ='efpic-collection-history';
	}

	return $hidden;
}

add_filter( 'default_hidden_meta_boxes', 'efpic_hide_collection_history_meta_box', 10, 2 );