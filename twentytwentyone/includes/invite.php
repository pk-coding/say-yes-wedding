<?php

// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'invite_enqueue_assets');
add_action('wp_ajax_send_invite', 'handle_send_invite');

// ---- ENQUEUE SCRIPTS ----
function invite_enqueue_assets()
{
    if (!is_page_template('page-invite.php')) return;

    $uri = get_stylesheet_directory_uri();
    $path = get_stylesheet_directory();

    wp_enqueue_style(
        'invite-style',
        "$uri/css/invite.css",
        [],
        filemtime("$path/css/invite.css")
    );

    wp_enqueue_script(
        'invite-script',
        "$uri/js/invite.js",
        [],
        filemtime("$path/js/invite.js"),
        true
    );

    wp_localize_script('invite-script', 'invite_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'partner_invite_nonce' => wp_create_nonce('partner_invite_nonce'),
        'invite_nonce' => wp_create_nonce('invite_nonce'),
    ]);
}

// ---- AJAX HANDLER ----
function handle_send_invite()
{
    check_ajax_referer('partner_invite_nonce', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) {
        return wp_send_json_error('Użytkownik niezalogowany');
    }

    $email = sanitize_email($_POST['email'] ?? '');
    if (!is_valid_email($email)) {
        return wp_send_json_error('Nieprawidłowy email');
    }

    if (is_already_paired($user_id)) {
        return wp_send_json_error('Masz już powiązanego partnera.');
    }

    if (has_pending_invite($user_id, $email)) {
        return wp_send_json_error('Zaproszenie jest już w trakcie oczekiwania.');
    }

    $invited_user_id = get_existing_user_id_if_not_paired($email);
    if ($invited_user_id === false) {
        return wp_send_json_error('Ten użytkownik ma już powiązanego partnera.');
    }
    if (is_user_busy_with_pending_invite($invited_user_id)) {
        return wp_send_json_error('Ten użytkownik ma już oczekujące zaproszenie.');
    }

    if (is_user_busy_with_pending_invite($user_id)) {
        return wp_send_json_error('Nie możesz wysłać nowego zaproszenia, dopóki Twoje poprzednie zaproszenie nie zostanie rozpatrzone.');
    }

    $token = generate_token();
    $expires_at = calculate_expiration();

    if (!insert_invite_record($user_id, $email, $invited_user_id, $token, $expires_at)) {
        return wp_send_json_error('Błąd zapisu zaproszenia.');
    }

    send_invite_email($email, $token);

    wp_send_json_success('Zaproszenie zostało wysłane.');
}


// ---- VALIDATE EMAIL ----
function is_valid_email($email, $current_user_id = null)
{
    $errors = [];

    $email = trim($email);

    if (empty($email)) {
        $errors[] = 'Adres e-mail jest wymagany.';
        return $errors; // nie ma sensu dalej sprawdzać
    }

    if (strlen($email) < 6 || strlen($email) > 254) {
        $errors[] = 'Adres e-mail musi mieć od 6 do 254 znaków.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Podaj poprawny adres e-mail.';
    }

    $existing_user_id = email_exists($email);
    if ($existing_user_id && (int)$existing_user_id !== (int)$current_user_id) {
        $errors[] = 'Ten adres e-mail jest już używany.';
    }

    return empty($errors) ? true : $errors;
}


// ---- HELPERS ----
function is_already_paired($user_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';
    return (bool) $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table 
        WHERE (sender_user_id = %d OR invited_user_id = %d) 
        AND status = 'accepted'
    ", $user_id, $user_id));
}


// ----
function has_pending_invite($user_id, $email)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';
    return (bool) $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table 
        WHERE (sender_user_id = %d OR invited_email = %s) 
        AND status = 'pending'
        AND expires_at > NOW()
    ", $user_id, $email));
}


// ----
function get_existing_user_id_if_not_paired($email)
{
    $user = get_user_by('email', $email);
    if (!$user) return null;

    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    $paired = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table 
        WHERE (sender_user_id = %d OR invited_user_id = %d) 
        AND status = 'accepted'
    ", $user->ID, $user->ID));

    return $paired ? false : $user->ID;
}

// ----
function is_user_busy_with_pending_invite($user_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    return (bool) $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table
        WHERE (sender_user_id = %d OR invited_user_id = %d)
          AND status = 'pending'
          AND expires_at > NOW()
    ", $user_id, $user_id));
}


// ----
function generate_token()
{
    return wp_generate_password(48, false, false);
}


// ----
function calculate_expiration()
{
    return date('Y-m-d H:i:s', strtotime('+3 days'));
}


// ----
function insert_invite_record($sender_id, $email, $invited_id, $token, $expires_at)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    return $wpdb->insert(
        $table,
        [
            'sender_user_id' => $sender_id,
            'invited_email' => $email,
            'invited_user_id' => $invited_id,
            'token' => $token,
            'status' => 'pending',
            'expires_at' => $expires_at,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ],
        ['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s']
    );
}


// ----
function send_invite_email($email, $token)
{
    $link = add_query_arg('token', $token, home_url('/aktywacjazaproszenia'));

    $subject = 'Zaproszenie do partnerstwa';
    $message = "Otrzymałeś zaproszenie do utworzenia pary na stronie.\n\nKliknij link, aby zaakceptować lub odrzucić zaproszenie:\n$link\n\nLink jest aktywny przez 3 dni.";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    wp_mail($email, $subject, $message, $headers);
}
