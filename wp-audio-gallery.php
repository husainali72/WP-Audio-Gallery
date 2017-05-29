<?php

/*

Plugin Name: WP AUDIO GALLERY

Plugin URI: http://www.husainbandookwala.com/

Description: A HTML5 based, simple and responsive audio player plugin which supports custom post type, shortcodes and works on all Browsers & devices for WordPress by Husain Bandook Wala.

Version: 1.0

Author: Husain Bandook Wala

Author URI: http://www.husainbandookwala.com/

License: GPLv2

*/





class wpag_wp_Loader {



    var $gallery;

    var $update;



    function wpag_wp_Loader() {

        $this->wpag_load_defines();

        $this->wpag_load_files();

        $this->wpag_create_gallery_directory();



        $plugin_name = basename(dirname(__FILE__)).'/'.basename(__FILE__);


        register_activation_hook( $plugin_name, array( &$this, 'wpag_plugin_active' ) );

        register_uninstall_hook( $plugin_name, array(__CLASS__, 'wpag_plugin_uninstall') );

        add_action( 'init', array( &$this, 'wpag_init' ), 11 );

        add_action( 'admin_init', array( &$this, 'wpag_admin_init' ) );

    }





    function  wpag_init() {

        wp_enqueue_script('jquery');



        wp_register_style('hb-style', WPAG_URLPATH .'css/hb-style.css', array(), null);

        wp_enqueue_style('hb-style');



        wp_register_style('hb-jplayer-style', WPAG_URLPATH .'lib/jPlayer/skin/blue.monday/jplayer.blue.monday.css', array(), null);

        wp_enqueue_style('hb-jplayer-style');



        wp_register_script('hb-jplayer', WPAG_URLPATH .'lib/jPlayer/js/jquery.jplayer.js', array('jquery'), null);

        wp_enqueue_script('hb-jplayer');



        wp_register_script('hb-jplayer-playlist', WPAG_URLPATH .'lib/jPlayer/js/jplayer.playlist.js', array('jquery'), null);

        wp_enqueue_script('hb-jplayer-playlist');



        add_action( 'wp_ajax_download_audio',  array( &$this, 'wpag_downloadaudiofile') );

        add_action( 'wp_ajax_nopriv_download_audio', array( &$this, 'wpag_downloadaudiofile') );



    }





