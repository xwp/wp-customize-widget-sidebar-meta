/* global wp */
/* exported CustomizeWidgetSidebarMetaControls */

var CustomizeWidgetSidebarMetaControls = (function( $ ) {
	'use strict';

	var component = {};

	/**
	 * Initialize component.
	 *
	 * @param {wp.customize.Values} api - The wp.customize object.
	 * @returns {void}
	 */
	component.init = function init( api ) {
		component.api = api;
		component.api.bind( 'ready', component.ready );
	};

	/**
	 * Ready.
	 *
	 * @returns {void}
	 */
	component.ready = function ready() {
		component.controlsTemplate = wp.template( 'customize-widget-sidebar-meta-controls' );

		component.api.section.each( component.extendSection );
		component.api.section.bind( 'add', component.extendSection );
	};

	/**
	 * Extend a given sidebar section with the
	 *
	 * @param {wp.customize.Section} section Section.
	 * @returns {boolean} Whether the section was extended (whether it was for a sidebar).
	 */
	component.extendSection = function extendSection( section ) {
		var controlsContainer;

		if ( ! section.extended( wp.customize.Widgets.SidebarSection ) ) {
			return false;
		}

		// Embed the sidebar controls once the necessary settings exist.
		component.api( 'sidebar_meta[' + section.params.sidebarId + '][title]', 'sidebar_meta[' + section.params.sidebarId + '][background_color]', function( titleSetting, backgroundColorSetting ) {
			var titleElement, backgroundColorElement;
			controlsContainer = $( $.trim( component.controlsTemplate() ) );

			// Sync title input with the title setting.
			titleElement = new component.api.Element( controlsContainer.find( 'input.title' ) );
			titleElement.set( titleSetting.get() );
			titleElement.sync( titleSetting );

			// Sync background-color input with the title setting.
			backgroundColorElement = new component.api.Element( controlsContainer.find( 'input.background-color' ) );
			backgroundColorElement.set( backgroundColorSetting.get() );
			backgroundColorElement.sync( backgroundColorSetting );

			// Add controls container to the DOM.
			section.contentContainer.find( '.customize-section-title' ).after( controlsContainer );

			// Add special handling for edit shortcut messages since the sidebar meta controls aren't a normal registered wp.customize.Control.
			component.api.previewer.bind( 'focus-control-for-setting', function( settingId ) {
				if ( settingId !== titleSetting.id ) {
					return;
				}
				section.expand( {
					completeCallback: function() {
						titleElement.element.focus();
					}
				} );
			} );
		} );

		return true;
	};

	return component;

})( jQuery );
