<?php
function wpag_gallery_review_list() {
    $query_params = array( 'post_type' => 'wpag_audio_gallery',
        'post_status' => 'publish',
        'posts_per_page' => 10 );
    $page_num = ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
    if ( $page_num != 1 )
        $query_params['paged'] = $page_num;
    $gallery_review_query = new WP_Query;
    $gallery_review_query->query( $query_params );
	$output = '<h3>Galley List</h3>';
    if ( $gallery_review_query->have_posts() ) {
        $output .= '<div class="hb-gallery-list"><table>';
        $output .= '<tr><th style="width: 350px"><strong>';
        $output .= 'Title</strong></th>';
        $output .= '<th><strong>Author</strong></th></tr>';
        while ( $gallery_review_query->have_posts() ) {
            $gallery_review_query->the_post();
            $output .= '<tr><td><a href="' . post_permalink();
            $output .= '">';
            $output .= get_the_title( get_the_ID() ) . '</a></td>';
            $output .='<td>';	
            $output .= esc_html( get_post_meta( get_the_ID(), 'gallery_author', true ) );
            $output .= '</td></tr>';
        }
        $output .= '</table></div>';
        // Display page navigation links
        if ( $gallery_review_query->max_num_pages > 1 ) {
            $output .= '<nav id="nav-below">';
            $output .= '<div class="nav-previous">';$output .= get_next_posts_link
            ( '<span class="meta-nav">&larr;</span>
                Older reviews',
                $gallery_review_query->max_num_pages );
            $output .= '</div>';
            $output .= '<div class="nav-next">';
            $output .= get_previous_posts_link
            ( 'Newer reviews <span class="meta-nav">
                &rarr;</span>',
                $gallery_review_query->max_num_pages );
            $output .= '</div>';
            $output .= '</nav>';
        }
        wp_reset_postdata();
    }
    return $output;
}

