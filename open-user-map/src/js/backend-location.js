window.addEventListener('load', function(e) {

  // Initialize image preview grid if editing a location
  const existingImages = jQuery('#oum_location_image').val();
  if (existingImages) {
    const imageUrls = existingImages.split('|');
    updateImagePreview(imageUrls);
  }

  // Restore the extended L object (OUMLeaflet.L) to the global scope (prevents conflicts with other Leaflet instances)
  window.L = window.OUMLeaflet.L;

  // FUNCTIONS

  //set lat & lng input fields
  function setLocationLatLng(markerLatLng) {
    jQuery('#oum_location_lat').val(markerLatLng.lat);
    jQuery('#oum_location_lng').val(markerLatLng.lng);
  }

  //set address field
  function setAddress(label) {
    jQuery('#oum_location_address').val(label);
  }


  // VARIABLES

  const latLngInputs = jQuery('#latLngInputs');
  const showLatLngInputs = jQuery('#showLatLngInputs');
  let markerIsVisible = false;

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


  // SETUP MAP

  const map = L.map('mapGetLocation', {
      scrollWheelZoom: false,
      zoomSnap: 1,
      zoomDelta: 1,
  });

  // prevent moving/zoom outside main world bounds
  let world_bounds = L.latLngBounds(L.latLng(-85, -200), L.latLng(85, 200));
  let world_min_zoom = map.getBoundsZoom(world_bounds);
  map.setMaxBounds(world_bounds);
  map.setMinZoom(Math.ceil(world_min_zoom));
  map.on('drag', function() {
    map.panInsideBounds(world_bounds, { animate: false });
  });

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

  let locationMarker = L.marker([lat, lng], {icon: markerIcon}, {
      'draggable': true
  });
  
  // render map
  if(lat && lng) {
      //location has coordinates
      map.setView([lat, lng], zoom);
      locationMarker.addTo(map);
      markerIsVisible = true;
  }else{
      //location has NO coordinates yet
      map.setView([0, 0], 1);
  }

  // Control: search address
  const search = new GeoSearch.GeoSearchControl({
    style: 'bar',
    showMarker: false,
    provider: oum_geosearch_selected_provider,
    searchLabel: oum_searchaddress_label
  });
  map.addControl(search);

  // Control: get current location
  if(enableCurrentLocation) {
    L.control.locate({
      flyTo: true,
      initialZoomLevel: 12,
      drawCircle: false,
      drawMarker: false
    }).addTo(map);
  }


  // Trigger resize (sometimes necessary to render the map properly)
  setInterval(function () {
    map.invalidateSize();
  }, 1000)


  // EVENTS

  //Event: click on map to set marker
  map.on('click locationfound', function(e) {
    let coords = e.latlng;

    locationMarker.setLatLng(coords);

    if(!markerIsVisible) {
        locationMarker.addTo(map);
        markerIsVisible = true;
    }

    setLocationLatLng(coords);
  });

  //Event: geosearch success
  map.on('geosearch/showlocation', function(e) {
    let coords = e.marker._latlng;
    let label = e.location.label;

    locationMarker.setLatLng(coords);

    if (!markerIsVisible) {
      locationMarker.addTo(map);
      markerIsVisible = true;
    }

    setLocationLatLng(coords);
    
    //setAddress(label);
  });

  //Event: drag marker
  locationMarker.on('dragend', function(e) {
      setLocationLatLng(e.target.getLatLng());
  });

  //Event: click on "edit coordinates manually"
  showLatLngInputs.on('click', function(e) {
      e.preventDefault();
      jQuery(this).parent('.hint').hide();
      latLngInputs.fadeIn();
  });

  // Media Uploader
  jQuery('#oum_location_image_preview').closest('form').on('click', '.oum_upload_image_button', function(e) {
    e.preventDefault();

    // Create new media frame
    const image_uploader = wp.media({
      title: 'Custom image',
      multiple: true,
      library: {
        type: 'image'
      },
      button: {
        text: 'Use these images'
      }
    });

    // Bind to select event
    image_uploader.on('select', function() {
      const attachments = image_uploader.state().get('selection').toJSON();
      const maxImages = 5;
      const existingImages = jQuery('#oum_location_image_preview img').length;
      const remainingSlots = maxImages - existingImages;
      
      if (attachments.length > remainingSlots) {
        alert('Maximum ' + maxImages + ' images allowed. Only the first ' + remainingSlots + ' images will be added.');
      }

      const imagesToProcess = attachments.slice(0, remainingSlots);
      let existingUrls = jQuery('#oum_location_image').val() ? jQuery('#oum_location_image').val().split('|') : [];
      
      imagesToProcess.forEach(attachment => {
        const url = attachment.sizes.large ? attachment.sizes.large.url : attachment.sizes.full.url;
        existingUrls.push(url);
      });

      jQuery('#oum_location_image').val(existingUrls.join('|'));
      updateImagePreview(existingUrls);
    });

    image_uploader.open();
    return false;
  });

  // Function to update image preview
  function updateImagePreview(imageUrls) {
    const previewContainer = jQuery('#oum_location_image_preview');
    
    // Remove old preview and classes
    previewContainer.empty().removeClass('has-image');
    
    if (!imageUrls || imageUrls.length === 0) {
      return;
    }

    previewContainer.addClass('has-image');

    // Create preview grid
    const gridContainer = jQuery('<div class="image-preview-grid"></div>');
    
    imageUrls.forEach((url, index) => {
      if (!url) return; // Skip empty URLs
      
      const previewItem = jQuery(`
        <div class="image-preview-item" draggable="true" data-url="${url}">
          <img src="${url}" alt="Preview">
          <div class="remove-image" title="Remove image">&times;</div>
          <div class="drag-handle" title="Drag to reorder">⋮⋮</div>
        </div>
      `);
      
      // Add drag and drop functionality
      const item = previewItem[0];
      setupDragAndDrop(item);
      
      gridContainer.append(previewItem);
    });
    
    previewContainer.append(gridContainer);
  }

  // Variables for drag and drop
  let isDragging = false;
  let placeholder;

  function setupDragAndDrop(previewItem) {
    previewItem.addEventListener('mousedown', function(e) {
      if (e.target.classList.contains('remove-image')) return;

      isDragging = true;
      this.classList.add('dragging');

      // Get element dimensions once at start
      const rect = this.getBoundingClientRect();
      this.style.width = rect.width + 'px';
      this.style.height = rect.height + 'px';

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

      // Store initial grid container for safety check
      this.initialContainer = this.closest('.image-preview-grid');
    });
    
    previewItem.addEventListener('touchstart', handleTouchStart);
  }

  function createPlaceholder(element) {
    placeholder = document.createElement("div");
    placeholder.classList.add("image-preview-placeholder");
    placeholder.style.width = element.offsetWidth + "px";
    placeholder.style.height = element.offsetHeight + "px";
    placeholder.style.transition = "transform 0.2s ease";
    placeholder.style.border = "2px dashed #e02aaf";
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
    const gridContainer = document.querySelector("#oum_location_image_preview .image-preview-grid");
    if (!gridContainer) return;

    const draggable = document.querySelector('.dragging');
    if (!draggable) return;

    const siblings = [...gridContainer.querySelectorAll(".image-preview-item:not(.dragging)")];
    
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
        gridContainer.insertBefore(placeholder, closestSibling.element);
    } else {
        gridContainer.appendChild(placeholder);
    }
  }

  function handleDragMove(e) {
    if (!isDragging) return;

    const draggable = document.querySelector(".dragging");
    if (!draggable) return;

    // Update dragged element position
    moveDraggedElement(draggable, e);
    
    // Check if cursor is still within any grid container
    const gridContainer = document.querySelector("#oum_location_image_preview .image-preview-grid");
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
    const gridContainer = document.querySelector("#oum_location_image_preview .image-preview-grid");
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

    // Update image order
    updateImageOrder();
  }

  function handleTouchStart(e) {
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent("mousedown", {
      clientX: touch.clientX,
      clientY: touch.clientY
    });
    this.dispatchEvent(mouseEvent);
  }

  // Add document-level event listeners for drag and drop
  document.addEventListener("mousemove", handleDragMove);
  document.addEventListener("mouseup", handleDragEnd);

  // Remove image handler with animation
  jQuery('body').on('click', '.remove-image', function(e) {
    e.preventDefault();
    const item = jQuery(this).closest('.image-preview-item');
    const url = item.data('url');
    
    // Remove from hidden input
    const currentUrls = jQuery('#oum_location_image').val().split('|');
    const newUrls = currentUrls.filter(currentUrl => currentUrl !== url);
    jQuery('#oum_location_image').val(newUrls.join('|'));
    
    // Animate and remove preview item
    item.css({
      transition: 'all 0.3s ease',
      transform: 'scale(0.8)',
      opacity: '0'
    });
    
    setTimeout(() => {
      item.remove();
      
      // Remove has-image class if no images left
      if (newUrls.length === 0) {
        jQuery('#oum_location_image_preview').removeClass('has-image').empty();
      }
    }, 300);
  });

  // Add back the updateImageOrder function
  function updateImageOrder() {
    const imageUrls = [];
    jQuery('#oum_location_image_preview .image-preview-item').each(function() {
      imageUrls.push(jQuery(this).data('url'));
    });
    jQuery('#oum_location_image').val(imageUrls.join('|'));
  }

}, false);