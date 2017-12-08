<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_jmayt%' OR option_name LIKE '_transient_timeout_jmayt%'" );

foreach( $plugin_options as $option ) {
    delete_option( $option->option_name );
}
