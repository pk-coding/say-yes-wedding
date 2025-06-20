<?php

// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'panel_enqueue_assets');
add_action('wp_footer', 'print_panel_ajax_obj_script', 5);

add_action('wp_ajax_get_user_result', 'handle_get_user_result');
add_action('wp_ajax_get_partner_result', 'handle_get_partner_result');

add_action('wp_ajax_user_delete_result', 'user_delete_result');


// ---- LOADING ASSETS ----
function panel_enqueue_assets()
{
    if (!is_page_template('page-panel.php')) return;

    $uri = get_stylesheet_directory_uri();
    $path = get_stylesheet_directory();

    wp_enqueue_style(
        'panel-style',
        "$uri/css/panel.css",
        [],
        filemtime("$path/css/panel.css")
    );

    wp_enqueue_script(
        'panel-script',
        "$uri/js/panel.js",
        [],
        filemtime("$path/js/panel.js"),
        true
    );

    wp_enqueue_script(
        'jspdf',
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
        array(),
        '2.5.1',
        true
    );
}

// print_panel_ajax_obj_script INSTEAD OF LOAD: wp_localize_script() in panel_enqueue_assets --> regarding CSP POLICY

function print_panel_ajax_obj_script()
{
?>
    <script <?php echo csp_nonce_attr(); ?>>
        window.panel_ajax_obj = window.panel_ajax_obj || {
            ajax_url: "<?php echo admin_url('admin-ajax.php'); ?>",
            nonce: "<?php echo wp_create_nonce('panel_nonce'); ?>",
            user_results_nonce: "<?php echo wp_create_nonce('user_results_nonce'); ?>",
            partner_results_nonce: "<?php echo wp_create_nonce('partner_results_nonce'); ?>",
            user_delete_result_nonce: "<?php echo wp_create_nonce('user_delete_result_nonce'); ?>",
        };
    </script>
<?php
}


// ---- DISPLAY USER CALCULATOR RESULTS IN page-panel.php FILE ----
function render_user_calculator_results($user_id)
{
    global $wpdb;

    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}calculator_results WHERE user_id = %d", $user_id)
    );

    if (!empty($results)) {
        foreach ($results as $row) {
            echo '<div class="result-item" data-id="' . esc_attr($row->id) . '">
                <span class="result-text">' . esc_html($row->result) . '</span>
                <button class="delete-btn">Usuń</button>
                <button class="generate-pdf-btn">Pobierz PDF z tym wynikiem</button>
            </div>';
        }
    } else {
        echo '<div>Brak zapisanych wyliczeń.</div>';
    }
}


// ---- DISPLAY PARTNER CALCULATOR RESULTS IN page-panel.php FILE ----
function get_partner_results($current_user_id)
{
    global $wpdb;

    // Znajdujemy partnera przez wp_user_invites (szukamy zaakceptowanego zaproszenia gdzie current_user jest zaproszonym)
    $partner_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT sender_user_id FROM {$wpdb->prefix}user_invites 
             WHERE invited_user_id = %d AND status = 'accepted' LIMIT 1",
            $current_user_id
        )
    );

    if (!$partner_id) {
        // Jeśli nie znaleźliśmy partnera, spróbujmy odwrotnie (gdzie current_user jest nadawcą i zaproszenie zaakceptowane)
        $partner_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT invited_user_id FROM {$wpdb->prefix}user_invites 
                 WHERE sender_user_id = %d AND status = 'accepted' LIMIT 1",
                $current_user_id
            )
        );
    }

    if ($partner_id) {
        $partner_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}calculator_results WHERE user_id = %d",
                $partner_id
            )
        );

        if (!empty($partner_results)) {
            foreach ($partner_results as $row) {
                echo '<div class="result-item" data-id="' . esc_attr($row->id) . '">
                <span class="result-text">' . esc_html($row->result) . '</span>
                <button class="generate-partner-pdf-btn">Pobierz PDF z tym wynikiem</button>
            </div>';
            }
        } else {
            echo '<p>Partner nie ma zapisanych wyliczeń.</p>';
        }
    } else {
        echo '<p>Nie znaleziono partnera.</p>';
    }
}


