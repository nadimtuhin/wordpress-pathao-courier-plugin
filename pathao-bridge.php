<?php
function get_base_url() {
  $options = get_option('pt_hms_settings');
  $environment = $options['environment'] ?? 'live';
  
  return ($environment === 'staging') ? 'https://courier-api-sandbox.pathao.com/' : 'https://api-hermes.pathao.com/';
}

function issue_access_token() {
  // Get settings from WordPress options
  $options = get_option('pt_hms_settings');
  
  $client_id = $options['client_id'] ?? '';
  $client_secret = $options['client_secret'] ?? '';
  $username = $options['username'] ?? '';
  $password = $options['password'] ?? '';

  $base_url = get_base_url() . "aladdin/api/v1/issue-token";

  $response = wp_remote_post($base_url, array(
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json'
    ),
    'body' => json_encode(array(
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'username' => $username,
      'password' => $password,
      'grant_type' => 'password'
    ))
  ));

  if (is_wp_error($response)) {
    return $response->get_error_message();
  }

  $body = wp_remote_retrieve_body($response);

  return json_decode($body, true);
}

function refresh_access_token($refresh_token) {
  // Get settings from WordPress options
  $options = get_option('pt_hms_settings');

  $client_id = $options['client_id'] ?? '';
  $client_secret = $options['client_secret'] ?? '';

  $base_url = get_base_url() . "aladdin/api/v1/issue-token";

  $response = wp_remote_post($base_url, array(
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json'
    ),
    'body' => json_encode(array(
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'refresh_token' => $refresh_token,
      'grant_type' => 'refresh_token'
    ))
  ));

  if (is_wp_error($response)) {
    return $response->get_error_message();
  }

  $body = wp_remote_retrieve_body($response);

  return json_decode($body, true);
}



function pt_hms_get_token() {
  // Assuming you save the token data in the WordPress option table.
  $token_data = get_option('pt_hms_token_data');

  // Check if the token is expired.
  if ($token_data && time() > $token_data['expires_in']) {
    $refresh_response = refresh_access_token($token_data['refresh_token']);

    if (isset($refresh_response['access_token'])) {
      // Update token data.
      $token_data = array(
        'access_token' => $refresh_response['access_token'],
        'refresh_token' => $refresh_response['refresh_token'],
        'expires_in' => time() + $refresh_response['expires_in']
      );
      update_option('pt_hms_token_data', $token_data);
    }
  } elseif (!$token_data) {
    // If the token does not exist, issue a new token.
    $new_token_response = issue_access_token();

    if (isset($new_token_response['access_token'])) {
      // Save token data.
      $token_data = array(
        'access_token' => $new_token_response['access_token'],
        'refresh_token' => $new_token_response['refresh_token'],
        'expires_in' => time() + $new_token_response['expires_in']
      );
      update_option('pt_hms_token_data', $token_data);
    }
  }

  // Return the current access token.
  return $token_data ? $token_data['access_token'] : false;
}
