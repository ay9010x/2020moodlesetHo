<?php



defined('MOODLE_INTERNAL') || die();


function tinymce_spellchecker_migrate_settings() {
    $engine = get_config('editor_tinymce', 'spellengine');
    if ($engine !== false) {
        set_config('spellengine', $engine, 'tinymce_spellchecker');
        unset_config('spellengine', 'editor_tinymce');
    }
    $list = get_config('editor_tinymce', 'spelllanguagelist');
    if ($list !== false) {
        set_config('spelllanguagelist', $list, 'tinymce_spellchecker');
        unset_config('spelllanguagelist', 'editor_tinymce');
    }
}
