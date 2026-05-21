<?php
/**
 * Custom Post Type "Collections"
 *
 * @since 0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Register Custom Post Type 'efpic_collection'
 *
 * @since 0.1.0
 */
function efpic_register_cpt_collection() {

	$labels = array(
		'name' => __( 'Collections', 'efpic' ),
		'singular_name' => __( 'Collection', 'efpic' ),
		'add_new' => __( 'New Collection', 'efpic' ),
		'add_new_item' => __( 'New Collection', 'efpic' ),
		'edit_item' => __( 'Edit Collection', 'efpic' ),
		'new_item' => __( 'New Collection', 'efpic' ),
		'view_item' => __( 'View Collection', 'efpic' ),
		'search_items' => __( 'Search Collections', 'efpic' ),
		'not_found' => __( 'No Collection Found', 'efpic' ),
		'not_found_in_trash' => __( 'No Collection Found in Trash', 'efpic' ),
		'parent_item_colon' => __( 'Parent Collection', 'efpic' ),
		'menu_name' => __( 'All Collections', 'efpic' ),
		'filter_items_list' => __( 'Filter collections list', 'efpic' ),
		'items_list_navigation' => __( 'Collections list navigation', 'efpic' ),
		'items_list' => __( 'Collections list', 'efpic' )
	);

	// Load slug from options
	$efpic_collection_slug = get_option( 'efpic_collection_slug' );

	// Fallback on default
	if ( empty( $efpic_collection_slug ) ) {
		/* translators: Base slug (part of the URL) for our custom post type. Cannot contain any special characters! */
		$efpic_collection_slug = _x( 'collections', 'efpic collections slug', 'efpic' );
	}

	// Filter the slug
	$efpic_collection_slug = apply_filters( 'efpic_collection_slug', $efpic_collection_slug );

	// Make sure the slug is sanitized
	$efpic_collection_slug = sanitize_title( $efpic_collection_slug );

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'supports' => array( 'title' ),
		'public' => true,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'show_in_menu'  => 'efpic',
		'menu_position' => 1,
		'publicly_queryable' => true,
		'exclude_from_search' => true,
		'has_archive' => false,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => array(
			'slug' => $efpic_collection_slug,
			'with_front' => false
		),
		'capabilities' => array(
			'edit_post'          => efpic_capability(),
			'read_post'          => efpic_capability(),
			'delete_post'        => efpic_capability(),
			'edit_posts'         => efpic_capability(),
			'edit_others_posts'  => efpic_capability(),
			'delete_posts'       => efpic_capability(),
			'publish_posts'      => efpic_capability(),
			'read_private_posts' => efpic_capability()
		),
	);

	$args = apply_filters( 'efpic_cpt_collection_args', $args );

	register_post_type( 'efpic_collection', $args );

}

add_action( 'init', 'efpic_register_cpt_collection' );


/**
 * Add collection slug setting & handle update.
 *
 * @since 1.9.0
 */
function efpic_collection_slug_settings() {
	/* translators: Slug base label */
	add_settings_field( 'efpic_collection_slug', __( 'efpic Collection base', 'efpic' ), 'efpic_collection_slug_output', 'permalink', 'optional' );

	// Update collection slug option
	if ( isset( $_POST['permalink_structure'] ) && ! empty( $_POST['efpic_collection_slug'] ) && current_user_can( efpic_capability() ) ) {
		update_option( 'efpic_collection_slug', sanitize_title( $_POST['efpic_collection_slug'] ) );
	}
}

add_action( 'admin_init', 'efpic_collection_slug_settings' );
 

/**
 * Display collection slug settings.
 *
 * Find it under "Settings > Permalinks > Optional"
 *
 * @since 1.9.0
 */
