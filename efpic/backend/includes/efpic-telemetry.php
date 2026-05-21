<?php
/**
 * efpic telemetry
 *
 * @since 1.10.0
 */
defined( 'ABSPATH' ) OR exit;


/**
 * Send telemetry data to efpic.io.
 *
 * @since 1.10.0
 *
 * @param array $data The request body parameters
 * @return object $response The response object
 */
function efpic_telemetry_api_request( $data ) {

	if ( empty( $data ) ) {
		return false;
	}

	// Prepare request args
	$request_args = [
		'method' => 'POST',
		'headers' => [
			'Content-Type' => 'application/json',
		]
	];

	// Prepare everything
	$endpoint = 'transfer/';
	$request_args['headers']['Authorization'] = wp_hash( get_home_url( null, '', 'https' ) );
	$request_args['body'] = $data;

	// Send the request
	$response = wp_remote_post( EFPIC_TELEMETRY_URL . $endpoint, $request_args );
	$response = is_wp_error( $response ) ? false : json_decode( wp_remote_retrieve_body( $response ) );

	if ( $response->request == 'success' ) {
		return $response;
	}
	
	return false;
}


/**
 * Prepare efpic telemetry data package.
 *
 * @since 1.10.0
 *
 * @return string|bool Returns either the json encoded data or false
 */
function efpic_prepare_telemetry_data_package() {
	$collections_cache = get_option( 'efpic_telemetry_cache' );
	$orders_cache = get_option( 'efpic_telemetry_cache_orders' );

	// Return false if both caches are empty
	if ( empty( $collections_cache ) && empty( $orders_cache ) ) {
		return false;
	}

	$data = [
		'version' => EFPIC_TELEMETRY_VERSION,
		'collections' => $collections_cache ?: [],
		'orders' => $orders_cache ?: [],
	];

	return json_encode( $data );
}


/**
 * Transmit telemetry data to efpic.io.
 *
 * @since 1.10.0
 */
function efpic_transmit_telemetry_data() {
	// Only transmit, if telemetry is enabled
	$telemetry_settings = get_option( 'efpic_telemetry_settings', [] );
	if ( ! empty( $telemetry_settings['consent'] ) && $telemetry_settings['consent'] == true ) {
		$data = efpic_prepare_telemetry_data_package();
		if ( $data !== false ) {
			$response = efpic_telemetry_api_request( $data );
			if ( $response ) {
				// Clean out telemetry cache
				efpic_purge_telemetry_cache();
			}
		}
	}
}


/**
 * Add hook to execute next telemetry transmission.
 *
 * @since 1.10.0
 */
add_action( 'efpic_run_telemetry_transmit', 'efpic_transmit_telemetry_data' );


/**
 * Clear telemetry cache.
 *
 * @since 1.10.0
 */
function efpic_purge_telemetry_cache() {
	delete_option( 'efpic_telemetry_cache' );
	delete_option( 'efpic_telemetry_cache_orders' );
}


/**
 * Schedule telemetry transmission.
 *
 * @since 1.10.0
 */
if ( ! wp_next_scheduled( 'efpic_run_telemetry_transmit' ) ) {
	wp_schedule_event( time(), 'daily', 'efpic_run_telemetry_transmit' );
}



/**
 * Schedule telemetry gathering.
 *
 * @since 2.3.0
 */
if ( ! wp_next_scheduled( 'efpic_run_compile_telemetry_data' ) ) {
	wp_schedule_event( time(), 'daily', 'efpic_run_compile_telemetry_data' );
}


/**
 * Add hook to execute next telemetry transmission.
 *
 * @since 1.10.0
 */
add_action( 'efpic_run_compile_telemetry_data', 'efpic_compile_telemetry_data' );


/**
 * Display reminder to activate efpic telemetry.
 *
 * @since 1.10.0
 */
