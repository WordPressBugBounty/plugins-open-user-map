<?php

$oum_all_locations = [];
foreach ( $locations_list as $location ) {
    if ( get_option( 'oum_enable_location_date' ) === 'on' ) {
        $date_tag = '<div class="oum_location_date">' . wp_kses_post( $location['date'] ) . '</div>';
    } else {
        $date_tag = '';
    }
    $name_tag = ( get_option( 'oum_enable_title', 'on' ) == 'on' ? '<h3 class="oum_location_name">' . esc_attr( $location['name'] ) . '</h3>' : '' );
    $media_tag = '';
    if ( $location['image'] ) {
        $media_tag = '<div class="oum_location_image"><img class="skip-lazy" src="' . esc_url_raw( $location['image'] ) . '"></div>';
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
        $custom_fields .= '<div class="oum_location_custom_fields">';
        foreach ( $location['custom_fields'] as $custom_field ) {
            if ( !$custom_field['val'] || $custom_field['val'] == '' ) {
                continue;
            }
            if ( is_array( $custom_field['val'] ) ) {
                array_walk( $custom_field['val'], function ( &$x ) {
                    $x = '<span data-value="' . $x . '">' . $x . '</span>';
                } );
                $custom_fields .= '<div class="oum_custom_field"><strong>' . $custom_field['label'] . ':</strong> ' . implode( '', $custom_field['val'] ) . '</div>';
            } else {
                if ( stristr( $custom_field['val'], '|' ) ) {
                    // multiple entries separated with | symbol
                    $custom_fields .= '<div class="oum_custom_field"><strong>' . $custom_field['label'] . ':</strong> ';
                    foreach ( explode( '|', $custom_field['val'] ) as $entry ) {
                        $entry = trim( $entry );
                        if ( wp_http_validate_url( $entry ) ) {
                            // URL
                            $custom_fields .= '<a target="_blank" href="' . $entry . '">' . $entry . '</a> ';
                        } elseif ( is_email( $entry ) && $custom_field['fieldtype'] == 'email' ) {
                            // Email
                            $custom_fields .= '<a target="_blank" href="mailto:' . $entry . '">' . $entry . '</a> ';
                        } else {
                            // Text
                            $custom_fields .= '<span data-value="' . $entry . '">' . $entry . '</span>';
                        }
                    }
                    $custom_fields .= '</div>';
                } else {
                    // single entry
                    if ( wp_http_validate_url( $custom_field['val'] ) ) {
                        // URL
                        if ( isset( $custom_field['uselabelastextoption'] ) && $custom_field['uselabelastextoption'] == 'on' ) {
                            $custom_fields .= '<div class="oum_custom_field"><a target="_blank" href="' . $custom_field['val'] . '">' . $custom_field['label'] . '</a></div>';
                        } else {
                            $custom_fields .= '<div class="oum_custom_field"><strong>' . $custom_field['label'] . ':</strong> <a target="_blank" href="' . $custom_field['val'] . '">' . $custom_field['val'] . '</a></div>';
                        }
                    } elseif ( is_email( $custom_field['val'] ) && $custom_field['fieldtype'] == 'email' ) {
                        // Email
                        $custom_fields .= '<div class="oum_custom_field"><strong>' . $custom_field['label'] . ':</strong> <a target="_blank" href="mailto:' . $custom_field['val'] . '">' . $custom_field['val'] . '</a></div>';
                    } else {
                        // Text
                        $custom_fields .= '<div class="oum_custom_field"><strong>' . $custom_field['label'] . ':</strong> <span data-value="' . $custom_field['val'] . '">' . $custom_field['val'] . '</span></div>';
                    }
                }
            }
        }
        $custom_fields .= '</div>';
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
    // building bubble block content
    $content = $media_tag;
    $content .= '<div class="oum_location_text">';
    $content .= $date_tag;
    $content .= $address_tag;
    $content .= $name_tag;
    $content .= $custom_fields;
    $content .= $description_tag;
    $content .= $audio_tag;
    $content .= $link_tag;
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
        'content'       => $content,
        'icon'          => esc_attr( $location["icon"] ),
        'types'         => ( isset( $location["types"] ) ? $location["types"] : [] ),
        'post_id'       => esc_attr( $location["post_id"] ),
        'address'       => esc_attr( $location["address"] ),
        'text'          => wp_kses_post( $location["text"] ),
        'image'         => esc_url( $location["image"] ),
        'audio'         => esc_url( $location["audio"] ),
        'video'         => esc_url( $location["video"] ),
        'custom_fields' => $location['custom_fields'],
    ];
    $oum_all_locations[] = $oum_location;
}
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
        ?>" data-toggle="tab"><?php 
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
      <input type="text" id="oum_filter_markers" placeholder="<?php 
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

        <div id="open-add-location-overlay" class="open-add-location-overlay" style="background-color: <?php 
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
    ?>">
        <div class="oum-filter-toggle"></div>
        <div class="oum-filter-list">
          <div class="close-filter-list">&#x2715;</div>
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
              <img src="<?php 
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

    <script type="text/javascript" data-category="functional" class="cmplz-native" id="oum-inline-js">
      var map_el = `map-<?php 
echo $unique_id;
?>`;

      if(document.getElementById(map_el)) {

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
        var start_lat = `<?php 
echo esc_attr( $start_lat );
?>`;
        var start_lng = `<?php 
echo esc_attr( $start_lng );
?>`;
        var start_zoom = `<?php 
echo esc_attr( $start_zoom );
?>`;
        var oum_enable_fixed_map_bounds = `<?php 
echo $oum_enable_fixed_map_bounds;
?>`;
        var oum_minimum_zoom_level = `<?php 
echo $oum_minimum_zoom_level;
?>`;
        var oum_use_settings_start_location = <?php 
echo $oum_use_settings_start_location;
?>;
        var oum_has_regions = <?php 
echo ( $oum_enable_regions == 'on' && $regions && count( $regions ) > 0 ? 'true' : 'false' );
?>;

        var oum_location = {};
        var oum_custom_css = '';
        var oum_custom_script = '';
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



        /* Transfer PHP array to JS json */
        var oum_all_locations = <?php 
echo json_encode( $oum_all_locations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES );
?>;


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

      }
    </script>

  </div>

</div>
