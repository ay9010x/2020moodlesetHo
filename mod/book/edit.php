<?php



require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/edit_form.php');

$cmid       = required_param('cmid', PARAM_INT);  $chapterid  = optional_param('id', 0, PARAM_INT); $pagenum    = optional_param('pagenum', 0, PARAM_INT);
$subchapter = optional_param('subchapter', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('book', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/book:edit', $context);

$PAGE->set_url('/mod/book/edit.php', array('cmid'=>$cmid, 'id'=>$chapterid, 'pagenum'=>$pagenum, 'subchapter'=>$subchapter));
$PAGE->set_pagelayout('admin'); 
if ($chapterid) {
    $chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);
} else {
    $chapter = new stdClass();
    $chapter->id         = null;
    $chapter->subchapter = $subchapter;
    $chapter->pagenum    = $pagenum + 1;
}
$chapter->cmid = $cm->id;

$options = array('noclean'=>true, 'subdirs'=>true, 'maxfiles'=>-1, 'maxbytes'=>0, 'context'=>$context);
$chapter = file_prepare_standard_editor($chapter, 'content', $options, $context, 'mod_book', 'chapter', $chapter->id);

$mform = new book_chapter_edit_form(null, array('chapter'=>$chapter, 'options'=>$options));

if ($mform->is_cancelled()) {
    if (empty($chapter->id)) {
        redirect("view.php?id=$cm->id");
    } else {
        redirect("view.php?id=$cm->id&chapterid=$chapter->id");
    }

} else if ($data = $mform->get_data()) {

    if ($data->id) {
                $data->timemodified = time();
        $data = file_postupdate_standard_editor($data, 'content', $options, $context, 'mod_book', 'chapter', $data->id);
        $DB->update_record('book_chapters', $data);
        $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));
        $chapter = $DB->get_record('book_chapters', array('id' => $data->id));

        \mod_book\event\chapter_updated::create_from_chapter($book, $context, $chapter)->trigger();

    } else {
                $data->bookid        = $book->id;
        $data->hidden        = 0;
        $data->timecreated   = time();
        $data->timemodified  = time();
        $data->importsrc     = '';
        $data->content       = '';                  $data->contentformat = FORMAT_HTML; 
                $sql = "UPDATE {book_chapters}
                   SET pagenum = pagenum + 1
                 WHERE bookid = ? AND pagenum >= ?";
        $DB->execute($sql, array($book->id, $data->pagenum));

        $data->id = $DB->insert_record('book_chapters', $data);

                $data = file_postupdate_standard_editor($data, 'content', $options, $context, 'mod_book', 'chapter', $data->id);
        $DB->update_record('book_chapters', $data);
        $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));
        $chapter = $DB->get_record('book_chapters', array('id' => $data->id));

        \mod_book\event\chapter_created::create_from_chapter($book, $context, $chapter)->trigger();
    }

    book_preload_chapters($book);     redirect("view.php?id=$cm->id&chapterid=$data->id");
}

$PAGE->set_title($book->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($book->name);

$mform->display();

echo $OUTPUT->footer();
