jQuery(document).ready(function($) {
  $('.open-modal-button').on('click', function() {
      var orderID = $(this).data('order-id');
      $('#order-id-field').val(orderID);  // Set the Order ID in a hidden field
      $('#custom-modal').show();
  });

  $('.close').on('click', function() {
      $('#custom-modal').hide();
  });

  $('#submit-button').on('click', function() {
      $.post(
          my_ajax_object.ajax_url,
          {
              action: 'process_pathao_data',
              order_id: $('#order-id-field').val(),
              // Add other fields here
          },
          function(response) {
              alert('Server responded: ' + response);
          }
      );
  });
});


jQuery(document).ready(function($) {
    $('#city').change(function() {
        $('#zone').html('<option value="">Select Zone</option>');
        $('#area').html('<option value="">Select Area</option>');
        const city_id = $(this).val();
        $.post(ajaxurl, {
            action: 'get_zones',
            city_id: city_id
        }, function(response) {
            const zones = response.data.data.data;
            let options = '<option value="">Select Zone</option>';
            zones.forEach(function(zone) {
                options += `<option value="${zone.zone_id}">${zone.zone_name}</option>`;
            });
            $('#zone').html(options);
        });
    });

    $('#zone').change(function() {
        $('#area').html('<option value="">Select Area</option>');

        const zone_id = $(this).val();
        $.post(ajaxurl, {
            action: 'get_areas',
            zone_id: zone_id
        }, function(response) {
            const areas = response.data.data.data;
            let options = '<option value="">Select Area</option>';
            areas.forEach(function(area) {
                options += `<option value="${area.area_id}">${area.area_name}</option>`;
            });
            $('#area').html(options);
        });
    });
});
