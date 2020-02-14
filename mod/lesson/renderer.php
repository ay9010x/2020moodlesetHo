<?php




defined('MOODLE_INTERNAL') || die();

class mod_lesson_renderer extends plugin_renderer_base {
    
    public function header($lesson, $cm, $currenttab = '', $extraeditbuttons = false, $lessonpageid = null, $extrapagetitle = null) {
        global $CFG;

        $activityname = format_string($lesson->name, true, $lesson->course);
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname.": ".$activityname;
        } else {
            $title = $this->page->course->shortname.": ".$activityname.": ".$extrapagetitle;
        }

                $context = context_module::instance($cm->id);

            $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        lesson_add_header_buttons($cm, $context, $extraeditbuttons, $lessonpageid);
        $output = $this->output->header();

        if (has_capability('mod/lesson:manage', $context)) {
            $output .= $this->output->heading_with_help($activityname, 'overview', 'lesson');

            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot.'/mod/lesson/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        } else {
            $output .= $this->output->heading($activityname);
        }

        foreach ($lesson->messages as $message) {
            $output .= $this->output->notification($message[0], $message[1], $message[2]);
        }

        return $output;
    }

    
    public function footer() {
        return $this->output->footer();
    }

    
    public function lesson_inaccessible($message) {
        global $CFG;
        $output  =  $this->output->box_start('generalbox boxaligncenter');
        $output .=  $this->output->box_start('center');
        $output .=  $message;
        $output .=  $this->output->box('<a href="'.$CFG->wwwroot.'/course/view.php?id='. $this->page->course->id .'">'. get_string('returnto', 'lesson', format_string($this->page->course->fullname, true)) .'</a>', 'lessonbutton standardbutton');
        $output .=  $this->output->box_end();
        $output .=  $this->output->box_end();
        return $output;
    }

    
    public function login_prompt(lesson $lesson, $failedattempt = false) {
        global $CFG;
        $output  = $this->output->box_start('password-form');
        $output .= $this->output->box_start('generalbox boxaligncenter');
        $output .=  '<form id="password" method="post" action="'.$CFG->wwwroot.'/mod/lesson/view.php" autocomplete="off">';
        $output .=  '<fieldset class="invisiblefieldset center">';
        $output .=  '<input type="hidden" name="id" value="'. $this->page->cm->id .'" />';
        $output .=  '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        if ($failedattempt) {
            $output .=  $this->output->notification(get_string('loginfail', 'lesson'));
        }
        $output .= get_string('passwordprotectedlesson', 'lesson', format_string($lesson->name)).'<br /><br />';
        $output .= get_string('enterpassword', 'lesson')." <input type=\"password\" name=\"userpassword\" /><br /><br />";
        $output .= "<div class='lessonbutton standardbutton submitbutton'><input type='submit' value='".get_string('continue', 'lesson')."' /></div>";
        $output .= " <div class='lessonbutton standardbutton submitbutton'><input type='submit' name='backtocourse' value='".get_string('cancel', 'lesson')."' /></div>";
        $output .=  '</fieldset></form>';
        $output .=  $this->output->box_end();
        $output .=  $this->output->box_end();
        return $output;
    }

    
    public function dependancy_errors($dependentlesson, $errors) {
        $output  = $this->output->box_start('generalbox boxaligncenter');
        $output .= get_string('completethefollowingconditions', 'lesson', $dependentlesson->name);
        $output .= $this->output->box(implode('<br />'.get_string('and', 'lesson').'<br />', $errors),'center');
        $output .= $this->output->box_end();
        return $output;
    }

    
    public function message($message, single_button $button = null) {
        $output  = $this->output->box_start('generalbox boxaligncenter');
        $output .= $message;
        if ($button !== null) {
            $output .= $this->output->box($this->output->render($button), 'lessonbutton standardbutton');
        }
        $output .= $this->output->box_end();
        return $output;
    }

    
    public function continue_links(lesson $lesson, $lastpageseenid) {
        global $CFG;
        $output = $this->output->box(get_string('youhaveseen','lesson'), 'generalbox boxaligncenter');
        $output .= $this->output->box_start('center');

        $yeslink = html_writer::link(new moodle_url('/mod/lesson/view.php', array('id'=>$this->page->cm->id, 'pageid'=>$lastpageseenid, 'startlastseen'=>'yes')), get_string('yes'));
        $output .= html_writer::tag('span', $yeslink, array('class'=>'lessonbutton standardbutton'));
        $output .= '&nbsp;';

        $nolink = html_writer::link(new moodle_url('/mod/lesson/view.php', array('id'=>$this->page->cm->id, 'pageid'=>$lesson->firstpageid, 'startlastseen'=>'no')), get_string('no'));
        $output .= html_writer::tag('span', $nolink, array('class'=>'lessonbutton standardbutton'));

        $output .= $this->output->box_end();
        return $output;
    }

    
    public function display_page(lesson $lesson, lesson_page $page, $attempt) {
                ob_start();
        echo $page->display($this, $attempt);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    
    public function display_edit_collapsed(lesson $lesson, $pageid) {
        global $DB, $CFG;

        $manager = lesson_page_type_manager::get($lesson);
        $qtypes = $manager->get_page_type_strings();
        $npages = count($lesson->load_all_pages());

        $table = new html_table();
        $table->head = array(get_string('pagetitle', 'lesson'), get_string('qtype', 'lesson'), get_string('jumps', 'lesson'), get_string('actions', 'lesson'));
        $table->align = array('left', 'left', 'left', 'center');
        $table->wrap = array('', 'nowrap', '', 'nowrap');
        $table->tablealign = 'center';
        $table->cellspacing = 0;
        $table->cellpadding = '2px';
        $table->width = '80%';
        $table->data = array();

        $canedit = has_capability('mod/lesson:edit', context_module::instance($this->page->cm->id));

        while ($pageid != 0) {
            $page = $lesson->load_page($pageid);
            $data = array();
            $url = new moodle_url('/mod/lesson/edit.php', array(
                'id'     => $this->page->cm->id,
                'mode'   => 'single',
                'pageid' => $page->id
            ));
            $data[] = html_writer::link($url, format_string($page->title, true), array('id' => 'lesson-' . $page->id));
            $data[] = $qtypes[$page->qtype];
            $data[] = implode("<br />\n", $page->jumps);
            if ($canedit) {
                $data[] = $this->page_action_links($page, $npages, true);
            } else {
                $data[] = '';
            }
            $table->data[] = $data;
            $pageid = $page->nextpageid;
        }

        return html_writer::table($table);
    }

    
    public function display_edit_full(lesson $lesson, $pageid, $prevpageid, $single=false) {
        global $DB, $CFG;

        $manager = lesson_page_type_manager::get($lesson);
        $qtypes = $manager->get_page_type_strings();
        $npages = count($lesson->load_all_pages());
        $canedit = has_capability('mod/lesson:edit', context_module::instance($this->page->cm->id));

        $content = '';
        if ($canedit) {
            $content = $this->add_page_links($lesson, $prevpageid);
        }

        $options = new stdClass;
        $options->noclean = true;

        while ($pageid != 0 && $single!=='stop') {
            $page = $lesson->load_page($pageid);

            $pagetable = new html_table();
            $pagetable->align = array('right','left');
            $pagetable->width = '100%';
            $pagetable->tablealign = 'center';
            $pagetable->cellspacing = 0;
            $pagetable->cellpadding = '5px';
            $pagetable->data = array();
            $pagetable->id = 'lesson-' . $pageid;

            $pageheading = new html_table_cell();

            $pageheading->text = format_string($page->title);
            if ($canedit) {
                $pageheading->text .= ' '.$this->page_action_links($page, $npages);
            }
            $pageheading->style = 'text-align:center';
            $pageheading->colspan = 2;
            $pageheading->scope = 'col';
            $pagetable->head = array($pageheading);

            $cell = new html_table_cell();
            $cell->colspan = 2;
            $cell->style = 'text-align:left';
            $cell->text = $page->contents;
            $pagetable->data[] = new html_table_row(array($cell));

            $cell = new html_table_cell();
            $cell->colspan = 2;
            $cell->style = 'text-align:center';
            $cell->text = '<strong>'.$qtypes[$page->qtype] . $page->option_description_string().'</strong>';
            $pagetable->data[] = new html_table_row(array($cell));

            $pagetable = $page->display_answers($pagetable);

            $content .= html_writer::start_tag('div', array('class' => 'no-overflow'));
            $content .= html_writer::table($pagetable);
            $content .= html_writer::end_tag('div');

            if ($canedit) {
                $content .= $this->add_page_links($lesson, $pageid);
            }

                                                if ($page->prevpageid != $prevpageid) {
                                $DB->set_field("lesson_pages", "prevpageid", $prevpageid, array("id" => $page->id));
                debugging("<p>***prevpageid of page $page->id set to $prevpageid***");
            }

            $prevpageid = $page->id;
            $pageid = $page->nextpageid;

            if ($single === true) {
                $single = 'stop';
            }

        }

        return $this->output->box($content, 'edit_pages_box');
    }

    
    public function add_page_links(lesson $lesson, $prevpageid=false) {
        global $CFG;

        $links = array();

        $importquestionsurl = new moodle_url('/mod/lesson/import.php',array('id'=>$this->page->cm->id, 'pageid'=>$prevpageid));
        $links[] = html_writer::link($importquestionsurl, get_string('importquestions', 'lesson'));

        $manager = lesson_page_type_manager::get($lesson);
        foreach($manager->get_add_page_type_links($prevpageid) as $link) {
            $links[] = html_writer::link($link['addurl'], $link['name']);
        }

        $addquestionurl = new moodle_url('/mod/lesson/editpage.php', array('id'=>$this->page->cm->id, 'pageid'=>$prevpageid));
        $links[] = html_writer::link($addquestionurl, get_string('addaquestionpagehere', 'lesson'));

        return $this->output->box(implode(" | \n", $links), 'addlinks');
    }

    
    public function add_first_page_links(lesson $lesson) {
        global $CFG;
        $prevpageid = 0;

        $output = $this->output->heading(get_string("whatdofirst", "lesson"), 3);
        $links = array();

        $importquestionsurl = new moodle_url('/mod/lesson/import.php',array('id'=>$this->page->cm->id, 'pageid'=>$prevpageid));
        $links[] = html_writer::link($importquestionsurl, get_string('importquestions', 'lesson'));

        $manager = lesson_page_type_manager::get($lesson);
        foreach ($manager->get_add_page_type_links($prevpageid) as $link) {
            $link['addurl']->param('firstpage', 1);
            $links[] = html_writer::link($link['addurl'], $link['name']);
        }

        $addquestionurl = new moodle_url('/mod/lesson/editpage.php', array('id'=>$this->page->cm->id, 'pageid'=>$prevpageid, 'firstpage'=>1));
        $links[] = html_writer::link($addquestionurl, get_string('addaquestionpage', 'lesson'));

        return $this->output->box($output.'<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }

    
    public function page_action_links(lesson_page $page, $printmove, $printaddpage=false) {
        global $CFG;

        $actions = array();

        if ($printmove) {
            $url = new moodle_url('/mod/lesson/lesson.php',
                    array('id' => $this->page->cm->id, 'action' => 'move', 'pageid' => $page->id, 'sesskey' => sesskey()));
            $label = get_string('movepagenamed', 'lesson', format_string($page->title));
            $img = html_writer::img($this->output->pix_url('t/move'), $label, array('class' => 'iconsmall'));
            $actions[] = html_writer::link($url, $img, array('title' => $label));
        }
        $url = new moodle_url('/mod/lesson/editpage.php', array('id' => $this->page->cm->id, 'pageid' => $page->id, 'edit' => 1));
        $label = get_string('updatepagenamed', 'lesson', format_string($page->title));
        $img = html_writer::img($this->output->pix_url('t/edit'), $label, array('class' => 'iconsmall'));
        $actions[] = html_writer::link($url, $img, array('title' => $label));

        $url = new moodle_url('/mod/lesson/view.php', array('id' => $this->page->cm->id, 'pageid' => $page->id));
        $label = get_string('previewpagenamed', 'lesson', format_string($page->title));
        $img = html_writer::img($this->output->pix_url('t/preview'), $label, array('class' => 'iconsmall'));
        $actions[] = html_writer::link($url, $img, array('title' => $label));

        $url = new moodle_url('/mod/lesson/lesson.php',
                array('id' => $this->page->cm->id, 'action' => 'confirmdelete', 'pageid' => $page->id, 'sesskey' => sesskey()));
        $label = get_string('deletepagenamed', 'lesson', format_string($page->title));
        $img = html_writer::img($this->output->pix_url('t/delete'), $label, array('class' => 'iconsmall'));
        $actions[] = html_writer::link($url, $img, array('title' => $label));

        if ($printaddpage) {
            $options = array();
            $manager = lesson_page_type_manager::get($page->lesson);
            $links = $manager->get_add_page_type_links($page->id);
            foreach ($links as $link) {
                $options[$link['type']] = $link['name'];
            }
            $options[0] = get_string('question', 'lesson');

            $addpageurl = new moodle_url('/mod/lesson/editpage.php', array('id'=>$this->page->cm->id, 'pageid'=>$page->id, 'sesskey'=>sesskey()));
            $addpageselect = new single_select($addpageurl, 'qtype', $options, null, array(''=>get_string('addanewpage', 'lesson').'...'), 'addpageafter'.$page->id);
            $addpageselector = $this->output->render($addpageselect);
        }

        if (isset($addpageselector)) {
            $actions[] = $addpageselector;
        }

        return implode(' ', $actions);
    }

    

     
    public function ongoing_score(lesson $lesson) {
        global $USER, $DB;

        $context = context_module::instance($this->page->cm->id);
        if (has_capability('mod/lesson:manage', $context)) {
            return $this->output->box(get_string('teacherongoingwarning', 'lesson'), "ongoing center");
        } else {
            $ntries = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$USER->id));
            if (isset($USER->modattempts[$lesson->id])) {
                $ntries--;
            }
            $gradeinfo = lesson_grade($lesson, $ntries);
            $a = new stdClass;
            if ($lesson->custom) {
                $a->score = $gradeinfo->earned;
                $a->currenthigh = $gradeinfo->total;
                return $this->output->box(get_string("ongoingcustom", "lesson", $a), "ongoing center");
            } else {
                $a->correct = $gradeinfo->earned;
                $a->viewed = $gradeinfo->attempts;
                return $this->output->box(get_string("ongoingnormal", "lesson", $a), "ongoing center");
            }
        }
    }

    
    public function progress_bar(lesson $lesson) {
        global $CFG, $USER, $DB;

        $context = context_module::instance($this->page->cm->id);

                if (!$lesson->progressbar) {
            return '';
        }

                if (has_capability('mod/lesson:manage', $context)) {
            return $this->output->notification(get_string('progressbarteacherwarning2', 'lesson'));
        }

        if (!isset($USER->modattempts[$lesson->id])) {
                        $pages = $lesson->load_all_pages();
            foreach ($pages as $page) {
                if ($page->prevpageid == 0) {
                    $pageid = $page->id;                      break;
                }
            }

                        if (!$ntries = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$USER->id))) {
                $ntries = 0;              }

            $viewedpageids = array();
            if ($attempts = $lesson->get_attempts($ntries, false)) {
                foreach($attempts as $attempt) {
                    $viewedpageids[$attempt->pageid] = $attempt;
                }
            }

            $viewedbranches = array();
                        if ($branches = $DB->get_records("lesson_branch", array ("lessonid"=>$lesson->id, "userid"=>$USER->id, "retry"=>$ntries), 'timeseen ASC', 'id, pageid')) {
                foreach($branches as $branch) {
                    $viewedbranches[$branch->pageid] = $branch;
                }
                $viewedpageids = array_merge($viewedpageids, $viewedbranches);
            }

                                                                                    $validpages = array();
            while ($pageid != 0) {
                $pageid = $pages[$pageid]->valid_page_and_view($validpages, $viewedpageids);
            }

                        $progress = round(count($viewedpageids)/count($validpages), 2) * 100;
        } else {
            $progress = 100;
        }

                $content = '<br />' . html_writer::tag('div', $progress . '%', array('class' => 'progress_bar_completed', 'style' => 'width: '. $progress . '%;'));
        $printprogress = html_writer::tag('div', get_string('progresscompleted', 'lesson', $progress) . $content, array('class' => 'progress_bar'));

        return $this->output->box($printprogress, 'progress_bar');
    }

    
    public function slideshow_start(lesson $lesson) {
        $attributes = array();
        $attributes['class'] = 'slideshow';
        $attributes['style'] = 'background-color:'.$lesson->properties()->bgcolor.';height:'.
                $lesson->properties()->height.'px;width:'.$lesson->properties()->width.'px;';
        $output = html_writer::start_tag('div', $attributes);
        return $output;
    }
    
    public function slideshow_end() {
        $output = html_writer::end_tag('div');
        return $output;
    }
    
    public function paragraph($contents, $class='') {
        $attributes = array();
        if ($class !== '') {
            $attributes['class'] = $class;
        }
        $output = html_writer::tag('p', $contents, $attributes);
        return $output;
    }
}
