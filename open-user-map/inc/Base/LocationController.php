<?php

/**
 * @package OpenUserMapPlugin
 */
namespace OpenUserMapPlugin\Base;

use OpenUserMapPlugin\Base\BaseController;
class LocationController extends BaseController {
    public $settings;

    public function register() {
        // CPT: Location
        add_action( 'init', array($this, 'location_cpt') );
        add_action( 'admin_init', array($this, 'oum_capabilities') );
        add_action( 'add_meta_boxes', array($this, 'add_meta_box') );
        add_action( 'save_post', array($this, 'save_fields') );
        add_action( 'manage_oum-location_posts_columns', array($this, 'set_custom_location_columns') );
        add_action(
            'manage_oum-location_posts_custom_column',
            array($this, 'set_custom_location_columns_data'),
            10,
            2
        );
        // this method has 2 attributes
        add_filter( 'manage_oum-location_posts_sortable_columns', array($this, 'set_sortable_columns') );
        add_action( 'pre_get_posts', array($this, 'custom_search_oum_location') );
        add_action( 'admin_menu', array($this, 'add_pending_counter_to_menu') );
        add_filter(
            'post_thumbnail_html',
            array($this, 'default_location_header'),
            10,
            5
        );
        add_filter( 'the_content', array($this, 'default_location_content') );
    }

    /**
     * CPT: Location
     */
    public static function location_cpt() {
        $labels = array(
            'name'               => __( 'Locations', 'open-user-map' ),
            'singular_name'      => __( 'Location', 'open-user-map' ),
            'add_new'            => __( 'Add new Location', 'open-user-map' ),
            'add_new_item'       => __( 'Add new Location', 'open-user-map' ),
            'edit_item'          => __( 'Edit Location', 'open-user-map' ),
            'new_item'           => __( 'New Location', 'open-user-map' ),
            'all_items'          => __( 'All Locations', 'open-user-map' ),
            'view_item'          => __( 'View Location', 'open-user-map' ),
            'search_items'       => __( 'Search Locations', 'open-user-map' ),
            'not_found'          => __( 'No Locations found', 'open-user-map' ),
            'not_found_in_trash' => __( 'No Location in trash', 'open-user-map' ),
            'parent_item_colon'  => '',
            'menu_name'          => __( 'Open User Map', 'open-user-map' ),
        );
        $args = array(
            'labels'              => $labels,
            'capability_type'     => 'oum-location',
            'map_meta_cap'        => true,
            'description'         => __( 'Location', 'open-user-map' ),
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'exclude_from_search' => true,
            'show_in_nav_menus'   => false,
            'has_archive'         => false,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-location-alt',
            'supports'            => array(
                'title',
                'author',
                'thumbnail',
                'excerpt',
                'revisions',
                'trash'
            ),
        );
        register_post_type( 'oum-location', $args );
    }

    /**
     * Assign default capabilities to default user roles (same as 'post')
     */
    public function oum_capabilities() {
        // Administrator, Editor
        $roles = array('editor', 'administrator');
        foreach ( $roles as $the_role ) {
            $role = get_role( $the_role );
            if ( !is_null( $role ) ) {
                $role->add_cap( 'read_oum-location' );
                $role->add_cap( 'read_private_oum-locations' );
                $role->add_cap( 'edit_oum-location' );
                $role->add_cap( 'edit_oum-locations' );
                $role->add_cap( 'edit_others_oum-locations' );
                $role->add_cap( 'edit_published_oum-locations' );
                $role->add_cap( 'edit_private_oum-locations' );
                $role->add_cap( 'publish_oum-locations' );
                $role->add_cap( 'delete_oum-locations' );
                $role->add_cap( 'delete_others_oum-locations' );
                $role->add_cap( 'delete_private_oum-locations' );
                $role->add_cap( 'delete_published_oum-locations' );
            }
        }
        // Author
        $role = get_role( 'author' );
        if ( !is_null( $role ) ) {
            $role->add_cap( 'edit_oum-locations' );
            $role->add_cap( 'edit_published_oum-locations' );
            $role->add_cap( 'publish_oum-locations' );
            $role->add_cap( 'delete_oum-locations' );
            $role->add_cap( 'delete_published_oum-locations' );
        }
        // Contributor
        $role = get_role( 'contributor' );
        if ( !is_null( $role ) ) {
            $role->add_cap( 'edit_oum-locations' );
            $role->add_cap( 'delete_oum-locations' );
        }
        // Subscriber
        $role = get_role( 'subscriber' );
        if ( !is_null( $role ) ) {
            $role->add_cap( 'edit_oum-locations' );
            $role->add_cap( 'delete_oum-locations' );
        }
    }

    public function add_meta_box() {
        add_meta_box(
            'location_customfields',
            __( 'Open User Map Location Settings', 'open-user-map' ),
            array($this, 'render_customfields_box'),
            'oum-location',
            'normal',
            'high'
        );
    }

