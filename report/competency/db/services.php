<?php




defined('MOODLE_INTERNAL') || die();

$functions = array(

    
    'report_competency_data_for_report' => array(
        'classname'    => 'report_competency\external',
        'methodname'   => 'data_for_report',
        'classpath'    => '',
        'description'  => 'Load the data for the competency report in a course.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:coursecompetencyview',
        'ajax'         => true,
    )
);

