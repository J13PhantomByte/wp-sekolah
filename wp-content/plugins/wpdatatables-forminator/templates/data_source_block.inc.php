<?php defined('ABSPATH') or die('Access denied.');

foreach (['forms','quizzes','polls'] as $formType){
    $forms[$formType] = WDTForminatorIntegration\Plugin::getForminatorFormsArr($formType);
}
?>

<!-- .col Forminator form selection -->
<div class="col-sm-6 hidden" id="wdt-frf-form-container">

    <h4 class="c-title-color m-b-2">
        <?php esc_html_e('Choose a Forminator Form', 'wpdatatables'); ?>
        <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right" title=""
           data-original-title="Please choose a Forminator Form that will be used as data source for wpDataTable"></i>
    </h4>

    <div class="form-group">
        <div class="fg-line">
            <select class="selectpicker" data-live-search="true" id="wdt-forminator-form-picker">
                <option value=""><?php esc_html_e('Pick a Forminator form...', 'wpdatatables'); ?></option>
                <?php foreach ($forms['forms'] as $form) { ?>
                    <option value="<?php echo $form->id; ?>"
                            data-form-type="<?php echo $form->get_post_type(); ?>"><?php esc_html_e('Form -> ', 'wpdatatables'); ?><?php echo $form->settings['formName'] ?></option>
                <?php } ?>
                <?php foreach ($forms['quizzes'] as $quiz) { ?>
                    <option value="<?php echo $quiz->id; ?>"
                            data-form-has-leads="<?php echo ($quiz->settings['hasLeads']) ? '1' : '0' ?>"
                            data-form-type="<?php echo $quiz->get_post_type(); ?>"><?php esc_html_e('Quiz -> ', 'wpdatatables'); ?><?php echo ($quiz->quiz_type == 'nowrong') ? $quiz->settings['formName'] . ' ' . esc_html__('(Personality)', 'wpdatatables') : $quiz->settings['formName'] . ' ' . esc_html__('(Knowledge)', 'wpdatatables'); ?></option>
                <?php } ?>
                <?php foreach ($forms['polls'] as $poll) { ?>
                    <option value="<?php echo $poll->id; ?>" data-form-chart-type="bar"
                            data-form-type="<?php echo $poll->get_post_type(); ?>"><?php esc_html_e('Poll -> ', 'wpdatatables'); ?><?php echo $poll->settings['formName'] . ' ' . esc_html__('(for Bar chart)', 'wpdatatables'); ?></option>
                    <option value="<?php echo $poll->id + 10000000; ?>" data-form-chart-type="pie"
                            data-form-type="<?php echo $poll->get_post_type(); ?>"><?php esc_html_e('Poll -> ', 'wpdatatables'); ?><?php echo $poll->settings['formName'] . ' ' . esc_html__('(for Pie chart)', 'wpdatatables') ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

</div>
<!-- /.col Forminator form selection -->

<!-- .col Fields selection -->
<div class="col-sm-6 hidden" id="wdt-frf-column-container">
    <h4 class="c-title-color m-b-2">
        <?php esc_html_e('Choose fields to show as columns', 'wpdatatables'); ?>
        <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="left" title=""
           data-original-title="Please choose fields that will be used as wpDataTable columns"></i>
    </h4>

    <div class="form-group">
        <div class="fg-line">
            <select class="selectpicker" multiple="true" id="wdt-forminator-form-column-picker" data-actions-box="true">

            </select>
        </div>
    </div>

</div>
<!-- /.col Fields selection -->