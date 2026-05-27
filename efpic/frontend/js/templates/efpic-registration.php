<script id="efpic-registration" type="text/template">
	<div class="efpic-modal-inner efpic-modal-inner--narrow">
		<div class="efpic-registration-before">
			<h1><?php _e( 'Before you start…', 'efpic' ); ?></h1>
			<p><?php
				$email_required = get_option( 'efpic_registration_email_required' );
				if ( $email_required == 'on' ) {
					$registration_intro = __( 'Please enter your name and email address. You can start selecting images right away; we will also email you a personal link for later.', 'efpic' );
				}
				else {
					$registration_intro = __( 'Please enter your name to start selecting images. If you add an email, we will also send you a personal link for later.', 'efpic' );
				}
				echo apply_filters( 'efpic_register_intro', $registration_intro );
			?></p>
			<form class="efpic-registration-form" method="post">
				<p class="col-100"><label for="name"><?php _e( 'Name', 'efpic' ); ?></label> <input type="text" name="efpic-registration-form['name']" id="name" /></p>
				<p class="col-100"><label for="email"><?php _e( 'Email', 'efpic' ); if ( $email_required != 'on' ) { echo ' (' . __( 'optional', 'efpic' ) . ')'; } ?></label> <input type="email" name="efpic-registration-form['email']" id="email" /></p>
				<p class="col-100 align-center"><button class="efpic-button primary efpic-register"><?php _e( 'Continue', 'efpic' ); ?></button></p>
			</form>
			<a class="efpic-close-modal" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span><?php _e( 'close', 'efpic' ); ?></span></a>
		</div>
		<div class="efpic-registration-after" style="display: none;">
		<?php
			$output = '<h1>' . __( 'Thanks!', 'efpic' ) . '</h1>';
			$output .= '<p>' . __( 'Please check your email inbox for your personal link to access the collection later.', 'efpic' ) . '</p>';
			$output .= '<p>' . __( 'You can safely close this window now.', 'efpic' ) . '</p>';
			echo apply_filters( 'efpic_registration_confirmation', $output );
			?>
		</div>
	</div><!-- .efpic-modal-inner -->
</script>