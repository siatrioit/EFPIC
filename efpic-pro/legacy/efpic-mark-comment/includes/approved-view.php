<?php
/**
 * Efpic Mark & Comment Approved View
 *
 * Add a custom column to the
 * Also load our custom colleciton edit screen and all its fields
 *
 * @since mark-comment (0.5.0)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Add "Comments" column to the approved view table.
 *
 * @since mark-comment (0.5.0)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 *
 * @param array $columns The columns, key = column name, value = column title
 * @param int $collection_id The collection post ID
 * @return array The filtered columns
 */
function efpic_mc_add_overview_table_columns( $columns ) {
	if ( efpic_pro_collection_uses_mark_comment() ) {
		$columns['comments'] = __( 'Comments', 'efpic-pro' );
	}

	return $columns;
}

add_filter( 'efpic_selection_overview_table_columns', 'efpic_mc_add_overview_table_columns' );


/**
 * Add content to "Comments" column.
 *
 * @since mark-comment (0.5.0)
 *
 * @see efpic/backend/includes/efpic-edit-collection.php
 *
 * @param string $content The column content
 * @param int $collection_id The collection post ID
 * @param int $image_id The image attachment ID
 * @param int $image_number The image number
 * @return string The filtered content
 */
function efpic_mc_add_overview_table_column_content( $content, $collection_id, $image_id, $image_number ) {

	// Multi-client collection
	$hashes = get_post_meta( $collection_id, '_efpic_collection_hashes', true );
	if ( ! empty( $hashes ) AND count( $hashes ) > 0 ) {
		ob_start();
		foreach( $hashes as $hash => $value ) {

			// Get comments
			$meta_data = get_post_meta( $collection_id, '_efpic_collection_selection_' . $hash, true );

			// If there are comments (markers) go ahead
			if ( isset( $meta_data['markers']['id_' . $image_id] ) ) {

				// Iterate through the comments
				foreach( $meta_data['markers']['id_' . $image_id] as $marker ) {
					echo '<div class="efpic-comment">';
					echo '<div class="efpic-comment__inner">' . $marker['comment'];
					if ( ! empty( $marker['x'] ) AND ! empty( $marker['y'] ) ) {
						echo '<a class="efpic-comment-marker" href="' . esc_url( add_query_arg( 'ident', $hash, get_the_permalink( $collection_id ) ) ) . '#' . $image_number . '" target="_blank"></a>';
					}
					echo '</div>';
					echo '<div class="efpic-comment__meta">';
					echo '<span class="efpic-comment__author">' . efpic_combine_name_email( $value['name'], $value['email'] ) . '</span>';
					if ( ! empty( $marker['time'] ) ) {
						echo '<span class="efpic-comment__time">' . wp_date( get_option( 'date_format' ), $marker['time'] ) . ', ' . wp_date( get_option( 'time_format' ), $marker['time'] ) . '</span>';
					}
					echo '</div>';
					echo '</div>';
				}
			}
		}
		return ob_get_clean();
	}
	// Single collection
	else {
		// Get comments
		$meta_data = get_post_meta( $collection_id, '_efpic_collection_selection', true );

		// If there are comments (markers) go ahead
		if ( isset( $meta_data['markers']['id_' . $image_id] ) ) {
			ob_start();

			// Iterate through the comments
			foreach( $meta_data['markers']['id_' . $image_id] as $marker ) {

				echo '<div class="efpic-comment">';
				echo '<div class="efpic-comment__inner">' . $marker['comment'];
				if ( ! empty( $marker['x'] ) AND ! empty( $marker['y'] ) ) {
					echo '<a class="efpic-comment-marker" href="' . get_the_permalink( $collection_id ) . '#' . $image_number . '" target="_blank"></a>';
				}
				echo '</div></div>';
			}

			return ob_get_clean();
		}
	}
}

add_filter( 'efpic_selection_overview_table_column_comments_content', 'efpic_mc_add_overview_table_column_content', 10, 4 );