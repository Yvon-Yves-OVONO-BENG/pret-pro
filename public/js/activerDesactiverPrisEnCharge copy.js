document.addEventListener('DOMContentLoaded', function() {
    $('.custom-switch-input').change(function() {
        let clientId = $(this).data('id');
        let isChecked = $(this).is(':checked');
        
        $.ajax({
            url: '/pretpro/public/client/terminer-client/' + clientId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    notif({
                        msg: "<b>Prise en charge du client terminé avec succès !</b>",
                        type: "success",
                        position: "left",
                        width: 500,
                        height: 60,
                        autohide: true
                        });
                }
                else
                {
                    notif({
                        msg: "<b>Erreur lors de la modification de la prise en charge du client !</b>",
                        type: "error",
                        position: "left",
                        width: 500,
                        height: 60,
                        autohide: true
                        });
                }
            },
            error: function() {
                notif({
                    msg: "<b>Erreur lors de la requête AJAX !</b>",
                    type: "error",
                    position: "left",
                    width: 500,
                    height: 60,
                    autohide: true
                    });
            }
        });
        
    });
});