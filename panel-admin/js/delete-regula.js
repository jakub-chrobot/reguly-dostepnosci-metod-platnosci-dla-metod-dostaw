jQuery(document).ready(function($) {
    $('.usun-przycisk').on('click', function(e) {
        e.preventDefault();

        var wierszID = $(this).data('wiersz-id');

        // Wyślij żądanie AJAX do usunięcia wiersza
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'usun_wiersz',
                nonce: customPluginData.nonce,
                wiersz_id: wierszID
            }, beforeSend: function() {
                // Wyświetl komunikat "Usuwanie..."
                $('.komunikat-usuwania').text('Usuwanie...').show();
            },beforeSend: function() {
                // Wyświetl komunikat "Usuwanie..."
                $('.komunikat-usuwania').text('Usuwanie...').show();
            },
            success: function(response) {
                if (response.success) {
                    // Wyświetl komunikat sukcesu
                    $('.komunikat-usuwania').text(response.data).show();

                    // Usuń wiersz z tabeli
                    tr.remove();

                    // Tutaj możesz wykonać dodatkowe operacje po usunięciu wiersza
                } else {
                    // Wyświetl komunikat o błędzie
                    $('.komunikat-usuwania').text(response.data).show();
                }
            },
            error: function(xhr, status, error) {
                // Wyświetl komunikat o błędzie żądania
                $('.komunikat-usuwania').text('Wystąpił błąd podczas usuwania wiersza').show();
            },
            complete: function() {
                // Schowaj komunikat po zakończeniu żądania
                setTimeout(function() {
                    $('.komunikat-usuwania').text('Reguła została usunięta! Skonfiguruj teraz nową lub odśwież stronę w celu zobaczenia zmian!').show();
                    $('.komunikat-usuwania').fadeOut('slow');
                }, 12000);
            }
        });
    });
});