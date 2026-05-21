<?php
/**
 * Brand & Customize
 *
 * Customize efpic appearance, message templaes, redirects and more.
 *
 * @since brand-customize (0.0.1)
 */
defined( 'EFPIC_PRO' ) OR exit;


/**
 * Initialize Brand & Customize.
 *
 * @since brand-customize (0.0.1)
 */
if ( ! function_exists( 'efpic_brand_customize' ) ) {
	// Include functions to customize collections in the front end
	require_once EFPIC_PRO_PATH . 'legacy/efpic-brand-customize/includes/customize-collection.php';

	// Include functions for ajax requests
	require_once EFPIC_PRO_PATH . 'legacy/efpic-brand-customize/includes/save-mail-messages.php';

	// Include functions for custom approval forms
	require_once EFPIC_PRO_PATH . 'legacy/efpic-brand-customize/includes/custom-approval-form.php';
}


/**
 * Replace send-selection backbone template.
 *
 * @since brand-customize (0.0.1)
 *
 * @param array $templates The front end backbone templates
 * @return array The filtered templates
 */
function efpic_bc_relaced_send_selection_template( $templates ) {
	$templates['send-selection'] = EFPIC_PRO_PATH . 'legacy/efpic-brand-customize/templates/send-selection.php';

	return $templates;
}

add_filter( 'efpic_load_backbone_templates', 'efpic_bc_relaced_send_selection_template', 10, 1 );


/**
 * Localize strings.
 *
 * @since brand-customize (1.4.0)
 *
 * @param array $strings Loclalized strings
 * @return array The filtered strings
 */
function efpic_brand_customize_localization_strings( $strings ) {
	$strings['link_text_edit'] = __( 'Edit', 'efpic-pro' );
	$strings['link_text_delete'] = __( 'Delete', 'efpic-pro' );
	$strings['button_text_save_message'] = __( 'Save Message', 'efpic-pro');
	$strings['button_text_cancel'] = __( 'Cancel', 'efpic-pro');
	/* translators: Message shown, when the user trys to save  a template without a name  */
	$strings['empty_template_name_hint'] = __( 'Please enter a template name', 'efpic-pro' );

	return $strings;
}

add_filter( 'efpic_localization_strings', 'efpic_brand_customize_localization_strings' );


/**
 * Add settings as action link.
 *
 * @since brand-customize (1.0.0)
 *
 * @param array $actions Plugin actions links
 * @return array Filtered actions
 */
function efpic_bc_plugin_action_links( $actions ) {
	$action = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=efpic-general' ), __( 'Settings', 'efpic-pro' ) );
	array_unshift( $actions, $action );

	return $actions;
}

add_filter( 'plugin_action_links_' . EFPIC_PRO_PLUGIN_BASENAME , 'efpic_bc_plugin_action_links', 10 );