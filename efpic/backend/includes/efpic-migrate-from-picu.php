<?php
/**
 * One-shot PICU → EFPIC data migration.
 *
 * Deactivate PICU first (do not uninstall). Activate EFPIC (+ Pro), then run
 * from efpic → Settings → Tools/Debug.
 *
 * @since 1.0.20
 */
defined( 'ABSPATH' ) || exit;

/**
 * Whether the original PICU plugin appears active.
 *
 * @return bool
 */
function efpic_migrate_picu_plugin_active() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$candidates = array(
		'picu/picu.php',
		'picu-pro/picu-pro.php',
		'picu-brand-customize/picu-brand-customize.php',
		'picu-delivery/picu-delivery.php',
		'picu-download/picu-download.php',
		'picu-import/picu-import.php',
		'picu-mark-comment/picu-mark-comment.php',
		'picu-selection-options/picu-selection-options.php',
		'picu-theft-protection/picu-theft-protection.php',
	);

	foreach ( $candidates as $plugin ) {
		if ( is_plugin_active( $plugin ) ) {
			return true;
		}
	}

	// Any active plugin whose folder starts with "picu".
	$active = (array) get_option( 'active_plugins', array() );
	foreach ( $active as $plugin ) {
		if ( 0 === strpos( $plugin, 'picu' ) ) {
			return true;
		}
	}

	// Fallback: PICU constants / functions still loaded.
	if ( defined( 'PICU_VERSION' ) || function_exists( 'picu_setup' ) ) {
		return true;
	}

	return false;
}

/**
 * Upload basedir helper.
 *
 * @return string
 */
function efpic_migrate_uploads_basedir() {
	$upload_dir = wp_upload_dir();
	return untrailingslashit( $upload_dir['basedir'] );
}

/**
 * Known string replacements inside serialized / plain values.
 * Longer tokens first.
 *
 * @return array<string,string>
 */
function efpic_migrate_value_replacements() {
	return array(
		'picu-bc-external-font'   => 'efpic-external-font',
		'picu-external-font'      => 'efpic-external-font',
		'picu-send-email'         => 'efpic-send-email',
		'picu-copy-link'          => 'efpic-copy-link',
		'picu_approval_message'   => 'efpic_approval_message',
		'[picu_list_collections]' => '[efpic_list_collections]',
		'picu-thumbnail'          => 'efpic-thumbnail',
		'picu-small'              => 'efpic-small',
		'picu-medium'             => 'efpic-medium',
		'picu-large'              => 'efpic-large',
		'/uploads/picu/'          => '/uploads/efpic/',
		'/picu/collections/'      => '/efpic/collections/',
		'/picu/import/'           => '/efpic/import/',
		'picu/collections/'       => 'efpic/collections/',
		'picu/import/'            => 'efpic/import/',
	);
}

/**
 * Recursively replace PICU tokens in a value.
 *
 * @param mixed $value Value to rewrite.
 * @return mixed
 */
function efpic_migrate_rewrite_value( $value ) {
	if ( is_array( $value ) ) {
		$out = array();
		foreach ( $value as $key => $item ) {
			$new_key = is_string( $key ) ? efpic_migrate_rewrite_string( $key ) : $key;
			$out[ $new_key ] = efpic_migrate_rewrite_value( $item );
		}
		return $out;
	}

	if ( is_object( $value ) ) {
		foreach ( get_object_vars( $value ) as $key => $item ) {
			$value->$key = efpic_migrate_rewrite_value( $item );
		}
		return $value;
	}

	if ( is_string( $value ) ) {
		return efpic_migrate_rewrite_string( $value );
	}

	return $value;
}

/**
 * Replace PICU tokens in a plain string.
 *
 * @param string $value Input.
 * @return string
 */
function efpic_migrate_rewrite_string( $value ) {
	foreach ( efpic_migrate_value_replacements() as $from => $to ) {
		if ( false !== strpos( $value, $from ) ) {
			$value = str_replace( $from, $to, $value );
		}
	}
	return $value;
}

/**
 * Scan current site for PICU leftovers.
 *
 * @return array<string,mixed>
 */
