<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once(dirname(__FILE__).'/locallib.php');

function lightboxgallery_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;

        default:
            return null;
    }
}


function lightboxgallery_add_instance($gallery) {
    global $DB;

    $gallery->timemodified = time();

    if (!lightboxgallery_rss_enabled()) {
        $gallery->rss = 0;
    }

    lightboxgallery_set_sizing($gallery);

    return $DB->insert_record('lightboxgallery', $gallery);
}


function lightboxgallery_update_instance($gallery) {
    global $DB;

    $gallery->timemodified = time();
    $gallery->id = $gallery->instance;

    if (!lightboxgallery_rss_enabled()) {
        $gallery->rss = 0;
    }

    lightboxgallery_set_sizing($gallery);

    return $DB->update_record('lightboxgallery', $gallery);
}


function lightboxgallery_set_sizing($gallery) {
    if (isset($gallery->autoresizedisabled)) {
        $gallery->autoresize = 0;
        $gallery->resize = 0;
    }
}


function lightboxgallery_delete_instance($id) {
    global $DB;

    if (!$gallery = $DB->get_record('lightboxgallery', array('id' => $id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('lightboxgallery', $gallery->id);
    $context = context_module::instance($cm->id);
        $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_lightboxgallery');

        $DB->delete_records('lightboxgallery_comments', array('gallery' => $gallery->id) );
    $DB->delete_records('lightboxgallery_image_meta', array('gallery' => $gallery->id));

        $DB->delete_records('lightboxgallery', array('id' => $id));

    return true;
}


function lightboxgallery_user_complete($course, $user, $mod, $resource) {
    global $DB, $CFG;

    $sql = "SELECT c.*
              FROM {lightboxgallery_comments} c
                   JOIN {lightboxgallery} l ON l.id = c.gallery
                   JOIN {user}            u ON u.id = c.userid
             WHERE l.id = :mod AND u.id = :userid
          ORDER BY c.timemodified ASC";
    $params = array('mod' => $mod->instance, 'userid' => $user->id);
    if ($comments = $DB->get_records_sql($sql, $params)) {
        $cm = get_coursemodule_from_id('lightboxgallery', $mod->id);
        $context = context_module::instance($cm->id);
        foreach ($comments as $comment) {
            lightboxgallery_print_comment($comment, $context);
        }
    } else {
        print_string('nocomments', 'lightboxgallery');
    }
}


function lightboxgallery_get_extra_capabilities() {
    return array('moodle/course:viewhiddenactivities');
}

function lightboxgallery_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    global $DB, $CFG, $COURSE;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];

    $userfields = user_picture::fields('u', null, 'userid');
    $userfieldsnoalias = user_picture::fields();
    $sql = "SELECT c.*, l.name, $userfields
              FROM {lightboxgallery_comments} c
                   JOIN {lightboxgallery} l ON l.id = c.gallery
                   JOIN {user}            u ON u.id = c.userid
             WHERE c.timemodified > $timestart AND l.id = {$cm->instance}
                   " . ($userid ? "AND u.id = $userid" : '') . "
          ORDER BY c.timemodified ASC";

    if ($comments = $DB->get_records_sql($sql)) {
        foreach ($comments as $comment) {
            $display = lightboxgallery_resize_text(trim(strip_tags($comment->commenttext)), MAX_COMMENT_PREVIEW);

            $activity = new stdClass();

            $activity->type         = 'lightboxgallery';
            $activity->cmid         = $cm->id;
            $activity->name         = format_string($cm->name, true);
            $activity->sectionnum   = $cm->sectionnum;
            $activity->timestamp    = $comment->timemodified;

            $activity->content = new stdClass();
            $activity->content->id      = $comment->id;
            $activity->content->comment = $display;

            $activity->user = new stdClass();
            $activity->user->id = $comment->userid;

            $fields = explode(',', $userfieldsnoalias);
            foreach ($fields as $field) {
                if ($field == 'id') {
                    continue;
                }
                $activity->user->$field = $comment->$field;
            }

            $activities[$index++] = $activity;

        }
    }
    return true;
}

function lightboxgallery_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $CFG, $OUTPUT;

    $userviewurl = new moodle_url('/user/view.php', array('id' => $activity->user->id, 'course' => $courseid));
    echo '<table border="0" cellpadding="3" cellspacing="0">'.
         '<tr><td class="userpicture" valign="top">'.$OUTPUT->user_picture($activity->user, array('courseid' => $courseid)).
         '</td><td>'.
         '<div class="title">'.
         ($detail ? '<img src="'.$CFG->modpixpath.'/'.$activity->type.'/icon.gif" class="icon" alt="'.s($activity->name).'" />' : ''
         ).
         '<a href="'.$CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$activity->cmid.'#c'.$activity->content->id.'">'.
         $activity->content->comment.'</a>'.
         '</div>'.
         '<div class="user"> '.
         html_writer::link($userviewurl, fullname($activity->user, $viewfullnames)).
         ' - '.userdate($activity->timestamp).
         '</div>'.
         '</td></tr></table>';

    return true;
}


