<?php



defined('MOODLE_INTERNAL') || die();


class assign_submit_for_grading_page implements renderable {
    
    public $notifications = array();
    
    public $coursemoduleid = 0;
    
    public $confirmform = null;

    
    public function __construct($notifications, $coursemoduleid, $confirmform) {
        $this->notifications = $notifications;
        $this->coursemoduleid = $coursemoduleid;
        $this->confirmform = $confirmform;
    }

}


class assign_gradingmessage implements renderable {
    
    public $heading = '';
    
    public $message = '';
    
    public $coursemoduleid = 0;
    
    public $gradingerror = null;

    
    public function __construct($heading, $message, $coursemoduleid, $gradingerror = false, $page = null) {
        $this->heading = $heading;
        $this->message = $message;
        $this->coursemoduleid = $coursemoduleid;
        $this->gradingerror = $gradingerror;
        $this->page = $page;
    }

}


class assign_form implements renderable {
    
    public $form = null;
    
    public $classname = '';
    
    public $jsinitfunction = '';

    
    public function __construct($classname, moodleform $form, $jsinitfunction = '') {
        $this->classname = $classname;
        $this->form = $form;
        $this->jsinitfunction = $jsinitfunction;
    }

}


class assign_user_summary implements renderable {
    
    public $user = null;
    
    public $courseid;
    
    public $viewfullnames = false;
    
    public $blindmarking = false;
    
    public $uniqueidforuser;
    
    public $extrauserfields;
    
    public $suspendeduser;

    
    public function __construct(stdClass $user,
                                $courseid,
                                $viewfullnames,
                                $blindmarking,
                                $uniqueidforuser,
                                $extrauserfields,
                                $suspendeduser = false) {
        $this->user = $user;
        $this->courseid = $courseid;
        $this->viewfullnames = $viewfullnames;
        $this->blindmarking = $blindmarking;
        $this->uniqueidforuser = $uniqueidforuser;
        $this->extrauserfields = $extrauserfields;
        $this->suspendeduser = $suspendeduser;
    }
}


class assign_feedback_plugin_feedback implements renderable {
    
    const SUMMARY                = 10;
    
    const FULL                   = 20;

    
    public $plugin = null;
    
    public $grade = null;
    
    public $view = self::SUMMARY;
    
    public $coursemoduleid = 0;
    
    public $returnaction = '';
    
    public $returnparams = array();

    
    public function __construct(assign_feedback_plugin $plugin,
                                stdClass $grade,
                                $view,
                                $coursemoduleid,
                                $returnaction,
                                $returnparams) {
        $this->plugin = $plugin;
        $this->grade = $grade;
        $this->view = $view;
        $this->coursemoduleid = $coursemoduleid;
        $this->returnaction = $returnaction;
        $this->returnparams = $returnparams;
    }

}


class assign_submission_plugin_submission implements renderable {
    
    const SUMMARY                = 10;
    
    const FULL                   = 20;

    
    public $plugin = null;
    
    public $submission = null;
    
    public $view = self::SUMMARY;
    
    public $coursemoduleid = 0;
    
    public $returnaction = '';
    
    public $returnparams = array();

    
    public function __construct(assign_submission_plugin $plugin,
                                stdClass $submission,
                                $view,
                                $coursemoduleid,
                                $returnaction,
                                $returnparams) {
        $this->plugin = $plugin;
        $this->submission = $submission;
        $this->view = $view;
        $this->coursemoduleid = $coursemoduleid;
        $this->returnaction = $returnaction;
        $this->returnparams = $returnparams;
    }
}


class assign_feedback_status implements renderable {

    
    public $gradefordisplay = '';
    
    public $gradeddate = 0;
    
    public $grader = null;
    
    public $feedbackplugins = array();
    
    public $grade = null;
    
    public $coursemoduleid = 0;
    
    public $returnaction = '';
    
