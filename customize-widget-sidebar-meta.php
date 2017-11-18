<?php
/**
 * Plugin Name: Customize Widget Sidebar Meta
 * Plugin URI:  https://github.com/xwp/wp-customize-widget-sidebar-meta
 * Description: Demonstration for how to add custom meta fields to a widget sidebar in the customizer.
 * Author:      Weston Ruter, XWP
 * Author URI:  https://make.xwp.co/
 * Text Domain: customize-widget-sidebar-meta
 * Domain Path: /languages
 * Version:     0.1.0
 *
 * @package Customize_Widget_Sidebar_Meta_Controls
 */

/*
 * Copyright (c) 2017 XWP (https://xwp.co/)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

namespace Customize_Widget_Sidebar_Meta_Controls;

/**
 * Register settings for sidebars.
 *
 * See `WP_Customize_Widgets::schedule_customize_register()` for why settings are registered later at the wp action.
 *
 * @see WP_Customize_Widgets::schedule_customize_register()
 * @param \WP_Customize_Manager $wp_customize Manager.
 */
function customize_register( \WP_Customize_Manager $wp_customize ) {
	if ( empty( $wp_customize->widgets ) ) {
		return;
	}

	if ( is_admin() ) {
		register_sidebar_meta_settings();
	} else {
		add_action( 'wp', __NAMESPACE__ . '\register_sidebar_meta_settings', 100 );
	}
}
add_action( 'customize_register', __NAMESPACE__ . '\customize_register' );

/**
 * Get the extra configs for sidebars for the given core theme.
 *
 * @param string $theme Theme.
 * @return array|null List of the sidebars
 */
function get_core_theme_sidebar_configs( $theme ) {
	$themes_sidebars = array(
		'twentysixteen' => array(
			'sidebar-1' => array(
				'container_selector' => '#secondary',
			),
			'sidebar-2' => array(
				'container_selector' => '#content-bottom-widgets .widget-area:first-child',
			),
			'sidebar-3' => array(
				'container_selector' => '#content-bottom-widgets .widget-area:last-child',
			),
		),
		'twentyfifteen' => array(
			'sidebar-1' => array(
				'container_selector' => '#widget-area',
			),
		),
		'twentyfourteen' => array(
			'sidebar-1' => array(
				'container_selector' => '#primary-sidebar',
			),
			'sidebar-2' => array(
				'container_selector' => '#content-sidebar',
			),
			'sidebar-3' => array(
				'container_selector' => '#footer-sidebar',
			),
		),
		'twentythirteen' => array(
			'sidebar-1' => array(
				'container_selector' => '#secondary',
			),
			'sidebar-2' => array(
				'container_selector' => '#tertiary',
			),
		),
		'twentytwelve' => array(
			'sidebar-1' => array(
				'container_selector' => '#secondary.widget-area',
			),
			'sidebar-2' => array(
				'container_selector' => '#secondary .front-widgets.first',
			),
			'sidebar-3' => array(
				'container_selector' => '#secondary .front-widgets.second',
			),
		),
		'twentyeleven' => array(
			'sidebar-1' => array(
				'container_selector' => '#secondary.widget-area',
			),
			'sidebar-2' => array(
				'container_selector' => '.showcase .widget-area',
			),
			'sidebar-3' => array(
				'container_selector' => '#first.widget-area',
			),
			'sidebar-4' => array(
				'container_selector' => '#second.widget-area',
			),
			'sidebar-5' => array(
				'container_selector' => '#third.widget-area',
			),
		),
		'twentyten' => array(
			'primary-widget-area' => array(
				'container_selector' => '#primary.widget-area',
			),
			'secondary-widget-area' => array(
				'container_selector' => '#secondary.widget-area',
			),
			'first-footer-widget-area' => array(
				'container_selector' => '#footer-widget-area .widget-area.first',
			),
			'second-footer-widget-area' => array(
				'container_selector' => '#footer-widget-area .widget-area.second',
			),
			'third-footer-widget-area' => array(
				'container_selector' => '#footer-widget-area .widget-area.third',
			),
			'fourth-footer-widget-area' => array(
				'container_selector' => '#footer-widget-area .widget-area.fourth',
			),
		),
	);
	if ( isset( $themes_sidebars[ $theme ] ) ) {
		return $themes_sidebars[ $theme ];
	}
	return null;
}

/**
 * Determine whether a core theme is active.
 *
 * This is used to determine whether extra padding should be added to the core theme's sidebars.
 * Normally a theme that supports sidebar background colors should add this padding on their own.
 *
 * @return bool Whether a core theme is active.
 */
function is_core_theme_active() {
	return null !== get_core_theme_sidebar_configs( get_template() );
}

/**
 * Upgrade core sidebars.
 *
 * @global array $wp_registered_sidebars
 */
