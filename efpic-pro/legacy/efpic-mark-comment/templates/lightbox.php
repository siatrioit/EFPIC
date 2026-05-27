<?php
/**
 * Replaces the lightbox template
 *
 * @since mark-comment (0.0.1)
 *
 * @see Original: efpic/frontend/js/templates/efpic-lightbox.php
 */
defined( 'EFPIC_PRO' ) OR exit;


?>
<script id="efpic-lightbox" type="text/template">
<@
let date_format = this.appstate.attributes.date_format;
let time_format = this.appstate.attributes.time_format;
let lang = this.appstate.attributes.lang;
// Get WordPress timezone UTC difference in minutes
let utc_diff = <?php echo wp_date( 'Z' ) / 60; ?>;
// Get the users timezone URC difference in minutes
let now = new Date();
let utc_local = now.getTimezoneOffset();
// Caclulate the difference, then convert it to seconds
let timezone_diff = ( utc_diff + utc_local ) * 60;
@>
	<div class="efpic-lightbox-inner mark-comment<@ if ( '' != markers && null != markers ) { if ( 0 < Object.keys( markers ).length ) { print( ' has-markers' ) } } @><@ if ( this.appstate.attributes.comments == false ) { print( ' comment-box-is-collapsed' ) } %>">
		<div class="efpic-lightbox-image-container-wrap">
			<div class="efpic-lightbox-image-container">
					<@ var poststatus = this.appstate.attributes.poststatus; @>
					<img <@ if ( selected == true ) { print( 'class="selected" ') } %> src="<@= imagePath @>" srcset="<@= imagePath_srcset @>" sizes="<@= sizeLightbox @>" />

					<@ if ( '' != markers && null != markers ) { if ( 0 < Object.keys( markers ).length ) {
						Object.keys( markers ).forEach( function( id ) {
							// Only display a marker if x and y are defined
							if ( markers[id].x != '' && markers[id].y != '' ) {
							@>
							<div id="marker-<@= id @>" class="marker is-waiting" tabindex="0" style="" data-x="<@= markers[id].x @>" data-y="<@= markers[id].y @>">
							</div>
							<@
							}
						});
					} }
					@>

			</div>

			<div class="efpic-comment-box">
				<span class="efpic-comment-box-toggle"><svg viewBox="0 0 100 100"><use xlink:href="#<@ if ( '' != markers && null != markers ) { if ( 0 < Object.keys( markers ).length ) { print( 'icon-comment-single' ) } } else { print( 'icon-comment-empty') } @>"></use></svg><span><?php _e( 'Toggle Comments', 'efpic-pro' ); ?></span></span>
				<div class="efpic-comment-box-inner">
					<span class="efpic-comment-box-header"><?php _e( 'Comments', 'efpic-pro' ); ?></span>
					<@ if ( poststatus != 'approved' ) { @>
					<div class="efpic-comment-box-intro"><?php _e( 'Click anywhere on the image to add <strong>a marker</strong> or simply add <strong>a comment</strong> by clicking below.', 'efpic-pro' ); ?></div>
					<@ } @>

					<div class="efpic-comments">

						<@ if ( '' != markers && null != markers ) {
							if ( 0 < Object.keys( markers ).length ) {
								Object.keys( markers ).forEach( function( id ) {
									if ( markers[id].x != '' && markers[id].y != '' ) {
										var hasMarker = 'has-marker';
									}
									@>
									<div id="comment-<@= id @>" class="efpic-comment <@= hasMarker @>">
										<div class="efpic-comment-textarea"><@= markers[id].comment @></div>
										<div class="efpic-comment-meta">
										<@ if ( null != markers[id].time ) {
											let timestamp = parseInt( markers[id].time );
											timestamp = Math.round( ( timestamp + timezone_diff ) * 1000 ); // Convert to milliseconds
											let markerDate = new Date( timestamp );
											var formattedDate = markerDate.format( date_format + ' ' + time_format, lang );
										@>
											<span class="efpic-comment-meta__time"><@= formattedDate @></span>
										<@ } @>
										</div>
										<@ if ( poststatus != 'approved' ) { @>
										<div class="efpic-comment-controls">
											<span class="efpic-comment-control efpic-delete-comment"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span><?php _e( 'Delete', 'efpic-pro' ); ?></span></span>
											<span class="efpic-comment-control efpic-save-comment"><svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg><span><?php _e( 'Save', 'efpic-pro' ); ?></span></span>
										</div>
										<@ }
										if ( markers[id].x != '' && markers[id].y != '' ) { @>
										<span class="efpic-comment-marker"></span><@ } @>
									</div>
									<@
								});
							}
						}
						@>

					</div>

					<@ if ( poststatus != 'approved' ) { @>
					<span class="efpic-add-comment"><svg viewBox="0 0 100 100"><use xlink:href="#icon_plus"></use></svg><span><?php _e( 'Add Comment', 'efpic-pro' ); ?></span></span>
					<@ } @>
				</div>
			</div>
		</div><!-- .efpic-comment-box-inner -->

		<nav class="efpic-lightbox-navigation">
			<a class="efpic-lightbox-close" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span><?php _e( 'close lightbox', 'efpic' ); ?></span></a>
			<span class="efpic-lightbox-meta">
				<span class="efpic-img-name" title="<@= Object.keys(title).map( function( key ) { return title[key]; } ).join( ' ' ) @>"><@= Object.keys(title).map( function( key ) { return '<span class="' + key + '">' + title[key] +  '</span>'; } ).join( ' ' ) @></span>
				<@ if ( has_in_price ) { @>
				<span class="efpic-lightbox-in-price">
					<span class="efpic-lightbox-in-price__count"><@= selected_count @> / <@= all_count @></span>
					<span class="efpic-lightbox-in-price__package"><@= in_price_package @></span>
					<@ if ( in_price_extra_line ) { @><span class="efpic-lightbox-in-price__extra"><@= in_price_extra_line @></span><@ } @>
				</span>
				<@ } @>
			</span>
			<a class="efpic-lightbox-next"><svg viewBox="0 0 100 100"><use xlink:href="#icon_arrow_right"></use></svg><span><?php _e( 'next image', 'efpic' ); ?></span></a>
			<a class="efpic-lightbox-prev"><svg viewBox="0 0 100 100"><use xlink:href="#icon_arrow_left"></use></svg><span><?php _e( 'previous image', 'efpic' ); ?></span></a>
			<@ if ( JSON.parse( appstate ).poststatus != 'approved' && JSON.parse( appstate ).poststatus != 'expired' ) { @>
				<a class="efpic-lightbox-select<@ if ( selected == true ) { print( ' selected' ) } @>"><svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg><span><?php _e( 'select image', 'efpic' ); ?></span></a>
			<@ } @>
			<@ if ( JSON.parse( appstate ).is_zip_download_enabled ) { @>
			<a class="efpic-download-button" href="<@= imagePath @>" download><span><?php _e( 'Download', 'efpic-pro' ); ?></span><svg viewBox="0 0 100 100"><use xlink:href="#icon-download"></use></svg></a>
			<@ } @>
    	</nav>
	</div><!-- .efpic-lightbox-inner.mark-comment -->
</script>