function efpic_migrate_from_picu_scan() {
	global $wpdb;

	$basedir = efpic_migrate_uploads_basedir();

	$collection_count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
			'picu_collection'
		)
	);

	$meta_count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '%picu%'"
	);

	$option_count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%picu%'"
	);

	$usermeta_count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE '%picu%'"
	);

	$attachment_path_count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->postmeta}
		WHERE meta_key = '_wp_attached_file' AND meta_value LIKE 'picu/%'"
	);

	$shortcode_count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts}
		WHERE post_content LIKE '%[picu_list_collections]%'"
	);

	$picu_dir  = $basedir . '/picu';
	$efpic_dir = $basedir . '/efpic';

	return array(
		'picu_plugin_active'     => efpic_migrate_picu_plugin_active(),
		'collections'            => $collection_count,
		'postmeta_keys'          => $meta_count,
		'options'                => $option_count,
		'usermeta'               => $usermeta_count,
		'attachment_paths'       => $attachment_path_count,
		'shortcodes'             => $shortcode_count,
		'picu_upload_dir_exists' => is_dir( $picu_dir ),
		'efpic_upload_dir_exists'=> is_dir( $efpic_dir ),
		'picu_upload_dir'        => $picu_dir,
		'efpic_upload_dir'       => $efpic_dir,
		'already_migrated'       => (bool) get_option( 'efpic_migrated_from_picu' ),
		'migration_log'          => get_option( 'efpic_migrated_from_picu_log', array() ),
		'needs_migration'        => (
			$collection_count > 0
			|| $meta_count > 0
			|| $option_count > 0
			|| $usermeta_count > 0
			|| $attachment_path_count > 0
			|| $shortcode_count > 0
			|| is_dir( $picu_dir )
		),
	);
}

/**
 * Rename option/meta keys that contain "picu".
 *
 * @param string $table Full table name.
 * @param string $column Column name.
 * @param bool   $dry_run Preview only.
 * @return int Rows that would be / were updated.
 */
function efpic_migrate_rename_keys_in_table( $table, $column, $dry_run ) {
	global $wpdb;

	$count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$table} WHERE {$column} LIKE '%picu%'"
	);

	if ( $dry_run || $count < 1 ) {
		return $count;
	}

	// Options: option_name is unique — rename row-by-row to avoid collisions.
	if ( $table === $wpdb->options && 'option_name' === $column ) {
		$rows = $wpdb->get_results(
			"SELECT option_id, option_name FROM {$wpdb->options} WHERE option_name LIKE '%picu%'"
		);
		$updated = 0;
		foreach ( $rows as $row ) {
			$new_name = str_replace( 'picu', 'efpic', $row->option_name );
			if ( $new_name === $row->option_name ) {
				continue;
			}
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT option_id FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
					$new_name
				)
			);
			if ( $exists ) {
				// Keep existing EFPIC option; drop leftover PICU duplicate.
				$wpdb->delete( $wpdb->options, array( 'option_id' => $row->option_id ), array( '%d' ) );
			} else {
				$wpdb->update(
					$wpdb->options,
					array( 'option_name' => $new_name ),
					array( 'option_id' => $row->option_id ),
					array( '%s' ),
					array( '%d' )
				);
			}
			$updated++;
		}
		return $updated;
	}

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table/column are internal constants.
	$wpdb->query(
		"UPDATE {$table}
		SET {$column} = REPLACE({$column}, 'picu', 'efpic')
		WHERE {$column} LIKE '%picu%'"
	);

	return $count;
}

/**
 * Rewrite postmeta values that still contain PICU tokens.
 *
 * @param bool $dry_run Preview only.
 * @return int Number of rows touched.
 */
