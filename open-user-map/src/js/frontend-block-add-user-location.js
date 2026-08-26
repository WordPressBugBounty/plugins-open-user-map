// Event: "Add location" Button click
if(document.getElementById('mapGetLocation') != null) {
  //init map
  const map = L.map('mapGetLocation', {
    attributionControl: true,
    gestureHandling: true,
  });

  map.attributionControl.setPrefix(false);

  const enableCurrentLocation = oum_enable_currentlocation ? true : false;

  function getCartoApiKey() {
    return (typeof oum_tile_provider_carto_key !== 'undefined') ? oum_tile_provider_carto_key : '';
  }

  function addCartoTileLayer(tileMap, style) {
    const cartoKey = getCartoApiKey();

    // CARTO tiles show a watermark without a key, so keep maps usable with ESRI.
    if (!cartoKey) {
      return L.tileLayer.provider('Esri.WorldStreetMap').addTo(tileMap);
    }

    return L.tileLayer.provider(style, {
      apikey: cartoKey
    }).addTo(tileMap);
  }

  function addCustomCartoLayers(tileMap, baseUrl) {
    const cartoKey = getCartoApiKey();

    // Custom styles are built from direct CARTO tile URLs and need the key manually.
    if (!cartoKey) {
      return L.tileLayer.provider('Esri.WorldStreetMap').addTo(tileMap);
    }

    const keyParam = '?key=' + encodeURIComponent(cartoKey);
    L.tileLayer(baseUrl + keyParam).addTo(tileMap);
    return L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png' + keyParam, {
      tileSize: 512,
      zoomOffset: -1
    }).addTo(tileMap);
  }

  // Activate Map inside overlay
  (function (){

    let markerIsVisible = false;
  
    // Set map style
    if (mapStyle == 'Custom1') {

      addCustomCartoLayers(map, 'https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}.png');

    } else if (mapStyle == 'Custom2') {

      addCustomCartoLayers(map, 'https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png');

    } else if (mapStyle == 'Custom3') {

      addCustomCartoLayers(map, 'https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png');

    } else if (mapStyle == 'MapBox.streets') {

      L.tileLayer.provider('MapBox', {
        id: 'mapbox/streets-v12',
        accessToken: oum_tile_provider_mapbox_key
      }).addTo(map);

    } else if (mapStyle == 'MapBox.outdoors') {

      L.tileLayer.provider('MapBox', {
        id: 'mapbox/outdoors-v12',
        accessToken: oum_tile_provider_mapbox_key
      }).addTo(map);

    } else if (mapStyle == 'MapBox.light') {

      L.tileLayer.provider('MapBox', {
        id: 'mapbox/light-v11',
        accessToken: oum_tile_provider_mapbox_key
      }).addTo(map);

    } else if (mapStyle == 'MapBox.dark') {

      L.tileLayer.provider('MapBox', {
        id: 'mapbox/dark-v11',
        accessToken: oum_tile_provider_mapbox_key
      }).addTo(map);

    } else if (mapStyle == 'MapBox.satellite') {

      L.tileLayer.provider('MapBox', {
        id: 'mapbox/satellite-v9',
        accessToken: oum_tile_provider_mapbox_key
      }).addTo(map);

    } else if (mapStyle == 'MapBox.satellite-streets') {

      L.tileLayer.provider('MapBox', {
        id: 'mapbox/satellite-streets-v12',
        accessToken: oum_tile_provider_mapbox_key
      }).addTo(map);

    } else if (mapStyle.indexOf('CartoDB.') === 0) {
      addCartoTileLayer(map, mapStyle);
    } else {
      // Default
      L.tileLayer.provider(mapStyle).addTo(map);
    }

    // Geosearch Provider
    switch (oum_geosearch_provider) {
      case 'osm':
        oum_geosearch_selected_provider = new GeoSearch.OpenStreetMapProvider();
        break;
      case 'geoapify':
        oum_geosearch_selected_provider = new GeoSearch.GeoapifyProvider({
          params: {
            apiKey: oum_geosearch_provider_geoapify_key
          }
        });
        break;
      case 'here':
        oum_geosearch_selected_provider = new GeoSearch.HereProvider({
          params: {
            apiKey: oum_geosearch_provider_here_key
          }
        });
        break;
      case 'mapbox':
        oum_geosearch_selected_provider = new GeoSearch.MapBoxProvider({
          params: {
            access_token: oum_geosearch_provider_mapbox_key
          }
        });
        break;
      default:
        oum_geosearch_selected_provider = new GeoSearch.OpenStreetMapProvider();
        break;
    }
  
    const search = new GeoSearch.GeoSearchControl({
        style: 'bar',
        showMarker: false,
        provider: oum_geosearch_selected_provider,
        searchLabel: oum_searchaddress_label
    });
    map.addControl(search);
  
    //define marker
  
    // Marker Icon
    let markerIcon = L.icon({
      iconUrl: marker_icon_url,
      iconSize: [26, 41],
      iconAnchor: [13, 41],
      popupAnchor: [0, -25],
      shadowUrl: marker_shadow_url,
      shadowSize: [41, 41],
      shadowAnchor: [13, 41]
    });
  
    let locationMarker = L.marker([0, 0], {icon: markerIcon}, {
        'draggable': true
    });
    
    //initial map view
    map.setView([start_lat, start_lng], start_zoom);

    // Bound map to fixed position
    if (oum_enable_fixed_map_bounds) {
      map.setMaxBounds(map.getBounds());
    }

    // Add control: get current location
    if(enableCurrentLocation) {
      L.control.locate({
        flyTo: true,
        showPopup: false
      }).addTo(map);
    }
  
    //Event: click on map to set marker
    map.on('click locationfound geosearch/showlocation', function(e) {
        let coords = (typeof e.marker !== 'undefined') ? e.marker._latlng : e.latlng;

        locationMarker.setLatLng(coords);
  
        if(!markerIsVisible) {
            locationMarker.addTo(map);
            markerIsVisible = true;
        }
  
        setLocationLatLng(coords);
    });
  
    //Event: drag marker
    locationMarker.on('dragend', function(e) {
        setLocationLatLng(e.target.getLatLng());
    });
    
    //set lat & lng input fields
    function setLocationLatLng(markerLatLng) {
        console.log(markerLatLng);
  
        document.getElementById('oum_location_lat').value = markerLatLng.lat;
        document.getElementById('oum_location_lng').value = markerLatLng.lng;
    }  
  
  })();

}