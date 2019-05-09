<?php
defined('ABSPATH') or die('You are not allowed here.');
function handle_imgs( $post_ID ) {

	// Check if uploaded file is an image, else do nothing

	if ( wp_attachment_is_image( $post_ID ) ) {

		$post = get_post($post_ID);
		$file = get_attached_file($post_ID);
		// $filesizeBytes = filesize($file);
		// $filesize = number_format($filesizeBytes / 1024, 1);
		// logError("Url: $file - Filesize: $filesize".'KB');
		$path = pathinfo($file);
		$removedExt = $path['filename'];
		$newfilename = $removedExt.'_test';
    $newfile = $path['dirname']."/".$newfilename.".".$path['extension'];

		$my_image_title = get_post( $post_ID )->post_title;

		$my_image_title = preg_replace( '/\s*[-_\s]+\s*/mi', ' ',  $my_image_title );

		$my_image_title = ucwords( strtolower( $my_image_title ) );

		$my_image_meta = array(
			'ID'		=> $post_ID,
			'post_title'	=> $my_image_title,
			'post_excerpt'	=> $my_image_title,
			'post_content'	=> $my_image_title,
		);

		// Set the image Alt-Text
		update_post_meta( $post_ID, '_wp_attachment_image_alt', $my_image_title );

		// Set the image meta (e.g. Title, Excerpt, Content)

		wp_update_post( $my_image_meta );

		if(get_option('image_optimization-checkbox')){
			include_once plugin_dir_path(__DIR__).'../api/apirequestor.php';
			processImage($file);
		}
		clearstatcache();
		// $filesizeBytes = filesize($file);
		// $filesize = number_format($filesizeBytes / 1024, 1);
		// logError("Optimization is done with file: $file - Filesize: $filesize".'KB');

		//Uppdatera filnamn, avakta med detta, kanske borde köra på att kolla db istället för att verifiera optimerad fil.
		//rename($file, $newfile);
		//update_attached_file( $post_ID, $newfile );
		//wp_update_attachment_metadata( $post_ID, wp_generate_attachment_metadata( $post_ID, $file ) );
	}
}
add_action( 'add_attachment', 'handle_imgs' );