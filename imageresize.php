<?php
/*
Plugin Name: Gravity Forms Image Resize
Description: Allows the automatic resizing of images for Gravity Forms.
Version: 1.0
Author: Vervocity
Author URI: https://vervocity.io

------------------------------------------------------------------------*/

define( 'GF_IMAGE_RESIZE_VERSION', '1.0' );
 
add_action( 'gform_loaded', array( 'GF_Image_Resize_Bootstrap', 'load' ), 5 );
 
class GF_Image_Resize_Bootstrap {
 
    public static function load() {
 
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }
 
        require_once( 'class-gfimageresize.php' );
 
        GFAddOn::register( 'GFImageResize' );
    }
 
}
 
function gf_image_resize() {
    return GFImageResize::get_instance();
}