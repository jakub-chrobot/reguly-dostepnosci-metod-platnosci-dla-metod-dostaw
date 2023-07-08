<?php

function get_results() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reguly_dostepnosci_platnosci_dla_dostaw'; 

    $query = $wpdb->prepare("SELECT * FROM $table_name");
    $results = $wpdb->get_results($query);

    $reguly = array();
    if ($results) {
        foreach ($results as $row) {
            // Przetwarzanie wyników
            $id = absint($row->id);
            $metoda_platnosci = sanitize_text_field($row->metoda_platnosci);
            $metoda_dostawy = sanitize_text_field($row->metoda_dostawy);
            $ukryj = $row->ukryj ? 1 : 0;

            // Twój kod dla przetwarzania wyników
            $regula = array(
                'id' => $id,
                'metoda_platnosci' => $metoda_platnosci,
                'metoda_dostawy' => $metoda_dostawy,
                'ukryj' => $ukryj,
            );
            $reguly[] = $regula;
        }
    }

    return $reguly;
}