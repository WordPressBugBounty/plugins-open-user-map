<?php

$oum_all_locations = [];
/**
 * Clean UTF-8 encoding for location data
 */
if ( !function_exists( 'clean_utf8' ) ) {
    function clean_utf8(  $value  ) {
        if ( is_array( $value ) ) {
            return array_map( 'clean_utf8', $value );
        } elseif ( is_string( $value ) ) {
            return mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
            // Re-encode to valid UTF-8
        } else {
            return $value;
        }
    }

}
foreach ( $locations_list as $location ) {
    if ( get_option( 'oum_enable_location_date' ) === 'on' ) {
        $date_tag = '<div class="oum_location_date">' . wp_kses_post( $location['date'] ) . '</div>';
    } else {
        $date_tag = '';
    }
    $name_tag = ( get_option( 'oum_enable_title', 'on' ) == 'on' ? '<h3 class="oum_location_name">' . esc_attr( $location['name'] ) . '</h3>' : '' );
    $media_tag = '';
    if ( isset( $location['images'] ) && !empty( $location['images'] ) ) {
        // Get the image size setting
        $oum_popup_image_size = ( get_option( 'oum_popup_image_size' ) ? get_option( 'oum_popup_image_size' ) : 'original' );
        $media_tag = '<div class="oum-carousel popup-image-size-' . esc_attr( $oum_popup_image_size ) . '">';
        $media_tag .= '<div class="oum-carousel-inner">';
        foreach ( $location['images'] as $index => $image_url ) {
            $active_class = ( $index === 0 ? ' active' : '' );
            $media_tag .= '<div class="oum-carousel-item' . $active_class . '">';
            $media_tag .= '<img class="skip-lazy" src="' . esc_url_raw( $image_url ) . '" alt="' . esc_attr( $location['name'] ) . '">';
            $media_tag .= '</div>';
        }
        $media_tag .= '</div>';
        $media_tag .= '</div>';
    }
    // HOOK: modify location image
    $media_tag = apply_filters( 'oum_location_bubble_image', $media_tag, $location );
    $audio_tag = ( $location['audio'] ? '<audio controls="controls" style="width:100%"><source type="audio/mp4" src="' . $location['audio'] . '"><source type="audio/mpeg" src="' . $location['audio'] . '"><source type="audio/wav" src="' . $location['audio'] . '"></audio>' : '' );
    $address_tag = '';
    if ( get_option( 'oum_enable_address', 'on' ) === 'on' ) {
        $address_tag = ( $location['address'] && !get_option( 'oum_hide_address' ) ? esc_attr( $location['address'] ) : '' );
        if ( $oum_enable_gmaps_link === 'on' && $address_tag ) {
            $address_tag = '<a title="' . __( 'go to Google Maps', 'open-user-map' ) . '" href="https://www.google.com/maps/search/?api=1&amp;query=' . esc_attr( $location['lat'] ) . '%2C' . esc_attr( $location['lng'] ) . '" target="_blank">' . $address_tag . '</a>';
        }
    }
    $address_tag = ( $address_tag != '' ? '<div class="oum_location_address">' . $address_tag . '</div>' : '' );
    if ( get_option( 'oum_enable_description', 'on' ) === 'on' ) {
        $description_tag = '<div class="oum_location_description">' . wp_kses_post( $location['text'] ) . '</div>';
    } else {
        $description_tag = '';
    }
    $custom_fields = '';
    if ( isset( $location['custom_fields'] ) && is_array( $location['custom_fields'] ) ) {
        $fields_html = [];
        foreach ( $location['custom_fields'] as $custom_field ) {
            if ( empty( $custom_field['val'] ) ) {
                continue;
            }
            $field_html = '<div class="oum_custom_field">';
            // Handle array values (like multiple select)
            if ( is_array( $custom_field['val'] ) ) {
                $values = array_map( function ( $x ) {
                    return '<span data-value="' . esc_attr( $x ) . '">' . esc_html( $x ) . '</span>';
                }, $custom_field['val'] );
                $field_html .= '<strong>' . esc_html( $custom_field['label'] ) . ':</strong> ' . implode( ' ', $values );
            } elseif ( strpos( $custom_field['val'], '|' ) !== false ) {
                $field_html .= '<strong>' . esc_html( $custom_field['label'] ) . ':</strong> ';
                $entries = array_map( 'trim', explode( '|', $custom_field['val'] ) );
                $formatted_entries = [];
                foreach ( $entries as $entry ) {
                    if ( filter_var( $entry, FILTER_VALIDATE_URL ) ) {
                        $formatted_entries[] = sprintf( '<a href="%s">%s</a>', esc_url( $entry ), esc_html( $entry ) );
                    } elseif ( $custom_field['fieldtype'] == 'email' && is_email( $entry ) ) {
                        $formatted_entries[] = sprintf( '<a target="_blank" href="mailto:%s">%s</a>', esc_attr( $entry ), esc_html( $entry ) );
                    } else {
                        $formatted_entries[] = sprintf( '<span data-value="%s">%s</span>', esc_attr( $entry ), esc_html( $entry ) );
                    }
                }
                $field_html .= implode( ' ', $formatted_entries );
            } else {
                $value = $custom_field['val'];
                if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
                    if ( !empty( $custom_field['uselabelastextoption'] ) ) {
                        $field_html .= sprintf( '<a href="%s">%s</a>', esc_url( $value ), esc_html( $custom_field['label'] ) );
                    } else {
                        $field_html .= sprintf(
                            '<strong>%s:</strong> <a href="%s">%s</a>',
                            esc_html( $custom_field['label'] ),
                            esc_url( $value ),
                            esc_html( $value )
                        );
                    }
                } elseif ( $custom_field['fieldtype'] == 'email' && is_email( $value ) ) {
                    $field_html .= sprintf(
                        '<strong>%s:</strong> <a target="_blank" href="mailto:%s">%s</a>',
                        esc_html( $custom_field['label'] ),
                        esc_attr( $value ),
                        esc_html( $value )
                    );
                } else {
                    $field_html .= sprintf(
                        '<strong>%s:</strong> <span data-value="%s">%s</span>',
                        esc_html( $custom_field['label'] ),
                        esc_attr( $value ),
                        esc_html( $value )
                    );
                }
            }
            $field_html .= '</div>';
            $fields_html[] = $field_html;
        }
        if ( !empty( $fields_html ) ) {
            $custom_fields = '<div class="oum_location_custom_fields">' . implode( '', $fields_html ) . '</div>';
        }
    }
    if ( get_option( 'oum_enable_single_page' ) ) {
        $link_tag = '<div class="oum_read_more"><a href="' . get_the_permalink( $location['post_id'] ) . '">' . __( 'Read more', 'open-user-map' ) . '</a></div>';
    } else {
        $link_tag = '';
    }
    // Determine if the current user is allowed to edit the location
    $has_general_permission = current_user_can( 'edit_oum-locations' );
    $is_author = get_current_user_id() == $location['author_id'];
    $can_edit_specific_post = current_user_can( 'edit_post', $location['post_id'] );
    $allow_edit = ( $has_general_permission && ($is_author || $can_edit_specific_post) ? true : false );
    // Add Edit button if the user is the owner or allowed to edit
    if ( $allow_edit ) {
        $edit_button = '<div title="' . __( 'Edit location', 'open-user-map' ) . '" class="edit-location-button" data-post-id="' . esc_attr( $location['post_id'] ) . '"></div>';
    } else {
        $edit_button = '';
    }
    // Add words that are not visible to the user but can be used for search
    $additional_search_meta = '<div style="display: none">' . get_post_field( 'post_name', $location['post_id'] ) . '</div>';
    // Add vote button if feature is enabled
    $vote_button = '';
    // building bubble block content
    $content = $media_tag;
    $content .= '<div class="oum_location_text">';
    $content .= $date_tag;
    $content .= $address_tag;
    $content .= $name_tag;
    $content .= $custom_fields;
    $content .= $description_tag;
    $content .= $audio_tag;
    $content .= '<div class="oum_location_text_bottom">' . $vote_button . $link_tag . '</div>';
    $content .= '</div>';
    $content .= $edit_button;
    $content .= $additional_search_meta;
    // removing backslash escape
    $content = str_replace( "\\", "", $content );
    // HOOK: modify location bubble content
    $content = apply_filters( 'oum_location_bubble_content', $content, $location );
    // set location
    $oum_location = [
        'title'         => html_entity_decode( esc_attr( $location['name'] ) ),
        'lat'           => esc_attr( $location["lat"] ),
        'lng'           => esc_attr( $location["lng"] ),
        'zoom'          => esc_attr( $location["zoom"] ),
        'content'       => $content,
        'icon'          => esc_attr( $location["icon"] ),
        'types'         => ( isset( $location["types"] ) ? $location["types"] : [] ),
        'post_id'       => esc_attr( $location["post_id"] ),
        'address'       => esc_attr( $location["address"] ),
        'text'          => wp_kses_post( $location["text"] ),
        'image'         => ( isset( $location['images'] ) && !empty( $location['images'] ) ? implode( '|', array_map( 'esc_url', $location['images'] ) ) : '' ),
        'audio'         => esc_url( $location["audio"] ),
        'video'         => esc_url( $location["video"] ),
        'custom_fields' => $location['custom_fields'],
        'votes'         => ( isset( $location['votes'] ) ? intval( $location['votes'] ) : 0 ),
    ];
    $oum_all_locations[] = $oum_location;
}
// Clean UTF-8 encoding for location data (Repair if needed)
$oum_all_locations_clean = clean_utf8( $oum_all_locations );
// Fixing height without unit
$oum_map_height = ( is_numeric( $oum_map_height ) ? $oum_map_height . 'px' : $oum_map_height );
$oum_map_height_mobile = ( is_numeric( $oum_map_height_mobile ) ? $oum_map_height_mobile . 'px' : $oum_map_height_mobile );
?>

