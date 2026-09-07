<?php
/**
 * EFPIC Pro admin overview page.
 *
 * @since 1.0.0
 * @since 1.0.38 Overview of active Pro features (license UI removed).
 */
defined( 'ABSPATH' ) || exit;

/**
 * Register EFPIC Pro submenu (replaces legacy add-ons page).
 */
function efpic_pro_register_admin_menu() {
	remove_submenu_page( 'efpic', 'efpic-add-ons' );

	add_submenu_page(
		'efpic',
		'EFPIC Pro',
		'EFPIC Pro',
		'manage_options',
		defined( 'EFPIC_PRO_LICENSE_PAGE' ) ? EFPIC_PRO_LICENSE_PAGE : 'efpic-pro',
		'efpic_pro_load_subpage'
	);
}
add_action( 'admin_menu', 'efpic_pro_register_admin_menu', 11 );

/**
 * Pro is always treated as licensed when the plugin is active.
 *
 * @return string
 */
function efpic_pro_get_license_status() {
	return 'valid';
}

/**
 * @return object
 */
function efpic_pro_get_license_data() {
	return (object) array( 'license' => 'valid' );
}

/**
 * Remove stored license data and external update checks.
 */
function efpic_pro_remove_licensing_data() {
	delete_option( 'efpic_pro_license_key' );
	delete_option( 'efpic_addon_licenses' );
	delete_transient( 'efpic_pro_license_status' );
}
add_action( 'init', 'efpic_pro_remove_licensing_data', 1 );

/**
 * Feature cards for the Pro overview.
 *
 * @return array<int,array<string,string>>
 */
function efpic_pro_overview_features() {
	$settings = admin_url( 'admin.php?page=efpic-settings' );
	$design   = admin_url( 'admin.php?page=efpic-design-appearance' );
	$security = admin_url( 'admin.php?page=efpic-security' );
	$general  = admin_url( 'admin.php?page=efpic-general' );
	$email    = admin_url( 'admin.php?page=efpic-email' );
	$collections = admin_url( 'edit.php?post_type=efpic_collection' );

	return array(
		array(
			'title' => __( 'Brand customize', 'efpic-pro' ),
			'desc'  => __( 'Logo, colors, fonts and site title in client galleries.', 'efpic-pro' ),
			'url'   => $design,
			'link'  => __( 'Open Design settings', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Watermark protection', 'efpic-pro' ),
			'desc'  => __( 'Apply watermarks to collection images by default or per collection.', 'efpic-pro' ),
			'url'   => $security,
			'link'  => __( 'Open Security settings', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Prevent direct image access', 'efpic-pro' ),
			'desc'  => __( 'Block direct file URLs so images load only inside the gallery.', 'efpic-pro' ),
			'url'   => $security,
			'link'  => __( 'Open Security settings', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Collection expiration', 'efpic-pro' ),
			'desc'  => __( 'Automatic expiry dates for collections after they are sent.', 'efpic-pro' ),
			'url'   => $general,
			'link'  => __( 'Open General settings', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Email message templates', 'efpic-pro' ),
			'desc'  => __( 'Reusable email templates when sending collections to clients.', 'efpic-pro' ),
			'url'   => $email,
			'link'  => __( 'Open Email settings', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Client access', 'efpic-pro' ),
			'desc'  => __( 'Magic login link so clients can open their galleries by email.', 'efpic-pro' ),
			'url'   => admin_url( 'post-new.php?post_type=page' ),
			'link'  => __( 'Add Client Access page', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Comments & markers', 'efpic-pro' ),
			'desc'  => __( 'Clients can mark and comment on images in a collection.', 'efpic-pro' ),
			'url'   => $collections,
			'link'  => __( 'Open collections', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Selection goals', 'efpic-pro' ),
			'desc'  => __( 'Set how many images a client should select.', 'efpic-pro' ),
			'url'   => $collections,
			'link'  => __( 'Open collections', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Image download', 'efpic-pro' ),
			'desc'  => __( 'Allow clients to download images from a collection.', 'efpic-pro' ),
			'url'   => $collections,
			'link'  => __( 'Open collections', 'efpic-pro' ),
		),
		array(
			'title' => __( 'FTP / folder import', 'efpic-pro' ),
			'desc'  => __( 'Import images from the uploads/efpic/import folder into a collection.', 'efpic-pro' ),
			'url'   => $collections,
			'link'  => __( 'Open collections', 'efpic-pro' ),
		),
		array(
			'title' => __( 'Final delivery', 'efpic-pro' ),
			'desc'  => __( 'Deliver finished images to the client after selection is done.', 'efpic-pro' ),
			'url'   => $collections,
			'link'  => __( 'Open collections', 'efpic-pro' ),
		),
		array(
			'title' => __( 'All EFPIC settings', 'efpic-pro' ),
			'desc'  => __( 'Core and Pro options in one place.', 'efpic-pro' ),
			'url'   => $settings,
			'link'  => __( 'Open Settings', 'efpic-pro' ),
		),
	);
}

/**
 * Render the EFPIC Pro overview page.
 */
function efpic_pro_load_subpage() {
	$core_version = defined( 'EFPIC_VERSION' ) ? EFPIC_VERSION : '—';
	$pro_version  = defined( 'EFPIC_PRO' ) ? EFPIC_PRO : '—';
	$features     = efpic_pro_overview_features();
	?>
	<div class="efpic-pro__head-wrapper">
		<h1><?php esc_html_e( 'EFPIC Pro', 'efpic-pro' ); ?></h1>
	</div>
	<div class="wrap efpic-pro-overview">
		<div class="efpic-pro-overview__status">
			<p class="efpic-pro-overview__badge">
				<span class="efpic-pro-overview__dot" aria-hidden="true"></span>
				<?php esc_html_e( 'EFPIC Pro is active', 'efpic-pro' ); ?>
			</p>
			<p class="efpic-pro-overview__versions">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: Pro version, 2: core version */
						__( 'Pro %1$s · Core %2$s', 'efpic-pro' ),
						$pro_version,
						$core_version
					)
				);
				?>
			</p>
			<p class="efpic-pro-overview__intro">
				<?php esc_html_e( 'Professional features for client galleries are enabled. Configure them below or in a collection.', 'efpic-pro' ); ?>
			</p>
		</div>

		<ul class="efpic-pro-overview__grid">
			<?php foreach ( $features as $feature ) : ?>
				<li class="efpic-pro-overview__card">
					<h2 class="efpic-pro-overview__card-title"><?php echo esc_html( $feature['title'] ); ?></h2>
					<p class="efpic-pro-overview__card-desc"><?php echo esc_html( $feature['desc'] ); ?></p>
					<p class="efpic-pro-overview__card-action">
						<a class="button" href="<?php echo esc_url( $feature['url'] ); ?>"><?php echo esc_html( $feature['link'] ); ?></a>
					</p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}
