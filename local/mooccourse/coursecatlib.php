<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir. '/coursecatlib.php');

class local_mooccourse_course_in_list extends course_in_list { 
    public function has_summary() {
        if (isset($this->record->hassummary)) {
            return !empty($this->record->hassummary);
        }
        if (!isset($this->record->summary)) {
            $this->__get('summary');
        }
        return !empty($this->record->summary);
    } 

    public function has_detail($field, $hasname) {
        if (isset($this->record->$hasname)) {
            return !empty($this->record->$hasname);
        }
        if (!isset($this->record->$field)) {
            $this->__get($field);
        }
        return !empty($this->record->$field);
    }

    function get_course_teacherfiles() {
        global $CFG;
        if (empty($CFG->courseoverviewfileslimit)) {
            return array();
        }
        require_once($CFG->libdir. '/filestorage/file_storage.php');
        require_once($CFG->dirroot. '/course/lib.php');
        $fs = get_file_storage();
        $context = context_course::instance($this->id);
        $files = $fs->get_area_files($context->id, 'course', 'teacherfiles', false, 'filename', false);
        if (count($files)) {
            $overviewfilesoptions = course_overviewfiles_options($this->id);
            $acceptedtypes = $overviewfilesoptions['accepted_types'];
            if ($acceptedtypes !== '*') {
                                require_once($CFG->libdir. '/filelib.php');
                foreach ($files as $key => $file) {
                    if (!file_extension_in_typegroup($file->get_filename(), $acceptedtypes)) {
                        unset($files[$key]);
                    }
                }
            }
            if (count($files) > $CFG->courseoverviewfileslimit) {
                                $files = array_slice($files, 0, $CFG->courseoverviewfileslimit, true);
            }
        }
        return $files;
    }
}

