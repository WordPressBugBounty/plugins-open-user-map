<?php

// Settings
$oum_enable_scrollwheel_zoom_map = ( get_option( 'oum_enable_scrollwheel_zoom_map' ) === 'on' ? 'true' : 'false' );
$oum_enable_regions = get_option( 'oum_enable_regions' );
$oum_regions_layout_style = get_option( 'oum_regions_layout_style', 'layout-1' );
$oum_enable_cluster = ( get_option( 'oum_enable_cluster', 'on' ) === 'on' ? 'true' : 'false' );
$oum_enable_fullscreen = ( get_option( 'oum_enable_fullscreen', 'on' ) === 'on' ? 'true' : 'false' );
$oum_enable_gmaps_link = get_option( 'oum_enable_gmaps_link', 'on' );
$oum_max_image_filesize = ( !empty( get_option( 'oum_max_image_filesize' ) ) ? get_option( 'oum_max_image_filesize' ) : 10 );
$map_style = ( get_option( 'oum_map_style' ) ? get_option( 'oum_map_style' ) : 'Esri.WorldStreetMap' );
$oum_tile_provider_mapbox_key = get_option( 'oum_tile_provider_mapbox_key', '' );
$marker_icon = ( get_option( 'oum_marker_icon' ) ? get_option( 'oum_marker_icon' ) : 'default' );
$marker_user_icon = get_option( 'oum_marker_user_icon' );
$map_size = get_option( 'oum_map_size' );
$oum_map_height = get_option( 'oum_map_height' );
$oum_map_height_mobile = get_option( 'oum_map_height_mobile' );
$oum_action_after_submit = get_option( 'oum_action_after_submit' );
$thankyou_headline = get_option( 'oum_thankyou_headline' );
$thankyou_text = get_option( 'oum_thankyou_text' );
$thankyou_redirect = get_option( 'oum_thankyou_redirect' );
$oum_enable_add_location = get_option( 'oum_enable_add_location', 'on' );
$oum_enable_user_notification = get_option( 'oum_enable_user_notification' );
$text_notify_me_on_publish_label = ( get_option( 'oum_user_notification_label' ) ? get_option( 'oum_user_notification_label' ) : $this->oum_get_default_label( 'user_notification' ) );
$text_notify_me_on_publish_name = __( 'Your name', 'open-user-map' );
$text_notify_me_on_publish_email = __( 'Your email', 'open-user-map' );
$oum_enable_currentlocation = ( get_option( 'oum_enable_currentlocation' ) ? 'true' : 'false' );
$oum_disable_oum_attribution = get_option( 'oum_disable_oum_attribution' );
$oum_collapse_filter = ( get_option( 'oum_collapse_filter' ) ? 'use-collapse' : 'active' );
$oum_enable_toggle_all_categories = get_option( 'oum_enable_toggle_all_categories', 'off' );
$oum_ui_color = ( get_option( 'oum_ui_color' ) ? get_option( 'oum_ui_color' ) : $this->oum_ui_color_default );
$oum_plus_button_label = ( get_option( 'oum_plus_button_label' ) ? get_option( 'oum_plus_button_label' ) : __( 'Add location', 'open-user-map' ) );
$oum_marker_types_label = ( get_option( 'oum_marker_types_label' ) ? get_option( 'oum_marker_types_label' ) : $this->oum_get_default_label( 'marker_types' ) );
$oum_title_label = ( get_option( 'oum_title_label' ) ? get_option( 'oum_title_label' ) : $this->oum_get_default_label( 'title' ) );
$oum_map_label = ( get_option( 'oum_map_label' ) ? get_option( 'oum_map_label' ) : $this->oum_get_default_label( 'map' ) );
$oum_address_label = ( get_option( 'oum_address_label' ) ? get_option( 'oum_address_label' ) : $this->oum_get_default_label( 'address' ) );
$oum_description_label = ( get_option( 'oum_description_label' ) ? get_option( 'oum_description_label' ) : $this->oum_get_default_label( 'description' ) );
$oum_upload_media_label = ( get_option( 'oum_upload_media_label' ) ? get_option( 'oum_upload_media_label' ) : $this->oum_get_default_label( 'upload_media' ) );
$oum_enable_fixed_map_bounds = get_option( 'oum_enable_fixed_map_bounds' );
$oum_enable_multiple_marker_types = ( get_option( 'oum_enable_multiple_marker_types', false ) ? 'true' : 'false' );
$oum_enable_searchbar = ( get_option( 'oum_enable_searchbar', 'on' ) === 'on' ? 'true' : 'false' );
$oum_searchbar_type = ( get_option( 'oum_searchbar_type' ) ? get_option( 'oum_searchbar_type' ) : 'address' );
$oum_enable_searchmarkers_button = ( get_option( 'oum_enable_searchmarkers_button', 'on' ) === 'on' ? 'true' : 'false' );
$oum_searchmarkers_label = ( get_option( 'oum_searchmarkers_label' ) ? get_option( 'oum_searchmarkers_label' ) : $this->oum_get_default_label( 'searchmarkers' ) );
$oum_searchmarkers_zoom = ( get_option( 'oum_searchmarkers_zoom' ) ? get_option( 'oum_searchmarkers_zoom' ) : $this->oum_searchmarkers_zoom_default );
$oum_geosearch_provider = ( get_option( 'oum_geosearch_provider' ) ? get_option( 'oum_geosearch_provider' ) : 'osm' );
$oum_geosearch_provider_geoapify_key = get_option( 'oum_geosearch_provider_geoapify_key', '' );
$oum_geosearch_provider_here_key = get_option( 'oum_geosearch_provider_here_key', '' );
$oum_geosearch_provider_mapbox_key = get_option( 'oum_geosearch_provider_mapbox_key', '' );
$oum_enable_searchaddress_button = ( get_option( 'oum_enable_searchaddress_button', 'on' ) === 'on' ? 'true' : 'false' );
$oum_searchaddress_label = ( get_option( 'oum_searchaddress_label' ) ? get_option( 'oum_searchaddress_label' ) : $this->oum_get_default_label( 'searchaddress' ) );
$oum_custom_js = get_option( 'oum_custom_js' );
$oum_location_date_type = get_option( 'oum_location_date_type', 'modified' );
// Custom Attribute: Map Size
if ( isset( $block_attributes['size'] ) && $block_attributes['size'] != '' ) {
    $map_size = $block_attributes['size'];
}
// Custom Attribute: Map Style
if ( isset( $block_attributes['map_style'] ) && $block_attributes['map_style'] != '' ) {
    $map_style = $block_attributes['map_style'];
}
// Custom Attribute: Height
if ( isset( $block_attributes['height'] ) && $block_attributes['height'] != '' ) {
    $oum_map_height = $block_attributes['height'];
}
// Custom Attribute: Height (Mobile)
if ( isset( $block_attributes['height_mobile'] ) && $block_attributes['height_mobile'] != '' ) {
    $oum_map_height_mobile = $block_attributes['height_mobile'];
}
// Custom Attribute: Clustering (true|false)
if ( isset( $block_attributes['enable_cluster'] ) && $block_attributes['enable_cluster'] != '' ) {
    $oum_enable_cluster = $block_attributes['enable_cluster'];
}
// Custom Attribute: Map Type (interactive|simple)
if ( isset( $block_attributes['map_type'] ) && $block_attributes['map_type'] != '' ) {
    $oum_enable_add_location = ( $block_attributes['map_type'] == 'interactive' ? 'on' : '' );
}
// Custom Attribute: Fullscreen (true|false)
if ( isset( $block_attributes['enable_fullscreen'] ) && $block_attributes['enable_fullscreen'] != '' ) {
    $oum_enable_fullscreen = $block_attributes['enable_fullscreen'];
}
// Custom Attribute: Searchbar (true|false)
if ( isset( $block_attributes['enable_searchbar'] ) && $block_attributes['enable_searchbar'] != '' ) {
    $oum_enable_searchbar = $block_attributes['enable_searchbar'];
}
// Custom Attribute: Search Address Button (true|false)
if ( isset( $block_attributes['enable_searchaddress_button'] ) && $block_attributes['enable_searchaddress_button'] != '' ) {
    $oum_enable_searchaddress_button = $block_attributes['enable_searchaddress_button'];
}
// Custom Attribute: Search Markers Button (true|false)
if ( isset( $block_attributes['enable_searchmarkers_button'] ) && $block_attributes['enable_searchmarkers_button'] != '' ) {
    $oum_enable_searchmarkers_button = $block_attributes['enable_searchmarkers_button'];
}
// Custom Attribute: Current Location Button (true|false)
if ( isset( $block_attributes['enable_currentlocation'] ) && $block_attributes['enable_currentlocation'] != '' ) {
    $oum_enable_currentlocation = $block_attributes['enable_currentlocation'];
}
// Custom Attribute: Disable Regions (true|false)
if ( isset( $block_attributes['disable_regions'] ) && $block_attributes['disable_regions'] != '' ) {
    $oum_enable_regions = ( $block_attributes['disable_regions'] == 'true' ? '' : $oum_enable_regions );
}
if ( $oum_enable_regions == 'on' ) {
    // Taxonomy: Regions
    $regions = get_terms( array(
        'taxonomy'   => 'oum-region',
        'hide_empty' => false,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'     => 'oum_lat',
                'compare' => 'EXISTS',
            ),
            array(
                'key'     => 'oum_lng',
                'compare' => 'EXISTS',
            ),
            array(
                'key'     => 'oum_zoom',
                'compare' => 'EXISTS',
            ),
        ),
    ) );
}
// Taxonomy: Types (Marker Categories)
$types = get_terms( array(
    'taxonomy'   => 'oum-type',
    'hide_empty' => false,
) );
if ( is_wp_error( $types ) || empty( $types ) ) {
    $types = false;
}
$query = array(
    'post_type'        => 'oum-location',
    'posts_per_page'   => -1,
    'suppress_filters' => false,
);
// Custom Attribute: Filter for types
if ( isset( $block_attributes['types'] ) && $block_attributes['types'] != '' ) {
    $selected_types_slugs = explode( '|', $block_attributes['types'] );
    // Check for attribute 'types-relation' and set relation accordingly
    $types_relation = ( isset( $block_attributes['types-relation'] ) && strtoupper( $block_attributes['types-relation'] ) === 'AND' ? 'AND' : 'OR' );
    if ( $types_relation === 'AND' ) {
        // Build tax_query with relation AND (all types must match)
        $tax_query = array(
            'relation' => 'AND',
        );
        foreach ( $selected_types_slugs as $slug ) {
            $tax_query[] = array(
                'taxonomy' => 'oum-type',
                'field'    => 'slug',
                'terms'    => $slug,
            );
        }
        $query['tax_query'] = $tax_query;
    } else {
        // Default: OR (any of the types)
        $query['tax_query'] = array(array(
            'taxonomy' => 'oum-type',
            'field'    => 'slug',
            'terms'    => $selected_types_slugs,
        ));
    }
    //overwrite types with filtered types
    $types = [];
    foreach ( $selected_types_slugs as $slug ) {
        $types[] = get_term_by( 'slug', $slug, 'oum-type' );
    }
}
// Custom Attribute: Filter for ids
if ( isset( $block_attributes['ids'] ) && $block_attributes['ids'] != '' ) {
    $selected_ids = explode( '|', $block_attributes['ids'] );
    $query['post__in'] = $selected_ids;
}
// Custom Attribute: Pre-select region
if ( isset( $regions ) && isset( $block_attributes['region'] ) && $block_attributes['region'] != '' ) {
    $oum_start_region_name = $block_attributes['region'];
    $regions_filtered = array_filter( $regions, function ( $obj ) use($oum_start_region_name) {
        return $obj->name == $oum_start_region_name;
    } );
    if ( !empty( $regions_filtered ) ) {
        $oum_start_region = current( $regions_filtered );
    }
}
// Instead of get_posts(), use WP_Query to get all post data at once
$locations_query = new WP_Query($query);
$posts = $locations_query->posts;
// Get all post meta in a single query
$post_ids = wp_list_pluck( $posts, 'ID' );
$all_meta = array();
if ( !empty( $post_ids ) ) {
    global $wpdb;
    $all_meta = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_key, meta_value \n      FROM {$wpdb->postmeta} \n      WHERE post_id IN (" . implode( ',', array_fill( 0, count( $post_ids ), '%d' ) ) . ")\n      AND meta_key IN ('_oum_location_key', '_oum_location_image', '_oum_location_audio')", $post_ids ) );
}
// Index meta values by post_id and meta_key for faster lookup
$indexed_meta = array();
foreach ( $all_meta as $meta ) {
    if ( !isset( $indexed_meta[$meta->post_id] ) ) {
        $indexed_meta[$meta->post_id] = array();
    }
    $indexed_meta[$meta->post_id][$meta->meta_key] = $meta->meta_value;
}
/**
 * Preload all attachment IDs and their URLs at once (for media library images)
 *  */