// ---- AJAX HANDELR GET USER RESULTS FOR CREATE PDF FILE ----
function handle_get_user_result()
{
    // Sprawdzamy nonce
    if (!check_ajax_referer('user_results_nonce', 'security', false)) {
        wp_send_json_error('Niepoprawny token bezpieczeństwa.', 403);
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Nie jesteś zalogowany.', 403);
    }

    // Pobieramy ID wyniku z żądania
    $result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;
    if (!$result_id) {
        wp_send_json_error('Brak ID wyniku.', 400);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'calculator_results';

    // Pobieramy wynik tylko jeśli należy do aktualnego użytkownika
    $result = $wpdb->get_row(
        $wpdb->prepare("SELECT result FROM $table_name WHERE id = %d AND user_id = %d", $result_id, $user_id),
        ARRAY_A
    );

    if (!$result) {
        wp_send_json_error('Nie znaleziono wyniku.', 404);
    }

    wp_send_json_success($result);
}

// ---- AJAX HANDELR GET PARTNER RESULTS FOR CREATE PDF FILE ----
function handle_get_partner_result()
{
    if (!check_ajax_referer('partner_results_nonce', 'security', false)) {
        wp_send_json_error('Niepoprawny token bezpieczeństwa.', 403);
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Nie jesteś zalogowany.', 403);
    }

    $partner_id = get_partner_id_for_user($user_id);
    if (!$partner_id) {
        wp_send_json_error('Nie znaleziono partnera.', 404);
    }

    $result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;
    if (!$result_id) {
        wp_send_json_error('Brak ID wyniku.', 400);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'calculator_results';

    $result = $wpdb->get_row(
        $wpdb->prepare("SELECT result FROM $table_name WHERE id = %d AND user_id = %d", $result_id, $partner_id),
        ARRAY_A
    );

    if (!$result) {
        wp_send_json_error('Nie znaleziono wyniku partnera.', 404);
    }

    wp_send_json_success($result);
}


// ----
function get_partner_id_for_user($user_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    // Szukamy relacji, gdzie użytkownik był zapraszającym lub zaproszonym i zaakceptowano zaproszenie
    $invite = $wpdb->get_row(
        $wpdb->prepare(
            "
            SELECT sender_user_id, invited_user_id
            FROM $table
            WHERE status = 'accepted' AND (sender_user_id = %d OR invited_user_id = %d)
            ",
            $user_id,
            $user_id
        ),
        ARRAY_A
    );

    if (!$invite) return null;

    // Zwracamy drugą stronę relacji
    if ($invite['sender_user_id'] == $user_id) {
        return (int)$invite['invited_user_id'];
    } else {
        return (int)$invite['sender_user_id'];
    }
}


// ---- FUNCTION DISPLAYS PARTNER INFO ----
function get_user_partner_info($user_id = null)
{
    if (!$user_id) {
        $user = wp_get_current_user();
        $user_id = $user->ID;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'user_invites';

    $invite = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $table
        WHERE (sender_user_id = %d OR invited_user_id = %d)
        AND status = 'accepted'
        LIMIT 1
    ", $user_id, $user_id));

    if (!$invite) {
        return 'Brak powiązanego partnera.';
    }

    $partner_id = ($invite->sender_user_id == $user_id) ? $invite->invited_user_id : $invite->sender_user_id;

    if (!$partner_id) {
        return 'Brak danych partnera.';
    }

    $partner = get_userdata($partner_id);
    if (!$partner) {
        return 'Nie znaleziono danych partnera.';
    }

    return esc_html($partner->display_name) . ' (' . esc_html($partner->user_email) . ')';
}


// ---- FUNCTION DISPLAYS PARTNER ROLE ----
function get_user_role_label($user_id)
{
    $role = get_user_meta($user_id, 'user_role', true);

    if (!$role) {
        return 'Nie przypisano';
    }

    switch ($role) {
        case 'panna_mloda':
            return 'Panna Młoda';
        case 'pan_mlody':
            return 'Pan Młody';
        default:
            return ucwords(str_replace('_', ' ', $role));
    }
}


// ---- DELETE USER CALCULATOR RESULT ----
function user_delete_result()
{
    check_ajax_referer('user_delete_result_nonce', 'security');

    if (!isset($_POST['result_id'])) {
        wp_send_json_error('Brak ID wyniku.');
    }

    $result_id = intval($_POST['result_id']);
    $user_id = get_current_user_id();

    global $wpdb;
    $table = $wpdb->prefix . 'calculator_results';

    // Sprawdzenie, czy wynik należy do użytkownika
    $owner = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $table WHERE id = %d", $result_id));
    if ($owner != $user_id) {
        wp_send_json_error('Nie masz uprawnień do usunięcia tego wyniku.');
    }

    $deleted = $wpdb->delete($table, ['id' => $result_id]);

    if ($deleted) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Nie udało się usunąć wyniku.');
    }
}
