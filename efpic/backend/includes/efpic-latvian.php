<?php
/**
 * Latvian UI strings for efpic / efpic-pro (admin + client when locale is lv_*).
 *
 * @since 1.0.23
 */
defined( 'ABSPATH' ) || exit;

/**
 * Whether Latvian translations should apply for the current locale.
 *
 * @return bool
 */
function efpic_should_use_latvian_strings() {
	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	return ( 0 === strpos( (string) $locale, 'lv' ) );
}

/**
 * Translation map (English source => Latvian).
 *
 * @return array<string,string>
 */
function efpic_get_latvian_translations() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}

	$map = array(
		// Image order
		'Image order' => 'Bilžu secība',
		'Manual (drag & drop)' => 'Manuāli (vilkt un nomest)',
		'Filename A → Z' => 'Faila nosaukums A → Z',
		'Filename Z → A' => 'Faila nosaukums Z → A',
		'Created date (oldest first)' => 'Izveides datums (vecākās pirmās)',
		'Created date (newest first)' => 'Izveides datums (jaunākās pirmās)',
		'Filename order uses the original file name (natural sort, e.g. 2 before 10). Manual keeps drag & drop order.' => 'Pēc faila nosaukuma tiek izmantots oriģinālais nosaukums (dabiskā kārtošana, piem. 2 pirms 10). Manuāli saglabā vilkšanas secību.',
		'Image order adjusted.' => 'Bilžu secība atjaunināta.',
		'Image order restored.' => 'Bilžu secība atjaunota.',
		'Images were already sorted in this order.' => 'Bildes jau bija sakārtotas šajā secībā.',
		'Undo' => 'Atsaukt',

		// Text filter
		'Search images…' => 'Meklēt bildes…',
		'Filter images by text' => 'Filtrēt bildes pēc teksta',
		'No images match your search.' => 'Neviena bilde neatbilst meklējumam.',
		'Clear search' => 'Notīrīt meklēšanu',

		// Collection edit – common
		'Collection Status' => 'Kolekcijas statuss',
		'Collection History' => 'Kolekcijas vēsture',
		'Upload Images' => 'Augšupielādēt bildes',
		'Collection Options' => 'Kolekcijas opcijas',
		'Share Options' => 'Kopīgošanas opcijas',
		'Send via email' => 'Nosūtīt e-pastā',
		'Copy link &amp; send manually' => 'Kopēt saiti un nosūtīt manuāli',
		'Copy link & send manually' => 'Kopēt saiti un nosūtīt manuāli',
		'Client Email' => 'Klienta e-pasts',
		'Message' => 'Ziņa',
		'Copy URL' => 'Kopēt URL',
		'Save' => 'Saglabāt',
		'Send to Client' => 'Nosūtīt klientam',
		'Publish' => 'Publicēt',
		'Edit' => 'Rediģēt',
		'Close' => 'Aizvērt',
		'Duplicate' => 'Dublēt',
		'Cancel' => 'Atcelt',
		'Caution!' => 'Uzmanību!',
		'You already sent this collection to the client.' => 'Šī kolekcija jau ir nosūtīta klientam.',
		'Are you sure you want to make changes?' => 'Vai tiešām vēlies veikt izmaiņas?',
		'Yes, I am sure' => 'Jā, esmu pārliecināts',
		'Show Password' => 'Rādīt paroli',
		'Password Protection' => 'Paroles aizsardzība',
		'Enter Password' => 'Ievadi paroli',
		'Empty Password Field' => 'Notīrīt paroles lauku',
		'Copy collection URL to clipboard' => 'Kopēt kolekcijas URL starpliktuvē',
		'Copied' => 'Nokopēts',
		'Selection Summary' => 'Atlases kopsavilkums',
		'Show Details' => 'Rādīt detaļas',
		'Hide Details' => 'Slēpt detaļas',
		'Add Client' => 'Pievienot klientu',
		'Approved' => 'Apstiprināts',
		'Failed' => 'Neizdevās',
		'Waiting' => 'Gaida',
		'Client name' => 'Klienta vārds',
		'Client email address' => 'Klienta e-pasta adrese',
		'Show' => 'Rādīt',
		'Images' => 'Bildes',
		'Published' => 'Publicēts',
		'Expires' => 'Beidzas',
		'Closed' => 'Aizvērts',
		'Expired' => 'Beidzies',
		'Create collection' => 'Izveidot kolekciju',
		'Drag and drop your images here or click the button to upload' => 'Velc bildes šeit vai spied pogu, lai augšupielādētu',
		'Upload / Edit Images' => 'Augšupielādēt / rediģēt bildes',
		'Maximum upload size' => 'Maksimālais faila izmērs',
		'Help' => 'Palīdzība',
		'Show all images' => 'Rādīt visas bildes',
		'Hide images' => 'Slēpt bildes',
		'Copy Filenames' => 'Kopēt failu nosaukumus',
		'Download Proof' => 'Lejupielādēt apstiprinājumu',
		'Collection reopened.' => 'Kolekcija atvērta atkārtoti.',
		'Open' => 'Atvērt',
		'Edit Delivery' => 'Rediģēt piegādi',
		'You already delivered this collection to your client.' => 'Šī kolekcija jau ir piegādāta klientam.',
		'You are about to close this collection.' => 'Tu gatavojies aizvērt šo kolekciju.',
		'Clients can no longer submit their selections after that.' => 'Pēc tam klienti vairs nevarēs iesniegt savu atlasi.',
		'New clients can no longer register themselves after that.' => 'Pēc tam jauni klienti vairs nevarēs paši reģistrēties.',
		'Are you sure you want to close this collection?' => 'Vai tiešām vēlies aizvērt šo kolekciju?',
		'Selected' => 'Atlasītas',
		'Unselected' => 'Neatlasītas',
		'Reset filters' => 'Atiestatīt filtrus',
		'Grid Size' => 'Režģa izmērs',
		'Small' => 'Mazs',
		'Medium' => 'Vidējs',
		'Large' => 'Liels',
		'saved' => 'saglabāts',
		'Send<span> selection</span>…' => 'Nosūtīt<span> atlasi</span>…',
		'Show Information about this collection' => 'Rādīt informāciju par šo kolekciju',
		'No collections found.' => 'Kolekcijas nav atrastas.',
		'You must be %slogged in%s to see collections.' => 'Lai redzētu kolekcijas, tev jābūt %spierakstījušamies%s.',

		// Tools / security / migrate / CSV
		'Migrate from PICU' => 'Migrēt no PICU',
		'Export collections (CSV)' => 'Eksportēt kolekcijas (CSV)',
		'Download CSV' => 'Lejupielādēt CSV',
		'Download a CSV list of all efpic collections (title, status, clients, image count, URL).' => 'Lejupielādē visu efpic kolekciju CSV sarakstu (nosaukums, statuss, klienti, bilžu skaits, URL).',
		'Prevent direct image access' => 'Bloķēt tiešo bilžu piekļuvi',
		'When enabled, collection images can only be loaded from within an efpic gallery (not via a direct file URL).' => 'Kad ieslēgts, kolekcijas bildes ielādējas tikai no efpic galerijas (nevis ar tiešu faila URL).',
		'On' => 'Ieslēgts',
		'Off' => 'Izslēgts',
		'Tools/Debug' => 'Rīki / atkļūdošana',
		'Debug info and tools.' => 'Atkļūdošanas info un rīki.',
		'Security' => 'Drošība',

		// Client access
		'efpic Client Access' => 'efpic klientu piekļuve',
		'Let clients request a magic login link to access their collections.' => 'Ļauj klientiem pieprasīt ieejas saiti uz savām kolekcijām.',
		'Access your galleries' => 'Piekļūsti savām galerijām',
		'Your email' => 'Tavs e-pasts',
		'Send login link' => 'Nosūtīt ieejas saiti',
		'Sign out' => 'Izrakstīties',
		'Signed in as %s' => 'Pierakstījies kā %s',
		'No collections found for this email.' => 'Šim e-pastam kolekcijas nav atrastas.',
		'Please enter a valid email address.' => 'Lūdzu, ievadi derīgu e-pasta adresi.',
		'If this email is associated with a collection, you will receive a login link shortly.' => 'Ja šis e-pasts ir saistīts ar kolekciju, drīzumā saņemsi ieejas saiti.',
		'Sending…' => 'Sūta…',
		'Done.' => 'Gatavs.',
		'Something went wrong. Please try again.' => 'Kaut kas nogāja greizi. Mēģini vēlreiz.',
		'That login link is invalid or has expired. Please request a new one.' => 'Šī ieejas saite nav derīga vai ir beigusies. Pieprasi jaunu.',
		'Your access link for %s' => 'Tava piekļuves saite: %s',
		"Click the link below to access your photo collections (valid for 20 minutes):\n\n%s\n\nIf you did not request this, you can ignore this email." => "Spied saiti zemāk, lai atvērtu savas foto kolekcijas (derīga 20 minūtes):\n\n%s\n\nJa tu to nepieprasīji, vari ignorēt šo e-pastu.",

		// Settings groups often seen
		'Settings' => 'Iestatījumi',
		'General' => 'Vispārīgi',
		'Documentation' => 'Dokumentācija',
		'Support' => 'Atbalsts',
		'Save Settings' => 'Saglabāt iestatījumus',
		'New Collection' => 'Jauna kolekcija',

		// Status labels often in lists
		'Draft' => 'Melnraksts',
		'Sent' => 'Nosūtīts',
		'Delivered' => 'Piegādāts',
	);

	/**
	 * Filter Latvian translation map.
	 *
	 * @param array $map English => Latvian.
	 */
	$map = apply_filters( 'efpic_latvian_translations', $map );

	return $map;
}

