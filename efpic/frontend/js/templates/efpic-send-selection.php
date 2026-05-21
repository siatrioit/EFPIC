<script id="efpic-send-selection" type="text/template">
	<div class="efpic-modal-inner">
		<h1><?php echo apply_filters( 'efpic_approval_heading', __( 'Approve Collection', 'efpic' ) ); ?>: <@= title @></h1>
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
		</div>
		<div class="efpic-approval-form">
			<p class="col-100"><label for="efpic_approval_message"><?php _e( 'Anything else you want us to know?', 'efpic' ); ?></label><textarea name="efpic-approval-form[efpic_approval_message]" id="efpic_approval_message" placeholder="<?php __( 'Leave a comment…', 'efpic' ); ?>"></textarea></p>
		</div>
		<?php
			$efpic_approval_warning = '<p><strong>' . __( 'You are about to approve this collection.', 'efpic' ) . '</strong><br />' . __( 'Please note, that you won\'t be able to make changes to your selection after that.', 'efpic' ) . '</p>';
			echo apply_filters( 'efpic_approval_warning', $efpic_approval_warning );
		?>
		<a id="efpic-send-button" class="efpic-button primary" href="#send"><?php echo apply_filters( 'efpic_approval_button_text', _x( 'approve selection', 'send selection button text', 'efpic' ) ); ?></a>
		<?php
			do_action( 'efpic_after_approval_warning' );
		?>
		<a class="efpic-close-modal" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span><?php _e( 'close', 'efpic' ); ?></span></a>
	</div><!-- .efpic-modal-inner -->
</script>