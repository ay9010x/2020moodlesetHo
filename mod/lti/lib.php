<?php



defined('MOODLE_INTERNAL') || die;


function lti_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function lti_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}


function lti_add_instance($lti, $mform) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/lti/locallib.php');

    if (!isset($lti->toolurl)) {
        $lti->toolurl = '';
    }

    lti_load_tool_if_cartridge($lti);

    $lti->timecreated = time();
    $lti->timemodified = $lti->timecreated;
    $lti->servicesalt = uniqid('', true);

    lti_force_type_config_settings($lti, lti_get_type_config_by_instance($lti));

    if (empty($lti->typeid) && isset($lti->urlmatchedtypeid)) {
        $lti->typeid = $lti->urlmatchedtypeid;
    }

    if (!isset($lti->instructorchoiceacceptgrades) || $lti->instructorchoiceacceptgrades != LTI_SETTING_ALWAYS) {
                $lti->grade = 0;
    }

    $lti->id = $DB->insert_record('lti', $lti);

    if (isset($lti->instructorchoiceacceptgrades) && $lti->instructorchoiceacceptgrades == LTI_SETTING_ALWAYS) {
        if (!isset($lti->cmidnumber)) {
            $lti->cmidnumber = '';
        }

        lti_grade_item_update($lti);
    }

    return $lti->id;
}


function lti_update_instance($lti, $mform) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/lti/locallib.php');

    lti_load_tool_if_cartridge($lti);

    $lti->timemodified = time();
    $lti->id = $lti->instance;

    if (!isset($lti->showtitlelaunch)) {
        $lti->showtitlelaunch = 0;
    }

    if (!isset($lti->showdescriptionlaunch)) {
        $lti->showdescriptionlaunch = 0;
    }

    lti_force_type_config_settings($lti, lti_get_type_config_by_instance($lti));

    if (isset($lti->instructorchoiceacceptgrades) && $lti->instructorchoiceacceptgrades == LTI_SETTING_ALWAYS) {
        lti_grade_item_update($lti);
    } else {
                $lti->grade = 0;
        $lti->instructorchoiceacceptgrades = 0;

        lti_grade_item_delete($lti);
    }

    if ($lti->typeid == 0 && isset($lti->urlmatchedtypeid)) {
        $lti->typeid = $lti->urlmatchedtypeid;
    }

    return $DB->update_record('lti', $lti);
}


function lti_delete_instance($id) {
    global $DB;

    if (! $basiclti = $DB->get_record("lti", array("id" => $id))) {
        return false;
    }

    $result = true;

        lti_grade_item_delete($basiclti);

    $ltitype = $DB->get_record('lti_types', array('id' => $basiclti->typeid));
    if ($ltitype) {
        $DB->delete_records('lti_tool_settings',
            array('toolproxyid' => $ltitype->toolproxyid, 'course' => $basiclti->course, 'coursemoduleid' => $id));
    }

    return $DB->delete_records("lti", array("id" => $basiclti->id));
}


function lti_get_shortcuts($defaultitem) {
    global $CFG, $COURSE;
    require_once($CFG->dirroot.'/mod/lti/locallib.php');

    $types = lti_get_configured_types($COURSE->id, $defaultitem->link->param('sr'));
    $types[] = $defaultitem;

        foreach (core_component::get_plugin_list('ltisource') as $pluginname => $dir) {
        if ($moretypes = component_callback("ltisource_$pluginname", 'get_types')) {
                        debugging('Deprecated callback get_types() is found in ltisource_' . $pluginname .
                ', use get_shortcuts() instead', DEBUG_DEVELOPER);
            $grouptitle = get_string('modulenameplural', 'mod_lti');
            foreach ($moretypes as $subtype) {
                                $subtype->title = get_string('activitytypetitle', '',
                    (object)['activity' => $grouptitle, 'type' => $subtype->typestr]);
                                                $subtype->type = str_replace('&amp;', '&', $subtype->type);
                $subtype->name = preg_replace('/.*type=/', '', $subtype->type);
                $subtype->link = new moodle_url($defaultitem->link, array('type' => $subtype->name));
                if (empty($subtype->help) && !empty($subtype->name) &&
                        get_string_manager()->string_exists('help' . $subtype->name, $pluginname)) {
                    $subtype->help = get_string('help' . $subtype->name, $pluginname);
                }
                unset($subtype->typestr);
                $types[] = $subtype;
            }
        }
                                if ($moretypes = component_callback("ltisource_$pluginname", 'get_shortcuts', array($defaultitem))) {
            $types = array_merge($types, $moretypes);
        }
    }
    return $types;
}


