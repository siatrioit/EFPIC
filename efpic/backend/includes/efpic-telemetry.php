<?php
/**
 * Telemetry disabled — remove scheduled tasks and stored data.
 *
 * @since 3.5.2-custom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stop cron jobs and delete telemetry options from the database.
 */
function efpic_remove_telemetry() {
	wp_clear_scheduled_hook( 'efpic_run_telemetry_transmit' );
	wp_clear_scheduled_hook( 'efpic_run_compile_telemetry_data' );

	$options = array(
		'efpic_telemetry_settings',
		'efpic_telemetry_cache',
		'efpic_telemetry_cache_orders',
		'efpic_telemetry_processed',
		'efpic_telemetry_delivery_processed',
		'efpic_telemetry_nag',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}
}

add_action( 'init', 'efpic_remove_telemetry', 1 );
