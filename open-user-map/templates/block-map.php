<?php require oum_get_template('partial-map-init.php'); ?>

<div class="open-user-map">

  <?php
  //TODO: manage variables from partial-map-init.php in a $oum_settings[]

  $plugin_path = $this->plugin_path;
  
  add_action('wp_footer', function () use (
    $plugin_path, 
    $oum_map_label, 
    $types,
    $oum_marker_types_label, 
    $oum_title_label, 
    $oum_address_label,
    $oum_description_label, 
    $oum_upload_media_label,
    $oum_searchaddress_label, 
    $oum_ui_color, 
    $oum_enable_user_notification, 
    $text_notify_me_on_publish_label, 
    $thankyou_text, 
    $map_style,
    $text_notify_me_on_publish_name, 
    $text_notify_me_on_publish_email, 
    $thankyou_headline) { 

    require_once oum_get_template('partial-map-add-location.php');

  });
  ?>

  <?php require oum_get_template('partial-map-render.php'); ?>

</div>