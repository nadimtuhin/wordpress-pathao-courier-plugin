<?php

// For logged-in users
add_action('wp_ajax_get_stores', 'pt_hms_ajax_get_stores');
add_action('wp_ajax_get_cities', 'pt_hms_ajax_get_cities');
add_action('wp_ajax_get_zones', 'pt_hms_ajax_get_zones');
add_action('wp_ajax_get_areas', 'pt_hms_ajax_get_areas');

function pt_hms_ajax_get_stores() {
    $stores = pt_hms_get_stores();
    wp_send_json_success($stores);
}

function pt_hms_ajax_get_cities() {
    $cities = pt_hms_get_cities();
    wp_send_json_success($cities);
}

function pt_hms_ajax_get_zones() {
    if(isset($_POST['city_id'])) {
        $city_id = intval($_POST['city_id']);
        $zones = pt_hms_get_zones($city_id);
        wp_send_json_success($zones);
    } else {
        wp_send_json_error('Missing city_id parameter.');
    }
}

function pt_hms_ajax_get_areas() {
    if(isset($_POST['zone_id'])) {
        $zone_id = intval($_POST['zone_id']);
        $areas = pt_hms_get_areas($zone_id);
        wp_send_json_success($areas);
    } else {
        wp_send_json_error('Missing zone_id parameter.');
    }
}
