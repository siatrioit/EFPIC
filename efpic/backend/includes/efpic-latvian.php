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
	$locales = array(
		function_exists( 'determine_locale' ) ? determine_locale() : null,
		get_locale(),
		function_exists( 'get_user_locale' ) ? get_user_locale() : null,
	);
	foreach ( $locales as $locale ) {
		if ( is_string( $locale ) && 0 === strpos( $locale, 'lv' ) ) {
			return true;
		}
	}
	return false;
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

	// Build into a local var first so a nested gettext call cannot cache a half-built map.
	$built = array(
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

		// Status bar — keep short for layout
		'Selected' => 'Atlasītas',
		'Unselected' => 'Pārējās',
		'Reset filters' => 'Atiestatīt',
		'Expand' => 'Izvērst',
		'Collapse' => 'Sakļaut',
		'Send<span> selection</span>…' => 'Nosūtīt<span> atlasi</span>…',
		'saved' => 'saglabāts',
		'Grid Size' => 'Izmērs',
		'Small' => 'Mazs',
		'Medium' => 'Vidējs',
		'Large' => 'Liels',
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
		'Client approved' => 'Klients Apstiprinājis',
		'Expired' => 'Termiņš beidzies',
		'Deadline expired' => 'Termiņš beidzies',
		'Create collection' => 'Izveidot kolekciju',
		'Drag and drop your images here or click the button to upload' => 'Velc bildes šeit vai spied pogu, lai augšupielādētu',
		'Upload / Edit Images' => 'Augšupielādēt / rediģēt bildes',
		'Maximum upload size' => 'Maksimālais faila izmērs',
		'Help' => 'Palīdzība',
		'Show all images' => 'Rādīt visas bildes',
		'Hide images' => 'Slēpt bildes',
		'Copy Filenames' => 'Kopēt nosaukumus',
		'Copy Filenames %s' => 'Kopēt nosaukumus %s',
		'Copy %s Filename' => 'Kopēt %s failu',
		'Download Proof' => 'Lejupielādēt apstiprinājumu',
		'Selected by all' => 'Atlasījuši visi',
		'Selected at least once' => 'Atlasīta vismaz reizi',
		'Selected by %s' => 'Atlasījis: %s',
		'Not selected' => 'Neatlasītas',
		'All' => 'Visas',
		'Thumbnail' => 'Sīktēls',
		'File' => 'Fails',
		'Comments' => 'Komentāri',
		'has comment' => 'ar komentāru',
		'View' => 'Skatīt',
		'Reopen' => 'Atvērt no jauna',
		'Remove…' => 'Noņemt…',
		'Remove...' => 'Noņemt…',
		'Show Download History' => 'Rādīt lejupielāžu vēsturi',
		'Hide Download History' => 'Slēpt lejupielāžu vēsturi',
		'Date/Time' => 'Datums/laiks',
		'Name' => 'Vārds',
		'Collection reopened.' => 'Kolekcija atvērta atkārtoti.',
		'Open' => 'Atvērt',
		'Sent for selection' => 'Nosūtīts atlasei',
		'Sent for selection <span class="count">(%s)</span>' => 'Nosūtīts atlasei <span class="count">(%s)</span>',
		'Client approved <span class="count">(%s)</span>' => 'Klients Apstiprinājis <span class="count">(%s)</span>',
		'Deadline expired <span class="count">(%s)</span>' => 'Termiņš beidzies <span class="count">(%s)</span>',
		'Completed <span class="count">(%s)</span>' => 'Noslēgts <span class="count">(%s)</span>',
		'Delivery draft <span class="count">(%s)</span>' => 'Piegādes melnraksts <span class="count">(%s)</span>',
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
		'Move to Trash' => 'Pārvietot uz atkritni',
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
		'Delivered' => 'Noslēgts',
		'Completed' => 'Noslēgts',
		'Delivery Draft' => 'Piegādes melnraksts',
		'Delivery draft' => 'Piegādes melnraksts',
		'Preparing delivery' => 'Piegādes melnraksts',
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
	$built = apply_filters( 'efpic_latvian_translations', $built );
	$map   = $built;

	return $map;
}

/**
 * Extra Latvian strings (admin filters, history, menu, import, branding).
 *
 * @param array $map Existing map.
 * @return array
 */
