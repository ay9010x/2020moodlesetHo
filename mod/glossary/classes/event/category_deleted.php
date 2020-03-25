<?php



namespace mod_glossary\event;
defined('MOODLE_INTERNAL') || die();


class category_deleted extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'glossary_categories';
    }

    
    public static function get_name() {
        return get_string('eventcategorydeleted', 'mod_glossary');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the category with id '$this->objectid' in the " .
            "glossary activity with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url("/mod/glossary/editcategories.php",
                array('id' => $this->contextinstanceid));
    }

    
    public function get_legacy_logdata() {
        return array($this->courseid, 'glossary', 'delete category',
            "editcategories.php?id={$this->contextinstanceid}",
            $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
                if (!$this->contextlevel === CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'glossary_categories', 'restore' => 'glossary_category');
    }
}

