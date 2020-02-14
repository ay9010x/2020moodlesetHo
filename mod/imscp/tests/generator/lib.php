<?php



defined('MOODLE_INTERNAL') || die();


class mod_imscp_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        global $CFG, $USER;

                $record = (array)$record + array(
            'package' => '',
            'packagepath' => $CFG->dirroot.'/mod/imscp/tests/packages/singlescobasic.zip',
            'keepold' => -1
        );

                if (empty($record['package'])) {
            if (!isloggedin() || isguestuser()) {
                throw new coding_exception('IMSCP generator requires a current user');
            }
            if (!file_exists($record['packagepath'])) {
                throw new coding_exception("File {$record['packagepath']} does not exist");
            }
            $usercontext = context_user::instance($USER->id);

                        $record['package'] = file_get_unused_draft_itemid();

                        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                    'contextid' => $usercontext->id, 'itemid' => $record['package'],
                    'filename' => basename($record['packagepath']), 'filepath' => '/');
            $fs = get_file_storage();
            $fs->create_file_from_pathname($filerecord, $record['packagepath']);
        }

        return parent::create_instance($record, (array)$options);
    }
}