    public function render_customfields_box( $post ) {
        wp_nonce_field( 'oum_location', 'oum_location_nonce' );
        $data = get_post_meta( $post->ID, '_oum_location_key', true );
        //$this->safe_log(print_r($data, true));
        $address = ( isset( $data['address'] ) ? $data['address'] : '' );
        $lat = ( isset( $data['lat'] ) ? $data['lat'] : '' );
        $lng = ( isset( $data['lng'] ) ? $data['lng'] : '' );
        $zoom = ( isset( $data['zoom'] ) ? $data['zoom'] : '16' );
        $text = ( isset( $data['text'] ) ? $data['text'] : '' );
        $video = ( isset( $data['video'] ) ? $data['video'] : '' );
        $has_video = ( isset( $video ) && $video != '' ? 'has-video' : '' );
        $video_tag = ( $has_video ? apply_filters( 'the_content', esc_attr( $video ) ) : '' );
        $image = get_post_meta( $post->ID, '_oum_location_image', true );
        // Convert relative paths to absolute URLs for preview
        if ( $image ) {
            $image_urls = explode( '|', $image );
            $absolute_urls = array();
            foreach ( $image_urls as $url ) {
                if ( !empty( $url ) ) {
                    // Convert relative path to absolute URL if needed
                    if ( strpos( $url, 'http' ) !== 0 ) {
                        // Get the site path from site_url
                        $site_path = parse_url( site_url(), PHP_URL_PATH );
                        // Check if the URL already starts with the site path
                        if ( $site_path && strpos( $url, $site_path ) === 0 ) {
                            // URL already has the site path, remove it to avoid duplication
                            $url = substr( $url, strlen( $site_path ) );
                        }
                        $absolute_urls[] = site_url() . $url;
                    } else {
                        $absolute_urls[] = $url;
                    }
                }
            }
            $image = implode( '|', $absolute_urls );
        }
        $audio = get_post_meta( $post->ID, '_oum_location_audio', true );
        $has_audio = ( isset( $audio ) && $audio != '' ? 'has-audio' : '' );
        // Convert relative audio path to absolute URL if needed
        if ( $audio && strpos( $audio, 'http' ) !== 0 ) {
            // Get the site path from site_url
            $site_path = parse_url( site_url(), PHP_URL_PATH );
            // Check if the URL already starts with the site path
            if ( $site_path && strpos( $audio, $site_path ) === 0 ) {
                // URL already has the site path, remove it to avoid duplication
                $audio = substr( $audio, strlen( $site_path ) );
            }
            $audio_url = site_url() . $audio;
            $audio_tag = ( $has_audio ? '<audio controls="controls" style="width:100%"><source type="audio/mp4" src="' . esc_attr( $audio_url ) . '"><source type="audio/mpeg" src="' . esc_attr( $audio_url ) . '"><source type="audio/wav" src="' . esc_attr( $audio_url ) . '"></audio>' : '' );
            $audio = $audio_url;
            // Update audio variable with the absolute URL
        } else {
            $audio_tag = ( $has_audio ? '<audio controls="controls" style="width:100%"><source type="audio/mp4" src="' . esc_attr( $audio ) . '"><source type="audio/mpeg" src="' . esc_attr( $audio ) . '"><source type="audio/wav" src="' . esc_attr( $audio ) . '"></audio>' : '' );
        }
        $notification = ( isset( $data['notification'] ) ? $data['notification'] : '' );
        $author_name = ( isset( $data['author_name'] ) ? $data['author_name'] : '' );
        $author_email = ( isset( $data['author_email'] ) ? $data['author_email'] : '' );
        $text_notify_me_on_publish_label = ( get_option( 'oum_user_notification_label' ) ? get_option( 'oum_user_notification_label' ) : $this->oum_get_default_label( 'user_notification' ) );
        $text_notify_me_on_publish_name = __( 'Your name', 'open-user-map' );
        $text_notify_me_on_publish_email = __( 'Your email', 'open-user-map' );
        $notified = get_post_meta( $post->ID, '_oum_location_notified', true );
        $notified_tag = ( isset( $notified ) && $notified != '' ? '<p>User has been notified on ' . date( "Y-m-d H:i:s", $notified ) . '</p>' : '' );
        // Set map style
        $map_style = ( get_option( 'oum_map_style' ) ? get_option( 'oum_map_style' ) : 'Esri.WorldStreetMap' );
        $oum_tile_provider_mapbox_key = get_option( 'oum_tile_provider_mapbox_key', '' );
        $marker_icon = ( get_option( 'oum_marker_icon' ) ? get_option( 'oum_marker_icon' ) : 'default' );
        $marker_user_icon = get_option( 'oum_marker_user_icon' );
        $meta_custom_fields = ( isset( $data['custom_fields'] ) ? $data['custom_fields'] : false );
        $active_custom_fields = get_option( 'oum_custom_fields' );
        // render view
        require_once oum_get_template( 'page-backend-location.php' );
    }

