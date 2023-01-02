;(function() {
	'use strict';

	function initializeTomSelect( element ) {
		if ( element.hasOwnProperty( 'tomselect' ) ) {
			return;
		}

		const defaultSettings = {
			plugins: [],
			valueField: 'id',
			labelField: 'title',
			searchField: 'title',
			maxItems: null,
			maxOptions: null,
			paginationPage: 1,
			paginationPerPage: 20,
			render: {
				option: function( data, escape ) {
					let classes = data.$option?.classList.toString() || '';

					return (
						`<div class="${ classes }"><span class="ts-option-label">${ escape( data.title ) }</span></div>`
					);
				}
			},
			firstUrl: function( query ) {
				const url = new URL( `${ restUrl }wp/v2/search` );

				url.searchParams.append( 'search', encodeURIComponent( query ) );

				return url.toString();
			},
			load: function( query, callback ) {
				const { restUrl } = window.tom_select_init_frontend_strings;

				const url = new URL( `${ restUrl }wp/v2/search` );

				url.searchParams.append( 'search', encodeURIComponent( query ) );
				url.searchParams.append( 'type', this.settings.searchType );
				url.searchParams.append( 'subtype', this.settings.searchSubtype );
				url.searchParams.append( 'page', this.settings.paginationPage );
				url.searchParams.append( 'per_page', this.settings.paginationPerPage );

				fetch( url )
					.then( response => {
						const totalPages = response.headers.get('x-wp-totalpages');

						if (totalPages > this.settings.paginationPage ) {
							this.settings.paginationPage = this.settings.paginationPage + 1;

							this.setNextUrl(query, url);
						}

						return response.json();
					} )
					.then( json => {
						console.log({json});
						callback( json );
					} )
					.catch( () => {
						callback();
					} );
			}
		};

		const fieldSettings = JSON.parse( element.getAttribute( 'data-ts-settings' ) );

		const settings = Object.assign( defaultSettings, fieldSettings );

		console.log( { settings } );

		if ( Array.isArray( element.dataset.selectedItems ) &&
		     element.dataset.selectedItems.length ) {
			settings.items = element.dataset.selectedItems;
		}

		new TomSelect( element, settings );
	}

	function initializeFeaturedItemSelect() {
		if ( typeof TomSelect !== 'function' ) {
			console.log( 'TomSelect not available!' );

			return;
		}

		document.querySelectorAll( '.gfield_select_tomselect' ).
		         forEach( initializeTomSelect );
	}

	document.addEventListener( 'DOMContentLoaded', initializeFeaturedItemSelect );

	window.gravityformsadvancedselectInitializeTomSelect = initializeFeaturedItemSelect;
})();
