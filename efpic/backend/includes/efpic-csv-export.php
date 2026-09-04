<?php
/**
 * Export collections list as CSV (Tools/Debug).
 *
 * @since 1.0.21
 */
defined( 'ABSPATH' ) || exit;

/**
 * Handle CSV download.
 */
function efpic_csv_export_handle() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Forbidden', 'efpic' ) );
	}
	check_admin_referer( 'efpic_export_collections_csv' );

	$q = new WP_Query( array(
		'post_type'      => 'efpic_collection',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=efpic-collections-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	// UTF-8 BOM for Excel
	fwrite( $out, "\xEF\xBB\xBF" );

	fputcsv( $out, array(
		'ID',
		'Title',
		'Status',
		'Date',
		'Slug',
		'Clients',
		'Emails',
		'Images',
		'URL',
	) );

	foreach ( $q->posts as $post ) {
		$hashes = get_post_meta( $post->ID, '_efpic_collection_hashes', true );
		$names  = array();
		$emails = array();
		if ( is_array( $hashes ) ) {
			foreach ( $hashes as $client ) {
				if ( ! empty( $client['name'] ) ) {
					$names[] = $client['name'];
				}
				if ( ! empty( $client['email'] ) ) {
					$emails[] = $client['email'];
				}
			}
		}

		$gallery_ids = get_post_meta( $post->ID, '_efpic_collection_gallery_ids', true );
		$image_count = 0;
		if ( is_string( $gallery_ids ) && '' !== $gallery_ids ) {
			$image_count = count( array_filter( explode( ',', $gallery_ids ) ) );
		}

		$row = array(
			$post->ID,
			efpic_csv_safe_cell( get_the_title( $post ) ),
			$post->post_status,
			$post->post_date,
			$post->post_name,
			efpic_csv_safe_cell( implode( '; ', $names ) ),
			efpic_csv_safe_cell( implode( '; ', $emails ) ),
			$image_count,
			get_permalink( $post ),
		);
		fputcsv( $out, $row );
	}

	fclose( $out );
	exit;
}
add_action( 'admin_post_efpic_export_collections_csv', 'efpic_csv_export_handle' );

/**
 * Mitigate CSV formula injection.
 *
 * @param string $value Cell value.
 * @return string
 */
function efpic_csv_safe_cell( $value ) {
	$value = (string) $value;
	if ( $value !== '' && in_array( $value[0], array( '=', '+', '-', '@' ), true ) ) {
		$value = "'" . $value;
	}
	return $value;
}

/**
 * Tools/Debug UI for CSV export.
 *
 * @return string
 */
function efpic_settings_csv_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return '';
	}
	ob_start();
	?>
	<fieldset class="efpic_settings__settings-item">
		<h2><?php esc_html_e( 'Export collections (CSV)', 'efpic' ); ?></h2>
		<p><?php esc_html_e( 'Download a CSV list of all efpic collections (title, status, clients, image count, URL).', 'efpic' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'efpic_export_collections_csv' ); ?>
			<input type="hidden" name="action" value="efpic_export_collections_csv" />
			<button type="submit" class="button"><?php esc_html_e( 'Download CSV', 'efpic' ); ?></button>
		</form>
	</fieldset>
	<?php
	return ob_get_clean();
}