    /**
     * Save Location Fields (Backend)
     */
    public static function save_fields( $post_id, $fields = array() ) {
        $location_data = $_REQUEST;
        // Set data source ($_REQUEST or $fields)
        if ( !empty( $fields ) ) {
            $location_data = $fields;
            $location_data['post_type'] = 'oum-location';
        }
        // Dont save if not a location
        if ( !isset( $location_data['post_type'] ) || $location_data['post_type'] != 'oum-location' ) {
            return $post_id;
        }
        // Dont save if wordpress just auto-saves
        if ( defined( 'DOING AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        // Dont save if user is not allowed to do
        $has_general_permission = current_user_can( 'edit_oum-locations' );
        $is_author = get_current_user_id() == get_post_field( 'post_author', $post_id );
        $can_edit_specific_post = current_user_can( 'edit_post', $post_id );
        $allow_edit = ( $has_general_permission && ($is_author || $can_edit_specific_post) ? true : false );
        if ( !$allow_edit ) {
            return $post_id;
        }
        // Check if this is a Quick Edit operation
        $is_quick_edit = isset( $location_data['action'] ) && in_array( $location_data['action'], array('edit', 'inline-save') );
        // For regular save operations, verify nonce
        if ( !$is_quick_edit ) {
            if ( !isset( $location_data['oum_location_nonce'] ) ) {
                return $post_id;
            }
            $nonce = $location_data['oum_location_nonce'];
            if ( !wp_verify_nonce( $nonce, 'oum_location' ) ) {
                return $post_id;
            }
        }
        // Handle image uploads and updates (for regular save)
        if ( isset( $location_data['oum_location_image'] ) ) {
            $images = explode( '|', $location_data['oum_location_image'] );
            // Validate image URLs and convert to relative paths
            $valid_images = array();
            foreach ( $images as $image_url ) {
                if ( !empty( $image_url ) && strpos( $image_url, '|' ) === false ) {
                    // Convert absolute URLs to relative paths if they're from our site
                    if ( strpos( $image_url, site_url() ) === 0 ) {
                        $valid_images[] = str_replace( site_url(), '', $image_url );
                    } else {
                        if ( strpos( $image_url, '/wp-content' ) === 0 ) {
                            // Already relative, use as is
                            $valid_images[] = $image_url;
                        } else {
                            // External URL or unexpected format, store as is
                            $valid_images[] = esc_url_raw( $image_url );
                        }
                    }
                }
            }
            // Store images as pipe-separated string
            update_post_meta( $post_id, '_oum_location_image', implode( '|', $valid_images ) );
            // Set first image as featured image (needs full URL)
            if ( !empty( $valid_images[0] ) ) {
                if ( strpos( $valid_images[0], '/wp-content' ) === 0 ) {
                    // Convert relative path to full URL for featured image
                    self::set_featured_image( $post_id, site_url() . $valid_images[0] );
                } else {
                    // Use as is if it's already a full URL
                    self::set_featured_image( $post_id, $valid_images[0] );
                }
            }
        } elseif ( $is_quick_edit ) {
            $old_images = get_post_meta( $post_id, '_oum_location_image', true );
            if ( !empty( $old_images ) ) {
                $images = explode( '|', $old_images );
                if ( !empty( $images[0] ) ) {
                    if ( strpos( $images[0], '/wp-content' ) === 0 ) {
                        // Convert relative path to full URL for featured image
                        self::set_featured_image( $post_id, site_url() . $images[0] );
                    } else {
                        // Use as is if it's already a full URL
                        self::set_featured_image( $post_id, $images[0] );
                    }
                }
            }
        }
        // Set excerpt if not set (for both regular and quick edit)
        if ( get_the_excerpt( $post_id ) == '' ) {
            self::set_excerpt( $post_id );
        }
        // If this is a Quick Edit operation, we're done
        if ( $is_quick_edit ) {
            return $post_id;
        }
        // Continue with regular save operation
        $lat_validated = ( isset( $location_data['oum_location_lat'] ) ? floatval( str_replace( ',', '.', sanitize_text_field( $location_data['oum_location_lat'] ) ) ) : '' );
        $lng_validated = ( isset( $location_data['oum_location_lng'] ) ? floatval( str_replace( ',', '.', sanitize_text_field( $location_data['oum_location_lng'] ) ) ) : '' );
        $zoom_validated = ( isset( $location_data['oum_location_zoom'] ) ? intval( sanitize_text_field( $location_data['oum_location_zoom'] ) ) : 13 );
        // Get existing location data to preserve vote count and other existing fields
        // This prevents vote counts from being lost when editing locations
        $existing_data = get_post_meta( $post_id, '_oum_location_key', true );
        if ( !is_array( $existing_data ) ) {
            $existing_data = array();
        }
        $data = array(
            'address'      => ( isset( $location_data['oum_location_address'] ) ? sanitize_text_field( $location_data['oum_location_address'] ) : '' ),
            'lat'          => $lat_validated,
            'lng'          => $lng_validated,
            'zoom'         => $zoom_validated,
            'text'         => ( isset( $location_data['oum_location_text'] ) ? wp_kses_post( $location_data['oum_location_text'] ) : '' ),
            'author_name'  => ( isset( $location_data['oum_location_author_name'] ) ? sanitize_text_field( $location_data['oum_location_author_name'] ) : '' ),
            'author_email' => ( isset( $location_data['oum_location_author_email'] ) ? sanitize_text_field( $location_data['oum_location_author_email'] ) : '' ),
            'video'        => ( isset( $location_data['oum_location_video'] ) ? sanitize_text_field( $location_data['oum_location_video'] ) : '' ),
        );
        // Preserve existing vote count if it exists
        if ( isset( $existing_data['votes'] ) ) {
            $data['votes'] = $existing_data['votes'];
        }
        if ( isset( $location_data['oum_location_notification'] ) ) {
            $data['notification'] = sanitize_text_field( $location_data['oum_location_notification'] );
        }
        if ( isset( $location_data['oum_location_custom_fields'] ) && is_array( $location_data['oum_location_custom_fields'] ) ) {
            $data['custom_fields'] = $location_data['oum_location_custom_fields'];
        }
        update_post_meta( $post_id, '_oum_location_key', $data );
        if ( isset( $location_data['oum_location_audio'] ) ) {
            // validate & store audio seperately (to avoid serialized URLs [bad for search & replace due to domain change])
            $audio_url = esc_url_raw( $location_data['oum_location_audio'] );
            // Convert absolute URLs to relative paths
            $data_audio = str_replace( site_url(), '', $audio_url );
            update_post_meta( $post_id, '_oum_location_audio', $data_audio );
        }
    }

    /**
     * Helper function to set the featured image
     */
    public static function set_featured_image( $post_id, $image_url ) {
        // Get current featured image filename
        $current_thumbnail_id = get_post_thumbnail_id( $post_id );
        if ( $current_thumbnail_id ) {
            // Get the original filename from attachment metadata
            $current_thumbnail_meta = wp_get_attachment_metadata( $current_thumbnail_id );
            if ( isset( $current_thumbnail_meta['original_image'] ) ) {
                $current_thumbnail_filename = pathinfo( $current_thumbnail_meta['original_image'], PATHINFO_FILENAME );
            } else {
                $current_thumbnail_filename = pathinfo( $current_thumbnail_meta['file'], PATHINFO_FILENAME );
            }
            // Remove any suffixes
            $current_thumbnail_filename = preg_replace( '/-(?:scaled|[0-9]+x[0-9]+|[0-9]+)$/', '', $current_thumbnail_filename );
        }
        // Get new image filename, handling both relative and absolute paths
        $new_image_filename = '';
        if ( strpos( $image_url, '/wp-content' ) === 0 || strpos( $image_url, '/uploads' ) === 0 ) {
            // Relative path
            $new_image_filename = pathinfo( $image_url, PATHINFO_FILENAME );
        } else {
            // Absolute path or external URL
            $new_image_filename = pathinfo( parse_url( $image_url, PHP_URL_PATH ), PATHINFO_FILENAME );
        }
        // Remove size suffixes like -scaled, -1024x587, -1, etc.
        $base_image_filename = preg_replace( '/-(?:scaled|[0-9]+x[0-9]+|[0-9]+)$/', '', $new_image_filename );
        // Compare base filenames
        if ( empty( $current_thumbnail_id ) || $base_image_filename !== $current_thumbnail_filename ) {
            // Convert relative URL to absolute URL if needed
            $absolute_url = ( strpos( $image_url, 'http' ) !== 0 ? site_url() . $image_url : $image_url );
            global $wpdb;
            $attachment_id = false;
            // Get uploads dir
            $upload_dir = wp_upload_dir();
            $file_path = false;
            if ( strpos( $absolute_url, $upload_dir['baseurl'] ) === 0 ) {
                $file_path = ltrim( str_replace( $upload_dir['baseurl'], '', $absolute_url ), '/' );
            } else {
                if ( strpos( $image_url, '/wp-content/uploads/' ) === 0 ) {
                    $file_path = ltrim( str_replace( '/wp-content/uploads/', '', $image_url ), '/' );
                }
            }
            $posts_table = $wpdb->posts;
            $postmeta_table = $wpdb->postmeta;
            // --- 1. Prefer exact _wp_attached_file match (image attachments) ---
            if ( $file_path ) {
                $query = $wpdb->prepare( "SELECT p.ID FROM {$posts_table} p INNER JOIN {$postmeta_table} pm ON p.ID = pm.post_id WHERE pm.meta_key = '_wp_attached_file' AND pm.meta_value = %s AND p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%' LIMIT 1", $file_path );
                $attachment_id = $wpdb->get_var( $query );
            }
            // --- 2. Fallback: Search for attachment by base filename (image attachments) ---
            if ( !$attachment_id && $base_image_filename ) {
                $like_pattern = '%' . $wpdb->esc_like( $base_image_filename ) . '%';
                $query = $wpdb->prepare( "SELECT p.ID, pm.meta_value FROM {$posts_table} p INNER JOIN {$postmeta_table} pm ON p.ID = pm.post_id WHERE pm.meta_key = '_wp_attached_file' AND pm.meta_value LIKE %s AND p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%'", $like_pattern );
                $results = $wpdb->get_results( $query );
                if ( $results ) {
                    // Try to find the best match: prefer the original file (no size suffix)
                    foreach ( $results as $row ) {
                        $filename = pathinfo( $row->meta_value, PATHINFO_FILENAME );
                        $filename_base = preg_replace( '/-(?:scaled|[0-9]+x[0-9]+|[0-9]+)$/', '', $filename );
                        if ( $filename_base === $base_image_filename ) {
                            $attachment_id = $row->ID;
                            break;
                        }
                    }
                    // If no perfect match, just use the first found
                    if ( !$attachment_id ) {
                        $attachment_id = $results[0]->ID;
                    }
                }
            }
            // --- 3. Fallback: search by guid (full URL, image attachments) ---
            if ( !$attachment_id ) {
                $query = $wpdb->prepare( "SELECT ID FROM {$posts_table} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND guid = %s LIMIT 1", $absolute_url );
                $attachment_id = $wpdb->get_var( $query );
            }
            if ( $attachment_id ) {
                // Attachment found, set as featured image
                set_post_thumbnail( $post_id, $attachment_id );
            } else {
                // Not found, sideload image as before
                $upload = media_sideload_image(
                    $absolute_url,
                    $post_id,
                    null,
                    'src'
                );
                if ( !is_wp_error( $upload ) ) {
                    $attachment_id = attachment_url_to_postid( $upload );
                    if ( $attachment_id ) {
                        set_post_thumbnail( $post_id, $attachment_id );
                    }
                }
            }
        }
    }

    /**
     * Helper function to set the excerpt (if not set)
     */
    public static function set_excerpt( $post_id ) {
        $max_length = 400;
        $post_text = oum_get_location_value( 'text', $post_id, true );
        $text = wp_strip_all_tags( $post_text );
        if ( $text ) {
            if ( strlen( $text ) > $max_length ) {
                $text = substr( $text, 0, $max_length );
                $last_space = strrpos( $text, ' ' );
                if ( $last_space !== false ) {
                    $text = substr( $text, 0, $last_space );
                }
                $text .= '...';
            }
            $post = array(
                'ID'           => $post_id,
                'post_excerpt' => sanitize_text_field( $text ),
            );
            wp_update_post( $post );
        }
    }

    public function set_custom_location_columns( $columns ) {
        // Get all default columns we want to preserve
        $cb = ( isset( $columns['cb'] ) ? $columns['cb'] : '' );
        $title = ( isset( $columns['title'] ) ? $columns['title'] : '' );
        $author = ( isset( $columns['author'] ) ? $columns['author'] : '' );
        $categories = ( isset( $columns['taxonomy-oum-type'] ) ? $columns['taxonomy-oum-type'] : '' );
        $comments = ( isset( $columns['comments'] ) ? $columns['comments'] : '' );
        $date = ( isset( $columns['date'] ) ? $columns['date'] : '' );
        // Remove all columns
        $columns = array();
        // Add columns in desired order
        if ( $cb ) {
            $columns['cb'] = $cb;
        }
        $columns['post_id'] = 'ID';
        $columns['title'] = $title;
        $columns['address'] = __( 'Subtitle', 'open-user-map' );
        if ( $categories ) {
            $columns['taxonomy-oum-type'] = $categories;
        }
        $columns['text'] = __( 'Text', 'open-user-map' );
        $columns['geocoordinates'] = __( 'Coordinates', 'open-user-map' );
        // Add votes column only if vote feature is enabled
        if ( get_option( 'oum_enable_vote_feature' ) === 'on' ) {
            $columns['votes'] = __( 'Votes', 'open-user-map' );
        }
        if ( $comments ) {
            $columns['comments'] = $comments;
        }
        if ( $author ) {
            $columns['author'] = $author;
        }
        if ( $date ) {
            $columns['date'] = $date;
        }
        return $columns;
    }

    public function set_custom_location_columns_data( $column, $post_id ) {
        $data = get_post_meta( $post_id, '_oum_location_key', true );
        $text = ( isset( $data['text'] ) ? $data['text'] : '' );
        $address = ( isset( $data['address'] ) ? $data['address'] : '' );
        $lat = ( isset( $data['lat'] ) ? $data['lat'] : '' );
        $lng = ( isset( $data['lng'] ) ? $data['lng'] : '' );
        $votes = ( isset( $data['votes'] ) ? intval( $data['votes'] ) : 0 );
        switch ( $column ) {
            case 'post_id':
                echo esc_html( $post_id );
                break;
            case 'text':
                echo esc_html( $text );
                break;
            case 'address':
                echo esc_html( $address );
                break;
            case 'geocoordinates':
                echo esc_attr( $lat ) . ', ' . esc_attr( $lng );
                break;
            case 'votes':
                echo esc_attr( $votes );
                break;
            default:
                break;
        }
    }

    /**
     * Set sortable columns for the admin list
     */
    public function set_sortable_columns( $columns ) {
        // Add votes column as sortable only if vote feature is enabled
        if ( get_option( 'oum_enable_vote_feature' ) === 'on' ) {
            $columns['votes'] = 'votes';
        }
        return $columns;
    }

    /**
     * Custom search for locations (including meta and author)
     */
    public function custom_search_oum_location( $query ) {
        // Ensure we're in the WordPress admin, it's a search query, and the right post type
        if ( $query->is_search() && is_admin() && $query->is_main_query() && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'oum-location' ) {
            // Get the search term
            $search_term = $query->query_vars['s'];
            // Clear the default search query
            $query->set( 's', '' );
            // Join wp_users and wp_postmeta tables for author and meta field searches
            add_filter( 'posts_join', function ( $join ) {
                global $wpdb;
                // Join wp_users for author search
                if ( strpos( $join, "{$wpdb->users}" ) === false ) {
                    $join .= " LEFT JOIN {$wpdb->users} AS u ON {$wpdb->posts}.post_author = u.ID ";
                }
                // Join wp_postmeta for meta field search
                if ( strpos( $join, "{$wpdb->postmeta}" ) === false ) {
                    $join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
                }
                return $join;
            } );
            // Modify the search query to include user_login, user_email, post_title, and post_content search
            add_filter(
                'posts_search',
                function ( $search, $query ) use($search_term) {
                    global $wpdb;
                    if ( $search_term ) {
                        $escaped_term = '%' . $wpdb->esc_like( $search_term ) . '%';
                        // Combine search conditions for post title, content, and author fields
                        $search .= " AND ({$wpdb->posts}.post_title LIKE '{$escaped_term}' \n                                    OR {$wpdb->posts}.post_content LIKE '{$escaped_term}' \n                                    OR u.user_login LIKE '{$escaped_term}' \n                                    OR u.user_email LIKE '{$escaped_term}') ";
                    }
                    return $search;
                },
                10,
                2
            );
            // Modify the WHERE clause to include the meta query for '_oum_location_key'
            add_filter( 'posts_where', function ( $where ) use($search_term) {
                global $wpdb;
                // Search in the _oum_location_key meta field
                $escaped_meta_value = '%' . $wpdb->esc_like( $search_term ) . '%';
                $where .= $wpdb->prepare( " OR ({$wpdb->postmeta}.meta_key = '_oum_location_key' AND {$wpdb->postmeta}.meta_value LIKE %s)", $escaped_meta_value );
                return $where;
            } );
            // Group results by post ID to avoid duplicates from multiple meta entries
            add_filter( 'posts_groupby', function ( $groupby ) {
                global $wpdb;
                if ( !$groupby ) {
                    $groupby = "{$wpdb->posts}.ID";
                    // Group by post ID to ensure unique results
                }
                return $groupby;
            } );
        }
    }

    public function add_pending_counter_to_menu() {
        global $menu;
        $count = count( get_posts( array(
            'post_type'      => 'oum-location',
            'post_status'    => 'pending',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) ) );
        $menu_item = wp_list_filter( $menu, array(
            2 => 'edit.php?post_type=oum-location',
        ) );
        if ( !empty( $menu_item ) && $count >= 1 ) {
            $menu_item_position = key( $menu_item );
            // get the array key (position) of the element
            $menu[$menu_item_position][0] .= ' <span class="awaiting-mod">' . $count . '</span>';
        }
    }

    /**
     * Get a value from a location
     */
    public function get_location_value( $attr, $post_id, $raw = false ) {
        $location = get_post_meta( $post_id, '_oum_location_key', true );
        // Early return if no valid location data
        if ( !is_array( $location ) ) {
            return '';
        }
        $custom_field_ids = get_option( 'oum_custom_fields', array() );
        // get all available custom fields
        $types = get_terms( array(
            'taxonomy'   => 'oum-type',
            'hide_empty' => false,
        ) );
        // get all available types
        $value = '';
        if ( $attr == 'title' ) {
            // GET TITLE
            $value = get_the_title( $post_id );
        } elseif ( $attr == 'image' || $attr == 'images' ) {
            // GET IMAGES
            $image = get_post_meta( $post_id, '_oum_location_image', true );
            $has_image = ( isset( $image ) && $image != '' ? 'has-image' : '' );
            if ( $has_image ) {
                $images = explode( '|', $image );
                if ( count( $images ) > 1 && !$raw ) {
                    // Enqueue carousel script and styles
                    wp_enqueue_style(
                        'oum_frontend_css',
                        plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/frontend.css',
                        array(),
                        $this->plugin_version
                    );
                    wp_enqueue_script(
                        'oum_frontend_carousel_js',
                        plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'src/js/frontend-carousel.js',
                        array(),
                        $this->plugin_version
                    );
                    // Multiple images - use carousel
                    $value = '<div class="oum-carousel">';
                    $value .= '<div class="oum-carousel-inner">';
                    foreach ( $images as $index => $image_url ) {
                        if ( !empty( $image_url ) ) {
                            // Convert relative path to absolute URL if needed
                            $absolute_image_url = ( strpos( $image_url, 'http' ) !== 0 ? site_url() . $image_url : $image_url );
                            $active_class = ( $index === 0 ? ' active' : '' );
                            $value .= '<div class="oum-carousel-item' . $active_class . '">';
                            $value .= '<img class="skip-lazy" src="' . esc_url_raw( $absolute_image_url ) . '">';
                            $value .= '</div>';
                        }
                    }
                    $value .= '</div>';
                    $value .= '</div>';
                } else {
                    // Single image or raw output
                    if ( !$raw ) {
                        // Convert relative path to absolute URL if needed
                        $absolute_image_url = ( strpos( $images[0], 'http' ) !== 0 ? site_url() . $images[0] : $images[0] );
                        $value = '<img src="' . esc_attr( $absolute_image_url ) . '">';
                    } else {
                        // For raw output (like CSV export), ensure all URLs are relative
                        $relative_urls = array();
                        foreach ( $images as $url ) {
                            if ( !empty( $url ) ) {
                                // If it's an absolute URL from this site, convert to relative
                                if ( strpos( $url, 'http' ) === 0 ) {
                                    // Convert absolute URL to relative path
                                    $site_url = site_url();
                                    if ( strpos( $url, $site_url ) === 0 ) {
                                        $url = str_replace( $site_url, '', $url );
                                    }
                                }
                                $relative_urls[] = $url;
                            }
                        }
                        $value = implode( '|', $relative_urls );
                    }
                }
            } else {
                $value = '';
            }
        } elseif ( $attr == 'audio' ) {
            // GET AUDIO
            $audio = get_post_meta( $post_id, '_oum_location_audio', true );
            $has_audio = ( isset( $audio ) && $audio != '' ? 'has-audio' : '' );
            if ( !$raw ) {
                // Convert relative path to absolute URL if needed for display
                $audio_url = ( isset( $audio ) && $audio != '' && strpos( $audio, 'http' ) !== 0 ? site_url() . $audio : $audio );
                $value = ( $has_audio ? '<audio controls="controls" style="width:100%"><source type="audio/mp4" src="' . esc_attr( $audio_url ) . '"><source type="audio/mpeg" src="' . esc_attr( $audio_url ) . '"><source type="audio/wav" src="' . esc_attr( $audio_url ) . '"></audio>' : '' );
            } else {
                // For raw output (like CSV export), ensure the URL is relative
                if ( $has_audio ) {
                    // If it's an absolute URL from this site, convert to relative
                    if ( strpos( $audio, 'http' ) === 0 ) {
                        // Convert absolute URL to relative path
                        $site_url = site_url();
                        if ( strpos( $audio, $site_url ) === 0 ) {
                            $audio = str_replace( $site_url, '', $audio );
                        }
                    }
                    $value = esc_attr( $audio );
                } else {
                    $value = '';
                }
            }
        } elseif ( $attr == 'video' ) {
            // GET VIDEO
            $video = $location['video'];
            $has_video = ( isset( $video ) && $video != '' ? 'has-video' : '' );
            $video_tag = ( $has_video ? apply_filters( 'the_content', esc_attr( $video ) ) : '' );
            if ( !$raw ) {
                $value = ( $has_video ? $video_tag : '' );
            } else {
                $value = ( $has_video ? esc_attr( $video ) : '' );
            }
        } elseif ( $attr == 'votes' ) {
            // GET VOTES
            $votes = ( isset( $location['votes'] ) ? intval( $location['votes'] ) : 0 );
            $value = $votes;
        } elseif ( $attr == 'type' ) {
            // GET TYPE
            $location_types = ( get_the_terms( $post_id, 'oum-type' ) && !is_wp_error( get_the_terms( $post_id, 'oum-type' ) ) ? get_the_terms( $post_id, 'oum-type' ) : false );
            if ( isset( $location_types ) && is_array( $location_types ) && count( $location_types ) == 1 && !get_option( 'oum_enable_multiple_marker_types' ) ) {
                $value = $location_types[0]->name;
            } else {
                $value = '';
                if ( isset( $location_types ) && is_array( $location_types ) ) {
                    $value = implode( '|', wp_list_pluck( $location_types, 'name' ) );
                }
            }
        } elseif ( $attr == 'map' ) {
            // GET MAP
            $plugin_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) );
            $map_style = ( get_option( 'oum_map_style' ) ? get_option( 'oum_map_style' ) : 'Esri.WorldStreetMap' );
            $oum_tile_provider_mapbox_key = get_option( 'oum_tile_provider_mapbox_key', '' );
            $lat = $location['lat'];
            $lng = $location['lng'];
            $zoom = ( isset( $location['zoom'] ) ? $location['zoom'] : '16' );
            // Get location types
            $location_types = ( get_the_terms( $post_id, 'oum-type' ) && !is_wp_error( get_the_terms( $post_id, 'oum-type' ) ) ? get_the_terms( $post_id, 'oum-type' ) : false );
            // Determine marker icon based on location type or settings
            if ( isset( $location_types ) && is_array( $location_types ) && count( $location_types ) == 1 && !get_option( 'oum_enable_multiple_marker_types' ) ) {
                $type = $location_types[0];
                if ( $type->term_id && get_term_meta( $type->term_id, 'oum_marker_icon', true ) ) {
                    // Get marker icon from location type
                    $marker_icon = get_term_meta( $type->term_id, 'oum_marker_icon', true );
                    $marker_user_icon = get_term_meta( $type->term_id, 'oum_marker_user_icon', true );
                } else {
                    // Get marker icon from settings
                    $marker_icon = ( get_option( 'oum_marker_icon' ) ? get_option( 'oum_marker_icon' ) : 'default' );
                    $marker_user_icon = get_option( 'oum_marker_user_icon' );
                }
            } else {
                // Get marker icon from settings
                $marker_icon = ( get_option( 'oum_marker_icon' ) ? get_option( 'oum_marker_icon' ) : 'default' );
                $marker_user_icon = get_option( 'oum_marker_user_icon' );
            }
            // Set marker icon URL
            $marker_icon_url = ( $marker_icon == 'user1' && $marker_user_icon ? esc_url( $marker_user_icon ) : esc_url( $plugin_url ) . 'src/leaflet/images/marker-icon_' . esc_attr( $marker_icon ) . '-2x.png' );
            $marker_shadow_url = esc_url( $plugin_url ) . 'src/leaflet/images/marker-shadow.png';
            $value = '<div id="mapRenderLocation" data-lat="' . $lat . '" data-lng="' . $lng . '" data-zoom="' . $zoom . '" data-mapstyle="' . $map_style . '" data-tile_provider_mapbox_key="' . $oum_tile_provider_mapbox_key . '" data-marker_icon_url="' . $marker_icon_url . '" data-marker_shadow_url="' . $marker_shadow_url . '" class="open-user-map-location-map leaflet-map map-style_' . $map_style . '"></div>';
        } elseif ( $attr == 'route' ) {
            // GET GOOGLE ROUTE LINK
            $lat = esc_attr( $location['lat'] );
            $lng = esc_attr( $location['lng'] );
            $text = ( $location['address'] ? $location['address'] : __( 'Route on Google Maps', 'open-user-map' ) );
            $value = '<a title="' . __( 'go to Google Maps', 'open-user-map' ) . '" href="https://www.google.com/maps/search/?api=1&amp;query=' . $lat . '%2C' . $lng . '" target="_blank">' . $text . '</a>';
        } elseif ( $attr == 'wp_author_id' ) {
            // GET AUTHOR ID
            $value = get_post_field( 'post_author', $post_id );
        } elseif ( isset( $location[$attr] ) ) {
            // GET DEFAULT FIELD
            $value = $location[$attr];
        } else {
            // GET CUSTOM FIELD
            foreach ( $custom_field_ids as $custom_field_id => $custom_field ) {
                if ( strtolower( $custom_field['label'] ) == strtolower( $attr ) && isset( $location['custom_fields'][$custom_field_id] ) ) {
                    $value = $location['custom_fields'][$custom_field_id];
                    break;
                }
            }
        }
        if ( !$raw ) {
            //change array to list
            if ( is_array( $value ) ) {
                $value = implode( ', ', $value );
            }
        }
        return $value;
    }

