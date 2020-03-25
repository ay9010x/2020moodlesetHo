<?php



namespace mod_book\event;
defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {
    
    public static function create_from_book(\stdClass $book, \context_module $context) {
        $data = array(
            'context' => $context,
            'objectid' => $book->id
        );
        
        $event = self::create($data);
        $event->add_record_snapshot('book', $book);
        return $event;
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
