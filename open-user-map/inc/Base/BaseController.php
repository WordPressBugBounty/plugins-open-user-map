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

    public $oum_title_label_default;

    public $oum_map_label_default;

    public $oum_address_label_default;

    public $oum_description_label_default;

    public $oum_upload_media_label_default;

    public $oum_marker_types_label_default;

    public $oum_searchmarkers_label_default;

    public $oum_searchmarkers_zoom_default;

    public $oum_searchaddress_label_default;

    public $oum_user_notification_label_default;

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

    public $oum_ui_color_default = '#e02aaf';

    public $oum_custom_field_fieldtypes = array(
        "text" => "Text",
    );

    public $pro_oum_custom_field_fieldtypes = array(
        "link"     => "Link [PRO]",
        "email"    => "Email [PRO]",
        "checkbox" => "Checkbox [PRO]",
        "radio"    => "Radio [PRO]",
        "select"   => "Select [PRO]",
        "html"     => "HTML [PRO]",
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

    public $oum_incompatible_3rd_party_scripts = array();

    public function __construct() {
        $this->plugin_path = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
        $this->plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
        $this->plugin_version = get_file_data( dirname( dirname( dirname( __FILE__ ) ) ) . '/open-user-map.php', array(
            'Version' => 'Version',
        ) )['Version'];
        $this->plugin = plugin_basename( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/open-user-map.php';
        //Default labels
        $this->oum_title_label_default = __( 'Title', 'open-user-map' );
        $this->oum_map_label_default = __( 'Click on the map to set a marker', 'open-user-map' );
        $this->oum_description_label_default = __( 'Description', 'open-user-map' );
        $this->oum_upload_media_label_default = __( 'Upload media', 'open-user-map' );
        $this->oum_address_label_default = __( 'Subtitle', 'open-user-map' );
        $this->oum_marker_types_label_default = __( 'Type', 'open-user-map' );
        $this->oum_searchaddress_label_default = __( 'Search for address', 'open-user-map' );
        $this->oum_searchmarkers_label_default = __( 'Find marker', 'open-user-map' );
        $this->oum_searchmarkers_zoom_default = 8;
        $this->oum_user_notification_label_default = __( 'Notify me when it is published', 'open-user-map' );
        add_action( 'init', array($this, 'oum_init') );
    }

    public function oum_init() {
        $this->post_status = 'pending';
        if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
            // Default: Allow Frontend Adding for everyone
            add_action( 'wp_ajax_nopriv_oum_add_location_from_frontend', array($this, 'ajax_add_location_from_frontend') );
            add_action( 'wp_ajax_oum_add_location_from_frontend', array($this, 'ajax_add_location_from_frontend') );
        }
    }

    /**
     * Render all necessary base scripts for the map
     */
    public function include_map_scripts() {
        // Unregister incompatible 3rd party scripts
        $this->remove_incompatible_3rd_party_scripts();
        // enqueue Leaflet css
        wp_enqueue_style(
            'oum_leaflet_css',
            $this->plugin_url . 'src/leaflet/leaflet.css',
            array(),
            $this->plugin_version
        );
        wp_enqueue_style(
            'oum_leaflet_gesture_css',
            $this->plugin_url . 'src/leaflet/leaflet-gesture-handling.min.css',
            array(),
            $this->plugin_version
        );
        wp_enqueue_style(
            'oum_leaflet_markercluster_css',
            $this->plugin_url . 'src/leaflet/leaflet-markercluster.css',
            array(),
            $this->plugin_version
        );
        wp_enqueue_style(
            'oum_leaflet_markercluster_default_css',
            $this->plugin_url . 'src/leaflet/leaflet-markercluster.default.css',
            array(),
            $this->plugin_version
        );
        wp_enqueue_style(
            'oum_leaflet_geosearch_css',
            $this->plugin_url . 'src/leaflet/geosearch.css',
            array(),
            $this->plugin_version
        );
        wp_enqueue_style(
            'oum_leaflet_fullscreen_css',
            $this->plugin_url . 'src/leaflet/control.fullscreen.css',
            array(),
            $this->plugin_version
        );
        wp_enqueue_style(
            'oum_leaflet_locate_css',
            $this->plugin_url . 'src/leaflet/leaflet-locate.min.css',
            array(),
            $this->plugin_version
        );
        wp_enqueue_style(
            'oum_leaflet_search_css',
            $this->plugin_url . 'src/leaflet/leaflet-search.css',
            array(),
            $this->plugin_version
        );
        wp_enqueue_style(
            'oum_leaflet_responsivepopup_css',
            $this->plugin_url . 'src/leaflet/leaflet-responsive-popup.css',
            array(),
            $this->plugin_version
        );
        // Add map loader script first (before any other scripts)
        wp_enqueue_script(
            'oum_map_loader_js',
            $this->plugin_url . 'src/js/frontend-map-loader.js',
            array(),
            $this->plugin_version,
            true
        );
        // enqueue Leaflet javascripts
        wp_enqueue_script(
            'oum_leaflet_polyfill_unfetch_js',
            $this->plugin_url . 'src/js/polyfills/unfetch.js',
            array(),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_js',
            $this->plugin_url . 'src/leaflet/leaflet.js',
            array('oum_leaflet_polyfill_unfetch_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_providers_js',
            $this->plugin_url . 'src/leaflet/leaflet-providers.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_markercluster_js',
            $this->plugin_url . 'src/leaflet/leaflet-markercluster.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_subgroups_js',
            $this->plugin_url . 'src/leaflet/leaflet.featuregroup.subgroup.js',
            array('oum_leaflet_js', 'oum_leaflet_markercluster_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_geosearch_js',
            $this->plugin_url . 'src/leaflet/geosearch.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_locate_js',
            $this->plugin_url . 'src/leaflet/leaflet-locate.min.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_fullscreen_js',
            $this->plugin_url . 'src/leaflet/control.fullscreen.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_search_js',
            $this->plugin_url . 'src/leaflet/leaflet-search.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_gesture_js',
            $this->plugin_url . 'src/leaflet/leaflet-gesture-handling.min.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        wp_enqueue_script(
            'oum_leaflet_responsivepopup_js',
            $this->plugin_url . 'src/leaflet/leaflet-responsive-popup.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        // Capture the fully extended L object after all Leaflet add-ons are loaded
        wp_enqueue_script(
            'oum_global_leaflet_js',
            $this->plugin_url . 'src/leaflet/oum-global-leaflet.js',
            array('oum_leaflet_js'),
            $this->plugin_version,
            true
        );
        // enqueue WordPress i18n (for translations inside JS)
        wp_enqueue_script( 'wp-i18n' );
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
        wp_enqueue_style(
            'oum_frontend_css',
            $this->plugin_url . 'assets/frontend.css',
            array(),
            $this->plugin_version
        );
        // load map base scripts
        $this->include_map_scripts();
        wp_enqueue_script(
            'oum_frontend_block_map_js',
            $this->plugin_url . 'src/js/frontend-block-map.js',
            array(
                'oum_leaflet_providers_js',
                'oum_leaflet_markercluster_js',
                'oum_leaflet_subgroups_js',
                'oum_leaflet_geosearch_js',
                'oum_leaflet_locate_js',
                'oum_leaflet_fullscreen_js',
                'oum_leaflet_search_js',
                'oum_leaflet_gesture_js',
                'wp-i18n',
                'oum_global_leaflet_js'
            ),
            $this->plugin_version,
            true
        );
        // add custom js to frontend-block-map.js
        wp_localize_script( 'oum_frontend_block_map_js', 'custom_js', array(
            'snippet' => get_option( 'oum_custom_js' ),
        ) );
        wp_enqueue_script(
            'oum_frontend_ajax_js',
            $this->plugin_url . 'src/js/frontend-ajax.js',
            array('jquery', 'oum_frontend_block_map_js'),
            $this->plugin_version,
            true
        );
        wp_localize_script( 'oum_frontend_ajax_js', 'oum_ajax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ) );
        // Enqueue carousel script
        wp_enqueue_script(
            'oum_frontend_carousel_js',
            $this->plugin_url . 'src/js/frontend-carousel.js',
            array(),
            $this->plugin_version,
            true
        );
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
                $data['oum_location_custom_fields'] = array();
                foreach ( $_POST['oum_location_custom_fields'] as $index => $val ) {
                    if ( is_array( $val ) ) {
                        //multiple values
                        $arr_vals = array();
                        foreach ( $val as $el ) {
                            $arr_vals[] = sanitize_text_field( wp_strip_all_tags( $el ) );
                        }
                        $data['oum_location_custom_fields'][$index] = $arr_vals;
                    } else {
                        //single value
                        $data['oum_location_custom_fields'][$index] = sanitize_text_field( wp_strip_all_tags( $val ) );
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
                //error_log(print_r($_FILES, true));
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
                                        error_log( "File {$key}: Invalid upload" );
                                        continue;
                                    }
                                    if ( !in_array( $ext, $valid_extensions ) ) {
                                        error_log( "File {$key}: Invalid extension" );
                                        continue;
                                    }
                                    if ( $size > $max_filesize ) {
                                        error_log( "File {$key}: File too large" );
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
                                        $relative_url = '/wp-content/uploads/oum-useruploads/' . $unique_filename;
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
                                    // Convert absolute URL to relative if needed
                                    $relative_url = str_replace( site_url(), '', $identifier );
                                    $final_image_urls[] = $relative_url;
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
                        $data_meta = array(
                            'address' => $data['oum_location_address'],
                            'lat'     => $lat_validated,
                            'lng'     => $lng_validated,
                            'text'    => $data['oum_location_text'],
                            'video'   => $data['oum_location_video'],
                        );
                        if ( isset( $data['oum_location_notification'] ) && isset( $data['oum_location_author_name'] ) && isset( $data['oum_location_author_email'] ) ) {
                            $data_meta['notification'] = $data['oum_location_notification'];
                            $data_meta['author_name'] = $data['oum_location_author_name'];
                            $data_meta['author_email'] = $data['oum_location_author_email'];
                        }
                        if ( isset( $data['oum_location_custom_fields'] ) && is_array( $data['oum_location_custom_fields'] ) ) {
                            $data_meta['custom_fields'] = $data['oum_location_custom_fields'];
                        }
                        update_post_meta( $post_id, '_oum_location_key', $data_meta );
                        // IMAGE
                        // remove the existing image
                        if ( isset( $_POST['oum_remove_existing_image'] ) && $_POST['oum_remove_existing_image'] == '1' ) {
                            delete_post_meta( $post_id, '_oum_location_image' );
                        }
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
                                $oum_location_audio_url = wp_upload_dir()['baseurl'] . '/oum-useruploads/' . $file_name;
                                $data_audio = esc_url_raw( $oum_location_audio_url );
                                update_post_meta( $post_id, '_oum_location_audio', $data_audio );
                            }
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
     * PRO: Trigger webhook notification
     */
    public function trigger_webhook( $post_id, $event_type, $data_meta = null ) {
        // Check if webhook notifications are enabled
        if ( !get_option( 'oum_enable_webhook_notification' ) ) {
            error_log( "Webhook notifications are disabled. Skipping trigger for Post ID: {$post_id}" );
            return;
        }
        // Get the webhook URL from settings
        $webhook_url = get_option( 'oum_webhook_notification_url' );
        if ( !$webhook_url ) {
            error_log( "No webhook URL configured for Post ID: {$post_id}" );
            return;
        }
        // Prepare webhook payload
        $webhook_data = array(
            'title'             => get_the_title( $post_id ),
            'content'           => get_post_field( 'post_content', $post_id ),
            'website_url'       => get_site_url(),
            'website_name'      => get_bloginfo( 'name' ),
            'edit_location_url' => get_edit_post_link( $post_id ),
            'taxonomy_terms'    => wp_get_post_terms( $post_id, 'oum-type', array(
                'fields' => 'names',
            ) ),
            'meta_data'         => $data_meta ?? get_post_meta( $post_id, '_oum_location_key', true ),
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
            error_log( 'Webhook error: ' . $response->get_error_message() );
        } else {
            error_log( "Webhook successfully triggered for Post ID: {$post_id} - Event: {$event_type}" );
        }
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

}
