<?php
namespace OpenUserMapPlugin\Base;

/** Opt-in Elementor dimensions; device order and boundaries come from Elementor. */
class ElementorSizing {
    private static function length($value){
        if(is_array($value)){
            $unit=isset($value['unit']) && in_array($value['unit'],array('px','%','em','rem','vh','vw','svh','lvh','dvh','vmin','vmax'),true)?$value['unit']:'px';
            $value=isset($value['size']) && is_scalar($value['size']) && (string)$value['size']!=='' ? $value['size'].$unit : '';
        }
        if(!is_scalar($value))return '';
        $value=trim((string)$value);if($value!=='' && is_numeric($value))$value.='px';
        return preg_match('/^(?:\d+(?:\.\d+)?|\.\d+)(?:px|%|em|rem|vh|vw|svh|lvh|dvh|vmin|vmax)$/D',$value)?$value:'';
    }
    private static function ratio($value){
        if(!is_scalar($value) || !preg_match('/^\d+(?:\.\d+)?(?:\s*\/\s*\d+(?:\.\d+)?)?$/D',(string)$value))return '';
        foreach(explode('/',(string)$value)as $part)if((float)$part<=0)return '';
        return (string)$value;
    }
    private static function geometry($settings,$suffix){
        $mode=isset($settings['oum_height_mode'.$suffix])?$settings['oum_height_mode'.$suffix]:'';
        $height=isset($settings['oum_height_value'.$suffix])?self::length($settings['oum_height_value'.$suffix]):'';
        $legacy=isset($settings['oum_map_height'.$suffix])?self::length($settings['oum_map_height'.$suffix]):'';
        if($mode==='ratio'){
            $ratio=isset($settings['oum_ratio'.$suffix])?$settings['oum_ratio'.$suffix]:'';
            if($ratio==='custom')$ratio=isset($settings['oum_ratio_custom'.$suffix])?$settings['oum_ratio_custom'.$suffix]:'';
            return array('height'=>'auto','ratio'=>self::ratio($ratio)?:'16/9');
        }
        if($mode==='height')return $height!=='' ? array('height'=>$height,'ratio'=>'1.77') : null;
        if($height!=='')return array('height'=>$height,'ratio'=>'1.77');
        return $legacy!=='' ? array('height'=>$legacy,'ratio'=>'1.77') : null;
    }
    private static function width($value){return is_string($value) && in_array($value,array('default','fullwidth'),true);}
    private static function rule($selector,$geometry,$width){
        $css='';
        if($geometry)$css.='--oum-block-height:'.$geometry['height'].';--oum-block-ratio:'.$geometry['ratio'].';';
        if(self::width($width))$css.='--oum-block-width:'.($width==='fullwidth'?'100vw':'100%').';--oum-block-offset:'.($width==='fullwidth'?'50%':'0px').';--oum-block-translate:'.($width==='fullwidth'?'-50%':'0px').';';
        return $css!==''?$selector.'{'.$css.'}':'';
    }
    public static function build($settings,$id,$points){
        $height=false;$width=false;
        foreach($settings as $key=>$value){
            if(preg_match('/^oum_height_mode(?:_|$)/',$key) && in_array($value,array('height','ratio'),true))$height=true;
            if(preg_match('/^oum_height_value(?:_|$)/',$key) && self::length($value)!=='')$height=true;
            if(strpos($key,'oum_map_size_')===0 && self::width($value))$width=true;
        }
        if(!$height && !$width)return array('css'=>'','height'=>false,'width'=>false);
        $selector='.elementor-element-'.preg_replace('/[^a-zA-Z0-9_-]/','',(string)$id).' .oum-elementor-sizing';
        $base=$height?self::geometry($settings,''):null;
        if($height && !$base){$value=self::length(get_option('oum_map_height',''));$base=array('height'=>$value!==''?$value:'auto','ratio'=>'1.77');}
        $size=isset($settings['oum_map_size']) && self::width($settings['oum_map_size'])?$settings['oum_map_size']:get_option('oum_map_size','default');
        $css=self::rule($selector,$base,$width?$size:null);
        uasort($points,function($a,$b){
            if($a->get_direction()!==$b->get_direction())return $a->get_direction()==='max'?-1:1;
            return $a->get_direction()==='max'?$b->get_value()<=>$a->get_value():$a->get_value()<=>$b->get_value();
        });
        foreach($points as $key=>$point){
            $suffix='_'.$key;$geometry=$height?self::geometry($settings,$suffix):null;
            $size=$width && isset($settings['oum_map_size'.$suffix])?$settings['oum_map_size'.$suffix]:null;
            $rule=self::rule($selector,$geometry,$size);
            if($rule!=='')$css.='@media('.($point->get_direction()==='min'?'min':'max').'-width:'.(float)$point->get_value().'px){'.$rule.'}';
        }
        return array('css'=>$css,'height'=>$height,'width'=>$width);
    }
}
