<?php




defined('MOODLE_INTERNAL') || die();


class file_info_context_system extends file_info {

    
    public function __construct($browser, $context) {
        parent::__construct($browser, $context);
    }

    
    public function get_file_info($component, $filearea, $itemid, $filepath, $filename) {
        if (empty($component)) {
            return $this;
        }

        $methodname = "get_area_{$component}_{$filearea}";

        if (method_exists($this, $methodname)) {
            return $this->$methodname($itemid, $filepath, $filename);
        }

        return null;
    }

    
    protected function get_area_backup_course($itemid, $filepath, $filename) {
        global $CFG;

        if (!isloggedin()) {
            return null;
        }

        if (!has_any_capability(array('moodle/backup:backupcourse', 'moodle/restore:restorecourse'), $this->context)) {
            return null;
        }

        if (is_null($itemid)) {
            return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'backup', 'course', 0, $filepath, $filename)) {
            if ($filepath === '/' && $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'backup', 'course', 0);
            } else {
                                return null;
            }
        }

        $downloadable = has_capability('moodle/backup:downloadfile', $this->context);
        $uploadable = has_capability('moodle/restore:uploadfile', $this->context);

        $urlbase = $CFG->wwwroot . '/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase,
            get_string('coursebackup', 'repository'), false, $downloadable, $uploadable, false);
    }

    
    public function get_visible_name() {
        return get_string('arearoot', 'repository');
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        global $DB;

        $children = array();

                $course_cats = $DB->get_records('course_categories', array('parent'=>0), 'sortorder', 'id,visible');
        foreach ($course_cats as $category) {
            $context = context_coursecat::instance($category->id);
            if (!$category->visible and !has_capability('moodle/category:viewhiddencategories', $context)) {
                continue;
            }
            if ($child = $this->browser->get_file_info($context)) {
                $children[] = $child;
            }
        }

                        if ($hiddencontexts = $this->get_inaccessible_coursecat_contexts()) {
            $courses = enrol_get_my_courses();
            foreach ($courses as $course) {
                $context = context_course::instance($course->id);
                $parents = $context->get_parent_context_ids();
                if (array_intersect($hiddencontexts, $parents)) {
                                        if ($child = $this->browser->get_file_info($context)) {
                        $children[] = $child;
                    }
                }
            }
        }

        return $children;
    }

    
    protected function get_inaccessible_coursecat_contexts() {
        global $DB;

        $sql = context_helper::get_preload_record_columns_sql('ctx');
        $records = $DB->get_records_sql("SELECT ctx.id, $sql
            FROM {course_categories} c
            JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = ?
            WHERE c.visible = ?", [CONTEXT_COURSECAT, 0]);
        $hiddencontexts = [];
        foreach ($records as $record) {
            context_helper::preload_from_record($record);
            $context = context::instance_by_id($record->id);
            if (!has_capability('moodle/category:viewhiddencategories', $context)) {
                $hiddencontexts[] = $record->id;
            }
        }
        return $hiddencontexts;
    }

    
    public function get_parent() {
        return null;
    }
}