global $wpdb;
// 1. Collect all image URLs from locations
$image_urls_needed = [];
foreach ( $posts as $post ) {
    $post_id = $post->ID;
    $image = ( isset( $indexed_meta[$post_id]['_oum_location_image'] ) ? $indexed_meta[$post_id]['_oum_location_image'] : '' );
    if ( $image ) {
        $image_urls_needed = array_merge( $image_urls_needed, explode( '|', $image ) );
    }
}
$image_urls_needed = array_unique( $image_urls_needed );
// 2. Filter out only media library images, ignore 'oum-useruploads'
$uploads_info = wp_upload_dir();
$uploads_baseurl = $uploads_info['baseurl'];
// Array of image URLs (input) — build the filenames we need
$filenames_needed = [];
foreach ( $image_urls_needed as $url ) {
    // Skip if it's a frontend user upload (not a media library attachment)
    if ( strpos( $url, 'oum-useruploads' ) !== false ) {
        continue;
    }
    // Check if the URL is a relative upload or an absolute upload from this site
    if ( strpos( $url, '/wp-content/uploads/' ) === 0 || strpos( $url, $uploads_baseurl ) === 0 ) {
        // Extract just the filename
        $filename = basename( $url );
        // Extract the filename without extension
        $filename_stem = pathinfo( $filename, PATHINFO_FILENAME );
        if ( $filename_stem ) {
            $filenames_needed[] = $filename_stem;
        }
    }
}
// If we have filenames to look for
if ( !empty( $filenames_needed ) ) {
    global $wpdb;
    // Build dynamic WHERE clause with safe prepared LIKE statements
    $likes = array_map( function ( $stem ) use($wpdb) {
        // Each LIKE: e.g., pm.meta_value LIKE '%683d9b1b1e7fd-1024x765%'
        return $wpdb->prepare( "pm.meta_value LIKE %s", '%' . $stem . '%' );
    }, $filenames_needed );
    // Build the SQL query
    $query = "\n        SELECT p.ID, pm.meta_value\n        FROM {$wpdb->posts} p\n        INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id)\n        WHERE p.post_type = 'attachment'\n        AND pm.meta_key = '_wp_attached_file'\n        AND (" . implode( ' OR ', $likes ) . ")\n    ";
    // Execute query and fetch results
    $attachments = $wpdb->get_results( $query );
    // Build lookup map: URL (relative and absolute) => attachment ID
    $image_url_to_id = [];
    foreach ( $attachments as $attachment ) {
        // Build relative URL (e.g., /wp-content/uploads/2025/06/file.jpg)
        $relative_url = '/wp-content/uploads/' . ltrim( $attachment->meta_value, '/' );
        // Build absolute URL (e.g., https://yoursite.com/wp-content/uploads/2025/06/file.jpg)
        $absolute_url = $uploads_baseurl . '/' . ltrim( $attachment->meta_value, '/' );
        // Map both relative and absolute URL to the attachment ID
        $image_url_to_id[$relative_url] = (int) $attachment->ID;
        $image_url_to_id[$absolute_url] = (int) $attachment->ID;
    }
}
// Get active custom fields once
$active_custom_fields = get_option( 'oum_custom_fields' );
$locations_list = array();
foreach ( $posts as $post ) {
    $post_id = $post->ID;
    // Get all meta values for this post from our indexed array
    $location_meta = ( isset( $indexed_meta[$post_id]['_oum_location_key'] ) ? maybe_unserialize( $indexed_meta[$post_id]['_oum_location_key'] ) : array() );
    if ( !isset( $location_meta['lat'] ) || !isset( $location_meta['lng'] ) ) {
        continue;
    }
    $name = str_replace( "'", "\\'", strip_tags( $post->post_title ) );
    $address = ( isset( $location_meta['address'] ) ? str_replace( "'", "\\'", preg_replace( '/\\r|\\n/', '', $location_meta['address'] ) ) : '' );
    $text = ( isset( $location_meta["text"] ) ? str_replace( "'", "\\'", str_replace( array("\r\n", "\r", "\n"), "<br>", $location_meta["text"] ) ) : '' );
    $video = ( isset( $location_meta["video"] ) ? $location_meta["video"] : '' );
    $image = ( isset( $indexed_meta[$post_id]['_oum_location_image'] ) ? $indexed_meta[$post_id]['_oum_location_image'] : '' );
    $image_thumb = null;
    // Handle multiple images
    $images = array();
    if ( $image ) {
        $image_urls = explode( '|', $image );
        foreach ( $image_urls as $url ) {
            if ( stristr( $url, 'oum-useruploads' ) ) {
                // Handle user uploads - always use original image
                $images[] = $url;
            } else {
                // Handle media library images
                $image_id = ( isset( $image_url_to_id[$url] ) ? $image_url_to_id[$url] : 0 );
                if ( $image_id > 0 ) {
                    $thumb = wp_get_attachment_image_url( $image_id, 'medium_large' );
                    $images[] = ( $thumb ? $thumb : $url );
                } else {
                    $images[] = $url;
                }
            }
        }
    }
    // Convert to absolute URLs for JavaScript display
    $absolute_images = array_map( function ( $url ) {
        // Convert relative path to absolute URL if needed
        return ( strpos( $url, 'http' ) !== 0 ? site_url() . $url : $url );
    }, $images );
    $audio = ( isset( $indexed_meta[$post_id]['_oum_location_audio'] ) ? $indexed_meta[$post_id]['_oum_location_audio'] : '' );
    // Convert audio to absolute URL if needed
    $absolute_audio = ( isset( $audio ) && $audio != '' && strpos( $audio, 'http' ) !== 0 ? site_url() . $audio : $audio );
    // Optimized custom fields processing
    $custom_fields = [];
    $meta_custom_fields = ( isset( $location_meta['custom_fields'] ) ? $location_meta['custom_fields'] : false );
    if ( is_array( $meta_custom_fields ) && is_array( $active_custom_fields ) ) {
        // Iterate over active_custom_fields to maintain order
        foreach ( $active_custom_fields as $index => $custom_field ) {
            // Skip if field is marked as private
            if ( isset( $custom_field['private'] ) ) {
                continue;
            }
            // Skip if no value exists for this field
            if ( !isset( $meta_custom_fields[$index] ) ) {
                continue;
            }
            $custom_fields[] = [
                'index'                => $index,
                'label'                => $custom_field['label'],
                'val'                  => $meta_custom_fields[$index],
                'fieldtype'            => ( isset( $custom_field['fieldtype'] ) ? $custom_field['fieldtype'] : 'text' ),
                'uselabelastextoption' => ( isset( $custom_field['uselabelastextoption'] ) ? $custom_field['uselabelastextoption'] : false ),
            ];
        }
    }
    // Determine marker icon based on number of categories (types)
    $multi_icon = ( get_option( 'oum_marker_multicategories_icon' ) ? get_option( 'oum_marker_multicategories_icon' ) : $this->oum_marker_multicategories_icon_default );
    // Helper function to get default icon based on settings
    $get_default_icon = function () use($marker_icon, $marker_user_icon) {
        if ( $marker_icon == 'user1' && $marker_user_icon ) {
            return esc_url( $marker_user_icon );
        } elseif ( $marker_icon ) {
            return esc_url( $this->plugin_url ) . 'src/leaflet/images/marker-icon_' . esc_attr( $marker_icon ) . '-2x.png';
        } else {
            return esc_url( $this->plugin_url ) . 'src/leaflet/images/marker-icon_default-2x.png';
        }
    };
    if ( isset( $location_types ) && is_array( $location_types ) ) {
        if ( count( $location_types ) > 1 ) {
            // Multiple categories: use multi-categories icon
            $icon = esc_url( $multi_icon );
        } elseif ( count( $location_types ) === 1 ) {
            // Single category: use that category's icon if set, else default
            $type = $location_types[0];
            $cat_icon = get_term_meta( $type->term_id, 'oum_marker_icon', true );
            $cat_user_icon = get_term_meta( $type->term_id, 'oum_marker_user_icon', true );
            if ( $cat_icon == 'user1' && $cat_user_icon ) {
                $icon = esc_url( $cat_user_icon );
            } elseif ( $cat_icon ) {
                $icon = esc_url( $this->plugin_url ) . 'src/leaflet/images/marker-icon_' . esc_attr( $cat_icon ) . '-2x.png';
            } else {
                // Use default marker icon from settings
                $icon = $get_default_icon();
            }
        } else {
            // No category: use default marker icon from settings
            $icon = $get_default_icon();
        }
    } else {
        // No category: use default marker icon from settings
        $icon = $get_default_icon();
    }
    // Date: modified or published
    if ( $oum_location_date_type == 'created' ) {
        $date = get_the_date( '', $post_id );
    } else {
        $date = get_the_modified_date( '', $post_id );
    }
    // collect locations for JS use
    $location = array(
        'post_id'       => $post_id,
        'date'          => $date,
        'name'          => $name,
        'address'       => $address,
        'lat'           => $location_meta['lat'],
        'lng'           => $location_meta['lng'],
        'zoom'          => ( isset( $location_meta['zoom'] ) ? $location_meta['zoom'] : '16' ),
        'text'          => $text,
        'images'        => $absolute_images,
        'audio'         => $absolute_audio,
        'video'         => $video,
        'icon'          => $icon,
        'custom_fields' => $custom_fields,
        'author_id'     => get_post_field( 'post_author', $post_id ),
        'votes'         => ( isset( $location_meta['votes'] ) ? intval( $location_meta['votes'] ) : 0 ),
    );
    if ( isset( $location_types ) && is_array( $location_types ) && count( $location_types ) > 0 ) {
        foreach ( $location_types as $term ) {
            $location['types'][] = (string) $term->term_taxonomy_id;
        }
    }
    $locations_list[] = $location;
}
$oum_use_settings_start_location = 'false';
// Set focus for map init
if ( isset( $block_attributes['lat'] ) && $block_attributes['lat'] != '' && isset( $block_attributes['long'] ) && $block_attributes['long'] != '' && isset( $block_attributes['zoom'] ) && $block_attributes['zoom'] != '' ) {
    //get lat, long, zoom from shortcode attributes
    $start_lat = str_replace( ',', '.', $block_attributes['lat'] );
    $start_lng = str_replace( ',', '.', $block_attributes['long'] );
    $start_zoom = str_replace( ',', '.', $block_attributes['zoom'] );
} elseif ( isset( $oum_start_region ) && $oum_start_region != '' ) {
    //get region from shortcode attribute
    $start_lat = get_term_meta( $oum_start_region->term_id, 'oum_lat', true );
    $start_lng = get_term_meta( $oum_start_region->term_id, 'oum_lng', true );
    $start_zoom = get_term_meta( $oum_start_region->term_id, 'oum_zoom', true );
} elseif ( get_option( 'oum_start_lat' ) && get_option( 'oum_start_lng' ) && get_option( 'oum_start_zoom' ) ) {
    //get from settings
    $oum_use_settings_start_location = 'true';
    $start_lat = get_option( 'oum_start_lat' );
    $start_lng = get_option( 'oum_start_lng' );
    $start_zoom = get_option( 'oum_start_zoom' );
} elseif ( count( $locations_list ) == 1 ) {
    //get from single location
    $start_lat = $locations_list[0]['lat'];
    $start_lng = $locations_list[0]['lng'];
    $start_zoom = '8';
} else {
    //default worldview
    $start_lat = '28';
    $start_lng = '0';
    $start_zoom = '1';
}
$i = 0;
// BUGFIX: resolves issue with non-unique ids when caching inline js with 3rd party plugins
// todo: allow multiple maps/shortcodes on same site
//$unique_id = uniqid();
$unique_id = 20210929;