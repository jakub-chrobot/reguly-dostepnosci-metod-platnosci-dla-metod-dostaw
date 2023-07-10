<?php
function dodaj_zakladke_wtyczki()
{
    add_submenu_page('woocommerce', 'Reguły dostępności metod dostaw i płatności', 'Reguły dostępności metod dostaw i płatności', 'manage_options', 'reguly-dostepnosci-metod-dostaw-i-platnosci', 'renderuj_zakladke_wtyczki');
}
add_action('admin_menu', 'dodaj_zakladke_wtyczki');

function dodaj_opcje_konfiguracyjne()
{
    add_settings_section('reguly_dostepnosci_sekcja', '', '', 'reguly-dostepnosci-metod-dostaw-i-platnosci');

    add_settings_field('reguly_dostepnosci_reguly', '', 'renderuj_pola_regul', 'reguly-dostepnosci-metod-dostaw-i-platnosci', 'reguly_dostepnosci_sekcja');

    register_setting('reguly_dostepnosci_opcje', 'reguly_dostepnosci_reguly');
}
add_action('admin_init', 'dodaj_opcje_konfiguracyjne');

function get_enabled_payment_methods()
{
    $enabled_payment_methods = array();

    $payment_gateways = WC()->payment_gateways()
        ->get_available_payment_gateways();
    foreach ($payment_gateways as $gateway)
    {
        if ($gateway->is_available())
        {
            $enabled_payment_methods[] = array(
                'title' => $gateway->title,
                'id' => $gateway->id,
            );
        }
    }

    return $enabled_payment_methods;
}

function get_enabled_shipping_methods()
{
    $enabled_shipping_methods = array();

    // Sprawdzanie, czy wtyczka Flexible Shipping jest aktywna
    if (is_plugin_active('flexible-shipping/flexible-shipping.php'))
    {
        $flexible_methods = get_option('flexible_shipping_rates', array());

        foreach ($flexible_methods as $method)
        {
            $enabled_shipping_methods[] = array(
                'title' => $method['title'],
                'id' => $method['identifier'],
            );
        }
    }
    else
    {
        // Pobieranie domyślnych metod dostawy z WooCommerce
        $default_methods = WC()->shipping()
            ->get_shipping_methods();

        foreach ($default_methods as $method)
        {
            // Sprawdzanie, czy metoda jest włączona
            if ($method->is_enabled())
            {
                $enabled_shipping_methods[] = array(
                    'title' => $method->method_title,
                    'id' => $method->id,
                );
            }
        }
    }

    return $enabled_shipping_methods;
}

function renderuj_zakladke_wtyczki()
{
?>
    <div class="wrap">
        <h1>Moja Wtyczka</h1>
        <h2 class="nav-tab-wrapper">
            <a href="#ustawienia" class="nav-tab nav-tab-active">Ustawienia</a>
            <a href="#informacje" class="nav-tab">Informacje</a>
        </h2>

        <div id="ustawienia" class="reguly-dostepnosci-tab-content">
            <h3>Ustawienia Wtyczki</h3>
            <form method="post" action="options.php">
                <?php
    settings_fields('reguly_dostepnosci_opcje');
    do_settings_sections('reguly-dostepnosci-metod-dostaw-i-platnosci');
    submit_button();
?>
            </form>
        </div>

        <div id="informacje" class="reguly-dostepnosci-tab-content" style="display: none;">
            <h3>Informacje o Wtyczce</h3>
            <p>Tutaj możesz umieścić informacje o swojej wtyczce.</p>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('.nav-tab-wrapper .nav-tab').click(function() {
                var tabId = $(this).attr('href');
                $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.reguly-dostepnosci-tab-content').hide();
                $(tabId).show();
            });
        });
    </script>
    <?php
}

function find_payment_method_name($enabled_payment_methods, $payment_method_id)
{
    foreach ($enabled_payment_methods as $payment_method)
    {
        if ($payment_method['id'] === $payment_method_id)
        {
            return $payment_method['title'];
        }
    }

    return 'Nieznana metoda płatności';
}

function find_shipping_method_name($enabled_shipping_methods, $shipping_method_id)
{
    foreach ($enabled_shipping_methods as $shipping_method)
    {
        if ($shipping_method['id'] === $shipping_method_id)
        {
            return $shipping_method['title'];
        }
    }

    return 'Nieznana metoda dostawy';
}

