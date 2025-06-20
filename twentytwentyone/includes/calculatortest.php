<?php

add_action('wp_enqueue_scripts', 'my_calculatortest_assets');
function my_calculatortest_assets()
{
    if (!is_page_template('page-calculatortest.php')) return;

    wp_enqueue_style(
        'calculatortest-style',
        get_stylesheet_directory_uri() . '/css/calculatortest.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/calculatortest.css')
    );

    wp_enqueue_script(
        'calculatortest-script',
        get_stylesheet_directory_uri() . '/js/calculatortest.js',
        [],
        filemtime(get_stylesheet_directory() . '/js/calculatortest.js'),
        true
    );

    wp_localize_script('calculatortest-script', 'calculatortest_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('save_result_nonce'),
        'is_logged_in' => is_user_logged_in()
    ]);
}