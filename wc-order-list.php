<?php
// Hook for adding admin columns and populating them
add_action('init', 'initialize_admin_columns');

function initialize_admin_columns()
{
    add_filter('manage_edit-shop_order_columns', 'add_store_column_to_order_list');
    add_action('manage_shop_order_posts_custom_column', 'populate_store_column', 10, 2);
}

function add_store_column_to_order_list($columns)
{
    $columns['pathao'] = __('Pathao Courier', 'textdomain');
    return $columns;
}

function populate_store_column($column, $post_id)
{
    if ($column === 'pathao') {
        $order = wc_get_order($post_id);
        echo render_store_modal_button($post_id, $order);
    }
}

function render_store_modal_button($post_id)
{
    return sprintf('<button class="open-modal-button" data-order-id="%s">Send with Pathao</button>', $post_id);
}

function render_form_group($label, $input)
{
    return sprintf('<div class="form-group"><label for="%1$s">%1$s:</label>%2$s</div>', $label, $input);
}


function render_store_modal_content($order = null)
{
    //   $order_data = $order->get_data();
    //   $order_billing_address   = $order_data['billing']['address_1'];
    //   $order_shipping_city     = $order_data['billing']['city'];
    //   $order_shipping_postcode = $order_data['billing']['postcode'];
    //   $order_note              = $order->get_customer_note();
    //   $recipient_address = $order_billing_address . ',' . $order_shipping_city . '-' . $order_shipping_postcode;

    $nameForm = render_form_group('Name', '<input type="text" id="ptc_wc_order_name" name="name" value="">');
    $phoneForm = render_form_group('Phone', '<input type="text" id="ptc_wc_order_phone" name="phone" value="">');

    $orderNumber = render_form_group('Order Number', '<input type="text" id="ptc_wc_order_number" name="order_number" value="" readonly>');
    $priceForm = render_form_group('Price', '<input type="text" id="price" name="price">');
    $weightForm = render_form_group('Weight', '<input type="text" id="weight" name="weight">');
    $quantityForm = render_form_group('Quantity', '<input type="number" id="quantity" name="quantity">');
    $addressForm = render_form_group('Address', '<textarea id="ptc_wc_shipping_address" name="address"></textarea>');

    $citiesForm = render_cities_dropdown();
    $zoneForm = render_form_group('Zone', '<select id="zone" name="zone"><option>Select zone</option></select>');
    $areaForm = render_form_group('Area', '<select id="area" name="area"><option>Select area</option></select>');

    
    echo
    '<div id="custom-modal" class="modal pt_hms_order_modal" style="display: none;">
      <div class="modal-content">
          <span class="close">&times;</span>
          <h2>Send this through pathao courier</h2>
          <hr>
          <?php if ($order): 
            
            ?>
              <div class="order-info">
                  <h3>Order Information</h3>
                  <p><strong>Total Price:</strong> <span id="ptc_wc_order_total_price"> </span> </p>
                  <h4>Order Items:</h4>
                  <ul id="ptc_wc_order_items">
                  </ul>
              </div>
              <hr>
          <?php endif; ?>

          <div class="courier-settings">
            <div class="row">
                ' . $nameForm . '
                ' . $phoneForm . '
            </div>
            <div class="row">
              <?= render_stores_dropdown(); ?>
              <?= render_item_type_dropdown(); ?>
              <?= render_order_type_dropdown(); ?>
            </div>
            <div class="row">
              ' . $orderNumber . '
              ' . $priceForm . '
              ' . $weightForm . '
              ' . $quantityForm . '
            </div>
            <div class="row">
              ' . $addressForm . '
              ' . $citiesForm . '
              ' . $zoneForm . '
              ' . $areaForm . '
           </div>
          </div>
          <button id="submit-button" type="button">Send with pathao courier</button>
      </div>
  </div>';
}
add_action('admin_enqueue_scripts', 'render_store_modal_content');


function render_stores_dropdown()
{
    // Simulated database query
    $stores = pt_hms_get_stores();
    $options = array_map(
        fn ($store) => sprintf("<option value='%s'>%s</option>", $store['store_id'], $store['store_name']),
        $stores
    );
    $select = sprintf("<select id='store' name='store'>%s</select>", implode('', $options));
    return render_form_group('Store', $select);
}

function render_cities_dropdown()
{
    // Simulated database query
    $cities = pt_hms_get_cities();
    $options = array_map(
        fn ($city) => sprintf("<option value='%s'>%s</option>", $city['city_id'], $city['city_name']),
        $cities
    );
    $select = sprintf("<select id='city' name='city'>
    <option>Select city</option>
    %s</select>", implode('', $options));
    return render_form_group('City', $select);
}

function render_item_type_dropdown()
{
    $item_types = [
        ['type_id' => '2', 'type_name' => 'Parcel'],
        ['type_id' => '1', 'type_name' => 'Document'],
    ];

    $options = array_map(
        fn ($item) => sprintf("<option value='%s'>%s</option>", $item['type_id'], $item['type_name']),
        $item_types
    );

    $select = sprintf("<select id='item_type' name='item_type'>%s</select>", implode('', $options));
    return render_form_group('Item Type', $select);
}

function render_order_type_dropdown()
{
    $order_types = [
        ['order_type_id' => '48', 'order_type_name' => 'Normal'],
        ['order_type_id' => '12', 'order_type_name' => 'On Demand']
    ];

    $options = array_map(
        fn ($type) => sprintf("<option value='%s'>%s</option>", $type['order_type_id'], $type['order_type_name']),
        $order_types
    );

    $select = sprintf("<select id='order_type' name='order_type'>%s</select>", implode('', $options));
    return render_form_group('Order Type', $select);
}
