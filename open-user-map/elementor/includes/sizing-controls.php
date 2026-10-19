<?php
namespace Elementor_OUM_Addon;

/** Native Elementor values with read-only adapters for existing scalar heights. */
class OUM_Height_Mode_Control extends \Elementor\Control_Select {
    public function get_type(){return 'oum_height_mode';}
}

class OUM_Height_Value_Control extends \Elementor\Control_Slider {
    public function get_type(){return 'oum_height_value';}

    // Keep Elementor's unit handling and responsive values, with a number field only.
    public function content_template(){
        ?>
        <div class="elementor-control-field">
            <label for="<?php $this->print_control_uid(); ?>" class="elementor-control-title">{{{ data.label }}}</label>
            <?php $this->print_units_template(); ?>
            <div class="elementor-control-input-wrapper">
                <input id="<?php $this->print_control_uid(); ?>" type="number" min="1" step="any" placeholder="{{ view.getControlPlaceholder()?.size }}" data-setting="size" />
            </div>
        </div>
        <?php
    }
}