function efpic_telemetry_nag() {
	$telemetry_options = get_option( 'efpic_telemetry_settings' );

	$display_telemetry_nag = ! get_transient( 'efpic_telemetry_nag_' . get_current_user_id() );

	if ( empty( $telemetry_options['consent'] ) || $telemetry_options['consent'] != true AND $display_telemetry_nag ) {

		// Only show on efpic related screens
		$current_screen = get_current_screen();
		$current_screen = $current_screen->id;

		$efpic_screens = [
			'edit-efpic_collection',
			'efpic_collection',
			'efpic_page_efpic-add-ons',
			'efpic_page_efpic-pro'
		];

		// Add settings pages
		$settings = array_keys( efpic_get_settings() );
		foreach( $settings as $settings_page ) {
			$efpic_screens[] = 'efpic_page_efpic-' . $settings_page;
		}

		if ( in_array( $current_screen, $efpic_screens ) ) {
			$message_id = get_option( 'efpic_telemetry_nag', 0 );
			if ( $message_id > 2 ) {
				return;
			}

			$messages = [
				/* translators: %s opening and closing link tags */
				'🧑‍💻 ' . __( 'To improve <strong>efpic</strong> further, real world usage data is invaluable. Help us out by activating %sefpic telemetry%s.', 'efpic' ),
				/* translators: %s opening and closing link tags */
				'💡 ' . __( 'We want to learn how <strong>efpic</strong> is used by photographers like you. %sActivate telemetry%s to share your usage data completely anonymously.', 'efpic' ),
				/* translators: %s opening and closing link tags */
				'🩷 ' . __( 'Want to help improve <strong>efpic</strong>? %sActivate telemetry%s and allow us to gather anonymous usage data.', 'efpic' ),
			];

			echo '<div class="notice notice-info is-dismissible efpic-telemetry-nag-notice"><p>' . sprintf( $messages[$message_id], '<a href="' . admin_url( 'admin.php?page=efpic-telemetry' ) . '">', '</a>' ) . '</p></div>';
		}
	}
}

add_action( 'admin_notices', 'efpic_telemetry_nag' );


/**
 * Get all the efpic settings.
 *
 * 2.0.0
 */
function efpic_get_settings_for_telemetry() {
	$options = [
		'random_slugs' => get_option( 'efpic_random_slugs' ),
		'expiration' => get_option( 'efpic_expiration' ),
		'efpic_love' => get_option( 'efpic_efpic_love' ),
		'theme' => get_option( 'efpic_theme' ),
		'send_html_mails' => get_option( 'efpic_send_html_mails' ),
		'send_password' => get_option( 'efpic_send_password' ),
		'from_email' => get_option( 'efpic_from_email' ),
		'from_name' => get_option( 'efpic_from_name' ),
		'notification_email' => get_option( 'efpic_notification_email' ),
		'password_by_default' => get_option( 'efpic_password_by_default' ),
		'default_image_processor' => get_option( 'efpic_default_image_processor' )
	];

	return $options;
}


/**
 * Compile telemetry data for a collection after it has been closed.
 *
 * @since 1.10.0
 *
 * @param int $collection_id The collection ID
 * @return void
 */
