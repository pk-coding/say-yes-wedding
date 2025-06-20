<?php

add_action('wp_enqueue_scripts', 'my_calculator_assets');
function my_calculator_assets()
{
    if (!is_page_template('page-calculator.php')) return;

    wp_enqueue_style(
        'calculator-style',
        get_stylesheet_directory_uri() . '/css/calculator.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/calculator.css')
    );

    wp_enqueue_script(
        'calculator-script',
        get_stylesheet_directory_uri() . '/js/calculator.js',
        [],
        filemtime(get_stylesheet_directory() . '/js/calculator.js'),
        true
    );

    wp_localize_script('calculator-script', 'calculator_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('save_result_nonce'),
        'is_logged_in' => is_user_logged_in()
    ]);
}

add_action('wp_ajax_save_user_result', 'save_user_result');
function save_user_result()
{
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'save_result_nonce')) {
        wp_send_json_error(['message' => 'Nieprawidłowy nonce']);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Użytkownik niezalogowany']);
    }

    $user_id = get_current_user_id();
    $result = sanitize_text_field($_POST['result'] ?? '');

    global $wpdb;
    $table = $wpdb->prefix . 'calculator_results';

    $inserted = $wpdb->insert($table, [
        'user_id' => $user_id,
        'result' => $result,
    ], ['%d', '%s']);

    if ($inserted === false) {
        wp_send_json_error(['message' => 'Błąd zapisu do bazy danych']);
    }

    wp_send_json_success();
}
