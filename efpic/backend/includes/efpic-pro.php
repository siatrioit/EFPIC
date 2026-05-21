<?php
/**
 * Handle Pro upselling.
 *
 * @since 3.3.1
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Define Black Friday start and end dates
 */
define( 'EFPIC_BF_START_DATE', '2025-11-23 00:00:00 Europe/Berlin' );
define( 'EFPIC_BF_END_DATE', '2025-11-28 23:59:59 Europe/Berlin' );
define( 'EFPIC_BF_EXTENDED_DATE', '2025-12-03 23:59:59 Europe/Berlin' );


/**
 * Maybe show the Black Friday banner.
 *
 * @since 3.3.1
 */
function efpic_maybe_show_bf_banner() {
	// Do not show the banner, if Pro is active
	if ( efpic_is_pro_active() && efpic_is_pro_license_valid() ) {
		return;
	}

	// Check if the time is right
	$timezone = new DateTimeZone( 'Europe/Berlin' );
	$now = new DateTime( 'now', $timezone );
	$start = new DateTime( EFPIC_BF_START_DATE );
	$end = new DateTime( EFPIC_BF_EXTENDED_DATE );

	if ( $now < $start || $now > $end ) {
		return;
	}

	// Check if the transient to hide the banner is set
	$hide_until_timestamp = get_transient( 'efpic_hide_bf_banner_' . get_current_user_id() );

	if ( ! empty( $hide_until_timestamp ) && $now->getTimestamp() < $hide_until_timestamp ) {
		return;
	}

	efpic_display_bf_banner();
}


/**
 * Display the Black Friday efpic Pro banner.
 *
 * @since 3.3.1
 */
function efpic_display_bf_banner() {
?>
	<style>
		.efpic-bf-banner {
			position: relative;
			display: grid;
			gap: 2px;
			padding: 10px 20px;
			background: #17181c url("<?php echo EFPIC_URL; ?>backend/images/efpic-bf-banner-bg.png") no-repeat right bottom;
			background-size: 350px;
			background-position: bottom -150px right -120px;
			color: white;
			border-radius: 5px;
			text-decoration: none;
		}
		.efpic-settings__wrap .efpic-bf-banner {
			margin-block: -20px 20px;
			width: 100%;
		}
		#wpbody-content > .efpic-bf-banner {
			margin-block-start: 20px;
			margin-inline-end: 20px;
		}
		.efpic-bf-banner__headline {
			color: #9cf51f;
			font-size: 10px;
			font-weight: 700;
		}
		.efpic-bf-banner__claim {
			margin-block-end: 2px;
			color: white;
			font-size: 18px;
			font-weight: 700;
		}
		.efpic-bf-banner__deal-end {
			color: white;
			font-size: 12px;
		}
		.efpic-bf-banner__hide {
			position: absolute;
			top: 3px;
			right: 3px;
			width: 14px;
			height: 14px;
			color: black;
			font-size: 10px;
			background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-x'%3E%3Cpath stroke='none' d='M0 0h24v24H0z' fill='none'/%3E%3Cpath d='M18 6l-12 12' /%3E%3Cpath d='M6 6l12 12' /%3E%3C/svg%3E") no-repeat center center;
			background-size: 12px 12px;
			border: none;
			border-radius: 50%;
			text-indent: -100000px;
			cursor: pointer;
		}
	</style>
	<?php
		$extended = false;
		$now = new DateTime();
		$end = new DateTime( EFPIC_BF_END_DATE );
		$diff = $now->diff( $end );

		if ( $diff->invert == 1 ) {
			$extended = true;
			$end = new DateTime( EFPIC_BF_EXTENDED_DATE );
			$diff = $now->diff( $end );
		}
	?>
	<a class="efpic-bf-banner" href="https://go.efpic.io/bf-2025">
		<span class="efpic-bf-banner__headline">Black Friday Sale<?php if ( $extended ) echo ' Extended!'; ?></span>
		<span class="efpic-bf-banner__claim">Save $50 on efpic Pro</span>
		<span class="efpic-bf-banner__deal-end">Deal <span id="countdown"></span><span>
		<?php
		if ( ! $extended ) {
		?>
		<button class="efpic-bf-banner__hide" title="Remind me later">Remind me later</button>
		<?php
		}
		?>
	</a>
	<?php
		// Convert date, so it works in JS
		$js_date = $end->format( 'c' );
	?>
	<script>
		function updateBlackFridayCountdown() {
			const endDate = new Date( '<?php echo $js_date; ?>' );
			const now = new Date();
			const diff = endDate - now;
			
			if ( diff <= 0 ) {
				document.getElementById( 'countdown' ).innerHTML = 'has ended!';
				return;
			}
			
			const days = Math.floor( diff / ( 1000 * 60 * 60 * 24 ) );
			const hours = Math.floor( ( diff % ( 1000 * 60 * 60 * 24 ) ) / ( 1000 * 60 * 60 ) );
			const minutes = Math.floor( ( diff % ( 1000 * 60 * 60 ) ) / ( 1000 * 60 ) );
			const seconds = Math.floor( ( diff % ( 1000 * 60 ) ) / 1000 );
			
			let display = '';
			
			if ( days >= 3 ) {
				// More than 3 days left: show only days
				display = `${days} days`;
			} else {
				// 3 days or less: show only non-zero values
				const parts = [];
				if ( days > 0 ) parts.push( `${days}d` );
				if ( hours > 0 ) parts.push( `${hours}h` );
				if ( minutes > 0 ) parts.push( `${minutes}m` );
				parts.push( `${seconds}s` );
		
				// Always show at least seconds if everything else is 0
				display = parts.length > 0 ? parts.join( ' ' ) : '0s';
			}
			
			document.getElementById( 'countdown' ).innerHTML = 'ends in ' + display;
		}

		updateBlackFridayCountdown();
		countdownInterval = setInterval( updateBlackFridayCountdown, 1000 );

		// Hide banner on click
		document.addEventListener( 'click', function( e ) {
			if ( e.target.classList.contains( 'efpic-bf-banner__hide' ) ) {
				e.preventDefault();

				// Stop the countdown
				clearInterval( countdownInterval );
				
				// Create FormData for the POST request
				const formData = new FormData();
				formData.append( 'action', 'efpic_save_bf_banner_state' );
				formData.append( 'security', efpic_admin.ajax_nonce );
				
				// Send AJAX request
				fetch( efpic_admin.ajaxurl, {
					method: 'POST',
					body: formData
				});
				
				// Remove the banner
				const banner = document.querySelector( '.efpic-bf-banner' );
				if ( banner ) {
					banner.remove();
				}
			}
		});
	</script>
