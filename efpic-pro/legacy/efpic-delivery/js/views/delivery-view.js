var efpic = efpic || {};

efpic.DeliveryView = Backbone.View.extend({

	tagName: 'div',
	className: 'efpic-delivery',
	model: efpic.appState,
	template: _.template( jQuery( "#efpic-delivery" ).html() ),

	initialize: function( collection, appstate ) {
		this.appstate = appstate;
		this.collection = collection;

		efpic.collection = collection;
	},

	render: function() {
		this.delivery();
		return this;
	},

	delivery: function() {
		// Empty everything
		this.$el.empty();

		if ( this.collection.length > 0 ) {

			// Map collection to html list items
			var deliveryitems = this.collection.map( this.renderDeliveryItems, this );

			// Put them together in one string
			this.deliveryItems = deliveryitems.join( '' );

			// Define template
			var deliveryTemplate = this.template( this.appstate, this.deliveryItems );

			// Return template
			this.$el.html( deliveryTemplate );
			return this;
		}
		else if ( this.appstate.attributes.delivery_zip_url != '' ) {

			// Define template
			var deliveryTemplate = this.template( this.appstate );

			// Return template
			this.$el.html( deliveryTemplate );
			return this;
		}
		// If collection has no images (which should not be possible)
		else {
			this.$el.append( '<div class="efpic-error"><div class="error-inner">' + this.appstate.get( 'error_msg_no_imgs' ) + '</div></div>' );
		}

	},

	renderDeliveryItems: function( image ) {
		var title = image.get( 'title' );

		return '<li class="efpic-delivery-image"><div class="efpic-delivery-image-wrap"><img src="' + image.get( 'imagePath_small' ) + '" loading="lazy" /></div> <span class="efpic-delivery-image-title">' + Object.values( title ).join( ' ' ) + '</span> <a class="efpic-button small js-image-download" data-image="' + image.get( 'imageID' ) + '" href="' + image.get( 'imagePath_original' ) + '">' + this.appstate.get( 'i18n_delivery_single_download_button_label' ) + '</a><a class="efpic-image-download-link" id="js-image-download-' + image.get( 'imageID' ) + '" href="' + image.get( 'imagePath_original' ) + '" download>' + this.appstate.get( 'i18n_delivery_single_download_button_label' ) + '</a></li>';
	},

	events: {
		'click .js-zip-download': 'trackDownload',
		'click .js-image-download': 'trackDownload',
	},

	trackDownload: function( e ) {
		e.preventDefault();

		var image = e.target.dataset.image;
		var downloadType = ( image ) ? 'image' : 'zip';
		var url = e.target.href;

		// Send AJAX request
		$.post( this.appstate.get( 'ajaxurl' ), {
			action: 'efpic_track_download',
			security: this.appstate.get( 'nonce' ),
			postid: this.appstate.get( 'postid' ),
			download: downloadType,
			imageid: image,
			url: url,
		}, function( response ) {
			// Do nothing
		}).fail( function() {
			// Do nothing
		});

		// Initiate actual download
		if ( 'external' == this.appstate.get( 'delivery_option' ) ) {
			window.open( url, '_blank' );
		}
		else if ( downloadType == 'zip' ) {
			document.getElementById( 'efpic-main-download-link' ).click();
		}
		else if ( downloadType == 'image' ) {
			document.getElementById( 'js-image-download-' + image ).click();
		}
	},

	remove: function() {
		// Remove child views and binds
		this.off();
	},

});