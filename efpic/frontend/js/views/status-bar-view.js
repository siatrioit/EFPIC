var efpic = efpic || {};

/**
 * Replace %1$s / %2$s placeholders in translated templates.
 */
efpic.formatPriceString = function( template, first, second ) {
	if ( ! template ) {
		return '';
	}
	return template.replace( '%1$s', first ).replace( '%2$s', second );
};

efpic.getInPriceDisplay = function( appstate, selected ) {
	var display = {
		in_price_package: '',
		in_price_extra_line: ''
	};

	if ( typeof appstate.attributes.selection_restriction === 'undefined' || appstate.attributes.selection_restriction.restriction !== 'in price' ) {
		return display;
	}

	var included = parseInt( appstate.attributes.selection_restriction.from, 10 ) || 0;
	var unit_cost = parseFloat( appstate.attributes.selection_restriction.extra_image_cost ) || 0;

	display.in_price_package = efpic.formatPriceString( appstate.get( 'in_price_package_tpl' ), included, '' );

	if ( selected > included ) {
		var extra_count = selected - included;
		var extra_total = ( extra_count * unit_cost ).toFixed( 2 );
		var extra_tpl = ( 1 === extra_count ) ? appstate.get( 'in_price_extra_one_tpl' ) : appstate.get( 'in_price_extra_many_tpl' );
		var extra_images = efpic.formatPriceString( extra_tpl, extra_count, '' );
		var extra_cost = efpic.formatPriceString( appstate.get( 'in_price_extra_cost_tpl' ), extra_total, '' );
		display.in_price_extra_line = extra_images + '. ' + extra_cost;
	}

	return display;
};

/**
 * Template data for collection info modal.
 *
 * @param {Object} appstate Backbone app state.
 * @param {number} selected Selected image count.
 * @param {number} imagecount Total images in gallery.
 * @return {Object}
 */
efpic.getCollectionInfoTemplateData = function( appstate, selected, imagecount ) {
	var has_in_price = (
		typeof appstate.attributes.selection_restriction !== 'undefined' &&
		appstate.attributes.selection_restriction.restriction === 'in price' &&
		appstate.attributes.selection_restriction.selection_option === true
	);

	var data = {
		has_in_price: has_in_price,
		panel_images_label: appstate.get( 'info_panel_images_in_gallery_label' ) || appstate.get( 'info_panel_images_label' ) || '',
		panel_extra_processing_label: appstate.get( 'info_panel_extra_processing_label' ) || '',
		panel_extra_payment_label: appstate.get( 'info_panel_extra_payment_label' ) || '',
		in_price_extra_count: 0,
		in_price_extra_total: '0.00',
		collection_description_html: appstate.get( 'collection_description_html' ) || ''
	};

	if ( has_in_price ) {
		var included = parseInt( appstate.attributes.selection_restriction.from, 10 ) || 0;
		var unit_cost = parseFloat( appstate.attributes.selection_restriction.extra_image_cost ) || 0;

		data.in_price_extra_count = Math.max( 0, selected - included );
		data.in_price_extra_total = ( data.in_price_extra_count * unit_cost ).toFixed( 2 );
	}

	return data;
};

/**
 * Template data for lightbox in-price display.
 *
 * @param {Backbone.View} view Lightbox view with collection and appstate.
 * @return {Object}
 */
efpic.getLightboxInPriceTemplateData = function( view ) {
	var selected = view.collection.where( { selected: true } ).length;
	var all = view.collection.length;
	var inPrice = efpic.getInPriceDisplay( view.appstate, selected );
	var has_in_price = (
		typeof view.appstate.attributes.selection_restriction !== 'undefined' &&
		view.appstate.attributes.selection_restriction.restriction === 'in price' &&
		view.appstate.attributes.selection_restriction.selection_option === true
	);

	return {
		selected_count: selected,
		all_count: all,
		has_in_price: has_in_price,
		in_price_package: inPrice.in_price_package,
		in_price_extra_line: inPrice.in_price_extra_line
	};
};

