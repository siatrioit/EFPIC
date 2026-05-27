<?php
/**
 * Per-collection client language (Latvian default, English optional).
 *
 * @since 1.0.5
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post meta key for collection client language.
 */
define( 'EFPIC_CLIENT_LANG_META', '_efpic_collection_client_language' );

/**
 * Allowed client languages.
 *
 * @return string[]
 */
function efpic_get_allowed_client_languages() {
	return array( 'lv', 'en' );
}

/**
 * Get client language for a collection.
 *
 * @param int $post_id Collection post ID.
 * @return string lv|en
 */
function efpic_get_collection_client_language( $post_id ) {
	$lang = get_post_meta( (int) $post_id, EFPIC_CLIENT_LANG_META, true );

	if ( ! in_array( $lang, efpic_get_allowed_client_languages(), true ) ) {
		return 'lv';
	}

	return $lang;
}

/**
 * Set active client language context for gettext filtering.
 *
 * @param int $post_id Collection post ID.
 */
function efpic_set_client_language_context( $post_id ) {
	$GLOBALS['efpic_client_language_context'] = efpic_get_collection_client_language( $post_id );
}

/**
 * Clear client language context.
 */
function efpic_clear_client_language_context() {
	unset( $GLOBALS['efpic_client_language_context'] );
}

/**
 * Get active client language context.
 *
 * @return string|null
 */
function efpic_get_client_language_context() {
	return isset( $GLOBALS['efpic_client_language_context'] ) ? $GLOBALS['efpic_client_language_context'] : null;
}

/**
 * Mail contexts shown to clients (not photographers).
 *
 * @return string[]
 */
function efpic_get_client_email_contexts() {
	return array(
		'client_collection_new',
		'client_delivery_new',
		'client_reminder',
		'new_client_confirmation',
	);
}

/**
 * Latvian translations for client-facing strings (msgid => translation).
 *
 * @return array<string, string>
 */
