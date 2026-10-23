<div class="wrap">
<h1>Open User Map</h1>

<?php 
settings_errors( 'oum_messages' );
?>

<form method="post" action="options.php">
    
    <!-- Hidden field to preserve active tab after form submission -->
    <?php 
// Determine active tab from URL parameter or default to tab-1
$active_tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'tab-1' );
?>
    <input type="hidden" name="oum_active_tab" id="oum_active_tab" value="<?php 
echo esc_attr( $active_tab );
?>">

    <?php 
if ( get_option( 'oum_enable_add_location' ) !== 'on' && get_option( 'oum_enable_add_location' ) !== '' ) {
    ?>

      <?php 
    settings_fields( 'open-user-map-settings-group-wizard-1' );
    ?>
      <?php 
    do_settings_sections( 'open-user-map-settings-group-wizard-1' );
    ?>

      <div class="oum-wizard">
          <div class="hero">
          <div class="logo">Open User Map</div>
          <div class="overline"><?php 
    echo __( 'Quick Setup (2/3)', 'open-user-map' );
    ?></div>
          <h1><?php 
    echo __( 'Let Visitors Add Locations to Your Map?', 'open-user-map' );
    ?></h1>
          <ul class="steps">
            <li class="done"></li>
            <li class="done"></li>
            <li></li>
          </ul>
        </div>
        <div class="step-content">
          <div class="intro">
            <?php 
    echo __( 'You can change this anytime later in the settings.', 'open-user-map' );
    ?>
          </div>
          <div class="map-types">
            <div class="option">
              <label>
                <div class="map-type-preview" data-type="interactive"></div>
                <div class="label-text">
                  <input type='radio' name='oum_wizard_usecase' value='1' checked>
                  <h2><?php 
    echo __( 'Yes — Enable Location Submissions', 'open-user-map' );
    ?></h2>
                  <p><?php 
    echo __( 'Visitors can submit new locations using the “+ Add Location” button.', 'open-user-map' );
    ?></p>
                </div>
              </label>
            </div>
            <div class="option">
              <label>
                <div class="map-type-preview" data-type="simple"></div>
                <div class="label-text">
                  <input type='radio' name='oum_wizard_usecase' value='2'>
                  <h2><?php 
    echo __( 'No — Only I Add Locations', 'open-user-map' );
    ?></h2>
                  <p><?php 
    echo __( 'Create a clean, controlled map without visitor submissions.', 'open-user-map' );
    ?></p>
                </div>
              </label>
            </div>
          </div>

          <input type="hidden" name="oum_wizard_usecase_done" value="1">

          <?php 
    submit_button(
        __( 'Next', 'open-user-map' ),
        'primary',
        'submit',
        false
    );
    ?>
        </div>
      </div>

    <?php 
} elseif ( get_option( 'oum_wizard_usecase_done' ) && !get_option( 'oum_wizard_finish_done' ) ) {
    ?>

      <?php 
    settings_fields( 'open-user-map-settings-group-wizard-2' );
    ?>
      <?php 
    do_settings_sections( 'open-user-map-settings-group-wizard-2' );
    ?>

      <div class="oum-wizard">
        <div class="hero">
          <div class="logo">Open User Map</div>
          <div class="overline"><?php 
    echo __( 'Quick Setup (3/3)', 'open-user-map' );
    ?></div>
          <h1>🎉 <?php 
    echo __( 'Yeah, complete!', 'open-user-map' );
    ?></h1>
          <ul class="steps">
            <li class="done"></li>
            <li class="done"></li>
            <li class="done"></li>
          </ul>
        </div>
        <div class="step-content">

          <h3><?php 
    echo __( 'Your next steps:', 'open-user-map' );
    ?></h3>

          <?php 
    if ( get_option( 'oum_wizard_usecase' ) == '1' ) {
        ?>
          
            <ol class="next-steps">
              <li><?php 
        echo __( 'Use the WordPress block editor (or Elementor) to insert the <b>Open User Map</b> block onto a page.<br>Alternatively, you can use the shortcode <input class="shortcode-display" type="text" readonly value=\'[open-user-map]\' />.', 'open-user-map' );
        ?></li>
              <li><?php 
        echo __( 'Your website visitors will see a <div class="oum-inline-plus">+</div> button in the upper right corner of the map, which they can use to propose their own location markers. New location proposals will appear with the status <b>Pending</b> until you review and approve them in the <i>Open User Map > All Locations</i> menu.', 'open-user-map' );
        ?></li>
              <li><?php 
        echo __( 'To customize styles, enable features, or get help, go to <i>Open User Map > Settings</i>', 'open-user-map' );
        ?></li>
            </ol>

          <?php 
    } elseif ( get_option( 'oum_wizard_usecase' ) == '2' ) {
        ?>

            <ol class="next-steps">
              <li><?php 
        echo sprintf( __( 'Add your first Location under <a href="%s">Open User Map > Add Location</a>', 'open-user-map' ), 'post-new.php?post_type=oum-location' );
        ?></li>
              <li><?php 
        echo __( 'Use the WordPress block editor (or Elementor) to insert the <b>Open User Map</b> block onto a page.<br>Alternatively, you can use the shortcode <input class="shortcode-display" type="text" readonly value=\'[open-user-map]\' />.', 'open-user-map' );
        ?></li>
              <li><?php 
        echo __( 'To customize styles, enable features, or get help, go to <i>Open User Map > Settings</i>', 'open-user-map' );
        ?></li>
            </ol>

          <?php 
    }
    ?>

          <input type="hidden" name="oum_wizard_finish_done" value="1">

          <?php 
    submit_button(
        'Okay, got it',
        'primary',
        'submit',
        false
    );
    ?>
        </div>
      </div>

    <?php 
} else {
    ?>

      <?php 
    settings_fields( 'open-user-map-settings-group' );
    ?>
      <?php 
    do_settings_sections( 'open-user-map-settings-group' );
    ?>

      <!-- NAV -->
      <nav class="oum-nav-tab-wrapper nav-tab-wrapper">
        <a href="#tab-1" class="nav-tab <?php 
    echo ( $active_tab === 'tab-1' ? 'nav-tab-active' : '' );
    ?>"><?php 
    echo __( 'General', 'open-user-map' );
    ?></a>
        <a href="#tab-2" class="nav-tab <?php 
    echo ( $active_tab === 'tab-2' ? 'nav-tab-active' : '' );
    ?>"><?php 
    echo __( 'Location Settings', 'open-user-map' );
    ?></a>
        <a href="#tab-3" class="nav-tab <?php 
    echo ( $active_tab === 'tab-3' ? 'nav-tab-active' : '' );
    ?>"><?php 
    echo __( 'Search & Filter', 'open-user-map' );
    ?></a>
        <a href="#tab-categories" class="nav-tab <?php 
    echo ( $active_tab === 'tab-categories' ? 'nav-tab-active' : '' );
    ?>"><?php 
    echo __( 'Categories & Types', 'open-user-map' );
    ?></a>
        <a href="#tab-4" class="nav-tab <?php 
    echo ( $active_tab === 'tab-4' ? 'nav-tab-active' : '' );
    ?>"><?php 
    echo __( 'Regions', 'open-user-map' );
    ?></a>
        <a href="#tab-5" class="nav-tab <?php 
    echo ( $active_tab === 'tab-5' ? 'nav-tab-active' : '' );
    ?>"><?php 
    echo __( 'Advanced', 'open-user-map' );
    ?></a>
        <a href="#tab-6" class="nav-tab <?php 
    echo ( $active_tab === 'tab-6' ? 'nav-tab-active' : '' );
    ?>"><?php 
    echo __( 'Import & Export', 'open-user-map' );
    ?></a>
        <a href="#tab-7" class="nav-tab <?php 
    echo ( $active_tab === 'tab-7' ? 'nav-tab-active' : '' );
    ?>"><?php 
    echo __( 'Help & Getting Started', 'open-user-map' );
    ?></a>
        <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
          <!-- PRO Trial Tab -->
          <a href="#tab-pro-trial" class="nav-tab oum-pro-trial-tab <?php 
        echo ( $active_tab === 'tab-pro-trial' ? 'nav-tab-active' : '' );
        ?>">
            ✨ <?php 
        echo __( 'Try PRO 7 Days Free!', 'open-user-map' );
        ?>
          </a>
        <?php 
    }
    ?>
        <?php 
    // Determine plugin type (Free, Trial, or PRO) and corresponding color
    $plugin_type = 'Free';
    $plugin_color = '#46b450';
    // Green for Free
    if ( oum_fs()->is_trial() ) {
        // User is in trial period
        $plugin_type = 'Trial';
        $plugin_color = '#ff9800';
        // Orange for Trial
    } elseif ( oum_fs()->is_premium() && oum_fs()->is_plan_or_trial( 'pro' ) ) {
        // User has PRO plan and is using premium version
        $plugin_type = 'PRO';
        $plugin_color = '#dc3232';
        // Red for PRO
    }
    ?>
        <span class="oum-plugin-info" style="float: right; margin-top: 8px; margin-right: 10px; font-size: 12px;">
          <strong style="color: <?php 
    echo esc_attr( $plugin_color );
    ?>;"><?php 
    echo esc_html( $plugin_type );
    ?></strong> <span style="color: #666;">v<?php 
    echo esc_html( $this->plugin_version );
    ?></span>
        </span>
      </nav>


      <!-- TABS -->
      <div class="oum-tab-content">
        
        <div id="tab-1" class="oum-tab-pane oum-settings-groups <?php 
    echo ( $active_tab === 'tab-1' ? 'active' : '' );
    ?>">
          <?php 
    $this->render_settings_quick_links( array(
        'oum-general-submissions' => __( 'Location submissions', 'open-user-map' ),
        'oum-general-style'       => __( 'Map style', 'open-user-map' ),
        'oum-general-markers'     => __( 'Markers & appearance', 'open-user-map' ),
        'oum-general-view'        => __( 'Map size & initial view', 'open-user-map' ),
        'oum-general-controls'    => __( 'Map controls', 'open-user-map' ),
    ) );
    ?>
          <table class="form-table">
          <?php 
    $this->render_settings_group_start( 'oum-general-submissions', __( 'Location submissions', 'open-user-map' ), __( 'Choose whether visitors can contribute to your map.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Location Submissions', 'open-user-map' );
    ?>
                <br>
                <span class="description"><?php 
    echo __( 'Allow visitors to submit new locations to your map. Admins always see the "+ Add Location" button on the map, even when this option is off.', 'open-user-map' );
    ?></span>
                <br>
              </th>
              <td class="oum-community-contributions-cell">
                <?php 
    $oum_enable_add_location = get_option( 'oum_enable_add_location', 'on' );
    $oum_community_teaser_img_url = plugins_url( 'assets/images/map_type_interactive_3.png', dirname( dirname( __FILE__ ) ) . '/open-user-map.php' );
    ?>
                <div class="oum-community-contributions-card">
                  <div class="oum-community-contributions-content">
                    <h2 class="oum-community-contributions-title"><?php 
    echo __( 'Let Visitors Add Locations', 'open-user-map' );
    ?></h2>
                    <p class="oum-community-contributions-intro"><?php 
    echo __( 'Turn your map into a collaborative community map where visitors can add new locations using the "+ Add Location" button in the top-right corner of the map.', 'open-user-map' );
    ?></p>

                    <div class="oum-community-contributions-toggle-wrap">
                      <input class="oum-switch" type="checkbox" id="oum_enable_add_location_toggle" name="oum_enable_add_location" <?php 
    echo ( $oum_enable_add_location == 'on' ? 'checked' : '' );
    ?>>
                      <label for="oum_enable_add_location_toggle"><?php 
    echo __( 'Allow visitors to add locations', 'open-user-map' );
    ?></label>
                    </div>

                    <div class="community-enabled-tip" <?php 
    echo ( $oum_enable_add_location != 'on' ? 'style="display:none;"' : '' );
    ?>>
                      <span class="description">💡 <?php 
    echo __( 'Tip: Disable this if you want to manage all locations yourself.', 'open-user-map' );
    ?></span>
                    </div>
                    <div class="community-disabled-tip" <?php 
    echo ( $oum_enable_add_location == 'on' ? 'style="display:none;"' : '' );
    ?>>
                      <span class="description">💡 <?php 
    echo __( 'Tip: Keep this disabled if you are the only one adding locations to the map.', 'open-user-map' );
    ?></span>
                    </div>

                    <p class="description oum-community-contributions-advanced"><?php 
    echo __( 'Configure moderation, form fields, and notifications in the <strong>Location Settings</strong> tab.', 'open-user-map' );
    ?></p>
                  </div>
                  <div class="oum-community-contributions-teaser">
                    <div class="oum-community-contributions-teaser-img-wrap">
                      <img src="<?php 
    echo esc_url( $oum_community_teaser_img_url );
    ?>" alt="" class="oum-community-contributions-teaser-img" />
                    </div>
                  </div>
                </div>
              </td>
            </tr>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-general-style', __( 'Map style', 'open-user-map' ), __( 'Choose a map provider, style, or custom image.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Map Style', 'open-user-map' );
    ?>
              </th>
              <td>
                <div class="map_styles">
                <?php 
    $map_style = ( get_option( 'oum_map_style' ) ? get_option( 'oum_map_style' ) : $this->get_default_map_style() );
    $oum_tile_provider_mapbox_key = get_option( 'oum_tile_provider_mapbox_key', '' );
    $oum_tile_provider_carto_key = get_option( 'oum_tile_provider_carto_key', '' );
    $style_providers = array(
        'openfreemap'   => 'OpenFreeMap',
        'openstreetmap' => 'OpenStreetMap',
        'esri'          => 'Esri',
        'carto'         => 'CARTO',
        'mapbox'        => 'MapBox',
    );
    foreach ( $style_providers as $style_provider => $style_provider_label ) {
        $provider_styles = $this->get_map_styles_by_provider( $style_provider, false );
        if ( empty( $provider_styles ) ) {
            continue;
        }
        echo '<div class="map_style_provider_group map_style_provider_group-' . esc_attr( $style_provider ) . '">';
        echo '<div class="map_style_provider_header">';
        echo '<h3 class="map_style_provider_heading">' . esc_html( $style_provider_label ) . '</h3>';
        if ( $style_provider === 'carto' ) {
            echo '<div class="map_style_provider_api_key tile-provider-carto">';
            echo '<label for="oum_tile_provider_carto_key">' . esc_html__( 'API Key:', 'open-user-map' ) . '</label>';
            echo '<input class="regular-text" type="text" name="oum_tile_provider_carto_key" id="oum_tile_provider_carto_key" value="' . esc_attr( $oum_tile_provider_carto_key ) . '">';
            echo '<a href="https://carto.com/basemaps/apikey/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get it here', 'open-user-map' ) . '</a>';
            echo '</div>';
        }
        if ( $style_provider === 'mapbox' ) {
            echo '<div class="map_style_provider_api_key tile-provider-mapbox">';
            echo '<label for="oum_tile_provider_mapbox_key">' . esc_html__( 'API Key:', 'open-user-map' ) . '</label>';
            echo '<input class="regular-text" type="text" name="oum_tile_provider_mapbox_key" id="oum_tile_provider_mapbox_key" value="' . esc_attr( $oum_tile_provider_mapbox_key ) . '">';
            echo '<a href="https://account.mapbox.com/signup/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get it here', 'open-user-map' ) . '</a>';
            echo '</div>';
        }
        echo '</div>';
        if ( in_array( $style_provider, array('carto', 'mapbox'), true ) ) {
            $provider_api_key = ( $style_provider === 'carto' ? $oum_tile_provider_carto_key : $oum_tile_provider_mapbox_key );
            $selected_style_definition = $this->get_map_style_definition( $map_style );
            $show_provider_notice = $provider_api_key === '' && $selected_style_definition && isset( $selected_style_definition['requires_api_key'] ) && $selected_style_definition['requires_api_key'] === $style_provider;
            $provider_notice_style = ( $show_provider_notice ? '' : ' style="display: none;"' );
            echo '<p class="map_style_provider_notice map_style_provider_notice-' . esc_attr( $style_provider ) . '"' . $provider_notice_style . '>' . esc_html__( 'This provider style requires an API Key.', 'open-user-map' ) . '</p>';
        }
        echo '<div class="map_style_provider_styles">';
        foreach ( $provider_styles as $val => $definition ) {
            $selected = ( $map_style == $val ? 'checked' : '' );
            $preview_classes = array('map_style_preview');
            $style_label = $definition['label'];
            if ( isset( $definition['provider'] ) && $definition['provider'] === 'openfreemap' ) {
                $preview_classes[] = 'vector';
            }
            if ( $style_provider === 'openfreemap' ) {
                $style_label = str_replace( 'OpenFreeMap ', '', $style_label );
            } elseif ( $style_provider === 'openstreetmap' ) {
                $style_label = ( $val === 'OpenStreetMap.DE' ? 'Hosted in Germany' : 'Mapnik' );
            } elseif ( $style_provider === 'esri' ) {
                $style_label = str_replace( 'Esri ', '', $style_label );
            } elseif ( $style_provider === 'carto' ) {
                $style_label = str_replace( 'CARTO ', '', $style_label );
            } elseif ( $style_provider === 'mapbox' ) {
                $style_label = str_replace( 'MapBox ', '', $style_label );
            }
            echo '<label class="' . esc_attr( $selected ) . '"><div class="' . esc_attr( implode( ' ', $preview_classes ) ) . '" data-style="' . esc_attr( $val ) . '"><div>' . esc_textarea( $style_label ) . '</div></div><input type="radio" name="oum_map_style" ' . $selected . ' value="' . esc_attr( $val ) . '"></label>';
        }
        echo '</div>';
        echo '</div>';
    }
    //pro map styles (PRO only)
    $pro_items = $this->get_map_styles_by_provider( 'custom_image', false );
    if ( !empty( $pro_items ) ) {
        echo '<div class="map_style_provider_group map_style_provider_group-custom_image">';
        echo '<div class="map_style_provider_header">';
        echo '<h3 class="map_style_provider_heading">' . esc_html__( 'Custom Image', 'open-user-map' ) . '</h3>';
        echo '</div>';
        echo '<div class="map_style_provider_styles">';
    }
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        foreach ( $pro_items as $val => $definition ) {
            echo '<div class="oum-pro-choice">';
            echo '<label class="pro-only"><div class="map_style_preview pro" data-style="' . esc_attr( $val ) . '"><div>' . esc_textarea( __( 'Image', 'open-user-map' ) ) . ' <span class="oum-pro">PRO</span></div></div></label>';
            $this->render_pro_feature_help( __( 'Custom Image', 'open-user-map' ), __( 'Use a custom image or SVG as your map overlay, for example a floor plan, historical map, or event map. Keep map interactions while displaying your own image.', 'open-user-map' ) );
            echo "</div>";
        }
    }
    if ( !empty( $pro_items ) ) {
        echo '</div>';
        echo '</div>';
    }
    ?>
                </div>

                <!-- Custom Image Settings -->
                <div class="wrap-custom-image-settings" style="display: <?php 
    echo ( $map_style === 'CustomImage' ? 'block' : 'none' );
    ?>;">
                  <h2><?php 
    echo __( 'Custom Image Overlay', 'open-user-map' );
    ?></h2>

                  
                  <?php 
    $custom_image_url = get_option( 'oum_custom_image_url', '' );
    $custom_image_bounds = get_option( 'oum_custom_image_bounds', '' );
    // Parse bounds for form fields
    $bounds = array(
        'north' => '90',
        'south' => '-90',
        'east'  => '180',
        'west'  => '-180',
    );
    if ( !empty( $custom_image_bounds ) ) {
        $parsed_bounds = maybe_unserialize( $custom_image_bounds );
        if ( is_array( $parsed_bounds ) ) {
            $bounds = array_merge( $bounds, $parsed_bounds );
        }
    }
    // Ensure all values use period as decimal separator (normalize after merge)
    foreach ( $bounds as $key => $value ) {
        $bounds[$key] = str_replace( ',', '.', (string) $value );
    }
    ?>
                  
                  <!-- Image Upload Section -->
                  <div class="custom-image-upload-section">
                    <h4><?php 
    echo __( '1. Upload Your Image', 'open-user-map' );
    ?></h4>
                    
                    <!-- Hidden input for storing the image URL -->
                    <input type="url" id="oum_custom_image_url" name="oum_custom_image_url" value="<?php 
    echo esc_attr( $custom_image_url );
    ?>" style="display: none;">
                    
                    <div style="margin-bottom: 15px;">
                      <p class="description"><?php 
    echo __( 'Supports JPG, PNG, WebP, and SVG files. The custom image overlay can be clickable when enabled in the display options below.', 'open-user-map' );
    ?></p>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                      <button type="button" id="upload_image_button" class="button button-primary"><?php 
    echo __( 'Choose Image', 'open-user-map' );
    ?></button>
                      <?php 
    if ( !empty( $custom_image_url ) ) {
        ?>
                        <button type="button" id="remove_image_button" class="button" style="margin-left: 10px;"><?php 
        echo __( 'Remove', 'open-user-map' );
        ?></button>
                      <?php 
    }
    ?>
                    </div>
                    
                    <!-- Image preview container (always present) -->
                    <div id="image_preview" style="margin-top: 15px; <?php 
    echo ( empty( $custom_image_url ) ? 'display: none;' : '' );
    ?>">
                      <?php 
    if ( !empty( $custom_image_url ) ) {
        ?>
                        <img src="<?php 
        echo esc_url( $custom_image_url );
        ?>" alt="Custom Map Image">
                      <?php 
    }
    ?>
                    </div>
                  </div>
                  
                  <!-- Image Position Section -->
                  <div class="image-bounds-section">
                    <h4><?php 
    echo __( '2. Position Image on Map', 'open-user-map' );
    ?></h4>
                    <p class="description"><?php 
    echo __( 'Use the visual editor below to position and scale your image overlay. Drag the image to move it, and drag the corner handles to resize it. Hold Shift while resizing to maintain the image\'s aspect ratio.', 'open-user-map' );
    ?></p>
                    
                    <!-- Visual Position Editor -->
                    <div id="oum-image-position-editor" style="margin-top: 20px; <?php 
    echo ( empty( $custom_image_url ) ? 'display: none;' : '' );
    ?>">
                      <!-- Editor will be initialized here -->
                    </div>
                    
                    <?php 
    // Enqueue editor script and dependencies
    wp_enqueue_media();
    // Required for wp.media uploader
    wp_enqueue_script(
        'oum_backend_image_position_editor_js',
        $this->plugin_url . 'src/js/backend-image-position-editor.js',
        array('oum_global_leaflet_js'),
        $this->plugin_version
    );
    // Localize script with translated strings
    wp_localize_script( 'oum_backend_image_position_editor_js', 'oumImagePositionEditorStrings', array(
        'chooseImageTitle' => __( 'Choose Custom Map Image', 'open-user-map' ),
        'useImageText'     => __( 'Use this image', 'open-user-map' ),
    ) );
    ?>
                    
                    <!-- Manual Input Fields (collapsible, shown for user reference and manual editing) -->
                    <div id="manual-bounds-inputs" style="margin-top: 20px;">
                      <div style="display: flex; align-items: center; cursor: pointer; margin-bottom: 10px;" onclick="toggleManualBoundsInputs()">
                        <span id="manual-bounds-toggle-icon" style="margin-right: 8px; font-size: 12px;">▶</span>
                        <p class="description" style="margin: 0;"><?php 
    echo __( 'Position coordinates (updated automatically when using the visual editor above, or edit manually):', 'open-user-map' );
    ?></p>
                      </div>
                      <div id="manual-bounds-inputs-content" style="display: none;">
                      <div class="grid-inputs">
                        <div class="input-group">
                          <label for="image_bounds_north"><?php 
    echo __( 'North:', 'open-user-map' );
    ?></label>
                          <input type="text" id="image_bounds_north" name="image_bounds_north" value="<?php 
    echo esc_attr( $bounds['north'] );
    ?>" pattern="-?[0-9]+\.?[0-9]*" inputmode="decimal" lang="en" placeholder="90">
                          <small><?php 
    echo __( 'Latitude', 'open-user-map' );
    ?></small>
                        </div>
                        <div class="input-group">
                          <label for="image_bounds_west"><?php 
    echo __( 'West:', 'open-user-map' );
    ?></label>
                          <input type="text" id="image_bounds_west" name="image_bounds_west" value="<?php 
    echo esc_attr( $bounds['west'] );
    ?>" pattern="-?[0-9]+\.?[0-9]*" inputmode="decimal" lang="en" placeholder="-180">
                          <small><?php 
    echo __( 'Longitude', 'open-user-map' );
    ?></small>
                        </div>
                      </div>
                      <div class="grid-inputs">
                        <div class="input-group">
                          <label for="image_bounds_south"><?php 
    echo __( 'South:', 'open-user-map' );
    ?></label>
                          <input type="text" id="image_bounds_south" name="image_bounds_south" value="<?php 
    echo esc_attr( $bounds['south'] );
    ?>" pattern="-?[0-9]+\.?[0-9]*" inputmode="decimal" lang="en" placeholder="-90">
                          <small><?php 
    echo __( 'Latitude', 'open-user-map' );
    ?></small>
                        </div>
                        <div class="input-group">
                          <label for="image_bounds_east"><?php 
    echo __( 'East:', 'open-user-map' );
    ?></label>
                          <input type="text" id="image_bounds_east" name="image_bounds_east" value="<?php 
    echo esc_attr( $bounds['east'] );
    ?>" pattern="-?[0-9]+\.?[0-9]*" inputmode="decimal" lang="en" placeholder="180">
                          <small><?php 
    echo __( 'Longitude', 'open-user-map' );
    ?></small>
                        </div>
                      </div>
                      </div>
                    </div>
                    
                    <!-- Hidden field to store bounds as JSON -->
                    <input type="hidden" id="oum_custom_image_bounds" name="oum_custom_image_bounds" value="<?php 
    echo esc_attr( $custom_image_bounds );
    ?>">
                  </div>
                  
                  <!-- Display Options -->
                  <div class="image-display-options">
                    <h4><?php 
    echo __( '3. Display Options', 'open-user-map' );
    ?></h4>
                    
                    <div class="custom-image-hide-tiles-option">
                      <input class="oum-switch" type="checkbox" name="oum_custom_image_hide_tiles" id="oum_custom_image_hide_tiles" value="on" <?php 
    checked( get_option( 'oum_custom_image_hide_tiles', '' ), 'on' );
    ?>>
                      <label for="oum_custom_image_hide_tiles"><?php 
    echo __( 'Hide default map tiles', 'open-user-map' );
    ?></label>
                      <p class="description"><?php 
    echo __( 'Show only your custom image without any background map tiles.', 'open-user-map' );
    ?></p>
                    </div>

                    <div class="custom-image-clickable-option">
                      <input class="oum-switch" type="checkbox" name="oum_custom_image_clickable" id="oum_custom_image_clickable" value="on" <?php 
    checked( get_option( 'oum_custom_image_clickable', '' ), 'on' );
    ?>>
                      <label for="oum_custom_image_clickable"><?php 
    echo __( 'Allow clicks on custom image overlay', 'open-user-map' );
    ?></label>
                      <p class="description"><?php 
    echo __( 'Enable this only when the custom image overlay itself needs to be clickable. When disabled, clicks pass through to lines and areas on the map.', 'open-user-map' );
    ?></p>
                    </div>
                    
                    <div>
                      <label for="oum_custom_image_background_color"><?php 
    echo __( 'Background Color:', 'open-user-map' );
    ?></label>
                      <div id="oum_custom_image_background_color_wrap" style="margin-top: 8px;">
                        <input type="text" class="oum_colorpicker" name="oum_custom_image_background_color" id="oum_custom_image_background_color" value="<?php 
    echo esc_attr( get_option( 'oum_custom_image_background_color', '#ffffff' ) );
    ?>" placeholder="<?php 
    echo esc_attr( get_option( 'oum_custom_image_background_color', '#ffffff' ) );
    ?>"></input>
                      </div>
                      <p class="description"><?php 
    echo __( 'Background color when map tiles are hidden.', 'open-user-map' );
    ?></p>
                    </div>
                  </div>
                </div>

              </td>
            </tr>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-general-markers', __( 'Markers & appearance', 'open-user-map' ), __( 'Customize marker icons, interface colors, and location popups.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Default Marker Icon', 'open-user-map' );
    ?>
              </th>
              <td>
                <div class="marker_icons">
                  <?php 
    $marker_icon = ( get_option( 'oum_marker_icon' ) ? get_option( 'oum_marker_icon' ) : 'default' );
    $items = $this->marker_icons;
    $marker_icon_labels = array(
        'default'  => __( 'Red marker', 'open-user-map' ),
        'custom1'  => __( 'Blue marker', 'open-user-map' ),
        'custom2'  => __( 'Green marker', 'open-user-map' ),
        'custom3'  => __( 'Yellow marker', 'open-user-map' ),
        'custom4'  => __( 'Black marker', 'open-user-map' ),
        'custom5'  => __( 'Gray marker', 'open-user-map' ),
        'custom6'  => __( 'Alert marker', 'open-user-map' ),
        'custom7'  => __( 'Speech marker', 'open-user-map' ),
        'custom8'  => __( 'Check marker', 'open-user-map' ),
        'custom9'  => __( 'Home marker', 'open-user-map' ),
        'custom10' => __( 'Person marker', 'open-user-map' ),
    );
    foreach ( $items as $val ) {
        $selected = ( $marker_icon == $val ? 'checked' : '' );
        echo '<label class="' . $selected . '"><div class="marker_icon_preview" data-style="' . esc_attr( $val ) . '"></div><input type="radio" aria-label="' . esc_attr( $marker_icon_labels[$val] ?? $val ) . '" name="oum_marker_icon" ' . $selected . ' value="' . esc_attr( $val ) . '"></label>';
    }
    ?>

                  <?php 
    ?>

                  <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>

                    <?php 
        //pro marker icons
        $pro_items = $this->pro_marker_icons;
        foreach ( $pro_items as $val ) {
            echo '<div class="oum-pro-choice">';
            echo "<label class='pro-only label_marker_user_icon'><div class='marker_icon_preview' data-style='{$val}'></div>";
            echo "\n                        <div class='icon_upload'>\n                          <button disabled class='button button-secondary'>" . __( 'Upload Icon', 'open-user-map' ) . "</button>\n                          <p class='description'>PNG, max. 100px</p>\n                        </div>\n                      ";
            echo "</label>";
            $this->render_pro_feature_help( __( 'Custom marker icons', 'open-user-map' ), __( 'Use your own PNG marker icon. Built-in marker icons remain available in the Free version.', 'open-user-map' ) );
            echo "</div>";
        }
        ?>

                  <?php 
    }
    ?>

                </div>
              </td>
            </tr>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              <tr class="oum-gopro-tr" valign="top">
                  <th scope="row">
                    <?php 
        echo __( 'Multi-Categories Icon', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Multi-Categories Icon', 'open-user-map' ), __( 'This icon is used for locations with more than one marker category.', 'open-user-map' ) );
        ?>
                    <br><span class="oum-pro">PRO</span><br>
                    <?php 
        $this->render_pro_feature_actions( true );
        ?>
                  </th>
                  <td>
                    <?php 
        $oum_marker_multicategories_icon = ( get_option( 'oum_marker_multicategories_icon' ) ? get_option( 'oum_marker_multicategories_icon' ) : $this->oum_marker_multicategories_icon_default );
        ?>
                    <!-- Multi-categories icon upload section -->
                    <div class="wrapper_marker_multicategories_icon">
                      <div class="marker_icon_preview" style="background-image: url('<?php 
        echo esc_attr( $oum_marker_multicategories_icon );
        ?>');"></div>
                      <div class="icon_upload">
                        <button disabled class="oum_upload_multicategories_icon_button button button-secondary"><?php 
        echo __( 'Upload Icon', 'open-user-map' );
        ?></button>
                        <p class="description">PNG, max. 100px</p>
                      </div>
                    </div>

                  </td>
                </tr>

            <?php 
    }
    ?>

            <tr valign="top">
              <?php 
    $oum_ui_color = ( get_option( 'oum_ui_color' ) ? get_option( 'oum_ui_color' ) : $this->oum_ui_color_default );
    ?>
              <th scope="row">
                <?php 
    echo __( 'UI Elements color', 'open-user-map' );
    ?>
              </th>
              <td>
                <div id="oum_ui_color_wrap">
                  <input type="text" class="oum_colorpicker" name="oum_ui_color" value="<?php 
    echo esc_attr( $oum_ui_color );
    ?>" placeholder="<?php 
    echo esc_attr( $oum_ui_color );
    ?>"></input>
                </div>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_hide_location_popup = get_option( 'oum_hide_location_popup', '' );
    ?>
              <th scope="row"><?php 
    echo __( 'Hide Marker Popups', 'open-user-map' );
    ?></th>
              <td>
                <input class="oum-switch" type="checkbox" name="oum_hide_location_popup" id="oum_hide_location_popup" <?php 
    echo ( $oum_hide_location_popup === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_hide_location_popup"></label><br><br>
                <span class="description"><?php 
    echo __( 'If enabled, clicking a marker does not open a popup.', 'open-user-map' );
    ?></span><br><br>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Image Size in Popup', 'open-user-map' );
    ?>
              </th>
              <td>
                <select name="oum_popup_image_size" id="oum_popup_image_size">
                  <?php 
    $oum_popup_image_size = ( get_option( 'oum_popup_image_size' ) ? get_option( 'oum_popup_image_size' ) : 'original' );
    $items = array(
        'original'   => __( 'Original Size (default)', 'open-user-map' ),
        'cover-4-3'  => __( 'Cover 4:3', 'open-user-map' ),
        'cover-16-9' => __( 'Cover 16:9', 'open-user-map' ),
        'cover-1-1'  => __( 'Cover 1:1', 'open-user-map' ),
    );
    foreach ( $items as $val => $label ) {
        $selected = ( $oum_popup_image_size == $val ? 'selected' : '' );
        echo '<option value="' . esc_textarea( $val ) . '" ' . $selected . '>' . esc_textarea( $label ) . '</option>';
    }
    ?>
                </select>
                <br><br>
                <span class="description"><?php 
    echo __( 'Choose how images are displayed in the location popup.', 'open-user-map' );
    ?></span>
              </td>
            </tr>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-general-view', __( 'Map size & initial view', 'open-user-map' ), __( 'Set the map dimensions and the view visitors see first.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Map size', 'open-user-map' );
    ?>
              </th>
              <td>
                <select name="oum_map_size" id="oum_map_size">
                  <?php 
    $map_size = ( get_option( 'oum_map_size' ) ? get_option( 'oum_map_size' ) : 'default' );
    $oum_map_height = get_option( 'oum_map_height' );
    $items = $this->oum_map_sizes;
    foreach ( $items as $val => $label ) {
        $selected = ( $map_size == $val ? 'selected' : '' );
        echo '<option value="' . esc_textarea( $val ) . '" ' . $selected . '>' . esc_textarea( $label ) . '</option>';
    }
    ?>
                </select>
                <br><br>
                <strong><?php 
    echo __( 'Custom Height:', 'open-user-map' );
    ?></strong><br>
                <input class="regular-text" type="text" name="oum_map_height" id="oum_map_height" placeholder="e.g. 400px" value="<?php 
    echo esc_attr( $oum_map_height );
    ?>"><br><br>
                <div class="description"><?php 
    echo __( 'Don\'t forget to add a unit like <b>px</b>.', 'open-user-map' );
    ?></div>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Map size (mobile)', 'open-user-map' );
    ?>
              </th>
              <td>
                <?php 
    $oum_map_height_mobile = get_option( 'oum_map_height_mobile' );
    ?>
                <strong><?php 
    echo __( 'Custom Height:', 'open-user-map' );
    ?></strong><br>
                <input class="regular-text" type="text" name="oum_map_height_mobile" id="oum_map_height_mobile" placeholder="e.g. 400px" value="<?php 
    echo esc_attr( $oum_map_height_mobile );
    ?>"><br><br>
                <div class="description"><?php 
    echo __( 'Don\'t forget to add a unit like <b>px</b>.', 'open-user-map' );
    ?></div>
              </td>
            </tr>

            <tr class="top">
              <th scope="row">
                <label><?php 
    echo __( 'Initial map view', 'open-user-map' );
    ?></label><br>
                <span class="description"><?php 
    echo __( 'This can be customized in the Block / Shortcode settings.', 'open-user-map' );
    ?></span><br>
              </th>
              <td>
                <?php 
    $start_lat = get_option( 'oum_start_lat' );
    $start_lng = get_option( 'oum_start_lng' );
    $start_zoom = get_option( 'oum_start_zoom' );
    $oum_enable_fixed_map_bounds = get_option( 'oum_enable_fixed_map_bounds' );
    $oum_searchaddress_label = ( get_option( 'oum_searchaddress_label' ) ? get_option( 'oum_searchaddress_label' ) : $this->oum_get_default_label( 'searchaddress' ) );
    ?>
                <div class="form-field geo-coordinates-wrap">
                    <div class="map-wrap">
                        <div id="mapGetInitial" class="leaflet-map map-style_<?php 
    echo esc_attr( $map_style );
    echo ( $map_style === 'CustomImage' && get_option( 'oum_custom_image_clickable', '' ) !== 'on' ? ' oum-custom-image-click-through' : '' );
    ?>"></div>
                    </div>
                    <div class="input-wrap">
                        <div class="latlng-wrap">
                            <div class="form-field lat-wrap">
                                <label class="meta-label" for="oum_start_lat">
                                    <?php 
    echo __( 'Lat', 'open-user-map' );
    ?>
                                </label>
                                <input type="text" readonly class="widefat" id="oum_start_lat" name="oum_start_lat" value="<?php 
    echo esc_attr( $start_lat );
    ?>"></input>
                            </div>
                            <div class="form-field lng-wrap">
                                <label class="meta-label" for="oum_start_lng">
                                    <?php 
    echo __( 'Lng', 'open-user-map' );
    ?>
                                </label>
                                <input type="text" readonly class="widefat" id="oum_start_lng" name="oum_start_lng" value="<?php 
    echo esc_attr( $start_lng );
    ?>"></input>
                            </div>
                            <div class="form-field zoom-wrap">
                                <label class="meta-label" for="oum_start_zoom">
                                    <?php 
    echo __( 'Zoom', 'open-user-map' );
    ?>
                                </label>
                                <input type="text" readonly class="widefat" id="oum_start_zoom" name="oum_start_zoom" value="<?php 
    echo ( esc_attr( $start_zoom ) ? esc_attr( $start_zoom ) : '' );
    ?>"></input>
                            </div>
                        </div>

                        <div class="geo-coordinates-hint">
                            <strong><?php 
    echo __( 'How to adjust the initial view:', 'open-user-map' );
    ?></strong>
                            <ol>
                                <li><?php 
    echo __( 'Use the map to the left to find your area of interest', 'open-user-map' );
    ?></li>
                                <li><?php 
    echo __( 'Zoom and pan the map to set the perfect initial view', 'open-user-map' );
    ?><br><br><strong>💡 <?php 
    echo __( 'Tip:', 'open-user-map' );
    ?></strong> <?php 
    echo __( 'Hold down the Shift key + mouse to zoom in on an area.', 'open-user-map' );
    ?></li>
                            </ol>
                        </div>

                        <div class="additional-map-settings">

                          <input class="oum-switch" type="checkbox" id="oum_enable_fixed_map_bounds" name="oum_enable_fixed_map_bounds" <?php 
    echo ( $oum_enable_fixed_map_bounds == 'on' ? 'checked' : '' );
    ?>>
                          <label for="oum_enable_fixed_map_bounds"><?php 
    echo __( 'Keep map focus in fixed position', 'open-user-map' );
    ?></label><br>
                          <span class="description"><?php 
    echo __( 'If enabled, the visible map will try to stay in the boundaries. (Initial Map View).', 'open-user-map' );
    ?> <?php 
    echo __( 'This does not work when using Custom Map Positions (e.g. Regions).', 'open-user-map' );
    ?></span>

                        </div>
                    </div>

                    <script type="text/javascript" data-category="functional" class="cmplz-native" id="oum-inline-js">
                    const lat = '<?php 
    echo ( esc_attr( $start_lat ) ? esc_attr( $start_lat ) : '26' );
    ?>';
                    const lng = '<?php 
    echo ( esc_attr( $start_lng ) ? esc_attr( $start_lng ) : '0' );
    ?>';
                    const zoom = '<?php 
    echo ( esc_attr( $start_zoom ) ? esc_attr( $start_zoom ) : '1' );
    ?>';
                    const mapStyle = '<?php 
    echo $map_style;
    ?>';
                    window.oum_tile_provider_mapbox_key = <?php 
    echo wp_json_encode( (string) $oum_tile_provider_mapbox_key );
    ?>;
                    window.oum_tile_provider_carto_key = <?php 
    echo wp_json_encode( (string) $oum_tile_provider_carto_key );
    ?>;
                    let oum_geosearch_selected_provider = "";
                    const oum_geosearch_provider = <?php 
    echo wp_json_encode( (string) (( get_option( 'oum_geosearch_provider' ) ? get_option( 'oum_geosearch_provider' ) : 'osm' )) );
    ?>;
                    const oum_geosearch_provider_geoapify_key = <?php 
    echo wp_json_encode( (string) get_option( 'oum_geosearch_provider_geoapify_key', '' ) );
    ?>;
                    const oum_geosearch_provider_here_key = <?php 
    echo wp_json_encode( (string) get_option( 'oum_geosearch_provider_here_key', '' ) );
    ?>;
                    const oum_geosearch_provider_mapbox_key = <?php 
    echo wp_json_encode( (string) get_option( 'oum_geosearch_provider_mapbox_key', '' ) );
    ?>;
                    const oum_searchaddress_label = <?php 
    echo wp_json_encode( (string) $oum_searchaddress_label );
    ?>;
                    
                    // Custom Image data
                    window.oum_custom_image_url = <?php 
    echo wp_json_encode( (string) get_option( 'oum_custom_image_url', '' ) );
    ?>;
                    window.oum_custom_image_bounds = <?php 
    $bounds = get_option( 'oum_custom_image_bounds', '' );
    if ( empty( $bounds ) ) {
        echo '{}';
    } else {
        $bounds_array = maybe_unserialize( $bounds );
        if ( is_array( $bounds_array ) ) {
            echo json_encode( $bounds_array );
        } else {
            echo '{}';
        }
    }
    ?>;
                    window.oum_custom_image_hide_tiles = <?php 
    echo ( get_option( 'oum_custom_image_hide_tiles', '' ) === 'on' ? 'true' : 'false' );
    ?>;
                    window.oum_custom_image_clickable = <?php 
    echo ( get_option( 'oum_custom_image_clickable', '' ) === 'on' ? 'true' : 'false' );
    ?>;
                    window.oum_custom_image_background_color = <?php 
    echo wp_json_encode( (string) get_option( 'oum_custom_image_background_color', '#ffffff' ) );
    ?>;
                    </script>

                    <?php 
    // Load map base scripts and vector dependencies when the selected style needs them.
    $this->include_map_scripts( $map_style );
    wp_enqueue_script(
        'oum_backend_settings_js',
        $this->plugin_url . 'src/js/backend-settings.js',
        array(
            'oum_leaflet_providers_js',
            'oum_leaflet_markercluster_js',
            'oum_leaflet_subgroups_js',
            'oum_leaflet_geosearch_js',
            'oum_leaflet_locate_js',
            'oum_leaflet_fullscreen_js',
            'oum_leaflet_search_js',
            'oum_leaflet_gesture_js',
            'oum_global_leaflet_js',
            'oum_map_styles_js',
            'oum_backend_image_position_editor_js'
        ),
        $this->plugin_version
    );
    ?>
                    
                </div>
              </td>
            </tr>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-general-controls', __( 'Map controls', 'open-user-map' ), __( 'Configure navigation, clustering, and address search.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <?php 
    $oum_enable_cluster = get_option( 'oum_enable_cluster', 'on' );
    ?>
              <th scope="row"><?php 
    echo __( 'Pins Clustering (group nearby markers)', 'open-user-map' );
    ?></th>
              <td>
                <input class="oum-switch" type="checkbox" name="oum_enable_cluster" id="oum_enable_cluster" <?php 
    echo ( $oum_enable_cluster === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_cluster"></label><br><br>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_enable_scrollwheel_zoom_map = get_option( 'oum_enable_scrollwheel_zoom_map' );
    ?>
              <th scope="row"><?php 
    echo __( 'Scroll Wheel Zoom', 'open-user-map' );
    ?></th>
              <td>
                <input class="oum-switch" type="checkbox" name="oum_enable_scrollwheel_zoom_map" id="oum_enable_scrollwheel_zoom_map" <?php 
    echo ( $oum_enable_scrollwheel_zoom_map === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_scrollwheel_zoom_map"></label>
                <br><br>
                <span class="description">💡 <?php 
    echo __( '<strong>Tip:</strong> Not recommended for full-width maps, as scrolling the page may cause the map to zoom unintentionally.', 'open-user-map' );
    ?></span><br>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_enable_fullscreen = get_option( 'oum_enable_fullscreen', 'on' );
    ?>
              <th scope="row"><?php 
    echo __( 'Full Screen Button', 'open-user-map' );
    ?></th>
              <td>
                <input class="oum-switch" type="checkbox" name="oum_enable_fullscreen" id="oum_enable_fullscreen" <?php 
    echo ( $oum_enable_fullscreen === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_fullscreen"></label><br><br>
              </td>
            </tr>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>

              <tr valign="top">
                <th scope="row">
                  <?php 
        echo __( 'Geosearch Provider', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Geosearch Provider', 'open-user-map' ), __( 'Use additional address search providers such as Geoapify, Here Maps, or MapBox. These providers require their own API key. OpenStreetMap remains available in the Free version.', 'open-user-map' ) );
        ?>
                </th>
                <td>
                  <select name="oum_geosearch_provider" id="oum_geosearch_provider">
                    <?php 
        $available_geosearch_providers = $this->oum_geosearch_provider;
        $not_available_geosearch_providers = $this->pro_oum_geosearch_provider;
        foreach ( $available_geosearch_providers as $val => $label ) {
            echo '<option value="' . esc_textarea( $val ) . '" selected>' . esc_textarea( $label ) . '</option>';
        }
        foreach ( $not_available_geosearch_providers as $val => $label ) {
            echo '<option disabled>' . esc_textarea( $label ) . '</option>';
        }
        ?>
                  </select>
                </td>
              </tr>

            <?php 
    }
    ?>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>

              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( '"Show me where I am" Button', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( '"Show me where I am" Button', 'open-user-map' ), __( 'Display a button that moves the map to the visitor\'s current location.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label></label>
                </td>
              </tr>

            <?php 
    }
    ?>

            <?php 
    ?>

            <?php 
    // Show this recommendation only when WooCommerce is installed.
    $oum_is_woocommerce_installed = class_exists( 'WooCommerce' ) || defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
    ?>
            <?php 
    if ( $oum_is_woocommerce_installed ) {
        ?>
              <tr valign="top">
                <th scope="row">
                  <?php 
        echo __( 'For WooCommerce Stores', 'open-user-map' );
        ?>
                </th>
                <td>
                  <p>
                    <img src="<?php 
        echo esc_url( $this->plugin_url . 'assets/images/customer-reach-map-logo.png' );
        ?>" alt="" width="24" height="24" style="vertical-align: middle; margin-right: 8px;">
                    <strong><?php 
        echo __( 'Customer Reach Map', 'open-user-map' );
        ?></strong>
                  </p>
                  <p style="width: 700px; max-width: 100%;"><?php 
        echo __( 'A separate WooCommerce plugin from the creator of Open User Map. Turn your existing WooCommerce orders into an interactive heatmap and instantly see where your customers are located.', 'open-user-map' );
        ?></p>
                  <p>
                    <a href="<?php 
        echo esc_url( 'https://customer-reach-map.com/?ref=oum' );
        ?>" target="_blank" rel="noopener noreferrer">
                      <?php 
        echo __( 'Learn more ↗', 'open-user-map' );
        ?>
                    </a>
                  </p>
                </td>
              </tr>
            <?php 
    }
    ?>

          </tbody>
          </table>
        </div>

        <div id="tab-2" class="oum-tab-pane <?php 
    echo ( $active_tab === 'tab-2' ? 'active' : '' );
    ?>">

          <?php 
    $oum_enable_add_location = get_option( 'oum_enable_add_location', 'on' );
    ?>
          <div class="community-tab-disabled-message" <?php 
    echo ( $oum_enable_add_location === 'on' ? 'style="display:none;"' : '' );
    ?>>
            <p class="description"><?php 
    echo __( 'Visitor submissions are currently disabled. The Submission Form section below still applies when administrators add locations. Enable "Allow visitors to add locations" in the General tab to configure access, moderation, after-submission behavior, and notifications.', 'open-user-map' );
    ?></p>
          </div>

          <?php 
    // Access, moderation, after-submit, and notifications only apply when visitors may add locations.
    $oum_community_gated_style = ( $oum_enable_add_location === 'on' ? '' : 'style="display:none;"' );
    ?>
          <?php 
    $this->render_settings_quick_links( array(
        'community-section-form'          => __( 'Submission Form', 'open-user-map' ),
        'community-section-access'        => __( 'Access & Permissions', 'open-user-map' ),
        'community-section-moderation'    => __( 'Submission & Moderation', 'open-user-map' ),
        'community-section-submit'        => __( 'After Submission', 'open-user-map' ),
        'community-section-notifications' => __( 'Notifications', 'open-user-map' ),
    ) );
    ?>

          <?php 
    // Submission form: always shown so admins can configure fields while visitor adds are disabled.
    ?>
          <div class="wrap-community-tab-settings wrap-community-tab-submission-form">
          <table class="form-table">
          <tbody class="community-group-box">
            <tr class="community-group-heading" id="community-section-form">
              <td colspan="2">
                <div class="community-group-heading-inner">
                  <h3><?php 
    echo __( 'Submission Form', 'open-user-map' );
    ?></h3>
                  <p><?php 
    echo __( 'Control which fields contributors see and what information is required.', 'open-user-map' );
    ?></p>
                </div>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_plus_button_label = get_option( 'oum_plus_button_label' );
    ?>
              <th scope="row">
                <?php 
    echo __( '"Add Location" Button Label', 'open-user-map' );
    ?>
              </th>
              <td>
                <input class="regular-text" type="text" name="oum_plus_button_label" id="oum_plus_button_label" placeholder="<?php 
    echo __( 'Add Location', 'open-user-map' );
    ?>" value="<?php 
    echo esc_textarea( $oum_plus_button_label );
    ?>"></input><br><br>
                <span class="description"><?php 
    echo __( 'Customize the text shown on the frontend submission button.', 'open-user-map' );
    ?></span>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_form_headline = get_option( 'oum_form_headline' );
    ?>
              <th scope="row"><?php 
    echo __( 'Submission Form Headline', 'open-user-map' );
    ?></th>
              <td>
                <input class="regular-text" type="text" name="oum_form_headline" id="oum_form_headline" placeholder="<?php 
    echo __( 'Add a new location', 'open-user-map' );
    ?>" value="<?php 
    echo esc_textarea( $oum_form_headline );
    ?>"></input><br>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_enable_title = get_option( 'oum_enable_title', 'on' );
    $oum_title_required = get_option( 'oum_title_required', 'on' );
    $oum_title_label = get_option( 'oum_title_label' );
    $oum_title_maxlength = get_option( 'oum_title_maxlength' );
    ?>
              <th scope="row"><?php 
    echo __( 'Title Field', 'open-user-map' );
    ?></th>
              <td>
                <div class="oum_2cols">
                  <div>
                    <input class="oum-switch" type="checkbox" name="oum_enable_title" id="oum_enable_title" <?php 
    echo ( $oum_enable_title == 'on' ? 'checked' : '' );
    ?>>
                    <label for="oum_enable_title"><?php 
    echo __( 'Enable', 'open-user-map' );
    ?></label>
                  </div>
                  <div>
                    <input class="oum-switch" type="checkbox" name="oum_title_required" id="oum_title_required" <?php 
    echo ( $oum_title_required ? 'checked' : '' );
    ?>>
                    <label for="oum_title_required"><?php 
    echo __( 'Required', 'open-user-map' );
    ?></label>
                  </div>
                  <div>
                    <input class="small-text oum_title_maxlength" type="number" min="0" name="oum_title_maxlength" id="oum_title_maxlength" value="<?php 
    echo ( isset( $oum_title_maxlength ) ? esc_attr( $oum_title_maxlength ) : '' );
    ?>" />
                    <label for="oum_title_maxlength"><?php 
    echo __( 'Maximum length', 'open-user-map' );
    ?></label>
                  </div>
                </div>
                <br>
                <strong><?php 
    echo __( 'Field Label:', 'open-user-map' );
    ?></strong><br>
                <input class="regular-text" type="text" name="oum_title_label" id="oum_title_label" placeholder="<?php 
    echo esc_attr( $this->oum_get_default_label( 'title' ) );
    ?>" value="<?php 
    echo esc_attr( $oum_title_label );
    ?>">
                <br><br>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_map_label = get_option( 'oum_map_label' );
    ?>
              <th scope="row"><?php 
    echo __( 'Map Field', 'open-user-map' );
    ?></th>
              <td>
                <strong><?php 
    echo __( 'Field Label:', 'open-user-map' );
    ?></strong><br>
                <input class="regular-text" type="text" name="oum_map_label" id="oum_map_label" placeholder="<?php 
    echo esc_attr( $this->oum_get_default_label( 'map' ) );
    ?>" value="<?php 
    echo esc_attr( $oum_map_label );
    ?>">
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Custom fields', 'open-user-map' );
    ?>
                    <?php 
    $this->render_pro_feature_help( __( 'Custom fields', 'open-user-map' ), __( 'PRO adds field types such as links, checkboxes, radio buttons, dropdowns, and Opening Hours. Basic custom fields remain available in the Free version.', 'open-user-map' ) );
    ?>
                <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
                  <?php 
        // PRO field types are marked in the field-type column below.
        ?>
                <?php 
    }
    ?>
              </th>
              <td>
                <div class="oum_custom_fields_wrapper" tabindex="0" role="region" aria-label="<?php 
    echo esc_attr__( 'Custom fields', 'open-user-map' );
    ?>"
                  data-move-up-label="<?php 
    echo esc_attr__( 'Move field up', 'open-user-map' );
    ?>"
                  data-move-down-label="<?php 
    echo esc_attr__( 'Move field down', 'open-user-map' );
    ?>"
                  data-remove-label="<?php 
    echo esc_attr__( 'Remove field', 'open-user-map' );
    ?>">
                  <?php 
    $oum_custom_fields = get_option( 'oum_custom_fields' );
    ?>
                  <table>
                    <thead>
                      <tr>
                        <th><?php 
    echo __( 'Field Label', 'open-user-map' );
    ?></th>
                        <th><?php 
    echo __( 'Required', 'open-user-map' );
    ?></th>
                        <th><?php 
    echo __( 'Private', 'open-user-map' );
    ?></th>
                        <th><?php 
    echo __( 'Maximum length', 'open-user-map' );
    ?></th>
                        <th><?php 
    echo __( 'Field type', 'open-user-map' );
    ?> <span class="oum-pro">PRO</span></th>
                        <th><?php 
    echo __( 'Options', 'open-user-map' );
    ?></th>
                        <th><?php 
    echo __( 'Description', 'open-user-map' );
    ?></th>
                        <th></th>
                      </tr>
                    </thead>

                    <tbody>
                    <?php 
    if ( is_array( $oum_custom_fields ) ) {
        ?>
                      <?php 
        foreach ( $oum_custom_fields as $index => $custom_field ) {
            ?>
                        <tr data-field-id="<?php 
            echo esc_attr( $index );
            ?>">
                          <td>
                            <input type="text" class="field-type-text field-type-link field-type-email field-type-checkbox field-type-radio field-type-select" name="oum_custom_fields[<?php 
            echo $index;
            ?>][label]" placeholder="<?php 
            echo __( 'Enter label', 'open-user-map' );
            ?>" value="<?php 
            echo esc_attr( $custom_field['label'] );
            ?>" />
                          </td>
                          <td>
                            <input class="oum-switch field-type-text field-type-link field-type-email field-type-checkbox field-type-radio field-type-select" id="oum_custom_fields_<?php 
            echo $index;
            ?>_required" type="checkbox" name="oum_custom_fields[<?php 
            echo $index;
            ?>][required]" <?php 
            echo ( isset( $custom_field['required'] ) ? 'checked' : '' );
            ?> /><label class="field-type-text field-type-link field-type-email field-type-checkbox field-type-radio field-type-select" for="oum_custom_fields_<?php 
            echo $index;
            ?>_required"></label>
                          </td>
                          <td>
                            <input class="oum-switch field-type-text field-type-link field-type-email field-type-checkbox field-type-radio field-type-select" id="oum_custom_fields_<?php 
            echo $index;
            ?>_private" type="checkbox" name="oum_custom_fields[<?php 
            echo $index;
            ?>][private]" <?php 
            echo ( isset( $custom_field['private'] ) ? 'checked' : '' );
            ?> /><label class="field-type-text field-type-link field-type-email field-type-checkbox field-type-radio field-type-select" for="oum_custom_fields_<?php 
            echo $index;
            ?>_private"></label>
                          </td>
                          <td>
                            <input class="small-text field-type-text field-type-link field-type-email" type="number" min="0" name="oum_custom_fields[<?php 
            echo $index;
            ?>][maxlength]" value="<?php 
            echo ( isset( $custom_field['maxlength'] ) ? esc_attr( $custom_field['maxlength'] ) : '' );
            ?>" />
                          </td>
                          <td>
                            <select class="oum-custom-field-fieldtype" name="oum_custom_fields[<?php 
            echo $index;
            ?>][fieldtype]">
                              <?php 
            $available_field_types = $this->oum_custom_field_fieldtypes;
            ?>

                              <?php 
            ?>

                              <?php 
            foreach ( $available_field_types as $value => $label ) {
                ?>
                                <?php 
                $selected = ( isset( $custom_field['fieldtype'] ) && $custom_field['fieldtype'] == $value ? 'selected' : '' );
                ?>

                                <?php 
                echo '<option value="' . esc_textarea( $value ) . '" ' . $selected . '>' . esc_textarea( $label ) . '</option>';
                ?>

                              <?php 
            }
            ?>
                            </select>
                          </td>
                          <td>
                            <input type="text" class="regular-text field-type-checkbox field-type-radio field-type-select" name="oum_custom_fields[<?php 
            echo $index;
            ?>][options]" placeholder="Red|Blue|Green" value="<?php 
            echo ( isset( $custom_field['options'] ) ? esc_attr( $custom_field['options'] ) : '' );
            ?>" />
                            <label class="field-type-select oum-custom-field-allow-empty"><input class="field-type-select" type="checkbox" name="oum_custom_fields[<?php 
            echo $index;
            ?>][emptyoption]" <?php 
            echo ( isset( $custom_field['emptyoption'] ) ? 'checked' : '' );
            ?> ><?php 
            echo __( 'add empty option', 'open-user-map' );
            ?></label>
                            <label class="field-type-select oum-custom-field-allow-multiple"><input class="field-type-select" type="checkbox" name="oum_custom_fields[<?php 
            echo $index;
            ?>][multiple]" <?php 
            echo ( isset( $custom_field['multiple'] ) ? 'checked' : '' );
            ?> ><?php 
            echo __( 'allow multiple', 'open-user-map' );
            ?></label>
                            <label class="field-type-link oum-custom-field-use-label-as-text"><input class="field-type-link" type="checkbox" name="oum_custom_fields[<?php 
            echo $index;
            ?>][uselabelastextoption]" <?php 
            echo ( isset( $custom_field['uselabelastextoption'] ) ? 'checked' : '' );
            ?> ><?php 
            echo __( 'use label as text', 'open-user-map' );
            ?></label>
                            <label class="field-type-opening-hours oum-custom-field-use-12hour"><input class="field-type-opening-hours" type="checkbox" name="oum_custom_fields[<?php 
            echo $index;
            ?>][use12hour]" <?php 
            echo ( isset( $custom_field['use12hour'] ) ? 'checked' : '' );
            ?> ><?php 
            echo __( 'use 12-hour format', 'open-user-map' );
            ?></label>
                            <textarea class="regular-text field-type-html" name="oum_custom_fields[<?php 
            echo $index;
            ?>][html]" placeholder="Enter HTML here"><?php 
            echo ( isset( $custom_field['html'] ) ? esc_attr( $custom_field['html'] ) : '' );
            ?></textarea>
                          </td>
                          <td>
                            <input type="text" class="field-type-text field-type-link field-type-email field-type-checkbox field-type-radio field-type-select" name="oum_custom_fields[<?php 
            echo $index;
            ?>][description]" placeholder="<?php 
            echo __( 'Enter description (optional)', 'open-user-map' );
            ?>" value="<?php 
            echo ( isset( $custom_field['description'] ) ? esc_textarea( $custom_field['description'] ) : '' );
            ?>" />
                          </td>
                          <td class="actions">
                            <button type="button" class="up"><span class="dashicons dashicons-arrow-up"></span></button>
                            <button type="button" class="down"><span class="dashicons dashicons-arrow-down"></span></button>
                            <button type="button" class="remove_button"><span class="dashicons dashicons-trash"></span></button>
                          </td>
                        </tr>
                      <?php 
        }
        ?>
                    <?php 
    }
    ?>
                    </tbody>

                  </table>

                </div>
                <div>
                  <a href="#" class="oum_add_button button" title="Add field">Add field</a>
                </div>
                <br><br>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_enable_address = get_option( 'oum_enable_address', 'on' );
    $oum_hide_address = get_option( 'oum_hide_address' );
    $oum_enable_gmaps_link = get_option( 'oum_enable_gmaps_link', 'on' );
    $oum_address_label = get_option( 'oum_address_label' );
    $oum_enable_address_autofill = get_option( 'oum_enable_address_autofill' );
    ?>
              <th scope="row"><?php 
    echo __( 'Subtitle Field', 'open-user-map' );
    ?></th>
              <td>
                <input class="oum-switch" type="checkbox" name="oum_enable_address" id="oum_enable_address" <?php 
    echo ( $oum_enable_address === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_address"><?php 
    echo __( 'Enable', 'open-user-map' );
    ?></label><br>

                <input class="oum-switch" type="checkbox" name="oum_enable_address_autofill" id="oum_enable_address_autofill" <?php 
    echo ( $oum_enable_address_autofill === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_address_autofill"><?php 
    echo __( 'Auto-fill address from marker location (Reverse Geocoding)', 'open-user-map' );
    ?></label><br>

                <input class="oum-switch" type="checkbox" name="oum_hide_address" id="oum_hide_address" <?php 
    echo ( $oum_hide_address ? 'checked' : '' );
    ?>>
                <label for="oum_hide_address"><?php 
    echo __( 'Don\'t show inside Location Pop-Up', 'open-user-map' );
    ?></label><br>
                
                <input class="oum-switch" type="checkbox" name="oum_enable_gmaps_link" id="oum_enable_gmaps_link" <?php 
    echo ( $oum_enable_gmaps_link === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_gmaps_link"><?php 
    echo __( 'Link to Google Maps', 'open-user-map' );
    ?></label><br>

                <strong><?php 
    echo __( 'Field Label:', 'open-user-map' );
    ?></strong><br>
                <input class="regular-text" type="text" name="oum_address_label" id="oum_address_label" placeholder="<?php 
    echo esc_attr( $this->oum_get_default_label( 'address' ) );
    ?>" value="<?php 
    echo esc_attr( $oum_address_label );
    ?>">
                <br><br>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_enable_description = get_option( 'oum_enable_description', 'on' );
    $oum_description_required = get_option( 'oum_description_required' );
    $oum_description_label = get_option( 'oum_description_label' );
    ?>
              <th scope="row"><?php 
    echo __( 'Description Field', 'open-user-map' );
    ?></th>
              <td>
                <div class="oum_2cols">
                  <div>
                    <input class="oum-switch" type="checkbox" name="oum_enable_description" id="oum_enable_description" <?php 
    echo ( $oum_enable_description === 'on' ? 'checked' : '' );
    ?>>
                    <label for="oum_enable_description"><?php 
    echo __( 'Enable', 'open-user-map' );
    ?></label>
                  </div>
                  <div>
                    <input class="oum-switch" type="checkbox" name="oum_description_required" id="oum_description_required" <?php 
    echo ( $oum_description_required ? 'checked' : '' );
    ?>>
                    <label for="oum_description_required"><?php 
    echo __( 'Required', 'open-user-map' );
    ?></label>
                  </div>
                </div>
                <br>
                <strong><?php 
    echo __( 'Field Label:', 'open-user-map' );
    ?></strong><br>
                <input class="regular-text" type="text" name="oum_description_label" id="oum_description_label" placeholder="<?php 
    echo esc_attr( $this->oum_get_default_label( 'description' ) );
    ?>" value="<?php 
    echo esc_attr( $oum_description_label );
    ?>">
                <br><br>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_enable_image = get_option( 'oum_enable_image', 'on' );
    $oum_image_required = get_option( 'oum_image_required' );
    $oum_max_image_uploads = get_option( 'oum_max_image_uploads', 5 );
    $oum_enable_audio = get_option( 'oum_enable_audio', '' );
    $oum_audio_required = get_option( 'oum_audio_required' );
    $oum_enable_video = get_option( 'oum_enable_video', false );
    $oum_video_required = get_option( 'oum_video_required' );
    $oum_upload_media_label = get_option( 'oum_upload_media_label' );
    ?>
              <th scope="row"><?php 
    echo __( 'Media upload fields', 'open-user-map' );
    ?></th>
              <td>

                <div class="oum_3cols">
                  <div>
                    <input class="oum-switch" type="checkbox" name="oum_enable_image" id="oum_enable_image" <?php 
    echo ( $oum_enable_image === 'on' ? 'checked' : '' );
    ?>>
                    <label for="oum_enable_image"><?php 
    echo __( 'Image', 'open-user-map' );
    ?></label>
                  </div>
                  <div>
                    <input class="oum-switch" type="checkbox" name="oum_image_required" id="oum_image_required" <?php 
    echo ( $oum_image_required ? 'checked' : '' );
    ?>>
                    <label for="oum_image_required"><?php 
    echo __( 'Required', 'open-user-map' );
    ?></label>
                  </div>
                </div>
                <br>
                <div>
                    <strong><?php 
    echo __( 'Max number of images:', 'open-user-map' );
    ?></strong><br>
                    <input class="small-text" type="number" min="1" max="5" name="oum_max_image_uploads" id="oum_max_image_uploads" value="<?php 
    echo esc_attr( $oum_max_image_uploads );
    ?>"></input>
                    <span class="description"><?php 
    echo __( 'Set the maximum number of images users can upload per location (1-5). Default: 5', 'open-user-map' );
    ?></span>
                  </div>
                <br><br>

                <?php 
    ?>

                <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>

                  <div class="oum_2cols">
                      <div class="oum-gopro-div">
                        <input class="oum-switch" type="checkbox" disabled>
                        <label><?php 
        echo __( 'Video (YouTube, Vimeo)', 'open-user-map' );
        ?></label>
                      </div>
                      <div class="oum-gopro-div">
                        <input class="oum-switch" type="checkbox" disabled>
                        <label><?php 
        echo __( 'Required', 'open-user-map' );
        ?></label>
                      </div>
                      <div>
                        <span class="oum-pro">PRO</span>
                      </div>
                    </div>
                    <br><br>

                <?php 
    }
    ?>

                <div class="oum_2cols">
                  <div>
                    <input class="oum-switch" type="checkbox" name="oum_enable_audio" id="oum_enable_audio" <?php 
    echo ( $oum_enable_audio === 'on' ? 'checked' : '' );
    ?>>
                    <label for="oum_enable_audio"><?php 
    echo __( 'Audio', 'open-user-map' );
    ?></label>
                  </div>
                  <div>
                    <input class="oum-switch" type="checkbox" name="oum_audio_required" id="oum_audio_required" <?php 
    echo ( $oum_audio_required ? 'checked' : '' );
    ?>>
                    <label for="oum_audio_required"><?php 
    echo __( 'Required', 'open-user-map' );
    ?></label>
                  </div>
                </div>
                <br><br>

                <strong><?php 
    echo __( 'Field Label:', 'open-user-map' );
    ?></strong><br>
                <input class="regular-text" type="text" name="oum_upload_media_label" id="oum_upload_media_label" placeholder="<?php 
    echo esc_attr( $this->oum_get_default_label( 'upload_media' ) );
    ?>" value="<?php 
    echo esc_attr( $oum_upload_media_label );
    ?>">
                <br><br>
              </td>
            </tr>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Max upload size', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Max upload size', 'open-user-map' ), __( 'Set the maximum file size for image and audio uploads.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <div class="oum_2cols">
                    <div>
                      <strong><?php 
        echo __( 'Image' );
        ?>:</strong><br>
                      <input disabled class="small-text" type="number" min="1" value="10"></input>MB
                    </div>
                    <div>
                      <strong><?php 
        echo __( 'Audio' );
        ?>:</strong><br>
                      <input disabled class="small-text" type="number" min="1" value="10"></input>MB
                    </div>
                  </div>
                  <br><br>
                </td>
              </tr>

            <?php 
    }
    ?>
          </tbody>

          </table>
          </div>

          <div class="wrap-community-tab-settings wrap-community-tab-gated" <?php 
    echo $oum_community_gated_style;
    ?>>
          <table class="form-table">
          <tbody class="community-group-box">
            <tr class="community-group-heading" id="community-section-access">
              <td colspan="2">
                <div class="community-group-heading-inner">
                  <h3><?php 
    echo __( 'Access & Permissions', 'open-user-map' );
    ?></h3>
                  <p><?php 
    echo __( 'Control who is allowed to submit locations to your map.', 'open-user-map' );
    ?></p>
                </div>
              </td>
            </tr>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Require users to be logged in to submit locations', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Require users to be logged in to submit locations', 'open-user-map' ), __( 'If enabled, only registered users can add new locations. The minimum required role is "Subscriber". <a href="https://www.open-user-map.com/knowledge-base/redirect-users-to-the-map-after-login/?ref=settings">Here</a> is an article on how to redirect users to the map page after login.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label></label><br><br>
                  <select disabled>
                    <option><?php 
        echo __( 'Redirect "Add location"-Button for logged-out users', 'open-user-map' );
        ?></option>
                  </select><br><br>
                </td>
              </tr>
            <?php 
    }
    ?>
          </tbody>

          <tbody class="community-group-box">
            <tr class="community-group-heading" id="community-section-moderation">
              <td colspan="2">
                <div class="community-group-heading-inner">
                  <h3><?php 
    echo __( 'Submission & Moderation', 'open-user-map' );
    ?></h3>
                  <p><?php 
    echo __( 'Control how submissions are handled and whether they require approval.', 'open-user-map' );
    ?></p>
                </div>
              </td>
            </tr>

            <tr valign="top">
              <?php 
    $oum_enable_auto_publish = get_option( 'oum_enable_auto_publish', '' );
    $oum_enable_auto_publish_for_everyone = get_option( 'oum_enable_auto_publish_for_everyone', '' );
    ?>
              <th scope="row">
                <?php 
    echo __( 'Approval', 'open-user-map' );
    ?>
                <p class="description community-moderation-intro"><?php 
    echo __( 'By default, every new submission needs your approval before it is published. But you can allow registered users or guests to publish without approval.', 'open-user-map' );
    ?><br>
                <br><?php 
    echo __( 'Hint: Submissions from Administrators are published immediately.', 'open-user-map' );
    ?></p>
              </th>
              <td>
                <input class="oum-switch" type="checkbox" id="oum_enable_auto_publish" name="oum_enable_auto_publish" <?php 
    echo ( $oum_enable_auto_publish == 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_auto_publish"><?php 
    echo __( 'Allow <strong>registered users</strong> to publish without approval', 'open-user-map' );
    ?></label><br>
                <span class="description"><?php 
    echo __( 'Skip moderation for logged-in users.', 'open-user-map' );
    ?></span><br><br>

                <?php 
    ?>

                <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label><?php 
        echo __( 'Allow guests to publish without approval', 'open-user-map' );
        ?> <span class="oum-pro">PRO</span></label><?php 
        $this->render_pro_feature_help( __( 'Publish guest submissions without approval', 'open-user-map' ), __( 'Allow unregistered visitors to publish locations immediately, without admin approval.', 'open-user-map' ) );
        ?><br>
                  <span class="description community-guest-publish-warning"><strong>⚠️ <?php 
        echo __( 'Use with caution.', 'open-user-map' );
        ?></strong> <?php 
        echo __( 'Anyone can publish without moderation.', 'open-user-map' );
        ?> </span>
                <?php 
    }
    ?>
              </td>
            </tr>
          </tbody>

          </table>
          </div>

          <div class="wrap-community-tab-settings wrap-community-tab-gated" <?php 
    echo $oum_community_gated_style;
    ?>>
          <table class="form-table">
          <tbody class="community-group-box">
            <tr class="community-group-heading" id="community-section-submit">
              <td colspan="2">
                <div class="community-group-heading-inner">
                  <h3><?php 
    echo __( 'After Submission', 'open-user-map' );
    ?></h3>
                  <p><?php 
    echo __( 'Choose what happens after a visitor submits a location.', 'open-user-map' );
    ?></p>
                </div>
              </td>
            </tr>

            <?php 
    $oum_submit_button_label = get_option( 'oum_submit_button_label' );
    ?>
            <tr valign="top">
              <th scope="row"><?php 
    echo __( 'Submit button text', 'open-user-map' );
    ?></th>
              <td>
                <input class="regular-text" type="text" name="oum_submit_button_label" id="oum_submit_button_label" placeholder="<?php 
    echo __( 'Submit location for review', 'open-user-map' );
    ?>" value="<?php 
    echo esc_textarea( $oum_submit_button_label );
    ?>"></input><br>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row"><?php 
    echo __( 'What happens after submission?', 'open-user-map' );
    ?></th>
              <td>
                <select name="oum_action_after_submit" id="oum_action_after_submit">
                  <?php 
    $oum_action_after_submit = ( get_option( 'oum_action_after_submit' ) ? get_option( 'oum_action_after_submit' ) : 'text' );
    $items = array(
        'text'     => __( 'Show confirmation message', 'open-user-map' ),
        'refresh'  => __( 'Refresh', 'open-user-map' ),
        'redirect' => __( 'Redirect', 'open-user-map' ),
    );
    foreach ( $items as $val => $label ) {
        $selected = ( $oum_action_after_submit == $val ? 'selected' : '' );
        echo '<option value="' . esc_textarea( $val ) . '" ' . $selected . '>' . esc_textarea( $label ) . '</option>';
    }
    ?>
                </select>
                <br><br>
                <div id="oum_action_after_submit_text">
                  <?php 
    $oum_thankyou_headline = get_option( 'oum_thankyou_headline' );
    $oum_thankyou_text = get_option( 'oum_thankyou_text' );
    $oum_thankyou_buttontext = get_option( 'oum_thankyou_buttontext' );
    ?>
                  <input class="regular-text" type="text" name="oum_thankyou_headline" id="oum_thankyou_headline" placeholder="<?php 
    echo __( 'Thank you!', 'open-user-map' );
    ?>" value="<?php 
    echo esc_textarea( $oum_thankyou_headline );
    ?>"></input><br><br>
                  <textarea class="regular-text" name="oum_thankyou_text" id="oum_thankyou_text" rows="4" cols="50" placeholder="<?php 
    echo __( 'We will check your location suggestion and release it as soon as possible.', 'open-user-map' );
    ?>"><?php 
    echo esc_textarea( $oum_thankyou_text );
    ?></textarea><br><br>
                  <input class="regular-text" type="text" name="oum_thankyou_buttontext" id="oum_thankyou_buttontext" placeholder="<?php 
    echo __( 'Close and refresh map', 'open-user-map' );
    ?>" value="<?php 
    echo esc_textarea( $oum_thankyou_buttontext );
    ?>"></input><br>
                  <br><span class="description"><?php 
    echo __( 'Text shown to the user after submission.', 'open-user-map' );
    ?></span>
                </div>
                <div id="oum_action_after_submit_redirect">
                  <?php 
    $oum_thankyou_redirect = get_option( 'oum_thankyou_redirect' );
    ?>
                  <input class="regular-text" type="text" name="oum_thankyou_redirect" id="oum_thankyou_redirect" placeholder="<?php 
    echo 'https://loremipsum.com';
    ?>" value="<?php 
    echo esc_textarea( $oum_thankyou_redirect );
    ?>"></input>
                </div>
              </td>
            </tr>
          </tbody>

          <tbody class="community-group-box">
            <tr class="community-group-heading" id="community-section-notifications">
              <td colspan="2">
                <div class="community-group-heading-inner">
                  <h3><?php 
    echo __( 'Notifications', 'open-user-map' );
    ?></h3>
                  <p><?php 
    echo __( 'Send email notifications when submissions are created or approved.', 'open-user-map' );
    ?></p>
                </div>
              </td>
            </tr>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Notify contributor by email', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Notify contributor by email', 'open-user-map' ), __( 'Notify contributors by email when their location submission has been approved.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label></label><br><br>

                  <strong><?php 
        echo __( 'Field Label:', 'open-user-map' );
        ?></strong><br>
                  <input disabled class="regular-text" type="text" placeholder="<?php 
        echo esc_attr( $this->oum_get_default_label( 'user_notification' ) );
        ?>">
                  <br><br>
                  
                  <strong><?php 
        echo __( 'Subject' );
        ?>:</strong><br>
                  <input disabled class="regular-text" type="text" placeholder="<?php 
        echo __( 'Your location has been approved', 'open-user-map' );
        ?>"></input><br><br>

                  <strong><?php 
        echo __( 'Message' );
        ?>:</strong><br>
                  <textarea disabled class="regular-text" rows="8" cols="50" placeholder="<?php 
        echo __( 'Hey %name%! Your submission on %website_url% has been published!', 'open-user-map' );
        ?>"></textarea><br><br>
                  <span class="description"><?php 
        echo __( 'Available tags' );
        ?>: %name%, %website_url%, %website_name%</span>
                  <br><br>
                </td>
              </tr>

            <?php 
    }
    ?>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Notify admin about new submissions', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Notify admin about new submissions', 'open-user-map' ), __( 'Send an email to the configured admin address when a new location submission has been added.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label></label><br><br>
                  
                  <strong><?php 
        echo __( 'Email address' );
        ?>:</strong><br>
                  <input disabled class="regular-text" type="text" placeholder="<?php 
        echo __( 'john@doe.com', 'open-user-map' );
        ?>"></input><br><br>
                  
                  <strong><?php 
        echo __( 'Subject' );
        ?>:</strong><br>
                  <input disabled class="regular-text" type="text" placeholder="<?php 
        echo __( 'New Open User Map location', 'open-user-map' );
        ?>"></input><br><br>

                  <strong><?php 
        echo __( 'Message' );
        ?>:</strong><br>
                  <textarea disabled class="regular-text" rows="8" cols="50" placeholder="<?php 
        echo __( 'A new submission with the title "%title%" on %website_url% has been added! Please verify and publish.\\n\\n%edit_location_url%', 'open-user-map' );
        ?>"></textarea><br><br>
                  <span class="description"><?php 
        echo __( 'Available tags' );
        ?>: %title%, %website_url%, %website_name%, %edit_location_url%, %user_name%, %user_email%</span>
                  <br><br>
                </td>
              </tr>

            <?php 
    }
    ?>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Trigger Webhook on new or updated Locations', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Trigger Webhook on new or updated Locations', 'open-user-map' ), __( 'Trigger an external web service, such as Zapier, when a location is added or updated.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label></label><br><br>
                  
                  <strong><?php 
        echo __( 'Webhook URL' );
        ?>:</strong><br>
                  <input disabled class="regular-text" type="text"></input>
                </td>
              </tr>

            <?php 
    }
    ?>
          </tbody>

          </table>
          </div>

        </div>

        <div id="tab-3" class="oum-tab-pane oum-settings-groups <?php 
    echo ( $active_tab === 'tab-3' ? 'active' : '' );
    ?>">
          <?php 
    $this->render_settings_quick_links( array(
        'oum-search-filter' => __( 'Advanced filter', 'open-user-map' ),
        'oum-search-bar'    => __( 'Searchbar', 'open-user-map' ),
        'oum-search-shared' => __( 'Search labels & map buttons', 'open-user-map' ),
    ) );
    ?>
          <table class="form-table">
          <?php 
    $this->render_settings_group_start( 'oum-search-filter', __( 'Advanced filter', 'open-user-map' ), __( 'Build a sidebar with search and filter sections.', 'open-user-map' ) );
    ?>

            <!-- Advanced Filter Interface Settings -->
            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Advanced Filter Interface', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Advanced Filter Interface', 'open-user-map' ), __( 'Create a search and filter sidebar next to your map. Add sections for Searchbar, Marker Categories, any custom field, or custom HTML. <a href="https://www.open-user-map.com/knowledge-base/advanced-filter-interface/?ref=pluginsettings" target="_blank" rel="noopener noreferrer">Learn more about the Advanced Filter Interface →</a>', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label><?php 
        echo __( 'Enable', 'open-user-map' );
        ?></label><br>

                </td>
              </tr>

            <?php 
    }
    ?>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-search-bar', __( 'Searchbar', 'open-user-map' ), __( 'Choose the search types available in the searchbar.', 'open-user-map' ) );
    ?>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>

                <tr valign="top">
                  <th scope="row">
                    <?php 
        echo __( 'Searchbar', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Searchbar', 'open-user-map' ), __( 'PRO adds the Live Filter search type: filter markers live as you type to instantly refine the map view. Address and marker search remain available in the Free version.', 'open-user-map' ) );
        ?>
                  </th>
                  <td>
                    <?php 
        $oum_enable_searchbar = get_option( 'oum_enable_searchbar', '' );
        ?>
                    <input class="oum-switch" type="checkbox" name="oum_enable_searchbar" id="oum_enable_searchbar" <?php 
        echo ( $oum_enable_searchbar === 'on' ? 'checked' : '' );
        ?>>
                    <label for="oum_enable_searchbar"></label><br><br>
                    <div class="wrap-searchbar-settings">
                      <?php 
        $oum_searchbar_type = ( get_option( 'oum_searchbar_type' ) ? get_option( 'oum_searchbar_type' ) : 'address' );
        $items = $this->oum_searchbar_types;
        ?>

                      <div id="oum_searchbar_type_options">
                          <?php 
        foreach ( $items as $val => $label ) {
            $checked = ( $oum_searchbar_type == $val ? 'checked' : '' );
            ?>
                              <label>
                                  <input type="radio" name="oum_searchbar_type" value="<?php 
            echo esc_attr( $val );
            ?>" <?php 
            echo $checked;
            ?>>
                                  <strong><?php 
            echo esc_html( $label );
            ?></strong><?php 
            echo ( $val === 'live_filter' ? '&nbsp;&nbsp;<span class="oum-pro">PRO</span>' : '' );
            ?><br>
                                  <?php 
            if ( $val === 'address' ) {
                ?>
                                      <small><?php 
                echo __( 'Find a specific address – type to see matching suggestions below and locate them on the map.', 'open-user-map' );
                ?></small>
                                  <?php 
            } elseif ( $val === 'markers' ) {
                ?>
                                      <small><?php 
                echo __( 'Search for specific markers and see suggestions below as you type.', 'open-user-map' );
                ?></small>
                                  <?php 
            }
            ?>
                              </label>
                          <?php 
        }
        ?>
                          <label class="oum-gopro-div">
                            <input type="radio" disabled>
                            <strong><?php 
        echo __( 'Live Filter Markers', 'open-user-map' );
        ?></strong>&nbsp;&nbsp;<span class="oum-pro">PRO</span><br>
                          </label>
                      </div>
                    </div>
                  </td>
                </tr>

            <?php 
    }
    ?>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-search-shared', __( 'Search labels & map buttons', 'open-user-map' ), __( 'Labels and marker search zoom apply to both the searchbar and the separate map buttons. The switches below only control the separate buttons.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Address search', 'open-user-map' );
    ?>
              </th>
              <td>
                <?php 
    $oum_enable_searchaddress_button = get_option( 'oum_enable_searchaddress_button', 'on' );
    $oum_searchaddress_label = get_option( 'oum_searchaddress_label' );
    ?>
                <input class="oum-switch" type="checkbox" name="oum_enable_searchaddress_button" id="oum_enable_searchaddress_button" <?php 
    echo ( $oum_enable_searchaddress_button === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_searchaddress_button"><?php 
    echo esc_html__( 'Show separate map button', 'open-user-map' );
    ?></label><br>
                <label for="oum_searchaddress_label"><strong><?php 
    echo __( 'Custom Label:', 'open-user-map' );
    ?></strong></label><br>
                <input class="regular-text" type="text" name="oum_searchaddress_label" id="oum_searchaddress_label" placeholder="<?php 
    echo esc_attr( $this->oum_get_default_label( 'searchaddress' ) );
    ?>" value="<?php 
    echo esc_attr( $oum_searchaddress_label );
    ?>">
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Marker search', 'open-user-map' );
    ?>
              </th>
              <td>
                <?php 
    $oum_enable_searchmarkers_button = get_option( 'oum_enable_searchmarkers_button', 'on' );
    $oum_searchmarkers_label = get_option( 'oum_searchmarkers_label' );
    $oum_searchmarkers_zoom = get_option( 'oum_searchmarkers_zoom' );
    ?>
                <input class="oum-switch" type="checkbox" name="oum_enable_searchmarkers_button" id="oum_enable_searchmarkers_button" <?php 
    echo ( $oum_enable_searchmarkers_button === 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_searchmarkers_button"><?php 
    echo esc_html__( 'Show separate map button', 'open-user-map' );
    ?></label><br>
                <label for="oum_searchmarkers_label"><strong><?php 
    echo __( 'Custom Label:', 'open-user-map' );
    ?></strong></label><br>
                <input class="regular-text" type="text" name="oum_searchmarkers_label" id="oum_searchmarkers_label" placeholder="<?php 
    echo esc_attr( $this->oum_get_default_label( 'searchmarkers' ) );
    ?>" value="<?php 
    echo esc_attr( $oum_searchmarkers_label );
    ?>"><br><br>
                <label for="oum_searchmarkers_zoom"><strong><?php 
    echo __( 'Zoom level:', 'open-user-map' );
    ?></strong></label><br>
                <input class="small-text" type="number" min="1" max="19" name="oum_searchmarkers_zoom" id="oum_searchmarkers_zoom" placeholder="<?php 
    echo esc_attr( $this->oum_searchmarkers_zoom_default );
    ?>" value="<?php 
    echo esc_attr( $oum_searchmarkers_zoom );
    ?>"></input><br><br>
                <span class="description"><?php 
    echo __( 'Set a value between 1 (far away) and 19 (very close).', 'open-user-map' );
    ?></span><br>
              </td>
            </tr>

          </tbody>
          </table>
        </div>

        <div id="tab-categories" class="oum-tab-pane <?php 
    echo ( $active_tab === 'tab-categories' ? 'active' : '' );
    ?>">
          <table class="form-table oum-categories-shell">

            <?php 
    $oum_enable_location_type_marker = get_option( 'oum_enable_location_type_marker', 'on' );
    $oum_enable_location_type_polyline = get_option( 'oum_enable_location_type_polyline', '' );
    $oum_enable_location_type_polyline_distance = get_option( 'oum_enable_location_type_polyline_distance', 'on' );
    $oum_enable_location_type_polygon = get_option( 'oum_enable_location_type_polygon', '' );
    $oum_enable_location_type_polygon_area = get_option( 'oum_enable_location_type_polygon_area', 'on' );
    $oum_route_icon_path = $this->plugin_path . 'assets/images/ico_route.svg';
    $oum_area_icon_path = $this->plugin_path . 'assets/images/ico_area.svg';
    $oum_route_icon_svg = ( is_readable( $oum_route_icon_path ) ? file_get_contents( $oum_route_icon_path ) : '' );
    $oum_area_icon_svg = ( is_readable( $oum_area_icon_path ) ? file_get_contents( $oum_area_icon_path ) : '' );
    $oum_category_type_icon_color = '#2c3338';
    $oum_route_icon_svg = str_replace( 'currentColor', $oum_category_type_icon_color, $oum_route_icon_svg );
    $oum_area_icon_svg = str_replace( 'currentColor', $oum_category_type_icon_color, $oum_area_icon_svg );
    $oum_category_type_marker_icon = esc_url( $this->plugin_url . 'src/leaflet/images/marker-icon_custom4-2x.png' );
    ?>

          <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>

              <tr valign="top" class="oum-gopro-tr">

                  <td colspan="2">
                    <div class="oum-marker-categories-settings-panel">
                      <?php 
        $this->render_settings_quick_links( array(
            'oum-categories-free-general'   => __( 'Marker categories', 'open-user-map' ),
            'oum-categories-free-types'     => __( 'Category Types', 'open-user-map' ),
            'oum-categories-free-selection' => __( 'Category Selection', 'open-user-map' ),
            'oum-categories-free-display'   => __( 'Filter & Popup Display', 'open-user-map' ),
        ) );
        ?>
                      <div class="oum-settings-groups">
                        <table class="form-table">
                          <?php 
        $this->render_settings_group_start( 'oum-categories-free-general', __( 'Marker categories', 'open-user-map' ), __( 'Enable categories and manage the categories used by your map.', 'open-user-map' ) );
        ?>
                            <tr valign="top">
                              <th scope="row">
                                <?php 
        echo __( 'Marker Categories & Types', 'open-user-map' );
        ?>
                                <?php 
        $this->render_pro_feature_help( __( 'Marker Categories & Types', 'open-user-map' ), __( 'Organize locations in filterable categories with individual icons. Enable Markers, Lines, and Areas as category types.', 'open-user-map' ) );
        ?>
                                <br><span class="oum-pro">PRO</span><br>
                                <?php 
        $this->render_pro_feature_actions( true );
        ?>
                              </th>
                              <td>
                                <input class="oum-switch" type="checkbox" disabled>
                                <label><?php 
        echo __( 'Enable Marker Categories', 'open-user-map' );
        ?></label>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div class="oum-settings-groups">
                        <table class="form-table">
                          <?php 
        $this->render_settings_group_start( 'oum-categories-free-types', __( 'Category Types', 'open-user-map' ), __( 'Choose which types are available when adding or editing locations. Icons and colors are set per Marker Category.', 'open-user-map' ) );
        ?>
                            <tr valign="top">
                              <th scope="row"><span class="oum-category-type-icon oum-category-type-icon-marker" style="background-image: url('<?php 
        echo $oum_category_type_marker_icon;
        ?>');" aria-hidden="true"></span><?php 
        echo __( 'Markers', 'open-user-map' );
        ?></th>
                              <td class="oum-location-shape-setting">
                                <input type="hidden" name="oum_enable_location_type_marker" value="on">
                                <input class="oum-switch oum-location-shape-checkbox" type="checkbox" checked onclick="return false;">
                                <label><?php 
        echo esc_html__( 'Enable markers', 'open-user-map' );
        ?></label>
                                <p class="description"><?php 
        echo __( 'Single points with marker icons.', 'open-user-map' );
        ?></p>
                              </td>
                            </tr>
                            <tr valign="top">
                              <th scope="row"><span class="oum-category-type-icon" aria-hidden="true"><?php 
        echo $oum_route_icon_svg;
        ?></span><?php 
        echo __( 'Lines', 'open-user-map' );
        ?></th>
                              <td class="oum-location-shape-setting">
                                <input class="oum-switch oum-location-shape-checkbox" type="checkbox" disabled>
                                <label><?php 
        echo esc_html__( 'Enable lines', 'open-user-map' );
        ?></label>
                                <p class="description"><?php 
        echo __( 'Connected paths made from multiple points.', 'open-user-map' );
        ?></p>
                                <div class="oum-location-shape-suboption">
                                  <input class="oum-switch" type="checkbox" checked disabled>
                                  <label><?php 
        echo __( 'Show line distance in popup', 'open-user-map' );
        ?></label>
                                </div>
                              </td>
                            </tr>
                            <tr valign="top">
                              <th scope="row"><span class="oum-category-type-icon" aria-hidden="true"><?php 
        echo $oum_area_icon_svg;
        ?></span><?php 
        echo __( 'Areas', 'open-user-map' );
        ?></th>
                              <td class="oum-location-shape-setting">
                                <input class="oum-switch oum-location-shape-checkbox" type="checkbox" disabled>
                                <label><?php 
        echo esc_html__( 'Enable areas', 'open-user-map' );
        ?></label>
                                <p class="description"><?php 
        echo __( 'Closed boundaries with a transparent fill.', 'open-user-map' );
        ?></p>
                                <div class="oum-location-shape-suboption">
                                  <input class="oum-switch" type="checkbox" checked disabled>
                                  <label><?php 
        echo __( 'Show calculated area size in popup', 'open-user-map' );
        ?></label>
                                </div>
                              </td>
                            </tr>
                          </tbody>
                          <?php 
        $this->render_settings_group_start( 'oum-categories-free-selection', __( 'Category Selection', 'open-user-map' ), __( 'Configure how contributors select categories for a location.', 'open-user-map' ) );
        ?>
                            <tr valign="top">
                              <th scope="row"><label><strong><?php 
        echo __( 'Custom Label:', 'open-user-map' );
        ?></strong></label></th>
                              <td>
                                <input disabled class="regular-text" type="text" value="" placeholder="<?php 
        echo esc_attr( $this->oum_get_default_label( 'marker_types' ) );
        ?>">
                              </td>
                            </tr>
                            <tr valign="top">
                              <th scope="row"><?php 
        echo __( 'Allow multiple selections', 'open-user-map' );
        ?></th>
                              <td>
                                <input class="oum-switch" type="checkbox" disabled>
                                <label><span class="screen-reader-text"><?php 
        echo __( 'Allow multiple selections', 'open-user-map' );
        ?></span></label>
                                <p class="description"><?php 
        echo __( '<strong>Important:</strong> If enabled all locations will fallback to the <a href="edit.php?post_type=oum-location&page=open-user-map-settings">Default Marker Icon</a> instead of a specific category icon.', 'open-user-map' );
        ?></p>
                              </td>
                            </tr>
                            <tr valign="top">
                              <th scope="row"><?php 
        echo __( 'Allow empty selection', 'open-user-map' );
        ?></th>
                              <td>
                                <input class="oum-switch" type="checkbox" disabled>
                                <label><span class="screen-reader-text"><?php 
        echo __( 'Allow empty selection', 'open-user-map' );
        ?></span></label>
                              </td>
                            </tr>
                          </tbody>
                          <?php 
        $this->render_settings_group_start( 'oum-categories-free-display', __( 'Filter & Popup Display', 'open-user-map' ), __( 'Choose how category filters and icons appear on the map.', 'open-user-map' ) );
        ?>
                            <tr valign="top">
                              <th scope="row"><?php 
        echo __( 'Show "Select all" checkbox', 'open-user-map' );
        ?></th>
                              <td>
                                <input class="oum-switch" type="checkbox" disabled>
                                <label><span class="screen-reader-text"><?php 
        echo __( 'Show "Select all" checkbox', 'open-user-map' );
        ?></span></label>
                                <p class="description"><?php 
        echo __( 'If enabled, a "Select all" checkbox will be shown at the top of the marker categories filter.', 'open-user-map' );
        ?></p>
                              </td>
                            </tr>
                            <tr valign="top">
                              <th scope="row"><?php 
        echo __( 'Collapsed Filterbox', 'open-user-map' );
        ?></th>
                              <td>
                                <input class="oum-switch" type="checkbox" disabled>
                                <label><span class="screen-reader-text"><?php 
        echo __( 'Collapsed Filterbox', 'open-user-map' );
        ?></span></label>
                                <p class="description"><?php 
        echo __( 'If enabled the filterbox will take less space and just open on mouseover.', 'open-user-map' );
        ?></p>
                              </td>
                            </tr>
                            <tr valign="top">
                              <th scope="row"><?php 
        echo __( 'Show category icons next to location name', 'open-user-map' );
        ?></th>
                              <td>
                                <input class="oum-switch" type="checkbox" disabled>
                                <label><span class="screen-reader-text"><?php 
        echo __( 'Show category icons next to location name', 'open-user-map' );
        ?></span></label>
                                <p class="description"><?php 
        echo __( 'If enabled, category icons will be displayed inline with the location title in popups and location lists.', 'open-user-map' );
        ?></p>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </td>
                </tr>

            <?php 
    }
    ?>

          </table>
        </div>

        <div id="tab-4" class="oum-tab-pane oum-settings-groups <?php 
    echo ( $active_tab === 'tab-4' ? 'active' : '' );
    ?>">
          <table class="form-table">
          <?php 
    $this->render_settings_group_start( 'oum-regions', __( 'Region navigation', 'open-user-map' ), __( 'Let visitors switch between predefined map regions.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <?php 
    $oum_enable_regions = get_option( 'oum_enable_regions' );
    ?>
              <th scope="row">
                <?php 
    echo __( 'Enable', 'open-user-map' );
    ?>
              </th>
              <td>
                <input class="oum-switch" type="checkbox" name="oum_enable_regions" id="oum_enable_regions" <?php 
    echo ( $oum_enable_regions ? 'checked' : '' );
    ?>>
                <label for="oum_enable_regions"><?php 
    echo esc_html__( 'Enable regions', 'open-user-map' );
    ?></label><br>

                <?php 
    if ( $oum_enable_regions ) {
        ?>
                  <div class="description"><?php 
        echo __( 'You can manage Regions <a href="edit-tags.php?taxonomy=oum-region&post_type=oum-location">here</a>', 'open-user-map' );
        ?></div>
                  <br>
                <?php 
    }
    ?>
              </td>
            </tr>
            <tr valign="top" data-oum-region-layout<?php 
    echo ( $oum_enable_regions ? '' : ' hidden' );
    ?>>
              <?php 
    $oum_regions_layout_style = get_option( 'oum_regions_layout_style', 'layout-1' );
    $items = $this->oum_regions_layout_styles;
    ?>
              <th scope="row">
                <?php 
    echo __( 'Layout', 'open-user-map' );
    ?>
              </th>
              <td>
                <?php 
    echo "<select id='oum_regions_layout_style' name='oum_regions_layout_style'>";
    foreach ( $items as $value => $label ) {
        $selected = ( $oum_regions_layout_style == $value ? 'selected="selected"' : '' );
        echo '<option value="' . esc_textarea( $value ) . '" ' . $selected . '>' . esc_textarea( $label ) . '</option>';
    }
    echo "</select>";
    ?>
              </td>
            </tr>

          </tbody>
          </table>
        </div>

        <div id="tab-5" class="oum-tab-pane oum-settings-groups <?php 
    echo ( $active_tab === 'tab-5' ? 'active' : '' );
    ?>">

          <?php 
    $this->render_settings_quick_links( array(
        'oum-advanced-locations'    => __( 'Location display', 'open-user-map' ),
        'oum-advanced-feedback'     => __( 'Votes & ratings', 'open-user-map' ),
        'oum-advanced-registration' => __( 'User registration', 'open-user-map' ),
        'oum-advanced-code'         => __( 'Custom code', 'open-user-map' ),
    ) );
    ?>
          <table class="form-table">
          <?php 
    $this->render_settings_group_start( 'oum-advanced-locations', __( 'Location display', 'open-user-map' ), __( 'Configure dates and individual location pages.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <?php 
    $oum_enable_location_date = get_option( 'oum_enable_location_date' );
    ?>
              <th scope="row">
                <?php 
    echo __( 'Show location date', 'open-user-map' );
    ?>
              </th>
              <td>
                <input class="oum-switch" type="checkbox" id="oum_enable_location_date" name="oum_enable_location_date" <?php 
    echo ( $oum_enable_location_date == 'on' ? 'checked' : '' );
    ?>>
                <label for="oum_enable_location_date"></label><br><br>
                <span class="description"><?php 
    echo __( 'Displays the date when the location was modified or published inside the location bubble.', 'open-user-map' );
    ?></span><br>
                <br>
                <?php 
    $oum_location_date_type = get_option( 'oum_location_date_type', 'modified' );
    $items = array(
        'modified' => __( 'Date of Last Modification', 'open-user-map' ),
        'created'  => __( 'Publishing Date', 'open-user-map' ),
    );
    echo "<select id='oum_location_date_type' name='oum_location_date_type'>";
    foreach ( $items as $value => $label ) {
        $selected = ( $oum_location_date_type == $value ? 'selected="selected"' : '' );
        echo '<option value="' . esc_textarea( $value ) . '" ' . $selected . '>' . esc_textarea( $label ) . '</option>';
    }
    echo "</select>";
    ?>
              </td>
            </tr>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Public pages for locations (Single pages)', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Public pages for locations (Single pages)', 'open-user-map' ), __( 'This will add a "Read more"-Button to the location bubble. It will link to the location\'s single page. In the backend on the "Edit location" page an additional content editor will become available. You can use shortcodes to display individual values of a location. <strong>See the Help section for details.</strong>', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label></label><br><br>


                </td>
              </tr>

            <?php 
    }
    ?>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-advanced-feedback', __( 'Votes & ratings', 'open-user-map' ), __( 'Let visitors give feedback on locations.', 'open-user-map' ) );
    ?>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Upvotes & Star Rating', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Upvotes & Star Rating', 'open-user-map' ), __( 'Adds a customizable feedback button to each location popup. You can use it for likes, upvotes, flagging, or star rating (1-5 stars). The counter is hidden when there are no votes.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label></label><br><br>

                  <strong><?php 
        echo __( 'Vote Type:', 'open-user-map' );
        ?></strong><br>
                  <select disabled>
                    <option><?php 
        echo __( 'Upvote', 'open-user-map' );
        ?></option>
                    <option><?php 
        echo __( 'Star Rating (1-5 stars)', 'open-user-map' );
        ?></option>
                  </select>
                  <br><br>
                  <strong><?php 
        echo __( 'Upvote Button Label:', 'open-user-map' );
        ?></strong><br>
                  <input class="regular-text" type="text" value="👍" disabled>
                  <span class="description"><?php 
        echo __( '(Only used for Upvote type)', 'open-user-map' );
        ?></span>
                  <br><br>
                  <strong><?php 
        echo __( 'Prevent Multiple Votes:', 'open-user-map' );
        ?></strong><br>
                  <select disabled>
                    <option><?php 
        echo __( 'Persistent Cookie (Lasts 1 year)', 'open-user-map' );
        ?></option>
                    <option><?php 
        echo __( 'Session Cookie (Privacy-friendly, expires when browser closes)', 'open-user-map' );
        ?></option>
                    <option><?php 
        echo __( 'No Prevention (Users can vote multiple times)', 'open-user-map' );
        ?></option>
                  </select>
                  <br><br>
                  <span class="description">
                    <?php 
        echo __( 'Choose how to prevent multiple votes:', 'open-user-map' );
        ?>
                    <br><br>
                    <strong><?php 
        echo __( 'Persistent Cookie:', 'open-user-map' );
        ?></strong> <?php 
        echo __( 'Users can vote once per location. Vote state lasts 1 year.', 'open-user-map' );
        ?>
                    <br>
                    <strong><?php 
        echo __( 'Session Cookie:', 'open-user-map' );
        ?></strong> <?php 
        echo __( 'Users can vote once per location. Vote state expires when browser closes (privacy-friendly for German DSGVO compliance).', 'open-user-map' );
        ?>
                    <br>
                    <strong><?php 
        echo __( 'No Prevention:', 'open-user-map' );
        ?></strong> <?php 
        echo __( 'Users can vote multiple times. Vote states are cleared on page refresh. No data is stored on user devices (maximum privacy).', 'open-user-map' );
        ?>
                    <br><br>
                    <a href="https://www.open-user-map.com/knowledge-base/preventing-multiple-votes-cookies-privacy/" target="_blank"><?php 
        echo __( 'Find ready-made text modules for your Privacy Declaration →', 'open-user-map' );
        ?></a>
                  </span><br><br>
                </td>
              </tr>

            <?php 
    }
    ?>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-advanced-registration', __( 'User registration', 'open-user-map' ), __( 'Connect location submissions with WordPress registration.', 'open-user-map' ) );
    ?>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>

              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Extend WordPress user registration form with "Add location" map', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Extend WordPress user registration form with "Add location" map', 'open-user-map' ), __( 'Adds the location map input to the WordPress registration flow.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <input class="oum-switch" type="checkbox" disabled>
                  <label></label><br><br>

                </td>
              </tr>

            <?php 
    }
    ?>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-advanced-code', __( 'Custom code', 'open-user-map' ), __( 'Customize the map with your own JavaScript and CSS.', 'open-user-map' ) );
    ?>

            <tr valign="top" >
                <?php 
    $oum_custom_js = get_option( 'oum_custom_js' );
    ?>
                <th scope="row">
                  <?php 
    echo __( 'Custom JS', 'open-user-map' );
    ?>
                </th>
                <td>
                  <strong><?php 
    echo __( 'This JS code will be executed after the map has been loaded:' );
    ?></strong><br>
                  <textarea class="regular-text" name="oum_custom_js" id="oum_custom_js" rows="8" cols="50" placeholder="<?php 
    echo __( "e.g. console.log('The map is ready')", "open-user-map" );
    ?>"><?php 
    echo $oum_custom_js;
    ?></textarea><br><br>
                  <span class="description"></span>
                  <br><br>
                </td>
              </tr>

            <tr valign="top" >
                <?php 
    $oum_custom_css = get_option( 'oum_custom_css' );
    ?>
                <th scope="row">
                  <?php 
    echo __( 'Custom CSS', 'open-user-map' );
    ?>
                </th>
                <td>
                  <strong><?php 
    echo __( 'This CSS will be rendered inline with the frontend styles:' );
    ?></strong><br>
                  <textarea class="regular-text" name="oum_custom_css" id="oum_custom_css" rows="8" cols="50" placeholder="<?php 
    echo __( "e.g. .open-user-map { border: 10px solid red; }", "open-user-map" );
    ?>"><?php 
    echo esc_textarea( $oum_custom_css );
    ?></textarea><br><br>
                  <span class="description"></span>
                  <br><br>
                </td>
              </tr>

          </tbody>
          </table>

        </div>

        <div id="tab-6" class="oum-tab-pane oum-settings-groups <?php 
    echo ( $active_tab === 'tab-6' ? 'active' : '' );
    ?>">

          <?php 
    $this->render_settings_quick_links( array(
        'oum-export' => __( 'Export', 'open-user-map' ),
        'oum-import' => __( 'Import', 'open-user-map' ),
    ) );
    ?>
          <table class="form-table">
          <?php 
    $this->render_settings_group_start( 'oum-export', __( 'Export', 'open-user-map' ), __( 'Download your published locations as a CSV file.', 'open-user-map' ) );
    ?>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Export all Locations', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Export all Locations', 'open-user-map' ), __( 'Export published locations to a CSV file for backup or bulk editing.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <button disabled class="button button-secondary"><?php 
        echo __( 'Export to CSV', 'open-user-map' );
        ?></button>
                  <br><br>
                  <div class="description">
                    <strong>This is how the export works:</strong><br>
                    <ul>
                      <li>Only published locations will be exported</li>
                      <li>The CSV uses Comma as delimiter</li>
                    </ul>
                  </div>
                </td>
              </tr>

            <?php 
    }
    ?>


            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-import', __( 'Import', 'open-user-map' ), __( 'Add or update locations from a CSV file.', 'open-user-map' ) );
    ?>

            <?php 
    ?>

            <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
              
              <tr valign="top" class="oum-gopro-tr">
                <th scope="row">
                  <?php 
        echo __( 'Import all Locations', 'open-user-map' );
        ?>
                    <?php 
        $this->render_pro_feature_help( __( 'Import all Locations', 'open-user-map' ), __( 'Import locations from a CSV file or update existing locations by their POST ID. Review the import instructions and backup warning before importing.', 'open-user-map' ) );
        ?>
                  <br><span class="oum-pro">PRO</span><br>
                  <?php 
        $this->render_pro_feature_actions( true );
        ?>
                </th>
                <td>
                  <div class="csv_upload">
                    <button disabled class="button button-secondary"><?php 
        echo __( 'Upload CSV & Import', 'open-user-map' );
        ?></button>
                    <br><br>
                    <div>
                      <input class="oum-switch" type="checkbox" disabled>
                      <label for="oum_csv_import_publish_immediately"><?php 
        echo __( 'Publish imported locations immediately', 'open-user-map' );
        ?></label>
                      <span class="description"><?php 
        echo __( 'If enabled, imported locations will be published immediately. If disabled, they will be saved as drafts for review.', 'open-user-map' );
        ?></span>
                    </div>
                    <br><br>
                    <div class="description">
                      <strong>This is important to make the import work:</strong><br>
                      <ul>
                        <li>Be patient, this can take a while.</li>
                        <li>Be aware that every location with matching POST ID will be overwritten. <span style="color: red">Consider creating a DB Backup before!</span></li>
                        <li>To import new locations leave values in the post_id column empty</li>
                        <li>Download an Export file first and use it as template for your import</li>
                        <li>All location types use the <code>geometry_type</code> and <code>geometry</code> columns. Existing imports with only <code>lat</code> and <code>lng</code> still work for Markers.</li>
                        <li>Comma or Semicolon work as delimiter</li>
                        <li>Non-existing Marker Categories will be created automatically</li>
                        <li>Multiselect values need to be written like so: Red|Green|Blue</li>
                        <li>By default, all imported locations will have status "Draft". You can enable an option to publish them immediately.</li>
                      </ul>
                    </div>
                  </div>
                </td>
              </tr>

            <?php 
    }
    ?>

          </tbody>
          </table>

        </div>
        
        <div id="tab-7" class="oum-tab-pane oum-settings-groups <?php 
    echo ( $active_tab === 'tab-7' ? 'active' : '' );
    ?>">

          <?php 
    $this->render_settings_quick_links( array(
        'oum-help-start'        => __( 'Getting started', 'open-user-map' ),
        'oum-help-shortcodes'   => __( 'Shortcodes', 'open-user-map' ),
        'oum-help-integrations' => __( 'API & hooks', 'open-user-map' ),
        'oum-help-support'      => __( 'Support & diagnostics', 'open-user-map' ),
    ) );
    ?>
          <table class="form-table">
          <?php 
    $this->render_settings_group_start( 'oum-help-start', __( 'Getting started', 'open-user-map' ), __( 'Add your first map using the block editor or Elementor.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( '🚀 Get started with Open User Map', 'open-user-map' );
    ?>
              </th>
              <td class="top-padding-20">
                <?php 
    echo sprintf( __( '<ol><li>Use the WordPress block editor (or Elementor) to insert the <b>Open User Map</b> block onto a page. Alternatively, you can use the shortcode <input class="shortcode-display" type="text" readonly value=\'[open-user-map]\' />.</li><li>You can <a href="%s">Manage Markers</a> under <i>Open User Map > All Locations</i></li><li><a href="%s">Customize</a> map styles, enable features, or get help via <i>Open User Map > Settings</i></li></ol>', 'open-user-map' ), 'edit.php?post_type=oum-location', 'edit.php?post_type=oum-location&page=open-user-map-settings' );
    ?>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Gutenberg Block', 'open-user-map' );
    ?>
              </th>
              <td class="top-padding-20">
                <?php 
    echo __( 'Use the "Open User Map" block to integrate the map inside your page. <br>You can set custom map position and filter for categories and locations inside the block settings.', 'open-user-map' );
    ?>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Elementor Widget', 'open-user-map' );
    ?>
              </th>
              <td class="top-padding-20">
                <?php 
    echo __( 'Use the Elementor Widget "Open User Map" to integrate the map inside your page. <br>You can set custom map position and filter for categories and locations inside the widget settings.', 'open-user-map' );
    ?>
              </td>
            </tr>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-help-shortcodes', __( 'Shortcodes', 'open-user-map' ), __( 'Embed maps and customize each map with shortcode attributes.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row"><?php 
    echo __( 'Place the shortcode anywhere in your content or integrate it within your theme template with PHP', 'open-user-map' );
    ?></th>
              <td class="top-padding-20">
                <strong>Shortcode:</strong><br><br>
                <input class="shortcode-display" type="text" readonly value="[open-user-map]" /> or with PHP <input class="shortcode-display" type="text" readonly value="&lt;?php echo do_shortcode('[open-user-map]'); ?&gt;" /><br><br>
                <p class="hint"><?php 
    echo __( 'Displays the Map with all locations.', 'open-user-map' );
    ?></p>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row"><?php 
    echo __( 'Shortcode attributes', 'open-user-map' );
    ?></th>
              <td class="top-padding-20">
                <p class="hint"><?php 
    echo __( 'You can use shortcode attributes to override the <a href="edit.php?post_type=oum-location&page=open-user-map-settings">global settings</a>. This allows for custom individual maps.', 'open-user-map' );
    ?></p>

                <div class="oum-shortcode-docs">
                    <!-- Group 1: Map Position & View -->
                    <h4><?php 
    echo __( 'Map Position & View', 'open-user-map' );
    ?></h4>
                    <table class="widefat oum-attribute-table">
                        <thead>
                            <tr>
                                <th><?php 
    echo __( 'Attribute', 'open-user-map' );
    ?></th>
                                <th><?php 
    echo __( 'Values/Example', 'open-user-map' );
    ?></th>
                                <th><?php 
    echo __( 'Description', 'open-user-map' );
    ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>lat</code>, <code>long</code>, <code>zoom</code></td>
                                <td><code>lat="51.50665" long="-0.12752" zoom="13"</code></td>
                                <td><?php 
    echo __( 'Set an individual map position with latitude, longitude and zoom level.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>region</code></td>
                                <td><code>region="Europe"</code></td>
                                <td><?php 
    echo __( 'Pre-select a region.', 'open-user-map' );
    ?> <?php 
    echo __( 'This works only if you enabled the regions feature in the settings.', 'open-user-map' );
    ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Group 2: Content Filtering -->
                    <h4><?php 
    echo __( 'Content Filtering', 'open-user-map' );
    ?></h4>
                    <table class="widefat oum-attribute-table">
                        <thead>
                            <tr>
                                <th><?php 
    echo __( 'Attribute', 'open-user-map' );
    ?></th>
                                <th><?php 
    echo __( 'Values/Example', 'open-user-map' );
    ?></th>
                                <th><?php 
    echo __( 'Description', 'open-user-map' );
    ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>types</code></td>
                                <td>
                                    <code>types="food"</code><br>
                                    <code>types="food|drinks|hotel"</code>
                                </td>
                                <td><?php 
    echo __( 'Filter locations by types (Marker Categories). Separate multiple types with a | symbol.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>types-relation</code></td>
                                <td>
                                    <code>types-relation="AND"</code><br>
                                </td>
                                <td><?php 
    echo __( 'All types must match (AND). By default any type can match (OR).', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>ids</code></td>
                                <td>
                                    <code>ids="123"</code><br>
                                    <code>ids="123|456|789"</code>
                                </td>
                                <td><?php 
    echo __( 'Filter locations by Post ID. Separate multiple IDs with a | symbol.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>date-filter</code></td>
                                <td>
                                    <code>date-filter="after:2025-10-03"</code><br>
                                    <code>date-filter="before:2025-11-03"</code><br>
                                    <code>date-filter="2025-11-03"</code><br>
                                    <code>date-filter="after:2025-10-03;before:2025-11-03"</code>
                                </td>
                                <td><?php 
    echo __( 'Filter by date using keywords. Use after:YYYY-MM-DD for newer than, before:YYYY-MM-DD for older than, a bare YYYY-MM-DD for an exact day, or combine with semicolons for a range. Uses modified or created date based on your settings.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>user</code> <span class="oum-pro">PRO</span></td>
                                <td>
                                    <code>user="current"</code><br>
                                    <code>user="123"</code><br>
                                    <code>user="role:subscriber"</code>
                                </td>
                                <td><?php 
    echo __( 'Filter locations by user. Use "current" to show only locations from the currently logged-in user, a specific user ID, or "role:rolename" to show locations from users with a specific role.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>custom-fields-filter</code></td>
                                <td>
                                    <code>custom-fields-filter="Color:Red|Blue:OR; Size:M"</code><br>
                                    <code>custom-fields-filter="Website:https\://example.com"</code>
                                </td>
                                <td><?php 
    echo __( 'Filter locations by custom field values. Format: LABEL:VALUE1|VALUE2:RELATION. Multiple filters separated by semicolon. RELATION defaults to OR. To include a colon in a value (e.g., URLs), escape it with a backslash, e.g. <code>https\\://example.com</code>.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>custom-fields-filter-relation</code></td>
                                <td>
                                    <code>custom-fields-filter-relation="AND"</code><br>
                                    <code>custom-fields-filter-relation="OR"</code>
                                </td>
                                <td><?php 
    echo __( 'Relation between multiple custom field filter groups. Default is AND (all groups must match). Set to OR if any group may match.', 'open-user-map' );
    ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Group 3: Display & Appearance -->
                    <h4><?php 
    echo __( 'Display & Appearance', 'open-user-map' );
    ?></h4>
                    <table class="widefat oum-attribute-table">
                        <thead>
                            <tr>
                                <th><?php 
    echo __( 'Attribute', 'open-user-map' );
    ?></th>
                                <th><?php 
    echo __( 'Values/Example', 'open-user-map' );
    ?></th>
                                <th><?php 
    echo __( 'Description', 'open-user-map' );
    ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>size</code></td>
                                <td>
                                    <code>size="default"</code><br>
                                    <code>size="fullwidth"</code>
                                </td>
                                <td><?php 
    echo __( 'Set a custom size for desktop view.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>size_mobile</code></td>
                                <td>
                                    <code>size_mobile="square"</code><br>
                                    <code>size_mobile="landscape"</code><br>
                                    <code>size_mobile="portrait"</code>
                                </td>
                                <td><?php 
    echo __( 'Set a custom size for mobile view.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>height</code></td>
                                <td><code>height="400px"</code></td>
                                <td><?php 
    echo __( 'Set a custom height for desktop view. Don\'t forget to add a unit like <b>px</b>.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>height_mobile</code></td>
                                <td><code>height_mobile="300px"</code></td>
                                <td><?php 
    echo __( 'Set a custom height for mobile view. Don\'t forget to add a unit like <b>px</b>.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>map_type</code></td>
                                <td>
                                    <code>map_type="interactive"</code><br>
                                    <code>map_type="simple"</code>
                                </td>
                                <td><?php 
    echo __( 'Enable or disable the "Add location" button.', 'open-user-map' );
    ?></td>
                            </tr>
                            <?php 
    $available_map_style_keys = implode( ', ', array_keys( $this->get_map_style_registry() ) );
    ?>
                            <tr>
                                <td><code>map_style</code></td>
                                <td>
                                    <code>map_style="OpenFreeMap.bright"</code><br>
                                    <code>map_style="OpenFreeMap.liberty"</code>
                                </td>
                                <td><?php 
    echo sprintf( esc_html__( 'Override the map style. Available style keys: %s', 'open-user-map' ), esc_html( $available_map_style_keys ) );
    ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Group 4: Features & Controls -->
                    <h4><?php 
    echo __( 'Features & Controls', 'open-user-map' );
    ?></h4>
                    <table class="widefat oum-attribute-table">
                        <thead>
                            <tr>
                                <th><?php 
    echo __( 'Attribute', 'open-user-map' );
    ?></th>
                                <th><?php 
    echo __( 'Values/Example', 'open-user-map' );
    ?></th>
                                <th><?php 
    echo __( 'Description', 'open-user-map' );
    ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>disable_regions</code></td>
                                <td>
                                    <code>disable_regions="true"</code><br>
                                    <code>disable_regions="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Enable or disable Regions.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_cluster</code></td>
                                <td>
                                    <code>enable_cluster="true"</code><br>
                                    <code>enable_cluster="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Enable or disable Marker Clustering.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_searchbar</code></td>
                                <td>
                                    <code>enable_searchbar="true"</code><br>
                                    <code>enable_searchbar="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Enable or disable the searchbar.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_searchaddress_button</code></td>
                                <td>
                                    <code>enable_searchaddress_button="true"</code><br>
                                    <code>enable_searchaddress_button="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Enable or disable the "Search for Address (Geosearch)" button.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_searchmarkers_button</code></td>
                                <td>
                                    <code>enable_searchmarkers_button="true"</code><br>
                                    <code>enable_searchmarkers_button="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Enable or disable the "Search for Markers" button.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>hide_filterbox</code></td>
                                <td>
                                    <code>hide_filterbox="true"</code><br>
                                    <code>hide_filterbox="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Hide the Marker Categories filterbox.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_location_type_marker</code></td>
                                <td>
                                    <code>enable_location_type_marker="true"</code><br>
                                    <code>enable_location_type_marker="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Override whether Marker categories and Marker locations are available on this map.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_location_type_polyline</code> <span class="oum-pro">PRO</span></td>
                                <td>
                                    <code>enable_location_type_polyline="true"</code><br>
                                    <code>enable_location_type_polyline="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Override whether Line categories and Line locations are available on this map.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_location_type_polygon</code> <span class="oum-pro">PRO</span></td>
                                <td>
                                    <code>enable_location_type_polygon="true"</code><br>
                                    <code>enable_location_type_polygon="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Override whether Area categories and Area locations are available on this map.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_advanced_filter</code> <span class="oum-pro">PRO</span></td>
                                <td>
                                    <code>enable_advanced_filter="true"</code><br>
                                    <code>enable_advanced_filter="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Override the Advanced Filter Interface toggle for this shortcode, regardless of the global setting.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>advanced_filter_layout</code> <span class="oum-pro">PRO</span></td>
                                <td>
                                    <code>advanced_filter_layout="left"</code><br>
                                    <code>advanced_filter_layout="right"</code><br>
                                    <code>advanced_filter_layout="button"</code><br>
                                    <code>advanced_filter_layout="panel"</code>
                                </td>
                                <td><?php 
    echo __( 'Force a specific Advanced Filter layout (sidebar, button, or panel) just for this shortcode.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_currentlocation</code></td>
                                <td>
                                    <code>enable_currentlocation="true"</code><br>
                                    <code>enable_currentlocation="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Enable or disable the "Show me where I am" button.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>enable_fullscreen</code></td>
                                <td>
                                    <code>enable_fullscreen="true"</code><br>
                                    <code>enable_fullscreen="false"</code>
                                </td>
                                <td><?php 
    echo __( 'Enable or disable the fullscreen button.', 'open-user-map' );
    ?></td>
                            </tr>
                            <tr>
                                <td><code>hide_location_popup</code></td>
                                <td>
                                    <code>hide_location_popup="true"</code><br>
                                    <code>hide_location_popup="false"</code>
                                </td>
                                <td><?php 
    echo __( 'If "true", clicking a marker does not open a popup. Useful for simple maps (e.g. next to a contact form) where you only want to show marker positions.', 'open-user-map' );
    ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Complete Examples -->
                    <h4><?php 
    echo __( 'Complete Examples', 'open-user-map' );
    ?></h4>
                    <div class="oum-examples">
                        <p><strong><?php 
    echo __( 'Example 1: Map of London with food locations only', 'open-user-map' );
    ?></strong></p>
                        <code>[open-user-map lat="51.50665" long="-0.12752" zoom="13" types="food" size="fullwidth" height="500px"]</code>
                        
                        <p><strong><?php 
    echo __( 'Example 2: Map with markers only (no popup on click)', 'open-user-map' );
    ?></strong></p>
                        <code>[open-user-map hide_location_popup="true"]</code>
                        
                        <p><strong><?php 
    echo __( 'Example 3: Simple map showing only locations from the current user', 'open-user-map' );
    ?></strong> <span class="oum-pro">PRO</span></p>
                        <code>[open-user-map map_type="simple" user="current" enable_fullscreen="true" enable_searchbar="false"]</code>
                        
                        <p><strong><?php 
    echo __( 'Example 4: Interactive map for a specific region with custom appearance', 'open-user-map' );
    ?></strong></p>
                        <code>[open-user-map region="Europe" map_type="interactive" height="600px" enable_cluster="false" enable_currentlocation="true"]</code>
                    </div>
                </div>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row"><?php 
    echo __( 'Additional Shortcodes', 'open-user-map' );
    ?></th>
              <td class="top-padding-20">
                <input class="shortcode-display" type="text" readonly value="[open-user-map-form]" />
                <br><br>
                <span class="hint"><?php 
    echo __( 'Display the "Add Location" form inline instead of within a popup. The form will be displayed directly on the page without the map.', 'open-user-map' );
    ?></span>
                <br><br>
                <br><br>

                <span class="oum-pro">PRO</span> <input class="shortcode-display" type="text" readonly value='[open-user-map-location value="Favorite color" post_id="12345"]' /> 
                <br><br>
                <span class="hint"><?php 
    echo __( 'Display specific values from a location. The POST_ID attribute is optional. Alternatively use the PHP function <input class="shortcode-display" type="text" readonly value="oum_get_location_value( $value, $post_id )" /> in case you just want to return the value.', 'open-user-map' );
    ?></span>
                <br><br>
                <strong><?php 
    echo __( 'Available shortcodeattributes:', 'open-user-map' );
    ?></strong>
                <ul>
                  <li><input class="shortcode-display" type="text" readonly value='format="object"' /> - <?php 
    echo __( 'Returns JSON data without wrapper div. Perfect for embedding in HTML attributes or custom JavaScript. If "value" attribute is provided, returns that single value as an object. If no "value" attribute is provided, returns all location data as an object.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='post_id="12345"' /> - <?php 
    echo __( 'The post ID of the location. If omitted, uses the current post ID.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='value="Favorite color"' /> - <?php 
    echo __( 'The specific value to display. See list below for available values.', 'open-user-map' );
    ?></li>
                </ul>
                <br><br>
                <strong><?php 
    echo __( 'These values are available:', 'open-user-map' );
    ?></strong>
                <ul>
                  <li>title</li>
                  <li>images</li>
                  <li>audio</li>
                  <li>video</li>
                  <li>type (Marker Categories)</li>
                  <li>map</li>
                  <li>subtitle (replaces the former "address" label)</li>
                  <li>lat</li>
                  <li>lng</li>
                  <li>geometry_type</li>
                  <li>geometry</li>
                  <li>route</li>
                  <li>text</li>
                  <li>votes</li>
                  <li>notification</li>
                  <li>author_name</li>
                  <li>author_email</li>
                  <li>wp_author_id</li>
                  <li>CUSTOM FIELD LABEL</li>
                </ul>
                <br><br>

                <span class="oum-pro">PRO</span> <input class="shortcode-display" type="text" readonly value="[open-user-map-gallery]" />
                <br><br>
                <span class="hint"><?php 
    echo __( 'Get a nice gallery view of all the location images. Each image is linked to the location marker on the map.', 'open-user-map' );
    ?></span>
                <br><br>
                <strong><?php 
    echo __( 'Available attributes:', 'open-user-map' );
    ?></strong>
                <ul>
                  <li><input class="shortcode-display" type="text" readonly value='url="https://mysite.com/"' /> - <?php 
    echo __( 'Link the images to another page.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='number="10"' /> - <?php 
    echo __( 'Limit the number of images displayed.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='user="current"' /> - <?php 
    echo __( 'Filter images by user. Accepts "current", a user ID, or "role:rolename".', 'open-user-map' );
    ?></li>
                </ul>
                <br><br>

                <span class="oum-pro">PRO</span> <input class="shortcode-display" type="text" readonly value="[open-user-map-list]" />
                <br><br>
                <span class="hint"><?php 
    echo __( 'Get a list view of all the locations. The list view is paginated. This number of items per page can be adjusted under <i>Settings > Reading</i> or overridden with the posts_per_page attribute.', 'open-user-map' );
    ?></span>
                <br><br>
                <strong><?php 
    echo __( 'Available attributes:', 'open-user-map' );
    ?></strong>
                <ul>
                  <li><input class="shortcode-display" type="text" readonly value='posts_per_page="24"' /> - <?php 
    echo __( 'Override the WordPress posts per page setting. Use -1 to show all matching locations without pagination.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='user="current"' /> - <?php 
    echo __( 'Filter locations by user. Accepts "current", a user ID, or "role:rolename".', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='types="food|drinks"' /> - <?php 
    echo __( 'Filter by marker categories. Separate multiple types with a | symbol.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='types-relation="AND"' /> - <?php 
    echo __( 'All types must match (AND). By default or any type can match (OR).', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='ids="123|456"' /> - <?php 
    echo __( 'Filter by location IDs. Separate multiple IDs with a | symbol.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='date-filter="after:2025-10-03"' /> - <?php 
    echo __( 'Filter by date using keywords. Use after:YYYY-MM-DD for newer than, before:YYYY-MM-DD for older than, a bare YYYY-MM-DD for an exact day, or combine with ";" for ranges. Uses modified or created date based on settings.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='custom-fields-filter="Color:Red|Blue:OR;Size:M"' /> - <?php 
    echo __( 'Filter by custom field values. Format: LABEL:VALUE1|VALUE2:RELATION. Multiple filters separated by semicolon. RELATION defaults to OR. To include a colon in a value (e.g., URLs), escape it with a backslash: <code>Website:https\\://example.com</code>', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='custom-fields-filter-relation="AND"' /> - <?php 
    echo __( 'Relation between multiple custom field filters. Default is AND. Set to OR if any filter group should match.', 'open-user-map' );
    ?></li>
                  <li><input class="shortcode-display" type="text" readonly value='sort="Title:DESC"' /> - <?php 
    echo __( 'Sort list view by title, date, or custom field label. Format: FIELD:ORDER (ASC or DESC), e.g. <code>sort="Date:ASC"</code> or <code>sort="Custom Field Label:DESC"</code>.', 'open-user-map' );
    ?></li>
                </ul>
                <br><br>
              </td>
            </tr>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-help-integrations', __( 'API & hooks', 'open-user-map' ), __( 'Control maps with URL parameters, JavaScript, and WordPress hooks.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row"><?php 
    echo __( 'URL parameters', 'open-user-map' );
    ?></th>
              <td class="top-padding-20">
                <input class="shortcode-display" type="text" readonly value='?markerid=123' /> <span class="hint"><?php 
    echo __( '123 can be the post_id of any public location. Add the parameter to the URL to auto-open a specific location.', 'open-user-map' );
    ?></span><br><br>
                <input class="shortcode-display" type="text" readonly value='?region=Europe' /> <span class="hint"><?php 
    echo __( 'Pre-select a region.', 'open-user-map' );
    ?> <?php 
    echo __( 'This works only if you enabled the regions feature in the settings.', 'open-user-map' );
    ?></span><br><br>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row"><?php 
    echo __( 'JavaScript API', 'open-user-map' );
    ?></th>
              <td class="top-padding-20">
                <p class="hint"><?php 
    echo __( 'Extend the map on the frontend with custom JavaScript. These helpers are available after the map finished loading.', 'open-user-map' );
    ?></p>
                <br><br>
                <strong><?php 
    echo __( 'Marker & filter helpers:', 'open-user-map' );
    ?></strong><br><br>
                <div class="oum-code-snippet">
                  <div class="oum-code-label">JavaScript</div>
<textarea readonly wrap="off" rows="9" cols="100" class="code-display">
// Returns the number of markers currently visible (honors all filters)
OUMMarkers.getFilteredMarkersCount();

// Inspect the active filter state (searchtext, categories, customfields)
OUMMarkers.getFilterState();

// Access the raw location data array (helpful for custom UIs)
OUMMarkers.getAllLocations();
</textarea>
                </div>
                <br><br>
                <br><br>
                <strong><?php 
    echo __( 'Conditional Fields', 'open-user-map' );
    ?></strong><br>
                <span class="hint"><?php 
    echo __( 'Show or hide a custom form field based on the selected value of another field.', 'open-user-map' );
    ?></span><br><br>
                <div class="oum-code-snippet">
                  <div class="oum-code-label">JavaScript</div>
<textarea readonly wrap="off" rows="10" cols="100" class="code-display">
/**
 * OUM: Conditional Field
 * 
 * sourceField   Element that defines the condition
 * targetField   Element to show or hide
 * condShow      Array of values that lead to show
 * condHide      (Optional) Array of values that lead to hide. If empty/omitted, the field will be hidden when condShow is not met.
*/

oumConditionalField(sourceField, targetField, condShow, condHide);
</textarea>
                </div>
                <br><br>
                <strong><?php 
    echo __( 'Examples:', 'open-user-map' );
    ?></strong><br><br>
                <div class="oum-code-snippet">
                  <div class="oum-code-label">JavaScript</div>
<textarea readonly wrap="off" rows="4" cols="100" class="code-display">
// With condHide: Show field when value is '1' or '2', hide when value is '3' or empty

oumConditionalField('[name="oum_marker_icon[]"]', '[name="oum_location_custom_fields[1645650268221]"]', ['1', '2'], ['3', '']);

</textarea>
                </div>
                <div class="oum-code-snippet">
                  <div class="oum-code-label">JavaScript</div>
<textarea readonly wrap="off" rows="4" cols="100" class="code-display">
// Without condHide: Show field only when value is '1' or '2', hide otherwise

oumConditionalField('[name="oum_marker_icon[]"]', '[name="oum_location_custom_fields[1645650268221]"]', ['1', '2']);

</textarea>
                </div>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Hooks', 'open-user-map' );
    ?>
              </th>
              <td class="top-padding-20">
                <span class="hint"><?php 
    echo __( 'Make use of filter hooks to extend the functionality of the Open User Map plugin.', 'open-user-map' );
    ?> <?php 
    echo __( 'Find more info on how to use hooks <a href="https://www.open-user-map.com/knowledge-base/change-or-extend-content-of-each-location-bubble/?ref=pluginsettings">here</a>.', 'open-user-map' );
    ?></span><br><br>
                <strong><?php 
    echo __( 'Customize location bubble content:', 'open-user-map' );
    ?></strong><br><br>
<div class="oum-code-snippet">
  <div class="oum-code-label">PHP</div>
<textarea readonly wrap="off" rows="8" cols="100" class="code-display">
add_filter('oum_location_bubble_content', function ( $content, $location ) {

  // extend or change content
  $content .= 'Post ID: ' . $location['post_id'];

  return $content;
}, 10, 2);
</textarea>
  </div>
                <br><br><br>
                <strong><?php 
    echo __( 'Customize the template for single location pages:', 'open-user-map' );
    ?></strong><br><br>
<div class="oum-code-snippet">
  <div class="oum-code-label">PHP</div>
<textarea readonly wrap="off" rows="8" cols="100" class="code-display">
add_filter('oum_default_location_content', function ($default_content, $post_id) {

    // Your custom content here
    $my_custom_default_content = 'WRITE YOUR CUSTOM CONTENT HERE';
   
    return $my_custom_default_content;
}, 10, 2);
</textarea>
  </div>
                <br><br><br>
                <span class="oum-pro">PRO</span> <strong><?php 
    echo __( 'Customize location list item content:', 'open-user-map' );
    ?></strong><br><br>
<div class="oum-code-snippet">
  <div class="oum-code-label">PHP</div>
<textarea readonly wrap="off" rows="8" cols="100" class="code-display">
add_filter('oum_location_list_item_content', function ( $content, $location ) {

  // extend or change content
  $content .= 'Post ID: ' . $location['post_id'];

  return $content;
}, 10, 2);
</textarea>
  </div>
                <br><br><br>
                <strong><?php 
    echo __( 'Customize location bubble image (eg. to add a lightbox):', 'open-user-map' );
    ?></strong><br><br>
<div class="oum-code-snippet">
  <div class="oum-code-label">PHP</div>
<textarea readonly wrap="off" rows="8" cols="100" class="code-display">
add_filter('oum_location_bubble_image', function ( $image, $location ) {

  // extend or change image
  $image = '&lt;a class=&quot;lightbox&quot; href=&quot;' . $location['image'] . '&quot;&gt;' . $image . '&lt;/a&gt;';

  return $image;
}, 10, 2);
</textarea>
  </div>
                <br><br><br>
                <strong><?php 
    echo __( 'Modify location data before rendering (map & list view, eg. customize marker icon):', 'open-user-map' );
    ?></strong><br><br>
<div class="oum-code-snippet">
  <div class="oum-code-label">PHP</div>
<textarea readonly wrap="off" rows="17" cols="100" class="code-display">
add_filter('oum_location_data', function ( $location_data, $post_id ) {

  // Example: Change marker icon based on custom logic
  if ( $post_id == 123 ) {
    $location_data['icon'] = 'https://example.com/custom-marker.png';
  }

  // You can modify any property of $location_data:
  // - icon: marker icon URL (works for map markers and list view)
  // - title, lat, lng, zoom, types, post_id, address, text, image, audio, video, custom_fields, votes, etc.
  // - content: plain text for the map marker search only (not the popup HTML). Full bubble markup is built
  //   on the server and can be changed with the oum_location_bubble_content filter (or oum_location_bubble_image).

  return $location_data;
}, 10, 2);
</textarea>
  </div>
                <br><br><br>
              </td>
            </tr>

            </tbody>

            <?php 
    $this->render_settings_group_start( 'oum-help-support', __( 'Support & diagnostics', 'open-user-map' ), __( 'Find support and information for troubleshooting.', 'open-user-map' ) );
    ?>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Support', 'open-user-map' );
    ?>
              </th>
              <td class="top-padding-20">
                <?php 
    echo __( 'Please have a look at our <a href="https://www.open-user-map.com/support/knowledge-base/" target="_blank">Knowledge Base</a> and the <a href="https://wordpress.org/plugins/open-user-map/#faq" target="_blank">FAQ</a>. We keep it up to date.', 'open-user-map' );
    ?>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">
                <?php 
    echo __( 'Debug Info', 'open-user-map' );
    ?>
              </th>
              <td class="top-padding-20">
                <?php 
    echo __( 'You can copy & paste or screenshot this info and send it as email to our support in case we need to debug something:', 'open-user-map' );
    ?><br><br>
                <div class="oum-debug-info">
                  <ul>
                    <li>Plugin: <?php 
    echo get_plugin_data( $this->plugin_path . 'open-user-map.php', false )['Name'];
    ?></li>
                    <li>Plugin version: <?php 
    echo $this->plugin_version;
    ?></li>
                    <li>Server: <?php 
    echo $_SERVER['SERVER_NAME'];
    ?></li>
                    <li>Server Software: <?php 
    echo $_SERVER['SERVER_SOFTWARE'];
    ?></li>
                    <li>PHP version: <?php 
    echo phpversion();
    ?></li>
                    <li>log_errors: <?php 
    echo ini_get( 'log_errors' );
    ?></li>
                    <li>output_buffering: <?php 
    echo ini_get( 'output_buffering' );
    ?></li>
                    <li>memory_limit: <?php 
    echo ini_get( 'memory_limit' );
    ?></li>
                    <li>upload_max_filesize: <?php 
    echo ini_get( 'upload_max_filesize' );
    ?></li>
                    <li>max_file_uploads: <?php 
    echo ini_get( 'max_file_uploads' );
    ?></li>
                    <li>max_input_vars: <?php 
    echo ini_get( 'max_input_vars' );
    ?></li>
                    <li>post_max_size: <?php 
    echo ini_get( 'post_max_size' );
    ?></li>
                    <li>
                      <br>
                      <strong>Error Logging Configuration:</strong>
                      <div class="oum-error-config">
                        <?php 
    $wp_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
    $wp_debug_log = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
    $php_log_errors = ini_get( 'log_errors' );
    $php_error_log = ini_get( 'error_log' );
    // Helper function for status badges
    if ( !function_exists( 'oum_status_badge' ) ) {
        function oum_status_badge(  $enabled, $label  ) {
            $badge_class = ( $enabled ? 'enabled' : 'disabled' );
            $icon = ( $enabled ? '✓' : '✗' );
            return '<span class="oum-status-badge ' . $badge_class . '">' . $icon . ' ' . esc_html( $label ) . '</span>';
        }

    }
    echo oum_status_badge( $wp_debug, 'WP_DEBUG' );
    echo oum_status_badge( $wp_debug_log, 'WP_DEBUG_LOG' );
    echo oum_status_badge( $php_log_errors, 'PHP log_errors' );
    ?>
                        
                        <?php 
    if ( $wp_debug_log || $php_error_log ) {
        ?>
                        <div class="oum-log-paths">
                          <?php 
        if ( $wp_debug_log ) {
            $wp_debug_log_path = WP_CONTENT_DIR . '/debug.log';
            ?>
                            <strong>WordPress debug.log:</strong> <?php 
            echo esc_html( $wp_debug_log_path );
            ?>
                            <?php 
            if ( file_exists( $wp_debug_log_path ) ) {
                ?>
                              <span class="status-icon success">✓</span>
                            <?php 
            } else {
                ?>
                              <span class="status-icon warning">⚠</span>
                            <?php 
            }
            ?>
                            <br>
                          <?php 
        }
        ?>
                          
                          <?php 
        if ( $php_error_log ) {
            ?>
                            <strong>PHP error_log:</strong> <?php 
            echo esc_html( $php_error_log );
            ?>
                            <?php 
            if ( file_exists( $php_error_log ) ) {
                ?>
                              <span class="status-icon success">✓</span>
                            <?php 
            } else {
                ?>
                              <span class="status-icon warning">⚠</span>
                            <?php 
            }
            ?>
                          <?php 
        }
        ?>
                        </div>
                        <?php 
    }
    ?>
                      </div>
                    </li>
                    <li>
                      <br>
                      <strong>Recent PHP Errors:</strong>
                      <div class="oum-errors-container">
                      <?php 
    // Safely read recent PHP errors from log files (filtered to show only errors, no warnings)
    // This function is wrapped in try-catch to prevent any issues on different server configurations
    try {
        $errors_to_display = [];
        $max_errors = 10;
        $log_source = '';
        // Helper function to safely read and filter error log
        // Returns array of error messages or empty array on failure
        if ( !function_exists( 'oum_read_error_log' ) ) {
            function oum_read_error_log(  $file_path, $max_errors = 10  ) {
                try {
                    $errors = [];
                    // Safety checks
                    if ( empty( $file_path ) || !file_exists( $file_path ) || !is_readable( $file_path ) ) {
                        return $errors;
                    }
                    // Additional safety: check file size (skip if > 10MB to avoid memory issues)
                    if ( filesize( $file_path ) > 10 * 1024 * 1024 ) {
                        return $errors;
                    }
                    // Safely read the last 100 lines from the error log
                    $file = new SplFileObject($file_path, 'r');
                    $file->seek( PHP_INT_MAX );
                    $last_line = $file->key();
                    $start_line = max( 0, $last_line - 100 );
                    $file->seek( $start_line );
                    while ( !$file->eof() ) {
                        $line = trim( $file->current() );
                        if ( !empty( $line ) ) {
                            // Filter to only include actual errors (not warnings, notices, deprecated)
                            if ( preg_match( '/PHP (Fatal error|Parse error|Error|Catchable fatal error):/i', $line ) ) {
                                $errors[] = $line;
                                if ( count( $errors ) >= $max_errors ) {
                                    break;
                                }
                            }
                        }
                        $file->next();
                    }
                    // Return errors in reverse order (most recent first)
                    return array_reverse( $errors );
                } catch ( Exception $e ) {
                    // Silently fail - return empty array
                    return [];
                }
            }

        }
        // Check WordPress debug constants
        $wp_debug_log_enabled = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
        // Priority 1: Check WordPress debug.log if WP_DEBUG_LOG is enabled
        if ( $wp_debug_log_enabled ) {
            $wp_debug_log_path = WP_CONTENT_DIR . '/debug.log';
            $errors_to_display = oum_read_error_log( $wp_debug_log_path, $max_errors );
            if ( !empty( $errors_to_display ) ) {
                $log_source = 'WordPress debug.log';
            }
        }
        // Priority 2: Check PHP error_log if no errors found yet
        if ( empty( $errors_to_display ) ) {
            $error_log_path = ini_get( 'error_log' );
            if ( !empty( $error_log_path ) ) {
                $errors_to_display = oum_read_error_log( $error_log_path, $max_errors );
                if ( !empty( $errors_to_display ) ) {
                    $log_source = 'PHP error_log';
                }
            }
        }
        // Priority 3: Fallback to error_get_last()
        if ( empty( $errors_to_display ) ) {
            $last_error = error_get_last();
            if ( $last_error && isset( $last_error['type'] ) && in_array( $last_error['type'], [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
                E_USER_ERROR,
                E_RECOVERABLE_ERROR
            ] ) ) {
                $errors_to_display[] = sprintf(
                    '[%s] %s in %s on line %d',
                    date( 'Y-m-d H:i:s' ),
                    $last_error['message'],
                    $last_error['file'],
                    $last_error['line']
                );
                $log_source = 'Last error in memory';
            }
        }
        // Display the errors with improved styling
        if ( !empty( $errors_to_display ) ) {
            echo '<div class="oum-error-alert warning">';
            echo '<strong>⚠ ' . count( $errors_to_display ) . ' Error(s) Found</strong>';
            if ( $log_source ) {
                echo ' <span class="source-label">(from ' . esc_html( $log_source ) . ')</span>';
            }
            echo '</div>';
            echo '<pre class="oum-error-log">';
            foreach ( $errors_to_display as $error ) {
                echo esc_html( $error ) . "\n";
            }
            echo '</pre>';
        } else {
            // No errors found - show appropriate message
            $php_error_log_enabled = ini_get( 'log_errors' ) && ini_get( 'error_log' );
            $any_logging_enabled = $wp_debug_log_enabled || $php_error_log_enabled;
            if ( !$any_logging_enabled ) {
                echo '<div class="oum-error-alert warning">';
                echo '<strong>⚠ Error logging is not enabled</strong><br>';
                echo '<span class="help-text">To capture PHP errors, please enable error logging. <a href="https://www.open-user-map.com/knowledge-base/how-to-debug-wordpress/" target="_blank">Learn how to enable error logging →</a></span>';
                echo '</div>';
            } else {
                echo '<div class="oum-error-alert success">';
                echo '<strong>✓ No recent errors detected</strong><br>';
                echo '<span class="success-text">Your site is running smoothly!</span>';
                echo '</div>';
            }
        }
    } catch ( Exception $e ) {
        // Ultimate fallback - if anything goes wrong, show a safe error message
        echo '<div class="oum-error-alert info">';
        echo '<em>Unable to retrieve error log information.</em>';
        echo '</div>';
    }
    ?>
                      </div>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>

          </tbody>
          </table>

        </div>
        <?php 
    if ( !oum_fs()->is_plan_or_trial( 'pro' ) || !oum_fs()->is_premium() ) {
        ?>
        <!-- PRO Trial Tab Pane -->
        <div id="tab-pro-trial" class="oum-tab-pane <?php 
        echo ( $active_tab === 'tab-pro-trial' ? 'active' : '' );
        ?>">
          <h2><?php 
        echo __( 'Try PRO Free for 7 Days', 'open-user-map' );
        ?></h2>
          <p><?php 
        echo __( 'Unlock advanced features like custom markers, CSV import/export, and live filters. Enjoy full access, no credit card needed.', 'open-user-map' );
        ?></p>

          <div class="oum-trial-cta">
            <a href="<?php 
        echo esc_url( oum_fs()->get_trial_url() );
        ?>" class="button button-primary" id="start-free-trial-button">👉 <?php 
        echo __( 'Start Free Trial', 'open-user-map' );
        ?></a> <?php 
        echo __( 'or', 'open-user-map' );
        ?> <a href="https://www.open-user-map.com/?ref=learnmore" target="_blank"><?php 
        echo __( 'Learn More', 'open-user-map' );
        ?></a>
          </div>

          <div class="wrap-pro-feature-list">
            <ul id="oum-pro-features-list">
              <!-- VISIBLE KEY FEATURES -->
              <li>
                <p><strong><?php 
        echo __( 'Filterable Marker Categories', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Organize locations in multiple filterable marker groups. Each group (category) can have an individual marker icon and will be accessible to visitors.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Export &amp; Import locations', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Maybe you already have a list of places you need to show on the map. No problem – just make use of the CSV Export &amp; Import feature. This also comes in handy for bulk editing a lot of locations.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Allow registered users to edit their locations', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'The editing capability allows users to update their existing locations, ensuring the map always reflects the most current information.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Custom marker icons', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Use your own custom marker icon.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Live Marker Filter', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Use the search bar to filter markers as you type. Instantly see relevant locations on the map.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Pre-Filter locations by user or role', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Shortcode Attribute to filter locations by current user, user_id or a role. Works for map, list and gallery.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Upvote & Star Rating', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Add a customizable feedback button for likes, upvotes, flagging, or star rating (1-5 stars). Let visitors interact with locations and provide valuable feedback.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Advanced Filter Interface', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Create a filter sidebar next to your map so visitors can filter locations by any custom field. Add custom HTML sections for branding or extra information.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Custom Image as Map', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Use a custom image as your map layer (e.g. floor plans or event maps). Keep all interactions, clustering and popups while displaying your own image.', 'open-user-map' );
        ?></p>
              </li>

              <li>
                <p><strong><?php 
        echo __( 'Opening Hours', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Add an Opening Hours custom field type. Visitors see structured opening times; you can manage weekly schedules and special days in the location form.', 'open-user-map' );
        ?></p>
              </li>

              <!-- HIDDEN FEATURES -->

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'More custom field types', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Add custom fields like links, radio buttons, checkboxes, dropdowns, Opening Hours, and even HTML. Social media links will automatically be rendered as icons.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Additional geosearch providers (Geoapify, Here, MapBox)', 'open-user-map' );
        ?></strong></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Videos from YouTube &amp; Vimeo', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Use videos instead of images to highlight each location.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Email user notification', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Users get notified by email after their location has been approved.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Email Admin notification', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Send notifications to an Admin email account on incoming location proposals.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Zapier/Webhook Integration', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Trigger an external webhook on new or updated locations.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'User restriction', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Restrict “Add location” feature to registered users only. Redirect your visitors to the registration form.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Auto-publish', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'When activated registered users will publish directly without admin approval. This can also be enabled for unregistered users.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Extend user registration', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Integrate the “Add location” feature to the WordPress user registration form.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Custom filesizes', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Customize max. filesize for image/audio uploads (default: 10MB).', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Custom UI Elements color', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Pick a color for buttons and icons that fits your theme.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Current location', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Add a button that relocates the map to the users current location.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Limit visible locations', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'You can limit the locations to be shown by marker categories and by post ids. So it is possible to show only locations of a specific type or to render only one single location. This can be done in the block settings or with shortcode attributes.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Single pages for locations', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Per default locations will not have a single page. This should ensure that all the locations wont negatively affect your SEO. But if you’d like to add more content to locations you can easily enable it in the PRO version.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Default template for Single pages', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'If Single pages are enabled locations will automatically have a post thumbnail, an excerpt as well as a default content template. And you can show locations within query loop blocks to setup your own list or grid view.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Shortcodes for single location values', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Use the shortcode [open-user-map-location value=”YOUR VALUE” post_id=”POST ID (optional)”] to easily display a value from a location. You will need this when creating single pages for locations. See all the available values in the Help section of the plugin settings (Open User Map > Settings > Help).', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Show all location images as gallery', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Use the shortcode [open-user-map-gallery] to get a nice gallery view of all the location images. Each image is linked to the location marker on the map.', 'open-user-map' );
        ?></p>
              </li>

              <li class="hidden-feature">
                <p><strong><?php 
        echo __( 'Show all locations as list or grid', 'open-user-map' );
        ?></strong><br>
                <?php 
        echo __( 'Use the shortcode [open-user-map-list] to get a list view of all the locations. Alternatively you can use the native query loops block to build a custom list or grid.', 'open-user-map' );
        ?></p>
              </li>

            </ul>

            <p class="toggle-pro-feature-list-wrapper">
              <a href="#" id="toggle-pro-feature-list"><?php 
        echo __( '↓ Show all PRO features', 'open-user-map' );
        ?></a>
            </p>
          </div>

        </div>
        <?php 
    }
    ?>
      </div>

      <?php 
    wp_enqueue_script(
        'oum_backend_settings_ui_js',
        $this->plugin_url . 'src/js/backend-settings-ui.js',
        array(),
        $this->plugin_version,
        true
    );
    ?>
      <?php 
    wp_enqueue_script(
        'oum_backend_settings_save_bar_js',
        $this->plugin_url . 'src/js/backend-settings-save-bar.js',
        array(),
        $this->plugin_version,
        true
    );
    ?>
      <div class="oum-settings-save-area">
        <div class="oum-settings-save-bar">
          <?php 
    submit_button();
    ?>
          <span class="oum-settings-save-status" role="status" aria-live="polite" data-unsaved-label="<?php 
    echo esc_attr__( 'Unsaved changes', 'open-user-map' );
    ?>"></span>
        </div>
      </div>

    <?php 
}
?>

    <script>
    // Feature help: hover, click, keyboard, and outside dismissal share one state.
    const tooltipTriggers = document.querySelectorAll('.oum-tooltip');
    // A viewport layer avoids overflow clipping by cards and settings tables.
    const tooltipLayer = document.createElement('span');
    tooltipLayer.className = 'oum-tooltip oum-tooltip-layer';
    document.body.appendChild(tooltipLayer);
    tooltipTriggers.forEach(function(trigger) {
        const tooltip = trigger.querySelector('.oum-tooltip-content');
        const button = trigger.querySelector('.oum-tooltip-trigger');
        if (!tooltip || !button) return;
        let timeout;
        let pinned = false;
        const contains = target => trigger.contains(target) || tooltip.contains(target);
        function position() {
            const anchor = button.getBoundingClientRect();
            const toolbar = document.getElementById('wpadminbar');
            const topLimit = Math.max(12, toolbar ? toolbar.getBoundingClientRect().bottom + 12 : 12);
            const bottomLimit = window.innerHeight - 12;
            if (anchor.bottom < topLimit || anchor.top > bottomLimit) {
                pinned = false;
                setOpen(false);
                return;
            }
            tooltip.style.left = '0';
            tooltip.style.top = '0';
            tooltip.style.bottom = 'auto';
            tooltip.style.transform = 'none';
            tooltip.style.overflowY = 'visible';
            tooltip.style.maxHeight = Math.max(0, bottomLimit - topLimit) + 'px';
            let bounds = tooltip.getBoundingClientRect();
            const above = Math.max(0, anchor.top - topLimit - 8);
            const below = Math.max(0, bottomLimit - anchor.bottom - 8);
            const placeAbove = bounds.height <= above || above > below;
            const availableHeight = placeAbove ? above : below;
            const needsScroll = bounds.height > availableHeight;
            tooltip.style.maxHeight = availableHeight + 'px';
            bounds = tooltip.getBoundingClientRect();
            const left = Math.max(12, Math.min(anchor.left, window.innerWidth - bounds.width - 12));
            const top = placeAbove ? anchor.top - bounds.height - 8 : anchor.bottom + 8;
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            if (needsScroll) tooltip.style.overflowY = 'auto';
            tooltip.dataset.placement = placeAbove ? 'above' : 'below';
            tooltip.style.setProperty('--oum-tooltip-arrow-left', Math.max(8, Math.min(bounds.width - 8, anchor.left + anchor.width / 2 - left)) + 'px');
        }
        function setOpen(open) {
            clearTimeout(timeout);
            tooltip.style.display = open ? 'block' : 'none';
            button.setAttribute('aria-expanded', String(open));
            if (open) {
                if (tooltip.parentElement !== tooltipLayer) tooltipLayer.appendChild(tooltip);
                position();
            } else if (tooltip.parentElement !== trigger) {
                trigger.appendChild(tooltip);
            }
        }
        function leave() {
            timeout = setTimeout(function() {
                if (!pinned && !contains(document.activeElement)) setOpen(false);
            }, 100);
        }
        trigger.addEventListener('mouseenter', function() { setOpen(true); });
        trigger.addEventListener('mouseleave', leave);
        tooltip.addEventListener('mouseenter', function() { clearTimeout(timeout); });
        tooltip.addEventListener('mouseleave', leave);
        button.addEventListener('click', function(event) {
            event.preventDefault();
            pinned = !pinned;
            setOpen(pinned);
        });
        button.addEventListener('keydown', function(event) {
            if (event.key === 'ArrowDown' || (event.key === 'Tab' && !event.shiftKey && tooltip.style.display === 'block')) {
                const link = tooltip.querySelector('a');
                if (link) {
                    event.preventDefault();
                    setOpen(true);
                    link.focus();
                }
            }
        });
        tooltip.addEventListener('keydown', function(event) {
            if (event.key === 'Tab' && event.shiftKey && event.target === tooltip.querySelector('a')) {
                event.preventDefault();
                button.focus();
            }
        });
        [trigger, tooltip].forEach(function(element) {
            element.addEventListener('focusin', function(event) {
                if (event.target.matches(':focus-visible')) setOpen(true);
            });
            element.addEventListener('focusout', function(event) {
                if (!contains(event.relatedTarget)) {
                    pinned = false;
                    setOpen(false);
                }
            });
            element.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    pinned = false;
                    button.focus();
                    setOpen(false);
                    event.stopPropagation();
                }
            });
        });
        document.addEventListener('click', function(event) {
            if (!contains(event.target)) {
                pinned = false;
                setOpen(false);
            }
        });
        function updatePosition(event) {
            if (tooltip.style.display === 'block' && event.target !== tooltip) position();
        }
        window.addEventListener('resize', updatePosition);
        window.addEventListener('scroll', updatePosition, true);
    });
    </script>

</form>
</div>
