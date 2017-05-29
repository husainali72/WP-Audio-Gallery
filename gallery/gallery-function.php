<?php
if (defined('ALLOWED_REFERRER')){
    define('ALLOWED_REFERRER', '');
}

function wpag_addgallery_process() {
    if ( isset($_POST['bulkaction']) && isset ($_POST['doaction']) ){

        switch ($_POST['bulkaction']) {
            case 'delete_audios':
                if ( is_array($_POST['doaction']) ) {
                    foreach ( $_POST['doaction'] as $audioID ) {
                        $audio = wpag_db_get_audio( $audioID );

                        $audio_path = wpag_convert_urltopath( $audio['audioURL'] );
                        if( is_file( $audio_path ) ){
                            unlink( $audio_path );
                        }
                        wpag_db_delete_audio( $audioID );
                    }
                }
                break;
        }

    }

    if( isset( $_POST['updateaudio'] ) ) {
        $audios = isset ( $_POST['aid'] ) ? $_POST['aid'] : false;
        $titles = isset ( $_POST['title'] ) ? $_POST['title'] : array();
		$orders= isset ( $_POST['orders'] ) ? $_POST['orders'] : array();
		
        if ( is_array($audios) ){
            foreach( $audios as $aid ){
                $audio = wpag_db_get_audio( $aid );
                if( !empty( $audio ) ) {
				
					if($titles!=0)
					{
                    	wpag_db_update_audio( $aid, sanitize_text_field($titles[$aid]) );
					}
					if($orders!=0)
					{
						wpag_db_update_order( $aid, sanitize_text_field($orders[$aid]) );
				
					}
					
						
					
                }
            }
        }
    }

}


function display_gallery_review_detail_meta_box( $gallery_review ) {
    // Retrieve current author and rating based on review ID
    $gallery_author = esc_html( get_post_meta( $gallery_review->ID, 'gallery_author', true ) );

    ?>
    <table>
        <tr>
            <td style="width: 150px">Gallery Author</td>
            <td><input type="text" size="80"
                       name="gallery_review_author_name"
                       value="<?php echo $gallery_author; ?>" /></td>
        </tr>
    </table>
<?php }



