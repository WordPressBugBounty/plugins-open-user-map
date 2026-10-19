<?php
namespace OpenUserMapPlugin\Base;

/** Apply Gutenberg sizing to the inner map while preserving legacy block attributes. */
class BlockHeight {
    public static function media_queries() {
        if (is_callable(array('WP_Theme_JSON', 'get_viewport_media_queries'))) {
            return \WP_Theme_JSON::get_viewport_media_queries(wp_get_global_settings(array('viewport')));
        }
        return array('@mobile'=>'@media (max-width:480px)', '@tablet'=>'@media (min-width:480.01px) and (max-width:782px)');
    }

    public static function editor_configuration() {
        global $wp_version;
        return array(
            'stylesGroup'=>version_compare($wp_version, '6.2', '>='),
            'responsive'=>function_exists('wp_is_block_theme') && wp_is_block_theme() && is_callable(array('WP_Theme_JSON', 'get_viewport_media_queries')),
            'mediaQueries'=>self::media_queries(),
        );
    }

    private static function length($value) {
        if (!is_scalar($value)) return '';
        $value = trim((string)$value);
        if (is_numeric($value)) $value .= 'px';
        return preg_match('/^(?:\d+(?:\.\d+)?|\.\d+)(?:px|em|rem|vh|vw|vmin|vmax|svh|lvh|dvh|%)$/D', $value) ? $value : '';
    }

    private static function ratio($value) {
        if (!is_scalar($value) || !preg_match('/^\d+(?:\.\d+)?(?:\s*\/\s*\d+(?:\.\d+)?)?$/D', (string)$value)) return '';
        foreach (explode('/', (string)$value) as $part) if ((float)$part <= 0) return '';
        return (string)$value;
    }

    private static function geometry($style) {
        $dimensions = isset($style['dimensions']) && is_array($style['dimensions']) ? $style['dimensions'] : array();
        $ratio = isset($dimensions['aspectRatio']) ? self::ratio($dimensions['aspectRatio']) : '';
        if ($ratio !== '') return array('height'=>'auto', 'ratio'=>$ratio);
        $height = isset($dimensions['height']) ? $dimensions['height'] : (isset($dimensions['minHeight']) ? $dimensions['minHeight'] : '');
        $height = self::length($height);
        return $height !== '' ? array('height'=>$height, 'ratio'=>'1.77') : null;
    }

    public static function class_name($attributes) {
        return 'oum-height-'.substr(md5(wp_json_encode($attributes)), 0, 12);
    }

    private static function rule($selector, $geometry) {
        return $selector.'{--oum-block-height:'.$geometry['height'].';--oum-block-ratio:'.$geometry['ratio'].';}';
    }

    public static function responsive_css($attributes) {
        $style = isset($attributes['style']) && is_array($attributes['style']) ? $attributes['style'] : array();
        $selector = '.'.self::class_name($attributes);
        $base = self::geometry($style);
        $native_base = (isset($style['dimensions']['height']) && self::length($style['dimensions']['height']) !== '') || (isset($style['dimensions']['aspectRatio']) && self::ratio($style['dimensions']['aspectRatio']) !== '');
        $states = '';
        foreach (self::media_queries() as $state=>$query) {
            $value = isset($style[$state]) ? self::geometry($style[$state]) : null;
            if ($value) $states .= $query.'{'.self::rule($selector, $value).'}';
        }
        $mobile_ratio = isset($attributes['aspect_ratio_mobile']) ? self::ratio($attributes['aspect_ratio_mobile']) : '';
        // No automatic migration: existing height/minHeight maps keep their exact output and mobile behavior.
        if (!$native_base && $states === '' && $mobile_ratio === '') return '';
        if (!$base) {
            $height = !empty($attributes['height']) ? $attributes['height'] : get_option('oum_map_height', '');
            $height = self::length($height);
            $base = array('height'=>$height !== '' ? $height : 'auto', 'ratio'=>'1.77');
        }
        $css = self::rule($selector, $base);
        $mobile = !empty($attributes['height_mobile']) ? $attributes['height_mobile'] : (!$native_base ? get_option('oum_map_height_mobile', '') : '');
        $mobile = self::length($mobile);
        if ($mobile_ratio !== '' || $mobile !== '') {
            $css .= '@media (max-width:767px){'.self::rule($selector, $mobile_ratio !== '' ? array('height'=>'auto','ratio'=>$mobile_ratio) : array('height'=>$mobile,'ratio'=>'1.77')).'}';
        }
        return $css.$states;
    }
}
