<?php

// ---- ACTIONS ----
add_action('wp_enqueue_scripts', 'panel_enqueue_assets');
add_action('wp_footer', 'print_panel_ajax_obj_script', 5);

add_action('wp_ajax_get_user_result', 'handle_get_user_result');
add_action('wp_ajax_get_partner_result', 'handle_get_partner_result');

add_action('wp_ajax_get_result_details', 'get_result_details');

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
        'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js',
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
            $result_data = maybe_unserialize($row->result);
            if (!is_array($result_data)) {
                $result_data = [];
            }

            $guestCountText = isset($result_data['guestCount']) ? ' - Gości: ' . (int)$result_data['guestCount'] : '';
            $label = 'Wynik z ' . date('d.m.Y H:i:s', strtotime($row->created_at));
            if (isset($result_data['totalBudget'])) {
                $label = 'Budżet: ' . number_format($result_data['totalBudget'], 0, ',', ' ') . ' zł' . $guestCountText . ' (' . date('d.m.Y H:i:s', strtotime($row->created_at)) . ')';
            }

            echo '<div class="result-item" data-id="' . esc_attr($row->id) . '">
            <span class="result-label">' . esc_html($label) . '</span>
            <button class="show-details-btn">Szczegóły</button>
            <button class="delete-btn">Usuń</button>
            </div>';
        }
    } else {
        echo '<div>Brak zapisanych wyliczeń.</div>';
    }
}


// ---- Get details result
function get_result_details()
{
    global $wpdb;
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        wp_send_json_error('Brak ID');
    }

    $current_user_id = get_current_user_id();

    // Pobierz podstawowy wynik
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT user_id, result FROM {$wpdb->prefix}calculator_results WHERE id = %d",
        $id
    ));

    if (!$result) {
        wp_send_json_error('Wynik nie istnieje');
    }

    $isOwner = ($result->user_id == $current_user_id);

    // Sprawdź partnera
    $partner_id = $wpdb->get_var($wpdb->prepare(
        "SELECT sender_user_id FROM {$wpdb->prefix}user_invites 
         WHERE invited_user_id = %d AND status = 'accepted'",
        $current_user_id
    ));

    if (!$partner_id) {
        $partner_id = $wpdb->get_var($wpdb->prepare(
            "SELECT invited_user_id FROM {$wpdb->prefix}user_invites 
             WHERE sender_user_id = %d AND status = 'accepted'",
            $current_user_id
        ));
    }

    if (!$isOwner && $partner_id != $result->user_id) {
        wp_send_json_error('Brak dostępu do wyniku');
    }

    // Rozpakuj podstawowy wynik (deserialize)
    $result_data = maybe_unserialize($result->result);

    // Pobierz pomysły powiązane z tym wynikiem
    $ideas = $wpdb->get_results($wpdb->prepare(
        "SELECT idea_name, idea_price FROM {$wpdb->prefix}calculator_ideas WHERE result_id = %d",
        $id
    ), ARRAY_A);

    // Dodaj pomysły do danych wyników (pod kluczem 'pomysly')
    $result_data['pomysly'] = $ideas ?: [];

    // Zwróć całość
    wp_send_json_success($result_data);
}


// ---- DISPLAY PARTNER CALCULATOR RESULTS IN page-panel.php FILE ----
function get_partner_results($current_user_id)
{
    global $wpdb;

    // Znajdujemy partnera przez wp_user_invites
    $partner_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT sender_user_id FROM {$wpdb->prefix}user_invites 
             WHERE invited_user_id = %d AND status = 'accepted' LIMIT 1",
            $current_user_id
        )
    );

    if (!$partner_id) {
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
                $result_data = maybe_unserialize($row->result);
                if (!is_array($result_data)) {
                    $result_data = [];
                }

                $guestCountText = isset($result_data['guestCount']) ? ' - Gości: ' . (int)$result_data['guestCount'] : '';
                $label = 'Wynik z ' . date('d.m.Y H:i:s', strtotime($row->created_at));
                if (isset($result_data['totalBudget'])) {
                    $label = 'Budżet: ' . number_format($result_data['totalBudget'], 0, ',', ' ') . ' zł' . $guestCountText . ' (' . date('d.m.Y H:i:s', strtotime($row->created_at)) . ')';
                }
                echo '<div class="partner-result-item" data-id="' . esc_attr($row->id) . '">
                <span class="result-label">' . esc_html($label) . '</span>
                <button class="show-partner-details-btn">Szczegóły</button>
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
    // Weryfikacja nonce
    if (!check_ajax_referer('user_results_nonce', 'security', false)) {
        wp_send_json_error('Niepoprawny token bezpieczeństwa.', 403);
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Nie jesteś zalogowany.', 403);
    }

    // Walidacja ID wyniku
    $result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;
    if (!$result_id) {
        wp_send_json_error('Brak ID wyniku.', 400);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'calculator_results';

    // Pobieranie rekordu użytkownika z tabeli (result + created_at)
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT result, created_at FROM $table_name WHERE id = %d AND user_id = %d",
            $result_id,
            $user_id
        ),
        ARRAY_A
    );

    if (!$row) {
        wp_send_json_error('Nie znaleziono wyniku.', 404);
    }

    // Deserializacja danych wyniku
    $parsed_result = maybe_unserialize($row['result']);
    if (!is_array($parsed_result)) {
        wp_send_json_error('Nieprawidłowy format danych.', 400);
    }

    // Zwracamy dane jako JSON
    wp_send_json_success([
        'detail' => $parsed_result,
        'created_at' => $row['created_at'],
    ]);
};



