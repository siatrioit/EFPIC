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
		'Manual (drag & drop)' => 'Manuāli',
		'Filename A → Z' => 'Nosaukums A → Z',
		'Filename Z → A' => 'Nosaukums Z → A',
		'Created date (oldest first)' => 'Datums ↑',
		'Created date (newest first)' => 'Datums ↓',
		'Filename order uses the original file name (natural sort, e.g. 2 before 10). Manual keeps drag & drop order.' => 'Pēc oriģinālā faila nosaukuma (2 pirms 10). Manuāli = vilkšanas secība.',
		'Image order adjusted.' => 'Bilžu secība atjaunināta.',
		'Image order restored.' => 'Bilžu secība atjaunota.',
		'Images were already sorted in this order.' => 'Bildes jau bija sakārtotas šajā secībā.',
		'Undo' => 'Atsaukt',

		// Text filter / status bar — keep short for layout
		'Search images…' => 'Meklēt…',
		'Filter images by text' => 'Filtrēt bildes',
		'No images match your search.' => 'Nav atbilstošu bilžu.',
		'Clear search' => 'Notīrīt',
		'Selected' => 'Atlasītas',
		'Unselected' => 'Pārējās',
		'Reset filters' => 'Atiestatīt',
		'Send<span> selection</span>…' => 'Nosūtīt<span> atlasi</span>…',
		'saved' => 'saglabāts',
		'Grid Size' => 'Izmērs',
		'Small' => 'S',
		'Medium' => 'M',
		'Large' => 'L',
		'Show Information about this collection' => 'Informācija',

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
		'efpic Settings' => 'efpic iestatījumi',
		'General efpic settings.' => 'Vispārīgie efpic iestatījumi.',
		'Use random URLs for efpic collections' => 'Nejauši URL kolekcijām',
		'Disable, to use the WordPress default, generating the slug from the title.' => 'Izslēdz, lai izmantotu WordPress noklusējumu no nosaukuma.',
		'Expire collections by default' => 'Pēc noklusējuma beigt derīgumu',
		'Show efpic logo' => 'Rādīt efpic logo',
		'Spread some efpic love, by displaying our logo in collections and efpic related emails.' => 'Rādīt efpic logo kolekcijās un e-pastos.',
		'Design/Appearance' => 'Dizains / izskats',
		'Configure the look of your collections.' => 'Pielāgo kolekciju izskatu.',
		'Emails' => 'E-pasti',
		'efpic email settings.' => 'efpic e-pasta iestatījumi.',
		'Use styling in emails' => 'Stilizēti e-pasti',
		'Use beautiful HTML templates when sending emails. Otherwise plain text emails will be sent.' => 'Skaisti HTML e-pasti. Citādi tiks sūtīts vienkāršs teksts.',
		'Include collection password in email' => 'Iekļaut kolekcijas paroli e-pastā',
		'If you set a collection password, it will be included in the email to the client.' => 'Ja parole ir iestatīta, tā tiks iekļauta e-pastā klientam.',
		'Send email reminder' => 'Sūtīt atgādinājumu',
		'If a client started selecting images but did not finally approve the collection, efpic will automatically send a reminder after 24 hours.' => 'Ja klients sāka atlasi, bet neapstiprināja, pēc 24 h tiks nosūtīts atgādinājums.',
		'From Email' => 'No e-pasta',
		'The email address that emails are sent from.' => 'E-pasta adrese, no kuras tiek sūtīti e-pasti.',
		'From Name' => 'No vārda',
		'The name that emails are sent from.' => 'Vārds, no kura tiek sūtīti e-pasti.',
		'Notification Email' => 'Paziņojumu e-pasts',
		'The email address <strong>all</strong> notification emails are sent to.' => 'E-pasts, uz kuru tiek sūtīti <strong>visi</strong> paziņojumi.',
		'Email signature (HTML)' => 'E-pasta paraksts (HTML)',
		'Optional HTML signature appended to outgoing client emails. This signature is NOT stored in the collection message and will not show in the client info modal.' => 'Neobligāts HTML paraksts izejošajiem e-pastiem. Netiek glabāts kolekcijas ziņā un nerādās klienta info logā.',
		'You can use links and images (e.g. <a>, <img>).' => 'Vari izmantot saites un bildes (piem. <a>, <img>).',
		'Password protect you collections and more.' => 'Paroles aizsardzība un citi drošības iestatījumi.',
		'Password protection by default' => 'Parole pēc noklusējuma',
		'A random password will automatically assigned to all new efpic collections.' => 'Jaunām kolekcijām automātiski tiks piešķirta nejauša parole.',
		'Image processor' => 'Attēlu procesors',
		'Switch between different image processors to improve performance when uploading/importing images.<br /><strong>Please be aware, that this affects all media uploads on your site, not just efpic images.</strong>' => 'Maini attēlu procesoru, lai uzlabotu augšupielādi.<br /><strong>Ietekmē visas vietnes mediijas, ne tikai efpic.</strong>',
		'Default processor, sometimes memory issue might cause not all images to be processed correctly. Allows to create PDF preview images.' => 'Noklusējuma procesors. Dažreiz atmiņas problēmas. Ļauj veidot PDF priekšskatījumus.',
		'Older processor, better at processing lots of images. Not able to create PDF preview images.' => 'Vecāks procesors, labāks lielam bilžu skaitam. Nevar veidot PDF priekšskatījumus.',
		'Debug Info' => 'Atkļūdošanas info',
		'Copy site info to clipboard' => 'Kopēt vietnes info',
		'Copied!' => 'Nokopēts!',
		'Add a watermark to your images and more.' => 'Ūdenszīme un citi drošības rīki.',
		'Disable right click' => 'Atslēgt labo klikšķi',
		'Watermark' => 'Ūdenszīme',
		'Apply watermark by default' => 'Piemērot ūdenszīmi pēc noklusējuma',
		'Image Title' => 'Bildes virsraksts',
		'After Approving a Collection' => 'Pēc kolekcijas apstiprināšanas',
		'Default expiration time' => 'Noklusējuma derīguma termiņš',
		'Time span after which a collection expires in days. (Expiration can be activated per collection.)' => 'Dienu skaits līdz derīguma beigām (ieslēdzams katrai kolekcijai).',
		'New collections will be set to automatically expire.' => 'Jaunās kolekcijas automātiski beigs derīgumu.',
		'Move to Trash' => 'Pārvietot uz miskasti',
		'Please note:' => 'Lūdzu, ņem vērā:',
		'<strong>Please note:</strong> efpic will <strong>NOT</strong> send an email. Make sure to copy and send the link to your client manually.' => '<strong>Uzmanību:</strong> efpic <strong>NESŪTĪS</strong> e-pastu. Nokopē un nosūti saiti klientam pats.',
		'The password will be sent to the client with the email.' => 'Parole tiks nosūtīta klientam e-pastā.',
		'The password will <strong>not</strong> be sent with the email. Make sure to send it to your client separately.' => 'Parole e-pastā <strong>netiks</strong> sūtīta. Nosūti to klientam atsevišķi.',
		'Don\'t forget to sent the password to your client!' => 'Neaizmirsti nosūtīt paroli klientam!',
		'Enter your clients email address' => 'Ievadi klienta e-pasta adresi',
		'The description will be sent to your client via email.' => 'Ziņa tiks nosūtīta klientam e-pastā.',
		'Send original message and collection link to this email address.' => 'Nosūtīt oriģinālo ziņu un kolekcijas saiti uz šo e-pastu.',
		'Security check failed!' => 'Drošības pārbaude neizdevās!',
		'Client ID not found.' => 'Klienta ID nav atrasts.',
		'The collection has been reopened for %s.' => 'Kolekcija atvērta atkārtoti: %s.',
		'Transfer all PICU collections, settings, and image files to EFPIC on this site. Deactivate PICU first — do not uninstall (uninstall can delete collections).' => 'Pārcel visas PICU kolekcijas, iestatījumus un bildes uz EFPIC. Vispirms deaktivē PICU — nevis uninstall (tas var dzēst kolekcijas).',
		'Dry-run (preview)' => 'Dry-run (priekšskatījums)',
		'Run migration' => 'Palaist migrāciju',
		'PICU plugin active' => 'PICU spraudnis aktīvs',
		'Yes — deactivate first' => 'Jā — vispirms deaktivē',
		'No' => 'Nē',
		'PICU collections' => 'PICU kolekcijas',
		'Post meta keys with “picu”' => 'Post meta ar “picu”',
		'Options with “picu”' => 'Opcijas ar “picu”',
		'Attachment paths under picu/' => 'Attachment ceļi zem picu/',
		'uploads/picu folder' => 'uploads/picu mape',
		'Exists' => 'Pastāv',
		'Not found' => 'Nav atrasts',
		'Last migration' => 'Pēdējā migrācija',
		'Migration is blocked while PICU is active.' => 'Migrācija bloķēta, kamēr PICU ir aktīvs.',
		'No PICU data found. Nothing to migrate.' => 'PICU dati nav atrasti. Nav ko migrēt.',
		'Dry-run complete (no changes written).' => 'Dry-run pabeigts (izmaiņas netika rakstītas).',
		'Migration completed successfully.' => 'Migrācija veiksmīgi pabeigta.',
		'Migration finished with errors.' => 'Migrācija beidzās ar kļūdām.',
		'Run PICU → EFPIC migration now? Make sure you have a full backup (DB + uploads/picu).' => 'Palaist PICU → EFPIC migrāciju? Pārliecinies, ka ir pilns backup (DB + uploads/picu).',

		// Status labels often in lists
		'Draft' => 'Melnraksts',
		'Sent' => 'Nosūtīts',
		'Delivered' => 'Piegādāts',
		'selected' => 'atlasītas',
		'Images' => 'Bildes',
		'No collections found.' => 'Kolekcijas nav atrastas.',
		'You must be %slogged in%s to see collections.' => 'Lai redzētu kolekcijas, tev jābūt %spierakstījušamies%s.',
		'efpic Collections List' => 'efpic kolekciju saraksts',
		'Display a list of collections' => 'Rādīt kolekciju sarakstu',
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
