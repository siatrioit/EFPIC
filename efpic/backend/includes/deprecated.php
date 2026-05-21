<?php
/**
 * Deprecated functions
 *
 * @since 1.6.0
 */
defined( 'ABSPATH' ) || exit;


/**
 * Deprecated `efpic_mail_subject` filter.
 *
 * @since 2.0.0
 */
function efpic_mail_subject_deprecated( $subject, $mail_context, $post_id ) {
	return apply_filters_deprecated( 'efpic_mail_subject', array( $subject, $mail_context, $post_id ), '2.0.0', 'efpic_email_subject' );
}

add_filter( 'efpic_mail_subject', 'efpic_mail_subject_deprecated', 0, 3 );


/**
 * Deprecate `efpic_email_from` filter
 *
 * @since 2.0.0
 */
function efpic_email_from_deprecated( $from ) {
	if ( ! empty( $from['from_name'] ) ) {
		$from['from_name'] = apply_filters_deprecated( 'efpic_email_from', $from['from_name'], '2.0.0', 'efpic_email_from_name' );
	}

	if ( ! empty( $from['from_address'] ) ) {
		$from['from_address'] = apply_filters_deprecated( 'efpic_email_from', $from['from_address'], '2.0.0', 'efpic_email_from_address' );
	}

	return $from;
}

add_filter( 'efpic_email_from', 'efpic_email_from_deprecated', 0 );


/**
 * Display notice about switchung to the new Pro plugin.
 *
 * @since 1.6.4
 */
function efpic_old_pro_notice() {
	// Check if any of the old Pro modules are active
	$efpic_addons = [];
	$efpic_addons = apply_filters( 'efpic_addons', $efpic_addons );
	if ( ! is_array( $efpic_addons ) OR count( $efpic_addons ) <= 0 ) {
		return;
	}

	/* translators: Admin notice */
	$notice = __( '🚨 <strong>Action required:</strong> The efpic Pro modules you are using are not compatible with this version of efpic. Please update to the latest version of efpic Pro.', 'efpic' ) . ' <a href="https://efpic.io/docs/efpic-2-update/">' . __( 'Learn more…', 'efpic' ) . '</a>';
	$notice_type = 'error';

	// Display admin notice
	add_action( 'admin_notices', function() use ( $notice, $notice_type ) {
		?>
		<div class="efpic-pro-module-notice notice notice-<?php echo $notice_type; ?>">
			<p><?php echo $notice; ?></p>
		</div>
		<?php
	});
}

add_action( 'init', 'efpic_old_pro_notice' );