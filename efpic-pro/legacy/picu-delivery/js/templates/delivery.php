<?php
/**
 * The delivery template
 *
 * @since delivery (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


?>
<script id="efpic-delivery" type="text/template">
	<div class="efpic-delivery-inner">
		<div class="efpic-delivery-intro">
			<?php /* translators: Headline in the delivery box in the client view */ ?>
			<h2 class="efpic-delivery-title"><?php echo apply_filters( 'efpic_delivery_box_title' ,__( 'Download Images', 'efpic-pro' ) ); ?></h2>
			<?php /* translators: Text in the delivery box in the client view */ ?>
			<div class="efpic-delivery-intro-text"><?php echo apply_filters( 'efpic_delivery_box_message' ,__( 'You can download your images by clicking on the buttons below.', 'efpic-pro' ) ); ?></div>
		</div>
		<div class="efpic-delivery-actions">
			<@ if ( this.appstate.get( 'delivery_option' ) == 'external' ) { @>
			<?php /* translators: Button text */ ?>
			<a class="efpic-button primary js-zip-download" href="<@= this.appstate.attributes.delivery_zip_url @>"><?php _e( 'Download', 'efpic-pro' ); ?></a><a id="efpic-main-download-link" href="<@= this.appstate.attributes.delivery_zip_url @>" download><?php _e( 'Download', 'efpic-pro' ); ?></a>	
			<@ } else { @>
			<?php /* translators: Button text */ ?>
			<a class="efpic-button primary js-zip-download" href="<@= this.appstate.attributes.delivery_zip_url @>"><?php _e( 'Download All', 'efpic-pro' ); ?></a><a id="efpic-main-download-link" href="<@= this.appstate.attributes.delivery_zip_url @>" download><?php _e( 'Download', 'efpic-pro' ); ?></a>
			<?php
				$show_images = apply_filters( 'efpic_delivery_show_images_by_default', false );
			?>
			<input type="checkbox" id="efpic-delivery-images-toggle" <?php checked( $show_images, true ); ?>>
			<label for="efpic-delivery-images-toggle" class="efpic-delivery-toggle-images efpic-delivery-toggle-images-show"><?php _e( 'Show Images', 'efpic-pro' ); ?> <svg viewBox="0 0 100 100"><use xlink:href="#icon_arrow_down"></use></svg></label>
			<label for="efpic-delivery-images-toggle" class="efpic-delivery-toggle-images efpic-delivery-toggle-images-hide"><?php _e( 'Hide Images', 'efpic-pro' ); ?> <svg viewBox="0 0 100 100"><use xlink:href="#icon_arrow_up"></use></svg></label>
			<ul class="efpic-delivery-images"><@= this.deliveryItems @></ul>
			<@ } @>
		</div>
	</div><!-- .efpic-delivery-inner -->
</script> 