function wpag_audio_list_in_gallery( $atts ) {
    extract( shortcode_atts( array(
        'gid' => '',
        'autoplay' => 'no',
		'img' =>'no',
    ), $atts ) );
    global $wpdb;
	$output = '';
    $gallery = wpag_db_get_AudioGallery( $gid );
    foreach( $gallery as $audio )
	{
        $filepath = wpag_convert_urltopath( $audio['audioURL'] );
        if( is_file( $filepath ) == false ) 
		{
            wpag_db_delete_audio($audio['aid']);
            continue;
        }
	}
    $gallery = wpag_db_get_AudioGallery( $gid );
	if( !empty( $gallery ) ) {
		$wpag_plugin_slug = 'wp-audio-gallery/wp-audio-gallery.php';
		$gallery_name = get_the_title($gid);
	
		$output .= '<div class="wpag_audio_gallery_wrapper">';
		$output .= '<div class="title_image_wrapper">';
		$output .= '<div class="song_detail">';
		if( has_post_thumbnail($gid)){
		 $output .= '<div class="hbgallery_image">'.get_the_post_thumbnail( $gid).'</div>';  
		} 
		$output .= '<div class="right_content"><div class="hbgallery_title"><h3>' . get_the_title($gid) .'</h3></div>';
		$output .= '<div class="hbgallery_artist">' . esc_html( get_post_meta( $gid, 'gallery_author', true ) ) .'</div>';  //for displyaing artist name 
		$output .= '</div></div>';
		$output .= '</div>';   //For showing gallary title 
        $output .= '<script>
                    //<![CDATA[
                    jQuery(document).ready(function(){
                        new jPlayerPlaylist({
                            jPlayer: "#jquery_jplayer_' . $gid . '",
                            cssSelectorAncestor: "#jp_container_' . $gid . '"
                        }, [';
		foreach( $gallery as $audio ) {
			$filename = basename($audio['filename']);
			$base_dir = WPAG_UPLOAD_DIR . DIRECTORY_SEPARATOR . $gallery_name;
			$file = $base_dir . '/' . $filename;
			$file_size = wpag_get_filesize( $audio['audioURL'] );
			$filepart = pathinfo($audio['filename']);
			$download_action_url = WPAG_URLPATH . 'gallery/audio-download.php?file_size=' . $file_size . '&file_path=' . $file;
			$options = get_option( WPAG_OPTIONS );
			$addthis_pubID = $options['addthis_publish_id'];
			$output_button = '<div class=\"sharing_btn\" >';
			if( $options['wpag_audio_download_enable'] == true ) {
				/*$output_button .= '<a href=\"' . $download_action_url . '\" style=\"margin-right:7px;\" target=\"audio-download\">' . '<img src=\"' . WPAG_URLPATH . 'images/audio-down-16.png\" alt=\"audio download\" style=\"border:none;\"/></a>';*/
                $output_button .= '<a href=\"' . admin_url( 'admin-ajax.php' ) . '?action=download_audio&audio='. $audio['aid'] .'&gallery=' . $gid .'\"  style=\"margin-right:7px;\" target=\"audio-download\">' . 
                    '<img src=\"' . WPAG_URLPATH . 'images/audio-down-16.png\" alt=\"Download audio\" style=\"border:none;\" /></a>';
			}
			if( $options['wpag_audio_facebook_sharing'] == true ) {
				$output_button .= '<a style=\"cursor:pointer; margin-right:7px;\"' .
						' onclick=\"window.open(\'http://www.facebook.com/sharer.php?s=100&amp;p[title]=' . $audio['title'] . ' | ' . get_the_title() . '&amp;p[url]=' . get_permalink() . '\',\'sharer\',\'toolbar=0,status=0,width=548,height=325\');\"' .
						' href=\"javascript: void(0)\">' .
						'<img src=\"' . WPAG_URLPATH . 'images/audio-facebook-16.png\" alt=\"audio facebook share\" style=\"border:none;\"/></a>';
			}
			if( $options['wpag_audio_addthis_sharing'] == true ) {
				if($addthis_pubID != "") {
					$output_button .= '<a class=\"addthis_button wp_audio_share\" href=\"http://www.addthis.com/bookmark.php?v=300&amp;pubid=' . $addthis_pubID . '\" style=\"margin-right:7px; text-indent:-9999px;\" addthis:url=\"' . get_permalink() . '\" addthis:title=\"' . $audio['title'] . ' | ' . get_the_title() . '\"><img src=\"' . WPAG_URLPATH . 'images/audio-share-16.png\" alt=\"Bookmark and Share\" style=\"border:none;\"/><span style=\"display:none;\">' . $audio['title'] . ' | ' . get_the_title() . '</span></a>';
					$output_button .= '<script_ type=\"text/javascript\" src=\"//s7.addthis.com/js/300/addthis_widget.js#pubid=' . $addthis_pubID . '\"></script_>';
				}
			}
			$output_button .= '<iframe name=\"audio-download\" style=\"display: none\"></iframe>';
			$output_button .= '</div>';
			$output .=    '{
							title:"' . $audio['title'] . '",
							mp3:"' . $audio['audioURL'] . '",
							button:"' . $output_button . '"
						},';
		}
        $output .=      '], {';
        if($autoplay == "yes") {
            $output .=          'playlistOptions: { autoPlay: true },';
        }
        $output .=             'swfPath: "http://www.jplayer.org/latest/js/Jplayer.swf",
                                supplied: "mp3",
                                wmode: "window",
                                smoothPlayBar: true,
                                keyEnabled: true
                            });
                        });
                    //]]>
                </script>';
        $output .= '<div id="jquery_jplayer_' . $gid . '" class="jp-jplayer"></div>';
        $output .= '<div id="jp_container_' . $gid . '" class="jp-audio hb-playlist">';
        $output .=      '<div class="jp-type-playlist">';
        $output .=          '<div class="jp-gui jp-interface">';
        $output .=              '<ul class="jp-controls">';
        $output .=                  '<li  class="jp-previous"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/rewind.png></a></li>';
        $output .=                  '<li class="jp-play"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/play.png></a></li>';
        $output .=                  '<li class="jp-pause"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/pause.png></a></li>';
        $output .=                  '<li class="jp-next"><a href="javascript:;"  tabindex="1"><img src=' . WPAG_URLPATH . 'images/forward.png></a></li>';
        $output .=                  '<li class="jp-stop"><a href="javascript:;"  tabindex="1"><img src=' . WPAG_URLPATH . 'images/stop.png></a></li>';
		$output .=                  '<li class="jp-mute"><a href="javascript:;" tabindex="1" ><img src=' . WPAG_URLPATH . 'images/unmute.png></a></li>';
        $output .=                  '<li class="jp-unmute"><a href="javascript:;" ><img src=' . WPAG_URLPATH . 'images/mute.png></a></li>';
		$output .=                  '<li class="hb-volume_bar"><div class="jp-volume-bar"><div class="jp-volume-bar-value"></div></div></li>';
        $output .=                  '<li class="jp-volume-max"><a href="javascript:;"><img src=' . WPAG_URLPATH . 'images/maxvol.png></a></li>';
        $output .=              '</ul>';
		$output .=              '<div class="jp-progress">';
        $output .=                  '<div class="jp-seek-bar">';
        $output .=                      '<div class="jp-play-bar"></div>';
        $output .=                  '</div>';
        $output .=              '</div>';
        $output .=              '<div class="jp-time-holder">';
        $output .=                  '<div class="jp-current-time"></div>';
        $output .=                  '<div class="jp-duration"></div>';
        $output .=              '</div>';
        $output .=              '<ul class="jp-toggles">';
        $output .=                  '<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>';
        $output .=                  '<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>';
        $output .=                  '<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>';
        $output .=                  '<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>';
        $output .=              '</ul>';
        $output .=          '</div>';
        $output .=          '<div class="jp-playlist">';
        $output .=              '<ul>';
        $output .=                  '<li></li>';
        $output .=              '</ul>';
        $output .=          '</div>';
        $output .=          '<div class="jp-no-solution">';
        $output .=              '<span>Update Required</span>
        To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.';
        $output .=          '</div>';
        $output .=      '</div>';
        $output .=  '</div>';
		$output .=  '</div>';
    }
	
	return $output;
}