efpic.StatusBarView = Backbone.View.extend({

	model: efpic.singleImage,

	template: _.template( jQuery( "#efpic-status-bar" ).html() ),

	tagName: 'header',

	className: 'efpic-status-bar',

	initialize: function( options ) {
		this.collection = options.collection;
		this.appstate = options.model;

		this.update();
		this.listenTo( this.collection, 'change', function() {
			this.update();
		} );

		// Key bindings
		_.bindAll( this , 'keyAction' );
		$( document ).on( 'keydown', this.keyAction );
	},

	events: {
		'click .efpic-save': 'saveSelection',
		'click .efpic-filter-selected': 'filterSelected',
		'click .efpic-filter-unselected': 'filterUnselected',
		'click .efpic-filter-reset': 'filterReset',
		'click .efpic-selection-alert': 'toggleRestrictionAlert',
		'keydown': 'keyAction'
	},

	update: function() {
		var all = this.collection.length;
		var selected = this.collection.where({selected: true}).length;
		var restrictionWarning = this.restrictionWarning();
		var animation = 'animation-off';
		if ( selected > 1 ) {
			this.appstate.animation = false;
			
		}
		if ( this.appstate.animation != false ) {
			animation = '';
		}

		var inPriceDisplay = efpic.getInPriceDisplay( this.appstate, selected );

		var statusbar = this.template({
			all: all,
			selected: selected,
			appstate: this.appstate,
			zip: this.appstate.get( 'zip' ),
			selection_restriction: this.appstate.get('selection_restriction'),
			restriction_warning: restrictionWarning,
			animation: animation,
			in_price_package: inPriceDisplay.in_price_package,
			in_price_extra_line: inPriceDisplay.in_price_extra_line
		});
		this.$el.html( statusbar );
	},

	filterSelected: function() {
		$( '.efpic-error' ).remove();

		this.appstate.set( 'filter', 'selected' );
		$( 'body' ).removeClass( 'filter-unselected' ).addClass( 'filter-selected' );

		if ( this.collection.where({selected: true}).length <= 0 ) {
			$( '.efpic-gallery' ).append('<div class="efpic-error"><div class="error-inner"><h2>' + this.appstate.get( 'error_msg_filter_selected' ) + '</h2><p><a class="error-filter-reset" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg>' + this.appstate.get( 'reset_filter_msg' ) + '</span></p></div></div>');
		}

		efpic.GalleryView.prototype.lazyLoad();
	},

	filterUnselected: function() {
		$( '.efpic-error' ).remove();

		this.appstate.set( 'filter', 'unselected' );
		$( 'body' ).removeClass( 'filter-selected' ).addClass( 'filter-unselected' );

		if ( this.collection.where({selected: true}).length >= this.collection.length ) {
			$( '.efpic-gallery' ).append('<div class="efpic-error"><div class="error-inner"><h2>' + this.appstate.get( 'error_msg_filter_unselected' ) + '</h2><p><a class="error-filter-reset" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg>' + this.appstate.get( 'reset_filter_msg' ) + '</span></p></div></div>');
		}

		efpic.GalleryView.prototype.lazyLoad();
	},

	filterReset: function() {
		this.appstate.unset( 'filter' );
		$( 'body' ).removeClass( 'filter-selected filter-unselected' );
		$( '.efpic-error' ).remove();

		efpic.GalleryView.prototype.lazyLoad();
	},

	saveSelection: function() {
		// Check if the client needs to register first
		var router = new Backbone.Router();
		var temp = jQuery.parseJSON( appstate );
		if ( temp.ident == null ) {
			router.navigate( 'register', {trigger: true} );
			return;
		}
		// Hide save button, show spinner
		$( '.efpic-save' ).hide();
		$( '<div class="efpic-saving">loading</div>' ).insertBefore( '.efpic-save' );

		var selection = _.map( this.collection.where({selected: true}), function( s ){ return s.attributes.imageID; });
 
		var allMarkers = {};

		var temp = this.collection.map( function( model ){
			var id = model.get( 'imageID' );
			var markers = model.get( 'markers' );

			if ( '' != markers && null != markers ) {
				if ( 0 < Object.keys( markers ).length ) {
					allMarkers['id_'+id] = markers;
				}
			}
			markers = '';
		});

		var allStars = {};

		var temp_stars = this.collection.map( function( model ){
			var id = model.get( 'imageID' );
			var stars = model.get( 'stars' );

			if ( '' != stars && null != stars ) {
				if ( 0 < Object.keys( stars ).length ) {
					allStars['id_'+id] = stars;
				}
			}
			stars = '';
		});

		// Send AJAX request
		$.post( this.appstate.get( 'ajaxurl' ), {
			action: 'efpic_send_selection',
			security: this.appstate.get( 'nonce' ),
			postid: this.appstate.get( 'postid' ),
			ident: this.appstate.get( 'ident' ),
			selection: selection,
			markers: allMarkers,
			stars: allStars,
			intent: 'temp'

		}, function( response ) {

			// Display response as overlay
			var overlayclass = '';
			if ( response.success == true ) {
				overlayclass = ' success';
			} else {
				overlayclass = ' fail';
			}

			$( '.efpic-collection' ).append('<div class="overlay'+ overlayclass +'"><div class="message"><p>' + response.data.message + '</p><p><a class="efpic-button small primary js-close-message">' + response.data.button_text + '</a></p></div></div>');

			// Remove spinner, show save button
			$( '.efpic-saving' ).remove();
			$( '.efpic-save' ).show();

		}).fail( _.bind( function() {
			$( '.efpic-collection' ).append('<div class="overlay fail"><div class="message"><p>' + this.appstate.get( 'request_failed_error' ) + '</p><p><a class="efpic-button small primary js-close-message" href="#">' + this.appstate.get( 'button_ok' ) + '</a></p></div></div>');

			// Remove spinner, show save button
			$( '.loading' ).remove();
			$( '.efpic-save' ).show();
		}, this ) );

	},

	toggleRestrictionAlert: function( e ) {
		document.querySelector( '.efpic-selection-alert__explanation' ).classList.toggle( 'is-visible' );
	},

	keyAction: function( e ) {
		// ESC key
		if ( e.keyCode == 27 ) {
			var selectionAlert = document.querySelector( '.efpic-selection-alert__explanation' );
			if ( selectionAlert != null ) {
				selectionAlert.classList.remove( 'is-visible' );
			}
		}
	},

	restrictionWarning: function() {
		if ( typeof this.appstate.attributes.selection_restriction !== 'undefined' ) {
			var restriction = this.appstate.attributes.selection_restriction.restriction;
			var from = this.appstate.attributes.selection_restriction.from;
			var to = this.appstate.attributes.selection_restriction.to;
			var num = this.collection.where({selected: true}).length;
		}

		if ( num == 0 ) {
			return false;
		}
		else if ( restriction == 'in price' ) {
			return false;
		}
		else if ( ( restriction == 'at least' && num < from ) || ( restriction == 'a maximum of' && num > from ) || ( restriction == 'exactly' && num != from ) || ( restriction == 'in the range of' && ( num < from || num > to ) ) ) {
			return true;
		}

		return false;
	},

	remove: function() {
		// Unbind keydown
		$( document ).off( 'keydown', this.keyAction );

		// Completely remove this view
		Backbone.View.prototype.remove.call( this );
	}

});