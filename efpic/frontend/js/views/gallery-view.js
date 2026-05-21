var efpic = efpic || {};

efpic.GalleryView = Backbone.View.extend({

	tagName: 'div',
	className: 'efpic-gallery',
	model: efpic.appState,

	initialize: function( collection, appstate ) {
		this.appstate = appstate;
		this.collection = collection;

		efpic.collection = collection;

		// Trigger lazyload on scroll and resize
		$( window ).on( 'scroll',  this.lazyLoad );
		$( window ).on( 'resize', this.lazyLoad );
	},

	render: function() {
		this.gallery();
		this.initialLazyLoad();
		return this;
	},

	events: {
		'click .error-filter-reset': 'resetFilter',
		'click .error-stars-filter-reset': 'resetStarsFilter',
	},

	resetFilter: function() {
		$( '.efpic-error' ).remove();
		$( 'body' ).removeClass( 'filter-selected filter-unselected' );
		this.appstate.set( 'filter', [] );
	},

	resetStarsFilter: function() {
		$( '.efpic-error' ).remove();
		$( 'body' ).removeClass( [ 'stars-filter-1', 'stars-filter-2', 'stars-filter-3', 'stars-filter-4', 'stars-filter-5' ] );
	},

	gallery: function() {
		// Empty everything
		this.$el.empty();

		if ( this.collection.length > 0 ) {

			// Call statusbar function, render HTML
			var statusbar = this.statusBar();
			// Make the status bar view "global", or at least accessible in the context of this view. We need this on remove!
			this.statusBarView = statusbar;
			this.$el.append( statusbar.render().el );

			// Iterate through all images in our collection, get views back
			var galleryitems = this.collection.map( this.renderGalleryItems, this );
			// Make the galleryItems "global", or at least accessible in the context of this view. We need this on remove!
			this.galleryItems = galleryitems;

			// Iterate throught the single image views, render them and then append them to the .efpic-gallery HTML element
			this.$el.append( galleryitems.map( function(num){ return num.render().el; }, this ) );
		}
		// If collection has no images (which should not be possible)
		else {
			this.$el.append( '<div class="efpic-error"><div class="error-inner">' + this.appstate.get( 'error_msg_no_imgs' ) + '</div></div>' );
		}
	},

	renderGalleryItems: function( image ) {
		var item = new efpic.GalleryView.Item({model: image, appstate: this.appstate});
		var temp = item.render().el;
		return item;
	},

	statusBar: function() {
		var item = new efpic.StatusBarView({model: this.appstate, collection: this.collection});
		return item;
	},

	lazyLoad: function() {
		// Get all images that need to be lazy loaded
		var lazyImages = $( 'img.lazy:visible' );
		var active = false;

		// Debounce with a timeout function
		if ( active === false ) {
			active = true;

			setTimeout( function() {
				// Iterate through lazy images
				lazyImages.each( function() {
					// Check if image is visible in the view port
					if ( $( this ).visible( true ) ) {
						// Set lazyloaded attribute to true in the image's model
						var number = $( this ).parents( 'a' ).attr( 'href' ).replace( '#', '' );
						efpic.collection.models[number - 1].set( 'lazyloaded', true );
					}
				});

				active = false;
			}, 200);
		}
	},

	initialLazyLoad: function() {
		setTimeout( function() {
			var lazyImages = $( 'img.lazy:visible' );
			// Iterate through lazy images
			lazyImages.each( function() {
				// Check if image is visible in the view port
				if ( $( this ).visible( true ) ) {
					// Set lazyloaded attribute to true in the image's model
					var number = $( this ).parents( 'a' ).attr( 'href' ).replace( '#', '' );
					efpic.collection.models[number - 1].set( 'lazyloaded', true );
				}
			});
		}, 200);
	},

	remove: function() {
		// Remove child views and binds
		this.off();
		this.statusBarView.remove();
		this.galleryItems.map( function(view){ view.remove() }, this );
	},

});