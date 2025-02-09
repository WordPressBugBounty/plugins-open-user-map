/**
 * Map Loading State Management
 */

// Listen for map initialization complete
document.addEventListener('oum:map_initialized', (event) => {
  const { mapId } = event.detail;
  
  // Show map controls once initialization is complete
  showMapControls(mapId);
});

/**
 * Show map controls and hide loading overlay
 */
const showMapControls = (mapId) => {
  if (!mapId) return;

  const mapWrap = document.getElementById(mapId).closest('.map-wrap');
  if (!mapWrap) return;

  const loadingOverlay = mapWrap.querySelector('.oum-loading-overlay');
  const filterControls = mapWrap.querySelector('.oum-filter-controls');
  const addLocationBtn = mapWrap.querySelector('.open-add-location-overlay');
  const filterMarkersInput = mapWrap.querySelector('#oum_filter_markers');

  // Hide loading overlay
  if (loadingOverlay) {
    loadingOverlay.classList.add('hidden');
  }

  // Show controls with a slight delay for smooth transition
  setTimeout(() => {
    // Remove the oum-hidden class and add visible class for filter controls
    if (filterControls) {
      filterControls.classList.remove('oum-hidden');
      filterControls.classList.add('visible');
    }

    // Remove the oum-hidden class and add visible class for add location button
    if (addLocationBtn) {
      addLocationBtn.classList.remove('oum-hidden');
      addLocationBtn.classList.add('visible');
    }

    // Handle filter markers input visibility
    if (filterMarkersInput) {
      filterMarkersInput.classList.remove('oum-hidden');
      filterMarkersInput.classList.add('visible');
    }
  }, 300);
};

/**
 * Map Loading State Handler
 */
const OUMLoader = (function() {
  let loadingStates = {};

  function initLoader(mapId) {
    loadingStates[mapId] = {
      initialized: false
    };
  }

  function setMapInitialized(mapId) {
    if (loadingStates[mapId]) {
      loadingStates[mapId].initialized = true;
      showMapControls(mapId);
      delete loadingStates[mapId]; // Cleanup
    }
  }

  // Public API
  return {
    initLoader,
    setMapInitialized
  };
})(); 