    // Add a custom header to the location single page
    public function default_location_header(
        $featured_image_html,
        $post_id,
        $post_thumbnail_id,
        $size,
        $attr
    ) {
        if ( is_singular( 'oum-location' ) && in_the_loop() && is_main_query() ) {
            $location = get_post_meta( $post_id, '_oum_location_key', true );
            if ( isset( $location['video'] ) && $location['video'] != '' ) {
                $featured_image_html = '<div class="open-user-map-single-default-template-media has-video">' . apply_filters( 'the_content', esc_attr( $location['video'] ) ) . '</div>';
            } else {
                $featured_image_html = '<div class="open-user-map-single-default-template-media">' . $featured_image_html . '</div>';
            }
        }
        return $featured_image_html;
    }

    // Add custom content to the location single page
    public function default_location_content( $content ) {
        // Check if we're inside the main loop in a single Post of type 'custom_post_type'.
        if ( is_singular( 'oum-location' ) && in_the_loop() && is_main_query() ) {
            // Check if the content is empty
            if ( empty( trim( $content ) ) ) {
                // Custom content to display if the original content is empty
                $custom_content = '
                <!-- wp:group {"className":"open-user-map-single-default-template","layout":{"type":"default"}} -->
                <div class="wp-block-group open-user-map-single-default-template">
                
                    <!-- wp:columns -->
                    <div class="wp-block-columns">
                    
                        <!-- wp:column {"width":"66.66%"} -->
                        <div class="wp-block-column" style="flex-basis:66.66%">

                            <!-- wp:shortcode -->
                            [open-user-map-location value="image"]
                            <!-- /wp:shortcode -->

                            <!-- wp:shortcode -->
                            [open-user-map-location value="text"]
                            <!-- /wp:shortcode -->
                        
                        </div>

                        <!-- /wp:column -->

                        <!-- wp:column {"width":"33.33%"} -->
                        <div class="wp-block-column" style="flex-basis:33.33%">

                            <!-- wp:shortcode -->
                            [open-user-map-location value="map"]
                            <!-- /wp:shortcode -->

                            <!-- wp:shortcode -->
                            [open-user-map-location value="route"]
                            <!-- /wp:shortcode -->

                            <!-- wp:shortcode -->
                            [open-user-map-location value="type"]
                            <!-- /wp:shortcode -->

                        </div>
                        <!-- /wp:column -->
                    </div>
                    <!-- /wp:columns -->
                </div>
                <!-- /wp:group -->
                ';
                // Apply filter to allow users to override the custom content
                $filtered_content = apply_filters( 'oum_default_location_content', $custom_content, get_the_ID() );
                // Return filtered content if not empty, otherwise return original content
                return ( !empty( trim( $filtered_content ) ) ? $filtered_content : $content );
            }
        }
        // Return the original content if it's not empty or if the conditions are not met
        return $content;
    }

}
