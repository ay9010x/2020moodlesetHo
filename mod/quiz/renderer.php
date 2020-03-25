<?php




defined('MOODLE_INTERNAL') || die();



class mod_quiz_renderer extends plugin_renderer_base {
    
    public function review_page(quiz_attempt $attemptobj, $slots, $page, $showall,
                                $lastpage, mod_quiz_display_options $displayoptions,
                                $summarydata) {

        $output = '';
        $output .= $this->header();
        $output .= $this->review_summary_table($summarydata, $page);
        $output .= $this->review_form($page, $showall, $displayoptions,
                $this->questions($attemptobj, true, $slots, $page, $showall, $displayoptions),
                $attemptobj);

        $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);
        $output .= $this->footer();
        return $output;
    }

    
    public function review_question_page(quiz_attempt $attemptobj, $slot, $seq,
            mod_quiz_display_options $displayoptions, $summarydata) {

        $output = '';
        $output .= $this->header();
        $output .= $this->review_summary_table($summarydata, 0);

        if (!is_null($seq)) {
            $output .= $attemptobj->render_question_at_step($slot, $seq, true, $this);
        } else {
            $output .= $attemptobj->render_question($slot, true, $this);
        }

        $output .= $this->close_window_button();
        $output .= $this->footer();
        return $output;
    }

    
    public function review_question_not_allowed(quiz_attempt $attemptobj, $message) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(format_string($attemptobj->get_quiz_name(), true,
                                  array("context" => $attemptobj->get_quizobj()->get_context())));
        $output .= $this->notification($message);
        $output .= $this->close_window_button();
        $output .= $this->footer();
        return $output;
    }

    
    protected function filter_review_summary_table($summarydata, $page) {
        if ($page == 0) {
            return $summarydata;
        }

                foreach ($summarydata as $key => $rowdata) {
            if (!in_array($key, array('user', 'attemptlist'))) {
                unset($summarydata[$key]);
            }
        }

        return $summarydata;
    }

    
    public function review_summary_table($summarydata, $page) {
        $summarydata = $this->filter_review_summary_table($summarydata, $page);
        if (empty($summarydata)) {
            return '';
        }

        $output = '';
        $output .= html_writer::start_tag('table', array(
                'class' => 'generaltable generalbox quizreviewsummary'));
        $output .= html_writer::start_tag('tbody');
        foreach ($summarydata as $rowdata) {
            if ($rowdata['title'] instanceof renderable) {
                $title = $this->render($rowdata['title']);
            } else {
                $title = $rowdata['title'];
            }

            if ($rowdata['content'] instanceof renderable) {
                $content = $this->render($rowdata['content']);
            } else {
                $content = $rowdata['content'];
            }

            $output .= html_writer::tag('tr',
                html_writer::tag('th', $title, array('class' => 'cell', 'scope' => 'row')) .
                        html_writer::tag('td', $content, array('class' => 'cell'))
            );
        }

        $output .= html_writer::end_tag('tbody');
        $output .= html_writer::end_tag('table');
        return $output;
    }

    
    public function questions(quiz_attempt $attemptobj, $reviewing, $slots, $page, $showall,
                              mod_quiz_display_options $displayoptions) {
        $output = '';
        foreach ($slots as $slot) {
            $output .= $attemptobj->render_question($slot, $reviewing, $this,
                    $attemptobj->review_url($slot, $page, $showall));
        }
        return $output;
    }

    
    public function review_form($page, $showall, $displayoptions, $content, $attemptobj) {
        if ($displayoptions->flags != question_display_options::EDITABLE) {
            return $content;
        }

        $this->page->requires->js_init_call('M.mod_quiz.init_review_form', null, false,
                quiz_get_js_module());

        $output = '';
        $output .= html_writer::start_tag('form', array('action' => $attemptobj->review_url(null,
                $page, $showall), 'method' => 'post', 'class' => 'questionflagsaveform'));
        $output .= html_writer::start_tag('div');
        $output .= $content;
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
                'value' => sesskey()));
        $output .= html_writer::start_tag('div', array('class' => 'submitbtns'));
        $output .= html_writer::empty_tag('input', array('type' => 'submit',
                'class' => 'questionflagsavebutton', 'name' => 'savingflags',
                'value' => get_string('saveflags', 'question')));
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');

        return $output;
    }

    
    public function finish_review_link(quiz_attempt $attemptobj) {
        $url = $attemptobj->view_url();

        if ($attemptobj->get_access_manager(time())->attempt_must_be_in_popup()) {
            $this->page->requires->js_init_call('M.mod_quiz.secure_window.init_close_button',
                    array($url), false, quiz_get_js_module());
            return html_writer::empty_tag('input', array('type' => 'button',
                    'value' => get_string('finishreview', 'quiz'),
                    'id' => 'secureclosebutton',
                    'class' => 'mod_quiz-next-nav'));

        } else {
            return html_writer::link($url, get_string('finishreview', 'quiz'),
                    array('class' => 'mod_quiz-next-nav'));
        }
    }

    
    public function review_next_navigation(quiz_attempt $attemptobj, $page, $lastpage, $showall = null) {
        $nav = '';
        if ($page > 0) {
            $nav .= link_arrow_left(get_string('navigateprevious', 'quiz'),
                    $attemptobj->review_url(null, $page - 1, $showall), false, 'mod_quiz-prev-nav');
        }
        if ($lastpage) {
            $nav .= $this->finish_review_link($attemptobj);
        } else {
            $nav .= link_arrow_right(get_string('navigatenext', 'quiz'),
                    $attemptobj->review_url(null, $page + 1, $showall), false, 'mod_quiz-next-nav');
        }
        return html_writer::tag('div', $nav, array('class' => 'submitbtns'));
    }

    
    public function countdown_timer(quiz_attempt $attemptobj, $timenow) {

        $timeleft = $attemptobj->get_time_left_display($timenow);
        if ($timeleft !== false) {
            $ispreview = $attemptobj->is_preview();
            $timerstartvalue = $timeleft;
            if (!$ispreview) {
                                                $timerstartvalue = max($timerstartvalue, 1);
            }
            $this->initialise_timer($timerstartvalue, $ispreview);
        }

        return html_writer::tag('div', get_string('timeleft', 'quiz') . ' ' .
                html_writer::tag('span', '', array('id' => 'quiz-time-left')),
                array('id' => 'quiz-timer', 'role' => 'timer',
                    'aria-atomic' => 'true', 'aria-relevant' => 'text'));
    }

    
    public function restart_preview_button($url) {
        return $this->single_button($url, get_string('startnewpreview', 'quiz'));
    }

    
    public function navigation_panel(quiz_nav_panel_base $panel) {

        $output = '';
        $userpicture = $panel->user_picture();
        if ($userpicture) {
            $fullname = fullname($userpicture->user);
            if ($userpicture->size === true) {
                $fullname = html_writer::div($fullname);
            }
            $output .= html_writer::tag('div', $this->render($userpicture) . $fullname,
                    array('id' => 'user-picture', 'class' => 'clearfix'));
        }
        $output .= $panel->render_before_button_bits($this);

        $bcc = $panel->get_button_container_class();
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
        foreach ($panel->get_question_buttons() as $button) {
            $output .= $this->render($button);
        }
        $output .= html_writer::end_tag('div');

        $output .= html_writer::tag('div', $panel->render_end_bits($this),
                array('class' => 'othernav'));

        $this->page->requires->js_init_call('M.mod_quiz.nav.init', null, false,
                quiz_get_js_module());

        return $output;
    }

    
    protected function render_quiz_nav_question_button(quiz_nav_question_button $button) {
        $classes = array('qnbutton', $button->stateclass, $button->navmethod);
        $extrainfo = array();

        if ($button->currentpage) {
            $classes[] = 'thispage';
            $extrainfo[] = get_string('onthispage', 'quiz');
        }

                if ($button->flagged) {
            $classes[] = 'flagged';
            $flaglabel = get_string('flagged', 'question');
        } else {
            $flaglabel = '';
        }
        $extrainfo[] = html_writer::tag('span', $flaglabel, array('class' => 'flagstate'));

        if (is_numeric($button->number)) {
            $qnostring = 'questionnonav';
        } else {
            $qnostring = 'questionnonavinfo';
        }

        $a = new stdClass();
        $a->number = $button->number;
        $a->attributes = implode(' ', $extrainfo);
        $tagcontents = html_writer::tag('span', '', array('class' => 'thispageholder')) .
                        html_writer::tag('span', '', array('class' => 'trafficlight')) .
                        get_string($qnostring, 'quiz', $a);
        $tagattributes = array('class' => implode(' ', $classes), 'id' => $button->id,
                                  'title' => $button->statestring, 'data-quiz-page' => $button->page);

        if ($button->url) {
            return html_writer::link($button->url, $tagcontents, $tagattributes);
        } else {
            return html_writer::tag('span', $tagcontents, $tagattributes);
        }
    }

    
    protected function render_quiz_nav_section_heading(quiz_nav_section_heading $heading) {
        return $this->heading($heading->heading, 3, 'mod_quiz-section-heading');
    }

    
    protected function render_mod_quiz_links_to_other_attempts(
            mod_quiz_links_to_other_attempts $links) {
        $attemptlinks = array();
        foreach ($links->links as $attempt => $url) {
            if (!$url) {
                $attemptlinks[] = html_writer::tag('strong', $attempt);
            } else if ($url instanceof renderable) {
                $attemptlinks[] = $this->render($url);
            } else {
                $attemptlinks[] = html_writer::link($url, $attempt);
            }
        }
        return implode(', ', $attemptlinks);
    }

    public function start_attempt_page(quiz $quizobj, mod_quiz_preflight_check_form $mform) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(format_string($quizobj->get_quiz_name(), true,
                                  array("context" => $quizobj->get_context())));
        $output .= $this->quiz_intro($quizobj->get_quiz(), $quizobj->get_cm());
        $output .= $mform->render();
        $output .= $this->footer();
        return $output;
    }

    
    public function attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id,
            $nextpage) {
        $output = '';
        $output .= $this->header();
        $output .= $this->quiz_notices($messages);
        $output .= $this->attempt_form($attemptobj, $page, $slots, $id, $nextpage);
        $output .= $this->footer();
        return $output;
    }

    
    public function quiz_notices($messages) {
        if (!$messages) {
            return '';
        }
        return $this->box($this->heading(get_string('accessnoticesheader', 'quiz'), 3) .
                $this->access_messages($messages), 'quizaccessnotices');
    }

    
    public function attempt_form($attemptobj, $page, $slots, $id, $nextpage) {
        $output = '';

                $output .= html_writer::start_tag('form',
                array('action' => $attemptobj->processattempt_url(), 'method' => 'post',
                'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8',
                'id' => 'responseform'));
        $output .= html_writer::start_tag('div');

                foreach ($slots as $slot) {
            $output .= $attemptobj->render_question($slot, false, $this,
                    $attemptobj->attempt_url($slot, $page), $this);
        }

        $navmethod = $attemptobj->get_quiz()->navmethod;
        $output .= $this->attempt_navigation_buttons($page, $attemptobj->is_last_page($page), $navmethod);

                $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'attempt',
                'value' => $attemptobj->get_attemptid()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'thispage',
                'value' => $page, 'id' => 'followingpage'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'nextpage',
                'value' => $nextpage));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'timeup',
                'value' => '0', 'id' => 'timeup'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
                'value' => sesskey()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'scrollpos',
                'value' => '', 'id' => 'scrollpos'));

                                $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'slots',
                'value' => implode(',', $attemptobj->get_active_slots($page))));

                $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');

        $output .= $this->connection_warning();

        return $output;
    }

    
    protected function attempt_navigation_buttons($page, $lastpage, $navmethod = 'free') {
        $output = '';

        $output .= html_writer::start_tag('div', array('class' => 'submitbtns'));
        if ($page > 0 && $navmethod == 'free') {
            $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'previous',
                    'value' => get_string('navigateprevious', 'quiz'), 'class' => 'mod_quiz-prev-nav'));
        }
        if ($lastpage) {
            $nextlabel = get_string('endtest', 'quiz');
        } else {
            $nextlabel = get_string('navigatenext', 'quiz');
        }
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next',
                'value' => $nextlabel, 'class' => 'mod_quiz-next-nav'));
        $output .= html_writer::end_tag('div');

        return $output;
    }

    
    public function redo_question_button($slot, $disabled) {
        $attributes = array('type' => 'submit',  'name' => 'redoslot' . $slot,
                'value' => get_string('redoquestion', 'quiz'), 'class' => 'mod_quiz-redo_question_button');
        if ($disabled) {
            $attributes['disabled'] = 'disabled';
        }
        return html_writer::div(html_writer::empty_tag('input', $attributes));
    }

    
    public function initialise_timer($timerstartvalue, $ispreview) {
        $options = array($timerstartvalue, (bool)$ispreview);
        $this->page->requires->js_init_call('M.mod_quiz.timer.init', $options, false, quiz_get_js_module());
    }

    
    public function close_attempt_popup($url, $message = '') {
        $output = '';
        $output .= $this->header();
        $output .= $this->box_start();

        if ($message) {
            $output .= html_writer::tag('p', $message);
            $output .= html_writer::tag('p', get_string('windowclosing', 'quiz'));
            $delay = 5;
        } else {
            $output .= html_writer::tag('p', get_string('pleaseclose', 'quiz'));
            $delay = 0;
        }
        $this->page->requires->js_init_call('M.mod_quiz.secure_window.close',
                array($url, $delay), false, quiz_get_js_module());

        $output .= $this->box_end();
        $output .= $this->footer();
        return $output;
    }

    
    public function access_messages($messages) {
        $output = '';
        foreach ($messages as $message) {
            $output .= html_writer::tag('p', $message) . "\n";
        }
        return $output;
    }

    
    
    public function summary_page($attemptobj, $displayoptions) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        $output .= $this->heading(get_string('summaryofattempt', 'quiz'), 3);
        $output .= $this->summary_table($attemptobj, $displayoptions);
        $output .= $this->summary_page_controls($attemptobj);
        $output .= $this->footer();
        return $output;
    }

    
    public function summary_table($attemptobj, $displayoptions) {
                $table = new html_table();
        $table->attributes['class'] = 'generaltable quizsummaryofattempt boxaligncenter';
        $table->head = array(get_string('question', 'quiz'), get_string('status', 'quiz'));
        $table->align = array('left', 'left');
        $table->size = array('', '');
        $markscolumn = $displayoptions->marks >= question_display_options::MARK_AND_MAX;
        if ($markscolumn) {
            $table->head[] = get_string('marks', 'quiz');
            $table->align[] = 'left';
            $table->size[] = '';
        }
        $tablewidth = count($table->align);
        $table->data = array();

                $slots = $attemptobj->get_slots();
        foreach ($slots as $slot) {
                        $heading = $attemptobj->get_heading_before_slot($slot);
            if ($heading) {
                $cell = new html_table_cell(format_string($heading));
                $cell->header = true;
                $cell->colspan = $tablewidth;
                $table->data[] = array($cell);
                $table->rowclasses[] = 'quizsummaryheading';
            }

                        if (!$attemptobj->is_real_question($slot)) {
                continue;
            }

                        $flag = '';
            if ($attemptobj->is_question_flagged($slot)) {
                $flag = html_writer::empty_tag('img', array('src' => $this->pix_url('i/flagged'),
                        'alt' => get_string('flagged', 'question'), 'class' => 'questionflag icon-post'));
            }
            if ($attemptobj->can_navigate_to($slot)) {
                $row = array(html_writer::link($attemptobj->attempt_url($slot),
                        $attemptobj->get_question_number($slot) . $flag),
                        $attemptobj->get_question_status($slot, $displayoptions->correctness));
            } else {
                $row = array($attemptobj->get_question_number($slot) . $flag,
                                $attemptobj->get_question_status($slot, $displayoptions->correctness));
            }
            if ($markscolumn) {
                $row[] = $attemptobj->get_question_mark($slot);
            }
            $table->data[] = $row;
            $table->rowclasses[] = 'quizsummary' . $slot . ' ' . $attemptobj->get_question_state_class(
                    $slot, $displayoptions->correctness);
        }

                $output = html_writer::table($table);

        return $output;
    }

    
    public function summary_page_controls($attemptobj) {
        $output = '';

                if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button = new single_button(
                    new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
                    get_string('returnattempt', 'quiz'));
            $output .= $this->container($this->container($this->render($button),
                    'controls'), 'submitbtns mdl-align');
        }

                $options = array(
            'attempt' => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup' => 0,
            'slots' => '',
            'sesskey' => sesskey(),
        );

        $button = new single_button(
                new moodle_url($attemptobj->processattempt_url(), $options),
                get_string('submitallandfinish', 'quiz'));
        $button->id = 'responseform';
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button->add_action(new confirm_action(get_string('confirmclose', 'quiz'), null,
                    get_string('submitallandfinish', 'quiz')));
        }

        $duedate = $attemptobj->get_due_date();
        $message = '';
        if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
            $message = get_string('overduemustbesubmittedby', 'quiz', userdate($duedate));

        } else if ($duedate) {
            $message = get_string('mustbesubmittedby', 'quiz', userdate($duedate));
        }

        $output .= $this->countdown_timer($attemptobj, time());
        $output .= $this->container($message . $this->container(
                $this->render($button), 'controls'), 'submitbtns mdl-align');

        return $output;
    }

    
    
    public function view_page($course, $quiz, $cm, $context, $viewobj) {
        $output = '';
        $output .= $this->view_information($quiz, $cm, $context, $viewobj->infomessages);
        $output .= $this->view_table($quiz, $context, $viewobj);
        $output .= $this->view_result_info($quiz, $context, $cm, $viewobj);
        $output .= $this->box($this->view_page_buttons($viewobj), 'quizattempt');
        return $output;
    }

    
    public function view_page_buttons(mod_quiz_view_object $viewobj) {
        global $CFG;
        $output = '';

        if (!$viewobj->quizhasquestions) {
            $output .= $this->no_questions_message($viewobj->canedit, $viewobj->editurl);
        }

        $output .= $this->access_messages($viewobj->preventmessages);

        if ($viewobj->buttontext) {
            $output .= $this->start_attempt_button($viewobj->buttontext,
                    $viewobj->startattempturl, $viewobj->preflightcheckform,
                    $viewobj->popuprequired, $viewobj->popupoptions);
        }

        if ($viewobj->showbacktocourse) {
            $output .= $this->single_button($viewobj->backtocourseurl,
                    get_string('backtocourse', 'quiz'), 'get',
                    array('class' => 'continuebutton'));
        }

        return $output;
    }

    
    public function start_attempt_button($buttontext, moodle_url $url,
            mod_quiz_preflight_check_form $preflightcheckform = null,
            $popuprequired = false, $popupoptions = null) {

        if (is_string($preflightcheckform)) {
                        debugging('The third argument to start_attempt_button should now be the ' .
                    'mod_quiz_preflight_check_form from ' .
                    'quiz_access_manager::get_preflight_check_form, not a warning message string.');
        }

        $button = new single_button($url, $buttontext);
        $button->class .= ' quizstartbuttondiv';

        $popupjsoptions = null;
        if ($popuprequired && $popupoptions) {
            $action = new popup_action('click', $url, 'popup', $popupoptions);
            $popupjsoptions = $action->get_js_options();
        }

        if ($preflightcheckform) {
            $checkform = $preflightcheckform->render();
        } else {
            $checkform = null;
        }

        $this->page->requires->js_call_amd('mod_quiz/preflightcheck', 'init',
                array('.quizstartbuttondiv input[type=submit]', get_string('startattempt', 'quiz'),
                       '#mod_quiz_preflight_form', $popupjsoptions));

        return $this->render($button) . $checkform;
    }

    
    public function no_questions_message($canedit, $editurl) {
        $output = '';
        $output .= $this->notification(get_string('noquestions', 'quiz'));
        if ($canedit) {
            $output .= $this->single_button($editurl, get_string('editquiz', 'quiz'), 'get');
        }

        return $output;
    }

    
    public function view_page_guest($course, $quiz, $cm, $context, $messages) {
        $output = '';
        $output .= $this->view_information($quiz, $cm, $context, $messages);
        $guestno = html_writer::tag('p', get_string('guestsno', 'quiz'));
        $liketologin = html_writer::tag('p', get_string('liketologin'));
        $referer = get_local_referer(false);
        $output .= $this->confirm($guestno."\n\n".$liketologin."\n", get_login_url(), $referer);
        return $output;
    }

    
    public function view_page_notenrolled($course, $quiz, $cm, $context, $messages) {
        global $CFG;
        $output = '';
        $output .= $this->view_information($quiz, $cm, $context, $messages);
        $youneedtoenrol = html_writer::tag('p', get_string('youneedtoenrol', 'quiz'));
        $button = html_writer::tag('p',
                $this->continue_button($CFG->wwwroot . '/course/view.php?id=' . $course->id));
        $output .= $this->box($youneedtoenrol."\n\n".$button."\n", 'generalbox', 'notice');
        return $output;
    }

    
    public function view_information($quiz, $cm, $context, $messages) {
        global $CFG;

        $output = '';

                $output .= $this->heading(format_string($quiz->name));
        $output .= $this->quiz_intro($quiz, $cm);

                if ($messages) {
            $output .= $this->box($this->access_messages($messages), 'quizinfo');
        }

                if (has_capability('mod/quiz:viewreports', $context)) {
            if ($strattemptnum = $this->quiz_attempt_summary_link_to_reports($quiz, $cm,
                    $context)) {
                $output .= html_writer::tag('div', $strattemptnum,
                        array('class' => 'quizattemptcounts'));
            }
        }
        return $output;
    }

    
    public function quiz_intro($quiz, $cm) {
        if (html_is_blank($quiz->intro)) {
            return '';
        }

        return $this->box(format_module_intro('quiz', $quiz, $cm->id), 'generalbox', 'intro');
    }

    
    public function view_table_heading() {
        return $this->heading(get_string('summaryofattempts', 'quiz'), 3);
    }

    
    public function view_table($quiz, $context, $viewobj) {
        if (!$viewobj->attempts) {
            return '';
        }

                $table = new html_table();
        $table->attributes['class'] = 'generaltable quizattemptsummary';
        $table->head = array();
        $table->align = array();
        $table->size = array();
        if ($viewobj->attemptcolumn) {
            $table->head[] = get_string('attemptnumber', 'quiz');
            $table->align[] = 'center';
            $table->size[] = '';
        }
        $table->head[] = get_string('attemptstate', 'quiz');
        $table->align[] = 'left';
        $table->size[] = '';
        if ($viewobj->markcolumn) {
            $table->head[] = get_string('marks', 'quiz') . ' / ' .
                    quiz_format_grade($quiz, $quiz->sumgrades);
            $table->align[] = 'center';
            $table->size[] = '';
        }
        if ($viewobj->gradecolumn) {
            $table->head[] = get_string('grade') . ' / ' .
                    quiz_format_grade($quiz, $quiz->grade);
            $table->align[] = 'center';
            $table->size[] = '';
        }
        if ($viewobj->canreviewmine) {
            $table->head[] = get_string('review', 'quiz');
            $table->align[] = 'center';
            $table->size[] = '';
        }
        if ($viewobj->feedbackcolumn) {
            $table->head[] = get_string('feedback', 'quiz');
            $table->align[] = 'left';
            $table->size[] = '';
        }

                foreach ($viewobj->attemptobjs as $attemptobj) {
            $attemptoptions = $attemptobj->get_display_options(true);
            $row = array();

                        if ($viewobj->attemptcolumn) {
                if ($attemptobj->is_preview()) {
                    $row[] = get_string('preview', 'quiz');
                } else {
                    $row[] = $attemptobj->get_attempt_number();
                }
            }

            $row[] = $this->attempt_state($attemptobj);

            if ($viewobj->markcolumn) {
                if ($attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                        $attemptobj->is_finished()) {
                    $row[] = quiz_format_grade($quiz, $attemptobj->get_sum_marks());
                } else {
                    $row[] = '';
                }
            }

                        $attemptgrade = quiz_rescale_grade($attemptobj->get_sum_marks(), $quiz, false);

            if ($viewobj->gradecolumn) {
                if ($attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                        $attemptobj->is_finished()) {

                                        if ($viewobj->overallstats && !$attemptobj->is_preview()
                            && $viewobj->numattempts > 1 && !is_null($viewobj->mygrade)
                            && $attemptobj->get_state() == quiz_attempt::FINISHED
                            && $attemptgrade == $viewobj->mygrade
                            && $quiz->grademethod == QUIZ_GRADEHIGHEST) {
                        $table->rowclasses[$attemptobj->get_attempt_number()] = 'bestrow';
                    }

                    $row[] = quiz_format_grade($quiz, $attemptgrade);
                } else {
                    $row[] = '';
                }
            }

            if ($viewobj->canreviewmine) {
                $row[] = $viewobj->accessmanager->make_review_link($attemptobj->get_attempt(),
                        $attemptoptions, $this);
            }

            if ($viewobj->feedbackcolumn && $attemptobj->is_finished()) {
                if ($attemptoptions->overallfeedback) {
                    $row[] = quiz_feedback_for_grade($attemptgrade, $quiz, $context);
                } else {
                    $row[] = '';
                }
            }

            if ($attemptobj->is_preview()) {
                $table->data['preview'] = $row;
            } else {
                $table->data[$attemptobj->get_attempt_number()] = $row;
            }
        } 
        $output = '';
        $output .= $this->view_table_heading();
        $output .= html_writer::table($table);
        return $output;
    }

    
    public function attempt_state($attemptobj) {
        switch ($attemptobj->get_state()) {
            case quiz_attempt::IN_PROGRESS:
                return get_string('stateinprogress', 'quiz');

            case quiz_attempt::OVERDUE:
                return get_string('stateoverdue', 'quiz') . html_writer::tag('span',
                        get_string('stateoverduedetails', 'quiz',
                                userdate($attemptobj->get_due_date())),
                        array('class' => 'statedetails'));

            case quiz_attempt::FINISHED:
                return get_string('statefinished', 'quiz') . html_writer::tag('span',
                        get_string('statefinisheddetails', 'quiz',
                                userdate($attemptobj->get_submitted_date())),
                        array('class' => 'statedetails'));

            case quiz_attempt::ABANDONED:
                return get_string('stateabandoned', 'quiz');
        }
    }

    
    public function view_result_info($quiz, $context, $cm, $viewobj) {
        $output = '';
        if (!$viewobj->numattempts && !$viewobj->gradecolumn && is_null($viewobj->mygrade)) {
            return $output;
        }
        $resultinfo = '';

        if ($viewobj->overallstats) {
            if ($viewobj->moreattempts) {
                $a = new stdClass();
                $a->method = quiz_get_grading_option_name($quiz->grademethod);
                $a->mygrade = quiz_format_grade($quiz, $viewobj->mygrade);
                $a->quizgrade = quiz_format_grade($quiz, $quiz->grade);
                $resultinfo .= $this->heading(get_string('gradesofar', 'quiz', $a), 3);
            } else {
                $a = new stdClass();
                $a->grade = quiz_format_grade($quiz, $viewobj->mygrade);
                $a->maxgrade = quiz_format_grade($quiz, $quiz->grade);
                $a = get_string('outofshort', 'quiz', $a);
                $resultinfo .= $this->heading(get_string('yourfinalgradeis', 'quiz', $a), 3);
            }
        }

        if ($viewobj->mygradeoverridden) {

            $resultinfo .= html_writer::tag('p', get_string('overriddennotice', 'grades'),
                    array('class' => 'overriddennotice'))."\n";
        }
        if ($viewobj->gradebookfeedback) {
            $resultinfo .= $this->heading(get_string('comment', 'quiz'), 3);
            $resultinfo .= html_writer::div($viewobj->gradebookfeedback, 'quizteacherfeedback') . "\n";
        }
        if ($viewobj->feedbackcolumn) {
            $resultinfo .= $this->heading(get_string('overallfeedback', 'quiz'), 3);
            $resultinfo .= html_writer::div(
                    quiz_feedback_for_grade($viewobj->mygrade, $quiz, $context),
                    'quizgradefeedback') . "\n";
        }

        if ($resultinfo) {
            $output .= $this->box($resultinfo, 'generalbox', 'feedback');
        }
        return $output;
    }

    
    public function review_link($url, $reviewinpopup, $popupoptions) {
        if ($reviewinpopup) {
            $button = new single_button($url, get_string('review', 'quiz'));
            $button->add_action(new popup_action('click', $url, 'quizpopup', $popupoptions));
            return $this->render($button);

        } else {
            return html_writer::link($url, get_string('review', 'quiz'),
                    array('title' => get_string('reviewthisattempt', 'quiz')));
        }
    }

    
    public function no_review_message($message) {
        return html_writer::nonempty_tag('span', $message,
                array('class' => 'noreviewmessage'));
    }

    
    public function quiz_attempt_summary_link_to_reports($quiz, $cm, $context,
                                                          $returnzero = false, $currentgroup = 0) {
        global $CFG;
        $summary = quiz_num_attempt_summary($quiz, $cm, $returnzero, $currentgroup);
        if (!$summary) {
            return '';
        }

        require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
        $url = new moodle_url('/mod/quiz/report.php', array(
                'id' => $cm->id, 'mode' => quiz_report_default_report($context)));
        return html_writer::link($url, $summary);
    }

    
    public function graph(moodle_url $url, $title) {
        global $CFG;

        $graph = html_writer::empty_tag('img', array('src' => $url, 'alt' => $title));

        return $this->heading($title, 3) . html_writer::tag('div', $graph, array('class' => 'graph'));
    }

    
    public function connection_warning() {
        $options = array('filter' => false, 'newlines' => false);
        $warning = format_text(get_string('connectionerror', 'quiz'), FORMAT_MARKDOWN, $options);
        $ok = format_text(get_string('connectionok', 'quiz'), FORMAT_MARKDOWN, $options);
        return html_writer::tag('div', $warning,
                    array('id' => 'connection-error', 'style' => 'display: none;', 'role' => 'alert')) .
                    html_writer::tag('div', $ok, array('id' => 'connection-ok', 'style' => 'display: none;', 'role' => 'alert'));
    }
}


class mod_quiz_links_to_other_attempts implements renderable {
    
    public $links = array();
}


class mod_quiz_view_object {
    
    public $infomessages;
    
    public $attempts;
    
    public $attemptobjs;
    
    public $accessmanager;
    
    public $canreviewmine;
    
    public $canedit;
    
    public $editurl;
    
    public $attemptcolumn;
    
    public $gradecolumn;
    
    public $markcolumn;
    
    public $overallstats;
    
    public $feedbackcolumn;
    
    public $timenow;
    
    public $numattempts;
    
    public $mygrade;
    
    public $moreattempts;
    
    public $mygradeoverridden;
    
    public $gradebookfeedback;
    
    public $unfinished;
    
    public $lastfinishedattempt;
    
    public $preventmessages;
    
    public $buttontext;
    
    public $startattempturl;
    
    public $preflightcheckform;
    
    public $backtocourseurl;
    
    public $showbacktocourse;
    
    public $popuprequired;
    
    public $popupoptions;
    
    public $quizhasquestions;

    public function __get($field) {
        switch ($field) {
            case 'startattemptwarning':
                debugging('startattemptwarning has been deprecated. It is now always blank.');
                return '';

            default:
                debugging('Unknown property ' . $field);
                return null;
        }
    }
}