function efpic_collection_slug_output() {
	// Load slug option
	$collection_slug = get_option( 'efpic_collection_slug' );

	// Fallback to the default
	if ( empty( $collection_slug ) ) {
		/* translators: Base slug (part of the URL) for our custom post type. Cannot contain any special characters! */
		$collection_slug = _x( 'collections', 'efpic collections slug', 'efpic' );
	}

	// Is the slug filtered?
	$filtered_slug = apply_filters( 'efpic_collection_slug', '' );
	$disabled = '';
	if ( ! empty( $filtered_slug ) ) {
		$collection_slug = $filtered_slug; 
		$disabled = ' disabled="disabled"';
	}
?>
	<input name="efpic_collection_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $collection_slug ); ?>" placeholder="<?php echo _x( 'collections', 'efpic collections slug', 'efpic' ); ?>"<?php echo $disabled; ?> />
<?php
	/* translators: Opening and closing link tags */
	if ( ! empty( $filtered_slug ) ) { echo '<span class="efpic-collection-slug__setting-hint">'.  sprintf( __( 'The <code>efpic_collection_slug</code> filter is currently overwriting this setting. %sLearn more…%s', 'efpic' ), '<a href="https://efpic.io/docs/developers/#define-custom-slug">', '</a>' ) . '</span>'; } ?>
<?php
}


/**
 * Register custom post statuses
 *
 * @since 0.1.0
 */
function efpic_collection_post_status() {

	register_post_status( 'sent', array(
		'label' => _x( 'Open', 'post status name', 'efpic' ),
		'public' => true,
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Open <span class="count">(%s)</span>', 'Open <span class="count">(%s)</span>', 'efpic' ),
	) );

	register_post_status( 'approved', array(
		'label' => _x( 'Closed', 'post status name', 'efpic' ),
		'public' => true,
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'efpic' ),
	) );

	register_post_status( 'expired', array(
		'label' => _x( 'Expired', 'post status name', 'efpic' ),
		'public' => true,
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'efpic' ),
	) );
}

add_action( 'init', 'efpic_collection_post_status' );


/**
 * Assign previous status when restoring a collection from trash
 *
 * @since 2.2.0
 *
 * @param string The default untrashed status
 * @param int The post ID
 * @param string The previous status
 * @return string The status the untrashed post should have
 */
function efpic_untrash_post_status( $status, $post_id, $previous_status ) {
	if ( get_post_type( $post_id ) == 'efpic_collection' ) {
		return $previous_status;
	}
	return $status;
}

add_filter( 'wp_untrash_post_status', 'efpic_untrash_post_status', 10, 3 );


/**
 * Add a unique post slug to new collections
 *
 * @since 0.1.0
 */
function efpic_add_unique_post_slug( $data, $postarr ) {

	// Check options, do we generate random slugs or not?
	$efpic_collection_do_random_slug = true;

	if ( get_option( 'efpic_random_slugs' ) != 'on' ) {
		$efpic_collection_do_random_slug = false;
	}

	// Posibility to override with filter
	$efpic_collection_do_random_slug = apply_filters( 'efpic_collection_do_random_slug', $efpic_collection_do_random_slug );

	// We only want our hashed slugs for post-type "collection"
	if ( $data['post_type'] == 'efpic_collection' AND empty( $data['post_name'] ) AND $efpic_collection_do_random_slug === true ) {
		$data['post_name'] = substr( md5( rand() ), 0, 5 );
	}

	return $data;
}

add_filter( 'wp_insert_post_data', 'efpic_add_unique_post_slug', 99, 2 );


/**
 * Get our custom link to a collection, even before it is saved for the first time
 *
 * @return string with the full url to a collection
 */
function get_draft_permalink( $post_id ) {

	// Make sure function get_sample_permalink() is loaded
	require_once(ABSPATH . 'wp-admin/includes/post.php');
	
	// Load sample permalink and post-name into separate variables
	list( $permalink, $postname ) = get_sample_permalink( $post_id );

	// Check if post-name has not been set yet
	if ( empty( $postname ) ) {
		// Generate the post-name that will be used later on
		$postname = substr( md5( $post_id ), 0, 5 );
	}

	// Replace the placeholder in our URL
	return str_replace( '%pagename%', $postname, $permalink );

}


/**
 * Add the collection status to the collection list view.
 *
 * @since 0.3.2
 * @since 1.7.5 Add custom modified date column, remove regular date column.
 * @since 2.3.0 Change column order, add new columns `image_num` and `client_num`.
 *
 * @param array $columns The columns.
 * @return array The filtered columns.
 */
