<?php
defined('ABSPATH') || exit;

// Register AJAX action
add_action('wp_ajax_get_token', 'ajax_get_token');

function ajax_get_token()
{
    $data = issue_access_token(
        $_POST['client_id'] ?? '',
        $_POST['client_secret'] ?? '',
        $_POST['environment'] ?? ''
    );

    $token = $data['access_token'] ?? null;

    if ($token) {
        wp_send_json_success($data);
    } else {
        wp_send_json_error(array('message' => 'Failed to retrieve the token.'));
    }
}

add_action('wp_ajax_reset_token', 'ajax_reset_token');

function ajax_reset_token()
{
    $token = pt_hms_get_token(true);
    if ($token) {
        wp_send_json_success(array('access_token' => $token));
    } else {
        wp_send_json_error(array('message' => 'Failed to retrieve the token.'));
    }
}

add_action('update_option_pt_hms_settings', 'pt_hms_on_option_update', 10, 3);

function pt_hms_on_option_update($old_value, $new_value, $option)
{
    // Reset the token stored in the database.
    delete_option('pt_hms_token_data');

    // Fetch a new token.
    pt_hms_get_token(); // Assuming pt_hms_get_token is your function to fetch the new token.
}

// Admin menu setup
add_action('admin_menu', 'pt_hms_menu_page');

// Admin menu callback
function pt_hms_menu_page()
{
    add_menu_page(
        'Pathao Courier Settings',
        'Pathao Courier',
        'manage_options',
        'pt_hms_settings',
        'pt_hms_settings_page',
        'dashicons-move',
        6
    );
}

// Render the settings page
function pt_hms_settings_page()
{
    $options = get_option('pt_hms_settings');
    $all_fields_filled = isset(
        $options['client_id'],
        $options['client_secret'],
//        $options['username'],
//        $options['password'],
        $options['environment']
    );

    $token = $all_fields_filled ? pt_hms_get_token() : null;
    ?>
    <div class="wrap">
        <h2>Pathao Courier Settings</h2>
        <?php if ($all_fields_filled && !$token): ?>
            <div class="notice notice-error">
                <p>API credentials are invalid. Please check your credentials and try again.</p>
            </div>
        <?php endif; ?>
        <form method="post" action="options.php">
            <input type="hidden" name="your_hidden_field" value="my_custom_action">
            <?php
            settings_fields('pt_hms_settings_group');
            do_settings_sections('pt_hms_settings');
            submit_button();
            ?>
        </form>
        <!-- Token Fetch Button -->
        <section>
            <h3>Test Credentials</h3>
            <button type="button" id="fetch-token-btn">Test credentials validity</button>

            <?php if ($token): ?>
                <button type="button" id="reset-token-btn">Reset token</button>
            <?php endif; ?>
        </section>
        <!-- JavaScript for AJAX call -->
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#fetch-token-btn').on('click', function () {

                    let clientId = $('#client_id').val();
                    let clientSecret = $('#client_secret').val();
                    let environment = $('#client_environment').val();

                    console.log({clientId, clientSecret, environment})

                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'get_token',
                            client_id: clientId,
                            client_secret: clientSecret,
                            environment: environment
                        },
                        success: function (response) {
                            if (response.success) {
                                alert('API credentials valid');
                            } else {
                                alert('Error: ' + response.data.message);
                            }
                        },
                        error: function () {
                            alert('An error occurred.');
                        }
                    });
                });
                $('#reset-token-btn').on('click', function () {

                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'reset_token',
                        },
                        success: function (response) {
                            if (response.success) {
                                alert('Token Reset Successful');
                            } else {
                                alert('Error: ' + response.data.message);
                            }
                        },
                        error: function () {
                            alert('An error occurred.');
                        }
                    });
                });
            });
        </script>
    </div>
    <?php
}

// Admin init setup
add_action('admin_init', 'pt_hms_settings_init');

