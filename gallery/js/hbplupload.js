jQuery.fn.exists = function () {
    return jQuery(this).length > 0;
}
jQuery(document).ready(function($) {

	
if($("#audio1plupload-upload-ui").exists()){
    if($(".plupload-upload-uic").exists()) {
		
        var pconfig=false;
        $(".plupload-upload-uic").each(function() {

            var $this=$(this);
            var id1=$this.attr("id");
            var audioId=id1.replace("plupload-upload-ui", "");
            var upload_directory = $("#upload-directory").val();
            var gallery_id = $("#gallery-id").val();
            var audio_columns = $("#audio-columns").val();
            var hidden_columns = $("#hidden-columns").val();

            pconfig=JSON.parse(JSON.stringify(base_plupload_config));
		

            pconfig["browse_button"] = audioId + pconfig["browse_button"];
			//alert("heeeeeeeeeellllllllooooooo");
            pconfig["container"] = audioId + pconfig["container"];
            pconfig["drop_element"] = audioId + pconfig["drop_element"];
            pconfig["file_data_name"] = audioId + pconfig["file_data_name"];
            pconfig["multipart_params"]["audioid"] = audioId;
            pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");

            if($this.hasClass("plupload-upload-uic-multiple")) {
					
                pconfig["multi_selection"]=true;
            }

            var uploader = new plupload.Uploader(pconfig);
			
			
            uploader.bind('Init', function(up){

            });
//if(".button"){
            uploader.init();//}
			

            // a file was added in the queue
            uploader.bind('FilesAdded', function(up, files){
				

                $.each(files, function(i, file) {
                    $this.find('.filelist').append(
                        '<div class="file" id="' + file.id + '"><b>' +
                            file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' +
                            '<div class="fileprogress"></div></div>');
                });

                up.refresh();
                up.start();
            });

            uploader.bind('UploadProgress', function(up, file) {

                $('#' + file.id + " .fileprogress").width(file.percent + "%");
                $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
            });

            // a file was uploaded
            uploader.bind('FileUploaded', function(up, file, response) {
                $('#' + file.id).fadeOut();
                response=response["response"];

                if(response == "") {
                    alert( "Upload failed!\n\n" +
                        "Check this audio file size is greater than maximum upload file size.\n\n" +
                        "if audio file size is greater, please increase maximum size at setting menu. " );
                } else {
                    // add url to the hidden field
                    if($this.hasClass("plupload-upload-uic-multiple")) {
                        // multiple
                        var v1=$.trim($("#" + audioId).val());
                        if(v1) {
                            v1 = v1 + "," + response;
                        }
                        else {
                            v1 = response;
							//alert(v1);
                        }
                        $("#" + audioId).val(v1);
                    }
                    else {
                        // single
						
                        $("#" + audioId).val(response + "");
                    }
               }
            });

            uploader.bind('UploadComplete', function(up, file) {
                //up.settings.url += "?dir=" + encodeURI(upload_directory);
				//alert("Hello");	
				
                add_gallery_audio( audioId, gallery_id, upload_directory, audio_columns, hidden_columns );
				
				
            });
			
			
            uploader.bind('Error', function(up, err) {
                alert( "Upload Failure" );
            });
        });
    }
	}
});







function console_print_arr( arr ) {
    var arr_str = '';
    for(var i in arr) {
        arr_str += i+':'+arr[i]+'\n'
    }
    console.log(arr_str);
}