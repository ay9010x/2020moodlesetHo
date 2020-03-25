<?php



defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/filelib.php');


define('BOOK_NUM_NONE',     '0');
define('BOOK_NUM_NUMBERS',  '1');
define('BOOK_NUM_BULLETS',  '2');
define('BOOK_NUM_INDENTED', '3');


define ('BOOK_LINK_TOCONLY', '0');
define ('BOOK_LINK_IMAGE', '1');
define ('BOOK_LINK_TEXT', '2');


function book_preload_chapters($book) {
    global $DB;
    $chapters = $DB->get_records('book_chapters', array('bookid'=>$book->id), 'pagenum', 'id, pagenum, subchapter, title, hidden');
    if (!$chapters) {
        return array();
    }

    $prev = null;
    $prevsub = null;

    $first = true;
    $hidesub = true;
    $parent = null;
    $pagenum = 0;     $i = 0;           $j = 0;           foreach ($chapters as $id => $ch) {
        $oldch = clone($ch);
        $pagenum++;
        $ch->pagenum = $pagenum;
        if ($first) {
                        $ch->subchapter = 0;
            $first = false;
        }
        if (!$ch->subchapter) {
            if ($ch->hidden) {
                if ($book->numbering == BOOK_NUM_NUMBERS) {
                    $ch->number = 'x';
                } else {
                    $ch->number = null;
                }
            } else {
                $i++;
                $ch->number = $i;
            }
            $j = 0;
            $prevsub = null;
            $hidesub = $ch->hidden;
            $parent = $ch->id;
            $ch->parent = null;
            $ch->subchapters = array();
        } else {
            $ch->parent = $parent;
            $ch->subchapters = null;
            $chapters[$parent]->subchapters[$ch->id] = $ch->id;
            if ($hidesub) {
                                $ch->hidden = 1;
            }
            if ($ch->hidden) {
                if ($book->numbering == BOOK_NUM_NUMBERS) {
                    $ch->number = 'x';
                } else {
                    $ch->number = null;
                }
            } else {
                $j++;
                $ch->number = $j;
            }
        }

        if ($oldch->subchapter != $ch->subchapter or $oldch->pagenum != $ch->pagenum or $oldch->hidden != $ch->hidden) {
                        $DB->update_record('book_chapters', $ch);
        }
        $chapters[$id] = $ch;
    }

    return $chapters;
}


function book_get_chapter_title($chid, $chapters, $book, $context) {
    $ch = $chapters[$chid];
    $title = trim(format_string($ch->title, true, array('context'=>$context)));
    $numbers = array();
    if ($book->numbering == BOOK_NUM_NUMBERS) {
        if ($ch->parent and $chapters[$ch->parent]->number) {
            $numbers[] = $chapters[$ch->parent]->number;
        }
        if ($ch->number) {
            $numbers[] = $ch->number;
        }
    }

    if ($numbers) {
        $title = implode('.', $numbers).' '.$title;
    }

    return $title;
}


function book_add_fake_block($chapters, $chapter, $book, $cm, $edit) {
    global $OUTPUT, $PAGE;

    $toc = book_get_toc($chapters, $chapter, $book, $cm, $edit, 0);

    $bc = new block_contents();
    $bc->title = get_string('toc', 'mod_book');
    $bc->attributes['class'] = 'block block_book_toc';
    $bc->content = $toc;

    $defaultregion = $PAGE->blocks->get_default_region();
    $PAGE->blocks->add_fake_block($bc, $defaultregion);
}


