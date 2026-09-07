<?php
/**
 * Read-only client permissions summary for sent/approved/expired collections.
 *
 * Collection Options UI is not rendered after send — this keeps visibility in the sidebar.
 *
 * @since 1.0.39
 */
defined( 'EFPIC_PRO' ) || exit;

/**
 * Statuses that hide Collection Options but still need a permissions overview.
 *
 * @return string[]
 */
function efpic_pro_client_permissions_summary_statuses() {
	return array( 'sent', 'approved', 'expired', 'delivered', 'delivery-draft' );
}

/**
 * Whether the current collection should show the summary.
 *
 * @param WP_Post|null $post Collection post.
 * @return bool
 */
function efpic_pro_should_show_client_permissions_summary( $post = null ) {
	if ( ! $post || empty( $post->ID ) || 'efpic_collection' !== $post->post_type ) {
		return false;
	}
	return in_array( $post->post_status, efpic_pro_client_permissions_summary_statuses(), true );
}

/**
 * Human-readable download permission line.
 *
 * @param int $post_id Collection ID.
 * @return string
 */
function efpic_pro_client_permissions_download_label( $post_id ) {
	$dl = get_post_meta( (int) $post_id, '_efpic_collection_download_images', true );
	if ( empty( $dl ) || empty( $dl['option'] ) ) {
		return __( 'Off', 'efpic-pro' );
	}
	if ( 'zip' === $dl['option'] ) {
		return __( 'On — ZIP (all images + selected)', 'efpic-pro' );
	}
	if ( 'url' === $dl['option'] ) {
		$url = ! empty( $dl['url'] ) ? $dl['url'] : '';
		if ( $url ) {
			return sprintf(
				/* translators: %s = external download URL */
				__( 'On — external URL (%s)', 'efpic-pro' ),
				$url
			);
		}
		return __( 'On — external URL', 'efpic-pro' );
	}
	return __( 'On', 'efpic-pro' );
}

/**
 * Human-readable selection goal line.
 *
 * @param int $post_id Collection ID.
 * @return string
 */
function efpic_pro_client_permissions_selection_label( $post_id ) {
	$options = get_post_meta( (int) $post_id, '_efpic_collection_selection_options', true );
	if ( empty( $options['selection_option'] ) ) {
		return __( 'Off', 'efpic-pro' );
	}

	$restriction = isset( $options['restriction'] ) ? $options['restriction'] : '';
	$from        = isset( $options['from'] ) ? (int) $options['from'] : 0;
	$to          = isset( $options['to'] ) ? (int) $options['to'] : 0;

	switch ( $restriction ) {
		case 'exactly':
			return sprintf(
				/* translators: %d = image count */
				__( 'Exactly %d image(s)', 'efpic-pro' ),
				$from
			);
		case 'at least':
			return sprintf(
				/* translators: %d = image count */
				__( 'At least %d image(s)', 'efpic-pro' ),
				$from
			);
		case 'a maximum of':
			return sprintf(
				/* translators: %d = image count */
				__( 'Maximum %d image(s)', 'efpic-pro' ),
				$from
			);
		case 'in the range of':
			return sprintf(
				/* translators: 1: min, 2: max */
				__( 'Range %1$d–%2$d image(s)', 'efpic-pro' ),
				$from,
				$to
			);
		case 'in price':
			$cost = isset( $options['extra_image_cost'] ) ? $options['extra_image_cost'] : '';
			return sprintf(
				/* translators: 1: included count, 2: extra cost */
				__( 'In Price — %1$d included, extra %2$s', 'efpic-pro' ),
				$from,
				$cost
			);
		default:
			return __( 'On', 'efpic-pro' );
	}
}

/**
 * On/Off label helper.
 *
 * @param bool $on Whether enabled.
 * @return string
 */
function efpic_pro_client_permissions_on_off( $on ) {
	return $on ? __( 'On', 'efpic-pro' ) : __( 'Off', 'efpic-pro' );
}

/**
 * Build permission rows for a collection.
 *
 * @param int $post_id Collection ID.
 * @return array<int,array{label:string,value:string}>
 */
function efpic_pro_get_client_permissions_rows( $post_id ) {
	$post_id = (int) $post_id;
	$rows    = array();

	$rows[] = array(
		'label' => __( 'Image download', 'efpic-pro' ),
		'value' => efpic_pro_client_permissions_download_label( $post_id ),
	);

	$rows[] = array(
		'label' => __( 'Selection goal', 'efpic-pro' ),
		'value' => efpic_pro_client_permissions_selection_label( $post_id ),
	);

	$comments = ( 1 === (int) get_post_meta( $post_id, '_efpic_collection_mark_comment', true ) );
	$rows[]   = array(
		'label' => __( 'Comments & markers', 'efpic-pro' ),
		'value' => efpic_pro_client_permissions_on_off( $comments ),
	);

	$watermark = get_post_meta( $post_id, '_efpic_apply_watermark', true );
	if ( '' === $watermark || false === $watermark ) {
		$default   = get_option( 'efpic_watermark', array() );
		$watermark = ( ! empty( $default['watermark_by_default'] ) && 'on' === $default['watermark_by_default'] ) ? 'on' : 'off';
	}
	$rows[] = array(
		'label' => __( 'Watermark', 'efpic-pro' ),
		'value' => efpic_pro_client_permissions_on_off( 'on' === $watermark ),
	);

	if ( function_exists( 'efpic_social_links_enabled_for_collection' ) && ! empty( efpic_get_social_link_urls() ) ) {
		$rows[] = array(
			'label' => __( 'Social links', 'efpic-pro' ),
			'value' => efpic_pro_client_permissions_on_off( efpic_social_links_enabled_for_collection( $post_id ) ),
		);
	}

	/**
	 * Filter client permission summary rows.
	 *
	 * @param array $rows    Rows with label/value.
	 * @param int   $post_id Collection ID.
	 */
	return apply_filters( 'efpic_pro_client_permissions_rows', $rows, $post_id );
}

/**
 * Render summary in collection status sidebar.
 *
 * @param WP_Post $post Collection post.
 */
function efpic_pro_client_permissions_summary_post_option( $post ) {
	if ( ! efpic_pro_should_show_client_permissions_summary( $post ) ) {
		return;
	}

	$rows = efpic_pro_get_client_permissions_rows( $post->ID );
	if ( empty( $rows ) ) {
		return;
	}

	$hint = __( 'To change these options, use Edit (reopens the collection as a draft).', 'efpic-pro' );
	?>
	<div class="efpic-option-item efpic-client-permissions" title="<?php echo esc_attr( $hint ); ?>">
		<span class="efpic-client-permissions__title"><?php esc_html_e( 'Client permissions', 'efpic-pro' ); ?></span>
		<table class="efpic-client-permissions__table">
			<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
					<td><?php echo esc_html( $row['value'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
add_action( 'efpic_collection_post_options', 'efpic_pro_client_permissions_summary_post_option', 5 );

/**
 * Skip option saves that would wipe meta when Collection Options are not on the form.
 *
 * @param int $post_id Collection ID.
 * @return bool True = abort save handler (keep existing meta).
 */
function efpic_pro_should_skip_collection_option_save( $post_id ) {
	// Draft / send form posts this marker with Collection Options.
	if ( isset( $_POST['efpic_collection_options_form'] ) && '1' === (string) wp_unslash( $_POST['efpic_collection_options_form'] ) ) {
		return false;
	}
	// Sent/approved Save (and similar) posts gallery nonce without options — keep meta.
	return true;
}
