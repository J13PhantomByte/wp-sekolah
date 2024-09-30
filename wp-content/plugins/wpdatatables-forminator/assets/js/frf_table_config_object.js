/**
 * wpDataTable Forminator Form config object
 *
 * Contains all the settings for the Forminator Form based wpDataTable.
 * setter methods adjust the binded jQuery elements
 *
 */
var wpdatatable_frf_config = {
    dateFilterLogic: null,
    dateFilterFrom: null,
    dateFilterTo: null,
    dateFilterTimeUnits: null,
    dateFilterTimePeriod: null,
    formIDFilterFrom: null,
    formIDFilterTo: null,
    fields: null,
    formType: null,
    formChartType: null,
    formHasLeads: null,
    formId: null,
    setDateFilterLogic: function (logic) {
        wpdatatable_frf_config.dateFilterLogic = logic;
        jQuery('#wdt-frf-date-filter-logic').selectpicker('val', logic);
    },
    setDateFilterFrom: function (dateFrom) {
        wpdatatable_frf_config.dateFilterFrom = dateFrom;
        jQuery('#wdt-frf-date-filter-from').val(dateFrom);
    },
    setDateFilterTo: function (dateTo) {
        wpdatatable_frf_config.dateFilterTo = dateTo;
        jQuery('#wdt-frf-date-filter-to').val(dateTo);
    },
    setDateFilterTimeUnits: function (timeUnits) {
        wpdatatable_frf_config.dateFilterTimeUnits = timeUnits;
        jQuery('#wdt-frf-date-filter-time-units').val(timeUnits);
    },
    setFormIDFilterFrom: function (formID) {
        wpdatatable_frf_config.formIDFilterFrom = formID;
        jQuery('#wdt-frf-filter-from-form-id').val(formID);
    },
    setFormIDFilterTo: function (formID) {
        wpdatatable_frf_config.formIDFilterTo = formID;
        jQuery('#wdt-frf-filter-to-form-id').val(formID);
    },
    setDateFilterTimePeriod: function (timePeriod) {
        wpdatatable_frf_config.dateFilterTimePeriod = timePeriod;
        jQuery('#wdt-frf-date-filter-time-period').selectpicker('val', timePeriod);
    },
    setFields: function (fields) {
        wpdatatable_frf_config.fields = fields;
        jQuery('#wdt-forminator-form-column-picker').selectpicker('val', fields);
    },
    setFormId: function (formId) {
        wpdatatable_frf_config.formId = formId;
        jQuery('#wdt-forminator-form-picker').selectpicker('val', formId);
    },
    setFormType: function (formType) {
        wpdatatable_frf_config.formType = formType;
    },
    setFormChartType: function (formChartType) {
        wpdatatable_frf_config.formChartType = formChartType;
    },
    setFormHasLeads: function (formHasLeads) {
        wpdatatable_frf_config.formHasLeads = formHasLeads;
    },
    getFRFConfig: function () {
        return {
            dateFilterLogic: wpdatatable_frf_config.dateFilterLogic,
            dateFilterFrom: wpdatatable_frf_config.dateFilterFrom,
            dateFilterTo: wpdatatable_frf_config.dateFilterTo,
            dateFilterTimeUnits: wpdatatable_frf_config.dateFilterTimeUnits,
            dateFilterTimePeriod: wpdatatable_frf_config.dateFilterTimePeriod,
            formIDFilterFrom: wpdatatable_frf_config.formIDFilterFrom,
            formIDFilterTo: wpdatatable_frf_config.formIDFilterTo,
            fields: wpdatatable_frf_config.fields,
            formType: wpdatatable_frf_config.formType,
            formChartType: wpdatatable_frf_config.formChartType,
            formHasLeads: wpdatatable_frf_config.formHasLeads,
            formId: wpdatatable_frf_config.formId
        };
    }
};