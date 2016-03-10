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

		'admin_css' => array("css/settings.css"),
		//  'public_css' => array("css/public.css"),
		'admin_js' => array("js/scripts.js"),
		//  'public_js' => array("js/public.js"),

		/*
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


	public static function get_all_sizes() {
		$sizes = array();
		foreach (array("thumbnail", "medium", "large") as $size_name) {
			$sizes[$size_name] = array("width"=>get_option($size_name."_size_w"), "height"=>get_option($size_name."_size_h"));
		}
		global $_wp_additional_image_sizes;
		return array_merge($sizes, $_wp_additional_image_sizes);
	}

	private static function get_biggest_size() {
		$all_sizes = self::get_all_sizes();
		usort($all_sizes, function($a, $b) {
			$ah = $a["height"];
			$bh = $b["height"];
			$aw = $a["width"];
			$bw = $b["width"];
			
			if($ah <= 0) { $ah = $aw; }
			if($aw <= 0) { $aw = $ah; }
			if($bh <= 0) { $bh = $bw; }
			if($bw <= 0) { $bw = $bh; }
			
			if($aw*$ah === $bw*$bh) { return 0; }
			return $aw*$ah > $bw*$bh ? 1 : -1;
		});
		return $all_sizes[sizeof($all_sizes)-1];
	}

	public function resize($dir, $sizes, $output=false) {
		foreach ($sizes as $size => $value) {
			$image = $dir . '/' . $value['file'];
			$resource = imagecreatefromjpeg($image);
			
			// get the worst case file size
			$mp_biggest_size = self::get_biggest_size();
			$mp_bs_h = $mp_biggest_size["height"];
			$mp_bs_w = $mp_biggest_size["width"];
			
			if($mp_bs_w <= 0) { $mp_bs_w = $mp_bs_h; }
			if($mp_bs_h <= 0) { $mp_bs_h = $mp_bs_w; }
			
			// ... as megapixel
			$mp_biggest = $mp_bs_w * $mp_bs_h;
			
			// get the actual mp
			$mp_new = imagesx($resource) * imagesy($resource);
			
			// scale factor 0-1 = %
			$scale_f = 1- ($mp_new / $mp_biggest);
			$scale_f *= $scale_f; 
			
			// quality in % => lb + (scale_f * (ub-lb))
			$quality_lower_bound = get_option("djq_lower_bound");
			$quality = (int) ((get_option("djq_upper_bound") - $quality_lower_bound) * $scale_f) + $quality_lower_bound;

			// save
			imagejpeg($resource, $image, $quality);
			imagedestroy($resource);
			if ($output) {
				echo __("Processing") . $image . "... -> \n" . $quality."%<br>\n";
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
		$msg = "";

		if (isset($_POST["update_settings"])) {
			$djq_lower_bound = esc_attr($_POST["djq-lower-bound"]);
			$djq_upper_bound = esc_attr($_POST["djq-upper-bound"]);
			update_option("djq_lower_bound", $djq_lower_bound);
			update_option("djq_upper_bound", $djq_upper_bound);
		}

		$djq_lower_bound = !empty(get_option("djq_lower_bound")) ? get_option("djq_lower_bound") : 30;
		$djq_upper_bound = !empty(get_option("djq_upper_bound")) ? get_option("djq_upper_bound") : 90;
		$this->render("views/admin/settings.php", array('title'=>__("Dynamic JPEG Quality"), 'msg' => $msg, "djq_lower_bound"=>$djq_lower_bound, "djq_upper_bound"=>$djq_upper_bound));
	}


	public function load_tools_page() {
		$this->render("views/admin/recalc.php", array('title'=>__("Recalculate JPEG Quality")));
	}


	/*
	* function gcd()
	*
	* returns greatest common divisor
	* between two numbers
	* tested against gmp_gcd()
	*/
	public static function gcd($a, $b) {
		if ($a == 0 || $b == 0)
			return abs( max(abs($a), abs($b)) );

		$r = $a % $b;
		return ($r != 0) ?
			self::gcd($b, $r) :
			abs($b);
	}


	/*
	* function gcd_array()
	*
	* gets greatest common divisor among
	* an array of numbers
	*/
	public static function gcd_array($array, $a = 0) {
		$b = array_pop($array);
		return ($b === null) ?
			(int)$a :
			self::gcd_array($array, self::gcd($a, $b));
	}


}
