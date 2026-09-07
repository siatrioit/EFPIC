var efpic = efpic || {};

efpic.GalleryView.Item = Backbone.View.extend({

    model: efpic.singleImage,

    template: _.template( jQuery( "#efpic-gallery-item" ).html() ),

    tagName: 'div',

    className: function() {
        if ( this.model.get( 'selected' ) == true ) {
            var selected = ' selected';
        }
        else {
            var selected = ' unselected';
        }

		if ( this.model.get( 'stars' ) > 0 ) {
            var stars = ' stars-' + this.model.get( 'stars' );
        }
        else {
            var stars = ' stars-0';
        }

        if ( this.model.get( 'focused' ) == true ) {
            var focused = ' focused';
        }
        else {
            var focused = '';
        }

        return 'efpic-gallery-item' + selected + stars + focused;
    },

    id: function() {
        return 'efpic-image-' + this.model.get( 'number' );
    },

    initialize: function( options ) {
		this.appstate = options.appstate;
        this.listenTo( this.model, 'change', this.render );
    },

    render: function() {
        var singleImageTemplate = this.template( this.model.attributes );

        this.$el.removeClass( 'flash' );

        if ( this.model.get( 'lazyloaded' ) == true ) {
            this.$el.addClass( 'loaded' );
        }

        this.$el.html( singleImageTemplate );
        return this;
    },

    events: {
        'click label': 'toggleImageSelection',
        'click .efpic-imgbox': 'toggleFocus',
		'click .efpic-star-rating__rating': 'setStarRating',
		'click .efpic-star-rating__list': 'removeStarRating',
    },

	toggleImageSelection: function() {
		// Check if the client needs to register first
		var router = new Backbone.Router();
		var temp = jQuery.parseJSON( appstate );
		if ( temp.ident == null ) {
			router.navigate( 'register', {trigger: true} );
			return;
		}
		efpic.GalleryView.prototype.lazyLoad();
		efpic.saveSelection( this );
		efpic.EventBus.trigger( 'save:now', this );
	},

	setStarRating: function( e ) {
		e.preventDefault();

		// Check if the client needs to register first
		var router = new Backbone.Router();
		var temp = jQuery.parseJSON( appstate );
		if ( temp.ident == null ) {
			router.navigate( 'register', {trigger: true} );
			return;
		}

		// Only allow changing the rating if the collection is open
		if ( this.appstate.get( 'poststatus' ) != 'sent' ) {
			return;
		}

		var currentStars = this.model.get( 'stars' );
		var setStars = e.target.closest( '.efpic-star-rating__rating' ).dataset.rating;
		var list = e.target.closest( '.efpic-caption' ).querySelector( '.efpic-star-rating__list' );
		

		if ( currentStars == setStars ) {
			this.model.set( 'stars', 0 );
			this.$el.removeClass( [ 'stars-0', 'stars-1', 'stars-2', 'stars-3', 'stars-4', 'stars-5' ] );
			this.$el.addClass( 'stars-0' );
		}
		else {
			this.model.set( 'stars', setStars );

			// Set the star's class
			this.$el.removeClass( [ 'stars-0', 'stars-1', 'stars-2', 'stars-3', 'stars-4', 'stars-5' ] );
			this.$el.addClass( 'stars-' + setStars );
		}

		// Save
		efpic.EventBus.trigger( 'save:now', this );
	},

	removeStarRating: function( e ) {
		// Only allow changing the rating if the collection is open
		if ( this.appstate.get( 'poststatus' ) != 'sent' ) {
			return;
		}

		if ( e.target.classList.contains( 'efpic-star-rating__list' ) ) {
			var currentStars = this.model.get( 'stars' );
			var list = e.target.closest( '.efpic-caption' ).querySelector( '.efpic-star-rating__list' );
			list.classList.remove( 'is-visible' );
			this.model.set( 'stars', 0 );
			this.$el.removeClass( [ 'stars-0', 'stars-1', 'stars-2', 'stars-3', 'stars-4', 'stars-5' ] );
			this.$el.addClass( 'stars-0' );
			efpic.EventBus.trigger( 'save:now', this );
		}
	}
});