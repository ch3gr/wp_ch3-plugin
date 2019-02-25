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
 * Plugin Menu for testing
 ***************************************************************/
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













/***************************************************************
 * RATING
 ***************************************************************/



/***************************************************************
 * POST EDITOR - RATING Field box
 ***************************************************************/
// register the meta box
add_action( 'add_meta_boxes', 'meta_box_rating' );
function meta_box_rating() {
    add_meta_box(
        'meta_box_rating_id',          // this is HTML id of the box on edit screen
        'Post Rating',    // title of the box
        'meta_box_rating_content',   // function to be called to display the checkboxes, see the function below
        'post',        // on which edit screen the box should appear
        'normal',      // part of page where the box should appear
        'default'      // priority of the box
    );
}

/***************************************************************/
// display the metabox
function meta_box_rating_content() {
    // nonce field for security check, you can have the same
    // nonce field for all your meta boxes of same plugin
    wp_nonce_field( plugin_basename( __FILE__ ), 'rating_nonce' );

    // get pre existing rating value
    $value = get_post_meta( $_GET['post'], 'rating', true );
    if( $value == '' )
    	$value = -999;
    echo '<input type="number" min="-10" max="10" name="rating_value" value="'.$value.'" > -10 ... 10 ';
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
 
    // printf( '
    //     <input type="checkbox" name="headline_news" class="headline_news"> %s',
    //     'Headline news position'
    // );

    // $value = get_post_meta( $post_id, 'rating', true );
    // if( $value == '' )
    // 	$value = 0;
    wp_nonce_field( plugin_basename( __FILE__ ), 'rating_nonce' );
    echo '<input type="number" min="-10" max="10" name="rating_value" class="ratingClass" value="1111"> Rating ';
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