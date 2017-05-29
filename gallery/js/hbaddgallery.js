function add_gallery_audio( audioId, galleryid, upload_dir, audio_columns, hidden_columns ) {
    var $=jQuery;
    var audioS=$("#"+audioId).val();
    $("#file-upload-message").html('');
    $("#file-upload-message").css( "display", "none" );

    var data = {
        action: 'hb-uploadaudio',
        gallery_id: galleryid,
        upload_dirctory:upload_dir,
        audio_upload:audioS,
        audio_columns:audio_columns,
        hidden_columns:hidden_columns
    };

    jQuery.post(ajax_object.ajax_url, data, function(response) {
        var obj = jQuery.parseJSON(response);
        $("#the-list").html(obj.content);
        $("#file-upload-message").html(obj.message);
        $("#file-upload-message").css( "display", "block" );
        $("#"+audioId).val('');
    });
}