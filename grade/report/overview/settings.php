<?php



defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('grade_report_overview_showrank', get_string('showrank', 'grades'),
                   get_string('showrank_help', 'grades'), 0));

    $settings->add(new admin_setting_configselect('grade_report_overview_showtotalsifcontainhidden', get_string('hidetotalifhiddenitems', 'grades'),
                                                      get_string('hidetotalifhiddenitems_help', 'grades'), GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN,
                                                      array(GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN => get_string('hide'),
                                                            GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowexhiddenitems', 'grades'),
                                                            GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowinchiddenitems', 'grades') )));
}
