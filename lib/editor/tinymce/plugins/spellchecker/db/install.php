<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_tinymce_spellchecker_install() {
    global $CFG, $DB;
    require_once(__DIR__.'/upgradelib.php');

    tinymce_spellchecker_migrate_settings();
}
