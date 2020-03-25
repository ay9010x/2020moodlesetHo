<?php



namespace mod_glossary\search;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/glossary/lib.php');


class entry extends \core_search\area\base_mod {

    
    protected $entriesdata = array();

    
    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        global $DB;

        $sql = "SELECT ge.*, g.course FROM {glossary_entries} ge
                  JOIN {glossary} g ON g.id = ge.glossaryid
                WHERE ge.timemodified >= ? ORDER BY ge.timemodified ASC";
        return $DB->get_recordset_sql($sql, array($modifiedfrom));
    }

    
    public function get_document($entry, $options = array()) {
        global $DB;

        $keywords = array();
        if ($aliases = $DB->get_records('glossary_alias', array('entryid' => $entry->id))) {
            foreach ($aliases as $alias) {
                $keywords[] = $alias->alias;
            }
        }

        try {
            $cm = $this->get_cm('glossary', $entry->glossaryid, $entry->course);
            $context = \context_module::instance($cm->id);
        } catch (\dml_missing_record_exception $ex) {
                        debugging('Error retrieving mod_glossary ' . $entry->id . ' document, not all required data is available: ' .
                $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $ex) {
                        debugging('Error retrieving mod_glossary' . $entry->id . ' document: ' . $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

                $doc = \core_search\document_factory::instance($entry->id, $this->componentname, $this->areaname);
        $doc->set('title', content_to_text($entry->concept, false));
        $doc->set('content', content_to_text($entry->definition, $entry->definitionformat));
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $entry->course);
        $doc->set('userid', $entry->userid);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', $entry->timemodified);

                if (isset($options['lastindexedtime']) && ($options['lastindexedtime'] < $entry->timecreated)) {
                        $doc->set_is_new(true);
        }

                if ($keywords) {
                        $doc->set('description1', implode(' ' , $keywords));
        }

        return $doc;
    }

    
    public function check_access($id) {
        global $USER;

        try {
            $entry = $this->get_entry($id);
            $cminfo = $this->get_cm('glossary', $entry->glossaryid, $entry->course);
        } catch (\dml_missing_record_exception $ex) {
            return \core_search\manager::ACCESS_DELETED;
        } catch (\dml_exception $ex) {
            return \core_search\manager::ACCESS_DENIED;
        }

        if (!glossary_can_view_entry($entry, $cminfo)) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    
    public function get_doc_url(\core_search\document $doc) {
        global $USER;

                $entry = $this->get_entry($doc->get('itemid'));
        $contextmodule = \context::instance_by_id($doc->get('contextid'));

        if ($entry->approved == false && $entry->userid != $USER->id) {
                        $docparams = array('id' => $contextmodule->instanceid, 'mode' => 'approval');
        } else {
            $docparams = array('id' => $contextmodule->instanceid, 'mode' => 'entry', 'hook' => $doc->get('itemid'));

        }
        return new \moodle_url('/mod/glossary/view.php', $docparams);
    }

    
    public function get_context_url(\core_search\document $doc) {
        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        return new \moodle_url('/mod/glossary/view.php', array('id' => $contextmodule->instanceid));
    }

    
    protected function get_entry($entryid) {
        global $DB;

        if (empty($this->entriesdata[$entryid])) {
            $this->entriesdata[$entryid] = $DB->get_record_sql("SELECT ge.*, g.course, g.defaultapproval FROM {glossary_entries} ge
                                                                  JOIN {glossary} g ON g.id = ge.glossaryid
                                                                WHERE ge.id = ?", array('id' => $entryid), MUST_EXIST);
        }
        return $this->entriesdata[$entryid];
    }
}
