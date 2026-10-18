<?php
namespace OpenUserMapPlugin\Base;

/** Read adapters: never resolve inherited values into persisted local settings. */
class EditorSettings {
    public static function elementor_attributes($settings) {
        $keys = array('latitude'=>'lat', 'longitude'=>'long', 'zoom'=>'zoom', 'region'=>'region', 'types'=>'types', 'ids'=>'ids', 'oum_map_size'=>'size', 'oum_map_height'=>'height', 'oum_map_height_mobile'=>'height_mobile', 'hide_location_popup'=>'hide_location_popup');
        $attributes = array();
        foreach ($keys as $key => $attribute) {
            if (isset($settings[$key]) && is_scalar($settings[$key]) && (string)$settings[$key] !== '') {
                $attributes[$attribute] = (string)$settings[$key];
            }
        }
        // New Elementor controls keep the existing shortcode attributes in one history value.
        $filters=isset($settings['oum_filters']) && is_string($settings['oum_filters']) ? json_decode($settings['oum_filters'],true) : array();
        foreach(array('types-relation','date-filter','date_filter','user','custom-fields-filter','custom-fields-filter-relation') as $key){
            if(isset($settings[$key]) && is_scalar($settings[$key]) && (string)$settings[$key]!=='')$attributes[$key]=(string)$settings[$key];
            if(is_array($filters) && array_key_exists($key,$filters) && is_scalar($filters[$key])){
                if((string)$filters[$key]==='')unset($attributes[$key]);else $attributes[$key]=(string)$filters[$key];
            }
        }
        return $attributes;
    }

    public static function shortcode($attributes) {
        $parts = array();
        foreach ($attributes as $key => $value) {
            if (is_scalar($value) && (string)$value !== '') {
                // Prevent a saved setting from injecting a shortcode delimiter.
                $parts[] = sanitize_key($key) . '="' . esc_attr(str_replace(array('[', ']'), '', (string)$value)) . '"';
            }
        }
        return '[open-user-map ' . implode(' ', $parts) . ']';
    }
}