function book_get_toc($chapters, $chapter, $book, $cm, $edit) {
    global $USER, $OUTPUT;

    $toc = '';
    $nch = 0;       $ns = 0;        $first = 1;

    $context = context_module::instance($cm->id);

    switch ($book->numbering) {
        case BOOK_NUM_NONE:
            $toc .= html_writer::start_tag('div', array('class' => 'book_toc_none clearfix'));
            break;
        case BOOK_NUM_NUMBERS:
            $toc .= html_writer::start_tag('div', array('class' => 'book_toc_numbered clearfix'));
            break;
        case BOOK_NUM_BULLETS:
            $toc .= html_writer::start_tag('div', array('class' => 'book_toc_bullets clearfix'));
            break;
        case BOOK_NUM_INDENTED:
            $toc .= html_writer::start_tag('div', array('class' => 'book_toc_indented clearfix'));
            break;
    }

    if ($edit) {         $toc .= html_writer::start_tag('ul');
        $i = 0;
        foreach ($chapters as $ch) {
            $i++;
            $title = trim(format_string($ch->title, true, array('context' => $context)));
            $titleunescaped = trim(format_string($ch->title, true, array('context' => $context, 'escape' => false)));
            $titleout = $title;

            if (!$ch->subchapter) {

                if ($first) {
                    $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                } else {
                    $toc .= html_writer::end_tag('ul');
                    $toc .= html_writer::end_tag('li');
                    $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                }

                if (!$ch->hidden) {
                    $nch++;
                    $ns = 0;
                    if ($book->numbering == BOOK_NUM_NUMBERS) {
                        $title = "$nch $title";
                        $titleout = $title;
                    }
                } else {
                    if ($book->numbering == BOOK_NUM_NUMBERS) {
                        $title = "x $title";
                    }
                    $titleout = html_writer::tag('span', $title, array('class' => 'dimmed_text'));
                }
            } else {

                if ($first) {
                    $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    $toc .= html_writer::start_tag('ul');
                    $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                } else {
                    $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                }

                if (!$ch->hidden) {
                    $ns++;
                    if ($book->numbering == BOOK_NUM_NUMBERS) {
                        $title = "$nch.$ns $title";
                        $titleout = $title;
                    }
                } else {
                    if ($book->numbering == BOOK_NUM_NUMBERS) {
                        if (empty($chapters[$ch->parent]->hidden)) {
                            $title = "$nch.x $title";
                        } else {
                            $title = "x.x $title";
                        }
                    }
                    $titleout = html_writer::tag('span', $title, array('class' => 'dimmed_text'));
                }
            }

            if ($ch->id == $chapter->id) {
                $toc .= html_writer::tag('strong', $titleout);
            } else {
                $toc .= html_writer::link(new moodle_url('view.php', array('id' => $cm->id, 'chapterid' => $ch->id)), $titleout,
                    array('title' => $titleunescaped));
            }

            $toc .= html_writer::start_tag('div', array('class' => 'action-list'));
            if ($i != 1) {
                $toc .= html_writer::link(new moodle_url('move.php', array('id' => $cm->id, 'chapterid' => $ch->id, 'up' => '1', 'sesskey' => $USER->sesskey)),
                        $OUTPUT->pix_icon('t/up', get_string('movechapterup', 'mod_book', $title)),
                        array('title' => get_string('movechapterup', 'mod_book', $titleunescaped)));
            }
            if ($i != count($chapters)) {
                $toc .= html_writer::link(new moodle_url('move.php', array('id' => $cm->id, 'chapterid' => $ch->id, 'up' => '0', 'sesskey' => $USER->sesskey)),
                        $OUTPUT->pix_icon('t/down', get_string('movechapterdown', 'mod_book', $title)),
                        array('title' => get_string('movechapterdown', 'mod_book', $titleunescaped)));
            }
            $toc .= html_writer::link(new moodle_url('edit.php', array('cmid' => $cm->id, 'id' => $ch->id)),
                    $OUTPUT->pix_icon('t/edit', get_string('editchapter', 'mod_book', $title)),
                    array('title' => get_string('editchapter', 'mod_book', $titleunescaped)));
            $toc .= html_writer::link(new moodle_url('delete.php', array('id' => $cm->id, 'chapterid' => $ch->id, 'sesskey' => $USER->sesskey)),
                        $OUTPUT->pix_icon('t/delete', get_string('deletechapter', 'mod_book', $title)),
                        array('title' => get_string('deletechapter', 'mod_book', $titleunescaped)));
            if ($ch->hidden) {
                $toc .= html_writer::link(new moodle_url('show.php', array('id' => $cm->id, 'chapterid' => $ch->id, 'sesskey' => $USER->sesskey)),
                        $OUTPUT->pix_icon('t/show', get_string('showchapter', 'mod_book', $title)),
                        array('title' => get_string('showchapter', 'mod_book', $titleunescaped)));
            } else {
                $toc .= html_writer::link(new moodle_url('show.php', array('id' => $cm->id, 'chapterid' => $ch->id, 'sesskey' => $USER->sesskey)),
                        $OUTPUT->pix_icon('t/hide', get_string('hidechapter', 'mod_book', $title)),
                        array('title' => get_string('hidechapter', 'mod_book', $titleunescaped)));
            }
            $toc .= html_writer::link(new moodle_url('edit.php', array('cmid' => $cm->id, 'pagenum' => $ch->pagenum, 'subchapter' => $ch->subchapter)),
                                            $OUTPUT->pix_icon('add', get_string('addafter', 'mod_book'), 'mod_book'), array('title' => get_string('addafter', 'mod_book')));
            $toc .= html_writer::end_tag('div');

            if (!$ch->subchapter) {
                $toc .= html_writer::start_tag('ul');
            } else {
                $toc .= html_writer::end_tag('li');
            }
            $first = 0;
        }

        $toc .= html_writer::end_tag('ul');
        $toc .= html_writer::end_tag('li');
        $toc .= html_writer::end_tag('ul');

    } else {         $toc .= html_writer::start_tag('ul');
        foreach ($chapters as $ch) {
            $title = trim(format_string($ch->title, true, array('context'=>$context)));
            $titleunescaped = trim(format_string($ch->title, true, array('context' => $context, 'escape' => false)));
            if (!$ch->hidden) {
                if (!$ch->subchapter) {
                    $nch++;
                    $ns = 0;

                    if ($first) {
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    } else {
                        $toc .= html_writer::end_tag('ul');
                        $toc .= html_writer::end_tag('li');
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    }

                    if ($book->numbering == BOOK_NUM_NUMBERS) {
                          $title = "$nch $title";
                    }
                } else {
                    $ns++;

                    if ($first) {
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                        $toc .= html_writer::start_tag('ul');
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    } else {
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    }

                    if ($book->numbering == BOOK_NUM_NUMBERS) {
                          $title = "$nch.$ns $title";
                    }
                }
                if ($ch->id == $chapter->id) {
                    $toc .= html_writer::tag('strong', $title);
                } else {
                    $toc .= html_writer::link(new moodle_url('view.php',
                                              array('id' => $cm->id, 'chapterid' => $ch->id)),
                                              $title, array('title' => s($titleunescaped)));
                }

                if (!$ch->subchapter) {
                    $toc .= html_writer::start_tag('ul');
                } else {
                    $toc .= html_writer::end_tag('li');
                }

                $first = 0;
            }
        }

        $toc .= html_writer::end_tag('ul');
        $toc .= html_writer::end_tag('li');
        $toc .= html_writer::end_tag('ul');

    }

    $toc .= html_writer::end_tag('div');

    $toc = str_replace('<ul></ul>', '', $toc); 
    return $toc;
}



