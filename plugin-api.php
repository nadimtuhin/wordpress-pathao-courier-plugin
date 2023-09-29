<?php

// For logged-in users
add_action('wp_ajax_get_stores', 'pt_hms_ajax_get_stores');
add_action('wp_ajax_get_cities', 'pt_hms_ajax_get_cities');
add_action('wp_ajax_get_zones', 'pt_hms_ajax_get_zones');
add_action('wp_ajax_get_areas', 'pt_hms_ajax_get_areas');
add_action('wp_ajax_create_order', 'ajax_pt_hms_create_new_order');
add_action('rest_api_init', function () {
    register_rest_route(PTC_PLUGIN_PREFIX . '/v1', '/products/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'product_details_callback',
    ));
});

function pt_hms_ajax_get_stores()
{
    $stores = pt_hms_get_stores();
    wp_send_json_success($stores);
}

function pt_hms_ajax_get_cities()
{
    $cities = pt_hms_get_cities();
    wp_send_json_success($cities);
}

function pt_hms_ajax_get_zones()
{
    if (isset($_POST['city_id'])) {
        $city_id = intval($_POST['city_id']);
        $zones = pt_hms_get_zones($city_id);
        wp_send_json_success($zones);
    } else {
        wp_send_json_error('Missing city_id parameter.');
    }
}

function pt_hms_ajax_get_areas()
{
    if (isset($_POST['zone_id'])) {
        $zone_id = intval($_POST['zone_id']);
        $areas = pt_hms_get_areas($zone_id);
        wp_send_json_success($areas);
    } else {
        wp_send_json_error('Missing zone_id parameter.');
    }
}

function ajax_pt_hms_create_new_order()
{
    // Check nonce for security
    // check_ajax_referer('create_new_order_nonce', 'nonce');

    // Collect data from POST request
    $order_data = $_POST['order_data'];

    // Call your function to create a new order
    $response = pt_hms_create_new_order($order_data);

    // Send the response back to JavaScript
    wp_send_json($response);

    // Always die in functions echoing AJAX content
    die();
}

function product_details_callback(WP_REST_Request $request)
{
    $orderId =  $request->get_param('id');

    $orderData = wc_get_order($orderId);

    wp_send_json_success($orderData->get_data());
}


