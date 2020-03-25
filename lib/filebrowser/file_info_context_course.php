<?php




defined('MOODLE_INTERNAL') || die();


class file_info_context_course extends file_info {
    
    protected $course;

    
    public function __construct($browser, $context, $course) {
        parent::__construct($browser, $context);
        $this->course   = $course;
    }

    
    public function get_file_info($component, $filearea, $itemid, $filepath, $filename) {
                if (!isloggedin()) {
            return null;
        }

        if (!$this->course->visible and !has_capability('moodle/course:viewhiddencourses', $this->context)) {
            return null;
        }

        if (!is_viewing($this->context) and !is_enrolled($this->context)) {
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

    
    protected function get_area_course_summary($itemid, $filepath, $filename) {
        global $CFG;

        if (!has_capability('moodle/course:update', $this->context)) {
            return null;
        }
        if (is_null($itemid)) {
            return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'course', 'summary', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'course', 'summary', 0);
            } else {
                                return null;
            }
        }
        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('areacourseintro', 'repository'), false, true, true, false);
    }

    
    protected function get_area_course_overviewfiles($itemid, $filepath, $filename) {
        global $CFG;

        if (!has_capability('moodle/course:update', $this->context)) {
            return null;
        }
        if (is_null($itemid)) {
            return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'course', 'overviewfiles', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'course', 'overviewfiles', 0);
            } else {
                                return null;
            }
        }
        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('areacourseoverviewfiles', 'repository'), false, true, true, false);
    }

    
    protected function get_area_course_section($itemid, $filepath, $filename) {
        global $CFG, $DB;

        if (!has_capability('moodle/course:update', $this->context)) {
            return null;
        }

        if (empty($itemid)) {
                        return new file_info_area_course_section($this->browser, $this->context, $this->course, $this);
        }

        if (!$section = $DB->get_record('course_sections', array('course'=>$this->course->id, 'id'=>$itemid))) {
            return null;         }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'course', 'section', $itemid, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'course', 'section', $itemid);
            } else {
                                return null;
            }
        }
        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, $section->section, true, true, true, false);
    }

    
    protected function get_area_course_legacy($itemid, $filepath, $filename) {
        if (!has_capability('moodle/course:managefiles', $this->context)) {
            return null;
        }

        if ($this->course->id != SITEID and $this->course->legacyfiles != 2) {
                    }

        if (is_null($itemid)) {
            return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'course', 'legacy', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'course', 'legacy', 0);
            } else {
                                return null;
            }
        }

        return new file_info_area_course_legacy($this->browser, $this->context, $storedfile);
    }

    
    protected function get_area_backup_course($itemid, $filepath, $filename) {
        global $CFG;

        if (!has_capability('moodle/backup:backupcourse', $this->context) and !has_capability('moodle/restore:restorecourse', $this->context)) {
            return null;
        }
        if (is_null($itemid)) {
            return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'backup', 'course', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'backup', 'course', 0);
            } else {
                                return null;
            }
        }

        $downloadable = has_capability('moodle/backup:downloadfile', $this->context);
        $uploadable   = has_capability('moodle/restore:uploadfile', $this->context);

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('coursebackup', 'repository'), false, $downloadable, $uploadable, false);
    }

    
    protected function get_area_backup_automated($itemid, $filepath, $filename) {
        global $CFG;

        if (!has_capability('moodle/restore:viewautomatedfilearea', $this->context)) {
            return null;
        }
        if (is_null($itemid)) {
            return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'backup', 'automated', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'backup', 'automated', 0);
            } else {
                                return null;
            }
        }

        $downloadable = has_capability('moodle/site:config', $this->context);
        $uploadable   = false;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('automatedbackup', 'repository'), true, $downloadable, $uploadable, false);
    }

    
    protected function get_area_backup_section($itemid, $filepath, $filename) {
        global $CFG, $DB;

        if (!has_capability('moodle/backup:backupcourse', $this->context) and !has_capability('moodle/restore:restorecourse', $this->context)) {
            return null;
        }

        if (empty($itemid)) {
                        return new file_info_area_backup_section($this->browser, $this->context, $this->course, $this);
        }

        if (!$section = $DB->get_record('course_sections', array('course'=>$this->course->id, 'id'=>$itemid))) {
            return null;         }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'backup', 'section', $itemid, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'backup', 'section', $itemid);
            } else {
                                return null;
            }
        }

        $downloadable = has_capability('moodle/backup:downloadfile', $this->context);
        $uploadable   = has_capability('moodle/restore:uploadfile', $this->context);

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, $section->id, true, $downloadable, $uploadable, false);
    }

    
    public function get_visible_name() {
        return ($this->course->id == SITEID) ? get_string('frontpage', 'admin') : format_string(get_course_display_name_for_list($this->course), true, array('context'=>$this->context));
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        $areas = array(
            array('course', 'summary'),
            array('course', 'overviewfiles'),
            array('course', 'section'),
            array('backup', 'section'),
            array('backup', 'course'),
            array('backup', 'automated'),
            array('course', 'legacy')
        );
        $children = array();
        foreach ($areas as $area) {
            if ($child = $this->get_file_info($area[0], $area[1], 0, '/', '.')) {
                if ($returnemptyfolders || $child->count_non_empty_children($extensions)) {
                    $children[] = $child;
                    if (($countonly !== false) && count($children) >= $countonly) {
                        return $countonly;
                    }
                }
            }
        }

        if (!has_capability('moodle/course:managefiles', $this->context)) {
                                } else {
                        $modinfo = get_fast_modinfo($this->course);
            foreach ($modinfo->cms as $cminfo) {
                if (empty($cminfo->uservisible)) {
                    continue;
                }
                $modcontext = context_module::instance($cminfo->id, IGNORE_MISSING);
                if ($child = $this->browser->get_file_info($modcontext)) {
                    if ($returnemptyfolders || $child->count_non_empty_children($extensions)) {
                        $children[] = $child;
                        if (($countonly !== false) && count($children) >= $countonly) {
                            return $countonly;
                        }
                    }
                }
            }
        }

        if ($countonly !== false) {
            return count($children);
        }
        return $children;
    }

    
    public function get_non_empty_children($extensions = '*') {
        return $this->get_filtered_children($extensions, false);
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        return $this->get_filtered_children($extensions, $limit);
    }

    
    public function get_parent() {
        $parent = $this->context->get_parent_context();
        return $this->browser->get_file_info($parent);
    }
}



