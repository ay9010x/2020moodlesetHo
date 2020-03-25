<?php



namespace mod_wiki\search;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/wiki/locallib.php');


class collaborative_page extends \core_search\area\base_mod {
    
    protected $wikiscache = array();

    
    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        global $DB;

        $sql = 'SELECT p.*, w.id AS wikiid, w.course AS courseid
                  FROM {wiki_pages} p
                  JOIN {wiki_subwikis} s ON s.id = p.subwikiid
                  JOIN {wiki} w ON w.id = s.wikiid
                 WHERE p.timemodified >= ?
                   AND w.wikimode = ?
              ORDER BY p.timemodified ASC';
        return $DB->get_recordset_sql($sql, array($modifiedfrom, 'collaborative'));
    }

    
    public function get_document($record, $options = array()) {
        try {
            $cm = $this->get_cm('wiki', $record->wikiid, $record->courseid);
            $context = \context_module::instance($cm->id);
        } catch (\dml_missing_record_exception $ex) {
                        debugging('Error retrieving ' . $this->areaid . ' ' . $record->id . ' document, not all required data is available: ' .
                $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $ex) {
                        debugging('Error retrieving ' . $this->areaid . ' ' . $record->id . ' document: ' . $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

                $page = clone $record;
        unset($page->courseid);
        unset($page->wikiid);

                        if ($page->timerendered + WIKI_REFRESH_CACHE_TIME < time()) {
            $content = wiki_refresh_cachedcontent($page);
            $page = $content['page'];
        }
                $content = content_to_text($page->cachedcontent, FORMAT_MOODLE);

                $doc = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);
        $doc->set('title', content_to_text($record->title, false));
        $doc->set('content', $content);
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $record->courseid);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', $record->timemodified);

                if (isset($options['lastindexedtime']) && ($options['lastindexedtime'] < $record->timecreated)) {
                        $doc->set_is_new(true);
        }

        return $doc;
    }

    
    public function check_access($id) {
        global $DB;

        try {
            $page = $DB->get_record('wiki_pages', array('id' => $id), '*', MUST_EXIST);
            if (!isset($this->wikiscache[$page->subwikiid])) {
                $sql = 'SELECT w.*
                          FROM {wiki_subwikis} s
                          JOIN {wiki} w ON w.id = s.wikiid
                         WHERE s.id = ?';
                $this->wikiscache[$page->subwikiid] = $DB->get_record_sql($sql, array('id' => $page->subwikiid), MUST_EXIST);
            }
            $wiki = $this->wikiscache[$page->subwikiid];
            $cminfo = $this->get_cm('wiki', $wiki->id, $wiki->course);
        } catch (\dml_missing_record_exception $ex) {
            return \core_search\manager::ACCESS_DELETED;
        } catch (\dml_exception $ex) {
            return \core_search\manager::ACCESS_DENIED;
        }

                if ($cminfo->uservisible === false) {
            return \core_search\manager::ACCESS_DENIED;
        }

        $context = \context_module::instance($cminfo->id);

        if (!has_capability('mod/wiki:viewpage', $context)) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    
    public function get_doc_url(\core_search\document $doc) {
        $params = array('pageid' => $doc->get('itemid'));
        return new \moodle_url('/mod/wiki/view.php', $params);
    }

    
    public function get_context_url(\core_search\document $doc) {
        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        return new \moodle_url('/mod/wiki/view.php', array('id' => $contextmodule->instanceid));
    }
}