function efpic_migrate_rewrite_postmeta_values( $dry_run ) {
	global $wpdb;

	$rows = $wpdb->get_results(
		"SELECT meta_id, meta_value FROM {$wpdb->postmeta}
		WHERE meta_value LIKE '%picu%'"
	);

	$changed = 0;
	foreach ( $rows as $row ) {
		$raw        = $row->meta_value;
		$unserialized = maybe_unserialize( $raw );
		$rewritten  = efpic_migrate_rewrite_value( $unserialized );
		$new_raw    = ( $unserialized !== $raw || is_array( $unserialized ) || is_object( $unserialized ) )
			? maybe_serialize( $rewritten )
			: efpic_migrate_rewrite_string( (string) $raw );

		if ( $new_raw === $raw ) {
			continue;
		}

		$changed++;
		if ( ! $dry_run ) {
			$wpdb->update(
				$wpdb->postmeta,
				array( 'meta_value' => $new_raw ),
				array( 'meta_id' => $row->meta_id ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	return $changed;
}

/**
 * Rewrite option values that still contain PICU tokens.
 *
 * @param bool $dry_run Preview only.
 * @return int Number of options touched.
 */
function efpic_migrate_rewrite_option_values( $dry_run ) {
	global $wpdb;

	$rows = $wpdb->get_results(
		"SELECT option_id, option_value FROM {$wpdb->options}
		WHERE option_value LIKE '%picu%'"
	);

	$changed = 0;
	foreach ( $rows as $row ) {
		$raw          = $row->option_value;
		$unserialized = maybe_unserialize( $raw );
		$rewritten    = efpic_migrate_rewrite_value( $unserialized );
		$new_raw      = ( $unserialized !== $raw || is_array( $unserialized ) || is_object( $unserialized ) )
			? maybe_serialize( $rewritten )
			: efpic_migrate_rewrite_string( (string) $raw );

		if ( $new_raw === $raw ) {
			continue;
		}

		$changed++;
		if ( ! $dry_run ) {
			$wpdb->update(
				$wpdb->options,
				array( 'option_value' => $new_raw ),
				array( 'option_id' => $row->option_id ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	return $changed;
}

/**
 * Move / merge uploads/picu → uploads/efpic.
 *
 * @param bool $dry_run Preview only.
 * @return array{status:string,message:string}
 */
function efpic_migrate_move_upload_dir( $dry_run ) {
	$basedir = efpic_migrate_uploads_basedir();
	$from    = $basedir . '/picu';
	$to      = $basedir . '/efpic';

	if ( ! is_dir( $from ) ) {
		return array(
			'status'  => 'skip',
			'message' => 'No uploads/picu directory.',
		);
	}

	if ( $dry_run ) {
		if ( is_dir( $to ) ) {
			return array(
				'status'  => 'merge',
				'message' => 'Would merge uploads/picu into existing uploads/efpic.',
			);
		}
		return array(
			'status'  => 'rename',
			'message' => 'Would rename uploads/picu → uploads/efpic.',
		);
	}

	if ( ! is_dir( $to ) ) {
		if ( @rename( $from, $to ) ) {
			return array(
				'status'  => 'renamed',
				'message' => 'Renamed uploads/picu → uploads/efpic.',
			);
		}
		return array(
			'status'  => 'error',
			'message' => 'Failed to rename uploads/picu → uploads/efpic. Check permissions.',
		);
	}

	// Merge: move children into existing efpic dir.
	$moved = efpic_migrate_merge_directories( $from, $to );
	if ( is_wp_error( $moved ) ) {
		return array(
			'status'  => 'error',
			'message' => $moved->get_error_message(),
		);
	}

	// Remove empty picu tree when possible.
	efpic_migrate_rrmdir_if_empty( $from );

	return array(
		'status'  => 'merged',
		'message' => 'Merged uploads/picu into uploads/efpic (' . (int) $moved . ' items).',
	);
}

/**
 * Recursively merge $from into $to.
 *
 * @param string $from Source.
 * @param string $to   Destination.
 * @return int|WP_Error Items moved.
 */
function efpic_migrate_merge_directories( $from, $to ) {
	if ( ! is_dir( $from ) ) {
		return new WP_Error( 'efpic_migrate', 'Source directory missing.' );
	}
	if ( ! wp_mkdir_p( $to ) ) {
		return new WP_Error( 'efpic_migrate', 'Could not create destination directory.' );
	}

	$moved = 0;
	$items = scandir( $from );
	if ( false === $items ) {
		return new WP_Error( 'efpic_migrate', 'Could not read source directory.' );
	}

	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) {
			continue;
		}
		$src = $from . '/' . $item;
		$dst = $to . '/' . $item;

		if ( is_dir( $src ) ) {
			$sub = efpic_migrate_merge_directories( $src, $dst );
			if ( is_wp_error( $sub ) ) {
				return $sub;
			}
			$moved += $sub;
			@rmdir( $src );
			continue;
		}

		if ( file_exists( $dst ) ) {
			// Keep existing efpic file; drop duplicate from picu.
			@unlink( $src );
			continue;
		}

		if ( ! @rename( $src, $dst ) ) {
			if ( ! @copy( $src, $dst ) ) {
				return new WP_Error( 'efpic_migrate', 'Failed to move file: ' . $item );
			}
			@unlink( $src );
		}
		$moved++;
	}

	return $moved;
}

/**
 * Remove directory if empty (best effort).
 *
 * @param string $dir Path.
 */
function efpic_migrate_rrmdir_if_empty( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$items = array_diff( scandir( $dir ), array( '.', '..' ) );
	foreach ( $items as $item ) {
		$path = $dir . '/' . $item;
		if ( is_dir( $path ) ) {
			efpic_migrate_rrmdir_if_empty( $path );
		}
	}
	$items = array_diff( scandir( $dir ), array( '.', '..' ) );
	if ( empty( $items ) ) {
		@rmdir( $dir );
	}
}

/**
 * Update attachment paths / GUIDs / size names.
 *
 * @param bool $dry_run Preview only.
 * @return array<string,int>
 */
function efpic_migrate_update_attachments( $dry_run ) {
	global $wpdb;

	$attached = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->postmeta}
		WHERE meta_key = '_wp_attached_file' AND meta_value LIKE 'picu/%'"
	);

	$guid = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'attachment' AND guid LIKE %s",
			'%/picu/%'
		)
	);

	$meta_sizes = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->postmeta}
		WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE '%picu-%'"
	);

	if ( $dry_run ) {
		return array(
			'attached_file' => $attached,
			'guid'          => $guid,
			'metadata'      => $meta_sizes,
		);
	}

	if ( $attached > 0 ) {
		$wpdb->query(
			"UPDATE {$wpdb->postmeta}
			SET meta_value = REPLACE(meta_value, 'picu/', 'efpic/')
			WHERE meta_key = '_wp_attached_file' AND meta_value LIKE 'picu/%'"
		);
	}

	if ( $guid > 0 ) {
		$wpdb->query(
			"UPDATE {$wpdb->posts}
			SET guid = REPLACE(guid, '/picu/', '/efpic/')
			WHERE post_type = 'attachment' AND guid LIKE '%/picu/%'"
		);
	}

	// Rewrite serialized attachment metadata size keys / file paths.
	$rows = $wpdb->get_results(
		"SELECT meta_id, meta_value FROM {$wpdb->postmeta}
		WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE '%picu%'"
	);
	$meta_updated = 0;
	foreach ( $rows as $row ) {
		$data = maybe_unserialize( $row->meta_value );
		$new  = efpic_migrate_rewrite_value( $data );
		$new_raw = maybe_serialize( $new );
		if ( $new_raw === $row->meta_value ) {
			continue;
		}
		$wpdb->update(
			$wpdb->postmeta,
			array( 'meta_value' => $new_raw ),
			array( 'meta_id' => $row->meta_id ),
			array( '%s' ),
			array( '%d' )
		);
		$meta_updated++;
	}

	return array(
		'attached_file' => $attached,
		'guid'          => $guid,
		'metadata'      => $meta_updated,
	);
}