    public $returnparams = array();

    
    public function __construct($gradefordisplay,
                                $gradeddate,
                                $grader,
                                $feedbackplugins,
                                $grade,
                                $coursemoduleid,
                                $returnaction,
                                $returnparams) {
        $this->gradefordisplay = $gradefordisplay;
        $this->gradeddate = $gradeddate;
        $this->grader = $grader;
        $this->feedbackplugins = $feedbackplugins;
        $this->grade = $grade;
        $this->coursemoduleid = $coursemoduleid;
        $this->returnaction = $returnaction;
        $this->returnparams = $returnparams;
    }
}


class assign_submission_status implements renderable {
    
    const STUDENT_VIEW     = 10;
    
    const GRADER_VIEW      = 20;

    
    public $allowsubmissionsfromdate = 0;
    
    public $alwaysshowdescription = false;
    
    public $submission = null;
    
    public $teamsubmissionenabled = false;
    
    public $teamsubmission = null;
    
    public $submissiongroup = null;
    
    public $submissiongroupmemberswhoneedtosubmit = array();
    
    public $submissionsenabled = false;
    
    public $locked = false;
    
    public $graded = false;
    
    public $duedate = 0;
    
    public $cutoffdate = 0;
    
    public $submissionplugins = array();
    
    public $returnaction = '';
    
    public $returnparams = array();
    
    public $courseid = 0;
    
    public $coursemoduleid = 0;
    
    public $view = self::STUDENT_VIEW;
    
    public $canviewfullnames = false;
    
    public $canedit = false;
    
    public $cansubmit = false;
    
    public $extensionduedate = 0;
    
    public $context = 0;
    
    public $blindmarking = false;
    
    public $gradingcontrollerpreview = '';
    
    public $attemptreopenmethod = 'none';
    
    public $maxattempts = -1;
    
    public $gradingstatus = '';
    
    public $preventsubmissionnotingroup = 0;
    
    public $usergroups = array();


    
    public function __construct($allowsubmissionsfromdate,
                                $alwaysshowdescription,
                                $submission,
                                $teamsubmissionenabled,
                                $teamsubmission,
                                $submissiongroup,
                                $submissiongroupmemberswhoneedtosubmit,
                                $submissionsenabled,
                                $locked,
                                $graded,
                                $duedate,
                                $cutoffdate,
                                $submissionplugins,
                                $returnaction,
                                $returnparams,
                                $coursemoduleid,
                                $courseid,
                                $view,
                                $canedit,
                                $cansubmit,
                                $canviewfullnames,
                                $extensionduedate,
                                $context,
                                $blindmarking,
                                $gradingcontrollerpreview,
                                $attemptreopenmethod,
                                $maxattempts,
                                $gradingstatus,
                                $preventsubmissionnotingroup,
                                $usergroups) {
        $this->allowsubmissionsfromdate = $allowsubmissionsfromdate;
        $this->alwaysshowdescription = $alwaysshowdescription;
        $this->submission = $submission;
        $this->teamsubmissionenabled = $teamsubmissionenabled;
        $this->teamsubmission = $teamsubmission;
        $this->submissiongroup = $submissiongroup;
        $this->submissiongroupmemberswhoneedtosubmit = $submissiongroupmemberswhoneedtosubmit;
        $this->submissionsenabled = $submissionsenabled;
        $this->locked = $locked;
        $this->graded = $graded;
        $this->duedate = $duedate;
        $this->cutoffdate = $cutoffdate;
        $this->submissionplugins = $submissionplugins;
        $this->returnaction = $returnaction;
        $this->returnparams = $returnparams;
        $this->coursemoduleid = $coursemoduleid;
        $this->courseid = $courseid;
        $this->view = $view;
        $this->canedit = $canedit;
        $this->cansubmit = $cansubmit;
        $this->canviewfullnames = $canviewfullnames;
        $this->extensionduedate = $extensionduedate;
        $this->context = $context;
        $this->blindmarking = $blindmarking;
        $this->gradingcontrollerpreview = $gradingcontrollerpreview;
        $this->attemptreopenmethod = $attemptreopenmethod;
        $this->maxattempts = $maxattempts;
        $this->gradingstatus = $gradingstatus;
        $this->preventsubmissionnotingroup = $preventsubmissionnotingroup;
        $this->usergroups = $usergroups;
    }
}

class assign_submission_status_compact extends assign_submission_status implements renderable {
    }


