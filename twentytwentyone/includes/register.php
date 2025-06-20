<?php


// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'register_assets');

add_action('wp_ajax_nopriv_custom_user_register', 'custom_user_register');
add_action('wp_ajax_custom_user_register', 'custom_user_register');


// ---- LOADING ASSETS ----
function register_assets()
{
    if (!is_page_template('page-register.php')) return;

    wp_enqueue_style(
        'register-style',
        get_stylesheet_directory_uri() . '/css/register.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/register.css')
    );

    wp_enqueue_script(
        'register-script',
        get_stylesheet_directory_uri() . '/js/register.js',
        [],
        filemtime(get_stylesheet_directory() . '/js/register.js'),
        true
    );

    wp_localize_script('register-script', 'register_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('register_nonce'),
        'redirect_url' => esc_url(site_url('/panel'))
    ]);
}


// ---- HANDLE REGISTER FORM ----
function custom_user_register()
{
    check_ajax_referer('register_nonce', 'security');

    $data = collect_registration_data($_POST);

    $error = validate_registration_data($data);
    if ($error) {
        wp_send_json_error($error);
    }

    if (is_existing_user($data['username'], $data['email'])) {
        wp_send_json_error('Nazwa użytkownika lub email już istnieje.');
    }

    $user_id = create_new_user($data);
    if (is_wp_error($user_id)) {
        wp_send_json_error('Błąd podczas rejestracji.');
    }

    save_user_meta_data($user_id, $data);

    wp_send_json_success('Rejestracja zakończona sukcesem!');
}


// ---- COLLECT REGISTRATION FORM DATA ----
function collect_registration_data($post)
{
    return [
        'username' => sanitize_user($post['username'] ?? ''),
        'email' => sanitize_email($post['email'] ?? ''),
        'password' => $post['password'] ?? '',
        'password_confirm' => $post['password_confirm'] ?? '',
        'role' => sanitize_text_field($post['role'] ?? ''),
        'statute' => $post['statute'] ?? null,
        'rodo' => $post['rodo'] ?? null,
    ];
}


// ---- VALIDATE REGISTRATION FORM DATA ----
function validate_registration_data($data)
{
    $errors = [];

    // Wymagane pola
    if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        $errors[] = 'Wszystkie pola są wymagane.';
    }

    // Email
    if (strlen($data['email']) < 6 || strlen($data['email']) > 254) {
        $errors[] = 'Adres e-mail musi mieć od 6 do 254 znaków.';
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Podaj poprawny adres e-mail.';
    }

    // Username
    if (strlen($data['username']) < 3 || strlen($data['username']) > 32) {
        $errors[] = 'Nazwa użytkownika musi mieć od 3 do 32 znaków.';
    }

    if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $data['username'])) {
        $errors[] = 'Nazwa użytkownika może zawierać tylko litery, cyfry, kropki, podkreślenia i myślniki.';
    }

    // Password
    if (strlen($data['password']) < 12 || strlen($data['password']) > 80) {
        $errors[] = 'Hasło musi mieć od 12 do 80 znaków.';
    }

    if (!preg_match('/[A-Z]/', $data['password'])) {
        $errors[] = 'Hasło musi zawierać co najmniej jedną dużą literę.';
    }

    if (!preg_match('/\d/', $data['password'])) {
        $errors[] = 'Hasło musi zawierać co najmniej jedną cyfrę.';
    }

    if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $data['password'])) {
        $errors[] = 'Hasło może zawierać tylko litery, cyfry, kropki, podkreślenia i myślniki.';
    }

    // Potwierdzenie hasła
    if (!isset($data['password_confirm']) || $data['password'] !== $data['password_confirm']) {
        $errors[] = 'Hasła nie są takie same.';
    }

    // Rola
    if (empty($data['role']) || !in_array($data['role'], ['pan_mlody', 'panna_mloda'], true)) {
        $errors[] = 'Nieprawidłowa rola.';
    }

    // Regulaminy
    if (empty($data['statute']) || $data['statute'] !== '1') {
        $errors[] = 'Musisz zaakceptować regulamin.';
    }

    if (empty($data['rodo']) || $data['rodo'] !== '1') {
        $errors[] = 'Musisz zaakceptować politykę prywatności.';
    }

    return empty($errors) ? null : $errors;
}


// ---- CHECK IF USER OR EMAIL ALREADY EXISTS HELPERS ----
function is_existing_user($username, $email)
{
    return username_exists($username) || email_exists($email);
}


// ---- CREATE NEW USER HELPERS ----
function create_new_user($data)
{
    return wp_create_user($data['username'], $data['password'], $data['email']);
}


// ---- SAVE DATA REGARDING TO ROLE, ACCEPTED STATUTE AND RODO, user_mata TABLE HELPERS ----
function save_user_meta_data($user_id, $data)
{
    update_user_meta($user_id, 'user_role', $data['role']);
    update_user_meta($user_id, 'accepted_statute', $data['statute'] === '1' ? 1 : 0);
    update_user_meta($user_id, 'accepted_rodo', $data['rodo'] === '1' ? 1 : 0);
}
