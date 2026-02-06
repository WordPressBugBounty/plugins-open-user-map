<?php

/**
 * @package OpenUserMapPlugin
 */
namespace OpenUserMapPlugin\Pages;

use OpenUserMapPlugin\Base\BaseController;
class Frontend extends BaseController {
    public function register() {
        // WordPress 6.9 compatibility fix (OPT-IN ONLY)
        // Check at template_redirect to ensure functions.php has loaded
        add_action( 'template_redirect', array($this, 'maybe_enable_wp69_fix'), 1 );
        // Shortcodes
        add_action( 'init', array($this, 'set_shortcodes') );
        // Footer containers (add-location form & fullscreen popup)
        // Rendered globally to work with page builder caching
        add_action( 'wp_footer', array($this, 'render_footer_containers') );
        // Enqueue AJAX script globally to work with page builder caching
        add_action( 'wp_footer', array($this, 'ensure_ajax_script_localized'), 5 );
        // Enqueue vote script globally to work with page builder caching (PRO)
        if ( oum_fs()->is__premium_only() && oum_fs()->can_use_premium_code() ) {
            if ( get_option( 'oum_enable_vote_feature' ) === 'on' ) {
                add_action( 'wp_footer', array($this, 'ensure_vote_script_localized'), 5 );
            }
        }
    }

    /**
     * Ensure AJAX script is localized even with page builder caching
     */
    public function ensure_ajax_script_localized() {
        if ( !$this->is_script_ready_for_localization( 'oum_frontend_ajax_js', 'oum_ajax' ) ) {
            return;
        }
        $ajax_data = array(
            'ajaxurl'              => admin_url( 'admin-ajax.php' ),
            'refresh_nonce_action' => 'oum_refresh_location_nonce',
        );
        wp_localize_script( 'oum_frontend_ajax_js', 'oum_ajax', $ajax_data );
        wp_localize_script( 'oum_frontend_ajax_js', 'oum_custom_strings', $this->oum_custom_strings() );
    }

    /**
     * Ensure vote script is localized even with page builder caching
     */
    public function ensure_vote_script_localized() {
        if ( !$this->is_script_ready_for_localization( 'oum_frontend_vote_js', 'oum_vote_nonce' ) ) {
            return;
        }
        $vote_nonce_data = array(
            'nonce' => wp_create_nonce( 'oum_vote_nonce' ),
        );
        $cookie_type_data = array(
            'type' => get_option( 'oum_vote_cookie_type', 'persistent' ),
        );
        wp_localize_script( 'oum_frontend_vote_js', 'oum_vote_nonce', $vote_nonce_data );
        wp_localize_script( 'oum_frontend_vote_js', 'oum_vote_cookie_type', $cookie_type_data );
    }

    /**
     * Check if script is enqueued and not already localized
     * 
     * @param string $script_handle The script handle to check
     * @param string $object_name The localized object name to check for
     * @return bool True if script needs localization, false otherwise
     */
    private function is_script_ready_for_localization( $script_handle, $object_name ) {
        // Check if script is enqueued
        if ( !wp_script_is( $script_handle, 'enqueued' ) && !wp_script_is( $script_handle, 'done' ) ) {
            return false;
        }
        global $wp_scripts;
        // Validate wp_scripts object and script registration
        if ( !$wp_scripts instanceof \WP_Scripts || !isset( $wp_scripts->registered[$script_handle] ) ) {
            return false;
        }
        // Check if already localized
        $script_data = $wp_scripts->registered[$script_handle];
        $is_already_localized = isset( $script_data->extra['data'] ) && strpos( $script_data->extra['data'], $object_name ) !== false;
        return !$is_already_localized;
    }

    /**
     * Render footer containers for map overlays
     * 
     * Outputs add-location form and fullscreen popup used by all maps.
     * Only renders on pages with maps (checks if CSS is enqueued).
     */
    public function render_footer_containers() {
        // Skip admin area
        if ( is_admin() ) {
            return;
        }
        // Only render if OUM CSS was enqueued (indicates map is present)
        if ( !wp_style_is( 'oum_frontend_css', 'enqueued' ) && !wp_style_is( 'oum_frontend_css', 'done' ) ) {
            return;
        }
        require_once oum_get_template( 'partial-map-add-location.php' );
    }

