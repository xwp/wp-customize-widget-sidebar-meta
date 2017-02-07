/* exported CustomizeWidgetSidebarMetaPreview */
/* eslint max-nested-callbacks: [ "error", 4 ] */

wp.customize.selectiveRefresh.partialConstructor.sidebar_meta_title = (function( api ) {
	'use strict';

	return api.selectiveRefresh.Partial.extend( {

		/**
		 * Partial ready.
		 *
		 * @returns {void}
		 */
		ready: function() {
			var partial = this; // eslint-disable-line consistent-this
			api.selectiveRefresh.Partial.prototype.ready.call( partial );

			// Do low-fidelity preview while waiting for selective refresh to return.
			api( partial.params.primarySetting, function( titleSetting ) {
				titleSetting.bind( function( newTitle ) {
					_.each( partial.placements(), function( placement ) {
						placement.container.toggle( '' !== newTitle );
						placement.container.text( newTitle );
					} );
				} );
			} );
		}
	} );
})( wp.customize );