function efpic_collection_add_status_admin_column( $columns ) {
	// Delete the default title column
	unset( $columns['title'] );

	// In order to set date as the last column we need to unset it first
	unset( $columns['date'] );

	// Add our custom columns to the default $columns
	$columns['efpic_status_title'] = _x( 'Title', 'column header', 'efpic' );
	$columns['efpic_actions'] = _x( '<span class="screen-reader-text">Actions</span>', 'column header', 'efpic' );
	$columns['efpic_image_num'] = _x( 'Images', 'column header', 'efpic' );
	$columns['efpic_clients'] = _x( 'Clients', 'column header', 'efpic' );
	$columns['efpic_expiration'] = _x( 'Expiration', 'column header', 'efpic' );
	$columns['efpic_collection_modified'] = _x( 'Last Modified', 'column header', 'efpic' );

	// Return all columns including our custom column
	return $columns;
}

add_filter( 'manage_efpic_collection_posts_columns', 'efpic_collection_add_status_admin_column', 10 );


/**
 * Add content to our admin column.
 *
 * @since 0.3.2
 * @since 1.7.5 Add custom modified date column
 *
 * @param string $column The column name.
 * @param int $post_id The collection post ID.
 */
function efpic_column_collection_status( $column, $post_id ) {
	$post_status = get_post_status( $post_id );

	if ( $column == 'efpic_status_title' ) {
		// Status URL, which will be linked to from the column, do not link expired directly
		$status_url = add_query_arg( 'post_status', ( $post_status == 'expired' ) ? 'approved' : $post_status );

		$post_state = '';
		if ( $post_status == 'sent' || $post_status == 'publish' ) {
			$post_state = 'open';
		}
		elseif ( $post_status == 'approved' || $post_status == 'expired' ) {
			$post_state = 'closed';
		}
		elseif ( $post_status == 'draft' ) {
			$post_state = 'draft';
		}
		elseif ( $post_status == 'delivered' ) {
			$post_state = 'delivered';
		}
		elseif ( $post_status == 'delivery-draft' ) {
			$post_state = 'delivery-draft';
		}

		// Define the status messages and classes for our column
		switch ( $post_state ) {
			case 'delivered':
				$title_description = __( 'This collection has been delivered to the client.', 'efpic' );
				$status_class = 'efpic-admin-status-delivered';
				break;
			case 'delivery-draft':
				$title_description = __( 'This collection is a delivery draft.', 'efpic' );
				$status_class = 'efpic-admin-status-delivery-draft';
				break;
			case 'open':
				$title_description = __( 'This collection is open.', 'efpic' );
				$status_class = 'efpic-admin-status-open';
				break;
			case 'closed':
				$title_description = __( 'This collection is closed.', 'efpic' );
				$status_class = 'efpic-admin-status-closed';
				break;
			case 'draft':
				$title_description = __( 'This collection is a draft, which means it cannot be publicly accessed', 'efpic' );
				$status_class = 'efpic-admin-status-draft';
				break;
			default:
				$title_description = __( 'This collection is in the trash. You can either restore or permanently delete it.', 'efpic' );
				$status_class = 'efpic-admin-status-closed';
		}

		// Construct the final output
		$title = get_the_title( $post_id );
		if ( empty( $title ) ) {
			$title = __( '(no title)', 'efpic' );
		}
		echo '<span class="efpic-admin-status__wrap"><span class="efpic-admin-status ' . $status_class . '" title="' . $title_description . '">' . $title_description . '</span>' . '<strong><a class="row-title" href="' . get_edit_post_link( $post_id ) . '">' . $title . '</a>' . _post_states( get_post( $post_id ), false ) . '</strong></span>';

		// Make quick edit work
		get_inline_data( get_post( $post_id ) );
	}

	if ( $column == 'efpic_actions' ) {
		if ( efpic_get_selection_count( $post_id ) > 0 ) {
			// Get proof file type
			$proof_file_type = pathinfo( apply_filters( 'efpic_proof_file_name', 'file.txt', $post_id ), PATHINFO_EXTENSION );
			if ( ! empty( $proof_file_name ) ) {
				$proof_file_type = '.' . $proof_file_type;
			}
			?>
			<a class="button efpic-download-button" role="button" tabindex="0" href="<?php echo admin_url( 'post.php?post=' . $post_id . '&action=edit&efpic-download=efpic-proof-file' ); ?>"><span class="efpic-download-button__dl"><?php _e( 'Download', 'efpic' ); ?></span> <?php _e( 'Proof', 'efpic' ); ?> (.<?php echo $proof_file_type; ?>)</a>
		<?php }
	}

	if ( $column == 'efpic_image_num' ) {
		echo efpic_get_collection_image_num( $post_id );
	}

	if ( $column == 'efpic_clients' ) {
		$collection_hashes = get_post_meta( $post_id, '_efpic_collection_hashes', true );
		if ( empty( $collection_hashes ) ) {
			return;
		}

		// Sort by time
		uasort( $collection_hashes, fn( $a, $b ) => $a['time'] <=> $b['time'] );
		// Sort by status
		uasort( $collection_hashes, fn( $a, $b ) => $a['status'] <=> $b['status'] );

		echo '<div class="collection-clients">';
		foreach ( $collection_hashes as $hash => $client ) {
			$status = $client['status'];
			$avatar = efpic_get_client_initials( efpic_combine_name_email( $client['name'], $client['email'] ) );
			echo '<a href="' . efpic_get_collection_link( $hash, $post_id ) . '" class="collection-client collection-client_status-' . $status . '" title="' . efpic_combine_name_email( $client['name'], $client['email'] ) . '"><span class="collection-client__profile-initials">' . $avatar . '</span></a>';
		}
		echo '</div>';
	}

	if ( $column == 'efpic_expiration' ) {
		$post_status = get_post_status( $post_id );
		if ( in_array( $post_status, [ 'sent', 'expired' ] ) ) {
			$expiration = get_post_meta( $post_id, '_efpic_collection_expiration_time', true );
			if ( ! empty( $expiration ) ) {
				if ( $expiration > time() ) {
					$classes = '';
					if ( strtotime( '+1 day', time() ) > $expiration ) {
						$classes = 'expires-soon';
					}
					echo '<span class="' . $classes . '" title="' . wp_date( __( 'Y/m/d g:i:s a', 'efpic' ), $expiration ) . '">' . /* translators: %s = a time span in the future, eg. in 7 days */ sprintf( __( 'in %s' ), human_time_diff( time(), $expiration ) ) . '</span>';
				}
				else {
					echo '<span title="' . wp_date( __( 'Y/m/d g:i:s a', 'efpic' ), $expiration ) . '">' . /* translators: %s = a time span in the past, eg. 1 hour ago */ sprintf( __( '%s ago' ), human_time_diff( time(), $expiration ) ) . '</span>';
				}
			}
		}
	}

	if ( $column == 'efpic_collection_modified' ) {
		$post_modified_gmt = get_post_field( 'post_modified_gmt', $post_id );

		// In some cases (draft, auto-draft etc.) WP saves a post_modified but not post_modified_gmt to the DB, in those cases we take this as a fallback
		if ( empty( $post_modified_gmt ) || $post_modified_gmt == '0000-00-00 00:00:00' ) {
			$date = get_gmt_from_date( get_post_field( 'post_modified' ) );
		} else {
			$date = $post_modified_gmt;
		}

		$date = strtotime( $date ); // Use the gmt time (UTC)
		echo efpic_collection_event_prettify( efpic_get_last_history_event( $post_id, $date ) ) . '<br />';
		/* translators: The modified time format; see https://www.php.net/manual/datetime.format.php; also check the translation consistency tool, to find the common time format for your language: https://translate.wordpress.org/consistency/?search=Y%2Fm%2Fd+g%3Ai%3As+a&set=de%2Fdefault&project=1&search_case_sensitive=1. */
		$post_modified = wp_date( __( 'Y/m/d g:i:s a', 'efpic' ), $date );

		if ( ! $post_modified ) {
			$post_modified = '';
		}
		echo $post_modified;
	}
}