class assign_attempt_history implements renderable {

    
    public $submissions = array();
    
    public $grades = array();
    
    public $submissionplugins = array();
    
    public $feedbackplugins = array();
    
    public $coursemoduleid = 0;
    
    public $returnaction = '';
    
    public $returnparams = array();
    
    public $cangrade = false;
    
    public $useridlistid = 0;
    
    public $rownum = 0;

    
    public function __construct($submissions,
                                $grades,
                                $submissionplugins,
                                $feedbackplugins,
                                $coursemoduleid,
                                $returnaction,
                                $returnparams,
                                $cangrade,
                                $useridlistid,
                                $rownum) {
        $this->submissions = $submissions;
        $this->grades = $grades;
        $this->submissionplugins = $submissionplugins;
        $this->feedbackplugins = $feedbackplugins;
        $this->coursemoduleid = $coursemoduleid;
        $this->returnaction = $returnaction;
        $this->returnparams = $returnparams;
        $this->cangrade = $cangrade;
        $this->useridlistid = $useridlistid;
        $this->rownum = $rownum;
    }
}


class assign_attempt_history_chooser implements renderable, templatable {

    
    public $submissions = array();
    
    public $grades = array();
    
    public $coursemoduleid = 0;
    
    public $userid = 0;

    
    public function __construct($submissions,
                                $grades,
                                $coursemoduleid,
                                $userid) {
        $this->submissions = $submissions;
        $this->grades = $grades;
        $this->coursemoduleid = $coursemoduleid;
        $this->userid = $userid;
    }

    
    public function export_for_template(renderer_base $output) {
                $export = (object) $this;
        $export->submissions = array_reverse($export->submissions);
        $export->submissioncount = count($export->submissions);

        foreach ($export->submissions as $i => $submission) {
            $grade = null;
            foreach ($export->grades as $onegrade) {
                if ($onegrade->attemptnumber == $submission->attemptnumber) {
                    $submission->grade = $onegrade;
                    break;
                }
            }
            if (!$submission) {
                $submission = new stdClass();
            }

            $editbtn = '';

            if ($submission->timemodified) {
                $submissionsummary = userdate($submission->timemodified);
            } else {
                $submissionsummary = get_string('nosubmission', 'assign');
            }

            $attemptsummaryparams = array('attemptnumber' => $submission->attemptnumber + 1,
                                          'submissionsummary' => $submissionsummary);
            $submission->attemptsummary = get_string('attemptheading', 'assign', $attemptsummaryparams);
            $submission->statussummary = get_string('submissionstatus_' . $submission->status, 'assign');

        }

        return $export;
    }
}


class assign_header implements renderable {
    
    public $assign = null;
    
    public $context = null;
    
    public $showintro = false;
    
    public $coursemoduleid = 0;
    
    public $subpage = '';
    
    public $preface = '';
    
    public $postfix = '';

    
    public function __construct(stdClass $assign,
                                $context,
                                $showintro,
                                $coursemoduleid,
                                $subpage='',
                                $preface='',
                                $postfix='') {
        $this->assign = $assign;
        $this->context = $context;
        $this->showintro = $showintro;
        $this->coursemoduleid = $coursemoduleid;
        $this->subpage = $subpage;
        $this->preface = $preface;
        $this->postfix = $postfix;
    }
}


class assign_plugin_header implements renderable {
    
    public $plugin = null;

    
    public function __construct(assign_plugin $plugin) {
        $this->plugin = $plugin;
    }
}


class assign_grading_summary implements renderable {
    
    public $participantcount = 0;
    
    public $submissiondraftsenabled = false;
    
    public $submissiondraftscount = 0;
    
    public $submissionsenabled = false;
    
    public $submissionssubmittedcount = 0;
    
    public $submissionsneedgradingcount = 0;
    
    public $duedate = 0;
    
    public $cutoffdate = 0;
    
    public $coursemoduleid = 0;
    
    public $teamsubmission = false;
    
