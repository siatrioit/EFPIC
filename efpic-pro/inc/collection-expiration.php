<?php
/**
 * Enhanced expiration options
 *
 * @since 1.2.0
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Add expiration date field.
 *
 * @since 1.2.0
 *
 * @param string $output The original expiration field output
 * @param string $expiration Either "on" or "off"
 * @param int $days Default number of days after which a collection expires
 * @return string The filtered expiration field output
 */
function efpic_pro_expiration_option( $output, $expiration, $days ) {
	ob_start();
	?>
	<div class="efpic-option-item">
		<input type="checkbox" name="collection_expires" id="collection_expires" <?php if ( isset ( $expiration ) ) checked( $expiration, 'on' ); ?> />
		<label for="collection_expires"><?php _e( 'Collection expires', 'efpic-pro' ); ?></label>
		<?php
			$min = current_time( 'Y-m-d' ) . 'T' . current_time( 'H:i' );
			$expiration_time = get_post_meta( get_the_ID(), '_efpic_collection_expiration_time', true );
			if ( empty( $expiration_time ) ) {
				$expiration_time = wp_date( 'Y-m-d\TH:i', efpic_calculate_expiration_time() );
			}
			else {
				$expiration_time = wp_date( 'Y-m-d\TH:i', $expiration_time );
			}
		?>
		<div class="expiration-date__wrap">
			<label for="expiration_date"><?php _e( 'Expiration date:', 'efpic-pro' ); ?></label>
			<input type="datetime-local" id="expiration_date" name="expiration_date" value="<?php echo $expiration_time; ?>" min="<?php echo $min; ?>" autocomplete="off" />
		</div>
	</div>
	<?php
	echo ob_get_clean();
}

add_filter( 'efpic_expiration_option', 'efpic_pro_expiration_option', 10, 3 );


/**
 * Save collection expiration date.
 *
 * Make sure this runs after the expiration handling of efpic Core.
 *
 * @since 1.2.0
 *
 * @param int $collection_id The collection ID
 */
function efpic_pro_save_expiration_option( $collection_id ) {
	// The general "on/off" state of expiration will be saved by efpic Core
	if ( ! empty( $_POST['expiration_date'] ) AND ! empty( $_POST['collection_expires'] ) AND $_POST['collection_expires'] == 'on' ) {
		// Get timestamp
		$expiration_date = strtotime( $_POST['expiration_date'] );
		// Correct timestamp with blog's timezone setting
		$expiration_date = ( get_option( 'gmt_offset' ) * -3600 ) + $expiration_date;
		// Save expiration time
		update_post_meta( $collection_id, '_efpic_collection_expiration_time', $expiration_date );
	}
}

add_action( 'save_post_efpic_collection', 'efpic_pro_save_expiration_option', 9 );


/**
 * Filter expiration length with `efpic_expiration_length` setting.
 *
 * @since 1.2.
 *
 * @param int $days Number of days
 * @return int Custom number of days
 */
function efpic_pro_expiration_length( $days ) {
	$days = get_option( 'efpic_expiration_length' );
	return $days;
}

add_filter( 'efpic_expiration_length', 'efpic_pro_expiration_length' );