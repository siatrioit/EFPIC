<script id="efpic-lightbox" type="text/template">
    <div class="efpic-lightbox-inner">
        <div class="efpic-lightbox-image-container">
            <a href="#index"><img <@ if ( selected == true ) { print( 'class="selected" ') } @> src="<@= imagePath @>" srcset="<@= imagePath_srcset @>" sizes="<@= sizeLightbox @>" /></a>
        </div>
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
			<a class="efpic-download-button" href="<@= imagePath_original @>" download><span><?php _e( 'Download', 'efpic' ); ?></span><svg viewBox="0 0 100 100"><use xlink:href="#icon-download"></use></svg></a>
			<@ } @>
        </nav>
    </div><!-- .efpic-lightbox-inner -->
</script>