function efpic_latvian_translations_extra( $map ) {
	$extra = array(
		'Copy Filenames %s' => 'Kopēt nosaukumus %s',
		'Copy %s Filename' => 'Kopēt %s failu',
		'Selected by all' => 'Atlasījuši visi',
		'Selected at least once' => 'Atlasīta vismaz reizi',
		'Selected by %s' => 'Atlasījis: %s',
		'Not selected' => 'Neatlasītas',
		'All' => 'Visas',
		'Thumbnail' => 'Sīktēls',
		'File' => 'Fails',
		'Comments' => 'Komentāri',
		'has comment' => 'ar komentāru',
		'comments' => 'komentāri',
		'Toggle Comments' => 'Komentāri',
		'Add Comment' => 'Pievienot komentāru',
		'Proof' => 'Apstiprinājums',
		'Download' => 'Lejupielādēt',
		'Reopen' => 'Atvērt no jauna',
		'Remove…' => 'Noņemt…',
		'Remove...' => 'Noņemt…',
		'View' => 'Skatīt',
		'Message' => 'Ziņa',
		'No events yet.' => 'Vēl nav notikumu.',
		'All images' => 'Visas bildes',
		'Selected images only' => 'Tikai atlasītās',
		'Unselected images only' => 'Tikai neatlasītās',
		'You are about to remove this client' => 'Tu gatavojies noņemt šo klientu',
		'All selections by this client will be deleted. This cannot be undone.' => 'Visas šī klienta atlases tiks dzēstas. To nevarēs atsaukt.',
		'Are you sure, you want to remove this client?' => 'Vai tiešām vēlies noņemt šo klientu?',
		'Yes, remove client' => 'Jā, noņemt klientu',
		'The client %s was removed from the collection.' => 'Klients %s noņemts no kolekcijas.',
		'%s is already a client of this collection.' => '%s jau ir šīs kolekcijas klients.',
		'Sent to client(s)' => 'Nosūtīts klientam(-iem)',
		'Sent to additional client' => 'Nosūtīts papildu klientam',
		'New client registered' => 'Reģistrēts jauns klients',
		'Removed client' => 'Klients noņemts',
		'Approved by client' => 'Klients apstiprināja',
		'Reopened for client' => 'Atvērts klientam no jauna',
		'Reopened' => 'Atvērts no jauna',
		'Reverted to draft' => 'Atgriezts melnrakstā',
		'Reverted to delivery draft' => 'Atgriezts piegādes melnrakstā',
		'Closed manually' => 'Aizvērts manuāli',
		'Show Download History' => 'Rādīt lejupielāžu vēsturi',
		'Hide Download History' => 'Slēpt lejupielāžu vēsturi',
		'Date/Time' => 'Datums/laiks',
		'Name' => 'Vārds',
		'Social links' => 'Sociālie tīkli',
		'Preparing Delivery' => 'Sagatavo piegādi',
		'Delivery published' => 'Piegāde publicēta',
		'Last modified' => 'Pēdējās izmaiņas',
		'Images updated' => 'Bildes atjauninātas',
		'Collections' => 'Kolekcijas',
		'Collection' => 'Kolekcija',
		'All Collections' => 'Visas kolekcijas',
		'Edit Collection' => 'Rediģēt kolekciju',
		'View Collection' => 'Skatīt kolekciju',
		'Search Collections' => 'Meklēt kolekcijas',
		'No Collection Found' => 'Kolekcija nav atrasta',
		'No Collection Found in Trash' => 'Miskastē kolekcija nav atrasta',
		'Parent Collection' => 'Vecākkolekcija',
		'Filter collections list' => 'Filtrēt kolekciju sarakstu',
		'Collections list navigation' => 'Kolekciju saraksta navigācija',
		'Collections list' => 'Kolekciju saraksts',
		'Title' => 'Nosaukums',
		'Clients' => 'Klienti',
		'Expiration' => 'Derīgums',
		'Last Modified' => 'Pēdējās izmaiņas',
		'Actions' => 'Darbības',
		'This collection has been delivered to the client.' => 'Šī kolekcija ir piegādāta klientam.',
		'This collection is a delivery draft.' => 'Šī kolekcija ir piegādes melnraksts.',
		'This collection is open.' => 'Šī kolekcija ir atvērta.',
		'This collection is closed.' => 'Šī kolekcija ir aizvērta.',
		'This collection is a draft, which means it cannot be publicly accessed' => 'Šī kolekcija ir melnraksts un nav publiski pieejama',
		'This collection is in the trash. You can either restore or permanently delete it.' => 'Šī kolekcija ir miskastē. Vari atjaunot vai neatgriezeniski dzēst.',
		'(no title)' => '(bez nosaukuma)',
		'Import / Upload Images' => 'Importēt / augšupielādēt bildes',
		'Import' => 'Imports',
		'Import images right from your web server.' => 'Importē bildes tieši no web servera.',
		'Import from folder' => 'Importēt no mapes',
		'Import Images' => 'Importēt bildes',
		'Importing' => 'Importē',
		'Import successful.' => 'Imports veiksmīgs.',
		'Import canceled.' => 'Imports atcelts.',
		'Upload images' => 'Augšupielādēt bildes',
		'efpic Pro' => 'EFPIC Pro',
		'EFPIC Pro' => 'EFPIC Pro',
		'EFPIC Pro is active' => 'EFPIC Pro ir aktīvs',
		'Pro %1$s · Core %2$s' => 'Pro %1$s · Core %2$s',
		'Professional features for client galleries are enabled. Configure them below or in a collection.' => 'Profesionālās klientu galeriju funkcijas ir ieslēgtas. Konfigurē zemāk vai kolekcijā.',
		'Brand customize' => 'Zīmola pielāgošana',
		'Logo, colors, fonts and site title in client galleries.' => 'Logo, krāsas, fonti un vietnes nosaukums klientu galerijās.',
		'Open Design settings' => 'Atvērt Dizaina iestatījumus',
		'Watermark protection' => 'Ūdenszīmes aizsardzība',
		'Apply watermarks to collection images by default or per collection.' => 'Ūdenszīmes bildēm pēc noklusējuma vai katrā kolekcijā.',
		'Open Security settings' => 'Atvērt Drošības iestatījumus',
		'Block direct file URLs so images load only inside the gallery.' => 'Bloķē tiešos failu URL — bildes tikai galerijā.',
		'Collection expiration' => 'Kolekcijas termiņš',
		'Automatic expiry dates for collections after they are sent.' => 'Automātisks termiņš pēc nosūtīšanas.',
		'Open General settings' => 'Atvērt Vispārīgos iestatījumus',
		'Email message templates' => 'E-pasta ziņu veidnes',
		'Choose message template' => 'Izvēlies ziņas veidni',
		'Reusable email templates when sending collections to clients.' => 'Atkārtoti izmantojamas e-pasta veidnes klientiem.',
		'Open Email settings' => 'Atvērt E-pasta iestatījumus',
		'Client access' => 'Klientu piekļuve',
		'Magic login link so clients can open their galleries by email.' => 'Ieejas saite e-pastā, lai klients atvērtu savas galerijas.',
		'Add Client Access block to a page' => 'Pievienot Klientu piekļuves bloku lapai',
		'Add Client Access page' => 'Pievienot Klientu piekļuves lapu',
		'Comments & markers' => 'Komentāri un marķieri',
		'Clients can mark and comment on images in a collection.' => 'Klienti var marķēt un komentēt bildes kolekcijā.',
		'Open collections' => 'Atvērt kolekcijas',
		'Selection goals' => 'Atlases mērķi',
		'Set how many images a client should select.' => 'Norādi, cik bildes klientam jāatlasa.',
		'Image download' => 'Bilžu lejupielāde',
		'Allow clients to download images from a collection.' => 'Ļauj klientiem lejupielādēt bildes no kolekcijas.',
		'FTP / folder import' => 'FTP / mapes imports',
		'Import images from the uploads/efpic/import folder into a collection.' => 'Importē bildes no uploads/efpic/import mapes kolekcijā.',
		'Final delivery' => 'Gala piegāde',
		'Deliver finished images to the client after selection is done.' => 'Piegādā gatavās bildes klientam pēc atlases.',
		'All EFPIC settings' => 'Visi EFPIC iestatījumi',
		'Core and Pro options in one place.' => 'Core un Pro opcijas vienā vietā.',
		'Open Settings' => 'Atvērt Iestatījumus',
		'Client permissions' => 'Klienta tiesības',
		'Image download' => 'Bilžu lejupielāde',
		'Selection goal' => 'Atlases mērķis',
		'Comments & markers' => 'Komentāri un marķieri',
		'Watermark' => 'Ūdenszīme',
		'On — ZIP (all images + selected)' => 'Ieslēgts — ZIP (visas + atlasītās)',
		'On — ZIP (%s)' => 'Ieslēgts — ZIP (%s)',
		'On — ZIP (none enabled)' => 'Ieslēgts — ZIP (neviena nav ieslēgta)',
		'On — external URL (%s)' => 'Ieslēgts — ārējais URL (%s)',
		'On — external URL' => 'Ieslēgts — ārējais URL',
		'all images' => 'visas bildes',
		'selected images' => 'atlasītās bildes',
		'Download all images' => 'Lejupielādēt visas bildes',
		'Download selected images' => 'Lejupielādēt atlasītās bildes',

		// Collection Options (admin)
		'Enable image download' => 'Ieslēgt bilžu lejupielādi',
		'Automatically create .zip file from collection' => 'Automātiski izveidot .zip no kolekcijas',
		'Use external URL' => 'Izmantot ārējo URL',
		'Learn more' => 'Uzzināt vairāk',
		'Not supported.' => 'Netiek atbalstīts.',
		'Set Selection Goal' => 'Iestatīt atlases mērķi',
		'The client needs to select' => 'Klientam jāatlasa',
		'exactly' => 'precīzi',
		'at least' => 'vismaz',
		'a maximum of' => 'ne vairāk kā',
		'in the range of' => 'diapazonā no',
		'In Price' => 'Cenā iekļauts',
		'to' => 'līdz',
		'image(s)' => 'bilde(s)',
		'Extra image cost' => 'Papildu bildes cena',
		'Enable Comments & Markers' => 'Ieslēgt komentārus un marķierus',
		'Enable Comments &amp; Markers' => 'Ieslēgt komentārus un marķierus',
		'Apply watermark to new images' => 'Piemērot ūdenszīmi jaunām bildēm',
		'Collection expires' => 'Kolekcijai beidzas termiņš',
		'Expiration date:' => 'Beigu datums:',
		'Expire after %d day' => 'Beigt derīgumu pēc %d dienas',
		'Expire after %d days' => 'Beigt derīgumu pēc %d dienām',
		'Saving' => 'Saglabā…',
		'Saved' => 'Saglabāts',
		'Please enter a download URL.' => 'Lūdzu, ievadi lejupielādes URL.',
		'You may change this for each collection before uploading images.' => 'Var mainīt katrā kolekcijā pirms bilžu augšupielādes.',

		'Client permissions updated' => 'Klienta tiesības atjauninātas',
		'Selected image download is not enabled for this collection.' => 'Atlasīto bilžu lejupielāde šai kolekcijai nav ieslēgta.',
		'Delivery screen: permissions are set on the selection collection before delivery.' => 'Piegādes skatā: tiesības iestata atlases kolekcijā pirms piegādes.',
		'To change these options, use Edit (reopens the collection as a draft).' => 'Lai mainītu, spied Rediģēt (kolekcija atveras kā melnraksts).',
		'Exactly %d image(s)' => 'Precīzi %d bilde(s)',
		'At least %d image(s)' => 'Vismaz %d bilde(s)',
		'Maximum %d image(s)' => 'Maksimums %d bilde(s)',
		'Range %1$d–%2$d image(s)' => 'Diapazons %1$d–%2$d bilde(s)',
		'In Price — %1$d included, extra %2$s' => 'Cenā iekļauts — %1$d iekļautas, papildu %2$s',
		'On' => 'Ieslēgts',
		'Off' => 'Izslēgts',
		'EFPIC' => 'EFPIC',
		'efpic' => 'EFPIC',
		'Theme' => 'Tēma',
		'Dark' => 'Tumša',
		'Light' => 'Gaiša',
		'Email' => 'E-pasts',
		'Client language' => 'Klienta valoda',
		'Latvian' => 'Latviešu',
		'English' => 'Angļu',
		'Language for all messages the client sees in the gallery and in emails for this collection.' => 'Valoda visiem ziņojumiem, ko klients redz galerijā un e-pastos šai kolekcijai.',
		'Defaults to the collection author\'s email address.' => 'Noklusējumā — kolekcijas autora e-pasts.',
		'new' => 'jauns',
		'Dismiss this notice.' => 'Aizvērt paziņojumu.',
		'Client' => 'Klients',
		'Your delivery is ready! Make sure to send the link to your client:' => 'Piegāde gatava! Nosūti saiti klientam:',
		'The collection is ready! Make sure to send the link to your client:' => 'Kolekcija gatava! Nosūti saiti klientam:',
		'efpic Client Access' => 'EFPIC klientu piekļuve',
		'efpic Settings' => 'EFPIC iestatījumi',
		'General efpic settings.' => 'Vispārīgie EFPIC iestatījumi.',
		'Show efpic logo' => 'Rādīt EFPIC logo',
		'Spread some efpic love, by displaying our logo in collections and efpic related emails.' => 'Rādīt EFPIC logo kolekcijās un e-pastos.',
		'efpic email settings.' => 'EFPIC e-pasta iestatījumi.',
		'<strong>Please note:</strong> efpic will <strong>NOT</strong> send an email. Make sure to copy and send the link to your client manually.' => '<strong>Uzmanību:</strong> EFPIC <strong>NESŪTĪS</strong> e-pastu. Nokopē un nosūti saiti klientam pats.',
		'efpic Collections List' => 'EFPIC kolekciju saraksts',
		'Download a CSV list of all efpic collections (title, status, clients, image count, URL).' => 'Lejupielādē visu EFPIC kolekciju CSV sarakstu (nosaukums, statuss, klienti, bilžu skaits, URL).',
		'When enabled, collection images can only be loaded from within an efpic gallery (not via a direct file URL).' => 'Kad ieslēgts, kolekcijas bildes ielādējas tikai no EFPIC galerijas (nevis ar tiešu faila URL).',
		'Switch between different image processors to improve performance when uploading/importing images.<br /><strong>Please be aware, that this affects all media uploads on your site, not just efpic images.</strong>' => 'Maini attēlu procesoru, lai uzlabotu augšupielādi.<br /><strong>Ietekmē visas vietnes mediijas, ne tikai EFPIC.</strong>',
		'Use random URLs for efpic collections' => 'Nejauši URL kolekcijām',
		'A random password will automatically assigned to all new efpic collections.' => 'Jaunām kolekcijām automātiski tiks piešķirta nejauša parole.',
		'If a client started selecting images but did not finally approve the collection, efpic will automatically send a reminder after 24 hours.' => 'Ja klients sāka atlasi, bet neapstiprināja, pēc 24 h tiks nosūtīts atgādinājums.',
		'efpic Collection base' => 'EFPIC kolekciju bāze',
		'Social links in galleries' => 'Sociālie tīkli galerijās',
		'Show your social profile links in client galleries. You can turn them off for individual collections.' => 'Rādi savu sociālo profilu saites klientu galerijās. Katrai kolekcijai vari atsevišķi izslēgt.',
		'Social links' => 'Sociālie tīkli',
		'Show photographer social links in this gallery.' => 'Rādīt fotogrāfa sociālo tīklu saites šajā galerijā.',
		'Full URL to your %s profile (leave empty to hide).' => 'Pilna URL uz tavu %s profilu (tukšs = nerādīt).',
		'Instagram' => 'Instagram',
		'Facebook' => 'Facebook',
		'TikTok' => 'TikTok',
		'YouTube' => 'YouTube',
		// Global settings (1.0.50)
		'Choose how your collections will be displayed:' => 'Izvēlies, kā rādīt kolekcijas:',
		'New collections will be set to expire %d day after being sent.' => 'Jaunās kolekcijas beigs derīgumu %d dienu pēc nosūtīšanas.',
		'New collections will be set to expire %d days after being sent.' => 'Jaunās kolekcijas beigs derīgumu %d dienas pēc nosūtīšanas.',
		'Disabled by WP Mail SMTP\'s "Force From Email" setting. %sChange%s' => 'Atspējots ar WP Mail SMTP „Force From Email”. %sMainīt%s',
		'Disabled by WP Mail SMTP\'s "Force From Name" setting. %sChange%s' => 'Atspējots ar WP Mail SMTP „Force From Name”. %sMainīt%s',
		'Debug info can be found in %sTools > Site Health%s.' => 'Atkļūdošanas info: %sRīki > Vietnes veselība%s.',
		'When submitting a %ssupport request%s, please use the button below to include your site info.' => 'Sūtot %satbalsta pieprasījumu%s, iekļauj vietnes info ar pogu zemāk.',
		'After approving a collection' => 'Pēc kolekcijas apstiprināšanas',
		'Thank you!' => 'Paldies!',
		'The collection has been approved and the photographer has been notified.' => 'Kolekcija apstiprināta un fotogrāfs informēts.',
		'You can now close this browser window.' => 'Tagad vari aizvērt šo pārlūkprogrammas logu.',
		'Time to redirect' => 'Novirzīšanas laiks',
		'Set a time or disable' => 'Iestati laiku vai izslēdz',
		'No redirect' => 'Bez novirzīšanas',
		'Immediately – don\'t show approval message' => 'Uzreiz – nerādīt apstiprinājuma ziņu',
		'5 seconds' => '5 sekundes',
		'10 seconds' => '10 sekundes',
		'After approval message' => 'Ziņa pēc apstiprināšanas',
		'Displayed, once the client has approved a collection.' => 'Tiek rādīta, kad klients apstiprinājis kolekciju.',
		'Target URL' => 'Mērķa URL',
		'Where the client is redirected after approving a collection. Defaults to %s' => 'Kur klients tiek novirzīts pēc apstiprināšanas. Noklusējums: %s',
		'Logo' => 'Logo',
		'Show site title' => 'Rādīt vietnes nosaukumu',
		'Display the site title &quot;%s&quot; above the collection title.' => 'Rādīt vietnes nosaukumu „%s” virs kolekcijas nosaukuma.',
		'Color' => 'Krāsa',
		'Define primary color' => 'Galvenā krāsa',
		'Define the color that is used for buttons and highlighting selected images.' => 'Krāsa pogām un atlasīto bilžu izcelšanai.',
		'Please choose a valid color.' => 'Lūdzu, izvēlies derīgu krāsu.',
		'Font' => 'Fonts',
		'Font:' => 'Fonts:',
		'Replace Logo' => 'Aizstāt logo',
		'Remove Logo' => 'Noņemt logo',
		'Upload Logo' => 'Augšupielādēt logo',
		'Use standard font' => 'Standarta fonts',
		'Use custom/external font' => 'Pielāgots / ārējs fonts',
		'Select font' => 'Izvēlies fontu',
		'The displayed font may vary, depending on whether it is installed on a user\'s system.' => 'Fonts var atšķirties atkarībā no tā, vai tas ir instalēts lietotāja sistēmā.',
		'efpic default' => 'EFPIC noklusējums',
		'We support <a href="%1$s">Google Fonts</a> and <a href="%2$s">Adobe Fonts (Typekit)</a>. Visit the efpic <a href="%3$s">FAQs</a> to see some usage examples.' => 'Atbalstām <a href="%1$s">Google Fonts</a> un <a href="%2$s">Adobe Fonts (Typekit)</a>. Skati EFPIC <a href="%3$s">BUJ</a> ar piemēriem.',
		'External Font Name' => 'Ārējā fonta nosaukums',
		'Enter the <code>font-family</code> value.' => 'Ievadi <code>font-family</code> vērtību.',
		'Embed Code' => 'Iegulšanas kods',
		'A reference to an external stylesheet or javascript.' => 'Saite uz ārējo stila failu vai JavaScript.',
		'We could not recognize the font embed code you entered. Please check our %sFAQs%s for more information on how to use external fonts.' => 'Neizdevās atpazīt fonta iegulšanas kodu. Skati %sBUJ%s par ārējiem fontiem.',
		'Define what will be displayed as image title by dragging properties onto the field below' => 'Velc īpašības uz lauku zemāk, lai noteiktu bildes virsrakstu',
		'number' => 'numurs',
		'aperture' => 'diafragma',
		'camera' => 'kamera',
		'copyright' => 'autortiesības',
		'file extension' => 'faila paplašinājums',
		'filename' => 'faila nosaukums',
		'focal length' => 'fokusa attālums',
		'iso' => 'iso',
		'shutter speed' => 'aizslēga ātrums',
		'title' => 'virsraksts',
		'keywords' => 'atslēgvārdi',
		'description' => 'apraksts',
		'Remove title part' => 'Noņemt virsraksta daļu',
		'Image Size' => 'Bildes izmērs',
		'Set default thumbnail image size' => 'Noklusējuma sīktēla izmērs',
		'small' => 'mazs',
		'medium' => 'vidējs',
		'large' => 'liels',
		'Email Templates' => 'E-pasta veidnes',
		'Email templates' => 'E-pasta veidnes',
		'Create/edit messsage templates. Set one as your default &#x2605;.' => 'Izveido/rediģē ziņu veidnes. Vienu iestati kā noklusējumu ★.',
		'Default Message' => 'Noklusējuma ziņa',
		'Template Name' => 'Veidnes nosaukums',
		'Save Message' => 'Saglabāt ziņu',
		'Add New Message' => 'Pievienot jaunu ziņu',
		'You have not saved any message templates yet' => 'Vēl nav saglabāta neviena ziņu veidne',
		'Delete' => 'Dzēst',
		'Require email address during client registration' => 'Pieprasīt e-pastu klienta reģistrācijā',
		'If enabled, clients are required to provide an email address during registration. Otherwise the email field will be optional.' => 'Ja ieslēgts, klientam reģistrācijā jānorāda e-pasts. Citādi e-pasta lauks ir neobligāts.',
		'Disables context menus in efpic collections.<br />Please be aware that this is not an effective method of protection. <a href="%s">Read more</a>' => 'Atslēdz konteksta izvēlnes EFPIC kolekcijās.<br />Ņem vērā: tas nav efektīva aizsardzība. <a href="%s">Lasīt vairāk</a>',
		'Replace Watermark' => 'Aizstāt ūdenszīmi',
		'Remove Watermark' => 'Noņemt ūdenszīmi',
		'Set Watermark' => 'Iestatīt ūdenszīmi',
		'Top Left' => 'Augšā pa kreisi',
		'Top Right' => 'Augšā pa labi',
		'Middle Left' => 'Vidū pa kreisi',
		'Middle Right' => 'Vidū pa labi',
		'Bottom Left' => 'Apakšā pa kreisi',
		'Bottom Right' => 'Apakšā pa labi',
		'Proportional' => 'Proporcionāli',
		'The watermark will be scaled to the chosen percentage of the original image size.' => 'Ūdenszīme tiek mērogota līdz izvēlētajam % no oriģinālā attēla izmēra.',
		'Fill' => 'Aizpildīt',
		'No scaling. The watermark will be centered and in its original size. Best if your watermark should cover the whole image.' => 'Bez mērogošanas. Ūdenszīme centrā oriģinālajā izmērā. Labi, ja jānosedz viss attēls.',
		'File Handling' => 'Failu apstrāde',
		'Choose what will happen to a source folder after it is successfully imported into a collection:' => 'Ko darīt ar avota mapi pēc veiksmīga importa kolekcijā:',
		'Do nothing' => 'Nedarīt neko',
		'The source folder will stay inside the <code>import</code> folder. You can even import the contained images again (and again).' => 'Avota mape paliek <code>import</code> mapē. Bildes var importēt atkārtoti.',
		'Move files' => 'Pārvietot failus',
		'The source folder will be moved to the <code>_imported</code> folder. You need to clean up this folder regularly, so you don\'t fill up your web hosting space!' => 'Avota mape tiks pārvietota uz <code>_imported</code>. Regulāri notīri to, lai neaizpildītu hostingu!',
		'Delete files' => 'Dzēst failus',
		'The source folder and all files it contains will be deleted. <strong>This cannot be undone!</strong>' => 'Avota mape un visi faili tiks dzēsti. <strong>To nevarēs atsaukt!</strong>',
		'Website' => 'Mājaslapa',
		'Bulk edit' => 'Masveida rediģēšana',
		'Bulk Edit' => 'Masveida rediģēšana',
	);

	return array_merge( $map, $extra );
}
add_filter( 'efpic_latvian_translations', 'efpic_latvian_translations_extra', 5 );

/**
 * Whether a WordPress core (default domain) string should get an EFPIC LV override.
 *
 * @param string $text Original English string.
 * @return bool
 */
function efpic_is_latvian_wp_core_string( $text ) {
	static $allowed = array(
		'Bulk edit' => true,
		'Bulk Edit' => true,
	);
	return isset( $allowed[ $text ] );
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
	$is_efpic = ( 'efpic' === $domain || 'efpic-pro' === $domain );
	$is_wp    = ( 'default' === $domain && efpic_is_latvian_wp_core_string( $text ) );
	if ( ! $is_efpic && ! $is_wp ) {
		return $translation;
	}
	if ( ! efpic_should_use_latvian_strings() ) {
		return $translation;
	}

	$map = efpic_get_latvian_translations();
	if ( isset( $map[ $text ] ) ) {
		return $map[ $text ];
	}

	// Brand display name: efpic -> EFPIC (keep URLs like efpic.io untouched).
	if ( $is_efpic && ( 'efpic' === $text || 'efpic' === $translation ) ) {
		return 'EFPIC';
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
