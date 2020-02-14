<?php



namespace booktool_print\event;
defined('MOODLE_INTERNAL') || die();


class chapter_printed extends \core\event\base {
    
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
        return "The user with id '$this->userid' has printed the chapter with id '$this->objectid' of the book with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'book', 'print chapter', 'tool/print/index.php?id=' . $this->contextinstanceid .
            '&chapterid=' . $this->objectid, $this->objectid, $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventchapterprinted', 'booktool_print');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/book/tool/print/index.php', array('id' => $this->contextinstanceid));
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
