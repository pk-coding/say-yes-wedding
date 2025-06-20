<?php


// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'resetpassword_enqueue_assets');

add_action('wp_ajax_nopriv_custom_forgot_password_service', 'custom_forgot_password_service');
add_action('wp_ajax_custom_forgot_password_service', 'custom_forgot_password_service');

add_action('wp_ajax_nopriv_custom_reset_password', 'custom_reset_password');
add_action('wp_ajax_custom_reset_password', 'custom_reset_password');

add_action('password_reset', 'remove_login_block_on_password_reset');


// ---- LOADING ASSETS ----
function resetpassword_enqueue_assets()
{
    if (!is_page_template('page-resetpassword.php')) return;

    $uri = get_stylesheet_directory_uri();
    $path = get_stylesheet_directory();

    wp_enqueue_style(
        'resetpassword-style',
        "$uri/css/resetpassword.css",
        [],
        filemtime("$path/css/resetpassword.css")
    );

    wp_enqueue_script(
        'resetpassword-script',
        "$uri/js/resetpassword.js",
        [],
        filemtime("$path/js/resetpassword.js"),
        true
    );

    wp_localize_script('resetpassword-script', 'login_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('login_nonce'),
    ]);
}


// ---- HANDLE PASSWORD RESET FORM ----
function custom_forgot_password_service()
{
    check_ajax_referer('login_nonce', 'security');

    $user_input = sanitize_text_field($_POST['username_or_email'] ?? '');
    if (empty($user_input)) {
        wp_send_json_error('Podaj nazwę użytkownika lub email.');
    }

    $user = get_user_by_login_or_email_for_reset($user_input);
    if (!$user) {
        wp_send_json_error('Nie znaleziono użytkownika.');
    }

    $reset_key = get_password_reset_key($user);
    if (is_wp_error($reset_key)) {
        wp_send_json_error('Błąd podczas generowania klucza resetu.');
    }

    $reset_url = generate_password_reset_url($user, $reset_key);
    $mail_sent = send_password_reset_email($user, $reset_url);

    if (!$mail_sent) {
        wp_send_json_error('Nie udało się wysłać wiadomości e-mail.');
    }

    wp_send_json_success('Link do resetowania hasła został wysłany.');
}


// ---- GET USER BY LOGIN OR EMAIL HELPERS ----
function get_user_by_login_or_email_for_reset($input)
{
    return is_email($input)
        ? get_user_by('email', $input)
        : get_user_by('login', $input);
}


// ---- GENERATE PASSWORD RESET URL HELPERS ----
function generate_password_reset_url($user, $key)
{
    return add_query_arg([
        'key'   => $key,
        'login' => rawurlencode($user->user_login)
    ], home_url('/resetowaniehasla'));
}


// ---- SEND PASSWORD RESET EMAIL HELPERS ----
function send_password_reset_email($user, $url)
{
    $subject = 'Resetowanie hasła';
    $message = "Kliknij poniższy link, aby ustawić nowe hasło:\n\n{$url}\n\nJeśli to nie Ty, zignoruj tę wiadomość.";

    return wp_mail($user->user_email, $subject, $message);
}


// ---- HANDLE SET NEW PASSWORD FORM ----
function custom_reset_password()
{
    check_ajax_referer('login_nonce', 'security');

    $data = collect_password_reset_data();

    $error = validate_new_password_data($data);
    if ($error) {
        wp_send_json_error($error);
    }

    $user = get_user_by('login', $data['login']);
    if (!$user) {
        wp_send_json_error('Nie znaleziono użytkownika.');
    }

    $check = check_password_reset_key($data['reset_key'], $user->user_login);
    if (is_wp_error($check)) {
        wp_send_json_error('Niepoprawny lub wygasły link resetu hasła.');
    }

    $reset = reset_password($user, $data['new_password']);
    if (!is_wp_error($reset)) {
        remove_login_block_on_password_reset($user);
        wp_send_json_success('Hasło zostało pomyślnie zmienione.');
    }
}


// ---- COLLECT NEW PASSWORD DATA HELPERS ----
function collect_password_reset_data()
{
    return [
        'reset_key'        => sanitize_text_field($_POST['reset_key'] ?? ''),
        'login'            => sanitize_text_field($_POST['login'] ?? ''),
        'new_password'     => $_POST['new_password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
    ];
}


// ---- VALIDATE NEW PASSWORD FORM DATA HELPERS ----
function validate_new_password_data($data)
{
    $errors = [];

    // Required fields
    if (empty($data['reset_key']) || empty($data['login']) || empty($data['new_password']) || empty($data['confirm_password'])) {
        $errors[] = 'Wszystkie pola są wymagane.';
        return $errors; // end because further validation is nosense
    }

    // Password length
    if (strlen($data['new_password']) < 12 || strlen($data['new_password']) > 80) {
        $errors[] = 'Hasło musi mieć od 12 do 80 znaków.';
    }

    // Capital letter
    if (!preg_match('/[A-Z]/', $data['new_password'])) {
        $errors[] = 'Hasło musi zawierać co najmniej jedną dużą literę.';
    }

    // Digit
    if (!preg_match('/\d/', $data['new_password'])) {
        $errors[] = 'Hasło musi zawierać co najmniej jedną cyfrę.';
    }

    // Allowed signes
    if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $data['new_password'])) {
        $errors[] = 'Hasło może zawierać tylko litery, cyfry, kropki, podkreślenia i myślniki.';
    }

    // Confrim password
    if ($data['new_password'] !== $data['confirm_password']) {
        $errors[] = 'Hasła muszą być takie same.';
    }

    return empty($errors) ? null : $errors;
}


// REMOVE LOGIN BLOCK
function remove_login_block_on_password_reset($user)
{
    reset_user_login_attempts($user->ID);
    unblock_user_login($user->ID);
}
