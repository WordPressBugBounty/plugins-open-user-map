<?php
namespace OpenUserMapPlugin\Base;

/** Optional Gutenberg device widths; legacy blocks keep their existing output. */
class BlockWidth {
    private static function valid($value) {
        return in_array($value, array('default', 'fullwidth'), true);
    }
    public static function enabled($attributes) {
        return (isset($attributes['size_mobile']) && self::valid($attributes['size_mobile'])) || (isset($attributes['size_tablet']) && self::valid($attributes['size_tablet']));
    }
    private static function rule($selector, $value) {
        $full = $value === 'fullwidth';
        return $selector.'{--oum-block-width:'.($full ? '100vw' : '100%').';--oum-block-offset:'.($full ? '50%' : '0px').';--oum-block-translate:'.($full ? '-50%' : '0px').';}';
    }
    public static function responsive_css($attributes) {
        if (!self::enabled($attributes)) return '';
        $selector = '.'.BlockHeight::class_name($attributes);
        $base = !empty($attributes['size']) ? $attributes['size'] : get_option('oum_map_size', 'default');
        $css = self::rule($selector, $base);
        $queries = BlockHeight::media_queries();
        if (isset($attributes['size_mobile']) && self::valid($attributes['size_mobile'])) {
            $query = !empty($attributes['width_responsive']) ? $queries['@mobile'] : '@media (max-width:767px)';
            $css .= $query.'{'.self::rule($selector, $attributes['size_mobile']).'}';
        }
        if (isset($attributes['size_tablet']) && self::valid($attributes['size_tablet'])) $css .= $queries['@tablet'].'{'.self::rule($selector, $attributes['size_tablet']).'}';
        return $css;
    }
}
