<?php
require_once 'gallery-function.php';

function wp_get_gallery_list_content( $audioList, $audio_columns, $hidden_columns ) {

    $return_str = '';
    $counter	= 0;

    if( is_array($audioList) && !empty($audioList) ) {
        foreach($audioList as $audio) {
            $counter++;
            $aid       = (int) $audio['aid'];

            $return_str .= '<tr id="audio-' . $aid . '" class="iedit"  valign="top">';
                foreach($audio_columns as $audio_column_key) {
                    $audio_column_key = strtolower($audio_column_key);
                    $class = "class='$audio_column_key column-$audio_column_key'";

                    $style = '';
                    if ( in_array($audio_column_key, $hidden_columns) )
                        $style = ' style="display:none;"';

                    $attributes = $class . $style;

                    switch ($audio_column_key) {
                        case 'cb' :
                            $attributes = 'class="column-cb check-column"' . $style;
                            $return_str .= '<th ' . $attributes . ' scope="row"><input name="doaction[]" type="checkbox" value="' . $aid . '" /></th>';
                            break;
                        case 'id' :
                            $return_str .= '<td ' . $attributes . ' style="">' . $aid;
                            $return_str .= '<input type="hidden" name="aid[]" value="' . $aid . '" />';
                            $return_str .= '</td>';
                            break;
                        case 'filename' :
                            $attributes = 'class="title column-filename column-title"' . $style;
                            $return_str .= '<td ' . $attributes . '>';
                            $return_str .= '<strong><a href="' . esc_url( $audio['audioURL'] ) . '" class="thickbox" title="' . esc_attr ($audio['filename']) . '">';
                            $return_str .= esc_attr($audio['filename']);
                            $return_str .= '</a></strong>';
                            $return_str .= '</td>';
                            break;
                        case 'title' :
                            $return_str .= '<td ' . $attributes . '>';
                            $return_str .= '<input name="title[' . $aid . ']" type="text" style="width:45%; margin-bottom: 2px;" value="' . stripslashes($audio['title']) . '" />';
                            $return_str .= '</td>';
                            break;
						case 'orders' :
							if($audio['orders']== 0) {
                            $return_str .= '<td ' . $attributes . '>';
                            $return_str .= '<input name="orders[' . $aid . ']" type="text" style="width:55%; margin-bottom: 2px;" value="" />';
                            $return_str .= '</td>';
							}else{
								$return_str .= '<td ' . $attributes . '>';
                            $return_str .= '<input name="orders[' . $aid . ']" type="text" style="width:55%; margin-bottom: 2px;" value="' . stripslashes($audio['orders']) . '" />';
                            $return_str .= '</td>';
                            	
							}
                            break;  //  On upload order blank 	
	                   default :
                            $return_str .= '<td ' . $attributes  . '>' . do_action('ngg_manage_image_custom_column', $audio_column_key, $aid) . '</td>';
                            break;
                    }
                }
            $return_str .= '</tr>';
        }
    }

    return $return_str;
}