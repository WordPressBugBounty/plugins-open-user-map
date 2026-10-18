<?php
namespace Elementor_OUM_Addon;

class OUM_Locations_Control extends \Elementor\Base_Data_Control {
    public function get_type() { return 'oum_locations'; }
    public function content_template() {
        ?>
        <div class="elementor-control-field">
            <label class="elementor-control-title" for="oum-picker-{{ data._cid }}">{{{ data.label }}}</label>
            <input type="hidden" data-setting="{{ data.name }}" />
            <select id="oum-picker-{{ data._cid }}" multiple class="oum-location-picker" style="width:100%" aria-label="{{ data.label }}">
                <# _.each(data.oum_choices, function(choice) { #>
                    <option value="{{ choice.value }}">{{ choice.label }}</option>
                <# }); #>
            </select>

        </div>
        <# if (data.description) { #><div class="elementor-control-field-description">{{{ data.description }}}</div><# } #>
        <?php
    }
}


class OUM_Region_Control extends \Elementor\Base_Data_Control {
    public function get_type() { return 'oum_region'; }
    public function content_template() {
        ?>
        <div class="elementor-control-field">
            <label class="elementor-control-title" for="oum-region-{{ data._cid }}">{{{ data.label }}}</label>
            <input type="hidden" data-setting="{{ data.name }}" />
            <select id="oum-region-{{ data._cid }}" class="oum-region-picker" aria-label="{{ data.label }}">
                <option value=""><?php esc_html_e('No region','open-user-map'); ?></option>
                <# _.each(data.oum_choices, function(choice) { #>
                    <option value="{{ choice.value }}">{{ choice.label }}</option>
                <# }); #>
            </select>
        </div>
        <?php
    }
}

class OUM_Filters_Control extends \Elementor\Base_Data_Control {
    public function get_type(){return 'oum_filters';}
    public function content_template(){ ?>
        <input type="hidden" data-setting="{{ data.name }}" />
        <div class="oum-shared-filters"></div>
    <?php }
}
