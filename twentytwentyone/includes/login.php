<?php

// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'login_enqueue_assets');
add_action('wp_footer', 'print_login_ajax_obj_script', 5);

add_action('wp_ajax_nopriv_custom_user_login', 'custom_user_login');
add_action('wp_ajax_custom_user_login', 'custom_user_login');

add_action('wp_ajax_nopriv_custom_forgot_password', 'custom_forgot_password');
add_action('wp_ajax_custom_forgot_password', 'custom_forgot_password');


// ---- LOADING ASSETS ----
function login_enqueue_assets()
{
    if (!is_page_template('page-login.php')) return;

    wp_enqueue_style(
        'login-style',
        get_stylesheet_directory_uri() . '/css/login.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/login.css')
    );

    wp_enqueue_script(
        'login-script',
        get_stylesheet_directory_uri() . '/js/login.js',
        [],
        filemtime(get_stylesheet_directory() . '/js/login.js'),
        true
    );
}


// print_login_ajax_obj_script INSTEAD OF LOAD: wp_localize_script() in login_enqueue_assets --> regarding CSP POLICY
function print_login_ajax_obj_script()
{
?>
    <script <?php echo csp_nonce_attr(); ?>>
        window.login_ajax_obj = window.login_ajax_obj || {
            ajax_url: "<?php echo admin_url('admin-ajax.php'); ?>",
            nonce: "<?php echo wp_create_nonce('login_nonce'); ?>"
        };
    </script>
<?php
}


// ---- HANDLING LOGIN FORM ----
function custom_user_login()
{
    check_ajax_referer('login_nonce', 'security');

    $username = sanitize_user($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = get_user_by('login', $username);

    if ($user) {
        $user_id = $user->ID;

        if (is_user_login_blocked($user_id)) {
            $remaining = get_user_login_block_remaining_seconds($user_id);
            wp_send_json_error("Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za " . ceil($remaining / 60) . " minut lub zresetuj hasło klikając poniżej w Zapomniałeś hasło?");
        }
    } else {
        $user_id = 0;
    }

    $error = validate_login_data($username, $password);
    if ($error) wp_send_json_error($error);

    $login_result = attempt_user_login($username, $password);

    if (is_wp_error($login_result)) {
        // Nieudane logowanie - inkrementacja licznika jeśli znamy user_id
        if ($user_id) {
            increment_user_login_attempts($user_id);

            if (get_user_login_attempts($user_id) >= 3) {
                block_user_login($user_id, 30 * 60);
                wp_send_json_error('Konto zablokowane z powodu zbyt wielu nieudanych prób logowania. Spróbuj ponownie za 30 minut lub zresetuj hasło klikając poniżej w Zapomniałeś hasło?');
            }
        }

        wp_send_json_error('Nieprawidłowe dane logowania.');
    }

    // Udane logowanie - resetowanie licznika i blokady
    if ($user_id) {
        reset_user_login_attempts($user_id);
        unblock_user_login($user_id);
    }

    if (is_custom_user_admin($login_result)) {
        wp_logout();
        wp_send_json_error('Nie masz dostępu jako administrator.');
    }

    log_in_user($login_result);

    wp_send_json_success('Zalogowano pomyślnie!');
}


// --- VALIDATE LOGIN FORM DATA ----
function validate_login_data($username, $password)
{
    if (empty($username) || empty($password)) {
        return 'Wszystkie pola są wymagane.';
    }
    return null;
}


// ---- ATTEMPT USER LOGIN ----
function attempt_user_login($username, $password)
{
    return wp_signon([
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => true
    ], is_ssl());
}


// ---- CHECK IF USER IS ADMIN HELPERS ----
function is_custom_user_admin($user)
{
    return in_array('administrator', (array) $user->roles, true);
}


// ---- LOG IN USER HELPERS ----
function log_in_user($user)
{
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
}


// ---- LOGIN ATTEMPTS & BLOCKING HELPERS ----

// --------

function get_user_login_attempts($user_id)
{
    return (int) get_user_meta($user_id, 'login_attempts_count', true);
}

// --------

function increment_user_login_attempts($user_id)
{
    $attempts = get_user_login_attempts($user_id);
    $attempts++;
    update_user_meta($user_id, 'login_attempts_count', $attempts);
}

// --------

function reset_user_login_attempts($user_id)
{
    delete_user_meta($user_id, 'login_attempts_count');
}

// --------

function block_user_login($user_id, $seconds)
{
    $blocked_until = time() + $seconds;
    update_user_meta($user_id, 'login_blocked_until', $blocked_until);
}

// --------

function unblock_user_login($user_id)
{
    delete_user_meta($user_id, 'login_blocked_until');
}

// --------

function is_user_login_blocked($user_id)
{
    $blocked_until = (int) get_user_meta($user_id, 'login_blocked_until', true);
    return ($blocked_until && time() < $blocked_until);
}

// --------

function get_user_login_block_remaining_seconds($user_id)
{
    $blocked_until = (int) get_user_meta($user_id, 'login_blocked_until', true);
    return max(0, $blocked_until - time());
}


// ---- HANDLING FORGOT PASSWORD FORM ----
function custom_forgot_password()
{
    check_ajax_referer('login_nonce', 'security');

    $username_or_email = sanitize_text_field($_POST['username_or_email'] ?? '');

    if (empty($username_or_email)) {
        wp_send_json_error('Podaj nazwę użytkownika lub email.');
    }

    $user = get_user_by_login_or_email($username_or_email);

    if (!$user) {
        wp_send_json_error('Nie znaleziono użytkownika.');
    }

    $reset_key = get_password_reset_key($user);
    if (is_wp_error($reset_key)) {
        wp_send_json_error('Nie udało się wygenerować linku resetującego.');
    }

    $reset_url = generate_reset_password_url($user, $reset_key);
    $mail_sent = send_reset_password_email($user, $reset_url);

    if (!$mail_sent) {
        wp_send_json_error('Nie udało się wysłać e-maila.');
    }

    wp_send_json_success('Wysłano link do resetowania hasła.');
}


// ---- GET USER BY LOGIN OR EMAIL HELPERS ----
function get_user_by_login_or_email($input)
{
    return is_email($input)
        ? get_user_by('email', $input)
        : get_user_by('login', $input);
}


// ---- GENERATE RESET PASSWORD URL HELPERS ----
function generate_reset_password_url($user, $key)
{
    return add_query_arg([
        'action' => 'rp',
        'key'    => $key,
        'login'  => rawurlencode($user->user_login)
    ], home_url('/resetowaniehasla'));
}


// ---- SEND RESET PASSWORD EMAIL HELPERS ----
function send_reset_password_email($user, $url)
{
    $subject = 'Resetowanie hasła - Say Yes';
    $message = "Cześć {$user->display_name},\n\nKliknij poniższy link, aby ustawić nowe hasło:\n\n{$url}\n\nJeśli nie prosiłeś o zmianę hasła, zignoruj tę wiadomość.\n\nZespół Say Yes";

    return wp_mail($user->user_email, $subject, $message);
}