function efpic_compile_collection_telemetry_data( $collection_id ) {
	// Get the status
	$collection_status = efpic_get_collection_status( $collection_id );

	if ( ! in_array( $collection_status, [ 'sent', 'approved', 'expired', 'delivered' ] ) ) {
		return;
	}

	// Make sure the `get_plugins` function is available
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Get active plugins
	$plugins = [];
	$plugins_temp = [];
	$all_plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins' );
	foreach( $active_plugins as $plugin ) {
		if ( isset( $all_plugins[$plugin] ) ) {
			array_push( $plugins_temp, $all_plugins[$plugin] );
		}
	}
	foreach( $plugins_temp as $plugin ) {
		$plugins[] = [
			'name' => $plugin['Name'],
			'version' => $plugin['Version']
		];
	}

	// Get the active theme
	$theme = wp_get_theme();

	// Get collection meta
	$collection_meta = get_post_meta( $collection_id );

	// Get number of recipients, default is one
	$recipients = efpic_get_recipients_num( $collection_id );

	// Prepare time to approval variable
	$time_to_approval = 0;

	// Get images
	if ( $collection_status == 'delivered' ) {
		$images_ids = explode( ',' , get_post_meta( $collection_id, '_efpic_collection_delivery_ids', true ) );
		$image_num = count( $images_ids );
	}
	else {
		$images_ids = efpic_get_collection_images( $collection_id );
		$image_num = efpic_get_collection_image_num( $collection_id );
	}

	// Gather basic WordPress and efpic data
	$telemetry_data = [
		'version' => EFPIC_VERSION,
		'server' => $_SERVER[ 'SERVER_SOFTWARE' ],
		'php_version' => PHP_VERSION,
		'wordpress_version' => get_bloginfo( 'version' ),
		'plugin_num' => count( get_option( 'active_plugins' ) ),
		'plugins' => $plugins,
		'theme' => $theme->get( 'Name') . ' (' . $theme->get( 'Version') . ')',
		'language' => get_bloginfo( 'language' ),
		'options' => efpic_get_settings_for_telemetry(),
		'collection_status' => $collection_status,
		'password_protected' => post_password_required( $collection_id ),
		'image_num' => $image_num,
		'image_sizes' => efpic_telemetry_get_image_filesizes( $images_ids ),
		'share_method' => $collection_meta['_efpic_collection_share_method'][0],
		'settings_version' => get_option( 'efpic_settings_version' )
	];

	// Mark data from testing sites
	$env_type = wp_get_environment_type();
	if ( $env_type == 'local' OR $env_type == 'development' ) {
		$telemetry_data['testing'] = true;
	}

	// Get data depending on status
	if ( $collection_status != 'delivered' ) {
		// Use the right event time
		if ( $collection_status == 'approved' ) {
			$end = (float) efpic_get_collection_history_event_time( $collection_id, 'closed-manually' );
		}
		elseif ( $collection_status == 'expired' ) {
			$end = (float) efpic_get_collection_history_event_time( $collection_id, 'expired' );
		}
		else { // Fallback
			$end = (float) efpic_get_collection_history_event_time( $collection_id, 'approved' );
		}

		if ( ! empty( $end ) ) {
			$time_to_approval = $end - (float) efpic_get_collection_history_event_time( $collection_id, 'sent' );
		}

		$telemetry_data = array_merge( $telemetry_data, [
			'multi_collection' => $recipients > 1,
			'selection_num' => efpic_get_selection_count( $collection_id ),
			
			'time_to_approval' => $time_to_approval,
			'recipients' => $recipients,
		] );
	}

	// Gather Pro specific settings data, if module is active
	if ( defined( 'EFPIC_PRO' ) ) {
		$telemetry_data['efpic_pro']['version'] = EFPIC_PRO;

		// Certain data only makes sense for approved collections
		if ( $collection_status != 'delivered' ) {
			$selection_options = [];
			if ( ! empty( $collection_meta['_efpic_collection_selection_options'][0] ) ) {
				$selection_options = maybe_unserialize( $collection_meta['_efpic_collection_selection_options'][0] );
			}
			$telemetry_data = array_merge( $telemetry_data, [
				'selection_options' => $selection_options,
			]);

			$mark_comment = [];
			$mark_comment['active'] = ( ! empty( $collection_meta['_efpic_collection_mark_comment'][0] ) ) ? 'on' : 'off';
			$mark_comment['comments'] = efpic_telemetry_get_comments_num( $collection_id );
			$telemetry_data = array_merge( $telemetry_data, [
				'mark_comment' => $mark_comment,
			]);

			$download = [];
			if ( ! empty( $collection_meta['_efpic_collection_download_images'][0] ) ) {
				$download = maybe_unserialize( $collection_meta['_efpic_collection_download_images'][0] );
			}
			$telemetry_data = array_merge( $telemetry_data, [
				'download' => $download,
			]);

			$ecommerce = [];
			if ( ! empty( $collection_meta['_efpic_collection_ecommerce'][0] ) ) {
				$ecommerce['active'] = 'on';
				$ecommerce['pricing_type'] = $collection_meta['_efpic_collection_pricing_type'][0] ?? '';
				
				// Volume pricing config
				if ( ! empty( $collection_meta['_efpic_collection_volume_pricing'][0] ) ) {
					$volume_pricing = maybe_unserialize( $collection_meta['_efpic_collection_volume_pricing'][0] );
					$ecommerce['tier_pricing'] = efpic_telemetry_on_off( $volume_pricing['tier_pricing'] ?? '' );
					$ecommerce['tier_num'] = isset( $volume_pricing['tiers'] ) ? count( $volume_pricing['tiers'] ) : 0;
				}
				
				// Payment methods (only include if meta exists)
				if ( isset( $collection_meta['_efpic_collection_payment_active_stripe'][0] ) ) {
					$ecommerce['payment_stripe'] = efpic_telemetry_on_off( $collection_meta['_efpic_collection_payment_active_stripe'][0] );
				}
				if ( isset( $collection_meta['_efpic_collection_payment_active_paypal'][0] ) ) {
					$ecommerce['payment_paypal'] = efpic_telemetry_on_off( $collection_meta['_efpic_collection_payment_active_paypal'][0] );
				}
				if ( isset( $collection_meta['_efpic_collection_payment_active_bank-transfer'][0] ) ) {
					$ecommerce['payment_bank_transfer'] = efpic_telemetry_on_off( $collection_meta['_efpic_collection_payment_active_bank-transfer'][0] );
				}
			}
			else {
				$ecommerce['active'] = 'off';
			}

			$telemetry_data = array_merge( $telemetry_data, [
				'ecommerce' => $ecommerce,
			]);
		}

		// Get Pro options
		if ( function_exists( 'efpic_pro_get_settings_for_telemetry' ) ) {
			$pro_options = efpic_pro_get_settings_for_telemetry();

			if ( is_array( $pro_options['email_templates'] ) ) {
				$pro_options['email_templates'] = count( $pro_options['email_templates'] );
			}
			else {
				$pro_options['email_templates'] = 'off';
			}

			$telemetry_data = array_merge( $telemetry_data, [ 'pro_options' => $pro_options ] );
		}
	}

	// Anonymize data
	$telemetry_data = efpic_anonymize_telemetry_data( $telemetry_data );

	// Update the telemetry cache
	efpic_update_telemetry_cache( $telemetry_data, $collection_id, $collection_status );
}


