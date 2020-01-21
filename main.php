<?php 
/**
 * @package test-plugin
 * Plugin Name: Test
 * Plugin URI:
 * Description: Test plugin
 * Author: Filip
 * Author URI:
 * Version: 0.1 
 * 
 */


include_once 'inc/test-plugin-database-functions.php';
include_once 'inc/test-plugin-functions.php';

register_activation_hook( __FILE__, 'jal_install' );

add_action("admin_menu", "addMenu");
function addMenu(){
    add_menu_page("Test plugin", "Test plugin", "manage_options", "test-options", "testMenu");
}

function testMenu(){
    include 'template-parts/index.php';
}
