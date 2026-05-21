/*
 * Extend quick edit status select box
 */
(function( $ ) {
	'use strict';

	$( 'select[name="_status"]' ).append( '<option value="delivery-draft">' + efpic_admin.delivery_draft_option_label + '</option>' + '</option><option value="delivered">' + efpic_admin.delivered_option_label + '</option>' );

})( jQuery );