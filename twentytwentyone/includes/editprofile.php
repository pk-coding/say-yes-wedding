<?php


// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'editprofile_enqueue_assets');
add_action('wp_footer', 'print_editprofile_ajax_obj_script', 5);

add_action('wp_ajax_custom_update_email', 'custom_update_email');
add_action('wp_ajax_custom_update_password', 'custom_update_password');
add_action('wp_ajax_custom_remove_partner_request', 'custom_remove_partner_request');
add_action('wp_ajax_custom_update_custom_user_role', 'custom_update_custom_user_role');


// ---- LOAD ASSETS ----
function editprofile_enqueue_assets()
{
    if (!is_page_template('page-editprofile.php')) return;

    wp_enqueue_style(
        'editprofile-style',
        get_stylesheet_directory_uri() . '/css/editprofile.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/editprofile.css')
    );

    wp_enqueue_script(
        'editprofile-script',
        get_stylesheet_directory_uri() . '/js/editprofile.js',
        [],
        filemtime(get_stylesheet_directory() . '/js/editprofile.js'),
        true
    );
}


// print_editprofile_ajax_obj_script INSTEAD OF LOAD: wp_localize_script() in editprofile_enqueue_assets --> regarding CSP POLICY
function print_editprofile_ajax_obj_script()
{
?>
    <script <?php echo csp_nonce_attr(); ?>>
        window.profile_ajax_obj = window.profile_ajax_obj || {
            ajax_url: "<?php echo admin_url('admin-ajax.php'); ?>",
            nonce_email: "<?php echo wp_create_nonce('profile_update_email'); ?>",
            nonce_password: "<?php echo wp_create_nonce('profile_update_password'); ?>",
            nonce_remove_partner: "<?php echo wp_create_nonce('profile_remove_partner'); ?>",
            nonce_user_role_custom: "<?php echo wp_create_nonce('profile_update_custom_user_role'); ?>",
        };
    </script>
<?php
}

// ---- HANDLE EMAIL UPDATE ----
function custom_update_email()
{
    check_ajax_referer('profile_update_email', 'security');

    $data = collect_email_update_data($_POST);

    $error = validate_email_update_data($data);
    if ($error) {
        wp_send_json_error($error);
    }

    $result = update_user_email($data['user_id'], $data['new_email']);
    if (is_wp_error($result)) {
        wp_send_json_error('Nie udało się zaktualizować e-maila.');
    }

    wp_logout();
    wp_send_json_success([
        'message' => 'Adres e-mail został zaktualizowany. Zaloguj się ponownie.',
        'redirect' => home_url('/logowanie')
    ]);
}


// ---- HANDLE PASSWORD UPDATE ----
function custom_update_password()
{
    check_ajax_referer('profile_update_password', 'security');

    $data = collect_password_update_data($_POST);

    $error = validate_password_update_data($data);
    if ($error) {
        wp_send_json_error($error);
    }

    $success = update_user_password($data['user_id'], $data['new_password']);
    if (!$success) {
        wp_send_json_error('Nie udało się zmienić hasła.');
    }

    wp_logout();
    wp_send_json_success([
        'message' => 'Hasło zostało zmienione. Zaloguj się ponownie.',
        'redirect' => home_url('/logowanie')
    ]);
}


// ---- COLLECT EMAIL DATA ----
function collect_email_update_data($post)
{
    return [
        'new_email' => sanitize_email($post['new_email'] ?? ''),
        'user_id' => get_current_user_id(),
    ];
}


