<?php
function wpag_arrToTable($arr) {
    if(arr){
        $str = "<br/><table border ='1'><tr><th>key</th><th>value</th></tr>";
        foreach ($arr  as $key=>$value){
            $str.="<tr><td>".$key."</td>";
            if(is_array($value)){
                $str .="<td>".wpag_arrToTable($value)."</td>";
            }else{
                $str.="<td>".$value."</td>";
            }
            $str.="</tr>";
        }
        $str.="</table><br/>";
        return $str;
    }else{
        return false;
    }
}

function wpag_console_log( $message ) {
    echo '<script>console.log("' . $message. '")</script>';
}
function get_files_in_dir( $dirname = '.' ) {
    $ext = apply_filters('wp_ag_allowed_file_types', array('mp3') );
    $files = array();
    if( $handle = opendir( $dirname ) ) {
        while( false !== ( $file = readdir( $handle ) ) ) {
            $info = pathinfo( $file );
            // just look for images with the correct extension
            if ( isset($info['extension']) )
                if ( in_array( strtolower($info['extension']), $ext) )
                    $files[] = utf8_encode( $file );
        }
        closedir( $handle );
    }
    sort( $files );
    return ( $files );
}
function fileinfo( $name ) {
    //Sanitizes a filename replacing whitespace with dashes
    $name = sanitize_file_name($name);
    //get the parts of the name
    $filepart = pathinfo ( strtolower($name) );
    if ( empty($filepart) )
        return false;
    // required until PHP 5.2.0
    if ( empty($filepart['filename']) )
        $filepart['filename'] = substr($filepart['basename'],0 ,strlen($filepart['basename']) - (strlen($filepart['extension']) + 1) );
    $filepart['filename'] = sanitize_title_with_dashes( $filepart['filename'] );
    //extension jpeg will not be recognized by the slideshow, so we rename it
    $filepart['extension'] = ($filepart['extension'] == 'jpeg') ? 'jpg' : $filepart['extension'];
    //combine the new file name
    $filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];
    return $filepart;
}
function wpag_get_filesize( $url ) {
    /*if( substr( $url, 0, 4 ) == 'http' ) {
        $x = array_change_key_case( get_headers( $url, 1 ), CASE_LOWER );
        if( strpos( $x[0], '404 Not Found' ) != false ) {
            $path = wpag_convert_urltopath( $url );
            $x = filesize( $path );
        } elseif( strcasecmp( $x[0], 'HTTP/1.1 200 OK' ) != 0 ) {
            $x = $x['content-length'][1];
        } else {
            $x = $x['content-length'];
        }
    } else {
        $path = wpag_convert_urltopath( $url );
        $x = filesize( $path );
    } Husain ALi as on 19-Nov-2013*/
	if( substr( $url, 0, 4 ) == 'http' ) {
        $x = array_change_key_case( get_headers( $url, 1 ), CASE_LOWER );
		if( isset($x) && !empty($x) ) {
			if( strpos( $x[0], '404 Not Found' ) != false ) {
				$path = wpag_convert_urltopath( $url );
				$x = filesize( $path );
				//} elseif( strcasecmp( $x[0], 'HTTP/1.1 200 OK' ) != 0 ) {
			} elseif( strcasecmp( $x[0], 'HTTP/1.1 200 OK' ) == 0 ) {
				$x = $x['content-length'][1];
			} else {
				$x = $x['content-length'];
			}
			if ( $x == 0 ) {
				$path = wpag_convert_urltopath( $url );
				$x = filesize( $path );
				
			}
		}else{
	        $path = wpag_convert_urltopath( $url );
	        $x = filesize( $path );
		}
    } else {
        $path = wpag_convert_urltopath( $url );
        $x = filesize( $path );
    }
    return $x;
}
function wpag_copyfile( $oldfile, $newfile ) {
    if( file_exists( $oldfile ) ) {
        if( !copy( $oldfile, $newfile ) ) {
            return false;
        } else {
            return true;
        }
    }
    return false;
}
function wpag_convert_urltopath( $url_addr ) {
    $path_addr = str_replace( get_site_url() . '/', WPAG_WINABSPATH, $url_addr );
    return $path_addr;
}
function wpag_convert_pathtourl( $url_addr ) {
    $path_addr = str_replace( WPAG_WINABSPATH, get_site_url() . '/', $url_addr );
    return $path_addr;
}
function wpag_remove_dir( $path ) {
    if( is_file($path) ) {
        unlink($path);
    } elseif ( is_dir($path) ) {
        $dir = opendir($path);
        while ($file = readdir($dir)) {
            if( $file!="." && $file!=".." ) {
                wpag_remove_dir($path."/".$file);
            }
        }
        closedir($dir);
        rmdir($path);
    } else {
        return FALSE;
    }
}
function wpag_WriteNewHtaccess($htaccess_new_content){
    $htaccess_orig_path = ABSPATH.'.htaccess';
    @clearstatcache();
    if(file_exists($htaccess_orig_path))
    {
        if(is_writable($htaccess_orig_path))
        {
            @unlink($htaccess_orig_path);
        }else{
            @chmod($htaccess_orig_path, 0666);
            @unlink($htaccess_orig_path);
        }
    }
    $htaccess_new_content = trim($htaccess_new_content);
    $htaccess_new_content = str_replace('\\\\', '\\', $htaccess_new_content);
    $htaccess_new_content = str_replace('\"', '"', $htaccess_new_content);
    $write_success = @file_put_contents($htaccess_orig_path, $htaccess_new_content, LOCK_EX);
    @clearstatcache();
    if(!file_exists($htaccess_orig_path) && $write_success === false)
    {
        unset($htaccess_orig_path);
        unset($htaccess_new_content);
        unset($write_success);
        return false;
    }else{
        unset($htaccess_orig_path);
        unset($htaccess_new_content);
        unset($write_success);
        return true;
    }
}