var efpic = efpic || {};

efpic.RegistrationView = Backbone.View.extend({
	tagName: 'div',
	className: 'efpic-modal',
	id: 'efpic-registration',

	template: _.template( jQuery( "#efpic-registration" ).html() ),

	initialize: function( options ) {
		this.title = options.title;
		this.router = options.router;
		this.appstate = options.appstate;

		// Key bindings
		_.bindAll( this , 'keyAction' );
		$( document ).on( 'keydown', this.keyAction);
	},

	render: function() {
		var registrationTemplate = this.template({title: this.title});
		this.$el.html( registrationTemplate );
		return this;
	},

	events: {
		'keydown': 'keyAction',
		'click .efpic-register': 'register',
		'click .efpic-close-modal': 'closeRegistration',
	},

	keyAction: function( e ) {
		// ESC key
		if ( e.keyCode == 27 ) {
			e.preventDefault();
			this.router.navigate('index', {trigger: true} );
		}
	},

	register: function( e ) {
		e.preventDefault();

		// Get values from registration form
		var values = {};
		var fields = document.querySelectorAll( '[name^=efpic-registration-form]' );
		_.each( fields, function( e ) {
			// Get the title from selectbox option, not just the value
			var title = e.querySelectorAll( "[selected]" );
			if ( typeof title[0] !== 'undefined' ) {
				title[0].innerText;
				values[e.id] = { value:e.value, label:e.labels[0].innerText, title:title[0].innerText }
			}
			else {
				values[e.id] = { value:e.value, label:e.labels[0].innerText }
			}
		});

		//Send AJAX request
		$.post( this.appstate.get( 'ajaxurl' ), {
			action: 'efpic_register',
			security: this.appstate.get( 'nonce' ),
			postid: this.appstate.get( 'postid' ),
			registration_fields: values,
			intent: 'register'
		}, function( response ) {
			if ( response.success == true ) {
				// Redirect immediately so the client can start selecting (email is sent in background).
				if ( response.data.ident != null ) {
					var baseUrl = window.location.href.split( '#' )[0];
					var url = new URL( baseUrl, window.location.origin );
					url.searchParams.set( 'ident', response.data.ident );
					window.location.replace( url.toString() );
					return;
				}
				// Fallback: no ident returned (should be rare)
				if ( response.data.verification == 1 ) {
					$( '.efpic-registration-before' ).hide();
					$( '.efpic-registration-after' ).show();
					return;
				}
			}
			else {
				// Display error message
				$( '.efpic-collection' ).append('<div class="overlay fail"><div class="message"><p>' + response.data.message + '</p><p><a class="efpic-button small primary js-close-message" href="#">' + response.data.button_text + '</a></p></div></div>');
			}

		}).fail( _.bind( function() {
			$( '.efpic-collection' ).append('<div class="overlay fail"><div class="message"><p>' + this.appstate.get( 'request_failed_error' ) + '</p><p><a class="efpic-button small primary js-close-message" href="#">' + this.appstate.get( 'button_ok' ) + '</a></p></div></div>');
		}, this ) );
	},

	closeRegistration: function( e ) {
		e.preventDefault();
		// Go back to the last opened lightbox view, if registration was triggered from there
		// Make sure there is "enough history"
		if ( this.router.history != null && this.router.history.length >= 2 ) {
			// Check if the last view was the lightbox
			if ( this.router.history[this.router.history.length - 2].name == 'efpicLightbox') {
				var imgNum = '#' + this.router.history[this.router.history.length - 2].args[0];
				this.router.navigate( imgNum, {trigger: true} );
				return;
			}
		}

		this.router.navigate( '#index', {trigger: true} );
		this.remove();
	},

	remove: function() {
		// Unbind keydown
		$( document ).off( 'keydown', this.keyAction );
		// Remove yourself
		$( '#efpic-registration' ).remove();
	}
});