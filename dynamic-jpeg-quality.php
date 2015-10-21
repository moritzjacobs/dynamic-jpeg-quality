<?php
/*
Plugin Name: Dynamic JPEG Quality
Plugin URI: http://DynamicJPEGQuality.example
Description: …
Version: 1.0
Author: Your name
Author URI: http://mywebsite.example
*/


require_once('framework/plugin-base.php');
require_once('dynamic-jpeg-quality-class.php');

// Initalize
$DynamicJPEGQuality = new DynamicJPEGQuality();


// Run the plugins initialization method
add_action('init', array(&$DynamicJPEGQuality, 'initialize'));
add_filter('jpeg_quality', create_function('$quality', 'return 100;'));
add_action('added_post_meta', array(&$DynamicJPEGQuality, 'update_jpeg_quality'), 10, 4);

