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
 * @package Customize_Featured_Content_Demo
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
		$customize_id = sprintf( 'sidebar_meta[%s][title]', $section->sidebar_id );
		$setting = new \WP_Customize_Setting( $wp_customize, $customize_id, array(
			'type' => 'theme_mod',
			'capability' => 'edit_theme_options', // i.e. manage_widgets.
			'sanitize_callback' => 'sanitize_text_field',
			'transport' => 'postMessage',
			'default' => '',
		) );
		$wp_customize->add_setting( $setting );

		// Handle previewing of late-created settings.
		if ( did_action( 'customize_preview_init' ) ) {
			$setting->preview();
		}

		$partial = new \WP_Customize_Partial( $wp_customize->selective_refresh, $customize_id, array(
			'container_inclusive' => true,
			'type' => 'sidebar_meta_title',
			'settings' => array( $customize_id ),
			'selector' => sprintf( '[data-customize-partial-id="%s"]', $customize_id ),
			'render_callback' => function() use ( $section ) {
				render_sidebar_title( $section->sidebar_id );
			},
		) );
		$wp_customize->selective_refresh->add_partial( $partial );
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
	$src = plugin_dir_url( __FILE__ ) . 'customize-widget-sidebar-meta-controls.js';
	$deps = array( 'customize-widgets' );
	wp_enqueue_script( $handle, $src, $deps );
	wp_add_inline_script( $handle, 'CustomizeWidgetSidebarMetaControls.init( wp.customize );' );
}
add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\customize_controls_enqueue_scripts' );

/**
 * Print controls template.
 */
function customize_controls_print_footer_scripts() {
	?>
	<script type="text/template" id="tmpl-customize-widget-sidebar-meta-controls">
		<# var elementIdBase = String( Math.random() ); #>
		<div class="customize-widget-sidebar-meta-controls">
			<p class="title">
				<label for="{{ elementIdBase + '[title]' }}"><?php esc_html_e( 'Title:', 'customize-widget-sidebar-meta' ); ?></label>
				<input class="title widefat" type="text" id="{{ elementIdBase + '[title]' }}">
			</p>
		</div>
	</script>
	<?php
}
add_action( 'customize_controls_print_footer_scripts', __NAMESPACE__ . '\customize_controls_print_footer_scripts' );

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