add_action( 'manage_efpic_collection_posts_custom_column' , 'efpic_column_collection_status', 10, 2 );


/**
 * Remove "Proteced" prefix from password protected collection title
 *
 * @since 0.9.0
 *
 * @param string Text displayed before the post title; default 'Protected: %s'
 * @param object The collection post object
 * @return string The filtered title text
 */
function efpic_remove_protected_prefix( $title_text, $post ) {
	if ( 'efpic_collection' == $post->post_type ) {
		return '%s';
	}
	else {
		return $title_text;
	}
}

add_filter( 'protected_title_format', 'efpic_remove_protected_prefix', 10, 2 );


/**
 * Remove efpic collections from sitemaps
 * 
 * @since 1.5.0
 *
 * @param array $post_types Array of registered post type objects keyed by their name
 */
add_filter( 'wp_sitemaps_post_types', function( $post_types ) {
	unset( $post_types['efpic_collection'] );
	return $post_types;
});


/**
 * Register sortable column
 *
 * @since 1.7.5
 *
 * @param array $columns An array of sortable columns
 * @return array The filtered columns array
 */
function efpic_collection_modified_column_register_sortable( $columns ) {
	$columns['efpic_collection_modified'] = 'collection_modified';
	return $columns;
}

add_filter( 'manage_edit-efpic_collection_sortable_columns', 'efpic_collection_modified_column_register_sortable' );


