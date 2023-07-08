<?php
/*
Plugin Name: Reguły dostępności metod płatności dla metod dostaw
Description: Wtyczka ta pozwala na tworzenie za pomocą w miarę przyjaznego interfejsu warunków, jaka metoda płatności ma zostać ukryta dla wskazanej metody dostawy.
Version: 1.0.0
Author: Jakub Chrobot
*/

// Sprawdzanie aktywności wtyczki WooCommerce
function sprawdz_aktywnosc_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'reguly_dostepnosci_woocomerce_brak');
        return;
    }
}
add_action('admin_init', 'sprawdz_aktywnosc_woocommerce');

// Komunikat o braku aktywnej wtyczki WooCommerce
function reguly_dostepnosci_woocomerce_brak() {
    echo '<div class="error"><p>Wtyczka Moja Wtyczka wymaga aktywnej wtyczki WooCommerce.</p></div>';
}

// Sprawdzanie wersji WooCommerce i WordPress
function sprawdz_wersje() {
    $wymagana_wersja_woocomerce = '5.0.0';
    $wymagana_wersja_wordpress = '5.0';

    if (!class_exists('WooCommerce') || !function_exists('get_plugins')) {
        return;
    }

    $woocomerce_wersja = get_option('woocommerce_version');
    $wordpress_wersja = get_bloginfo('version');

    if (version_compare($woocomerce_wersja, $wymagana_wersja_woocomerce, '<')) {
        add_action('admin_notices', 'reguly_dostepnosci_woocomerce_wersja');
    }

    if (version_compare($wordpress_wersja, $wymagana_wersja_wordpress, '<')) {
        add_action('admin_notices', 'reguly_dostepnosci_wordpress_wersja');
    }
}
add_action('admin_init', 'sprawdz_wersje');

// Komunikat o wymaganej wersji WooCommerce
function reguly_dostepnosci_woocomerce_wersja() {
    echo '<div class="error"><p>Wtyczka Moja Wtyczka wymaga wersji WooCommerce 5.0.0 lub nowszej.</p></div>';
}

// Komunikat o wymaganej wersji WordPress
function reguly_dostepnosci_wordpress_wersja() {
    echo '<div class="error"><p>Wtyczka Moja Wtyczka wymaga wersji WordPress 5.0 lub nowszej.</p></div>';
}

function reguly_scripts() {
    if (is_admin()) {
        wp_enqueue_script('delete-regula', plugin_dir_url(__FILE__) . 'panel-admin/js/delete-regula.js', array('jquery'), '1.0', true);
        wp_enqueue_script('delete-regula', 'panel-admin/js/delete-regula.js', array('jquery'), '1.0', true);

        wp_localize_script('delete-regula', 'customPluginData', array(
            'nonce' => wp_create_nonce('usun_wiersz_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'reguly_scripts');

// Tworzenie tabeli w bazie danych podczas instalacji wtyczki
function tworz_tabele_wtyczki() {
    global $wpdb;
    $tabela = $wpdb->prefix . 'reguly_dostepnosci_platnosci_dla_dostaw';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $tabela (
        id INT AUTO_INCREMENT PRIMARY KEY,
        metoda_platnosci VARCHAR(255) NOT NULL,
        metoda_dostawy VARCHAR(255) NOT NULL,
        metoda_dostawy_id VARCHAR(255) NOT NULL,
        ukryj TINYINT(1) DEFAULT 0
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'tworz_tabele_wtyczki');

include_once plugin_dir_path(__FILE__) . 'db.php';
include_once plugin_dir_path(__FILE__) . 'panel-admin/admin.php';
include_once plugin_dir_path(__FILE__) . 'core.php';