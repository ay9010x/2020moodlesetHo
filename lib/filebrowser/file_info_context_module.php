<?php




defined('MOODLE_INTERNAL') || die();


class file_info_context_module extends file_info {
    
    protected $course;
    
    protected $cm;
    
    protected $modname;
    
    protected $areas;
    
    protected $nonemptychildren;

    
    public function __construct($browser, $context, $course, $cm, $modname) {
        global $CFG;

        parent::__construct($browser, $context);
        $this->course  = $course;
        $this->cm      = $cm;
        $this->modname = $modname;
        $this->nonemptychildren = null;

        include_once("$CFG->dirroot/mod/$modname/lib.php");

                $functionname     = 'mod_'.$modname.'_get_file_areas';
        $functionname_old = $modname.'_get_file_areas';

        if (function_exists($functionname)) {
            $this->areas = $functionname($course, $cm, $context);
        } else if (function_exists($functionname_old)) {
            $this->areas = $functionname_old($course, $cm, $context);
        } else {
            $this->areas = array();
        }
        unset($this->areas['intro']);     }

    
    public function get_file_info($component, $filearea, $itemid, $filepath, $filename) {
                if (!isloggedin()) {
            return null;
        }

        $coursecontext = $this->context->get_course_context(true);
        if (!$this->course->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
            return null;
        }

        if (!is_viewing($this->context) and !is_enrolled($this->context)) {
                        return null;
        }

        $modinfo = get_fast_modinfo($this->course);
        $cminfo = $modinfo->get_cm($this->cm->id);
        if (!$cminfo->uservisible) {
                        return null;
        }

        if (empty($component)) {
            return $this;
        }

        if ($component == 'mod_'.$this->modname and $filearea === 'intro') {
            return $this->get_area_intro($itemid, $filepath, $filename);
        } else if ($component == 'backup' and $filearea === 'activity') {
            return $this->get_area_backup($itemid, $filepath, $filename);
        }

        $functionname     = 'mod_'.$this->modname.'_get_file_info';
        $functionname_old = $this->modname.'_get_file_info';

        if (function_exists($functionname)) {
            return $functionname($this->browser, $this->areas, $this->course, $this->cm, $this->context, $filearea, $itemid, $filepath, $filename);
        } else if (function_exists($functionname_old)) {
            return $functionname_old($this->browser, $this->areas, $this->course, $this->cm, $this->context, $filearea, $itemid, $filepath, $filename);
        }

        return null;
    }

    
    protected function get_area_intro($itemid, $filepath, $filename) {
        global $CFG;

        if (!plugin_supports('mod', $this->modname, FEATURE_MOD_INTRO, true) or !has_capability('moodle/course:managefiles', $this->context)) {
            return null;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'mod_'.$this->modname, 'intro', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'mod_'.$this->modname, 'intro', 0);
            } else {
                                return null;
            }
        }

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('moduleintro'), false, true, true, false);
    }

    
    protected function get_area_backup($itemid, $filepath, $filename) {
        global $CFG;

        if (!has_capability('moodle/backup:backupactivity', $this->context)) {
            return null;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($this->context->id, 'backup', 'activity', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'backup', 'activity', 0);
            } else {
                                return null;
            }
        }

        $downloadable = has_capability('moodle/backup:downloadfile', $this->context);
        $uploadable   = has_capability('moodle/restore:uploadfile', $this->context);

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('activitybackup', 'repository'), false, $downloadable, $uploadable, false);
    }

    
    public function get_visible_name() {
        return $this->cm->name.' ('.get_string('modulename', $this->cm->modname).')';
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_empty_area() {
        if ($child = $this->get_area_backup(0, '/', '.')) {
            if (!$child->is_empty_area()) {
                return false;
            }
        }
        if ($child = $this->get_area_intro(0, '/', '.')) {
            if (!$child->is_empty_area()) {
                return false;
            }
        }

        foreach ($this->areas as $area=>$desctiption) {
            if ($child = $this->get_file_info('mod_'.$this->modname, $area, null, null, null)) {
                if (!$child->is_empty_area()) {
                    return false;
                }
            }
        }

        return true;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        global $DB;
                $areas = array(
            array('mod_'.$this->modname, 'intro'),
            array('backup', 'activity')
        );
        foreach ($this->areas as $area => $desctiption) {
            $areas[] = array('mod_'.$this->modname, $area);
        }

        $params1 = array('contextid' => $this->context->id, 'emptyfilename' => '.');
        list($sql2, $params2) = $this->build_search_files_sql($extensions);
        $children = array();
        foreach ($areas as $area) {
            if (!$returnemptyfolders) {
                                $params1['component'] = $area[0];
                $params1['filearea'] = $area[1];
                if (!$DB->record_exists_sql('SELECT 1 from {files}
                        WHERE contextid = :contextid
                        AND filename <> :emptyfilename
                        AND component = :component
                        AND filearea = :filearea '.$sql2,
                        array_merge($params1, $params2))) {
                    continue;
                }
            }
            if ($child = $this->get_file_info($area[0], $area[1], null, null, null)) {
                if ($returnemptyfolders || $child->count_non_empty_children($extensions)) {
                    $children[] = $child;
                    if ($countonly !== false && count($children) >= $countonly) {
                        break;
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
        if ($this->nonemptychildren !== null) {
            return $this->nonemptychildren;
        }
        $this->nonemptychildren = $this->get_filtered_children($extensions);
        return $this->nonemptychildren;
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        if ($this->nonemptychildren !== null) {
            return count($this->nonemptychildren);
        }
        return $this->get_filtered_children($extensions, $limit);
    }

    
    public function get_parent() {
        $parent = $this->context->get_parent_context();
        return $this->browser->get_file_info($parent);
    }
}