function efpic_get_client_lv_translations() {
	$efpic = array(
		'Before you start…' => 'Pirms sākat…',
		'Please enter your name and email address. You can start selecting images right away; we will also email you a personal link for later.' => 'Lūdzu, ievadiet vārdu un e-pasta adresi. Varat uzreiz sākt atlasīt bildes; nosūtīsim arī personīgo saiti vēlākai piekļuvei.',
		'Please enter your name to start selecting images. If you add an email, we will also send you a personal link for later.' => 'Lūdzu, ievadiet vārdu, lai sāktu atlasīt bildes. Ja pievienosiet e-pastu, nosūtīsim arī personīgo saiti vēlākai piekļuvei.',
		'Name' => 'Vārds',
		'Email' => 'E-pasts',
		'optional' => 'neobligāti',
		'Continue' => 'Turpināt',
		'close' => 'aizvērt',
		'Thanks!' => 'Paldies!',
		'Please check your email inbox for your personal link to access the collection later.' => 'Pārbaudiet e-pastu — tur būs personīgā saite piekļuvei galerijai vēlāk.',
		'You can safely close this window now.' => 'Tagad varat droši aizvērt šo logu.',
		'Selected' => 'Atlasītas',
		'Unselected' => 'Neatlasītas',
		'Reset filters' => 'Notīrīt filtrus',
		'Show Information about this collection' => 'Rādīt informāciju par šo galeriju',
		'saved' => 'saglabāts',
		'Send<span> selection</span>…' => 'Nosūtīt<span> atlasi</span>…',
		'Approve Collection' => 'Apstiprināt galeriju',
		'Images' => 'Bildes',
		'selected' => 'atlasītas',
		'Anything else you want us to know?' => 'Vai vēlaties kaut ko piebilst?',
		'Leave a comment…' => 'Atstājiet komentāru…',
		'You are about to approve this collection.' => 'Jūs gatavojaties apstiprināt šo galeriju.',
		'Please note, that you won\'t be able to make changes to your selection after that.' => 'Pēc apstiprināšanas atlasi vairs nevarēsiet mainīt.',
		'close lightbox' => 'aizvērt lightbox',
		'next image' => 'nākamā bilde',
		'previous image' => 'iepriekšējā bilde',
		'select image' => 'atlasīt bildi',
		'Download' => 'Lejupielādēt',
		'Select image' => 'Atlasīt bildi',
		'<em>Please Note:</em> This collection has expired. Therefore it is not possible to change your selection at this time.' => '<em>Lūdzu, ņemiet vērā:</em> šī galerija ir beigusies. Pašlaik atlasi vairs nevar mainīt.',
		'<em>Please Note:</em> This collection will expire on %s and you won\'t be able to make changes after that.' => '<em>Lūdzu, ņemiet vērā:</em> šī galerija beigsies %s, un pēc tam atlasi vairs nevarēs mainīt.',
		'OK' => 'Labi',
		'View collection' => 'Skatīt galeriju',
		'Thank you!' => 'Paldies!',
		'The collection has been approved and the photographer has been notified.' => 'Galerija ir apstiprināta, un fotogrāfs ir informēts.',
		'You can now close this browser window.' => 'Tagad varat aizvērt pārlūkprogrammas logu.',
		'You will be redirected in %s seconds.' => 'Pāradresācija pēc %s sekundēm.',
		'<h2>No images found</h2><p>It seems there are no images in this collection.</p>' => '<h2>Bildes nav atrastas</h2><p>Šķiet, ka šajā galerijā nav bilžu.</p>',
		'You have not selected any images.' => 'Jūs neesat atlasījis nevienu bildi.',
		'You have no <em>unselected</em> images.' => 'Jums nav <em>neatlasītu</em> bilžu.',
		'Reset filter to show all images' => 'Notīrīt filtru, lai rādītu visas bildes',
		'No images with that many stars' => 'Nav bilžu ar tik daudz zvaigznēm',
		'Reset stars filter to show available images' => 'Notīrīt zvaigžņu filtru',
		'You have to select at least one image.' => 'Jāatlasīta vismaz viena bilde.',
		'This collection has already been approved.' => 'Šī galerija jau ir apstiprināta.',
		'This collection has expired.' => 'Šī galerija ir beigusies.',
		'Error: Request failed.<br />Do you have a working internet connection?' => 'Kļūda: pieprasījums neizdevās.<br />Vai jums ir interneta savienojums?',
		'This collection is still a draft. You have to open it to select images.' => 'Šī galerija vēl ir melnraksts. Lai atlasītu bildes, fotogrāfam tā jāatver.',
		'Error: You can not make any changes to this collection.' => 'Kļūda: šajā galerijā vairs nevar veikt izmaiņas.',
		'Message' => 'Ziņa',
		'You successfully approved this collection.' => 'Galerija veiksmīgi apstiprināta.',
		'Please select at least one image.' => 'Lūdzu, atlasiet vismaz vienu bildi.',
		'Your selection was saved.' => 'Jūsu atlase ir saglabāta.',
		'Error. Your selection could not be saved.' => 'Kļūda. Atlasi neizdevās saglabāt.',
		'<strong>Error:</strong> Nonce check failed.<br />Refresh your browser window.' => '<strong>Kļūda:</strong> drošības pārbaude neizdevās.<br />Pārlādējiet pārlūkprogrammas logu.',
		'Error: Post id is not set.' => 'Kļūda: galerijas ID nav norādīts.',
		'This collection is in draft mode for preview only. Publish it to allow image selection.' => 'Galerija ir melnrakstā — tikai priekšskatījumam.',
		'Error: Collection is closed.' => 'Kļūda: galerija ir slēgta.',
		'Error: You are not authorized to change the selection.' => 'Kļūda: jums nav tiesību mainīt atlasi.',
		'Success' => 'Veiksmīgi',
		'An error occurred' => 'Radās kļūda',
		'This collection is password protected.' => 'Šī galerija ir aizsargāta ar paroli.',
		'Wrong password.' => 'Nepareiza parole.',
		'To view this collection, enter the password below.' => 'Lai skatītu galeriju, ievadiet paroli zemāk.',
		'Enter' => 'Ievadīt',
		'Password:' => 'Parole:',
		'View Images' => 'Skatīt bildes',
		'<em>Please Note:</em> This collection will expire on %s and you won\'t be able to make changes after that.' => '<em>Lūdzu, ņemiet vērā:</em> šī galerija beigsies %s, un pēc tam atlasi vairs nevarēs mainīt.',
	);

	$pro = array(
		'Please enter a valid email address.' => 'Lūdzu, ievadiet derīgu e-pasta adresi.',
		'Error: Collection is already approved.' => 'Kļūda: galerija jau ir apstiprināta.',
		'Error: Collection has expired.' => 'Kļūda: galerijas termiņš ir beidzies.',
		'You have to provide an email address to continue.' => 'Lai turpinātu, jānorāda e-pasta adrese.',
		'You need to enter either a name or an email address before you can make a selection.' => 'Pirms atlases jāievada vārds vai e-pasta adrese.',
		'Email with collection link sent.' => 'E-pasts ar galerijas saiti ir nosūtīts.',
		'<strong>The name "%s" is already in use for this collection.</strong><br />Either add more details (eg. your surname) or enter your email address to continue.' => '<strong>Vārds „%s” šai galerijai jau tiek izmantots.</strong><br />Pievienojiet papildu informāciju (piem., uzvārdu) vai ievadiet e-pasta adresi.',
		'Registration successful.' => 'Reģistrācija veiksmīga.',
		'An error occured' => 'Radās kļūda',
		"<strong>Registration failed.</strong> Please try again later." => '<strong>Reģistrācija neizdevās.</strong> Lūdzu, mēģiniet vēlāk.',
		"Thanks!\n\nPlease follow the link below to view the images and make your selection." => "Paldies!\n\nLūdzu, izmantojiet saiti zemāk, lai skatītu bildes un veiktu atlasi.",
		'Start selecting images' => 'Sākt atlasīt bildes',
		'Grid Size' => 'Režģa izmērs',
		'S' => 'S',
		'M' => 'M',
		'L' => 'L',
		'Small' => 'Mazs',
		'Medium' => 'Vidējs',
		'Large' => 'Liels',
		'You need to select between %s and %s images.' => 'Jāatlasī no %s līdz %s bildēm.',
		'Toggle Comments' => 'Komentāri',
		'Comments' => 'Komentāri',
		'Click anywhere on the image to add <strong>a marker</strong> or simply add <strong>a comment</strong> by clicking below.' => 'Noklikšķiniet uz bildes, lai pievienotu <strong>marķieri</strong>, vai pievienojiet <strong>komentāru</strong> zemāk.',
		'Delete' => 'Dzēst',
		'Save' => 'Saglabāt',
		'Add Comment' => 'Pievienot komentāru',
		'comments' => 'komentāri',
		'has comment' => 'ar komentāru',
	);

	$map = array();

	foreach ( $efpic as $source => $translation ) {
		$map[ 'efpic|' . $source ] = $translation;
	}

	foreach ( $pro as $source => $translation ) {
		$map[ 'efpic-pro|' . $source ] = $translation;
	}

	return apply_filters( 'efpic_client_lv_translations', $map );
}

