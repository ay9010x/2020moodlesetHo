<?php



namespace mod_book\search;

defined('MOODLE_INTERNAL') || die();


class chapter extends \core_search\area\base_mod {
    
    protected $bookscache = array();

    
    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        global $DB;

        $sql = 'SELECT c.*, b.id AS bookid, b.course AS courseid
                  FROM {book_chapters} c
                  JOIN {book} b ON b.id = c.bookid
                 WHERE c.timemodified >= ? ORDER BY c.timemodified ASC';
        return $DB->get_recordset_sql($sql, array($modifiedfrom));
    }

    
    public function get_document($record, $options = array()) {
        try {
            $cm = $this->get_cm('book', $record->bookid, $record->courseid);
            $context = \context_module::instance($cm->id);
        } catch (\dml_missing_record_exception $ex) {
                        debugging('Error retrieving ' . $this->areaid . ' ' . $record->id . ' document, not all required data is available: ' .
                $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $ex) {
                        debugging('Error retrieving ' . $this->areaid . ' ' . $record->id . ' document: ' . $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

                $doc = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);
        $doc->set('title', content_to_text($record->title, false));
        $doc->set('content', content_to_text($record->content, $record->contentformat));
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
            $chapter = $DB->get_record('book_chapters', array('id' => $id), '*', MUST_EXIST);
            if (!isset($this->bookscache[$chapter->bookid])) {
                $this->bookscache[$chapter->bookid] = $DB->get_record('book', array('id' => $chapter->bookid), '*', MUST_EXIST);
            }
            $book = $this->bookscache[$chapter->bookid];
            $cminfo = $this->get_cm('book', $chapter->bookid, $book->course);
        } catch (\dml_missing_record_exception $ex) {
            return \core_search\manager::ACCESS_DELETED;
        } catch (\dml_exception $ex) {
            return \core_search\manager::ACCESS_DENIED;
        }

                if ($cminfo->uservisible === false) {
            return \core_search\manager::ACCESS_DENIED;
        }

        $context = \context_module::instance($cminfo->id);

        if (!has_capability('mod/book:read', $context)) {
            return \core_search\manager::ACCESS_DENIED;
        }

                if ($chapter->hidden && !has_capability('mod/book:viewhiddenchapters', $context)) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    
    public function get_doc_url(\core_search\document $doc) {
        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        $params = array('id' => $contextmodule->instanceid, 'chapterid' => $doc->get('itemid'));
        return new \moodle_url('/mod/book/view.php', $params);
    }

    
    public function get_context_url(\core_search\document $doc) {
        $contextmodule = \context::instance_by_id($doc->get('contextid'));
        return new \moodle_url('/mod/book/view.php', array('id' => $contextmodule->instanceid));
    }
}
