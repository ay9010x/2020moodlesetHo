<?php



namespace tool_recyclebin;

defined('MOODLE_INTERNAL') || die();

define('TOOL_RECYCLEBIN_COURSE_BIN_FILEAREA', 'recyclebin_course');


class course_bin extends base_bin {

    
    protected $_courseid;

    
    public function __construct($courseid) {
        $this->_courseid = $courseid;
    }

    
    public static function is_enabled() {
        return get_config('tool_recyclebin', 'coursebinenable');
    }

    
    public function get_item($itemid) {
        global $DB;

        return $DB->get_record('tool_recyclebin_course', array(
            'id' => $itemid
        ), '*', MUST_EXIST);
    }

    
    public function get_items() {
        global $DB;

        return $DB->get_records('tool_recyclebin_course', array(
            'courseid' => $this->_courseid
        ));
    }

    
    public function store_item($cm) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

                $modinfo = get_fast_modinfo($cm->course);

        if (!isset($modinfo->cms[$cm->id])) {
            return;         }

        $cminfo = $modinfo->cms[$cm->id];

                if (!plugin_supports('mod', $cminfo->modname , FEATURE_BACKUP_MOODLE2)) {
            return;
        }

                $user = get_admin();
        $controller = new \backup_controller(
            \backup::TYPE_1ACTIVITY,
            $cm->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $user->id
        );
        $controller->execute_plan();

                $result = $controller->get_results();
        if (!isset($result['backup_destination'])) {
            throw new \moodle_exception('Failed to backup activity prior to deletion.');
        }

                $controller->destroy();

                $file = $result['backup_destination'];
        if (!$file->get_contenthash()) {
            throw new \moodle_exception('Failed to backup activity prior to deletion (invalid file).');
        }

                $activity = new \stdClass();
        $activity->courseid = $cm->course;
        $activity->section = $cm->section;
        $activity->module = $cm->module;
        $activity->name = $cminfo->name;
        $activity->timecreated = time();
        $binid = $DB->insert_record('tool_recyclebin_course', $activity);

                $filerecord = array(
            'contextid' => \context_course::instance($this->_courseid)->id,
            'component' => 'tool_recyclebin',
            'filearea' => TOOL_RECYCLEBIN_COURSE_BIN_FILEAREA,
            'itemid' => $binid,
            'timemodified' => time()
        );

                $fs = get_file_storage();
        if (!$fs->create_file_from_storedfile($filerecord, $file)) {
                        $DB->delete_records('tool_recyclebin_course', array(
                'id' => $binid
            ));

            throw new \moodle_exception("Failed to copy backup file to recyclebin.");
        }

                $file->delete();

                $event = \tool_recyclebin\event\course_bin_item_created::create(array(
            'objectid' => $binid,
            'context' => \context_course::instance($cm->course)
        ));
        $event->trigger();
    }

    
    public function restore_item($item) {
        global $CFG, $OUTPUT, $PAGE;

        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $user = get_admin();

                $context = \context_course::instance($this->_courseid);

                $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'tool_recyclebin', TOOL_RECYCLEBIN_COURSE_BIN_FILEAREA, $item->id,
            'itemid, filepath, filename', false);

        if (empty($files)) {
            throw new \moodle_exception('Invalid recycle bin item!');
        }

        if (count($files) > 1) {
            throw new \moodle_exception('Too many files found!');
        }

                $file = reset($files);

                $tempdir = \restore_controller::get_tempdir_name($context->id, $user->id);
        $fulltempdir = make_temp_directory('/backup/' . $tempdir);

                $fb = get_file_packer('application/vnd.moodle.backup');
        $fb->extract_to_pathname($file, $fulltempdir);

                $controller = new \restore_controller(
            $tempdir,
            $this->_courseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $user->id,
            \backup::TARGET_EXISTING_ADDING
        );

                if (!$controller->execute_precheck()) {
            $results = $controller->get_precheck_results();

                        if (!empty($results['errors'])) {
                fulldelete($fulltempdir);

                echo $OUTPUT->header();
                $backuprenderer = $PAGE->get_renderer('core', 'backup');
                echo $backuprenderer->precheck_notices($results);
                echo $OUTPUT->continue_button(new \moodle_url('/course/view.php', array('id' => $this->_courseid)));
                echo $OUTPUT->footer();
                exit();
            }
        }

                $controller->execute_plan();

                $controller->destroy();

                $event = \tool_recyclebin\event\course_bin_item_restored::create(array(
            'objectid' => $item->id,
            'context' => $context
        ));
        $event->add_record_snapshot('tool_recyclebin_course', $item);
        $event->trigger();

                fulldelete($fulltempdir);
        $this->delete_item($item);
    }

    
    public function delete_item($item) {
        global $DB;

                $context = \context_course::instance($this->_courseid);

                $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'tool_recyclebin', TOOL_RECYCLEBIN_COURSE_BIN_FILEAREA, $item->id);
        foreach ($files as $file) {
            $file->delete();
        }

                $DB->delete_records('tool_recyclebin_course', array(
            'id' => $item->id
        ));

                $context = \context_course::instance($item->courseid, \IGNORE_MISSING);
        if (!$context) {
            return;
        }

                $event = \tool_recyclebin\event\course_bin_item_deleted::create(array(
            'objectid' => $item->id,
            'context' => $context
        ));
        $event->add_record_snapshot('tool_recyclebin_course', $item);
        $event->trigger();
    }

    
    public function can_view() {
        $context = \context_course::instance($this->_courseid);
        return has_capability('tool/recyclebin:viewitems', $context);
    }

    
    public function can_restore() {
        $context = \context_course::instance($this->_courseid);
        return has_capability('tool/recyclebin:restoreitems', $context);
    }

    
    public function can_delete() {
        $context = \context_course::instance($this->_courseid);
        return has_capability('tool/recyclebin:deleteitems', $context);
    }
}
