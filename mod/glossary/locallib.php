<?php




require_once($CFG->libdir . '/portfolio/caller.php');
require_once($CFG->libdir . '/filelib.php');


class glossary_full_portfolio_caller extends portfolio_module_caller_base {

    private $glossary;
    private $exportdata;
    private $keyedfiles = array(); 
    
    public static function expected_callbackargs() {
        return array(
            'id' => true,
        );
    }

    
    public function load_data() {
        global $DB;
        if (!$this->cm = get_coursemodule_from_id('glossary', $this->id)) {
            throw new portfolio_caller_exception('invalidid', 'glossary');
        }
        if (!$this->glossary = $DB->get_record('glossary', array('id' => $this->cm->instance))) {
            throw new portfolio_caller_exception('invalidid', 'glossary');
        }
        $entries = $DB->get_records('glossary_entries', array('glossaryid' => $this->glossary->id));
        list($where, $params) = $DB->get_in_or_equal(array_keys($entries));

        $aliases = $DB->get_records_select('glossary_alias', 'entryid ' . $where, $params);
        $categoryentries = $DB->get_records_sql('SELECT ec.entryid, c.name FROM {glossary_entries_categories} ec
            JOIN {glossary_categories} c
            ON c.id = ec.categoryid
            WHERE ec.entryid ' . $where, $params);

        $this->exportdata = array('entries' => $entries, 'aliases' => $aliases, 'categoryentries' => $categoryentries);
        $fs = get_file_storage();
        $context = context_module::instance($this->cm->id);
        $this->multifiles = array();
        foreach (array_keys($entries) as $entry) {
            $this->keyedfiles[$entry] = array_merge(
                $fs->get_area_files($context->id, 'mod_glossary', 'attachment', $entry, "timemodified", false),
                $fs->get_area_files($context->id, 'mod_glossary', 'entry', $entry, "timemodified", false)
            );
            $this->multifiles = array_merge($this->multifiles, $this->keyedfiles[$entry]);
        }
    }

    
    public function expected_time() {
        $filetime = portfolio_expected_time_file($this->multifiles);
        $dbtime   = portfolio_expected_time_db(count($this->exportdata['entries']));
        return ($filetime > $dbtime) ? $filetime : $dbtime;
    }

    
    public function get_sha1() {
        $file = '';
        if ($this->multifiles) {
            $file = $this->get_sha1_file();
        }
        return sha1(serialize($this->exportdata) . $file);
    }

    
    public function prepare_package() {
        $entries = $this->exportdata['entries'];
        $aliases = array();
        $categories = array();
        if (is_array($this->exportdata['aliases'])) {
            foreach ($this->exportdata['aliases'] as $alias) {
                if (!array_key_exists($alias->entryid, $aliases)) {
                    $aliases[$alias->entryid] = array();
                }
                $aliases[$alias->entryid][] = $alias->alias;
            }
        }
        if (is_array($this->exportdata['categoryentries'])) {
            foreach ($this->exportdata['categoryentries'] as $cat) {
                if (!array_key_exists($cat->entryid, $categories)) {
                    $categories[$cat->entryid] = array();
                }
                $categories[$cat->entryid][] = $cat->name;
            }
        }
        if ($this->get('exporter')->get('formatclass') == PORTFOLIO_FORMAT_SPREADSHEET) {
            $csv = glossary_generate_export_csv($entries, $aliases, $categories);
            $this->exporter->write_new_file($csv, clean_filename($this->cm->name) . '.csv', false);
            return;
        } else if ($this->get('exporter')->get('formatclass') == PORTFOLIO_FORMAT_LEAP2A) {
            $ids = array();             global $USER, $DB;
            $writer = $this->get('exporter')->get('format')->leap2a_writer($USER);
            $format = $this->exporter->get('format');
            $filename = $this->get('exporter')->get('format')->manifest_name();
            foreach ($entries as $e) {
                $content = glossary_entry_portfolio_caller::entry_content(
                    $this->course,
                    $this->cm,
                    $this->glossary,
                    $e,
                    (array_key_exists($e->id, $aliases) ? $aliases[$e->id] : array()),
                    $format
                );
                $entry = new portfolio_format_leap2a_entry('glossaryentry' . $e->id, $e->concept, 'entry', $content);
                $entry->author    = $DB->get_record('user', array('id' => $e->userid), 'id,firstname,lastname,email');
                $entry->published = $e->timecreated;
                $entry->updated   = $e->timemodified;
                if (!empty($this->keyedfiles[$e->id])) {
                    $writer->link_files($entry, $this->keyedfiles[$e->id], 'glossaryentry' . $e->id . 'file');
                    foreach ($this->keyedfiles[$e->id] as $file) {
                        $this->exporter->copy_existing_file($file);
                    }
                }
                if (!empty($categories[$e->id])) {
                    foreach ($categories[$e->id] as $cat) {
                                                                                                $entry->add_category($cat);
                    }
                }
                $writer->add_entry($entry);
                $ids[] = $entry->id;
            }
            $selection = new portfolio_format_leap2a_entry('wholeglossary' . $this->glossary->id, get_string('modulename', 'glossary'), 'selection');
            $writer->add_entry($selection);
            $writer->make_selection($selection, $ids, 'Grouping');
            $content = $writer->to_xml();
        }
        $this->exporter->write_new_file($content, $filename, true);
    }

    
    public function check_permissions() {
        return has_capability('mod/glossary:export', context_module::instance($this->cm->id));
    }

    
    public static function display_name() {
        return get_string('modulename', 'glossary');
    }

    
    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_SPREADSHEET, PORTFOLIO_FORMAT_LEAP2A);
    }
}


