<?php



namespace mod_page\search;

defined('MOODLE_INTERNAL') || die();


class activity extends \core_search\area\base_activity {


    
    public function get_document($record, $options = array()) {

        try {
            $cm = $this->get_cm($this->get_module_name(), $record->id, $record->course);
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
        $doc->set('title', content_to_text($record->name, false));
        $doc->set('content', content_to_text($record->content, $record->contentformat));
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $record->course);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', $record->timemodified);
        $doc->set('description1', content_to_text($record->intro, $record->introformat));

        return $doc;
    }
}