/**
 * Translate a string for the active client language context.
 *
 * @param string $text   Source (English) string.
 * @param string $domain Text domain.
 * @return string
 */
function efpic_translate_client_string( $text, $domain = 'efpic' ) {
	static $map = null;

	if ( null === $map ) {
		$map = efpic_get_client_lv_translations();
	}

	$key = $domain . '|' . $text;

	if ( isset( $map[ $key ] ) ) {
		return $map[ $key ];
	}

	return $text;
}

/**
 * Filter gettext for client-facing pages and emails.
 *
 * @param string $translation Current translation.
 * @param string $text          Msgid.
 * @param string $domain        Text domain.
 * @return string
 */
function efpic_filter_client_gettext( $translation, $text, $domain ) {
	if ( ! in_array( $domain, array( 'efpic', 'efpic-pro' ), true ) ) {
		return $translation;
	}

	$lang = efpic_get_client_language_context();

	if ( null === $lang || 'en' === $lang ) {
		return $translation;
	}

	return efpic_translate_client_string( $text, $domain );
}

add_filter( 'gettext', 'efpic_filter_client_gettext', 20, 3 );

/**
 * Activate language context on collection frontend.
 */
function efpic_client_language_on_collection_template() {
	if ( ! is_singular( 'efpic_collection' ) ) {
		return;
	}

	efpic_set_client_language_context( get_queried_object_id() );
}

