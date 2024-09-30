<?php

namespace WDTForminatorIntegration;

defined('ABSPATH') or die("Cannot access pages directly.");

/**
 * @package wpDataTables integration for Forminator Forms
 * @version 1.3.2
 *
 * @wordpress-plugin
 * Plugin Name: wpDataTables integration for Forminator Forms
 * Description: Tool that adds "Forminator Form" as a new table type and allows you to create wpDataTables from Forminator Forms entries data (submissions).
 * Version: 1.3.2
 * Author: TMS-Plugins
 * Author URI: https://www.tmsproducts.io/
 * Text Domain: wpdatatables
 * Domain Path: /languages
 */


use DateTime;
use stdClass;
use WDTTools;
use Forminator_API;

// Full path to the WDT FRF root directory
define('WDT_FRF_ROOT_PATH', plugin_dir_path(__FILE__));
// URL of WDT FRF integration plugin
define('WDT_FRF_ROOT_URL', plugin_dir_url(__FILE__));
// Current version of WDT FRF integration plugin
define('WDT_FRF_VERSION', '1.3.2');
// Required wpDataTables version
define('WDT_FRF_VERSION_TO_CHECK', '3.4.2.16');

// Init wpDataTables integration for Forminator Forms add-on
add_action('plugins_loaded', array('WDTForminatorIntegration\Plugin', 'init'), 10);

// Enqueue Forminator files
add_action('wpdatatables_enqueue_on_edit_page', array('WDTForminatorIntegration\Plugin', 'wdtForminatorIntegrationEnqueue'));

// Add "Forminator Form" in "Input data source type" dropdown on "Data Source" tab
add_action('wpdatatables_add_table_type_option', array('WDTForminatorIntegration\Plugin', 'addForminatorTableTypeOption'));

// Add Forminator Form HTML elements on "Data Source" tab on table configuration page
add_action('wpdatatables_add_data_source_elements', array('WDTForminatorIntegration\Plugin', 'addForminatorOnDataSourceTab'));

// Get form fields AJAX action
add_action('wp_ajax_wdt_forminator_get_form_fields', array('WDTForminatorIntegration\Plugin', 'getForminatorFormFields'));

// Save table configuration
add_action('wp_ajax_wdt_forminator_save_table_config', array('WDTForminatorIntegration\Plugin', 'saveTableConfig'));

// Extend the wpDataTables supported data sources
add_action('wpdatatables_generate_forminator', array('WDTForminatorIntegration\Plugin', 'forminatorBasedConstruct'), 10, 3);

// Add "Forminator Form" tab on table configuration page
add_action('wpdatatables_add_table_configuration_tab', array('WDTForminatorIntegration\Plugin', 'addForminatorTab'));

// Add tabpanel for "Forminator Form" tab on table configuration page
add_action('wpdatatables_add_table_configuration_tabpanel', array('WDTForminatorIntegration\Plugin', 'addForminatorTabPanel'));

// Extend table config before saving table to DB
add_filter('wpdatatables_filter_insert_table_array', array('WDTForminatorIntegration\Plugin', 'extendTableConfig'), 10, 1);


/**
 * Class Plugin
 * Main entry point of the wpDataTables Forminator Forms integration
 * @package WDTForminatorIntegration
 */
class Plugin
{

    public static $initialized = false;

    /**
     * Instantiates the class
     * @return bool
     */
    public static function init()
    {
        // Check if wpDataTables and Forminator Forms are installed
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (!(is_plugin_active('forminator/forminator.php'))) {
            add_action('admin_notices', array('WDTForminatorIntegration\Plugin', 'wdtNotInstalled'));
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return false;
        }
        if (!defined('WDT_ROOT_PATH')) {
            add_action('admin_notices', array('WDTForminatorIntegration\Plugin', 'wdtNotInstalled'));
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return false;
        }

        // Check if wpDataTables required version is installed
        if (version_compare(WDT_CURRENT_VERSION, WDT_FRF_VERSION_TO_CHECK) < 0) {
            // Show message if required wpDataTables version is not installed
            add_action('admin_notices', array('WDTForminatorIntegration\Plugin', 'wdtRequiredVersionMissing'));
            deactivate_plugins( plugin_basename( __FILE__ ) );
            return false;
        }

        \WPDataTable::$allowedTableTypes[] = 'forminator';

        return self::$initialized = true;
    }

