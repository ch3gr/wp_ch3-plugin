<?php




/***************************************************************
 * GET META DATA
 ***************************************************************/

// Recursive function to locate desired metadata tag
// get_tag_value( $ar, 'photoshop:Country', 'tag', 'value'){
function get_tag_value_old(array &$metadata, $tag, $keyname, $dataname){
    // echo ' A IN<br>';
    unset( $out );
    if( is_array($metadata) ){
        // echo ' B<br>';
       if( array_key_exists($keyname, $metadata) && 
           array_key_exists($dataname, $metadata) &&
           $metadata[$keyname]==$tag ){

            // echo '__FOUND__<br>';
            $out = $metadata[$dataname];
        } else {
            foreach ($metadata as $key => $value) {
                // echo ' D ' . $key . ' ' . $value. " " .is_array($value) .' <br>';
                if( is_array($value) ){
                    // echo ' E <br>';
                    $out = get_tag_value($value, $tag, $keyname, $dataname);
                    if( isset($out) )
                        break;
                }
            }
        }
    }
    // echo ' F OUT<br>';
    if( isset( $out )){
        // echo ' C ' . $out. ' <br>';
        return $out;
    }
}
 /************************ NOT USED ***************************/









// Recursive function to locate the block with the matching tag
// get_tag_block( $XMP_array, 'dc:description', 'tag')
function get_tag_block(array &$metadata, $tag, $keyname){
    // echo ' A IN<br>';
    unset( $out );
    if( is_array($metadata) ){
        // echo ' B<br>';
       if( array_key_exists($keyname, $metadata) && $metadata[$keyname]==$tag ){

            // echo '__FOUND__<br>';
            $out = $metadata;
        } else {
            foreach ($metadata as $key => $value) {
                // echo ' D ' . $key . ' ' . $value. " " .is_array($value) .' <br>';
                if( is_array($value) ){
                    // echo ' E <br>';
                    $out = get_tag_block($value, $tag, $keyname);
                    if( isset($out) )
                        break;
                }
            }
        }
    }
    // echo ' F OUT<br>';
    if( isset( $out )){
        // echo ' C ' . $out. ' <br>';
        return $out;
    }
}


// Recursive function to get the value
// get_value( $t, 'value');
function get_value(array &$metadata, $keyname){
    // echo ' A IN<br>';
    unset( $out );
    
    // echo ' B<br>';
   if( array_key_exists($keyname, $metadata)){

        // echo '__FOUND__<br>';
        $out = $metadata[$keyname];
    } else {
        foreach ($metadata as $key => $value) {
            // echo ' D ' . $key . ' ' . $value. " " .is_array($value) .' <br>';
            if( is_array($value) ){
                // echo ' E <br>';
                $out = get_value($value, $keyname);
                if( isset($out) )
                    break;
            }
        }
    }

    // echo ' F OUT<br>';
    if( isset( $out )){
        // echo ' C ' . $out. ' <br>';
        return $out;
    }
}


// Recursive function to get multiple values as array
// get_value( $t, 'value');
// function get_values(array &$metadata, $keyname){
function get_values(array &$metadata, $keyname){
    // echo ' A IN<br>';
    $out = array();
    
    // echo ' B >>'.$keyname.'<br>';
    if( array_key_exists($keyname, $metadata)){
        // echo '__FOUND__ :: '. $metadata[$keyname]. '<br>';
        $out[0] = $metadata[$keyname];

    } else {
        foreach ($metadata as $key => $value) {
            // echo ' D ' . $key . ' ' . $value. " " .is_array($value) .' <br>';
            if( is_array($value) ){
                // echo ' E <br>';

                $newValue = get_values($value, $keyname);
                $out = array_merge($out, $newValue);
            }
        }
    }

    // echo ' F OUT<br>';
    // echo ' <br> ............................................ <<<<<<';
    // print("<pre>".print_r( $out ,true)."</pre>");

    return $out;
}


// Combines the two functions above
function get_tag_value(array &$metadata, $tag, $keyname, $dataname){
    $block = get_tag_block( $metadata, $tag, $keyname);
    if( !is_null($block) )
        return get_value( $block, $dataname);
    else
        return '';
}

// Combines the two functions above
function get_tag_values(array &$metadata, $tag, $keyname, $dataname){
    $block = get_tag_block( $metadata, $tag, $keyname);
    if( !is_null($block) )
        return get_values( $block, $dataname);
    else
        return array();

    // varried output type
    // $out = get_values( $block, $dataname);
    // if( sizeof($out) > 1 )
    //     return $out;
    // else
    //     return $out[0];
}












/***************************************************************
 *  MASTER function to return array of metadata
 ***************************************************************/
//  Config in case of future fuck up
function getMetadata($filename){
    $metadata = array();

    $jpeg_header_data = get_jpeg_header_data( $filename );
    
    $Exif_array = get_EXIF_JPEG( $filename );
    $metadata['date'] = get_tag_value( $Exif_array, 'Date and Time of Original', 'Tag Name', 'Text Value');

    $XMP_array = read_XMP_array_from_text( get_XMP_text( $jpeg_header_data ) );
    if( is_null($XMP_array) != ''  ){
        $metadata['title'] = get_tag_value( $XMP_array, 'photoshop:Headline', 'tag', 'value');
        $metadata['caption'] = get_tag_value( $XMP_array, 'dc:description', 'tag', 'value');

        // $metadata['product'] = get_tag_value( $XMP_array, 'dc:title', 'tag', 'value');
        // $metadata['event'] = get_tag_value( $XMP_array, 'mediapro:Event', 'tag', 'value');
        // $metadata['creator'] = get_tag_value( $XMP_array, 'dc:creator', 'tag', 'value');
        // $metadata['rights'] = get_tag_value( $XMP_array, 'dc:rights', 'tag', 'value');
        // $metadata['url'] = get_tag_value( $XMP_array, 'xapRights:WebStatement', 'tag', 'value');

        $metadata['location'] = get_tag_value( $XMP_array, 'Iptc4xmpCore:Location', 'tag', 'value');
        $metadata['city'] = get_tag_value( $XMP_array, 'photoshop:City', 'tag', 'value');
        $metadata['state'] = get_tag_value( $XMP_array, 'photoshop:State', 'tag', 'value');
        $metadata['country'] = get_tag_value( $XMP_array, 'photoshop:Country', 'tag', 'value');
        $metadata['keywords'] = get_tag_values( $XMP_array, 'dc:subject', 'tag', 'value');
    }



    unset($info);
    $size = getimagesize($filename, $info);
    $iptc = iptcparse($info['APP13']);
    // $metadata = array_merge($metadata, $iptc);

    if( $metadata['title'] == '' )
        $metadata['title'] = $iptc['2#005'][0];

    if( $metadata['caption'] == '' )
        $metadata['caption'] = $iptc['2#120'][0];

    if( $metadata['city'] == '' )
        $metadata['city'] = $iptc['2#090'][0];
    if( $metadata['state'] == '' )
        $metadata['state'] = $iptc['2#095'][0];
    if( $metadata['country'] == '' )
        $metadata['country'] = $iptc['2#101'][0];

    if( isset($metadata['keywords']) && !count($metadata['keywords']) )
        $metadata['keywords'] = $iptc['2#025'];
    else 
        $metadata['keywords'] = array();


    return $metadata;
}

//  Title
//  Caption
//  Alt = location + city + state + country + keywords




/***************************************************************
 * GET META DATA                    END END END
 ***************************************************************/




?>