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
Version: 1.1.1
Author URI: http://ch3.gr
*/


// PLUGINS NEEDED
// ch3-plugin
// Classic editors
// Regenerate Thumbnails


// CUSTOM UPLOAD DIRECTORY
// define('UPLOADS', 'file');

// DISABLE AUTO DATE FOLDER STRUCTURE 
update_option( 'uploads_use_yearmonth_folders', 0);

// DISABLE AUTO RESIZE :: 2560px large images auto resize. Was introduced @ v5.3
add_filter( 'big_image_size_threshold', '__return_false' );

// MAX UPLOAD SIZE
ini_set( 'upload_max_size' , '64M' );


// include 'vars.php';
ini_set('max_execution_time', 60*60*10);



// error_reporting ( 0 );

// $GLOBALS['HIDE_UNKNOWN_TAGS'] = TRUE;
// $toolkit_Dir = "PHP_JPEG_Metadata_Toolkit_1.12/";
// https://github.com/evanhunter/PJMT
$toolkit_Dir = "PJMT/";
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'Toolkit_Version.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'JPEG.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'JFIF.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'PictureInfo.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'XMP.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'Photoshop_IRB.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'EXIF.php');
include( plugin_dir_path( __FILE__ ) . $toolkit_Dir. 'Photoshop_File_Info.php');


include( plugin_dir_path( __FILE__ ) . 'ch3-metadata.php');








/***************************************************************
 * Plugin Menu for debugging
 ***************************************************************/
add_action('admin_menu', 'ch3_plugin_menu', 1);

function ch3_plugin_menu(){
    add_menu_page( 'ch3 Plugin', 'ch3 Plugin', 'manage_options', 'ch3-plugin', 'ch3_plugin' );
}

function ch3_plugin(){
    echo "---------------<br>";
    echo "-- S T A R T --<br>";
    echo "ch3-plugin running...<br>";
    echo "---------------<br>";
    echo "--- D O N E ---<br>";
}






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
 * Lazy function to print array
 ***************************************************************/
function print_ar( $ar ){
    print("<pre>".print_r( $ar ,true)."</pre>");
}









/***************************************************************
 * Partial match within an array and return index
 ***************************************************************/
function array_search_partial(& $arr, $keyword) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
    return -1;
}




















/***************************************************************
 * Choose Image engine
 ***************************************************************/
add_filter( 'wp_image_editors', 'select_wp_image_editors' );
 
function select_wp_image_editors( $editors ) {
    // return array( 'WP_Image_Editor_GD', 'WP_Image_Editor_Imagick' );        // Default 
    return array( 'WP_Image_Editor_Imagick', 'WP_Image_Editor_GD' );
}




/***************************************************************
 * INTERMEDIATE FILES HAVE AN EXTRA - AT THE END FOR EASY FILTERING
 ***************************************************************/
add_filter('image_make_intermediate_size', 'custom_rename_images');
function custom_rename_images($image) {
    // Split the $image path
    $info = pathinfo($image);
    $dir = $info['dirname'] . '/';
    $ext = '.' . $info['extension'];
    $name = wp_basename($image, '$ext');

    // New Name
    $new_name = $dir . substr($name, 0, -strlen($ext)) ."-". $ext;

    // Rename the intermediate size
    $did_it = rename($image, $new_name);

    // Return if successful
    if ($did_it) return $new_name;

    // Return on fail
    return $image;
}























/***************************************************************
 * METADATA
 ***************************************************************/
//  Populate title, caption, alt text with the metadata from jpg
/////////////////////////////////////////////////////////////////
add_filter('add_attachment', 'populate_img_metadata');

function populate_img_metadata($img_id) {
    
    // get local file
    $file = wp_normalize_path( get_attached_file( $img_id ) );
    $metadata = getMetadata($file);


    $updatedPost = array();
    $alt = '';

    $updatedPost['ID'] = $img_id;

    if( $metadata['title'] != '' )
    {
        $updatedPost['post_title'] = $metadata['title'];
        $updatedPost['post_name'] = $metadata['title'];
        
        $alt .= $metadata['title'];
    }
    else{
    // In case I remove caption from source img and want to update WP
        $filename = basename(get_attached_file( $img_id ));
        $filename = pathinfo($filename,PATHINFO_FILENAME);

        $updatedPost['post_title'] = $filename;
        $updatedPost['post_name'] = $filename;
    }

    if( $metadata['date'] != '' ){
        // echo "++ SETTING DATE ". $metadata['date'];
        $updatedPost['post_date'] = $metadata['date'];
    }

    $caption = $metadata['caption'];
    if( $caption != '' ) {
        // if(0){
        if( (strpos($caption,'<a')!== false || strpos($caption,'< a')!== false ) && strpos($caption,'>')!== false)
                $caption = str_replace('">','" rel="noopener" target="_blank">', $caption);
        $updatedPost['post_excerpt'] = '';              // Caption
        $updatedPost['post_content'] = $caption;        // Description

        if( $alt != '' )
            $alt .= ' - ';
        $alt .=  wp_strip_all_tags($caption);

    }
    else if( get_post($img_id)->post_content != '' )
        $updatedPost['post_content'] = '';
        // In case I remove caption from source img and want to update WP


    //  Populate the Alternative Text. Not the best content, but better than nothing.
    if(is_array($metadata['keywords']) && count($metadata['keywords']) != 0) {
        foreach ($metadata['keywords'] as $value){
            if( $alt != '' )
                $alt .= ', ';
            $alt .= $value;
        }
    }

    //  Probably bad idea to spam alt with location
    // if( $metadata['location']!='' || $metadata['city']!='' || $metadata['state']!='' || $metadata['country']!='') {
    //     $alt .= '- ';
    //     if( $metadata['location'] != '' )
    //         $alt .= $metadata['location'] .' ';
    //     if( $metadata['city'] != '' )
    //         $alt .= $metadata['city'] .' ';
    //     if( $metadata['state'] != '' )
    //         $alt .= $metadata['state'] .' ';
    //     if( $metadata['country'] != '' )
    //         $alt .= $metadata['country'] .' ';
    // }


    wp_update_post( $updatedPost );
    update_post_meta( $img_id, '_wp_attachment_image_alt', $alt );

}


