<?php



defined('MOODLE_INTERNAL') || die();


function imscp_supports($feature) {
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


function imscp_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function imscp_reset_userdata($data) {
    return array();
}


function imscp_get_view_actions() {
    return array('view', 'view all');
}


function imscp_get_post_actions() {
    return array('update', 'add');
}


function imscp_add_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/imscp/locallib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $data->revision     = 1;
    $data->structure    = null;

    $data->id = $DB->insert_record('imscp', $data);

        $DB->set_field('course_modules', 'instance', $data->id, array('id' => $cmid));
    $context = context_module::instance($cmid);
    $imscp = $DB->get_record('imscp', array('id' => $data->id), '*', MUST_EXIST);

    if (!empty($data->package)) {
                $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_imscp', 'backup', 1);
        file_save_draft_area_files($data->package, $context->id, 'mod_imscp', 'backup',
            1, array('subdirs' => 0, 'maxfiles' => 1));
                $files = $fs->get_area_files($context->id, 'mod_imscp', 'backup', 1, '', false);
        if ($files) {
                        $package = reset($files);
            $packer = get_file_packer('application/zip');
            $package->extract_to_storage($packer, $context->id, 'mod_imscp', 'content', 1, '/');
            $structure = imscp_parse_structure($imscp, $context);
            $imscp->structure = is_array($structure) ? serialize($structure) : null;
            $DB->update_record('imscp', $imscp);
        }
    }

    return $data->id;
}


function imscp_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/imscp/locallib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->structure   = null; 
    $DB->update_record('imscp', $data);

    $context = context_module::instance($cmid);
    $imscp = $DB->get_record('imscp', array('id' => $data->id), '*', MUST_EXIST);

    if (!empty($data->package) && ($draftareainfo = file_get_draft_area_info($data->package)) &&
            $draftareainfo['filecount']) {
        $fs = get_file_storage();

        $imscp->revision++;
        $DB->update_record('imscp', $imscp);

                if ($imscp->keepold > -1) {
            $packages = $fs->get_area_files($context->id, 'mod_imscp', 'backup', false, "itemid ASC", false);
        } else {
            $packages = array();
        }

        file_save_draft_area_files($data->package, $context->id, 'mod_imscp', 'backup',
            $imscp->revision, array('subdirs' => 0, 'maxfiles' => 1));
        $files = $fs->get_area_files($context->id, 'mod_imscp', 'backup', $imscp->revision, '', false);
        $package = reset($files);

                $fs->delete_area_files($context->id, 'mod_imscp', 'content');

                if ($package) {
            $packer = get_file_packer('application/zip');
            $package->extract_to_storage($packer, $context->id, 'mod_imscp', 'content', $imscp->revision, '/');
        }

                while ($packages and (count($packages) > $imscp->keepold)) {
            $package = array_shift($packages);
            $fs->delete_area_files($context->id, 'mod_imscp', 'backup', $package->get_itemid());
        }
    }

    $structure = imscp_parse_structure($imscp, $context);
    $imscp->structure = is_array($structure) ? serialize($structure) : null;
    $DB->update_record('imscp', $imscp);

    return true;
}


function imscp_delete_instance($id) {
    global $DB;

    if (!$imscp = $DB->get_record('imscp', array('id' => $id))) {
        return false;
    }

    
    $DB->delete_records('imscp', array('id' => $imscp->id));

    return true;
}


function imscp_get_file_areas($course, $cm, $context) {
    $areas = array();

    $areas['content'] = get_string('areacontent', 'imscp');
    $areas['backup']  = get_string('areabackup', 'imscp');

    return $areas;
}


function imscp_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB;

    
    if (!has_capability('moodle/course:managefiles', $context)) {
                return null;
    }

    if ($filearea !== 'content' and $filearea !== 'backup') {
        return null;
    }

    require_once("$CFG->dirroot/mod/imscp/locallib.php");

    if (is_null($itemid)) {
        return new imscp_file_info($browser, $course, $cm, $context, $areas, $filearea, $itemid);
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!$storedfile = $fs->get_file($context->id, 'mod_imscp', $filearea, $itemid, $filepath, $filename)) {
        return null;
    }

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $itemid, true, true, false, false); }


function imscp_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    if ($filearea === 'content') {
        if (!has_capability('mod/imscp:view', $context)) {
            return false;
        }
        $revision = array_shift($args);
        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        if ($relativepath === 'imsmanifest.xml') {
            if (!has_capability('moodle/course:managefiles', $context)) {
                                return false;
            }
        }
        $fullpath = "/$context->id/mod_imscp/$filearea/$revision/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

                send_stored_file($file, null, 0, $forcedownload, $options);

    } else if ($filearea === 'backup') {
        if (!has_capability('moodle/course:managefiles', $context)) {
                        return false;
        }
        $revision = array_shift($args);
        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_imscp/$filearea/$revision/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

                send_stored_file($file, null, 0, $forcedownload, $options);

    } else {
        return false;
    }
}


function imscp_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array('mod-imscp-*' => get_string('page-mod-imscp-x', 'imscp'));
    return $modulepagetype;
}


function imscp_export_contents($cm, $baseurl) {
    global $DB;

    $contents = array();
    $context = context_module::instance($cm->id);

    $imscp = $DB->get_record('imscp', array('id' => $cm->instance), '*', MUST_EXIST);

        $structure = array();
    $structure['type']         = 'content';
    $structure['filename']     = 'structure';
    $structure['filepath']     = '/';
    $structure['filesize']     = 0;
    $structure['fileurl']      = null;
    $structure['timecreated']  = $imscp->timemodified;
    $structure['timemodified'] = $imscp->timemodified;
    $structure['content']      = json_encode(unserialize($imscp->structure));
    $structure['sortorder']    = 0;
    $structure['userid']       = null;
    $structure['author']       = null;
    $structure['license']      = null;
    $contents[] = $structure;

        $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_imscp', 'content', $imscp->revision, 'id ASC', false);
    foreach ($files as $fileinfo) {
        $file = array();
        $file['type']         = 'file';
        $file['filename']     = $fileinfo->get_filename();
        $file['filepath']     = $fileinfo->get_filepath();
        $file['filesize']     = $fileinfo->get_filesize();
        $file['fileurl']      = moodle_url::make_webservice_pluginfile_url(
                                    $context->id, 'mod_imscp', 'content', $imscp->revision,
                                    $fileinfo->get_filepath(), $fileinfo->get_filename())->out(false);
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


function imscp_view($imscp, $course, $cm, $context) {

        $params = array(
        'context' => $context,
        'objectid' => $imscp->id
    );

    $event = \mod_imscp\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('imscp', $imscp);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