class file_info_area_course_legacy extends file_info_stored {
    
    public function __construct($browser, $context, $storedfile) {
        global $CFG;
        $urlbase = $CFG->wwwroot.'/file.php';
        parent::__construct($browser, $context, $storedfile, $urlbase, get_string('coursefiles'), false, true, true, false);
    }

    
    public function get_url($forcedownload=false, $https=false) {
        if (!$this->is_readable()) {
            return null;
        }

        if ($this->lf->is_directory()) {
            return null;
        }

        $filepath = $this->lf->get_filepath();
        $filename = $this->lf->get_filename();
        $courseid = $this->context->instanceid;

        $path = '/'.$courseid.$filepath.$filename;

        return file_encode_url($this->urlbase, $path, $forcedownload, $https);
    }

    
    public function get_children() {
        if (!$this->lf->is_directory()) {
            return array();
        }

        $result = array();
        $fs = get_file_storage();

        $storedfiles = $fs->get_directory_files($this->context->id, 'course', 'legacy', 0, $this->lf->get_filepath(), false, true, "filepath ASC, filename ASC");
        foreach ($storedfiles as $file) {
            $result[] = new file_info_area_course_legacy($this->browser, $this->context, $file);
        }

        return $result;
    }

    
    public function get_non_empty_children($extensions = '*') {
        if (!$this->lf->is_directory()) {
            return array();
        }

        $result = array();
        $fs = get_file_storage();

        $storedfiles = $fs->get_directory_files($this->context->id, 'course', 'legacy', 0,
                                                $this->lf->get_filepath(), false, true, "filepath, filename");
        foreach ($storedfiles as $file) {
            $extension = core_text::strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
            if ($file->is_directory() || $extensions === '*' || (!empty($extension) && in_array('.'.$extension, $extensions))) {
                $fileinfo = new file_info_area_course_legacy($this->browser, $this->context, $file, $this->urlbase, $this->topvisiblename,
                                                 $this->itemidused, $this->readaccess, $this->writeaccess, false);
                if (!$file->is_directory() || $fileinfo->count_non_empty_children($extensions)) {
                    $result[] = $fileinfo;
                }
            }
        }

        return $result;
    }
}


