<?php



defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/book/locallib.php');


function booktool_print_get_toc($chapters, $book, $cm) {
    $first = true;
    $titles = array();

    $context = context_module::instance($cm->id);

    $toc = ''; 
    switch ($book->numbering) {
        case BOOK_NUM_NONE:
            $toc .= html_writer::start_tag('div', array('class' => 'book_toc_none'));
            break;
        case BOOK_NUM_NUMBERS:
            $toc .= html_writer::start_tag('div', array('class' => 'book_toc_numbered'));
            break;
        case BOOK_NUM_BULLETS:
            $toc .= html_writer::start_tag('div', array('class' => 'book_toc_bullets'));
            break;
        case BOOK_NUM_INDENTED:
            $toc .= html_writer::start_tag('div', array('class' => 'book_toc_indented'));
            break;
    }

    $toc .= html_writer::tag('a', '', array('name' => 'toc')); 
    $toc .= html_writer::tag('h2', get_string('toc', 'mod_book'));
    $toc .= html_writer::start_tag('ul');
    foreach ($chapters as $ch) {
        if (!$ch->hidden) {
            $title = book_get_chapter_title($ch->id, $chapters, $book, $context);
            if (!$ch->subchapter) {

                if ($first) {
                    $toc .= html_writer::start_tag('li');
                } else {
                    $toc .= html_writer::end_tag('ul');
                    $toc .= html_writer::end_tag('li');
                    $toc .= html_writer::start_tag('li');
                }

            } else {

                if ($first) {
                    $toc .= html_writer::start_tag('li');
                    $toc .= html_writer::start_tag('ul');
                    $toc .= html_writer::start_tag('li');
                } else {
                    $toc .= html_writer::start_tag('li');
                }

            }
            $titles[$ch->id] = $title;
            $toc .= html_writer::link(new moodle_url('#ch'.$ch->id), $title, array('title' => s($title)));
            if (!$ch->subchapter) {
                $toc .= html_writer::start_tag('ul');
            } else {
                $toc .= html_writer::end_tag('li');
            }
            $first = false;
        }
    }

    $toc .= html_writer::end_tag('ul');
    $toc .= html_writer::end_tag('li');
    $toc .= html_writer::end_tag('ul');
    $toc .= html_writer::end_tag('div');

    $toc = str_replace('<ul></ul>', '', $toc); 
    return array($toc, $titles);
}