// Restore Metadata on all intermediate files
/////////////////////////////////////////////////////////////////
add_filter( 'wp_generate_attachment_metadata', 'filter_wp_generate_attachment_metadata', 10, 2 ); 

function filter_wp_generate_attachment_metadata( $metadata, $img_id ) { 
    $file = wp_normalize_path(get_attached_file( $img_id ));
    // All data exist in $metadata, don't use the wp function to query DB

    // Uses global var for custom folder structure
    // global $customDir;

    // Get metadata - function from PJMT
    // Only XMP for the time, hopefully no need to add Exif
    $orig_data = get_jpeg_header_data( $file );
    $orig_XMP_text = get_XMP_text( $orig_data );

        foreach( get_intermediate_image_sizes() as $size ){
        // constract the file name of the intermediate file on dist
        $intFile = $metadata['sizes'][$size]['file'] ;
        $intFile = wp_basename($intFile);
        $intFile = wp_normalize_path( wp_upload_dir()['path'] ) .'/'. $intFile;
        
        

        // Check if the file exist, which is the case for images which don't generate all sizes
        if( is_file($intFile) ) {
            // Get the metadata of the intermediate file and add the XMP from the original
            $int_data = get_jpeg_header_data( $intFile );
            $new_int_data = put_XMP_text($int_data, $orig_XMP_text);

            // Embed metadata
            put_jpeg_header_data( $intFile, $intFile, $new_int_data );
        }
    }
    return $metadata;
}



























// WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP  WIP 
/***************************************************************
 * Bulk Action to update images
 ***************************************************************/
/*
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
*/

































/***************************************************************
 * RATING
 ***************************************************************/



/***************************************************************
 * POST EDITOR - RATING Field box
 ***************************************************************/



add_action( 'post_submitbox_misc_actions', 'add_rating_field' );

function add_rating_field($post){
    wp_nonce_field( plugin_basename( __FILE__ ), 'rating_nonce' );


    echo '<div class="misc-pub-section">';
    echo '<span class="dashicons dashicons-star-filled" style="vertical-align: sub"></span>';

    if( $post->rating == 1 )
    // if( get_post_meta( $_GET['post'], 'rating', true ) == 1 )
        echo '<span class="rating" style="padding-left: 8px">Rated : <input type="checkbox" name="rating_value" checked value="rated" style=""></span>';
    else
        echo '<span class="rating" style="padding-left: 8px">Rated : <input type="checkbox" name="rating_value" value="rated" style=""></span>';
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

    // php8
    // security check
    // if ( !wp_verify_nonce( $_POST['rating_nonce'], plugin_basename( __FILE__ ) ) )
    //     return $post_id;
    
    // php8
    // if ( ! current_user_can( 'edit_post', $post_id ) || 'post' != $_POST['post_type'] )
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return $post_id;


    // get input rating and store it on the post metadata
    // $value = $_POST['rating_value'];
    // echo 'V : ' . $value . '<br>';
    // update_post_meta( $post_id, 'rating', $value );

    if( isset($_POST['rating_value']) && $_POST['rating_value'] == "rated")
    // if( $_POST['rating_value'] != "rated" )
        $value = 1;
    else
        $value = 0;

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
    $columns['rating'] = 'Rated';
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
    if( $value )
        echo '<span class="dashicons dashicons-star-filled" style="vertical-align: sub"></span>';
    // echo $value ;
}


/***************************************************************/
// Quick edit display


add_action( 'quick_edit_custom_box', 'quick_edit_rating', 10, 2 );

function quick_edit_rating( $column_name, $post_type ) {
    if ( 'rating' != $column_name )
        return;

    wp_nonce_field( plugin_basename( __FILE__ ), 'rating_nonce' );
    echo '<div class="inline-edit-group wp-clearfix" >';
    echo '<span class="dashicons dashicons-star-filled" style="vertical-align: sub"></span>';
    echo '<input type="checkbox" name="rating_value" class="ratingClass" value="rated">Rated ';
    
    echo '</div>';


}







/***************************************************************/
// Quick edit save
// add_action( 'save_post', 'quick_edit_rating_save', 20, 1 );

// BUG - the check box doesn't show the correct state when re-opened, right after quick update


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
 

    if( $_POST['rating_value'] == "rated" )
        $value = 1;
    else
        $value = 0;

    update_post_meta( $post_id, 'rating', 9 );
}


/***************************************************************/
// Populate initial value - javaScript
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
        if( fieldValue == 1)
            jQuery( '.ratingClass' ).prop('checked', true);
        else
            jQuery( '.ratingClass' ).prop('checked', false);

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