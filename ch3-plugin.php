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


// include 'vars.php';
//ini_set('max_execution_time', 60*60*10);

// error_reporting ( 0 );



// $GLOBALS['HIDE_UNKNOWN_TAGS'] = TRUE;
// $toolkit_Dir = "PHP_JPEG_Metadata_Toolkit_1.12/";
$toolkit_Dir = "PJMT/";
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'Toolkit_Version.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'JPEG.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'JFIF.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'PictureInfo.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'XMP.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'Photoshop_IRB.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'EXIF.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'Photoshop_File_Info.php');



/***************************************************************
 * SECURITY : Exit if accessed directly
 ***************************************************************/
if ( !defined( 'ABSPATH' ) ) {
	
	die( 'Direct access not allowed!' );
	
}




/***************************************************************
 * OUTPUT LOG on text file for sensitive operations
 ***************************************************************/
function printLog($msg){
	$file = fopen("__DEL_ME_printLog.txt", "a");
	$msg .= "\n";
	fwrite($file, $msg);
	fclose($file);
}


/***************************************************************
 * Recursive directory listing
 ***************************************************************/
function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}




/***************************************************************
 * GET META DATA
 ***************************************************************/
// NEED TO DO IT ALL IN ONE FUNCTION
function recursive_array_search($needle, $haystack, $currentKey = '') {
    foreach($haystack as $key=>$value) {
        if (is_array($value)) {
            $nextKey = recursive_array_search($needle,$value, $currentKey . '[' . $key . ']');
            if ($nextKey) {
                return $nextKey;
            }
        }
        else if($value==$needle) {
            return is_numeric($key) ? $currentKey . '[' .$key . ']' : $currentKey;
            // return $key;
        }
    }
    return false;
}
function get_deep_key( $string, $vars )
{
    $keys = explode( '][', substr( $string, 1, -1 ) );
    foreach( $keys as $key ) {
        $vars = $vars[$key];
    }
    return $vars;
}

// function extract_tag_value($tag, $array) {
//     $path = recursive_array_search($tag, $array);
//     return get_deep_key( $path.'[value]', $array );
// }


//  Need to rewrite and use single function for finding [value] from [tag]
function extract_tag_value($tag, $array) {
    $path = recursive_array_search($tag, $array);

    $keys = explode( '][', substr( $path.'[value]', 1, -1 ) );
    foreach( $keys as $key ) {
        $array = $array[$key];
    }
    return $array;
}

function getMetadata($filename){
    $jpeg_header_data = get_jpeg_header_data( $filename );
    $Exif_array = get_EXIF_JPEG( $filename );
    $XMP_array = read_XMP_array_from_text( get_XMP_text( $jpeg_header_data ) );

    $metadata = array();
    $metadata['Location'] = extract_tag_value('Iptc4xmpCore:Location', $XMP_array);
    $metadata['City'] = extract_tag_value('photoshop:City', $XMP_array);
    $metadata['State'] = extract_tag_value('photoshop:State', $XMP_array);
    $metadata['Country'] = extract_tag_value('photoshop:Country', $XMP_array);

    $metadata['Date'] = extract_tag_value('Exif Version', $Exif_array);

    return $metadata;
}

// function find_metadata($needle, $haystack) {
//     foreach($haystack as $key=>$value) {
//         if (is_array($value)) {
//             if( array_key_exists('tag', $value) && array_key_exists('value', $value) && $value['tag']==$needle)
//                     return $value['value'];
//             else
//                 find_metadata($needle, $value);
//         }
//     }
//     return false;
// }

/***************************************************************
 * GET META DATA                    END END END
 ***************************************************************/





/***************************************************************
 * Plugin Menu for testing -- REMOVE when done
 ***************************************************************/
add_action('admin_menu', 'ch3_plugin_menu');

function ch3_plugin_menu(){
    add_menu_page( 'ch3 Plugin', 'ch3 Plugin', 'manage_options', 'ch3-plugin', 'ch3_plugin' );
}

