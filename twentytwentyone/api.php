<?php

// LINK DO URUCHOMIENIA KODU:
// http://localhost/say-yes/wp-content/themes/twentytwentyone/api.php


require_once('../../../wp-config.php');


$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


if ($db->connect_errno) {
    echo 'Failed to connect to MySQL: ' . $db->connect_error;
    exit();
}


$wp_calculator_results = $db->query('
    CREATE TABLE IF NOT EXISTS `wp_calculator_results` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` BIGINT(20) UNSIGNED NOT NULL,
        `result` TEXT NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `wp_users`(`ID`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
');


$wp_user_invites = $db->query('
CREATE TABLE wp_user_invites (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sender_user_id` BIGINT(20) UNSIGNED NOT NULL,
    `invited_email` VARCHAR(255) NOT NULL,
    `invited_user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `token` VARCHAR(64) NOT NULL,
    `status` ENUM("pending", "accepted", "rejected", "expired") NOT NULL DEFAULT "pending",
    `remove_token` VARCHAR(64) DEFAULT NULL,
    `remove_token_expires` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at` DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY token (token),
    KEY sender_user_id (sender_user_id),
    KEY invited_user_id (invited_user_id),
    KEY invited_email (invited_email),
    KEY remove_token (remove_token)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
');

if ($wp_calculator_results && $wp_user_invites) {
    echo 'Tabele zostały utworzone poprawnie.';
} else {
    echo 'Błąd przy tworzeniu tabel: ' . $db->error;
}
