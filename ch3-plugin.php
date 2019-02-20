<?php
/**
 * @package ch3 plugin
 * @version 1.0
 */
/*
Plugin Name: ch3 Plugin
Plugin URI:
Description: Plugin for all back end functionality
Author: Georgios Cherouvim
Version: 1.0
Author URI: http://ch3.gr
*/

/***************************************************************
 * SECURITY : Exit if accessed directly
 ***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	
	die( 'Direct access not allowed!' );
	
}

//ini_set('max_execution_time', 60*60*10);


function printLog($msg){
	$file = fopen("__DEL_ME_printLog.txt", "a");
	$msg .= "\n";
	fwrite($file, $msg);
	fclose($file);
}



add_action('admin_menu', 'ch3_plugin_menu');

function ch3_plugin_menu(){
    add_menu_page( 'ch3 Plugin', 'ch3 Plugin', 'manage_options', 'ch3-plugin', 'ch3_plugin' );
}

function ch3_plugin(){
	echo "<br> <br> <br>--------------<br>";
	echo "Start<br>";

	printLog('Hello');
	
	echo "<br>--------------<br>";
	echo "<br>-- D O N E ---<br>";
}





add_action('wp_handle_upload_prefilter', 'imageSave');

function imageSave($file){
	
	// printLog($file['name']);
	// print_r($file);
	// $a = get_object_vars($file);
	foreach($file as $key => $value)
		printLog($key .' : '. $value);


    // $file['name'] = 'wordpress-is-awesome-' . $file['name'];
    return $file;
}





add_filter("wp_image_editors", "my_wp_image_editors");
function my_wp_image_editors($editors) {
    array_unshift($editors, "WP_Image_Editor_Custom");

    return $editors;
}




// Store images to custom directory
// Include the existing classes first in order to extend them.
require_once ABSPATH.WPINC."/class-wp-image-editor.php";
require_once ABSPATH.WPINC."/class-wp-image-editor-gd.php";

class WP_Image_Editor_Custom extends WP_Image_Editor_GD {
    public function generate_filename($prefix = NULL, $dest_path = NULL, $extension = NULL) {
        // If empty, generate a prefix with the parent method get_suffix().
        if(!$prefix)
            $prefix = $this->get_suffix();

        // Determine extension and directory based on file path.
        $info = pathinfo($this->file);
        $dir  = $info['dirname'];
        $ext  = $info['extension'];

        // Determine image name.
        $name = wp_basename($this->file, ".$ext");

        // Allow extension to be changed via method argument.
        $new_ext = strtolower($extension ? $extension : $ext);

        // Default to $_dest_path if method argument is not set or invalid.
        if(!is_null($dest_path) && $_dest_path = realpath($dest_path))
            $dir = $_dest_path;

        // $dir = trailingslashit($dir)."{$prefix}/{$name}.{$new_ext}";
        $dir = trailingslashit($dir)."img/{$name}_{$prefix}.{$new_ext}";
        return $dir;
    }

    function multi_resize($sizes) {
    $sizes = parent::multi_resize($sizes);

    foreach($sizes as $slug => $data)
        // $sizes[$slug]['file'] = $data['width']."x".$data['height']."/".$data['file'];
        $sizes[$slug]['file'] = "img/".$data['file'];

    return $sizes;
}
}