function ch3_plugin(){
	echo "<br> <br> <br>--------------<br>";
	echo "Start<br>";

	printLog('Hello');

    // include "PHP_JPEG_Metadata_Toolkit_1.12/IPTC.php"; 


echo "<br>--------------<br>";    
// $file = wp_upload_dir()['basedir'].'/iptc_fixed.jpg';
// $file = wp_normalize_path("D:/myStuff/ch3/web/v4.ch3.gr/file/test/iptc_fixed.jpg");
// $file = wp_normalize_path("D:/myStuff/ch3/web/v4.ch3.gr/file/test/iptc_broken.jpg");
// echo $file;

echo "<br>------ exif --------<br>";   
// $wpRead = wp_read_image_metadata($file);
// print("<pre>".print_r($wpRead,true)."</pre>");
// $exif = exif_read_data($file,'IFD0',true);
// print("<pre>".print_r($exif,true)."</pre>");


echo "<br>------ check --------<br>";   

// check if it's broken
echo "<br>------ broken --------<br>";   
$files = getDirContents('D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs');
// $files = getDirContents('D:/myStuff/My Pictures/digi/2019');
// $files = getDirContents('D:/myStuff/My Pictures/digi/2018');

foreach($files as $file) {

    // print( end(explode(".", $file)) . "______");
    // print($file . "<br>");
    if( end(explode(".", $file)) == "jpg" ) {
        print($file);
        unset($info);
        $size = getimagesize($file, $info);
        $iptc = iptcparse($info['APP13']);
        if( $iptc == "" )
            print( " <-- broken ");
            // print($file . "<br>");
    }

    print("<br>");


}
echo "<br>------ check end --------<br>";   


echo "<br>------IPTC--------<br>";   
$file = "D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs/ch3_190516_0002.jpg";
// $exif = exif_read_data($file,'IFD0',true);
// print("<pre>".print_r($exif,true)."</pre>");

unset($info);
$size = getimagesize($file, $info);
// print("<pre>".print_r($info,true)."</pre>");

echo "<br>------RAW--------<br>";
// $content = $info['APP13'];
// print("<pre>".print_r($content,true)."</pre>");
// echo "<br>--------------<br>";


$iptc = iptcparse($info['APP14']);
print("<pre>".print_r($iptc,true)."</pre>");


// if(isset($info['APP13']))
// {
//     $iptc = iptcparse($info['APP13']);
//     // var_dump($iptc);
//     print("<pre>".print_r($iptc,true)."</pre>");

// }



echo "<br>--------------<br>";
echo "<br>----      ----<br>";
echo "<br>--------------<br>";


// $filename = "D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs/ch3_190516_0001.jpg";
// $filename = "D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs/iptc_broken.jpg";
// $filename = "D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs/iptc_fixed.jpg";
// $filename = "D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs/ch3_180611_2074.jpg";
// $filename = "D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs/ch3_180611_2075.jpg";
// $filename = "D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs/fresh_1_add.jpg";
$filename = "D:/myStuff/ch3/web/v4.ch3.gr/file/itcp_bs/fresh_1-MEM.jpg";
// Retrieve the header information from the JPEG file
$jpeg_header_data = get_jpeg_header_data( $filename );
// Retrieve EXIF information from the JPEG file
$Exif_array = get_EXIF_JPEG( $filename );
// Retrieve XMP information from the JPEG file
$XMP_array = read_XMP_array_from_text( get_XMP_text( $jpeg_header_data ) );
// Retrieve Photoshop IRB information from the JPEG file
// $IRB_array = get_Photoshop_IRB( $jpeg_header_data );
// Retrieve Photoshop File Info from the three previous arrays
// $new_ps_file_info_array = get_photoshop_file_info( $Exif_array, $XMP_array, $IRB_array );

echo "<br>------EXIF--------<br>";
print("<pre>".print_r( $Exif_array ,true)."</pre>");
echo "<br>------XMP--------<br>";
print("<pre>".print_r( $XMP_array ,true)."</pre>");

// print("<pre>".print_r( $XMP_array[0]['children'][0]['children'][0] ,true)."</pre>");

// $a = recursive_array_search('photoshop:City', $XMP_array);
// print($a ."<br>");
// echo get( $a.'[value]', $XMP_array );

// echo get( recursive_array_search('Iptc4xmpCore:Location', $XMP_array).'[value]', $XMP_array );
// echo get( '[0][children][0][children][0][children][0][value]', $XMP_array );
echo "<br>--------------<br>";
// echo extract_tag_value('Iptc4xmpCore:Location', $XMP_array);
// echo "<br>";
// echo extract_tag_value('photoshop:Country', $XMP_array);


print("<pre>".print_r( getMetadata($filename) ,true)."</pre>");

// echo Interpret_EXIF_to_HTML($Exif_array, $filename);
// echo Interpret_XMP_to_HTML($XMP_array, $filename);

// Check for operation mode 4 - $new_ps_file_info_array and $filename are not defined,
// $new_ps_file_info_array = array("keywords" => array());
// foreach( $new_ps_file_info_array[ 'keywords' ] as $keyword )
        // echo "$keyword, ";



echo "<br>--------------<br>";
echo "<br>--------------<br>";



/*
$file = wp_upload_dir()['basedir'].'/ch3_190101_3403.jpg';
echo $file;
echo "<br>------EXIF--------<br>";   
$exif = exif_read_data($file,'IFD0',true);
print("<pre>".print_r($exif,true)."</pre>");


echo "<br>------IPTC--------<br>";   
unset($info);
$size = getimagesize($file, $info);
$iptc = iptcparse($info['APP13']);
print("<pre>".print_r($iptc,true)."</pre>");

echo "<br>------IPTC 2--------<br>";   

$data = get_IPTC($file);
print("<pre>".print_r($data,true)."</pre>");


// if(isset($info['APP13']))
// {
//     $iptc = iptcparse($info['APP13']);
//     // var_dump($iptc);
//     print("<pre>".print_r($iptc,true)."</pre>");
*/
// }

 //    print( "<br>----0---<br>" );
	// $postId = 246;
 //    $children = get_children($postId);
 //    print("<pre>".print_r($children,true)."</pre>");


 //    print( "<br>----1---<br>" );
 //    print("<pre>".print_r($children[295],true)."</pre>");
 //    print( "<br>----1---<br>" );

    // require_once( ABSPATH . 'wp-admin/includes/image.php' );





// re-gen thumbs
/*
    $img = 298;
    // $img = $children[295]->ID;
    $meta = wp_get_attachment_metadata( $img );
    print("<pre>".print_r($meta,true)."</pre>");

    print( "<br>----1---<br>" );
    $fullsizepath = wp_get_attachment_url($img);
    print( $fullsizepath );


    print( "<br>----2---<br>" );
    // unset($meta['sizes']['thumbnail']);
    // print("<pre>".print_r($meta,true)."</pre>");
    // wp_update_attachment_metadata( $img, $meta );

    print("<pre>".print_r(get_intermediate_image_sizes(),true)."</pre>");

    // if ( false !== $fullsizepath && file_exists( $fullsizepath ) ) {
        // $meta1 = wp_generate_attachment_metadata( $img, $fullsizepath );
        // wp_update_attachment_metadata( $img, $meta1 );

        // print("<pre>".print_r($meta1,true)."</pre>");
        print( "<br>----UPDATE---<br>" );
    // }

    $img_path = 'D:/myStuff/ch3/web/v4.ch3.gr/file/ch3_102-17.jpg';
    $gd_image_editor = new WP_Image_Editor_GD($img_path); 
    $gd_image_editor->load(); 
    // $gd_image_editor->resize(300,450,false); 
    $gd_image_editor->multi_resize(get_intermediate_image_sizes());
    $gd_image_editor->save($img_path);

        // print("<pre>".print_r($meta1,true)."</pre>");

        // foreach ( $meta['sizes'] as $size) {
        //     $file = wp_normalize_path( wp_upload_dir()['basedir'] ."/". $size['file'] );
        //     print( "Deleting file ". $file ."<br>");
        //     // wp_delete_file(  $file );
        // }

*/
	echo "<br>--------------<br>";
	echo "<br>-- D O N E ---<br>";
}







