

function save_htaccess() {
    var $=jQuery;
    var ht_content = $("#ht_content").val();
    $("#save-htaccess-message").html('');
    $("#save-htaccess-message").css( "display", "none" );

    var data = {
        action: 'hb-htaccess',
        ht_content: ht_content
    };

    jQuery.ajaxSettings.traditional = true;
    jQuery.post(ajax_object.ajax_url, data, function(response) {
        var obj = jQuery.parseJSON(response);
        $("#save-htaccess-message").html(obj.message);
        $("#save-htaccess-message").css( "display", "block" );
    });
}