class book_file_info extends file_info {
    
    protected $course;
    
    protected $cm;
    
    protected $areas;
    
    protected $filearea;

    
    public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
        parent::__construct($browser, $context);
        $this->course   = $course;
        $this->cm       = $cm;
        $this->areas    = $areas;
        $this->filearea = $filearea;
    }

    
    public function get_params() {
        return array('contextid'=>$this->context->id,
                     'component'=>'mod_book',
                     'filearea' =>$this->filearea,
                     'itemid'   =>null,
                     'filepath' =>null,
                     'filename' =>null);
    }

    
    public function get_visible_name() {
        return $this->areas[$this->filearea];
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        global $DB;
        $params = array('contextid' => $this->context->id,
            'component' => 'mod_book',
            'filearea' => $this->filearea,
            'bookid' => $this->cm->instance);
        $sql = 'SELECT DISTINCT bc.id, bc.pagenum
                    FROM {files} f, {book_chapters} bc
                    WHERE f.contextid = :contextid
                    AND f.component = :component
                    AND f.filearea = :filearea
                    AND bc.bookid = :bookid
                    AND bc.id = f.itemid';
        if (!$returnemptyfolders) {
            $sql .= ' AND filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }
        list($sql2, $params2) = $this->build_search_files_sql($extensions, 'f');
        $sql .= ' '.$sql2;
        $params = array_merge($params, $params2);
        if ($countonly === false) {
            $sql .= ' ORDER BY bc.pagenum';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = array();
        foreach ($rs as $record) {
            if ($child = $this->browser->get_file_info($this->context, 'mod_book', $this->filearea, $record->id)) {
                if ($returnemptyfolders || $child->count_non_empty_children($extensions)) {
                    $children[] = $child;
                }
            }
            if ($countonly !== false && count($children) >= $countonly) {
                break;
            }
        }
        $rs->close();
        if ($countonly !== false) {
            return count($children);
        }
        return $children;
    }

    
    public function get_non_empty_children($extensions = '*') {
        return $this->get_filtered_children($extensions, false);
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        return $this->get_filtered_children($extensions, $limit);
    }

    
    public function get_parent() {
        return $this->browser->get_file_info($this->context);
    }
}
