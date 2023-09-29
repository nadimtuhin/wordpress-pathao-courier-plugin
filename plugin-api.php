<?php

// For logged-in users
add_action('wp_ajax_get_stores', 'pt_hms_ajax_get_stores');
add_action('wp_ajax_get_cities', 'pt_hms_ajax_get_cities');
add_action('wp_ajax_get_zones', 'pt_hms_ajax_get_zones');
add_action('wp_ajax_get_areas', 'pt_hms_ajax_get_areas');
add_action('wp_ajax_create_order', 'ajax_pt_hms_create_new_order');
add_action('wp_ajax_get_wc_order', 'ajax_pt_wc_order_details');


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


function ajax_pt_wc_order_details()
{
    
    $orderId =  $_POST['order_id'] ?? null;

    if (!$orderId) {
        return new WP_Error('no_order_id', 'No order id found', array('status' => 404));
    }

    $order = wc_get_order($orderId);

    if (!$order) {
        return new WP_Error('no_order', 'No order found', array('status' => 404));
    }

    $orderData = $order->get_data();
    $orderItems = 0;
    // add items to order
    $orderData['items'] = array_values(array_map(function($item) use (&$orderItems) {

        $quantity = $item->get_quantity();

        $orderItems += $quantity;

        return [
            'name' => $item->get_name(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total(),
            'product_id' => $item->get_product_id(),
            'variation_id' => $item->get_variation_id(),
            'image' => wp_get_attachment_image_src($item->get_product()->get_image_id(), 'thumbnail')[0],
            'product_url' => $item->get_product()->get_permalink(),
        ];
    }, $order->get_items()));

    $orderData['billing']['full_name'] = $order->get_formatted_billing_full_name();
    
    $orderData['total_items'] = $orderItems;

    wp_send_json_success($orderData);
}