/***************************************************************
 * Test
 ***************************************************************/
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





/***************************************************************
 * CUSTOM UPLOAD location
 ***************************************************************/
add_filter("wp_image_editors", "my_wp_image_editors");
function my_wp_image_editors($editors) {
    array_unshift($editors, "WP_Image_Editor_Custom");

    return $editors;
}


// Store images to custom directory
// Include the existing classes first in order to extend them.
require_once ABSPATH.WPINC."/class-wp-image-editor.php";
require_once ABSPATH.WPINC."/class-wp-image-editor-gd.php";
define('UPLOADS', 'file');

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









// WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP 
/***************************************************************
 * ITPC Automatically populate image attachment metadata
 ***************************************************************/
function aqq_populate_img_meta($post_id) {
    // get image info
    getimagesize(get_attached_file($post_id), $info);
    // print( isset($info['APP13']) );
    // parse it for iptc
    if(isset($info['APP13']))
    {
        $iptc = iptcparse($info['APP13']);
        // var_dump($iptc);
        // print("<pre>".print_r($iptc,true)."</pre>");

        $title = $iptc['2#005'][0];

        if( !empty( $title ) )
            wp_update_post(array('ID' => $post_id, 'post_title' => $title));
        // wp_update_post(array('ID' => $post_id, 'post_excerpt' => $caption));
        // update_post_meta($post_id, '_wp_attachment_image_alt', $alt);
        // wp_update_post(array('ID' => $post_id, 'post_content' => $description));

    // echo $$exif_excerpt;
    // echo '<________>';
    // echo $title;
    // echo '<________>';
    // print("<pre>".print_r($exif,true)."</pre>");
    }
}
 
