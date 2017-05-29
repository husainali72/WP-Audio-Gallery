<?php
global $wpdb;
define( 'AUDIO_DB', $wpdb->prefix . 'wpag_audios' );


function wpag_db_create_table() {
    global $wpdb;
    $sql =
        "CREATE TABLE IF NOT EXISTS " . AUDIO_DB . " (
            aid BIGINT(20) NOT NULL AUTO_INCREMENT ,
            gid BIGINT(20) DEFAULT '0' NOT NULL ,
            filename VARCHAR(255) NOT NULL ,
            audioURL VARCHAR(255) NOT NULL ,
            title VARCHAR(255) NOT NULL ,
			orders BIGINT(20) NOT NULL ,					
            PRIMARY KEY  (aid)
            ) ;" ;

    $wpdb->query( $sql );
}


function wpag_db_delete_table() {
    global $wpdb;
    $sql = "DROP TABLE IF EXISTS " . AUDIO_DB;
    $wpdb->query( $sql );
}

function wpag_db_getCorrectSql($sql) {
    $find_str = "'" . AUDIO_DB . "'";
    $retsql = str_replace($find_str, AUDIO_DB, $sql);
    return $retsql;


}
function wpag_db_get_AudioGallery($gallery_id) {

    global $wpdb;

    $gallery = array();
    $sql = $wpdb->prepare( "SELECT * FROM %s WHERE gid=%d ORDER BY orders, title ", AUDIO_DB, $gallery_id );  // For order and title sorting
    $gallery = $wpdb->get_results( wpag_db_getCorrectSql($sql), ARRAY_A );

    return $gallery;
}


function wpag_db_updateGallery_title($gallery_id, $old_title, $new_title) {

    global $wpdb;

    $gallery = array();

    $sqlExpression = "UPDATE " . AUDIO_DB . "
SET audioURL = REPLACE(audioURL, '" . $old_title ."', '".  $new_title ."')
WHERE gid = " . $gallery_id;

    $gallery = $wpdb->query( wpag_db_getCorrectSql($sqlExpression), ARRAY_A );

    return $gallery;
}



function wpag_db_insert_audio( $gallery_id, $title, $filename, $audioURL,$orders ) {
    global $wpdb;
    $sql = $wpdb->prepare( "INSERT INTO %s (gid, filename, audioURL, title, orders) VALUES (%d, %s, %s, %s,%d)",
        AUDIO_DB, $gallery_id, $filename, $audioURL, $title, $orders );

    $wpdb->query( wpag_db_getCorrectSql($sql) );

    return true;
}


function wpag_db_get_audio( $audio_id ) {
    global $wpdb;
    $audio = array();
    $sql = $wpdb->prepare( "SELECT * FROM %s WHERE aid=%d ORDER BY aid", AUDIO_DB, $audio_id );
    $audio = $wpdb->get_row( wpag_db_getCorrectSql($sql), ARRAY_A );
    return $audio;
}


function wpag_db_delete_audio( $audio_id ) {
    global $wpdb;
    $sql = $wpdb->prepare( "DELETE FROM %s WHERE aid=%d", AUDIO_DB, $audio_id );
    $wpdb->query( wpag_db_getCorrectSql($sql) );
}


function wpag_db_update_audio( $audio_id, $audio_title ) {
    global $wpdb;
    $sql = $wpdb->prepare( "UPDATE %s SET title=%s WHERE aid=%d", AUDIO_DB, $audio_title, $audio_id );
    $wpdb->query( wpag_db_getCorrectSql($sql) );
}
function wpag_db_update_order( $audio_id, $audio_order ) {
    global $wpdb;
    $sql = $wpdb->prepare( "UPDATE %s SET orders=%d WHERE aid=%d", AUDIO_DB, $audio_order, $audio_id );
    $wpdb->query( wpag_db_getCorrectSql($sql) );
}


function wpag_db_delete_audiogallery_post() {
    $gallery_posts = get_posts( array( 'post_type' => 'wpag_audio_gallery', 'numberposts' => 300));
    foreach( $gallery_posts as $gpost ) {
        wp_delete_post( $gpost->ID, true);
    }
	
function wpag_delete_custom_terms($taxonomy){
    global $wpdb;

    $query = 'SELECT t.name, t.term_id
            FROM ' . $wpdb->terms . ' AS t
            INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
            ON t.term_id = tt.term_id
            WHERE tt.taxonomy = "' . $taxonomy . '"';

    $terms = $wpdb->get_results($query);

    foreach ($terms as $term) {
        wp_delete_term( $term->term_id, $taxonomy );
    }
}

// Delete all custom terms for this taxonomy
	
}
function wpag_add_order()
{
		global $wpdb;
		$col_name = 'orders';
		//$col = mysql_query("SELECT ".$col_name." FROM ".AUDIO_DB);
        $sql = "SELECT ".$col_name." FROM ".AUDIO_DB;
        $col = $wpdb->query( $sql );
		if ( !is_int($col) )
		{
			$sql ="ALTER TABLE " . AUDIO_DB ." ADD ".$col_name." BIGINT NOT NULL";
			$out = $wpdb->query( $sql );
		}
}