<?php



defined('MOODLE_INTERNAL') || die;


class backup_book_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $book = new backup_nested_element('book', array('id'), array(
            'name', 'intro', 'introformat', 'numbering', 'navstyle',
            'customtitles', 'timecreated', 'timemodified'));
        $chapters = new backup_nested_element('chapters');
        $chapter = new backup_nested_element('chapter', array('id'), array(
            'pagenum', 'subchapter', 'title', 'content', 'contentformat',
            'hidden', 'timemcreated', 'timemodified', 'importsrc'));

        $book->add_child($chapters);
        $chapters->add_child($chapter);

                $book->set_source_table('book', array('id' => backup::VAR_ACTIVITYID));
        $chapter->set_source_table('book_chapters', array('bookid' => backup::VAR_PARENTID));

                $book->annotate_files('mod_book', 'intro', null);         $chapter->annotate_files('mod_book', 'chapter', 'id');

                return $this->prepare_activity_structure($book);
    }
}
