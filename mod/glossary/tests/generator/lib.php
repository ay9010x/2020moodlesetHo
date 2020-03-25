<?php



defined('MOODLE_INTERNAL') || die();


class mod_glossary_generator extends testing_module_generator {

    
    protected $entrycount = 0;

    
    protected $categorycount = 0;

    
    public function reset() {
        $this->entrycount = 0;
        $this->categorycount = 0;
        parent::reset();
    }

    public function create_instance($record = null, array $options = null) {
        global $CFG;

                $record = (array)$record + array(
            'globalglossary' => 0,
            'mainglossary' => 0,
            'defaultapproval' => $CFG->glossary_defaultapproval,
            'allowduplicatedentries' => $CFG->glossary_dupentries,
            'allowcomments' => $CFG->glossary_allowcomments,
            'usedynalink' => $CFG->glossary_linkbydefault,
            'displayformat' => 'dictionary',
            'approvaldisplayformat' => 'default',
            'entbypage' => !empty($CFG->glossary_entbypage) ? $CFG->glossary_entbypage : 10,
            'showalphabet' => 1,
            'showall' => 1,
            'showspecial' => 1,
            'allowprintview' => 1,
            'rsstype' => 0,
            'rssarticles' => 0,
            'grade' => 100,
            'assessed' => 0,
        );

        return parent::create_instance($record, (array)$options);
    }

    public function create_category($glossary, $record = array(), $entries = array()) {
        global $CFG, $DB;
        $this->categorycount++;
        $record = (array)$record + array(
            'name' => 'Glossary category '.$this->categorycount,
            'usedynalink' => $CFG->glossary_linkbydefault,
        );
        $record['glossaryid'] = $glossary->id;

        $id = $DB->insert_record('glossary_categories', $record);

        if ($entries) {
            foreach ($entries as $entry) {
                $ce = new stdClass();
                $ce->categoryid = $id;
                $ce->entryid = $entry->id;
                $DB->insert_record('glossary_entries_categories', $ce);
            }
        }

        return $DB->get_record('glossary_categories', array('id' => $id), '*', MUST_EXIST);
    }

    public function create_content($glossary, $record = array(), $aliases = array()) {
        global $DB, $USER, $CFG;
        $this->entrycount++;
        $now = time();
        $record = (array)$record + array(
            'glossaryid' => $glossary->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'userid' => $USER->id,
            'concept' => 'Glossary entry '.$this->entrycount,
            'definition' => 'Definition of glossary entry '.$this->entrycount,
            'definitionformat' => FORMAT_MOODLE,
            'definitiontrust' => 0,
            'usedynalink' => $CFG->glossary_linkentries,
            'casesensitive' => $CFG->glossary_casesensitive,
            'fullmatch' => $CFG->glossary_fullmatch
        );
        if (!isset($record['teacherentry']) || !isset($record['approved'])) {
            $context = context_module::instance($glossary->cmid);
            if (!isset($record['teacherentry'])) {
                $record['teacherentry'] = has_capability('mod/glossary:manageentries', $context, $record['userid']);
            }
            if (!isset($record['approved'])) {
                $defaultapproval = $glossary->defaultapproval;
                $record['approved'] = ($defaultapproval || has_capability('mod/glossary:approve', $context));
            }
        }

        $id = $DB->insert_record('glossary_entries', $record);

        if ($aliases) {
            foreach ($aliases as $alias) {
                $ar = new stdClass();
                $ar->entryid = $id;
                $ar->alias = $alias;
                $DB->insert_record('glossary_alias', $ar);
            }
        }

        return $DB->get_record('glossary_entries', array('id' => $id), '*', MUST_EXIST);
    }
}
