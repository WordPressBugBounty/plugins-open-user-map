document.addEventListener('DOMContentLoaded', function(e) {
  // Event: "Add Location"-Form send
  jQuery('#oum_add_location').submit(function(event) {
    jQuery('#oum_submit_btn').addClass('oum-loading');
    
    event.preventDefault();
    let formData = new FormData(this);

    // Get all images (both existing and new) in their current order
    const previewContainer = document.getElementById('oum_location_images_preview');
    let imageOrder = [];

    // Only process images if the preview container exists
    if (previewContainer) {
      const previewItems = previewContainer.querySelectorAll('.image-preview-item');

      // Add existing and new images to formData in their current order
      previewItems.forEach((item, index) => {
        if (item.classList.contains('existing-image')) {
          // For existing images, get the URL from the hidden input
          let imgUrl = item.querySelector('[name="existing_images[]"]').value;
          
          // Fix for handling URLs - extract just the path portion if it's a full URL
          if (imgUrl.startsWith('http')) {
            try {
              // Create a URL object to extract just the pathname
              const urlObj = new URL(imgUrl);
              imgUrl = urlObj.pathname; // This gives us just the path portion
            } catch (e) {
              // If URL parsing fails, keep the original value
              console.log('Could not parse URL:', imgUrl);
            }
          }
          
          formData.append('existing_images[]', imgUrl);
          imageOrder.push('existing:' + imgUrl);
        } else {
          // For new images, get the file from selectedFiles using the filename
          const fileName = item.dataset.fileName;
          const file = window.oumSelectedFiles.find(f => f.name === fileName);
          if (file) {
            formData.append('oum_location_images[]', file);
            imageOrder.push('new:' + fileName);
          }
        }
      });

      // Add the complete image order
      formData.append('image_order', JSON.stringify(imageOrder));
    }

    formData.append('action', 'oum_add_location_from_frontend');

    jQuery.ajax({
      type: 'POST',
      url: oum_ajax.ajaxurl,
      cache: false,
      contentType: false,
      processData: false,
      data: formData,
      success: function (response, textStatus, XMLHttpRequest) {
        jQuery('#oum_submit_btn').removeClass('oum-loading');

        if(response.success == false) {
          oumShowError(response.data);
        }
        if(response.success == true) {
          jQuery('#oum_add_location').trigger('reset');
          
          // Determine message type based on action
          if (document.getElementById('oum_delete_location').value === 'true') {
              // For deletion
              OUMFormController.showFormMessage(
                  'success',
                  wp.i18n.__('Location deleted', 'open-user-map'),
                  wp.i18n.__('The location has been successfully removed from the map.', 'open-user-map'),
                  wp.i18n.__('Close and refresh map', 'open-user-map'),
                  function() {
                      window.location.reload();
                  }
              );
          } else if (document.getElementById('oum_post_id').value) {
              // For edits
              OUMFormController.showFormMessage(
                  'success',
                  wp.i18n.__('Changes saved', 'open-user-map'),
                  wp.i18n.__('Your changes have been saved and will be visible after we reviewed them.', 'open-user-map'),
                  wp.i18n.__('Close and refresh map', 'open-user-map'),
                  function() {
                      window.location.reload();
                  }
              );
          } else {
              // For new locations
              if(typeof oum_action_after_submit !== 'undefined') {
                  if(oum_action_after_submit === 'refresh') {
                      window.location.reload();
                  } else if(oum_action_after_submit === 'redirect' && typeof thankyou_redirect !== 'undefined' && thankyou_redirect !== '') {
                      window.location.href = thankyou_redirect;
                  } else {
                      // Show thank you message with refresh button (default)
                      const thankyouDiv = document.getElementById('oum_add_location_thankyou');
                      const thankyouHeadline = thankyouDiv?.querySelector('h3')?.textContent || wp.i18n.__('Thank you!', 'open-user-map');
                      const thankyouText = thankyouDiv?.querySelector('.oum-add-location-thankyou-text')?.textContent || wp.i18n.__('We will check your location suggestion and release it as soon as possible.', 'open-user-map');
                      
                      OUMFormController.showFormMessage(
                          'success',
                          thankyouHeadline,
                          thankyouText,
                          wp.i18n.__('Close and refresh map', 'open-user-map'),
                          function() {
                              window.location.reload();
                          }
                      );
                  }
              } else {
                  // Fallback to thank you message with refresh button
                  const thankyouDiv = document.getElementById('oum_add_location_thankyou');
                  const thankyouHeadline = thankyouDiv?.querySelector('h3')?.textContent || wp.i18n.__('Thank you!', 'open-user-map');
                  const thankyouText = thankyouDiv?.querySelector('.oum-add-location-thankyou-text')?.textContent || wp.i18n.__('We will check your location suggestion and release it as soon as possible.', 'open-user-map');
                  
                  OUMFormController.showFormMessage(
                      'success',
                      thankyouHeadline,
                      thankyouText,
                      wp.i18n.__('Close and refresh map', 'open-user-map'),
                      function() {
                          window.location.reload();
                      }
                  );
              }
          }
        }
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) { 
        console.log(errorThrown);
      }
    });
  });

  function oumShowError(errors) {
    const errorWrapEl = jQuery('#oum_add_location_error');
    errorWrapEl.html('');
    errors.forEach(error => {
      errorWrapEl.append(error.message + '<br>');
    });
    errorWrapEl.show();
  }
});