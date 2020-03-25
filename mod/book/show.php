<?php



require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id        = required_param('id', PARAM_INT);        $chapterid = required_param('chapterid', PARAM_INT); 
$cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);
require_sesskey();

$context = context_module::instance($cm->id);
require_capability('mod/book:edit', $context);

$PAGE->set_url('/mod/book/show.php', array('id'=>$id, 'chapterid'=>$chapterid));

$chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);

$chapter->hidden = $chapter->hidden ? 0 : 1;

$DB->update_record('book_chapters', $chapter);
\mod_book\event\chapter_updated::create_from_chapter($book, $context, $chapter)->trigger();

if (!$chapter->subchapter) {
    $chapters = $DB->get_recordset('book_chapters', array('bookid'=>$book->id), 'pagenum ASC');
    $found = 0;
    foreach ($chapters as $ch) {
        if ($ch->id == $chapter->id) {
            $found = 1;

        } else if ($found and $ch->subchapter) {
            $ch->hidden = $chapter->hidden;
            $DB->update_record('book_chapters', $ch);
            \mod_book\event\chapter_updated::create_from_chapter($book, $context, $ch)->trigger();

        } else if ($found) {
            break;
        }
    }
    $chapters->close();
}

book_preload_chapters($book); $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));

redirect('view.php?id='.$cm->id.'&chapterid='.$chapter->id);

