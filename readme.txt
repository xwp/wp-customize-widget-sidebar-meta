=== Customize Widget Sidebar Meta ===
Contributors: westonruter, xwp
Tags: customize
Requires at least: 4.7.0
Tested up to: 4.9
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Demonstration for how to add custom meta fields to a widget sidebar in the customizer.

== Description ==

This plugin demonstrates how to add custom fields to widget sidebar sections in the WordPress customizer. For background, see the full writeup: [Adding Meta Fields to a Widget Sidebar Section in the Customizer](https://make.xwp.co/2017/02/08/adding-meta-fields-to-a-widget-sidebar-section-in-the-customizer/).

As a demonstration this plugin adds a sidebar background color field. See demo video:

[youtube https://www.youtube.com/watch?v=aN6Swhfch-8]

Notice that the purpose of this plugin is to demonstrate an approach for implementing certain functionality in the customizer. This plugin should not be expected to be maintained or directly supported. The concepts in this plugin can be incorporated into your own themes and plugins.

The plugin requires a tiny bit of theme support, although the core themes are all supported by default. In order to add support, you have to register your sidebars with a `container_selector` property which points to the element that contains the `dynamic_sidebar()` call. In other words, given a `sidebar.php` that contains:

<pre lang="php">
<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<div id="widget-area" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</div><!-- .widget-area -->
<?php endif; ?>
</pre>

Modify your `register_sidebar()` to look like this:

<pre lang="php">
register_sidebar( array(
	'name' => __( 'Widget Area', 'example' ),
	'id' => 'sidebar-1',
	'description' => __( 'Add widgets here to appear in your sidebar.', 'example' ),
	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	'after_widget' => '</aside>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>',
	'container_selector' => '#widget-area', // 👈 Add this.
) );
</pre>

== Changelog ==

= 0.1.0 =

Initial release.
