<?php 

// Create database table after activating plugin
global $jal_db_version;
global $wpdb;
$jal_db_version = '1.0';


$category_data = $wpdb->get_results("SELECT * FROM `wp_terms`");
$active_jobs_media = $wpdb->get_results("SELECT * FROM `wp_testplugin` WHERE mediaDays IS NOT NULL");
$active_jobs_post_status = $wpdb->get_results("SELECT * FROM `wp_testplugin` WHERE postStatusDays IS NOT NULL");
$active_jobs_post_category = $wpdb->get_results("SELECT * FROM `wp_testplugin` WHERE numberPosts IS NOT NULL");

function jal_install() {
    global $wpdb;
    global $jal_db_version;

    $table_name = $wpdb->prefix . 'testplugin';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        mediaDays int(11) NULL DEFAULT NULL,
        formatDate varchar(10),
        deleteFrom varchar(10),
        attachmentOption VARCHAR(20),
        postStatusDays int(11) NULL DEFAULT NULL, 
        formatDatePostStatus VARCHAR(10), 
        newPostStatus VARCHAR(20), 
        numberPosts int(11) NULL DEFAULT NULL,
        formatDatePostCategory VARCHAR(10),
        fromCategory int(11) NULL DEFAULT NULL,
        toCategory int(11) NULL DEFAULT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
        modifiedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'jal_db_version', $jal_db_version );
}

// Scheduling cron
if ( ! wp_next_scheduled( 'delete_media_hook' ) ) {
    wp_schedule_event( time(), 'daily', 'delete_media_hook' );  
}
add_action( 'delete_media_hook', 'delete_media_cron');

// Scheduling cron
if ( ! wp_next_scheduled( 'change_post_status_hook' ) ) {
    wp_schedule_event( time(), 'daily', 'change_post_status_hook' );  
}
add_action( 'change_post_status_hook', 'change_post_status_cron');

// Scheduling cron
if ( ! wp_next_scheduled( 'change_category_hook' ) ) {
    wp_schedule_event( time(), 'daily', 'change_category_hook' );
}
add_action( 'change_category_hook', 'change_category_cron');

