<?php



defined('MOODLE_INTERNAL') || die();


class mod_lesson_file_info extends file_info {

    
    protected $course;
    
    protected $cm;
    
    protected $areas;
    
    protected $filearea;

    
    public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
        parent::__construct($browser, $context);
        $this->course   = $course;
        $this->cm       = $cm;
        $this->areas    = $areas;
        $this->filearea = $filearea;
    }

    
    public function get_params() {
        return array('contextid' => $this->context->id,
                     'component' => 'mod_lesson',
                     'filearea'  => $this->filearea,
                     'itemid'    => null,
                     'filepath'  => null,
                     'filename'  => null);
    }

    
    public function get_visible_name() {
        return $this->areas[$this->filearea];
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
        global $DB;

        $params = array(
            'contextid' => $this->context->id,
            'component' => 'mod_lesson',
            'filearea' => $this->filearea
        );
        $sql = 'SELECT DISTINCT itemid
                  FROM {files}
                 WHERE contextid = :contextid
                   AND component = :component
                   AND filearea = :filearea';

        if (!$returnemptyfolders) {
            $sql .= ' AND filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }

        list($sql2, $params2) = $this->build_search_files_sql($extensions);
        $sql .= ' ' . $sql2;
        $params = array_merge($params, $params2);

        if ($countonly !== false) {
            $sql .= ' ORDER BY itemid DESC';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = array();
        foreach ($rs as $record) {
            if (($child = $this->browser->get_file_info($this->context, 'mod_lesson', $this->filearea, $record->itemid))
                    && ($returnemptyfolders || $child->count_non_empty_children($extensions))) {
                $children[] = $child;
            }
            if ($countonly !== false && count($children) >= $countonly) {
                break;
            }
        }
        $rs->close();
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
        return $this->browser->get_file_info($this->context);
    }
}