// add_filter('add_attachment', 'aqq_populate_img_meta');





// WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP 
/***************************************************************
 * Bulk Action to update images
 ***************************************************************/
function register_my_bulk_update_images($bulk_actions) {
  $bulk_actions['bulk_update_images'] = __( 'Update Images', 'bulk_update_images');
  return $bulk_actions;
}
add_filter( 'bulk_actions-upload', 'register_my_bulk_update_images' );


 
function bulk_update_images_handler( $redirect_to, $doaction, $post_ids ) {
  if ( $doaction !== 'bulk_update_images' ) {
    return $redirect_to;
  }
  foreach ( $post_ids as $post_id ) {
    if( wp_attachment_is_image( $post_id ) ) {
        print("ss");
    }
  }
  $redirect_to = add_query_arg( 'bulk_update_images', count( $post_ids ), $redirect_to );
  return $redirect_to;
}
add_filter( 'handle_bulk_actions-upload', 'bulk_update_images_handler', 10, 3 );


function my_bulk_action_admin_notice() {
  if ( ! empty( $_REQUEST['bulk_update_images'] ) ) {
    $image_count = intval( $_REQUEST['bulk_update_images'] );
    printf( '<div id="message" class="updated fade">' .
      _n( 'Updated %s images.',
        'Updated %s images.',
        $image_count,
        'bulk_update_images'
      ) . '</div>', $image_count );
  }
}
add_action( 'admin_notices', 'my_bulk_action_admin_notice' );

















/***************************************************************
 * RATING
 ***************************************************************/



/***************************************************************
 * POST EDITOR - RATING Field box
 ***************************************************************/



add_action( 'post_submitbox_misc_actions', 'add_rating_field' );