// Cron function to delete media from database & server
function delete_media_cron(){
    global $wpdb;

    $plugin_data=$wpdb->get_results("SELECT * FROM  `wp_testplugin` ");
    foreach( $plugin_data as $choosen_format ){
        $choosen_format->formatDate;
        $choosen_format->mediaDays;
        $choosen_format->deleteFrom;
        $choosen_format->attachmentOption;

        if($choosen_format->attachmentOption == "image"){
            // Select image from database
            $attachment_type = "(post_mime_type = 'image/png' OR post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif')";
        }
        else if($choosen_format->attachmentOption == "video"){
            // Select video from database
            $attachment_type = "(post_mime_type = 'video/mp4' OR post_mime_type = 'video/mpeg' OR post_mime_type = 'video/quicktime')";
        }
        else if($choosen_format->attachmentOption == "audio"){
            // Select audio from database
            $attachment_type =  "(post_mime_type = 'audio/mpeg' OR post_mime_type = 'audio/wav')";
        }
        else if($choosen_format->attachmentOption == "text"){
            // Select text from database
            $attachment_type = "(post_mime_type = 'text/csv' OR post_mime_type = 'text/plain')";
        }
        else if($choosen_format->attachmentOption == "pdf"){
            // Select pdf from database
            $attachment_type = "(post_mime_type = 'application/pdf')";
        }
        else if($choosen_format->attachmentOption == "allAttachment"){
            // Select media files from database
            $attachment_type = "(post_mime_type = 'image/png' OR post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif' OR post_mime_type = 'video/mp4' OR post_mime_type = 'video/mpeg' OR post_mime_type = 'video/quicktime' OR post_mime_type = 'audio/mpeg' OR post_mime_type = 'audio/wav' OR post_mime_type = 'text/csv' OR post_mime_type = 'text/plain' OR post_mime_type = 'application/pdf')";
        }
    
        $select_posts = $wpdb1->get_results("SELECT * FROM $wpdb1->posts WHERE $attachment_type AND TIMESTAMPDIFF($choosen_format->formatDate,post_date,CURDATE())>=$choosen_format->mediaDays");
    
        if ($choosen_format->deleteFrom == "server"){
            // Delete attachment from database & server
            foreach ( $select_posts as $page )
            {
                wp_delete_attachment( $page->ID, true );
            }
        }
        else{
            // Delete attachment only from database
            foreach ( $select_posts as $page )
            {
                $wpdb->delete($wpdb->posts, array('id' => $page->ID));
            }
        }
    }
}
// Cron function to change post status from publish to
function change_post_status_cron(){
    global $wpdb;
    $plugin_data=$wpdb->get_results("SELECT * FROM  `wp_testplugin` ");
    foreach( $plugin_data as $choosen_format ){
        $choosen_format->postStatusDays;
        $choosen_format->formatDatePostStatus;
        $choosen_format->newPostStatus;

        // Update post_status
        if($choosen_format->postStatusDays != NULL){
            /* $changeStatus = $wpdb1->update('wp_posts', array('post_status' => '$choosen_format->newPostStatus'), 
            WHERE ('post_status' => 'publish') AND TIMESTAMPDIFF($choosen_format->formatDatePostStatus,post_date,CURDATE())>=$choosen_format->postStatusDays; */
            $change_post_status_sql = "UPDATE $wpdb->posts
                            SET post_status = '$choosen_format->newPostStatus'
                            WHERE (`post_status` = 'publish')
                            AND TIMESTAMPDIFF($choosen_format->formatDatePostStatus,post_date,CURDATE())>=$choosen_format->postStatusDays"; 
            $change_post_status_result = $wpdb->get_results ( $change_post_status_sql );   
        }
    }
}
// Cron function to change post category
function change_category_cron(){
    global $wpdb;
    $plugin_data=$wpdb->get_results("SELECT * FROM  `wp_testplugin` ");
 
    foreach($plugin_data  as $database_row) {
        if($database_row->numberPosts != NULL){
        $posts_date=$wpdb->get_results("SELECT * FROM `wp_posts` WHERE TIMESTAMPDIFF($database_row->formatDatePostCategory,post_date,CURDATE())>=$database_row->numberPosts");
            foreach($posts_date as $date){
                $changeCategory= $wpdb->get_results("UPDATE `wp_term_relationships` INNER JOIN `wp_posts` ON `wp_term_relationships`.object_id=`wp_posts`.ID SET `term_taxonomy_id`=$database_row->toCategory WHERE `term_taxonomy_id`=$database_row->fromCategory AND `object_id`=$date->ID");
            }
        }
    }
}