function upgrade_core_theme_sidebars_for_background_coloring() {
	global $wp_registered_sidebars;
	$sidebar_configs = get_core_theme_sidebar_configs( get_template() );
	if ( empty( $sidebar_configs ) ) {
		return;
	}
	foreach ( $sidebar_configs as $sidebar_id => $sidebar_config ) {
		if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
			$wp_registered_sidebars[ $sidebar_id ] = array_merge(
				$wp_registered_sidebars[ $sidebar_id ],
				$sidebar_config
			);
		}
	}
}
add_action( 'widgets_init', __NAMESPACE__ . '\upgrade_core_theme_sidebars_for_background_coloring', 100 );

/**
 * Register meta settings for widget sidebars.
 *
 * @global \WP_Customize_Manager $wp_customize
 */
function register_sidebar_meta_settings() {
	global $wp_customize;
	foreach ( $wp_customize->sections() as $section ) {
		if ( ! ( $section instanceof \WP_Customize_Sidebar_Section ) ) {
			continue;
		}

		$background_color_setting = $wp_customize->add_setting( sprintf( 'sidebar_meta[%s][background_color]', $section->sidebar_id ), array(
			'type' => 'theme_mod',
			'capability' => 'edit_theme_options', // i.e. manage_widgets.
			'sanitize_callback' => 'sanitize_hex_color',
			'transport' => 'postMessage',
			'default' => '',
		) );

		// Handle previewing of late-created settings.
		if ( did_action( 'customize_preview_init' ) ) {
			$background_color_setting->preview();
		}
	}
}

/**
 * Enqueue script.
 *
 * @global \WP_Customize_Manager $wp_customize
 */
function customize_controls_enqueue_scripts() {
	global $wp_customize;

	if ( empty( $wp_customize->widgets ) ) {
		return;
	}

	$handle = 'customize-widget-sidebar-meta-controls';
	$src = plugin_dir_url( __FILE__ ) . 'customize-widget-sidebar-meta-controls.css';
	$deps = array( 'customize-controls' );
	wp_enqueue_style( $handle, $src, $deps );

	$handle = 'customize-widget-sidebar-meta-controls';
	$src = plugin_dir_url( __FILE__ ) . 'customize-widget-sidebar-meta-controls.js';
	$deps = array( 'customize-widgets' );
	wp_enqueue_script( $handle, $src, $deps );
	$data = array(
		'l10n' => array(
			'background_color_label' => __( 'Background Color:', 'customize-widget-sidebar-meta' ),
		),
	);
	wp_add_inline_script( $handle, sprintf( 'CustomizeWidgetSidebarMetaControls.init( wp.customize, %s );', wp_json_encode( $data ) ) );
}
add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\customize_controls_enqueue_scripts' );

/**
 * Print sidebar styles.
 *
 * @global array $wp_registered_sidebars
 */
function print_sidebar_styles() {
	global $wp_registered_sidebars;

	$sidebar_meta = get_theme_mod( 'sidebar_meta' );

	$needs_padding = is_core_theme_active();
	foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
		if ( empty( $sidebar['container_selector'] ) ) {
			continue;
		}
		$style = '';
		if ( ! empty( $sidebar_meta[ $sidebar_id ]['background_color'] ) ) {
			$style .= sprintf(
				'%s { background-color: %s; }',
				$sidebar['container_selector'],
				$sidebar_meta[ $sidebar_id ]['background_color']
			);
		}
		printf( '<style class="widget-sidebar-background-color" data-sidebar-id="%s">%s</style>', esc_attr( $sidebar_id ), $style );

		if ( $needs_padding ) {
			printf( '<style>%s { padding: 5px; }</style>', $sidebar['container_selector'] );
		}
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\print_sidebar_styles', 100 );

/**
 * Enqueue frontend preview script.
 *
 * @global \WP_Customize_Manager $wp_customize
 */
function customize_preview_init() {
	global $wp_customize;
	if ( empty( $wp_customize->widgets ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_preview_scripts' );
}
add_action( 'customize_preview_init', __NAMESPACE__ . '\customize_preview_init' );

/**
 * Enqueue preview scripts.
 */
function enqueue_preview_scripts() {
	$handle = 'customize-widget-sidebar-meta-background-color-partial';
	$src = plugin_dir_url( __FILE__ ) . 'customize-widget-sidebar-meta-background-color-partial.js';
	$deps = array( 'customize-preview', 'customize-selective-refresh' );
	wp_enqueue_script( $handle, $src, $deps );

	$handle = 'customize-widget-sidebar-meta-preview';
	$src = plugin_dir_url( __FILE__ ) . 'customize-widget-sidebar-meta-preview.js';
	$deps = array( 'customize-preview', 'customize-selective-refresh' );
	wp_enqueue_script( $handle, $src, $deps );
	wp_add_inline_script( $handle, 'CustomizeWidgetSidebarMetaPreview.init( wp.customize );' );
}
