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
    $columns['pathao_status'] = __('Pathao Courier Status', 'textdomain');
    return $columns;
}

function populate_store_column($column, $post_id)
{
    if ($column === 'pathao') {
        $order = wc_get_order($post_id);
        echo render_store_modal_button($post_id);
    }

    if ($column === 'pathao_status') {
        $status = get_post_meta($post_id, 'ptc_status', true);
        if ($status) {
            echo sprintf('<span id="%s"> %s </span>', $post_id, ucfirst($status));
        }
    }
}

function render_store_modal_button($post_id)
{
    $consignmentId = get_post_meta($post_id, 'ptc_consignment_id', true);

    $button = sprintf('<button class="ptc-open-modal-button" data-order-id="%s">Send with Pathao</button>', $post_id);

    if ($consignmentId) {
        return sprintf('<pre> %s </pre>', $consignmentId);
    }

    return sprintf('<span class="ptc-assign-area">'. $button .'</span>', $post_id);
}

function render_form_group($label, $input)
{
    return sprintf('<div class="form-group"><label for="%1$s">%1$s:</label>%2$s</div>', $label, $input);
}


function render_store_modal_content()
{

    $nameForm = render_form_group('Name', '<input type="text" id="ptc_wc_order_name" name="name" value="">');
    $phoneForm = render_form_group('Phone', '<input type="text" id="ptc_wc_order_phone" name="phone" value="">');

    $orderNumber = render_form_group('Order Number', '<input type="text" id="ptc_wc_order_number" name="order_number" value="" readonly>');
    $priceForm = render_form_group('Price', '<input type="text" id="ptc_wc_order_price" name="ptc_wc_order_price">');
    $weightForm = render_form_group('Weight', '<input type="text" id="ptc_wc_order_weight" name="ptc_wc_order_weight">');
    $quantityForm = render_form_group('Quantity', '<input type="number" id="ptc_wc_order_quantity" name="ptc_wc_order_quantity">');
    $addressForm = render_form_group('Address', '<textarea id="ptc_wc_shipping_address" name="address"></textarea>');

    $storeForm = render_form_group('Store', '<select id="store" name="store"><option>Select store</option></select>');
    $citiesForm = render_form_group('City', '<select id="city" name="city"><option>Select city</option></select>');
    $zoneForm = render_form_group('Zone', '<select id="zone" name="zone"><option>Select zone</option></select>');
    $areaForm = render_form_group('Area', '<select id="area" name="area"><option>Select area</option></select>');


    echo
    '<div id="ptc-custom-modal" class="modal pt_hms_order_modal" style="display: none;">
      <div class="modal-content">
          <span class="close">&times;</span>
          <h2>Send this through pathao courier</h2>
          <hr>
          <?php if ($order): 
            
            ?>
              <div class="order-info">
                  <h3>Order Information</h3>
                  <p><strong>Total Price:</strong> <span id="ptc_wc_order_total_price"> </span> </p>
                  <h4>Order Items: <span id="ptc_wc_total_order_items"></span></h4>
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
            ' . $storeForm . '
            </div>
            <div class="row">
          
              ' . $citiesForm . '
              ' . $zoneForm . '
              ' . $areaForm . '
           </div>
          </div>
          <button id="ptc-submit-button" type="button">Send with pathao courier</button>
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