    public $warnofungroupedusers = false;

    
    public function __construct($participantcount,
                                $submissiondraftsenabled,
                                $submissiondraftscount,
                                $submissionsenabled,
                                $submissionssubmittedcount,
                                $cutoffdate,
                                $duedate,
                                $coursemoduleid,
                                $submissionsneedgradingcount,
                                $teamsubmission,
                                $warnofungroupedusers) {
        $this->participantcount = $participantcount;
        $this->submissiondraftsenabled = $submissiondraftsenabled;
        $this->submissiondraftscount = $submissiondraftscount;
        $this->submissionsenabled = $submissionsenabled;
        $this->submissionssubmittedcount = $submissionssubmittedcount;
        $this->duedate = $duedate;
        $this->cutoffdate = $cutoffdate;
        $this->coursemoduleid = $coursemoduleid;
        $this->submissionsneedgradingcount = $submissionsneedgradingcount;
        $this->teamsubmission = $teamsubmission;
        $this->warnofungroupedusers = $warnofungroupedusers;
    }
}


class assign_course_index_summary implements renderable {
    
    public $assignments = array();
    
    public $usesections = false;
    
    public $courseformatname = '';

    
    public function __construct($usesections, $courseformatname) {
        $this->usesections = $usesections;
        $this->courseformatname = $courseformatname;
    }

    
    public function add_assign_info($cmid, $cmname, $sectionname, $timedue, $submissioninfo, $gradeinfo) {
        $this->assignments[] = array('cmid'=>$cmid,
                               'cmname'=>$cmname,
                               'sectionname'=>$sectionname,
                               'timedue'=>$timedue,
                               'submissioninfo'=>$submissioninfo,
                               'gradeinfo'=>$gradeinfo);
    }


}



class assign_files implements renderable {
    
    public $context;
    
    public $dir;
    
    public $portfolioform;
    
    public $cm;
    
    public $course;

    
    public function __construct(context $context, $sid, $filearea, $component) {
        global $CFG;
        $this->context = $context;
        list($context, $course, $cm) = get_context_info_array($context->id);
        $this->cm = $cm;
        $this->course = $course;
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, $component, $filearea, $sid);

        $files = $fs->get_area_files($this->context->id,
                                     $component,
                                     $filearea,
                                     $sid,
                                     'timemodified',
                                     false);

        if (!empty($CFG->enableportfolios)) {
            require_once($CFG->libdir . '/portfoliolib.php');
            if (count($files) >= 1 && !empty($sid) &&
                    has_capability('mod/assign:exportownsubmission', $this->context)) {
                $button = new portfolio_add_button();
                $callbackparams = array('cmid' => $this->cm->id,
                                        'sid' => $sid,
                                        'area' => $filearea,
                                        'component' => $component);
                $button->set_callback_options('assign_portfolio_caller',
                                              $callbackparams,
                                              'mod_assign');
                $button->reset_formats();
                $this->portfolioform = $button->to_html(PORTFOLIO_ADD_TEXT_LINK);
            }

        }

        $this->preprocess($this->dir, $filearea, $component);
    }

    
    public function preprocess($dir, $filearea, $component) {
        global $CFG;
        foreach ($dir['subdirs'] as $subdir) {
            $this->preprocess($subdir, $filearea, $component);
        }
        foreach ($dir['files'] as $file) {
            $file->portfoliobutton = '';
            if (!empty($CFG->enableportfolios)) {
                require_once($CFG->libdir . '/portfoliolib.php');
                $button = new portfolio_add_button();
                if (has_capability('mod/assign:exportownsubmission', $this->context)) {
                    $portfolioparams = array('cmid' => $this->cm->id, 'fileid' => $file->get_id());
                    $button->set_callback_options('assign_portfolio_caller',
                                                  $portfolioparams,
                                                  'mod_assign');
                    $button->set_format_by_file($file);
                    $file->portfoliobutton = $button->to_html(PORTFOLIO_ADD_ICON_LINK);
                }
            }
            $path = '/' .
                    $this->context->id .
                    '/' .
                    $component .
                    '/' .
                    $filearea .
                    '/' .
                    $file->get_itemid() .
                    $file->get_filepath() .
                    $file->get_filename();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", $path, true);
            $filename = $file->get_filename();
            $file->fileurl = html_writer::link($url, $filename, [
                    'target' => '_blank',
                ]);
        }
    }
}