function add_rating_field($post){
    wp_nonce_field( plugin_basename( __FILE__ ), 'rating_nonce' );

    // get pre existing rating value
    $value = get_post_meta( $_GET['post'], 'rating', true );
    if( $value == '' )
        $value = 0;

    echo '<div class="misc-pub-section">';
    echo '<span class="dashicons dashicons-chart-bar" style="vertical-align: sub"></span>';
    echo '<span class="rating" style="padding-left: 8px">Post Rating : <input type="number" min="-10" max="10" name="rating_value" value="'.$value.'" style="width: 4em"></span>';
    echo '</div>';



}



/***************************************************************/
// save data from checkboxes
add_action( 'save_post', 'rating_save', 10, 1 );
/**
 * Add columns to management page
 * @param int $post_id
 * @return array
 */
function rating_save($post_id) {
// update_post_meta( $post_id, 'rating', 153 );
    // check if this isn't an auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;

    // security check
    if ( !wp_verify_nonce( $_POST['rating_nonce'], plugin_basename( __FILE__ ) ) )
        return $post_id;
    
    if ( ! current_user_can( 'edit_post', $post_id ) || 'post' != $_POST['post_type'] )
        return $post_id;


    // get input rating and store it on the post metadata
    $value = $_POST['rating_value'];
    // echo $value.'<br>';
    update_post_meta( $post_id, 'rating', $value );
    
}







/***************************************************************
 * ALL POSTS list - RATING Field box
 ***************************************************************/
add_filter( 'manage_post_posts_columns', 'add_column_rating' );
/**
 * Add columns to management page
 * @param array $columns
 * @return array
 */
function add_column_rating( $columns ) {
    $columns['rating'] = 'Rating';
    return $columns;
}

add_action( 'manage_posts_custom_column', 'column_rating_content', 10, 2 );
/**
 * Set content for columns in management page
 * @param string $column_name
 * @param int $post_id
 * @return void
 */
function column_rating_content( $column_name, $post_id ) {
    if ( 'rating' != $column_name )
        return;
 
    $value = get_post_meta( $post_id, 'rating', true );
    echo $value ;
}


/***************************************************************/
// Quick edit display

add_action( 'quick_edit_custom_box', 'quick_edit_rating', 10, 2 );
/**
 * Add Rating to quick edit screen
 * @param string $column_name Custom column name, used to check
 * @param string $post_type
 * @return void
 */
function quick_edit_rating( $column_name, $post_type ) {
    if ( 'rating' != $column_name )
        return;

    wp_nonce_field( plugin_basename( __FILE__ ), 'rating_nonce' );
    echo '<div class="inline-edit-group wp-clearfix">';
    echo '<br>dsa <input type="number" min="-10" max="10" name="rating_value" class="ratingClass" value="1111">Rating ';
    echo '</div>';


}

/***************************************************************/
// Quick edit save
// add_action( 'save_post', 'quick_edit_rating_save', 20, 1 );
/**
 * Save quick edit data
 * @param int $post_id
 * @return void|int
 */
function quick_edit_rating_save( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;
 
    if ( ! current_user_can( 'edit_post', $post_id ) || 'post' != $_POST['post_type'] )
        return $post_id;
 
    // $data = get_post_meta( $post_id, 'rating', true );
    $value = $_POST['rating_value'];
    update_post_meta( $post_id, 'rating', $value );

    // echo '<br>AAAAAA<br>';
    // echo $_POST['rating_value'];
}


/***************************************************************/
// Populate initial value - javeScript
add_action( 'admin_footer', 'quick_edit_rating_javascript' );
 /**
 * Write javascript function to set rating number
 * @return void
 */
function quick_edit_rating_javascript() {
    global $current_screen;
 
    if ( 'post' != $current_screen->post_type )
        return;
?>
    <script type="text/javascript">
    function get_rating( fieldValue ) {
        inlineEditPost.revert();
        // jQuery( '.ratingClass' ).attr( 'number', 7  );
        jQuery( '.ratingClass' ).val( fieldValue );
    }
    </script>
<?php
}

// Feed java script with post rating value
add_filter( 'post_row_actions', 'expand_quick_edit_link', 10, 2 );
/**
 * Pass rating value to quick_edit_rating_javascript javascript function
 * @param array $actions
 * @param array $post
 * @return array
 */
