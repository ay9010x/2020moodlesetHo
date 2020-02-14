<?php



namespace booktool_print\event;
defined('MOODLE_INTERNAL') || die();


class book_printed extends \core\event\base {
    
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
        return "The user with id '$this->userid' has printed the book with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'book', 'print', 'tool/print/index.php?id=' . $this->contextinstanceid,
            $this->objectid, $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventbookprinted', 'booktool_print');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/book/tool/print/index.php', array('id' => $this->contextinstanceid));
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'book';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'book', 'restore' => 'book');
    }
}