class glossary_entry_portfolio_caller extends portfolio_module_caller_base {

    private $glossary;
    private $entry;
    protected $entryid;
    
    public static function expected_callbackargs() {
        return array(
            'entryid' => true,
            'id'      => true,
        );
    }

    
    public function load_data() {
        global $DB;
        if (!$this->cm = get_coursemodule_from_id('glossary', $this->id)) {
            throw new portfolio_caller_exception('invalidid', 'glossary');
        }
        if (!$this->glossary = $DB->get_record('glossary', array('id' => $this->cm->instance))) {
            throw new portfolio_caller_exception('invalidid', 'glossary');
        }
        if ($this->entryid) {
            if (!$this->entry = $DB->get_record('glossary_entries', array('id' => $this->entryid))) {
                throw new portfolio_caller_exception('noentry', 'glossary');
            }
                        $this->entry->approved = true;
        }
        $this->categories = $DB->get_records_sql('SELECT ec.entryid, c.name FROM {glossary_entries_categories} ec
            JOIN {glossary_categories} c
            ON c.id = ec.categoryid
            WHERE ec.entryid = ?', array($this->entryid));
        $context = context_module::instance($this->cm->id);
        if ($this->entry->sourceglossaryid == $this->cm->instance) {
            if ($maincm = get_coursemodule_from_instance('glossary', $this->entry->glossaryid)) {
                $context = context_module::instance($maincm->id);
            }
        }
        $this->aliases = $DB->get_record('glossary_alias', array('entryid'=>$this->entryid));
        $fs = get_file_storage();
        $this->multifiles = array_merge(
            $fs->get_area_files($context->id, 'mod_glossary', 'attachment', $this->entry->id, "timemodified", false),
            $fs->get_area_files($context->id, 'mod_glossary', 'entry', $this->entry->id, "timemodified", false)
        );

        if (!empty($this->multifiles)) {
            $this->add_format(PORTFOLIO_FORMAT_RICHHTML);
        } else {
            $this->add_format(PORTFOLIO_FORMAT_PLAINHTML);
        }
    }

    
    public function expected_time() {
        return PORTFOLIO_TIME_LOW;
    }

    
    public function check_permissions() {
        $context = context_module::instance($this->cm->id);
        return has_capability('mod/glossary:exportentry', $context)
            || ($this->entry->userid == $this->user->id && has_capability('mod/glossary:exportownentry', $context));
    }

    
    public static function display_name() {
        return get_string('modulename', 'glossary');
    }

    
    public function prepare_package() {
        global $DB;

        $format = $this->exporter->get('format');
        $content = self::entry_content($this->course, $this->cm, $this->glossary, $this->entry, $this->aliases, $format);

        if ($this->exporter->get('formatclass') === PORTFOLIO_FORMAT_PLAINHTML) {
            $filename = clean_filename($this->entry->concept) . '.html';
            $this->exporter->write_new_file($content, $filename);

        } else if ($this->exporter->get('formatclass') === PORTFOLIO_FORMAT_RICHHTML) {
            if ($this->multifiles) {
                foreach ($this->multifiles as $file) {
                    $this->exporter->copy_existing_file($file);
                }
            }
            $filename = clean_filename($this->entry->concept) . '.html';
            $this->exporter->write_new_file($content, $filename);

        } else if ($this->exporter->get('formatclass') === PORTFOLIO_FORMAT_LEAP2A) {
            $writer = $this->get('exporter')->get('format')->leap2a_writer();
            $entry = new portfolio_format_leap2a_entry('glossaryentry' . $this->entry->id, $this->entry->concept, 'entry', $content);
            $entry->author = $DB->get_record('user', array('id' => $this->entry->userid), 'id,firstname,lastname,email');
            $entry->published = $this->entry->timecreated;
            $entry->updated = $this->entry->timemodified;
            if ($this->multifiles) {
                $writer->link_files($entry, $this->multifiles);
                foreach ($this->multifiles as $file) {
                    $this->exporter->copy_existing_file($file);
                }
            }
            if ($this->categories) {
                foreach ($this->categories as $cat) {
                                                                                $entry->add_category($cat->name);
                }
            }
            $writer->add_entry($entry);
            $content = $writer->to_xml();
            $filename = $this->get('exporter')->get('format')->manifest_name();
            $this->exporter->write_new_file($content, $filename);

        } else {
            throw new portfolio_caller_exception('unexpected_format_class', 'glossary');
        }
    }

    
    public function get_sha1() {
        if ($this->multifiles) {
            return sha1(serialize($this->entry) . $this->get_sha1_file());
        }
        return sha1(serialize($this->entry));
    }

    
    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_RICHHTML, PORTFOLIO_FORMAT_PLAINHTML, PORTFOLIO_FORMAT_LEAP2A);
    }

    
    public static function entry_content($course, $cm, $glossary, $entry, $aliases, $format) {
        global $OUTPUT, $DB;
        $entry = clone $entry;
        $context = context_module::instance($cm->id);
        $options = portfolio_format_text_options();
        $options->trusted = $entry->definitiontrust;
        $options->context = $context;

        $output = '<table class="glossarypost dictionary" cellspacing="0">' . "\n";
        $output .= '<tr valign="top">' . "\n";
        $output .= '<td class="entry">' . "\n";

        $output .= '<div class="concept">';
        $output .= format_text($OUTPUT->heading($entry->concept, 3), FORMAT_MOODLE, $options);
        $output .= '</div> ' . "\n";

        $entry->definition = format_text($entry->definition, $entry->definitionformat, $options);
        $output .= portfolio_rewrite_pluginfile_urls($entry->definition, $context->id, 'mod_glossary', 'entry', $entry->id, $format);

        if (isset($entry->footer)) {
            $output .= $entry->footer;
        }

        $output .= '</td></tr>' . "\n";

        if (!empty($aliases)) {
            $aliases = explode(',', $aliases->alias);
            $output .= '<tr valign="top"><td class="entrylowersection">';
            $key = (count($aliases) == 1) ? 'alias' : 'aliases';
            $output .= get_string($key, 'glossary') . ': ';
            foreach ($aliases as $alias) {
                $output .= s($alias) . ',';
            }
            $output = substr($output, 0, -1);
            $output .= '</td></tr>' . "\n";
        }

        if ($entry->sourceglossaryid == $cm->instance) {
            if (!$maincm = get_coursemodule_from_instance('glossary', $entry->glossaryid)) {
                return '';
            }
            $filecontext = context_module::instance($maincm->id);

        } else {
            $filecontext = $context;
        }
        $fs = get_file_storage();
        if ($files = $fs->get_area_files($filecontext->id, 'mod_glossary', 'attachment', $entry->id, "timemodified", false)) {
            $output .= '<table border="0" width="100%"><tr><td>' . "\n";

            foreach ($files as $file) {
                $output .= $format->file_output($file);
            }
            $output .= '</td></tr></table>' . "\n";
        }

        $output .= '</table>' . "\n";

        return $output;
    }
}



