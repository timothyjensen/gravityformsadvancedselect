;(function() {
	'use strict';

	function initializeTomSelect( element ) {
		if ( element.hasOwnProperty( 'tomselect' ) ) {
			return;
		}

		element.setAttribute( 'aria-hidden', 'true' );

		const apiUrl = ( query, settings ) => {
			const { restUrl } = window.tom_select_init_frontend_strings;

			const url = new URL( `${ restUrl }wp/v2/search` );

			url.searchParams.append( 'search', query );
			url.searchParams.append( 'type', settings.searchType );
			url.searchParams.append( 'subtype', settings.searchSubtype );
			url.searchParams.append( 'per_page', settings.paginationPerPage );
			url.searchParams.append( 'page', settings.paginationPage );

			return url;
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
			paginationTotalPages: 1,
			render: {
				option: ( data, escape ) => {
					let classes = data.$option?.classList.toString() || '';

					return (
						`<div class="${ classes }"><span class="ts-option-label">${ escape( data.title ) }</span></div>`
					);
				}
			},

			firstUrl: query => apiUrl( query, this.settings ),

			onItemAdd: function() {
				this.setTextboxValue('');
				this.refreshOptions();
			},

			load: function( query, callback ) {
				if ( !this.settings.plugins.includes( 'virtual_scroll' ) ) {
					callback();

					return;
				}

				fetch( apiUrl( query, this.settings ) )
					.then( response => {
						this.settings.paginationTotalPages = Number( response.headers.get( 'x-wp-totalpages' ) ) || 1;
						this.settings.maxOptions = Number( response.headers.get( 'x-wp-total' ) ) || null;

						if ( ! response.ok ) {
							throw response.status;
						}

						return response.json();
					} )
					.then( json => {
						if ( this.settings.paginationTotalPages > this.settings.paginationPage ) {
							this.settings.paginationPage = this.settings.paginationPage + 1;

							this.setNextUrl( query, apiUrl( query, this.settings ) );
						} else {
							this.setNextUrl( query, null );
						}

						callback( json );
					} )
					.catch( (e) => {
						console.log(e);

						callback()
					} );
			}
		};

		const fieldSettings = JSON.parse( element.getAttribute( 'data-ts-settings' ) );

		const settings = Object.assign( defaultSettings, fieldSettings );

		if (
			Array.isArray( element.dataset.selectedItems )
			&& element.dataset.selectedItems.length
		) {
			settings.items = element.dataset.selectedItems;
		}

		const ts = new TomSelect( element, settings );

		if ( settings.plugins.includes( 'virtual_scroll' ) ) {
			ts.on( 'focus', function() {
				ts.settings.paginationPage = ts.settings.paginationPage + 1;

				ts.setNextUrl( '', apiUrl( '', ts.settings ) );
			} );

			ts.on( 'type', function() {
				ts.settings.paginationPage = 1;

				ts.setNextUrl( '', apiUrl( '', ts.settings ) );
			} );
		}
	}

	function initializeGFieldTomSelect() {
		if ( typeof TomSelect !== 'function' ) {
			console.log( 'TomSelect not available!' );

			return;
		}

		document.querySelectorAll( 'select.gfield_select_tomselect' ).forEach( initializeTomSelect );
	}

	window.gravityformsadvancedselectInitializeTomSelect = initializeGFieldTomSelect;

	if (typeof jQuery !== 'undefined') {
		jQuery(document).on('gform_post_render', gravityformsadvancedselectInitializeTomSelect );
	} else {
		document.addEventListener( 'DOMContentLoaded', gravityformsadvancedselectInitializeTomSelect );
	}
})();