    /**
     * Setup Shortcodes
     * 
     * Note: Page builder detection is handled in the individual rendering methods
     * via the is_page_builder_active() helper function. Shortcodes are always
     * registered, but will return a styled placeholder when rendered inside a
     * page builder interface.
     */
    public function set_shortcodes() {
        // Render Map
        add_shortcode( 'open-user-map', array($this, 'render_block_map') );
        // Shortcode: "Add Location" Form (only)
        add_shortcode( 'open-user-map-form', array($this, 'render_block_form') );
        // Inject Complianz attributes and the tile provider attribute on all OUM scripts
        add_filter(
            'script_loader_tag',
            function ( $tag, $handle, $source ) {
                if ( strpos( $handle, 'oum' ) === false ) {
                    return $tag;
                }
                // Parse existing attributes from the tag
                $existing_attrs = array();
                if ( preg_match_all(
                    '/(\\w+(?:-\\w+)*)=["\']([^"\']*)["\']/',
                    $tag,
                    $matches,
                    PREG_SET_ORDER
                ) ) {
                    foreach ( $matches as $match ) {
                        $existing_attrs[$match[1]] = $match[2];
                    }
                }
                // Extract src if present (fallback to $source parameter)
                $src = ( isset( $existing_attrs['src'] ) ? $existing_attrs['src'] : $source );
                // Build our custom attributes (these will override existing ones if present)
                $custom_attrs = array(
                    'src'           => esc_url( $src ),
                    'data-category' => 'functional',
                    'class'         => 'cmplz-native',
                    'id'            => esc_attr( $handle ) . '-js',
                );
                // Append the tile provider attribute if we stored a value for this handle during enqueue.
                if ( function_exists( 'wp_scripts' ) ) {
                    $scripts = wp_scripts();
                    if ( $scripts instanceof \WP_Scripts ) {
                        $tile_provider = $scripts->get_data( $handle, 'data-oum-tile-provider' );
                        if ( !empty( $tile_provider ) ) {
                            $custom_attrs['data-oum-tile-provider'] = esc_attr( $tile_provider );
                        }
                    }
                }
                // Merge: existing attributes first, then our custom ones (custom overrides)
                $all_attrs = array_merge( $existing_attrs, $custom_attrs );
                // Build attribute string
                $attr_parts = array();
                foreach ( $all_attrs as $key => $value ) {
                    $attr_parts[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
                }
                return sprintf( '<script %s></script>', implode( ' ', $attr_parts ) );
            },
            10,
            3
        );
        // Prevent shortcode parsing by All In One SEO plugin
        add_filter( 'aioseo_disable_shortcode_parsing', '__return_true' );
        // Prevent shortcode parsing by Slim SEO plugin
        add_filter( 'slim_seo_skipped_shortcodes', function ( $shortcodes ) {
            $shortcodes[] = 'open-user-map';
            $shortcodes[] = 'open-user-map-location';
            $shortcodes[] = 'open-user-map-gallery';
            $shortcodes[] = 'open-user-map-list';
            $shortcodes[] = 'open-user-map-form';
            return $shortcodes;
        } );
        // Prevent block parsing by Slim SEO plugin
        add_filter( 'slim_seo_skipped_blocks', function ( $blocks ) {
            $blocks[] = 'open-user-map/map';
            return $blocks;
        } );
    }

    /**
     * Check if WP 6.9 fix should be enabled
     * Runs at template_redirect to ensure functions.php has loaded
     * 
     * @return void
     */
    public function maybe_enable_wp69_fix() {
        // Check both URL parameter and filter (filter is checked late so functions.php has loaded)
        $enable_via_url = isset( $_GET['oum_test_wp69_fix'] ) && $_GET['oum_test_wp69_fix'] == '1';
        $enable_via_filter = apply_filters( 'oum_enable_wp69_buffer_fix', false );
        if ( $enable_via_url || $enable_via_filter ) {
            add_action( 'wp_footer', array($this, 'reduce_ob_level_for_scripts'), 1 );
        }
    }

    /**
     * WordPress 6.9 Compatibility Fix (OPT-IN ONLY)
     * 
     * WordPress 6.9 introduced a "template enhancement output buffer" feature that can cause
     * blank pages on some site configurations when combined with output buffering at level 2+.
     * 
     * @see https://make.wordpress.org/core/2025/11/18/wordpress-6-9-frontend-performance-field-guide/
     * 
     * This fix is OPT-IN and must be explicitly enabled via filter:
     * add_filter('oum_enable_wp69_buffer_fix', '__return_true');
     * 
     * @return void
     */
    public function reduce_ob_level_for_scripts() {
        $initial_level = ob_get_level();
        // Only proceed if output buffering is at level 2 or higher
        if ( $initial_level < 2 ) {
            return;
        }
        // Step 1: Print any late-enqueued OUM styles before flushing buffers
        global $wp_styles;
        if ( $wp_styles instanceof \WP_Styles ) {
            // List of all OUM style handles (must match handles in BaseController.php and Enqueue.php)
            $oum_style_handles = array(
                'oum_frontend_css',
                'oum_style',
                'oum_leaflet_css',
                'oum_leaflet_gesture_css',
                'oum_leaflet_markercluster_css',
                'oum_leaflet_markercluster_default_css',
                'oum_leaflet_geosearch_css',
                'oum_leaflet_fullscreen_css',
                'oum_leaflet_locate_css',
                'oum_leaflet_search_css',
                'oum_leaflet_responsivepopup_css'
            );
            foreach ( $oum_style_handles as $handle ) {
                // Check if style is enqueued but not yet printed
                if ( wp_style_is( $handle, 'enqueued' ) && !wp_style_is( $handle, 'done' ) ) {
                    $wp_styles->do_item( $handle );
                }
            }
        }
        // Step 2: Flush output buffers to reduce level to 1
        $levels_closed = 0;
        while ( ob_get_level() > 1 ) {
            ob_end_flush();
            $levels_closed++;
        }
        // Step 3: Restore buffer structure after scripts have printed
        // This runs at priority 1000 (after wp_print_scripts at priority 20)
        add_action( 'wp_footer', function () use($levels_closed) {
            for ($i = 0; $i < $levels_closed; $i++) {
                ob_start();
            }
        }, 1000 );
    }

}