/**
 * Register sortable column.
 *
 * @since 2.0.0
 *
 * @param array $columns An array of sortable columns
 * @return array The filtered columns array
 */
function efpic_expiration_column_register_sortable( $columns ) {
	$columns['efpic_expiration'] = 'expiration';
	return $columns;
}

add_filter( 'manage_edit-efpic_collection_sortable_columns', 'efpic_expiration_column_register_sortable' );


/**
 * Use custom sort order for collections
 * 
 * @since 1.7.5
 */
function efpic_collection_admin_order( $wp_query ) {
	if ( is_admin() && $wp_query->is_main_query() && ! empty( $wp_query->query['post_type'] ) ) {

		if ( $wp_query->query['post_type'] == 'efpic_collection' && ! empty( $wp_query->get( 'orderby' ) && $wp_query->get( 'orderby' ) == 'expiration' ) ) {
			$wp_query->set( 'meta_query', [
				'expiration' => [
					'key' => '_efpic_collection_expiration_time',
					'type' => 'NUMERIC',
					'compare' => 'EXISTS',
				],
			]);
			$wp_query->set( 'orderby', 'meta_value_num' );
			$wp_query->set( 'post_status', [ 'sent', 'expired' ] );
		}

		if ( $wp_query->query['post_type'] == 'efpic_collection' && empty( $wp_query->get( 'orderby' ) ) ) {
			$wp_query->set( 'orderby', 'post_modified' );
			$wp_query->set('order', 'DESC');
		}
	}
}

add_filter( 'pre_get_posts', 'efpic_collection_admin_order' );


/**
 * Adjust row actions
 *
 * @param array $actions An array of row action links
 * @param object $post The post object
 * @return array The filtered actions
 * @since 1.8.0
 */
function efpic_collection_row_actions( $actions, $post ) {
	// Make sure this only runs for efpic collections
	if ( $post->post_type != 'efpic_collection' ) {
		return $actions;
	}

	// Change the edit link text to something more meaningful, depending on collection status
	if ( in_array( $post->post_status, [ 'sent', 'approved', 'expired' ] ) ) {
		$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'View Proof Results', 'efpic' ) . '</a>';
	}
	elseif ( $post->post_status == 'delivered' ) {
		$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'View Download History', 'efpic' ) . '</a>';
	}
	
	return $actions;
}

add_filter( 'post_row_actions', 'efpic_collection_row_actions', 10, 2 );


/**
 * Remove expired link from subsubsub menu.
 * 
 * @since 2.3.0
 * 
 * @param array $links The subsubsub menu links.
 * @return array The filtered links.
 */
