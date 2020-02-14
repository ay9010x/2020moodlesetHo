<?php




    require_once('../../config.php');
    require_once('lib.php');
    require_once($CFG->libdir.'/completionlib.php');

    $id          = optional_param('id', 0, PARAM_INT);           $f           = optional_param('f', 0, PARAM_INT);            $mode        = optional_param('mode', 0, PARAM_INT);         $showall     = optional_param('showall', '', PARAM_INT);     $changegroup = optional_param('group', -1, PARAM_INT);       $page        = optional_param('page', 0, PARAM_INT);         $search      = optional_param('search', '', PARAM_CLEAN);
    $params = array();
    if ($id) {
        $params['id'] = $id;
    } else {
        $params['f'] = $f;
    }
    if ($page) {
        $params['page'] = $page;
    }
    if ($search) {
        $params['search'] = $search;
    }
    $PAGE->set_url('/mod/forum/view.php', $params);

    if ($id) {
        if (! $cm = get_coursemodule_from_id('forum', $id)) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
            print_error('coursemisconf');
        }
        if (! $forum = $DB->get_record("forum", array("id" => $cm->instance))) {
            print_error('invalidforumid', 'forum');
        }
        if ($forum->type == 'single') {
            $PAGE->set_pagetype('mod-forum-discuss');
        }
                        require_course_login($course, true, $cm);
        $strforums = get_string("modulenameplural", "forum");
        $strforum = get_string("modulename", "forum");
    } else if ($f) {

        if (! $forum = $DB->get_record("forum", array("id" => $f))) {
            print_error('invalidforumid', 'forum');
        }
        if (! $course = $DB->get_record("course", array("id" => $forum->course))) {
            print_error('coursemisconf');
        }

        if (!$cm = get_coursemodule_from_instance("forum", $forum->id, $course->id)) {
            print_error('missingparameter');
        }
                        require_course_login($course, true, $cm);
        $strforums = get_string("modulenameplural", "forum");
        $strforum = get_string("modulename", "forum");
    } else {
        print_error('missingparameter');
    }

    if (!$PAGE->button) {
        $PAGE->set_button(forum_search_form($course, $search));
    }

    $context = context_module::instance($cm->id);
    $PAGE->set_context($context);

    if (!empty($CFG->enablerssfeeds) && !empty($CFG->forum_enablerssfeeds) && $forum->rsstype && $forum->rssarticles) {
        require_once("$CFG->libdir/rsslib.php");

        $rsstitle = format_string($course->shortname, true, array('context' => context_course::instance($course->id))) . ': ' . format_string($forum->name);
        rss_add_http_header($context, 'mod_forum', $forum, $rsstitle);
    }


    $PAGE->set_title($forum->name);
    $PAGE->add_body_class('forumtype-'.$forum->type);
    $PAGE->set_heading($course->fullname);

    if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
        notice(get_string("activityiscurrentlyhidden"));
    }

    if (!has_capability('mod/forum:viewdiscussion', $context)) {
        notice(get_string('noviewdiscussionspermission', 'forum'));
    }

        forum_view($forum, $course, $cm, $context);

    echo $OUTPUT->header();

    echo $OUTPUT->heading(format_string($forum->name), 2);
    if (!empty($forum->intro) && $forum->type != 'single' && $forum->type != 'teacher') {
        echo $OUTPUT->box(format_module_intro('forum', $forum, $cm->id), 'generalbox', 'intro');
    }

    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/forum/view.php?id=' . $cm->id);

    $SESSION->fromdiscussion = qualified_me();   


            if ($forum->type == 'single') {
        $discussion = NULL;
        $discussions = $DB->get_records('forum_discussions', array('forum'=>$forum->id), 'timemodified ASC');
        if (!empty($discussions)) {
            $discussion = array_pop($discussions);
        }
        if ($discussion) {
            if ($mode) {
                set_user_preference("forum_displaymode", $mode);
            }
            $displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);
            forum_print_mode_form($forum->id, $displaymode, $forum->type);
        }
    }

    if (!empty($forum->blockafter) && !empty($forum->blockperiod)) {
        $a = new stdClass();
        $a->blockafter = $forum->blockafter;
        $a->blockperiod = get_string('secondstotime'.$forum->blockperiod);
        echo $OUTPUT->notification(get_string('thisforumisthrottled', 'forum', $a));
    }

    if ($forum->type == 'qanda' && !has_capability('moodle/course:manageactivities', $context)) {
        echo $OUTPUT->notification(get_string('qandanotify','forum'));
    }

    switch ($forum->type) {
        
			
		case 'single':
			if (!empty($discussions) && count($discussions) > 1) {
                echo $OUTPUT->notification(get_string('warnformorepost', 'forum'));
            }
            if (! $post = forum_get_post_full($discussion->firstpost)) {
                print_error('cannotfindfirstpost', 'forum');
            }
            if ($mode) {
                set_user_preference("forum_displaymode", -1);
            }

			
            $canreply    = forum_user_can_post($forum, $discussion, $USER, $cm, $course, $context);
            $canrate     = has_capability('mod/forum:rate', $context);
            $displaymode = get_user_preferences("forum_displaymode", $CFG->forum_displaymode);

			
			forum_print_discussion($course, $cm, $forum, $discussion, $post, $displaymode, $canreply, $canrate);
			break;

        case 'eachuser':
            echo '<p class="mdl-align">';
            if (forum_user_can_post_discussion($forum, null, -1, $cm)) {
                print_string("allowsdiscussions", "forum");
            } else {
                echo '&nbsp;';
            }
            echo '</p>';
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forum_print_latest_discussions($course, $forum, -1, 'header', '', -1, -1, $page, $CFG->forum_manydiscussions, $cm);
            }
            break;

        case 'teacher':
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forum_print_latest_discussions($course, $forum, -1, 'header', '', -1, -1, $page, $CFG->forum_manydiscussions, $cm);
            }
            break;

        case 'blog':
            echo '<br />';
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'plain', 'd.pinned DESC, p.created DESC', -1, -1, -1, 0, $cm);
            } else {
                forum_print_latest_discussions($course, $forum, -1, 'plain', 'd.pinned DESC, p.created DESC', -1, -1, $page,
                    $CFG->forum_manydiscussions, $cm);
            }
            break;
        case 'news':
            echo '<br />';
            if (!empty($showall)) {
                                forum_print_latest_discussions($course, $forum, 0, 'plain', 'd.pinned DESC, p.created DESC', -1, -1, -1, 0, $cm);
            } else {
                                forum_print_latest_discussions($course, $forum, -1, 'plain', 'd.pinned DESC, p.created DESC', -1, -1, $page, $CFG->forum_manydiscussions, $cm);
            }
            break;
        default:
            echo '<br />';
			
            if (!empty($showall)) {
                forum_print_latest_discussions($course, $forum, 0, 'header', '', -1, -1, -1, 0, $cm);
            } else {
                forum_print_latest_discussions($course, $forum, -1, 'header', '', -1, -1, $page, $CFG->forum_manydiscussions, $cm);
            }
			
            break;
    }

        $PAGE->requires->yui_module('moodle-mod_forum-subscriptiontoggle', 'Y.M.mod_forum.subscriptiontoggle.init');

    echo $OUTPUT->footer($course);