function lightboxgallery_print_recent_activity($course, $viewfullnames, $timestart) {
    global $DB, $CFG, $OUTPUT;

    $userfields = get_all_user_name_fields(true, 'u');
    $sql = "SELECT c.*, l.name, $userfields
              FROM {lightboxgallery_comments} c
                   JOIN {lightboxgallery} l ON l.id = c.gallery
                   JOIN {user}            u ON u.id = c.userid
             WHERE c.timemodified > $timestart AND l.course = {$course->id}
          ORDER BY c.timemodified ASC";

    if ($comments = $DB->get_records_sql($sql)) {
        echo $OUTPUT->heading(get_string('newgallerycomments', 'lightboxgallery').':', 3);

        echo '<ul class="unlist">';

        foreach ($comments as $comment) {
            $display = lightboxgallery_resize_text(trim(strip_tags($comment->commenttext)), MAX_COMMENT_PREVIEW);

            $output = '<li>'.
                 ' <div class="head">'.
                 '  <div class="date">'.userdate($comment->timemodified, get_string('strftimerecent')).'</div>'.
                 '  <div class="name">'.fullname($comment, $viewfullnames).' - '.format_string($comment->name).'</div>'.
                 ' </div>'.
                 ' <div class="info">'.
                 '  "<a href="'.$CFG->wwwroot.'/mod/lightboxgallery/view.php?l='.$comment->gallery.'#c'.$comment->id.'">'.
                 $display.'</a>"'.
                 ' </div>'.
                 '</li>';
            echo $output;
        }

        echo '</ul>';

    }

    return true;
}


function lightboxgallery_get_participants($galleryid) {
    global $DB, $CFG;

    return $DB->get_records_sql("SELECT DISTINCT u.id, u.id
                                   FROM {user} u,
                                        {lightboxgallery_comments} c
                                  WHERE c.gallery = $galleryid AND u.id = c.userid");
}

function lightboxgallery_get_view_actions() {
    return array('view', 'view all', 'search');
}

function lightboxgallery_get_post_actions() {
    return array('comment', 'addimage', 'editimage');
}


function lightboxgallery_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB, $USER;

    require_once($CFG->libdir.'/filelib.php');

    $gallery = $DB->get_record('lightboxgallery', array('id' => $cm->instance));
    if (!$gallery->ispublic) {
        require_login($course, false, $cm);
    }

    $relativepath = implode('/', $args);
    $fullpath = '/'.$context->id.'/mod_lightboxgallery/'.$filearea.'/'.$relativepath;

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, true); 
    return;

}



function lightboxgallery_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['gallery_images'] = get_string('images', 'lightboxgallery');

    return $areas;
}


function lightboxgallery_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if ($filearea === 'gallery_images') {
        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($context->id, 'mod_lightboxgallery', 'gallery_images', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_lightboxgallery', 'gallery_images', 0);
            } else {
                                return null;
            }
        }

        require_once("$CFG->dirroot/mod/lightboxgallery/locallib.php");
        $urlbase = $CFG->wwwroot.'/pluginfile.php';

        return new lightboxgallery_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea],
                                                        true, true, false, false);
    }

    return null;
}


function lightboxgallery_resize_text($text, $length) {
    return core_text::strlen($text) > $length ? core_text::substr($text, 0, $length) . '...' : $text;
}


function lightboxgallery_print_comment($comment, $context) {
    global $DB, $CFG, $COURSE, $OUTPUT;

    
    $user = $DB->get_record('user', array('id' => $comment->userid));

    $deleteurl = new moodle_url('/mod/lightboxgallery/comment.php', array('id' => $comment->gallery, 'delete' => $comment->id));

    echo '<table cellspacing="0" width="50%" class="boxaligncenter datacomment forumpost">'.
         '<tr class="header"><td class="picture left">'.$OUTPUT->user_picture($user, array('courseid' => $COURSE->id)).'</td>'.
         '<td class="topic starter" align="left"><a name="c'.$comment->id.'"></a><div class="author">'.
         '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$COURSE->id.'">'.
         fullname($user, has_capability('moodle/site:viewfullnames', $context)).'</a> - '.userdate($comment->timemodified).
         '</div></td></tr>'.
         '<tr><td class="left side">'.
             '</td><td class="content" align="left">'.
         format_text($comment->commenttext, FORMAT_MOODLE).
         '<div class="commands">'.
         (has_capability('mod/lightboxgallery:edit', $context) ? html_writer::link($deleteurl, get_string('delete')) : '').
         '</div>'.
         '</td></tr></table>';
}


function lightboxgallery_rss_enabled() {
    global $CFG;

    return ($CFG->enablerssfeeds && get_config('lightboxgallery', 'enablerssfeeds'));
}
