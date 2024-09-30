=== wpDataTables integration for Forminator Forms ===
Contributors: wpDataTables
Author URI: https://www.tmsproducts.io/
Tags: wpdatatables, forminator, table, form, quiz
Requires at least: 4.0
Tested up to: 6.5.3
Requires PHP: 5.6
Stable tag: 1.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create responsive, sortable tables & charts from Forminator forms submissions with wpDataTables.

== Description ==

wpDataTables integration for Forminator Forms is an add-on that connects the best WordPress table plugin [wpDataTables](https://wordpress.org/plugins/wpdatatables/) and easy-to-use WordPress form builder [Forminator](https://wordpress.org/plugins/forminator/).

A powerful tool that adds *"Forminator Form"* as a new table type in wpDataTables and allows you to create responsive, sortable tables & charts based on Forminator Forms submissions from your site frontend using intuitive wpDataTables table and chart wizard.

You will need to install [wpDataTables](https://wordpress.org/plugins/wpdatatables/) and [Forminator](https://wordpress.org/plugins/forminator/) plugins to be able to use this integration.
This great integration is compatible with [wpDataTables Premium](https://wpdatatables.com/) version and [Forminator PRO](https://wpmudev.com/project/forminator-pro/) and their advanced features. You can use any combination of these two plugins. Isn't that awesome?

When the form/quiz/poll is created and entries are ready, you can begin creating a wpDataTable based on it. First, go to **wpDataTables** -> **Create a Table**, choose **“Create a table linked to an existing data source”** option, and click **“Next”**.

Then choose **“Forminator Form”** as the Input data source type. After you choose **“Forminator Form”** as a table type, a new select-box **“Choose a Forminator Form”** will appear. With this select-box, you can choose a form, quiz or poll, that will provide entries as data for your new table.

After this step you will see a select-box **“Choose fields to show as columns”** that allows you to choose the form/quiz/poll fields that you will use as columns. Using this select-box, you can choose form fields that will be used in the table.

Furthermore, you can choose which form fields will be shown in the table.

Here is the list of the supported form fields:

* **NEW!** Slider Field (Single and Range)
* **NEW!** Group Field
* Name (Single and Multiple)
* Email
* Phone
* Address
* Website
* Input
* Textarea
* Number
* Radio
* Checkbox
* Calculations
* Select (Single and Multiple)
* Datepicker (Calendar, Dropdowns, and Text input)
* Timepicker ( Dropdowns and Number input)
* File Upload (Single and Multiple)
* Post Data
* Hidden Field
* Currency
* PayPal
* Stripe
* E-Signature (only available in [Forminator PRO](https://wpmudev.com/project/forminator-pro/))

Please note that fields like reCaptcha, HTML, Page break, Section, and GDPR Approval are excluded from tables.

**Important:** For all form types that you want to create tables (form, quiz, or poll) you will need to have submission data for those form types in the database. That means that you need to turn off the option *"Disable store submissions in my database"* on Data Storage settings in the Forminator plugin. You will be able to save submissions in the database and then create a table in wpDataTables.

**Forms**

When you create a table from Regular form, in table column headers will be used names of your fields, and each row will be shown as a separate submission. Common fields such as Entry data, Entry ID, and User IP are available for each form.

Fields like *"Name (Multiple)"* and *"Address"* will be formatted like on the Forminator forms Submissions page.
If you want to show those data in one line with space between without formatting, you can use hooks for *"Name(Multiple)"* like in the following example:

`// Remove formatting from Name (Multiple) fields`
`// $removeForminatorFormatting- false by default - bool`
`// $formID - Id of the form - int`
`function remove_style_from_name_multiply_fields($removeForminatorFormatting, $formID){`
   `// Example for the form with id 1`
   `if ($formID == 1){`
    `// Provide true to remove formatting`
       `$removeForminatorFormatting= true;`
   `}`
   `return $removeForminatorFormatting;`
`}`
`add_filter('wdt_forminator_remove_style_from_name_multiply_fields', 'remove_style_from_name_multiply_fields', 10, 2);`

and for the *"Address"* fields as well you can show the data in one line separated with a comma using this hook:

`// Remove formatting from the Address fields`
`// $removeForminatorFormatting- it is false by default - bool`
`// $formID - Id of the form - int`
`function remove_style_form_address_fields($removeForminatorFormatting, $formID){`
   `// Example for form with id 1`
   `if ($formID == 1){`
    `// Provide true to remove formatting`
       `$removeForminatorFormatting= true;`
   `}`
   `return $removeForminatorFormatting;`
`}`
`add_filter('wdt_forminator_remove_style_form_address_fields','remove_style_form_address_fields', 10, 2);`

For the upload fields, there are some formatting rules applied depending on file extension. For image extensions *(jpg, jpeg, png, gif, webp)* the output will be formatted like image links.
Files with the *'mp3'* and *'wav'* extensions will be formatted as audio HTML tags, and the files with *'mp4'* and *'webm'* extensions will be formatted as video HTML tags.

If you need some different formatting rules for those upload fields you can use the following hook:

`// Filter formatted file URL`
`// $fileURLOutput - Already formatted file URL - string`
`// $fileURLExtension - File URL Extension (png,csv,pdf,mp4...) - string`
`// $file - Original file URL - string`
`function filter_file_upload_output($fileURLOutput, $fileURLExtension, $file, $formID){`
   `// Example for form with id 1`
   `if ($formID == 1){`
    `// Example only for the jpg extension`
       `if ($fileURLExtension == 'jpg'){`
        `// Set a custom image HTML tag with width of 400px`
       `$fileURLOutput = '<img width="400" alt="' . basename($file) . '" src=' . $file . '>';`
       `}`
   `}`
   `return $fileURLOutput;`
`}`
`add_filter('wdt_forminator_filter_file_upload_output', 'filter_file_upload_output', 10, 4);`

All other uploaded files will be formatted as HTML links.

For the Website and email fields, there is no formatting rules applied from Forminator.

If you need formatting rules from Formiantor for those fields (to be shown as links) you can use the following hook

for Email field:

`// Add formatting from Formiantor for Email field`
`// $removeForminatorFormatting- it is true by default - bool`
`// $formID - Id of the form - int`
`function remove_style_form_email_fields($removeForminatorFormatting, $formID){`
   `// Example for form with id 1`
   `if ($formID == 1){`
    `// Provide false to add Forminator formatting as link`
       `$removeForminatorFormatting = false;`
   `}`
   `return $removeForminatorFormatting;`
`}`
`add_filter('wdt_forminator_remove_style_form_email_fields','remove_style_form_email_fields', 10, 2);`

and for Website field:

`// Add formatting from Formiantor for Website (URL) field`
`// $removeForminatorFormatting- it is true by default - bool`
`// $formID - Id of the form - int`
`function remove_style_form_url_fields($removeForminatorFormatting, $formID){`
   `// Example for form with id 1`
   `if ($formID == 1){`
    `// Provide false to add Forminator formatting as link`
       `$removeForminatorFormatting = false;`
   `}`
   `return $removeForminatorFormatting;`
`}`
`add_filter('wdt_forminator_remove_style_form_url_fields','remove_style_form_url_fields', 10, 2);`

**New field support - Group field**

Now, support for Group fields is avalible with our integration as well. Every Group field in form will be shown as separate column and all fields that are in group will be parsed as table layout (in each cell you will have separate table as is shown on Formiantor submissions). Each field in group will be represent in the head of that table and entries from those fileds will be in the body of that table. If Repeater is enabled then those entries will be populated in table body as well.

Additionally, there is a hook available for filtering all the formatted entries (only for forms and quizzes)

`// Filter all the formatted entries`
`// $formattedEntry - Already formatted entry - string`
`// $field - Forminator_Form_Field_Model - object`
`// $entry - Forminator_Form_Entry_Model - object`
`function filter_formatted_entry($formattedEntry, $field, $entry){`
    `// Example for form with id 1`
      `if ($entry->form_id == 1){`
       `// Example only for custom forms`
          `if ($entry->entry_type == 'custom-forms'){`
               `// Check is set meta data for current field`
              `if (isset($entry->meta_data[$field->slug])) {`
                   `$entryValue = $entry->meta_data[$field->slug]['value'];`
                   `// Check is field type 'name' and that is not array`
                    `if ($field->raw['type']== 'name' && !is_array($entryValue)) {`
                      `// if $entryValue is John return null - it will not be shown in the table `
                        `if ($entryValue == 'John') $formattedEntry = null;`
                    `}`
               `}`
          `}`
      `}`
      `return $formattedEntry;`
`}`
`add_filter('wdt_forminator_filter_formatted_entry','filter_formatted_entry', 10, 3);`

**Quizzes**

You can create a table from  the *Personality* and *Knowledge* quizzes. For quizzes, common fields like Entry Date and Entry ID are available.
If lead generation feature is enabled, it will be available two more fields like *Email* and *Name* generated by this feature.

1. When you create a table from a *Knowledge quiz*, in the table, questions will appear as column headers, and each row will be populated with the separate submission answers. For this type of quiz, three more fields are available like Correct answers, Incorrect answers and Score (Correct answers/Total answers). In that table, the answers will be formatted the same way as on the Forminator Submissions page. (correct answers have a green background and the wrong ones have a red background).

1. When you create a table from the *Personality quiz*, questions will appear as column headers in that table. Also, the *"Quiz result"* will show up in the columns (if you choose it in the option *"Choose fields to show as columns"*), and each row will be shown as a separate submission answer.

If you want to show answers from *Knowledge quiz* without their formatting, you can use the following hook:

`// Remove formatting from answers in Knowledge quiz`
`// $removeForminatorFormatting - it is false by default - bool`
`// $formID - Id of the form - int`
`function remove_quiz_iscorrect_style($removeForminatorFormatting, $formID){`
   `// Example for form with id 1`
   `if ($formID == 1){`
    `// Provide true to remove formatting`
       `$removeForminatorFormatting = true;`
   `}`
   `return $removeForminatorFormatting;`
`}`
`add_filter('wdt_forminator_remove_quiz_iscorrect_style','remove_quiz_iscorrect_style', 10, 2);`

**Polls**

Polls entries in the Forminator submissions are shown as grouped values based on the answers. The same data can be displayed in a chart (Bar or Pie chart depending on your settings). In accordance with that, you can create tables based on the Forminator poll submissions either for the Bar chart or for the Pie chart structure, no matter what was chosen in the Forminator settings for the poll chart type. Polls do not have common fields like forms and quizzes.

1. In the first case, if you select the *Poll for Bar chart* option, you can choose the columns to be created from the *Poll question* and *Answers* of that poll in the table. Only one row of data will be shown, since the data is grouped. After creating a table you are able to create a Google Bar chart and to show it on the front-end.

1. In the second case, if you select the *Poll for Pie chart* option, you can choose only the columns to be created from the *Poll answers* and *Total votes* of that poll in the table. Then, the first column will list all the possible answers *(Poll answers)*, and the second one *(Total votes)*, will display grouped data for each answer. After creating a table, you can create a Google Pie chart and add it on the website page as well.

If you need, you can show both charts (Pie and Bar) on the front-end for the same Poll.

**Integration settings**

Each Forminator form-based wpDataTable receives an extra Forminator settings tab on the table configuration page, together with several additional table settings. Using this tab, you can define which form entries will appear in the wpDataTable based on the range of entry IDs, entry date by choosing one of the two possible filtering logic options in the Filter by date select box. You can select between Filter by date range and Filter by the last X time period; or, you can leave this block empty if you don’t wish to filter form entries displayed in the table.

* *Filter by entry ID range* – Two input fields ("From" and "To") are shown in this section. If you define some values in these number input fields, wpDataTable rows will be updated according to the selected range.

* *Filter by date range* –  If you select this option, two input fields ("From" and "To") will be displayed right to the Filter by date select box. By defining some date values in these datetimepicker input fields, wpDataTable rows will be narrowed down according to the provided date range.

* *Filter by last X time period* – When this option is selected, the Filter by date select box will display two input fields. In the first one, you can define a number (e.g., 30), and in the second one, you can choose between (Day(s), Week(s), Month(s), and Year(s)). By selecting, e.g., "30 Day(s)" you will filter and display in the wpDataTable only the entries added in the last 30 days in the Forminator Form used as a data source for this wpDataTable.

In those tables, you can use all features that are included in wpDataTables:

1. **NEW!** [Create 9 different Chart.js charts types](https://wpdatatables.com/documentation/wpdatacharts/chartjs/),
1. **NEW!** [Responsive datatables](https://wpdatatables.com/documentation/table-features/responsiveness/)
1. **NEW!** [Integration with Avada Website builder](https://wpdatatables.com/documentation/general/avada-builder-integration/)
1. **NEW!** [Integration with DIVI Website builder](https://wpdatatables.com/documentation/general/divi-builder-integration/)
1. **NEW!** [Caching data](https://wpdatatables.com/documentation/table-features/caching-data-and-auto-update-cache-in-wordpress-tables/)
1. **NEW!** [Auto update cache data](https://wpdatatables.com/documentation/table-features/caching-data-and-auto-update-cache-in-wordpress-tables/#auto-update-cache)
1. **NEW!** FULL Frontend WCAG compatibility
1. **NEW!** Responsive Charts – now each chart (both Google and Chartjs) can be responsive
1. **NEW!** Grouping Charts – rows with same labels would be treated as a single entry, summing up all the values in other cells
1. [Global search](https://wpdatatables.com/documentation/general/table-configuration-page-overview/#table-settings-sorting-filtering),
1. [Sorting](https://wpdatatables.com/documentation/table-features/sorting/),
1. [Column visibility](https://wpdatatables.com/documentation/column-features/column-visibility/),
1. [Pagination](https://wpdatatables.com/documentation/general/table-configuration-page-overview/#table-settings-display),
1. [Show rows per page](https://wpdatatables.com/documentation/general/table-configuration-page-overview/#table-settings-display),
1. [Row grouping](https://wpdatatables.com/documentation/column-features/row-grouping/),
1. [Table layout](https://wpdatatables.com/documentation/table-features/table-layout-and-word-wrap/),
1. [Scrollable](https://wpdatatables.com/documentation/table-features/scrollable/),
1. [Export data (in Excel, CSV, PDF, Copy or Print)](https://wpdatatables.com/documentation/table-features/table-tools/),
1. [Create 14 different Google charts types](https://wpdatatables.com/documentation/wpdatacharts/google-charts/),
1. [Global and ](https://wpdatatables.com/documentation/general/configuration/),
1. [Column customization](https://wpdatatables.com/documentation/general/table-configuration-page-overview/#column-settings-display)

If, apart from creating tables based on the Forminator forms data, you would also like to create tables from scratch or  from other data sources, wpDataTables provides more options for you:

* **NEW!** Create tables from Nested JSON - [Text and video documentation](https://wpdatatables.com/documentation/creating-wpdatatables/creating-wordpress-tables-from-nested-json-data-with-json-authentication/)
* Create Simple tables from scratch - [Text and video documentation](https://wpdatatables.com/documentation/creating-new-wpdatatables-with-table-constructor/creating-a-simple-table-with-wpdatatables/)
* Create tables from Excel - [Text and video documentation](https://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-excel/)
* Create tables from CSV - [Text and video documentation](https://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-csv/)
* Create tables from JSON - [Text and video documentation](https://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-json-input/)
* Create tables from XML - [Text and video documentation](https://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-xml/)
* Create tables from Serialized PHP array - [Text and video documentation](https://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-serialized-php-array/)

To check out the table on the front-end you can [insert wpDataTables shortcode in your page or post](https://wpdatatables.com/documentation/general/wpdatatables-shortcodes/) (for example [wpdatatable id=1]) or with page widgets/blocks if you are using some of the page builders like [WP Bakery](https://wpdatatables.com/documentation/table-features/visual-composer-integration/), [Elementor](https://wpdatatables.com/documentation/general/elementor-integration/) or [Gutenberg](https://wpdatatables.com/documentation/general/gutenberg-editor/).

**Limitation**
Those tables do not have a server-side option (this means that these tables can’t contain a large amount of data (no exact limit, but 2.000 – 3.000 rows is a good example)), and that they cannot be editable.

== Installation ==

Installation of the plugin is straightforward.

1. Make sure you have both wpDataTables and Forminator core plugins installed.
2. Install using one of these options:
   * Install directly from the WordPress Admin panel: go to Plugins -> Add New -> Search for "wpDataTables integration for Forminator forms", and click the Install button.
   * Download the ZIP manually from WordPress’s plugins repository, and upload it through the WordPress Admin panel: go to Plugins -> Add New -> Upload Plugin, browse to the downloaded Zip and upload it.
   * Download the ZIP, extract it and manually upload the extracted folder through FTP to the `/wp-content/plugins/` directory of your WordPress installation.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. That's it!

== Screenshots ==
1. Table from forms
2. Table from quizzes
3. Table from polls(for bar chart)
4. Table from polls(for pie chart)
5. Poll Bar chart
6. Poll Pie chart
7. Integration settings

== Changelog ==
= 1.3.2 =
* Bugfix: Fixed issue with PM time not showing correctly.
* Compatibility with WordPress 6.5.3 version approved.
* Other small bug fixes and stability improvements.

= 1.3.1 =
* Bugfix: Fixed issue with activation using the Lite wpDataTables version.
* Other small bug fixes and stability improvements.

= 1.3 =
* Feature: Added support for Slider field (Single and Range).
* Improvement: SEO improvement- Reduced unused css and js.
* Other small bug fixes and stability improvements.

= 1.2.2 =
* Compatibility with WordPress 6.4.3 approved.
* Other small bug fixes and stability improvements.

= 1.2.1 =
* Improvement: Deactivation plugin when wpDataTables or Forminator are not activated.
* Compatibility with WordPress 6.2.2 approved.
* Other small bug fixes and stability improvements.

= 1.2 =
* Feature: Added support for Group field
* Improvement: Added new hooks for Email and Website fields
* Compatibility with WordPress 6.1 approved.
* Other small bug fixes and stability improvements.

= 1.1.2 =
* Bugfix: Fixed issue with showing placeholder tab
* Compatibility with WordPress 6.0.2 approved.
* Other small bug fixes and stability improvements.

= 1.1.1 =
* Compatibility with WordPress 5.9.3 approved.
* Other small bug fixes and stability improvements.

= 1.1 =
* Bugfix: Fixed issue with not showing all forms, quizzes and polls for selection
* Bugfix: Fixed issue with checkbox values if they are string
* Bugfix: Fixed issue with not showing row grouping option
* Bugfix: Fixed conflict with other wpDataTables form integration add-ons
* Compatibility with WordPress 5.8.1 approved.
* Other small bug fixes and stability improvements.

= 1.0 =
* Initial version for wp.org