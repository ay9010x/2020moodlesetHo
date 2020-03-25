<?php



require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id        = required_param('id', PARAM_INT);        $chapterid = required_param('chapterid', PARAM_INT); $up        = optional_param('up', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);
require_sesskey();

$context = context_module::instance($cm->id);
require_capability('mod/book:edit', $context);

$chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);


$oldchapters = $DB->get_records('book_chapters', array('bookid'=>$book->id), 'pagenum', 'id, pagenum, subchapter');

$nothing = 0;

$chapters = array();
$chs = 0;
$che = 0;
$ts = 0;
$te = 0;
$i = 1;
$found = 0;
foreach ($oldchapters as $ch) {
    $chapters[$i] = $ch;
    if ($chapter->id == $ch->id) {
        $chs = $i;
        $che = $chs;
        if ($ch->subchapter) {
            $found = 1;        }
    } else if ($chs) {
        if ($found) {
                    } else if ($ch->subchapter) {
            $che = $i;         } else {
            $found = 1;
        }
    }
    $i++;
}

if ($chapters[$chs]->subchapter) {     if ($up) {
        if ($chs == 1) {
            $nothing = 1;         } else {
            $ts = $chs - 1;
            $te = $ts;
        }
    } else {         if ($che == count($chapters)) {
            $nothing = 1;         } else {
            $ts = $che + 1;
            $te = $ts;
        }
    }
} else {     if ($up) {         if ($chs == 1) {
            $nothing = 1;         } else {
            $te = $chs - 1;
            for ($i = $chs-1; $i >= 1; $i--) {
                if ($chapters[$i]->subchapter) {
                    $ts = $i;
                } else {
                    $ts = $i;
                    break;
                }
            }
        }
    } else {         if ($che == count($chapters)) {
            $nothing = 1;         } else {
            $ts = $che + 1;
            $found = 0;
            for ($i = $che+1; $i <= count($chapters); $i++) {
                if ($chapters[$i]->subchapter) {
                    $te = $i;
                } else {
                    if ($found) {
                        break;
                    } else {
                        $te = $i;
                        $found = 1;
                    }
                }
            }
        }
    }
}

if (!$nothing) {
    $newchapters = array();

    if ($up) {
        if ($ts > 1) {
            for ($i=1; $i<$ts; $i++) {
                $newchapters[] = $chapters[$i];
            }
        }
        for ($i=$chs; $i<=$che; $i++) {
            $newchapters[$i] = $chapters[$i];
        }
        for ($i=$ts; $i<=$te; $i++) {
            $newchapters[$i] = $chapters[$i];
        }
        if ($che<count($chapters)) {
            for ($i=$che; $i<=count($chapters); $i++) {
                $newchapters[$i] = $chapters[$i];
            }
        }
    } else {
        if ($chs > 1) {
            for ($i=1; $i<$chs; $i++) {
                $newchapters[] = $chapters[$i];
            }
        }
        for ($i=$ts; $i<=$te; $i++) {
            $newchapters[$i] = $chapters[$i];
        }
        for ($i=$chs; $i<=$che; $i++) {
            $newchapters[$i] = $chapters[$i];
        }
        if ($te<count($chapters)) {
            for ($i=$te; $i<=count($chapters); $i++) {
                $newchapters[$i] = $chapters[$i];
            }
        }
    }

        $i = 1;
    foreach ($newchapters as $ch) {
        $ch->pagenum = $i;
        $DB->update_record('book_chapters', $ch);
        $ch = $DB->get_record('book_chapters', array('id' => $ch->id));

        \mod_book\event\chapter_updated::create_from_chapter($book, $context, $ch)->trigger();

        $i++;
    }
}

book_preload_chapters($book); $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));

redirect('view.php?id='.$cm->id.'&chapterid='.$chapter->id);