function delete_media($wpdb1){
        
        if(isset($_POST['dailyMedia'])){
        $insert_result= $wpdb1->get_results ( "INSERT INTO `wp_testplugin`(`mediaDays` , `formatDate`, `deleteFrom`, `attachmentOption` ) VALUES ('$_POST[days]', '$_POST[chooseFormat]', '$_POST[deleteFrom]', '$_POST[chooseAttachment]')" );
    
        $plugin_data=$wpdb1->get_results("SELECT * FROM  `wp_testplugin` ");
        foreach( $plugin_data as $choosen_format ){
            $choosen_format->mediaDays;
            $choosen_format->formatDate;
            $choosen_format->deleteFrom;
            $choosen_format->attachmentOption;

            if($choosen_format->attachmentOption == "image"){
                // Select image from database
                $attachment_type = "(post_mime_type = 'image/png' OR post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif')";
            }
            else if($choosen_format->attachmentOption == "video"){
                // Select video from database
                $attachment_type = "(post_mime_type = 'video/mp4' OR post_mime_type = 'video/mpeg' OR post_mime_type = 'video/quicktime')";
            }
            else if($choosen_format->attachmentOption == "audio"){
                // Select audio from database
                $attachment_type =  "(post_mime_type = 'audio/mpeg' OR post_mime_type = 'audio/wav')";
            }
            else if($choosen_format->attachmentOption == "text"){
                // Select text from database
                $attachment_type = "(post_mime_type = 'text/csv' OR post_mime_type = 'text/plain')";
            }
            else if($choosen_format->attachmentOption == "pdf"){
                // Select pdf from database
                $attachment_type = "(post_mime_type = 'application/pdf')";
            }
            else if($choosen_format->attachmentOption == "allAttachment"){
                // Select media files from database
                $attachment_type = "(post_mime_type = 'image/png' OR post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif' OR post_mime_type = 'video/mp4' OR post_mime_type = 'video/mpeg' OR post_mime_type = 'video/quicktime' OR post_mime_type = 'audio/mpeg' OR post_mime_type = 'audio/wav' OR post_mime_type = 'text/csv' OR post_mime_type = 'text/plain' OR post_mime_type = 'application/pdf')";
            }
        
            $select_posts = $wpdb1->get_results("SELECT * FROM $wpdb1->posts WHERE $attachment_type AND TIMESTAMPDIFF($choosen_format->formatDate,post_date,CURDATE())>=$choosen_format->mediaDays");

            if ($choosen_format->deleteFrom == "server"){
                // Delete attachment from database & server
                foreach ( $select_posts as $page )
                {
                    wp_delete_attachment( $page->ID, true );
                }
            }
            else{
                // Delete attachment only from database
                foreach ( $select_posts as $page )
                {
                    $wpdb1->delete($wpdb1->posts, array('id' => $page->ID));
                }
            }
        }
    }
    else{
        $input_media_days = $_POST['days'];
        $format_date_media = $_POST['chooseFormat'];
        $delete_from_once = $_POST['deleteFrom'];
        $attachment_option = $_POST['chooseAttachment'];
    
        if($attachment_option == "image"){
            // Select image from database
            $attachment_type = "(post_mime_type = 'image/png' OR post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif')";
        }
        else if($attachment_option == "video"){
            // Select video from database
            $attachment_type = "(post_mime_type = 'video/mp4' OR post_mime_type = 'video/mpeg' OR post_mime_type = 'video/quicktime')";
        }
        else if($attachment_option == "audio"){
            // Select audio from database
            $attachment_type = "(post_mime_type = 'audio/mpeg' OR post_mime_type = 'audio/wav')";
        }
        else if($attachment_option == "text"){
            // Select text from database
            $attachment_type = "(post_mime_type = 'text/csv' OR post_mime_type = 'text/plain')";
        }
        else if($attachment_option == "pdf"){
            // Select pdf from database
            $attachment_type = "(post_mime_type = 'application/pdf')";
        }
        else if($attachment_option == "allAttachment"){
            // Select all attachment from database
            $attachment_type = "(post_mime_type = 'image/png' OR post_mime_type = 'image/jpeg' OR post_mime_type = 'image/gif' OR post_mime_type = 'video/mp4' OR post_mime_type = 'video/mpeg' OR post_mime_type = 'video/quicktime' OR post_mime_type = 'audio/mpeg' OR post_mime_type = 'audio/wav' OR post_mime_type = 'text/csv' OR post_mime_type = 'text/plain' OR post_mime_type = 'application/pdf')";
        }
       
        $select_posts = $wpdb1->get_results("SELECT * FROM  $wpdb1->posts WHERE $attachment_type AND TIMESTAMPDIFF($format_date_media,post_date,CURDATE())>=$input_media_days");
    
        if($delete_from_once == "server"){
            // Delete attachment from database & server
            foreach ( $select_posts as $page )
            {
                wp_delete_attachment( $page->ID, true );
            }
        }
        else{
            // Delete attachment only from database
            foreach ( $select_posts as $page )
            {
                $wpdb1->delete($wpdb1->posts, array('id' => $page->ID));
            }
        }
        echo 'Deleted successfully';
        
    }
    
}