<?php
}


/**
 * Add BF banner in various places
 *
 * @since 3.3.1
 */
add_action( 'efpic_pre_settings', 'efpic_maybe_show_bf_banner', 9 );


/**
 * Display Black Friday banner above collections.
 *
 * @since 3.3.1
 */
function efpic_bf_admin_notices() {
	$screen = get_current_screen();
	if ( in_array( $screen->id, [ 'edit-efpic_collection', 'efpic_collection' ] ) ) {
		efpic_maybe_show_bf_banner();
	}
}

add_action( 'admin_notices', 'efpic_bf_admin_notices' );


/**
 * Temporarily hide the Black Friday banner.
 *
 * @since 3.3.1
 */
function efpic_save_bf_banner_state() {
	if ( ! check_ajax_referer( 'efpic_ajax', 'security', false ) ) {
		efpic_send_json( 'error', __( '<strong>Error:</strong> Nonce check failed.', 'efpic' ) );
	}

	// Calculate time until 3 days before the deal ends
	$now = new DateTime();
	$end = new DateTime( EFPIC_BF_END_DATE );
	$diff = $now->diff( $end );

	if ( $diff->invert == 1 ) {
		// We're past the regular end date, don't set transient (show banner during extended period)
		wp_send_json_success();
		exit;
	}
	else {
		// Calculate seconds until the end date
		$timestamp = $end->getTimestamp();

		// Calculate expiration time for the transient itself (until end date)
		$expiration = $timestamp - $now->getTimestamp();
	}
	
	set_transient( 'efpic_hide_bf_banner_' . get_current_user_id(), $timestamp, $expiration );

	wp_send_json_success();
	exit;
}

add_action( 'wp_ajax_efpic_save_bf_banner_state', 'efpic_save_bf_banner_state' );