function renderuj_pola_regul()
{
    $metody_platnosci = get_enabled_payment_methods();
    $metody_dostawy = get_enabled_shipping_methods();
    $reguly = get_results();

?>
    <?php if ($reguly)
    { ?> 
    <style> tr,th{
        text-align: center !important;
    }
    .form-table th{
        width: auto !important;
    } </style>
    <table class="form-table" border="1" style="text-align: center;">
    <tr style="text-align: center;">
        <th> # </th>
        <th> Metoda płatności </th>
        <th> Metoda dostawy</th>
        <th> Ukryta</th>
        <th> Usuń </th>
    </tr>
      <?php $i = 1;
        foreach ($reguly as $option_regula)
        {

            $enabled_methods = get_enabled_shipping_methods();
            $shipping_method_id = $option_regula['metoda_dostawy'];
            $shipping_method_name = find_shipping_method_name($enabled_methods, $shipping_method_id);

            $enabled_methods = get_enabled_payment_methods();
            $payment_method_id = $option_regula['metoda_platnosci'];
            $payment_method_name = find_payment_method_name($enabled_methods, $payment_method_id);

?>
            <tr style="text-align:center;">
            <td> <?php echo $i; ?> </td>
            <td><?php echo $payment_method_name;
            echo ' [ ';
            echo $option_regula['metoda_platnosci'];
            echo ' ] '; ?></td>
            <td><?php echo $shipping_method_name;
            echo ' [ ';
            echo $option_regula['metoda_dostawy'];
            echo ' ] '; ?></td>
            <td> Tak </td>
            <td> <button class="button button-secondary remove-regular usun-przycisk" data-wiersz-id="<?php echo $option_regula['id']; ?>"> Usuń </button> </td>
        </tr>
      <?php $i++;
        } ?>

    </table>
    <?php
    }
    else
    {
        echo 'Brak ustawionych reguł do wyświetlenia';
    } ?>

    <div class="komunikat-usuwania" style="display: none; margin: 2rem 0rem;"></div>

    <div style="margin:2rem 0rem;" >
    <?php if ($metody_platnosci || $metody_dostawy)
    { ?>
    <div style="margin:1rem 0rem;" class="info"> Wybierz metodę płatności a potem metodę dostawy z poniższej listy rozwijanej. Dla wybranej metody dostawy zostanie ukryta metoda płatności. </div> 
    <select class="metoda-platnosci" name="moja_wtyczka_metoda_platnosci[]">
    <option value="">Wybierz metodę płatności</option>
        <?php
        foreach ($metody_platnosci as $platnosc)
        {
            $selected = ($platnosc === $metoda_platnosci) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($platnosc['id']) . '" ' . $selected . '>' . esc_html($platnosc['title']) . ' [' . esc_html($platnosc['id']) . ']' . '</option>';
        }
?>
    </select>
    
    <select class="metoda-dostawy" name="moja_wtyczka_metoda_dostawy[]">
    <option value="">Wybierz metodę dostawy</option>
     <?php
        foreach ($metody_dostawy as $dostawa)
        {
            $selected = ($dostawa === $metoda_dostawy) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($dostawa['id']) . '" ' . $selected . '>' . esc_html($dostawa['title']) . ' [' . esc_html($dostawa['id']) . ']' . '</option>';
        }
?>
  </select>

  <?php
    }
    else
    {
        echo 'Sprawdź konfiguracje metod płatności lub metod dostaw swojego sklepu internetowego';
    } ?>
    </div>


    <style>
        p.submit {
            width: 50% !important;
            margin: auto !important;
            text-align: center !important;
        }
    </style>

    <?php
}

function zapisz_opcje_wtyczki()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'reguly_dostepnosci_platnosci_dla_dostaw';

    if (isset($_POST['submit']))
    {
        $metody_dostawy = $_POST['moja_wtyczka_metoda_dostawy'];
        $metody_platnosci = $_POST['moja_wtyczka_metoda_platnosci'];

        foreach ($metody_dostawy as $index => $metoda_dostawy)
        {
            $reguly = array();
            if (isset($_POST['moja_wtyczka_reguly'][$index]))
            {
                $reguly = $_POST['moja_wtyczka_reguly'][$index];
            }

            $data = array(
                'metoda_platnosci' => $metody_platnosci[$index],
                'metoda_dostawy' => $metoda_dostawy,
            );

            $format = array(
                '%s',
                '%s',
                '%d',
                '%s'
            );
            $wpdb->insert($table_name, $data, $format);
        }
    }
}
add_action('admin_init', 'zapisz_opcje_wtyczki');

function usun_wiersz_z_tabeli()
{
    if (isset($_POST['action']) && $_POST['action'] === 'usun_wiersz' && wp_verify_nonce($_POST['nonce'], 'usun_wiersz_nonce'))
    {
        if (isset($_POST['wiersz_id']))
        {
            $wiersz_id = absint($_POST['wiersz_id']);

            global $wpdb;
            $table_name = $wpdb->prefix . 'reguly_dostepnosci_platnosci_dla_dostaw';
            $wpdb->delete($table_name, array(
                'id' => $wiersz_id
            ) , array(
                '%d'
            ));

            wp_send_json_success('Wiersz został pomyślnie usunięty');
        }
        else
        {
            wp_send_json_error('Nieprawidłowy identyfikator wiersza');
        }
    }
    else
    {
        wp_send_json_error('Wystąpił błąd podczas usuwania wiersza');
    }
}
add_action('wp_ajax_usun_wiersz', 'usun_wiersz_z_tabeli');
add_action('wp_ajax_nopriv_usun_wiersz', 'usun_wiersz_z_tabeli');