class glossary_file_info_container extends file_info {
    
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
        $this->component = 'mod_glossary';
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
        $sql = 'SELECT DISTINCT f.itemid, ge.concept
                  FROM {files} f
                  JOIN {modules} m ON (m.name = :modulename AND m.visible = 1)
                  JOIN {course_modules} cm ON (cm.module = m.id AND cm.id = :instanceid)
                  JOIN {glossary} g ON g.id = cm.instance
                  JOIN {glossary_entries} ge ON (ge.glossaryid = g.id AND ge.id = f.itemid)
                 WHERE f.contextid = :contextid
                  AND f.component = :component
                  AND f.filearea = :filearea';
        $params = array(
            'modulename' => 'glossary',
            'instanceid' => $this->context->instanceid,
            'contextid' => $this->context->id,
            'component' => $this->component,
            'filearea' => $this->filearea);
        if (!$returnemptyfolders) {
            $sql .= ' AND f.filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }
        list($sql2, $params2) = $this->build_search_files_sql($extensions, 'f');
        $sql .= ' '.$sql2;
        $params = array_merge($params, $params2);
        if ($countonly !== false) {
            $sql .= ' ORDER BY ge.concept, f.itemid';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = array();
        foreach ($rs as $record) {
            if ($child = $this->browser->get_file_info($this->context, 'mod_glossary', $this->filearea, $record->itemid)) {
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
