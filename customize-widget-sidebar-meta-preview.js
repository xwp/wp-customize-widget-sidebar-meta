/* exported CustomizeWidgetSidebarMetaPreview */

var CustomizeWidgetSidebarMetaPreview = (function() {
	'use strict';

	var component = {};

	/**
	 * Initialize component.
	 *
	 * @param {wp.customize.Values} api  - The wp.customize object.
	 * @returns {void}
	 */
	component.init = function init( api ) {
		component.api = api;
		component.api.bind( 'preview-ready', component.ready );
	};

	/**
	 * Ready.
	 *
	 * @returns {void}
	 */
	component.ready = function ready() {
		component.addBackgroundColorPartials();
	};

	/**
	 * Add background color partial for each registered sidebar that has a container_selector defined.
	 *
	 * @returns {void}
	 */
	component.addBackgroundColorPartials = function addBackgroundColorPartials() {
		_.each( component.api.widgetsPreview.registeredSidebars, function ( sidebar ) {
			var partial, settingId;
			if ( ! sidebar.container_selector ) {
				return;
			}
			settingId = 'sidebar_meta[' + sidebar.id + '][background_color]';
			partial = new component.api.selectiveRefresh.partialConstructor.sidebar_meta_background_color( settingId, {
				params: {
					settings: [ settingId ],
					primarySetting: settingId,
					sidebar_id: sidebar.id,
					sidebar_container_selector: sidebar.container_selector
				}
			} );
			component.api.selectiveRefresh.partial.add( partial.id, partial );
		} );
	};

	return component;

})();