// ---- VALIDATE EMAIL ----
function validate_email_update_data($data)
{
    $errors = [];

    if (empty($data['new_email'])) {
        $errors[] = 'Wszystkie pola są wymagane.';
        return $errors; // end because further validation is nosense
    }

    if (strlen($data['new_email']) < 6 || strlen($data['new_email']) > 254) {
        $errors[] = 'Adres e-mail musi mieć od 6 do 254 znaków.';
    }

    if (!filter_var($data['new_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Podaj poprawny adres e-mail.';
    }

    $existing_user_id = email_exists($data['new_email']);
    if ($existing_user_id && (int) $existing_user_id !== (int) $data['user_id']) {
        $errors[] = 'Ten adres e-mail jest już używany.';
    }

    return empty($errors) ? null : $errors;
}


// ---- UPDATE EMAIL ----
function update_user_email($user_id, $new_email)
{
    $result = wp_update_user([
        'ID' => $user_id,
        'user_email' => $new_email,
    ]);

    return !is_wp_error($result);
}


// ---- COLLECT PASSWORD DATA ----
function collect_password_update_data($post)
{
    return [
        'current_password' => $post['current_password'] ?? '',
        'new_password' => $post['new_password'] ?? '',
        'confirm_password' => $post['confirm_password'] ?? '',
        'user_id' => get_current_user_id(),
    ];
}


// ---- VALIDATE PASSWORD ----
function validate_password_update_data($data)
{
    $errors = [];

    // Required fields
    if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
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

    $user = wp_get_current_user();
    if (!wp_check_password($data['current_password'], $user->user_pass, $user->ID)) {
        $errors[] = 'Błędne aktualne hasło.';
    }

    return empty($errors) ? null : $errors;
}


// ---- UPDATE PASSWORD ----
function update_user_password($user_id, $new_password)
{
    $result = wp_update_user([
        'ID' => $user_id,
        'user_pass' => $new_password,
    ]);

    return !is_wp_error($result);
}


// ---- REMOVE PARTNER RELATIONSHIP ----
function custom_remove_partner_request()
{
    check_ajax_referer('profile_remove_partner', 'security');

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Musisz być zalogowany.');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    // Znajdź aktywny rekord relacji (status = 'accepted'), gdzie user jest sender lub invited
    $invite = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table 
         WHERE status = 'accepted' 
           AND (sender_user_id = %d OR invited_user_id = %d)
         LIMIT 1",
        $user_id,
        $user_id
    ));

    if (!$invite) {
        wp_send_json_error('Nie masz aktywnej relacji z partnerem.');
    }

    // Stwórz unikalny token
    $token = bin2hex(random_bytes(32));

    // Zapisz token i powiązanie z rekordem zaproszenia (np. w tabeli wp_user_invites, dodaj kolumnę remove_token i remove_token_expires)
    save_remove_partner_token($invite->id, $token);

    // Wyślij maila z linkiem potwierdzającym
    $user = get_userdata($user_id);
    $user_email = $user->user_email;

    $remove_link = add_query_arg([
        'remove_partner_token' => $token,
        'user' => $user_id,
    ], home_url('/potwierdz-usuniecie-partnera')); // Podmień na stronę obsługującą potwierdzenie

    $subject = 'Potwierdź usunięcie relacji z partnerem';
    $message = "Kliknij w poniższy link, aby potwierdzić usunięcie relacji z partnerem:\n\n" . $remove_link . "\n\nLink jest ważny 24 godziny.";

    $sent = wp_mail($user_email, $subject, $message);

    if (!$sent) {
        wp_send_json_error('Nie udało się wysłać e-maila.');
    }

    wp_send_json_success([
        'message' => 'Na Twój adres e-mail został wysłany link potwierdzający usunięcie relacji.'
    ]);
}

// Pomocnicza funkcja do zapisu tokena i czasu wygaśnięcia w bazie
function save_remove_partner_token($invite_id, $token)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    $wpdb->update(
        $table,
        [
            'remove_token' => $token,
            'remove_token_expires' => date('Y-m-d H:i:s', time() + 24 * 3600),
            'updated_at' => current_time('mysql'),
        ],
        ['id' => $invite_id],
        ['%s', '%s', '%s'],
        ['%d']
    );
}

// Funkcja do weryfikacji tokena przy potwierdzeniu usunięcia relacji
function verify_remove_partner_token($user_id, $token)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    $invite = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE remove_token = %s AND (sender_user_id = %d OR invited_user_id = %d)",
        $token,
        $user_id,
        $user_id
    ));

    if (!$invite) {
        return new WP_Error('invalid_token', 'Niepoprawny token.');
    }

    // Sprawdź czy token nie wygasł
    if (strtotime($invite->remove_token_expires) < time()) {
        return new WP_Error('expired_token', 'Token wygasł.');
    }

    return $invite;
}

// Funkcja, którą wywołasz po kliknięciu linku z tokenem, aby usunąć relację
function confirm_remove_partner($user_id, $token)
{
    $invite = verify_remove_partner_token($user_id, $token);
    if (is_wp_error($invite)) {
        return $invite;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    // Wyczyść relację i tokeny
    $updated = $wpdb->update(
        $table,
        [
            'invited_email' => '',
            'invited_user_id' => null,
            'status' => 'rejected', // lub inny status "brak relacji"
            'remove_token' => '',
            'remove_token_expires' => null,
            'updated_at' => current_time('mysql'),
        ],
        ['id' => $invite->id],
        ['%s', '%s', '%s', '%s', '%s', '%s'],
        ['%d']
    );

    if ($updated === false) {
        return new WP_Error('db_error', 'Błąd podczas usuwania relacji.');
    }

    return true;
}


// ---- CHANGE USER ROLE ----
function custom_update_custom_user_role()
{
    check_ajax_referer('profile_update_custom_user_role', 'security');

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Nie jesteś zalogowany.');
    }

    $new_role = sanitize_text_field($_POST['user_role'] ?? '');

    if (!in_array($new_role, ['pan_mlody', 'panna_mloda'])) {
        wp_send_json_error('Nieprawidłowa rola.');
    }

    update_user_meta($user_id, 'user_role', $new_role);

    wp_send_json_success(['message' => 'Rola została zaktualizowana.']);
}
