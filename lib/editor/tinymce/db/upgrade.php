<?php



defined('MOODLE_INTERNAL') || die();

function xmldb_editor_tinymce_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2014062900) {
                        if (!check_dir_exists($CFG->libdir . '/editor/tinymce/plugins/dragmath', false)) {
                        $currentorder = get_config('editor_tinymce', 'customtoolbar');
            $newtoolbarrows = array();
            $currenttoolbarrows = explode("\n", $currentorder);
            foreach ($currenttoolbarrows as $currenttoolbarrow) {
                $currenttoolbarrow = implode(',', array_diff(str_getcsv($currenttoolbarrow), array('dragmath')));
                $newtoolbarrows[] = $currenttoolbarrow;
            }
            $neworder = implode("\n", $newtoolbarrows);
            unset_config('customtoolbar', 'editor_tinymce');
            set_config('customtoolbar', $neworder, 'editor_tinymce');
        }

        upgrade_plugin_savepoint(true, 2014062900, 'editor', 'tinymce');
    }

        
        
        
        
    return true;
}