/**
 * Update the telemetry cache.
 *
 * @since 3.4.0
 *
 * @param array $telemetry_data The telemetry data for the collection.
 * @param int $collection_id The collection post ID.
 * @param string $collection_status The collection status.
 */
function efpic_update_telemetry_cache( $telemetry_data, $collection_id, $collection_status ) {
	// Get cached telemetry data
	$telemetry_cache = get_option( 'efpic_telemetry_cache', [] );

	// Add new telemetry data entry
	$telemetry_cache[] = $telemetry_data;

	// As we do not want to process the same collection twice,
	// we add the collection ID to the list of processed collections
	$processed = get_option( 'efpic_telemetry_processed', [] );
	$processed_delivery = get_option( 'efpic_telemetry_delivery_processed', [] );

	// Update cache and list of processed collections
	if ( ! in_array( $collection_id, $processed ) && ( in_array( $collection_status, [ 'sent', 'approved', 'expired' ] ) ) ) {
		update_option( 'efpic_telemetry_cache', $telemetry_cache, false );
		$processed[] = $collection_id;
		update_option( 'efpic_telemetry_processed', $processed, false );
	}

	if ( ! in_array( $collection_id, $processed_delivery ) && $collection_status == 'delivered' ) {
		update_option( 'efpic_telemetry_cache', $telemetry_cache, false );
		$processed_delivery[] = $collection_id;
		update_option( 'efpic_telemetry_delivery_processed', $processed_delivery, false );
	}
}


/**
 * Compile telemetry data for an order.
 *
 * @since 3.4.0
 *
 * @param int $order_id The order post ID.
 */
function efpic_compile_order_telemetry_data( $order_id ) {
	$order_telemetry_cache = get_option( 'efpic_telemetry_cache_orders', [] );
	$processed = get_option( 'efpic_telemetry_processed', [] );
	$order_status = get_post_status( $order_id );

	// Skip if processed or wrong status
	if ( in_array( $order_id, $processed ) || ( ! in_array( $order_status, [ 'completed', 'failed', 'refunded' ] ) ) ) {
		return;
	}

	// Get order meta
	$order_meta = get_post_meta( $order_id );

	// Get pricing info
	$pricing = maybe_unserialize( $order_meta['_efpic_order_pricing'][0] ?? '' );

	$pricing_data = [
		'type' => $pricing['type'] ?? '',
	];

	// Add volume pricing details if applicable
	if ( ( $pricing['type'] ?? '' ) === 'volume-pricing' && ! empty( $pricing['volume_pricing'] ) ) {
		$pricing_data['tier_pricing'] = efpic_telemetry_on_off( $pricing['volume_pricing']['tier_pricing'] ?? '' );
		$pricing_data['tier_num'] = count( $pricing['volume_pricing']['tiers'] ?? [] );
	}

	$telemetry_data = [
		'status' => $order_status,
		'payment_provider' => $order_meta['_efpic_payment_provider'][0] ?? '' ?: 'none',
		'image_count' => (int) ( $order_meta['_efpic_order_selected_images_num'][0] ?? 0 ),
		'pricing' => $pricing_data,
		'tax' => efpic_telemetry_on_off( $order_meta['_efpic_order_tax'][0] ?? '' ),
		'currency' => strtoupper( $order_meta['_efpic_order_currency'][0] ?? '' ),
	];

	// Mark as processed
	$processed[] = $order_id;
	update_option( 'efpic_telemetry_processed', $processed, false );

	// Save data
	$order_telemetry_cache[] = $telemetry_data;
	update_option( 'efpic_telemetry_cache_orders', $order_telemetry_cache, false );
}


