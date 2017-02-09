<?php
/**
 * Plugin Name: Customize Widget Sidebar Meta
 * Plugin URI:  https://github.com/xwp/wp-customize-widget-sidebar-meta
 * Description: Demonstration for how to add custom meta fields to a widget sidebar in the customizer.
 * Author:      Weston Ruter, XWP
 * Author URI:  https://make.xwp.co/
 * Text Domain: customize-widget-sidebar-meta
 * Domain Path: /languages
 * Version:     0.1.0-beta
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
 * Upgrade core sidebars.
 *
 * @global array $wp_registered_sidebars
 */
function upgrade_core_theme_sidebars_for_background_coloring() {
	global $wp_registered_sidebars;

	$core_theme_sidebar_selectors = array(
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

	$theme_sidebars = array();
	if ( isset( $core_theme_sidebar_selectors[ get_template() ] ) ) {
		$theme_sidebars = $core_theme_sidebar_selectors[ get_template() ];
	} elseif ( isset( $core_theme_sidebar_selectors[ get_stylesheet() ] ) ) {
		$theme_sidebars = $core_theme_sidebar_selectors[ get_stylesheet() ];
	}

	foreach ( $theme_sidebars as $sidebar_id => $sidebar_config ) {
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

		$title_setting = $wp_customize->add_setting( sprintf( 'sidebar_meta[%s][title]', $section->sidebar_id ), array(
			'type' => 'theme_mod',
			'capability' => 'edit_theme_options', // i.e. manage_widgets.
			'sanitize_callback' => 'sanitize_text_field',
			'transport' => 'postMessage',
			'default' => '',
		) );
		$wp_customize->selective_refresh->add_partial( $title_setting->id, array(
			'container_inclusive' => true,
			'type' => 'sidebar_meta_title',
			'settings' => array( $title_setting->id ),
			'selector' => sprintf( '[data-customize-partial-id="%s"]', $title_setting->id ),
			'render_callback' => function() use ( $section ) {
				render_sidebar_title( $section->sidebar_id );
			},
		) );

		$background_color_setting = $wp_customize->add_setting( sprintf( 'sidebar_meta[%s][background_color]', $section->sidebar_id ), array(
			'type' => 'theme_mod',
			'capability' => 'edit_theme_options', // i.e. manage_widgets.
			'sanitize_callback' => 'sanitize_hex_color',
			'transport' => 'postMessage',
			'default' => '',
		) );

		// Handle previewing of late-created settings.
		if ( did_action( 'customize_preview_init' ) ) {
			$title_setting->preview();
			$background_color_setting->preview();
		}
	} // End foreach().
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
			'title_label' => __( 'Title:', 'customize-widget-sidebar-meta' ),
			'background_color_label' => __( 'Background Color:', 'customize-widget-sidebar-meta' ),
		),
	);
	wp_add_inline_script( $handle, sprintf( 'CustomizeWidgetSidebarMetaControls.init( wp.customize, %s );', wp_json_encode( $data ) ) );
}
add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\customize_controls_enqueue_scripts' );

/**
 * Print controls template.
 *
 * This should not be needed as of #30738.
 *
 * @link https://core.trac.wordpress.org/ticket/30738
 */
function customize_controls_print_footer_scripts() {
	?>
	<script type="text/html" id="tmpl-customize-control-widget-sidebar-meta-title-content">
		<# var elementIdBase = String( Math.random() ); #>
		<label for="{{ elementIdBase + '[title]' }}" class="customize-control-title">{{ data.label }}</label>
		<input class="title widefat" type="text" id="{{ elementIdBase + '[title]' }}" data-customize-setting-link="{{ data.settings['default'] }}">
	</script>
	<?php
}
add_action( 'customize_controls_print_footer_scripts', __NAMESPACE__ . '\customize_controls_print_footer_scripts' );

/**
 * Print sidebar styles.
 *
 * @global array $wp_registered_sidebars
 */
function print_sidebar_styles() {
	global $wp_registered_sidebars;

	$sidebar_meta = get_theme_mod( 'sidebar_meta' );

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
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\print_sidebar_styles' );

/**
 * Enqueue frontend preview script.
 *
 * @global \WP_Customize_Manager $wp_customize
 */
function customize_preview_init() {
	global $wp_customize;

	if ( empty( $wp_customize->widgets ) ) {
		return;
	}

	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_preview_scripts' );
}
add_action( 'customize_preview_init', __NAMESPACE__ . '\customize_preview_init' );

/**
 * Enqueue preview scripts.
 */
function enqueue_preview_scripts() {
	$handle = 'customize-widget-sidebar-meta-title-partial';
	$src = plugin_dir_url( __FILE__ ) . 'customize-widget-sidebar-meta-title-partial.js';
	$deps = array( 'customize-preview', 'customize-selective-refresh' );
	wp_enqueue_script( $handle, $src, $deps );

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

/**
 * Render the sidebar title.
 *
 * Note the priority is 9 so that it will output the title before the "milestone" comment.
 *
 * @see WP_Customize_Widgets::start_dynamic_sidebar()
 *
 * @param string $sidebar_id Sidebar ID.
 */
function render_sidebar_title( $sidebar_id ) {
	$sidebar_meta = get_theme_mod( 'sidebar_meta' );
	$is_empty_title = empty( $sidebar_meta[ $sidebar_id ]['title'] );

	if ( $is_empty_title && ! is_customize_preview() ) {
		return;
	}

	$title = $is_empty_title ? '' : $sidebar_meta[ $sidebar_id ]['title'];
	$container_attributes = '';
	if ( is_customize_preview() ) {
		$container_attributes .= sprintf( ' data-customize-partial-id="%s"', "sidebar_meta[$sidebar_id][title]" );
		if ( $is_empty_title ) {
			$container_attributes .= ' hidden';
		}
	}

	$rendered_title = wptexturize( $title );
	$rendered_title = convert_smilies( $rendered_title );

	printf( '<h1 %s>%s</h1>', $container_attributes, esc_html( $rendered_title ) );
}
add_action( 'dynamic_sidebar_before', __NAMESPACE__ . '\render_sidebar_title', 9 );
