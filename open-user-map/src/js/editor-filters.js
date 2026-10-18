/* Shared native WordPress controls for both builders. Stored values use existing shortcode keys. */
(function(window){
    'use strict';
    const wp=window.wp,h=wp.element.createElement,{useState,useEffect,useRef}=wp.element;
    const {SelectControl,TextControl,FormTokenField,ComboboxControl,Button}=wp.components;
    const UserControl=ComboboxControl || SelectControl;
    const __=label=>wp.i18n.__(label,'open-user-map');
    const keys=['types-relation','date-filter','date_filter','user','custom-fields-filter','custom-fields-filter-relation'];
    // Count restrictions, not individual selected locations or relation settings.
    function activeCount(attributes){
        return ['ids','types','date','user','fields'].filter(function(key){
            const value=key==='date' ? attributes['date-filter'] || attributes.date_filter : key==='fields' ? attributes['custom-fields-filter'] : attributes[key];
            return value !== undefined && value !== null && String(value).trim() !== '';
        }).length;
    }
    function title(attributes){
        const count=activeCount(attributes);
        return __('Filter')+(count ? ' · '+count : '');
    }
    function parseDate(raw){
        if(!raw)return {mode:'',first:'',second:''};
        if(/^\d{4}-\d{2}-\d{2}$/.test(raw))return {mode:'on',first:raw,second:''};
        const parts=raw.split(';'),date={mode:'',first:'',second:''};
        for(const part of parts){const m=/^(after|before):\s*(\d{4}-\d{2}-\d{2})$/i.exec(part.trim());if(!m)return {mode:'saved',first:'',second:''};if(m[1].toLowerCase()==='after'){if(date.first)return {mode:'saved',first:'',second:''};date.first=m[2];}else {if(date.second)return {mode:'saved',first:'',second:''};date.second=m[2];}}
        date.mode=date.first && date.second ? 'period' : date.first ? 'after' : 'before';if(date.mode==='before'){date.first=date.second;date.second='';}return date;
    }
    function parseFields(raw){
        return (raw || '').split(';').filter(Boolean).map(function(group){
            const parts=group.replace(/\\:/g,'\u0001').split(':');
            if(parts.length<2)return {label:'',values:[],relation:'OR',raw:group};
            const label=parts.shift().replace(/\u0001/g,':').trim();
            const relation=parts.length>1 && /^(AND|OR)$/i.test(parts[parts.length-1].trim()) ? parts.pop().trim().toUpperCase() : 'OR';
            return {label:label,values:parts.join(':').replace(/\u0001/g,':').split('|').map(v=>v.trim()),relation:relation};
        });
    }
    function serializeFields(rows){
        const escape=v=>String(v).replace(/:/g,'\\:');
        return rows.map(row=>row.raw || (row.label && row.values.length ? escape(row.label)+':'+row.values.map(escape).join('|')+':'+row.relation : '')).filter(Boolean).join(';');
    }
    function Filters({attributes:a,onChange,config}){
        const [date,setDate]=useState(()=>parseDate(a['date-filter'] || a.date_filter || ''));
        const [rows,setRows]=useState(()=>parseFields(a['custom-fields-filter']));
        const ownDate=useRef(null),ownFields=useRef(null);
        useEffect(()=>{if(ownDate.current!==a['date-filter'])setDate(parseDate(a['date-filter'] || a.date_filter || ''));ownDate.current=null;},[a['date-filter'],a.date_filter]);
        useEffect(()=>{if(ownFields.current!==a['custom-fields-filter'])setRows(parseFields(a['custom-fields-filter']));ownFields.current=null;},[a['custom-fields-filter']]);
        function changeDate(next){
            setDate(next);if(next.mode==='saved')return;
            let value='';
            if(next.first){if(next.mode==='on')value=next.first;else if(next.mode==='before')value='before:'+next.first;else if(next.mode)value='after:'+next.first;}
            if(next.mode==='period' && next.second)value+=(value ? ';' : '')+'before:'+next.second;
            ownDate.current=value;onChange({'date-filter':value,date_filter:''});
        }
        function changeRows(next){setRows(next);const value=serializeFields(next);ownFields.current=value;onChange({'custom-fields-filter':value});}
        const user=a.user || '',userMode=user==='current' ? 'current' : user.startsWith('role:') ? 'role' : user ? 'user' : '';
        const userOptions=config.users.slice();if(userMode==='user' && !userOptions.some(o=>o.value===user))userOptions.push({value:user,label:__('User')+' #'+user});
        const roleOptions=config.roles.slice();if(userMode==='role' && !roleOptions.some(o=>o.value===user.slice(5)))roleOptions.push({value:user.slice(5),label:user.slice(5)});
        const dateOptions=[{value:'',label:__('Any date')},{value:'on',label:__('On')},{value:'after',label:__('After')},{value:'before',label:__('Before')},{value:'period',label:__('Period')}];
        if(date.mode==='saved')dateOptions.push({value:'saved',label:__('Saved filter')});
        const proSaved=!!user || !!a['custom-fields-filter'];
        return h('div',{className:'oum-extra-filters'},
            a.types && h(SelectControl,{label:__('Match categories'),value:a['types-relation'] || 'OR',options:[{value:'OR',label:__('Any')},{value:'AND',label:__('All')}],onChange:v=>onChange({'types-relation':v})}),
            h(SelectControl,{label:__(config.dateType==='created' ? 'Created date' : 'Updated date'),value:date.mode,options:dateOptions,onChange:v=>changeDate({...date,mode:v})}),
            date.mode && date.mode!=='saved' && h(TextControl,{type:'date',label:__(date.mode==='period' ? 'After' : 'Date'),value:date.first,onChange:v=>changeDate({...date,first:v})}),
            date.mode==='period' && h(TextControl,{type:'date',label:__('Before'),value:date.second,onChange:v=>changeDate({...date,second:v})}),
            date.mode==='period' && date.first && date.second && date.first>=date.second && h('p',{role:'status'},__('Choose an end date after the start date.')),
            date.mode==='saved' && h('p',null,__('Saved filter is kept until you replace it.')),
            !config.pro && proSaved && h('p',null,__('These filters require PRO. Saved values are kept.')),
            config.pro && h(SelectControl,{label:__('Added by'),value:userMode,options:[{value:'',label:__('All users')},{value:'current',label:__('Current user')},{value:'user',label:__('User')},{value:'role',label:__('Role')}],onChange:v=>onChange({user:v==='current' ? 'current' : v==='role' ? 'role:'+((roleOptions[0] || {}).value || '') : v==='user' ? ((userOptions[0] || {}).value || '') : ''})}),
            config.pro && userMode==='current' && h('p',null,__('Preview shows your locations.')),
            config.pro && userMode==='user' && h(UserControl,{label:__('User'),value:user,options:userOptions,onChange:v=>onChange({user:v || ''})}),
            config.pro && userMode==='role' && h(SelectControl,{label:__('Role'),value:user.slice(5),options:roleOptions,onChange:v=>onChange({user:v ? 'role:'+v : ''})}),
            config.pro && (config.fields.length || rows.length) ? h('div',{className:'oum-field-filters'},
                h('strong',null,__('Custom fields')),
                rows.length>1 && h(SelectControl,{label:__('Match conditions'),value:a['custom-fields-filter-relation'] || 'AND',options:[{value:'AND',label:__('All')},{value:'OR',label:__('Any')}],onChange:v=>onChange({'custom-fields-filter-relation':v})}),
                rows.map(function(row,index){
                    const field=config.fields.find(f=>f.label===row.label);
                    const options=[{value:'',label:__('Choose field')}].concat(config.fields.map(f=>({value:f.label,label:f.label})));
                    if(row.label && !field)options.push({value:row.label,label:row.label});
                    const update=change=>changeRows(rows.map((r,i)=>i===index ? {...r,...change} : r));
                    return h('div',{className:'oum-filter-condition',key:index},
                        row.raw ? h('p',null,__('Saved filter is kept until you replace it.')) : h(wp.element.Fragment,null,
                            h(SelectControl,{label:__('Field'),value:row.label,options:options,onChange:v=>update({label:v,values:[],raw:undefined})}),
                            row.label && h(FormTokenField,{label:__('Values'),value:row.values,suggestions:field ? field.values : [],maxSuggestions:20,__experimentalShowHowTo:false,__experimentalValidateInput:v=>!!v.trim() && !/[;|]/.test(v),onChange:v=>update({values:v.map(x=>x.trim()).filter(Boolean)})}),
                            row.values.length>1 && h(SelectControl,{label:__('Match values'),value:row.relation,options:[{value:'OR',label:__('Any')},{value:'AND',label:__('All')}],onChange:v=>update({relation:v})})
                        ),h(Button,{variant:'tertiary',isDestructive:true,onClick:()=>changeRows(rows.filter((_,i)=>i!==index))},__('Remove')));
                }),
                h(Button,{variant:'secondary',onClick:()=>setRows(rows.concat({label:'',values:[],relation:'OR'}))},__('Add condition'))
            ) : null,
            Boolean(keys.concat(['types','ids']).some(k=>a[k]) || date.mode || rows.length) && h(Button,{variant:'secondary',title:__('Reset filters'),'aria-label':__('Reset filters'),onClick:()=>{setDate(parseDate(''));setRows([]);onChange(Object.fromEntries(keys.concat(['types','ids']).map(k=>[k,''])));}},__('Reset'))
        );
    }
    window.OUMEditorFilters={Component:Filters,activeCount:activeCount,title:title,keys:keys,parseDate:parseDate,parseFields:parseFields,serializeFields:serializeFields};
})(window);
