<?php

// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'inviteconfirm_enqueue_assets');
add_action('wp_ajax_invite_confirm_action', 'handle_invite_confirm_action');
add_action('wp_ajax_nopriv_invite_confirm_action', 'handle_invite_confirm_action');


// ---- ENQUEUE ASSETS ----
function inviteconfirm_enqueue_assets()
{
    if (!is_page_template('page-inviteconfirm.php')) return;

    $uri = get_stylesheet_directory_uri();
    $path = get_stylesheet_directory();

    wp_enqueue_style(
        'inviteconfirm-style',
        "$uri/css/inviteconfirm.css",
        [],
        filemtime("$path/css/inviteconfirm.css")
    );

    wp_enqueue_script(
        'inviteconfirm-script',
        "$uri/js/inviteconfirm.js",
        [],
        filemtime("$path/js/inviteconfirm.js"),
        true
    );

    wp_localize_script('inviteconfirm-script', 'inviteconfirm_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'partner_inviteconfirm_nonce' => wp_create_nonce('partner_inviteconfirm_nonce'),
    ]);
}


// ---- HANDLE INVITE CONFIRM ACTION ----
function handle_invite_confirm_action()
{
    check_ajax_referer('partner_inviteconfirm_nonce', 'nonce');

    $token = get_invite_token();
    $action = get_invite_action_type();

    $invite = fetch_invite_by_token($token);
    validate_invite_status($invite);
    $user = wp_get_current_user();
    validate_user_can_confirm_invite($invite, $user);

    process_invite_action($invite, $action, $user);

    wp_send_json_success(get_invite_action_success_message($action));
}


// ---- HELPERS ----

function get_invite_token()
{
    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
    if (!$token) {
        wp_send_json_error('Brak tokena zaproszenia.');
    }
    return $token;
}

function get_invite_action_type()
{
    $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
    if (!in_array($action, ['accept', 'reject'], true)) {
        wp_send_json_error('Nieprawidłowa akcja.');
    }
    return $action;
}

function fetch_invite_by_token($token)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    $invite = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE token = %s", $token));

    if (!$invite) {
        wp_send_json_error('Nie znaleziono zaproszenia.');
    }

    return $invite;
}

function validate_invite_status($invite)
{
    $now = current_time('mysql');

    if ($invite->expires_at < $now) {
        if ($invite->status === 'pending') {
            mark_invite_as_expired($invite->id, $now);
        }
        wp_send_json_error('Link wygasł.');
    }

    if ($invite->status !== 'pending') {
        wp_send_json_error('To zaproszenie zostało już przetworzone.');
    }
}

function mark_invite_as_expired($invite_id, $now)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    $wpdb->update(
        $table,
        ['status' => 'expired', 'updated_at' => $now],
        ['id' => $invite_id],
        ['%s', '%s'],
        ['%d']
    );
}

function validate_user_can_confirm_invite($invite, $user)
{
    $current_user_id = get_current_user_id();

    if (!$current_user_id) {
        wp_send_json_error('Nie jesteś zalogowany. Zaloguj się lub, jeśli nie masz konta, zarejestruj i zaloguj, a następnie odświeź tę stronę jeszcze raz.');
    }

    // Jeśli user_id przypisany — sprawdzamy zgodność
    if ($invite->invited_user_id) {
        if ($current_user_id != $invite->invited_user_id) {
            wp_send_json_error('To zaproszenie nie jest przypisane do Twojego konta.');
        }
    } else {
        // Jeśli nie było przypisanego user_id — sprawdzamy email
        if (strtolower($user->user_email) !== strtolower($invite->invited_email)) {
            wp_send_json_error('Twój adres e-mail nie pasuje do zaproszenia.');
        }
    }
}

function process_invite_action($invite, $action, $user)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';
    $now = current_time('mysql');

    if ($action === 'accept') {
        $wpdb->update(
            $table,
            [
                'status' => 'accepted',
                'invited_user_id' => $user->ID,
                'updated_at' => $now
            ],
            ['id' => $invite->id],
            ['%s', '%d', '%s'],
            ['%d']
        );
    } elseif ($action === 'reject') {
        $wpdb->update(
            $table,
            [
                'status' => 'rejected',
                'updated_at' => $now
            ],
            ['id' => $invite->id],
            ['%s', '%s'],
            ['%d']
        );
    }
}

function get_invite_action_success_message($action)
{
    return $action === 'accept'
        ? 'Zaproszenie zostało zaakceptowane.'
        : 'Zaproszenie zostało odrzucone.';
}
