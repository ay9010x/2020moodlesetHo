<?php



defined('MOODLE_INTERNAL') || die();


class mod_folder_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
                $record = (array)$record + array('display' => 0);
        if (!isset($record['showexpanded'])) {
            $record['showexpanded'] = get_config('folder', 'showexpanded');
        }
        if (!isset($record['files'])) {
            $record['files'] = file_get_unused_draft_itemid();
        }
        return parent::create_instance($record, (array)$options);
    }
}
