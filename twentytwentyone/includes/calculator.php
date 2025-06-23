<?php


// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'calculator_enqueue_assets');
add_action('wp_ajax_save_budget', 'save_budget_callback');


// ---- LOADING ASSETS ----
function calculator_enqueue_assets()
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

    wp_localize_script('calculator-script', 'calculatorAjax', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
}


// ---- wysyałnie wyników ----


function save_budget_callback()
{

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $user_id = get_current_user_id();
    $raw_input = file_get_contents('php://input');
    error_log('RAW: ' . $raw_input);



    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Błąd JSON: ' . json_last_error_msg());
    }

    global $wpdb;
    $table_results = $wpdb->prefix . 'calculator_results';
    $table_ideas = $wpdb->prefix . 'calculator_ideas';

    $state = json_decode(stripslashes($_POST['state']), true);

    if (!$state) {
        wp_send_json_error('Brak danych lub zły format');
    }

    $result_json = maybe_serialize($state);

    $wpdb->insert(
        $table_results,
        [
            'user_id' => $user_id,
            'result' => $result_json,
            'created_at' => current_time('mysql'),
        ]
    );

    $result_id = $wpdb->insert_id;

    if ($result_id && !empty($state['pomysly'])) {
        foreach ($state['pomysly'] as $idea) {
            $wpdb->insert(
                $table_ideas,
                [
                    'result_id' => $result_id,
                    'idea_name' => sanitize_text_field($idea['name']),
                    'idea_price' => floatval($idea['price']),
                ]
            );
        }
    }

    wp_send_json_success('Zapisane!');
}
