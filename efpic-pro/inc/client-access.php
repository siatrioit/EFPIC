<?php
/**
 * Client Access — magic-link login for collection clients (no WP account).
 *
 * @since 1.0.21
 */
defined( 'EFPIC_PRO' ) || exit;

/**
 * Cookie name for client access session.
 */
define( 'EFPIC_CLIENT_ACCESS_COOKIE', 'efpic_client_access' );

/**
 * Get email from client access session cookie.
 *
 * @return string Sanitized email or empty.
 */
function efpic_client_access_email() {
	if ( empty( $_COOKIE[ EFPIC_CLIENT_ACCESS_COOKIE ] ) ) {
		return '';
	}

	$raw = (string) $_COOKIE[ EFPIC_CLIENT_ACCESS_COOKIE ];
	$parts = explode( '|', $raw, 2 );
	if ( count( $parts ) !== 2 ) {
		return '';
	}

	list( $email, $sig ) = $parts;
	$email = sanitize_email( $email );
	if ( ! is_email( $email ) ) {
		return '';
	}

	$expected = hash_hmac( 'sha256', strtolower( $email ), wp_salt( 'auth' ) );
	if ( ! hash_equals( $expected, $sig ) ) {
		return '';
	}

	return $email;
}

/**
 * Set client access cookie for an email.
 *
 * @param string $email Client email.
 */
function efpic_client_access_set_cookie( $email ) {
	$email = sanitize_email( $email );
	if ( ! is_email( $email ) ) {
		return;
	}
	$sig = hash_hmac( 'sha256', strtolower( $email ), wp_salt( 'auth' ) );
	$value = $email . '|' . $sig;
	$expire = time() + ( 14 * DAY_IN_SECONDS );
	setcookie( EFPIC_CLIENT_ACCESS_COOKIE, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	$_COOKIE[ EFPIC_CLIENT_ACCESS_COOKIE ] = $value;
}

/**
 * Clear client access cookie.
 */
function efpic_client_access_clear_cookie() {
	setcookie( EFPIC_CLIENT_ACCESS_COOKIE, ' ', time() - YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	unset( $_COOKIE[ EFPIC_CLIENT_ACCESS_COOKIE ] );
}

/**
 * Create a one-time magic login token for an email.
 *
 * @param string $email Email.
 * @return string Token.
 */
function efpic_client_access_create_token( $email ) {
	$email = sanitize_email( $email );
	$token = wp_generate_password( 48, false, false );
	set_transient(
		'efpic_client_access_' . hash( 'sha256', $token ),
		array(
			'email' => $email,
			'created' => time(),
		),
		20 * MINUTE_IN_SECONDS
	);
	return $token;
}

/**
 * Consume magic token → email.
 *
 * @param string $token Token.
 * @return string|false Email or false.
 */
function efpic_client_access_consume_token( $token ) {
	$token = preg_replace( '/[^a-zA-Z0-9]/', '', (string) $token );
	if ( strlen( $token ) < 20 ) {
		return false;
	}
	$key = 'efpic_client_access_' . hash( 'sha256', $token );
	$data = get_transient( $key );
	delete_transient( $key );
	if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
		return false;
	}
	return sanitize_email( $data['email'] );
}

/**
 * Handle magic link landing + logout.
 */
function efpic_client_access_handle_request() {
	if ( ! empty( $_GET['efpic_client_logout'] ) ) {
		efpic_client_access_clear_cookie();
		$redirect = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/' );
		wp_safe_redirect( $redirect );
		exit;
	}

	if ( empty( $_GET['efpic_client_login'] ) ) {
		return;
	}

	$token = sanitize_text_field( wp_unslash( $_GET['efpic_client_login'] ) );
	$email = efpic_client_access_consume_token( $token );
	$redirect = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/' );

	if ( ! $email ) {
		wp_safe_redirect( add_query_arg( 'efpic_client_access', 'invalid', $redirect ) );
		exit;
	}

	efpic_client_access_set_cookie( $email );
	wp_safe_redirect( add_query_arg( 'efpic_client_access', 'ok', remove_query_arg( array( 'efpic_client_login' ), $redirect ) ) );
	exit;
}
add_action( 'template_redirect', 'efpic_client_access_handle_request', 2 );

/**
 * AJAX: request magic link.
 */
function efpic_client_access_request_link() {
	check_ajax_referer( 'efpic-client-access', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'efpic-pro' ) ) );
	}

	// Only send if this email appears on at least one collection (avoid enumeration soft-fail with same message).
	$has_collections = efpic_client_access_email_has_collections( $email );
	if ( $has_collections ) {
		$token = efpic_client_access_create_token( $email );
		$link  = add_query_arg(
			array(
				'efpic_client_login' => $token,
				'redirect_to'        => $redirect,
			),
			home_url( '/' )
		);

		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Your access link for %s', 'efpic-pro' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		);
		$body = sprintf(
			/* translators: %s: magic login URL */
			__( "Click the link below to access your photo collections (valid for 20 minutes):\n\n%s\n\nIf you did not request this, you can ignore this email.", 'efpic-pro' ),
			$link
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		wp_mail( $email, $subject, $body, $headers );
	}

	wp_send_json_success( array(
		'message' => __( 'If this email is associated with a collection, you will receive a login link shortly.', 'efpic-pro' ),
	) );
}
add_action( 'wp_ajax_efpic_client_access_request', 'efpic_client_access_request_link' );
add_action( 'wp_ajax_nopriv_efpic_client_access_request', 'efpic_client_access_request_link' );