function change_post_status($wpdb1){

    if(isset($_POST['dailyPostStatus'])){
        $insert_post_status= $wpdb1->get_results("INSERT INTO `wp_testplugin`(`postStatusDays` , `formatDatePostStatus`, `newPostStatus`) 
        VALUES ('$_POST[inputPostDays]', '$_POST[choosePostDateFormat]', '$_POST[postStatus]')");

        $plugin_data=$wpdb1->get_results("SELECT * FROM  `wp_testplugin` ");
        foreach( $plugin_data as $choosen_format ){
            $choosen_format->postStatusDays;
            $choosen_format->formatDatePostStatus;
            $choosen_format->newPostStatus;

            // Update post_status
            if($choosen_format->postStatusDays != NULL){
                $change_post_status_sql = "UPDATE $wpdb1->posts
                                SET post_status = '$choosen_format->newPostStatus'
                                WHERE (`post_status` = 'publish')
                                AND TIMESTAMPDIFF($choosen_format->formatDatePostStatus,post_date,CURDATE())>=$choosen_format->postStatusDays"; 
                $change_post_status_result = $wpdb1->get_results ( $change_post_status_sql );
            }
        }
        echo 'Status updated successfully';
    }
    else{
        $input_post_days = $_POST['inputPostDays'];
        $format_date_post_status = $_POST['choosePostDateFormat'];
        $new_post_status = $_POST['postStatus'];

        //Update post_status
        $change_status_once_sql = "UPDATE $wpdb1->posts
            SET post_status = '$new_post_status'
            WHERE (`post_status` = 'publish')
            AND TIMESTAMPDIFF($format_date_post_status,post_date,CURDATE())>=$input_post_days"; 
        $change_status_once_result = $wpdb1->get_results ( $change_status_once_sql );
        echo 'Status updated successfully';
    }
}

function change_category($wpdb1){
    $post_category_relationship = $wpdb1->get_results("SELECT * FROM `wp_term_relationships`");
    if(isset($_POST['dailyCategory'])){

        $insertTable="INSERT INTO `wp_testplugin`(`fromCategory` , `toCategory`, `numberPosts`, `formatDatePostCategory` )
        VALUES ($_POST[changeFrom], $_POST[changeTo], $_POST[inputDays], '$_POST[chooseDateFormat]')";
        $insertResult= $wpdb1->get_results ( $insertTable );
        $plugin_data=$wpdb1->get_results("SELECT * FROM  `wp_testplugin` ");

        foreach($plugin_data as $database_row) {

            if($database_row->numberPosts != NULL){
                $posts_date=$wpdb1->get_results("SELECT * FROM `wp_posts` WHERE TIMESTAMPDIFF($database_row->formatDatePostCategory,post_date,CURDATE())>=$database_row->numberPosts");
                
                foreach($posts_date as $date){

                        $changeCategory= $wpdb1->get_results("UPDATE `wp_term_relationships` INNER JOIN `wp_posts` ON `wp_term_relationships`.object_id=`wp_posts`.ID SET `term_taxonomy_id`=$database_row->toCategory WHERE `term_taxonomy_id`=$database_row->fromCategory AND `object_id`=$date->ID");
                    
                    }
                }

            }
        echo "Category changed successfully";
    }
    else{
        $category_from=$_POST['changeFrom'];
        $category_to=$_POST['changeTo'];
        $input_post_category_days=$_POST['inputDays'];
        $format_date_post_category=$_POST['chooseDateFormat'];
        
        foreach($post_category_relationship  as $post) {
    
            $update_category=   "UPDATE `wp_term_relationships` 
                    INNER JOIN `wp_posts` ON `wp_term_relationships`.object_id=`wp_posts`.ID 
                    SET `term_taxonomy_id` = $category_to WHERE `term_taxonomy_id` = $category_from 
                    AND TIMESTAMPDIFF($format_date_post_category,post_date,CURDATE())>=$input_post_category_days";
            $wpdb1->get_results($update_category);
        }
        echo "Category changed successfully";
    }
}