function handle_get_partner_result()
{
    // Weryfikacja nonce
    if (!check_ajax_referer('partner_results_nonce', 'security', false)) {
        wp_send_json_error('Niepoprawny token bezpieczeństwa.', 403);
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error('Nie jesteś zalogowany.', 403);
    }

    // Pobranie ID partnera
    $partner_id = get_partner_id_for_user($user_id);
    if (!$partner_id) {
        wp_send_json_error('Nie znaleziono partnera.', 404);
    }

    // Walidacja ID wyniku
    $result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;
    if (!$result_id) {
        wp_send_json_error('Brak ID wyniku.', 400);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'calculator_results';

    // Pobranie danych wyniku partnera
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT result, created_at FROM $table_name WHERE id = %d AND user_id = %d",
            $result_id,
            $partner_id
        ),
        ARRAY_A
    );

    if (!$row) {
        wp_send_json_error('Nie znaleziono wyniku partnera.', 404);
    }

    // Deserializacja danych
    $parsed_result = maybe_unserialize($row['result']);
    if (!is_array($parsed_result)) {
        wp_send_json_error('Nieprawidłowy format danych.', 400);
    }

    // Zwrócenie danych jako JSON
    wp_send_json_success([
        'detail' => $parsed_result,
        'created_at' => $row['created_at'],
    ]);
};


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
// function user_delete_result()
// {
//     check_ajax_referer('user_delete_result_nonce', 'security');

//     if (!isset($_POST['result_id'])) {
//         wp_send_json_error('Brak ID wyniku.');
//     }

//     $result_id = intval($_POST['result_id']);
//     $user_id = get_current_user_id();

//     global $wpdb;
//     $table = $wpdb->prefix . 'calculator_results';

//     // Sprawdzenie, czy wynik należy do użytkownika
//     $owner = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $table WHERE id = %d", $result_id));
//     if ($owner != $user_id) {
//         wp_send_json_error('Nie masz uprawnień do usunięcia tego wyniku.');
//     }

//     $deleted = $wpdb->delete($table, ['id' => $result_id]);

//     if ($deleted) {
//         wp_send_json_success();
//     } else {
//         wp_send_json_error('Nie udało się usunąć wyniku.');
//     }
// }
function user_delete_result()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('Musisz być zalogowany.');
    }

    check_ajax_referer('user_delete_result_nonce', 'security');

    if (!isset($_POST['result_id'])) {
        wp_send_json_error('Brak ID wyniku.');
    }

    $result_id = intval($_POST['result_id']);
    $user_id = get_current_user_id();

    global $wpdb;
    $table = $wpdb->prefix . 'calculator_results';

    $owner = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $table WHERE id = %d", $result_id));

    if (intval($owner) !== intval($user_id)) {
        wp_send_json_error('Nie masz uprawnień do usunięcia tego wyniku.');
        return;
    }

    $deleted = $wpdb->delete($table, ['id' => $result_id]);

    if ($deleted !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Nie udało się usunąć wyniku.');
    }
}


// ----
function get_partner_id($current_user_id)
{
    global $wpdb;

    // Szukamy partnera w tabeli user_invites (status = accepted)
    $partner_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT sender_user_id FROM {$wpdb->prefix}user_invites 
             WHERE invited_user_id = %d AND status = 'accepted' LIMIT 1",
            $current_user_id
        )
    );

    if (!$partner_id) {
        $partner_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT invited_user_id FROM {$wpdb->prefix}user_invites 
                 WHERE sender_user_id = %d AND status = 'accepted' LIMIT 1",
                $current_user_id
            )
        );
    }

    return $partner_id ?: 0;
}
