<?php




defined('MOODLE_INTERNAL') || die();


class mod_workshop_renderer extends plugin_renderer_base {

            
    
    protected function render_workshop_message(workshop_message $message) {

        $text   = $message->get_message();
        $url    = $message->get_action_url();
        $label  = $message->get_action_label();

        if (empty($text) and empty($label)) {
            return '';
        }

        switch ($message->get_type()) {
        case workshop_message::TYPE_OK:
            $sty = 'ok';
            break;
        case workshop_message::TYPE_ERROR:
            $sty = 'error';
            break;
        default:
            $sty = 'info';
        }

        $o = html_writer::tag('span', $message->get_message());

        if (!is_null($url) and !is_null($label)) {
            $o .= $this->output->single_button($url, $label, 'get');
        }

        return $this->output->container($o, array('message', $sty));
    }


    
    protected function render_workshop_submission(workshop_submission $submission) {
        global $CFG;

        $o  = '';            $anonymous = $submission->is_anonymous();
        $classes = 'submission-full';
        if ($anonymous) {
            $classes .= ' anonymous';
        }
        $o .= $this->output->container_start($classes);
        $o .= $this->output->container_start('header');

        $title = format_string($submission->title);

        if ($this->page->url != $submission->url) {
            $title = html_writer::link($submission->url, $title);
        }

        $o .= $this->output->heading($title, 3, 'title');

        if (!$anonymous) {
            $author = new stdclass();
            $additionalfields = explode(',', user_picture::fields());
            $author = username_load_fields_from_object($author, $submission, 'author', $additionalfields);
            $userpic            = $this->output->user_picture($author, array('courseid' => $this->page->course->id, 'size' => 64));
            $userurl            = new moodle_url('/user/view.php',
                                            array('id' => $author->id, 'course' => $this->page->course->id));
            $a                  = new stdclass();
            $a->name            = fullname($author);
            $a->url             = $userurl->out();
            $byfullname         = get_string('byfullname', 'workshop', $a);
            $oo  = $this->output->container($userpic, 'picture');
            $oo .= $this->output->container($byfullname, 'fullname');

            $o .= $this->output->container($oo, 'author');
        }

        $created = get_string('userdatecreated', 'workshop', userdate($submission->timecreated));
        $o .= $this->output->container($created, 'userdate created');

        if ($submission->timemodified > $submission->timecreated) {
            $modified = get_string('userdatemodified', 'workshop', userdate($submission->timemodified));
            $o .= $this->output->container($modified, 'userdate modified');
        }

        $o .= $this->output->container_end(); 
        $content = file_rewrite_pluginfile_urls($submission->content, 'pluginfile.php', $this->page->context->id,
                                                        'mod_workshop', 'submission_content', $submission->id);
        $content = format_text($content, $submission->contentformat, array('overflowdiv'=>true));
        if (!empty($content)) {
            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir.'/plagiarismlib.php');
                $content .= plagiarism_get_links(array('userid' => $submission->authorid,
                    'content' => $submission->content,
                    'cmid' => $this->page->cm->id,
                    'course' => $this->page->course));
            }
        }
        $o .= $this->output->container($content, 'content');

        $o .= $this->helper_submission_attachments($submission->id, 'html');

        $o .= $this->output->container_end(); 
        return $o;
    }

    
    protected function render_workshop_submission_summary(workshop_submission_summary $summary) {

        $o  = '';            $anonymous = $summary->is_anonymous();
        $classes = 'submission-summary';

        if ($anonymous) {
            $classes .= ' anonymous';
        }

        $gradestatus = '';

        if ($summary->status == 'notgraded') {
            $classes    .= ' notgraded';
            $gradestatus = $this->output->container(get_string('nogradeyet', 'workshop'), 'grade-status');

        } else if ($summary->status == 'graded') {
            $classes    .= ' graded';
            $gradestatus = $this->output->container(get_string('alreadygraded', 'workshop'), 'grade-status');
        }

        $o .= $this->output->container_start($classes);          $o .= html_writer::link($summary->url, format_string($summary->title), array('class' => 'title'));

        if (!$anonymous) {
            $author             = new stdClass();
            $additionalfields = explode(',', user_picture::fields());
            $author = username_load_fields_from_object($author, $summary, 'author', $additionalfields);
            $userpic            = $this->output->user_picture($author, array('courseid' => $this->page->course->id, 'size' => 35));
            $userurl            = new moodle_url('/user/view.php',
                                            array('id' => $author->id, 'course' => $this->page->course->id));
            $a                  = new stdClass();
            $a->name            = fullname($author);
            $a->url             = $userurl->out();
            $byfullname         = get_string('byfullname', 'workshop', $a);

            $oo  = $this->output->container($userpic, 'picture');
            $oo .= $this->output->container($byfullname, 'fullname');
            $o  .= $this->output->container($oo, 'author');
        }

        $created = get_string('userdatecreated', 'workshop', userdate($summary->timecreated));
        $o .= $this->output->container($created, 'userdate created');

        if ($summary->timemodified > $summary->timecreated) {
            $modified = get_string('userdatemodified', 'workshop', userdate($summary->timemodified));
            $o .= $this->output->container($modified, 'userdate modified');
        }

        $o .= $gradestatus;
        $o .= $this->output->container_end();         return $o;
    }

    
    protected function render_workshop_example_submission(workshop_example_submission $example) {

        $o  = '';            $classes = 'submission-full example';
        $o .= $this->output->container_start($classes);
        $o .= $this->output->container_start('header');
        $o .= $this->output->container(format_string($example->title), array('class' => 'title'));
        $o .= $this->output->container_end(); 
        $content = file_rewrite_pluginfile_urls($example->content, 'pluginfile.php', $this->page->context->id,
                                                        'mod_workshop', 'submission_content', $example->id);
        $content = format_text($content, $example->contentformat, array('overflowdiv'=>true));
        $o .= $this->output->container($content, 'content');

        $o .= $this->helper_submission_attachments($example->id, 'html');

        $o .= $this->output->container_end(); 
        return $o;
    }

    
    protected function render_workshop_example_submission_summary(workshop_example_submission_summary $summary) {

        $o  = '';    
                $o .= $this->output->box_start('generalbox example-summary ' . $summary->status);

                $o .= $this->output->container_start('example-title');
        $o .= html_writer::link($summary->url, format_string($summary->title), array('class' => 'title'));

        if ($summary->editable) {
            $o .= $this->output->action_icon($summary->editurl, new pix_icon('i/edit', get_string('edit')));
        }
        $o .= $this->output->container_end();

                if ($summary->status == 'notgraded') {
            $o .= $this->output->container(get_string('nogradeyet', 'workshop'), 'example-info nograde');
        } else {
            $o .= $this->output->container(get_string('gradeinfo', 'workshop' , $summary->gradeinfo), 'example-info grade');
        }

                $button = new single_button($summary->assessurl, $summary->assesslabel, 'get');
        $o .= $this->output->container($this->output->render($button), 'example-actions');

                $o .= $this->output->box_end();

        return $o;
    }

    
    protected function render_workshop_user_plan(workshop_user_plan $plan) {
        $table = new html_table();
        $table->attributes['class'] = 'userplan';
        $table->attributes['role'] = 'section';
        $numberofphases = count($plan->phases);
        $table->attributes['aria-label'] = get_string('userplanaccessibilitytitle', 'workshop', $numberofphases);
        $table->head = array();
        $table->colclasses = array();
        $row = new html_table_row();
        $row->attributes['class'] = 'phasetasks';
        foreach ($plan->phases as $phasecode => $phase) {
            $title = html_writer::tag('span', $phase->title);
            if ($phase->active) {
                $title .= ' ' . html_writer::tag('span', get_string('userplancurrentphase', 'workshop'),
                    array('class' => 'accesshide'));
            }
            $actions = '';
            foreach ($phase->actions as $action) {
                switch ($action->type) {
                case 'switchphase':
                    $icon = 'i/marker';
                    if ($phasecode == workshop::PHASE_ASSESSMENT
                            and $plan->workshop->phase == workshop::PHASE_SUBMISSION
                            and $plan->workshop->phaseswitchassessment) {
                        $icon = 'i/scheduled';
                    }
                    $actions .= $this->output->action_icon($action->url, new pix_icon($icon, get_string('switchphase', 'workshop')));
                    break;
                }
            }
            if (!empty($actions)) {
                $actions = $this->output->container($actions, 'actions');
            }
            $table->head[] = $this->output->container($title . $actions);
            $classes = 'phase' . $phasecode;
            if ($phase->active) {
                $classes .= ' active';
            } else {
                $classes .= ' nonactive';
            }
            $table->colclasses[] = $classes;
            $cell = new html_table_cell();
            $cell->text = $this->helper_user_plan_tasks($phase->tasks);
            $row->cells[] = $cell;
        }
        $table->data = array($row);

        return html_writer::table($table);
    }

    
    protected function render_workshop_allocation_result(workshop_allocation_result $result) {
        global $CFG;

        $status = $result->get_status();

        if (is_null($status) or $status == workshop_allocation_result::STATUS_VOID) {
            debugging('Attempt to render workshop_allocation_result with empty status', DEBUG_DEVELOPER);
            return '';
        }

        switch ($status) {
        case workshop_allocation_result::STATUS_FAILED:
            if ($message = $result->get_message()) {
                $message = new workshop_message($message, workshop_message::TYPE_ERROR);
            } else {
                $message = new workshop_message(get_string('allocationerror', 'workshop'), workshop_message::TYPE_ERROR);
            }
            break;

        case workshop_allocation_result::STATUS_CONFIGURED:
            if ($message = $result->get_message()) {
                $message = new workshop_message($message, workshop_message::TYPE_INFO);
            } else {
                $message = new workshop_message(get_string('allocationconfigured', 'workshop'), workshop_message::TYPE_INFO);
            }
            break;

        case workshop_allocation_result::STATUS_EXECUTED:
            if ($message = $result->get_message()) {
                $message = new workshop_message($message, workshop_message::TYPE_OK);
            } else {
                $message = new workshop_message(get_string('allocationdone', 'workshop'), workshop_message::TYPE_OK);
            }
            break;

        default:
            throw new coding_exception('Unknown allocation result status', $status);
        }

                $o = $this->render($message);

                $logs = $result->get_logs();
        if (is_array($logs) and !empty($logs)) {
            $o .= html_writer::start_tag('ul', array('class' => 'allocation-init-results'));
            foreach ($logs as $log) {
                if ($log->type == 'debug' and !$CFG->debugdeveloper) {
                                        continue;
                }
                $class = $log->type;
                if ($log->indent) {
                    $class .= ' indent';
                }
                $o .= html_writer::tag('li', $log->message, array('class' => $class)).PHP_EOL;
            }
            $o .= html_writer::end_tag('ul');
        }

        return $o;
    }

    
    protected function render_workshop_grading_report(workshop_grading_report $gradingreport) {

        $data       = $gradingreport->get_data();
        $options    = $gradingreport->get_options();
        $grades     = $data->grades;
        $userinfo   = $data->userinfo;

        if (empty($grades)) {
            return '';
        }

        $table = new html_table();
        $table->attributes['class'] = 'grading-report';

        $sortbyfirstname = $this->helper_sortable_heading(get_string('firstname'), 'firstname', $options->sortby, $options->sorthow);
        $sortbylastname = $this->helper_sortable_heading(get_string('lastname'), 'lastname', $options->sortby, $options->sorthow);
        if (self::fullname_format() == 'lf') {
            $sortbyname = $sortbylastname . ' / ' . $sortbyfirstname;
        } else {
            $sortbyname = $sortbyfirstname . ' / ' . $sortbylastname;
        }

        $sortbysubmisstiontitle = $this->helper_sortable_heading(get_string('submission', 'workshop'), 'submissiontitle',
                $options->sortby, $options->sorthow);
        $sortbysubmisstionlastmodified = $this->helper_sortable_heading(get_string('submissionlastmodified', 'workshop'),
                'submissionmodified', $options->sortby, $options->sorthow);
        $sortbysubmisstion = $sortbysubmisstiontitle . ' / ' . $sortbysubmisstionlastmodified;

        $table->head = array();
        $table->head[] = $sortbyname;
        $table->head[] = $sortbysubmisstion;

                if ($options->workshopphase != workshop::PHASE_SUBMISSION) {
            $table->head[] = $this->helper_sortable_heading(get_string('receivedgrades', 'workshop'));
            if ($options->showsubmissiongrade) {
                $table->head[] = $this->helper_sortable_heading(get_string('submissiongradeof', 'workshop', $data->maxgrade),
                        'submissiongrade', $options->sortby, $options->sorthow);
            }
            $table->head[] = $this->helper_sortable_heading(get_string('givengrades', 'workshop'));
            if ($options->showgradinggrade) {
                $table->head[] = $this->helper_sortable_heading(get_string('gradinggradeof', 'workshop', $data->maxgradinggrade),
                        'gradinggrade', $options->sortby, $options->sorthow);
            }
        }
        $table->rowclasses  = array();
        $table->colclasses  = array();
        $table->data        = array();

        foreach ($grades as $participant) {
            $numofreceived  = count($participant->reviewedby);
            $numofgiven     = count($participant->reviewerof);
            $published      = $participant->submissionpublished;

                        if ($numofreceived > 0 and $numofgiven > 0) {
                $numoftrs       = workshop::lcm($numofreceived, $numofgiven);
                $spanreceived   = $numoftrs / $numofreceived;
                $spangiven      = $numoftrs / $numofgiven;
            } elseif ($numofreceived == 0 and $numofgiven > 0) {
                $numoftrs       = $numofgiven;
                $spanreceived   = $numoftrs;
                $spangiven      = $numoftrs / $numofgiven;
            } elseif ($numofreceived > 0 and $numofgiven == 0) {
                $numoftrs       = $numofreceived;
                $spanreceived   = $numoftrs / $numofreceived;
                $spangiven      = $numoftrs;
            } else {
                $numoftrs       = 1;
                $spanreceived   = 1;
                $spangiven      = 1;
            }

            for ($tr = 0; $tr < $numoftrs; $tr++) {
                $row = new html_table_row();
                if ($published) {
                    $row->attributes['class'] = 'published';
                }
                                if ($tr == 0) {
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_participant($participant, $userinfo);
                    $cell->rowspan = $numoftrs;
                    $cell->attributes['class'] = 'participant';
                    $row->cells[] = $cell;
                }
                                if ($tr == 0) {
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_submission($participant);
                    $cell->rowspan = $numoftrs;
                    $cell->attributes['class'] = 'submission';
                    $row->cells[] = $cell;
                }

                                if ($options->workshopphase == workshop::PHASE_SUBMISSION) {
                    $table->data[] = $row;
                    continue;
                }

                                if ($tr % $spanreceived == 0) {
                    $idx = intval($tr / $spanreceived);
                    $assessment = self::array_nth($participant->reviewedby, $idx);
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_assessment($assessment, $options->showreviewernames, $userinfo,
                            get_string('gradereceivedfrom', 'workshop'));
                    $cell->rowspan = $spanreceived;
                    $cell->attributes['class'] = 'receivedgrade';
                    if (is_null($assessment) or is_null($assessment->grade)) {
                        $cell->attributes['class'] .= ' null';
                    } else {
                        $cell->attributes['class'] .= ' notnull';
                    }
                    $row->cells[] = $cell;
                }
                                if ($options->showsubmissiongrade and $tr == 0) {
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_grade($participant->submissiongrade, $participant->submissiongradeover);
                    $cell->rowspan = $numoftrs;
                    $cell->attributes['class'] = 'submissiongrade';
                    $row->cells[] = $cell;
                }
                                if ($tr % $spangiven == 0) {
                    $idx = intval($tr / $spangiven);
                    $assessment = self::array_nth($participant->reviewerof, $idx);
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_assessment($assessment, $options->showauthornames, $userinfo,
                            get_string('gradegivento', 'workshop'));
                    $cell->rowspan = $spangiven;
                    $cell->attributes['class'] = 'givengrade';
                    if (is_null($assessment) or is_null($assessment->grade)) {
                        $cell->attributes['class'] .= ' null';
                    } else {
                        $cell->attributes['class'] .= ' notnull';
                    }
                    $row->cells[] = $cell;
                }
                                if ($options->showgradinggrade and $tr == 0) {
                    $cell = new html_table_cell();
                    $cell->text = $this->helper_grading_report_grade($participant->gradinggrade);
                    $cell->rowspan = $numoftrs;
                    $cell->attributes['class'] = 'gradinggrade';
                    $row->cells[] = $cell;
                }

                $table->data[] = $row;
            }
        }

        return html_writer::table($table);
    }

    
    protected function render_workshop_feedback_author(workshop_feedback_author $feedback) {
        return $this->helper_render_feedback($feedback);
    }

    
    protected function render_workshop_feedback_reviewer(workshop_feedback_reviewer $feedback) {
        return $this->helper_render_feedback($feedback);
    }

    
    private function helper_render_feedback($feedback) {

        $o  = '';            $o .= $this->output->container_start('feedback feedbackforauthor');
        $o .= $this->output->container_start('header');
        $o .= $this->output->heading(get_string('feedbackby', 'workshop', s(fullname($feedback->get_provider()))), 3, 'title');

        $userpic = $this->output->user_picture($feedback->get_provider(), array('courseid' => $this->page->course->id, 'size' => 32));
        $o .= $this->output->container($userpic, 'picture');
        $o .= $this->output->container_end(); 
        $content = format_text($feedback->get_content(), $feedback->get_format(), array('overflowdiv' => true));
        $o .= $this->output->container($content, 'content');

        $o .= $this->output->container_end();

        return $o;
    }

    
    protected function render_workshop_assessment(workshop_assessment $assessment) {

        $o = '';         $anonymous = is_null($assessment->reviewer);
        $classes = 'assessment-full';
        if ($anonymous) {
            $classes .= ' anonymous';
        }

        $o .= $this->output->container_start($classes);
        $o .= $this->output->container_start('header');

        if (!empty($assessment->title)) {
            $title = s($assessment->title);
        } else {
            $title = get_string('assessment', 'workshop');
        }
        if (($assessment->url instanceof moodle_url) and ($this->page->url != $assessment->url)) {
            $o .= $this->output->container(html_writer::link($assessment->url, $title), 'title');
        } else {
            $o .= $this->output->container($title, 'title');
        }

        if (!$anonymous) {
            $reviewer   = $assessment->reviewer;
            $userpic    = $this->output->user_picture($reviewer, array('courseid' => $this->page->course->id, 'size' => 32));

            $userurl    = new moodle_url('/user/view.php',
                                       array('id' => $reviewer->id, 'course' => $this->page->course->id));
            $a          = new stdClass();
            $a->name    = fullname($reviewer);
            $a->url     = $userurl->out();
            $byfullname = get_string('assessmentby', 'workshop', $a);
            $oo         = $this->output->container($userpic, 'picture');
            $oo        .= $this->output->container($byfullname, 'fullname');

            $o .= $this->output->container($oo, 'reviewer');
        }

        if (is_null($assessment->realgrade)) {
            $o .= $this->output->container(
                get_string('notassessed', 'workshop'),
                'grade nograde'
            );
        } else {
            $a              = new stdClass();
            $a->max         = $assessment->maxgrade;
            $a->received    = $assessment->realgrade;
            $o .= $this->output->container(
                get_string('gradeinfo', 'workshop', $a),
                'grade'
            );

            if (!is_null($assessment->weight) and $assessment->weight != 1) {
                $o .= $this->output->container(
                    get_string('weightinfo', 'workshop', $assessment->weight),
                    'weight'
                );
            }
        }

        $o .= $this->output->container_start('actions');
        foreach ($assessment->actions as $action) {
            $o .= $this->output->single_button($action->url, $action->label, $action->method);
        }
        $o .= $this->output->container_end(); 
        $o .= $this->output->container_end(); 
        if (!is_null($assessment->form)) {
            $o .= print_collapsible_region_start('assessment-form-wrapper', uniqid('workshop-assessment'),
                    get_string('assessmentform', 'workshop'), '', false, true);
            $o .= $this->output->container(self::moodleform($assessment->form), 'assessment-form');
            $o .= print_collapsible_region_end(true);

            if (!$assessment->form->is_editable()) {
                $o .= $this->overall_feedback($assessment);
            }
        }

        $o .= $this->output->container_end(); 
        return $o;
    }

    
    protected function render_workshop_example_assessment(workshop_example_assessment $assessment) {
        return $this->render_workshop_assessment($assessment);
    }

    
    protected function render_workshop_example_reference_assessment(workshop_example_reference_assessment $assessment) {
        return $this->render_workshop_assessment($assessment);
    }

    
    protected function overall_feedback(workshop_assessment $assessment) {

        $content = $assessment->get_overall_feedback_content();

        if ($content === false) {
            return '';
        }

        $o = '';

        if (!is_null($content)) {
            $o .= $this->output->container($content, 'content');
        }

        $attachments = $assessment->get_overall_feedback_attachments();

        if (!empty($attachments)) {
            $o .= $this->output->container_start('attachments');
            $images = '';
            $files = '';
            foreach ($attachments as $attachment) {
                $icon = $this->output->pix_icon(file_file_icon($attachment), get_mimetype_description($attachment),
                    'moodle', array('class' => 'icon'));
                $link = html_writer::link($attachment->fileurl, $icon.' '.substr($attachment->filepath.$attachment->filename, 1));
                if (file_mimetype_in_typegroup($attachment->mimetype, 'web_image')) {
                    $preview = html_writer::empty_tag('img', array('src' => $attachment->previewurl, 'alt' => '', 'class' => 'preview'));
                    $preview = html_writer::tag('a', $preview, array('href' => $attachment->fileurl));
                    $images .= $this->output->container($preview);
                } else {
                    $files .= html_writer::tag('li', $link, array('class' => $attachment->mimetype));
                }
            }
            if ($images) {
                $images = $this->output->container($images, 'images');
            }

            if ($files) {
                $files = html_writer::tag('ul', $files, array('class' => 'files'));
            }

            $o .= $images.$files;
            $o .= $this->output->container_end();
        }

        if ($o === '') {
            return '';
        }

        $o = $this->output->box($o, 'overallfeedback');
        $o = print_collapsible_region($o, 'overall-feedback-wrapper', uniqid('workshop-overall-feedback'),
            get_string('overallfeedback', 'workshop'), '', false, true);

        return $o;
    }

    
    public function perpage_selector($current=10) {

        $options = array();
        foreach (array(10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 400, 500, 1000) as $option) {
            if ($option != $current) {
                $options[$option] = $option;
            }
        }
        $select = new single_select($this->page->url, 'perpage', $options, '', array('' => get_string('showingperpagechange', 'mod_workshop')));
        $select->label = get_string('showingperpage', 'mod_workshop', $current);
        $select->method = 'post';

        return $this->output->container($this->output->render($select), 'perpagewidget');
    }

    
    protected function render_workshop_final_grades(workshop_final_grades $grades) {

        $out = html_writer::start_tag('div', array('class' => 'finalgrades'));

        if (!empty($grades->submissiongrade)) {
            $cssclass = 'grade submissiongrade';
            if ($grades->submissiongrade->hidden) {
                $cssclass .= ' hiddengrade';
            }
            $out .= html_writer::tag(
                'div',
                html_writer::tag('div', get_string('submissiongrade', 'mod_workshop'), array('class' => 'gradetype')) .
                html_writer::tag('div', $grades->submissiongrade->str_long_grade, array('class' => 'gradevalue')),
                array('class' => $cssclass)
            );
        }

        if (!empty($grades->assessmentgrade)) {
            $cssclass = 'grade assessmentgrade';
            if ($grades->assessmentgrade->hidden) {
                $cssclass .= ' hiddengrade';
            }
            $out .= html_writer::tag(
                'div',
                html_writer::tag('div', get_string('gradinggrade', 'mod_workshop'), array('class' => 'gradetype')) .
                html_writer::tag('div', $grades->assessmentgrade->str_long_grade, array('class' => 'gradevalue')),
                array('class' => $cssclass)
            );
        }

        $out .= html_writer::end_tag('div');

        return $out;
    }

            
    
    protected function helper_submission_attachments($submissionid, $format = 'html') {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $fs     = get_file_storage();
        $ctx    = $this->page->context;
        $files  = $fs->get_area_files($ctx->id, 'mod_workshop', 'submission_attachment', $submissionid);

        $outputimgs     = '';           $outputfiles    = '';   
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }

            $filepath   = $file->get_filepath();
            $filename   = $file->get_filename();
            $fileurl    = moodle_url::make_pluginfile_url($ctx->id, 'mod_workshop', 'submission_attachment',
                            $submissionid, $filepath, $filename, true);
            $embedurl   = moodle_url::make_pluginfile_url($ctx->id, 'mod_workshop', 'submission_attachment',
                            $submissionid, $filepath, $filename, false);
            $embedurl   = new moodle_url($embedurl, array('preview' => 'bigthumb'));
            $type       = $file->get_mimetype();
            $image      = $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file), 'moodle', array('class' => 'icon'));

            $linkhtml   = html_writer::link($fileurl, $image . substr($filepath, 1) . $filename);
            $linktxt    = "$filename [$fileurl]";

            if ($format == 'html') {
                if (file_mimetype_in_typegroup($type, 'web_image')) {
                    $preview     = html_writer::empty_tag('img', array('src' => $embedurl, 'alt' => '', 'class' => 'preview'));
                    $preview     = html_writer::tag('a', $preview, array('href' => $fileurl));
                    $outputimgs .= $this->output->container($preview);

                } else {
                    $outputfiles .= html_writer::tag('li', $linkhtml, array('class' => $type));
                }

            } else if ($format == 'text') {
                $outputfiles .= $linktxt . PHP_EOL;
            }

            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir.'/plagiarismlib.php');
                $outputfiles .= plagiarism_get_links(array('userid' => $file->get_userid(),
                    'file' => $file,
                    'cmid' => $this->page->cm->id,
                    'course' => $this->page->course->id));
            }
        }

        if ($format == 'html') {
            if ($outputimgs) {
                $outputimgs = $this->output->container($outputimgs, 'images');
            }

            if ($outputfiles) {
                $outputfiles = html_writer::tag('ul', $outputfiles, array('class' => 'files'));
            }

            return $this->output->container($outputimgs . $outputfiles, 'attachments');

        } else {
            return $outputfiles;
        }
    }

    
    protected function helper_user_plan_tasks(array $tasks) {
        $out = '';
        foreach ($tasks as $taskcode => $task) {
            $classes = '';
            $icon = null;
            if ($task->completed === true) {
                $classes .= ' completed';
            } elseif ($task->completed === false) {
                $classes .= ' fail';
            } elseif ($task->completed === 'info') {
                $classes .= ' info';
            }
            if (is_null($task->link)) {
                $title = $task->title;
            } else {
                $title = html_writer::link($task->link, $task->title);
            }
            $title = $this->output->container($title, 'title');
            $details = $this->output->container($task->details, 'details');
            $out .= html_writer::tag('li', $title . $details, array('class' => $classes));
        }
        if ($out) {
            $out = html_writer::tag('ul', $out, array('class' => 'tasks'));
        }
        return $out;
    }

    
    protected function helper_sortable_heading($text, $sortid=null, $sortby=null, $sorthow=null) {
        global $PAGE;

        $out = html_writer::tag('span', $text, array('class'=>'text'));

        if (!is_null($sortid)) {
            if ($sortby !== $sortid or $sorthow !== 'ASC') {
                $url = new moodle_url($PAGE->url);
                $url->params(array('sortby' => $sortid, 'sorthow' => 'ASC'));
                $out .= $this->output->action_icon($url, new pix_icon('t/sort_asc', get_string('sortasc', 'workshop')),
                    null, array('class' => 'iconsort sort asc'));
            }
            if ($sortby !== $sortid or $sorthow !== 'DESC') {
                $url = new moodle_url($PAGE->url);
                $url->params(array('sortby' => $sortid, 'sorthow' => 'DESC'));
                $out .= $this->output->action_icon($url, new pix_icon('t/sort_desc', get_string('sortdesc', 'workshop')),
                    null, array('class' => 'iconsort sort desc'));
            }
        }
        return $out;
}

    
    protected function helper_grading_report_participant(stdclass $participant, array $userinfo) {
        $userid = $participant->userid;
        $out  = $this->output->user_picture($userinfo[$userid], array('courseid' => $this->page->course->id, 'size' => 35));
        $out .= html_writer::tag('span', fullname($userinfo[$userid]));

        return $out;
    }

    
    protected function helper_grading_report_submission(stdclass $participant) {
        global $CFG;

        if (is_null($participant->submissionid)) {
            $out = $this->output->container(get_string('nosubmissionfound', 'workshop'), 'info');
        } else {
            $url = new moodle_url('/mod/workshop/submission.php',
                                  array('cmid' => $this->page->context->instanceid, 'id' => $participant->submissionid));
            $out = html_writer::link($url, format_string($participant->submissiontitle), array('class'=>'title'));

            $lastmodified = get_string('userdatemodified', 'workshop', userdate($participant->submissionmodified));
            $out .= html_writer::tag('div', $lastmodified, array('class' => 'lastmodified'));
        }

        return $out;
    }

    
    protected function helper_grading_report_assessment($assessment, $shownames, array $userinfo, $separator) {
        global $CFG;

        if (is_null($assessment)) {
            return get_string('nullgrade', 'workshop');
        }
        $a = new stdclass();
        $a->grade = is_null($assessment->grade) ? get_string('nullgrade', 'workshop') : $assessment->grade;
        $a->gradinggrade = is_null($assessment->gradinggrade) ? get_string('nullgrade', 'workshop') : $assessment->gradinggrade;
        $a->weight = $assessment->weight;
                if (is_null($assessment->gradinggradeover)) {
            if ($a->weight == 1) {
                $grade = get_string('formatpeergrade', 'workshop', $a);
            } else {
                $grade = get_string('formatpeergradeweighted', 'workshop', $a);
            }
        } else {
            $a->gradinggradeover = $assessment->gradinggradeover;
            if ($a->weight == 1) {
                $grade = get_string('formatpeergradeover', 'workshop', $a);
            } else {
                $grade = get_string('formatpeergradeoverweighted', 'workshop', $a);
            }
        }
        $url = new moodle_url('/mod/workshop/assessment.php',
                              array('asid' => $assessment->assessmentid));
        $grade = html_writer::link($url, $grade, array('class'=>'grade'));

        if ($shownames) {
            $userid = $assessment->userid;
            $name   = $this->output->user_picture($userinfo[$userid], array('courseid' => $this->page->course->id, 'size' => 16));
            $name  .= html_writer::tag('span', fullname($userinfo[$userid]), array('class' => 'fullname'));
            $name   = $separator . html_writer::tag('span', $name, array('class' => 'user'));
        } else {
            $name   = '';
        }

        return $this->output->container($grade . $name, 'assessmentdetails');
    }

    
    protected function helper_grading_report_grade($grade, $over=null) {
        $a = new stdclass();
        $a->grade = is_null($grade) ? get_string('nullgrade', 'workshop') : $grade;
        if (is_null($over)) {
            $text = get_string('formataggregatedgrade', 'workshop', $a);
        } else {
            $a->over = is_null($over) ? get_string('nullgrade', 'workshop') : $over;
            $text = get_string('formataggregatedgradeover', 'workshop', $a);
        }
        return $text;
    }

            
    
    protected static function moodleform(moodleform $mform) {

        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }

    
    protected static function array_nth(array $a, $n) {
        $keys = array_keys($a);
        if ($n < 0 or $n > count($keys) - 1) {
            return null;
        }
        $key = $keys[$n];
        return $a[$key];
    }

    
    protected static function fullname_format() {
        $fake = new stdclass();         $fake->lastname = 'LLLL';
        $fake->firstname = 'FFFF';
        $fullname = get_string('fullnamedisplay', '', $fake);
        if (strpos($fullname, 'LLLL') < strpos($fullname, 'FFFF')) {
            return 'lf';
        } else {
            return 'fl';
        }
    }
}