function display_gallery_review_audiolist_meta_box( $gallery_review ) {
    $counter	= 0;

    $wp_list_table = new wp_Audio_List_Table('wpag-manage-audios');

    $audioList = wpag_db_get_AudioGallery( $gallery_review->ID );

    $audio_columns   = $wp_list_table->get_columns();
	
    $hidden_columns  = get_hidden_columns('wpag-manage-audios');
    $num_columns     = count($audio_columns) - count($hidden_columns);

?>
    <script>
        function checkAll(form)
        {
            for (i = 0, n = form.elements.length; i < n; i++) {
                if(form.elements[i].type == "checkbox") {
                    if(form.elements[i].name == "doaction[]") {
                        if(form.elements[i].checked == true)
                            form.elements[i].checked = false;
                        else
                            form.elements[i].checked = true;
                    }
                }
            }
        }

        function getNumChecked(form)
        {
            var num = 0;
            for (i = 0, n = form.elements.length; i < n; i++) {
                if(form.elements[i].type == "checkbox") {
                    if(form.elements[i].name == "doaction[]")
                        if(form.elements[i].checked == true)
                            num++;
                }
            }
            return num;
        }

        function checkSelected() {

            var numchecked = getNumChecked(document.getElementById('updategallery'));

            if (typeof document.activeElement == "undefined" && document.addEventListener) {
                document.addEventListener("focus", function (e) {
                    document.activeElement = e.target;
                }, true);
            }

            if ( document.activeElement.name == 'post_paged' )
                return true;

            if(numchecked < 1) {
                alert('<?php echo esc_js(__('No audios selected', 'hb')); ?>');
                return false;
            }

            return confirm('<?php echo sprintf(esc_js(__("You are about to start the bulk edit for %s audios \n \n 'Cancel' to stop, 'OK' to proceed.",'hb')), "' + numchecked + '") ; ?>');
        }
    </script>
    <div class="tablenav top">
    <div class="alignleft actions">
        <select id="bulkaction" name="bulkaction">
            <option value="no_action" ><?php _e("Bulk actions",'hb'); ?></option>
            <option value="delete_audios" ><?php _e("Delete audios",'hb'); ?></option>
        </select>
        <input class="button-secondary" type="submit" name="showThickbox" value="<?php _e('Apply', 'nggallery'); ?>" onclick="if ( !checkSelected() ) return false;" />
        <input type="submit" name="updateaudio" class="button-primary action"  value="<?php _e('Save Changes', 'hb');?>" />
    </div>
    </div>

    <table id="hb-listaudios" class="widefat fixed" cellspacing="0" >

        <thead>
        <tr>
            <?php $wp_list_table->print_column_headers(true); ?>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <?php $wp_list_table->print_column_headers(false); ?>
        </tr>
        </tfoot>
        <tbody id="the-list">
        <?php
        if($audioList) {

            foreach($audioList as $audio) {
                $counter++;
                $aid       = (int) $audio['aid'];

                ?>
                <tr id="audio-<?php echo $aid ?>" class="iedit"  valign="top">
                    <?php
                    foreach($audio_columns as $audio_column_key => $column_display_name) {
                        $class = "class='$audio_column_key column-$audio_column_key'";

                        $style = '';
                        if ( in_array($audio_column_key, $hidden_columns) )
                            $style = ' style="display:none;"';

                        $attributes = $class . $style;
						
                        switch ($audio_column_key) {
                            case 'cb' :
                                $attributes = 'class="column-cb check-column"' . $style;
                                ?>
                                <th <?php echo $attributes ?> scope="row"><input name="doaction[]" type="checkbox" value="<?php echo $aid ?>" /></th>
                                <?php
                                break;
                            case 'id' :
                                ?>
                                <td <?php echo $attributes ?> style=""><?php echo $aid; ?>
                                    <input type="hidden" name="aid[]" value="<?php echo $aid ?>" />
                                </td>
                                <?php
                                break;
                            case 'filename' :
                                $attributes = 'class="title column-filename column-title"' . $style;
                                ?>
                                <td <?php echo $attributes ?>>
                                    <strong><a href="<?php echo esc_url( $audio['audioURL'] ); ?>" class="thickbox" title="<?php echo esc_attr ($audio['filename']); ?>">
                                            <?php echo esc_attr($audio['filename']); ?>
                                        </a></strong>
                                </td>
                                <?php
                                break;
                            case 'title' :
                                ?>
                                <td <?php echo $attributes ?>>
                                    <input name="title[<?php echo $aid ?>]" type="text" style="width:95%; margin-bottom: 2px;" value="<?php echo stripslashes($audio['title']) ?>" />
                                </td>
                                <?php
                                break;
								
							case 'orders' : 
							if($audio['orders']== 0) {?>
                            <td <?php echo $attributes ?>>
                         	<input name="orders[<?php echo $aid ?>]" type="text" style="width:15%; margin-bottom: 2px;" value=""/>
                            </td> 
							<?php } else {?> 
                            <td <?php echo $attributes ?>>
                      <input name="orders[<?php echo $aid ?>]" type="text" style="width:15%; margin-bottom: 2px;" value="<?php echo stripslashes($audio['orders']) ?>"/>
                            </td>
                            <?php }?>
                            
							<?php
							break;	    //After uploading order is blank 
								
								
                            default :
                                ?>
                                    <td <?php echo $attributes ?>><?php do_action('ngg_manage_image_custom_column', $audio_column_key, $aid); ?></td>
                                <?php
                                break;
                        }
                        ?>
                    <?php } ?>
                </tr>
            <?php
            }
        }

        // In the case you have no capaptibility to see the search result
        if ( $counter == 0 )
            echo '<tr><td colspan="' . $num_columns . '" align="center"><strong>'.__('No entries found','hb').'</strong></td></tr>';

        ?>

        </tbody>
    </table>
<?php
}


