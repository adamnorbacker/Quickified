<?php
defined('ABSPATH') or die('You are not allowed here.');
// Deactivate jQuery Migrate on frontend
function dequeue_jquery_migrate($scripts)
{
    if (!empty($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, array(
            'jquery-migrate',
        ));
    }
}
add_action('wp_default_scripts', 'dequeue_jquery_migrate');