/**
 * Replace shortcodes in post content.
 *
 * @param bool $dry_run Preview only.
 * @return int
 */
function efpic_migrate_shortcodes( $dry_run ) {
	global $wpdb;

	$count = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts}
		WHERE post_content LIKE '%[picu_list_collections]%'"
	);

	if ( $dry_run || $count < 1 ) {
		return $count;
	}

	$wpdb->query(
		"UPDATE {$wpdb->posts}
		SET post_content = REPLACE(post_content, '[picu_list_collections]', '[efpic_list_collections]')
		WHERE post_content LIKE '%[picu_list_collections]%'"
	);

	return $count;
}

/**
 * Run full migration (or dry-run).
 *
 * @param bool $dry_run If true, only report planned changes.
 * @return array<string,mixed>
 */
function efpic_migrate_from_picu_run( $dry_run = true ) {
	global $wpdb;

	$log = array(
		'dry_run'   => (bool) $dry_run,
		'started'   => current_time( 'mysql' ),
		'steps'     => array(),
		'errors'    => array(),
		'success'   => false,
	);

	if ( efpic_migrate_picu_plugin_active() ) {
		$log['errors'][] = __( 'PICU is still active. Deactivate PICU (do not uninstall), then run migration again.', 'efpic' );
		$log['finished'] = current_time( 'mysql' );
		return $log;
	}

	$scan = efpic_migrate_from_picu_scan();
	if ( ! $scan['needs_migration'] ) {
		$log['steps'][] = array(
			'step'    => 'scan',
			'message' => __( 'Nothing left to migrate from PICU.', 'efpic' ),
			'count'   => 0,
		);
		$log['success']  = true;
		$log['finished'] = current_time( 'mysql' );
		return $log;
	}

	// 1) Post type
	$collections = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
			'picu_collection'
		)
	);
	$log['steps'][] = array(
		'step'    => 'post_type',
		'message' => 'picu_collection → efpic_collection',
		'count'   => $collections,
	);
	if ( ! $dry_run && $collections > 0 ) {
		$wpdb->update(
			$wpdb->posts,
			array( 'post_type' => 'efpic_collection' ),
			array( 'post_type' => 'picu_collection' ),
			array( '%s' ),
			array( '%s' )
		);
	}

	// 2) Meta / option / usermeta keys
	$meta_keys = efpic_migrate_rename_keys_in_table( $wpdb->postmeta, 'meta_key', $dry_run );
	$log['steps'][] = array(
		'step'    => 'postmeta_keys',
		'message' => 'Rename postmeta keys (*picu* → *efpic*)',
		'count'   => $meta_keys,
	);

	$option_keys = efpic_migrate_rename_keys_in_table( $wpdb->options, 'option_name', $dry_run );
	$log['steps'][] = array(
		'step'    => 'option_keys',
		'message' => 'Rename option names (*picu* → *efpic*)',
		'count'   => $option_keys,
	);

	$usermeta_keys = efpic_migrate_rename_keys_in_table( $wpdb->usermeta, 'meta_key', $dry_run );
	$log['steps'][] = array(
		'step'    => 'usermeta_keys',
		'message' => 'Rename usermeta keys (*picu* → *efpic*)',
		'count'   => $usermeta_keys,
	);

	// 3) Values inside meta/options
	$meta_values = efpic_migrate_rewrite_postmeta_values( $dry_run );
	$log['steps'][] = array(
		'step'    => 'postmeta_values',
		'message' => 'Rewrite PICU tokens in postmeta values',
		'count'   => $meta_values,
	);

	$option_values = efpic_migrate_rewrite_option_values( $dry_run );
	$log['steps'][] = array(
		'step'    => 'option_values',
		'message' => 'Rewrite PICU tokens in option values',
		'count'   => $option_values,
	);

	// 4) Filesystem
	$fs = efpic_migrate_move_upload_dir( $dry_run );
	$log['steps'][] = array(
		'step'    => 'uploads',
		'message' => $fs['message'],
		'count'   => ( 'error' === $fs['status'] ) ? 0 : 1,
		'status'  => $fs['status'],
	);
	if ( 'error' === $fs['status'] ) {
		$log['errors'][] = $fs['message'];
	}

	// 5) Attachments
	$attachments = efpic_migrate_update_attachments( $dry_run );
	$log['steps'][] = array(
		'step'    => 'attachments',
		'message' => sprintf(
			'Attachment paths/GUID/metadata (files: %d, guid: %d, meta: %d)',
			$attachments['attached_file'],
			$attachments['guid'],
			$attachments['metadata']
		),
		'count'   => $attachments['attached_file'] + $attachments['guid'] + $attachments['metadata'],
	);

	// 6) Shortcodes
	$shortcodes = efpic_migrate_shortcodes( $dry_run );
	$log['steps'][] = array(
		'step'    => 'shortcodes',
		'message' => '[picu_list_collections] → [efpic_list_collections]',
		'count'   => $shortcodes,
	);

	if ( ! $dry_run ) {
		// 7) Clear old PICU cron hooks
		wp_clear_scheduled_hook( 'picu_collection_checker' );
		wp_clear_scheduled_hook( 'picu_collection_folders' );
		$log['steps'][] = array(
			'step'    => 'cron',
			'message' => 'Cleared PICU cron hooks',
			'count'   => 1,
		);

		// 8) Ensure collection slug option exists (keep "collections")
		if ( false === get_option( 'efpic_collection_slug', false ) ) {
			update_option( 'efpic_collection_slug', 'collections', false );
		}

		// 9) Run settings bag upgrade if old bag options exist
		if ( function_exists( 'efpic_settings_upgrade' ) ) {
			$bag = get_option( 'efpic_settings' );
			if ( ! empty( $bag ) && is_array( $bag ) ) {
				efpic_settings_upgrade();
				$log['steps'][] = array(
					'step'    => 'settings_upgrade',
					'message' => 'Ran efpic_settings_upgrade() for legacy option bags',
					'count'   => 1,
				);
			}
		}

		// 10) Flush rewrites
		flush_rewrite_rules( false );
		$log['steps'][] = array(
			'step'    => 'rewrites',
			'message' => 'Flushed rewrite rules',
			'count'   => 1,
		);

		update_option( 'efpic_migrated_from_picu', current_time( 'mysql' ), false );
		update_option( 'efpic_migrated_from_picu_log', $log, false );
	}

	$log['success']  = empty( $log['errors'] );
	$log['finished'] = current_time( 'mysql' );

	if ( ! $dry_run ) {
		update_option( 'efpic_migrated_from_picu_log', $log, false );
	}

	return $log;
}