function display_gallery_review_upload_meta_box( $gallery_review ) {

    global $post;
    if( $post->post_status != "publish" ) {?>
        <p style="color:#ff0000">Upload function is active after post is published.</p>
    <?php
        return;}


    $action_url = admin_url() . 'post.php?post=' . $gallery_review->ID . '&ampaction=edit';

    $upload_directory = WPAG_UPLOAD_DIR . '/' . get_the_title( $gallery_review->ID );
    if ( get_the_title( $gallery_review->ID ) != 'Auto Draft' && !is_dir($upload_directory)) {
        umask(0);
        mkdir($upload_directory, 0777);
    }


    $gallery_id = $gallery_review->ID;

    $wp_list_table = new wp_Audio_List_Table('wpag-manage-audios');
    $audio_columns   = $wp_list_table->get_columns();
    $audio_columns['cb'] = 'cb';
    $audio_columns_str = implode( ',', $audio_columns );

    $hidden_columns  = get_hidden_columns('wpag-manage-audios');
    $hidden_columns_str = implode( ',', $hidden_columns );
    if(empty($hidden_columns_str))
        $hidden_columns_str = 'no';

    $id = "audio1";
    $svalue = "";



?>

    <form name="uploadaudio" id="uploadaudio_form" method="POST" enctype="multipart/form-data" action="<?php echo $action_url; ?>" accept-charset="utf-8" >
        <input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $svalue; ?>" />
        <input type="hidden" name="upload-directory" id="upload-directory" value="<?php echo $upload_directory; ?>" />
        <input type="hidden" name="gallery-id" id="gallery-id" value="<?php echo $gallery_id; ?>" />
        <input type="hidden" name="audio-columns" id="audio-columns" value='<?php echo $audio_columns_str; ?>' />
        <input type="hidden" name="hidden-columns" id="hidden-columns" value="<?php echo $hidden_columns_str; ?>" />
        <div class="plupload-upload-uic hide-if-no-js plupload-upload-uic-multiple" id="<?php echo $id; ?>plupload-upload-ui">
            <table>
                <tr>
                    <td style="width: 150px">Upload Audios</td>
                    <td>
                        <input id="<?php echo $id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button" style="width: 100px;"/>
                        <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($id . 'pluploadan'); ?>"></span>
                    </td>
                </tr>
                <?php
                    global $wpag_wp_Loader;
					
					
                   // if( $remote_pay_version == 'pay' ) { ?>
                        <tr>
                            <td style="width: 150px">Scan Audios</td>
                            <td>
                                <input id="scan-files-button" type="button" value="<?php esc_attr_e('Scan Files'); ?>" class="button"  style="width: 100px;" onclick="scan_files();"/>
                            </td>
                        </tr>
                <?php // } ?>

            </table>

            <div class="filelist"></div>
            <div class="file-upload-message" id="file-upload-message" style="margin-top: 10px;  padding:5px 40px; background-color:#f1fddc; border:1px solid #99cc00; border-radius: 3px; color:#339900; display: none;"></div>
        </div>
        <div class="plupload-thumbs plupload-thumbs-multiple" id="<?php echo $id; ?>plupload-thumbs">
        </div>
        <div class="clear"></div>
    </form>

<?php
}



