<?php
defined('ABSPATH') or die('You are not allowed here.');
add_action('init', 'get_all_image_sizes');

function get_all_image_sizes(){
$args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image/jpeg,image/jpg,image/png',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'orderby' => 'id',
        'order' => 'ASC'
    );
    // Get all the available thumbnail sizes
    $sizes = get_intermediate_image_sizes();
    // Query the attachments
    $query_images = new WP_Query( $args );
    $images = array();
    // Run a loop
    if ( $query_images->have_posts() ){
        while ($query_images->have_posts()){
            $query_images->the_post();
            // For each attachment size, store its URL in an array
            foreach ( $sizes as $key => $size ) {
                $thumbnails[$key] = wp_get_attachment_image_src( get_the_ID(), $size)[0];
            }
            $images = array_merge( $thumbnails , $images );
        }
        return $images;
    }
	print_r($images);
}