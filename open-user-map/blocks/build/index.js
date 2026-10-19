/* No JSX or bundler dependencies. Built by build-editor.js. */
(function (wp) {
    'use strict';
    const h = wp.element.createElement, Fragment = wp.element.Fragment;
    const {useRef,useEffect,useState} = wp.element;
    const {InspectorControls} = wp.blockEditor;
    const useBlockProps = wp.blockEditor.useBlockProps || function(props){return props;};
    const {PanelBody,TextControl,SelectControl,Button,FormTokenField} = wp.components;
    const __ = function (label) {return wp.i18n.__(label,'open-user-map');};
    const ratioOptions = [
        {value:'16/9',label:'16:9'}, {value:'4/3',label:'4:3'}, {value:'1/1',label:'1:1'},
        {value:'3/4',label:'3:4'}, {value:'9/16',label:'9:16'}, {value:'custom',label:__('Custom')}
    ];
    function cssHeight(value) {
        const text=String(value == null ? '' : value).trim();
        return /^\d+(?:\.\d+)?$/.test(text) ? text+'px' : text;
    }
    function validHeight(value) {return /^(?:\d+(?:\.\d+)?|\.\d+)(?:px|em|rem|vh|vw|vmin|vmax|svh|lvh|dvh|%)$/.test(cssHeight(value));}
    function validRatio(value) {
        return /^\d+(?:\.\d+)?(?:\s*\/\s*\d+(?:\.\d+)?)?$/.test(String(value)) && String(value).split('/').every(part=>Number(part)>0);
    }
    function geometry(style) {
        const dimensions=style && style.dimensions || {};
        if (validRatio(dimensions.aspectRatio)) return {height:'auto',ratio:dimensions.aspectRatio};
        const height=dimensions.height || dimensions.minHeight;
        return validHeight(height) ? {height:cssHeight(height),ratio:'1.77'} : null;
    }
    function sizeCSS(a,selector,globals) {
        const queries=window.oumEditor.heightControls.mediaQueries;
        const base=geometry(a.style);
        const nativeBase=a.style && a.style.dimensions && (validHeight(a.style.dimensions.height) || validRatio(a.style.dimensions.aspectRatio));
        const states=Object.keys(queries).map(state=>({query:queries[state],value:geometry(a.style && a.style[state])})).filter(state=>state.value);
        const legacyRatio=validRatio(a.aspect_ratio_mobile) ? a.aspect_ratio_mobile : '';
        if (!nativeBase && !states.length && !legacyRatio) return '';
        const inherited=cssHeight(a.height || globals.height);
        const initial=base || {height:validHeight(inherited) ? inherited : 'auto',ratio:'1.77'};
        function rule(value) {return selector+'{--oum-block-height:'+value.height+';--oum-block-ratio:'+value.ratio+';}';}
        let css=rule(initial);
        const mobile=cssHeight(a.height_mobile || (!nativeBase ? globals.height_mobile : ''));
        if(legacyRatio || validHeight(mobile)) css+='@media (max-width:767px){'+rule(legacyRatio ? {height:'auto',ratio:legacyRatio} : {height:mobile,ratio:'1.77'})+'}';
        return css+states.map(state=>state.query+'{'+rule(state.value)+'}').join('');
    }
    function inlineSizing(a,device,width,globals) {
        const nativeBase=a.style && a.style.dimensions && (validHeight(a.style.dimensions.height) || validRatio(a.style.dimensions.aspectRatio));
        const inherited=cssHeight(a.height || globals.height);
        let value=geometry(a.style) || {height:validHeight(inherited) ? inherited : 'auto',ratio:'1.77'};
        const mobile=cssHeight(a.height_mobile || (!nativeBase ? globals.height_mobile : ''));
        if(width<=767){
            if(validRatio(a.aspect_ratio_mobile))value={height:'auto',ratio:a.aspect_ratio_mobile};
            else if(validHeight(mobile))value={height:mobile,ratio:'1.77'};
        }
        const state=device==='mobile' ? '@mobile' : device==='tablet' ? '@tablet' : '';
        if(state)value=geometry(a.style && a.style[state]) || value;
        let size=a.size || globals.size;
        if(a.width_responsive && state)size=a[widthKey(device)] || size;
        else if(width<=767)size=a.size_mobile || size;
        if(device==='tablet')size=a.size_tablet || size;
        return {'--oum-block-height':value.height,'--oum-block-ratio':value.ratio,'--oum-block-width':size==='fullwidth' ? width+'px' : '100%','--oum-block-offset':size==='fullwidth' ? '50%' : '0px','--oum-block-translate':size==='fullwidth' ? '-50%' : '0px'};
    }
    function deviceIndicator(device,nativeControls,responsiveEditing) {
        if(nativeControls && !responsiveEditing)return {device:'all',tooltip:__('All devices (base)')};
        return {device:device,tooltip:device==='mobile' ? __('Mobile') : device==='tablet' ? __('Tablet') : __('Desktop (base)')};
    }
    function DeviceLabel({label,device,nativeControls,responsiveEditing}) {
        const scope=deviceIndicator(device,nativeControls,responsiveEditing);
        // Match the WordPress device symbols. The combined symbol denotes shared base values.
        const paths={
            desktop:'M20.5 16h-.7V8c0-1.1-.9-2-2-2H6.2c-1.1 0-2 .9-2 2v8h-.7c-.8 0-1.5.7-1.5 1.5h20c0-.8-.7-1.5-1.5-1.5zM5.7 8c0-.3.2-.5.5-.5h11.6c.3 0 .5.2.5.5v7.6H5.7V8z',
            tablet:'M17 4H7c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm.5 14c0 .3-.2.5-.5.5H7c-.3 0-.5-.2-.5-.5V6c0-.3.2-.5.5-.5h10c.3 0 .5.2.5.5v12zm-7.5-.5h4V16h-4v1.5z',
            mobile:'M15 4H9c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h6c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm.5 14c0 .3-.2.5-.5.5H9c-.3 0-.5-.2-.5-.5V6c0-.3.2-.5.5-.5h6c.3 0 .5.2.5.5v12zm-4.5-.5h2V16h-2v1.5z',
            all:'M3 4h13a2 2 0 0 1 2 2v3h-1.5V6a.5.5 0 0 0-.5-.5H3a.5.5 0 0 0-.5.5v9h9v1.5H10V19h2v1.5H5V19h3.5v-2.5H3a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm12 6h6a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2zm0 1.5a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h6a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5z'
        };
        const indicator=h('span',{className:'oum-device-indicator',tabIndex:0,role:'img','aria-label':scope.tooltip,'data-device':scope.device,title:wp.components.Tooltip ? undefined : scope.tooltip},h('svg',{width:16,height:16,viewBox:'0 0 24 24',fill:'currentColor','aria-hidden':true,focusable:false},h('path',{d:paths[scope.device] || paths.desktop})));
        return h('span',{className:'oum-device-label'},__(label),' ',wp.components.Tooltip ? h(wp.components.Tooltip,{text:scope.tooltip},indicator) : indicator);
    }
    function SizeControls({attributes:a,setAttributes,device,nativeControls,responsiveEditing}) {
        const mobile=device==='mobile',state=device==='tablet' ? '@tablet' : device==='mobile' ? '@mobile' : '';
        const fallback=mobile && !geometry(a.style && a.style['@mobile']);
        const dimensions=(state && !fallback ? a.style && a.style[state] : a.style) && (state && !fallback ? a.style[state].dimensions : a.style.dimensions) || {};
        const localDevice=dimensions.height || dimensions.minHeight || dimensions.aspectRatio;
        const ratio=fallback ? a.aspect_ratio_mobile : dimensions.aspectRatio || (mobile && !localDevice ? a.aspect_ratio_mobile : undefined);
        const height=fallback ? a.height_mobile : dimensions.height || dimensions.minHeight || (!state ? a.height : mobile && !localDevice ? a.height_mobile : '');
        const mode=ratio !== undefined && ratio !== '' ? 'ratio' : height ? 'height' : 'auto';
        const globalHeight=mobile ? window.oumEditor.globals.height_mobile || window.oumEditor.globals.height : window.oumEditor.globals.height;
        function change(nextMode,value) {
            const changes={editorVersion:1};
            // New device edits use native states; legacy mobile values remain as the inherited fallback.
            {
                const style=Object.assign({},a.style),branch=Object.assign({},state ? style[state] : style),next=Object.assign({},branch.dimensions);
                delete next.height;delete next.minHeight;delete next.aspectRatio;
                if(nextMode==='height') next.height=value;
                if(nextMode==='ratio') next.aspectRatio=value;
                if(Object.keys(next).length)branch.dimensions=next;else delete branch.dimensions;
                if(state){if(Object.keys(branch).length)style[state]=branch;else delete style[state];}
                else {if(branch.dimensions)style.dimensions=branch.dimensions;else delete style.dimensions;changes.height='';}
                changes.style=style;
            }
            setAttributes(changes);
        }
        const UnitControl=wp.components.__experimentalUnitControl;
        const heightInput={label:window.oumEditor.heightControls.stylesGroup ? h(DeviceLabel,{label:'Fixed height',device:device,nativeControls:nativeControls,responsiveEditing:responsiveEditing}) : __('Fixed height'),value:cssHeight(height),onChange:value=>change(value ? 'height' : 'auto',String(value || ''))};
        if(UnitControl) Object.assign(heightInput,{units:['px','%','em','rem','vw','vh','vmin','vmax','svh','lvh','dvh'].map(unit=>({value:unit,label:unit,default:unit==='px' ? 400 : 50})),min:0});
        const ratioChoice=ratioOptions.some(option=>option.value===ratio) ? ratio : 'custom';
        return h('div',{className:'oum-size-controls'},
            h(SelectControl,{label:h(DeviceLabel,{label:'Height',device:device,nativeControls:nativeControls,responsiveEditing:responsiveEditing}),value:mode,options:[{value:'auto',label:__('Automatic')},{value:'ratio',label:__('Aspect ratio')},{value:'height',label:__('Fixed height')}],onChange:function(value){change(value,value==='ratio' ? '16/9' : cssHeight(height || globalHeight || '400px'));}}),
            mode==='ratio' && h(SelectControl,{label:__('Aspect ratio'),value:ratioChoice,options:ratioOptions,onChange:value=>change('ratio',value==='custom' ? '1.77' : value)}),
            mode==='ratio' && ratioChoice==='custom' && h(TextControl,{label:__('Aspect ratio'),value:ratio,onChange:value=>change(value ? 'ratio' : 'auto',value),help:validRatio(ratio) ? undefined : __('Use 16/9.')}),
            mode==='height' && h(UnitControl || TextControl,heightInput),
            mode!=='auto' && h(Button,{variant:'secondary',title:__('Reset height'),'aria-label':__('Reset height'),onClick:()=>change('auto','')},__('Reset'))
        );
    }
    function WidthControl({attributes:a,setAttributes,device,nativeControls,responsiveEditing}) {
        const key=device==='mobile' ? 'size_mobile' : device==='tablet' ? 'size_tablet' : 'size';
        const inherited=device==='desktop' ? window.oumEditor.globals.size : a.size || window.oumEditor.globals.size;
        const label=inherited==='fullwidth' ? __('Screen width') : __('Column width');
        return h(SelectControl,{label:h(DeviceLabel,{label:'Width',device:device,nativeControls:nativeControls,responsiveEditing:responsiveEditing}),value:a[key] || '',options:[{value:'',label:__('Automatic')+' ('+label+')'},{value:'default',label:__('Column width')},{value:'fullwidth',label:__('Screen width')}],onChange:function(value){
            const changes={[key]:value,editorVersion:1};
            if(device!=='desktop')changes.width_responsive=true;
            setAttributes(changes);
        }});
    }
    function widthCSS(a,selector,globals) {
        if(!['default','fullwidth'].includes(a.size_mobile) && !['default','fullwidth'].includes(a.size_tablet))return '';
        function rule(value){return selector+'{--oum-block-width:'+(value==='fullwidth' ? '100vw' : '100%')+';--oum-block-offset:'+(value==='fullwidth' ? '50%' : '0px')+';--oum-block-translate:'+(value==='fullwidth' ? '-50%' : '0px')+';}';}
        const queries=window.oumEditor.heightControls.mediaQueries;
        let css=rule(a.size || globals.size);
        if(['default','fullwidth'].includes(a.size_mobile))css+=(a.width_responsive ? queries['@mobile'] : '@media (max-width:767px)')+'{'+rule(a.size_mobile)+'}';
        if(['default','fullwidth'].includes(a.size_tablet))css+=queries['@tablet']+'{'+rule(a.size_tablet)+'}';
        return css;
    }
    function sizingDevice(nativeControls, responsiveEditing, previewDevice) {
        return nativeControls && !responsiveEditing ? 'desktop' : previewDevice;
    }
    function useResponsiveEditing(enabled) {
        // WordPress currently keeps its editing-mode selector private. Observe the
        // native toolbar's language-independent state instead of unlocking private APIs.
        const selector='.editor-preview-dropdown';
        function read() {
            const control=enabled && document.querySelector(selector);
            return {available:!!control,active:!!control && control.classList.contains('is-responsive-editing')};
        }
        const [mode,setMode]=useState(read);
        useEffect(function(){
            function update(){const next=read();setMode(previous=>previous.available===next.available && previous.active===next.active ? previous : next);}
            update();
            if(!enabled || typeof MutationObserver==='undefined')return;
            const observer=new MutationObserver(function(records){
                if(records.some(function(record){
                    if(record.type==='attributes')return record.target.matches(selector);
                    return Array.from(record.addedNodes).concat(Array.from(record.removedNodes)).some(node=>node.nodeType===1 && (node.matches(selector) || node.querySelector(selector)));
                }))update();
            });
            observer.observe(document.body,{subtree:true,childList:true,attributes:true,attributeFilter:['class']});
            return ()=>observer.disconnect();
        },[enabled]);
        return mode;
    }
    function getPreviewDevice(select) {
        const editor=select('core/editor');
        if(editor && editor.getDeviceType)return editor.getDeviceType().toLowerCase();
        const post=select('core/edit-post');
        return post && post.__experimentalGetPreviewDeviceType ? post.__experimentalGetPreviewDeviceType().toLowerCase() : 'desktop';
    }
    function setPreviewDevice(name) {
        const device=name==='mobile' ? 'Mobile' : name==='tablet' ? 'Tablet' : 'Desktop';
        const editor=wp.data.dispatch('core/editor');
        if(editor && editor.setDeviceType){editor.setDeviceType(device);return;}
        const post=wp.data.dispatch('core/edit-post');
        if(post && post.__experimentalSetPreviewDeviceType)post.__experimentalSetPreviewDeviceType(device);
    }
    function SizeSettings(props) {
        function controls(device) {
            return h(Fragment,{key:device},
                h(props.item || Fragment,props.widthItem,h(WidthControl,Object.assign({},props,{device:device}))),
                h(props.item || Fragment,props.heightItem,h(SizeControls,Object.assign({},props,{device:device}))));
        }
        if(props.nativeControls)return controls(props.device);
        const tabs=[{name:'desktop',title:__('Desktop')},{name:'tablet',title:__('Tablet')},{name:'mobile',title:__('Mobile')}];
        return h(wp.components.TabPanel,{key:props.device,className:'oum-size-tabs',initialTabName:tabs.some(tab=>tab.name===props.device) ? props.device : 'desktop',onSelect:setPreviewDevice,tabs:tabs},tab=>controls(tab.name));
    }
    function widthKey(device) {
        return device==='mobile' ? 'size_mobile' : device==='tablet' ? 'size_tablet' : 'size';
    }
    function resetHeight(attributes,device) {
        const state=device==='mobile' ? '@mobile' : device==='tablet' ? '@tablet' : '';
        const style=Object.assign({},attributes.style),branch=Object.assign({},state ? style[state] : style);
        const dimensions=Object.assign({},branch.dimensions);
        delete dimensions.height;delete dimensions.minHeight;delete dimensions.aspectRatio;
        if(Object.keys(dimensions).length)branch.dimensions=dimensions;else delete branch.dimensions;
        if(state){if(Object.keys(branch).length)style[state]=branch;else delete style[state];}
        else {if(branch.dimensions)style.dimensions=branch.dimensions;else delete style.dimensions;}
        const changes={style:style};
        if(device==='mobile'){changes.height_mobile='';changes.aspect_ratio_mobile='';}
        else if(device!=='tablet')changes.height='';
        return changes;
    }
    function Edit({attributes:a,setAttributes,clientId}) {
        const root = useRef(null);
        const [inlineViewport,setInlineViewport]=useState(0);
        useEffect(function(){
            const node=root.current;
            if(!node || node.ownerDocument!==document)return;
            const canvas=node.closest('.editor-styles-wrapper') || node.parentElement;
            function measure(){setInlineViewport(canvas.getBoundingClientRect().width);}
            measure();
            if(typeof ResizeObserver==='undefined')return;
            const observer=new ResizeObserver(measure);observer.observe(canvas);
            return ()=>observer.disconnect();
        },[]);
        const editorState=wp.data.useSelect(function(select){
            const editor=select('core/editor');
            const settings=editor && editor.getEditorSettings ? editor.getEditorSettings() : {};
            return {device:getPreviewDevice(select),nativeResponsive:!!settings.responsiveEditingEnabled};
        },[]);
        const responsiveMode=useResponsiveEditing(editorState.nativeResponsive);
        const editDevice=sizingDevice(responsiveMode.available,responsiveMode.active,editorState.device);

        const globals = window.oumEditor.globals, choices = window.oumEditor.choices;
        const mapHeight = a.style && a.style.dimensions && (a.style.dimensions.height || a.style.dimensions.minHeight);
        const heightControls = window.oumEditor.heightControls;
        const responsiveCSS = sizeCSS(a,'.oum-height-'+clientId,globals);
        const responsiveWidthCSS=widthCSS(a,'.oum-width-'+clientId,globals);
        const preview = Object.assign({},a, mapHeight ? {height:mapHeight} : {});
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
        const ToolsPanelItem=heightControls.stylesGroup && wp.components.__experimentalToolsPanelItem;
        const widthItem=ToolsPanelItem ? {className:'oum-width-setting',label:__('Width'),panelId:clientId,isShownByDefault:true,hasValue:()=>!!a[widthKey(editDevice)],onDeselect:()=>setAttributes({[widthKey(editDevice)]:''}),resetAllFilter:()=>({[widthKey(editDevice)]:''})} : {};
        const heightItem=ToolsPanelItem ? {className:'oum-height-setting',label:__('Height'),panelId:clientId,isShownByDefault:true,hasValue:()=>!!(a.height || a.height_mobile || a.aspect_ratio_mobile || geometry(a.style) || geometry(a.style && a.style['@mobile']) || geometry(a.style && a.style['@tablet'])),onDeselect:()=>setAttributes(resetHeight(a,editDevice)),resetAllFilter:attributes=>resetHeight(attributes,editDevice)} : {};
        const popup = globals.hide_location_popup === 'true' ? __('Hidden') : __('Visible');
        const blockProps = useBlockProps({style:inlineViewport ? inlineSizing(a,editorState.device,inlineViewport,globals) : undefined,className:'oum-native-block oum-height-'+clientId+' oum-width-'+clientId+(responsiveWidthCSS || inlineViewport ? ' has-oum-width' : '')+(responsiveCSS || inlineViewport ? ' has-oum-dimensions' : '')+(a.style && a.style.border && a.style.border.radius ? ' has-oum-radius' : '')+(mapHeight ? ' has-oum-height' : '')});
        if(a.oumPreview)return h('div',blockProps,h('img',{src:window.oumEditor.previewImage,alt:__('Example map with locations and popup'),style:{display:'block',width:'100%',height:'auto'}}));
        return h(Fragment,null,
            h(InspectorControls,null,
                h(PanelBody,{title:__('Map view'),initialOpen:true},h('p',null,__('Choose "Edit view" on the map and then move and zoom the map')),h('p',null,__('Add markers in "Manage Locations" or directly on the map in the front end.')),h('p',null,h('a',{href:window.oumEditor.settingsUrl,target:'_blank',rel:'noopener'},__('Global settings')),' · ',h('a',{href:window.oumEditor.locationsUrl,target:'_blank',rel:'noopener'},__('Manage locations'))),choices.region.length || a.region ? picker('region','Region',false) : null),
                h(PanelBody,{title:window.OUMEditorFilters.title(a),initialOpen:false},choices.types.length || a.types ? picker('types','Categories',true) : null,picker('ids','Locations',true),h('p',null,__('Leave empty to show all.')),h(window.OUMEditorFilters.Component,{attributes:a,config:window.oumFilterConfig,onChange:changes=>setAttributes({...changes,editorVersion:1})})),
                h(PanelBody,{title:__('Popups'),initialOpen:false},h(SelectControl,{label:__('Popups'),value:a.hide_location_popup || '',options:[{value:'',label:popup+' ('+__('Global setting')+')'},{value:'false',label:__('Visible')},{value:'true',label:__('Hidden')}],onChange:function(value){setAttributes({hide_location_popup:value,editorVersion:1});}}))
            ),
            h(InspectorControls,heightControls.stylesGroup ? {group:'dimensions'} : null,
                h(heightControls.stylesGroup ? 'div' : PanelBody,heightControls.stylesGroup ? {className:'oum-height-controls'} : {title:__('Height'),initialOpen:false},
                    h(SizeSettings,{attributes:a,setAttributes:setAttributes,nativeControls:responsiveMode.available,responsiveEditing:responsiveMode.active,device:editDevice,item:ToolsPanelItem,widthItem:widthItem,heightItem:heightItem}))
            ),
            h('div',blockProps,responsiveCSS || responsiveWidthCSS ? h('style',null,responsiveCSS+responsiveWidthCSS) : null,h('div',{ref:root}))
        );
    }
    wp.blocks.registerBlockType('open-user-map/map',{icon:h('svg',{viewBox:'0 0 24 24',width:24,height:24,'aria-hidden':true,focusable:false},h('image',{href:window.oumEditor.iconImage,width:24,height:24})),example:{attributes:{oumPreview:true}},edit:Edit,save:function(){return null;}});
})(window.wp);
