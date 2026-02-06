<?php

/**
 * @package OpenUserMapPlugin
 */
namespace OpenUserMapPlugin\Base;

class BaseController {
    public $plugin_path;

    public $plugin_url;

    public $plugin_version;

    public $plugin;

    public $post_status;

    public $oum_searchmarkers_zoom_default;

    public $oum_marker_multicategories_icon_default;

    /**
     * Safe logging function that works even when error_log is disabled
     * 
     * @param string $message The message to log
     * @return void
     */
    protected function safe_log( $message ) {
        // Try to use error_log if available
        if ( function_exists( 'error_log' ) && !in_array( 'error_log', explode( ',', ( ini_get( 'disable_functions' ) ?: '' ) ) ) ) {
            error_log( $message );
            return;
        }
        // Fallback: Write directly to debug.log file if WP_DEBUG_LOG is enabled
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && defined( 'WP_CONTENT_DIR' ) ) {
            $log_file = WP_CONTENT_DIR . '/debug.log';
            // Only write if file is writable or can be created
            if ( is_writable( dirname( $log_file ) ) ) {
                $timestamp = current_time( 'Y-m-d H:i:s' );
                $log_message = "[{$timestamp}] {$message}\n";
                // Suppress errors in case of file permission issues
                @file_put_contents( $log_file, $log_message, FILE_APPEND );
            }
        }
    }

    /**
     * Enqueue frontend CSS with optional custom inline CSS
     * 
     * This method enqueues the registered frontend CSS stylesheet and adds any custom CSS
     * from the settings as inline styles.
     * 
     * @return void
     */
    protected function enqueue_frontend_css() {
        // Enqueue the registered frontend CSS file
        wp_enqueue_style( 'oum_frontend_css' );
        // Add custom CSS inline if it exists
        $custom_css = get_option( 'oum_custom_css' );
        if ( !empty( $custom_css ) ) {
            // Sanitize CSS: strip HTML tags and escape for safe output
            $custom_css = wp_strip_all_tags( $custom_css );
            // Check if this inline style was already added to prevent duplicates
            // This can happen if enqueue_frontend_css() is called multiple times (e.g., multiple shortcodes on same page)
            global $wp_styles;
            $already_added = false;
            if ( $wp_styles instanceof \WP_Styles && isset( $wp_styles->registered['oum_frontend_css'] ) ) {
                if ( isset( $wp_styles->registered['oum_frontend_css']->extra['after'] ) && is_array( $wp_styles->registered['oum_frontend_css']->extra['after'] ) ) {
                    // Check if the same CSS is already in the inline styles array
                    foreach ( $wp_styles->registered['oum_frontend_css']->extra['after'] as $existing_css ) {
                        if ( trim( $existing_css ) === trim( $custom_css ) ) {
                            $already_added = true;
                            break;
                        }
                    }
                }
            }
            // Only add inline style if it hasn't been added already
            if ( !$already_added ) {
                // wp_add_inline_style will handle additional escaping
                wp_add_inline_style( 'oum_frontend_css', $custom_css );
            }
        }
    }

    /**
     * Static flag to track if AJAX script localization filter has been added
     * 
     * @var bool
     */
    private static $ajax_localization_filter_added = false;

    /**
     * Static flag to track if vote script localization filter has been added
     * 
     * @var bool
     */
    private static $vote_localization_filter_added = false;

    /**
     * Static flag to track if custom_js localization filter has been added
     * 
     * @var bool
     */
    private static $custom_js_localization_filter_added = false;

    /**
     * Enqueue and localize the AJAX script with required data
     * 
     * This helper method ensures the AJAX script is properly enqueued and localized
     * with all required data. It uses a filter hook as a safety net to ensure localization
     * happens reliably even with deferred scripts and in various contexts (shortcodes, AJAX, iframes).
     * 
     * @return void
     */
    protected function enqueue_and_localize_ajax_script() {
        // Ensure script is enqueued
        wp_enqueue_script( 'oum_frontend_ajax_js' );
        // Get custom strings data
        $custom_strings_data = $this->oum_custom_strings();
        // Localize with custom strings (must happen immediately after enqueue)
        wp_localize_script( 'oum_frontend_ajax_js', 'oum_custom_strings', $custom_strings_data );
        // Localize with AJAX URL and nonce action
        $ajax_data = array(
            'ajaxurl'              => admin_url( 'admin-ajax.php' ),
            'refresh_nonce_action' => 'oum_refresh_location_nonce',
        );
        // Localize the script (primary method)
        wp_localize_script( 'oum_frontend_ajax_js', 'oum_ajax', $ajax_data );
        // Add a filter as a safety net to ensure localization happens even if script is already output
        // This handles edge cases where wp_localize_script might not work (AJAX, iframes, etc.)
        // Only add the filter once to avoid duplicates
        if ( !self::$ajax_localization_filter_added ) {
            // Store reference to this instance for the filter closure
            $controller_instance = $this;
            add_filter(
                'script_loader_tag',
                function ( $tag, $handle, $src ) use($controller_instance) {
                    // Only process our AJAX script
                    if ( $handle !== 'oum_frontend_ajax_js' ) {
                        return $tag;
                    }
                    // For deferred scripts, ensure both oum_ajax and oum_custom_strings are available
                    // wp_localize_script() should handle this, but this is a safety net
                    // We inject them as inline scripts that run immediately (not deferred)
                    // This ensures the variables exist when the deferred script runs
                    // Get fresh data each time to ensure we have the latest values
                    if ( strpos( $tag, 'defer' ) !== false || strpos( $tag, 'async' ) !== false ) {
                        $ajax_data = array(
                            'ajaxurl'              => admin_url( 'admin-ajax.php' ),
                            'refresh_nonce_action' => 'oum_refresh_location_nonce',
                        );
                        $custom_strings_data = $controller_instance->oum_custom_strings();
                        $inline_scripts = sprintf( '<script>window.oum_ajax = window.oum_ajax || %s; window.oum_custom_strings = window.oum_custom_strings || %s;</script>', wp_json_encode( $ajax_data ), wp_json_encode( $custom_strings_data ) );
                        return $inline_scripts . $tag;
                    }
                    return $tag;
                },
                20,
                3
            );
            self::$ajax_localization_filter_added = true;
        }
    }

    /**
     * Enqueue and localize the vote script with required data
     * 
     * This helper method ensures the vote script is properly enqueued and localized
     * with all required data. It uses a filter hook as a safety net to ensure localization
     * happens reliably even with deferred scripts and in various contexts (shortcodes, AJAX, iframes).
     * 
     * @return void
     */
    protected function enqueue_and_localize_vote_script() {
        // Ensure script is enqueued
        wp_enqueue_script( 'oum_frontend_vote_js' );
        // Localize with vote nonce
        $vote_nonce_data = array(
            'nonce' => wp_create_nonce( 'oum_vote_nonce' ),
        );
        wp_localize_script( 'oum_frontend_vote_js', 'oum_vote_nonce', $vote_nonce_data );
        // Localize with cookie type
        $cookie_type_data = array(
            'type' => get_option( 'oum_vote_cookie_type', 'persistent' ),
        );
        wp_localize_script( 'oum_frontend_vote_js', 'oum_vote_cookie_type', $cookie_type_data );
        // Add a filter as a safety net to ensure localization happens even if script is already output
        // This handles edge cases where wp_localize_script might not work (AJAX, iframes, etc.)
        // Only add the filter once to avoid duplicates
        if ( !self::$vote_localization_filter_added ) {
            // Store reference to this instance for the filter closure to get fresh data
            $controller_instance = $this;
            add_filter(
                'script_loader_tag',
                function ( $tag, $handle, $src ) use($controller_instance) {
                    // Only process our vote script
                    if ( $handle !== 'oum_frontend_vote_js' ) {
                        return $tag;
                    }
                    // Get fresh data each time to ensure we have the latest values
                    $vote_nonce_data = array(
                        'nonce' => wp_create_nonce( 'oum_vote_nonce' ),
                    );
                    $cookie_type_data = array(
                        'type' => get_option( 'oum_vote_cookie_type', 'persistent' ),
                    );
                    // Ensure variables are available before the script executes
                    // wp_localize_script() should handle this, but this is a safety net
                    // We inject them as inline scripts that run immediately
                    // This ensures the variables exist when the script runs, especially for late-enqueued scripts
                    $inline_scripts = sprintf( '<script>window.oum_vote_nonce = window.oum_vote_nonce || %s; window.oum_vote_cookie_type = window.oum_vote_cookie_type || %s;</script>', wp_json_encode( $vote_nonce_data ), wp_json_encode( $cookie_type_data ) );
                    return $inline_scripts . $tag;
                },
                20,
                3
            );
            self::$vote_localization_filter_added = true;
        }
    }

    /**
     * Ensure custom_js localization is available even with script optimization
     * 
     * This method adds a filter hook as a safety net to ensure custom_js
     * is available even when scripts are optimized, minified, or deferred.
     * This handles edge cases where wp_localize_script might not work (AJAX, iframes, etc.)
     * 
     * @return void
     */
    protected function ensure_custom_js_localization() {
        // Only add the filter once to avoid duplicates
        if ( !self::$custom_js_localization_filter_added ) {
            add_filter(
                'script_loader_tag',
                function ( $tag, $handle, $src ) {
                    // Only process our frontend block map script
                    if ( $handle !== 'oum_frontend_block_map_js' ) {
                        return $tag;
                    }
                    // Get fresh custom_js data each time to ensure we have the latest values
                    $custom_js_snippet = get_option( 'oum_custom_js' );
                    // Decode HTML entities before JSON encoding
                    // WordPress may HTML-escape content when saving options (depending on sanitization),
                    // or the value was already stored with entities. Decoding before encoding ensures
                    // the raw characters (like > in arrow functions) are preserved in the JSON output.
                    if ( !empty( $custom_js_snippet ) ) {
                        $custom_js_snippet = html_entity_decode( $custom_js_snippet, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                    }
                    $custom_js_data = array(
                        'snippet' => $custom_js_snippet,
                    );
                    // Inject as inline script that runs immediately (not deferred)
                    // This ensures the variable exists when the main script runs
                    // Check if custom_js is not already defined to avoid overwriting
                    $inline_script = sprintf( '<script>window.custom_js = window.custom_js || %s;</script>', wp_json_encode( $custom_js_data ) );
                    return $inline_script . $tag;
                },
                20,
                3
            );
            self::$custom_js_localization_filter_added = true;
        }
    }

    /**
     * Check if the shortcode is being rendered within a page builder interface
     * 
     * Detects various page builders: Elementor, Breakdance, Divi, and Bricks
     * 
     * @return bool True if inside a page builder, false otherwise
     */
    protected function is_page_builder_active() {
        // Check if inside Elementor Backend
        if ( did_action( 'elementor/loaded' ) ) {
            if ( class_exists( '\\Elementor_OUM_Addon\\Plugin' ) && \Elementor_OUM_Addon\Plugin::is_elementor_backend() ) {
                $this->safe_log( 'OUM: detected page builder - Elementor' );
                return true;
            }
        }
        // Check if inside Breakdance Builder iframe
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'breakdance_server_side_render' || isset( $_POST['breakdance_ajax_at_any_url'] ) && $_POST['breakdance_ajax_at_any_url'] === 'true' ) {
            $this->safe_log( 'OUM: detected page builder - Breakdance (AJAX detection)' );
            return true;
        }
        // Check if inside Divi Builder
        if ( isset( $_GET['et_fb'] ) && $_GET['et_fb'] === '1' || isset( $_GET['et_builder'] ) && $_GET['et_builder'] === 'true' || function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
            $this->safe_log( 'OUM: detected page builder - Divi Builder' );
            return true;
        }
        // Check if inside Bricks Builder UI (NOT canvas, NOT preview)
        // Check if Bricks is active/loaded
        $is_bricks_active = defined( 'BRICKS_VERSION' ) || class_exists( 'Bricks\\Builder' ) || function_exists( 'bricks_is_builder' );
        if ( $is_bricks_active ) {
            // Check Bricks helper function first (most reliable if available)
            if ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() ) {
                $this->safe_log( 'OUM: detected page builder - Bricks Builder UI' );
                return true;
            }
            // Check for direct builder access via GET parameter
            if ( isset( $_GET['bricks'] ) && $_GET['bricks'] === 'run' ) {
                $this->safe_log( 'OUM: detected page builder - Bricks Builder UI' );
                return true;
            }
        }
        return false;
    }

    /**
     * Render a styled placeholder for shortcodes when inside a page builder
     * 
     * Returns a styled div matching the Gutenberg block placeholder style,
     * containing the shortcode text and a brief hint that it will render
     * in the frontend only.
     * 
     * @param string $shortcode_name The name of the shortcode (e.g., 'open-user-map')
     * @param array $shortcode_attrs The shortcode attributes array
     * @return string HTML output for the placeholder
     */
    protected function render_page_builder_placeholder( $shortcode_name, $shortcode_attrs = array() ) {
        // Build the shortcode string from name and attributes
        $shortcode_string = '[' . esc_html( $shortcode_name );
        if ( !empty( $shortcode_attrs ) ) {
            foreach ( $shortcode_attrs as $key => $value ) {
                if ( $value !== '' && $value !== null ) {
                    $shortcode_string .= ' ' . esc_html( $key ) . '="' . esc_attr( $value ) . '"';
                }
            }
        }
        $shortcode_string .= ']';
        // Get image URLs for inline styles
        $bg_image_url = esc_url( $this->plugin_url . 'assets/images/block-bg.jpg' );
        $icon_image_url = esc_url( $this->plugin_url . 'assets/images/icon-256x256.png' );
        // Determine if this is a compact shortcode (location shortcode)
        $is_compact = $shortcode_name === 'open-user-map-location';
        // Inline styles to ensure placeholder displays correctly even if CSS file isn't loaded
        $inline_styles = sprintf( '<style>
                .oum-page-builder-placeholder {
                    background: url(%s) top center no-repeat;
                    background-size: cover;
                }
                .oum-page-builder-placeholder .hint {
                    backdrop-filter: blur(2px);
                    position: relative;
                    padding: 50px 40px;
                    text-align: left;
                    color: white;
                    border: 6px solid #fff;
                    box-shadow: 0 0 1px 0px #008fff;
                    display: flex;
                    align-items: flex-start;
                    gap: 20px;
                }
                .oum-page-builder-placeholder[data-shortcode="open-user-map-location"] .hint {
                    padding: 5px;
                }
                .oum-page-builder-placeholder .hint__icon {
                    width: 80px;
                    height: 80px;
                    background: url(%s) center center no-repeat;
                    background-size: cover;
                    border: 5px solid #fff;
                    opacity: 0.8;
                    flex-shrink: 0;
                }
                .oum-page-builder-placeholder[data-shortcode="open-user-map-location"] .hint__icon {
                    width: 40px;
                    height: 40px;
                    border: 2px solid #fff;
                }
                .oum-page-builder-placeholder .hint__content {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                }
                .oum-page-builder-placeholder[data-shortcode="open-user-map-location"] .hint__content {
                    display: none;
                }
                .oum-page-builder-placeholder .hint h5 {
                    font-size: 24px;
                    font-weight: 600;
                    font-family: "Courier New", monospace;
                    margin: 0;
                    padding: 0;
                    color: white;
                    word-break: break-all;
                }
                .oum-page-builder-placeholder .hint p {
                    font-size: 17px;
                    margin: 0;
                    padding: 0;
                }
            </style>', $bg_image_url, $icon_image_url );
        // Return styled placeholder HTML with inline styles
        return $inline_styles . sprintf(
            '<div class="oum-page-builder-placeholder" data-shortcode="%s" title="%s">
                <div class="hint">
                    <div class="hint__icon"></div>
                    <div class="hint__content">
                        <h5>%s</h5>
                        <p>%s</p>
                    </div>
                </div>
            </div>',
            esc_attr( $shortcode_name ),
            esc_attr( $shortcode_string ),
            $shortcode_string,
            esc_html__( 'This shortcode will render in the frontend only.', 'open-user-map' )
        );
    }

    public $map_styles = array(
        "Esri.WorldStreetMap"  => "Esri WorldStreetMap",
        "OpenStreetMap.Mapnik" => "OpenStreetMap",
        "OpenStreetMap.DE"     => "OpenStreetMap (Germany)",
        "CartoDB.DarkMatter"   => "CartoDB DarkMatter",
        "CartoDB.Positron"     => "CartoDB Positron",
        "Esri.WorldImagery"    => "Esri WorldImagery",
    );

    public $custom_map_styles = array(
        "Custom1" => "Light with big labels",
        "Custom2" => "Purple Glow with big labels",
        "Custom3" => "Blue with big labels",
    );

    public $commercial_map_styles = array(
        "MapBox.streets"           => "MapBox Streets",
        "MapBox.outdoors"          => "MapBox Outdoors",
        "MapBox.light"             => "MapBox Light",
        "MapBox.dark"              => "MapBox Dark",
        "MapBox.satellite"         => "MapBox Satellite",
        "MapBox.satellite-streets" => "MapBox Satellite Streets",
    );

    public $pro_map_styles = array(
        "CustomImage" => "Custom Image",
    );

    public $marker_icons = array(
        "default",
        "custom1",
        "custom2",
        "custom3",
        "custom4",
        "custom5",
        "custom6",
        "custom7",
        "custom8",
        "custom9",
        "custom10"
    );

    public $oum_map_sizes = array(
        "default"   => "Content width",
        "fullwidth" => "Full width",
    );

    public $pro_marker_icons = array("user1");

    public $oum_ui_color_default = '#e82c71';

    public $oum_custom_field_fieldtypes = array(
        "text" => "Text",
    );

    public $pro_oum_custom_field_fieldtypes = array(
        "link"          => "Link [PRO]",
        "email"         => "Email [PRO]",
        "checkbox"      => "Checkbox [PRO]",
        "radio"         => "Radio [PRO]",
        "select"        => "Select [PRO]",
        "opening_hours" => "Opening Hours [PRO]",
        "html"          => "HTML [PRO]",
    );

    public $oum_title_required_default = true;

    public $oum_geosearch_provider = array(
        "osm" => "Open Street Map",
    );

    public $pro_oum_geosearch_provider = array(
        "geoapify" => "Geoapify [PRO]",
        "here"     => "Here [PRO]",
        "mapbox"   => "MapBox [PRO]",
    );

    public $oum_searchbar_types = array(
        "address" => "Search for Address (Geosearch)",
        "markers" => "Search for Location Marker",
    );

    public $pro_oum_searchbar_types = array(
        "live_filter" => "Live Filter Markers",
    );

    public $oum_regions_layout_styles = array(
        "layout-1" => "Top",
        "layout-2" => "Sidebar",
    );

    /**
     * Extract the base tile provider from a map style string.
     *
     * @param string $map_style Map style identifier, e.g. "Esri.WorldStreetMap".
     * @return string Lowercase provider slug or empty string.
     */
    public function get_tile_provider_from_style( $map_style ) {
        $map_style = strtolower( trim( (string) $map_style ) );
        if ( $map_style === '' ) {
            return '';
        }
        if ( in_array( $map_style, array('custom1', 'custom2', 'custom3'), true ) ) {
            return 'openstreetmap';
        }
        if ( $map_style === 'customimage' ) {
            return 'esri';
        }
        $provider = $map_style;
        if ( strpos( $map_style, '.' ) !== false ) {
            list( $provider ) = explode( '.', $map_style, 2 );
        }
        if ( strpos( $provider, 'mapbox' ) === 0 ) {
            $provider = 'mapbox';
        }
        return $provider;
    }

    /**
     * Build a data attribute snippet for the detected tile provider.
     *
     * @param string $map_style Map style identifier.
     * @return string Attribute snippet or empty string.
     */
    public function get_tile_provider_data_attribute( $map_style, $type = 'script' ) {
        $provider = $this->get_tile_provider_from_style( $map_style );
        if ( $provider === '' ) {
            return '';
        }
        if ( $type === 'container' ) {
            return ' data-oum-tile-provider-container="' . esc_attr( $provider ) . '"';
        } else {
            return ' data-oum-tile-provider="' . esc_attr( $provider ) . '"';
        }
    }

    /**
     * Persist tile provider metadata on a script handle.
     *
     * @param string $handle    Script handle.
     * @param string $map_style Map style identifier.
     * @return void
     */
    public function assign_tile_provider_to_script( $handle, $map_style ) {
        if ( !function_exists( 'wp_scripts' ) ) {
            return;
        }
        $provider = $this->get_tile_provider_from_style( $map_style );
        if ( $provider === '' ) {
            return;
        }
        $scripts = wp_scripts();
        if ( $scripts instanceof \WP_Scripts ) {
            $scripts->add_data( $handle, 'data-oum-tile-provider', $provider );
        }
    }

    public function oum_custom_strings() {
        return array(
            'delete_location'         => __( 'Delete this location?', 'open-user-map' ),
            'delete_location_message' => __( 'This action cannot be undone. The location will be permanently removed from the map.', 'open-user-map' ),
            'delete_location_button'  => __( 'Yes, delete location', 'open-user-map' ),
            'location_deleted'        => __( 'Location deleted', 'open-user-map' ),
            'delete_success'          => __( 'The location has been successfully removed from the map.', 'open-user-map' ),
            'delete_error'            => __( 'An error occurred while deleting the location. Please try again.', 'open-user-map' ),
            'close_and_refresh'       => ( get_option( 'oum_thankyou_buttontext' ) ?: __( 'Close and refresh map', 'open-user-map' ) ),
            'changes_saved'           => __( 'Changes saved', 'open-user-map' ),
            'changes_saved_message'   => __( 'Your changes have been saved and will be visible after we reviewed them.', 'open-user-map' ),
            'thank_you'               => __( 'Thank you!', 'open-user-map' ),
            'thank_you_message'       => __( 'We will check your location suggestion and release it as soon as possible.', 'open-user-map' ),
            'max_files_exceeded'      => __( 'Maximum %1$d images allowed. Only the first %2$d new images will be used.', 'open-user-map' ),
            'max_filesize_exceeded'   => __( 'The following images exceed the maximum file size of %1$dMB:\\n%2$s', 'open-user-map' ),
            'edit_location'           => __( 'Edit location', 'open-user-map' ),
        );
    }

    public function oum_get_default_label( $key ) {
        $labels = [
            'title'             => __( 'Title', 'open-user-map' ),
            'map'               => __( 'Click on the map to set a marker', 'open-user-map' ),
            'description'       => __( 'Description', 'open-user-map' ),
            'upload_media'      => __( 'Upload media', 'open-user-map' ),
            'address'           => __( 'Subtitle', 'open-user-map' ),
            'marker_types'      => __( 'Type', 'open-user-map' ),
            'searchaddress'     => __( 'Search for address', 'open-user-map' ),
            'searchmarkers'     => __( 'Find marker', 'open-user-map' ),
            'user_notification' => __( 'Notify me when it is published', 'open-user-map' ),
        ];
        return ( isset( $labels[$key] ) ? $labels[$key] : '' );
    }

    public $oum_incompatible_3rd_party_scripts = array();

    public function __construct() {
        $this->plugin_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
        $this->plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
        $this->plugin_version = get_file_data( dirname( dirname( dirname( __FILE__ ) ) ) . '/open-user-map.php', array(
            'Version' => 'Version',
        ) )['Version'];
        $this->plugin = plugin_basename( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/open-user-map.php';
        $this->oum_searchmarkers_zoom_default = 8;
        // Set the default multi-categories icon URL
        $this->oum_marker_multicategories_icon_default = $this->plugin_url . 'src/leaflet/images/marker-icon_multicategories_default.png';
        add_action( 'init', array($this, 'oum_init') );
        add_action(
            'transition_post_status',
            array($this, 'assign_user_on_approval'),
            10,
            3
        );
        // Reset PRO-only settings after Freemius is fully loaded (prevents crashes during early init)
        add_action( 'oum_fs_loaded', array($this, 'on_freemius_loaded') );
    }

    public function oum_init() {
        $this->post_status = 'pending';
        if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
            // Auto-Publish for registered users
            if ( get_option( 'oum_enable_auto_publish', 'on' ) && current_user_can( 'edit_oum-locations' ) ) {
                $this->post_status = 'publish';
            }
            // Default: Allow Frontend Adding for everyone
            add_action( 'wp_ajax_nopriv_oum_add_location_from_frontend', array($this, 'ajax_add_location_from_frontend') );
            add_action( 'wp_ajax_oum_add_location_from_frontend', array($this, 'ajax_add_location_from_frontend') );
        }
        // AJAX: Handle vote/unvote actions (available for all users)
        add_action( 'wp_ajax_oum_toggle_vote', array($this, 'ajax_toggle_vote') );
        add_action( 'wp_ajax_nopriv_oum_toggle_vote', array($this, 'ajax_toggle_vote') );
        // AJAX: Get updated vote count for a location
        add_action( 'wp_ajax_oum_get_vote_count', array($this, 'ajax_get_vote_count') );
        add_action( 'wp_ajax_nopriv_oum_get_vote_count', array($this, 'ajax_get_vote_count') );
        // AJAX: Provide a fresh nonce for cached frontend forms
        add_action( 'wp_ajax_oum_refresh_location_nonce', array($this, 'ajax_refresh_location_nonce') );
        add_action( 'wp_ajax_nopriv_oum_refresh_location_nonce', array($this, 'ajax_refresh_location_nonce') );
    }

    /**
     * Called after Freemius is fully initialized
     * Safely checks and resets PRO-only settings if needed
     */
    public function on_freemius_loaded() {
        // Only run in admin context to avoid unnecessary option writes on frontend/login/cron requests
        if ( is_admin() ) {
            $this->reset_pro_only_settings();
        }
    }

    /**
     * Reset PRO-only settings when user no longer has PRO access
     * 
     * This function checks if the user has PRO access and resets PRO-only settings
     * if they don't. This handles cases like when a PRO trial ends.
     * 
     * @return void
     */
    protected function reset_pro_only_settings() {
        // Defensive check: Ensure Freemius is available
        if ( !function_exists( 'oum_fs' ) ) {
            return;
        }
        $fs = oum_fs();
        // Defensive check: Ensure Freemius returned a valid object
        if ( !$fs || !is_object( $fs ) ) {
            return;
        }
        // Check if user has PRO access
        $has_pro_access = false;
        try {
            if ( $fs->is__premium_only() && $fs->can_use_premium_code() ) {
                $has_pro_access = true;
            }
        } catch ( \Throwable $e ) {
            // Catch any errors from Freemius and log them safely
            $this->safe_log( 'Freemius error in reset_pro_only_settings: ' . $e->getMessage() );
            return;
        }
        // If user doesn't have PRO access, reset PRO-only settings
        if ( !$has_pro_access ) {
            // Reset Advanced Filter Interface setting
            if ( get_option( 'oum_enable_advanced_filter' ) ) {
                delete_option( 'oum_enable_advanced_filter' );
            }
            // Reset Custom Image map style to default if it's currently set
            $current_map_style = get_option( 'oum_map_style' );
            if ( $current_map_style === 'CustomImage' ) {
                update_option( 'oum_map_style', 'Esri.WorldStreetMap' );
            }
        }
    }

    /**
     * Enqueue all necessary base scripts for the map
     * 
     * This method enqueues all registered Leaflet CSS and JS assets.
     * Assets must be registered first via Enqueue::register_assets().
     * 
     * @return void
     */
    public function include_map_scripts() {
        // Unregister incompatible 3rd party scripts
        $this->remove_incompatible_3rd_party_scripts();
        // Enqueue registered Leaflet CSS files
        wp_enqueue_style( 'oum_leaflet_css' );
        wp_enqueue_style( 'oum_leaflet_gesture_css' );
        wp_enqueue_style( 'oum_leaflet_markercluster_css' );
        wp_enqueue_style( 'oum_leaflet_markercluster_default_css' );
        wp_enqueue_style( 'oum_leaflet_geosearch_css' );
        wp_enqueue_style( 'oum_leaflet_fullscreen_css' );
        wp_enqueue_style( 'oum_leaflet_locate_css' );
        wp_enqueue_style( 'oum_leaflet_search_css' );
        wp_enqueue_style( 'oum_leaflet_responsivepopup_css' );
        // Enqueue map loader script first (before any other scripts)
        wp_enqueue_script( 'oum_map_loader_js' );
        // Enqueue registered Leaflet JavaScript files
        wp_enqueue_script( 'oum_leaflet_polyfill_unfetch_js' );
        wp_enqueue_script( 'oum_leaflet_js' );
        wp_enqueue_script( 'oum_leaflet_providers_js' );
        wp_enqueue_script( 'oum_leaflet_markercluster_js' );
        wp_enqueue_script( 'oum_leaflet_subgroups_js' );
        wp_enqueue_script( 'oum_leaflet_geosearch_js' );
        wp_enqueue_script( 'oum_leaflet_locate_js' );
        wp_enqueue_script( 'oum_leaflet_fullscreen_js' );
        wp_enqueue_script( 'oum_leaflet_search_js' );
        wp_enqueue_script( 'oum_leaflet_gesture_js' );
        wp_enqueue_script( 'oum_leaflet_responsivepopup_js' );
        // Capture the fully extended L object after all Leaflet add-ons are loaded
        wp_enqueue_script( 'oum_global_leaflet_js' );
    }

    /**
     * Unregister incompatible 3rd party scripts
     */
    public function remove_incompatible_3rd_party_scripts() {
        foreach ( $this->oum_incompatible_3rd_party_scripts as $item ) {
            wp_deregister_script( $item );
        }
    }

    /**
     * Render the map
     */
    public function render_block_map( $block_attributes, $content ) {
        // Check if inside a page builder - return placeholder if so
        if ( $this->is_page_builder_active() ) {
            return $this->render_page_builder_placeholder( 'open-user-map', $block_attributes );
        }
        // Enqueue frontend CSS with custom inline CSS
        $this->enqueue_frontend_css();
        // Load map base scripts (enqueues registered Leaflet CSS and JS)
        $this->include_map_scripts();
        // Enqueue registered frontend block map script
        wp_enqueue_script( 'oum_frontend_block_map_js' );
        // Localize custom strings
        wp_localize_script( 'oum_frontend_block_map_js', 'oum_custom_strings', $this->oum_custom_strings() );
        // Add custom js to frontend-block-map.js
        // Decode HTML entities before passing to wp_localize_script to ensure
        // raw characters (like > in arrow functions) are preserved
        $custom_js_snippet = get_option( 'oum_custom_js' );
        if ( !empty( $custom_js_snippet ) ) {
            $custom_js_snippet = html_entity_decode( $custom_js_snippet, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        }
        wp_localize_script( 'oum_frontend_block_map_js', 'custom_js', array(
            'snippet' => $custom_js_snippet,
        ) );
        // Add safety net filter for custom_js localization
        $this->ensure_custom_js_localization();
        // Enqueue and localize AJAX script with all required data
        $this->enqueue_and_localize_ajax_script();
        // Enqueue registered carousel script
        wp_enqueue_script( 'oum_frontend_carousel_js' );
        ob_start();
        require oum_get_template( 'block-map.php' );
        return ob_get_clean();
    }

    /**
     * Add location from frontend (AJAX)
     */
    public function ajax_add_location_from_frontend() {
        if ( !empty( $_POST['action'] ) && $_POST['action'] == 'oum_add_location_from_frontend' ) {
            // Initialize error handling
            $error = new \WP_Error();
            // Dont save without nonce
            if ( !isset( $_POST['oum_location_nonce'] ) ) {
                $error->add( '000', 'Security error (no nonce povided)' );
                wp_send_json_error( $error );
                die;
            }
            // Dont save if nonce is incorrect
            $nonce = $_POST['oum_location_nonce'];
            if ( !wp_verify_nonce( $nonce, 'oum_location' ) ) {
                $error->add( '000', 'Security error (incorrect nonce)' );
                wp_send_json_error( $error );
                die;
            }
            $data['oum_location_title'] = ( isset( $_POST['oum_location_title'] ) && $_POST['oum_location_title'] != '' ? sanitize_text_field( wp_strip_all_tags( $_POST['oum_location_title'] ) ) : time() );
            $data['oum_location_lat'] = sanitize_text_field( wp_strip_all_tags( $_POST['oum_location_lat'] ) );
            $data['oum_location_lng'] = sanitize_text_field( wp_strip_all_tags( $_POST['oum_location_lng'] ) );
            $data['oum_location_address'] = ( isset( $_POST['oum_location_address'] ) ? sanitize_text_field( wp_strip_all_tags( $_POST['oum_location_address'] ) ) : '' );
            $data['oum_location_text'] = ( isset( $_POST['oum_location_text'] ) ? wp_kses_post( $_POST['oum_location_text'] ) : '' );
            $data['oum_location_notification'] = ( isset( $_POST['oum_location_notification'] ) ? $_POST['oum_location_notification'] : '' );
            $data['oum_location_author_name'] = ( isset( $_POST['oum_location_notification'] ) ? sanitize_text_field( wp_strip_all_tags( $_POST['oum_location_author_name'] ) ) : '' );
            $data['oum_location_author_email'] = ( isset( $_POST['oum_location_notification'] ) ? sanitize_email( wp_strip_all_tags( $_POST['oum_location_author_email'] ) ) : '' );
            $data['oum_location_video'] = ( isset( $_POST['oum_location_video'] ) ? sanitize_url( wp_strip_all_tags( $_POST['oum_location_video'] ) ) : '' );
            if ( isset( $_POST['oum_marker_icon'] ) ) {
                $data['oum_marker_icon'] = array();
                foreach ( $_POST['oum_marker_icon'] as $index => $val ) {
                    $data['oum_marker_icon'][$index] = (int) sanitize_text_field( wp_strip_all_tags( $val ) );
                }
            } else {
                $data['oum_marker_icon'] = '';
            }
            if ( isset( $_POST['oum_location_custom_fields'] ) && is_array( $_POST['oum_location_custom_fields'] ) ) {
                $available_custom_fields = get_option( 'oum_custom_fields', array() );
                $data['oum_location_custom_fields'] = array();
                foreach ( $_POST['oum_location_custom_fields'] as $index => $val ) {
                    $fieldtype = ( isset( $available_custom_fields[$index]['fieldtype'] ) ? $available_custom_fields[$index]['fieldtype'] : 'text' );
                    // Handle opening hours field type
                    if ( $fieldtype === 'opening_hours' ) {
                        // Handle both array format [hours] and string format
                        $hours_input = '';
                        if ( is_array( $val ) ) {
                            $hours_input = ( isset( $val['hours'] ) ? sanitize_text_field( $val['hours'] ) : '' );
                        } else {
                            // Treat as string input
                            $hours_input = sanitize_text_field( $val );
                        }
                        if ( $hours_input !== '' ) {
                            // Parse the input format to JSON (without timezone - loaded from WordPress when needed)
                            $parsed = \OpenUserMapPlugin\Base\LocationController::convert_opening_hours_input_to_json( $hours_input );
                            if ( $parsed ) {
                                $data['oum_location_custom_fields'][$index] = json_encode( $parsed );
                            } else {
                                // Invalid format, skip
                                $data['oum_location_custom_fields'][$index] = '';
                            }
                        } else {
                            $data['oum_location_custom_fields'][$index] = '';
                        }
                    } elseif ( is_array( $val ) ) {
                        // Multiple values (like checkbox)
                        $arr_vals = array();
                        foreach ( $val as $el ) {
                            $arr_vals[] = sanitize_text_field( $el );
                        }
                        $data['oum_location_custom_fields'][$index] = $arr_vals;
                    } else {
                        // Single value
                        $sanitized_value = sanitize_text_field( $val );
                        if ( $fieldtype === 'link' ) {
                            // Link fields require URL sanitizing to preserve protocols (e.g., https://).
                            $sanitized_value = esc_url_raw( $val );
                        }
                        $data['oum_location_custom_fields'][$index] = $sanitized_value;
                    }
                }
            }
            if ( isset( $_POST['oum_post_id'] ) && $_POST['oum_post_id'] != '' ) {
                $data['oum_post_id'] = intval( $_POST['oum_post_id'] );
                // Does the post exist?
                if ( get_post_status( $data['oum_post_id'] ) === false ) {
                    $error->add( '008', 'The provided Post ID does not exist.' );
                }
                // Is the current user allowed to edit this post?
                $has_general_permission = current_user_can( 'edit_oum-locations' );
                $is_author = get_current_user_id() == get_post_field( 'post_author', $data['oum_post_id'] );
                $can_edit_specific_post = current_user_can( 'edit_post', $data['oum_post_id'] );
                $allow_edit = ( $has_general_permission && ($is_author || $can_edit_specific_post) ? true : false );
                if ( !$allow_edit ) {
                    $error->add( '009', 'You are not allowed to edit this location.' );
                }
                // Should the location be deleted?
                if ( isset( $_POST['oum_delete_location'] ) && $_POST['oum_delete_location'] == 'true' ) {
                    $data['oum_delete_location'] = $_POST['oum_delete_location'];
                }
            }
            if ( !$data['oum_location_title'] ) {
                $error->add( '001', 'Missing or incorrect Title value.' );
            }
            if ( !$data['oum_location_lat'] || !$data['oum_location_lng'] ) {
                $error->add( '002', 'Missing or incorrect location. Click on the map to set a marker.' );
            }
            if ( isset( $_FILES['oum_location_audio']['name'] ) && $_FILES['oum_location_audio']['name'] != '' ) {
                $valid_extensions = array(
                    'mp3',
                    'wav',
                    'mp4',
                    'm4a'
                );
                // valid extensions
                $img = sanitize_file_name( $_FILES['oum_location_audio']['name'] );
                $tmp = sanitize_text_field( $_FILES['oum_location_audio']['tmp_name'] );
                // get uploaded file's extension
                $ext = strtolower( pathinfo( $img, PATHINFO_EXTENSION ) );
                // check internal upload handling
                if ( $tmp == '' ) {
                    $error->add( '003', 'Something went wrong with file upload. Use a valid audio file.' );
                }
                // check valid format
                if ( in_array( $ext, $valid_extensions ) ) {
                    $data['oum_location_audio_src'] = $tmp;
                    $data['oum_location_audio_ext'] = $ext;
                } else {
                    $error->add( '004', 'Invalid audio file extension. Please use .mp3, .wav, .mp4 or .m4a.' );
                }
                // check maximum filesize
                // default 10MB
                $oum_max_audio_filesize = ( get_option( 'oum_max_audio_filesize' ) ? get_option( 'oum_max_audio_filesize' ) : 10 );
                $max_filesize = (int) $oum_max_audio_filesize * 1048576;
                if ( $_FILES['oum_location_audio']['size'] > $max_filesize ) {
                    $error->add( '005', 'The audio file exceeds maximum size of ' . $oum_max_audio_filesize . 'MB.' );
                }
            }
            if ( isset( $data['oum_location_notification'] ) && $data['oum_location_notification'] != '' ) {
                if ( !$data['oum_location_author_name'] ) {
                    $error->add( '006', 'Missing author name.' );
                }
                if ( !$data['oum_location_author_email'] ) {
                    $error->add( '007', 'Missing author email.' );
                }
            }
            if ( $error->has_errors() ) {
                wp_send_json_error( $error );
            } else {
                $new_post = array(
                    'post_title'     => $data['oum_location_title'],
                    'post_type'      => 'oum-location',
                    'post_status'    => $this->post_status,
                    'comment_status' => 'closed',
                );
                // DELETE, UPDATE or INSERT the location
                if ( isset( $data['oum_delete_location'] ) && $data['oum_delete_location'] == 'true' ) {
                    // DELETE (Move to trash)
                    wp_trash_post( $data['oum_post_id'] );
                    wp_send_json_success( array(
                        'message' => 'Ok, the location has been removed.',
                        'post_id' => $data['oum_post_id'],
                    ) );
                } else {
                    // INSERT or UPDATE the location based on 'oum_post_id'
                    $is_update = isset( $data['oum_post_id'] ) && get_post_status( $data['oum_post_id'] ) !== false;
                    $post_id = ( $is_update ? wp_update_post( array_merge( $new_post, [
                        'ID' => $data['oum_post_id'],
                    ] ) ) : wp_insert_post( $new_post ) );
                    if ( $post_id ) {
                        // Handle multiple images
                        $final_image_urls = array();
                        $image_order = ( isset( $_POST['image_order'] ) ? json_decode( stripslashes( $_POST['image_order'] ), true ) : array() );
                        $new_image_mapping = array();
                        // Store mapping of original filename to new URL
                        // First, handle new uploaded images to create the mapping
                        if ( isset( $_FILES['oum_location_images'] ) ) {
                            $valid_extensions = array(
                                'jpeg',
                                'jpg',
                                'png',
                                'webp'
                            );
                            $oum_max_image_filesize = ( get_option( 'oum_max_image_filesize' ) ? get_option( 'oum_max_image_filesize' ) : 10 );
                            $max_filesize = (int) $oum_max_image_filesize * 1048576;
                            if ( is_array( $_FILES['oum_location_images']['name'] ) ) {
                                foreach ( $_FILES['oum_location_images']['name'] as $key => $name ) {
                                    if ( empty( $name ) ) {
                                        continue;
                                    }
                                    $tmp = $_FILES['oum_location_images']['tmp_name'][$key];
                                    $ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
                                    $size = $_FILES['oum_location_images']['size'][$key];
                                    // Validate file
                                    if ( $tmp == '' || !is_uploaded_file( $tmp ) ) {
                                        $this->safe_log( "File {$key}: Invalid upload" );
                                        continue;
                                    }
                                    if ( !in_array( $ext, $valid_extensions ) ) {
                                        $this->safe_log( "File {$key}: Invalid extension" );
                                        continue;
                                    }
                                    if ( $size > $max_filesize ) {
                                        $this->safe_log( "File {$key}: File too large" );
                                        $error->add( '005', sprintf( __( 'Image "%s" is too large. Maximum file size is %d MB.', 'open-user-map' ), $name, $oum_max_image_filesize ) );
                                        continue;
                                    }
                                    // Process the upload
                                    $uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'oum-useruploads/';
                                    wp_mkdir_p( $uploads_dir );
                                    $unique_filename = uniqid() . '.' . $ext;
                                    $file_fullpath = $uploads_dir . $unique_filename;
                                    if ( move_uploaded_file( $tmp, $file_fullpath ) ) {
                                        // Store relative path in the mapping with original filename as key
                                        $upload_dir = wp_upload_dir();
                                        $relative_upload_path = str_replace( site_url(), '', $upload_dir['baseurl'] );
                                        $relative_url = $relative_upload_path . '/oum-useruploads/' . $unique_filename;
                                        $new_image_mapping[$name] = $relative_url;
                                    }
                                }
                            }
                        }
                        // Now build the final array based on image_order
                        if ( !empty( $image_order ) ) {
                            foreach ( $image_order as $item ) {
                                list( $type, $identifier ) = explode( ':', $item );
                                if ( $type === 'existing' ) {
                                    // Improved URL handling to properly convert absolute URLs to relative paths
                                    if ( filter_var( $identifier, FILTER_VALIDATE_URL ) ) {
                                        // Parse URL to handle different domain formats (with/without www, etc.)
                                        $url_parts = parse_url( $identifier );
                                        if ( isset( $url_parts['path'] ) ) {
                                            // Get the site path
                                            $site_path = parse_url( site_url(), PHP_URL_PATH );
                                            // Check if the URL path already starts with the site path
                                            if ( $site_path && strpos( $url_parts['path'], $site_path ) === 0 ) {
                                                // URL already has the site path, remove the duplicate
                                                $path = substr( $url_parts['path'], strlen( $site_path ) );
                                                $final_image_urls[] = $path;
                                            } else {
                                                // Only keep the path part
                                                $final_image_urls[] = $url_parts['path'];
                                            }
                                        } else {
                                            // Fallback to old method
                                            $relative_url = str_replace( site_url(), '', $identifier );
                                            $final_image_urls[] = $relative_url;
                                        }
                                    } else {
                                        // Check if this is a relative path that already includes the site path
                                        $site_path = parse_url( site_url(), PHP_URL_PATH );
                                        if ( $site_path && strpos( $identifier, $site_path ) === 0 ) {
                                            // URL already has the site path, remove the duplicate
                                            $identifier = substr( $identifier, strlen( $site_path ) );
                                        }
                                        // Already a relative path or other format
                                        $final_image_urls[] = $identifier;
                                    }
                                } else {
                                    // For new images, get URL from our mapping
                                    if ( isset( $new_image_mapping[$identifier] ) ) {
                                        $final_image_urls[] = $new_image_mapping[$identifier];
                                    }
                                }
                            }
                        }
                        // Save the final list of images
                        if ( !empty( $final_image_urls ) ) {
                            update_post_meta( $post_id, '_oum_location_image', implode( '|', $final_image_urls ) );
                            // Set first image as featured image
                            if ( !empty( $final_image_urls[0] ) ) {
                                \OpenUserMapPlugin\Base\LocationController::set_featured_image( $post_id, $final_image_urls[0] );
                            }
                        } else {
                            // If no images are set, remove both the location image meta and featured image
                            delete_post_meta( $post_id, '_oum_location_image' );
                            delete_post_thumbnail( $post_id );
                        }
                        // update meta
                        $lat_validated = floatval( str_replace( ',', '.', $data['oum_location_lat'] ) );
                        if ( !$lat_validated ) {
                            $lat_validated = '';
                        }
                        $lng_validated = floatval( str_replace( ',', '.', $data['oum_location_lng'] ) );
                        if ( !$lng_validated ) {
                            $lng_validated = '';
                        }
                        // Get existing location data to preserve vote count and other existing fields
                        // This prevents vote counts from being lost when editing locations via AJAX
                        $existing_data = get_post_meta( $post_id, '_oum_location_key', true );
                        if ( !is_array( $existing_data ) ) {
                            $existing_data = array();
                        }
                        $data_meta = array(
                            'address' => $data['oum_location_address'],
                            'lat'     => $lat_validated,
                            'lng'     => $lng_validated,
                            'text'    => $data['oum_location_text'],
                            'video'   => $data['oum_location_video'],
                        );
                        // Preserve existing vote count if it exists
                        if ( isset( $existing_data['votes'] ) ) {
                            $data_meta['votes'] = $existing_data['votes'];
                        }
                        // Preserve existing star rating data if it exists
                        if ( isset( $existing_data['star_rating_avg'] ) ) {
                            $data_meta['star_rating_avg'] = $existing_data['star_rating_avg'];
                        }
                        if ( isset( $existing_data['star_rating_count'] ) ) {
                            $data_meta['star_rating_count'] = $existing_data['star_rating_count'];
                        }
                        if ( isset( $data['oum_location_notification'] ) && isset( $data['oum_location_author_name'] ) && isset( $data['oum_location_author_email'] ) ) {
                            $data_meta['notification'] = $data['oum_location_notification'];
                            $data_meta['author_name'] = $data['oum_location_author_name'];
                            $data_meta['author_email'] = $data['oum_location_author_email'];
                        }
                        if ( isset( $data['oum_location_custom_fields'] ) && is_array( $data['oum_location_custom_fields'] ) ) {
                            $data_meta['custom_fields'] = $data['oum_location_custom_fields'];
                        }
                        update_post_meta( $post_id, '_oum_location_key', $data_meta );
                        // AUDIO
                        // remove the existing audio
                        if ( isset( $_POST['oum_remove_existing_audio'] ) && $_POST['oum_remove_existing_audio'] == '1' ) {
                            delete_post_meta( $post_id, '_oum_location_audio' );
                        }
                        if ( isset( $data['oum_location_audio_src'] ) && isset( $data['oum_location_audio_ext'] ) ) {
                            //set uploads dir
                            $uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'oum-useruploads/';
                            wp_mkdir_p( $uploads_dir );
                            $file_name = $post_id . '.' . $data['oum_location_audio_ext'];
                            $file_fullpath = $uploads_dir . $file_name;
                            // save file to wp-content/uploads/oum-useruploads/
                            if ( move_uploaded_file( $data['oum_location_audio_src'], $file_fullpath ) ) {
                                $upload_dir = wp_upload_dir();
                                $relative_upload_path = str_replace( site_url(), '', $upload_dir['baseurl'] );
                                $relative_url = $relative_upload_path . '/oum-useruploads/' . $file_name;
                                $data_audio = esc_url_raw( $relative_url );
                                update_post_meta( $post_id, '_oum_location_audio', $data_audio );
                            }
                        }
                        // Set excerpt if not set
                        if ( get_the_excerpt( $post_id ) == '' ) {
                            \OpenUserMapPlugin\Base\LocationController::set_excerpt( $post_id );
                        }
                    }
                    wp_send_json_success( array(
                        'message' => 'Ok, the location is now pending review.',
                        'post_id' => $post_id,
                    ) );
                }
            }
        }
        die;
        //necessary for correct ajax return in WordPress plugins
    }

    /**
     * AJAX: Handle vote/unvote actions and star ratings
     */
    public function ajax_toggle_vote() {
        // Check if vote feature is enabled
        if ( get_option( 'oum_enable_vote_feature' ) !== 'on' ) {
            wp_send_json_error( array(
                'message' => __( 'Vote feature is disabled.', 'open-user-map' ),
            ) );
            return;
        }
        // Verify nonce for security
        if ( !wp_verify_nonce( $_POST['nonce'], 'oum_vote_nonce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security check failed.', 'open-user-map' ),
            ) );
            return;
        }
        // Get post ID
        $post_id = intval( $_POST['post_id'] );
        if ( !$post_id || get_post_type( $post_id ) !== 'oum-location' ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid location.', 'open-user-map' ),
            ) );
            return;
        }
        // Get vote type setting (default: upvote)
        $vote_type = get_option( 'oum_vote_type', 'upvote' );
        // Get current location data
        $location_data = get_post_meta( $post_id, '_oum_location_key', true );
        if ( !is_array( $location_data ) ) {
            $location_data = array();
        }
        $cookie_type = get_option( 'oum_vote_cookie_type', 'persistent' );
        // Handle star rating type
        if ( $vote_type === 'star_rating' ) {
            // Get star rating value (1-5)
            $new_rating = ( isset( $_POST['star_rating'] ) ? intval( $_POST['star_rating'] ) : 0 );
            // Validate rating range
            if ( $new_rating < 1 || $new_rating > 5 ) {
                wp_send_json_error( array(
                    'message' => __( 'Invalid rating. Please select 1-5 stars.', 'open-user-map' ),
                ) );
                return;
            }
            // Get current star rating data
            $star_rating_avg = ( isset( $location_data['star_rating_avg'] ) ? floatval( $location_data['star_rating_avg'] ) : 0 );
            $star_rating_count = ( isset( $location_data['star_rating_count'] ) ? intval( $location_data['star_rating_count'] ) : 0 );
            // Cookie name for star rating
            $cookie_name = 'oum_star_rating_' . $post_id;
            // Get user's previous rating (if any)
            $previous_rating = 0;
            if ( $cookie_type === 'none' ) {
                // For no-cookie mode, use the current_rating parameter from frontend
                $previous_rating = ( isset( $_POST['current_rating'] ) ? intval( $_POST['current_rating'] ) : 0 );
            } else {
                // For cookie modes, check the cookie
                if ( isset( $_COOKIE[$cookie_name] ) ) {
                    $previous_rating = intval( $_COOKIE[$cookie_name] );
                }
            }
            // Calculate new average rating
            if ( $previous_rating > 0 ) {
                // User is changing their rating
                // Remove old rating and add new one
                // Handle edge case: if count is 0 but previous_rating exists (data inconsistency), treat as new rating
                if ( $star_rating_count > 0 ) {
                    $total_sum = $star_rating_avg * $star_rating_count - $previous_rating + $new_rating;
                    $new_avg = $total_sum / $star_rating_count;
                } else {
                    // Data inconsistency: cookie exists but count is 0, treat as new rating
                    $star_rating_count = 1;
                    $new_avg = $new_rating;
                }
            } else {
                // User is adding a new rating
                $total_sum = $star_rating_avg * $star_rating_count + $new_rating;
                $star_rating_count++;
                $new_avg = $total_sum / $star_rating_count;
            }
            // Update location data
            $location_data['star_rating_avg'] = round( $new_avg, 2 );
            $location_data['star_rating_count'] = $star_rating_count;
            update_post_meta( $post_id, '_oum_location_key', $location_data );
            // Set cookie to track user's rating
            if ( $cookie_type !== 'none' ) {
                // Set cookie based on type
                if ( $cookie_type === 'session' ) {
                    // Session cookie (expires when browser closes)
                    setcookie(
                        $cookie_name,
                        $new_rating,
                        0,
                        '/'
                    );
                } elseif ( $cookie_type === 'persistent' ) {
                    // Persistent cookie (expires in 1 year)
                    setcookie(
                        $cookie_name,
                        $new_rating,
                        time() + 365 * 24 * 60 * 60,
                        '/'
                    );
                }
            }
            // Return response
            wp_send_json_success( array(
                'rating'  => $new_rating,
                'average' => round( $new_avg, 2 ),
                'count'   => $star_rating_count,
                'message' => __( 'Rating saved!', 'open-user-map' ),
            ) );
            return;
        }
        // Handle upvote type (existing logic)
        $current_votes = ( isset( $location_data['votes'] ) ? intval( $location_data['votes'] ) : 0 );
        $cookie_name = 'oum_voted_' . $post_id;
        // Determine current vote state
        if ( $cookie_type === 'none' ) {
            // For no-cookie mode, use the current_vote_state parameter from frontend
            $is_voted = isset( $_POST['current_vote_state'] ) && $_POST['current_vote_state'] === 'voted';
        } else {
            // For cookie modes, check the cookie
            $is_voted = isset( $_COOKIE[$cookie_name] ) && $_COOKIE[$cookie_name] === '1';
        }
        // Toggle vote status
        if ( $is_voted ) {
            // Unvote: decrease count
            $new_votes = max( 0, $current_votes - 1 );
            $location_data['votes'] = $new_votes;
            $response_data = array(
                'voted'   => false,
                'votes'   => $new_votes,
                'message' => __( 'Location unvoted.', 'open-user-map' ),
            );
        } else {
            // Vote: increase count
            $new_votes = $current_votes + 1;
            $location_data['votes'] = $new_votes;
            $response_data = array(
                'voted'   => true,
                'votes'   => $new_votes,
                'message' => __( 'Location voted!', 'open-user-map' ),
            );
        }
        // Update location data
        update_post_meta( $post_id, '_oum_location_key', $location_data );
        // Set cookie to track user's vote status
        if ( $cookie_type !== 'none' ) {
            if ( $is_voted ) {
                // Remove cookie
                setcookie(
                    $cookie_name,
                    '',
                    time() - 3600,
                    '/'
                );
            } else {
                // Set cookie based on type
                if ( $cookie_type === 'session' ) {
                    // Session cookie (expires when browser closes)
                    setcookie(
                        $cookie_name,
                        '1',
                        0,
                        '/'
                    );
                } elseif ( $cookie_type === 'persistent' ) {
                    // Persistent cookie (expires in 1 year)
                    setcookie(
                        $cookie_name,
                        '1',
                        time() + 365 * 24 * 60 * 60,
                        '/'
                    );
                }
            }
        }
        // For 'none' type, no cookies are set or removed
        wp_send_json_success( $response_data );
    }

    /**
     * AJAX handler to get updated vote count for a location
     */
    public function ajax_get_vote_count() {
        // Check if vote feature is enabled
        if ( get_option( 'oum_enable_vote_feature' ) !== 'on' ) {
            wp_send_json_error( array(
                'message' => __( 'Vote feature is disabled.', 'open-user-map' ),
            ) );
            return;
        }
        // Verify nonce for security
        if ( !wp_verify_nonce( $_POST['nonce'], 'oum_vote_nonce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security check failed.', 'open-user-map' ),
            ) );
            return;
        }
        // Get post ID
        $post_id = intval( $_POST['post_id'] );
        if ( !$post_id || get_post_type( $post_id ) !== 'oum-location' ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid location.', 'open-user-map' ),
            ) );
            return;
        }
        // Get vote type setting (default: upvote)
        $vote_type = get_option( 'oum_vote_type', 'upvote' );
        // Get current location data
        $location_data = get_post_meta( $post_id, '_oum_location_key', true );
        if ( !is_array( $location_data ) ) {
            $location_data = array();
        }
        // Return data based on vote type
        if ( $vote_type === 'star_rating' ) {
            $star_rating_avg = ( isset( $location_data['star_rating_avg'] ) ? floatval( $location_data['star_rating_avg'] ) : 0 );
            $star_rating_count = ( isset( $location_data['star_rating_count'] ) ? intval( $location_data['star_rating_count'] ) : 0 );
            // Get user's current rating from cookie (if any)
            $cookie_type = get_option( 'oum_vote_cookie_type', 'persistent' );
            $user_rating = 0;
            if ( $cookie_type !== 'none' ) {
                $cookie_name = 'oum_star_rating_' . $post_id;
                if ( isset( $_COOKIE[$cookie_name] ) ) {
                    $user_rating = intval( $_COOKIE[$cookie_name] );
                }
            }
            wp_send_json_success( array(
                'average'     => round( $star_rating_avg, 2 ),
                'count'       => $star_rating_count,
                'user_rating' => $user_rating,
            ) );
        } else {
            // Upvote type
            $votes = ( isset( $location_data['votes'] ) ? intval( $location_data['votes'] ) : 0 );
            wp_send_json_success( array(
                'votes' => $votes,
            ) );
        }
    }

    /**
     * PRO: Trigger webhook notification
     */
    public function trigger_webhook( $post_id, $event_type, $data_meta = null ) {
        // Check if webhook notifications are enabled
        if ( !get_option( 'oum_enable_webhook_notification' ) ) {
            $this->safe_log( "Webhook notifications are disabled. Skipping trigger for Post ID: {$post_id}" );
            return;
        }
        // Get the webhook URL from settings
        $webhook_url = get_option( 'oum_webhook_notification_url' );
        if ( !$webhook_url ) {
            $this->safe_log( "No webhook URL configured for Post ID: {$post_id}" );
            return;
        }
        // Get first image URL if available
        $first_image_url = '';
        $image_value = oum_get_location_value( 'image', $post_id, true );
        if ( !empty( $image_value ) ) {
            // Extract first image from pipe-separated string
            $images = explode( '|', $image_value );
            if ( !empty( $images[0] ) ) {
                $first_image = trim( $images[0] );
                // Convert relative path to absolute URL if needed
                $first_image_url = ( strpos( $first_image, 'http' ) !== 0 ? site_url() . $first_image : $first_image );
            }
        }
        // Prepare webhook payload
        $webhook_data = array(
            'post_id'           => $post_id,
            'title'             => get_the_title( $post_id ),
            'content'           => get_post_field( 'post_content', $post_id ),
            'website_url'       => get_site_url(),
            'website_name'      => get_bloginfo( 'name' ),
            'edit_location_url' => get_edit_post_link( $post_id ),
            'taxonomy_terms'    => wp_get_post_terms( $post_id, 'oum-type', array(
                'fields' => 'names',
            ) ),
            'meta_data'         => $data_meta ?? get_post_meta( $post_id, '_oum_location_key', true ),
            'image_url'         => $first_image_url,
            'event'             => $event_type,
            'timestamp'         => current_time( 'mysql' ),
        );
        // Send webhook
        $response = wp_remote_post( $webhook_url, array(
            'body'    => json_encode( $webhook_data ),
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
            ),
        ) );
        // Handle response
        if ( is_wp_error( $response ) ) {
            $this->safe_log( 'Webhook error: ' . $response->get_error_message() );
        } else {
            $this->safe_log( "Webhook successfully triggered for Post ID: {$post_id} - Event: {$event_type}" );
        }
    }

    /**
     * AJAX callback: return a fresh nonce for the frontend "Add location" form.
     *
     * This keeps cached pages functional by letting the form request
     * a new nonce right before submission/opening.
     */
    public function ajax_refresh_location_nonce() {
        wp_send_json_success( array(
            'nonce' => wp_create_nonce( 'oum_location' ),
        ) );
    }

    public function correctImageOrientation( $filename, $img ) {
        if ( !function_exists( 'exif_read_data' ) ) {
            //exit, if EXIF PHP Library is not available
            return $img;
        }
        $exif = @exif_read_data( $filename );
        if ( $exif && isset( $exif['Orientation'] ) ) {
            $orientation = $exif['Orientation'];
            if ( $orientation != 1 ) {
                $deg = 0;
                switch ( $orientation ) {
                    case 3:
                        $deg = 180;
                        break;
                    case 6:
                        $deg = 270;
                        break;
                    case 8:
                        $deg = 90;
                        break;
                }
                if ( $deg ) {
                    $img = imagerotate( $img, $deg, 0 );
                }
            }
        }
        return $img;
    }

    /**
     * Assign current user to locations without user id on approval
     */
    public function assign_user_on_approval( $new_status, $old_status, $post ) {
        // Only proceed if:
        // 1. It's a location post type
        // 2. Status is changing to publish
        // 3. Post has no author assigned
        if ( $post->post_type === 'oum-location' && 'publish' === $new_status && 'publish' !== $old_status && $post->post_author == 0 ) {
            wp_update_post( array(
                'ID'          => $post->ID,
                'post_author' => get_current_user_id(),
            ) );
            $this->safe_log( 'Open User Map: Assigned user ID ' . get_current_user_id() . ' to location ' . $post->ID . ' on approval' );
        }
    }

    /**
     * Render Add Location Form only
     */
    public function render_block_form( $block_attributes, $content ) {
        // Check if inside a page builder - return placeholder if so
        if ( $this->is_page_builder_active() ) {
            return $this->render_page_builder_placeholder( 'open-user-map-form', $block_attributes );
        }
        // Enqueue frontend CSS with custom inline CSS
        $this->enqueue_frontend_css();
        // Load map base scripts (enqueues registered Leaflet CSS and JS)
        $this->include_map_scripts();
        // Enqueue registered frontend block map script
        wp_enqueue_script( 'oum_frontend_block_map_js' );
        // Localize block map script
        wp_localize_script( 'oum_frontend_block_map_js', 'oum_custom_strings', $this->oum_custom_strings() );
        // Decode HTML entities before passing to wp_localize_script to ensure
        // raw characters (like > in arrow functions) are preserved
        $custom_js_snippet = get_option( 'oum_custom_js' );
        if ( !empty( $custom_js_snippet ) ) {
            $custom_js_snippet = html_entity_decode( $custom_js_snippet, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        }
        wp_localize_script( 'oum_frontend_block_map_js', 'custom_js', array(
            'snippet' => $custom_js_snippet,
        ) );
        // Add safety net filter for custom_js localization
        $this->ensure_custom_js_localization();
        // Enqueue and localize AJAX script with all required data
        $this->enqueue_and_localize_ajax_script();
        // Enqueue registered carousel script
        wp_enqueue_script( 'oum_frontend_carousel_js' );
        // Enqueue vote functionality script if enabled (PRO feature)
        if ( oum_fs()->is__premium_only() && oum_fs()->can_use_premium_code() ) {
            if ( get_option( 'oum_enable_vote_feature' ) === 'on' ) {
                wp_enqueue_script( 'oum_frontend_vote_js' );
                wp_localize_script( 'oum_frontend_vote_js', 'oum_vote_nonce', array(
                    'nonce' => wp_create_nonce( 'oum_vote_nonce' ),
                ) );
                wp_localize_script( 'oum_frontend_vote_js', 'oum_vote_cookie_type', array(
                    'type' => get_option( 'oum_vote_cookie_type', 'persistent' ),
                ) );
            }
        }
        ob_start();
        require oum_get_template( 'block-form.php' );
        return ob_get_clean();
    }

}
