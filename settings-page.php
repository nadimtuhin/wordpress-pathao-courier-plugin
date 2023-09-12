<?php
defined( 'ABSPATH' ) || exit;

add_action('admin_menu', 'pt_hms_menu_page');

// Action function for the above hook
function pt_hms_menu_page() {
    // Add a new top-level menu with an icon
    add_menu_page(
        'Pathao Courier Settings', 
        'Pathao Courier', 
        'manage_options', 
        'pt_hms_settings', 
        'pt_hms_settings_page', 
        'dashicons-move', // WordPress Dashicon as the menu icon
        6 // Position in the menu order
    );
}

// Function to display the admin options page
function pt_hms_settings_page() {
?>
<div class="wrap">
    <h2>Pathao Courier Settings</h2>
    <form method="post" action="options.php">
        <?php settings_fields('pt_hms_settings_group'); ?>
        <?php do_settings_sections('pt_hms_settings'); ?>
        <?php submit_button(); ?>
    </form>
</div>
<?php
}

// Hook admin init
add_action('admin_init', 'pt_hms_settings_init');

// Action function for above hook
function pt_hms_settings_init() {
    register_setting('pt_hms_settings_group', 'pt_hms_settings');

    add_settings_section(
        'section_one',
        'API Credentials',
        'section_one_callback',
        'pt_hms_settings'
    );

    add_settings_field(
        'client_id',
        'Client ID',
        'field_client_id_callback',
        'pt_hms_settings',
        'section_one'
    );

    add_settings_field(
        'client_secret',
        'Client Secret',
        'field_client_secret_callback',
        'pt_hms_settings',
        'section_one'
    );

    add_settings_field(
        'username',
        'Username (Email)',
        'field_username_callback',
        'pt_hms_settings',
        'section_one'
    );

    add_settings_field(
        'password',
        'Password',
        'field_password_callback',
        'pt_hms_settings',
        'section_one'
    );

    add_settings_field(
        'environment',
        'Environment',
        'field_environment_callback',
        'pt_hms_settings',
        'section_one'
    );
}

function section_one_callback() {
    echo 'Enter your API credentials below:';
}

function field_client_id_callback() {
  $options = get_option('pt_hms_settings');
  $value = is_array($options) && isset($options['client_id']) ? $options['client_id'] : '';
  echo "<input type='text' name='pt_hms_settings[client_id]' value='{$value}' style='width: 300px;' />";
}

function field_client_secret_callback() {
  $options = get_option('pt_hms_settings');
  $value = is_array($options) && isset($options['client_secret']) ? $options['client_secret'] : '';
  echo "<input type='password' name='pt_hms_settings[client_secret]' value='{$value}' style='width: 300px;' />";
}

function field_username_callback() {
  $options = get_option('pt_hms_settings');
  $value = is_array($options) && isset($options['username']) ? $options['username'] : '';
  echo "<input type='text' name='pt_hms_settings[username]' value='{$value}' style='width: 300px;' />";
}

function field_password_callback() {
  $options = get_option('pt_hms_settings');
  $value = is_array($options) && isset($options['password']) ? $options['password'] : '';
  echo "<input type='password' name='pt_hms_settings[password]' value='{$value}' style='width: 300px;' />";
}

function field_environment_callback() {
  $options = get_option('pt_hms_settings');
  $selected = is_array($options) && isset($options['environment']) ? $options['environment'] : '';
  echo "<select name='pt_hms_settings[environment]' style='width: 300px;'>
      <option value='live' " . selected($selected, 'live', false) . ">Live</option>
      <option value='staging' " . selected($selected, 'staging', false) . ">Staging</option>
  </select>";
}
