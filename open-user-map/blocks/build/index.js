/* No JSX or bundler dependencies. Built by build-editor.js. */
(function (wp) {
    'use strict';
    const h = wp.element.createElement, Fragment = wp.element.Fragment;
    const {useRef,useEffect} = wp.element;
    const {InspectorControls} = wp.blockEditor;
    const useBlockProps = wp.blockEditor.useBlockProps || function(props){return props;};
    const {PanelBody,TextControl,SelectControl,Button,FormTokenField} = wp.components;
    const __ = function (label) {return wp.i18n.__(label,'open-user-map');};
    function heightLabel(value) {
        return /^\d+(?:\.\d+)?$/.test(String(value)) ? value+' px' : value;
    }
    function Edit({attributes:a,setAttributes}) {
        const root = useRef(null);
        const nativeHeight = wp.blockEditor.useSettings ? wp.blockEditor.useSettings('dimensions.minHeight')[0] : (wp.blockEditor.useSetting ? wp.blockEditor.useSetting('dimensions.minHeight') : false);
        const globals = window.oumEditor.globals, choices = window.oumEditor.choices;
        const minHeight = a.style && a.style.dimensions && a.style.dimensions.minHeight;
        const preview = Object.assign({},a, minHeight ? {height:minHeight} : {});
        useEffect(function () {
            window.OUMEditorMap.mount(root.current,preview,setAttributes);
        },[JSON.stringify(preview)]);
        useEffect(function () {const node=root.current;return function () {window.OUMEditorMap.unmount(node);};},[]);
        function picker(key,label,multiple) {
            const values = multiple ? (a[key] || '').split('|').filter(Boolean) : a[key] || '';
            const options = choices[key].slice();
            (multiple ? values : [values]).filter(Boolean).forEach(function (value) {if (!options.some(function(option){return option.value === value;})) options.push({value:value,label:value});});
            if (!multiple) options.unshift({value:'',label:__('No region')});
            if (multiple) {
                // Include IDs in duplicate titles so each suggestion remains unambiguous.
                const counts={}; options.forEach(function(o){counts[o.label]=(counts[o.label]||0)+1;});
                const tokens=options.map(function(o){return {value:o.value,label:counts[o.label]>1 ? o.label+' ('+o.value+')' : o.label};});
                const names=values.map(function(v){return tokens.find(function(o){return o.value===v;}).label;});
                return h(FormTokenField,{label:__(label),value:names,suggestions:tokens.map(function(o){return o.label;}),maxSuggestions:20,__experimentalShowHowTo:false,__experimentalValidateInput:function(v){return tokens.some(function(o){return o.label===v;});},onChange:function(next){
                    setAttributes({[key]:next.map(function(v){return tokens.find(function(o){return o.label===v;});}).filter(Boolean).map(function(o){return o.value;}).join('|'),editorVersion:1});
                }});
            }
            return h(SelectControl,{label:__(label),multiple:multiple,value:values,options:options,onChange:function(value){
                const update = {[key]:multiple ? value.join('|') : value,editorVersion:1};
                if (key === 'region') Object.assign(update,{lat:'',long:'',zoom:''});
                setAttributes(update);
            }});
        }
        const size = globals.size === 'fullwidth' ? __('Screen width') : __('Column width');
        const popup = globals.hide_location_popup === 'true' ? __('Hidden') : __('Visible');
        const blockProps = useBlockProps({className:'oum-native-block'+(a.style && a.style.border && a.style.border.radius ? ' has-oum-radius' : '')+(minHeight ? ' has-oum-height' : '')});
        if(a.oumPreview)return h('div',blockProps,h('img',{src:window.oumEditor.previewImage,alt:__('Example map with locations and popup'),style:{display:'block',width:'100%',height:'auto'}}));
        return h(Fragment,null,
            h(InspectorControls,null,
                h(PanelBody,{title:__('Map view'),initialOpen:true},h('p',null,__('Choose "Edit view" on the map and then move and zoom the map')),h('p',null,__('Add markers in "Manage Locations" or directly on the map in the front end.')),h('p',null,h('a',{href:window.oumEditor.settingsUrl,target:'_blank',rel:'noopener'},__('Global settings')),' · ',h('a',{href:window.oumEditor.locationsUrl,target:'_blank',rel:'noopener'},__('Manage locations'))),choices.region.length || a.region ? picker('region','Region',false) : null),
                h(PanelBody,{title:window.OUMEditorFilters.title(a),initialOpen:false},choices.types.length || a.types ? picker('types','Categories',true) : null,picker('ids','Locations',true),h('p',null,__('Leave empty to show all.')),h(window.OUMEditorFilters.Component,{attributes:a,config:window.oumFilterConfig,onChange:changes=>setAttributes({...changes,editorVersion:1})})),
                h(PanelBody,{title:__('Popups'),initialOpen:false},h(SelectControl,{label:__('Popups'),value:a.hide_location_popup || '',options:[{value:'',label:popup+' ('+__('Global setting')+')'},{value:'false',label:__('Visible')},{value:'true',label:__('Hidden')}],onChange:function(value){setAttributes({hide_location_popup:value,editorVersion:1});}})),
                h(PanelBody,{title:__('Size'),initialOpen:false},h(SelectControl,{label:__('Width'),value:a.size || '',options:[{value:'',label:size+' ('+__('Global setting')+')'},{value:'default',label:__('Column width')},{value:'fullwidth',label:__('Screen width')}],onChange:function(value){setAttributes({size:value,editorVersion:1});}}),
                    // Keep the inherited mobile height visible alongside native dimensions.
                    h('p',null,__('Height')+': '+heightLabel(minHeight || a.height || globals.height || __('Automatic'))),
                    (a.height_mobile || globals.height_mobile) && h('p',null,__('Mobile height')+': '+heightLabel(a.height_mobile || globals.height_mobile)),
                    nativeHeight ? h('p',null,__('Use Dimensions to change the height.')) : h(TextControl,{label:__('Height'),value:minHeight || a.height || '',placeholder:globals.height,onChange:function(value){const style=Object.assign({},a.style);style.dimensions=Object.assign({},style.dimensions);delete style.dimensions.minHeight;setAttributes({height:value,style:style,editorVersion:1});}}),
                    (minHeight || a.height || a.height_mobile) && h(Button,{variant:'secondary',title:__('Reset height'),'aria-label':__('Reset height'),onClick:function(){
                        const style=Object.assign({},a.style);style.dimensions=Object.assign({},style.dimensions);delete style.dimensions.minHeight;
                        setAttributes({style:style,height:'',height_mobile:'',editorVersion:1});
                    }},__('Reset')))
            ),
            h('div',blockProps,h('div',{ref:root}))
        );
    }
    wp.blocks.registerBlockType('open-user-map/map',{icon:h('svg',{viewBox:'0 0 24 24',width:24,height:24,'aria-hidden':true,focusable:false},h('image',{href:window.oumEditor.iconImage,width:24,height:24})),example:{attributes:{oumPreview:true}},edit:Edit,save:function(){return null;}});
})(window.wp);
