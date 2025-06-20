<?php

// LOADING FILES AND DEPENDIENCIES

add_action('wp_enqueue_scripts', 'my_rodo_assets');
function my_rodo_assets()
{
    if (!is_page_template('page-rodo.php')) return;

    wp_enqueue_style(
        'rodo-style',
        get_stylesheet_directory_uri() . '/css/rodo.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/rodo.css')
    );

    wp_enqueue_script(
        'rodo-script',
        get_stylesheet_directory_uri() . '/js/rodo.js',
        [],
        filemtime(get_stylesheet_directory() . '/js/rodo.js'),
        true
    );

    wp_localize_script('rodo-script', 'rodo_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rodo_nonce')
    ]);
}