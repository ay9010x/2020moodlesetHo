<?php




defined('MOODLE_INTERNAL') || die();


class file_info_context_coursecat extends file_info {
    
    protected $category;

    
    public function __construct($browser, $context, $category) {
        parent::__construct($browser, $context);
        $this->category = $category;
    }

    
    public function get_file_info($component, $filearea, $itemid, $filepath, $filename) {
        global $DB;

        if (!$this->category->visible and !has_capability('moodle/category:viewhiddencategories', $this->context)) {
            if (empty($component)) {
                                if ($this->category->parent and $pc = $DB->get_record('course_categories', array('id'=>$this->category->parent))) {
                    $parent = context_coursecat::instance($pc->id, IGNORE_MISSING);
                    return $this->browser->get_file_info($parent);
                } else {
                    return $this->browser->get_file_info();
                }
            }
            return null;
        }

        if (empty($component)) {
            return $this;
        }

        $methodname = "get_area_{$component}_{$filearea}";
        if (method_exists($this, $methodname)) {
            return $this->$methodname($itemid, $filepath, $filename);
        }

        return null;
    }

    
    protected function get_area_coursecat_description($itemid, $filepath, $filename) {
        global $CFG;

        if (!$this->category->visible and !has_capability('moodle/category:viewhiddencategories', $this->context)) {
            return null;
        }
        if (!has_capability('moodle/category:manage', $this->context)) {
            return null;
        }

        if (is_null($itemid)) {
            return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($this->context->id, 'coursecat', 'description', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'coursecat', 'description', 0);
            } else {
                                return null;
            }
        }

        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('areacategoryintro', 'repository'), false, true, true, false);
    }

    
    public function get_visible_name() {
        return format_string($this->category->name, true, array('context'=>$this->context));
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

        if ($child = $this->get_area_coursecat_description(0, '/', '.')) {
            $children[] = $child;
        }

        $course_cats = $DB->get_records('course_categories', array('parent'=>$this->category->id), 'sortorder', 'id,visible');
        foreach ($course_cats as $category) {
            $context = context_coursecat::instance($category->id);
            if (!$category->visible and !has_capability('moodle/category:viewhiddencategories', $context)) {
                continue;
            }
            if ($child = $this->browser->get_file_info($context)) {
                $children[] = $child;
            }
        }

        $courses = $DB->get_records('course', array('category'=>$this->category->id), 'sortorder', 'id,visible');
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            if (!$course->visible and !has_capability('moodle/course:viewhiddencourses', $context)) {
                continue;
            }
            if ($child = $this->browser->get_file_info($context)) {
                $children[] = $child;
            }
        }

        return $children;
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        global $DB;
        $cnt = 0;
        if (($child = $this->get_area_coursecat_description(0, '/', '.'))
                && $child->count_non_empty_children($extensions) && (++$cnt) >= $limit) {
            return $cnt;
        }

        $rs = $DB->get_recordset_sql('SELECT ctx.id AS contextid, c.visible
                FROM {context} ctx, {course} c
                WHERE ctx.instanceid = c.id
                AND ctx.contextlevel = :courselevel
                AND c.category = :categoryid
                ORDER BY c.visible DESC',                 array('categoryid' => $this->category->id, 'courselevel' => CONTEXT_COURSE));
        foreach ($rs as $record) {
            $context = context::instance_by_id($record->contextid);
            if (!$record->visible and !has_capability('moodle/course:viewhiddencourses', $context)) {
                continue;
            }
            if (($child = $this->browser->get_file_info($context))
                    && $child->count_non_empty_children($extensions) && (++$cnt) >= $limit) {
                break;
            }
        }
        $rs->close();
        if ($cnt >= $limit) {
            return $cnt;
        }

        $rs = $DB->get_recordset_sql('SELECT ctx.id AS contextid, cat.visible
                FROM {context} ctx, {course_categories} cat
                WHERE ctx.instanceid = cat.id
                AND ctx.contextlevel = :catlevel
                AND cat.parent = :categoryid
                ORDER BY cat.visible DESC',                 array('categoryid' => $this->category->id, 'catlevel' => CONTEXT_COURSECAT));
        foreach ($rs as $record) {
            $context = context::instance_by_id($record->contextid);
            if (!$record->visible and !has_capability('moodle/category:viewhiddencategories', $context)) {
                continue;
            }
            if (($child = $this->browser->get_file_info($context))
                    && $child->count_non_empty_children($extensions) && (++$cnt) >= $limit) {
                break;
            }
        }
        $rs->close();

        return $cnt;
    }

    
    public function get_parent() {
        $parent = $this->context->get_parent_context();
        return $this->browser->get_file_info($parent);
    }
}
