<?php

use moritzjacobs\PluginBase\Main as DynamicJPEGQualityBaseClass;

class DynamicJPEGQuality extends DynamicJPEGQualityBaseClass {

	function __construct() {
		parent::__construct($this->preferences, __DIR__);
	}
	
	
	/***************************************************
	*
	*  Boilerplate starts here:
	*
	****************************************************
	
	Default settings API overview:
	
	- admin_css: Array of paths to CSS files for the admin
		interface
	
	- public_css: Array of paths to CSS files for the site
	
	- admin_js: Array of paths to JS files for the admin
		interface
	
	- public_js: Array of paths to JS files for the site 
	
	- custom_post_types: associative array of post types
		to register. Value equals options parameter for
		register_post_type(); see:
		http://codex.wordpress.org/Function_Reference/register_post_type
		
		array("my-post-type-slug" => $options)
	
	****************************************************/
	

	private $preferences = array(
/*
		'admin_css' => array("css/admin.css"),
		'public_css' => array("css/public.css"),
		'admin_js' => array("js/admin.js"),
		'public_js' => array("js/public.js"),
		
		'custom_post_types' => array(
			'foobar' => array(
				'labels' => array(
					'name' 				=> 'Foobars',
					'singular_name'		=> 'Foobar',
					'add_new' 			=> 'Add',
					'menu_name'			=> 'My Plugin',
				),
			),
		),
*/
	);



	public $version = '1.0';

	public function recalculate_all_attachments($output=false) {
		$attachments = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'attachment',
			'post_mime_type' => 'image/jpeg'
		));
	
		if (empty($attachments)) return;
	
		$uploads = wp_upload_dir();
	
		foreach ($attachments as $attachment) {
	
			$attach_meta = wp_get_attachment_metadata($attachment->ID);
			if (!is_array($attach_meta['sizes'])) break;
	
			$pathinfo = pathinfo($attach_meta['file']);
			$dir = $uploads['basedir'] . '/' . $pathinfo['dirname'];
			$this->resize($dir, $attach_meta['sizes'], $output);
		}
	}	
	
	public function resize($dir, $sizes, $output=false) {
		foreach ($sizes as $size => $value) {
			$image = $dir . '/' . $value['file'];
			$resource = imagecreatefromjpeg($image);
			$mp_orig = 2000 * 2000;
			$mp_new = imagesx($resource) * imagesy($resource);
			$scale_f = $mp_new / $mp_orig;
			$quality_f =  1-$scale_f;
			$quality_lower_bound = get_option("djq_lower_bound");
			$quality = (int) ((get_option("djq_upper_bound") - $quality_lower_bound) * $quality_f) + $quality_lower_bound;
	
			imagejpeg($resource, $image, $quality);
			imagedestroy($resource);
			if ($output) {
				echo __("Processing") . $image . "...<br>\n";
			}
		}
	}
	
	
	public function update_jpeg_quality($meta_id, $attach_id, $meta_key, $attach_meta) {
	
		if ($meta_key == '_wp_attachment_metadata') {
	
			$post = get_post($attach_id);
	
			if ($post->post_mime_type == 'image/jpeg' && is_array($attach_meta['sizes'])) {
	
				$pathinfo = pathinfo($attach_meta['file']);
				$uploads = wp_upload_dir();
				$dir = $uploads['basedir'] . '/' . $pathinfo['dirname'];
	
				$this->resize($dir, $attach_meta['sizes'], false);
			}
		}
	}

	/***************************************************
	*
	*  Init function, add hooks, actions etc. here
	*
	****************************************************/

	public function initialize() {
		$this->add_settings_page("Dynamic JPEG Quality", array($this, "load_settings_page"));
		$this->add_tools_page("Recalculate JPEG Quality", array($this, "load_tools_page"));
	}





	/***************************************************
	*
	* Examples for action callbacks for PluginBase
	* helper functions 
	*
	****************************************************/

	public function load_settings_page() {
		$this->render("views/admin/settings.php", array('title'=>__("Dynamic JPEG Quality")));
	}
	
	public function load_tools_page() {
		$this->render("views/admin/recalc.php", array('title'=>__("Recalculate JPEG Quality")));
	}
	

}