add_action( 'template_redirect', 'efpic_client_language_on_collection_template', 0 );

/**
 * Activate language context for client AJAX requests.
 */
function efpic_client_language_on_ajax() {
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		return;
	}

	$post_id = 0;

	if ( ! empty( $_POST['postid'] ) ) {
		$post_id = absint( $_POST['postid'] );
	}

	if ( ! $post_id ) {
		return;
	}

	efpic_set_client_language_context( $post_id );
}

add_action( 'wp_ajax_efpic_send_selection', 'efpic_client_language_on_ajax', 0 );
add_action( 'wp_ajax_nopriv_efpic_send_selection', 'efpic_client_language_on_ajax', 0 );
add_action( 'wp_ajax_efpic_register', 'efpic_client_language_on_ajax', 0 );
add_action( 'wp_ajax_nopriv_efpic_register', 'efpic_client_language_on_ajax', 0 );

/**
 * Activate language context before client emails are built/sent.
 *
 * @param string $mail_context Email context.
 * @param int    $post_id      Collection ID.
 */
function efpic_client_language_before_client_email( $mail_context, $post_id ) {
	if ( in_array( $mail_context, efpic_get_client_email_contexts(), true ) ) {
		efpic_set_client_language_context( $post_id );
	}
}

add_action( 'efpic_before_email_build', 'efpic_client_language_before_client_email', 0, 2 );

/**
 * Render client language selector on collection edit screen.
 *
 * @param WP_Post $post Collection post.
 */
function efpic_collection_client_language_field() {
	global $post;

	if ( empty( $post ) || 'efpic_collection' !== get_post_type( $post ) ) {
		return;
	}

	$current = efpic_get_collection_client_language( $post->ID );
	$disabled = in_array( get_post_status( $post ), array( 'approved', 'expired', 'delivered' ), true );
	?>
	<p class="efpic-client-language-field">
		<label for="efpic-collection-client-language"><?php esc_html_e( 'Client language', 'efpic' ); ?></label>
		<select id="efpic-collection-client-language" name="efpic_collection_client_language" <?php disabled( $disabled ); ?>>
			<option value="lv" <?php selected( $current, 'lv' ); ?>><?php esc_html_e( 'Latvian', 'efpic' ); ?></option>
			<option value="en" <?php selected( $current, 'en' ); ?>><?php esc_html_e( 'English', 'efpic' ); ?></option>
		</select>
		<span class="efpic-hint"><?php esc_html_e( 'Language for all messages the client sees in the gallery and in emails for this collection.', 'efpic' ); ?></span>
	</p>
	<?php
}

add_action( 'efpic_after_collection_description', 'efpic_collection_client_language_field' );

/**
 * Save collection client language meta.
 *
 * @param int $post_id Collection post ID.
 */
function efpic_save_collection_client_language( $post_id ) {
	if ( ! isset( $_POST['efpic_gallery_ids_nonce'] ) || ! wp_verify_nonce( $_POST['efpic_gallery_ids_nonce'], 'efpic_gallery_ids' ) ) {
		return;
	}

	if ( ! current_user_can( efpic_capability(), $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['efpic_collection_client_language'] ) ) {
		return;
	}

	$lang = sanitize_key( wp_unslash( $_POST['efpic_collection_client_language'] ) );

	if ( ! in_array( $lang, efpic_get_allowed_client_languages(), true ) ) {
		$lang = 'lv';
	}

	update_post_meta( $post_id, EFPIC_CLIENT_LANG_META, $lang );
}

add_action( 'save_post_efpic_collection', 'efpic_save_collection_client_language', 6 );
