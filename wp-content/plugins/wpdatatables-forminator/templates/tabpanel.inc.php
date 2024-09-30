<?php defined('ABSPATH') or die('Access denied.'); ?>

<!-- Forminator Form settings -->
<div role="tabpanel" class="tab-pane" id="forminator-settings">
    <!-- .row -->
    <div class="row">
        <!-- Filter by date range -->
        <div class="col-sm-12 m-b-16 wdt-frf-filter-by-form-id-range-block">
            <h4 class="c-title-color m-b-4">
                <?php esc_html_e('Filter by entry ID range', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Here you can filter table by providing range entry ID value', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="row">
                <div class="col-md-6 p-l-0 p-r-0 wdt-frf-form-id-block">
                    <div class="col-md-4">
                        <div class="fg-line wdt-custom-number-input">
                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                    data-type="minus" data-field="wdt-frf-filter-from-form-id">
                                <i class="wpdt-icon-minus"></i>
                            </button>
                            <input type="number" name="wdt-frf-filter-from-form-id" min="1" pattern="^[0-9]*$"
                                   class="form-control input-sm input-number" id="wdt-frf-filter-from-form-id"
                                   placeholder="From">
                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                    data-type="plus" data-field="wdt-frf-filter-from-form-id">
                                <i class="wpdt-icon-plus-full"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="fg-line wdt-custom-number-input">
                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                    data-type="minus" data-field="wdt-frf-filter-to-form-id">
                                <i class="wpdt-icon-minus"></i>
                            </button>
                            <input type="number" name="wdt-frf-filter-to-form-id" min="1" pattern="^[0-9]*$"
                                   class="form-control input-sm input-number" id="wdt-frf-filter-to-form-id"
                                   placeholder="To">
                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                    data-type="plus" data-field="wdt-frf-filter-to-form-id">
                                <i class="wpdt-icon-plus-full"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Filter by date range -->
    </div>
    <!-- /.row -->
    <!-- .row -->
    <div class="row">
        <!-- Filter by date range -->
        <div class="col-sm-12 m-b-16 wdt-frf-filter-by-date-range-block">
            <h4 class="c-title-color m-b-4">
                <?php esc_html_e('Filter by entry date', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Chose date filter logic if you want to filter form entries by date', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="row">
                <div class='col-md-4 wdt-frf-date-filter-logic-block'>
                    <div class="form-group">
                        <div class="fg-line">
                            <div class="select">
                                <select class="selectpicker" id="wdt-frf-date-filter-logic">
                                    <option value=""><?php esc_html_e('Select date filter logic', 'wpdatatables'); ?></option>
                                    <option value="range"><?php esc_html_e('Filter by date range', 'wpdatatables'); ?></option>
                                    <option value="last"><?php esc_html_e('Filter by last X time period', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 hidden p-l-0 wdt-frf-date-range-block">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="date">
                                <input class="form-control wdt-datetimepicker" id="wdt-frf-date-filter-from"
                                       placeholder="From"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="date">
                                <input class="form-control wdt-datetimepicker" id="wdt-frf-date-filter-to"
                                       placeholder="To"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 hidden p-l-0 p-r-0 wdt-frf-last-x-block">
                    <div class="col-md-6">
                        <div class="fg-line wdt-custom-number-input">
                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                    data-type="minus" data-field="wdt-frf-date-filter-time-units">
                                <i class="wpdt-icon-minus"></i>
                            </button>
                            <input type="text" name="wdt-frf-date-filter-time-units" min="1"
                                   class="form-control input-sm input-number" id="wdt-frf-date-filter-time-units">
                            <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                    data-type="plus" data-field="wdt-frf-date-filter-time-units">
                                <i class="wpdt-icon-plus-full"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-frf-date-filter-time-period">
                                        <option value=""></option>
                                        <option value="days"><?php esc_html_e('Day(s)', 'wpdatatables'); ?></option>
                                        <option value="weeks"><?php esc_html_e('Week(s)', 'wpdatatables'); ?></option>
                                        <option value="months"><?php esc_html_e('Month(s)', 'wpdatatables'); ?></option>
                                        <option value="years"><?php esc_html_e('Year(s)', 'wpdatatables'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Filter by date range -->
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-md-4">
            <button id="wdt-frf-clear-all-filters"
                    class="btn btn-primary"> <?php esc_html_e('Reset filters', 'wpdatatables'); ?></button>
        </div>
    </div>
</div>
<!-- /Forminator Form settings -->