    /**
     * Show message if wpDataTables or Forminator Forms are not installed
     */
    public static function wdtNotInstalled()
    {
        $message = esc_html__('wpDataTables integration for Forminator Forms is an add-on - please install and activate wpDataTables (Free or Premium) and Forminator Forms (Free or PRO) to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Show message if required wpDataTables version is not installed
     */
    public static function wdtRequiredVersionMissing()
    {
        $message = esc_html__('wpDataTables integration for Forminator Forms add-on requires wpDataTables version ' . WDT_FRF_VERSION_TO_CHECK . '. Please update wpDataTables plugin to be able to use it!', 'wpdatatables');
        echo "<div class=\"error\"><p>{$message}</p></div>";
    }

    /**
     * Enqueue all necessary styles and scripts
     */
    public static function wdtForminatorIntegrationEnqueue()
    {
        // Forminator Forms integration CSS
        wp_enqueue_style('wdt-frf-wizard', WDT_FRF_ROOT_URL . 'assets/css/table_creation_wizard.css', array(), WDT_FRF_VERSION);
        // Forminator Forms integration JS
        wp_enqueue_script('wdt-frf-table-config', WDT_FRF_ROOT_URL . 'assets/js/frf_table_config_object.js', array(), WDT_FRF_VERSION, true);
        wp_enqueue_script('wdt-frf-wizard', WDT_FRF_ROOT_URL . 'assets/js/table_creation_wizard.js', array(), WDT_FRF_VERSION, true);

        \WDTTools::exportJSVar('wdtFRFSettings', \WDTTools::getDateTimeSettings());
        \WDTTools::exportJSVar('wdtFRFTranslationStrings', \WDTTools::getTranslationStringsPlugin());
    }

    /**
     * Method that adds "Forminator From" option in "Input data source type" dropdown
     */
    public static function addForminatorTableTypeOption()
    {
        echo '<option value="forminator">Forminator Form</option>';
    }

    /**
     * Adds Forminator Form HTML elements on table configuration page
     */
    public static function addForminatorOnDataSourceTab()
    {
        ob_start();
        include WDT_FRF_ROOT_PATH . 'templates/data_source_block.inc.php';
        $forminatorDataSource = apply_filters('wdt_forminator_data_source_block', ob_get_contents());
        ob_end_clean();

        echo $forminatorDataSource;
    }

    /**
     * Helper method to get all forms names and IDs
     * @param $formType
     */
    public static function getForminatorFormsArr($formType)
    {
        $formsArray = [];
        switch ($formType) {
            case 'forms':
                $formsArray = Forminator_API::get_forms(null,1,-1,'');
                break;
            case 'polls':
                $formsArray = Forminator_API::get_polls(null,1,-1,'');
                break;
            case 'quizzes':
                $formsArray = Forminator_API::get_quizzes(null,1,-1,'');
                break;
        }
        return $formsArray;
    }


    /**
     * Helper method to get form fields
     */
    public static function getForminatorFormFields()
    {
        $nonce = sanitize_text_field($_POST['nonce']);

        if (!current_user_can('manage_options') || !wp_verify_nonce($nonce, 'wdtEditNonce')) {
            exit();
        }

        $formData = self::sanitizeForminatorConfig(json_decode(
            stripslashes_deep($_POST['formData'])
        ));
        $fieldArr = [];

        $formId = $formData->formId;
        $formType = in_array($formData->formType, ['forminator_forms', 'forminator_polls', 'forminator_quizzes']) ? $formData->formType : '';
        if (isset($formData->formChartType)) {
            if(in_array($formData->formChartType, ['bar', 'pie'])){
                $formChartType = $formData->formChartType;
            } else {
                $formChartType = '';
            }
        } else {
            $formChartType = '';
        }
        if ($formId) {
            if ($formType != '') {
                $fieldArr['formType'] = $formType;
                $fieldArr['formChartType'] = $formChartType;
                $formFields = self::prepareFormFieldsFromAPI($formId, $formType);
                if (!is_wp_error($formFields['fields'])) {
                    if ($formFields['fields'] != []) {
                        switch ($formType) {
                            case 'forminator_forms':
                                foreach ($formFields['fields'] as $key => $field) {
                                    if (!in_array($field->raw['type'],
                                        ['page-break', 'section', 'html', 'captcha', 'gdprcheckbox', 'password'])
                                    ) {
                                        if ($field->parent_group == '')
                                            $fieldArr['data'][$key] = $field->raw;
                                    }
                                }
                                break;
                            case 'forminator_polls':
                                if ($formChartType == 'bar') {
                                    $fieldArr['data']['poll_question'] = $formFields['poll_question'];
                                    foreach ($formFields['fields'] as $key => $field) {
                                        $fieldArr['data'][$field->raw['element_id']] = $field->raw['title'];
                                    }
                                } else if ($formChartType == 'pie') {
                                    $fieldArr['data']['poll_answers'] = $formFields['poll_answers'];
                                    $fieldArr['data']['poll_total_votes'] = $formFields['poll_total_votes'];
                                }
                                break;
                            case 'forminator_quizzes':
                                foreach ($formFields['fields'] as $key => $field) {
                                    $fieldArr['data'][$field['slug']] = $field['title'];
                                }
                                if ($formData->formHasLeads) {
                                    if (isset($formFields['leads_email'])) $fieldArr['data']['leads_email'] = $formFields['leads_email'];
                                    if (isset($formFields['leads_name'])) $fieldArr['data']['leads_name'] = $formFields['leads_name'];
                                }
                                if ($formFields['model']->quiz_type == 'knowledge') {
                                    if (isset($formFields['quiz_result_correct'])) $fieldArr['data']['quiz_result_correct'] = $formFields['quiz_result_correct'];
                                    if (isset($formFields['quiz_result_incorrect'])) $fieldArr['data']['quiz_result_incorrect'] = $formFields['quiz_result_incorrect'];
                                    if (isset($formFields['quiz_result_total'])) $fieldArr['data']['quiz_result_total'] = $formFields['quiz_result_total'];
                                } else {
                                    if (isset($formFields['quiz_result'])) $fieldArr['data']['quiz_result'] = $formFields['quiz_result'];
                                }
                                break;
                        }
                    } else {
                        echo json_encode(array('error' => 'Form has no fields!'));
                        exit();
                    }
                } else {
                    echo json_encode(array('error' => $formFields['fields']->get_error_message()));
                    exit();
                }
            } else {
                echo json_encode(array('error' => 'Form type is unknown'));
                exit();
            }
            echo json_encode($fieldArr);
            exit();
        } else {
            echo json_encode(array('error' => 'Form data could not be read!'));
            exit();
        }

    }

    /**
     * Validate and save Forminator based wpDataTable config to DB
     */
    public static function saveTableConfig()
    {
        // Sanitize NONCE
        $nonce = sanitize_text_field($_POST['nonce']);

        if (!current_user_can('manage_options') || !wp_verify_nonce($nonce, 'wdtEditNonce')) {
            exit();
        }

        // Sanitize Forminator Config
        $forminatorData = self::sanitizeForminatorConfig(json_decode(
            stripslashes_deep($_POST['forminator'])
        ));

        if ($forminatorData->formId) {
            // Create a table object
            $table = json_decode(stripslashes_deep($_POST['table']));
            $table->content = json_encode(
                array(
                    'formId' => $forminatorData->formId,
                    'formType' => $forminatorData->formType,
                    'formHasLeads' => $forminatorData->formHasLeads,
                    'formChartType' => $forminatorData->formChartType,
                    'fieldIds' => $forminatorData->fields
                )
            );

            \WDTConfigController::saveTableConfig($table);
        } else {
            echo json_encode(array('error' => 'Form data could not be read!'));
        }
        exit();
    }

    /**
     * Helper method for sanitizing the user input in the forminator config
     * @param $forminatorData
     * @return stdClass
     */
    public static function sanitizeForminatorConfig($forminatorData)
    {
        $sanitizedForminatorData = new stdClass();

        if (isset($forminatorData->fields)) {
            foreach ($forminatorData->fields as $key => $field) {
                $sanitizedForminatorData->fields[$key] = sanitize_text_field($field);
            }
        } else {
            $sanitizedForminatorData->fields = null;
        }

        if (isset($forminatorData->dateFilterLogic)) {
            $sanitizedForminatorData->dateFilterLogic = sanitize_text_field($forminatorData->dateFilterLogic);
        } else {
            $sanitizedForminatorData->dateFilterLogic = null;
        }

        if (isset($forminatorData->dateFilterFrom)){
            $sanitizedForminatorData->dateFilterFrom = sanitize_text_field($forminatorData->dateFilterFrom);
        } else {
            $sanitizedForminatorData->dateFilterFrom = null;
        }

        if (isset($forminatorData->dateFilterTo)){
            $sanitizedForminatorData->dateFilterTo = sanitize_text_field($forminatorData->dateFilterTo);
        } else {
            $sanitizedForminatorData->dateFilterTo = null;
        }

        if (isset($forminatorData->dateFilterTimeUnits)){
            $sanitizedForminatorData->dateFilterTimeUnits = (int)($forminatorData->dateFilterTimeUnits);
        } else {
            $sanitizedForminatorData->dateFilterTimeUnits = null;
        }

        if (isset($forminatorData->dateFilterTimePeriod)){
            $sanitizedForminatorData->dateFilterTimePeriod = sanitize_text_field($forminatorData->dateFilterTimePeriod);
        } else {
            $sanitizedForminatorData->dateFilterTimePeriod = null;
        }

        if (isset($forminatorData->formIDFilterFrom)){
            $sanitizedForminatorData->formIDFilterFrom = (int)($forminatorData->formIDFilterFrom);
        } else {
            $sanitizedForminatorData->formIDFilterFrom = null;
        }

        if (isset($forminatorData->formIDFilterTo)){
            $sanitizedForminatorData->formIDFilterTo = (int)($forminatorData->formIDFilterTo);
        } else {
            $sanitizedForminatorData->formIDFilterTo = null;
        }

        if (isset($forminatorData->formType)){
            $sanitizedForminatorData->formType = sanitize_text_field($forminatorData->formType);
        } else {
            $sanitizedForminatorData->formType = null;
        }

        if (isset($forminatorData->formChartType)){
            $sanitizedForminatorData->formChartType = sanitize_text_field($forminatorData->formChartType);
        } else {
            $sanitizedForminatorData->formChartType = null;
        }

        if (isset($forminatorData->formId)){
            $sanitizedForminatorData->formId = (int)$forminatorData->formId;
        } else {
            $sanitizedForminatorData->formId = null;
        }

        if (isset($forminatorData->formHasLeads)){
            $sanitizedForminatorData->formHasLeads = (int)$forminatorData->formHasLeads;
        } else {
            $sanitizedForminatorData->formHasLeads = null;
        }

        return $sanitizedForminatorData;
    }

    /**
     * Method that pass $array and $params to wpDataTable arrayBasedConstruct Method
     * that will fill wpDataTable object
     * @param $wpDataTable - WPDataTable object
     * @param $content - stdClass with form ID and form fields IDs
     * @param $params - parameters that are prepared in WPDataTable fillFromData method
     */
    public static function forminatorBasedConstruct($wpDataTable, $content, $params)
    {
        $content = json_decode($content);
        /** @var \WPDataTable $wpDataTable */
        if ($wpDataTable->getWpId()) {
            $table = \WDTConfigController::loadTableFromDB($wpDataTable->getWpId());
            $forminatorData = json_decode($table->advanced_settings)->forminator;
        } else {
            $forminatorData = null;
        }

        if (empty($params['columnTitles'])) {
            $params['columnTitles'] = self::getColumnHeaders($content->formId, $content->formType, $content->formChartType, $content->fieldIds);
        }

        $wpDataTable->arrayBasedConstruct(self::generateFormArray($content, $forminatorData), $params);
    }

    /**
     * Prepare form fields from Forminator API based on form type
     * @param $formId
     * @param $formType
     * @return array
     */
    public static function prepareFormFieldsFromAPI($formId, $formType)
    {
        $data = [];
        switch ($formType) {
            case'forminator_forms':
                $data['fields'] = Forminator_API::get_form_fields($formId);
                break;
            case 'forminator_polls':
                $pollModel = Forminator_API::get_poll($formId);
                $data['model'] = $pollModel;
                $data['fields'] = $pollModel->fields;
                $data['poll_question'] = $pollModel->settings['poll-question'];
                $data['poll_answers'] = esc_html__('Poll answers', 'wpdatatables');
                $data['poll_total_votes'] = esc_html__('Total Votes', 'wpdatatables');
                break;
            case 'forminator_quizzes':
                $quizModel = Forminator_API::get_quiz($formId);
                $data['model'] = $quizModel;
                if ($quizModel->settings['hasLeads'] == true) {
                    $data['leads_email'] = esc_html__('Email', 'wpdatatables');
                    $data['leads_name'] = esc_html__('Name', 'wpdatatables');
                }
                if ($quizModel->quiz_type == 'nowrong') {
                    $data['quiz_result'] = esc_html__('Quiz results', 'wpdatatables');
                } else {
                    $data['quiz_result_correct'] = esc_html__('Correct answers', 'wpdatatables');
                    $data['quiz_result_incorrect'] = esc_html__('Incorrect answers', 'wpdatatables');
                    $data['quiz_result_total'] = esc_html__('Quiz results (Correct/Total)', 'wpdatatables');
                }
                $data['fields'] = $quizModel->questions;
                break;
        }
        return $data;
    }

    /**
     * Get form entries from Forminator API based on form type
     * @param $formId
     * @param $formType
     * @return array
     */
    public static function getFormEntriesFromAPI($formId, $formType)
    {
        $formEntries = [];
        switch ($formType) {
            case'forminator_forms':
                $formEntries = Forminator_API::get_form_entries($formId);
                break;
            case 'forminator_polls':
                $formEntries = Forminator_API::get_poll_entries($formId);
                break;
            case 'forminator_quizzes':
                $formEntries = Forminator_API::get_quiz_entries($formId);
                break;
        }
        return $formEntries;
    }

    /**
     * Create column nad origin headers for special fields
     * @param $fields
     * @param $headers
     */
    public static function specialFieldsFillHeaders($headers, $fields)
    {
        $specialFields = [
            'poll_question',
            'leads_email',
            'leads_name',
            'quiz_result',
            'quiz_result_correct',
            'quiz_result_incorrect',
            'quiz_result_total'
        ];
        foreach ($specialFields as $specialField) {
            if (isset($fields[$specialField])) $headers[$specialField] = $fields[$specialField];
        }
        return $headers;
    }

    /**
     * Generate array for table
     * @param $content
     * @param $forminatorData
     * @return array
     */
    public static function generateFormArray($content, $forminatorData)
    {
        $tableArray = array();
        $origHeaders = array();

        $searchCriteria = self::prepareSearchCriteria($content->formId, $forminatorData);
        $count = 0;
        $entries = \Forminator_Form_Entry_Model::query_entries($searchCriteria, $count);
        if ($entries != []) {
            $fields = self::prepareFormFieldsFromAPI($content->formId, $content->formType);
            foreach ($fields['fields'] as $field) {
                $elementID = $content->formType == 'forminator_quizzes' ? $field['slug'] : $field->raw['element_id'];
                $origHeaders[$elementID] = WDTTools::generateMySQLColumnName($elementID, $origHeaders);
            }
            $origHeaders = self::specialFieldsFillHeaders($origHeaders, $fields);

            if ($content->formType != 'forminator_polls') {
                /** @var array $entries */
                foreach ($entries as $entry) {
                    $tableArrayEntry = array();

                    foreach ($fields['fields'] as $key => $field) {
                        if ($content->formType == 'forminator_quizzes') {
                            if (in_array($field['slug'], $content->fieldIds)) {
                                $elementID = $field['slug'];
                                $tableArrayEntry[$origHeaders[$elementID]] = self::prepareFieldsData($field, $entry, $field->slug);;
                            }
                        } else {
                            if (in_array($field->slug, $content->fieldIds)) {
                                $elementID = $field->raw['element_id'];
                                $tableArrayEntry[$origHeaders[$elementID]] = self::prepareFieldsData($field, $entry, $field->slug);
                            }
                        }
                    }
                    if ($content->formType == 'forminator_quizzes') {
                        if ($content->formHasLeads) {
                            if (isset($fields['leads_email'])) $tableArrayEntry['leads_email'] = isset($entry->meta_data['email-1']) ? $entry->meta_data['email-1']['value'] : '';
                            if (isset($fields['leads_name'])) $tableArrayEntry['leads_name'] = isset($entry->meta_data['name-1']) ? $entry->meta_data['name-1']['value'] : '';
                        }

                        if ($field['type'] == 'nowrong') {
                            $resultValue = $entry->meta_data['entry']['value'][0]['value']['result']['title'];
                            $tableArrayEntry['quiz_result'] = $resultValue;
                        } else {
                            $allAnswers = $entry->meta_data['entry']['value'];
                            $totalAnswers = count($entry->meta_data['entry']['value']);
                            $rightAnswer = 0;
                            $wrongAnswer = 0;
                            foreach ($allAnswers as $answer) {
                                if ($answer['isCorrect']) {
                                    $rightAnswer++;
                                } else {
                                    $wrongAnswer++;
                                }
                            }
                            $tableArrayEntry['quiz_result_correct'] = $rightAnswer;
                            $tableArrayEntry['quiz_result_incorrect'] = $wrongAnswer;
                            $tableArrayEntry['quiz_result_total'] = $rightAnswer . '/' . $totalAnswers;
                        }
                    }

                    foreach (array_intersect($content->fieldIds, ['date_created_sql', 'entry_id', 'ip']) as $commonField) {
                        $tableArrayEntry[strtolower(str_replace(array(' ', '_'), '', $commonField))] =
                            $commonField === 'ip' ? $entry->meta_data['_forminator_user_ip']['value'] : $entry->$commonField;
                    }

                    $tableArray[] = $tableArrayEntry;
                }
            } else {
                $fieldsArray = $fields['model']->get_fields_as_array();
                $pollEntriesMap = \Forminator_Form_Entry_Model::map_polls_entries($content->formId, $fieldsArray);
                if ($content->formChartType != 'pie') {
                    $tableArrayEntry['poll_question'] = $fields['poll_question'];
                    foreach ($fields['fields'] as $field) {
                        if (in_array($field->slug, $content->fieldIds)) {
                            $elementID = $field->raw['element_id'];
                            $tableArrayEntry[$origHeaders[$elementID]] = isset($pollEntriesMap[$elementID]) ? $pollEntriesMap[$elementID] : 0;
                        }
                    }
                    $tableArray[] = $tableArrayEntry;
                } else {
                    foreach ($fields['fields'] as $key => $field) {
                        $elementID = $field->raw['element_id'];
                        foreach ($content->fieldIds as $fieldId) {
                            if ($fieldId == 'poll_answers') {
                                $tableArray[$key][$fieldId] = $field->raw['title'];
                            } else {
                                $tableArray[$key][$fieldId] = isset($pollEntriesMap[$elementID]) ? $pollEntriesMap[$elementID] : 0;
                            }
                        }
                    }
                }
            }
        } else {
            return [];
        }
        return $tableArray;
    }

    /**
     * Method that return array that will be passed in Forminator Form method
     * to filter the entries
     * @param $formId
     * @param $forminatorData
     * @return array
     */
    public static function prepareSearchCriteria($formId, $forminatorData)
    {
        $searchCriteria = array(
            'form_id' => $formId,
            'is_spam' => 0,
            'per_page' => Forminator_API::count_entries($formId),
            'offset' => 0,
            'order_by' => 'entries.date_created',
            'order' => 'DESC',
        );

        if ($forminatorData !== null) {

            if ($forminatorData->dateFilterLogic) {

                if ($forminatorData->dateFilterLogic === 'range') {

                    $dateFormat = get_option('wdtDateFormat');

                    if ($forminatorData->dateFilterFrom) {
                        $searchCriteria['date_created'][0] = DateTime::createFromFormat(
                            $dateFormat,
                            $forminatorData->dateFilterFrom
                        )->format('Y-m-d');
                    }

                    if ($forminatorData->dateFilterTo) {
                        $searchCriteria['date_created'][1] = DateTime::createFromFormat(
                            $dateFormat,
                            $forminatorData->dateFilterTo
                        )->format('Y-m-d');
                    }

                } else {
                    if ($forminatorData->dateFilterTimeUnits && $forminatorData->dateFilterTimePeriod) {
                        $searchCriteria['date_created'][0] = date('Y-m-d', strtotime("-{$forminatorData->dateFilterTimeUnits} {$forminatorData->dateFilterTimePeriod}"));
                        $searchCriteria['date_created'][1] = date("Y-m-d");
                    }
                }

                if ($forminatorData->dateFilterTo) {
                    $searchCriteria['date_created'][1] = DateTime::createFromFormat(
                        $dateFormat,
                        $forminatorData->dateFilterTo
                    )->format('Y-m-d');
                }
            }

            if ($forminatorData->formIDFilterFrom) {
                $searchCriteria['min_id'] = $forminatorData->formIDFilterFrom;
            }

            if ($forminatorData->formIDFilterTo) {
                $searchCriteria['max_id'] = $forminatorData->formIDFilterTo;
            }
        }

        return $searchCriteria;
    }

    /**
     * Add "Forminator Form" tab on table configuration page
     */
    public static function addForminatorTab()
    {
        ob_start();
        include WDT_FRF_ROOT_PATH . 'templates/tab.inc.php';
        $forminatorTab = apply_filters('wdt_forminator_tab', ob_get_contents());
        ob_end_clean();

        echo $forminatorTab;
    }

    /**
     * Add tab panel for "Forminator Form" tab on table configuration page
     */
    public static function addForminatorTabPanel()
    {
        ob_start();
        include WDT_FRF_ROOT_PATH . 'templates/tabpanel.inc.php';
        $forminatorTabpanel = apply_filters('wdt_forminator_tabpanel', ob_get_contents());
        ob_end_clean();

        echo $forminatorTabpanel;
    }

    /**
     * Function that extend table config before saving table to the database
     * @param $tableConfig - array that contains table configuration
     * @return mixed
     */
    public static function extendTableConfig($tableConfig)
    {
        if ($tableConfig['table_type'] !== 'forminator') {
            return $tableConfig;
        }

        // Sanitize Forminator Config
        $forminatorData = self::sanitizeForminatorConfig(json_decode(
            stripslashes_deep($_POST['forminator'])
        ));

        $advancedSettings = json_decode($tableConfig['advanced_settings']);
        $advancedSettings->forminator = array(
            'dateFilterLogic' => $forminatorData->dateFilterLogic,
            'dateFilterFrom' => $forminatorData->dateFilterFrom,
            'dateFilterTo' => $forminatorData->dateFilterTo,
            'dateFilterTimeUnits' => $forminatorData->dateFilterTimeUnits,
            'dateFilterTimePeriod' => $forminatorData->dateFilterTimePeriod,
            'formIDFilterFrom' => $forminatorData->formIDFilterFrom,
            'formIDFilterTo' => $forminatorData->formIDFilterTo
        );

        $tableConfig['advanced_settings'] = json_encode($advancedSettings);

        return $tableConfig;
    }

    /**
     * Get field display headers
     * @param $formId - Forminator Form ID
     * @param $formType - Forminator Form Type
     * @param $formChartType - Forminator Form Chart Type
     * @param $fieldsIds - IDs of the fields to fetch labels
     * @return array of columns headers (field labels)
     */
    public static function getColumnHeaders($formId, $formType, $formChartType, $fieldsIds)
    {
        $columnHeaders = array();
        $formEntries = self::getFormEntriesFromAPI($formId, $formType);
        if ($formEntries != []) {
            $fields = self::prepareFormFieldsFromAPI($formId, $formType);
            if ($formChartType != 'pie') {
                // Form fields
                foreach ($fields['fields'] as $field) {
                    if ($formType == 'forminator_quizzes') {
                        if (in_array($field['slug'], $fieldsIds)) {
                            $originalHeader = \WDTTools::generateMySQLColumnName($field['slug'], $columnHeaders);
                            if (empty($columnHeaders[$originalHeader])) {
                                $columnHeaders[$originalHeader] = isset($field['title']) ? $field['title'] : ucfirst($field['slug']);
                            }
                        }
                    } else {
                        if (in_array($field->raw['element_id'], $fieldsIds)) {
                            $originalHeader = \WDTTools::generateMySQLColumnName($field->raw['element_id'], $columnHeaders);
                            if (empty($columnHeaders[$originalHeader])) {
                                if ($formType == 'forminator_polls') {
                                    $columnHeaders[$originalHeader] = isset($field->raw['title']) ? $field->raw['title'] . " (" . $field->raw['element_id'] . ")" : ucfirst($field->raw['element_id']);
                                } else {
                                    $columnHeaders[$originalHeader] = isset($field->raw['field_label']) ? $field->raw['field_label'] : ucfirst($field->raw['type']);
                                }
                            }
                        }
                    }
                }
                // Special fields
                $columnHeaders = self::specialFieldsFillHeaders($columnHeaders, $fields);

                // Common fields
                foreach (array_intersect($fieldsIds, ['date_created_sql', 'entry_id', 'ip']) as $commonField) {
                    if ($commonField === 'date_created_sql') {
                        $columnHeaders['datecreatedsql'] = 'Entry Date';
                    } else if ($commonField === 'entry_id') {
                        $columnHeaders['entryid'] = 'Entry ID';
                    } else if ($commonField === 'ip') {
                        $columnHeaders['ip'] = 'User IP';
                    }
                }
            } else {
                // Special fields in poll for Pie chart
                foreach ($fieldsIds as $fieldId) {
                    $columnHeaders[$fieldId] = $fields[$fieldId];
                }
            }
        }

        return $columnHeaders;
    }

    /**
     * Prepare form entries based on form and field type
     * @param $field
     * @param $entry
     * @param $slug
     * @param $isGroup
     * @return mixed|null
     */
    private static function prepareFieldsData($field, $entry, $slug, $isGroup = false)
    {
        $formattedEntry = '';
        if ($entry->entry_type == 'quizzes') {
            $entryValues = $field['type'] != 'nowrong' ? $entry->meta_data['entry']['value'] : $entry->meta_data['entry']['value'][0]['value']['answers'];
            $removeForminatorFormatting = apply_filters('wdt_forminator_remove_quiz_iscorrect_style', false, $entry->form_id);
            if ($entryValues != []) {
                foreach ($entryValues as $tempEntryValue) {
                    if ($tempEntryValue['question'] == $field['title']) {
                        if ($field['type'] != 'nowrong') {
                            if (isset($tempEntryValue['isCorrect']) && !$removeForminatorFormatting) {
                                if ($tempEntryValue['isCorrect']) {
                                    $formattedEntry = '<span style="background-color: #1abc9c;color: #fff;padding: 0 16px;border: 2px solid transparent;border-radius: 13px;">' . $tempEntryValue['answer'] . '</span>';
                                } else {
                                    $formattedEntry = '<span style="background-color: #ff6d6d;;color: #fff;padding: 0 16px;border: 2px solid transparent;border-radius: 13px;">' . $tempEntryValue['answer'] . '</span>';
                                }
                            } else {
                                $formattedEntry = $tempEntryValue['answer'];
                            }
                        } else {
                            $formattedEntry = $tempEntryValue['answer'];
                        }
                    }
                }
            } else {
                $formattedEntry = null;
            }
        } else {
            if ($field->raw['type'] == 'group' || isset($entry->meta_data[$slug])) {
                $entryValue = '';
                if ($field->raw['type'] != 'group'){
                    $entryValue = $entry->meta_data[$slug]['value'];
                }
                switch ($field->raw['type']) {
                    case 'name':
                        if (is_array($entryValue)) {
                            $removeForminatorFormatting = apply_filters('wdt_forminator_remove_style_from_name_multiply_fields', false, $entry->form_id);
                            foreach ($entryValue as $label => $data) {
                                switch ($label) {
                                    case 'prefix':
                                        if ($data != '') {
                                            if ($field->raw['prefix'] == 'true' && !$removeForminatorFormatting) {
                                                $formattedEntry .= '<strong>' . $field->raw['prefix_label'] . '</strong><br>' . $data . '<br><br>';
                                            } else {
                                                $formattedEntry .= $data . ' ';
                                            }
                                        } else {
                                            $formattedEntry .= '';
                                        }
                                        break;
                                    case 'first-name':
                                        if ($data != '') {
                                            if ($field->raw['fname'] == 'true' && !$removeForminatorFormatting) {
                                                $formattedEntry .= '<strong>' . $field->raw['fname_label'] . '</strong><br>' . $data . '<br><br>';
                                            } else {
                                                $formattedEntry .= $data . ' ';
                                            }
                                        } else {
                                            $formattedEntry .= '';
                                        }
                                        break;
                                    case 'middle-name':
                                        if ($data != '') {
                                            if ($field->raw['mname'] == 'true' && !$removeForminatorFormatting) {
                                                $formattedEntry .= '<strong>' . $field->raw['mname_label'] . '</strong><br>' . $data . '<br><br>';
                                            } else {
                                                $formattedEntry .= $data . ' ';
                                            }
                                        } else {
                                            $formattedEntry .= '';
                                        }
                                        break;
                                    case 'last-name':
                                        if ($data != '') {
                                            if ($field->raw['lname'] == 'true' && !$removeForminatorFormatting) {
                                                $formattedEntry .= '<strong>' . $field->raw['lname_label'] . '</strong><br>' . $data . '<br><br>';
                                            } else {
                                                $formattedEntry .= $data;
                                            }
                                        } else {
                                            $formattedEntry .= '';
                                        }
                                        break;
                                }
                            }
                        } else {
                            $formattedEntry = $entryValue;
                        }
                        break;
                    case 'textarea':
                        $formattedEntry = nl2br($entryValue);
                        break;
                    case 'slider':
                        if ($field->raw['slider_type'] == 'single') {
                            $formattedEntry = $entryValue;
                        } else if ($field->raw['slider_type'] == 'range') {
                            $formattedEntry = $entryValue['min'] . ' - ' . $entryValue['max'];
                        }
                        break;
                    case 'date':
                        if (is_array($entryValue)) {
                            $originalDate = $entryValue['year'] . '-' . $entryValue['month'] . '-' . $entryValue['day'];
                            $formattedEntry = date(get_option('wdtDateFormat'), strtotime($originalDate));
                        } else {
                            if ($isGroup){
                                $formattedEntry = date(get_option('wdtDateFormat'), strtotime($entryValue));
                            } else {
                                $formattedEntry = $entryValue;
                            }
                        }
                        break;
                    case 'select':
                        if (is_array($entryValue)) {
                            $formattedEntry = implode(', ', $entryValue);
                        } else {
                            $formattedEntry = $entryValue;
                        }
                        break;
                    case 'checkbox':
                        if (is_array($entryValue)) {
                            $formattedEntry = implode(', ', array_filter($entryValue));
                        } else {
                            $formattedEntry = $entryValue;
                        }
                        break;
                    case 'time':
                        $hours = $entryValue['hours'];
                        $minutes = $entryValue['minutes'];
                        $ampm = isset($entryValue['ampm']) ? strtolower($entryValue['ampm']) : '';
                        if ($ampm === 'pm') {
                            $hours = ($entryValue['hours'] % 12) + 12;
                        }
                        $timeString = $hours . ':' . $minutes;
                        $timestamp = is_numeric($timeString) ? $timeString : strtotime($timeString);
                        $formattedEntry = date(get_option('wdtTimeFormat'), $timestamp);
                        break;
                    case 'address':
                        $entryValueFormatted = [
                            'street_address' => isset($entryValue['street_address']) ? $entryValue['street_address'] : '',
                            'address_line' => isset($entryValue['address_line']) ? $entryValue['address_line'] : '',
                            'city' => isset($entryValue['city']) ? $entryValue['city'] : '',
                            'state' => isset($entryValue['state']) ? $entryValue['state'] : '',
                            'zip' => isset($entryValue['zip']) ? $entryValue['zip'] : '',
                            'country' => isset($entryValue['country']) ? $entryValue['country'] : ''
                        ];
                        $removeForminatorFormatting = apply_filters('wdt_forminator_remove_style_form_address_fields', false, $entry->form_id);
                        foreach ($entryValueFormatted as $label => $data) {
                            switch ($label) {
                                case 'street_address':
                                    if ($data != '') {
                                        if ($field->raw['street_address'] == 'true' && !$removeForminatorFormatting) {
                                            $formattedEntry .= '<strong>' . $field->raw['street_address_label'] . '</strong><br>' . $data . '<br><br>';
                                        } else {
                                            $formattedEntry .= $data . ', ';
                                        }
                                    } else {
                                        $formattedEntry .= '';
                                    }
                                    break;
                                case 'address_line':
                                    if ($data != '') {
                                        if ($field->raw['address_line'] == 'true' && !$removeForminatorFormatting) {
                                            $formattedEntry .= '<strong>' . $field->raw['address_line_label'] . '</strong><br>' . $data . '<br><br>';
                                        } else {
                                            $formattedEntry .= $data . ', ';
                                        }
                                    } else {
                                        $formattedEntry .= '';
                                    }
                                    break;
                                case 'city':
                                    if ($data != '') {
                                        if ($field->raw['address_city'] == 'true' && !$removeForminatorFormatting) {
                                            $formattedEntry .= '<strong>' . $field->raw['address_city_label'] . '</strong><br>' . $data . '<br><br>';
                                        } else {
                                            $formattedEntry .= $data . ', ';
                                        }
                                    } else {
                                        $formattedEntry .= '';
                                    }
                                    break;
                                case 'state':
                                    if ($data != '') {
                                        if ($field->raw['address_state'] == 'true' && !$removeForminatorFormatting) {
                                            $formattedEntry .= '<strong>' . $field->raw['address_state_label'] . '</strong><br>' . $data . '<br><br>';
                                        } else {
                                            $formattedEntry .= $data . ', ';
                                        }
                                    } else {
                                        $formattedEntry .= '';
                                    }
                                    break;
                                case 'zip':
                                    if ($data != '') {
                                        if ($field->raw['address_zip'] == 'true' && !$removeForminatorFormatting) {
                                            $formattedEntry .= '<strong>' . $field->raw['address_zip_label'] . '</strong><br>' . $data . '<br><br>';
                                        } else {
                                            $formattedEntry .= $data . ', ';
                                        }
                                    } else {
                                        $formattedEntry .= '';
                                    }
                                    break;
                                case 'country':
                                    if ($data != '') {
                                        if ($field->raw['address_country'] == 'true' && !$removeForminatorFormatting) {
                                            $formattedEntry .= '<strong>' . $field->raw['address_country_label'] . '</strong><br>' . $data . '<br><br>';
                                        } else {
                                            $formattedEntry .= $data;
                                        }
                                    } else {
                                        $formattedEntry .= '';
                                    }
                                    break;
                            }
                        }
                        break;
                    case 'calculation':
                        $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;
                        $formattedEntry = \Forminator_Form_Entry_Model::meta_value_to_string('calculation', $entryValue);
                        if ($numberFormat == 1) {
                            $formattedEntry = str_replace('.', ',', $formattedEntry);
                        }
                        break;
                    case 'upload':
                        $fileURLOutput = '';
                        $fileURLs = $entryValue['file']['file_url'];
                        if (!empty($fileURLs)) {
                            if (is_array($fileURLs)) {
                                foreach ($fileURLs as $fileURL) {
                                    $fileURLOutput .= self::convertUploadFiles($fileURL, true, $entry->form_id);
                                }
                            } else {
                                $fileURLOutput = self::convertUploadFiles($fileURLs, false, $entry->form_id);
                            }
                        }
                        $formattedEntry = $fileURLOutput;
                        break;
                    case 'postdata':
                        $removeForminatorFormatting = apply_filters('wdt_forminator_remove_style_form_postdata_fields', false, $entry->form_id);
                        $formattedEntry = \Forminator_Form_Entry_Model::meta_value_to_string('postdata', $entryValue, !$removeForminatorFormatting);
                        break;
                    case 'email':
                        $removeForminatorFormatting = apply_filters('wdt_forminator_remove_style_form_email_fields', true, $entry->form_id);
                        $formattedEntry = \Forminator_Form_Entry_Model::meta_value_to_string('email', $entryValue, !$removeForminatorFormatting);
                        break;
                    case 'url':
                        $removeForminatorFormatting = apply_filters('wdt_forminator_remove_style_form_url_fields', true, $entry->form_id);
                        $formattedEntry = \Forminator_Form_Entry_Model::meta_value_to_string('url', $entryValue, !$removeForminatorFormatting);
                        break;
                    case 'number':
                        $numberFormat = get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1;

                        if ($numberFormat == 1) {
                            $formattedEntry = str_replace('.', ',', $entryValue);
                        } else {
                            $formattedEntry = $entryValue;
                        }
                        break;
                    case 'paypal':
                    case 'stripe':
                        if (is_array($entryValue)) {
                            foreach ($entryValue as $label => $data) {
                                if ($label == 'transaction_link') {
                                    continue;
                                } else if ($label == 'transaction_id') {
                                    $formattedEntry .= '<strong>' . __('Transaction ID', 'wpdatatables') . '</strong>: <a href="' . esc_url($entryValue['transaction_link']) . '">' . $data . '</a> ';
                                } else {
                                    $formattedEntry .= '<strong>' . ucfirst($label) . '</strong>: ' . $data . '<br>';
                                }
                            }
                        }
                        break;
                    case 'group':
                        $groupFields = Forminator_API::get_form($entry->form_id)->get_grouped_real_fields($slug);
                        if (!empty($groupFields)) {
                            $fieldsSlugs = [];
                            $fieldsObjArr = [];
                            $isRepeater = $field->raw['is_repeater'] == 'true';
                            $formattedEntry = '<table style="border: 1px solid #e6e6e6;width: 100%;"><thead><tr>';
                            foreach ($groupFields as $groupField) {
                                $fieldsObjArr[$groupField->slug] = $groupField;
                                $fieldsSlugs[] = $groupField->slug;
                                $formattedEntry .= '<th style="border: 1px solid #e6e6e6;padding: 5px;color: #333;text-align: left">';
                                $formattedEntry .= (isset($groupField->raw['field_label']) && $groupField->raw['field_label'] != '') ? $groupField->raw['field_label'] : ucfirst(WDTTools::slugify($groupField->slug));
                                $formattedEntry .= '</th>';
                            }
                            $formattedEntry .= '</tr></thead><tbody><tr>';
                            foreach ($fieldsSlugs as $fieldsSlug) {
                                $formattedEntry .= '<td style="border: 1px solid #e6e6e6;padding: 5px;text-align: left">';
                                if (isset($entry->meta_data[$fieldsSlug]))
                                    $formattedEntry .= self::prepareFieldsData(
                                        $fieldsObjArr[$fieldsSlug],
                                        $entry,
                                        $fieldsSlug,
                                        true
                                    );
                                $formattedEntry .= '</td>';
                            }
                            $formattedEntry .= '</tr>';
                            if ($isRepeater) {
                                $repeatedGroupKeys = forminator_get_cloned_field_keys($entry, $fieldsSlugs);
                                foreach ($repeatedGroupKeys as $repeatedGroupKey) {
                                    $formattedEntry .= '<tr>';
                                    foreach ($fieldsSlugs as $fieldsSlug) {
                                        $formattedEntry .= '<td style="border: 1px solid #e6e6e6;padding: 5px;text-align: left">';
                                        if (isset($entry->meta_data[$fieldsSlug . $repeatedGroupKey]))
                                            $formattedEntry .= self::prepareFieldsData(
                                                $fieldsObjArr[$fieldsSlug],
                                                $entry,
                                                $fieldsSlug . $repeatedGroupKey,
                                                true
                                            );
                                        $formattedEntry .= '</td>';
                                    }
                                    $formattedEntry .= '</tr>';
                                }
                            }
                            $formattedEntry .= '</tbody></table>';
                        }
                        break;
                    case 'signature':
                        $file = '';
                        if (isset($entryValue['file'])) {
                            $file = $entryValue['file'];
                        }
                        if (!empty($file) && is_array($file) && isset($file['file_url'])) {
                            $formattedEntry = $file['file_url'];

                            // make image
                            $url = $formattedEntry;
                            $file_name = basename($url);
                            $file_name = !empty($file_name) ? $file_name : __('(no filename)', 'wpdatatables');

                            $formattedEntry = '<a href="' . esc_url($url) . '" target="_blank"><img src="' . esc_url($url) . '" alt="' . esc_attr($file_name) . '" width="100" /></a>';
                        }
                        break;
                    default:
                        $formattedEntry = $entryValue;
                        break;
                }
            } else {
                $formattedEntry = null;
            }
        }

        return apply_filters('wdt_forminator_filter_formatted_entry', $formattedEntry, $field, $entry);
    }

    /**
     *
     * Convert upload files based on extension
     * @param $file
     * @param $isArray
     * @param $formID
     * @return mixed|null
     */
    private static function convertUploadFiles($file, $isArray, $formID)
    {
        $urlInfo = pathinfo($file);
        $breakTag = $isArray ? '</br>' : '';
        $blockStyle = $isArray ? 'style="display:block;margin-bottom:10px;"' : '';
        switch ($urlInfo['extension']) {
            case 'jpg':
            case 'png':
            case 'jpeg':
            case 'gif':
            case 'webp':
                $fileURLOutput = '<a href="' . $file . '" target="_blank"><img width="200" ' . $blockStyle . ' alt="' . basename($file) . '" src=' . $file . '></a>';
                break;
            case 'mp3':
                $fileURLOutput = '<audio controls style="width: 250px;"><source src="' . $file . '" type="audio/mpeg"></audio>' . $breakTag;
                break;
            case 'wav':
                $fileURLOutput = '<audio controls style="width: 250px;"><source src="' . $file . '" type="audio/wav"></audio>' . $breakTag;
                break;
            case 'mp4':
                $fileURLOutput = '<video controls style="width: 250px;" ><source src="' . $file . '" type="video/mp4"></video>' . $breakTag;
                break;
            case 'webm':
                $fileURLOutput = '<video controls style="width: 250px;" ><source src="' . $file . '" type="video/webm"></video>' . $breakTag;
                break;
            default:
                $fileURLOutput = '<a href="' . $file . '" target="_blank">' . basename($file) . '</a>' . $breakTag;
                break;
        }

        return apply_filters('wdt_forminator_filter_file_upload_output', $fileURLOutput, $urlInfo['extension'], $file, $formID);
    }
}
