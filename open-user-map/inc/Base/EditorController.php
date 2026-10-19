<?php
namespace OpenUserMapPlugin\Base;

/** Authenticated, data-only editor preview; no visitor scripts or custom JS. */
class EditorController extends BaseController {
    public function register() {
        add_action('enqueue_block_editor_assets', array($this, 'assets'));
        add_action('elementor/preview/enqueue_scripts', array($this, 'assets'));
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'elementor_controls'));
        add_action('wp_ajax_oum_editor_map', array($this, 'preview'));
    }

    private function filter_assets() {
        wp_enqueue_script('oum_editor_filters',$this->plugin_url.'src/js/editor-filters.js',array('wp-element','wp-components','wp-i18n'),filemtime($this->plugin_path.'src/js/editor-filters.js'),true);
        wp_localize_script('oum_editor_filters','oumFilterConfig',self::filter_configuration());
        wp_enqueue_style('wp-components');
        wp_enqueue_style('oum_filter_controls',$this->plugin_url.'assets/editor-filters.css',array(),filemtime($this->plugin_path.'assets/editor-filters.css'));
        wp_set_script_translations('oum_editor_filters','open-user-map',$this->plugin_path.'languages');
    }

    public static function filter_configuration() {
        $pro=function_exists('oum_fs') && oum_fs()->is__premium_only() && oum_fs()->can_use_premium_code();
        $config=array('pro'=>(bool)$pro,'dateType'=>get_option('oum_location_date_type','updated'),'fields'=>array(),'roles'=>array(),'users'=>array());
        if(!$pro)return $config;
        foreach((array)get_option('oum_custom_fields',array()) as $field){
            if(!is_array($field) || empty($field['label']) || !empty($field['private']) || preg_match('/[;|]/',$field['label']))continue;
            $config['fields'][]=array('label'=>(string)$field['label'],'values'=>array_values(array_filter(array_map('trim',explode('|',isset($field['options']) ? (string)$field['options'] : '')),'strlen')));
        }
        foreach(wp_roles()->roles as $slug=>$role)$config['roles'][]=array('value'=>$slug,'label'=>translate_user_role($role['name']));
        // Public location authors only; never send email addresses or private profile fields.
        global $wpdb;
        $authors=$wpdb->get_col($wpdb->prepare("SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type=%s AND post_status=%s",'oum-location','publish'));
        $authors[]=get_current_user_id();
        foreach(array_unique($authors) as $id){$user=get_userdata((int)$id);if($user)$config['users'][]=array('value'=>(string)$user->ID,'label'=>$user->display_name.' (#'.$user->ID.')');}
        return $config;
    }

    public function elementor_controls() {
        $this->filter_assets();
        wp_enqueue_style('oum_editor_controls',$this->plugin_url.'assets/editor-controls.css',array(),filemtime($this->plugin_path.'assets/editor-controls.css'));
        wp_localize_script('elementor-editor','oumEditorControls',array('search'=>__('Search…','open-user-map')));
        wp_enqueue_script('oum_editor_controls', $this->plugin_url.'src/js/editor-elementor-controls.js', array('elementor-editor','oum_editor_filters'), filemtime($this->plugin_path.'src/js/editor-elementor-controls.js'), true);
    }

    public function assets() {
        $this->filter_assets();
        (new Enqueue())->register_admin_assets();
        $this->include_map_scripts(get_option('oum_map_style', 'OpenFreeMap.bright'));
        wp_enqueue_script('oum_editor_map', $this->plugin_url.'src/js/editor-map.js', array('oum_map_styles_js', 'oum_leaflet_markercluster_js'), filemtime($this->plugin_path.'src/js/editor-map.js'), true);
        wp_localize_script('oum_editor_map', 'oumEditor', array(
            'addLocationUrl'=>current_user_can('edit_posts') ? admin_url('post-new.php?post_type=oum-location') : '',
            'previewImage'=>$this->plugin_url.'assets/block-preview.png',
            'iconImage'=>$this->plugin_url.'assets/images/icon-256x256.png',
            'settingsUrl'=>admin_url('edit.php?post_type=oum-location&page=open-user-map-settings'), 'locationsUrl'=>admin_url('edit.php?post_type=oum-location'),
            'url'=>admin_url('admin-ajax.php'), 'nonce'=>wp_create_nonce('oum_editor_map'),
            'runtimeScripts'=>array_map(function($path){return add_query_arg('ver',filemtime($this->plugin_path.$path),$this->plugin_url.$path);}, array(
                'src/leaflet/leaflet.js','src/leaflet/leaflet-providers.js','src/leaflet/leaflet-markercluster.js',
                'src/maplibre/maplibre-gl.js','src/maplibre/leaflet-maplibre-gl.js','src/js/oum-map-styles.js','src/js/editor-map.js'
            )),
            'shadow'=>$this->plugin_url.'src/leaflet/images/marker-shadow.png',
            'labels'=>array(
                'manageLocations'=>__('Manage Locations','open-user-map'), 'settings'=>__('Settings','open-user-map'), 'addMarkers'=>__('Add and edit markers','open-user-map'), 'moveHint'=>__('Move and zoom.','open-user-map'),
                'resetView'=>__('Reset map view','open-user-map'),
                'empty'=>__('No locations yet.','open-user-map'),
                'unavailable'=>__('No visible locations.','open-user-map'), 'addLocation'=>__('Add location','open-user-map'),
                'globalSettings'=>__('Global settings','open-user-map'), 'missingKey'=>__('Add a map key.','open-user-map'),
                'checkStyle'=>__('Check map style.','open-user-map'), 'tilesError'=>__('Map tiles could not load.','open-user-map'), 'sessionExpired'=>__('Session expired.','open-user-map'),
                'reloadEditor'=>__('Reload editor','open-user-map'), 'connectionError'=>__('Connection lost.','open-user-map'),
                'height'=>__('Height','open-user-map'),'previous'=>__('Previous image','open-user-map'),'next'=>__('Next image','open-user-map'),'edit'=>__('Edit view','open-user-map'),'done'=>__('Done','open-user-map'),'reset'=>__('Reset','open-user-map'),'loading'=>__('Loading map…','open-user-map'),'error'=>__('Map could not be loaded.','open-user-map'),'retry'=>__('Retry','open-user-map'),'zoomIn'=>__('Zoom in','open-user-map'),'zoomOut'=>__('Zoom out','open-user-map'),'checkView'=>__('Check map view.','open-user-map'),'noLocations'=>__('No locations found.','open-user-map')),
            'heightControls'=>BlockHeight::editor_configuration(),
            'globals'=>self::globals(), 'choices'=>self::choices(),
        ));
        wp_enqueue_style('oum_editor_popups',$this->plugin_url.'assets/editor-popups.css',array(),filemtime($this->plugin_path.'assets/editor-popups.css'));
        wp_enqueue_style('oum_editor_map', $this->plugin_url.'assets/editor-map.css', array(), filemtime($this->plugin_path.'assets/editor-map.css'));
    }

    public static function globals() {
        return array('lat'=>(string)get_option('oum_start_lat', ''), 'long'=>(string)get_option('oum_start_lng', ''), 'zoom'=>(string)get_option('oum_start_zoom', ''),
            'size'=>get_option('oum_map_size', 'default'), 'height'=>(string)get_option('oum_map_height', ''), 'height_mobile'=>(string)get_option('oum_map_height_mobile', ''), 'hide_location_popup'=>get_option('oum_hide_location_popup') === 'on' ? 'true' : 'false');
    }

    public static function choices() {
        $choices = array('region'=>array(), 'types'=>array(), 'ids'=>array());
        foreach (array('region'=>'oum-region', 'types'=>'oum-type') as $key=>$taxonomy) {
            $terms = get_terms(array('taxonomy'=>$taxonomy, 'hide_empty'=>false));
            if (!is_wp_error($terms)) foreach ($terms as $term) $choices[$key][] = array('value'=>$key === 'region' ? $term->name : $term->slug, 'label'=>$term->name);
        }
        $posts = get_posts(array('post_type'=>'oum-location', 'post_status'=>'publish', 'numberposts'=>-1));
        $titles = array();
        foreach ($posts as $post) {
            $title = get_the_title($post);
            $titles[$title] = isset($titles[$title]) ? $titles[$title] + 1 : 1;
            $choices['ids'][] = array('value'=>(string)$post->ID, 'label'=>$title);
        }
        foreach ($choices['ids'] as &$choice) {
            if ($titles[$choice['label']] > 1) $choice['label'] .= ' (#'.$choice['value'].')';
        }
        unset($choice);
        return $choices;
    }

    public function preview() {
        check_ajax_referer('oum_editor_map', 'nonce');
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) wp_send_json_error(array('message'=>'Forbidden'), 403);
        $raw = isset($_POST['attributes']) ? json_decode(wp_unslash($_POST['attributes']), true) : array();
        $block_attributes = array();
        foreach (array('lat','long','zoom','region','types','ids','size','height','height_mobile','hide_location_popup','types-relation','date-filter','date_filter','user','custom-fields-filter','custom-fields-filter-relation') as $key) {
            if (isset($raw[$key]) && is_scalar($raw[$key])) $block_attributes[$key] = sanitize_text_field((string)$raw[$key]);
        }
        wp_send_json_success($this->configuration($block_attributes));
    }

    public function configuration($block_attributes) {
        // Reuse the frontend's filtering, icons, region and initial-view precedence.
        // Only return fields needed for drawing; private/custom fields are excluded.
        $content = '';
        ob_start();
        require $this->plugin_path.'templates/partial-map-init.php';
        ob_end_clean();
        $markers = array();
        foreach ($locations_list as $location) {
            // Admin WP_Query also includes drafts; preview the same public locations as visitors.
            if (get_post_status($location['post_id']) !== 'publish') continue;
            $markers[] = array_intersect_key($location, array_flip(array('post_id','title','lat','lng','icon','geometry_type','geometry','category_color')));
        }
        $empty_state = '';
        if (!$markers) {
            $has_locations = (bool)get_posts(array('post_type'=>'oum-location','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','no_found_rows'=>true));
            $has_filters = false;
            foreach (array('ids','types','date-filter','date_filter','user','custom-fields-filter') as $key) {
                if (isset($block_attributes[$key]) && trim((string)$block_attributes[$key]) !== '') $has_filters = true;
            }
            $empty_state = !$has_locations ? 'empty' : ($has_filters ? 'filtered' : 'unavailable');
        }
        $definition = $this->get_map_style_definition($map_style);
        $required_key = isset($definition['requires_api_key']) ? $definition['requires_api_key'] : '';
        $missing_key = $required_key && !$this->get_map_style_api_key_value($required_key);
        return array(
            'emptyState'=>$empty_state, 'missingKey'=>(bool)$missing_key,
            'center'=>array((float)$start_lat,(float)$start_lng), 'zoom'=>(float)$start_zoom,
            'style'=>$map_style, 'markers'=>$markers, 'cluster'=>$oum_enable_cluster === 'true',
            'size'=>$map_size, 'height'=>$oum_map_height, 'height_mobile'=>$oum_map_height_mobile,
            'hidePopup'=>$oum_hide_location_popup === 'true',
            'mapboxKey'=>$oum_tile_provider_mapbox_key, 'cartoKey'=>$oum_tile_provider_carto_key,
            'customImage'=>array('url'=>$oum_custom_image_url, 'bounds'=>json_decode($oum_custom_image_bounds,true), 'hideTiles'=>$oum_custom_image_hide_tiles, 'background'=>$oum_custom_image_background_color),
        );
    }
}