/**
 * Admin-post: dry-run.
 */
function efpic_migrate_from_picu_handle_dry_run() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Forbidden', 'efpic' ) );
	}
	check_admin_referer( 'efpic_migrate_from_picu' );

	$result = efpic_migrate_from_picu_run( true );
	set_transient( 'efpic_migrate_from_picu_result', $result, MINUTE_IN_SECONDS * 30 );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'                => 'efpic-tools-debug',
				'efpic_migrate_done'  => 'dry',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_efpic_migrate_from_picu_dry_run', 'efpic_migrate_from_picu_handle_dry_run' );

/**
 * Admin-post: execute.
 */
function efpic_migrate_from_picu_handle_execute() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Forbidden', 'efpic' ) );
	}
	check_admin_referer( 'efpic_migrate_from_picu' );

	@set_time_limit( 0 );
	@ini_set( 'memory_limit', '512M' );

	$result = efpic_migrate_from_picu_run( false );
	set_transient( 'efpic_migrate_from_picu_result', $result, MINUTE_IN_SECONDS * 30 );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'               => 'efpic-tools-debug',
				'efpic_migrate_done' => $result['success'] ? 'ok' : 'error',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_efpic_migrate_from_picu_execute', 'efpic_migrate_from_picu_handle_execute' );

/**
 * Tools/Debug UI block for migration.
 *
 * @return string HTML
 */