// Admin init callback
function pt_hms_settings_init()
{
    register_setting('pt_hms_settings_group', 'pt_hms_settings');

    // API Credentials
    add_settings_section('section_one', 'API Credentials', 'section_one_callback', 'pt_hms_settings');
    add_settings_field('client_id', 'Client ID', 'field_client_id_callback', 'pt_hms_settings', 'section_one');
    add_settings_field('client_secret', 'Client Secret', 'field_client_secret_callback', 'pt_hms_settings', 'section_one');

//    add_settings_field('username', 'Username (Email)', 'field_username_callback', 'pt_hms_settings', 'section_one');
//    add_settings_field('password', 'Password', 'field_password_callback', 'pt_hms_settings', 'section_one'); // todo: remove this
//    add_settings_field(
//        'default_store',
//        'Default Store',
//        'field_default_store_callback',
//        'pt_hms_settings',
//        'section_one'
//    );
    add_settings_field('environment', 'Environment', 'field_environment_callback', 'pt_hms_settings', 'section_one');
    add_settings_field('client_webhook', 'Client Default Webhook', 'field_webhook_callback', 'pt_hms_settings', 'section_one');
    add_settings_field('client_webhook_secret', 'Client Webhook Secret', 'field_webhook_secret_callback', 'pt_hms_settings', 'section_one');
}

function section_one_callback()
{
    echo 'Enter your API credentials below:';
}

function field_client_id_callback()
{
    $options = get_option('pt_hms_settings');
    $value = is_array($options) && isset($options['client_id']) ? $options['client_id'] : '';
    echo "<input type='text' id='client_id' name='pt_hms_settings[client_id]' value='{$value}' style='width: 300px;' />";
}

function field_client_secret_callback()
{
    $options = get_option('pt_hms_settings');
    $value = is_array($options) && isset($options['client_secret']) ? $options['client_secret'] : '';
    echo "<input type='password' id='client_secret' name='pt_hms_settings[client_secret]' value='{$value}' style='width: 300px;' />";
}

function field_webhook_callback()
{
    $baseUrl = get_site_url();
    echo "{$baseUrl}/wp-json/ptc/v1/webhook";
    echo "<p class='description'>
            This is the default <a href=\"https://merchant.pathao.com//courier/developer-api\">webhook</a> URL that will be used for all orders.
          </p>";
}

function field_webhook_secret_callback()
{
    $options = get_option('pt_hms_settings');
    $clientSecret = $options['client_secret'] ?? '';
    $webhookSecret = $options['webhook_secret'] ?? '';
    $value = $webhookSecret ? $webhookSecret : $clientSecret;
    echo "<input type='text' name='pt_hms_settings[webhook_secret]' value='{$value}' style='width: 300px;' />";
    echo "<p class='description'>
            The default <a href=\"https://merchant.pathao.com//courier/developer-api\">webhook</a> secret will be your client secret if you don't provide any webhook secret.
            </p>";
}


function field_username_callback()
{
    $options = get_option('pt_hms_settings');
    $value = is_array($options) && isset($options['username']) ? $options['username'] : '';
    echo "<input type='text' name='pt_hms_settings[username]' value='{$value}' style='width: 300px;' />";
}

function field_password_callback()
{
    $options = get_option('pt_hms_settings');
    $value = is_array($options) && isset($options['password']) ? $options['password'] : '';
    echo "<input type='password' name='pt_hms_settings[password]' value='{$value}' style='width: 300px;' />";
}

function field_environment_callback()
{
    $options = get_option('pt_hms_settings');
    $selected = is_array($options) && isset($options['environment']) ? $options['environment'] : '';
    echo "<select name='pt_hms_settings[environment]' id='client_environment'  style='width: 300px;'>
      <option value='live' " . selected($selected, 'live', false) . ">Live</option>
      <option value='staging' " . selected($selected, 'staging', false) . ">Staging</option>
  </select>";
}

function field_default_store_callback()
{
    $stores = pt_hms_get_stores(); // Assuming this function returns an array of stores
    if (!$stores || !is_array($stores) || empty($stores)) {
        echo "No stores found.";
        return;
    }

    $options = get_option('pt_hms_settings');
    $selected_store = is_array($options) && isset($options['default_store']) ? $options['default_store'] : '';

    echo "<select name='pt_hms_settings[default_store]' style='width: 300px;'>";
    foreach ($stores as $store) {
        $selected = ($selected_store == $store['store_id']) ? 'selected' : '';
        echo "<option value='{$store['store_id']}' $selected>{$store['store_name']}</option>";
    }
    echo "</select>";
}
