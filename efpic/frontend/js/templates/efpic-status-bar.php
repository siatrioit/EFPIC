<script id="efpic-status-bar" type="text/template">
	<?php
		$after_info_button = '';
		echo apply_filters( 'efpic_frontend_after_info_button', $after_info_button );
	?>
	<div class="efpic-display-filter">
		<a class="efpic-filter-selected">
			<span class="efpic-filter-icon"><svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg></span>
			<span class="efpic-filter-label"><?php _e( 'Selected', 'efpic' ); ?></span>
		</a>
		<a class="efpic-filter-unselected">
			<span class="efpic-filter-icon"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg></span>
			<span class="efpic-filter-label"><?php _e( 'Unselected', 'efpic' ); ?></span>
		</a>
		<a class="efpic-filter-reset"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span><?php _e( 'Reset filters', 'efpic' ); ?></span></a>
	</div>
	<div class="efpic-selection-count">
		<a class="efpic-info-button" href="#collection-info" title="<?php _e( 'Show Information about this collection', 'efpic' ); ?>"><?php _e( 'Show Information about this collection', 'efpic' ); ?></a>
		<span class="efpic-selected-num"><@= selected @></span> <span class="efpic-total-num">/ <@= all @></span>
	</div>
	<@ if ( appstate.attributes.poststatus != 'approved' && appstate.attributes.poststatus != 'expired' ) { @>
	<div class="efpic-collection-actions">
		<span class="efpic-save"><span><?php _e( 'saved', 'efpic' ); ?></span><svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg></span>
		<a class="efpic-button primary efpic-pre-send" href="#send"><?php /* translators: Content inside the <span> is hidden on smaller screens */ echo apply_filters( 'efpic_send_selection_button_text', __( 'Send<span> selection</span>…', 'efpic' ) ); ?></a>
	</div>
	<@ } @>
</script>