/* Shared, isolated editor map. Does not load visitor handlers or custom JavaScript. */
(function (window) {
    'use strict';
    const instances = new Map();
    const interactions = new Map();
    const frameRuntimes = new WeakMap();
    const bridges = new WeakMap();
    function frameRuntime(document) {
        if (frameRuntimes.has(document)) return frameRuntimes.get(document);
        // Leaflet's drag handler binds to its own document. The libraries must run
        // in Gutenberg's canvas window, rather than using the parent window's L.
        const target=document.defaultView;
        target.oumEditor=window.oumEditor;
        target.oum_map_style_registry=window.oum_map_style_registry;
        const ready=window.oumEditor.runtimeScripts.reduce(function(chain,url) {
            return chain.then(function(){return new Promise(function(resolve,reject){
                const script=document.createElement('script');script.src=url;script.onload=resolve;script.onerror=reject;document.head.append(script);
            });});
        },Promise.resolve()).then(function(){return target.OUMEditorMap;});
        frameRuntimes.set(document,ready);
        ready.catch(function(){frameRuntimes.delete(document);});
        return ready;
    }
    const labels = window.oumEditor && window.oumEditor.labels || {
        edit: 'Edit view', done: 'Done', reset: 'Reset', loading: 'Loading map…', error: 'Map could not be loaded.', retry: 'Retry'
    };
    function button(document, text, action) {
        const el = document.createElement('button'); el.type = 'button'; el.textContent = text;
        el.addEventListener('click', function (event) { event.stopPropagation(); action(); }); return el;
    }
    function addBaseLayer(map, config) {
        const L = window.L;
        const image = config.customImage;
        return window.OUMMapStyles.addBaseLayer(map, config.style, {
            mapboxKey:config.mapboxKey,cartoKey:config.cartoKey,
            customImageHideTiles:!!image.hideTiles,customImageBackgroundColor:image.background,
            setupCustomImageLayer:function () {
                const b = image.bounds;
                if (image.url && b && ['north','south','east','west'].every(function (k) {return b[k] !== '' && Number.isFinite(Number(b[k]));})) L.imageOverlay(image.url, [[b.north,b.west],[b.south,b.east]]).addTo(map);
            }
        });
    }
    const filterKeys = ['ids','types','types-relation','date-filter','date_filter','user','custom-fields-filter','custom-fields-filter-relation'];
    function settingsLink(document) {
        return actionLink(document, labels.globalSettings || 'Global settings', window.oumEditor.settingsUrl);
    }
    function actionLink(document, text, url) {
        const link=document.createElement('a');link.textContent=text;link.href=url;
        link.target='_blank';link.rel='noopener noreferrer';link.draggable=false;
        link.addEventListener('click',function(event){event.stopPropagation();});return link;
    }
    function showNotice(node, text, action) {
        const root=node.closest('.oum-editor-map');if(root)root.classList.add('has-map-notice');
        node.replaceChildren();
        const message=node.ownerDocument.createElement('span');message.textContent=text;
        node.append(message);if(action)node.append(action);
    }
    function showPreviewError(node, error, retry) {
        if(error.status===403 || error.status===401) {
            showNotice(node,labels.sessionExpired || 'Session expired.',button(node.ownerDocument,labels.reloadEditor || 'Reload editor',function(){(window.parent || window).location.reload();}));
        } else if(error.mapStyle) {
            showNotice(node,labels.checkStyle || 'Check map style.',settingsLink(node.ownerDocument));
        } else {
            showNotice(node,error.previewConnection ? (labels.connectionError || 'Connection lost.') : labels.error,button(node.ownerDocument,labels.retry,retry));
        }
    }
    function showEmptyState(node, config) {
        if(config.markers.length)return;
        const document=node.ownerDocument;
        if(config.emptyState==='filtered')return;
        if(config.emptyState==='empty') {
            showNotice(node,labels.empty || 'No locations yet.',window.oumEditor.addLocationUrl ? actionLink(document,labels.addLocation || 'Add location',window.oumEditor.addLocationUrl) : null);
        } else {
            showNotice(node,labels.unavailable || 'No visible locations.',settingsLink(document));
        }
    }
    function observeBaseLayer(layer, state, node, retry, empty) {
        if(!layer)return;
        const failed=function(){if(!state.disposed){empty.replaceChildren();showNotice(node,labels.tilesError || 'Map tiles could not load.',button(node.ownerDocument,labels.retry,retry));}};
        // Raster tiles and the vector renderer report failures through different APIs.
        if(layer.on)layer.on('tileerror',failed);
        const vector=layer.getMaplibreMap && layer.getMaplibreMap();
        if(vector && vector.on)vector.on('error',failed);
        return function(){if(layer.off)layer.off('tileerror',failed);if(vector && vector.off)vector.off('error',failed);};
    }
    function bindLocationPopup(marker, item, config, state, controller, document) {
        if (!config.hidePopup) {
            const content=document.createElement('div');content.textContent=labels.loading;
            marker.bindPopup(content,{minWidth:250,maxWidth:250});
            let loaded=false;
            marker.on('popupopen',function(){
                if(loaded)return;
                fetch(window.oumEditor.url,{method:'POST',credentials:'same-origin',signal:controller.signal,body:new URLSearchParams({action:'oum_get_location_bubble',post_id:String(item.post_id)})})
                .then(function(r){if(!r.ok)throw new Error(r.status);return r.json();})
                .then(function(r){
                    if(state.disposed)return;if(!r.success || !r.data.html)throw new Error('Popup unavailable');
                    const parsed=new window.DOMParser().parseFromString(r.data.html,'text/html');
                    // Preview frontend markup without running visitor actions or custom scripts.
                    parsed.querySelectorAll('script,object,embed,base,meta,link').forEach(function(el){el.remove();});
                    parsed.querySelectorAll('*').forEach(function(el){
                        Array.from(el.attributes).forEach(function(attr){if(/^on/i.test(attr.name) || attr.name==='srcdoc' || (/^(href|src|action|formaction)$/i.test(attr.name) && /^\s*javascript:/i.test(attr.value)))el.removeAttribute(attr.name);});
                        if(el.tagName==='A'){el.target='_blank';el.rel='noopener noreferrer';}
                    });
                    content.replaceChildren(...Array.from(parsed.body.childNodes));
                    content.querySelectorAll('.oum_vote_button_wrap button,.oum_star_rating_wrap button').forEach(function(el){el.disabled=true;});
                    content.addEventListener('submit',function(e){e.preventDefault();});
                    content.querySelectorAll('img').forEach(function(img){img.addEventListener('load',function(){if(!state.disposed)marker.getPopup().update();});});
                    content.querySelectorAll('.oum-carousel').forEach(function(gallery){
                        const slides=Array.from(gallery.querySelectorAll('.oum-carousel-item'));if(slides.length<2)return;
                        let current=0;
                        [-1,1].forEach(function(direction){
                            const arrow=button(document,'',function(){slides[current].classList.remove('active');current=(current+direction+slides.length)%slides.length;slides[current].classList.add('active');marker.getPopup().update();});
                            arrow.className=direction<0 ? 'oum-carousel-prev' : 'oum-carousel-next';arrow.setAttribute('aria-label',direction<0 ? labels.previous : labels.next);gallery.append(arrow);
                        });
                    });
                    loaded=true;marker.getPopup().update();
                }).catch(function(error){if(error.name!=='AbortError' && !state.disposed)content.textContent=labels.error;});
            });
        }
    }
    function createLocationLayer(item) {
        const L = window.L;
        let marker;
        const point=function(){
            const lat=Number(item.lat),lng=Number(item.lng);
            if(!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
            return L.marker([lat,lng],{title:item.title,icon:L.icon({iconUrl:item.icon,iconSize:[26,41],iconAnchor:[13,41],popupAnchor:[0,-25],shadowUrl:window.oumEditor.shadow,shadowSize:[41,41],shadowAnchor:[13,41]})});
        };
        if ((item.geometry_type === 'polyline' || item.geometry_type === 'polygon') && item.geometry) {
            let geometry=item.geometry;
            if(typeof geometry==='string'){try{geometry=JSON.parse(geometry);}catch(_){geometry={};}}
            if(!geometry || typeof geometry!=='object')geometry={};
            const coordinates=geometry.type==='Polygon' && Array.isArray(geometry.coordinates) && Array.isArray(geometry.coordinates[0]) ? geometry.coordinates[0] : (Array.isArray(geometry.coordinates) ? geometry.coordinates : []);
            const points=coordinates.filter(function(p){return Array.isArray(p) && p.length>=2 && Number.isFinite(Number(p[0])) && Number.isFinite(Number(p[1]));}).map(function(p){return L.latLng(Number(p[1]),Number(p[0]));});
            if(points.length>=2){
                const closed=geometry.type==='Polygon' || (points.length>=4 && points[0].equals(points[points.length-1]));
                const style={color:item.category_color,weight:4,opacity:0.9,fillColor:item.category_color,fillOpacity:0.25};
                marker=closed ? L.polygon(points,style) : L.polyline(points,style);
            }else marker=point();
        }else marker=point();
        if(marker)marker.on('add',function(){
            // Gutenberg blocks pointer input on unmarked images and SVGs. These
            // belong to Leaflet, so explicitly opt out of native block/image dragging.
            const element=marker.getElement && marker.getElement();
            if(!element)return;
            element.setAttribute('draggable','false');
            element.querySelectorAll('img,svg').forEach(function(node){node.setAttribute('draggable','false');});
            const svg=element.closest('svg');if(svg)svg.setAttribute('draggable','false');
        });
        return marker;
    }
    // Stored views use the frontend's original 520 x 294 settings-map reference.
    // Keep this conversion aligned with frontend-block-map.js; do not change old frontend views.
    function referenceLayout(canvas) {
        const width=canvas.offsetWidth || 520, height=canvas.offsetHeight || 294;
        const sameAspect=Math.abs(width/height-520/294)<0.01;
        return {width:width,height:height,sameAspect:sameAspect,offset:Math.log2(sameAspect ? width/520 : Math.min(width/520,height/294))};
    }
    function previewZoom(map, view, canvas, clustered) {
        const layout=referenceLayout(canvas);
        let zoom;
        if(layout.sameAspect) {
            zoom=view.zoom+layout.offset;
        } else {
            const L=window.L, crs=L.CRS.EPSG3857;
            const referenceZoom=Math.max(1,Math.min(20,view.zoom));
            const center=crs.latLngToPoint(L.latLng(view.center[0],view.center[1]),referenceZoom);
            const southWest=crs.pointToLatLng(L.point(center.x-260,center.y+147),referenceZoom);
            const northEast=crs.pointToLatLng(L.point(center.x+260,center.y-147),referenceZoom);
            zoom=map.getBoundsZoom(L.latLngBounds(southWest,northEast),false);
        }
        return clustered ? Math.round(zoom) : zoom;
    }
    function applyReferenceView(state) {
        if(!state.view || !state.canvas.clientWidth || !state.canvas.clientHeight)return;
        const wasResizing=state.resizing;
        state.resizing=true;
        try {
            // The persisted format permits reference zoom >= 0. Keep zoom-out within
            // views that the unchanged frontend can reproduce from that format.
            state.map.setMinZoom(1);
            state.map.setMinZoom(Math.max(1,previewZoom(state.map,{center:state.view.center,zoom:0},state.canvas,state.clustered)));
            state.map.setView(state.view.center,previewZoom(state.map,state.view,state.canvas,state.clustered),{animate:false});
            state.displayZoom=state.map.getZoom();
            // At a zoom limit, a simple delta would reproduce the limit on reopen.
            // Remove only the clipped part when a user actually changes zoom.
            const layout=referenceLayout(state.canvas), snap=state.clustered ? 1 : 0.01;
            const effective=layout.sameAspect ? state.view.zoom : Math.max(1,Math.min(20,state.view.zoom));
            const raw=effective+layout.offset;
            const unsaturated=layout.sameAspect ? Math.round(raw/snap)*snap : Math.floor(Math.round(raw/(snap/100))*(snap/100)/snap)*snap;
            state.inverseZoomBase=effective-(unsaturated-state.displayZoom);
        } finally {state.resizing=wasResizing;}
    }
    function bindViewPersistence(map, state) {
        map.on('autopanstart',function(){state.popupPan=true;});
        map.on('movestart',function(){
            // Leaflet resize notifications also end with moveend, without a gesture.
            if(state.active && !state.popupPan && !state.resizing)state.userMoved=true;
        });
        map.on('moveend', function () {
            if(state.resizing)return;
            if(state.popupPan){state.popupPan=false;state.userMoved=false;return;}
            if (!state.active || state.disposed || !state.userMoved) return;
            state.userMoved=false;
            const center = map.getCenter();
            if(center.lng < -180 || center.lng > 180) center.lng=((center.lng+180)%360+360)%360-180;
            // Convert the visual change back to reference zoom. A pan keeps the original
            // value exactly, including fractional values rounded for clustered display.
            const delta=Math.round((map.getZoom()-state.displayZoom)*1e10)/1e10;
            const zoom=delta===0 ? state.view.zoom : state.inverseZoomBase+delta;
            if(delta!==0)state.inverseZoomBase=zoom;
            state.view={center:[center.lat,center.lng],zoom:zoom};
            state.displayZoom=map.getZoom();
            state.onChange({lat:String(center.lat),long:String(center.lng),zoom:String(zoom),region:'',editorVersion:1});
        });
    }
    function observeMapSize(map, state, canvas, height, actions) {
        function updateHeight() {
            height.textContent = (labels.height || 'Height')+': '+Math.round(canvas.clientHeight)+' px';
            height.style.top=Math.max(0,canvas.clientHeight-24-(actions.offsetHeight || 0)-8-(height.offsetHeight || 20))+'px';
        }
        updateHeight();
        const observer = new ResizeObserver(function () {
            updateHeight();
            if (!canvas.clientWidth || !canvas.clientHeight) return;
            state.resizing=true;
            try { map.invalidateSize({pan:false});applyReferenceView(state); } finally { state.resizing=false; }
        }); observer.observe(canvas);observer.observe(actions);
        return observer;
    }
    function createPreviewUI(root, state, widgetId) {
        const document = root.ownerDocument;
        const toolbar = document.createElement('div'); toolbar.className = 'oum-editor-toolbar';
        const canvas = document.createElement('div'); canvas.className = 'oum-editor-canvas';
        canvas.inert = !state.active;
        const status = document.createElement('div'); status.className = 'oum-editor-status'; status.setAttribute('role', 'status'); status.textContent = labels.loading;
        const icon=document.createElement('span');icon.setAttribute('aria-hidden','true');icon.className='oum-editor-edit-icon';icon.textContent='✥';
        function setActive(active) {
            if(!active && state.active && state.map) {
                // stop() may itself emit movement events. Flush an actual user gesture only.
                if(!state.userMoved)state.active=false;
                state.map.stop();
            }
            state.active=active;canvas.inert=!active;if(widgetId)interactions.set(widgetId,active);
            if(active)showHint();else clearHint();
            toggle.replaceChildren();if(!active)toggle.append(icon);toggle.append(document.createTextNode(active ? labels.done : labels.edit));
            toggle.setAttribute('aria-pressed',String(active));root.classList.toggle('is-editing',active);
            if(state.map) ['dragging','scrollWheelZoom','doubleClickZoom','touchZoom','boxZoom','keyboard'].forEach(function(handler){if(state.map[handler])state.map[handler][active ? 'enable' : 'disable']();});
        }
        const toggle = button(document,labels.edit,function(){setActive(!state.active);});
        toggle.className='oum-editor-toggle';if(!state.active)toggle.prepend(icon);
        toggle.setAttribute('aria-pressed', String(state.active));
        if (state.active) {toggle.textContent=labels.done;root.classList.add('is-editing');}
        const reset=button(document, labels.reset, function () { setActive(false);state.onChange({lat:'',long:'',zoom:'',region:'',editorVersion:1}); });
        reset.title=labels.resetView || 'Reset map view';reset.setAttribute('aria-label',reset.title);
        const hint=document.createElement('div');hint.className='oum-editor-move-hint';hint.textContent=labels.moveHint || 'Move and zoom.';
        hint.setAttribute('role','status');hint.hidden=true;
        let hintTimer;
        function clearHint(){clearTimeout(hintTimer);hint.hidden=true;root.classList.remove('is-hint-visible');}
        function showHint(){
            clearHint();hint.hidden=false;root.classList.add('is-hint-visible');
            hintTimer=setTimeout(clearHint,2200);
        }
        toolbar.append(toggle, reset);
        const height = document.createElement('div');
        height.className = 'oum-editor-height';
        const empty=document.createElement('div');empty.className='oum-editor-feedback oum-editor-empty';empty.setAttribute('role','status');
        const problem=document.createElement('div');problem.className='oum-editor-feedback oum-editor-problem';problem.setAttribute('role','status');
        const actions=document.createElement('div');actions.className='oum-editor-actions';
        if(window.oumEditor.locationsUrl){
            const locations=actionLink(document,labels.manageLocations || 'Manage Locations',window.oumEditor.locationsUrl);
            locations.title=labels.addMarkers || 'Add and edit markers';actions.append(locations);
        }
        if(window.oumEditor.settingsUrl){
            const settings=actionLink(document,labels.settings || 'Settings',window.oumEditor.settingsUrl);
            settings.title=labels.globalSettings || 'Global settings';actions.append(settings);
        }
        root.append(canvas, toolbar, status, height, empty, problem, hint, actions);
        return {canvas:canvas, toolbar:toolbar, status:status, height:height, empty:empty, problem:problem, clearHint:clearHint, actions:actions};
    }
    function applyPreviewLayout(root, canvas, toolbar, attributes, config) {
        const document = root.ownerDocument;
        root.dataset.size = config.size === 'fullwidth' ? 'fullwidth' : 'default';
        root.style.setProperty('--oum-editor-height', config.height || 'auto');
        canvas.style.aspectRatio = config.height ? 'auto' : '';
        const viewValues=['lat','long','zoom'].map(function(k){return String(attributes[k] === undefined ? '' : attributes[k]).trim().replace(',','.');});
        const hasView=viewValues.some(Boolean);
        const validView=viewValues.every(function(v){return v !== '' && Number.isFinite(Number(v));}) && Math.abs(Number(viewValues[0]))<=90 && Math.abs(Number(viewValues[1]))<=180 && Number(viewValues[2])>=0;
        if(hasView && !validView) { const note=document.createElement('p');note.className='oum-editor-notice';note.textContent=labels.checkView || 'Check map view.';root.classList.add('has-map-notice');root.append(note); }
        root.style.setProperty('--oum-editor-mobile-height', config.height_mobile || config.height || '400px');
    }
    function guardWidgetDragging(root, canvas, toolbar, state) {
        if(root.hasAttribute('data-oum-editor-settings')) {
            // Native HTML dragging of the Elementor widget would steal Leaflet's mouseup.
            const nativeDragTarget=root.closest('[data-widget_type]');
            const cancelNativeDrag=function(event){if(state.active){event.preventDefault();event.stopPropagation();}};
            if(nativeDragTarget) nativeDragTarget.addEventListener('dragstart',cancelNativeDrag,true);
            ['mousedown','pointerdown','touchstart'].forEach(function(event){
                canvas.addEventListener(event,function(e){if(state.active)e.stopPropagation();});
                toolbar.addEventListener(event,function(e){e.stopPropagation();});
            });
            return function () {if(nativeDragTarget)nativeDragTarget.removeEventListener('dragstart',cancelNativeDrag,true);};
        }
        return function () {};
    }
    function mount(root, attributes, onChange) {
        if (!root) return;
        if (root.ownerDocument !== window.document) {
            const token={active:true};
            const previous=bridges.get(root);if(previous) previous.active=false;
            bridges.set(root,token);
            frameRuntime(root.ownerDocument).then(function(runtime){
                if(token.active && root.isConnected) runtime.mount(root,attributes,onChange);
            }).catch(function(){
                if(!token.active || !root.isConnected) return;
                showPreviewError(root,new Error('Runtime unavailable'),function(){mount(root,attributes,onChange);});
            });
            return;
        }
        const previous = instances.get(root);
        const key = JSON.stringify(attributes);
        if (previous && previous.key === key) { previous.onChange = onChange; return; }
        if (previous && previous.map) {
            const omitView = function (value) {const result=Object.assign({},value);['lat','long','zoom','editorVersion'].forEach(function(k){delete result[k];});if(!result.region) delete result.region;return JSON.stringify(result);};
            const complete = ['lat','long','zoom'].every(function(k){return attributes[k] !== undefined && String(attributes[k]).trim() !== '' && Number.isFinite(Number(attributes[k]));}) && Math.abs(Number(attributes.lat))<=90 && Math.abs(Number(attributes.long))<=180 && Number(attributes.zoom)>=0;
            if (complete && omitView(JSON.parse(previous.key)) === omitView(attributes)) {
                previous.key=key;previous.onChange=onChange;
                const wasActive=previous.active;previous.active=false;
                const view={center:[Number(attributes.lat),Number(attributes.long)],zoom:Number(attributes.zoom)};
                if(!previous.view || view.zoom!==previous.view.zoom || view.center.some(function(v,i){return v!==previous.view.center[i];})) {
                    previous.view=view;applyReferenceView(previous);
                }
                previous.active=wasActive;return;
            }
        }
        if (previous) previous.destroy();
        const document = root.ownerDocument;
        const widget = root.closest('[data-id]');
        const widgetId = widget && widget.dataset.id;
        const state = { key: key, onChange: onChange, active: previous ? previous.active : !!interactions.get(widgetId), disposed: false, map: null };
        instances.set(root, state);
        root.classList.add('oum-editor-map');root.classList.remove('has-map-notice');root.classList.toggle('is-editing',state.active);delete root.dataset.ready; root.replaceChildren();
        const {canvas, toolbar, status, height, empty, problem, clearHint, actions} = createPreviewUI(root, state, widgetId);
        const controller = new AbortController();
        let observer;
        let removeDragGuard = function () {};
        let removeLayerEvents = function () {};
        state.destroy = function () { state.disposed = true; clearHint(); controller.abort(); if (observer) observer.disconnect(); removeDragGuard(); removeLayerEvents(); if (state.map) state.map.remove(); instances.delete(root); };
        const request = new URLSearchParams({action:'oum_editor_map',nonce:window.oumEditor.nonce,attributes:key});
        fetch(window.oumEditor.url, {method:'POST', credentials:'same-origin',body:request,signal:controller.signal})
            .catch(function(error){error.previewConnection=true;throw error;})
            .then(function (response) { if (!response.ok) {const error=new Error('Preview request failed');error.status=response.status;throw error;} return response.json(); })
            .then(function (response) {
                if (state.disposed) return;
                if (!response.success) throw new Error('Preview unavailable');
                const config = response.data;
                applyPreviewLayout(root, canvas, toolbar, attributes, config);
                const L = window.L;
                state.canvas=canvas;state.clustered=!!config.cluster;
                state.view={center:config.center.slice(),zoom:Number(config.zoom)};
                const map = state.map = L.map(canvas, {scrollWheelZoom:false,dragging:false,doubleClickZoom:false,touchZoom:false,boxZoom:false,keyboard:false,zoomControl:true,zoomAnimation:false,minZoom:1,maxZoom:20,zoomSnap:state.clustered ? 1 : 0.01,zoomDelta:1}).setView(config.center,config.zoom);
                map.zoomControl.getContainer().querySelector('.leaflet-control-zoom-in').setAttribute('aria-label',labels.zoomIn || 'Zoom in');
                map.zoomControl.getContainer().querySelector('.leaflet-control-zoom-out').setAttribute('aria-label',labels.zoomOut || 'Zoom out');
                canvas.addEventListener('click',function(event){if(state.active)event.stopPropagation();});
                removeDragGuard = guardWidgetDragging(root, canvas, toolbar, state);
                const retry=function(){state.destroy();mount(root,attributes,state.onChange);};
                let layer;
                try{layer=addBaseLayer(map, config);}catch(error){error.mapStyle=true;throw error;}
                removeLayerEvents=observeBaseLayer(layer,state,problem,retry,empty) || function(){};
                if(config.missingKey)showNotice(problem,labels.missingKey || 'Add a map key.',settingsLink(document));
                if(!config.missingKey)showEmptyState(empty,config);
                const group = config.cluster && L.markerClusterGroup ? L.markerClusterGroup() : L.layerGroup();
                config.markers.forEach(function (item) {
                    const marker = createLocationLayer(item);
                    if(!marker)return;
                    bindLocationPopup(marker, item, config, state, controller, document);
                    group.addLayer(marker);
                });
                group.addTo(map);
                applyReferenceView(state);

                if (state.active) ['dragging','scrollWheelZoom','doubleClickZoom','touchZoom','boxZoom','keyboard'].forEach(function(handler){if(map[handler]) map[handler].enable();});
                bindViewPersistence(map, state);
                observer = observeMapSize(map, state, canvas, height, actions);
                status.remove(); root.dataset.ready = 'true';
            }).catch(function (error) {
                if (error.name === 'AbortError' || state.disposed) return;
                showPreviewError(status,error,function(){state.destroy();mount(root,attributes,state.onChange);});
            });
    }
    function scan() {
        document.querySelectorAll('[data-oum-editor-settings]').forEach(function (root) {
            const settings = JSON.parse(root.getAttribute('data-oum-editor-settings'));
            mount(root,settings,function (changes) {
                const element = root.closest('[data-id]');
                const host = window.parent;
                if (!element || !host.elementor || !host.$e) return;
                const mapped = {};
                const container = host.elementor.getContainer(element.dataset.id);
                let filters={};
                try{filters=JSON.parse(container.settings.get('oum_filters') || '{}');}catch(_){}
                Object.keys(changes).forEach(function (key) {
                    if(filterKeys.includes(key) && key!=='ids' && key!=='types') {
                        filters[key]=changes[key];mapped.oum_filters=JSON.stringify(filters);
                    } else mapped[{lat:'latitude',long:'longitude',editorVersion:'oum_editor_version'}[key] || key] = changes[key];
                });
                host.$e.run('document/elements/settings', {container:container,settings:mapped});
            });
        });
        instances.forEach(function (state, root) { if (!root.isConnected) state.destroy(); });
    }
    window.OUMEditorMap = {mount:mount,unmount:function (root) { if(!root)return;if(root.ownerDocument !== window.document){const token=bridges.get(root);if(token)token.active=false;const view=root.ownerDocument.defaultView;const runtime=view && view.OUMEditorMap;if(runtime)runtime.unmount(root);bridges.delete(root);return;}const state = instances.get(root); if (state) state.destroy(); }};
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', scan); else scan();
    let pending;
    new MutationObserver(function () { clearTimeout(pending); pending = setTimeout(scan,50); }).observe(document.documentElement,{childList:true,subtree:true});
})(window);
