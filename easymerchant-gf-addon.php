<?php

/**
 * Plugin Name:   EasyMerchant GF Addon
 * Plugin URI:   https://dightinfotech.com
 * Description:   This is gravity form addon plugins 
 * Version:   1:0.:0
 * Author Name:   Dight Infotech
 * Author URI:   https://dightinfotech.com
 * License:   GPL-2.0+
 * Text Domain:   easymerchant-gf-addon
 */

defined('ABSPATH') || die();

define('GF_EASYMERCHANT_VERSION', '1.1.0');

// Add admin menu and submenu page
function easymerchant_plugin_menu()
{
    add_menu_page(
        'Addon Settings',
        'GF Addon',
        'manage_options',
        'easymerchant-addon-setting',
        'easymerchant_gf_addon_settings_page',
        'dashicons-admin-generic',
        10,
    );
    // add_submenu_page('easymerchant-addon-setting', 'Entries', 'Entries', 'manage_options', 'easymerchant-addon-entries', 'easymerchant_gf_addon_entry_list', 4);
}
add_action('admin_menu', 'easymerchant_plugin_menu');

// Create table when plugin activate
register_activation_hook(__FILE__, 'create_table_in_database');
function create_table_in_database()
{
    global $wpdb;
    $table = $wpdb->prefix . 'easymerchant_api_details';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id int(11) NOT NULL auto_increment,
        api_key varchar(255) NOT NULL,
        api_secret_key varchar(255) NOT NULL,
        mode varchar(255) NOT NULL,
        UNIQUE KEY id (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $selected_form_table_name = $wpdb->prefix . 'easymerchant_selected_form';
    $selected_form_sql = "CREATE TABLE $selected_form_table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        form int(11) NOT NULL,
        UNIQUE KEY id (id)
    ) $charset_collate;";
    dbDelta($selected_form_sql);

    $field_map_form_table = $wpdb->prefix . 'easymerchant_field_map';
    $selected_form_field = "CREATE TABLE $field_map_form_table (
        id int(11) NOT NULL AUTO_INCREMENT,
        formid int(11) NOT NULL,
        first_name_field_id varchar(11) NOT NULL,
        last_name_field_id varchar(11) NOT NULL,
        email_field_id varchar(11) NOT NULL,
        phone_field_id varchar(11) NOT NULL,
        UNIQUE KEY id (id)
    ) $charset_collate;";
    dbDelta($selected_form_field);
}