<div class="box-wrap map-size-<?php 
echo esc_attr( $map_size );
?> <?php 
if ( $oum_enable_regions == 'on' && $regions && count( $regions ) > 0 ) {
    ?>oum-regions-<?php 
    echo $oum_regions_layout_style;
    ?> <?php 
}
?>">
  <?php 
if ( $oum_enable_regions == 'on' && $regions && count( $regions ) > 0 ) {
    ?>
    <div class="tab-wrap">
      <div class="oum-tabs" id="nav-tab-<?php 
    echo $unique_id;
    ?>" role="tablist">
        <?php 
    $i = 0;
    ?>
        <?php 
    foreach ( $regions as $region ) {
        ?>

          <?php 
        $i++;
        $name = $region->name;
        $t_id = $region->term_id;
        $term_lat = get_term_meta( $t_id, 'oum_lat', true );
        $term_lng = get_term_meta( $t_id, 'oum_lng', true );
        $term_zoom = get_term_meta( $t_id, 'oum_zoom', true );
        ?>
          <div class="nav-item nav-link <?php 
        echo ( isset( $oum_start_region_name ) && $name == $oum_start_region_name ? 'active' : '' );
        ?> change_region" data-lat="<?php 
        echo esc_attr( $term_lat );
        ?>" data-lng="<?php 
        echo esc_attr( $term_lng );
        ?>" data-zoom="<?php 
        echo esc_attr( $term_zoom );
        ?>" data-toggle="tab" role="tab"><?php 
        echo esc_html( $name );
        ?></div>

        <?php 
    }
    ?>
      </div>
    </div>
  <?php 
}
?>

  <div class="map-wrap">
    <div class="oum-loading-overlay">
      <div class="oum-loading-spinner"></div>
    </div>
    <div id="map-<?php 
