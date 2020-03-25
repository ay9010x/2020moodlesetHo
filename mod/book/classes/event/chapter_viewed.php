<?php



namespace mod_book\event;
defined('MOODLE_INTERNAL') || die();


class chapter_viewed extends \core\event\base {
    
    public static function create_from_chapter(\stdClass $book, \context_module $context, \stdClass $chapter) {
        $data = array(
            'context' => $context,
            'objectid' => $chapter->id,
        );
        
        $event = self::create($data);
        $event->add_record_snapshot('book', $book);
        $event->add_record_snapshot('book_chapters', $chapter);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the chapter with id '$this->objectid' for the book with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'book', 'view chapter', 'view.php?id=' . $this->contextinstanceid .
            '&amp;chapterid=' . $this->objectid, $this->objectid, $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventchapterviewed', 'mod_book');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/book/view.php', array('id' => $this->contextinstanceid, 'chapterid' => $this->objectid));
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'book_chapters';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'book_chapters', 'restore' => 'book_chapter');
    }
}
