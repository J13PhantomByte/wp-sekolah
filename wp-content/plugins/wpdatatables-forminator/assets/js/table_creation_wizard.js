(function ($) {

    $(function () {

        var applyButtonEvent = typeof $('.wdt-apply').data('events') !== 'undefined' ? $('.wdt-apply').data('events').click[1] : null;

        /**
         * Show Forminator Form block if "Forminator Form" table type is selected
         */
        $('#wdt-table-type').change(function () {
            if ($(this).val() === 'forminator') {
                // Show "Choose a Forminator Form" block on "Data Source" tabpanel
                $('#wdt-frf-form-container').animateFadeIn();
                //Turn off server-side proceeding
                wpdatatable_config.server_side = 0;
                // Off default save event and bind event for saving forminator form table
                $('.wdt-apply').off('click').click(function (e) {
                    e.preventDefault()
                    e.stopImmediatePropagation()
                    saveTableConfig();
                });
            } else if ( $(this).val() === 'gravity' || $(this).val() === 'formidable') {
                // Hide "Choose a Forminator Form" and "Choose fields to show as columns" blocks
                $('#wdt-frf-form-container').addClass('hidden');
                $('#wdt-frf-column-container').addClass('hidden');
                // Reset "Choose a Forminator Form" and "Choose fields to show as columns" values
                $('#wdt-forminator-form-picker').selectpicker('val', '');
                $('#wdt-forminator-form-column-picker').selectpicker('val', '');
            } else {
                // Hide "Choose a Forminator Form" and "Choose fields to show as columns" blocks
                $('#wdt-frf-form-container').addClass('hidden');
                $('#wdt-frf-column-container').addClass('hidden');
                // Reset "Choose a Forminator Form" and "Choose fields to show as columns" values
                $('#wdt-forminator-form-picker').selectpicker('val', '');
                $('#wdt-forminator-form-column-picker').selectpicker('val', '');
                // Off forminator form save event and revert to default one
                $('.wdt-apply').off().bind('click', applyButtonEvent);
            }
            // Reset content and disable "Apply" button
            wpdatatable_config.content = '';
            $('.wdt-apply').prop('disabled', true);
        });

        /**
         * Pick a form
         */
        $('#wdt-forminator-form-picker').change(function (e) {
            if ($(this).val() !== '') {
                wpdatatable_frf_config.setFormId($(this).val());
                wpdatatable_frf_config.setFormType($(this).find(':selected').data('form-type'));
                if (wpdatatable_frf_config.formType == 'forminator_polls') {
                    wpdatatable_frf_config.setFormChartType($(this).find(':selected').data('form-chart-type'));
                    if (wpdatatable_frf_config.formChartType == 'pie')
                        wpdatatable_frf_config.formId = wpdatatable_frf_config.formId - 10000000;
                }
                if (wpdatatable_frf_config.formType == 'forminator_quizzes') {
                    wpdatatable_frf_config.setFormHasLeads($(this).find(':selected').data('form-has-leads'));
                }

                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'wdt_forminator_get_form_fields',
                        nonce: $('#wdtNonce').val(),
                        formData: JSON.stringify(wpdatatable_frf_config.getFRFConfig()),
                    },
                    dataType: 'json',
                    method: 'POST',
                    success: function (results) {
                        if (typeof results.error !== 'undefined') {
                            wdtNotify(wdtFRFTranslationStrings.error, results.error, 'danger');
                            if ($('#wdt-frf-column-container').is(':visible'))
                                $('#wdt-frf-column-container').animateFadeOut();
                        } else {
                            if (!$('#wdt-frf-column-container').is(':visible')) {
                                $('#wdt-frf-column-container').animateFadeIn();
                            }
                            switch (results.formType) {
                                case 'forminator_forms':
                                    fillFormFields(results.data);
                                    break;
                                case 'forminator_polls':
                                    fillPollFields(results.data);
                                    break;
                                case 'forminator_quizzes':
                                    fillQuizFields(results.data);
                                    break;
                            }

                            if (typeof wpdatatable_init_config !== 'undefined' && wpdatatable_init_config.table_type === 'forminator') {
                                var content = JSON.parse(wpdatatable_init_config.content);
                                let fieldIds = content.fieldIds;
                                wpdatatable_frf_config.setFields(fieldIds);
                                wpdatatable_frf_config.setFormType(results.formType);
                                wpdatatable_frf_config.setFormChartType(results.formChartType);
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        var message = JSON.parse(xhr.responseText);
                        wdtNotify('Error!', message.error, 'danger');
                        if ($('#wdt-frf-column-container').is(':visible'))
                            $('#wdt-frf-column-container').animateFadeOut();
                    }
                });
            } else {
                $('#wdt-frf-column-container').animateFadeOut();
            }
        });

        /**
         * Validation for entry ID range filter
         */
        $('#wdt-frf-filter-from-form-id, #wdt-frf-filter-to-form-id').on('input change', function (e) {
            var currentInputVal = $(this).val() === '' ? 0 : parseInt($(this).val());
            if (currentInputVal === 0) {
                $(this).val(1)
                currentInputVal = parseInt($(this).val());
            } else {
                $(this).val(currentInputVal)
            }
            if (this.id == 'wdt-frf-filter-from-form-id') {
                var compareValueTo = parseInt($(this).closest('.wdt-frf-form-id-block').find('#wdt-frf-filter-to-form-id').val());
                if (currentInputVal > compareValueTo) $(this).closest('.wdt-frf-form-id-block').find('#wdt-frf-filter-to-form-id').val(currentInputVal)
            } else if (this.id == 'wdt-frf-filter-to-form-id') {
                var compareValueFrom = parseInt($(this).closest('.wdt-frf-form-id-block').find('#wdt-frf-filter-from-form-id').val())
                if (currentInputVal < compareValueFrom) $(this).closest('.wdt-frf-form-id-block').find('#wdt-frf-filter-from-form-id').val(currentInputVal)
            }
        });

        /**
         * Save table config when columns are selected and preview the table
         */
        $('#wdt-forminator-form-column-picker').on('change', function () {
            if ($(this).val().length) {
                $('.wdt-apply').prop('disabled', false);
                if (!$('.display-settings-tab').is(':visible')) {
                    $('.display-settings-tab').animateFadeIn();
                    $('.table-sorting-filtering-settings-tab').animateFadeIn();
                    $('.table-tools-settings-tab').animateFadeIn();
                    $('.customize-table-settings-tab').animateFadeIn();
                    $('.placeholders-settings-tab').animateFadeIn();
                    $('.forminator-settings-tab').animateFadeIn();
                }
            } else {
                $('.wdt-apply').prop('disabled', true);
                $('.display-settings-tab').animateFadeOut();
                $('.table-sorting-filtering-settings-tab').animateFadeOut();
                $('.table-tools-settings-tab').animateFadeOut();
                $('.customize-table-settings-tab').animateFadeOut();
                $('.placeholders-settings-tab').animateFadeOut();
                $('.forminator-settings-tab').animateFadeOut();
            }
            let fieldIds = $(this).val()
            wpdatatable_frf_config.setFields(fieldIds);
        });

        /**
         * Change Date Filter From
         */
        $('#wdt-frf-date-filter-from').on('dp.change', function () {
            wpdatatable_frf_config.setDateFilterFrom($(this).val());
        });

        /**
         * Change Date Filter To
         */
        $('#wdt-frf-date-filter-to').on('dp.change', function () {
            wpdatatable_frf_config.setDateFilterTo($(this).val());
        });

        /**
         * Change Date Filter Time Units
         */
        $('#wdt-frf-date-filter-time-units').on('input keyup change', function () {
            wpdatatable_frf_config.setDateFilterTimeUnits($(this).val());
        });

        /**
         * Change Date Filter Time Period
         */
        $('#wdt-frf-date-filter-time-period').on('change', function () {
            wpdatatable_frf_config.setDateFilterTimePeriod($(this).val());
        });

        /**
         * Change Form ID From Filter
         */
        $('#wdt-frf-filter-from-form-id').on('input keyup change', function () {
            wpdatatable_frf_config.setFormIDFilterFrom($(this).val());
        });
        /**
         * Change Form ID To Filter
         */
        $('#wdt-frf-filter-to-form-id').on('input keyup change', function () {
            wpdatatable_frf_config.setFormIDFilterTo($(this).val());
        });

        /**
         * "Filter by date" logic
         */
        $('#wdt-frf-date-filter-logic').on('change', function (e) {
            wpdatatable_frf_config.setDateFilterLogic($(this).val());
            if ($(this).val() === 'range') {
                $('.wdt-frf-date-range-block').animateFadeIn();
                $('.wdt-frf-last-x-block').addClass('hidden');
                wpdatatable_frf_config.setDateFilterTimePeriod(null);
                wpdatatable_frf_config.setDateFilterTimeUnits(null);
            } else if ($(this).val() === 'last') {
                $('.wdt-frf-last-x-block').animateFadeIn();
                $('.wdt-frf-date-range-block').addClass('hidden');
                $('#wdt-frf-date-filter-time-period').change();
                wpdatatable_frf_config.setDateFilterFrom(null);
                wpdatatable_frf_config.setDateFilterTo(null);
            } else {
                $('.wdt-frf-last-x-block').addClass('hidden');
                $('.wdt-frf-date-range-block').addClass('hidden');
                wpdatatable_frf_config.setDateFilterFrom(null);
                wpdatatable_frf_config.setDateFilterTo(null);
                wpdatatable_frf_config.setDateFilterTimePeriod(null);
                wpdatatable_frf_config.setDateFilterTo(null);
            }
        });

        /**
         * Reset all filters from Forminator settings
         */
        $('#wdt-frf-clear-all-filters').on('click', function () {
            $('#wdt-frf-filter-from-form-id').val('');
            $('#wdt-frf-filter-to-form-id').val('');
            $('#wdt-frf-date-filter-time-units').val('');
            $('#wdt-frf-date-filter-time-period').val('').trigger('change');
            $('#wdt-frf-date-filter-from').val('');
            $('#wdt-frf-date-filter-to').val('');
            $('#wdt-frf-date-filter-logic').val('').trigger('change');
            wpdatatable_frf_config.setDateFilterFrom(null);
            wpdatatable_frf_config.setDateFilterTo(null);
            wpdatatable_frf_config.setDateFilterTimePeriod(null);
            wpdatatable_frf_config.setDateFilterTimeUnits(null);
            wpdatatable_frf_config.setDateFilterTo(null);
            wpdatatable_frf_config.setFormIDFilterFrom(null);
            wpdatatable_frf_config.setFormIDFilterTo(null);
        })

        /**
         * Initialize datetime picker for Forminator Form "Filter by date range" feature
         */
        var wdtDateFormat = getMomentWdtForminatorDateFormat()
        //var wdtTimeFormat = wdtFRFSettings.wdtTimeFormat.replace('H', 'H').replace('i', 'mm');

        $('#wdt-frf-date-filter-from').datetimepicker({
            format: wdtDateFormat,
            showClear: true
        });
        $('#wdt-frf-date-filter-to').datetimepicker({
            format: wdtDateFormat,
            showClear: true,
            useCurrent: false
        });
        $("#wdt-frf-date-filter-from").on("dp.change", function (e) {
            $('#wdt-frf-date-filter-to').data("DateTimePicker").minDate(e.date);
        });
        $("#wdt-frf-date-filter-to").on("dp.change", function (e) {
            $('#wdt-frf-date-filter-from').data("DateTimePicker").maxDate(e.date);
        });

        /**
         * Load the table for editing
         */
        if (typeof wpdatatable_init_config !== 'undefined' && wpdatatable_init_config.table_type === 'forminator') {
            $('#wdt-frf-form-container').animateFadeIn();
            $('.forminator-settings-tab').animateFadeIn();
            $('.placeholders-settings-tab').animateFadeIn();

            initFRFFromJSON(wpdatatable_init_config);

            $('.wdt-apply').off('click').click(function (e) {
                e.preventDefault()
                e.stopImmediatePropagation()
                saveTableConfig();
            })
        }

    });

    /**
     * Populates "Forminator form column picker" select-box with quizzes fields
     * @param fields
     */
    function fillQuizFields(fields) {
        var quizOptions = '', commonOptions, optGroupEl, fieldType;

        optGroupEl = '<optgroup id="wdt-frf-form-fields" label="Quiz fields">';
        for (var i in fields) {
            var fieldLabel = fields[i];
            switch (i) {
                case "leads_email":
                    fieldType = "(leads email)"
                    break;
                case "leads_name":
                    fieldType = "(leads name)"
                    break;
                case "quiz_result":
                case "quiz_result_correct":
                case "quiz_result_incorrect":
                case "quiz_result_total":
                    fieldType = ""
                    break;
                default:
                    fieldType = "(question)"
                    break;
            }

            quizOptions += '<option value="' + i + '">' + fieldLabel + ' ' + fieldType + '</option>';
        }
        commonOptions = '<optgroup id="wdt-frf-common-fields" label="Common fields">' +
            '<option value="date_created_sql">Entry Date</option>' +
            '<option value="entry_id">Entry ID</option>' +
            '</optgroup>';

        optGroupEl += quizOptions + '</optgroup>' + commonOptions;

        $('#wdt-forminator-form-column-picker').html(optGroupEl).selectpicker('refresh');
    }

    /**
     * Populates "Forminator form column picker" select-box with polls fields
     * @param fields
     */
    function fillPollFields(fields) {
        var pollOptions = '', fieldType, optGroupEl;

        optGroupEl = '<optgroup id="wdt-frf-form-fields" label="Poll fields">';
        for (var i in fields) {
            var fieldLabel = fields[i];
            if (i == "poll_answers" || i == "poll_total_votes") {
                fieldType = "";
            } else {
                fieldType = i == "poll_question" ? "(question)" : "(answer)";
            }

            pollOptions += '<option value="' + i + '">' + fieldLabel + ' ' + fieldType + '</option>';
        }

        optGroupEl += pollOptions + '</optgroup>';

        $('#wdt-forminator-form-column-picker').html(optGroupEl).selectpicker('refresh');
    }


    /**
     * Populates "Forminator form column picker" selectbox with form fields
     * @param fields
     */
    function fillFormFields(fields) {
        var formOptions = '', commonOptions, optGroupEl;

        optGroupEl = '<optgroup id="wdt-frf-form-fields" label="Form fields">';

        for (var i in fields) {
            var fieldLabel = fields[i].field_label;
            var fieldElementID = fields[i].element_id;
            var fieldType = fields[i].type;
            fieldLabel = typeof fieldLabel != 'undefined' ? fieldLabel : fieldType.charAt(0).toUpperCase() + fieldType.slice(1);
            formOptions += '<option value="' + fieldElementID + '">' + fieldLabel + ' (' + fieldType + ')</option>';
        }
        commonOptions = '<optgroup id="wdt-frf-common-fields" label="Common fields">' +
            '<option value="date_created_sql">Entry Date</option>' +
            '<option value="entry_id">Entry ID</option>' +
            '<option value="ip">User IP</option>' +
            '</optgroup>';

        optGroupEl += formOptions + '</optgroup>' + commonOptions;

        $('#wdt-forminator-form-column-picker').html(optGroupEl).selectpicker('refresh');
    }

    /**
     * Save Forminator based wpDataTable config to DB and preview the wpDataTable
     */
    function saveTableConfig() {
        if ($('#wdt-forminator-form-picker').val() && $('#wdt-forminator-form-column-picker').val()) {
            $('.wdt-preload-layer').animateFadeIn();
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'wdt_forminator_save_table_config',
                    forminator: JSON.stringify(wpdatatable_frf_config.getFRFConfig()),
                    nonce: $('#wdtNonce').val(),
                    table: JSON.stringify(wpdatatable_config.getJSON())
                },
                dataType: 'json',
                method: 'POST',
                success: function (data) {
                    $('.wdt-preload-layer').animateFadeOut();
                    if (data.error) {
                        // Show error message
                        $('#wdt-error-modal .modal-body').html(data.error);
                        $('#wdt-error-modal').modal('show');
                        $('.wdt-preload-layer').animateFadeOut();
                    } else {
                        // Reinitialize table with returned data
                        wpdatatable_config.initFromJSON(data.table);
                        wpdatatable_config.setTableHtml(data.wdtHtml);
                        wpdatatable_config.setDataTableConfig(data.wdtJsonConfig);
                        wpdatatable_config.renderTable();

                        // Show success message
                        wdtNotify(
                            wpdatatables_edit_strings.success,
                            wpdatatables_edit_strings.tableSaved,
                            'success'
                        );

                        if (window.location.href.indexOf("table_id=") === -1) {
                            window.history.replaceState(null, null, window.location.pathname + "?page=wpdatatables-constructor&source&table_id=" + data.table.id);
                        }
                        // Remove disable from "Apply" button
                        $('.wdt-apply').prop('disabled', false);

                    }
                },
                error: function () {
                    wdtNotify(
                        wpdatatables_edit_strings.error,
                        '',
                        'danger'
                    )
                }
            });
        }
    }

    /**
     * Initializes forminator config from JSON for edit table
     * @param tableJSON
     */
    function initFRFFromJSON(tableJSON) {
        // Fill "Choose a Forminator Form" dropdown and trigger change so that
        // "Choose fields to show as columns" dropdown will be populated with form fields
        var content = JSON.parse(wpdatatable_init_config.content);
        $('#wdt-forminator-form-picker').selectpicker('val', content.formId).change();
        wpdatatable_config.setServerSide( tableJSON.server_side );
        var forminatorData = JSON.parse(tableJSON.advanced_settings).forminator;
        wpdatatable_frf_config.setDateFilterLogic(forminatorData.dateFilterLogic);
        wpdatatable_frf_config.setDateFilterFrom(forminatorData.dateFilterFrom);
        wpdatatable_frf_config.setDateFilterTo(forminatorData.dateFilterTo);
        wpdatatable_frf_config.setDateFilterTimeUnits(forminatorData.dateFilterTimeUnits);
        wpdatatable_frf_config.setDateFilterTimePeriod(forminatorData.dateFilterTimePeriod);
        wpdatatable_frf_config.setFormIDFilterFrom(forminatorData.formIDFilterFrom);
        wpdatatable_frf_config.setFormIDFilterTo(forminatorData.formIDFilterTo);

        // Trigger change event to show selected logic block
        $('#wdt-frf-date-filter-logic').change();
    }

    function getMomentWdtForminatorDateFormat() {
        return wdtFRFSettings.wdtDateFormat.replace('d', 'DD').replace('M', 'MMM').replace('m', 'MM').replace('y', 'YY').replace('F', 'MMMM').replace('j', 'DD');
    }

})(jQuery);