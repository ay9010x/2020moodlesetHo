<?php



defined('MOODLE_INTERNAL') || die();


function xmldb_editor_atto_install() {
    global $CFG;

        $currenteditors = $CFG->texteditors;
    $neweditors = array();

    $list = explode(',', $currenteditors);
    array_push($neweditors, 'atto');
    foreach ($list as $editor) {
        if ($editor != 'atto') {
            array_push($neweditors, $editor);
        }
    }

    set_config('texteditors', implode(',', $neweditors));

    return true;
}
