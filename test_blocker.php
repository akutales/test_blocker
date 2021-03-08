<?php
/**
 * Plugin Name: test_blocker
 * Description: Hide warnings of the not writable WP Rocket files advanced-cache.php and .htaccess
 * Plugin URI:  https://github.com/akutales/test_blocker
 * Author: Frank Romero
 */


// Find hook, might just be admin_notices
// Confirmed that admin_notices is hook

// Display warning is rocket_warning_htaccess_permissions() - notices.php

add_action( 'init', 'hide_htaccess_warning' );

function hide_htaccess_warning() {
    remove_action( 'admin_notices', 'rocket_warning_htaccess_permissions' );
}

// Callback function is registered to hook in add_subscriber_callback() - class-event-manager.php
// Displays notice with notice_advanced_cache_permissions() - AdminSubscriber.php
// Display warning is notice_permissions() - AdvancedCache.php

add_action( 'init', 'hide_advanced_cache_warning' );

function hide_advanced_cache_warning() {
    // Don't think remove_action() works without access to class for actions from class objects
    remove_action( 'admin_notices', 'notice_permissions' );
    remove_action( 'admin_notices', 'notice_advanced_cache_permissions' );

    // Make sure advanced-cache warning will not display
    remove_filters_with_method_name( 'admin_notices', 'notice_advanced_cache_permissions', 10 );
    remove_filters_with_method_name( 'admin_notices', 'notice_permissions', 10 );
}

// Necessary since can't access plugin class object for remove_action() to work
function remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
    global $wp_filter;
    // Take only filters on right hook name and priority
    if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
        return false;
    }
    // Loop on filters registered
    foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
        // Test if filter is an array ! (always for class/method)
        if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
            // Test if object is a class and method is equal to param !
            if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
                // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
                    unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                } else {
                    unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                }
            }
        }
    }
    return false;
}