function wpag_setting_config_page() {

    $options = get_option( WPAG_OPTIONS );

    global $wpag_wp_Loader;
    ?>
    <div id="wp-ag-setting" class="wrap" style="width:900px;">
        <h2>WP Audio Gallery Setting</h2>
        <?php if ( isset( $_GET['message'] ) && $_GET['message'] == '1' ) { ?>
            <div id='message' class='updated fade'><p><strong>Settings Saved</strong></p></div>
        <?php } ?>

        <?php //if( $remote_pay_version == 'pay' ) { ?>
            <div class="" style="margin-top: 40px;">
                <h3>Audio Download and Sharing</h3>
                <div style="margin-left: 20px;">
                    <form method="post" action="admin-post.php">
                        <input type="hidden" name="action" value="save_wp_ag_option" />

                        <?php wp_nonce_field( 'wp_ag_setting' ); ?>
                        <table>
                            <tr>
                                <td style="width: 200px;"><input type="checkbox" name="wpag_audio_download_enable" <?php if( $options['wpag_audio_download_enable'] ) echo ' checked="checked" ';?>/>   Enable Audio Download</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="width: 200px;"><input type="checkbox" name="wpag_audio_facebook_sharing" <?php if( $options['wpag_audio_facebook_sharing'] ) echo ' checked="checked" ';?>/>   Enable Facebook Sharing</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="width: 200px;"><input type="checkbox" name="wpag_audio_addthis_sharing" <?php if( $options['wpag_audio_addthis_sharing'] ) echo ' checked="checked" ';?>/>   Enable Addthis Sharing</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="width: 200px; padding-left:20px;">Addthis Publisher ID</td>
                                <td><input type="text" name="addthis_publish_id" value="<?php echo esc_html( $options['addthis_publish_id'] ); ?>"  style="width: 300px"/></td>
                            </tr>
                        </table>
                        <input type="submit" value="Save Settings" class="button-primary"/>
                    </form>
                </div>
            </div>
        <?php //} ?>


        <div class="" style="margin-top: 40px;">
            <h3>Shortcode</h3>
            <div style="margin-left: 20px;">
                <table>
                	<tr>
                        <td style="width: 200px;">Single Audio Player</td>
                        <td><code>[wp-audio-single-audio aid="<span style="color: #0088d0">(audio id)</span>"]</code></td>
                    </tr>
                    
                     <tr>
                        <td style="width: 200px;">Audio Gallery Player</td>
                        <td><code>[wp-audio-list-in-gallery gid="<span style="color: #0088d0">(gallery id)</span>" autoplay="no"]</code></td>
                    </tr>
                    <tr>
                        <td style="width: 200px;">Category Wise Player</td>
                        <td><code>[wp-audio-gallery-list-in-category category_id="<span style="color: #0088d0">(category id)</span>"]</code></td>
                        
                    </tr>
                     <tr>
                        <td style="width: 200px;">Audio Gallery List</td>
                        <td><code>[wp-audio-gallery-list]</code></td>
                    </tr>
                   </table>
            </div>
        </div>

<?php

        $success = false;
        $htaccess_orig_path = ABSPATH.'.htaccess';
        @chmod($htaccess_orig_path, 0644);
        $htaccess_content = @file_get_contents($htaccess_orig_path, false, NULL);
        if($htaccess_content === false){
        $success = wpag_WriteNewHtaccess('# Created by WP Audio Gallery Plugin');
        if( $success == true ) {
        $htaccess_content = @file_get_contents($htaccess_orig_path, false, NULL);
        } else {
        echo'<div class="postbox wphe-box">';
            echo'<pre class="wphe-red">'.__('Htaccess file cannot read!', 'wphe').'</pre>';
            echo'</div>';
        }
        } else {
            $success = true;
        }

        if($success == true){
?>
        <div class="" style="margin-top: 40px;">
            <h3 class="" style="margin-bottom: 0;">Content of the Htaccess file</h3>
            <div style="margin-left: 20px;">
                <form method="post" action="admin-post.php">
                    <textarea id="ht_content" name="ht_content" class="" style="width:450px; height: 200px;"><?php echo $htaccess_content;?></textarea>
                    <div class="description" style="display: inline-block;  width:400px; margin-left: 20px;">
                        <p>If audio file size is greater than maxium upload file size, audio file upload can be failed.</p>
                        <p>At this time, you must increase maximum upload file size by copying follow text to .htaccess file.</p>
                        <p style="color: #0088d0;">&lt;IfModule mod_php5.c&gt;<br/>
                            php_value post_max_size 64M<br/>
                            php_value upload_max_filesize 64M<br/>
                            php_value max_execution_time 300<br/>
                            php_value max_input_time 300<br/>
                            &lt;&#47;IfModule&gt;</p>
                        <p>Please copy above text, and then <b>paste to end of .htaccess file</b>.</p>
                    </div><br/>
                    <input id="save-htaccess-button" type="button" value="<?php esc_attr_e('Save Files'); ?>" class="button"  style="width: 100px;" onclick="save_htaccess();"/>
                    <div class="save-htaccess-message" id="save-htaccess-message" style="margin-top: 10px;  padding:5px 40px; background-color:#f1fddc; border:1px solid #99cc00; border-radius: 3px; color:#339900; display: none;"></div>
                </form>
            </div>
        </div>
<?php
        unset($htaccess_content);
        }
?>
    </div>
<?php }


if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class wp_Audio_List_Table extends WP_List_Table {
    var $_screen;
    var $_columns;

    function wp_Audio_List_Table( $screen ) {
        if ( is_string( $screen ) )
            $screen = convert_to_screen( $screen );

        $this->_screen = $screen;
        $this->_columns = array() ;

        add_filter( 'manage_' . $screen->id . '_columns', array( &$this, 'get_columns' ), 0 );
    }

    function get_column_info() {

        $columns = get_column_headers( $this->_screen );
        $hidden = get_hidden_columns( $this->_screen );
        $_sortable = $this->get_sortable_columns();
        $sortable = array();

        foreach ( $_sortable as $id => $data ) {
            if ( empty( $data ) )
                continue;

            $data = (array) $data;
            if ( !isset( $data[1] ) )
                $data[1] = false;

            $sortable[$id] = $data;
        }

        return array( $columns, $hidden, $sortable, 'gid' );
    }

    // define the columns to display, the syntax is 'internal name' => 'display name'
    function get_columns() {
        $columns = array();

        $columns['cb'] = '<input name="checkall" type="checkbox" onclick="checkAll(document.getElementById(\'updategallery\'));" />';
        $columns['id'] = __('ID');
        $columns['filename'] = __('Filename', 'hb');
        $columns['title'] = __('Title', 'hb');
		$columns['orders'] = __('Orders', 'hb');	//Define new column order

        return $columns;
    }

    function get_sortable_columns() {
        return array();
    }
}