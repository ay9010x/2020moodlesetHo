<?php



 defined('MOODLE_INTERNAL') || die();

 require_once($CFG->dirroot . '/comment/lib.php');
 require_once($CFG->dirroot . '/mod/assign/submissionplugin.php');


class assign_submission_comments extends assign_submission_plugin {

    
    public function get_name() {
        return get_string('pluginname', 'assignsubmission_comments');
    }

    
    public function view_summary(stdClass $submission, & $showviewlink) {

                $showviewlink = false;
                comment::init();

        $options = new stdClass();
        $options->area    = 'submission_comments';
        $options->course    = $this->assignment->get_course();
        $options->context = $this->assignment->get_context();
        $options->itemid  = $submission->id;
        $options->component = 'assignsubmission_comments';
        $options->showcount = true;
        $options->displaycancel = true;

        $comment = new comment($options);
        $comment->set_view_permission(true);

        $o = $this->assignment->get_renderer()->container($comment->output(true), 'commentscontainer');
        return $o;
    }

    
    public function is_empty(stdClass $submission) {
        return true;
    }

    
    public function can_upgrade($type, $version) {

        if ($type == 'upload' && $version >= 2011112900) {
            return true;
        }
        return false;
    }


    
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        if ($oldassignment->assignmenttype == 'upload') {
                        if (!$oldassignment->var2) {
                $this->disable();
            }
        }
        return true;
    }

    
    public function upgrade(context $oldcontext,
                            stdClass $oldassignment,
                            stdClass $oldsubmission,
                            stdClass $submission,
                            & $log) {

        if ($oldsubmission->data1 != '') {

                        comment::init();

            $options = new stdClass();
            $options->area = 'submission_comments_upgrade';
            $options->course = $this->assignment->get_course();
            $options->context = $this->assignment->get_context();
            $options->itemid = $submission->id;
            $options->component = 'assignsubmission_comments';
            $options->showcount = true;
            $options->displaycancel = true;

            $comment = new comment($options);
            $comment->add($oldsubmission->data1);
            $comment->set_view_permission(true);

            return $comment->output(true);
        }

        return true;
    }

    
    public function allow_submissions() {
        return false;
    }

    
    public function is_enabled() {
        global $CFG;

        return (!empty($CFG->usecomments));
    }

    
    public function is_configurable() {
        return false;
    }
}
