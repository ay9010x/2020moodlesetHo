<?php



defined('MOODLE_INTERNAL') || die();


function atto_table_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('createtable',
                                          'updatetable',
                                          'appearance',
                                          'headers',
                                          'caption',
                                          'columns',
                                          'rows',
                                          'numberofcolumns',
                                          'numberofrows',
                                          'both',
                                          'edittable',
                                          'addcolumnafter',
                                          'addrowafter',
                                          'movecolumnright',
                                          'movecolumnleft',
                                          'moverowdown',
                                          'moverowup',
                                          'deleterow',
                                          'deletecolumn',
                                          'captionposition',
                                          'borders',
                                          'bordersize',
                                          'bordercolour',
                                          'borderstyles',
                                          'none',
                                          'all',
                                          'backgroundcolour',
                                          'width',
                                          'outer',
                                          'noborder',
                                          'themedefault',
                                          'dotted',
                                          'dashed',
                                          'solid'),
                                    'atto_table');

    $PAGE->requires->strings_for_js(array('top',
                                          'bottom'),
                                    'editor');
}


function atto_table_params_for_js($elementid, $options, $foptions) {
    $params = array('allowBorders' => (bool) get_config('atto_table', 'allowborders'),
                    'allowWidth' => (bool) get_config('atto_table', 'allowwidth'),
                    'allowBackgroundColour' => (bool) get_config('atto_table', 'allowbackgroundcolour'));
    return $params;
}
