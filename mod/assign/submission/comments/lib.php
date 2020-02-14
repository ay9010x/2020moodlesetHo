<?php


defined('MOODLE_INTERNAL') || die();


function assignsubmission_comments_comment_validate(stdClass $options) {
    global $USER, $CFG, $DB;

    if ($options->commentarea != 'submission_comments' &&
            $options->commentarea != 'submission_comments_upgrade') {
        throw new comment_exception('invalidcommentarea');
    }
    if (!$submission = $DB->get_record('assign_submission', array('id'=>$options->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    $context = $options->context;

    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $assignment = new assign($context, null, null);

    if ($assignment->get_instance()->id != $submission->assignment) {
        throw new comment_exception('invalidcontext');
    }
    $canview = false;
    if ($submission->userid) {
        $canview = $assignment->can_view_submission($submission->userid);
    } else {
        $canview = $assignment->can_view_group_submission($submission->groupid);
    }
    if (!$canview) {
        throw new comment_exception('nopermissiontocomment');
    }

    return true;
}


function assignsubmission_comments_comment_permissions(stdClass $options) {
    global $USER, $CFG, $DB;

    if ($options->commentarea != 'submission_comments' &&
            $options->commentarea != 'submission_comments_upgrade') {
        throw new comment_exception('invalidcommentarea');
    }
    if (!$submission = $DB->get_record('assign_submission', array('id'=>$options->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    $context = $options->context;

    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $assignment = new assign($context, null, null);

    if ($assignment->get_instance()->id != $submission->assignment) {
        throw new comment_exception('invalidcontext');
    }

    if ($assignment->get_instance()->teamsubmission &&
        !$assignment->can_view_group_submission($submission->groupid)) {
        return array('post' => false, 'view' => false);
    }

    if (!$assignment->get_instance()->teamsubmission &&
        !$assignment->can_view_submission($submission->userid)) {
        return array('post' => false, 'view' => false);
    }

    return array('post' => true, 'view' => true);
}


function assignsubmission_comments_comment_display($comments, $options) {
    global $CFG, $DB, $USER;

    if ($options->commentarea != 'submission_comments' &&
        $options->commentarea != 'submission_comments_upgrade') {
        throw new comment_exception('invalidcommentarea');
    }
    if (!$submission = $DB->get_record('assign_submission', array('id'=>$options->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    $context = $options->context;
    $cm = $options->cm;
    $course = $options->courseid;

    require_once($CFG->dirroot . '/mod/assign/locallib.php');
    $assignment = new assign($context, $cm, $course);

    if ($assignment->get_instance()->id != $submission->assignment) {
        throw new comment_exception('invalidcontext');
    }

    if ($assignment->is_blind_marking() && !empty($comments)) {
                $usermappings = array();
        $hiddenuserstr = trim(get_string('hiddenuser', 'assign'));
        $guestuser = guest_user();

        foreach ($comments as $comment) {
                        if (empty($usermappings[$comment->userid])) {
                                $anonid = $assignment->get_uniqueid_for_user($comment->userid);
                $commenter = new stdClass();
                $commenter->firstname = $hiddenuserstr;
                $commenter->lastname = $anonid;
                $commenter->picture = 0;
                $commenter->id = $guestuser->id;
                $commenter->email = $guestuser->email;
                $commenter->imagealt = $guestuser->imagealt;

                                $usermappings[$comment->userid]->fullname = fullname($commenter);
                $usermappings[$comment->userid]->avatar = $assignment->get_renderer()->user_picture($commenter,
                        array('size'=>18, 'link' => false));
            }

                        $comment->fullname = $usermappings[$comment->userid]->fullname;
            $comment->avatar = $usermappings[$comment->userid]->avatar;
            $comment->profileurl = null;
        }
    }

    return $comments;
}


function assignsubmission_comments_comment_add(stdClass $comment, stdClass $param) {

    global $DB;
    if ($comment->commentarea == 'submission_comments_upgrade') {
        $submissionid = $comment->itemid;
        $submission = $DB->get_record('assign_submission', array('id' => $submissionid));

        $comment->userid = $submission->userid;
        $comment->commentarea = 'submission_comments';
    }
}

