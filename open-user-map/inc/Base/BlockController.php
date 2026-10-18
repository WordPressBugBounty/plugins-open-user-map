<?php
/**
 * @package OpenUserMapPlugin
 */

namespace OpenUserMapPlugin\Base;

use OpenUserMapPlugin\Base\BaseController;

class BlockController extends BaseController
{
    public function register() {
        // Gutenberg Blocks
        add_action('init', array($this, 'set_gutenberg_blocks'));

        // Elementor Widgets
        add_action('plugins_loaded', array($this, 'set_elementor_widgets'));
    }

    /**
     * Setup Gutenberg Blocks
     */
    public function set_gutenberg_blocks()
    {   
        // Register Block
        register_block_type_from_metadata(
            $this->plugin_path . 'blocks',
            array(
                'render_callback' => array($this, 'render_native_map')
            )
        );

        wp_set_script_translations('open-user-map-map-editor-script', 'open-user-map', $this->plugin_path.'languages');


    }

    public function render_native_map($attributes, $content) {
        $native_height = isset($attributes['style']['dimensions']['minHeight']) ? $attributes['style']['dimensions']['minHeight'] : '';
        if (is_scalar($native_height) && (string)$native_height !== '') {
            $attributes['height'] = (string)$native_height;
        }
        $map = $this->render_block_map($attributes, $content);
        // Old blocks keep their exact markup unless native styles are explicitly set.
        if (!empty($attributes['style']) || !empty($attributes['align']) || (!empty($attributes['className']) && !empty($attributes['editorVersion']))) {
            $radius=!empty($attributes['style']['border']['radius']);
            $wrapper = function_exists('get_block_wrapper_attributes') ? get_block_wrapper_attributes(array('class'=>'oum-native-block'.($radius ? ' has-oum-radius' : '').($native_height!=='' ? ' has-oum-height' : ''))) : 'class="oum-native-block"';
            return '<div '.$wrapper.'>'.$map.'</div>';
        }
        return $map;
    }

    /**
     * Setup Elementor Widgets
     */
    public function set_elementor_widgets($widgets_manager)
    {
        require_once "$this->plugin_path/elementor/includes/plugin.php";

        // Run the plugin
        \Elementor_OUM_Addon\Plugin::instance();
    }
}
