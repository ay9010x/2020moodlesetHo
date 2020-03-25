<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/data/lib.php');
require_once($CFG->libdir . '/portfolio/caller.php');
require_once($CFG->libdir . '/filelib.php');


class data_portfolio_caller extends portfolio_module_caller_base {

    
    protected $recordid;

    
    private $data;

    
    private $fields;
    private $fieldtypes;

    
    private $records;

    
    private $minecount;

    
    public static function expected_callbackargs() {
        return array(
            'id'       => true,
            'recordid' => false,
        );
    }

    
    public function __construct($callbackargs) {
        parent::__construct($callbackargs);
                $this->selectedfields = array();
        foreach ($callbackargs as $key => $value) {
            if (strpos($key, 'field_') === 0) {
                $this->selectedfields[] = substr($key, 6);
            }
        }
    }

    
    public function load_data() {
        global $DB, $USER;
        if (!$this->cm = get_coursemodule_from_id('data', $this->id)) {
            throw new portfolio_caller_exception('invalidid', 'data');
        }
        if (!$this->data = $DB->get_record('data', array('id' => $this->cm->instance))) {
            throw new portfolio_caller_exception('invalidid', 'data');
        }
        $fieldrecords = $DB->get_records('data_fields', array('dataid' => $this->cm->instance), 'id');
                $this->fields = array();
        foreach ($fieldrecords as $fieldrecord) {
            $tmp = data_get_field($fieldrecord, $this->data);
            $this->fields[] = $tmp;
            $this->fieldtypes[]  = $tmp->type;
        }

        $this->records = array();
        if ($this->recordid) {
            $tmp = $DB->get_record('data_records', array('id' => $this->recordid));
            $tmp->content = $DB->get_records('data_content', array('recordid' => $this->recordid));
            $this->records[] = $tmp;
        } else {
            $where = array('dataid' => $this->data->id);
            if (!has_capability('mod/data:exportallentries', context_module::instance($this->cm->id))) {
                $where['userid'] = $USER->id;             }
            $tmp = $DB->get_records('data_records', $where);
            foreach ($tmp as $t) {
                $t->content = $DB->get_records('data_content', array('recordid' => $t->id));
                $this->records[] = $t;
            }
            $this->minecount = $DB->count_records('data_records', array('dataid' => $this->data->id, 'userid' => $USER->id));
        }

        if ($this->recordid) {
            list($formats, $files) = self::formats($this->fields, $this->records[0]);
            $this->set_file_and_format_data($files);
        }
    }

    
    public function expected_time() {
        if ($this->recordid) {
            return $this->expected_time_file();
        } else {
            return portfolio_expected_time_db(count($this->records));
        }
    }

    
    public function get_sha1() {
                        if ($this->exporter->get('format') instanceof portfolio_format_file && $this->singlefile) {
            return $this->get_sha1_file();
        }
                $str = '';
        foreach ($this->records as $record) {
            foreach ($record as $data) {
                if (is_array($data) || is_object($data)) {
                    $keys = array_keys($data);
                    $testkey = array_pop($keys);
                    if (is_array($data[$testkey]) || is_object($data[$testkey])) {
                        foreach ($data as $d) {
                            $str .= implode(',', (array)$d);
                        }
                    } else {
                        $str .= implode(',', (array)$data);
                    }
                } else {
                    $str .= $data;
                }
            }
        }
        return sha1($str . ',' . $this->exporter->get('formatclass'));
    }

    
    public function prepare_package() {
        global $DB;
        $leapwriter = null;
        $content = '';
        $filename = '';
        $uid = $this->exporter->get('user')->id;
        $users = array();         $onlymine = $this->get_export_config('mineonly');
        if ($this->exporter->get('formatclass') == PORTFOLIO_FORMAT_LEAP2A) {
            $leapwriter = $this->exporter->get('format')->leap2a_writer();
            $ids = array();
        }

        if ($this->exporter->get('format') instanceof portfolio_format_file && $this->singlefile) {
            return $this->get('exporter')->copy_existing_file($this->singlefile);
        }
        foreach ($this->records  as $key => $record) {
            if ($onlymine && $record->userid != $uid) {
                unset($this->records[$key]);                 continue;
            }
            list($tmpcontent, $files)  = $this->exportentry($record);
            $content .= $tmpcontent;
            if ($leapwriter) {
                $entry = new portfolio_format_leap2a_entry('dataentry' . $record->id, $this->data->name, 'resource', $tmpcontent);
                $entry->published = $record->timecreated;
                $entry->updated = $record->timemodified;
                if ($record->userid != $uid) {
                    if (!array_key_exists($record->userid, $users)) {
                        $users[$record->userid] = $DB->get_record('user', array('id' => $record->userid), 'id,firstname,lastname');
                    }
                    $entry->author = $users[$record->userid];
                }
                $ids[] = $entry->id;
                $leapwriter->link_files($entry, $files, 'dataentry' . $record->id . 'file');
                $leapwriter->add_entry($entry);
            }
        }
        if ($leapwriter) {
            if (count($this->records) > 1) {                 $selection = new portfolio_format_leap2a_entry('datadb' . $this->data->id,
                    get_string('entries', 'data') . ': ' . $this->data->name, 'selection');
                $leapwriter->add_entry($selection);
                $leapwriter->make_selection($selection, $ids, 'Grouping');
            }
            $filename = $this->exporter->get('format')->manifest_name();
            $content = $leapwriter->to_xml();
        } else {
            if (count($this->records) == 1) {
                $filename = clean_filename($this->cm->name . '-entry.html');
            } else {
                $filename = clean_filename($this->cm->name . '-full.html');
            }
        }
        return $this->exporter->write_new_file(
            $content,
            $filename,
            ($this->exporter->get('format') instanceof PORTFOLIO_FORMAT_RICH)         );
    }

    
    public function check_permissions() {
        if ($this->recordid) {
            if (data_isowner($this->recordid)) {
                return has_capability('mod/data:exportownentry', context_module::instance($this->cm->id));
            }
            return has_capability('mod/data:exportentry', context_module::instance($this->cm->id));
        }
        if ($this->has_export_config() && !$this->get_export_config('mineonly')) {
            return has_capability('mod/data:exportallentries', context_module::instance($this->cm->id));
        }
        return has_capability('mod/data:exportownentry', context_module::instance($this->cm->id));
    }

    
    public static function display_name() {
        return get_string('modulename', 'data');
    }

    
    public function __wakeup() {
        global $CFG;
        if (empty($CFG)) {
            return true;         }
        foreach ($this->fieldtypes as $key => $field) {
            require_once($CFG->dirroot . '/mod/data/field/' . $field .'/field.class.php');
            $this->fields[$key] = unserialize(serialize($this->fields[$key]));
        }
    }

    
    private function exportentry($record) {
            $patterns = array();
        $replacement = array();

        $files = array();
            $format = $this->get('exporter')->get('format');
        foreach ($this->fields as $field) {
            $patterns[]='[['.$field->field->name.']]';
            if (is_callable(array($field, 'get_file'))) {
                if (!$file = $field->get_file($record->id)) {
                    $replacement[] = '';
                    continue;                 }
                $replacement[] = $format->file_output($file);
                $this->get('exporter')->copy_existing_file($file);
                $files[] = $file;
            } else {
                $replacement[] = $field->display_browse_field($record->id, 'singletemplate');
            }
        }

            $patterns[]='##edit##';
        $patterns[]='##delete##';
        $patterns[]='##export##';
        $patterns[]='##more##';
        $patterns[]='##moreurl##';
        $patterns[]='##user##';
        $patterns[]='##approve##';
        $patterns[]='##disapprove##';
        $patterns[]='##comments##';
        $patterns[] = '##timeadded##';
        $patterns[] = '##timemodified##';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = '';
        $replacement[] = userdate($record->timecreated);
        $replacement[] = userdate($record->timemodified);

                return array(str_ireplace($patterns, $replacement, $this->data->singletemplate), $files);
    }

    
    public static function formats($fields, $record) {
        $formats = array(PORTFOLIO_FORMAT_PLAINHTML);
        $includedfiles = array();
        foreach ($fields as $singlefield) {
            if (is_callable(array($singlefield, 'get_file'))) {
                if ($file = $singlefield->get_file($record->id)) {
                    $includedfiles[] = $file;
                }
            }
        }
        if (count($includedfiles) == 1 && count($fields) == 1) {
            $formats = array(portfolio_format_from_mimetype($includedfiles[0]->get_mimetype()));
        } else if (count($includedfiles) > 0) {
            $formats = array(PORTFOLIO_FORMAT_RICHHTML);
        }
        return array($formats, $includedfiles);
    }

