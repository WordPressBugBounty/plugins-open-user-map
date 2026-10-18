(function ($) {
    'use strict';
    function registerControls() {
        // Add the opt-in to the same native command/history transaction as the radius edit.
        // Previously saved (ineffective) radii stay untouched until deliberately edited.
        $e.commands.on('run:before', function (component,command,args) {
            if (command !== 'document/elements/settings' || !args.settings) return;
            const containers=args.containers || [args.container];
            containers.filter(Boolean).forEach(function(container) {
                if (container.model.get('widgetType') !== 'open_user_map_widget') return;
                if (!args.isMultiSettings && containers.some(function(item){return item.model.get('widgetType') !== 'open_user_map_widget';})) return;
                const settings=args.isMultiSettings ? args.settings[container.id] : args.settings;
                if (settings && Object.keys(settings).some(function(key){return /^_border_radius(?:_|$)/.test(key);})) settings.oum_radius_native='yes';
                if(settings) {
                    settings.oum_editor_version=1;
                    if (settings.region) Object.assign(settings,{latitude:'',longitude:'',zoom:''});
                }
            });
        });
        let summaryModel, summaryChanged, summaryObserver;
        elementor.hooks.addAction('panel/open_editor/widget',function(panel,model){
            if(summaryModel)summaryModel.off('change',summaryChanged);
            if(summaryObserver)summaryObserver.disconnect();
            summaryModel=null;
            if(model.get('widgetType')!=='open_user_map_widget')return;
            summaryModel=model.get('settings');
            summaryChanged=function(){
                const settings=summaryModel.toJSON();let filters={};
                try{filters=JSON.parse(settings.oum_filters || '{}');}catch(_){}
                const heading=panel.el.querySelector('.elementor-control-section_custom_locations .elementor-panel-heading-title');
                const title=window.OUMEditorFilters.title(Object.assign({},settings,filters));
                if(heading && heading.textContent!==title)heading.textContent=title;
            };
            summaryModel.on('change',summaryChanged);
            // Elementor rebuilds section headings when sections or tabs change.
            summaryObserver=new MutationObserver(summaryChanged);
            summaryObserver.observe(panel.el,{childList:true,subtree:true});
            summaryChanged();
        });
        const Picker = elementor.modules.controls.BaseData.extend({
            // These inputs are owned by the custom picker, not Elementor's raw-value handler.
            events:function(){return {};},
            applySavedValue: function () {
                elementor.modules.controls.BaseData.prototype.applySavedValue.apply(this, arguments);
                this.syncPicker();
            },
            syncPicker: function () {
                const select = this.el.querySelector('.oum-location-picker');
                if (!select) return;
                const values = String(this.getControlValue() || '').split('|').filter(Boolean);
                Array.from(select.options).forEach(function (option) { option.selected = values.includes(option.value); });
                // Refresh only Select2: a model-driven update must not create another history entry.
                if (this.$picker) this.$picker.trigger('change.select2');
            },
            onReady: function () {
                const select = this.el.querySelector('.oum-location-picker');
                const values = String(this.getControlValue() || '').split('|').filter(Boolean);
                if(!select.options.length && !values.length){this.el.style.display='none';return;}
                values.forEach(function (value) {
                    if (!Array.from(select.options).some(function (option) {return option.value === value;})) {
                        const option = document.createElement('option'); option.value = value; option.textContent = value; select.append(option);
                    }
                });
                Array.from(select.options).forEach(function (option) {option.selected = values.includes(option.value);});
                // Elementor ships Select2: searchable suggestions and removable selections.
                this.$picker=$(select).select2({width:'100%',placeholder:window.oumEditorControls.search,allowClear:true,closeOnSelect:false});
                this.listenTo(this.container.settings,'change:'+this.model.get('name'),this.syncPicker);
                this.$picker.on('change.oum',()=>this.setValue(Array.from(select.selectedOptions).map(function(option){return option.value;}).join('|')));
            },
            onBeforeDestroy:function(){if(this.$picker){this.$picker.off('.oum');this.$picker.select2('destroy');}}
        });
        elementor.addControlView('oum_locations',Picker);
        const Filters=elementor.modules.controls.BaseData.extend({
            events:function(){return {};},
            onReady:function(){
                const node=this.el.querySelector('.oum-shared-filters');
                this.reactRoot=wp.element.createRoot ? wp.element.createRoot(node) : {
                    render:child=>wp.element.render(child,node),
                    unmount:()=>wp.element.unmountComponentAtNode(node)
                };
                this.renderFilters=()=>{
                    const settings=this.container.settings.toJSON();let values={};
                    try{values=JSON.parse(this.getControlValue() || '{}');}catch(_){}
                    const attributes=Object.assign({},settings,values);
                    this.reactRoot.render(wp.element.createElement(window.OUMEditorFilters.Component,{attributes:attributes,config:window.oumFilterConfig,onChange:changes=>{
                        const filters=Object.assign({},values);const settings={};
                        Object.keys(changes).forEach(key=>{if(key==='types' || key==='ids')settings[key]=changes[key];else filters[key]=changes[key];});
                        settings.oum_filters=JSON.stringify(filters);
                        $e.run('document/elements/settings',{container:this.container,settings:settings});
                    }}));
                };
                this.listenTo(this.container.settings,'change',this.renderFilters);this.renderFilters();
            },
            onBeforeDestroy:function(){if(this.reactRoot)this.reactRoot.unmount();}
        });
        elementor.addControlView('oum_filters',Filters);
        const Region=elementor.modules.controls.BaseData.extend({events:function(){return {};},
            applySavedValue:function(){
                elementor.modules.controls.BaseData.prototype.applySavedValue.apply(this,arguments);
                const select=this.el.querySelector('.oum-region-picker');
                if(select)select.value=String(this.getControlValue() || '');
            },onReady:function(){
            const select=this.el.querySelector('.oum-region-picker');const value=String(this.getControlValue() || '');
            if(select.options.length===1 && !value){this.el.style.display='none';return;}
            if(value && !Array.from(select.options).some(function(option){return option.value===value;})){const option=document.createElement('option');option.value=value;option.textContent=value;select.append(option);}
            select.value=value;
            this.listenTo(this.container.settings,'change:'+this.model.get('name'),()=>{select.value=String(this.getControlValue() || '');});
            select.addEventListener('change',()=>this.setValue(select.value));
        }});
        elementor.addControlView('oum_region',Region);
    }
    if(window.elementor && elementor.modules && elementor.modules.controls && window.$e && $e.commands) registerControls();
    else $(window).on('elementor/init',registerControls);
})(jQuery);
