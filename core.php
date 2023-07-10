<?php

function reguly_dostepnosci_payment_gateways( $available_gateways ) {
    if ( !is_admin() ) {
        $chosen_shipping_rates = ( isset( WC()->session ) ) ? WC()->session->get( 'chosen_shipping_methods' ) : array();
        $reguly = get_results();

        if ( ! is_admin() && WC()->session ) {
            foreach ( $reguly as $regula_checkout ) {
                $delivery = $regula_checkout['metoda_dostawy'];
                $payment = $regula_checkout['metoda_platnosci'];

                $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
                
                $chosen_shipping = $chosen_methods[0];
                    if ( isset( $available_gateways[$payment] ) && 0 === strpos( $chosen_shipping, $delivery ) ) {
                          unset( $available_gateways[$payment] );
                     }
   }
        }
    }

    return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'reguly_dostepnosci_payment_gateways' );