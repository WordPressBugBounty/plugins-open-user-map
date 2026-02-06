//Dismiss Getting Started Notice
jQuery(document).on('click', '.oum-getting-started-notice .notice-dismiss', function() {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'oum_dismiss_getting_started_notice'
        }
    });
});

//Dismiss Update Notice
jQuery(document).on('click', '.oum-update-notice .notice-dismiss', function(e) {
    // Prevent WordPress default dismiss handler from interfering
    e.preventDefault();
    e.stopImmediatePropagation();
    
    var $notice = jQuery(this).closest('.oum-update-notice');
    var version = $notice.attr('data-version') || $notice.data('version') || '';
    
    if (!version) {
        console.error('Update notice: Version data missing');
        // Still allow WordPress to dismiss the notice visually
        return;
    }
    
    // Make AJAX call to save dismissed version
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'oum_dismiss_update_notice',
            version: version
        },
        success: function(response) {
            if (response && response.success) {
                // Option saved successfully
                // WordPress will handle hiding the notice via CSS
            } else {
                console.error('Failed to dismiss update notice:', response ? response.data : 'Unknown error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error dismissing update notice:', error);
        }
    });
    
    // Manually hide the notice since we prevented default
    $notice.fadeOut(100, function() {
        jQuery(this).remove();
    });
});


