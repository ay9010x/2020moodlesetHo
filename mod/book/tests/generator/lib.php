<?php



defined('MOODLE_INTERNAL') || die();


class mod_book_generator extends testing_module_generator {

    
    protected $chaptercount = 0;

    
    public function reset() {
        $this->chaptercount = 0;
        parent::reset();
    }

    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once("$CFG->dirroot/mod/book/locallib.php");

        $record = (object)(array)$record;

        if (!isset($record->numbering)) {
            $record->numbering = BOOK_NUM_NUMBERS;
        }
        if (!isset($record->customtitles)) {
            $record->customtitles = 0;
        }

        return parent::create_instance($record, (array)$options);
    }

    public function create_chapter($record = null, array $options = null) {
        global $DB;

        $record = (object) (array) $record;
        $options = (array) $options;
        $this->chaptercount++;

        if (empty($record->bookid)) {
            throw new coding_exception('Chapter generator requires $record->bookid');
        }

        if (empty($record->title)) {
            $record->title = "Chapter {$this->chaptercount}";
        }
        if (empty($record->pagenum)) {
            $record->pagenum = 1;
        }
        if (!isset($record->subchapter)) {
            $record->subchapter = 0;
        }
        if (!isset($record->hidden)) {
            $record->hidden = 0;
        }
        if (!isset($record->importsrc)) {
            $record->importsrc = '';
        }
        if (!isset($record->content)) {
            $record->content = "Chapter {$this->chaptercount} content";
        }
        if (!isset($record->contentformat)) {
            $record->contentformat = FORMAT_MOODLE;
        }
        if (!isset($record->timecreated)) {
            $record->timecreated = time();
        }
        if (!isset($record->timemodified)) {
            $record->timemodified = time();
        }

                $sql = "UPDATE {book_chapters}
                   SET pagenum = pagenum + 1
                 WHERE bookid = ? AND pagenum >= ?";
        $DB->execute($sql, array($record->bookid, $record->pagenum));
        $record->id = $DB->insert_record('book_chapters', $record);

        $sql = "UPDATE {book}
                   SET revision = revision + 1
                 WHERE id = ?";
        $DB->execute($sql, array($record->bookid));

        return $record;
    }

    public function create_content($instance, $record = array()) {
        $record = (array)$record + array(
            'bookid' => $instance->id
        );
        return $this->create_chapter($record);
    }

}