    public static function has_files($data) {
        global $DB;
        $fieldrecords = $DB->get_records('data_fields', array('dataid' => $data->id), 'id');
                foreach ($fieldrecords as $fieldrecord) {
            $field = data_get_field($fieldrecord, $data);
            if (is_callable(array($field, 'get_file'))) {
                return true;
            }
        }
        return false;
    }

    
    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_RICHHTML, PORTFOLIO_FORMAT_PLAINHTML, PORTFOLIO_FORMAT_LEAP2A);
    }

    public function has_export_config() {
                                return (empty($this->recordid)             && $this->minecount > 0                && $this->minecount != count($this->records)             && has_capability('mod/data:exportallentries', context_module::instance($this->cm->id)));     }

    public function export_config_form(&$mform, $instance) {
        if (!$this->has_export_config()) {
            return;
        }
        $mform->addElement('selectyesno', 'mineonly', get_string('exportownentries', 'data', (object)array('mine' => $this->minecount, 'all' => count($this->records))));
        $mform->setDefault('mineonly', 1);
    }

    public function get_allowed_export_config() {
        return array('mineonly');
    }
}



class data_file_info_container extends file_info {
    
    protected $browser;
    
    protected $course;
    
    protected $cm;
    
    protected $component;
    
    protected $context;
    
    protected $areas;
    
    protected $filearea;

    
    public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
        parent::__construct($browser, $context);
        $this->browser = $browser;
        $this->course = $course;
        $this->cm = $cm;
        $this->component = 'mod_data';
        $this->context = $context;
        $this->areas = $areas;
        $this->filearea = $filearea;
    }

    
    public function get_params() {
        return array(
            'contextid' => $this->context->id,
            'component' => $this->component,
            'filearea' => $this->filearea,
            'itemid' => null,
            'filepath' => null,
            'filename' => null,
        );
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_visible_name() {
        return $this->areas[$this->filearea];
    }

    
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        global $DB;
        $params = array('contextid' => $this->context->id,
            'component' => $this->component,
            'filearea' => $this->filearea);
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
        $sql .= ' '.$sql2;
        $params = array_merge($params, $params2);
        if ($countonly === false) {
            $sql .= ' ORDER BY itemid DESC';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = array();
        foreach ($rs as $record) {
            if ($child = $this->browser->get_file_info($this->context, 'mod_data', $this->filearea, $record->itemid)) {
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