function efpic_customize_subsubsub_menu( $links ) {
	// Remove expired post status
	unset( $links['expired'] );

	// Update "Closed" found posts number
	global $wp_query;

	$query = array(  
		'post_type'   => 'efpic_collection',  
		'post_status' => [ 'approved', 'expired' ],  
	);  
	$result = new WP_Query($query);

	unset( $links['approved'] );
	if ( $result->found_posts > 0 ) {
		$current = ( $wp_query->query_vars['post_status'] == [ 'approved', 'expired' ] ) ? ' class="current" aria-current="page"' : '';

		$links['approved'] = '<a href="' . admin_url( 'edit.php?post_status=approved&post_type=efpic_collection' ) . '" ' . $current . '>' . __( 'Closed', 'efpic' ) . ' <span class="count">(' . $result->found_posts . ')</span></a>';
	}

	return $links;
}

add_filter( 'views_edit-efpic_collection', 'efpic_customize_subsubsub_menu' );


/**
 * Adjust query when using custom filters.
 *
 * @since 2.3.0
 *
 * @param object $query The WP Query.
 */
function efpic_custom_filter_query( $query ) {
	// Open filter
	if ( is_admin() && ! empty( $_GET['post_status'] ) && $_GET['post_status'] == 'open' ) {
		$query->set( 'post_status', [ 'sent', 'publish' ] );
	}

	// Closed filter
	if ( is_admin() && ! empty( $_GET['post_status'] ) && $_GET['post_status'] == 'closed' ) {
		$query->set( 'post_status', [ 'approved', 'expired' ] );
	}

	// Include expired status when filtering for approved status
	if ( is_admin() && ! empty( $_GET['post_status'] ) && $_GET['post_status'] == 'approved' ) {
		$query->set( 'post_status', [ 'approved', 'expired' ] );
	}
}

add_action( 'parse_query', 'efpic_custom_filter_query' );


/**
 * Add "Closed" to post states.
 *
 * @since 2.3.0
 *
 * @param array $post_states The post states.
 * @param object $post The collection post object.
 * @return array The filtered post states.
 */
function efpic_filter_post_states( $post_states, $post ) {
	$post_status = get_post_status( $post->ID );

	if ( in_array( $post_status, [ 'approved', 'expired' ] ) ) {
		$post_states = [ 'closed' => __( 'Closed', 'efpic' ) ];
	}

	return $post_states;
}

return add_filter( 'display_post_states', 'efpic_filter_post_states', 10, 2 );


/**
 * Run upgrades after plugin update
 *
 * @since 2.3.0
 */
function efpic_collections_upgrade() {
	// Get all collections that have no hashes but existing collections
	$args = [
		'post_type' => 'efpic_collection',
		'post_status' => [ 'any' ],
		'posts_per_page' => -1,
		'meta_query' => [
			'relation' => 'AND',
			'no_hashes' => [
				'key' => '_efpic_collection_hashes',
				'compare' => 'NOT EXISTS'
			],
			'has_selection' => [
				'key' => '_efpic_collection_selection',
				'compare' => 'EXISTS'
			],
		],
	];

	$collections = get_posts( $args );

	// Iterate through collections and make the necessary changes
	foreach( $collections as $collection ) {
		// Check if there is an email address, if so, use it, to create the ident
		$email = get_post_meta( $collection->ID, '_efpic_collection_email_address', true );
		$name = efpic_get_default_client_name();
		if ( ! empty( $email ) ) {
			$name = '';
		}

		$args = [];
		// Use the correct status for the client
		switch( $collection->post_status ) {
			case 'delivered':
				$args['status'] = 'approved';
				break;
			case 'delivery-draft':
				$args['status'] = 'approved';
				break;
			case 'publish':
				$args['status'] = 'sent';
				break;
			case 'expired':
				$args['status'] = 'failed';
				break;
			default:
				$args['status'] = $collection->post_status;
		}

		// Get last modified time!
		$args['time'] = strtotime( $collection->post_modified );

		// Create ident
		$ident = efpic_add_client_to_hashes( $collection->ID, $name, $email, $args );

		// Copy markers & comments and  and the final approval message (or fields) for that ident
		$selection = get_post_meta( $collection->ID, '_efpic_collection_selection', true );
		if ( ! empty( $selection ) ) {
			add_post_meta( $collection->ID, '_efpic_collection_selection_' . $ident, $selection, true );
		}
	}

	// Update settings version
	update_option( 'efpic_settings_version', '2.3.0' );
}