/**
 * Apply Latvian gettext overrides.
 *
 * @param string $translation Translated text.
 * @param string $text        Original text.
 * @param string $domain      Text domain.
 * @return string
 */
function efpic_latvian_gettext( $translation, $text, $domain ) {
	if ( 'efpic' !== $domain && 'efpic-pro' !== $domain ) {
		return $translation;
	}
	if ( ! efpic_should_use_latvian_strings() ) {
		return $translation;
	}

	$map = efpic_get_latvian_translations();
	if ( isset( $map[ $text ] ) ) {
		return $map[ $text ];
	}

	return $translation;
}
add_filter( 'gettext', 'efpic_latvian_gettext', 20, 3 );

/**
 * Apply Latvian gettext_with_context overrides.
 *
 * @param string $translation Translated text.
 * @param string $text        Original text.
 * @param string $context     Context.
 * @param string $domain      Text domain.
 * @return string
 */
function efpic_latvian_gettext_with_context( $translation, $text, $context, $domain ) {
	return efpic_latvian_gettext( $translation, $text, $domain );
}
add_filter( 'gettext_with_context', 'efpic_latvian_gettext_with_context', 20, 4 );

/**
 * Apply Latvian ngettext overrides (singular/plural).
 *
 * @param string $translation Translated text.
 * @param string $single      Singular.
 * @param string $plural      Plural.
 * @param int    $number      Number.
 * @param string $domain      Domain.
 * @return string
 */
function efpic_latvian_ngettext( $translation, $single, $plural, $number, $domain ) {
	if ( 'efpic' !== $domain && 'efpic-pro' !== $domain ) {
		return $translation;
	}
	if ( ! efpic_should_use_latvian_strings() ) {
		return $translation;
	}

	$map = efpic_get_latvian_translations();
	$key = ( 1 === (int) $number ) ? $single : $plural;
	if ( isset( $map[ $key ] ) ) {
		return $map[ $key ];
	}

	return $translation;
}
add_filter( 'ngettext', 'efpic_latvian_ngettext', 20, 5 );
