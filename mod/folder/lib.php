<?php




defined('MOODLE_INTERNAL') || die();


define('FOLDER_DISPLAY_PAGE', 0);

define('FOLDER_DISPLAY_INLINE', 1);


function folder_supports($feature) {
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


function folder_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function folder_reset_userdata($data) {
    return array();
}


function folder_get_view_actions() {
    return array('view', 'view all');
}


function folder_get_post_actions() {
    return array('update', 'add');
}


function folder_add_instance($data, $mform = null) {    global $DB;

    $cmid        = $data->coursemodule;
    $draftitemid = $data->files;

    $data->timemodified = time();
    $data->id = $DB->insert_record('folder', $data);

        $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);

    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_folder', 'content', 0, array('subdirs'=>true));
    }

    return $data->id;
}


function folder_update_instance($data, $mform) {
    global $CFG, $DB;

    $cmid        = $data->coursemodule;
    $draftitemid = $data->files;

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->revision++;

    $DB->update_record('folder', $data);

    $context = context_module::instance($cmid);
    if ($draftitemid = file_get_submitted_draft_itemid('files')) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_folder', 'content', 0, array('subdirs'=>true));
    }

    return true;
}


function folder_delete_instance($id) {
    global $DB;

    if (!$folder = $DB->get_record('folder', array('id'=>$id))) {
        return false;
    }

    
    $DB->delete_records('folder', array('id'=>$folder->id));

    return true;
}


function folder_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('foldercontent', 'folder');

    return $areas;
}


function folder_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;


    if ($filearea === 'content') {
        if (!has_capability('mod/folder:view', $context)) {
            return NULL;
        }
        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($context->id, 'mod_folder', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_folder', 'content', 0);
            } else {
                                return null;
            }
        }

        require_once("$CFG->dirroot/mod/folder/locallib.php");
        $urlbase = $CFG->wwwroot.'/pluginfile.php';

                $canwrite = has_capability('mod/folder:managefiles', $context);
        return new folder_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, $canwrite, false);
    }

    
    return null;
}


function folder_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/folder:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
                return false;
    }

    array_shift($args); 
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_folder/content/0/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

            send_stored_file($file, 0, 0, true, $options);
}


function folder_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-folder-*'=>get_string('page-mod-folder-x', 'folder'));
    return $module_pagetype;
}


function folder_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    $contents = array();
    $context = context_module::instance($cm->id);
    $folder = $DB->get_record('folder', array('id'=>$cm->instance), '*', MUST_EXIST);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_folder', 'content', 0, 'sortorder DESC, id ASC', false);

    foreach ($files as $fileinfo) {
        $file = array();
        $file['type'] = 'file';
        $file['filename']     = $fileinfo->get_filename();
        $file['filepath']     = $fileinfo->get_filepath();
        $file['filesize']     = $fileinfo->get_filesize();
        $file['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_folder/content/'.$folder->revision.$fileinfo->get_filepath().$fileinfo->get_filename(), true);
        $file['timecreated']  = $fileinfo->get_timecreated();
        $file['timemodified'] = $fileinfo->get_timemodified();
        $file['sortorder']    = $fileinfo->get_sortorder();
        $file['userid']       = $fileinfo->get_userid();
        $file['author']       = $fileinfo->get_author();
        $file['license']      = $fileinfo->get_license();
        $contents[] = $file;
    }

    return $contents;
}


function folder_dndupload_register() {
    return array('files' => array(
                     array('extension' => 'zip', 'message' => get_string('dnduploadmakefolder', 'mod_folder'))
                 ));
}


function folder_dndupload_handle($uploadinfo) {
    global $DB, $USER;

        $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    $data->coursemodule = $uploadinfo->coursemodule;
    $data->files = null; 
    $data->id = folder_add_instance($data, null);

        $context = context_module::instance($uploadinfo->coursemodule);
    file_save_draft_area_files($uploadinfo->draftitemid, $context->id, 'mod_folder', 'temp', 0, array('subdirs'=>true));
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_folder', 'temp', 0, 'sortorder', false);
        $file = reset($files);

    $success = $file->extract_to_storage(new zip_packer(), $context->id, 'mod_folder', 'content', 0, '/', $USER->id);
    $fs->delete_area_files($context->id, 'mod_folder', 'temp', 0);

    if ($success) {
        return $data->id;
    }

    $DB->delete_records('folder', array('id' => $data->id));
    return false;
}


function folder_get_coursemodule_info($cm) {
    global $DB;
    if (!($folder = $DB->get_record('folder', array('id' => $cm->instance),
            'id, name, display, showexpanded, intro, introformat'))) {
        return NULL;
    }
    $cminfo = new cached_cm_info();
    $cminfo->name = $folder->name;
    if ($folder->display == FOLDER_DISPLAY_INLINE) {
                $fdata = new stdClass();
        $fdata->showexpanded = $folder->showexpanded;
        if ($cm->showdescription && strlen(trim($folder->intro))) {
            $fdata->intro = $folder->intro;
            if ($folder->introformat != FORMAT_MOODLE) {
                $fdata->introformat = $folder->introformat;
            }
        }
        $cminfo->customdata = $fdata;
    } else {
        if ($cm->showdescription) {
                        $cminfo->content = format_module_intro('folder', $folder, $cm->id, false);
        }
    }
    return $cminfo;
}


function folder_cm_info_dynamic(cm_info $cm) {
    if ($cm->customdata) {
                $cm->set_no_view_link();
    }
}


function folder_cm_info_view(cm_info $cm) {
    global $PAGE;
    if ($cm->uservisible && $cm->customdata &&
            has_capability('mod/folder:view', $cm->context)) {
                                $folder = $cm->customdata;
        $folder->id = (int)$cm->instance;
        $folder->course = (int)$cm->course;
        $folder->display = FOLDER_DISPLAY_INLINE;
        $folder->name = $cm->name;
        if (empty($folder->intro)) {
            $folder->intro = '';
        }
        if (empty($folder->introformat)) {
            $folder->introformat = FORMAT_MOODLE;
        }
                $renderer = $PAGE->get_renderer('mod_folder');
        $cm->set_content($renderer->display_folder($folder));
    }
}


function folder_view($folder, $course, $cm, $context) {

        $params = array(
        'context' => $context,
        'objectid' => $folder->id
    );

    $event = \mod_folder\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('folder', $folder);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}


function folder_archive_available($folder, $cm) {
    if (!$folder->showdownloadfolder) {
        return false;
    }

    $context = context_module::instance($cm->id);
    $fs = get_file_storage();
    $dir = $fs->get_area_tree($context->id, 'mod_folder', 'content', 0);

    $size = folder_get_directory_size($dir);
    $maxsize = get_config('folder', 'maxsizetodownload') * 1024 * 1024;

    if ($size == 0) {
        return false;
    }

    if (!empty($maxsize) && $size > $maxsize) {
        return false;
    }

    return true;
}


function folder_get_directory_size($directory) {
    $size = 0;

    foreach ($directory['files'] as $file) {
        $size += $file->get_filesize();
    }

    foreach ($directory['subdirs'] as $subdirectory) {
        $size += folder_get_directory_size($subdirectory);
    }

    return $size;
}


function folder_downloaded($folder, $course, $cm, $context) {
    $params = array(
        'context' => $context,
        'objectid' => $folder->id
    );
    $event = \mod_folder\event\all_files_downloaded::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('folder', $folder);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