function expand_quick_edit_link( $actions, $post ) {
    global $current_screen;
 
    if ( 'post' != $current_screen->post_type ) {
        return $actions;
    }
 
    $data                               = get_post_meta( $post->ID, 'rating', true );
    $data                               = empty( $data ) ? 0 : $data;
    $actions['inline hide-if-no-js']    = '<a href="#" class="editinline" title="';
    $actions['inline hide-if-no-js']    .= esc_attr( 'Edit this item inline' ) . '"';
    $actions['inline hide-if-no-js']    .= " onclick=\"get_rating('{$data}')\" >";
    $actions['inline hide-if-no-js']    .= 'Quick Edit';
    $actions['inline hide-if-no-js']    .= '</a>';
 
    return $actions;
}



// https://ducdoan.com/add-custom-field-to-quick-edit-screen-in-wordpress/























/***************************************************************
 * GALLERY UPGRADE
 ***************************************************************/



/***************************************************************
 * POST EDITOR - RATING Field box
 ***************************************************************/
add_action('print_media_templates', function(){
?>
<script type="text/html" id="tmpl-custom-gallery-setting">
    <h3>Gallery Preset</h3>

    <label class="setting">
      <span><?php _e('Select'); ?></span>
      <select data-setting="preset" id='selectPreset'>
        <option value="images"> Image Gallery </option>
        <option value="posts"> Post Collection </option>
        <option value="custom"> Custom Options </option>
      </select>

    <h3 style="z-index: -1;">___________________________________________________________________________________________</h3>


</script>

<script>

    $(function()
    {
        _.extend(wp.media.gallery.defaults, {
        preset: 'images',
        });

        wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
            template: function(view){
              return wp.media.template('custom-gallery-setting')(view)
                   + wp.media.template('gallery-settings')(view);
            }
        });
        // $('#selectPreset option[value="images"]').attr("selected", "selected");
        // $('.testField option[value="images"]');

        // $('#selectPreset').on('change', function() {
        //     alert("aaa");
        //     if ($(this).val() == 'custom') {
        //         $('.link-to').prop('disabled', false);
        //     } else {
        //         // $('#selectTesty').reset();
        //         $('.link-to').prop('disabled', true);
        //     }
        // });
    });



    // $(function () {
    //     $('#selectPreset').on('change', function() {
    //         alert("aaa");
    //         if ($(this).val() == 'custom') {
    //             $('.link-to').prop('disabled', false);
    //         } else {
    //             // $('#selectTesty').reset();
    //             $('.link-to').prop('disabled', true);
    //         }
    //     });

    //     $('#selectPreset').val('images');
    // });


</script>



<?php

});













