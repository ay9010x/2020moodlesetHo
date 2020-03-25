<?php



defined('MOODLE_INTERNAL') || die();


function atto_accessibilitychecker_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('nowarnings',
                                    'report',
                                    'imagesmissingalt',
                                    'needsmorecontrast',
                                    'needsmoreheadings',
                                    'tableswithmergedcells',
                                    'tablesmissingcaption',
                                    'emptytext',
                                    'entiredocument',
                                    'tablesmissingheaders'),
                                    'atto_accessibilitychecker');
}