class file_info_area_course_section extends file_info {
    
    protected $course;
    
    protected $courseinfo;

    
    public function __construct($browser, $context, $course, file_info_context_course $courseinfo) {
        parent::__construct($browser, $context);
        $this->course     = $course;
        $this->courseinfo = $courseinfo;
    }

    
    public function get_params() {
        return array('contextid' => $this->context->id,
                     'component' => 'course',
                     'filearea'  => 'section',
                     'itemid'    => null,
                     'filepath'  => null,
                     'filename'  => null);
    }

    
    public function get_visible_name() {
                $sectionsname = get_string("coursesectionsummaries");

        return $sectionsname;
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_empty_area() {
        $fs = get_file_storage();
        return $fs->is_area_empty($this->context->id, 'course', 'section');
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        global $DB;

        $children = array();

        $course_sections = $DB->get_records('course_sections', array('course'=>$this->course->id), 'section');
        foreach ($course_sections as $section) {
            if ($child = $this->courseinfo->get_file_info('course', 'section', $section->id, '/', '.')) {
                $children[] = $child;
            }
        }

        return $children;
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        global $DB;
        $params1 = array(
            'courseid' => $this->course->id,
            'contextid' => $this->context->id,
            'component' => 'course',
            'filearea' => 'section',
            'emptyfilename' => '.');
        $sql1 = "SELECT DISTINCT cs.id FROM {files} f, {course_sections} cs
            WHERE cs.course = :courseid
            AND f.contextid = :contextid
            AND f.component = :component
            AND f.filearea = :filearea
            AND f.itemid = cs.id
            AND f.filename <> :emptyfilename";
        list($sql2, $params2) = $this->build_search_files_sql($extensions);
        $rs = $DB->get_recordset_sql($sql1. ' '. $sql2, array_merge($params1, $params2));
        $cnt = 0;
        foreach ($rs as $record) {
            if ((++$cnt) >= $limit) {
                break;
            }
        }
        $rs->close();
        return $cnt;
    }

    
    public function get_parent() {
        return $this->courseinfo;
    }
}



class file_info_area_backup_section extends file_info {
    
    protected $course;
    
    protected $courseinfo;

    
    public function __construct($browser, $context, $course, file_info_context_course $courseinfo) {
        parent::__construct($browser, $context);
        $this->course     = $course;
        $this->courseinfo = $courseinfo;
    }

    
    public function get_params() {
        return array('contextid' => $this->context->id,
                     'component' => 'backup',
                     'filearea'  => 'section',
                     'itemid'    => null,
                     'filepath'  => null,
                     'filename'  => null);
    }

    
    public function get_visible_name() {
        return get_string('sectionbackup', 'repository');
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_empty_area() {
        $fs = get_file_storage();
        return $fs->is_area_empty($this->context->id, 'backup', 'section');
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        global $DB;

        $children = array();

        $course_sections = $DB->get_records('course_sections', array('course'=>$this->course->id), 'section');
        foreach ($course_sections as $section) {
            if ($child = $this->courseinfo->get_file_info('backup', 'section', $section->id, '/', '.')) {
                $children[] = $child;
            }
        }

        return $children;
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        global $DB;
        $params1 = array(
            'courseid' => $this->course->id,
            'contextid' => $this->context->id,
            'component' => 'backup',
            'filearea' => 'section',
            'emptyfilename' => '.');
        $sql1 = "SELECT DISTINCT cs.id AS sectionid FROM {files} f, {course_sections} cs
            WHERE cs.course = :courseid
            AND f.contextid = :contextid
            AND f.component = :component
            AND f.filearea = :filearea
            AND f.itemid = cs.id
            AND f.filename <> :emptyfilename";
        list($sql2, $params2) = $this->build_search_files_sql($extensions);
        $rs = $DB->get_recordset_sql($sql1. ' '. $sql2, array_merge($params1, $params2));
        $cnt = 0;
        foreach ($rs as $record) {
            if ((++$cnt) >= $limit) {
                break;
            }
        }
        $rs->close();
        return $cnt;
    }

    
    public function get_parent() {
        return $this->browser->get_file_info($this->context);
    }
}