echo $unique_id;
?>" class="leaflet-map map-style_<?php 
echo esc_attr( $map_style );
?>"></div>
    
    <?php 
if ( $oum_enable_searchbar === 'true' && $oum_searchbar_type == 'markers' ) {
    ?>
      <div id="oum_search_marker"></div>
    <?php 
}
?>

    <?php 
if ( $oum_enable_searchbar === 'true' && $oum_searchbar_type == 'live_filter' ) {
    ?>
      <input type="text" id="oum_filter_markers" class="oum-hidden" placeholder="<?php 
    echo esc_attr( $oum_searchmarkers_label );
    ?>" />
    <?php 
}
?>

    <?php 
if ( $oum_enable_add_location === 'on' ) {
    ?>
    
      <?php 
    ?>

      <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>

        <div id="open-add-location-overlay" class="open-add-location-overlay oum-hidden" style="background-color: <?php 
        echo $oum_ui_color;
        ?>"><span class="btn_icon">+</span><span class="btn_text"><?php 
        echo esc_attr( $oum_plus_button_label );
        ?></span></div>

      <?php 
    }
    ?>

    <?php 
}
?>

    <?php 
if ( $types ) {
    ?>
      <div class="oum-filter-controls <?php 
    echo $oum_collapse_filter;
    ?> oum-hidden">
        <div class="oum-filter-toggle"></div>
        <div class="oum-filter-list">
          <div class="close-filter-list">&#x2715;</div>
          
          <!-- Toggle All Checkbox -->
          <?php 
    if ( $oum_enable_toggle_all_categories === 'on' ) {
        ?>
            <div class="oum-toggle-all-wrapper">
              <label class="oum-toggle-all-label">
                <input style="accent-color: <?php 
        echo $oum_ui_color;
        ?>" type="checkbox" id="oum-toggle-all" class="oum-toggle-all-checkbox">
                <span class="oum-toggle-all-text"><?php 
        echo __( 'Select all', 'open-user-map' );
        ?></span>
              </label>
            </div>
          <?php 
    }
    ?>
          
          <?php 
    foreach ( $types as $type ) {
        ?>

            <?php 
        if ( $type->term_id && get_term_meta( $type->term_id, 'oum_marker_icon', true ) ) {
            //get type marker icon from oum-type taxonomy
            $type_marker_icon = get_term_meta( $type->term_id, 'oum_marker_icon', true );
            $type_marker_user_icon = get_term_meta( $type->term_id, 'oum_marker_user_icon', true );
        } else {
            //get type marker icon from settings
            $type_marker_icon = $marker_icon;
            $type_marker_user_icon = $marker_user_icon;
        }
        if ( $type_marker_icon == 'user1' && $type_marker_user_icon ) {
            $icon = esc_url( $type_marker_user_icon );
        } else {
            $icon = esc_url( $this->plugin_url ) . 'src/leaflet/images/marker-icon_' . esc_attr( $type_marker_icon ) . '-2x.png';
        }
        ?>

            <label>
              <input style="accent-color: <?php 
        echo $oum_ui_color;
        ?>" type="checkbox" name="type" value="<?php 
        echo esc_attr( $type->term_taxonomy_id );
        ?>" checked>
              <img alt="category icon" src="<?php 
        echo $icon;
        ?>">
              <span><?php 
        echo esc_html( $type->name );
        ?></span>
            </label>

          <?php 
    }
    ?>
        </div>
      </div>
    <?php 
}
?>

    <?php 
