<?php
/**
 * efpic welcome screen
 *
 * @since 0.7.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Redirect to the efpic welcome screen
 *
 * @since 0.5.0
 */
function efpic_welcome_screen_activation_redirect() {

	// Only redirect if transient is set
	if ( ! get_transient( '_efpic_welcome_screen_activation_redirect' ) ) {
		return;
	}

	// Delete the redirect transient
	delete_transient( '_efpic_welcome_screen_activation_redirect' );

	// Don't redirect if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Redirect to bbPress about page
	wp_safe_redirect( add_query_arg( array( 'page' => 'efpic-welcome-screen' ), admin_url( 'index.php' ) ) );

}

add_action( 'admin_init', 'efpic_welcome_screen_activation_redirect' );


/**
 * Add welcome screen as dashboard page
 *
 * @since 0.7.0
 */
function efpic_welcome_screen_page() {

	add_dashboard_page(
		'efpic Welcome Screen',
		'efpic Welcome Screen',
		'read',
		'efpic-welcome-screen',
		'welcome_screen_content'
	);
}

add_action('admin_menu', 'efpic_welcome_screen_page');


/**
 * Display the welcome screen
 *
 * @since 0.7.0
 */
function welcome_screen_content() {
?>
<div class="wrap">
	<div class="efpic-welcome">
		<h2 class="efpic-welcome-subtitle"></h2>
		<img class="efpic-logo" src="<?php echo EFPIC_URL; ?>/frontend/images/efpic-logo-grey.svg" alt="efpic" />
		<div class="row header-row">
			<div class="column col-50">
				<h1><?php _e( 'Greetings, Photographer!', 'efpic' ); ?></h1>
				<p class="thanks"><?php _e( 'Thank you for installing efpic.', 'efpic' ); ?></p>
				<p><?php _e( 'We work very hard to make your experience and that of your clients as smooth as possible and we hope using efpic will transform the way you work and interact with your photo clients.', 'efpic' ); ?></p>
				<p><?php _e( 'If you have feedback of any kind, please get in touch.<br />We\'d love to hear from you.', 'efpic' ); ?></p>
				<p><?php _e( 'To get started, follow the steps below.', 'efpic' ); ?></p>
			</div>
			<div class="column col-50"><img src="<?php echo EFPIC_URL; ?>/backend/images/efpic-browser.jpg" alt="efpic collection" /></div>
		</div>

		<div class="row row-white">
			<div class="column col-33 get-started">
				<h2><?php _e( 'Get started', 'efpic' ); ?></h2>
				<p><?php _e( 'We suggest to take a look at the settings first – no worries, we kept them pretty simple. Start by selecting one of two themes:', 'efpic' ); ?></p>
				<p class="alt"><a class="button" href="<?php echo get_admin_url(); ?>admin.php?page=efpic-settings"><?php _e( 'Select efpic theme now', 'efpic' ); ?></a></p>
				<p><?php _e( 'Already have images prepared that you want to send to a client? Start creating your first collection, no instructions needed.', 'efpic' ); ?></p>
				<p><a class="button button-primary" href="<?php echo get_admin_url(); ?>post-new.php?post_type=efpic_collection"><?php _e( 'Create a collection', 'efpic' ); ?></a></p>
			</div>

			<div class="column col-33">
				<h2><?php _e( 'Need help?', 'efpic' ); ?></h2>
				<ul>
					<li><?php /* translators: %s = opening and closing link tags */ echo sprintf( __( 'Please take a look at the %sdocumentation%s first.', 'efpic' ), '<a href="https://efpic.io/docs/">', '</a>' ); ?></li>
					<li><?php /* translators: %s = opening and closing link tags */ echo sprintf( __( 'If you can\'t find the answer to your question, please use the official WordPress.org %ssupport forum%s.', 'efpic' ), '<a href="https://wordpress.org/support/plugin/efpic">', '</a>' ); ?></li>
					<p><?php /* translators: %s = opening and closing link tags */ echo sprintf( __( 'Pro customers may contact us via our %ssupport page%s.', 'efpic' ), '<a href="https://efpic.io/support/">', '</a>' ); ?></p>
				</ul>
			</div>
			<div class="column col-33">
				<?php efpic_render_ad_slot( 'welcome-sidebar', 'efpic-welcome-custom-slot' ); ?>
				<?php efpic_display_pro_metabox(); ?>
			</div>
		</div>
	</div>
</div>
<?php
}


/**
 * Remove the welcome screen from the menu
 *
 * @since 0.7.0
 */
function welcome_screen_remove_menus() {
	remove_submenu_page( 'index.php', 'efpic-welcome-screen' );
}

add_action( 'admin_head', 'welcome_screen_remove_menus' );