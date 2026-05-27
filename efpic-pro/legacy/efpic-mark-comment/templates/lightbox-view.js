var efpic = efpic || {};

efpic.LightboxView = Backbone.View.extend({

	tagName: 'div',
	className: 'efpic-lightbox',

	template: _.template( jQuery( "#efpic-lightbox" ).html() ),

	initialize: function( number, collection, appstate, router ) {
		this.number = ( parseInt(number) - 1 );
		this.collection = collection;
		this.appstate = appstate;
		this.max = collection.length;
		this.router = router;
		this.current = ( parseInt(number) );

		// Set date variables
		this.date_format = this.appstate.attributes.date_format;
		this.time_format = this.appstate.attributes.time_format;
		this.lang = this.appstate.attributes.lang;
		// Get WordPress timezone UTC difference in minutes
		let utc_diff = this.appstate.attributes.utc_diff;
		// Get the users timezone URC difference in minutes
		let now = new Date();
		let utc_local = now.getTimezoneOffset();
		// Caclulate the difference, then convert it to milliseconds
		this.timezone_diff = ( utc_diff + utc_local ) * 60000;

		// Collapse comments by default on small screens
		if ( this.appstate.get( 'comments' ) == undefined && window.screen.width < 640 ) {
			this.appstate.set( 'comments', false );
		}

		// Get model from collection based on the number
		this.model = collection.models[this.number];

		// Render on change
		this.listenTo( this.model, 'change', this.render );
		this.listenTo( this.collection, 'change', this.render );

		// Key bindings
		_.bindAll( this , 'keyAction' );
		$( document ).on( 'keydown', this.keyAction );

		// Recalcualte markers on resize
		$( window ).on( 'resize', _.debounce( this.markerPosition ) );

	},

	render: function() {
		var templateData = _.extend( {}, this.model.attributes, efpic.getLightboxInPriceTemplateData( this ) );
		var lightboxTemplate = this.template( templateData, this.current );

		this.$el.html( lightboxTemplate );
		_.defer( function( view ){ view.afterRender(); }, this );
		return this;
	},

	afterRender: function() {
		this.markerPosition();
	},

	markerPosition: function() {
		// Make sure we are in the lightbox view
		if ( $( '.efpic-lightbox' ).length > 0 ) {
		
			var markers = new Array();
			markers = $( '.marker' );

			var img = $( '.efpic-lightbox-inner.mark-comment img' );

			// Make sure the image is loaded
			img.on( 'load', function () {
				// Do the positioning
				var imgWidth = img.width();
				var imgHeight = img.height();
				var offset = img.offset();
				var leftPadding = offset.left;
				var topPadding = offset.top;

				markers.each( function( index ) {
					var x = $( this ).attr( 'data-x' );
					var y = $( this ).attr( 'data-y' );
					var left = x / 100 * imgWidth + leftPadding - 5;
					var top = y / 100 * imgHeight + topPadding - 5;
					$( this ).css({top: top, left: left});
					$( this ).removeClass( 'is-waiting' );
				});
			});

			// Simulate successful loading event, if the image is cached
			if ( img[0].complete ) {
				img.trigger( 'load' );
			}
		}
	},

	events: {
		'click .efpic-lightbox-select': 'toggleImageSelection',
		'click .efpic-lightbox-next': 'nextImage',
		'click .efpic-lightbox-prev': 'previousImage',
		'click .efpic-lightbox-close': 'closeLightbox',
		'click .efpic-comment-box-toggle': 'toggleCommentBox',
		'click .efpic-add-comment': 'addComment',
		'click .efpic-save-comment': 'saveComment',
		'click .efpic-comment-textarea': 'editComment',
		'click .efpic-delete-comment': 'deleteComment',
		'click .efpic-lightbox-inner.mark-comment img': 'addMarker',
		'click .marker': 'editComment',
		'mouseenter .efpic-comment.has-marker' : 'startMarkerFocus',
		'mouseleave .efpic-comment.has-marker' : 'endFocus',
		'mouseenter .marker' : 'startCommentBoxFocus',
		'mouseleave .marker' : 'endFocus',
		'keydown': 'keyAction',
	},

	nextImage: function( e ) {
		e.preventDefault();

		if ( $( '.efpic-comment-textarea[contenteditable]' ).length > 0 ) {
			$( '.efpic-comment.editable .efpic-save-comment span' ).trigger( 'click' );
		}

		// If a filter is active
		if ( this.appstate.get( 'filter' ) == 'selected' || this.appstate.get( 'filter' ) == 'unselected' ) {

			if ( this.appstate.get( 'filter' ) == 'selected' ) {
				var filter = true;
			}
			if ( this.appstate.get( 'filter' ) == 'unselected' ) {
				var filter = false;
			}

			// Get current image number
			var currentImage = this.current;

			// Find all images that follow the current image and correspond to our filter
			var filteredCollection = this.collection.filter( function( model ) {
				return (
					model.get( 'number' ) > currentImage &&
					model.get( 'selected' ) == filter
				)
			});

			// If there is no image following the current image, start from the beginning
			if ( filteredCollection.length < 1 ) {
				filteredCollection = this.collection.filter( function( model ) {
					return model.get( 'selected' ) == filter
				});
			}

			// If there is still no image, just use the current one
			if ( filteredCollection.length < 1 ) {
				var nextImage = this.current;
			}
			// Otherwise define the next image
			else {
				var nextImage = filteredCollection[0].get( 'number' );
			}

			// Set global image counter and jump to that image
			this.current = nextImage;
			this.router.navigate( this.current.toString(), {trigger: true} );

		}
		else {

			if ( this.current >= this.max ) {
				this.current = 1;
				this.router.navigate( '1', {trigger: true} );
			}
			else {
				this.current++;
				this.router.navigate( this.current.toString(), {trigger: true} );
			}
		}
	},

	previousImage: function( e ) {
		e.preventDefault();

		if ( $( '.efpic-comment-textarea[contenteditable]' ).length > 0 ) {
			$( '.efpic-comment.editable .efpic-save-comment span' ).trigger( 'click' );
		}

		// If a filter is active
		if ( this.appstate.get( 'filter' ) == 'selected' || this.appstate.get( 'filter' ) == 'unselected' ) {

			if ( this.appstate.get( 'filter' ) == 'selected' ) {
				var filter = true;
			}
			if ( this.appstate.get( 'filter' ) == 'unselected' ) {
				var filter = false;
			}

			// Get current image number
			var currentImage = this.current;

			// Find all images that come before the current image and correspond to our filter
			var filteredCollection = this.collection.filter( function( model ) {
				return (
					model.get( 'number') < currentImage &&
					model.get( 'selected' ) == filter
				)
			});

			// If there is no image before the current image, jump to the end
			if ( filteredCollection.length < 1 ) {
				filteredCollection = this.collection.filter( function( model ) {
					return model.get( 'selected') == filter
				});
			}

			// If there is still no image, just use the current one
			if ( filteredCollection.length < 1 ) {
				var previousImage = this.current;
			}
			// Get the previous image, which is the last entry in the filteredCollection
			else {
				var previousImage = filteredCollection.slice(-1)[0].get( 'number' );
			}

			// Set global image counter and jump to that image
			this.current = previousImage;
			this.router.navigate( this.current.toString(), {trigger: true} );

		}
		else {

			if ( this.current <= 1 ) {
				this.current = this.max;
				this.router.navigate( this.current.toString(), {trigger: true} );
			}
			else {
				this.current--;
				this.router.navigate( this.current.toString() , {trigger: true} );
			}
		}
	},

	closeLightbox: function( e ) {
		if ( $( '.efpic-comment-textarea[contenteditable]' ).length > 0 ) {
			$( '.efpic-comment.editable .efpic-save-comment span' ).trigger( 'click' );
		}
	},

	toggleCommentBox: function( e ) {
		e.preventDefault();

		if ( $( '.efpic-comment-textarea[contenteditable]' ).length > 0 ) {
			$( '.efpic-comment.editable .efpic-save-comment span' ).trigger( 'click' );
		}

		this.appstate.set( 'comments', $( '.efpic-lightbox-inner').hasClass( 'comment-box-is-collapsed' ) );
		$( '.efpic-lightbox-inner' ).toggleClass( 'comment-box-is-collapsed' );
		this.markerPosition();
	},

	addComment: function( e ) {
		e.preventDefault();
		e.stopPropagation();

		// Toggle registration modal if ident is not defined
		if ( this.appstate.attributes.ident == null || this.appstate.attributes.ident == false ) {
			this.router.navigate( 'register', {trigger: true} );
			return;
		}

		// Don't do anything if collection has been approved
		if ( 'approved' == this.appstate.get( 'poststatus' ) ) {
			return;
		}

		// Generate random id
		var id = Math.random().toString(36).substr(2, 10);

		$( '.efpic-comment' ).removeClass( 'editable' );
		$( '.efpic-comment-textarea' ).removeAttr( 'contenteditable' );
		$( '.marker' ).removeClass( 'is-active' );

		let timestamp = Math.round( new Date().valueOf() + this.timezone_diff );
		let commentDate = new Date( timestamp );
		var formattedDate = commentDate.format( this.date_format + ' ' + this.time_format, this.lang );

		$( '.efpic-comments' ).append( '<div id="comment-' + id + '" class="efpic-comment editable is-new"><div contenteditable class="efpic-comment-textarea"></div><div class="efpic-comment-meta"><span class="efpic-comment-meta__time">' + formattedDate + '</span></div><div class="efpic-comment-controls"><span class="efpic-comment-control efpic-delete-comment"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span>' + this.appstate.get( 'i18n-delete-comment' ) + '</span></span><span class="efpic-comment-control efpic-save-comment"><svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg><span>' + this.appstate.get( 'i18n-save-comment' ) + '</span></span></div></div>' );

		$( '#comment-' + id ).find( '.efpic-comment-textarea').focus();

		$( '.efpic-lightbox-inner.mark-comment' ).addClass( 'has-markers' );

		$( '.efpic-lightbox-inner' ).addClass( 'is-editing' );

		this.tempComment = '';
	},

	saveComment: function( e ) {
		e.preventDefault();
		e.stopPropagation();

		// Don't do anything if collection has been approved
		if ( 'approved' == this.appstate.get( 'poststatus' ) ) {
			return;
		}

		// Get comment value from textarea
		var comment = $( e.target ).parent().parent().parent().find( '.efpic-comment-textarea' ).text();
		var id = $( e.target ).parent().parent().parent().attr( 'id' ).substr( 8 );

		// If comment is empty, don't save it
		if ( comment.length <= 0 ) {
			return;
		}

		// Get marker coordinates, if one exists
		if ( $( '#marker-' +  id ).length ) {
			var marker = $( '#marker-' +  id );
			var x = marker.attr( 'data-x' );
			var y = marker.attr( 'data-y' );
		}
		else {
			var x = '';
			var y = '';
		}

		// Get all markers
		var markers = this.model.get( 'markers' );

		// Create comment/marker object
		var markerObj = new Object();
		markerObj.id = id;
		markerObj.time = + Math.round( new Date().valueOf() / 1000 );  // Convert to seconds
		markerObj.user = this.appstate.get( 'user' );
		markerObj.x = x;
		markerObj.y = y;
		markerObj.comment = comment;

		// Create new, or edit existing marker collection
		if ( typeof markers === 'object' ) {
			markers[id] = markerObj;
		}
		else {
			var markers = new Object();
			markers[id] = markerObj;
		}

		// Save marker to collection
		this.model.set( 'markers', markers );

		// Hide comment controls
		$( e.target ).parent().parent().parent().removeClass( 'editable' );
		$( '.efpic-comment-textarea' ).removeAttr( 'contenteditable' );
		$( '.marker' ).removeClass( 'is-active' );

		// Remove is-new class on save,
		$( '.is-new' ).removeClass( 'is-new' );

		if ( $( '.efpic-comment' ).length <= 0 ) {
			$( '.efpic-lightbox-inner.mark-comment' ).removeClass( 'has-markers' );
		}

		$( '.efpic-lightbox-inner' ).removeClass( 'is-editing' );

		efpic.EventBus.trigger( 'save:now', this );
	},

	editComment: function( e ) {
		e.preventDefault();
		e.stopPropagation();

		// Don't do anything if collection has been approved
		if ( 'approved' == this.appstate.get( 'poststatus' ) ) {
			return;
		}

		// Don't do anything if this comment is already being edited
		if ( $( e.currentTarget ).attr( 'contenteditable' ) != null ) {
			return;
		}

		$( '.marker' ).removeClass( 'is-active' );
		$( '.efpic-comment' ).removeClass( 'editable' );
		$( '.efpic-comment-textarea' ).removeAttr( 'contenteditable' );

		// Check what the user clicked on, make comment editable, focus on texarea
		if  ( $( e.currentTarget ).hasClass( 'marker' ) ) {
			var id = $( e.currentTarget ).attr( 'id' ).substr( 7 );
			$( '#comment-' + id ).addClass( 'editable' );
			$( '#comment-' + id + ' .efpic-comment-textarea' ).attr( 'contenteditable', '' ).focus();
			$( '#marker-' + id ).addClass( 'is-active' );
			$( '#comment-' + id ).find( '.efpic-comment-textarea' ).focus();
		}
		else if ( $( e.currentTarget ).hasClass( 'efpic-comment-textarea' ) ) {
			var id = $( e.currentTarget ).parent().attr( 'id' ).substr( 8 );
			$( '#comment-' + id ).addClass( 'editable' );
			$( '#comment-' + id + ' .efpic-comment-textarea' ).attr( 'contenteditable', '' ).focus();
			$( '#marker-' + id ).addClass( 'is-active' );
		}

		// Set temporary comment
		this.tempComment = $( '#comment-' + id + ' .efpic-comment-textarea' ).text();

		// Check if a marker exists, get its data
		if ( $( '#marker-' + id ).length > 0 ) {
			this.tempMarker = [ $( '#marker-' + id ).css( 'top'), $( '#marker-' + id ).css( 'left'), $( '#marker-' + id ).attr( 'data-x' ), $( '#marker-' + id ).attr( 'data-y' ) ];
		}
 		// Otherwise set temporary maker to empty
		else {
			this.tempMarker = '';
		}

		$( '.efpic-lightbox-inner' ).addClass( 'is-editing' );
	},

	deleteComment: function( e ) {
		e.preventDefault();
		e.stopPropagation();

		// Don't do anything if collection has been approved
		if ( 'approved' == this.appstate.get( 'poststatus' ) ) {
			return;
		}

		// Get id of the comment
		var id = $( e.target ).parent().parent().parent().attr( 'id' ).substr( 8 );

		// Get all marker objects
		var markers = this.model.get( 'markers' );

		// Delete from model
		delete markers[id];

		// Remove comment from DOM
		$( '#comment-' + id ).remove();

		// Remove marker form DOM
		$( '#marker-' +  id ).remove();

		// Check if there are any comments left, if not remove has-markers class
		if ( $( '.efpic-comment' ).length <= 0 ) {
			$( '.efpic-lightbox-inner.mark-comment' ).removeClass( 'has-markers' );
		}

		$( '.efpic-lightbox-inner' ).removeClass( 'is-editing' );

		efpic.EventBus.trigger( 'save:now', this );
	},

	addMarker: function( e ) {
		e.preventDefault();
		e.stopPropagation();

		// Toggle registration modal if ident is not defined
		if ( this.appstate.attributes.ident == null || this.appstate.attributes.ident == false ) {
			this.router.navigate( 'register', {trigger: true} );
			return;
		}

		// No markers when comment box is collapsed
		if ( $( '.efpic-lightbox-inner' ).hasClass( 'comment-box-is-collapsed' ) ) {
			return;
		}

		$( '.efpic-lightbox-inner.mark-comment' ).addClass( 'has-markers' );

		// Don't do anything if collection has been approved
		if ( 'approved' == this.appstate.get( 'poststatus' ) ) {
			return;
		}

		// Calculate relative position of marker
		var x = e.offsetX / $( e.target ).width() * 100;
		var y = e.offsetY / $( e.target ).height() * 100;

		// Get values for positioning marker in the DOM
		var imgWidth = $( e.target ).width();
		var imgHeight = $( e.target ).height();
		var offset = $( e.target ).offset();
		var leftPadding = offset.left;
		var topPadding = offset.top;
		var left = x / 100 * imgWidth + leftPadding - 5;
		var top = y / 100 * imgHeight + topPadding - 5;

		// If there is an active marker
		if ( $( '.marker' ).hasClass( 'is-active' ) ) {
			// ... move its position
			var activeMarker = $( '.marker.is-active' );
			activeMarker.css({
				'top': top + 'px',
				'left': left + 'px'
			}).attr({
				'data-x': x,
				'data-y': y
			});

			// Get id for setting focus later
			var id = $( '.efpic-comment.editable' ).attr( 'id' ).substr( 8 );
		}
		// Create new marker for existing comment
		else if ( ! $( '.marker' ).hasClass( 'is-active' ) && $( '.efpic-comment' ).hasClass( 'editable' ) ) {
			// Get this comments ID
			var id = $( '.efpic-comment.editable' ).attr( 'id' ).substr( 8 );
			$( '.efpic-comment.editable' ).addClass( 'has-marker' );
			$( '.efpic-comment.editable' ).append( '<span class="efpic-comment-marker"></span>' );

			$( '.efpic-lightbox-inner' ).append( '<div id="marker-' + id + '" class="marker is-active is-new" tabindex="0" style="top: ' + top + 'px; left: ' + left + 'px;" data-x="' + x + '" data-y="' + y + '"></div>' );

			this.tempComment = $( '#comment-' + id + ' .efpic-comment-textarea' ).text();
			this.tempMarker = '';
		}
		// Create a new maker + comment
		else {
			// Generate random id
			var id = Math.random().toString(36).substr(2, 10);

			// Add marker to the DOM
			$( '.efpic-lightbox-inner' ).append( '<div id="marker-' + id + '" class="marker is-active is-new" tabindex="0" style="top: ' + top + 'px; left: ' + left + 'px;" data-x="' + x + '" data-y="' + y + '"></div>' );

			// Remove editbale class from all comments (you never know)
			$( '.efpic-comment' ).removeClass( 'editable' );

			let timestamp = Math.round( new Date().valueOf() + this.timezone_diff );
			let commentDate = new Date( timestamp );
			var formattedDate = commentDate.format( this.date_format + ' ' + this.time_format, this.lang );

			// Add new comment to the DOM
			$( '.efpic-comments' ).append( '<div id="comment-' + id + '" class="efpic-comment has-marker editable is-new"><div contenteditable class="efpic-comment-textarea"></div><div class="efpic-comment-meta"><span class="efpic-comment-meta__time">' + formattedDate + '</span></div><div class="efpic-comment-controls"><span class="efpic-comment-control efpic-delete-comment"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg><span>' + this.appstate.get( 'i18n-delete-comment' ) + '</span></span><span class="efpic-comment-control efpic-save-comment"><svg viewBox="0 0 100 100"><use xlink:href="#icon_check"></use></svg><span>' + this.appstate.get( 'i18n-save-comment' ) + '</span></span></div><span class="efpic-comment-marker"></span></div>' );

			this.tempComment = '';
			this.tempMarker = '';
		}

		// Set focus to the textarea
		$( '#comment-' + id ).find( '.efpic-comment-textarea').focus();

		$( '.efpic-lightbox-inner' ).addClass( 'is-editing' );
	},

	startMarkerFocus: function( e ) {
		var id = $( e.currentTarget ).attr( 'id' ).substr( 8 );
		$( '#marker-' + id ).addClass( 'is-focused' );
	},

	startCommentBoxFocus: function( e ) {
		var id = $( e.currentTarget ).attr( 'id' ).substr( 7 );
		$( '#comment-' + id ).addClass( 'is-focused' );
	},

	endFocus: function( e ) {
		$( '.is-focused' ).removeClass( 'is-focused' );
	},

	toggleImageSelection: function() {
		if ( $( '.efpic-comment-textarea[contenteditable]' ).length > 0 ) {
			$( '.efpic-comment.editable .efpic-save-comment span' ).trigger( 'click' );
		}

		efpic.saveSelection( this );
		efpic.EventBus.trigger( 'save:now', this );
	},

	keyAction: function( e ) {
		e.stopPropagation();

		// If there is an active comment field, do the following
		if ( $( '[contenteditable]' ).length > 0 ) {

			// Enter!
			if ( e.keyCode == 13 ) {
				e.preventDefault();

				// Don't do anything if collection has been approved
				if ( 'approved' == this.appstate.get( 'poststatus' ) ) {
					return;
				}

				// Get comment value from textarea
				var comment = $( e.target ).text();

				// If comment is empty, don't save it
				if ( comment.length <= 0 ) {
					return;
				}

				// Get ID
				var id = $( e.target ).parent().attr( 'id' ).substr( 8 );

				// Get marker coordinates, if one exists
				if ( $( '#marker-' +  id ).length ) {
					var marker = $( '#marker-' +  id );
					var x = marker.attr( 'data-x' );
					var y = marker.attr( 'data-y' );
				}
				else {
					var x = '';
					var y = '';
				}

				// Get all markers
				var markers = this.model.get( 'markers' );

				// Create comment/marker object
				var markerObj = new Object();
				markerObj.id = id;
				markerObj.time = + Math.round( new Date().valueOf() / 1000 ); // Convert to seconds
				markerObj.user = this.appstate.get( 'user' );
				markerObj.x = x;
				markerObj.y = y;
				markerObj.comment = comment;

				// Create new, or edit existing marker collection
				if ( typeof markers === 'object' ) {
					markers[id] = markerObj;
				}
				else {
					var markers = new Object();
					markers[id] = markerObj;
				}

				// Save marker to collection
				this.model.set( 'markers', markers );

				// Hide comment controls
				$( e.target ).parent().removeClass( 'editable' );
				$( '.efpic-comment-textarea' ).removeAttr( 'contenteditable' );

				// Remove focus, set inactive
				$( '.efpic-comment-textarea' ).blur();
				$( '.marker' ).removeClass( 'is-active' );

				$( '.is-new' ).removeClass( 'is-new' );

				if ( $( '.efpic-comment' ).length <= 0 ) {
					$( '.efpic-lightbox-inner.mark-comment' ).removeClass( 'has-markers' );
				}

				$( '.efpic-lightbox-inner' ).removeClass( 'is-editing' );

				efpic.EventBus.trigger( 'save:now', this );
			}

			// ESC!
			if ( e.keyCode == 27 ) {
				e.preventDefault();

				// If is-new classes are around, just remove those new elements
				if ( $( '.is-new' ).length > 0 ) {
					$( '.is-new' ).remove();
					if ( $( '.efpic-comment' ).length <= 0 ) {
						$( '.efpic-lightbox-inner.mark-comment' ).removeClass( 'has-markers' );
					}
					$( '.efpic-comment.has-marker.editable .efpic-comment-marker' ).remove();
					$( '.is-editing' ).removeClass( 'is-editing' );
				}

				if ( this.tempMarker == '' ) {
					$( '.marker.is-active' ).remove();
				}

				// Revert comment back to version before editiing began
				$( e.target ).parent().children( '.efpic-comment-textarea' ).text( this.tempComment );

				// Set marker to its old position
				$( '.marker.is-active' ).css({
					'top': this.tempMarker[0],
					'left': this.tempMarker[1]
				}).attr({
					'data-x': this.tempMarker[2],
					'data-y': this.tempMarker[3]
				});

				// Hide comment controls
				$( e.target ).parent().removeClass( 'editable' );
				$( '.efpic-comment-textarea' ).removeAttr( 'contenteditable' );

				// Remove focus, set inactive
				$( '.efpic-comment-textarea' ).blur();
				$( '.marker' ).removeClass( 'is-active' );

				if ( $( '.efpic-comment' ).length <= 0 ) {
					$( '.efpic-lightbox-inner.mark-comment' ).removeClass( 'has-markers' );
				}

				$( '.efpic-lightbox-inner' ).removeClass( 'is-editing' );
			}
		}
		else {
			// ESC key
			if ( e.keyCode == 27 ) {
				e.preventDefault();
				this.router.navigate('index', {trigger: true} );
			}
			// Do not capture any other keys, if registration is open
			if ( this.router.history[this.router.history.length - 1].name == 'register' ) {
				return;
			}
			// left arrow key
			if ( e.keyCode == 37 ) {
				e.preventDefault();
				this.previousImage( e );
			}
			// right arrow key
			if ( e.keyCode == 39 ) {
				e.preventDefault();
				this.nextImage( e );
			}
			// c Key
			if ( e.keyCode == 67 ) {
				e.preventDefault();
				this.toggleCommentBox( e );
			}
			// enter, p or space key
			if ( 'approved' != this.appstate.get( 'poststatus' ) ) {
				if ( e.keyCode == 13 || e.keyCode == 80 || e.keyCode == 32 ) {
					e.preventDefault();
					this.toggleImageSelection();
				}
			}
		}
	},

	remove: function() {
		// Unbind keydown
		$( document ).off( 'keydown', this.keyAction );

		// Completely remove this view
		Backbone.View.prototype.remove.call( this );
	}

});