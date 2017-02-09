/* eslint max-nested-callbacks: [ "error", 4 ], consistent-this: [ "error", "partial" ] */

wp.customize.selectiveRefresh.partialConstructor.sidebar_meta_background_color = (function( api, $ ) {
	'use strict';

	return api.selectiveRefresh.Partial.extend( {

		/**
		 * Constructor.
		 *
		 * @inheritDoc
		 *
		 * @param {string} id - Partial ID.
		 * @param {Object} options - Options.
		 */
		initialize: function initialize( id, options ) {
			var partial = this;
			api.selectiveRefresh.Partial.prototype.initialize.call( partial, id, options );

			if ( ! partial.params.sidebar_id ) {
				throw new Error( 'Missing sidebar_id param.' );
			}
			if ( ! partial.params.sidebar_container_selector ) {
				throw new Error( 'Missing sidebar_container_selector param.' );
			}
			if ( ! partial.params.selector ) {
				partial.params.selector = 'style.widget-sidebar-background-color[data-sidebar-id="' + partial.params.sidebar_id + '"]';
			}
		},

		/**
		 * Refresh.
		 *
		 * Override refresh behavior to apply changes with JS instead of doing
		 * a selective refresh request for PHP rendering (since unnecessary).
		 *
		 * @returns {jQuery.promise}
		 */
		refresh: function() {
			var partial = this, backgroundColorSetting;

			backgroundColorSetting = api( partial.params.primarySetting );
			_.each( partial.placements(), function( placement ) {
				var css = partial.params.sidebar_container_selector + '{ background-color: ' + backgroundColorSetting.get() + '; }';
				placement.container.text( css );
			} );

			// Return resolved promise since no server-side selective refresh will be requested.
			return $.Deferred().resolve().promise();
		}
	} );
})( wp.customize, jQuery );
