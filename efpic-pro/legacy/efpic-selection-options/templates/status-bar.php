<?php
/**
 * Replaces the status-bar template
 *
 * @since selection-options (0.0.1)
 *
 * @see Original: efpic/frontend/js/templates/efpic-status-bar.php
 */
defined( 'EFPIC_PRO' ) OR exit;


?>
<script id="efpic-status-bar" type="text/template">
	<?php
		$after_info_button = '';
		echo apply_filters( 'efpic_frontend_after_info_button', $after_info_button );
	?>
	<div class="efpic-display-filter">
		<a class="efpic-filter-selected">
			<span class="efpic-filter-icon"><svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg></span>
			<span class="efpic-filter-label"><?php _e( 'Selected', 'efpic-pro' ); ?></span>
		</a>
		<a class="efpic-filter-unselected">
			<span class="efpic-filter-icon"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg></span>
			<span class="efpic-filter-label"><?php _e( 'Unselected', 'efpic-pro' ); ?></span>
		</a>
		<a class="efpic-filter-reset"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span><?php _e( 'Reset filters', 'efpic-pro' ); ?></span></a>
	</div>
	<div class="efpic-grid-size">
		<button class="efpic-grid-size__toggle"><?php _e( 'Grid Size', 'efpic-pro' ); ?></button>
		<ul class="efpic-grid-size__list">
			<li class="efpic-grid-size__switch__wrap-small"><button class="efpic-grid-size__switch efpic-grid-size__small" id="grid-size-small"><abbr class="efpic-grid-size__abbr"><?php _e( 'S', 'efpic-pro' ); ?></abbr><span class="efpic-grid-size__size"><?php _e( 'Small', 'efpic-pro' ); ?></span></button></li>
			<li class="efpic-grid-size__switch__wrap-medium"><button class="efpic-grid-size__switch efpic-grid-size__medium" id="grid-size-medium"><abbr class="efpic-grid-size__abbr"><?php _e( 'M', 'efpic-pro' ); ?></abbr><span class="efpic-grid-size__size"><?php _e( 'Medium', 'efpic-pro' ); ?></span></button></li>
			<li class="efpic-grid-size__switch__wrap-large"><button class="efpic-grid-size__switch efpic-grid-size__large" id="grid-size-large"><abbr class="efpic-grid-size__abbr"><?php _e( 'L', 'efpic-pro' ); ?></abbr><span class="efpic-grid-size__size"><?php _e( 'Large', 'efpic-pro' ); ?></span></button></li>
		</ul>
	</div>
	<div class="efpic-selection-count">
		<a class="efpic-info-button" href="#collection-info" title="<?php _e( 'Show Information about this collection', 'efpic-pro' ); ?>"><?php _e( 'Show Information about this collection', 'efpic-pro' ); ?></a>
		<span class="efpic-selected-num"><@= selected @></span> / <span class="efpic-total-num"><@= all @></span>
		<@
			if ( typeof appstate.attributes.selection_restriction !== 'undefined' && appstate.attributes.selection_restriction.restriction === 'in price' && appstate.attributes.selection_restriction.selection_option == true ) {
		@>
		<div class="efpic-in-price-info">
			<span class="efpic-in-price-info__package"><@= in_price_package @></span>
			<@ if ( in_price_extra_line ) { @>
			<span class="efpic-in-price-info__extra"><@= in_price_extra_line @></span>
			<@ } @>
		</div>
		<@
			}
			if ( typeof appstate.attributes.selection_restriction !== 'undefined' ) {
				if ( appstate.attributes.selection_restriction.selection_option == true && restriction_warning == true ) {
			@>
			<div class="efpic-selection-alert">
				<span class="efpic-selection-alert__icon <@= animation @>"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 1.67c.955 0 1.845 .467 2.39 1.247l.105 .16l8.114 13.548a2.914 2.914 0 0 1 -2.307 4.363l-.195 .008h-16.225a2.914 2.914 0 0 1 -2.582 -4.2l.099 -.185l8.11 -13.538a2.914 2.914 0 0 1 2.491 -1.403zm.01 13.33l-.127 .007a1 1 0 0 0 0 1.986l.117 .007l.127 -.007a1 1 0 0 0 0 -1.986l-.117 -.007zm-.01 -7a1 1 0 0 0 -.993 .883l-.007 .117v4l.007 .117a1 1 0 0 0 1.986 0l.007 -.117v-4l-.007 -.117a1 1 0 0 0 -.993 -.883z" /></svg></span>
				<span class="efpic-selection-alert__explanation"><@= appstate.attributes.selection_restriction.selection_info @></span>
			</div>
			<@
				}
			}
		@>
	</div>
	<@ if ( appstate.attributes.poststatus != 'approved' && appstate.attributes.poststatus != 'expired' ) { @>
	<div class="efpic-collection-actions">
		<span class="efpic-save"><span><?php _e( 'saved', 'efpic-pro' ); ?></span><svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg></span>
		<a class="efpic-button primary efpic-pre-send" href="#send"><?php /* translators: Content inside the <span> is hidden on smaller screens */ echo apply_filters( 'efpic_send_selection_button_text', __( 'Send<span> selection</span>…', 'efpic-pro' ) ); ?></a>
	</div>
	<@ } @>
</script>