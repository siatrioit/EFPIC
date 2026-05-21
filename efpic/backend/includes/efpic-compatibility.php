<?php
/**
 * Fix compatibility issues with third parties
 *
 * @since 1.1.0
 * @since 2.3.5 moved fixes into seperate files
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require EFPIC_PATH . 'backend/includes/compatibility/plugin-autoptimize.php';
require EFPIC_PATH . 'backend/includes/compatibility/plugin-jetpack.php';
require EFPIC_PATH . 'backend/includes/compatibility/plugin-media-library-assistant.php';
require EFPIC_PATH . 'backend/includes/compatibility/plugin-nextgen-gallery.php';
require EFPIC_PATH . 'backend/includes/compatibility/plugin-rankmath-seo.php';
require EFPIC_PATH . 'backend/includes/compatibility/plugin-real-media-library.php';
require EFPIC_PATH . 'backend/includes/compatibility/plugin-seo-framework.php';
require EFPIC_PATH . 'backend/includes/compatibility/plugin-sg-cachepress.php';
require EFPIC_PATH . 'backend/includes/compatibility/plugin-wordpress-seo.php';