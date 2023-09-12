<?php
// Hook for adding admin columns and populating them
add_action('init', 'initialize_admin_columns');

function initialize_admin_columns() {
    add_filter('manage_edit-shop_order_columns', 'add_store_column_to_order_list');
    add_action('manage_shop_order_posts_custom_column', 'populate_store_column', 10, 2);
}

function add_store_column_to_order_list($columns) {
    $columns['pathao'] = __('Pathao Courier', 'textdomain');
    return $columns;
}

function populate_store_column($column, $post_id) {
    if ($column === 'pathao') {
        echo render_store_modal_button($post_id) . render_store_modal_content();
    }
}

function render_store_modal_button($post_id) {
    return sprintf('<button class="open-modal-button" data-order-id="%s">Send with Pathao</button>', $post_id);
}

function render_form_group($label, $input) {
    return sprintf('<div class="form-group"><label for="%1$s">%1$s:</label>%2$s</div>', $label, $input);
}

function render_store_modal_content() {
    ob_start();
    ?>
    <div id="custom-modal" class="modal pt_hms_order_modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Set store, address, and price</h2>
            <?php
            echo render_form_group('Price', '<input type="text" id="price" name="price">');
            echo render_stores_dropdown();
            echo render_cities_dropdown();
            echo render_additional_fields();
            ?>
            <button id="submit-button">Submit</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function render_stores_dropdown() {
    // Simulated database query
    $stores = pt_hms_get_stores();
    $options = array_map(
        fn($store) => sprintf("<option value='%s'>%s</option>", $store['store_id'], $store['store_name']),
        $stores
    );
    $select = sprintf("<select id='store' name='store'>%s</select>", implode('', $options));
    return render_form_group('Store', $select);
}

function render_cities_dropdown() {
    // Simulated database query
    $cities = pt_hms_get_cities();
    $options = array_map(
        fn($city) => sprintf("<option value='%s'>%s</option>", $city['city_id'], $city['city_name']),
        $cities
    );
    $select = sprintf("<select id='city' name='city'>
    <option>Select city</option>
    %s</select>", implode('', $options));
    return render_form_group('City', $select);
}

function render_additional_fields() {
    return render_form_group('Zone', '<select id="zone" name="zone">
    <option>Select zone</option>
    </select>') .
           render_form_group('Area', '<select id="area" name="area">
    <option>Select area</option>
           </select>');
}