// Register styles and script
function script_style_register_admin_area()
{
    wp_register_style('style-admin', plugin_dir_url(__FILE__) . '/assets/style.css');
    wp_enqueue_style('style-admin');
    // wp_deregister_script('jquery');
    wp_register_script('jquery', 'https://code.jquery.com/jquery-3.7.1.min.js');
    wp_register_script('script-js', plugin_dir_url(__FILE__) . '/assets/script.js');
    wp_enqueue_script('script-js');
}
add_action('admin_enqueue_scripts', 'script_style_register_admin_area');
// GF Addon Setting page 
function easymerchant_gf_addon_settings_page()
{
?>
    <div class="wrap">
        <h2>Settings</h2>
        <div class="easymerchant-api-settings">
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'easymerchant_api_details';
            $results = $wpdb->get_results(
                "SELECT * FROM $table_name"
            );
            $apiKey = '';
            $apiSecret = '';
            $mode = '';
            foreach ($results as $result) {

                $apiKey .= $result->api_key;
                $apiSecret .= $result->api_secret_key;
                $mode       .= $result->mode;
            }
            ?>
            <form action="" method="post" id="api-setting-form">
                <h3 class="api-form-heading">Fill the api field</h3>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="api-key">API Key</label>
                            </th>
                            <td>
                                <input type="text" name="api_key" id="api-key" class="form-control" value="<?php echo $apiKey; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api-secret-key">API Secret Key</label>
                            </th>
                            <td>
                                <input type="text" name="api_secret_key" id="api-secret-key" class="form-control" value="<?php echo $apiSecret; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api-mode">Mode</label>
                            </th>
                            <td>
                                <label for="live">
                                    <input type="radio" name="api_mode" id="live" class="mode" value="live" <?php if ($mode === 'live') {
                                                                                                                echo 'checked';
                                                                                                            } ?>>
                                    <span>Live</span>
                                </label>
                                <br>
                                <label for="test">
                                    <input type="radio" name="api_mode" id="test" class="mode" value="test" <?php if ($mode === 'test') {
                                                                                                                echo 'checked';
                                                                                                            } ?> <span>Test</span>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="api-setting-submit-button">
                    <input type="submit" value="Save" class="button button-primary" id="api-submit">
                </div>

            </form>
        </div>
        <hr>
        <form method="post" action="" class="gf-list" id="gfList">
            <?php
            $results = GFAPI::get_forms();

            global $wpdb;
            $table_name = $wpdb->prefix . 'easymerchant_selected_form';
            $selected_form_sql = $wpdb->get_results(
                "SELECT * FROM $table_name"
            );
            $formId = '';
            foreach ($selected_form_sql as $sql) {
                $formId .= $sql->form;
            }


            // get the field id from database
            $fieldIdTable = $wpdb->prefix . 'easymerchant_field_map';
            $fieldTable = $wpdb->get_results(
                "SELECT * FROM $fieldIdTable  WHERE formid = $formId"
            );

            $fId = '';
            $lId = '';
            $eId = '';
            $pId = '';
            foreach ($fieldTable as $field) {
                $fId .= $field->first_name_field_id;
                $lId .= $field->last_name_field_id;
                $eId .= $field->email_field_id;
                $pId .= $field->phone_field_id;
            }
            ?>
            <input type="hidden" name="formid" value="<?php echo $formId; ?>">
            <header class="gform-settings-header">
                <div class="gform-settings__wrapper">
                    <select id="gravityformlists" name="gravityformlist">
                        <option value="">Search Form...</option>
                        <?php
                        foreach ($results as $result) {
                            $selected = '';
                            if ($result['id'] == $formId) {
                                $selected .= 'selected';
                            }
                            echo '<option value="' . $result['id'] . '" ' . $selected . '>' . $result['title'] . '</option>';
                        }
                        ?>
                    </select>
                    <div class="gform-settings-selected-form">
                        <ul class="ul-list-form">
                        </ul>
                    </div>
                </div>
            </header>
            <section class="easymerchant-gfaddon-settings-section">
                <div class="gform-settings__wrapper">
                    <table class="setting-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Field Name</th>
                                <th>Field List</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>First Name</td>
                                <td>
                                    <select name="firstnamefield" id="firstnamefield">
                                        <?php
                                        $printed_ids = [];

                                        foreach ($results as $fields) {
                                            foreach ($fields['fields'] as $field) {
                                                $gffield = GFAPI::get_field($formId, $field->id);
                                                if ($gffield['label'] === 'Name') {
                                                    foreach ($gffield['inputs'] as $input) {
                                                        if ($input['label'] === 'First' || $input['label'] === 'Last') {
                                                            $current_id = $input['id'];
                                                            if (!in_array($current_id, $printed_ids)) {
                                                                $selected = '';
                                                                if ($gffield['formId'] == $formId && $current_id == $fId) {
                                                                    $selected .= 'selected';
                                                                }
                                                                echo '<option value="' . $current_id . '" form-id="' . $gffield['formId'] . '" ' . $selected . '>' . $input['label'] . '</option>';
                                                                $printed_ids[] = $current_id;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($gffield && $gffield['label'] != 'Name') {
                                                    $current_id = $gffield['id'];
                                                    if (!in_array($current_id, $printed_ids)) {
                                                        $selected = '';
                                                        if ($gffield['formId'] == $formId && $current_id == $fId) {
                                                            $selected .= 'selected';
                                                        }
                                                        echo '<option value="' . $current_id . '" form-id="' . $gffield['formId'] . '" ' . $selected . '>' . $gffield['label'] . '</option>';
                                                        $printed_ids[] = $current_id;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Last Name</td>
                                <td>
                                    <select name="lastnamefield" id="lastnamefield">
                                        <?php
                                        $printed_ids = [];
                                        foreach ($results as $fields) {
                                            foreach ($fields['fields'] as $field) {
                                                $gffield = GFAPI::get_field($formId, $field->id);
                                                if ($gffield['label'] === 'Name') {
                                                    foreach ($gffield['inputs'] as $input) {
                                                        if ($input['label'] === 'First' || $input['label'] === 'Last') {
                                                            $current_id = $input['id'];
                                                            if (!in_array($current_id, $printed_ids)) {
                                                                $selected = '';
                                                                if ($gffield['formId'] == $formId && $current_id == $lId) {
                                                                    $selected .= 'selected';
                                                                }
                                                                echo '<option value="' . $current_id . '" form-id="' . $gffield['formId'] . '" ' . $selected . '>' . $input['label'] . '</option>';
                                                                $printed_ids[] = $current_id;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($gffield && $gffield['label'] != 'Name') {
                                                    $current_id = $gffield['id'];
                                                    if (!in_array($current_id, $printed_ids)) {
                                                        $selected = '';
                                                        if ($gffield['formId'] == $formId && $current_id == $lId) {
                                                            $selected .= 'selected';
                                                        }
                                                        echo '<option value="' . $current_id . '" form-id="' . $gffield['formId'] . '" ' . $selected . '>' . $gffield['label'] . '</option>';
                                                        $printed_ids[] = $current_id;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Email</td>
                                <td>
                                    <select name="emailfield" id="emailfield">
                                        <?php
                                        $printed_ids = [];
                                        foreach ($results as $fields) {
                                            foreach ($fields['fields'] as $field) {
                                                $gffield = GFAPI::get_field($formId, $field->id);
                                                if ($gffield['label'] === 'Name') {
                                                    foreach ($gffield['inputs'] as $input) {
                                                        if ($input['label'] === 'First' || $input['label'] === 'Last') {
                                                            $current_id = $input['id'];
                                                            if (!in_array($current_id, $printed_ids)) {
                                                                $selected = '';
                                                                if ($gffield['formId'] == $formId && $current_id == $eId) {
                                                                    $selected .= 'selected';
                                                                }
                                                                echo '<option value="' . $current_id . '" form-id="' . $gffield['formId'] . '" ' . $selected . '>' . $input['label'] . '</option>';
                                                                $printed_ids[] = $current_id;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($gffield && $gffield['label'] != 'Name') {
                                                    $current_id = $gffield['id'];
                                                    if (!in_array($current_id, $printed_ids)) {
                                                        $selected = '';
                                                        if ($gffield['formId'] == $formId && $current_id == $eId) {
                                                            $selected .= 'selected';
                                                        }
                                                        echo '<option value="' . $current_id . '" form-id="' . $gffield['formId'] . '" ' . $selected . '>' . $gffield['label'] . '</option>';
                                                        $printed_ids[] = $current_id;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Phone</td>
                                <td>
                                    <select name="phonefield" id="phonefield">
                                        <?php
                                        $printed_ids = [];
                                        foreach ($results as $fields) {
                                            foreach ($fields['fields'] as $field) {
                                                $gffield = GFAPI::get_field($formId, $field->id);
                                                if ($gffield['label'] === 'Name') {
                                                    foreach ($gffield['inputs'] as $input) {
                                                        if ($input['label'] === 'First' || $input['label'] === 'Last') {
                                                            $current_id = $input['id'];
                                                            if (!in_array($current_id, $printed_ids)) {
                                                                $selected = '';
                                                                if ($gffield['formId'] == $formId && $current_id == $pId) {
                                                                    $selected .= 'selected';
                                                                }
                                                                echo '<option value="' . $current_id . '" form-id="' . $gffield['formId'] . '" ' . $selected . '>' . $input['label'] . '</option>';
                                                                $printed_ids[] = $current_id;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($gffield && $gffield['label'] != 'Name') {
                                                    $current_id = $gffield['id'];
                                                    if (!in_array($current_id, $printed_ids)) {
                                                        $selected = '';
                                                        if ($gffield['formId'] == $formId && $current_id == $pId) {
                                                            $selected .= 'selected';
                                                        }
                                                        echo '<option value="' . $current_id . '" form-id="' . $gffield['formId'] . '" ' . $selected . '>' . $gffield['label'] . '</option>';
                                                        $printed_ids[] = $current_id;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="form-gf-submit">
                <input type="submit" id="submit" value="Submit" class="button button-primary" />
            </div>
        </form>

    </div>
<?php
}

// Ajax Call to save or update Api data in the database
add_action('wp_ajax_save_api_in_database', 'save_api_in_database');
add_action('wp_ajax_nopriv_save_api_in_database', 'save_api_in_database');
function save_api_in_database()
{
    global $wpdb;
    $table = $wpdb->prefix . 'easymerchant_api_details';
    $apiKey = $_POST['api_key'];
    $apiSecret = $_POST['api_secret_key'];
    $mode = $_POST['api_mode'];

    // Check if entry already exists
    $existing_entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table"));

    if ($existing_entry) {
        // Entry exists, update the values
        $wpdb->update(
            $table,
            array(
                'api_key' => $apiKey,
                'api_secret_key' => $apiSecret,
                'mode' => $mode,
            ),
            array('id' => $existing_entry->id),
            array(
                '%s',
                '%s',
                '%s',
            ),
            array('%d')
        );
    } else {
        // Entry doesn't exist, insert new values
        $wpdb->insert(
            $table,
            array(
                'api_key' => $apiKey,
                'api_secret_key' => $apiSecret,
                'mode' => $mode,
            ),
            array(
                '%s',
                '%s',
                '%s',
            )
        );
    }

    // Send response
    wp_send_json_success('Api Key and Secret Key saved successfully');
}

// Selected Form save in database
add_action('wp_ajax_save_form_in_database', 'save_form_in_database');
add_action('wp_ajax_nopriv_save_form_in_database', 'save_form_in_database');
function save_form_in_database()
{
    global $wpdb;
    $table = $wpdb->prefix . 'easymerchant_selected_form';
    $selectedForm = $_POST['gravityformlist'];
    $existing_entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table"));
    if ($existing_entry) {
        $wpdb->update(
            $table,
            array(
                'form' => $selectedForm,
            ),
            array('id' => $existing_entry->id),
            array(
                '%s',
            ),
            array('%d')
        );
    } else {
        $wpdb->insert(
            $table,
            array(
                'form' => $selectedForm,
            ),
            array(
                '%s',
            )
        );
        // Send success response
        wp_send_json_success('Form saved successfully');
    }
    // Send error response if something went wrong
    wp_send_json_error('Failed to save form');
}

// Save Field
add_action('wp_ajax_save_field_id_in_database', 'save_field_id_in_database');
add_action('wp_ajax_nopriv_save_field_id_in_database', 'save_field_id_in_database');
function save_field_id_in_database()
{
    global $wpdb;
    $table = $wpdb->prefix . 'easymerchant_field_map';
    $formId = $_POST['formid'];
    $firstNameFieldId = $_POST['firstnamefield'];
    $lastNameFieldId = $_POST['lastnamefield'];
    $emailFieldId = $_POST['emailfield'];
    $phoneFieldId = $_POST['phonefield'];
    $existing_entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table"));
    if ($existing_entry && $formId === $existing_entry->id) {
        $wpdb->update(
            $table,
            array(
                'formid' => $formId,
                'first_name_field_id' => $firstNameFieldId,
                'last_name_field_id' => $lastNameFieldId,
                'email_field_id' => $emailFieldId,
                'phone_field_id' => $phoneFieldId
            ),
            array('id' => $existing_entry->id),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            ),
            array('%d')
        );
    } else {
        $wpdb->insert(
            $table,
            array(
                'formid' => $formId,
                'first_name_field_id' => $firstNameFieldId,
                'last_name_field_id' => $lastNameFieldId,
                'email_field_id' => $emailFieldId,
                'phone_field_id' => $phoneFieldId
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
        // Send success response
        wp_send_json_success('Form saved successfully');
    }
    // Send error response if something went wrong
    wp_send_json_error('Failed to save form');
}

// API triggered on submission
add_action('gform_after_submission', 'send_entry_to_easymerchant_crm', 10, 2);
function send_entry_to_easymerchant_crm($entry, $form)
{
    global $wpdb;
    $table = $wpdb->prefix . 'easymerchant_api_details';
    $existing_entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table"));

    if ($existing_entry) {
        // Determine API URL based on mode
        if ($existing_entry->mode === 'test') {
            $apiurl = 'https://stage-api.stage-easymerchant.io/api/v1';
        } else {
            // $apiurl = 'https://stage-api.stage-easymerchant.io/api/v1';
        }
        $fieldTable = $wpdb->prefix . 'easymerchant_field_map';
        $fieldEntries = $wpdb->get_row($wpdb->prepare("SELECT * FROM $fieldTable"));
        if ($form['id'] === intval($fieldEntries->formid)) {
            $firstNameFieldId = $fieldEntries->first_name_field_id;
            $lastNameFieldId = $fieldEntries->last_name_field_id;
            $emailFieldId = $fieldEntries->email_field_id;
            $phoneFieldId = $fieldEntries->phone_field_id;
        }
        // Check if new entries exist
        if (!empty($entry)) {
            $lead_fname = rgar($entry, $firstNameFieldId);
            $lead_lname = rgar($entry, $lastNameFieldId);
            $lead_email = rgar($entry, $emailFieldId);
            $lead_phno = rgar($entry, $phoneFieldId);
            // Set up cURL request to send data to API
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiurl . '/leads',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode(array(
                    "lead_fname" => $lead_fname,
                    "lead_lname" => $lead_lname,
                    "lead_email" => $lead_email,
                    "lead_phno" =>  $lead_phno
                )),
                CURLOPT_HTTPHEADER => array(
                    'X-Api-Key:' . $existing_entry->api_key,
                    'X-Api-Secret:' . $existing_entry->api_secret_key,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            echo $response;

            die();
        }
    }
}