function wpag_single_audio( $atts ) {
    extract( shortcode_atts( array(
        'aid' => '',
        'autoplay' => 'no'
    ), $atts ) );
    global $wpdb;
    $audio = wpag_db_get_audio( $aid );
    $output = '';
    if( !empty( $audio ) ) {
        $output .= '<script>
                        //<![CDATA[
                        jQuery(document).ready(function(){
                            jQuery("#jquery_jplayer_s' . $aid . '").jPlayer({
                                ready: function () {
                                    jQuery(this).jPlayer("setMedia", {
                                        mp3:"' . $audio['audioURL'] . '"
                                    })';
								if($autoplay == "yes") {
									$output .= '.jPlayer("play");';
								}else{
									$output .= ';';
								}
                                $output .= '},swfPath: "http://www.jplayer.org/latest/js/Jplayer.swf",
                                supplied: "mp3",
                                wmode: "window",
                                smoothPlayBar: true,
                                keyEnabled: true,
                                cssSelectorAncestor: "#jp_container_s' . $aid . '",
                            });
                        });
                        //]]>
                    </script>';
        $output .= '<div id="jquery_jplayer_s' . $aid . '" class="jp-jplayer"></div>';
        $output .= '<div id="jp_container_s' . $aid . '" class="jp-audio hb-single">';
        $output .=      '<div class="jp-type-single">';
        $output .=          '<div class="jp-gui jp-interface">';
        $output .=              '<ul class="jp-controls">';
        $output .=                  '<li class="jp-play"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/play.png></a></li>';
        $output .=                  '<li class="jp-pause"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/pause.png></a></li>';
        $output .=                  '<li class="jp-stop"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/stop.png></a></li>';
        $output .=                  '<li class="jp-mute"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/unmute.png></a></li>';
        $output .=                  '<li class="jp-unmute"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/mute.png></a></li>';
		$output .=                  '<li class="hb-volume_bar"><div class="jp-volume-bar"><div class="jp-volume-bar-value"></div></div></li>';
        $output .=                  '<li class="jp-volume-max"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/maxvol.png></a></li>';
        $output .=              '</ul>';
        $output .=              '<div class="jp-progress">';
        $output .=                  '<div class="jp-seek-bar">';
        $output .=                      '<div class="jp-play-bar"></div>';
        $output .=                  '</div>';
        $output .=              '</div>';
        $output .=              '<div class="jp-time-holder">';
        $output .=                  '<div class="jp-current-time"></div>';
        $output .=                  '<div class="jp-duration"></div>';
        $output .=                  '<ul class="jp-toggles">';
        $output .=                      '<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>';
        $output .=                      '<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>';
        $output .=                  '</ul>';
        $output .=              '</div>';
        $output .=          '</div>';
        $output .=          '<div class="jp-title">';
        $output .=              '<ul>';
        $output .=                  '<li>' . $audio['title'] . '</li>';
        $output .=              '</ul>';
        $output .=          '</div>';
        $output .=          '<div class="jp-no-solution">';
        $output .=              '<span>Update Required</span>To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.';
        $output .=          '</div>';
        $output .=      '</div>';
        $output .=  '</div>';
		wp_reset_postdata();
    }
    return $output;
}

function wpag_gallery_list_in_category( $atts ) {
    extract( shortcode_atts( array(
   			'category_id' => ''
    ), $atts ) );
	
	global $wpdb;
	$output='';
	if( empty( $category_id ) ) {
		return $output;
	}
	
	$taxonomy='wp_ag_category';
	$term = get_term( $category_id, $taxonomy );
	$slug = $term->slug;
	$myposts = get_posts(array(
		'posts_per_page'=> -1,
		'post_type' => 'wpag_audio_gallery',
		'tax_query' => array(
			array(
			'taxonomy' => $taxonomy,
			'field' => 'slug',
			'terms' => $slug)
		))
	);
 
	foreach ($myposts as $mypost) {
		$gid = $mypost->ID;
		$gallery = wpag_db_get_AudioGallery( $gid );
	
		foreach( $gallery as $audio )
		{
			$filepath = wpag_convert_urltopath( $audio['audioURL'] );
			if( is_file( $filepath ) == false ) 
			{
				wpag_db_delete_audio($audio['aid']);
				continue;
			}
		}
	
		$gallery = wpag_db_get_AudioGallery( $gid );
		if( empty( $gallery ) ) {
			continue;
		}
		$wpag_plugin_slug = 'wp-audio-gallery/wp-audio-gallery.php';
		$gallery_name = get_the_title($gid);
		$aid = $audio['aid'];
		$base_dir = WPAG_UPLOAD_DIR . DIRECTORY_SEPARATOR . $gallery_name;
		$audio = wpag_db_get_audio( $aid );
		$filename = basename($audio['filename']);
		$file = $base_dir . '/' . $filename;
		$file_size = wpag_get_filesize( $audio['audioURL'] );
	
		$output .= '<div class="wpag_audio_gallery_wrapper">';
		$output .= '<div class="title_image_wrapper">';
		$output .= '<div class="song_detail">';
		if( has_post_thumbnail($gid)){
		 $output .= '<div class="hbgallery_image">'.get_the_post_thumbnail( $gid).'</div>';  
		} 
		$output .= '<div class="right_content"><div class="hbgallery_title"><h3>' . get_the_title($gid) .'</h3></div>';
		$output .= '<div class="hbgallery_artist">' . esc_html( get_post_meta( $gid, 'gallery_author', true ) ) .'</div>';  //for displyaing artist name 
		$output .= '</div></div>';
		$output .= '</div>';   //For showing gallary title 
		$output .= '<script>
                    //<![CDATA[
                    jQuery(document).ready(function(){
                        new jPlayerPlaylist({
                            jPlayer: "#jquery_jplayer_' . $gid .'_'.$category_id. '",
                            cssSelectorAncestor: "#jp_container_' . $gid .'_'.$category_id. '"
                        }, [';
		foreach( $gallery as $audio ) {
    		$filename = basename($audio['filename']);
		    $base_dir = WPAG_UPLOAD_DIR . DIRECTORY_SEPARATOR . $gallery_name;
		    $file = $base_dir . '/' . $filename;
		    $file_size = wpag_get_filesize( $audio['audioURL'] );
	
            $filepart = pathinfo($audio['filename']);
            $download_action_url = WPAG_URLPATH . 'gallery/audio-download.php?file_size=' . $file_size . '&file_path=' . $file;
            $options = get_option( WPAG_OPTIONS );
            $addthis_pubID = $options['addthis_publish_id'];
            $output_button = '<div class=\"sharing_btn\" >';
            if( $options['wpag_audio_download_enable'] == true ) {
                /*$output_button .= '<a href=\"' . $download_action_url . '\" style=\"margin-right:7px;\" target=\"audio-download\">' . '<img src=\"' . WPAG_URLPATH . 'images/audio-down-16.png\" alt=\"audio download\" style=\"border:none;\"/></a>';*/
                $output_button .= '<a href=\"' . admin_url( 'admin-ajax.php' ) . '?action=download_audio&audio='. $audio['aid'] .'&gallery=' . $gid .'\"  style=\"margin-right:7px;\" target=\"audio-download\">' . 
                    '<img src=\"' . WPAG_URLPATH . 'images/audio-down-16.png\" alt=\"Download audio\" style=\"border:none;\" /></a>';
            }
            if( $options['wpag_audio_facebook_sharing'] == true ) {
                $output_button .= '<a style=\"cursor:pointer; margin-right:7px;\"' .
                        ' onclick=\"window.open(\'http://www.facebook.com/sharer.php?s=100&amp;p[title]=' . $audio['title'] . ' | ' . get_the_title() . '&amp;p[url]=' . get_permalink() . '\',\'sharer\',\'toolbar=0,status=0,width=548,height=325\');\"' .
                        ' href=\"javascript: void(0)\">' .
                        '<img src=\"' . WPAG_URLPATH . 'images/audio-facebook-16.png\" alt=\"audio facebook share\" style=\"border:none;\"/></a>';
            }
            if( $options['wpag_audio_addthis_sharing'] == true ) {
                if($addthis_pubID != "") {
                    $output_button .= '<a class=\"addthis_button wp_audio_share\" href=\"http://www.addthis.com/bookmark.php?v=300&amp;pubid=' . $addthis_pubID . '\" style=\"margin-right:7px; text-indent:-9999px;\" addthis:url=\"' . get_permalink() . '\" addthis:title=\"' . $audio['title'] . ' | ' . get_the_title() . '\"><img src=\"' . WPAG_URLPATH . 'images/audio-share-16.png\" alt=\"Bookmark and Share\" style=\"border:none;\"/><span style=\"display:none;\">' . $audio['title'] . ' | ' . get_the_title() . '</span></a>';
                    $output_button .= '<script_ type=\"text/javascript\" src=\"//s7.addthis.com/js/300/addthis_widget.js#pubid=' . $addthis_pubID . '\"></script_>';
                }
            }
            $output_button .= '<iframe name=\"audio-download\" style=\"display: none\"></iframe>';
            $output_button .= '</div>';
	        $output .=    '{
                        title:"' . $audio['title'] . '",
                        mp3:"' . $audio['audioURL'] . '",
                        button:"' . $output_button . '"
                    },';
		}
        $output .=      '], {';
        $output .=             'swfPath: "http://www.jplayer.org/latest/js/Jplayer.swf",
                                supplied: "mp3",
                                wmode: "window",
                                smoothPlayBar: true,
                                keyEnabled: true
                            });
                        });
                    //]]>
                </script>';
        $output .= '<div id="jquery_jplayer_' . $gid .'_'.$category_id. '" class="jp-jplayer"></div>';
        $output .= '<div id="jp_container_' . $gid .'_'.$category_id. '" class="jp-audio hb-playlist">';
        $output .=      '<div class="jp-type-playlist">';
        $output .=          '<div class="jp-gui jp-interface">';
        $output .=              '<ul class="jp-controls">';
        $output .=                  '<li  class="jp-previous"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/rewind.png></a></li>';
        $output .=                  '<li class="jp-play"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/play.png></a></li>';
        $output .=                  '<li class="jp-pause"><a href="javascript:;" tabindex="1"><img src=' . WPAG_URLPATH . 'images/pause.png></a></li>';
        $output .=                  '<li class="jp-next"><a href="javascript:;"  tabindex="1"><img src=' . WPAG_URLPATH . 'images/forward.png></a></li>';
        $output .=                  '<li class="jp-stop"><a href="javascript:;"  tabindex="1"><img src=' . WPAG_URLPATH . 'images/stop.png></a></li>';
		$output .=                  '<li class="jp-mute"><a href="javascript:;" tabindex="1" ><img src=' . WPAG_URLPATH . 'images/unmute.png></a></li>';
        $output .=                  '<li class="jp-unmute"><a href="javascript:;" ><img src=' . WPAG_URLPATH . 'images/mute.png></a></li>';
		$output .=                  '<li class="hb-volume_bar"><div class="jp-volume-bar"><div class="jp-volume-bar-value"></div></div></li>';
        $output .=                  '<li class="jp-volume-max"><a href="javascript:;"><img src=' . WPAG_URLPATH . 'images/maxvol.png></a></li>';
        $output .=              '</ul>';
		$output .=              '<div class="jp-progress">';
        $output .=                  '<div class="jp-seek-bar">';
        $output .=                      '<div class="jp-play-bar"></div>';
        $output .=                  '</div>';
        $output .=              '</div>';
        $output .=              '<div class="jp-time-holder">';
        $output .=                  '<div class="jp-current-time"></div>';
        $output .=                  '<div class="jp-duration"></div>';
        $output .=              '</div>';
        $output .=              '<ul class="jp-toggles">';
        $output .=                  '<li><a href="javascript:;" class="jp-shuffle" tabindex="1" title="shuffle">shuffle</a></li>';
        $output .=                  '<li><a href="javascript:;" class="jp-shuffle-off" tabindex="1" title="shuffle off">shuffle off</a></li>';
        $output .=                  '<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>';
        $output .=                  '<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>';
        $output .=              '</ul>';
        $output .=          '</div>';
        $output .=          '<div class="jp-playlist">';
        $output .=              '<ul>';
        $output .=                  '<li></li>';
        $output .=              '</ul>';
        $output .=          '</div>';
        $output .=          '<div class="jp-no-solution">';
        $output .=              '<span>Update Required</span>
        To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.';
        $output .=          '</div>';
        $output .=      '</div>';
        $output .=  '</div>';
		$output .=  '</div>';
		
	}
    return $output;
}