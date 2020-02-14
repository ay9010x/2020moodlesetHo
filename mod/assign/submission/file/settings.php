<?php




$settings->add(new admin_setting_configcheckbox('assignsubmission_file/default',
                   new lang_string('default', 'assignsubmission_file'),
                   new lang_string('default_help', 'assignsubmission_file'), 1));

$settings->add(new admin_setting_configtext('assignsubmission_file/maxfiles',
                   new lang_string('maxfiles', 'assignsubmission_file'),
                   new lang_string('maxfiles_help', 'assignsubmission_file'), 20, PARAM_INT));

if (isset($CFG->maxbytes)) {

    $name = new lang_string('maximumsubmissionsize', 'assignsubmission_file');
    $description = new lang_string('configmaxbytes', 'assignsubmission_file');

    $maxbytes = get_config('assignsubmission_file', 'maxbytes');
    $element = new admin_setting_configselect('assignsubmission_file/maxbytes',
                                              $name,
                                              $description,
                                              $CFG->maxbytes,
                                              get_max_upload_sizes($CFG->maxbytes, 0, 0, $maxbytes));
    $settings->add($element);
}
