<?php

/**
 * @package OpenUserMapPlugin
 */
/*
Plugin Name: Open User Map
Plugin URI: https://wordpress.org/plugins/open-user-map/
Description: Engage your visitors with an interactive map – let them add markers instantly or create a custom map showcasing your favorite spots.
Author: 100plugins
Version: 1.4.12
Author URI: https://www.open-user-map.com/
License: GPLv3 or later
Text Domain: open-user-map
Domain Path: /languages/
*/
/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

Copyright 2025 100plugins
*/
defined( 'ABSPATH' ) or die( 'Direct access is not allowed.' );
if ( function_exists( 'oum_fs' ) ) {
    oum_fs()->set_basename( false, __FILE__ );
} else {
    // FREEMIUS INTEGRATION CODE
    if ( !function_exists( 'oum_fs' ) ) {
        // Create a helper function for easy SDK access.
        function oum_fs() {
            global $oum_fs;
            if ( !isset( $oum_fs ) ) {
                // Enable the new Freemius Garbage Collector (Beta)
                // if ( ! defined( 'WP_FS__ENABLE_GARBAGE_COLLECTOR' ) ) {
                //     define( 'WP_FS__ENABLE_GARBAGE_COLLECTOR', true );
                // }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $oum_fs = fs_dynamic_init( array(
                    'id'             => '9083',
                    'slug'           => 'open-user-map',
                    'premium_slug'   => 'open-user-map-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_e4bbeb52c0d44fa562ba49d2c632d',
                    'is_premium'     => false,
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 7,
                        'is_require_payment' => false,
                    ),
                    'menu'           => array(
                        'slug'       => 'edit.php?post_type=oum-location',
                        'first-path' => 'edit.php?post_type=oum-location&page=open-user-map-settings',
                        'contact'    => false,
                        'support'    => false,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $oum_fs;
        }

        // Init Freemius.
        oum_fs();
        // Signal that SDK was initiated.
        do_action( 'oum_fs_loaded' );
    }
    // Always show annual pricing instead of monthly pricing
    oum_fs()->add_filter( 'pricing/show_annual_in_monthly', '__return_false' );
    // Special uninstall routine with Freemius
    function oum_fs_uninstall_cleanup() {
        global $wpdb;
        //delete posts
        $wpdb->query( "DELETE FROM " . $wpdb->prefix . "posts WHERE post_type='oum-location'" );
        //delete postmeta
        $wpdb->query( "DELETE FROM " . $wpdb->prefix . "postmeta WHERE meta_key LIKE '%oum_%'" );
        //delete options
        $wpdb->query( "DELETE FROM " . $wpdb->prefix . "options WHERE option_name LIKE 'oum_%'" );
    }

    oum_fs()->add_action( 'after_uninstall', 'oum_fs_uninstall_cleanup' );
    // Better Opt-In Screen
    oum_fs()->add_action( 'connect/before', function () {
        echo '<div class="oum-wizard">
            <div class="hero">
                <div class="logo">Open User Map</div>
                <div class="overline">' . __( 'Quick Setup (1/3)', 'open-user-map' ) . '</div>
                <h1>' . __( 'Hi! Thanks for using Open User Map', 'open-user-map' ) . '</h1>
                <ul class="steps">
                    <li class="done"></li>
                    <li></li>
                    <li></li>
                </ul>
            </div>
            <div class="step-content">';
    } );
    oum_fs()->add_action( 'connect/after', function () {
        echo '</div></div>';
    } );
    // ... Your plugin's main file logic ...
    // Require once the composer autoload
    if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once dirname( __FILE__ ) . '/vendor/autoload.php';
    }
    /**
     * The code that runs during plugin activation
     */
    function oum_activate_plugin() {
        OpenUserMapPlugin\Base\Activate::activate();
    }

    register_activation_hook( __FILE__, 'oum_activate_plugin' );
    /**
     * The code that runs during plugin deactivation
     */
    function oum_deactivate_plugin() {
        OpenUserMapPlugin\Base\Deactivate::deactivate();
    }

    register_deactivation_hook( __FILE__, 'oum_deactivate_plugin' );
    /**
     * Initialize all the core classes of the plugin
     */
    if ( class_exists( 'OpenUserMapPlugin\\Init' ) ) {
        // OpenUserMapPlugin\Init::register_services();
        try {
            OpenUserMapPlugin\Init::register_services();
        } catch ( \Error $e ) {
            return 'An error has occurred. Please look in the settings under Open User Map > Help > Debug Info.';
            // Safe logging that works even when error_log is disabled
            if ( function_exists( 'error_log' ) && !in_array( 'error_log', explode( ',', ( ini_get( 'disable_functions' ) ?: '' ) ) ) ) {
                error_log( $e->getMessage() . '(' . $e->getFile() . ' Line: ' . $e->getLine() . ')' );
            }
        }
    }
    /**
     * Get a value from a location (public function)
     * 
     * possible attributes: 
     * - title
     * - images
     * - audio
     * - video
     * - type
     * - map
     * - address
     * - lat
     * - lng
     * - route
     * - text
     * - notification
     * - author_name
     * - author_email
     * - wp_author_id
     * - CUSTOM FIELD LABEL
     */
    function oum_get_location_value(  $attr, $post_id, $raw = false  ) {
        $location_controller = new OpenUserMapPlugin\Base\LocationController();
        return $location_controller->get_location_value( $attr, $post_id, $raw );
    }

    /**
     * Allow to get the template from the theme directory (template override)
     * 
     * Just add a folder "open-user-map" in your theme directory and copy the template file you want to override.
     * Be aware that new features may then not be available or even break the functionality!
     */
    function oum_get_template(  $template_name  ) {
        // Define the paths to the template locations
        $theme_template = get_stylesheet_directory() . '/open-user-map/' . $template_name;
        $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/' . $template_name;
        // Check if the template exists in the theme directory
        if ( file_exists( $theme_template ) ) {
            return $theme_template;
        } else {
            return $plugin_template;
        }
    }

    function oum_load_textdomain() {
        load_plugin_textdomain( 'open-user-map', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    add_action( 'init', 'oum_load_textdomain', 1 );
    // Redirect ?page=open-user-map to ?page=open-user-map-settings (for compatibility with Freemius Trial URL)
    // This is necessary because the Freemius trial URL uses the plugin slug as default page
    add_action( 'admin_menu', function () {
        if ( is_admin() && isset( $_GET['fs_action'] ) && isset( $_GET['page'] ) && $_GET['page'] === 'open-user-map' && strpos( $_SERVER['PHP_SELF'], 'edit.php' ) !== false ) {
            $query_args = $_GET;
            $query_args['page'] = 'open-user-map-settings';
            $new_url = add_query_arg( $query_args, admin_url( 'edit.php' ) );
            wp_redirect( $new_url );
            exit;
        }
    } );
}