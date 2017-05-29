function scan_files() {

    var $=jQuery;
    var upload_directory = $("#upload-directory").val();
    var gallery_id = $("#gallery-id").val();
    var audio_columns = $("#audio-columns").val();
    var hidden_columns = $("#hidden-columns").val();
    $("#file-upload-message").html('');
    $("#file-upload-message").css( "display", "none" );

    var data = {
        action: 'hb-scanaudio',
        gallery_id: gallery_id,
        upload_dirctory:upload_directory,
        audio_columns:audio_columns,
        hidden_columns:hidden_columns
    };
    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
    jQuery.post(ajax_object.ajax_url, data, function(response) {
        var obj = jQuery.parseJSON(response);
        $("#the-list").html(obj.content);
        $("#file-upload-message").html(obj.message);
        $("#file-upload-message").css( "display", "block" );
    });
}