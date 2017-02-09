<!-- DO NOT EDIT THIS FILE; it is auto-generated from readme.txt -->
# Customize Widget Sidebar Meta

Demonstration for how to add custom meta fields to a widget sidebar in the customizer.

**Contributors:** [westonruter](https://profiles.wordpress.org/westonruter), [xwp](https://profiles.wordpress.org/xwp)  
**Tags:** [customize](https://wordpress.org/plugins/tags/customize)  
**Requires at least:** 4.7.0  
**Tested up to:** 4.8-alpha  
**Stable tag:** 0.1.0  
**License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)  

[![Build Status](https://travis-ci.org/xwp/wp-customize-widget-sidebar-meta.svg?branch=master)](https://travis-ci.org/xwp/wp-customize-widget-sidebar-meta) 

## Description ##

This plugin demonstrates how to add custom fields to widget sidebar sections in the WordPress customizer. As a demonstration it adds a sidebar background color field.

[![Play video on YouTube](https://i1.ytimg.com/vi/aN6Swhfch-8/hqdefault.jpg)](https://www.youtube.com/watch?v=aN6Swhfch-8)

The plugin requires a tiny bit of theme support, although the core themes are all supported by default. In order to add support, you have to register your sidebars with a `container_selector` property which points to the element that contains the `dynamic_sidebar()` call. In other words, given a `sidebar.php` that contains:

```php
<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<div id="widget-area" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</div><!-- .widget-area -->
<?php endif; ?>
```

Modify your `register_sidebar()` to look like this:

```php
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
```

## Changelog ##

### 0.1.0 ###
Initial release.


