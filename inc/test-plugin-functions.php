<?php

/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/

global $wpdb;
// Include custom css
add_action('admin_init', 'myplugin_admin_init');

function myplugin_admin_init() {
    wp_register_style('mypluginadminstylesheet', '/wp-content/plugins/test-plugin/assets/css/style.css');

    add_action('admin_print_styles', 'myplugin_admin_style');

    function myplugin_admin_style() {
        wp_enqueue_style('mypluginadminstylesheet');
        wp_enqueue_style("test-plugin-bootstrap", "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css");
        wp_enqueue_script('bootstrap-js', "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js");
        wp_enqueue_script('bootstrap-jq', "https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js");
    }

}


