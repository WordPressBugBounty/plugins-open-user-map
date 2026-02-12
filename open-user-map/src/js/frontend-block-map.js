/**
 * @typedef {Object} Location
 * @property {string} title - Location title
 * @property {number} lat - Latitude
 * @property {number} lng - Longitude
 * @property {string} content - Location content/description
 * @property {string} icon - URL to marker icon
 * @property {string} post_id - WordPress post ID
 * @property {string[]} types - Array of location types/categories
 */

/**
 * @typedef {Object} MapBounds
 * @property {number} lat - Center latitude
 * @property {number} lng - Center longitude
 * @property {number} zoom - Zoom level
 */

// Add shared bounds variable at the top level
let sharedMapBounds = null;

/**
 * Utility Module - Contains helper functions used across other modules
 */
const OUMUtils = (function () {
  function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    const regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null
      ? ""
      : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  function latLngToBounds(lat, lng, zoom, width, height) {
    // Convert all inputs to numbers first, handling string inputs
    lat =
      typeof lat === "string"
        ? parseFloat(lat.replace(/['"]+/g, ""))
        : parseFloat(lat);
    lng =
      typeof lng === "string"
        ? parseFloat(lng.replace(/['"]+/g, ""))
        : parseFloat(lng);
    zoom =
      typeof zoom === "string"
        ? parseFloat(zoom.replace(/['"]+/g, ""))
        : parseFloat(zoom);
    width =
      typeof width === "string"
        ? parseFloat(width.replace(/['"]+/g, ""))
        : parseFloat(width);
    height =
      typeof height === "string"
        ? parseFloat(height.replace(/['"]+/g, ""))
        : parseFloat(height);

    // Validate coordinates
    if (!validateCoordinates(lat, lng)) {
      console.warn("Invalid coordinates for latLngToBounds, using defaults");
      return [
        [OUMConfig.defaults.map.lat, OUMConfig.defaults.map.lng],
        [OUMConfig.defaults.map.lat, OUMConfig.defaults.map.lng],
      ];
    }

    // Validate dimensions
    if (isNaN(width) || width <= 0 || isNaN(height) || height <= 0) {
      console.warn("Invalid dimensions for latLngToBounds");
      width = 520; // Default width
      height = 294; // Default height
    }

    // Validate and adjust zoom
    if (isNaN(zoom)) {
      zoom = OUMConfig.defaults.map.zoom;
    } else {
      // Ensure zoom is between 1 and 20
      zoom = Math.max(1, Math.min(20, zoom));
    }

    // Use Leaflet's projection system to properly account for Mercator distortion
    // This ensures equal visual margins regardless of latitude
    const crs = L.CRS.EPSG3857; // Default Mercator projection used by Leaflet
    
    // Project the center position to pixel space at the specified zoom level
    const centerLatLng = L.latLng(lat, lng);
    const centerPoint = crs.latLngToPoint(centerLatLng, zoom);
    
    // Calculate pixel bounds: center ± half width/height
    const halfWidth = width / 2;
    const halfHeight = height / 2;
    
    // Calculate the four corners of the viewport in pixel space
    const topLeftPoint = L.point(centerPoint.x - halfWidth, centerPoint.y - halfHeight);
    const topRightPoint = L.point(centerPoint.x + halfWidth, centerPoint.y - halfHeight);
    const bottomLeftPoint = L.point(centerPoint.x - halfWidth, centerPoint.y + halfHeight);
    const bottomRightPoint = L.point(centerPoint.x + halfWidth, centerPoint.y + halfHeight);
    
    // Unproject the corners back to lat/lng (this properly accounts for Mercator distortion)
    const topLeftLatLng = crs.pointToLatLng(topLeftPoint, zoom);
    const topRightLatLng = crs.pointToLatLng(topRightPoint, zoom);
    const bottomLeftLatLng = crs.pointToLatLng(bottomLeftPoint, zoom);
    const bottomRightLatLng = crs.pointToLatLng(bottomRightPoint, zoom);
    
    // Find the min/max lat and lng from all four corners
    // This ensures we capture the full bounds correctly
    const minLat = Math.min(topLeftLatLng.lat, topRightLatLng.lat, bottomLeftLatLng.lat, bottomRightLatLng.lat);
    const maxLat = Math.max(topLeftLatLng.lat, topRightLatLng.lat, bottomLeftLatLng.lat, bottomRightLatLng.lat);
    const minLng = Math.min(topLeftLatLng.lng, topRightLatLng.lng, bottomLeftLatLng.lng, bottomRightLatLng.lng);
    const maxLng = Math.max(topLeftLatLng.lng, topRightLatLng.lng, bottomLeftLatLng.lng, bottomRightLatLng.lng);

    return [
      [minLat, minLng],  // Southwest corner
      [maxLat, maxLng],  // Northeast corner
    ];
  }

  function customAutoSuggestText(text, val) {
    return (
      '<div><img src="' +
      val.layer.options.icon.options.iconUrl +
      '" />' +
      val.layer.options.title +
      "</div>"
    );
  }

  function initGeosearchProvider() {
    let provider;
    switch (oum_geosearch_provider) {
      case "osm":
        provider = new GeoSearch.OpenStreetMapProvider();
        break;
      case "geoapify":
        provider = new GeoSearch.GeoapifyProvider({
          params: {
            apiKey: oum_geosearch_provider_geoapify_key,
          },
        });
        break;
      case "here":
        provider = new GeoSearch.HereProvider({
          params: {
            apiKey: oum_geosearch_provider_here_key,
          },
        });
        break;
      case "mapbox":
        provider = new GeoSearch.MapBoxProvider({
          params: {
            access_token: oum_geosearch_provider_mapbox_key,
          },
        });
        break;
      default:
        provider = new GeoSearch.OpenStreetMapProvider();
        break;
    }
    return provider;
  }

  /**
   * Validates and sanitizes coordinates
   * @param {number|string} lat
   * @param {number|string} lng
   * @returns {boolean}
   */
  function validateCoordinates(lat, lng) {
    const parsedLat = parseFloat(lat);
    const parsedLng = parseFloat(lng);
    return (
      !isNaN(parsedLat) &&
      !isNaN(parsedLng) &&
      parsedLat >= -90 &&
      parsedLat <= 90 &&
      parsedLng >= -180 &&
      parsedLng <= 180
    );
  }

  /**
   * Safely parses a JSON string
   * @param {string} str
   * @param {*} fallback
   * @returns {*}
   */
  function safeJSONParse(str, fallback = null) {
    try {
      return JSON.parse(str);
    } catch (e) {
      return fallback;
    }
  }

  /**
   * Debounces a function
   * @param {Function} func
   * @param {number} wait
   * @returns {Function}
   */
  function debounce(func, wait = 250) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  /**
   * Simple sprintf implementation for string formatting
   * @param {string} str - String with placeholders (%1$d, %2$d, etc)
   * @param {...any} args - Values to replace placeholders
   * @returns {string}
   */
  function sprintf(str, ...args) {
    return str.replace(/%(\d+)\$d/g, (match, num) => args[num - 1] || match);
  }

  // Public interface
  return {
    getParameterByName,
    latLngToBounds,
    customAutoSuggestText,
    initGeosearchProvider,
    validateCoordinates,
    safeJSONParse,
    debounce,
    sprintf
  };
})();

/**
 * Error Handler Module - Centralizes error management
 */
const OUMErrorHandler = (function () {
  function showError(message, type = "error") {
    console.error(`OUM Error: ${message}`);

    // Show error in UI if error container exists
    const errorContainer = document.getElementById("oum_add_location_error");
    if (errorContainer) {
      errorContainer.innerHTML = `${message}<br>`;
      errorContainer.style.display = "block";
    }
  }

  function handleAjaxError(error) {
    showError(`Ajax request failed: ${error.message}`);
  }

  function validateCoordinates(lat, lng) {
    const parsedLat = parseFloat(lat);
    const parsedLng = parseFloat(lng);

    if (isNaN(parsedLat) || isNaN(parsedLng)) {
      showError("Invalid coordinates provided");
      return false;
    }

    if (
      parsedLat < -90 ||
      parsedLat > 90 ||
      parsedLng < -180 ||
      parsedLng > 180
    ) {
      showError("Coordinates out of valid range");
      return false;
    }

    return true;
  }

  return {
    showError,
    handleAjaxError,
    validateCoordinates,
  };
})();

/**
 * Configuration Module - Centralizes all configuration settings
 */
const OUMConfig = (function () {
  // Private variables
  const defaults = {
    map: {
      lat: 26,
      lng: 10,
      zoom: 1,
      bounds: L.latLngBounds(
        L.latLng(-85, -200), // Southwest corner (adjusted to prevent grey areas)
        L.latLng(85, 200)    // Northeast corner (adjusted to prevent grey areas)
      ),
    },
    media: {
      maxFiles: 5,
      validImageExtensions: ["jpeg", "jpg", "png", "webp"],
      maxImageSize: (window.oum_max_image_filesize || 10) * 1048576, // Convert MB to bytes
    },
    search: {
      zoomLevel: window.oum_searchmarkers_zoom || 8,
      addressLabel: window.oum_searchaddress_label || "Search for address",
      markersLabel: window.oum_searchmarkers_label || "Find marker",
    },
  };

  function getMapStyle() {
    return window.mapStyle || "Esri.WorldStreetMap";
  }

  function getTileProviderKey() {
    return window.oum_tile_provider_mapbox_key || "";
  }

  function getGeosearchProvider() {
    let provider;
    switch (oum_geosearch_provider) {
      case "geoapify":
        provider = new GeoSearch.GeoapifyProvider({
          params: {
            apiKey: oum_geosearch_provider_geoapify_key,
          },
        });
        break;
      case "here":
        provider = new GeoSearch.HereProvider({
          params: {
            apiKey: oum_geosearch_provider_here_key,
          },
        });
        break;
      case "mapbox":
        provider = new GeoSearch.MapBoxProvider({
          params: {
            access_token: oum_geosearch_provider_mapbox_key,
          },
        });
        break;
      default:
        provider = new GeoSearch.OpenStreetMapProvider();
    }
    return provider;
  }

  return {
    defaults,
    getMapStyle,
    getTileProviderKey,
    getGeosearchProvider,
  };
})();

/**
 * Map Core Module - Handles the main map initialization and configuration
 */
const OUMMap = (function () {
  // Private variables
  let map = null;
  let world_bounds = null;
  let startPosition = {
    lat:
      typeof start_lat !== "undefined"
        ? Number(start_lat)
        : OUMConfig.defaults.map.lat,
    lng:
      typeof start_lng !== "undefined"
        ? Number(start_lng)
        : OUMConfig.defaults.map.lng,
    zoom:
      typeof start_zoom !== "undefined"
        ? Number(start_zoom)
        : OUMConfig.defaults.map.zoom,
  };

  // Private functions
  function initializeStartPosition() {
    // Validate coordinates
    if (!OUMUtils.validateCoordinates(startPosition.lat, startPosition.lng)) {
      console.warn("Invalid coordinates, using defaults");
      startPosition.lat = OUMConfig.defaults.map.lat;
      startPosition.lng = OUMConfig.defaults.map.lng;
    }

    // Validate zoom level (between 1 and 20)
    if (
      isNaN(startPosition.zoom) ||
      startPosition.zoom < 1 ||
      startPosition.zoom > 20
    ) {
      console.warn("Invalid zoom level, using default");
      startPosition.zoom = OUMConfig.defaults.map.zoom;
    }
  }

  function setupMapBounds() {
    // Set bounds if fixed map bounds is enabled
    if (oum_enable_fixed_map_bounds) {
      // Calculate bounds using the updated latLngToBounds method
      // This uses Leaflet's projection system to properly account for Mercator distortion
      // Settings map dimensions (used when setting initial view in admin)
      const boundsArray = OUMUtils.latLngToBounds(
        startPosition.lat,
        startPosition.lng,
        startPosition.zoom,
        520, // Width of settings map
        294 // Height of settings map
      );

      // Convert the bounds array to a Leaflet LatLngBounds object
      world_bounds = L.latLngBounds(
        L.latLng(boundsArray[0][0], boundsArray[0][1]),
        L.latLng(boundsArray[1][0], boundsArray[1][1])
      );

      // Store bounds globally for form map to use
      sharedMapBounds = world_bounds;
    } else {
      // Use default world bounds when fixed map bounds is disabled
      world_bounds = OUMConfig.defaults.map.bounds;
      sharedMapBounds = world_bounds;
    }

    // Set the minimum zoom level to prevent zooming out too far
    const maxVisibleBounds = map.getBoundsZoom(world_bounds);
    map.setMinZoom(maxVisibleBounds);

    // Set max bounds without padding
    map.setMaxBounds(world_bounds);

    let isAdjusting = false;

    // Handle map movement without recursion
    map.on("moveend", function () {
      if (isAdjusting) return;
      isAdjusting = true;

      const zoom = map.getZoom();

      // Only enforce bounds if we're zoomed in beyond the minimum zoom
      if (zoom > maxVisibleBounds) {
        const currentBounds = map.getBounds();
        const currentCenter = map.getCenter();

        let needsAdjustment = false;
        let newLat = currentCenter.lat;
        let newLng = currentCenter.lng;

        // Calculate current viewport dimensions
        const viewportHeight =
          currentBounds.getNorth() - currentBounds.getSouth();
        const viewportWidth =
          currentBounds.getEast() - currentBounds.getWest();

        // Check and adjust latitude (north/south)
        if (currentBounds.getNorth() > world_bounds.getNorth()) {
          newLat = world_bounds.getNorth() - viewportHeight / 2;
          needsAdjustment = true;
        } else if (currentBounds.getSouth() < world_bounds.getSouth()) {
          newLat = world_bounds.getSouth() + viewportHeight / 2;
          needsAdjustment = true;
        }

        // Check and adjust longitude (east/west)
        if (currentBounds.getEast() > world_bounds.getEast()) {
          newLng = world_bounds.getEast() - viewportWidth / 2;
          needsAdjustment = true;
        } else if (currentBounds.getWest() < world_bounds.getWest()) {
          newLng = world_bounds.getWest() + viewportWidth / 2;
          needsAdjustment = true;
        }

        if (needsAdjustment) {
          map.setView([newLat, newLng], zoom, { animate: false });
        }
      }

      isAdjusting = false;
    });
  }

  function setupTileLayer(mapStyle) {
    let tileLayer;

    if (mapStyle == "Custom1") {
      tileLayer = L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}.png"
      ).addTo(map);
      L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png",
        {
          tileSize: 512,
          zoomOffset: -1,
        }
      ).addTo(map);
    } else if (mapStyle == "Custom2") {
      tileLayer = L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png"
      ).addTo(map);
      L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png",
        {
          tileSize: 512,
          zoomOffset: -1,
        }
      ).addTo(map);
    } else if (mapStyle == "Custom3") {
      tileLayer = L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png"
      ).addTo(map);
      L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png",
        {
          tileSize: 512,
          zoomOffset: -1,
        }
      ).addTo(map);
    } else if (mapStyle == "MapBox.streets") {
      tileLayer = L.tileLayer
        .provider("MapBox", {
          id: "mapbox/streets-v12",
          accessToken: OUMConfig.getTileProviderKey(),
        })
        .addTo(map);
    } else if (mapStyle == "MapBox.outdoors") {
      tileLayer = L.tileLayer
        .provider("MapBox", {
          id: "mapbox/outdoors-v12",
          accessToken: OUMConfig.getTileProviderKey(),
        })
        .addTo(map);
    } else if (mapStyle == "MapBox.light") {
      tileLayer = L.tileLayer
        .provider("MapBox", {
          id: "mapbox/light-v11",
          accessToken: OUMConfig.getTileProviderKey(),
        })
        .addTo(map);
    } else if (mapStyle == "MapBox.dark") {
      tileLayer = L.tileLayer
        .provider("MapBox", {
          id: "mapbox/dark-v11",
          accessToken: OUMConfig.getTileProviderKey(),
        })
        .addTo(map);
    } else if (mapStyle == "MapBox.satellite") {
      tileLayer = L.tileLayer
        .provider("MapBox", {
          id: "mapbox/satellite-v9",
          accessToken: OUMConfig.getTileProviderKey(),
        })
        .addTo(map);
    } else if (mapStyle == "MapBox.satellite-streets") {
      tileLayer = L.tileLayer
        .provider("MapBox", {
          id: "mapbox/satellite-streets-v12",
          accessToken: OUMConfig.getTileProviderKey(),
        })
        .addTo(map);
    } else if (mapStyle == "CustomImage") {
      // Custom Image layer
      setupCustomImageLayer();
      // Always add a base tile layer for proper map functionality
      // If hide tiles is enabled, use a transparent/invisible layer
      if (window.oum_custom_image_hide_tiles) {
        // Use a transparent tile layer to maintain map functionality
        tileLayer = L.tileLayer('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', {
          attribution: '',
          opacity: 0
        }).addTo(map);
        
        // Apply background color to map container
        if (window.oum_custom_image_background_color) {
          map.getContainer().style.backgroundColor = window.oum_custom_image_background_color;
        }
      } else {
        tileLayer = L.tileLayer.provider("OpenStreetMap.Mapnik").addTo(map);
      }
    } else {
      // Default
      tileLayer = L.tileLayer.provider(mapStyle).addTo(map);
    }

    return tileLayer;
  }

  function setupCustomImageLayer() {
    // Check if we have an image URL and bounds
    if (typeof window.oum_custom_image_url !== 'undefined' && window.oum_custom_image_url && 
        typeof window.oum_custom_image_bounds !== 'undefined' && window.oum_custom_image_bounds) {
      
      // Check if the uploaded file is an SVG
      const isSVG = window.oum_custom_image_url.toLowerCase().includes('.svg');
      
      if (isSVG) {
        // Handle SVG file - fetch and render as DOM elements
        setupSVGFromFile();
      } else {
        // Handle regular image file
        setupImageOverlay();
      }
    } else {
    }
  }

  // Helper function to setup SVG from uploaded file
  function setupSVGFromFile() {
    try {
      // Get bounds data (now properly handled as object)
      const bounds = window.oum_custom_image_bounds;

      // Validate bounds
      if (!bounds || typeof bounds.north === 'undefined' || typeof bounds.south === 'undefined' ||
          typeof bounds.east === 'undefined' || typeof bounds.west === 'undefined' ||
          bounds.north === '' || bounds.south === '' || bounds.east === '' || bounds.west === '') {
        console.warn('Open User Map: Invalid or empty bounds data, skipping SVG file layer');
        return;
      }


      // Fetch the SVG file and render it
      fetch(window.oum_custom_image_url)
        .then(response => response.text())
        .then(svgText => {
          // Create SVG element from the fetched content
          const svgElement = createSVGElement(svgText);
          if (!svgElement) {
            console.warn('Open User Map: Cannot create SVG layer from file - invalid SVG element');
            return;
          }

          // Create a custom SVG layer
          const svgLayer = L.svgOverlay(svgElement, [
            [bounds.south, bounds.west], // Southwest corner
            [bounds.north, bounds.east]  // Northeast corner
          ], {
            opacity: 1.0,
            interactive: true
          });

          svgLayer.addTo(map);


          // Store reference for potential removal
          window.oumCustomSVGLayer = svgLayer;

          console.log('Open User Map: Custom SVG file layer added successfully');
        })
        .catch(error => {
          console.warn('Open User Map: Error fetching SVG file:', error);
        });

    } catch (error) {
      console.warn('Open User Map: Error setting up custom SVG file layer:', error);
    }
  }

  // Helper function to setup regular image overlay
  function setupImageOverlay() {
    try {
      // Get bounds data (now properly handled as object)
      const bounds = window.oum_custom_image_bounds;

      // Validate bounds
      if (!bounds || typeof bounds.north === 'undefined' || typeof bounds.south === 'undefined' ||
          typeof bounds.east === 'undefined' || typeof bounds.west === 'undefined' ||
          bounds.north === '' || bounds.south === '' || bounds.east === '' || bounds.west === '') {
        console.warn('Open User Map: Invalid or empty bounds data, skipping image layer');
        return;
      }


      // Create image overlay
      const imageOverlay = L.imageOverlay(window.oum_custom_image_url, [
        [bounds.south, bounds.west], // Southwest corner
        [bounds.north, bounds.east]  // Northeast corner
      ], {
        opacity: 1.0,
        interactive: true
      });

      imageOverlay.addTo(map);


      // Store reference for potential removal
      window.oumCustomImageLayer = imageOverlay;

      console.log('Open User Map: Custom image layer added successfully');

    } catch (error) {
      console.warn('Open User Map: Error setting up custom image layer:', error);
    }
  }

  // Helper function to create SVG element from text
  function createSVGElement(svgText) {
    // Create a temporary div to parse the SVG
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = svgText;
    const svgElement = tempDiv.querySelector('svg');
    
    if (!svgElement) {
      console.warn('Open User Map: No valid SVG element found in SVG text');
      return null;
    }
    
    // Preserve the original viewBox if it exists
    // If missing, try to create it from width/height attributes
    if (!svgElement.getAttribute('viewBox')) {
      const width = svgElement.getAttribute('width');
      const height = svgElement.getAttribute('height');
      if (width && height) {
        // Remove units if present (e.g., "1580px" -> "1580")
        const widthNum = parseFloat(width);
        const heightNum = parseFloat(height);
        if (!isNaN(widthNum) && !isNaN(heightNum)) {
          svgElement.setAttribute('viewBox', `0 0 ${widthNum} ${heightNum}`);
        } else {
          svgElement.setAttribute('viewBox', '0 0 1000 1200');
        }
      } else {
        svgElement.setAttribute('viewBox', '0 0 1000 1200');
      }
    }
    
    // Ensure the SVG has proper styling for overlay
    svgElement.style.width = '100%';
    svgElement.style.height = '100%';
    svgElement.style.display = 'block';
    
    // Ensure the SVG fills the entire bounds area to prevent cropping
    svgElement.setAttribute('preserveAspectRatio', 'none');
    
    console.log('Open User Map: SVG element created successfully:', svgElement);
    
    return svgElement;
  }

  function setupControls() {
    // Search markers control as button
    if (oum_enable_searchmarkers_button) {
      L.control
        .search({
          textPlaceholder: oum_searchmarkers_label,
          layer: window.oumMarkersLayer,
          propertyName: "content",
          initial: false,
          buildTip: OUMUtils.customAutoSuggestText,
          autoType: false,
          firstTipSubmit: true,
          autoCollapse: true,
          zoom: oum_searchmarkers_zoom,
        })
        .addTo(map);
    }

    // Search markers control as searchbar
    if (oum_enable_searchbar && oum_searchbar_type === "markers") {
      L.control
        .search({
          textPlaceholder: oum_searchmarkers_label,
          layer: window.oumMarkersLayer,
          propertyName: "content",
          initial: false,
          buildTip: OUMUtils.customAutoSuggestText,
          autoType: false,
          firstTipSubmit: true,
          autoCollapse: false,
          collapsed: false,
          zoom: oum_searchmarkers_zoom,
          container: 'oum_search_marker'
        })
        .addTo(map);
    }

    // Add searchbar for address search
    if (oum_enable_searchbar && oum_searchbar_type === "address") {
      const searchBar = new GeoSearch.GeoSearchControl({
        style: "bar",
        showMarker: false,
        provider: OUMUtils.initGeosearchProvider(),
        searchLabel: oum_searchaddress_label,
        updateMap: false,
      });
      map.addControl(searchBar);
    }

    // Add button for address search
    if (oum_enable_searchaddress_button) {
      const searchButton = new GeoSearch.GeoSearchControl({
        style: "button",
        showMarker: false,
        provider: OUMUtils.initGeosearchProvider(),
        searchLabel: oum_searchaddress_label,
        updateMap: false,
      });
      map.addControl(searchButton);
    }

    // Current location control
    if (oum_enable_currentlocation) {
      window.map_locate_process = L.control
        .locate({
          flyTo: true,
          showPopup: false,
        })
        .addTo(map);
    }
  }

  function setupMapEvents() {
    // Event: pan or zoom Map
    map.on("moveend", function (ev) {
      startPosition.lat = map.getCenter().lat;
      startPosition.lng = map.getCenter().lng;
      startPosition.zoom = map.getZoom();
    });

    // Event: Enter/Exit Fullscreen
    const addLocationPopup = document.querySelector("#add-location-overlay");
    const originalContainer = addLocationPopup?.parentElement;
    const fullscreenContainer = document.querySelector(
      ".open-user-map .map-wrap"
    );

    if (addLocationPopup) {
      map.on("enterFullscreen", function () {
        fullscreenContainer.appendChild(addLocationPopup);
      });

      map.on("exitFullscreen", function () {
        originalContainer.appendChild(addLocationPopup);
      });
    }

    // Event: geosearch success
    map.on("geosearch/showlocation", function(e) {
      let coords = e.marker._latlng;
      
      // Check against fixed map bounds if enabled, otherwise allow any location
      let isInBounds = true; // Default to true when fixed bounds is disabled
      if (oum_enable_fixed_map_bounds === 'on' && sharedMapBounds) {
        // Check if location is within the fixed map bounds (not just current viewport)
        isInBounds = sharedMapBounds.contains(coords);
      }
      
      if (!isInBounds && oum_enable_fixed_map_bounds === 'on') {
        console.log("This search result is out of reach.");
        const searchBar = document.querySelector(`#${map_el} .leaflet-geosearch-bar form`);
        if (searchBar) {
          searchBar.style.boxShadow = "0 0 10px rgb(255, 111, 105)";
          setTimeout(function () {
            searchBar.style.boxShadow = "0 1px 5px rgba(255, 255, 255, 0.65)";
          }, 2000);
        }
      } else {
        // Handle valid search result
        if (e.location.bounds) {
          map.flyToBounds(e.location.bounds);
        } else if (e.location.raw && e.location.raw.mapView) {
          map.flyToBounds([
            [e.location.raw.mapView.south, e.location.raw.mapView.west],
            [e.location.raw.mapView.north, e.location.raw.mapView.east]
          ]);
        } else {
          map.flyTo([e.location.y, e.location.x], 17);
        }
      }
    });
  }

  function setupRegionEvents() {
    document
      .querySelectorAll(".open-user-map .change_region")
      .forEach(function (btn) {
        btn.onclick = function (event) {
          let el = event.currentTarget;
          let region_lat = el.getAttribute("data-lat");
          let region_lng = el.getAttribute("data-lng");
          let region_zoom = el.getAttribute("data-zoom");

          // Get frontend map container size
          const mapContainer = map.getContainer();
          const frontendMapWidth = mapContainer.offsetWidth || 520;
          const frontendMapHeight = mapContainer.offsetHeight || 294;
          const settingsMapWidth = 520;
          const settingsMapHeight = 294;
          
          // Calculate bounds based on settings map dimensions
          let region_bounds = OUMUtils.latLngToBounds(
            parseFloat(region_lat),
            parseFloat(region_lng),
            parseFloat(region_zoom),
            settingsMapWidth,
            settingsMapHeight
          );
          
          // Convert to Leaflet bounds
          const regionBounds = L.latLngBounds(
            L.latLng(region_bounds[0][0], region_bounds[0][1]),
            L.latLng(region_bounds[1][0], region_bounds[1][1])
          );
          
          // Calculate aspect ratios
          const settingsAspectRatio = settingsMapWidth / settingsMapHeight;
          const frontendAspectRatio = frontendMapWidth / frontendMapHeight;
          const aspectRatioDifference = Math.abs(settingsAspectRatio - frontendAspectRatio);
          
          let region_bounds_zoom;
          
          // If aspect ratios match, use ratio-based calculation
          if (aspectRatioDifference < 0.01) {
            const widthRatio = frontendMapWidth / settingsMapWidth;
            const zoomAdjustment = Math.log2(widthRatio);
            region_bounds_zoom = parseFloat(region_zoom) + zoomAdjustment;
          } else {
            // Different aspect ratios - use getBoundsZoom
            region_bounds_zoom = map.getBoundsZoom(regionBounds, false);
          }

          // Center Map
          map.flyTo([region_lat, region_lng], region_bounds_zoom);

          // Update active state
          document
            .querySelectorAll(".open-user-map .change_region")
            .forEach(function (el) {
              el.classList.remove("active");
            });
          el.classList.add("active");
        };

        // Event: Change Region on ?region=Europe
        const REGION_ID = OUMUtils.getParameterByName("region");
        if (btn.textContent == REGION_ID) {
          btn.click();
        }
      });
  }

  // Public interface
  return {
    init: function (mapEl) {
      try {
        initializeStartPosition();

        // Initialize map
        map = L.map(mapEl, {
          gestureHandling: oum_enable_scrollwheel_zoom_map ? false : true,
          minZoom: 1, // Set default minimum zoom
          zoomSnap: 0.01, // Allow fractional zoom levels (0.01 steps)
          zoomDelta: 1, // Zoom step size for controls
          attributionControl: true,
          fullscreenControl: oum_enable_fullscreen,
          fullscreenControlOptions: {
            position: "topleft",
            fullscreenElement: document.querySelector(
              ".open-user-map .map-wrap"
            ),
          },
        });

        map.attributionControl.setPrefix(false);

        // First set up the tile layer
        setupTileLayer(OUMConfig.getMapStyle());

        // Invalidate size to ensure accurate measurements
        map.invalidateSize();
        
        // Get the actual frontend map container size
        const mapContainer = map.getContainer();
        const frontendMapWidth = mapContainer.offsetWidth || 520;
        const frontendMapHeight = mapContainer.offsetHeight || 294;
        
        // Settings map dimensions (fixed)
        const settingsMapWidth = 520;
        const settingsMapHeight = 294;
        
        // Calculate aspect ratios to determine if they match
        const settingsAspectRatio = settingsMapWidth / settingsMapHeight;
        const frontendAspectRatio = frontendMapWidth / frontendMapHeight;
        const aspectRatioDifference = Math.abs(settingsAspectRatio - frontendAspectRatio);
        
        // Calculate initial bounds based on settings map dimensions (520)
        // This gives us the geographic area that should be visible
        const settingsBoundsArray = OUMUtils.latLngToBounds(
          startPosition.lat,
          startPosition.lng,
          startPosition.zoom,
          settingsMapWidth,
          settingsMapHeight
        );

        // Convert to Leaflet bounds
        const settingsBounds = L.latLngBounds(
          L.latLng(settingsBoundsArray[0][0], settingsBoundsArray[0][1]),
          L.latLng(settingsBoundsArray[1][0], settingsBoundsArray[1][1])
        );

        let targetZoom;
        
        // If aspect ratios are very similar (within 1%), use ratio-based zoom calculation
        // This preserves the exact same geographic area when aspect ratios match
        if (aspectRatioDifference < 0.01) {
          // Calculate zoom adjustment based on size ratio
          // If frontend map is larger, we need higher zoom to show same area
          // Formula: zoom adjustment = log2(frontendWidth / settingsWidth)
          const widthRatio = frontendMapWidth / settingsMapWidth;
          const zoomAdjustment = Math.log2(widthRatio);
          targetZoom = startPosition.zoom + zoomAdjustment;
        } else {
          // Aspect ratios differ - use getBoundsZoom to account for different aspect ratios
          // This will fit the bounds but may show slightly different area due to aspect ratio
          targetZoom = map.getBoundsZoom(settingsBounds, false);
        }
        
        // Use setView with the calculated zoom level to show the same geographic area
        // This ensures the same geographic bounds are visible regardless of frontend map size
        map.setView([startPosition.lat, startPosition.lng], targetZoom, {
          animate: false
        });

        // Set up other components
        setupMapBounds();
        setupMapEvents();
        setupRegionEvents();

        window.oumMap = map;

        // Listen for markers initialized event
        document.addEventListener('oum:markers_initialized', function(e) {
          if (e.detail.mapId === mapEl) {
            // Set up controls after markers are ready
            setupControls();
          }
        }, { once: true });  // Use once: true to ensure it only runs once

        // Dispatch map initialized event when everything is ready
        document.dispatchEvent(new CustomEvent('oum:map_initialized', {
          detail: {
            mapId: mapEl,
            map: oumMap
          }
        }));

        return map;
      } catch (error) {
        OUMErrorHandler.showError("Error initializing map: " + error.message);
        throw error;
      }
    },
    getMap: function () {
      return map;
    },
    getStartPosition: function () {
      return startPosition;
    },
  };
})();

/**
 * Markers Module - Handles all marker-related functionality
 */
const OUMMarkers = (function () {
  // Private variables
  let markersLayer = null;
  let allMarkers = [];
  let map = null;
  let locationsById = {};
  let searchtextFilterValue = "";
  let categoriesFilterSelection = null;
  let customfieldsFilterSelection = null;
  let visibleMarkersCount = 0;

  // Private functions
  function initializeMarkersLayer() {
    markersLayer = !oum_enable_cluster
      ? L.layerGroup({ chunkedLoading: true })
      : L.markerClusterGroup({
          showCoverageOnHover: false,
          removeOutsideVisibleBounds: false,
          maxClusterRadius: 40,
          chunkedLoading: true,
        });
  }

  function createMarker(location) {
    const contentText = (
      location.title +
      " | " +
      location.content.replace(/(<([^>]+)>)/gi, " ").replace(/\s\s+/g, " ")
    ).toLowerCase();

    let marker = L.marker([location.lat, location.lng], {
      title: location.title,
      post_id: location.post_id,
      content: contentText,
      zoom: location.zoom,
      icon: L.icon({
        iconUrl: location.icon,
        iconSize: [26, 41],
        iconAnchor: [13, 41],
        popupAnchor: [0, -25],
        shadowUrl: marker_shadow_url,
        shadowSize: [41, 41],
        shadowAnchor: [13, 41],
      }),
      types: location.types || [],
    });

    let popup = L.responsivePopup().setContent(location.content);
    marker.bindPopup(popup);

    return marker;
  }

  function setupMarkerEvents() {
    // Event: Open Location Bubble
    map.on("popupopen", function (locationBubble) {
      const el = document.querySelector(
        ".open-user-map #location-fullscreen-container"
      );
      const locationContentWrap = el ? el.querySelector(".location-content-wrap") : null;
      if (locationContentWrap) {
        locationContentWrap.innerHTML = locationBubble.popup.getContent();
      }
      if (el) {
        el.classList.add("visible");
        document.querySelector("body").classList.add("oum-location-opened");
      }
      
      // Check edit permissions and inject edit button if allowed (for fullscreen container)
      if (locationContentWrap) {
        checkEditPermissionAndInjectButton(locationContentWrap);
      }
      
      // Also check for the Leaflet popup itself (the small popup on the map)
      const popupContent = locationBubble.popup.getElement();
      if (popupContent) {
        checkEditPermissionAndInjectButton(popupContent);
      }
      
      // Update vote button states for the newly opened popup
      if (window.OUMVoteHandler && window.OUMVoteHandler.updateVoteButtonStates) {
        window.OUMVoteHandler.updateVoteButtonStates();
      }
      
      // Initialize vote counts from data for the newly opened popup
      if (window.OUMVoteHandler && window.OUMVoteHandler.initializeVoteCountsFromData) {
        const voteButtons = el ? el.querySelectorAll('.oum_vote_button') : [];
        if (voteButtons.length > 0) {
          // For popups, use the updated location data to initialize vote counts
          // This avoids AJAX calls while ensuring we have the latest vote data
          window.OUMVoteHandler.initializeVoteCountsFromData();
        }
      }
      
      // Initialize star ratings from data for the newly opened popup
      if (window.OUMVoteHandler && window.OUMVoteHandler.initializeStarRatings) {
        const starRatings = el ? el.querySelectorAll('.oum_star_rating') : [];
        if (starRatings.length > 0) {
          // For popups, use the updated location data to initialize star ratings
          // This avoids AJAX calls while ensuring we have the latest star rating data
          window.OUMVoteHandler.initializeStarRatings();
        }
      }
      
      // Setup opening hours toggle functionality for fullscreen container
      if (locationContentWrap) {
        OUMOpeningHours.setupToggle(locationContentWrap);
      }
      
      // Setup opening hours toggle for popup content
      if (popupContent) {
        OUMOpeningHours.setupToggle(popupContent);
      }
    });

    // Event: Close Location Bubble
    map.on("popupclose", function () {
      const el = document.querySelector(
        ".open-user-map #location-fullscreen-container"
      );
      if (el) {
        // Clear the content to stop any media playback (YouTube, Vimeo, etc.)
        const locationContentWrap = el.querySelector(".location-content-wrap");
        if (locationContentWrap) {
          locationContentWrap.innerHTML = '';
        }
        el.classList.remove("visible");
      }
      document.querySelector("body").classList.remove("oum-location-opened");
    });
  }
  
  /**
   * Check edit permission for a location and inject edit button if allowed
   * This is called when a popup opens to dynamically show edit buttons
   * based on real-time permissions (cache-safe solution)
   * 
   * @param {HTMLElement} container - The container element to search for placeholder
   */
  function checkEditPermissionAndInjectButton(container) {
    // Find the edit button placeholder in the opened popup
    const placeholder = container.querySelector('.edit-location-button-placeholder');
    
    if (!placeholder) {
      return; // No placeholder found, nothing to do
    }
    
    const postId = placeholder.getAttribute('data-post-id');
    
    if (!postId) {
      placeholder.remove();
      return;
    }
    
    if (typeof jQuery === 'undefined') {
      placeholder.remove();
      return;
    }
    
    if (!oum_ajax || !oum_ajax.ajaxurl) {
      placeholder.remove();
      return;
    }
    
    // Make AJAX call to check permissions
    jQuery.ajax({
      type: 'POST',
      url: oum_ajax.ajaxurl,
      dataType: 'json',
      data: {
        action: 'oum_check_edit_permission',
        post_id: postId
      },
      success: function(response) {
        if (response && response.success && response.data && response.data.can_edit) {
          // User can edit - inject the edit button
          const editButton = document.createElement('div');
          editButton.className = 'edit-location-button';
          editButton.setAttribute('data-post-id', postId);
          editButton.setAttribute('title', oum_custom_strings && oum_custom_strings.edit_location ? oum_custom_strings.edit_location : 'Edit location');
          
          // Replace placeholder with actual button
          placeholder.parentNode.replaceChild(editButton, placeholder);
        } else {
          // User cannot edit - remove placeholder (keep clean HTML)
          placeholder.remove();
        }
      },
      error: function(xhr, status, error) {
        // On error, just remove the placeholder
        placeholder.remove();
      }
    });
  }

  function applyFilters() {
    if (!markersLayer) {
      return;
    }

    markersLayer.clearLayers();
    visibleMarkersCount = 0;

    allMarkers.forEach((marker) => {
      if (
        markerMatchesSearchtextFilter(marker) &&
        markerMatchesCategoriesFilter(marker) &&
        markerMatchesCustomfieldsFilter(marker)
      ) {
        markersLayer.addLayer(marker);
        visibleMarkersCount += 1;
      }
    });

    if (oum_enable_cluster && typeof markersLayer.refreshClusters === "function") {
      markersLayer.refreshClusters();
    }
  }

  function markerMatchesSearchtextFilter(marker) {
    if (!searchtextFilterValue) {
      return true;
    }
    return marker.options.content.toLowerCase().includes(searchtextFilterValue);
  }

  function markerMatchesCategoriesFilter(marker) {
    const markerTypes = marker.options.types || [];

    if (!markerTypes.length) {
      return true;
    }

    // Before the user interacts with the category checkboxes, keep the previous behaviour (show all)
    if (categoriesFilterSelection === null) {
      return true;
    }

    if (!categoriesFilterSelection.size) {
      return false;
    }

    return markerTypes.some((type) => categoriesFilterSelection.has(type));
  }

  function markerMatchesCustomfieldsFilter(marker) {
    if (!customfieldsFilterSelection || !Object.keys(customfieldsFilterSelection).length) {
      return true;
    }

    const locationData = getLocationData(marker.options.post_id);
    if (!locationData) {
      return true;
    }

    for (const [customFieldId, criterion] of Object.entries(customfieldsFilterSelection)) {
      // Special handling for opening_hours_open_now filter type
      if (criterion.type === 'opening_hours_open_now') {
        const field = locationData.custom_fields?.find(
          (customField) => String(customField.index) === String(customFieldId)
        );
        
        // Use pre-calculated open_now value from PHP
        if (field && typeof field.open_now === 'boolean') {
          if (field.open_now !== true) {
            return false;
          }
        } else {
          // Fallback: if open_now not available, exclude location
          return false;
        }
        continue;
      }

      // Standard custom field filtering
      const fieldValue = getCustomFieldValue(locationData, customFieldId);
      if (!matchesCriterion(fieldValue, criterion)) {
        return false;
      }
    }

    return true;
  }

  function getLocationData(postId) {
    if (!postId) {
      return null;
    }

    if (locationsById[String(postId)]) {
      return locationsById[String(postId)];
    }

    if (typeof window !== "undefined" && Array.isArray(window.oum_all_locations)) {
      const fallback = window.oum_all_locations.find(
        (location) => String(location.post_id) === String(postId)
      );
      if (fallback) {
        locationsById[String(postId)] = fallback;
        return fallback;
      }
    }

    return null;
  }

  function getCustomFieldValue(locationData, customFieldId) {
    if (!locationData || !Array.isArray(locationData.custom_fields)) {
      return null;
    }

    const field = locationData.custom_fields.find(
      (customField) => String(customField.index) === String(customFieldId)
    );

    return field ? field.val : null;
  }

  function normalizeFieldValues(fieldValue) {
    if (Array.isArray(fieldValue)) {
      return fieldValue.map((val) => val.toString()).filter((val) => val !== "");
    }

    if (typeof fieldValue === "string") {
      return fieldValue
        .split("|")
        .map((val) => val.trim())
        .filter((val) => val !== "");
    }

    if (fieldValue !== null && typeof fieldValue !== "undefined") {
      const value = fieldValue.toString();
      return value ? [value] : [];
    }

    return [];
  }

  function matchesCriterion(fieldValue, criterion) {
    if (
      fieldValue === null ||
      typeof fieldValue === "undefined" ||
      (typeof fieldValue === "string" && fieldValue.trim() === "") ||
      (Array.isArray(fieldValue) && fieldValue.length === 0)
    ) {
      return false;
    }

    switch (criterion.type) {
      case "text":
        return fieldValue.toString().toLowerCase().includes(criterion.value);
      case "checkbox": {
        const values = normalizeFieldValues(fieldValue);
        const relation = criterion.relation || 'OR'; // Default to OR for backward compatibility
        
        if (relation === 'AND') {
          // AND: All selected checkbox values must be present in the field values
          return criterion.values.length > 0 && criterion.values.every((val) => values.includes(val));
        } else {
          // OR: At least one selected checkbox value must match (default behavior)
          return criterion.values.some((val) => values.includes(val));
        }
      }
      case "radio":
      case "select": {
        const values = normalizeFieldValues(fieldValue);
        return values.includes(criterion.value);
      }
      default:
        return true;
    }
  }

  function updateSearchtextAndCategoriesFilters() {
    // Read searchtext filter
    const markerFilterInput = document.getElementById("oum_filter_markers");
    searchtextFilterValue = markerFilterInput ? markerFilterInput.value.toLowerCase() : "";

    // Read categories filter
    const categoryInputs = document.querySelectorAll(
      '.open-user-map .oum-filter-controls [name="type"]'
    );
    categoriesFilterSelection = new Set(
      Array.from(categoryInputs)
        .filter((input) => input.checked)
        .map((input) => input.value)
    );

    applyFilters();
  }

  function setupFilterListEvents() {
    const filterControls = document.querySelector(
      ".open-user-map .oum-filter-controls"
    );
    if (!filterControls) return;

    const closeButton = filterControls.querySelector(".close-filter-list");
    const toggle = filterControls.querySelector(".oum-filter-toggle");
    const filterWrapper = filterControls.closest(".oum-map-filter-wrapper");

    // Close button always collapses the filter controls
    if (closeButton) {
      closeButton.addEventListener("click", function(e) {
        e.stopPropagation();
        filterControls.classList.remove("active");
        // Ensure use-collapse class is present so the toggle button becomes visible
        filterControls.classList.add("use-collapse");
      });
    }

    // Setup click toggle for the toggle button (works regardless of initial use-collapse state)
    if (toggle) {
      toggle.addEventListener("click", function(e) {
        e.stopPropagation();
        filterControls.classList.toggle("active");
      });
    }

    // Close when clicking outside the filter wrapper (only if use-collapse is present)
    if (filterWrapper) {
      document.addEventListener("click", function(e) {
        if (filterControls.classList.contains("use-collapse") && !filterWrapper.contains(e.target)) {
          filterControls.classList.remove("active");
        }
      });
    }

    // Function to setup toggle all functionality
    function setupToggleAll() {
      const toggleAllCheckbox = filterControls.querySelector(
        ".oum-toggle-all-checkbox"
      );
      const categoryCheckboxes = filterControls.querySelectorAll(
        '[name="type"]'
      );

      if (!toggleAllCheckbox || categoryCheckboxes.length === 0) return;

      // Function to update toggle all state based on individual checkboxes
      function updateToggleAllState() {
        const checkedCount = Array.from(categoryCheckboxes).filter(
          (cb) => cb.checked
        ).length;
        const totalCount = categoryCheckboxes.length;

        if (checkedCount === 0) {
          toggleAllCheckbox.checked = false;
          toggleAllCheckbox.indeterminate = false;
        } else if (checkedCount === totalCount) {
          toggleAllCheckbox.checked = true;
          toggleAllCheckbox.indeterminate = false;
        } else {
          toggleAllCheckbox.checked = false;
          toggleAllCheckbox.indeterminate = true;
        }
      }

      // Function to toggle all categories
      function toggleAllCategories() {
        // Determine if we should check all or uncheck all based on current state
        const checkedCount = Array.from(categoryCheckboxes).filter(
          (cb) => cb.checked
        ).length;
        const totalCount = categoryCheckboxes.length;

        // If all are checked, uncheck all. Otherwise, check all
        const shouldCheck = checkedCount < totalCount;

        categoryCheckboxes.forEach((checkbox) => {
          checkbox.checked = shouldCheck;
        });

        // Trigger filter update
        updateSearchtextAndCategoriesFilters();
      }

      // Event listeners
      toggleAllCheckbox.addEventListener("change", toggleAllCategories);

      // Update toggle all state when individual checkboxes change
      categoryCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", updateToggleAllState);
      });

      // Set initial state
      updateToggleAllState();
    }

    // Setup toggle all functionality regardless of layout
    setupToggleAll();
  }

  function handleAutoOpenMarker(markerId) {
    const targetMarker = allMarkers.find((m) => m.options.post_id === markerId);
    if (targetMarker) {

      if (oum_enable_cluster) {

        // Wait a bit for the clustering to update

        setTimeout(() => {
          // Try to zoom to the specific marker and show it
          markersLayer.zoomToShowLayer(targetMarker, () => {
            targetMarker.openPopup();
          });
        }, 500);
      } else {
        // For non-clustered markers, we can open the popup directly and use the zoom level from the marker data
        map.setView(targetMarker.getLatLng(), targetMarker.options.zoom);
        targetMarker.openPopup();
      }
    }
  }

  // Public interface
  return {
    init: function (mapInstance) {
      map = mapInstance;
      initializeMarkersLayer();
      markersLayer.addTo(map);
      setupMarkerEvents();
      setupFilterListEvents();

      // Make layer globally available (for backward compatibility)
      window.oumMarkersLayer = markersLayer;
      window.oumAllMarkers = allMarkers;

      // Dispatch markers initialized event
      document.dispatchEvent(new CustomEvent('oum:markers_initialized', {
        detail: {
          mapId: map._container.id,
          markersLayer: markersLayer
        }
      }));

      return this;
    },
    addMarkers: function (locations) {
      locations.forEach((location) => {
        const marker = createMarker(location);
        allMarkers.push(marker);
        locationsById[String(location.post_id)] = location;
        markersLayer.addLayer(marker);
      });

      // Re-run filters so that advanced filter state or search/category selections stay in sync
      applyFilters();

      // After adding all markers, check if we need to auto-open one
      const POPUP_MARKER_ID = OUMUtils.getParameterByName("markerid");
      if (POPUP_MARKER_ID) {
        handleAutoOpenMarker(POPUP_MARKER_ID);
      }
    },
    updateSearchtextAndCategoriesFilters: updateSearchtextAndCategoriesFilters,
    getMarkersLayer: function () {
      return markersLayer;
    },
    getAllMarkers: function () {
      return allMarkers;
    },
    getAllLocations: function () {
      return Object.values(locationsById);
    },
    setCustomfieldsFilter: function (criteria) {
      if (criteria && Object.keys(criteria).length > 0) {
        customfieldsFilterSelection = criteria;
      } else {
        customfieldsFilterSelection = null;
      }
      applyFilters();
    },
    getFilterState: function () {
      return {
        searchtext: searchtextFilterValue,
        categories:
          categoriesFilterSelection === null
            ? null
            : Array.from(categoriesFilterSelection),
        customfields: customfieldsFilterSelection
      };
    },
    getFilteredMarkersCount: function () {
      return visibleMarkersCount;
    },
  };
})();

/**
 * Form Map Module - Handles all map-related functionality for the form
 */
const OUMFormMap = (function () {
  // Private variables
  let formMap = null;
  let locationMarker = null;
  let markerIsVisible = false;
  let isAdjusting = false;
  let isInitialized = false;

  // Private functions
  function initializeFormMap() {
    if (isInitialized) {
      return;
    }

    formMap = L.map("mapGetLocation", {
      attributionControl: false,
      gestureHandling: true,
      minZoom: 1,
      zoomSnap: 0.01, // Allow fractional zoom levels (0.01 steps)
      zoomDelta: 1, // Zoom step size for controls
      fullscreenControl: oum_enable_fullscreen,
      fullscreenControlOptions: {
        position: "topleft",
      },
    });

    // Make form map globally available (for backward compatibility)
    window.oumMap2 = formMap;

    setupTileLayer();
    setupControls();
    setupMarker();
    setupMapEvents();

    // Invalidate size to ensure accurate measurements before calculating bounds
    formMap.invalidateSize();

    // Always apply bounds to prevent showing repeated maps
    const boundsToUse = oum_enable_fixed_map_bounds 
      ? sharedMapBounds 
      : OUMConfig.defaults.map.bounds;

    // Set the bounds
    formMap.setMaxBounds(boundsToUse);

    // Calculate minimum zoom level based on bounds
    // When fixed bounds are enabled, use sharedMapBounds for accurate calculation
    // This is used both for setting minZoom and in the moveend event handler
    let maxVisibleBounds;
    if (oum_enable_fixed_map_bounds && sharedMapBounds) {
      // Invalidate size first to ensure accurate measurements
      formMap.invalidateSize();
      maxVisibleBounds = formMap.getBoundsZoom(sharedMapBounds);
      // Set minimum zoom level to prevent users from zooming out too far
      formMap.setMinZoom(maxVisibleBounds);
    } else {
      maxVisibleBounds = formMap.getBoundsZoom(boundsToUse);
    }

    // Add moveend event to enforce bounds
    formMap.on("moveend", function () {
      if (isAdjusting) return;
      isAdjusting = true;

      const zoom = formMap.getZoom();

      // Only enforce bounds if we're zoomed in beyond the minimum zoom
      if (zoom > maxVisibleBounds) {
        const currentBounds = formMap.getBounds();
        const currentCenter = formMap.getCenter();

        let needsAdjustment = false;
        let newLat = currentCenter.lat;
        let newLng = currentCenter.lng;

        // Calculate current viewport dimensions
        const viewportHeight = currentBounds.getNorth() - currentBounds.getSouth();
        const viewportWidth = currentBounds.getEast() - currentBounds.getWest();

        // Check and adjust latitude (north/south)
        if (currentBounds.getNorth() > boundsToUse.getNorth()) {
          newLat = boundsToUse.getNorth() - viewportHeight / 2;
          needsAdjustment = true;
        } else if (currentBounds.getSouth() < boundsToUse.getSouth()) {
          newLat = boundsToUse.getSouth() + viewportHeight / 2;
          needsAdjustment = true;
        }

        // Check and adjust longitude (east/west)
        if (currentBounds.getEast() > boundsToUse.getEast()) {
          newLng = boundsToUse.getEast() - viewportWidth / 2;
          needsAdjustment = true;
        } else if (currentBounds.getWest() < boundsToUse.getWest()) {
          newLng = boundsToUse.getWest() + viewportWidth / 2;
          needsAdjustment = true;
        }

        if (needsAdjustment) {
          formMap.setView([newLat, newLng], zoom, { animate: false });
        }
      }

      isAdjusting = false;
    });

    // Update minZoom when fixed bounds are enabled
    // This prevents users from zooming out too far
    updateMinZoom();

    isInitialized = true;
  }

  function updateMinZoom() {
    if (!formMap) return;
    
    // Recalculate and set minZoom when fixed bounds are enabled
    // This prevents users from zooming out beyond the defined bounds
    if (oum_enable_fixed_map_bounds && sharedMapBounds) {
      formMap.invalidateSize();
      const maxVisibleBounds = formMap.getBoundsZoom(sharedMapBounds);
      formMap.setMinZoom(maxVisibleBounds);
    }
  }

  function setupTileLayer() {
    // Default to OpenStreetMap if mapStyle is undefined
    const mapStyle = window.mapStyle || 'OpenStreetMap.Mapnik';

    if (mapStyle === "Custom1") {
      L.tileLayer("https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}.png").addTo(formMap);
      L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png", {
        tileSize: 512,
        zoomOffset: -1,
      }).addTo(formMap);
    } else if (mapStyle === "Custom2") {
      L.tileLayer("https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png").addTo(formMap);
      L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png", {
        tileSize: 512,
        zoomOffset: -1,
      }).addTo(formMap);
    } else if (mapStyle === "Custom3") {
      L.tileLayer("https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png").addTo(formMap);
      L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png", {
        tileSize: 512,
        zoomOffset: -1,
      }).addTo(formMap);
    } else if (mapStyle.startsWith("MapBox.")) {
      L.tileLayer.provider("MapBox", {
        id: mapStyle.replace("MapBox.", "mapbox/") + (mapStyle.includes("-v") ? "" : "-v12"),
        accessToken: OUMConfig.getTileProviderKey(),
      }).addTo(formMap);
    } else if (mapStyle == "CustomImage") {
      // Custom Image layer
      setupCustomImageLayer();
      // Always add a base tile layer for proper map functionality
      // If hide tiles is enabled, use a transparent/invisible layer
      if (window.oum_custom_image_hide_tiles) {
        // Use a transparent tile layer to maintain map functionality
        L.tileLayer('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', {
          attribution: '',
          opacity: 0
        }).addTo(formMap);
        
        // Apply background color to map container
        if (window.oum_custom_image_background_color) {
          formMap.getContainer().style.backgroundColor = window.oum_custom_image_background_color;
        }
      } else {
        L.tileLayer.provider("OpenStreetMap.Mapnik").addTo(formMap);
      }
    } else {
      // Default
      L.tileLayer.provider(mapStyle).addTo(formMap);
    }
  }

  function setupControls() {
    // Add searchbar: address
    const search = new GeoSearch.GeoSearchControl({
      style: "bar",
      showMarker: false,
      provider: OUMUtils.initGeosearchProvider(),
      searchLabel: oum_searchaddress_label,
      updateMap: false,
    });
    formMap.addControl(search);

    // Add control: get current location
    if (oum_enable_currentlocation) {
      window.formMap_locate_process = L.control.locate({
        flyTo: true,
        showPopup: false,
      }).addTo(formMap);
    }
  }

  function setupMarker() {
    // Marker Icon
    let markerIcon = L.icon({
      iconUrl: marker_icon_url,
      iconSize: [26, 41],
      iconAnchor: [13, 41],
      popupAnchor: [0, -25],
      shadowUrl: marker_shadow_url,
      shadowSize: [41, 41],
      shadowAnchor: [13, 41],
    });

    locationMarker = L.marker([0, 0], {
      icon: markerIcon,
      draggable: true,
    });

    // Make marker globally available (for backward compatibility)
    window.locationMarker = locationMarker;
    window.markerIsVisible = markerIsVisible;

    // Event: drag marker
    locationMarker.on("dragend", function (e) {
      setLocationLatLng(e.target.getLatLng());
    });
  }

  function setupMapEvents() {
    // Event: click on map to set marker OR location found
    formMap.on("click locationfound", function (e) {
      let coords = e.latlng;
      locationMarker.setLatLng(coords);

      if (!markerIsVisible) {
        locationMarker.addTo(formMap);
        markerIsVisible = true;
        window.markerIsVisible = true;
      }

      setLocationLatLng(coords);
    });

    // Event: geosearch success
    formMap.on("geosearch/showlocation", handleGeosearchSuccess);
  }

  function handleGeosearchSuccess(e) {
    let coords = e.marker._latlng;
    let label = e.location.label;
    const searchBar = document.querySelector(`#mapGetLocation .leaflet-geosearch-bar form`);

    // Check against fixed map bounds if enabled, otherwise allow any location
    let isInBounds = true; // Default to true when fixed bounds is disabled
    if (oum_enable_fixed_map_bounds === 'on' && sharedMapBounds) {
      // Check if location is within the fixed map bounds (not just current viewport)
      isInBounds = sharedMapBounds.contains(coords);
    }

    // Only restrict movement if fixed map bounds is enabled and location is outside those bounds
    if (!isInBounds && oum_enable_fixed_map_bounds === 'on') {
      console.log("This search result is out of reach.");
      if (searchBar) {
        searchBar.style.boxShadow = "0 0 10px rgb(255, 111, 105)";
        setTimeout(function () {
          searchBar.style.boxShadow = "0 1px 5px rgba(255, 255, 255, 0.65)";
        }, 2000);
      }
    } else {
      // Location is within fixed bounds (or fixed bounds is disabled) - handle normally
      // Set coordinates and address immediately (before map animation) for instant feedback
      setLocationLatLng(coords, label);
      
      // Set marker position
      locationMarker.setLatLng(coords);

      if (!markerIsVisible) {
        locationMarker.addTo(formMap);
        markerIsVisible = true;
        window.markerIsVisible = true;
      }

      // Fly to location (animated - happens after address is already set)
      handleValidGeosearchResult(e.location);
    }
  }

  function handleValidGeosearchResult(location) {
    if (location.bounds !== null) {
      formMap.flyToBounds(location.bounds);
    } else if (location.raw.mapView) {
      formMap.flyToBounds([
        [location.raw.mapView.south, location.raw.mapView.west],
        [location.raw.mapView.north, location.raw.mapView.east],
      ]);
    } else {
      formMap.flyTo([location.y, location.x], 17);
    }
  }

  function setLocationLatLng(markerLatLng, address) {
    document.getElementById("oum_location_lat").value = markerLatLng.lat;
    document.getElementById("oum_location_lng").value = markerLatLng.lng;
    
    // Only perform reverse geocoding if both subtitle field and autofill are enabled
    if (shouldPerformReverseGeocoding()) {
      reverseGeocode(markerLatLng.lat, markerLatLng.lng, address);
    }
  }

  /**
   * Check if reverse geocoding should be performed
   * Requires both subtitle field and autofill to be enabled
   * @returns {boolean}
   */
  function shouldPerformReverseGeocoding() {
    // Check if subtitle field is enabled (use window prefix for safety)
    const subtitleEnabled = typeof window.oum_enable_address !== 'undefined' && window.oum_enable_address === 'on';
    
    // Check if autofill is enabled (use window prefix for safety)
    const autofillEnabled = typeof window.oum_enable_address_autofill !== 'undefined' && window.oum_enable_address_autofill === 'on';
    
    return subtitleEnabled && autofillEnabled;
  }

  /**
   * Format address from Nominatim API response
   * @param {Object} addressData - Address data from Nominatim API
   * @param {string} displayName - Fallback display name
   * @returns {string}
   */
  function formatAddressFromResponse(addressData, displayName) {
    const addressParts = [];
    
    // Build address from most specific to least specific
    if (addressData.house_number) addressParts.push(addressData.house_number);
    if (addressData.road) addressParts.push(addressData.road);
    if (addressData.suburb) addressParts.push(addressData.suburb);
    if (addressData.city || addressData.town || addressData.village) {
      addressParts.push(addressData.city || addressData.town || addressData.village);
    }
    if (addressData.postcode) addressParts.push(addressData.postcode);
    if (addressData.country) addressParts.push(addressData.country);
    
    return addressParts.length > 0 
      ? addressParts.join(', ') 
      : displayName || 'Address not found';
  }

  /**
   * Reverse geocode coordinates to get address
   * Uses OpenStreetMap Nominatim API (free, no API key required)
   * @param {number} lat - Latitude
   * @param {number} lng - Longitude
   * @param {string} [providedAddress] - Optional address to use directly (e.g., from geosearch result)
   */
  function reverseGeocode(lat, lng, providedAddress) {
    // If address is already provided (e.g., from geosearch), use it directly
    if (providedAddress && providedAddress.trim()) {
      handleAddressResult(providedAddress.trim());
      return;
    }

    // Validate coordinates
    if (!OUMUtils.validateCoordinates(lat, lng)) {
      console.warn('Open User Map: Invalid coordinates for reverse geocoding');
      return;
    }

    // Use Nominatim reverse geocoding API
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
    
    fetch(url, {
      headers: {
        'User-Agent': 'OpenUserMap/1.0' // Required by Nominatim
      }
    })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data && data.address) {
          const address = formatAddressFromResponse(data.address, data.display_name);
          handleAddressResult(address);
        } else {
          console.log('Open User Map: No address found for coordinates:', lat, lng);
        }
      })
      .catch(error => {
        console.warn('Open User Map: Reverse geocoding error:', error);
      });
  }

  /**
   * Handle the address result - log it and optionally fill subtitle field
   * @param {string} address - The formatted address
   */
  function handleAddressResult(address) {
    if (!address || typeof address !== 'string') {
      return;
    }

    console.log('Open User Map: Address:', address);
    
    // Auto-fill subtitle field if enabled (always update when address changes)
    if (shouldPerformReverseGeocoding()) {
      const subtitleField = document.getElementById('oum_location_address');
      if (subtitleField) {
        subtitleField.value = address;
      }
    }
  }

  // Helper function to setup custom image layer
  function setupCustomImageLayer() {
    // Check if we have an image URL and bounds
    if (typeof window.oum_custom_image_url !== 'undefined' && window.oum_custom_image_url && 
        typeof window.oum_custom_image_bounds !== 'undefined' && window.oum_custom_image_bounds) {
      
      // Check if the uploaded file is an SVG
      const isSVG = window.oum_custom_image_url.toLowerCase().includes('.svg');
      
      if (isSVG) {
        // Handle SVG file - fetch and render as DOM elements
        setupSVGFromFile();
      } else {
        // Handle regular image file
        setupImageOverlay();
      }
    } else {
    }
  }

  // Helper function to setup SVG from uploaded file
  function setupSVGFromFile() {
    try {
      // Get bounds data (now properly handled as object)
      const bounds = window.oum_custom_image_bounds;

      // Validate bounds
      if (!bounds || typeof bounds.north === 'undefined' || typeof bounds.south === 'undefined' ||
          typeof bounds.east === 'undefined' || typeof bounds.west === 'undefined' ||
          bounds.north === '' || bounds.south === '' || bounds.east === '' || bounds.west === '') {
        console.warn('Open User Map: Invalid or empty bounds data, skipping SVG file layer');
        return;
      }


      // Fetch the SVG file and render it
      fetch(window.oum_custom_image_url)
        .then(response => response.text())
        .then(svgText => {
          // Create SVG element from the fetched content
          const svgElement = createSVGElement(svgText);
          if (!svgElement) {
            console.warn('Open User Map: Cannot create SVG layer from file - invalid SVG element');
            return;
          }

          // Create a custom SVG layer
          const svgLayer = L.svgOverlay(svgElement, [
            [bounds.north, bounds.west], // Southwest corner
            [bounds.south, bounds.east]  // Northeast corner
          ], {
            opacity: 1.0,
            interactive: true
          });

          svgLayer.addTo(formMap);


          // Store reference for potential removal
          window.oumCustomSVGLayer = svgLayer;

          console.log('Open User Map: Custom SVG file layer added successfully');
        })
        .catch(error => {
          console.warn('Open User Map: Error fetching SVG file:', error);
        });

    } catch (error) {
      console.warn('Open User Map: Error setting up custom SVG file layer:', error);
    }
  }

  // Helper function to setup regular image overlay
  function setupImageOverlay() {
    try {
      // Get bounds data (now properly handled as object)
      const bounds = window.oum_custom_image_bounds;

      // Validate bounds
      if (!bounds || typeof bounds.north === 'undefined' || typeof bounds.south === 'undefined' ||
          typeof bounds.east === 'undefined' || typeof bounds.west === 'undefined' ||
          bounds.north === '' || bounds.south === '' || bounds.east === '' || bounds.west === '') {
        console.warn('Open User Map: Invalid or empty bounds data, skipping image layer');
        return;
      }


      // Create image overlay
      const imageOverlay = L.imageOverlay(window.oum_custom_image_url, [
        [bounds.north, bounds.west], // Southwest corner
        [bounds.south, bounds.east]  // Northeast corner
      ], {
        opacity: 1.0,
        interactive: true
      });

      imageOverlay.addTo(formMap);


      // Store reference for potential removal
      window.oumCustomImageLayer = imageOverlay;

      console.log('Open User Map: Custom image layer added successfully');

    } catch (error) {
      console.warn('Open User Map: Error setting up custom image layer:', error);
    }
  }

  // Helper function to create SVG element from text
  function createSVGElement(svgText) {
    // Create a temporary div to parse the SVG
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = svgText;
    const svgElement = tempDiv.querySelector('svg');
    
    if (!svgElement) {
      console.warn('Open User Map: No valid SVG element found in SVG text');
      return null;
    }
    
    // Preserve the original viewBox if it exists
    // If missing, try to create it from width/height attributes
    if (!svgElement.getAttribute('viewBox')) {
      const width = svgElement.getAttribute('width');
      const height = svgElement.getAttribute('height');
      if (width && height) {
        // Remove units if present (e.g., "1580px" -> "1580")
        const widthNum = parseFloat(width);
        const heightNum = parseFloat(height);
        if (!isNaN(widthNum) && !isNaN(heightNum)) {
          svgElement.setAttribute('viewBox', `0 0 ${widthNum} ${heightNum}`);
        } else {
          svgElement.setAttribute('viewBox', '0 0 1000 1200');
        }
      } else {
        svgElement.setAttribute('viewBox', '0 0 1000 1200');
      }
    }
    
    // Ensure the SVG has proper styling for overlay
    svgElement.style.width = '100%';
    svgElement.style.height = '100%';
    svgElement.style.display = 'block';
    
    // Ensure the SVG fills the entire bounds area to prevent cropping
    svgElement.setAttribute('preserveAspectRatio', 'none');
    
    console.log('Open User Map: SVG element created successfully:', svgElement);
    
    return svgElement;
  }

  // Public interface
  return {
    init: function() {
      if (!document.getElementById("mapGetLocation")) {
        return;
      }
      initializeFormMap();
    },
    setLocation: function(lat, lng) {
      if (!locationMarker) return;
      locationMarker.setLatLng([lat, lng]);
      if (!markerIsVisible) {
        locationMarker.addTo(formMap);
        markerIsVisible = true;
        window.markerIsVisible = true;
      }
      setLocationLatLng({lat, lng});
    },
    clearMarker: function() {
      if (locationMarker && formMap) {
        locationMarker.remove();
        markerIsVisible = false;
        window.markerIsVisible = false;
      }
    },
    invalidateSize: function() {
      if (formMap) {
        formMap.invalidateSize();
      }
    },
    updateMinZoom: function() {
      updateMinZoom();
    },
    getMap: function() {
      return formMap;
    },
    setView: function(lat, lng, zoom) {
      if (formMap) {
        formMap.setView([lat, lng], zoom);
      }
    },
    setViewToMatchMainMap: function(mainMap) {
      if (!formMap || !mainMap) return;
      
      // Invalidate size to ensure accurate measurements
      formMap.invalidateSize();
      
      // Get the main map's bounds (the geographic area currently visible)
      const mainBounds = mainMap.getBounds();
      const mainCenter = mainMap.getCenter();
      const mainZoom = mainMap.getZoom();
      
      // Get form map container size
      const formMapContainer = formMap.getContainer();
      const formMapWidth = formMapContainer.offsetWidth || 520;
      const formMapHeight = formMapContainer.offsetHeight || 294;
      
      // Get main map container size
      const mainMapContainer = mainMap.getContainer();
      const mainMapWidth = mainMapContainer.offsetWidth || 520;
      const mainMapHeight = mainMapContainer.offsetHeight || 294;
      
      // Calculate aspect ratios
      const mainAspectRatio = mainMapWidth / mainMapHeight;
      const formAspectRatio = formMapWidth / formMapHeight;
      const aspectRatioDifference = Math.abs(mainAspectRatio - formAspectRatio);
      
      let targetZoom;
      
      // If aspect ratios match, use ratio-based zoom calculation
      if (aspectRatioDifference < 0.01) {
        // Calculate zoom adjustment based on size ratio
        const widthRatio = formMapWidth / mainMapWidth;
        const zoomAdjustment = Math.log2(widthRatio);
        targetZoom = mainZoom + zoomAdjustment;
      } else {
        // Different aspect ratios - use getBoundsZoom to fit the main map's bounds
        targetZoom = formMap.getBoundsZoom(mainBounds, false);
      }
      
      // Set view with calculated zoom to show the same geographic area
      formMap.setView([mainCenter.lat, mainCenter.lng], targetZoom, {
        animate: false
      });
    }
  };
})();

