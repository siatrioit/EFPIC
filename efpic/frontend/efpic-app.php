<?php
/**
 * @since 0.3.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// No caching or minification for efpic collections
if ( ! defined( 'DONOTCACHEPAGE' ) ) {
	define( 'DONOTCACHEPAGE', true );
}
if ( ! defined( 'DONOTCACHEDB' ) ) {
	define( 'DONOTCACHEDB', true );
}
if ( ! defined( 'DONOTMINIFY' ) ) {
	define( 'DONOTMINIFY', true );
}
if ( ! defined( 'DONOTCDN' ) ) {
	define( 'DONOTCDN', true );
}
if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
	define( 'DONOTCACHEOBJECT', true );
}

efpic_collection_bouncer();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="robots" content="noindex, nofollow" />
	<?php
		if ( has_site_icon() ) {
			wp_site_icon();
		}
	?>
		<title><?php if ( post_password_required( $post ) AND $post->post_author != get_current_user_id() ) {
			_e( 'This collection is password protected.', 'efpic' );
			} else { the_title(); } ?></title>
		<?php

			efpic_load_styles();

			$custom_styles = '';
			$custom_styles = apply_filters( 'efpic_custom_styles', $custom_styles );

			if ( !empty( $custom_styles) ) {
				echo '<style>' . $custom_styles . '</style>';
			}

		?>
	</head>
	<body<?php efpic_body_classes(); ?>>
		<?php if ( post_password_required( $post) AND $post->post_author != get_current_user_id() ) { ?>
			<div class="efpic-protected">
				<div class="efpic-protected-inner">
				<?php
					$efpic_password_box_content = '<h1>' . __( 'This collection is password protected.', 'efpic' ) . '</h1>';
					$efpic_password_box_content = apply_filters( 'efpic_password_box_content', $efpic_password_box_content, $post->ID );
					echo $efpic_password_box_content;

					if ( isset( $_COOKIE['wp-postpass_' . COOKIEHASH] ) and $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password ) { ?>
						<p class="error-msg"><?php _e( 'Wrong password.', 'efpic' ); ?></p>
				<?php } ?>
				<?php echo get_the_password_form(); ?>
				</div>
			</div><!-- .efpic-protected -->
			<?php
		}
		else {
		?>

		<?php
			if ( file_exists( EFPIC_PATH . '/frontend/images/icons.svg' ) ) {
				include_once( EFPIC_PATH . '/frontend/images/icons.svg' );
			}
		?>
		<?php
			// Display efpic admin bar
			if ( is_user_logged_in() AND current_user_can( efpic_capability() ) AND apply_filters( 'efpic_show_admin_bar', true ) ) {
				if ( $post->post_status == 'delivered' ) { ?>
					<div class="efpic-admin-bar delivered">
						<div class="efpic-admin-bar-status"><?php _e( 'Delivered', 'efpic' ); ?></div>
						<div class="efpic-admin-bar-message">
						<?php echo sprintf( __( 'This collection has been delivered to the client on <span class="date">%s</span>.', 'efpic' ), wp_date( get_option( 'date_format' ), efpic_get_collection_history_event_time( $post->ID, 'delivered' ) ) ); ?></div>
						<div class="efpic-admin-bar-actions"><?php edit_post_link( __( 'View Download Stats', 'efpic' ) ); ?></div>
					</div>
				<?php } elseif ( $post->post_status == 'delivery-draft' ) { ?>
					<div class="efpic-admin-bar delivery-draft">
						<div class="efpic-admin-bar-status"><?php _e( 'Delivery Draft', 'efpic' ); ?></div>
						<div class="efpic-admin-bar-message"><?php _e( 'This collection has not been sent to the client.', 'efpic' ); ?></div>
						<div class="efpic-admin-bar-actions"><?php edit_post_link( __( 'Edit', 'efpic' ) ); ?></div>
					</div>
				<?php } elseif ( $post->post_status == 'sent' ) { ?>
					<div class="efpic-admin-bar sent">
						<div class="efpic-admin-bar-status"><?php _e( 'Waiting for approval', 'efpic' ); ?></div>
						<div class="efpic-admin-bar-message">
						<?php echo sprintf( __( 'This collection has been sent to the client on <span class="date">%s</span>.', 'efpic' ), wp_date( get_option( 'date_format' ), efpic_get_collection_history_event_time( $post->ID, 'sent' ) ) ); ?></div>
						<div class="efpic-admin-bar-actions"><?php edit_post_link( __( 'Edit', 'efpic' ) ); ?></div>	
					</div>
				<?php } elseif ( $post->post_status == 'approved' ) { ?>
					<div class="efpic-admin-bar approved">
						<div class="efpic-admin-bar-status"><?php _e( 'Approved', 'efpic' ); ?></div>
						<div class="efpic-admin-bar-message"><?php echo sprintf( __( 'The selection has been sent by the client on %s.', 'efpic' ), wp_date( get_option( 'date_format' ), efpic_get_collection_history_event_time( $post->ID, 'approved' ) ) ); ?></div>
						<div class="efpic-admin-bar-actions"><?php edit_post_link( __( 'View Selection', 'efpic' ) ); ?></div>
					</div>
				<?php } else { ?>
					<div class="efpic-admin-bar draft">
						<div class="efpic-admin-bar-status"><?php _e( 'Draft', 'efpic' ); ?></div>
						<div class="efpic-admin-bar-message"><?php _e( 'This collection has not been sent to the client.', 'efpic' ); ?></div>
						<div class="efpic-admin-bar-actions"><?php edit_post_link( __( 'Edit', 'efpic' ) ); ?></div>	
					</div>
				<?php }
			}
		?>
		<header class="efpic-header">
			<?php
				ob_start();
			?>
			<div class="efpic-header-inner">
				<div class="blog-name"><?php echo get_bloginfo( 'name' ); ?></div>
				<div class="efpic-collection-title">
					<?php echo get_the_title( $post->ID ); ?>
					<?php
						if ( is_user_logged_in() ) {
							edit_post_link( __( 'Edit', 'efpic' ), '<span class="edit-button">', '</span>', $post->ID );
						}
					?>
				</div>
			</div>
			<div class="efpic-header-actions">
			<?php
				$efpic_header = apply_filters( 'efpic_header', ob_get_clean(), $post->ID );
				echo $efpic_header;
			?>
			</div>
		</header>

		<?php do_action( 'efpic_before_collection_images' ); ?>

		<div class="efpic-collection"></div>

		<?php
			// Load backbone templates
			$templates = efpic_load_backbone_templates();

			foreach ( $templates as $template => $template_path ) {
				include_once( $template_path );
				echo "\n\n\t\t";
			}
		?>

		<script>
			var efpic = efpic || {};
		</script>

		<script id="efpic-jquery-js" src='<?php echo EFPIC_URL; ?>frontend/js/_vendor/jquery.min.js'></script>
		<script id="efpic-jquery-visible-js" src='<?php echo EFPIC_URL; ?>frontend/js/_vendor/jquery.visible.js'></script>
		<script id="efpic-underscore-js" src='<?php echo EFPIC_URL; ?>frontend/js/_vendor/underscore.min.js'></script>
		<script id="efpic-backbone-js" src='<?php echo EFPIC_URL; ?>frontend/js/_vendor/backbone.min.js'></script>
		<script id="efpic-dateformat-js" src='<?php echo EFPIC_URL; ?>frontend/js/_vendor/dateformat.min.js'></script>

		<script>
			_.templateSettings = {
				evaluate: /<[%@]([\s\S]+?)[%@]>/g,
				interpolate: /<[%@]=([\s\S]+?)[%@]>/g,
				escape: /<[%@]-([\s\S]+?)[%@]>/g
			};

			// Load collection data and app state
			var data = '<?php echo efpic_escape_json_for_inline_js( efpic_get_images() ); ?>';
			var appstate = '<?php echo efpic_escape_json_for_inline_js( efpic_get_app_state() ); ?>';
		</script>

		<?php
			// Load collections, models & views
			$cmv = efpic_load_cmv();

			foreach ( $cmv as $file_name => $file_path ) {
				echo '<script src=' . $file_path . '></script>' . "\n\t\t";
			}
		?>

		<script src='<?php echo EFPIC_URL; ?>frontend/js/router.js'></script>

		<script src='<?php echo EFPIC_URL; ?>frontend/js/efpic-app.js'></script>
		<script src='<?php echo EFPIC_URL; ?>frontend/js/efpic-ui-helpers.js'></script>

		<script>

			// Booting up...
			$(function() { efpic.boot( $( '.efpic-collection' ), data, appstate ); });
		</script>

		<?php
			$custom_scripts = '';
			$custom_scripts = apply_filters( 'efpic_custom_scripts', $custom_scripts );
			echo $custom_scripts;
		?>

		<?php } // post_password_required() ?>

		<?php
			if ( get_option( 'efpic_efpic_love' ) == 'on' ) { ?>
				<a class="efpic-brand" href="https://efpic.io/">powered by efpic</a>
		<?php } ?>
	</body>
</html>