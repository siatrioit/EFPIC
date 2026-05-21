jQuery(function($){
	$(document).ready(function() {

		$( '.efpic-collection' ).on( 'click', '.js-close-message', function( e ) {
			e.preventDefault();
			$( '.overlay' ).remove();
		});

		/**
		 * Close grid size and filter when clicking elsewhere
		 */
		$( 'body' ).on( 'click', function( e ) {
			var sizeList = document.querySelector( '.efpic-grid-size__list' );
			if ( sizeList != null && ! e.target.classList.contains( 'efpic-grid-size__toggle' ) ) {
				document.querySelector( '.efpic-grid-size__list' ).classList.remove( 'is-visible' );
			}

			var selectionAlert = document.querySelector( '.efpic-selection-alert__explanation' );
			if ( selectionAlert != null && ( e.target.closest( 'div' ) == null || ! e.target.closest( 'div' ).classList.contains( 'efpic-selection-alert' ) ) ) {
				document.querySelector( '.efpic-selection-alert__explanation' ).classList.remove( 'is-visible' );
			}
		});

		/**
		 * Make sure the filter opens on the correct side
		 */
		function checkFilterSide() {
			var filterToggle = document.querySelector( '.efpic-filter' );
			if ( filterToggle != null ) {
				if ( filterToggle.offsetLeft < 20 ) {
					filterToggle.classList.add( 'is-left' );
				}
				else {
					filterToggle.classList.remove( 'is-left' );
				}
			}
		}
		checkFilterSide();
		window.onresize = checkFilterSide;

		/**
		 * Grid size & filters 
		 */
		var body = document.querySelector( 'body' );

		/**
		 * Show/hide grid size list
		 */
		function toggleGridSizeList() {
			var minWidth = document.querySelector( '.efpic-grid-size__toggle' ).offsetWidth + 'px';
			var gridSizeList = document.querySelector( '.efpic-grid-size__list' );
			gridSizeList.style.minWidth =  minWidth;
			gridSizeList.classList.toggle( 'is-visible' );
		}
	
		/**
		 * Switch the grid size
		 */
		function switchGridSize( e ) {
			var size = e.target.id.substr( 10 );
			document.querySelector( 'body' ).classList.remove( 'thumbsize-small', 'thumbsize-medium', 'thumbsize-large' );
			document.querySelector( 'body' ).classList.add( 'thumbsize-' + size );
			document.querySelector( '.efpic-grid-size__list' ).classList.remove( 'is-visible' );
			efpic.GalleryView.prototype.lazyLoad();
		}

		/**
		 * Event listener for clicks
		 */
		body.addEventListener( 'click', function( e ) {
			// Toggle the grid size list panel
			if ( e.target.classList.contains( 'efpic-grid-size__toggle' ) ) {
				toggleGridSizeList();
			}

			// Switch the grid size
			if ( e.target.classList.contains( 'efpic-grid-size__switch' ) ) {
				switchGridSize( e );
			}

			// Toggle filter panel
			if ( e.target.closest( 'button' ) != null && e.target.closest( 'button' ).classList.contains( 'efpic-filter__toggle' ) ) {
				document.querySelector( '.efpic-filter__toggle' ).classList.toggle( 'is-open' );
			}

			// Close filter panel
			if ( e.target.closest( '.efpic-filter' ) == null && document.querySelector( '.efpic-filter__toggle' ) != null ) {
				document.querySelector( '.efpic-filter__toggle' ).classList.remove( 'is-open' );
			}

			// Reset all filters
			if ( e.target.classList.contains( 'filter-reset-all' ) ) {
				// Remove all filter related to body classes
				body.classList.remove( 'filter-active', 'filter-selected', 'filter-unselected', 'stars-filter-1', 'stars-filter-2', 'stars-filter-3', 'stars-filter-4', 'stars-filter-5' );

				// Reset the filter
				appState.set( 'filter', [] );

				// Remove possible error message
				$( '.efpic-error' ).remove();
			}

			// Handle selected filter
			if ( ( e.target.closest( 'button' ) != null && e.target.closest( 'button' ).classList.contains( 'filter-selected--selected' ) ) || e.target.classList.contains( 'filter-selected--selected' ) ) {
				// Remove possible error message
				$( '.efpic-error' ).remove();

				// Unset the selected filter, if it is already set
				if ( body.classList.contains( 'filter-selected' ) ) {
					body.classList.remove( 'filter-selected', 'filter-unselected' );
					var temp = appState.attributes.filter.filter(function( filter ) {
						return filter !== 'selected';
					});
					appState.set( 'filter', temp );
				}
				// Set the selected filter
				else {
					body.classList.remove( 'filter-unselected' );
					body.classList.add( 'filter-selected' );
					var temp = appState.attributes.filter.filter(function( filter ) {
						return filter !== 'unselected';
					});
					appState.set( 'filter', temp.concat( 'selected' ) );

					// Check if there are any selcted images and maybe display error message
					if ( efpic.collection.where({selected: true}).length <= 0 ) {
						$( '.efpic-gallery' ).append('<div class="efpic-error"><div class="error-inner"><h2>' + appState.get( 'error_msg_filter_selected' ) + '</h2><p><a class="error-filter-reset" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg>' + appState.get( 'reset_filter_msg' ) + '</span></p></div></div>');
					}
				}
			}
			// Handle unselected filter
			else if ( ( e.target.closest( 'button' ) != null && e.target.closest( 'button' ).classList.contains( 'filter-selected--unselected' ) ) || e.target.classList.contains( 'filter-selected--unselected' ) ) {
				// Remove possible error message
				$( '.efpic-error' ).remove();

				// Unset the unselected filter, if it is already set
				if ( body.classList.contains( 'filter-unselected' ) ) {
					body.classList.remove( 'filter-selected', 'filter-unselected' );
					var temp = appState.attributes.filter.filter(function( filter ) {
						return filter !== 'unselected';
					});
					appState.set( 'filter', temp );
				}
				else {
					// Set the unselected filter
					body.classList.remove( 'filter-selected' );
					body.classList.add( 'filter-unselected' );
					temp = appState.attributes.filter.filter(function( filter ) {
						return filter !== 'selected';
					});
					appState.set( 'filter', temp.concat( 'unselected' ) );

					// Check if there are any unselected images and maybe display error message
					if ( efpic.collection.where({selected: true}).length >= efpic.collection.length ) {
						$( '.efpic-gallery' ).append('<div class="efpic-error"><div class="error-inner"><h2>' + appState.get( 'error_msg_filter_unselected' ) + '</h2><p><a class="error-filter-reset" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg>' + appState.get( 'reset_filter_msg' ) + '</span></p></div></div>');
					}
				}
			}

			// Handle star filter
			if ( e.target.classList.contains( 'efpic-stars-filter__filter' ) ) {
				// Remove possible error message
				$( '.efpic-error' ).remove();

				// If star filter is already active, deactivate it
				if ( body.classList.contains( 'stars-filter-' + e.target.dataset.stars ) ) {
					body.classList.remove( 'stars-filter-1', 'stars-filter-2', 'stars-filter-3', 'stars-filter-4', 'stars-filter-5' );
					var temp = appState.attributes.filter.filter(function( filter ) {
						return ( filter !== '1' && filter !== '2' && filter !== '3' && filter !== '4' && filter !== '5' );
					});
					appState.set( 'filter', temp );
				}
				// Set star filter
				else {
					body.classList.remove( 'stars-filter-1', 'stars-filter-2', 'stars-filter-3', 'stars-filter-4', 'stars-filter-5' );
					body.classList.add( 'stars-filter-' + e.target.dataset.stars );
					var temp = appState.attributes.filter.filter(function( filter ) {
						return ( filter !== '1' && filter !== '2' && filter !== '3' && filter !== '4' && filter !== '5' );
					});
					temp.push( e.target.dataset.stars );
					appState.set( 'filter', temp );

					// Check if there are images with the current star rating
					var images = efpic.collection.filter( function( model ) {
						if ( model.attributes.stars >= e.target.dataset.stars ) {
							return true;
						}
						return false;
					});
					
					// Display error message, if there are no images
					if ( images.length < 1 ) {
						$( '.efpic-gallery' ).append('<div class="efpic-error"><div class="error-inner"><h2>' + appState.get( 'error_msg_stars_filter_empty' ) + '</h2><p><a class="error-stars-filter-reset" href="#index"><svg viewBox="0 0 100 100"><use xlink:href="#icon_close"></use></svg>' + appState.get( 'reset_stars_filter_msg' ) + '</span></p></div></div>');
					}
			
				}
			}

			// Always run lazyload after doing anything filter related
			efpic.GalleryView.prototype.lazyLoad();
		});

		/**
		 * Listening for key input.
		 */
		document.addEventListener( 'keydown', function( e ) {
			// ESC key
			if ( e.code == 'Escape' ) {
				if ( document.querySelector( '.efpic-grid-size__list' ) != null ) {
					// Hide grid size dropdown
					document.querySelector( '.efpic-grid-size__list' ).classList.remove( 'is-visible' );
					// Hide filter dropdown
					document.querySelector( '.efpic-filter__toggle' ).classList.remove( 'is-open' );
				}
			}
		}); 
	});
});