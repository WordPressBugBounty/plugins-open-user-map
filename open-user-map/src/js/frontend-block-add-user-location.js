document.addEventListener('DOMContentLoaded', function(e) {

  // Event: "Add location" Button click
  if(document.getElementById('mapGetLocation') != null) {
    //init map
    const map = L.map('mapGetLocation', {
      attributionControl: true,
      gestureHandling: true,
    });

    map.attributionControl.setPrefix(false);

    const enableCurrentLocation = oum_enable_currentlocation ? true : false;

    // Activate Map inside overlay
    (function (){

      let markerIsVisible = false;
    
      // Set map style
      if (mapStyle == 'Custom1') {

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}.png').addTo(map);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
          tileSize: 512,
          zoomOffset: -1
        }).addTo(map);

      } else if (mapStyle == 'Custom2') {

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png').addTo(map);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
          tileSize: 512,
          zoomOffset: -1
        }).addTo(map);

      } else if (mapStyle == 'Custom3') {

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png').addTo(map);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
          tileSize: 512,
          zoomOffset: -1
        }).addTo(map);

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

});