/**
 * Maybe add open collections to telemetry data.
 *
 * @since 2.3.0
 */
function efpic_compile_telemetry_data() {
	// Get already processed collections
	$already_processed = get_option( 'efpic_telemetry_processed', [] );

	// Query all sent collections without expiration
	$args = [
		'post_type' => 'efpic_collection',
		'post_status' => 'sent',
		'posts_per_page' => 500,
		'post__not_in' => $already_processed,
		'meta_query' => [
			'relation' => 'OR',
			'no_expiration' => [
				'key' => '_efpic_collection_expiration',
				'compare' => 'NOT EXISTS',
			],
			'expiration_off' => [
				'key' => '_efpic_collection_expiration',
				'value' => 'off',
			],
		],
	];

	$open_collections = get_posts( $args );

	foreach( $open_collections as $collection ) {
		$last_approved = efpic_get_collection_history_event_time( $collection->ID, 'approved-by-client' );
		
		// Only capture collections where the last approval was more than 2 months ago
		if ( ! empty( $last_approved ) && ( $last_approved - strtotime( '-2 months' ) ) < 0 ) {
			efpic_compile_collection_telemetry_data( $collection->ID );
		}
	}

	$args = [
		'post_type' => 'efpic_collection',
		'post_status' => [ 'approved', 'expired' ],
		'posts_per_page' => 500,
		'post__not_in' => $already_processed,
	];

	$closed_collections = get_posts( $args );

	foreach( $closed_collections as $collection ) {
		efpic_compile_collection_telemetry_data( $collection->ID );
	}


	// Process order data
	$processed = get_option( 'efpic_telemetry_processed', [] );

	$process_orders = get_posts([
		'post_type' => 'efpic_order',
		'post_status' => [ 'completed', 'failed', 'refunded' ],
		'posts_per_page' => -1,
		'orderby' => 'modified',
		'order' => 'ASC',
		'fields' => 'ids',
		'post__not_in' => $processed,
		'date_query' => [
			[
				'column' => 'post_modified',
				'before' => '1 week ago',
			],
		],
	]);

	foreach ( $process_orders as $order_id ) {
		efpic_compile_order_telemetry_data( $order_id );
	}
}


/**
 * Trigger data collection when a delivery collection is published.
 *
 * @since 1.10.0
 *
 * @param string $new_status The new status
 * @param string $old_status The old status
 * @param object $post The post object
 */
function efpic_compile_delivery_collection_telemetry_data( $new_status, $old_status, $post ) {
	// Abort if this is not a delivery collection being sent/published
	if ( $new_status != 'delivered' && $old_status != 'delivery-draft' ) {
		return;
	}
	efpic_compile_collection_telemetry_data( $post->ID );
}

add_action( 'transition_post_status', 'efpic_compile_delivery_collection_telemetry_data', 10, 3 );


/**
 * Return max, min and median sizes of an array of images.
 *
 * @since 1.10.0
 *
 * @param array $image_ids An array of image IDs
 * @return array Max, min and median images sizes
 */
function efpic_telemetry_get_image_filesizes( $image_ids ) {
	$sizes = [];

	if ( empty( $image_ids ) ) {
		return $sizes;
	}

	$image_sizes = [];
	foreach( $image_ids as $image_id ) {
		$filesize = filesize( get_attached_file( $image_id ) );
		if ( ! empty( $filesize ) ) {
			$image_sizes[] = $filesize;
		}
		else {
			$image_sizes[] = 0;
		}
	}

	sort( $image_sizes );

	// Get max and min
	$max = max( $image_sizes );
	$min = min( $image_sizes );

	// Calculate median
	$count = count( $image_sizes );
	$middle = $count / 2;
	if ( ! is_int( $middle ) ) {
		$median = $image_sizes[ floor( $middle ) ]; 
	}
	else{
		$temp1 = $image_sizes[ $middle ]; 
		$temp2 = $image_sizes[ $middle - 1 ]; 
		$median = ( $temp1 + $temp2 ) / 2;
	}

	$sizes = [
		'max' => $max,
		'min' => $min,
		'median' => $median
	];

	return $sizes;
}