add_filter( 'post_gallery', 'my_post_gallery', 10, 2 );
function my_post_gallery( $output, $attr) {
    global $post, $wp_locale;

    static $instance = 0;
    $instance++;

    // We're trusting author input, so let's at least make sure it looks like a valid orderby statement
    if ( isset( $attr['orderby'] ) ) {
        $attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
        if ( !$attr['orderby'] )
            unset( $attr['orderby'] );
    }

    extract(shortcode_atts(array(
        'preset'     => '',
        'order'      => 'ASC',
        'orderby'    => 'menu_order ID',
        'id'         => $post->ID,
        'itemtag'    => 'dl',
        'icontag'    => 'dt',
        'captiontag' => 'dd',
        'columns'    => 3,
        'size'       => 'thumbnail',
        'include'    => '',
        'exclude'    => ''
    ), $attr));

    $id = intval($id);
    $output = '';





    if( $preset == 'images' || $preset == ''){
        //////////////////////////////////////////////////////////////////////////////////////////
        // Image Gallery
        //////////////////////////////////////////////////////////////////////////////////////////

        $output .= "Image preset<br>";


        $selector = "gallery-{$instance}";
        $output = apply_filters('gallery_style', "
            <style type='text/css'>
                #{$selector} {
                    margin: auto;
                }
                #{$selector} img {
                    border: 1px solid red;
                }
                
            </style>
            <!-- see gallery_shortcode() in wp-includes/media.php -->
            <div id='$selector' class='gallery galleryid-{$id}'>");


        $ids = explode(",", $include);
        foreach( $ids as $id ) {
            // $output .= '<div id=""'
            $output .= wp_get_attachment_image( $id, 'large', 0, '' );
            
            // $output .=
        }

        $output .= '</div>';
        $output .= '<br>END of gallery<br>__________<br>';
        return $output;

    }






    else if( $preset == 'posts'){
        //////////////////////////////////////////////////////////////////////////////////////////
        // Post listing
        // The Image is a link to parent post
        //////////////////////////////////////////////////////////////////////////////////////////
        $output .= "Image preset<br>";

        $selector = "gallery-{$instance}";
        $output = apply_filters('gallery_style', "
            <style type='text/css'>
                #{$selector} {
                    margin: auto;
                }
                #{$selector} img {
                    border: 1px solid green;
                }
                
            </style>
            <!-- see gallery_shortcode() in wp-includes/media.php -->
            <div id='$selector' class='gallery galleryid-{$id}'>");


        $ids = explode(",", $include);
        foreach( $ids as $id ) {
            $output .= '<a href="'. get_permalink( wp_get_post_parent_id($id) ) .'">';
            $output .= wp_get_attachment_image( $id, 'large', 0, '' );
            $output .= '</a>';
            
        }

        $output .= '</div>';
        $output .= '<br>END of gallery<br>__________<br>';
        return $output;
    } 











    else {
        //////////////////////////////////////////////////////////////////////////////////////////
        // Custom Order

        if ( 'RAND' == $order )
            $orderby = 'none';

        if ( !empty($include) ) {
            $include = preg_replace( '/[^0-9,]+/', '', $include );
            $_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

            $attachments = array();
            foreach ( $_attachments as $key => $val ) {
                $attachments[$val->ID] = $_attachments[$key];
            }
        } elseif ( !empty($exclude) ) {
            $exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
            $attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
        } else {
            $attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
        }

        if ( empty($attachments) )
            return '';

        if ( is_feed() ) {
            $output = "\n";
            foreach ( $attachments as $att_id => $attachment )
                $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
            return $output;
        }

        $itemtag = tag_escape($itemtag);
        $captiontag = tag_escape($captiontag);
        $columns = intval($columns);
        $itemwidth = $columns > 0 ? floor(100/$columns) : 100;
        $float = is_rtl() ? 'right' : 'left';

        $selector = "gallery-{$instance}";

        $output = apply_filters('gallery_style', "
            <style type='text/css'>
                #{$selector} {
                    margin: auto;
                }
                #{$selector} .gallery-item {
                    float: {$float};
                    margin-top: 10px;
                    text-align: center;
                    width: {$itemwidth}%;           }
                #{$selector} img {
                    border: 2px solid #cfcfcf;
                }
                #{$selector} .gallery-caption {
                    margin-left: 0;
                }
            </style>
            <!-- see gallery_shortcode() in wp-includes/media.php -->
            <div id='$selector' class='gallery galleryid-{$id}'>");

        $i = 0;
        foreach ( $attachments as $id => $attachment ) {
            $link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);

            $output .= "<{$itemtag} class='gallery-item'>";
            $output .= "
                <{$icontag} class='gallery-icon'>
                    $link
                </{$icontag}>";
            if ( $captiontag && trim($attachment->post_excerpt) ) {
                $output .= "
                    <{$captiontag} class='gallery-caption'>
                    " . wptexturize($attachment->post_excerpt) . "
                    </{$captiontag}>";
            }
            $output .= "</{$itemtag}>";
            if ( $columns > 0 && ++$i % $columns == 0 )
                $output .= '<br style="clear: both" />';
        }

        $output .= "
                <br style='clear: both;' />
            </div>\n";
        return $output;
    } 


    // $output .= '<br>END of post<br>__________<br>';


}