    function  wpag_admin_init() {



        add_action( 'admin_head',  array( &$this, 'wpag_plupload_admin_head') );

        add_action( 'wp_ajax_plupload_action',  array( &$this, 'wpag_g_plupload_action') );



        wp_enqueue_script('plupload-all');



        wp_register_script('hbplupload', WPAG_URLPATH .'gallery/js/hbplupload.js', array('jquery'), null);

        wp_enqueue_script('hbplupload');



        wp_register_style('hbplupload', WPAG_URLPATH .'gallery/css/hbplupload.css');

        wp_enqueue_style('hbplupload');



        // scan audio ajax

        wp_enqueue_script('hb-scan-audiofile', WPAG_URLPATH .'gallery/js/hbscanfile.js', array('jquery'), null);

        wp_localize_script( 'hb-scan-audiofile', 'ajax_object',

            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

        add_action( 'wp_ajax_hb-scanaudio', array( &$this, 'wpag_scanaudio_callback') );



        // upload audio ajax

        wp_enqueue_script('hb-upload-audiofile', WPAG_URLPATH .'gallery/js/hbaddgallery.js', array('jquery'), null);

        wp_localize_script( 'hb-upload-audiofile', 'ajax_object',

            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

        add_action( 'wp_ajax_hb-uploadaudio', array( &$this, 'wpag_uploadaudio_callback') );



        // htaccess ajax

        wp_enqueue_script('hb-htaccess', WPAG_URLPATH .'gallery/js/hbhtaccess.js', array('jquery'), null);

        wp_localize_script( 'hb-htaccess', 'ajax_object',

            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

        add_action( 'wp_ajax_hb-htaccess', array( &$this, 'wpag_htaccess_callback') );



        add_action( 'post_updated', array( &$this, 'wpag_update_gallery_folder_name'), 10, 3 );



    }





    function wpag_load_defines() {
        define( 'WPAG_WINABSPATH', str_replace("\\", "/", ABSPATH) );

        define( 'WPAG_AG_FOLDER', basename( dirname( __FILE__ ) ) );

        define( 'WPAG_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . WPAG_AG_FOLDER ) ) );

        define( 'WPAG_URLPATH', trailingslashit( plugins_url( WPAG_AG_FOLDER ) ) );



        $upload_dir = wp_upload_dir();



        $upload_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'wp-audio-gallery';

        $upload_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_path);

        $upload_path = str_replace('\\', DIRECTORY_SEPARATOR, $upload_path);

        define( 'WPAG_UPLOAD_DIR', $upload_path );

        

        define( 'WPAG_OPTIONS', 'wp_ag_options' );



        global $wpdb;

    }





    function  wpag_load_files() {

        require_once (dirname (__FILE__) . '/gallery/gallery.php');

        require_once (dirname (__FILE__) . '/gallery/gallery-content.php');

        $this->gallery = new wpag_wp_gallery();



        require_once (dirname (__FILE__) . '/lib/audioDB.php');

        require_once (dirname (__FILE__) . '/lib/util-functions.php');

    }



    function wpag_create_gallery_directory() {

        require_once( ABSPATH . "wp-admin/includes/class-wp-filesystem-base.php" );

        require_once( ABSPATH . "wp-admin/includes/class-wp-filesystem-direct.php" );

        $wp_fs_d = new WP_Filesystem_Direct( new StdClass() );



        if ( !$wp_fs_d->is_dir( WPAG_UPLOAD_DIR ) && !$wp_fs_d->mkdir( WPAG_UPLOAD_DIR, 0777 ) )

            wp_die( sprintf( __( "Impossible to create %s directory." ), WPAG_UPLOAD_DIR ) );



        $uploads = wp_upload_dir();

        if ( !$wp_fs_d->is_dir( $uploads['path'] ) && !$wp_fs_d->mkdir( $uploads['path'], 0777 ) )

            wp_die( sprintf( __( "Impossible to create %s directory." ), $uploads['path'] ) );



//        if (!is_dir($uploads['path'])) {

//            umask(0);

//            mkdir($uploads['path'], 0777);

//        }



    }

    function wpag_plupload_admin_head() {



    // place js config array for plupload

        $plupload_init = array(

            'runtimes' => 'html5,silverlight,flash,html4',

            'browse_button' => 'plupload-browse-button', // will be adjusted per uploader

            'container' => 'plupload-upload-ui', // will be adjusted per uploader

            'drop_element' => 'drag-drop-area', // will be adjusted per uploader

            'file_data_name' => 'async-upload', // will be adjusted per uploader

            'multiple_queues' => true,

            'max_file_size' => 64000000 . 'b',

            'url' => admin_url('admin-ajax.php'),

            'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),

            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),

            'filters' => array(array('title' => __('Audio Files'), 'extensions' => 'mp3')),

            'multipart' => true,

            'urlstream_upload' => true,

            'multi_selection' => false, // will be added per uploader

            // additional post data to send to our ajax hook

            'multipart_params' => array(

                '_ajax_nonce' => "", // will be added per uploader

                'action' => 'plupload_action', // the ajax action name

                'audioid' => 0 // will be added per uploader

            )

        );

        ?>

        <script type="text/javascript">

            var base_plupload_config=<?php echo json_encode($plupload_init); ?>;

        </script>

    <?php

    }





    function wpag_g_plupload_action() {



        // check ajax noonce

        $audioid = sanitize_text_field($_POST['audioid']);



        check_ajax_referer($audioid . 'pluploadan');



        // handle file upload

        $status = wp_handle_upload($_FILES[$audioid . 'async-upload'], array('test_form' => true, 'action' => 'plupload_action'));



        // send the uploaded file url in response

        echo $status['url'];

        exit;

    }







    function wpag_scanaudio_callback() {



        $upload_dir = sanitize_text_field($_REQUEST['upload_dirctory']) . '/';

        $gallery_id = sanitize_text_field($_REQUEST['gallery_id']);

        $audio_columns = $_REQUEST['audio_columns'];

        $hidden_columns = $_REQUEST['hidden_columns'];



        $audio_columns = explode(",", $audio_columns);

        if($hidden_columns != "no")

            $hidden_columns = explode(",", $hidden_columns);

        else

            $hidden_columns = array();



        global $wpdb;



        $gallerypost = get_post($gallery_id); 

        $galleryslug = $gallerypost->post_name;

        //die($galleryslug);



        $audioList = array();

        $audioList = wpag_db_get_AudioGallery( $gallery_id );



        $audio_filename_List = array();

        $count_del = 0;

        foreach( $audioList as $audio ) {

            $filepath = wpag_convert_urltopath( $audio['audioURL'] );

            if( is_file( $filepath ) == false ) {

                wpag_db_delete_audio($audio['aid']);

                $count_del++;

                continue;

            }

            array_push( $audio_filename_List, $audio['filename'] );

        }



        $count = 0;

        if (is_dir($upload_dir)) {

            if ($dh = opendir($upload_dir)) {

                while (($file = readdir($dh)) !== false) {

                    if($file == "." || $file == "..")

                        continue;



                    if( !in_array( $file, $audio_filename_List ) ) {

                        $file_path = $upload_dir . $file;

                        $file_url = wpag_convert_pathtourl( $file_path );

                        $filepart = pathinfo( $file_path );



						$orders=0;

                        wpag_db_insert_audio( $gallery_id, $filepart['filename'], $filepart['basename'], $file_url, $orders);

                        $count++;

                    }

                }

                closedir($dh);

            }

        }



        $audioList = wpag_db_get_AudioGallery( $gallery_id );

        $return_arr['content'] = wp_get_gallery_list_content( $audioList, $audio_columns, $hidden_columns );

        $return_arr['message'] = '<p style="margin:5px 0;">Scan Finished!</p>';



        if( $count != 0 ){

            $return_arr['message'] .= '<p style="margin:5px 0;">' . $count . ' files are added.</p>';

        } else {

            $return_arr['message'] .= '<p style="margin:5px 0;">No files to be added.</p>';

        }

        if( $count_del != 0 ) {

            $return_arr['message'] .= '<p style="margin:5px 0;">' . $count_del . ' files are not exist. These Files are removed in gallery.</p>';

        }



        echo json_encode( $return_arr );

        die();

    }





    function wpag_uploadaudio_callback() {

        if( $_REQUEST['upload_dirctory'] && $_REQUEST['audio_upload'] && $_REQUEST['gallery_id'] && $_REQUEST['audio_columns'] && $_REQUEST['hidden_columns'] ) {



            $upload_dir = $_REQUEST['upload_dirctory'];

            $audioS = $_REQUEST['audio_upload'];

            $audio_arr = explode( ',', $audioS );

			



            $gallery_id = sanitize_text_field($_REQUEST['gallery_id']);

            $audio_columns = $_REQUEST['audio_columns'];

            $hidden_columns = $_REQUEST['hidden_columns'];



            $audio_columns = explode(",", $audio_columns);

            if($hidden_columns != "no")

                $hidden_columns = explode(",", $hidden_columns);

            else

                $hidden_columns = array();



            $audioList = array();



            $count_success = 0;

            $count_fail = 0;

            $count_exist = 0;

            foreach( $audio_arr as $audio ) {

                $filepart = pathinfo( $audio );



                $newfile = $upload_dir . '/' . $filepart['basename'];

                $newfile_url = wpag_convert_pathtourl( $newfile );

                $oldfile = wpag_convert_urltopath( $audio );





                if( file_exists( $newfile ) ) {

                    $count_exist++;

                    unlink( $oldfile );

                    continue;

                }



                wpag_copyfile( $oldfile, $newfile );

                if( file_exists( $newfile ) ) {

					//$orders=0;

                    wpag_db_insert_audio( $gallery_id, $filepart['filename'], $filepart['basename'], $newfile_url,$orders);

					

                    $count_success++;

                } else {

                    $count_fail++;

                }



                unlink( $oldfile );

            }



            $audioList = wpag_db_get_AudioGallery( $gallery_id );

			

            $return_arr['content'] = wp_get_gallery_list_content( $audioList, $audio_columns, $hidden_columns );

            $return_arr['message'] = '<p style="margin:5px 0;">Upload Finished!</p>';

            if($count_success != 0)

                $return_arr['message'] .= '<p style="margin:5px 0;">' . $count_success . ' files are uploaded.</p>';

            if( $count_fail != 0 )

                $return_arr['message'] .= '<p style="margin:5px 0;">' . $count_fail . ' files are failed.</p>';

            if( $count_exist != 0 )

                $return_arr['message'] .= '<p style="margin:5px 0;">' . $count_exist . ' files alread exist.</p>';



            echo json_encode( $return_arr );

            die();

        }

    }





    function wpag_htaccess_callback() {

        if( $_REQUEST['ht_content']  ) {

            $ht_content = $_REQUEST['ht_content'];

            if( wpag_WriteNewHtaccess( $ht_content ) ) {

                $return_arr['message'] = '<p style="margin:5px 0;">Save Successful!</p>';

            } else {

                $return_arr['message'] = '<p style="margin:5px 0;">The file could not be saved!</p>';

            }

            echo json_encode( $return_arr );

            die();

        }

    }



    function wpag_plugin_active() {
        wpag_db_create_table();
		wpag_add_order();

        if ( get_option( WPAG_OPTIONS ) === false ) {

            $new_options['wpag_audio_download_enable'] = false;

            $new_options['wpag_audio_facebook_sharing'] = false;

            $new_options['wpag_audio_addthis_sharing'] = false;

            $new_options['addthis_publish_id'] = "";

            add_option( WPAG_OPTIONS, $new_options );

        }

    }



    function wpag_plugin_uninstall() {

		

        if ( get_option( 'wp_ag_options' ) != false ) {

            delete_option( 'wp_ag_options' );

        }



        wpag_db_delete_table();

		

		wpag_db_delete_audiogallery_post();



        wpag_remove_dir( WPAG_UPLOAD_DIR );

		wpag_delete_custom_terms('wp_ag_category');

    }    



    function wpag_downloadaudiofile(){

        $audioID = sanitize_text_field($_REQUEST['audio']);

        $gid = sanitize_text_field($_REQUEST['gallery']);

        if (!$audioID || !$gid) die();        

        $audioRow = wpag_db_get_audio($audioID);

        $filename = basename($audioRow['filename']);

        $gallery_name = get_the_title($gid);

        $base_dir = WPAG_UPLOAD_DIR . DIRECTORY_SEPARATOR . $gallery_name;

        $file = $base_dir . DIRECTORY_SEPARATOR . $filename;

        $file_size = wpag_get_filesize( $audioRow['audioURL'] );

        if (!$file){

            die('invalid_audio');

        } else {

            $this->download_audio($file, $file_size);    

        }

    }



    function wpag_download_audio($file, $file_size){

        if( $file_size && $file ) {

            $filename = basename($file);



            if(strstr($_SERVER["HTTP_USER_AGENT"] , "MSIE 6.") or strstr($_SERVER["HTTP_USER_AGENT"] , "MSIE 5.5"))

            {

                Header("Content-type: application/x-msdownload");

                header("Content-type: application/octet-stream");

                header("Cache-Control: private, must-revalidate");

                header("Pragma: no-cache");

                header("Expires: 0");

            }

            else

            {

                header("Cache-control: private");

                header("Content-type: file/unknown");

                header('Content-Length: '.$file_size);

                Header("Content-type: file/unknown");

                Header("Content-Disposition: attachment; filename=\"" . $filename . "\"");

                Header("Content-Description: PHP5 Generated Data");

                header("Pragma: no-cache");

                header("Expires: 0");

            }



            if(is_file("$file")) {

                $fp = fopen("$file", "r");



                if(!fpassthru($fp)) {

                    fclose($fp);

                }

            } else {

                die('invalid_audio');

            }

        } else {

            die('invalid_audio');

        }

    }



    public function wpag_update_gallery_folder_name($post_ID, $post_after, $post_before)

    {

        if ($post_before->post_type == 'wpag_audio_gallery'){

            if ($post_before->post_title != $post_after->post_title){

                rename(WPAG_UPLOAD_DIR . DIRECTORY_SEPARATOR . $post_before->post_title, 

                    WPAG_UPLOAD_DIR . DIRECTORY_SEPARATOR . $post_after->post_title);

                wpag_db_updateGallery_title($post_ID, $post_before->post_title, $post_after->post_title);

            }

        }

    }



}



global $wpag_wp_Loader;

$wpag_wp_Loader = new wpag_wp_Loader();