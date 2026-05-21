<script id="efpic-gallery-item" type="text/template">
    <figure class="efpic-figure" tabindex="0">
        <div class="efpic-imgbox <@= orientation @>">
            <div class="efpic-imgbox-inner">
				<@ if ( lazyloaded == true ) { @>
				<a class="efpic-imgbox-link" href='#<@= number @>' tabindex="-1"><img src="<@= imagePath_small @>" srcset="<@= imagePath_small_srcset @>" sizes="<@= size @>" /></a>
				<@ } else { @>
					<a class="efpic-imgbox-link" href='#<@= number @>' tabindex="-1"><img class="lazy" src="<?php echo EFPIC_URL; ?>frontend/images/ripple-dark.gif" style="width: 20px; height: 20px;" /></a>
				<@ } @>
            </div>
        </div>
        <figcaption class="efpic-caption">
            <div class="efpic-img-title">
                <span class="efpic-img-name" title="<@= Object.keys( title ).map( function( key ) { return title[key]; } ).join( ' ' ) @>"><@= Object.keys( title ).map( function( key ) { return '<span class="' + key + '">' + title[key] + '</span>'; } ).join( ' ' ) @>
                </span>
            </div>
			<@ if ( JSON.parse( appstate ).poststatus != 'approved' && JSON.parse( appstate ).poststatus != 'expired' ) { @>
            <div class="efpic-select-item">
                <input type="checkbox" name="approved-<@= number @>" id="check<@= number @>" value="<@= imageID @>" tabindex="-1" /> <label for="check<@= number @>" tabindex="-1">
                    <svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg>
                    <span class="efpic-select-label"><?php _e( 'Select image', 'efpic' ); ?> <@= number @></span>
                </label>
            </div>
            <@ } @>
        </figcaption>
    </figure><!-- .efpic-figure -->
</script>