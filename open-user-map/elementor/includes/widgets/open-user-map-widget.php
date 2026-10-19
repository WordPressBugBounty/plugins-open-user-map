<?php
namespace Elementor_OUM_Addon;

class Elementor_Open_User_Map_Widget extends \Elementor\Widget_Base {
  public function get_name() {
		return 'open_user_map_widget';
	}

	public function get_title() {
		return esc_html__( 'Open User Map', 'open-user-map' );
	}

	public function get_icon() {
		return 'eicon-google-maps oum-widget-icon';
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'map', 'location', 'leaflet', 'marker' ];
	}

	public function get_style_depends() {

		wp_register_style('oum_style', plugins_url( '../../../assets/style.css', __FILE__ ));

		return [
			'oum_style'
		];

	}

    protected function register_controls() {
        // Supplement older downloaded language packs with the bundled editor terms.
        get_translations_for_domain('open-user-map');
        load_textdomain('open-user-map', dirname(__DIR__, 3).'/languages/open-user-map-'.determine_locale().'.mo');
        $globals = \OpenUserMapPlugin\Base\EditorController::globals();
        $choices = \OpenUserMapPlugin\Base\EditorController::choices();
        $this->start_controls_section('section_custom_map_position', ['label'=>__('Map view', 'open-user-map')]);
        $this->add_control('custom_map_position_note', ['type'=>\Elementor\Controls_Manager::RAW_HTML, 'raw'=>esc_html__('Choose "Edit view" on the map and then move and zoom the map', 'open-user-map').'<br><br>'.esc_html__('Add markers in "Manage Locations" or directly on the map in the front end.', 'open-user-map')]);
        $this->add_control('oum_map_links', ['type'=>\Elementor\Controls_Manager::RAW_HTML, 'raw'=>'<a target="_blank" rel="noopener" href="'.esc_url(admin_url('edit.php?post_type=oum-location&page=open-user-map-settings')).'">'.esc_html__('Global settings','open-user-map').'</a> · <a target="_blank" rel="noopener" href="'.esc_url(admin_url('edit.php?post_type=oum-location')).'">'.esc_html__('Manage locations','open-user-map').'</a>']);
        $this->add_control('region', ['label'=>__('Region', 'open-user-map'), 'type'=>'oum_region', 'oum_choices'=>$choices['region']]);
        // Keep stored coordinates available to the map and native history, without manual fields.
        foreach (['latitude','longitude','zoom'] as $key) $this->add_control($key, ['type'=>\Elementor\Controls_Manager::HIDDEN]);
        $this->end_controls_section();
        $this->start_controls_section('section_custom_locations', ['label'=>__('Filter', 'open-user-map')]);
        // Keep the old pipe-delimited storage. A name picker writes it through a custom control.
        foreach (['types'=>__('Categories', 'open-user-map'), 'ids'=>__('Locations', 'open-user-map')] as $key=>$label) {
            $this->add_control($key, ['label'=>$label, 'label_block'=>true, 'type'=>'oum_locations', 'oum_choices'=>$choices[$key], 'description'=>__('Leave empty to show all.', 'open-user-map')]);
        }
        $this->add_control('oum_filters',['type'=>'oum_filters']);
        $this->end_controls_section();
        $this->start_controls_section('section_marker_popups', ['label'=>__('Popups', 'open-user-map')]);
        $popup = $globals['hide_location_popup'] === 'true' ? __('Hidden', 'open-user-map') : __('Visible', 'open-user-map');
        $this->add_control('hide_location_popup', ['label'=>__('Popups', 'open-user-map'), 'type'=>\Elementor\Controls_Manager::SELECT, 'options'=>[''=>$popup.' ('.__('Global setting', 'open-user-map').')','false'=>__('Visible', 'open-user-map'),'true'=>__('Hidden', 'open-user-map')]]);
        $this->add_control('oum_radius_native', ['type'=>\Elementor\Controls_Manager::HIDDEN]);
        $this->add_control('oum_editor_version', ['type'=>\Elementor\Controls_Manager::HIDDEN]);
        $this->end_controls_section();
        $this->start_controls_section('section_title_style', ['label'=>__('Dimensions', 'open-user-map'), 'tab'=>\Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('oum_map_size', ['label'=>__('Width', 'open-user-map'), 'type'=>\Elementor\Controls_Manager::SELECT, 'frontend_available'=>true, 'default'=>'', 'options'=>[''=>__('Automatic','open-user-map'),'default'=>__('Column width', 'open-user-map'),'fullwidth'=>__('Screen width', 'open-user-map')]]);
        // Keep every legacy numeric device value; the native size control reads it without migration.
        $this->add_responsive_control('oum_map_height', ['type'=>\Elementor\Controls_Manager::HIDDEN,'frontend_available'=>true]);
        $this->add_responsive_control('oum_height_mode', ['label'=>__('Height','open-user-map'),'type'=>'oum_height_mode','classes'=>'elementor-control-type-select','frontend_available'=>true,'default'=>'','options'=>[''=>__('Automatic','open-user-map'),'ratio'=>__('Aspect ratio','open-user-map'),'height'=>__('Fixed height','open-user-map')]]);
        $height_args=[];$ratio_args=[];$custom_args=[];
        $devices=\Elementor\Plugin::$instance->breakpoints->get_active_devices_list(['reverse'=>true,'desktop_first'=>true]);
        foreach($devices as $device){
            $suffix=$device==='desktop' ? '' : '_'.$device;
            $height_args[$device]=['conditions'=>['relation'=>'or','terms'=>[
                ['name'=>'oum_height_mode'.$suffix,'operator'=>'===','value'=>'height'],
                ['relation'=>'and','terms'=>[['name'=>'oum_height_mode'.$suffix,'operator'=>'===','value'=>''],['name'=>'oum_map_height'.$suffix,'operator'=>'!==','value'=>'']]]
            ]]];
            $ratio_args[$device]=['conditions'=>['terms'=>[['name'=>'oum_height_mode'.$suffix,'operator'=>'===','value'=>'ratio']]]];
            $custom_args[$device]=['conditions'=>['terms'=>[['name'=>'oum_height_mode'.$suffix,'operator'=>'===','value'=>'ratio'],['name'=>'oum_ratio'.$suffix,'operator'=>'===','value'=>'custom']]]];
        }
        $this->add_responsive_control('oum_height_value', ['label'=>__('Fixed height','open-user-map'),'type'=>'oum_height_value','label_block'=>false,'classes'=>'elementor-control-type-slider','frontend_available'=>true,'size_units'=>['px','%','em','rem','vh','vw','svh','lvh','dvh','vmin','vmax'],'range'=>['px'=>['min'=>1,'max'=>1200],'vh'=>['min'=>1,'max'=>100],'%'=>['min'=>1,'max'=>100]],'device_args'=>$height_args]);
        $this->add_responsive_control('oum_ratio', ['label'=>__('Aspect ratio','open-user-map'),'type'=>\Elementor\Controls_Manager::SELECT,'frontend_available'=>true,'default'=>'','options'=>['16/9'=>'16:9','4/3'=>'4:3','1/1'=>'1:1','3/4'=>'3:4','9/16'=>'9:16','custom'=>__('Custom','open-user-map')],'device_args'=>$ratio_args]);
        $this->add_responsive_control('oum_ratio_custom', ['label'=>__('Aspect ratio','open-user-map'),'type'=>\Elementor\Controls_Manager::TEXT,'frontend_available'=>true,'placeholder'=>'16/9','device_args'=>$custom_args]);
        $this->add_control('oum_reset_height',['type'=>\Elementor\Controls_Manager::BUTTON,'text'=>__('Reset','open-user-map'),'event'=>'oum:reset-height']);
        $this->end_controls_section();
    }

	/**
	 * Declare the widget as dynamic so Elementor treats it as dynamic content (e.g. for caching).
	 */
	protected function is_dynamic_content(): bool {
		return true;
	}

    private function render_responsive_height($settings) {
        if(!isset(\Elementor\Plugin::$instance->breakpoints)) return;
        $breakpoints=\Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();
        uasort($breakpoints, function($a,$b){return $b->get_value() <=> $a->get_value();});
        $selector='.elementor-element-'.preg_replace('/[^a-zA-Z0-9_-]/', '', $this->get_id());
        $selector .= ' .open-user-map .box-wrap > .map-wrap, '.$selector.' .oum-editor-canvas';
        $format=function($value){$value=trim((string)$value);if(is_numeric($value))return $value.'px';return preg_match('/^\\d+(?:\\.\\d+)?(?:px|%|vh|vw|em|rem)$/i',$value)?strtolower($value):'';};
        $css='';
        // Desktop and mobile remain owned by the original shortcode height logic.
        // Only newly available intermediate breakpoints need additional scoped CSS.
        foreach($breakpoints as $key=>$point) {
            if($key==='mobile') continue;
            $value=isset($settings['oum_map_height_'.$key]) ? $format($settings['oum_map_height_'.$key]) : '';
            if($value==='')continue;
            $css.='@media('.($point->get_direction()==='min'?'min':'max').'-width:'.(float)$point->get_value().'px){'.$selector.'{height:'.$value.' !important;padding:0 !important;aspect-ratio:auto !important;}}';
        }
        if($css==='')return;
        $mobile=isset($settings['oum_map_height_mobile']) && (string)$settings['oum_map_height_mobile']!=='' ? $settings['oum_map_height_mobile'] : get_option('oum_map_height_mobile','');
        $mobile=$format($mobile);
        if($mobile!=='' && isset($breakpoints['mobile'])) $css.='@media(max-width:'.(float)$breakpoints['mobile']->get_value().'px){'.$selector.'{height:'.$mobile.' !important;padding:0 !important;aspect-ratio:auto !important;}}';
        echo '<style>'.$css.'</style>';
    }

    private function render_native_radius($settings) {
        $selector='.elementor-element-'.preg_replace('/[^a-zA-Z0-9_-]/', '', $this->get_id());
        $selector .= ' .map-wrap, '.$selector.' .oum-editor-canvas';
        $devices=[''=>null];
        if(isset(\Elementor\Plugin::$instance->breakpoints)) {
            $breakpoints=\Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();
            uasort($breakpoints, function($a,$b){return $b->get_value() <=> $a->get_value();});
            foreach($breakpoints as $key=>$point) $devices['_'.$key]=$point;
        }
        $css='';
        foreach($devices as $suffix=>$point) {
            $value=isset($settings['_border_radius'.$suffix]) ? $settings['_border_radius'.$suffix] : null;
            if(!is_array($value)) continue;
            $parts=[];$has_value=false;
            $unit=isset($value['unit']) && in_array($value['unit'],['px','%','em','rem','vh','vw'],true) ? $value['unit'] : 'px';
            foreach(['top','right','bottom','left'] as $corner) {
                $number=isset($value[$corner]) && is_numeric($value[$corner]) ? max(0,(float)$value[$corner]) : 0;
                if(isset($value[$corner]) && (string)$value[$corner] !== '') $has_value=true;
                $parts[]=$number.$unit;
            }
            if(!$has_value) continue;
            $rule=$selector.'{border-radius:'.implode(' ',$parts).';overflow:hidden;}';
            if($point) $rule='@media('.($point->get_direction()==='min'?'min':'max').'-width:'.(float)$point->get_value().'px){'.$rule.'}';
            $css.=$rule;
        }
        if($css!=='') echo '<style>'.$css.'</style>';
    }

    protected function render() {
        $settings=$this->get_settings_for_display();
        $attributes = \OpenUserMapPlugin\Base\EditorSettings::elementor_attributes($settings);
        $sizing=\OpenUserMapPlugin\Base\ElementorSizing::build($settings,$this->get_id(),\Elementor\Plugin::$instance->breakpoints->get_active_breakpoints());
        if(!$sizing['height'])$this->render_responsive_height($settings);
        if($sizing['css']!==''){
            echo '<style>'.$sizing['css'].'</style><div class="oum-elementor-sizing'.($sizing['height']?' has-oum-dimensions':'').($sizing['width']?' has-oum-width':'').'">';
        }
        $radius=!empty($settings['oum_radius_native']);
        if($radius) $this->render_native_radius($settings);
        if (Plugin::is_elementor_backend()) {
            echo '<div data-oum-editor-settings="'.esc_attr(wp_json_encode($attributes)).'"></div>';
        } else {
            echo do_shortcode(\OpenUserMapPlugin\Base\EditorSettings::shortcode($attributes));
        }
        if($sizing['css']!=='')echo '</div>';
    }
}