/**
 * Form Controller Module - Handles all form-related functionality
 */
const OUMFormController = (function () {
  // Private variables
  let isEditMode = false;
  let currentLocationId = null;
  let selectedFiles = [];

  // Private functions
  function showFormMessage(type, headline, message, buttonText = null, buttonCallback = null) {
    const form = document.getElementById('oum_add_location');
    const errorDiv = document.getElementById('oum_add_location_error');
    const thankyouDiv = document.getElementById('oum_add_location_thankyou');
    
    if (!form || !errorDiv || !thankyouDiv) {
      console.error('Required form elements not found');
      return;
    }
    
    // Hide form and error
    form.style.display = 'none';
    errorDiv.style.display = 'none';
    
    // Update thank you message
    const headlineEl = thankyouDiv.querySelector('h3');
    const messageEl = thankyouDiv.querySelector('.oum-add-location-thankyou-text');
    const buttonEl = thankyouDiv.querySelector('button');

    if (!headlineEl || !messageEl || !buttonEl) {
      // Create elements if they don't exist
      if (!headlineEl) {
        const newHeadline = document.createElement('h3');
        thankyouDiv.appendChild(newHeadline);
      }
      if (!messageEl) {
        const newMessage = document.createElement('p');
        newMessage.className = 'oum-add-location-thankyou-text';
        thankyouDiv.appendChild(newMessage);
      }
      if (!buttonEl) {
        const newButton = document.createElement('button');
        thankyouDiv.appendChild(newButton);
      }
    }

    // Get elements again (they should exist now)
    const finalHeadlineEl = thankyouDiv.querySelector('h3');
    const finalMessageEl = thankyouDiv.querySelector('.oum-add-location-thankyou-text');
    const finalButtonEl = thankyouDiv.querySelector('button');
    
    // Add specific class for delete confirmation
    thankyouDiv.className = type === 'confirm-delete' ? 'oum-delete-confirmation' : '';
    
    if (finalHeadlineEl) finalHeadlineEl.textContent = headline || '';
    if (finalMessageEl) finalMessageEl.textContent = message || '';
    
    // Handle button
    if (finalButtonEl) {
      if (buttonText && buttonCallback) {
        finalButtonEl.textContent = buttonText;
        finalButtonEl.onclick = buttonCallback;
        finalButtonEl.style.display = 'inline-block';
      } else {
        finalButtonEl.style.display = 'none';
      }
    }
    
    thankyouDiv.style.display = 'block';
  }

  function setupDeleteButton() {
    const deleteBtn = document.getElementById('oum_delete_location_btn');
    if (deleteBtn) {
      deleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Show confirmation using the message system
        showFormMessage(
          'confirm-delete',
          oum_custom_strings.delete_location,
          oum_custom_strings.delete_location_message,
          oum_custom_strings.delete_location_button,
          function() {
            // Set delete flag
            document.getElementById('oum_delete_location').value = 'true';
            
            // Get the form
            const form = document.getElementById('oum_add_location');
            const formData = new FormData(form);
            formData.append('action', 'oum_add_location_from_frontend');

            // Submit via AJAX
            jQuery.ajax({
              type: 'POST',
              url: oum_ajax.ajaxurl,
              cache: false,
              contentType: false,
              processData: false,
              data: formData,
              success: function(response) {
                if (response.success) {
                  showFormMessage(
                    'success',
                    oum_custom_strings.location_deleted,
                    oum_custom_strings.delete_success,
                    oum_custom_strings.close_and_refresh,
                    function() {
                      window.location.reload();
                    }
                  );
                } else {
                  oumShowError(response.data);
                }
              },
              error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(errorThrown);
                oumShowError([{message: oum_custom_strings.delete_error}]);
              }
            });
          }
        );
      });
    }
  }

  function setupFormEvents() {
    // Event: click on "+ Add Location" button
    const addLocationBtn = document.getElementById("open-add-location-overlay");
    if (addLocationBtn) {
      addLocationBtn.addEventListener("click", handleAddLocationClick);
    }

    // Event: click on "Edit Location" button
    document.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('edit-location-button')) {
        e.preventDefault();
        const locationId = e.target.getAttribute('data-post-id');
        const location = window.oum_all_locations.find(loc => loc.post_id === locationId);
        
        if (location) {
          resetForm();
          openForm(location);
        }
      }
    });

    setupCloseEvents();
    setupNotificationEvents();
    setupMediaEvents();
  }

  /**
   * Request a fresh form nonce so cached pages stay valid.
   *
   * We refresh when the overlay opens, ensuring the submission
   * uses a nonce generated in the current time window.
   */
  function refreshLocationNonce() {
    const formEl = document.getElementById('oum_add_location');
    if (!formEl || typeof oum_ajax === 'undefined') {
      return Promise.resolve();
    }

    const nonceField = formEl.querySelector('input[name="oum_location_nonce"]');
    if (!nonceField || !oum_ajax.ajaxurl) {
      return Promise.resolve();
    }

    const action = oum_ajax.refresh_nonce_action || 'oum_refresh_location_nonce';

    return jQuery.ajax({
      type: 'POST',
      url: oum_ajax.ajaxurl,
      dataType: 'json',
      data: { action },
    }).done(function(response) {
      if (response && response.success && response.data && response.data.nonce) {
        nonceField.value = response.data.nonce;
      }
    }).fail(function() {
      // Keep backward compatibility: fall back to the existing nonce if refresh fails.
      console.warn('Open User Map: Unable to refresh nonce.');
    });
  }

  function handleAddLocationClick() {
    resetForm();
    openForm();
  }
  
  function openForm(location = null) {
    // Refresh nonce on every open to avoid cached/expired tokens.
    refreshLocationNonce();
    document.querySelector(".add-location").classList.add("active");
    document.body.classList.add("oum-add-location-opened");

    setTimeout(function () {
      // Initialize map if needed
      OUMFormMap.init();
      
      // Update minZoom after form opens to ensure it's set correctly when fixed bounds are enabled
      // Use a small delay to ensure the map container is properly sized
      setTimeout(function() {
        OUMFormMap.updateMinZoom();
      }, 100);

      // Apply main map's aspect ratio to form map
      const applyAspectRatioAndView = () => {
        const mainMapEl = document.querySelector(`#${map_el}`);
        if (mainMapEl) {
          const mainMap = window.oumMap;
          if (mainMap) {
            const mainMapContainer = mainMap.getContainer();
            const mainMapWidth = mainMapContainer.offsetWidth;
            const mainMapHeight = mainMapContainer.offsetHeight;
            
            if (mainMapWidth && mainMapHeight) {
              // Calculate aspect ratio from main map
              const aspectRatio = mainMapWidth / mainMapHeight;
              
              // Apply aspect ratio to form map container
              const formMapWrap = document.querySelector('.add-location .location-overlay-content .map-wrap');
              if (formMapWrap) {
                formMapWrap.style.aspectRatio = aspectRatio.toString();
                formMapWrap.style.height = 'auto';
              }
            }
          }
        }
        
        if (location) {
          populateForm(location);
        } else {
          // Set view to match main map's geographic bounds
          const mainMapEl = document.querySelector(`#${map_el}`);
          if (mainMapEl) {
            const mainMap = window.oumMap;
            // Use the new method that calculates zoom based on form map size
            // This ensures the same geographic area is visible regardless of map size differences
            OUMFormMap.setViewToMatchMainMap(mainMap);
          }
        }
      };
      
      // Wait for container to resize after applying aspect ratio
      applyAspectRatioAndView();
      requestAnimationFrame(applyAspectRatioAndView);

      // Add a separate timeout for invalidateSize to ensure accurate measurements
      setTimeout(() => {
        OUMFormMap.invalidateSize();
        // Re-apply the view after size invalidation to ensure correct zoom
        if (!location) {
          const mainMapEl = document.querySelector(`#${map_el}`);
          if (mainMapEl) {
            const mainMap = window.oumMap;
            OUMFormMap.setViewToMatchMainMap(mainMap);
          }
        }
      }, 200);
    }, 150);
  }

  function setupCloseEvents() {
    const closeBtn = document.getElementById("close-add-location-overlay");
    if (!closeBtn) return;

    // Close button click
    closeBtn.addEventListener("click", closeForm);

    // ESC key
    document.addEventListener("keydown", function(evt) {
      evt = evt || window.event;
      if (evt.key === "Escape" && document.getElementById("add-location-overlay").classList.contains("active")) {
        closeForm();
      }
    });

    // Backdrop click
    document.getElementById("add-location-overlay").addEventListener("click", function(event) {
      if (event.target === this) {
        closeForm();
      }
    });
  }

  function closeForm() {
    const addLocationOverlay = document.getElementById("add-location-overlay");
    if (addLocationOverlay) {
      addLocationOverlay.classList.remove("active");
    }

    // Stop locate process
    if (window.formMap_locate_process) {
      window.formMap_locate_process.stop();
    }

    // Allow body scrolling
    document.querySelector("body").classList.remove("oum-add-location-opened");

    // Reset form and clear marker
    resetForm();
    OUMFormMap.clearMarker();
  }

  function setupNotificationEvents() {
    const notificationCheckbox = document.getElementById("oum_location_notification");
    if (notificationCheckbox) {
      notificationCheckbox.addEventListener("change", function() {
        const authorFields = document.getElementById("oum_author");
        const nameField = document.getElementById("oum_location_author_name");
        const emailField = document.getElementById("oum_location_author_email");

        if (this.checked) {
          authorFields.classList.add("active");
          nameField.required = true;
          emailField.required = true;
        } else {
          authorFields.classList.remove("active");
          nameField.required = false;
          emailField.required = false;
        }
      });
    }
  }

  function setupMediaEvents() {
    // Image upload
    const imageInput = document.getElementById("oum_location_images");
    if (imageInput) {
      // Let OUMMedia handle the image upload
      OUMMedia.initializeImageUpload(imageInput);
    }

    // Remove image button
    const removeImageBtn = document.getElementById("oum_remove_image");
    if (removeImageBtn) {
      removeImageBtn.addEventListener("click", function() {
        document.getElementById("oum_location_images_preview").innerHTML = "";
      });
    }

    // Remove audio button
    const removeAudioBtn = document.getElementById("oum_remove_audio");
    if (removeAudioBtn) {
      removeAudioBtn.addEventListener("click", function() {
        const audioInput = document.getElementById("oum_location_audio");
        const previewContainer = audioInput.nextElementSibling;
        const previewDiv = previewContainer.querySelector('.audio-preview');
        
        // Clear the file input
        audioInput.value = "";
        
        // Clear the preview
        if (previewDiv) {
          previewDiv.innerHTML = '';
        }
        
        // Remove active state
        previewContainer.classList.remove("active");
        
        // Set remove flag
        document.getElementById("oum_remove_existing_audio").value = "1";
      });
    }
  }

  function setupCategoryDropdownIcons() {
    // Handle dropdown with icons for marker categories
    const categorySelect = document.querySelector('select#oum_marker_icon');
    if (categorySelect) {
      // Create a custom dropdown wrapper
      const wrapper = document.createElement('div');
      wrapper.className = 'oum-category-dropdown-wrapper';
      wrapper.style.position = 'relative';
      
      // Insert wrapper before the select
      categorySelect.parentNode.insertBefore(wrapper, categorySelect);
      wrapper.appendChild(categorySelect);
      
      // Create custom dropdown display
      const display = document.createElement('div');
      display.className = 'oum-category-dropdown-display';
      
      // Create icon element
      const icon = document.createElement('img');
      icon.className = 'oum-category-dropdown-icon';
      
      // Create text element
      const text = document.createElement('span');
      text.className = 'oum-category-dropdown-text';
      
      display.appendChild(icon);
      display.appendChild(text);
      
      // Insert display before select
      wrapper.insertBefore(display, categorySelect);
      
      // Make select invisible but still functional
      categorySelect.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 1;
      `;
      
      // Update display when selection changes
      function updateDisplay() {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
          const iconUrl = selectedOption.getAttribute('data-icon');
          if (iconUrl) {
            icon.src = iconUrl;
            icon.style.display = 'block';
          } else {
            icon.style.display = 'none';
          }
          text.textContent = selectedOption.textContent;
        } else {
          icon.style.display = 'none';
          text.textContent = 'Select a category...';
        }
      }
      
      // Initial display update
      updateDisplay();
      
      // Handle selection change
      categorySelect.addEventListener('change', updateDisplay);

      
      // Make display focusable for accessibility
      display.setAttribute('tabindex', '0');
      
      // Handle keyboard navigation
      display.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          categorySelect.focus();
          categorySelect.click();
        }
      });
    }
  }

  function populateForm(location) {
    isEditMode = true;
    currentLocationId = location.post_id;

    // Add edit-location class
    const addLocationEl = document.querySelector(".add-location");
    if (addLocationEl) {
        addLocationEl.classList.add("edit-location");
    }

    // Set post_id
    const postIdField = document.getElementById("oum_post_id");
    if (postIdField) {
      postIdField.value = location.post_id;
    }

    // Basic fields
    const titleField = document.getElementById("oum_location_title");
    const latField = document.getElementById("oum_location_lat");
    const lngField = document.getElementById("oum_location_lng");
    const addressField = document.getElementById("oum_location_address");

    if (titleField) titleField.value = location.title || "";
    if (latField) latField.value = location.lat || "";
    if (lngField) lngField.value = location.lng || "";
    if (addressField) addressField.value = location.address || "";
    
    // Marker types/categories
    if (location.types && Array.isArray(location.types)) {
        if (typeof oum_enable_multiple_marker_types !== 'undefined' && oum_enable_multiple_marker_types == 'true') {
            // Handle multiple marker types (checkboxes)
            const checkboxes = document.querySelectorAll('input[name="oum_marker_icon[]"]');
            if (checkboxes.length > 0) {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = location.types.includes(checkbox.value);
                });
            }
        } else {
            // Handle single marker type (select)
            const markerSelect = document.querySelector('select#oum_marker_icon');
            if (markerSelect) {
                markerSelect.value = location.types[0] || '';
                
                // Update custom dropdown display if it exists
                const customDisplay = document.querySelector('.oum-category-dropdown-display');
                if (customDisplay) {
                    // Trigger the change event to update the display
                    markerSelect.dispatchEvent(new Event('change'));
                }
            }
        }
    }

    // Description
    const descriptionField = document.getElementById("oum_location_text");
    if (descriptionField) {
        descriptionField.value = location.text || "";
    }

    // Video field
    const videoField = document.getElementById("oum_location_video");
    if (videoField && location.video) {
        videoField.value = location.video;
    }

    // Handle custom fields
    if (location.custom_fields && Array.isArray(location.custom_fields)) {
        location.custom_fields.forEach(field => {
            if (!field || typeof field.index === 'undefined') return;

            if (field.fieldtype === 'checkbox') {
                const fieldValues = Array.isArray(field.val) ? field.val : [field.val];
                const checkboxes = document.querySelectorAll(
                    `input[type="checkbox"][name="oum_location_custom_fields[${field.index}][]"]`
                );
                
                if (checkboxes.length > 0) {
                    checkboxes.forEach(checkbox => {
                        if (checkbox) {
                            const checkboxValue = checkbox.value.trim();
                            const normalizedValues = fieldValues.map(val => (val || '').toString().trim());
                            checkbox.checked = normalizedValues.includes(checkboxValue);
                        }
                    });
                }
            } else if (field.fieldtype === 'radio') {
                const radioInputs = document.querySelectorAll(
                    `input[type="radio"][name="oum_location_custom_fields[${field.index}]"]`
                );
                
                if (radioInputs.length > 0) {
                    radioInputs.forEach(radio => {
                        if (radio) {
                            radio.checked = radio.value === field.val;
                        }
                    });
                }
            } else if (field.fieldtype === 'select') {
                // Handle single and multiple select
                let input = document.querySelector(`[name="oum_location_custom_fields[${field.index}][]"]`);
                const isMultiple = !!input;
                if (!input) {
                    input = document.querySelector(`[name="oum_location_custom_fields[${field.index}]"]`);
                }
                if (input && input.tagName === 'SELECT') {
                    const values = Array.isArray(field.val) ? field.val.map(v => (v || '').toString().trim()) : [(field.val || '').toString().trim()];
                    Array.from(input.options).forEach(opt => {
                        opt.selected = values.includes((opt.value || '').toString().trim());
                    });
                }
            } else if (field.fieldtype === 'opening_hours') {
                // Handle opening hours field - expect JSON string
                try {
                    const openingHoursData = JSON.parse(field.val);
                    if (openingHoursData && typeof openingHoursData === 'object') {
                        // Find the hours input
                        const hoursInput = document.querySelector(`input[name="oum_location_custom_fields[${field.index}][hours]"]`);
                        const fieldContainer = document.querySelector(`[data-custom-field-index="${field.index}"]`);
                        
                        // Check if 12-hour format is enabled for this field
                        const use12hour = fieldContainer && fieldContainer.dataset.use12hour === '1';
                        
                        if (hoursInput && typeof OUMOpeningHours !== 'undefined') {
                            // Convert JSON back to input format (with 12-hour format if enabled)
                            hoursInput.value = OUMOpeningHours.formatForInput(openingHoursData, use12hour);
                        }
                    }
                } catch (e) {
                    // Invalid JSON, try to set as raw value
                    const input = document.querySelector(`input[name="oum_location_custom_fields[${field.index}][hours]"]`);
                    if (input) {
                        input.value = field.val || '';
                    }
                }
            } else {
                const input = document.querySelector(`[name="oum_location_custom_fields[${field.index}]"]`);
                if (input) {
                    input.value = field.val || '';
                }
            }
        });
    }

    // Handle images
    if (location.image) {
      const imageUrls = location.image.split('|').filter(url => url);
      const previewContainer = document.getElementById('oum_location_images_preview');
      
      // Remove required attribute from image upload field when editing with existing images
      const imageInput = document.getElementById('oum_location_images');
      if (imageInput && imageUrls.length > 0) {
        imageInput.removeAttribute('required');
      }
      
      if (previewContainer) {
        previewContainer.innerHTML = '';
        
        imageUrls.forEach(url => {
          if (!url) return;
          
          const previewItem = document.createElement('div');
          previewItem.className = 'image-preview-item existing-image';
          previewItem.dataset.url = url;
          
          previewItem.innerHTML = `
            <img src="${url}" alt="Preview">
            <div class="remove-image" title="Remove image">&times;</div>
            <div class="drag-handle" title="Drag to reorder">⋮⋮</div>
            <input type="hidden" name="existing_images[]" value="${url}">
          `;
          
          // Add event listener for remove button
          const removeButton = previewItem.querySelector('.remove-image');
          if (removeButton) {
            removeButton.addEventListener('click', OUMMedia.handleRemoveImage);
          }
          
          // Set up drag and drop for existing images
          OUMMedia.setupDragAndDrop(previewItem);
          
          previewItem.style.opacity = "0";
          previewItem.style.transform = "scale(0.9)";
          previewItem.style.transition = "all 0.3s ease";
          previewItem.style.opacity = "1";
          previewItem.style.transform = "scale(1)";
          previewContainer.appendChild(previewItem);
        });
      }
    }

    // Handle audio
    if (location.audio) {
      OUMMedia.setExistingAudio(location.audio);
      document.getElementById("oum_remove_existing_audio").value = "0";
    }

    // Set map view to location
    if (location.lat && location.lng) {
      OUMFormMap.setView(location.lat, location.lng, 16);
      OUMFormMap.setLocation(location.lat, location.lng);
    }

    // Sync checkbox validation after form population
    OUMCheckboxValidation.syncAllGroups();
  }

  function resetForm() {
    isEditMode = false;
    currentLocationId = null;
    selectedFiles = [];
    window.oumSelectedFiles = [];
    
    const addLocationEl = document.querySelector(".add-location");
    if (addLocationEl) {
      addLocationEl.classList.remove("edit-location");
    }
    
    // Reset form and message system
    const form = document.getElementById("oum_add_location");
    const errorDiv = document.getElementById("oum_add_location_error");
    const thankyouDiv = document.getElementById("oum_add_location_thankyou");
    
    // Reset form if it exists
    if (form) {
      form.reset();
      form.style.display = 'block';
    }

    // Stop locate process
    if (window.map_locate_process) {
      window.map_locate_process.stop();
    }

    // Reset custom fields
    const customFields = document.querySelectorAll('[name^="oum_location_custom_fields"]');
    if (customFields) {
      customFields.forEach(field => {
        if (field.type === 'checkbox' || field.type === 'radio') {
          field.checked = false;
        } else if (field.type === 'select-one') {
          field.selectedIndex = 0;
        } else {
          field.value = '';
        }
      });
    }

    // Reset post_id field
    const postIdField = document.getElementById("oum_post_id");
    if (postIdField) {
      postIdField.value = "";
    }

    // Reset oum_delete_location field
    const deleteLocationField = document.getElementById("oum_delete_location");
    if (deleteLocationField) {
        deleteLocationField.value = "";
    }

    // Reset error message if it exists
    if (errorDiv) {
      errorDiv.style.display = 'none';
    }

    // Reset thank you message if it exists
    if (thankyouDiv) {
      thankyouDiv.style.display = 'none';
      thankyouDiv.classList.remove('oum-delete-confirmation');
    }

    // Reset image preview
    const previewContainer = document.getElementById("oum_location_images_preview");
    if (previewContainer) {
      previewContainer.innerHTML = "";
    }

    // Reset audio preview
    const audioInput = document.getElementById("oum_location_audio");
    if (audioInput) {
      const previewContainer = audioInput.nextElementSibling;
      const previewDiv = previewContainer.querySelector('.audio-preview');
      
      // Clear the file input
      audioInput.value = "";
      
      // Clear the preview
      if (previewDiv) {
        previewDiv.innerHTML = '';
      }
      
      // Remove active state
      previewContainer.classList.remove("active");
    }

    // Reset hidden fields
    const removeExistingImage = document.getElementById("oum_remove_existing_image");
    if (removeExistingImage) {
      removeExistingImage.value = "0";
    }

    const removeExistingAudio = document.getElementById("oum_remove_existing_audio");
    if (removeExistingAudio) {
      removeExistingAudio.value = "0";
    }

    // Reset author section
    const authorSection = document.getElementById("oum_author");
    if (authorSection) {
      authorSection.classList.remove("active");
    }

    // Reset name and email fields
    const nameField = document.getElementById("oum_location_author_name");
    const emailField = document.getElementById("oum_location_author_email");
    if (nameField && emailField) {
      nameField.required = false;
      emailField.required = false;
    }
  }

  // Public interface
  return {
    init: function() {
      setupFormEvents();
      setupDeleteButton(); // Add delete button handler
      setupCategoryDropdownIcons();
    },
    showFormMessage: showFormMessage,
    openForm: openForm,
    closeForm: closeForm,
    resetForm: resetForm,
    populateForm: populateForm,
    isEditMode: function() {
      return isEditMode;
    },
    getCurrentLocationId: function() {
      return currentLocationId;
    }
  };
})();

// OUMOpeningHours module is now in a separate file: frontend-opening-hours.js
// It's loaded as a dependency of this script

/**
 * Media Module - Handles image upload and preview functionality
 */
const OUMMedia = (function () {
  // Private variables
  let selectedFiles = [];
  let startX, startY, originalPosition, placeholder;
  let isDragging = false;
  
  // Private functions
  function initializeImageUpload(imageInput) {
    if (!imageInput) return;

    // Add click handler for upload icon label
    const uploadLabel = imageInput.parentElement.querySelector(
      'label[for="oum_location_images"]'
    );
    if (uploadLabel) {
      uploadLabel.addEventListener("click", function (e) {
        e.preventDefault();
        imageInput.click();
      });
    }

    // Setup drag and drop handlers
    document.addEventListener("mousemove", handleDragMove);
    document.addEventListener("mouseup", handleDragEnd);
    if (imageInput.parentElement.querySelector(".image-preview-container")) {
      imageInput.parentElement.querySelector(".image-preview-container").addEventListener("dragover", handleDragOver);
    }

    // Setup image input change handler
    imageInput.addEventListener("change", handleImageInputChange);
  }

  function initializeAudioUpload() {
    const audioInput = document.getElementById('oum_location_audio');
    if (!audioInput) return;

    // Add click handler for upload icon label
    const uploadLabel = audioInput.parentElement.querySelector(
      'label[for="oum_location_audio"]'
    );
    if (uploadLabel) {
      uploadLabel.addEventListener("click", function (e) {
        e.preventDefault();
        audioInput.click();
      });
    }

    // Setup audio input change handler
    audioInput.addEventListener("change", handleAudioInputChange);
  }

  function handleAudioInputChange(e) {
    const audioInput = e.target;
    const previewContainer = audioInput.nextElementSibling;
    const previewDiv = previewContainer.querySelector('.audio-preview');
    
    if (audioInput.files && audioInput.files[0]) {
      const file = audioInput.files[0];
      
      previewContainer.classList.add('active');

      // Create audio preview element
      const audio = document.createElement('audio');
      audio.controls = true;
      audio.style.width = '100%';
      
      const source = document.createElement('source');
      source.src = URL.createObjectURL(file);
      source.type = file.type;
      
      audio.appendChild(source);
      
      // Replace existing audio preview if any
      previewDiv.innerHTML = '';
      previewDiv.appendChild(audio);
    }
  }

  function setExistingAudio(audioUrl) {
    if (!audioUrl) return;

    const audioInput = document.getElementById('oum_location_audio');
    if (!audioInput) return;

    const previewContainer = audioInput.nextElementSibling;
    const previewDiv = previewContainer.querySelector('.audio-preview');
    
    previewContainer.classList.add('active');

    // Create audio preview element
    const audio = document.createElement('audio');
    audio.controls = true;
    audio.style.width = '100%';
    
    const source = document.createElement('source');
    source.src = audioUrl;
    source.type = 'audio/' + audioUrl.split('.').pop();
    
    audio.appendChild(source);
    
    // Replace existing audio preview if any
    previewDiv.innerHTML = '';
    previewDiv.appendChild(audio);
  }

  function handleImageInputChange(e) {
    const imageInput = e.target;
    const previewContainer = document.getElementById(
      "oum_location_images_preview"
    );
    const maxFiles = parseInt(imageInput.dataset.maxFiles) || 5;
    const maxFileSize = OUMConfig.defaults.media.maxImageSize; // in bytes
    
    // Convert FileList to Array and store in a variable
    const files = Array.prototype.slice.call(e.target.files);
    const existingCount = selectedFiles.length;
    const totalFiles = existingCount + files.length;
    
    if (totalFiles > maxFiles) {
      alert(
        OUMUtils.sprintf(
          oum_custom_strings.max_files_exceeded,
          maxFiles,
          maxFiles - existingCount
        )
      );
    }
    
    // Process only up to remaining slots
    const remainingSlots = maxFiles - existingCount;
    const filesToProcess = files.slice(0, remainingSlots);
    
    // Validate file sizes and collect valid files
    const validFiles = [];
    const invalidFiles = [];
    
    filesToProcess.forEach(file => {
      if (file.size > maxFileSize) {
        invalidFiles.push(file.name);
      } else {
        validFiles.push(file);
      }
    });
    
    // Show error message for invalid files
    if (invalidFiles.length > 0) {
      const maxSizeMB = Math.round(maxFileSize / (1024 * 1024));
      alert(
        OUMUtils.sprintf(
          oum_custom_strings.max_filesize_exceeded,
          maxSizeMB,
          invalidFiles.join('\n')
        )
      );
    }
    
    // Update selected files with only valid ones
    selectedFiles = [...selectedFiles, ...validFiles];
    
    // Create previews for valid files only
    createImagePreviews(validFiles, previewContainer);

    // Make selectedFiles available globally for the form submission
    window.oumSelectedFiles = selectedFiles;
  }

  function createImagePreviews(files, container) {
    files.forEach((file) => {
      const reader = new FileReader();
      
      reader.onload = function (e) {
        const previewItem = createPreviewItem(e.target.result, file.name);

        // Add the item with a fade-in animation
        previewItem.style.opacity = "0";
        previewItem.style.transform = "scale(0.9)";
        container.appendChild(previewItem);

        // Trigger animation after a brief delay
        setTimeout(() => {
          previewItem.style.transition = "all 0.3s ease";
          previewItem.style.opacity = "1";
          previewItem.style.transform = "scale(1)";
        }, 50);
      };

      reader.readAsDataURL(file);
    });
  }

  function createPreviewItem(imgSrc, fileName) {
    const previewItem = document.createElement("div");
    previewItem.className = "image-preview-item";
    previewItem.dataset.fileName = fileName;
    
    previewItem.innerHTML = `
      <img src="${imgSrc}" alt="Preview">
      <div class="remove-image" title="Remove image">&times;</div>
      <div class="drag-handle" title="Drag to reorder">⋮⋮</div>
    `;
    
    // Add event listener for remove button
    const removeButton = previewItem.querySelector('.remove-image');
    if (removeButton) {
      removeButton.addEventListener('click', handleRemoveImage);
    }
    
    // Set up drag and drop for existing images
    setupDragAndDrop(previewItem);

    return previewItem;
  }

  function handleRemoveImage(e) {
    e.preventDefault();
    const previewItem = this.closest(".image-preview-item");
    
    // If it's an existing image, handle removal differently
    if (previewItem.classList.contains("existing-image")) {
      const imgUrl = previewItem.querySelector("[name='existing_images[]']").value;
      const removedImagesInput = document.getElementById("oum_remove_existing_image");
      const currentValue = removedImagesInput.value === "0" ? [] : removedImagesInput.value.split('|');
      currentValue.push(imgUrl);
      removedImagesInput.value = currentValue.join('|');
    } else {
      // Remove from selectedFiles array if it's a new image
      const fileName = previewItem.dataset.fileName;
      selectedFiles = selectedFiles.filter(file => file.name !== fileName);
      window.oumSelectedFiles = selectedFiles;
    }

    // Animate and remove the preview item
    previewItem.style.transition = "all 0.3s ease";
    previewItem.style.transform = "scale(0.8)";
    previewItem.style.opacity = "0";
    
    setTimeout(() => {
      previewItem.remove();
    }, 300);
  }

  function setupDragAndDrop(previewItem) {
    previewItem.setAttribute('draggable', 'true');
    
    previewItem.addEventListener('mousedown', function(e) {
      if (e.target.classList.contains('remove-image')) return;

      isDragging = true;
      this.classList.add('dragging');

      // Get element dimensions once at start
      const rect = this.getBoundingClientRect();
      this.style.width = rect.width + 'px';
      this.style.height = rect.height + 'px';

      // Store initial grid container for safety check
      this.initialContainer = this.closest('.oum-image-preview-grid');

      // Create placeholder immediately
      createPlaceholder(this);
      
      // Set up dragged element
      this.style.position = 'fixed';
      this.style.zIndex = '1000';
      this.style.opacity = '0.9';
      this.style.transform = 'scale(1.05) rotate(1deg)';
      this.style.pointerEvents = 'none';
      this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.15)';

      // Set initial position
      moveDraggedElement(this, e);

      document.body.style.cursor = 'grabbing';
    });
    
    previewItem.addEventListener('touchstart', handleTouchStart);
  }

  function createPlaceholder(element) {
    placeholder = document.createElement("div");
    placeholder.classList.add("image-preview-placeholder");
    placeholder.style.width = element.offsetWidth + "px";
    placeholder.style.height = element.offsetHeight + "px";
    placeholder.style.transition = "transform 0.2s ease";
    placeholder.style.border = "2px dashed #e82c71";
    placeholder.style.borderRadius = "4px";
    placeholder.style.backgroundColor = "rgba(224, 42, 175, 0.05)";
    element.parentNode.insertBefore(placeholder, element);
  }

  function moveDraggedElement(draggable, e) {
    const rect = draggable.getBoundingClientRect();
    const centerOffsetX = rect.width / 2;
    const centerOffsetY = rect.height / 2;
    
    // Position element directly at cursor with center offset
    draggable.style.left = (e.clientX - centerOffsetX) + 'px';
    draggable.style.top = (e.clientY - centerOffsetY) + 'px';
  }

  function updatePlaceholderPosition(e) {
    const previewContainer = document.getElementById("oum_location_images_preview");
    if (!previewContainer) return;

    const draggable = document.querySelector('.dragging');
    if (!draggable) return;

    const siblings = [...previewContainer.querySelectorAll(".image-preview-item:not(.dragging)")];
    
    // Find the closest sibling based on mouse position
    const closestSibling = siblings.reduce((closest, child) => {
      const rect = child.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;
      const offset = e.clientX - centerX;
      
      if (offset < 0 && (!closest.element || offset > closest.offset)) {
        return { offset: offset, element: child };
      }
      return closest;
    }, { offset: Number.NEGATIVE_INFINITY, element: null });

    if (closestSibling.element) {
      previewContainer.insertBefore(placeholder, closestSibling.element);
    } else {
      previewContainer.appendChild(placeholder);
    }
  }

  function handleDragMove(e) {
    if (!isDragging) return;

    const draggable = document.querySelector(".dragging");
    if (!draggable) return;

    // Update dragged element position
    moveDraggedElement(draggable, e);
    
    // Check if cursor is still within any grid container
    const gridContainer = document.getElementById("oum_location_images_preview");
    if (!gridContainer) return;

    const gridRect = gridContainer.getBoundingClientRect();
    const isWithinGrid = e.clientX >= gridRect.left - 50 && 
                        e.clientX <= gridRect.right + 50 && 
                        e.clientY >= gridRect.top - 50 && 
                        e.clientY <= gridRect.bottom + 50;

    // If cursor is outside grid boundaries, hide placeholder
    if (!isWithinGrid && placeholder) {
      placeholder.style.display = 'none';
    } else if (placeholder) {
      placeholder.style.display = 'block';
      updatePlaceholderPosition(e);
    }
  }

  function handleDragEnd() {
    const draggable = document.querySelector(".dragging");
    if (!draggable) return;

    // Reset cursor
    document.body.style.cursor = "";

    // Check if we're still within the grid
    const gridContainer = document.getElementById("oum_location_images_preview");
    if (!gridContainer) {
      // If no grid found, return item to its initial position
      if (draggable.initialContainer) {
        draggable.initialContainer.appendChild(draggable);
      }
    } else {
      // Place draggable element at placeholder position if within grid
      if (placeholder && placeholder.style.display !== 'none') {
        draggable.style.transition = "none";
        placeholder.parentNode.insertBefore(draggable, placeholder);
      } else {
        // If placeholder is hidden (outside grid), append to end
        gridContainer.appendChild(draggable);
      }
    }

    // Remove placeholder
    if (placeholder) {
      placeholder.remove();
    }

    // Reset draggable element styles
    draggable.style.position = "";
    draggable.style.zIndex = "";
    draggable.style.top = "";
    draggable.style.left = "";
    draggable.style.width = "";
    draggable.style.height = "";
    draggable.style.transform = "";
    draggable.style.pointerEvents = "";
    draggable.style.boxShadow = "";
    draggable.classList.remove("dragging");

    isDragging = false;
  }

  function handleTouchStart(e) {
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent("mousedown", {
      clientX: touch.clientX,
      clientY: touch.clientY
    });
    this.dispatchEvent(mouseEvent);
  }

  function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();

    const draggable = document.querySelector(".dragging");
    if (!draggable) return;

    const afterElement = getDragAfterElement(this, e.clientX);
    if (afterElement == null) {
      this.appendChild(placeholder);
    } else {
      this.insertBefore(placeholder, afterElement);
    }
  }

  // Public interface
  return {
    init: function () {
      initializeAudioUpload();
    },
    initializeImageUpload: function(imageInput) {
      initializeImageUpload(imageInput);
    },
    getSelectedFiles: function () {
      return selectedFiles;
    },
    setupDragAndDrop: setupDragAndDrop,
    handleRemoveImage: handleRemoveImage,
    setExistingAudio: setExistingAudio
  };
})();

/**
 * Checkbox Validation Module - Handles required checkbox group validation
 */
const OUMCheckboxValidation = (function () {
  // Private functions
  function syncRequired(group) {
    const anyChecked = group.some(cb => cb.checked);
    const first = group[0];
    first.required = !anyChecked;
  }

  function getCheckboxGroups() {
    const checkboxGroups = document.querySelectorAll('.oum-checkbox-group.is-required input[type="checkbox"]');
    
    if (!checkboxGroups.length) return {};

    // Group checkboxes by name
    const groups = {};
    checkboxGroups.forEach(checkbox => {
      const name = checkbox.name;
      if (!groups[name]) groups[name] = [];
      groups[name].push(checkbox);
    });

    return groups;
  }

  function processCheckboxGroup(group, setupEvents = true) {
    const first = group[0];
    
    // Only process if first checkbox is required
    if (!first.required) return;

    // Add event listeners if requested
    if (setupEvents) {
      group.forEach(cb => cb.addEventListener('change', () => syncRequired(group)));
    }
    
    // Sync the state
    syncRequired(group);
  }

  function initCheckboxGroups() {
    const groups = getCheckboxGroups();
    
    // Process each group with event listeners
    Object.keys(groups).forEach(groupName => {
      processCheckboxGroup(groups[groupName], true);
    });
  }

  // Public interface
  return {
    init: function() {
      initCheckboxGroups();
    },
    syncAllGroups: function() {
      const groups = getCheckboxGroups();
      
      // Sync each group without setting up new event listeners
      Object.keys(groups).forEach(groupName => {
        processCheckboxGroup(groups[groupName], false);
      });
    }
  };
})();

/**
 * Advanced Filter Interface Module - Handles advanced filtering functionality
 */
const OUMAdvancedFilter = (function () {
  // Private variables
  let isInitialized = false;
  let filterSidebar = null;

  // Private functions
  function init() {
    if (isInitialized) return;

    filterSidebar = document.querySelector('.oum-advanced-filter-sidebar');
    if (!filterSidebar) {
      return;
    }
    
    setupFilterEvents();
    setupFloatingPanelToggle();
    isInitialized = true;
  }

  // Setup click toggle for button/panel
  function setupFloatingPanelToggle() {
    const floatingPanel = document.querySelector('.oum-advanced-filter-button, .oum-advanced-filter-panel');
    if (!floatingPanel || !floatingPanel.classList.contains('use-collapse')) {
      return;
    }

    const toggle = floatingPanel.querySelector('.oum-advanced-filter-toggle');
    const closeButton = floatingPanel.querySelector('.close-advanced-filter');
    const filterWrapper = floatingPanel.closest('.oum-map-filter-wrapper');

    // Toggle active class on click
    if (toggle) {
      toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        floatingPanel.classList.toggle('active');
      });
    }

    // Close button always collapses the floating panel
    if (closeButton) {
      closeButton.addEventListener('click', function(e) {
        e.stopPropagation();
        floatingPanel.classList.remove('active');
      });
    }

    // Close when clicking outside the filter wrapper
    if (filterWrapper) {
      document.addEventListener('click', function(e) {
        if (!filterWrapper.contains(e.target)) {
          floatingPanel.classList.remove('active');
        }
      });
    }
  }

  function setupFilterEvents() {
    // Get all filter inputs
    const filterInputs = filterSidebar.querySelectorAll('.oum-advanced-filter-input');
    const filterCheckboxes = filterSidebar.querySelectorAll('.oum-advanced-filter-checkbox');
    const filterRadios = filterSidebar.querySelectorAll('.oum-advanced-filter-radio');
    const filterSelects = filterSidebar.querySelectorAll('.oum-advanced-filter-select');

    // Add event listeners to all filter inputs
    filterInputs.forEach(input => {
      input.addEventListener('input', handleFilterChange);
    });

    filterCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', handleFilterChange);
    });

    filterRadios.forEach(radio => {
      radio.addEventListener('change', handleFilterChange);
    });

    filterSelects.forEach(select => {
      select.addEventListener('change', handleFilterChange);
    });

    // Add reset button functionality
    const resetButton = filterSidebar.querySelector('#oum-advanced-filter-reset');
    if (resetButton) {
      resetButton.addEventListener('click', resetAllFilters);
    }
  }

  function handleFilterChange() {
    if (typeof OUMMarkers === 'undefined' || !OUMMarkers.setCustomfieldsFilter) {
      console.warn('OUMMarkers not available for advanced filtering');
      return;
    }

    const filterCriteria = getFilterCriteria();
    OUMMarkers.setCustomfieldsFilter(filterCriteria);
  }

  function getFilterCriteria() {
    const criteria = {};
    if (!filterSidebar) return criteria;

    // Get text/email/url inputs
    const textInputs = filterSidebar.querySelectorAll('.oum-advanced-filter-input');
    textInputs.forEach(input => {
      const customFieldId = input.dataset.customFieldId;
      if (customFieldId && input.value.trim()) {
        criteria[customFieldId] = {
          type: 'text',
          value: input.value.trim().toLowerCase()
        };
      }
    });

    // Get checkbox selections
    const checkboxes = filterSidebar.querySelectorAll('.oum-advanced-filter-checkbox');
    checkboxes.forEach(checkbox => {
      const customFieldId = checkbox.dataset.customFieldId;
      const filterType = checkbox.dataset.filterType;
      
      if (customFieldId) {
        // Check if this is an opening_hours open_now filter
        if (filterType === 'opening_hours_open_now') {
          if (checkbox.checked) {
            criteria[customFieldId] = {
              type: 'opening_hours_open_now',
              value: 'open'
            };
          }
        } else {
          // Standard checkbox handling
          if (!criteria[customFieldId]) {
            // Get checkbox relation from the fieldset (default to OR for backward compatibility)
            const fieldset = checkbox.closest('.oum-checkbox-group');
            const relation = fieldset ? (fieldset.dataset.checkboxRelation || 'OR') : 'OR';
            criteria[customFieldId] = { type: 'checkbox', values: [], relation: relation };
          }
          if (checkbox.checked) {
            criteria[customFieldId].values.push(checkbox.value);
          }
        }
      }
    });

    // Get radio selections
    const radios = filterSidebar.querySelectorAll('.oum-advanced-filter-radio');
    radios.forEach(radio => {
      const customFieldId = radio.dataset.customFieldId;
      if (customFieldId && radio.checked) {
        criteria[customFieldId] = {
          type: 'radio',
          value: radio.value
        };
      }
    });

    // Get select selections
    const selects = filterSidebar.querySelectorAll('.oum-advanced-filter-select');
    selects.forEach(select => {
      const customFieldId = select.dataset.customFieldId;
      if (customFieldId && select.value) {
        criteria[customFieldId] = {
          type: 'select',
          value: select.value
        };
      }
    });

    // Remove empty criteria (no checkboxes selected, empty text inputs, etc.)
    Object.keys(criteria).forEach(key => {
      const criterion = criteria[key];
      if (criterion.type === 'checkbox' && criterion.values.length === 0) {
        delete criteria[key];
      }
    });

    return criteria;
  }

  function resetAllFilters() {
    // Clear all text inputs
    const filterInputs = filterSidebar.querySelectorAll('.oum-advanced-filter-input');
    filterInputs.forEach(input => {
      input.value = '';
    });

    // Uncheck all checkboxes
    const filterCheckboxes = filterSidebar.querySelectorAll('.oum-advanced-filter-checkbox');
    filterCheckboxes.forEach(checkbox => {
      checkbox.checked = false;
    });

    // Uncheck all radio buttons
    const filterRadios = filterSidebar.querySelectorAll('.oum-advanced-filter-radio');
    filterRadios.forEach(radio => {
      radio.checked = false;
    });

    // Reset all select dropdowns to first option
    const filterSelects = filterSidebar.querySelectorAll('.oum-advanced-filter-select');
    filterSelects.forEach(select => {
      select.selectedIndex = 0;
    });

    if (typeof OUMMarkers !== 'undefined' && OUMMarkers.setCustomfieldsFilter) {
      OUMMarkers.setCustomfieldsFilter(null);
    }
  }

  // Public interface
  return {
    init: init
  };
})();

// Main initialization function
function oumInitializeMap() {
  // Only proceed if we have a map element
  if (!document.getElementById(map_el)) {
    console.warn('‼️ Open User Map: Map container missing. Initialization aborted. Disable page caching if applicable, or contact support@open-user-map.com for help.');
    return;
  }
  
  // Initialize opening hours UI
  OUMOpeningHours.init();

  // Restore the extended L object
  window.L = window.OUMLeaflet.L;

  // Initialize map and get instance
  const mapInstance = OUMMap.init(map_el);

  // Initialize markers
  const markersModule = OUMMarkers.init(mapInstance);

  // Add markers from the global oum_all_locations
  if (
    typeof oum_all_locations !== "undefined" &&
    Array.isArray(oum_all_locations)
  ) {
    markersModule.addMarkers(oum_all_locations);
  }

  // Initialize location form
  OUMFormMap.init(mapInstance);

  // Initialize form controller
  OUMFormController.init();

  // Initialize media handling
  OUMMedia.init();

  // Initialize checkbox validation
  OUMCheckboxValidation.init();

  // Initialize Advanced Filter Interface
  OUMAdvancedFilter.init();

  // Setup filter events
  const markerFilterInput = document.getElementById("oum_filter_markers");
  if (markerFilterInput) {
    markerFilterInput.addEventListener("input", OUMMarkers.updateSearchtextAndCategoriesFilters);
  }

  const categoryInputs = document.querySelectorAll(
    '.open-user-map .oum-filter-controls [name="type"]'
  );
  if (categoryInputs.length > 0) {
    categoryInputs.forEach((input) => {
      input.addEventListener("change", OUMMarkers.updateSearchtextAndCategoriesFilters);
    });
  }

  // Execute custom JS from settings
  if (typeof custom_js !== "undefined" && custom_js.snippet) {
    try {
      // Wrap custom JS execution in a try-catch with proper element existence checks
      const wrappedJS = `
        try {
          if (typeof document !== 'undefined') {
            // Defer map2-related code execution
            if (${custom_js.snippet.includes("oumMap2")}) {
              // Create a MutationObserver to watch for the form map initialization
              const observer = new MutationObserver((mutations) => {
                if (window.oumMap2) {
                  // Execute the custom JS only when oumMap2 is available
                  try {
                    ${custom_js.snippet}
                  } catch (e) {
                    console.warn('Custom JS execution error (deferred):', e);
                  }
                  observer.disconnect();
                }
              });

              // Start observing the document for the form map to be added
              observer.observe(document.body, {
                childList: true,
                subtree: true
              });
            } else {
              // Execute non-map2 related code immediately
              ${custom_js.snippet}
            }
          }
        } catch (e) {
          console.warn('Custom JS execution error:', e);
        }
      `;
      Function(wrappedJS)();
    } catch (error) {
      console.warn("Error executing custom JS:", error);
    }
  }
}
