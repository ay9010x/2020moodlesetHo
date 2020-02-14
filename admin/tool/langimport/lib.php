<?php



defined('MOODLE_INTERNAL') || die;


function tool_langimport_preupgrade_update($lang) {
    global $CFG, $OUTPUT;
    require_once($CFG->libdir.'/componentlib.class.php');

    echo $OUTPUT->heading(get_string('langimport', 'tool_langimport').': '.$lang);

    @mkdir ($CFG->tempdir.'/');        @mkdir ($CFG->dataroot.'/lang/');

    $installer = new lang_installer($lang);
    $results = $installer->run();
    foreach ($results as $langcode => $langstatus) {
        switch ($langstatus) {
        case lang_installer::RESULT_DOWNLOADERROR:
            echo $OUTPUT->notification($langcode . '.zip');
            break;
        case lang_installer::RESULT_INSTALLED:
            echo $OUTPUT->notification(get_string('langpackinstalled', 'tool_langimport', $langcode), 'notifysuccess');
            break;
        case lang_installer::RESULT_UPTODATE:
            echo $OUTPUT->notification(get_string('langpackuptodate', 'tool_langimport', $langcode), 'notifysuccess');
            break;
        }
    }
}
