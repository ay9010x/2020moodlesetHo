<?php




require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/forum/lib.php');
require_once($CFG->dirroot.'/rating/lib.php');

$courseid  = optional_param('course', null, PARAM_INT); $userid = optional_param('id', $USER->id, PARAM_INT);        $mode = optional_param('mode', 'posts', PARAM_ALPHA);   $page = optional_param('page', 0, PARAM_INT);           $perpage = optional_param('perpage', 5, PARAM_INT);     
if (empty($userid)) {
    if (!isloggedin()) {
        require_login();
    }
    $userid = $USER->id;
}

$discussionsonly = ($mode !== 'posts');
$isspecificcourse = !is_null($courseid);
$iscurrentuser = ($USER->id == $userid);

$url = new moodle_url('/mod/forum/user.php', array('id' => $userid));
if ($isspecificcourse) {
    $url->param('course', $courseid);
}
if ($discussionsonly) {
    $url->param('mode', 'discussions');
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

if ($page != 0) {
    $url->param('page', $page);
}
if ($perpage != 5) {
    $url->param('perpage', $perpage);
}

$user = $DB->get_record("user", array("id" => $userid), '*', MUST_EXIST);
$usercontext = context_user::instance($user->id, MUST_EXIST);
if (isguestuser($user)) {
            print_error('invaliduserid');
}
if ($user->deleted) {
    $PAGE->set_title(get_string('userdeleted'));
    $PAGE->set_context(context_system::instance());
    echo $OUTPUT->header();
    echo $OUTPUT->heading($PAGE->title);
    echo $OUTPUT->footer();
    die;
}

$isloggedin = isloggedin();
$isguestuser = $isloggedin && isguestuser();
$isparent = !$iscurrentuser && $DB->record_exists('role_assignments', array('userid'=>$USER->id, 'contextid'=>$usercontext->id));
$hasparentaccess = $isparent && has_all_capabilities(array('moodle/user:viewdetails', 'moodle/user:readuserposts'), $usercontext);

if ($isspecificcourse) {
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $coursecontext = context_course::instance($courseid, MUST_EXIST);
        if ($hasparentaccess) {
                                require_login();
        $PAGE->set_context($coursecontext);
        $PAGE->set_course($course);
    } else {
                require_login($course);
    }
        $courses = array($courseid => $course);
} else {
            require_login();
    $PAGE->set_context(context_user::instance($user->id));

            $courses = forum_get_courses_user_posted_in($user, $discussionsonly);
}

$params = array(
    'context' => $PAGE->context,
    'relateduserid' => $user->id,
    'other' => array('reportmode' => $mode),
);
$event = \mod_forum\event\user_report_viewed::create($params);
$event->trigger();

$result = forum_get_posts_by_user($user, $courses, $isspecificcourse, $discussionsonly, ($page * $perpage), $perpage);

if (empty($result->posts)) {
                    
                    $canviewuser = ($iscurrentuser || $isspecificcourse || empty($CFG->forceloginforprofiles) || has_coursecontact_role($userid));
            $canviewuser = ($canviewuser || ($isspecificcourse && has_capability('moodle/user:viewdetails', $coursecontext) || has_capability('moodle/user:viewalldetails', $usercontext)));

            if (!$canviewuser) {
                $sharedcourses = enrol_get_shared_courses($USER->id, $user->id, true);
        foreach ($sharedcourses as $sharedcourse) {
                        if (has_capability('moodle/user:viewdetails', context_course::instance($sharedcourse->id))) {
                $canviewuser = true;
                break;
            }
        }
        unset($sharedcourses);
    }

        $pagetitle = get_string('noposts', 'mod_forum');

        if ($isspecificcourse) {
        $pageheading = format_string($course->fullname, true, array('context' => $coursecontext));
    } else {
        $pageheading = get_string('pluginname', 'mod_forum');
    }

            if ($iscurrentuser) {
                        if ($discussionsonly) {
            $notification = get_string('nodiscussionsstartedbyyou', 'forum');
        } else {
            $notification = get_string('nopostsmadebyyou', 'forum');
        }
                        $usernode = $PAGE->navigation->find('users', null);
        $usernode->make_inactive();
                if (isset($courseid) && $courseid != SITEID) {
                        $newusernode = $PAGE->navigation->find('user' . $user->id, null);
            $newusernode->make_active();
                        if ($mode == 'posts') {
                $navbar = $PAGE->navbar->add(get_string('posts', 'forum'), new moodle_url('/mod/forum/user.php',
                        array('id' => $user->id, 'course' => $courseid)));
            } else {
                $navbar = $PAGE->navbar->add(get_string('discussions', 'forum'), new moodle_url('/mod/forum/user.php',
                        array('id' => $user->id, 'course' => $courseid, 'mode' => 'discussions')));
            }
        }
    } else if ($canviewuser) {
        $PAGE->navigation->extend_for_user($user);
        $PAGE->navigation->set_userid_for_parent_checks($user->id); 
                if (isset($courseid) && $courseid != SITEID) {
                        $usernode = $PAGE->navigation->find('user' . $user->id, null);
            $usernode->make_active();
                        if ($mode == 'posts') {
                $navbar = $PAGE->navbar->add(get_string('posts', 'forum'), new moodle_url('/mod/forum/user.php',
                        array('id' => $user->id, 'course' => $courseid)));
            } else {
                $navbar = $PAGE->navbar->add(get_string('discussions', 'forum'), new moodle_url('/mod/forum/user.php',
                        array('id' => $user->id, 'course' => $courseid, 'mode' => 'discussions')));
            }
        }

        $fullname = fullname($user);
        if ($discussionsonly) {
            $notification = get_string('nodiscussionsstartedby', 'forum', $fullname);
        } else {
            $notification = get_string('nopostsmadebyuser', 'forum', $fullname);
        }
    } else {
                        $notification = get_string('cannotviewusersposts', 'forum');
        if ($isspecificcourse) {
            $url = new moodle_url('/course/view.php', array('id' => $courseid));
        } else {
            $url = new moodle_url('/');
        }
        navigation_node::override_active_url($url);
    }

        $PAGE->set_title($pagetitle);
    if ($isspecificcourse) {
        $PAGE->set_heading($pageheading);
    } else {
        $PAGE->set_heading(fullname($user));
    }
    echo $OUTPUT->header();
    if (!$isspecificcourse) {
        echo $OUTPUT->heading($pagetitle);
    } else {
        $userheading = array(
                'heading' => fullname($user),
                'user' => $user,
                'usercontext' => $usercontext
            );
        echo $OUTPUT->context_header($userheading, 2);
    }
    echo $OUTPUT->notification($notification);
    if (!$url->compare($PAGE->url)) {
        echo $OUTPUT->continue_button($url);
    }
    echo $OUTPUT->footer();
    die;
}

$postoutput = array();

$discussions = array();
foreach ($result->posts as $post) {
    $discussions[] = $post->discussion;
}
$discussions = $DB->get_records_list('forum_discussions', 'id', array_unique($discussions));

$rm = new rating_manager();
$ratingoptions = new stdClass;
$ratingoptions->component = 'mod_forum';
$ratingoptions->ratingarea = 'post';
foreach ($result->posts as $post) {
    if (!isset($result->forums[$post->forum]) || !isset($discussions[$post->discussion])) {
                continue;
    }
    $forum = $result->forums[$post->forum];
    $cm = $forum->cm;
    $discussion = $discussions[$post->discussion];
    $course = $result->courses[$discussion->course];

    $forumurl = new moodle_url('/mod/forum/view.php', array('id' => $cm->id));
    $discussionurl = new moodle_url('/mod/forum/discuss.php', array('d' => $post->discussion));

        if ($forum->assessed != RATING_AGGREGATE_NONE) {
        $ratingoptions->context = $cm->context;
        $ratingoptions->items = array($post);
        $ratingoptions->aggregate = $forum->assessed;        $ratingoptions->scaleid = $forum->scale;
        $ratingoptions->userid = $user->id;
        $ratingoptions->assesstimestart = $forum->assesstimestart;
        $ratingoptions->assesstimefinish = $forum->assesstimefinish;
        if ($forum->type == 'single' or !$post->discussion) {
            $ratingoptions->returnurl = $forumurl;
        } else {
            $ratingoptions->returnurl = $discussionurl;
        }

        $updatedpost = $rm->get_ratings($ratingoptions);
                $result->posts[$updatedpost[0]->id] = $updatedpost[0];
    }

    $courseshortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
    $forumname = format_string($forum->name, true, array('context' => $cm->context));

    $fullsubjects = array();
    if (!$isspecificcourse && !$hasparentaccess) {
        $fullsubjects[] = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $courseshortname);
        $fullsubjects[] = html_writer::link($forumurl, $forumname);
    } else {
        $fullsubjects[] = html_writer::tag('span', $courseshortname);
        $fullsubjects[] = html_writer::tag('span', $forumname);
    }
    if ($forum->type != 'single') {
        $discussionname = format_string($discussion->name, true, array('context' => $cm->context));
        if (!$isspecificcourse && !$hasparentaccess) {
            $fullsubjects[] .= html_writer::link($discussionurl, $discussionname);
        } else {
            $fullsubjects[] .= html_writer::tag('span', $discussionname);
        }
        if ($post->parent != 0) {
            $postname = format_string($post->subject, true, array('context' => $cm->context));
            if (!$isspecificcourse && !$hasparentaccess) {
                $fullsubjects[] .= html_writer::link(new moodle_url('/mod/forum/discuss.php', array('d' => $post->discussion, 'parent' => $post->id)), $postname);
            } else {
                $fullsubjects[] .= html_writer::tag('span', $postname);
            }
        }
    }
    $post->subject = join(' -> ', $fullsubjects);
            $post->subjectnoformat = true;
    $discussionurl->set_anchor('p'.$post->id);
    $fulllink = html_writer::link($discussionurl, get_string("postincontext", "forum"));

    $postoutput[] = forum_print_post($post, $discussion, $forum, $cm, $course, false, false, false, $fulllink, '', null, true, null, true);
}