/**
 * Whether an email is used on any efpic collection.
 *
 * @param string $email Email.
 * @return bool
 */
function efpic_client_access_email_has_collections( $email ) {
	$email = strtolower( sanitize_email( $email ) );
	$q = new WP_Query( array(
		'post_type'      => 'efpic_collection',
		'post_status'    => array( 'sent', 'approved', 'expired', 'delivered', 'publish', 'draft' ),
		'posts_per_page' => 50,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	foreach ( $q->posts as $id ) {
		$hashes = get_post_meta( $id, '_efpic_collection_hashes', true );
		if ( ! is_array( $hashes ) ) {
			continue;
		}
		foreach ( $hashes as $client ) {
			if ( ! empty( $client['email'] ) && strtolower( $client['email'] ) === $email ) {
				return true;
			}
		}
		$delivery = get_post_meta( $id, '_efpic_delivery_email_address', true );
		if ( $delivery && strtolower( $delivery ) === $email ) {
			return true;
		}
	}

	return false;
}

/**
 * Collections for a client email.
 *
 * @param string $email Email.
 * @return WP_Post[]
 */
function efpic_client_access_collections_for_email( $email ) {
	$email = strtolower( sanitize_email( $email ) );
	$q = new WP_Query( array(
		'post_type'      => 'efpic_collection',
		'post_status'    => array( 'sent', 'approved', 'expired', 'delivered' ),
		'posts_per_page' => 100,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$out = array();
	foreach ( $q->posts as $post ) {
		$hashes = get_post_meta( $post->ID, '_efpic_collection_hashes', true );
		$match  = false;
		if ( is_array( $hashes ) ) {
			foreach ( $hashes as $client ) {
				if ( ! empty( $client['email'] ) && strtolower( $client['email'] ) === $email ) {
					$match = true;
					break;
				}
			}
		}
		if ( ! $match ) {
			$delivery = get_post_meta( $post->ID, '_efpic_delivery_email_address', true );
			if ( $delivery && strtolower( $delivery ) === $email ) {
				$match = true;
			}
		}
		if ( $match ) {
			$out[] = $post;
		}
	}
	return $out;
}

/**
 * Skip collection password when client access email matches a recipient.
 *
 * @param bool $required Whether password is required.
 * @return bool
 */
function efpic_client_access_bypass_password( $required ) {
	if ( ! $required || ! is_singular( 'efpic_collection' ) ) {
		return $required;
	}

	$email = efpic_client_access_email();
	if ( ! $email ) {
		return $required;
	}

	$ident = efpic_get_ident_from_email( get_the_ID(), $email );
	if ( $ident ) {
		return false;
	}

	return $required;
}
add_filter( 'post_password_required', 'efpic_client_access_bypass_password', 20 );

/**
 * Auto-append ident when client access session matches collection.
 */
function efpic_client_access_auto_ident() {
	if ( ! is_singular( 'efpic_collection' ) || ! empty( $_GET['ident'] ) ) {
		return;
	}

	$email = efpic_client_access_email();
	if ( ! $email ) {
		return;
	}

	$ident = efpic_get_ident_from_email( get_the_ID(), $email );
	if ( ! $ident ) {
		return;
	}

	$url = add_query_arg( 'ident', $ident );
	wp_safe_redirect( $url );
	exit;
}
add_action( 'template_redirect', 'efpic_client_access_auto_ident', 6 );

/**
 * Register Client Access block (server-rendered).
 */
function efpic_client_access_register_block() {
	register_block_type(
		'efpic/client-access',
		array(
			'api_version'     => 3,
			'title'           => __( 'efpic Client Access', 'efpic-pro' ),
			'category'        => 'media',
			'icon'            => 'email-alt',
			'description'     => __( 'Let clients request a magic login link to access their collections.', 'efpic-pro' ),
			'render_callback' => 'efpic_client_access_render_block',
			'supports'        => array(
				'html'   => false,
				'color'  => array( 'background' => true, 'text' => true ),
				'spacing' => array( 'margin' => true, 'padding' => true ),
			),
			'attributes'      => array(
				'title' => array(
					'type'    => 'string',
					'default' => __( 'Access your galleries', 'efpic-pro' ),
				),
			),
		)
	);
}
add_action( 'init', 'efpic_client_access_register_block' );

/**
 * Render Client Access block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function efpic_client_access_render_block( $attributes = array() ) {
	$title = ! empty( $attributes['title'] ) ? $attributes['title'] : __( 'Access your galleries', 'efpic-pro' );
	$email = efpic_client_access_email();
	$wrapper = get_block_wrapper_attributes( array( 'class' => 'efpic-client-access' ) );

	ob_start();
	echo '<div ' . $wrapper . '>';
	echo '<h3 class="efpic-client-access__title">' . esc_html( $title ) . '</h3>';

	if ( ! empty( $_GET['efpic_client_access'] ) && 'invalid' === $_GET['efpic_client_access'] ) {
		echo '<p class="efpic-client-access__notice">' . esc_html__( 'That login link is invalid or has expired. Please request a new one.', 'efpic-pro' ) . '</p>';
	}

	if ( $email ) {
		$collections = efpic_client_access_collections_for_email( $email );
		echo '<p class="efpic-client-access__logged-in">' . sprintf(
			/* translators: %s: email address */
			esc_html__( 'Signed in as %s', 'efpic-pro' ),
			esc_html( $email )
		) . '</p>';

		$logout = add_query_arg(
			array(
				'efpic_client_logout' => 1,
				'redirect_to'         => get_permalink(),
			),
			home_url( '/' )
		);
		echo '<p><a class="efpic-client-access__logout" href="' . esc_url( $logout ) . '">' . esc_html__( 'Sign out', 'efpic-pro' ) . '</a></p>';

		if ( empty( $collections ) ) {
			echo '<p>' . esc_html__( 'No collections found for this email.', 'efpic-pro' ) . '</p>';
		} else {
			echo efpic_prepare_collections_list_html( $collections, array( 'email' => $email ) );
		}
	} else {
		$nonce = wp_create_nonce( 'efpic-client-access' );
		?>
		<form class="efpic-client-access__form" method="post" action="#">
			<label for="efpic-client-access-email"><?php esc_html_e( 'Your email', 'efpic-pro' ); ?></label>
			<input type="email" id="efpic-client-access-email" name="email" required autocomplete="email" />
			<button type="submit" class="button"><?php esc_html_e( 'Send login link', 'efpic-pro' ); ?></button>
			<p class="efpic-client-access__status" hidden></p>
		</form>
		<script>
		(function(){
			var form = document.currentScript.previousElementSibling;
			if (!form || !form.classList.contains('efpic-client-access__form')) {
				form = document.querySelector('.efpic-client-access__form');
			}
			if (!form) return;
			form.addEventListener('submit', function(e){
				e.preventDefault();
				var status = form.querySelector('.efpic-client-access__status');
				var email = form.querySelector('input[type="email"]').value;
				status.hidden = false;
				status.textContent = <?php echo wp_json_encode( __( 'Sending…', 'efpic-pro' ) ); ?>;
				var body = new FormData();
				body.append('action', 'efpic_client_access_request');
				body.append('nonce', <?php echo wp_json_encode( $nonce ); ?>);
				body.append('email', email);
				body.append('redirect_to', <?php echo wp_json_encode( get_permalink() ?: home_url( '/' ) ); ?>);
				fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, { method: 'POST', body: body, credentials: 'same-origin' })
					.then(function(r){ return r.json(); })
					.then(function(data){
						status.textContent = (data.data && data.data.message) ? data.data.message : <?php echo wp_json_encode( __( 'Done.', 'efpic-pro' ) ); ?>;
					})
					.catch(function(){
						status.textContent = <?php echo wp_json_encode( __( 'Something went wrong. Please try again.', 'efpic-pro' ) ); ?>;
					});
			});
		})();
		</script>
		<?php
	}

	echo '</div>';
	return ob_get_clean();
}