function lti_get_coursemodule_info($coursemodule) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/lti/locallib.php');

    if (!$lti = $DB->get_record('lti', array('id' => $coursemodule->instance),
            'icon, secureicon, intro, introformat, name, typeid, toolurl, launchcontainer')) {
        return null;
    }

    $info = new cached_cm_info();

    if ($coursemodule->showdescription) {
                $info->content = format_module_intro('lti', $lti, $coursemodule->id, false);
    }

    if (!empty($lti->typeid)) {
        $toolconfig = lti_get_type_config($lti->typeid);
    } else if ($tool = lti_get_tool_by_url_match($lti->toolurl)) {
        $toolconfig = lti_get_type_config($tool->id);
    } else {
        $toolconfig = array();
    }

            if (lti_request_is_using_ssl() &&
        (!empty($lti->secureicon) || (isset($toolconfig['secureicon']) && !empty($toolconfig['secureicon'])))) {
        if (!empty($lti->secureicon)) {
            $info->iconurl = new moodle_url($lti->secureicon);
        } else {
            $info->iconurl = new moodle_url($toolconfig['secureicon']);
        }
    } else if (!empty($lti->icon)) {
        $info->iconurl = new moodle_url($lti->icon);
    } else if (isset($toolconfig['icon']) && !empty($toolconfig['icon'])) {
        $info->iconurl = new moodle_url($toolconfig['icon']);
    }

        $launchcontainer = lti_get_launch_container($lti, $toolconfig);
    if ($launchcontainer == LTI_LAUNCH_CONTAINER_WINDOW) {
        $launchurl = new moodle_url('/mod/lti/launch.php', array('id' => $coursemodule->id));
        $info->onclick = "window.open('" . $launchurl->out(false) . "', 'lti'); return false;";
    }

    $info->name = $lti->name;

    return $info;
}


function lti_user_outline($course, $user, $mod, $basiclti) {
    return null;
}


function lti_user_complete($course, $user, $mod, $basiclti) {
    return true;
}


function lti_print_recent_activity($course, $isteacher, $timestart) {
    return false;  }


function lti_cron () {
    return true;
}


function lti_grades($basicltiid) {
    return null;
}


function lti_scale_used ($basicltiid, $scaleid) {
    $return = false;

                    
    return $return;
}


function lti_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('lti', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}


function lti_install() {
     return true;
}


function lti_uninstall() {
    return true;
}


function lti_get_lti_types() {
    global $DB;

    return $DB->get_records('lti_types', null, 'state DESC, timemodified DESC');
}


function lti_get_lti_types_from_proxy_id($toolproxyid) {
    global $DB;

    return $DB->get_records('lti_types', array('toolproxyid' => $toolproxyid), 'state DESC, timemodified DESC');
}


function lti_grade_item_update($basiclti, $grades = null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array('itemname' => $basiclti->name, 'idnumber' => $basiclti->cmidnumber);

    if ($basiclti->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $basiclti->grade;
        $params['grademin']  = 0;

    } else if ($basiclti->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$basiclti->grade;

    } else {
        $params['gradetype'] = GRADE_TYPE_TEXT;     }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/lti', $basiclti->course, 'mod', 'lti', $basiclti->id, 0, $grades, $params);
}


function lti_grade_item_delete($basiclti) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/lti', $basiclti->course, 'mod', 'lti', $basiclti->id, 0, null, array('deleted' => 1));
}


function lti_get_post_actions() {
    return array();
}


function lti_get_view_actions() {
    return array('view all', 'view');
}


function lti_view($lti, $course, $cm, $context) {

        $params = array(
        'context' => $context,
        'objectid' => $lti->id
    );

    $event = \mod_lti\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('lti', $lti);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
