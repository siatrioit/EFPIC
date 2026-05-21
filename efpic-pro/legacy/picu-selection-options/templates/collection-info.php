<?php
/**
 * Replaces the collection-info template
 *
 * @since selection-options (0.0.1)
 *
 * @see Original: efpic/frontend/js/templates/efpic-collection-info.php
 */
defined( 'EFPIC_PRO' ) OR exit;


?>
<script id="efpic-info-view" type="text/template">
	<div class="efpic-modal-inner">
		<h1><@= title @></h1>
		<div class="info-panel">
			<div class="panel-item">
				<div class="panel-value"><@= imagecount @></div>
				<div class="panel-label"><?php _e( 'Images', 'efpic'); ?></div>
			</div>
			<div class="panel-item">
				<div class="panel-value"><@= selected @></div>
				<div class="panel-label"><?php _e( 'selected', 'efpic'); ?></div>
			</div>
			<?php
				$panels = array();
				echo implode( '',  apply_filters( 'efpic_info_view_panel_items', $panels ) );
			?>
			<@
				if ( typeof appstate.attributes.selection_restriction !== 'undefined' ) {
					if ( appstate.attributes.selection_restriction.selection_option == true ) {
			@>
			<div class="selection-options">
				<@= appstate.attributes.selection_restriction.selection_info @>
			</div>
			<@
					}
				}
			@>
		</div>
		<div class="description"><@= description @>
		<?php
			if ( get_post_status() == 'expired' ) {
				echo '<p class="additional-info">' . __( '<em>Please Note:</em> This collection has expired. Therefore it is not possible to change your selection at this time.', 'efpic-pro' ) . '</p>';
			}
			$expiration_time = get_post_meta( $post->ID, '_efpic_collection_expiration_time', true );
			if ( ! empty( $expiration_time ) AND get_post_status() != 'expired' ) {
				$expiration_time = wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $expiration_time );
				echo '<p class="additional-info">' . sprintf( __( '<em>Please Note:</em> This collection will expire on %s and you won\'t be able to make changes after that.', 'efpic-pro' ), $expiration_time ) . '</p>';
			}
		?>
		</div>
		<a class="efpic-button primary efpic-start-selection" href="#index">
		<@ if ( efpic.poststatus != 'approved' ) { @>
		<?php _e( 'OK', 'efpic' ); ?>
		<@ } else { @>
		<?php _e( 'View collection', 'efpic' ); ?>
		<@ } @>
		</a>
		<a class="efpic-close-modal" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span><?php _e( 'close', 'efpic' ); ?></span></a>
	</div><!-- .efpic-modal-inner -->
</script>