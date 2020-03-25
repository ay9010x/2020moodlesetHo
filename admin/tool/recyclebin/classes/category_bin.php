<?php



namespace tool_recyclebin;

defined('MOODLE_INTERNAL') || die();

define('TOOL_RECYCLEBIN_COURSECAT_BIN_FILEAREA', 'recyclebin_coursecat');


class category_bin extends base_bin {

    
    protected $_categoryid;

    
    public function __construct($categoryid) {
        $this->_categoryid = $categoryid;
    }

    
    public static function is_enabled() {
        return get_config('tool_recyclebin', 'categorybinenable');
    }

    
    public function get_item($itemid) {
        global $DB;

        $item = $DB->get_record('tool_recyclebin_category', array(
            'id' => $itemid
        ), '*', MUST_EXIST);

        $item->name = get_course_display_name_for_list($item);

        return $item;
    }

    
    public function get_items() {
        global $DB;

        $items = $DB->get_records('tool_recyclebin_category', array(
            'categoryid' => $this->_categoryid
        ));

        foreach ($items as $item) {
            $item->name = get_course_display_name_for_list($item);
        }

        return $items;
    }

    
    public function store_item($course) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

                $user = get_admin();
        $controller = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
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

                $item = new \stdClass();
        $item->categoryid = $course->category;
        $item->shortname = $course->shortname;
        $item->fullname = $course->fullname;
        $item->timecreated = time();
        $binid = $DB->insert_record('tool_recyclebin_category', $item);

                $filerecord = array(
            'contextid' => \context_coursecat::instance($course->category)->id,
            'component' => 'tool_recyclebin',
            'filearea' => TOOL_RECYCLEBIN_COURSECAT_BIN_FILEAREA,
            'itemid' => $binid,
            'timemodified' => time()
        );

                $fs = get_file_storage();
        if (!$fs->create_file_from_storedfile($filerecord, $file)) {
                        $DB->delete_records('tool_recyclebin_category', array(
                'id' => $binid
            ));

            throw new \moodle_exception("Failed to copy backup file to recyclebin.");
        }

                $file->delete();

                $event = \tool_recyclebin\event\category_bin_item_created::create(array(
            'objectid' => $binid,
            'context' => \context_coursecat::instance($course->category)
        ));
        $event->trigger();
    }

    
    public function restore_item($item) {
        global $CFG, $OUTPUT, $PAGE;

        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        require_once($CFG->dirroot . '/course/lib.php');

        $user = get_admin();

                $context = \context_coursecat::instance($this->_categoryid);

                $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'tool_recyclebin', TOOL_RECYCLEBIN_COURSECAT_BIN_FILEAREA, $item->id,
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

                $course = new \stdClass();
        $course->category = $this->_categoryid;
        $course->shortname = $item->shortname;
        $course->fullname = $item->fullname;
        $course->summary = '';

                $course = create_course($course);
        if (!$course) {
            throw new \moodle_exception("Could not create course to restore into.");
        }

                $controller = new \restore_controller(
            $tempdir,
            $course->id,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $user->id,
            \backup::TARGET_NEW_COURSE
        );

                if (!$controller->execute_precheck()) {
            $results = $controller->get_precheck_results();

                        if (!empty($results['errors'])) {
                                fulldelete($fulltempdir);

                                delete_course($course, false);

                echo $OUTPUT->header();
                $backuprenderer = $PAGE->get_renderer('core', 'backup');
                echo $backuprenderer->precheck_notices($results);
                echo $OUTPUT->continue_button(new \moodle_url('/course/index.php', array('categoryid' => $this->_categoryid)));
                echo $OUTPUT->footer();
                exit();
            }
        }

                $controller->execute_plan();

                $controller->destroy();

                $event = \tool_recyclebin\event\category_bin_item_restored::create(array(
            'objectid' => $item->id,
            'context' => $context
        ));
        $event->add_record_snapshot('tool_recyclebin_category', $item);
        $event->trigger();

                fulldelete($fulltempdir);
        $this->delete_item($item);
    }

    
    public function delete_item($item) {
        global $DB;

                $context = \context_coursecat::instance($this->_categoryid);

                $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'tool_recyclebin', TOOL_RECYCLEBIN_COURSECAT_BIN_FILEAREA, $item->id);
        foreach ($files as $file) {
            $file->delete();
        }

                $DB->delete_records('tool_recyclebin_category', array(
            'id' => $item->id
        ));

                $event = \tool_recyclebin\event\category_bin_item_deleted::create(array(
            'objectid' => $item->id,
            'context' => \context_coursecat::instance($item->categoryid)
        ));
        $event->add_record_snapshot('tool_recyclebin_category', $item);
        $event->trigger();
    }

    
    public function can_view() {
        $context = \context_coursecat::instance($this->_categoryid);
        return has_capability('tool/recyclebin:viewitems', $context);
    }

    
    public function can_restore() {
        $context = \context_coursecat::instance($this->_categoryid);
        return has_capability('tool/recyclebin:restoreitems', $context);
    }

    
    public function can_delete() {
        $context = \context_coursecat::instance($this->_categoryid);
        return has_capability('tool/recyclebin:deleteitems', $context);
    }
}