?>

    <script type="text/javascript" id="oum-inline-js" 
      data-category="functional" 
      class="cmplz-native" 
      data-minify="0"
      data-no-optimize="1"
      data-no-defer="1"
      data-no-combine="1"
      data-cfasync="false"
      data-pagespeed-no-defer
      data-boot="1">
      var map_el = `map-<?php 
echo $unique_id;
?>`;

      if(document.getElementById(map_el)) {
        /* Transfer PHP array to JS json */
        var oum_all_locations = <?php 
echo json_encode( $oum_all_locations_clean, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
?>;

        // Wait for OUMLoader to be defined
        function initializeMap() {
          if (typeof OUMLoader !== 'undefined') {
            // Initialize loader for this map
            OUMLoader.initLoader(map_el);

            // Add event listener for map initialization complete
            document.addEventListener('oum:map_initialized', function(e) {
              if (e.detail.mapId === map_el) {
                OUMLoader.setMapInitialized(map_el);
              }
            });
          } else {
            // If OUMLoader is not yet defined, wait and try again
            setTimeout(initializeMap, 100);
          }
        }

        // Start initialization
        initializeMap();

        var mapStyle = `<?php 
echo esc_attr( $map_style );
?>`;
        var oum_tile_provider_mapbox_key = `<?php 
echo esc_attr( $oum_tile_provider_mapbox_key );
?>`;
        var marker_icon_url = `<?php 
echo ( $marker_icon == 'user1' && $marker_user_icon ? esc_url( $marker_user_icon ) : esc_url( $this->plugin_url ) . 'src/leaflet/images/marker-icon_' . esc_attr( $marker_icon ) . '-2x.png' );
?>`;
        var marker_shadow_url = `<?php 
echo esc_url( $this->plugin_url );
?>src/leaflet/images/marker-shadow.png`;
        var oum_enable_scrollwheel_zoom_map = <?php 
echo $oum_enable_scrollwheel_zoom_map;
?>;
        var oum_enable_cluster = <?php 
echo $oum_enable_cluster;
?>;
        var oum_enable_fullscreen = <?php 
echo $oum_enable_fullscreen;
?>;

        var oum_enable_searchbar = <?php 
echo $oum_enable_searchbar;
?>;
        var oum_searchbar_type = `<?php 
echo $oum_searchbar_type;
?>`;

        var oum_geosearch_selected_provider = ``; 
        var oum_geosearch_provider = `<?php 
echo $oum_geosearch_provider;
?>`;
        var oum_geosearch_provider_geoapify_key = `<?php 
echo esc_attr( $oum_geosearch_provider_geoapify_key );
?>`;
        var oum_geosearch_provider_here_key = `<?php 
echo esc_attr( $oum_geosearch_provider_here_key );
?>`;
        var oum_geosearch_provider_mapbox_key = `<?php 
echo esc_attr( $oum_geosearch_provider_mapbox_key );
?>`;
        
        var oum_enable_searchaddress_button = <?php 
echo $oum_enable_searchaddress_button;
?>;
        var oum_searchaddress_label = `<?php 
echo esc_attr( $oum_searchaddress_label );
?>`;

        var oum_enable_searchmarkers_button = <?php 
echo $oum_enable_searchmarkers_button;
?>;
        var oum_searchmarkers_label = `<?php 
echo esc_attr( $oum_searchmarkers_label );
?>`;
        var oum_searchmarkers_zoom = `<?php 
echo esc_attr( $oum_searchmarkers_zoom );
?>`;

        var oum_enable_currentlocation = <?php 
echo $oum_enable_currentlocation;
?>;
        var oum_action_after_submit = `<?php 
echo $oum_action_after_submit;
?>`;
        var thankyou_redirect = `<?php 
echo $thankyou_redirect;
?>`;
        var start_lat = Number(<?php 
echo esc_attr( $start_lat );
?>);
        var start_lng = Number(<?php 
echo esc_attr( $start_lng );
?>);
        var start_zoom = Number(<?php 
echo esc_attr( $start_zoom );
?>);
        
        var oum_enable_fixed_map_bounds = `<?php 
echo $oum_enable_fixed_map_bounds;
?>`;
        var oum_use_settings_start_location = <?php 
echo $oum_use_settings_start_location;
?>;
        var oum_has_regions = <?php 
echo ( $oum_enable_regions == 'on' && $regions && count( $regions ) > 0 ? 'true' : 'false' );
?>;
        var oum_enable_multiple_marker_types = `<?php 
echo $oum_enable_multiple_marker_types;
?>`;

        var oum_location = {};
        var oum_custom_css = '';
        var oum_custom_script = '';
        var oum_max_image_filesize = <?php 
echo esc_attr( $oum_max_image_filesize );
?>;
        var oumMap;
        var oumMap2;

        /**
         * Conditional Field Feature
         * 
         * @param {string} sourceField - The source field selector
         * @param {string} targetField - The target field selector
         * @param {array} condShow - The values that should show the target field
         * @param {array} condHide - The values that should hide the target field
         */
        var oumConditionalField = (sourceField, targetField, condShow, condHide) => {
            const sourceElements = document.querySelectorAll(sourceField); // Select all radios/checkboxes or single select
            const targetElementWrapper = document.querySelector(targetField)?.parentElement; /* works with custom fields only */

            // Check if both sourceElements and targetElementWrapper exist
            if (!sourceElements.length) {
                console.warn(`OUM: Source field(s) not found: ${sourceField}`);
                return;
            }

            if (!targetElementWrapper) {
                console.warn(`OUM: Target field wrapper not found: ${targetField}`);
                return;
            }

            /* Event listener for change */
            const onChangeHandler = function() {
                // Get selected values for checkboxes and single selected value for radios/select
                const selectedValues = Array.from(sourceElements)
                    .filter(element => element.checked || element.tagName === 'SELECT')
                    .map(element => element.value);

                const selectedValue = selectedValues[0]; // For radios and selects, we use only the first (and only) value

                console.log('OUM: run condition', {selectedValue, sourceField, targetField, condShow, condHide});
                
                // Show or hide target field based on the selected value(s)
                if (condShow.includes(selectedValue)) {
                    targetElementWrapper.style.display = 'block';
                } else if (condHide.includes(selectedValue)) {
                    targetElementWrapper.style.display = 'none';
                }
            };

            /* Attach the event listener to each radio/checkbox or select */
            sourceElements.forEach(element => {
                element.addEventListener('change', onChangeHandler);
            });

            /* Trigger initially */
            onChangeHandler(); // Call it directly to set initial state
        };

        /**
         * Add Custom Styles
         */
        
        <?php 
if ( $oum_ui_color ) {
    ?>

          /* custom color */
          oum_custom_css += `
            .open-user-map .add-location #close-add-location-overlay:hover {color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .box-wrap .map-wrap .oum-filter-controls .oum-filter-list .close-filter-list:hover {color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map input.oum-switch[type="checkbox"]:checked + label::before {background-color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .add-location .location-overlay-content #oum_add_location_thankyou h3 {color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .oum_location_text a {color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .oum_location_text .oum_vote_button_wrap .oum_vote_button.voted {background: <?php 
    echo $oum_ui_color;
    ?> !important; border-color: <?php 
    echo $oum_ui_color;
    ?> !important;}
            .open-user-map .oum-tabs {border-color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .oum-tabs .nav-item:hover {color: <?php 
    echo $oum_ui_color;
    ?> !important; border-color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .oum-tabs .nav-item.active {color: <?php 
    echo $oum_ui_color;
    ?> !important; border-color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .box-wrap .map-wrap .oum-attribution a {color: <?php 
    echo $oum_ui_color;
    ?> !important;}
            /* Message CTA Buttons */
            .open-user-map .add-location .location-overlay-content #oum_add_location_thankyou button {background-color: <?php 
    echo $oum_ui_color;
    ?> !important; border-color: <?php 
    echo $oum_ui_color;
    ?> !important;}
            .open-user-map .add-location .location-overlay-content .oum-delete-confirmation button {background-color: <?php 
    echo $oum_ui_color;
    ?> !important; border-color: <?php 
    echo $oum_ui_color;
    ?> !important;}
            /* Media Section Colors */
            .open-user-map .add-location .location-overlay-content #oum_add_location .oum_media .media-upload label {color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .add-location .location-overlay-content #oum_add_location .oum_media .oum-image-upload .media-upload-top label .multi-upload-indicator {background: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .add-location .location-overlay-content #oum_add_location .oum_media .oum-video-upload input[type=text]:hover {border-color: <?php 
    echo $oum_ui_color;
    ?> !important}
            .open-user-map .add-location .location-overlay-content #oum_add_location .oum_media .oum-video-upload input[type=text]:focus {border-color: <?php 
    echo $oum_ui_color;
    ?> !important; box-shadow: 0 0 0 2px <?php 
    echo $oum_ui_color;
    ?>1a !important}
            .open-user-map .add-location .location-overlay-content #oum_add_location .oum_media .image-preview-placeholder {border-color: <?php 
    echo $oum_ui_color;
    ?> !important; background: <?php 
    echo $oum_ui_color;
    ?>0a !important}
            .open-user-map .add-location .location-overlay-content #oum_add_location .oum_media .oum-image-preview-grid .image-preview-item.dragging {border-color: <?php 
    echo $oum_ui_color;
    ?> !important}
            /* List Styles */
            .open-user-map-locations-list .oum-locations-list-item .oum_location_text a {color: <?php 
    echo $oum_ui_color;
    ?> !important} 
            .open-user-map-locations-list .oum-locations-list-item .oum_location_text .oum_vote_button_wrap .oum_vote_button.voted {background: <?php 
    echo $oum_ui_color;
    ?> !important; border-color: <?php 
    echo $oum_ui_color;
    ?> !important;}`;

        <?php 
}
?>

        <?php 
if ( $oum_map_height ) {
    ?>

          /* custom map height */
          oum_custom_css += `
            .open-user-map .box-wrap > .map-wrap {padding: 0 !important; height: <?php 
    echo esc_attr( $oum_map_height );
    ?> !important; aspect-ratio: unset !important;}`;

        <?php 
}
?>

        <?php 
if ( $oum_map_height_mobile ) {
    ?>

          /* custom map height */
          oum_custom_css += `
            @media screen and (max-width: 768px) {.open-user-map .box-wrap > .map-wrap {padding: 0 !important; height: <?php 
    echo esc_attr( $oum_map_height_mobile );
    ?> !important; aspect-ratio: unset !important;}}`;

        <?php 
}
?>

        var custom_style = document.createElement('style');

        if (custom_style.styleSheet) {
          custom_style.styleSheet.cssText = oum_custom_css;
        } else {
          custom_style.appendChild(document.createTextNode(oum_custom_css));
        }

        document.getElementsByTagName('head')[0].appendChild(custom_style);

        /* Add initial CSS to prevent flash of unstyled content */
        var initialStyles = document.createElement('style');
        initialStyles.textContent = `
          .oum-hidden {
            opacity: 0 !important;
            visibility: hidden !important;
            transition: opacity 0.3s ease, visibility 0.3s ease;
          }
          .oum-filter-controls,
          .open-add-location-overlay,
          #oum_filter_markers {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
          }
          .oum-filter-controls.visible,
          .open-add-location-overlay.visible,
          #oum_filter_markers.visible {
            opacity: 1;
            visibility: visible;
          }
        `;
        document.head.appendChild(initialStyles);

      }
    </script>

  </div>

</div>
