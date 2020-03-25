<?php




defined('MOODLE_INTERNAL') || die;


function page_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


function page_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function page_reset_userdata($data) {
    return array();
}


function page_get_view_actions() {
    return array('view','view all');
}


function page_get_post_actions() {
    return array('update', 'add');
}


function page_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    $displayoptions['printheading'] = $data->printheading;
    $displayoptions['printintro']   = $data->printintro;
    $data->displayoptions = serialize($displayoptions);

    if ($mform) {
        $data->content       = $data->page['text'];
        $data->contentformat = $data->page['format'];
    }

    $data->id = $DB->insert_record('page', $data);

        $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);

    if ($mform and !empty($data->page['itemid'])) {
        $draftitemid = $data->page['itemid'];
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_page', 'content', 0, page_get_editor_options($context), $data->content);
        $DB->update_record('page', $data);
    }

    return $data->id;
}


function page_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $data->coursemodule;
    $draftitemid = $data->page['itemid'];

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->revision++;

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    $displayoptions['printheading'] = $data->printheading;
    $displayoptions['printintro']   = $data->printintro;
    $data->displayoptions = serialize($displayoptions);

    $data->content       = $data->page['text'];
    $data->contentformat = $data->page['format'];

    $DB->update_record('page', $data);

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_page', 'content', 0, page_get_editor_options($context), $data->content);
        $DB->update_record('page', $data);
    }

    return true;
}


function page_delete_instance($id) {
    global $DB;

    if (!$page = $DB->get_record('page', array('id'=>$id))) {
        return false;
    }

    
    $DB->delete_records('page', array('id'=>$page->id));

    return true;
}


function page_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if (!$page = $DB->get_record('page', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $page->name;

    if ($coursemodule->showdescription) {
                $info->content = format_module_intro('page', $page, $coursemodule->id, false);
    }

    if ($page->display != RESOURCELIB_DISPLAY_POPUP) {
        return $info;
    }

    $fullurl = "$CFG->wwwroot/mod/page/view.php?id=$coursemodule->id&amp;inpopup=1";
    $options = empty($page->displayoptions) ? array() : unserialize($page->displayoptions);
    $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
    $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
    $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
    $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    return $info;
}



function page_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('content', 'page');
    return $areas;
}


function page_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
                return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'content') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_page', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_page', 'content', 0);
            } else {
                                return null;
            }
        }
        require_once("$CFG->dirroot/mod/page/locallib.php");
        return new page_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, true, false);
    }

    
    return null;
}


function page_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/page:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
                return false;
    }

        $arg = array_shift($args);
    if ($arg == 'index.html' || $arg == 'index.htm') {
                $filename = $arg;

        if (!$page = $DB->get_record('page', array('id'=>$cm->instance), '*', MUST_EXIST)) {
            return false;
        }

                $content = file_rewrite_pluginfile_urls($page->content, 'webservice/pluginfile.php', $context->id, 'mod_page', 'content',
                                                $page->revision);
        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->overflowdiv = true;
        $formatoptions->context = $context;
        $content = format_text($content, $page->contentformat, $formatoptions);

                $options = array('reverse' => true);
        $content = file_rewrite_pluginfile_urls($content, 'webservice/pluginfile.php', $context->id, 'mod_page', 'content',
                                                $page->revision, $options);
        $content = str_replace('@@PLUGINFILE@@/', '', $content);

        send_file($content, $filename, 0, 0, true, true);
    } else {
        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_page/$filearea/0/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            $page = $DB->get_record('page', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
            if ($page->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                return false;
            }
            if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_page', 'content', 0)) {
                return false;
            }
                        $page->legacyfileslast = time();
            $DB->update_record('page', $page);
        }

                send_stored_file($file, null, 0, $forcedownload, $options);
    }
}


function page_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-page-*'=>get_string('page-mod-page-x', 'page'));
    return $module_pagetype;
}


function page_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    $contents = array();
    $context = context_module::instance($cm->id);

    $page = $DB->get_record('page', array('id'=>$cm->instance), '*', MUST_EXIST);

        $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_page', 'content', 0, 'sortorder DESC, id ASC', false);
    foreach ($files as $fileinfo) {
        $file = array();
        $file['type']         = 'file';
        $file['filename']     = $fileinfo->get_filename();
        $file['filepath']     = $fileinfo->get_filepath();
        $file['filesize']     = $fileinfo->get_filesize();
        $file['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_page/content/'.$page->revision.$fileinfo->get_filepath().$fileinfo->get_filename(), true);
        $file['timecreated']  = $fileinfo->get_timecreated();
        $file['timemodified'] = $fileinfo->get_timemodified();
        $file['sortorder']    = $fileinfo->get_sortorder();
        $file['userid']       = $fileinfo->get_userid();
        $file['author']       = $fileinfo->get_author();
        $file['license']      = $fileinfo->get_license();
        $contents[] = $file;
    }

        $filename = 'index.html';
    $pagefile = array();
    $pagefile['type']         = 'file';
    $pagefile['filename']     = $filename;
    $pagefile['filepath']     = '/';
    $pagefile['filesize']     = 0;
    $pagefile['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_page/content/' . $filename, true);
    $pagefile['timecreated']  = null;
    $pagefile['timemodified'] = $page->timemodified;
        $pagefile['sortorder']    = 1;
    $pagefile['userid']       = null;
    $pagefile['author']       = null;
    $pagefile['license']      = null;
    $contents[] = $pagefile;

    return $contents;
}


function page_dndupload_register() {
    return array('types' => array(
                     array('identifier' => 'text/html', 'message' => get_string('createpage', 'page')),
                     array('identifier' => 'text', 'message' => get_string('createpage', 'page'))
                 ));
}


function page_dndupload_handle($uploadinfo) {
        $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    if ($uploadinfo->type == 'text/html') {
        $data->contentformat = FORMAT_HTML;
        $data->content = clean_param($uploadinfo->content, PARAM_CLEANHTML);
    } else {
        $data->contentformat = FORMAT_PLAIN;
        $data->content = clean_param($uploadinfo->content, PARAM_TEXT);
    }
    $data->coursemodule = $uploadinfo->coursemodule;

        $config = get_config('page');
    $data->display = $config->display;
    $data->popupheight = $config->popupheight;
    $data->popupwidth = $config->popupwidth;
    $data->printheading = $config->printheading;
    $data->printintro = $config->printintro;

    return page_add_instance($data, null);
}


function page_view($page, $course, $cm, $context) {

        $params = array(
        'context' => $context,
        'objectid' => $page->id
    );

    $event = \mod_page\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('page', $page);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