$userfullname = fullname($user);

if ($discussionsonly) {
    $inpageheading = get_string('discussionsstartedby', 'mod_forum', $userfullname);
} else {
    $inpageheading = get_string('postsmadebyuser', 'mod_forum', $userfullname);
}
if ($isspecificcourse) {
    $a = new stdClass;
    $a->fullname = $userfullname;
    $a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
    $pageheading = $a->coursename;
    if ($discussionsonly) {
        $pagetitle = get_string('discussionsstartedbyuserincourse', 'mod_forum', $a);
    } else {
        $pagetitle = get_string('postsmadebyuserincourse', 'mod_forum', $a);
    }
} else {
    $pagetitle = $inpageheading;
    $pageheading = $userfullname;
}

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pageheading);

$PAGE->navigation->extend_for_user($user);
$PAGE->navigation->set_userid_for_parent_checks($user->id); 
if (isset($courseid) && $courseid != SITEID) {
    $usernode = $PAGE->navigation->find('user' . $user->id , null);
    $usernode->make_active();
        if ($mode == 'posts') {
        $navbar = $PAGE->navbar->add(get_string('posts', 'forum'), new moodle_url('/mod/forum/user.php',
                array('id' => $user->id, 'course' => $courseid)));
    } else {
        $navbar = $PAGE->navbar->add(get_string('discussions', 'forum'), new moodle_url('/mod/forum/user.php',
                array('id' => $user->id, 'course' => $courseid, 'mode' => 'discussions')));
    }
}

echo $OUTPUT->header();
echo html_writer::start_tag('div', array('class' => 'user-content'));

if ($isspecificcourse) {
    $userheading = array(
        'heading' => fullname($user),
        'user' => $user,
        'usercontext' => $usercontext
    );
    echo $OUTPUT->context_header($userheading, 2);
} else {
    echo $OUTPUT->heading($inpageheading);
}

if (!empty($postoutput)) {
    echo $OUTPUT->paging_bar($result->totalcount, $page, $perpage, $url);
    foreach ($postoutput as $post) {
        echo $post;
        echo html_writer::empty_tag('br');
    }
    echo $OUTPUT->paging_bar($result->totalcount, $page, $perpage, $url);
} else if ($discussionsonly) {
    echo $OUTPUT->heading(get_string('nodiscussionsstartedby', 'forum', $userfullname));
} else {
    echo $OUTPUT->heading(get_string('noposts', 'forum'));
}

echo html_writer::end_tag('div');
echo $OUTPUT->footer();