/**
 * Get number of markers/comments across all recipients.
 *
 * @since 1.10.0
 *
 * @param int $collection_id The collection ID
 * @return int The number of comments
 */
function efpic_telemetry_get_comments_num( $collection_id ) {
	$num = 0;
	$markers = [];

	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
	if ( ! empty( $hashes ) ) {
		foreach( $hashes as $hash => $value ) {
			$selection = get_post_meta( $collection_id, '_efpic_collection_selection_' . $hash, true );
			if ( $selection AND ! empty( $selection['markers'] ) ) {
				$markers = array_merge( $markers, $selection['markers'] );
			}
		}
		
	}
	else {
		$selection = get_post_meta( $collection_id, '_efpic_collection_selection', true );
		if ( $selection AND ! empty( $selection['markers'] ) ) {
			$markers = array_merge( $markers, $selection['markers'] );
		}
	}

	foreach( $markers as $image ) {
		$num = $num + count( $image );
	}

	return $num;
}


/**
 * Anonymize settings.
 *
 * This is where we define which options should be anonymized.
 *
 * @since 1.10.0
 *
 * @param array $telemetry_data The unanonymized telemetry data
 * @return array The anonymized data
 */
function efpic_anonymize_telemetry_data( $telemetry_data ) {
	/**
	 * Core options
	 */
	$telemetry_data['options'] = efpic_anonymize( $telemetry_data['options'], [ 
		'from_email',
		'from_name',
		'notification_email'
	] );

	/**
	 * Pro options
	 */
	if ( ! empty( $telemetry_data['pro_options'] ) ) {
		$telemetry_data['pro_options'] = efpic_anonymize( $telemetry_data['pro_options'], [ 
			'logo',
		] );
	}

	if ( ! empty( $telemetry_data['pro_options']['after_approval'] ) ) {
		$telemetry_data['pro_options']['after_approval'] = efpic_anonymize( $telemetry_data['pro_options']['after_approval'], [ 
			'after_approval_message',
			'target_url',
		] );
	}

	if ( ! empty( $telemetry_data['pro_options']['font'] ) ) {
		$telemetry_data['pro_options']['font'] = efpic_anonymize( $telemetry_data['pro_options']['font'], [ 
			'external_font_name',
			'external_font_code',
			'external_font_kit_id',
		] );
	}

	if ( ! empty( $telemetry_data['pro_options']['watermark'] ) ) {
		$telemetry_data['pro_options']['watermark'] = efpic_anonymize( $telemetry_data['pro_options']['watermark'], [ 
			'watermark',
		] );
	}

	if ( isset( $telemetry_data['pro_options']['address'] ) ) {
		$telemetry_data['pro_options']['address'] = efpic_telemetry_on_off( $telemetry_data['pro_options']['address'] );
	}

	/**
	 * Download
	 */
	if ( ! empty( $telemetry_data['download'] ) ) {
		$telemetry_data['download'] = efpic_anonymize( $telemetry_data['download'], [ 
			'url'
		] );
	}

	return $telemetry_data;
}


/**
 * Map array and anonymize values for keys given.
 *
 * @since 1.10.0
 *
 * @param array $data The telemetry data array to anonymize
 * @param array $key The keys to look for
 * @return array The anonymozed data array
 */
function efpic_anonymize( $data, $keys ) {
	if ( is_array( $data ) && is_array( $keys ) ) {
		foreach( $data as $key => $value ) {
			if ( in_array( $key, $keys ) ) {
				$data[$key] = efpic_telemetry_on_off( $value );
			}
		}
	}

	return $data;
}


/**
 * Helper function to anonymize data.
 *
 * @since 1.10.0
 *
 * @param mixed $metric The data point value to be anonymized
 * @return string Either "on" or "off"
 */
function efpic_telemetry_on_off( $data_point ) {
	if ( ! empty( $data_point ) ) {
		return 'on';
	}

	return 'off';
}