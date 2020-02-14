<?php



namespace booktool_exportimscp\event;
defined('MOODLE_INTERNAL') || die();


class book_exported extends \core\event\base {
    
    public static function create_from_book(\stdClass $book, \context_module $context) {
        $data = array(
            'context' => $context,
            'objectid' => $book->id
        );
        
        $event = self::create($data);
        $event->add_record_snapshot('book', $book);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has exported the book with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'book', 'exportimscp', 'tool/exportimscp/index.php?id=' . $this->contextinstanceid,
            $this->objectid, $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventbookexported', 'booktool_exportimscp');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/book/tool/exportimscp/index.php', array('id' => $this->contextinstanceid));
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'book';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'book', 'restore' => 'book');
    }
}