jQuery(function($){
  // Audio Uploader
  $('body').on('click', '.oum_upload_audio_button', function(e){
    e.preventDefault();

    const audio_uploader = wp.media({
        title: 'Custom audio',
        library : {
            type : 'audio'
        },
        button: {
            text: 'Use this audio'
        },
        multiple: false
    }).on('select', function() {
        const attachment = audio_uploader.state().get('selection').first().toJSON();
        const url = attachment.url;
        $('#oum_location_audio').val(url);
        $('#oum_location_audio_preview').addClass('has-audio');
        $('#oum_location_audio_preview').html(url + '<div onclick="oumRemoveAudioUpload()" class="remove-upload">&times;</div>');
    });

    audio_uploader.open();
  });

  // Icon Uploader
  $('body').on('click', '.oum_upload_icon_button', function(e){
    e.preventDefault();

    const icon_uploader = wp.media({
        title: 'Custom icon',
        library : {
            type : 'image'
        },
        button: {
            text: 'Use this image'
        },
        multiple: false
    }).on('select', function() {
        const attachment = icon_uploader.state().get('selection').first().toJSON();
        const url = attachment.url;
        $('#oum_marker_user_icon').val(url);
        $('#oum_marker_user_icon_preview').addClass('has-icon');
        $('#oum_marker_user_icon_preview').css("background-image", "url(" + url + ")");
        $('#oum_marker_user_icon_preview').next('input[type=radio]').prop('checked', true);
        $('#oum_marker_user_icon_preview').next('input[type=radio]').trigger('change');
    });
    
    icon_uploader.open();
  });

  // Multi-Categories Icon Uploader
  $('body').on('click', '.oum_upload_multicategories_icon_button', function(e){
    e.preventDefault();

    // Get the default icon URL from the preview's data-default attribute
    const $preview = $('#oum_marker_multicategories_icon_preview');
    const defaultIcon = $preview.data('default');

    const multicat_icon_uploader = wp.media({
        title: 'Multi-Categories Icon',
        library : {
            type : 'image'
        },
        button: {
            text: 'Use this image'
        },
        multiple: false
    }).on('select', function() {
        const attachment = multicat_icon_uploader.state().get('selection').first().toJSON();
        const url = attachment.url;
        $('#oum_marker_multicategories_icon').val(url);
        $preview.addClass('has-icon');
        $preview.css('background-image', 'url(' + url + ')');
        // Show remove button
        $('.oum_remove_multicategories_icon_button').show();
    });
    multicat_icon_uploader.open();
  });

  // Multi-Categories Icon Remove Button
  $('body').on('click', '.oum_remove_multicategories_icon_button', function(e){
    e.preventDefault();
    const $preview = $('#oum_marker_multicategories_icon_preview');
    const defaultIcon = $preview.data('default');
    // Clear the input
    $('#oum_marker_multicategories_icon').val('');
    // Reset the preview to default
    $preview.removeClass('has-icon');
    $preview.css('background-image', 'url(' + defaultIcon + ')');
    // Hide remove button
    $(this).hide();
  });

  // Export CSV
  $('body').on('click', '.oum_export_csv_button', function(e){
    e.preventDefault();

    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            'action': 'oum_csv_export',
        },
        success: function (response, textStatus, XMLHttpRequest) {
            console.log(response);
            console.log(textStatus);

            // locations from PHP
            var $locations_list = response.data.locations;
            var datetime = response.data.datetime;

            // EXIT, if no locations
            if($locations_list.length === 0) {
                alert('Something went wrong. Please see errors in console.');
                console.error('OUM: No public locations available to export.');
                return;
            } 

            const download = function (data) {
              const blob = new Blob([data], { type: 'text/csv' });
              const url = window.URL.createObjectURL(blob)
              const a = document.createElement('a')
              a.setAttribute('href', url)
              a.setAttribute('download', 'oum-locations_' + datetime + '.csv');
              a.click()
            }

            const csvmaker = function (data) {
              csvRows = [];
              let headerValues = '';
              for (let col of data.header) { headerValues += '"' + col + '"' + ','; }
              csvRows.push(headerValues.slice(0, -1));
              data.rows.forEach(row => {
                let locationValues = '';
                for (let col of row) { locationValues += '"' + col + '"' + ','; }
                csvRows.push(locationValues.slice(0, -1));
              });
              return csvRows.join('\r\n')
            }

            const get = function () {
              const data = {};
              data.header = Object.keys($locations_list[0]);
              data.rows = [];
              $locations_list.forEach(location_row => {
                data.rows.push(Object.values(location_row))
              });
              console.log(data);
              const csvdata = csvmaker(data);
              download(csvdata);
            }
            
            get();
        }
    });
  });

  // Import CSV
  $('body').on('click', '.oum_upload_csv_button', function(e){
    e.preventDefault();

    var button = $(this),
    csv_uploader = wp.media({
        title: 'Upload CSV file',
        library : {
            type : 'file'
        },
        button: {
            text: 'Use this file'
        },
        multiple: false
    }).on('select', function() {
        var attachment = csv_uploader.state().get('selection').first().toJSON();

        // Show loading spinner
        if(!$('.oum-import-loading').length) {
          button.after('<div class="oum-import-loading"><div class="oum-spinner"></div></div>');
        }
        $('.oum-import-loading').show();
        button.prop('disabled', true);

        // Import CSV with PHP
        // Get the publish immediately checkbox value
        var publish_immediately = $('#oum_csv_import_publish_immediately').is(':checked') ? 'on' : '';
        
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                'action': 'oum_csv_import',
                'oum_location_nonce': oum_ajax.oum_location_nonce,
                'url': attachment.url,
                'oum_csv_import_publish_immediately': publish_immediately,
            },
            success: function (response, textStatus, XMLHttpRequest) {
                // Hide loading spinner
                $('.oum-import-loading').hide();
                button.prop('disabled', false);

                if(response.success) {
                    alert(response.data);
                }else{
                    alert('Something went wrong. Please see errors in console.');
                    response.data.forEach((error) => {
                        console.error(error.code + ': ' + error.message);
                    });
                }
            },
            error: function() {
                // Hide loading spinner
                $('.oum-import-loading').hide();
                button.prop('disabled', false);
                alert('Something went wrong. Please try again.');
            }
        });
    })
    .open();
  });

  // Shortcode Display Auto Width
  jQuery(document).ready(function ($) {
    $('.shortcode-display').each(function() {
      var input = this;
      var span = document.createElement('span');
      span.style.visibility = 'hidden';
      span.style.whiteSpace = 'pre';
      span.style.font = getComputedStyle(input).font;
      document.body.appendChild(span);
      span.textContent = input.value;
      input.style.width = (span.offsetWidth + 16) + 'px'; // padding allowance
      span.remove();
    });
  });

});

function oumRemoveVideoUpload() {
    document.getElementById('oum_location_video').value = '';
    document.getElementById('oum_location_video_preview').classList.remove('has-video');
    document.getElementById('oum_location_video_preview').textContent = '';
}

function oumRemoveAudioUpload() {
    document.getElementById('oum_location_audio').value = '';
    document.getElementById('oum_location_audio_preview').classList.remove('has-audio');
    document.getElementById('oum_location_audio_preview').textContent = '';
}