function efpic_settings_migrate_from_picu() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return '';
	}

	$scan   = efpic_migrate_from_picu_scan();
	$result = get_transient( 'efpic_migrate_from_picu_result' );
	ob_start();
	?>
	<fieldset class="efpic_settings__settings-item">
		<h2><?php esc_html_e( 'Migrate from PICU', 'efpic' ); ?></h2>
		<p><?php esc_html_e( 'Transfer all PICU collections, settings, and image files to EFPIC on this site. Deactivate PICU first — do not uninstall (uninstall can delete collections).', 'efpic' ); ?></p>

		<?php if ( ! empty( $_GET['efpic_migrate_done'] ) && is_array( $result ) ) : ?>
			<div class="notice notice-<?php echo ! empty( $result['success'] ) ? 'success' : 'error'; ?> inline" style="margin: 12px 0;">
				<?php if ( ! empty( $result['dry_run'] ) ) : ?>
					<p><strong><?php esc_html_e( 'Dry-run complete (no changes written).', 'efpic' ); ?></strong></p>
				<?php elseif ( ! empty( $result['success'] ) ) : ?>
					<p><strong><?php esc_html_e( 'Migration completed successfully.', 'efpic' ); ?></strong></p>
				<?php else : ?>
					<p><strong><?php esc_html_e( 'Migration finished with errors.', 'efpic' ); ?></strong></p>
				<?php endif; ?>

				<?php if ( ! empty( $result['errors'] ) ) : ?>
					<ul>
						<?php foreach ( $result['errors'] as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( ! empty( $result['steps'] ) ) : ?>
					<ul>
						<?php foreach ( $result['steps'] as $step ) : ?>
							<li><?php echo esc_html( $step['message'] . ' — ' . (int) $step['count'] ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<?php delete_transient( 'efpic_migrate_from_picu_result' ); ?>
		<?php endif; ?>

		<table class="widefat striped" style="max-width: 720px; margin: 12px 0;">
			<tbody>
				<tr>
					<td><?php esc_html_e( 'PICU plugin active', 'efpic' ); ?></td>
					<td><?php echo $scan['picu_plugin_active'] ? '<strong style="color:#b32d2e;">' . esc_html__( 'Yes — deactivate first', 'efpic' ) . '</strong>' : esc_html__( 'No', 'efpic' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PICU collections', 'efpic' ); ?></td>
					<td><?php echo (int) $scan['collections']; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Post meta keys with “picu”', 'efpic' ); ?></td>
					<td><?php echo (int) $scan['postmeta_keys']; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Options with “picu”', 'efpic' ); ?></td>
					<td><?php echo (int) $scan['options']; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Attachment paths under picu/', 'efpic' ); ?></td>
					<td><?php echo (int) $scan['attachment_paths']; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'uploads/picu folder', 'efpic' ); ?></td>
					<td><?php echo $scan['picu_upload_dir_exists'] ? esc_html__( 'Exists', 'efpic' ) : esc_html__( 'Not found', 'efpic' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Last migration', 'efpic' ); ?></td>
					<td><?php echo $scan['already_migrated'] ? esc_html( (string) get_option( 'efpic_migrated_from_picu' ) ) : '—'; ?></td>
				</tr>
			</tbody>
		</table>

		<?php if ( $scan['picu_plugin_active'] ) : ?>
			<p><strong><?php esc_html_e( 'Migration is blocked while PICU is active.', 'efpic' ); ?></strong></p>
		<?php elseif ( ! $scan['needs_migration'] ) : ?>
			<p><?php esc_html_e( 'No PICU data found. Nothing to migrate.', 'efpic' ); ?></p>
		<?php else : ?>
			<p style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
					<?php wp_nonce_field( 'efpic_migrate_from_picu' ); ?>
					<input type="hidden" name="action" value="efpic_migrate_from_picu_dry_run" />
					<button type="submit" class="button"><?php esc_html_e( 'Dry-run (preview)', 'efpic' ); ?></button>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Run PICU → EFPIC migration now? Make sure you have a full backup (DB + uploads/picu).', 'efpic' ) ); ?>');">
					<?php wp_nonce_field( 'efpic_migrate_from_picu' ); ?>
					<input type="hidden" name="action" value="efpic_migrate_from_picu_execute" />
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Run migration', 'efpic' ); ?></button>
				</form>
			</p>
		<?php endif; ?>
	</fieldset>
	<?php
	return ob_get_clean();
}
