<?php

defined('MOODLE_INTERNAL') || die();

function block_course_menu_format_get_module_metadata($course, $modnames, $sectionreturn = null) {
    global $CFG, $OUTPUT;

    static $modlist = array();
    if (!isset($modlist[$course->id])) {
        $modlist[$course->id] = array();
    }

    $return = array();
    $urlbase = new moodle_url('/course/mod.php', array('id' => $course->id, 'sesskey' => sesskey()));
    if ($sectionreturn !== null) {
        $urlbase->param('sr', $sectionreturn);
    }
    foreach($modnames as $modname => $modnamestr) {
        if (!course_allowed_module($course, $modname)) {
            continue;
        }
        if (isset($modlist[$course->id][$modname])) {
            $return[$modname] = $modlist[$course->id][$modname];
            continue;
        }

        $libfile = "$CFG->dirroot/mod/$modname/lib.php";
        if (!file_exists($libfile)) {
            continue;
        }
        include_once($libfile);
        $module = new stdClass();
        $module->title = $modnamestr;
        $module->name = $modname;
        $module->link = new moodle_url($urlbase, array('add' => $modname));
        $module->icon = $OUTPUT->pix_icon('icon', '', $module->name, array('class' => 'icon'));
        $sm = get_string_manager();
        if ($sm->string_exists('modulename_help', $modname)) {
            $module->help = get_string('modulename_help', $modname);
            if ($sm->string_exists('modulename_link', $modname)) {                  $link = get_string('modulename_link', $modname);
                $linktext = get_string('morehelp');
                $module->help .= html_writer::tag('div', $OUTPUT->doc_link($link, $linktext, true), array('class' => 'helpdoclink'));
            }
        }
        $module->archetype = plugin_supports('mod', $modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
        $modlist[$course->id][$modname] = $module;
        
        if (isset($modlist[$course->id][$modname])) {
            $return[$modname] = $modlist[$course->id][$modname];
        } else {
            debugging("Invalid module metadata configuration for {$modname}");
        }
    }

    return $return;
}