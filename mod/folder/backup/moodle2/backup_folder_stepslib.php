<?php




defined('MOODLE_INTERNAL') || die();


class backup_folder_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $folder = new backup_nested_element('folder', array('id'), array(
            'name', 'intro', 'introformat', 'revision',
            'timemodified', 'display', 'showexpanded'));

                
                $folder->set_source_table('folder', array('id' => backup::VAR_ACTIVITYID));

                
                $folder->annotate_files('mod_folder', 'intro', null);
        $folder->annotate_files('mod_folder', 'content', null);

                return $this->prepare_activity_structure($folder);
    }
}
