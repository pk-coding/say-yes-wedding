<?php

// LOADING FILES AND DEPENDIENCIES

add_action('wp_enqueue_scripts', 'my_statute_assets');
function my_statute_assets()
{
    if (!is_page_template('page-statute.php')) return;

    wp_enqueue_style(
        'statute-style',
        get_stylesheet_directory_uri() . '/css/statute.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/statute.css')
    );

    wp_enqueue_script(
        'statute-script',
        get_stylesheet_directory_uri() . '/js/statute.js',
        [],
        filemtime(get_stylesheet_directory() . '/js/statute.js'),
        true
    );

    wp_localize_script('statute-script', 'statute_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('statute_nonce')
    ]);
}
