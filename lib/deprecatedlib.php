<?php




defined('MOODLE_INTERNAL') || die();




function add_to_log($courseid, $module, $action, $url='', $info='', $cm=0, $user=0) {
    debugging('add_to_log() has been deprecated, please rewrite your code to the new events API', DEBUG_DEVELOPER);

            $manager = get_log_manager();
    if (method_exists($manager, 'legacy_add_to_log')) {
        $manager->legacy_add_to_log($courseid, $module, $action, $url, $info, $cm, $user);
    }
}


function events_trigger($eventname, $eventdata) {
    debugging('events_trigger() is deprecated, please use new events instead', DEBUG_DEVELOPER);
    return events_trigger_legacy($eventname, $eventdata);
}


function get_core_subsystems($fullpaths = false) {
    global $CFG;

    
    $subsystems = core_component::get_core_subsystems();

    if ($fullpaths) {
        return $subsystems;
    }

    debugging('Short paths are deprecated when using get_core_subsystems(), please fix the code to use fullpaths instead.', DEBUG_DEVELOPER);

    $dlength = strlen($CFG->dirroot);

    foreach ($subsystems as $k => $v) {
        if ($v === null) {
            continue;
        }
        $subsystems[$k] = substr($v, $dlength+1);
    }

    return $subsystems;
}


function get_plugin_types($fullpaths = true) {
    global $CFG;

    
    $types = core_component::get_plugin_types();

    if ($fullpaths) {
        return $types;
    }

    debugging('Short paths are deprecated when using get_plugin_types(), please fix the code to use fullpaths instead.', DEBUG_DEVELOPER);

    $dlength = strlen($CFG->dirroot);

    foreach ($types as $k => $v) {
        if ($k === 'theme') {
            $types[$k] = 'theme';
            continue;
        }
        $types[$k] = substr($v, $dlength+1);
    }

    return $types;
}


function get_plugin_list($plugintype) {

    
    if ($plugintype === '') {
        $plugintype = 'mod';
    }

    return core_component::get_plugin_list($plugintype);
}


function get_plugin_list_with_class($plugintype, $class, $file) {

    
    return core_component::get_plugin_list_with_class($plugintype, $class, $file);
}


function get_plugin_directory($plugintype, $name) {

    
    if ($plugintype === '') {
        $plugintype = 'mod';
    }

    return core_component::get_plugin_directory($plugintype, $name);
}


function normalize_component($component) {

    
    return core_component::normalize_component($component);
}


function get_component_directory($component) {

    
    return core_component::get_component_directory($component);
}


function get_context_instance($contextlevel, $instance = 0, $strictness = IGNORE_MISSING) {

    debugging('get_context_instance() is deprecated, please use context_xxxx::instance() instead.', DEBUG_DEVELOPER);

    $instances = (array)$instance;
    $contexts = array();

    $classname = context_helper::get_class_for_level($contextlevel);

        foreach ($instances as $inst) {
        $contexts[$inst] = $classname::instance($inst, $strictness);
    }

    if (is_array($instance)) {
        return $contexts;
    } else {
        return $contexts[$instance];
    }
}



function clam_log_upload($newfilepath, $course=null, $nourl=false) {
    throw new coding_exception('clam_log_upload() can not be used any more, please use file picker instead');
}


function clam_log_infected($oldfilepath='', $newfilepath='', $userid=0) {
    throw new coding_exception('clam_log_infected() can not be used any more, please use file picker instead');
}


function clam_change_log($oldpath, $newpath, $update=true) {
    throw new coding_exception('clam_change_log() can not be used any more, please use file picker instead');
}


function clam_replace_infected_file($file) {
    throw new coding_exception('clam_replace_infected_file() can not be used any more, please use file picker instead');
}


function clam_handle_infected_file($file, $userid=0, $basiconly=false) {
    throw new coding_exception('clam_handle_infected_file() can not be used any more, please use file picker instead');
}


function clam_scan_moodle_file(&$file, $course) {
    throw new coding_exception('clam_scan_moodle_file() can not be used any more, please use file picker instead');
}



function password_compat_not_supported() {
    throw new coding_exception('Do not use password_compat_not_supported() - bcrypt is now always available');
}


function session_get_instance() {
    throw new coding_exception('session_get_instance() is removed, use \core\session\manager instead');
}


function session_is_legacy() {
    throw new coding_exception('session_is_legacy() is removed, do not use any more');
}


function session_kill_all() {
    throw new coding_exception('session_kill_all() is removed, use \core\session\manager::kill_all_sessions() instead');
}


function session_touch($sid) {
    throw new coding_exception('session_touch() is removed, use \core\session\manager::touch_session() instead');
}


function session_kill($sid) {
    throw new coding_exception('session_kill() is removed, use \core\session\manager::kill_session() instead');
}


function session_kill_user($userid) {
    throw new coding_exception('session_kill_user() is removed, use \core\session\manager::kill_user_sessions() instead');
}


function session_set_user($user) {
    throw new coding_exception('session_set_user() is removed, use \core\session\manager::set_user() instead');
}


function session_is_loggedinas() {
    throw new coding_exception('session_is_loggedinas() is removed, use \core\session\manager::is_loggedinas() instead');
}


function session_get_realuser() {
    throw new coding_exception('session_get_realuser() is removed, use \core\session\manager::get_realuser() instead');
}


function session_loginas($userid, $context) {
    throw new coding_exception('session_loginas() is removed, use \core\session\manager::loginas() instead');
}


function js_minify($files) {
    throw new coding_exception('js_minify() is removed, use core_minify::js_files() or core_minify::js() instead.');
}


function css_minify_css($files) {
    throw new coding_exception('css_minify_css() is removed, use core_minify::css_files() or core_minify::css() instead.');
}



function check_gd_version() {
    throw new coding_exception('check_gd_version() is removed, GD extension is always available now');
}


function update_login_count() {
    throw new coding_exception('update_login_count() is removed, all calls need to be removed');
}


function reset_login_count() {
    throw new coding_exception('reset_login_count() is removed, all calls need to be removed');
}


function update_log_display_entry($module, $action, $mtable, $field) {

    throw new coding_exception('The update_log_display_entry() is removed, please use db/log.php description file instead.');
}


function filter_text($text, $courseid = NULL) {
    throw new coding_exception('filter_text() can not be used anymore, use format_text(), format_string() etc instead.');
}


function httpsrequired() {
    throw new coding_exception('httpsrequired() can not be used any more use $PAGE->https_required() instead.');
}


function get_file_url($path, $options=null, $type='coursefile') {
    debugging('Function get_file_url() is deprecated, please use moodle_url factory methods instead.', DEBUG_DEVELOPER);
    global $CFG;

    $path = str_replace('//', '/', $path);
    $path = trim($path, '/'); 
        switch ($type) {
       case 'questionfile':
            $url = $CFG->wwwroot."/question/exportfile.php";
            break;
       case 'rssfile':
            $url = $CFG->wwwroot."/rss/file.php";
            break;
        case 'httpscoursefile':
            $url = $CFG->httpswwwroot."/file.php";
            break;
         case 'coursefile':
        default:
            $url = $CFG->wwwroot."/file.php";
    }

    if ($CFG->slasharguments) {
        $parts = explode('/', $path);
        foreach ($parts as $key => $part) {
                    $subparts = explode('#', $part);
            $subparts = array_map('rawurlencode', $subparts);
            $parts[$key] = implode('#', $subparts);
        }
        $path  = implode('/', $parts);
        $ffurl = $url.'/'.$path;
        $separator = '?';
    } else {
        $path = rawurlencode('/'.$path);
        $ffurl = $url.'?file='.$path;
        $separator = '&amp;';
    }

    if ($options) {
        foreach ($options as $name=>$value) {
            $ffurl = $ffurl.$separator.$name.'='.$value;
            $separator = '&amp;';
        }
    }

    return $ffurl;
}


function get_course_participants($courseid) {
    throw new coding_exception('get_course_participants() can not be used any more, use get_enrolled_users() instead.');
}


function is_course_participant($userid, $courseid) {
    throw new coding_exception('is_course_participant() can not be used any more, use is_enrolled() instead.');
}


function get_recent_enrolments($courseid, $timestart) {
    throw new coding_exception('get_recent_enrolments() is removed as it returned inaccurate results.');
}


function detect_munged_arguments($string, $allowdots=1) {
    throw new coding_exception('detect_munged_arguments() can not be used any more, please use clean_param(,PARAM_FILE) instead.');
}



function unzip_file($zipfile, $destination = '', $showstatus_ignored = true) {
    global $CFG;

        $path_parts = pathinfo(cleardoubleslashes($zipfile));
    $zippath = $path_parts["dirname"];           $zipfilename = $path_parts["basename"];      $extension = $path_parts["extension"];    
        if (empty($zipfilename)) {
        return false;
    }

        if (empty($extension)) {
        return false;
    }

        $zipfile = cleardoubleslashes($zipfile);

        if (!file_exists($zipfile)) {
        return false;
    }

        if (empty($destination)) {
        $destination = $zippath;
    }

        $destpath = rtrim(cleardoubleslashes($destination), "/");

        if (!is_dir($destpath)) {
        return false;
    }

    $packer = get_file_packer('application/zip');

    $result = $packer->extract_to_pathname($zipfile, $destpath);

    if ($result === false) {
        return false;
    }

    foreach ($result as $status) {
        if ($status !== true) {
            return false;
        }
    }

    return true;
}


function zip_files ($originalfiles, $destination) {
    global $CFG;

        $path_parts = pathinfo(cleardoubleslashes($destination));
    $destpath = $path_parts["dirname"];           $destfilename = $path_parts["basename"];      $extension = $path_parts["extension"];    
        if (empty($destfilename)) {
        return false;
    }

        if (empty($extension)) {
        $extension = 'zip';
        $destfilename = $destfilename.'.'.$extension;
    }

        if (!is_dir($destpath)) {
        return false;
    }

    
        $destfilename = clean_filename($destfilename);

        $files = array();
    $origpath = NULL;

    foreach ($originalfiles as $file) {                  $tempfile = cleardoubleslashes($file);                 if ($origpath === NULL) {
            $origpath = rtrim(cleardoubleslashes(dirname($tempfile)), "/");
        }
                if (!is_readable($tempfile)) {              continue;
        }
                if (rtrim(cleardoubleslashes(dirname($tempfile)), "/") != $origpath) {
            continue;
        }
                $files[] = $tempfile;
    }

    $zipfiles = array();
    $start = strlen($origpath)+1;
    foreach($files as $file) {
        $zipfiles[substr($file, $start)] = $file;
    }

    $packer = get_file_packer('application/zip');

    return $packer->archive_to_pathname($zipfiles, $destpath . '/' . $destfilename);
}


function mygroupid($courseid) {
    throw new coding_exception('mygroupid() can not be used any more, please use groups_get_all_groups() instead.');
}



function groupmode($course, $cm=null) {

    debugging('groupmode() is deprecated, please use groups_get_* instead', DEBUG_DEVELOPER);
    if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
        return $cm->groupmode;
    }
    return $course->groupmode;
}


function set_current_group($courseid, $groupid) {
    global $SESSION;

    debugging('set_current_group() is deprecated, please use $SESSION->currentgroup[$courseid] instead', DEBUG_DEVELOPER);
    return $SESSION->currentgroup[$courseid] = $groupid;
}


function get_current_group($courseid, $full = false) {
    global $SESSION;

    debugging('get_current_group() is deprecated, please use groups_get_* instead', DEBUG_DEVELOPER);
    if (isset($SESSION->currentgroup[$courseid])) {
        if ($full) {
            return groups_get_group($SESSION->currentgroup[$courseid]);
        } else {
            return $SESSION->currentgroup[$courseid];
        }
    }

    $mygroupid = mygroupid($courseid);
    if (is_array($mygroupid)) {
        $mygroupid = array_shift($mygroupid);
        set_current_group($courseid, $mygroupid);
        if ($full) {
            return groups_get_group($mygroupid);
        } else {
            return $mygroupid;
        }
    }

    if ($full) {
        return false;
    } else {
        return 0;
    }
}


function groups_filter_users_by_course_module_visible($cm, $users) {
    throw new coding_exception('groups_filter_users_by_course_module_visible() is removed. ' .
            'Replace with a call to \core_availability\info_module::filter_user_list(), ' .
            'which does basically the same thing but includes other restrictions such ' .
            'as profile restrictions.');
}


function groups_course_module_visible($cm, $userid=null) {
    throw new coding_exception('groups_course_module_visible() is removed, use $cm->uservisible to decide whether the current
        user can ' . 'access an activity.', DEBUG_DEVELOPER);
}


function error($message, $link='') {
    throw new coding_exception('notlocalisederrormessage', 'error', $link, $message, 'error() is a removed, please call
            print_error() instead of error()');
}



function current_theme() {
    throw new coding_exception('current_theme() can not be used any more, please use $PAGE->theme->name instead');
}


function formerr($error) {
    throw new coding_exception('formerr() is removed. Please change your code to use $OUTPUT->error_text($string).');
}


function skip_main_destination() {
    throw new coding_exception('skip_main_destination() can not be used any more, please use $OUTPUT->skip_link_target() instead.');
}


function print_container($message, $clearfix=false, $classes='', $idbase='', $return=false) {
    throw new coding_exception('print_container() can not be used any more. Please use $OUTPUT->container() instead.');
}


function print_container_start($clearfix=false, $classes='', $idbase='', $return=false) {
    throw new coding_exception('print_container_start() can not be used any more. Please use $OUTPUT->container_start() instead.');
}


function print_container_end($return=false) {
    throw new coding_exception('print_container_end() can not be used any more. Please use $OUTPUT->container_end() instead.');
}


function notify($message, $classes = 'error', $align = 'center', $return = false) {
    global $OUTPUT;

    debugging('notify() is deprecated, please use $OUTPUT->notification() instead', DEBUG_DEVELOPER);

    if ($classes == 'green') {
        debugging('Use of deprecated class name "green" in notify. Please change to "success".', DEBUG_DEVELOPER);
        $classes = 'success';     }

    $output = $OUTPUT->notification($message, $classes);
    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function print_continue($link, $return = false) {
    throw new coding_exception('print_continue() can not be used any more. Please use $OUTPUT->continue_button() instead.');
}


function print_header($title='', $heading='', $navigation='', $focus='',
                      $meta='', $cache=true, $button='&nbsp;', $menu=null,
                      $usexml=false, $bodytags='', $return=false) {

    throw new coding_exception('print_header() can not be used any more. Please use $PAGE methods instead.');
}


function print_header_simple($title='', $heading='', $navigation='', $focus='', $meta='',
                       $cache=true, $button='&nbsp;', $menu='', $usexml=false, $bodytags='', $return=false) {

    throw new coding_exception('print_header_simple() can not be used any more. Please use $PAGE methods instead.');
}


function print_side_block($heading='', $content='', $list=NULL, $icons=NULL, $footer='', $attributes = array(), $title='') {
    throw new coding_exception('print_side_block() can not be used any more, please use $OUTPUT->block() instead.');
}


function print_textarea($unused, $rows, $cols, $width, $height, $name, $value='', $obsolete=0, $return=false, $id='') {
        
        
    global $CFG;

    $mincols = 65;
    $minrows = 10;
    $str = '';

    if ($id === '') {
        $id = 'edit-'.$name;
    }

    if ($height && ($rows < $minrows)) {
        $rows = $minrows;
    }
    if ($width && ($cols < $mincols)) {
        $cols = $mincols;
    }

    editors_head_setup();
    $editor = editors_get_preferred_editor(FORMAT_HTML);
    $editor->set_text($value);
    $editor->use_editor($id, array('legacy'=>true));

    $str .= "\n".'<textarea class="form-textarea" id="'. $id .'" name="'. $name .'" rows="'. $rows .'" cols="'. $cols .'" spellcheck="true">'."\n";
    $str .= htmlspecialchars($value);     $str .= '</textarea>'."\n";

    if ($return) {
        return $str;
    }
    echo $str;
}


function print_arrow($direction='up', $strsort=null, $return=false) {
    global $OUTPUT;

    debugging('print_arrow() is deprecated. Please use $OUTPUT->arrow() instead.', DEBUG_DEVELOPER);

    if (!in_array($direction, array('up', 'down', 'right', 'left', 'move'))) {
        return null;
    }

    $return = null;

    switch ($direction) {
        case 'up':
            $sortdir = 'asc';
            break;
        case 'down':
            $sortdir = 'desc';
            break;
        case 'move':
            $sortdir = 'asc';
            break;
        default:
            $sortdir = null;
            break;
    }

        $strsort = '';
    if (empty($strsort) && !empty($sortdir)) {
        $strsort  = get_string('sort' . $sortdir, 'grades');
    }

    $return = ' <img src="'.$OUTPUT->pix_url('t/' . $direction) . '" alt="'.$strsort.'" /> ';

    if ($return) {
        return $return;
    } else {
        echo $return;
    }
}


function choose_from_menu ($options, $name, $selected='', $nothing='choose', $script='',
                           $nothingvalue='0', $return=false, $disabled=false, $tabindex=0,
                           $id='', $listbox=false, $multiple=false, $class='') {
    throw new coding_exception('choose_from_menu() is removed. Please change your code to use html_writer::select().');

}


function print_scale_menu_helpbutton($courseid, $scale, $return=false) {
    throw new coding_exception('print_scale_menu_helpbutton() can not be used any more. '.
        'Please use $OUTPUT->help_icon_scale($courseid, $scale) instead.');
}


function print_checkbox($name, $value, $checked = true, $label = '', $alt = '', $script='', $return=false) {
    throw new coding_exception('print_checkbox() can not be used any more. Please use html_writer::checkbox() instead.');
}


function update_module_button($cmid, $ignored, $string) {
    global $CFG, $OUTPUT;

    
    
    if (has_capability('moodle/course:manageactivities', context_module::instance($cmid))) {
        $string = get_string('updatethis', '', $string);

        $url = new moodle_url("$CFG->wwwroot/course/mod.php", array('update' => $cmid, 'return' => true, 'sesskey' => sesskey()));
        return $OUTPUT->single_button($url, $string);
    } else {
        return '';
    }
}


function print_navigation ($navigation, $separator=0, $return=false) {
    throw new coding_exception('print_navigation() can not be used any more, please update use $OUTPUT->navbar() instead.');
}


function build_navigation($extranavlinks, $cm = null) {
    throw new coding_exception('build_navigation() can not be used any more, please use $PAGE->navbar methods instead.');
}


function navmenu($course, $cm=NULL, $targetwindow='self') {
    throw new coding_exception('navmenu() can not be used any more, it is no longer relevant with global navigation.');
}




function add_event($event) {
    throw new coding_exception('add_event() can not be used any more, please use calendar_event::create() instead.');
}


function update_event($event) {
    throw new coding_exception('update_event() is removed, please use calendar_event->update() instead.');
}


function delete_event($id) {
    throw new coding_exception('delete_event() can not be used any more, please use '.
        'calendar_event->delete() instead.');
}


function hide_event($event) {
    throw new coding_exception('hide_event() can not be used any more, please use '.
        'calendar_event->toggle_visibility(false) instead.');
}


function show_event($event) {
    throw new coding_exception('show_event() can not be used any more, please use '.
        'calendar_event->toggle_visibility(true) instead.');
}


function textlib_get_instance() {
    throw new coding_exception('textlib_get_instance() can not be used any more, please use '.
        'core_text::functioname() instead.');
}


function get_generic_section_name($format, stdClass $section) {
    throw new coding_exception('get_generic_section_name() is deprecated. Please use appropriate functionality from class format_base');
}


function get_all_sections($courseid) {

    throw new coding_exception('get_all_sections() is removed. See phpdocs for this function');
}


function add_mod_to_section($mod, $beforemod = null) {
    throw new coding_exception('Function add_mod_to_section() is removed, please use course_add_cm_to_section()');
}


function get_all_mods($courseid, &$mods, &$modnames, &$modnamesplural, &$modnamesused) {
    throw new coding_exception('Function get_all_mods() is removed. Use get_fast_modinfo() and get_module_types_names() instead. See phpdocs for details');
}


function get_course_section($section, $courseid) {
    throw new coding_exception('Function get_course_section() is removed. Please use course_create_sections_if_missing() and get_fast_modinfo() instead.');
}


function format_weeks_get_section_dates($section, $course) {
    throw new coding_exception('Function format_weeks_get_section_dates() is removed. It is not recommended to'.
            ' use it outside of format_weeks plugin');
}


function get_print_section_cm_text(cm_info $cm, $course) {
    throw new coding_exception('Function get_print_section_cm_text() is removed. Please use '.
            'cm_info::get_formatted_content() and cm_info::get_formatted_name()');
}


function print_section_add_menus($course, $section, $modnames = null, $vertical=false, $return=false, $sectionreturn=null) {
    throw new coding_exception('Function print_section_add_menus() is removed. Please use course renderer '.
            'function course_section_add_cm_control()');
}


function make_editing_buttons(stdClass $mod, $absolute_ignored = true, $moveselect = true, $indent=-1, $section=null) {
    throw new coding_exception('Function make_editing_buttons() is removed, please see PHPdocs in '.
            'lib/deprecatedlib.php on how to replace it');
}


function print_section($course, $section, $mods, $modnamesused, $absolute=false, $width="100%", $hidecompletion=false, $sectionreturn=null) {
    throw new coding_exception('Function print_section() is removed. Please use course renderer function '.
            'course_section_cm_list() instead.');
}


function print_overview($courses, array $remote_courses=array()) {
    throw new coding_exception('Function print_overview() is removed. Use block course_overview to display this information');
}


function print_recent_activity($course) {
    throw new coding_exception('Function print_recent_activity() is removed. It is not recommended to'.
            ' use it outside of block_recent_activity');
}


function delete_course_module($id) {
    throw new coding_exception('Function delete_course_module() is removed. Please use course_delete_module() instead.');
}


function update_category_button($categoryid = 0) {
    throw new coding_exception('Function update_category_button() is removed. Pages to view '.
            'and edit courses are now separate and no longer depend on editing mode.');
}


function make_categories_list(&$list, &$parents, $requiredcapability = '',
        $excludeid = 0, $category = NULL, $path = "") {
    throw new coding_exception('Global function make_categories_list() is removed. Please use '.
            'coursecat::make_categories_list() and coursecat::get_parents()');
}


function category_delete_move($category, $newparentid, $showfeedback=true) {
    throw new coding_exception('Function category_delete_move() is removed. Please use coursecat::delete_move() instead.');
}


function category_delete_full($category, $showfeedback=true) {
    throw new coding_exception('Function category_delete_full() is removed. Please use coursecat::delete_full() instead.');
}


function move_category($category, $newparentcat) {
    throw new coding_exception('Function move_category() is removed. Please use coursecat::change_parent() instead.');
}


function course_category_hide($category) {
    throw new coding_exception('Function course_category_hide() is removed. Please use coursecat::hide() instead.');
}


function course_category_show($category) {
    throw new coding_exception('Function course_category_show() is removed. Please use coursecat::show() instead.');
}


function get_course_category($catid=0) {
    throw new coding_exception('Function get_course_category() is removed. Please use coursecat::get(), see phpdocs for more details');
}


function create_course_category($category) {
    throw new coding_exception('Function create_course_category() is removed. Please use coursecat::create(), see phpdocs for more details');
}


function get_all_subcategories($catid) {
    throw new coding_exception('Function get_all_subcategories() is removed. Please use appropriate methods() of coursecat
            class. See phpdocs for more details');
}


function get_child_categories($parentid) {
    throw new coding_exception('Function get_child_categories() is removed. Use coursecat::get_children() or see phpdocs for
            more details.');
}


function get_categories($parent='none', $sort=NULL, $shallow=true) {
    throw new coding_exception('Function get_categories() is removed. Please use coursecat::get_children() or see phpdocs for other alternatives');
}


function print_course_search($value="", $return=false, $format="plain") {
    throw new coding_exception('Function print_course_search() is removed, please use course renderer');
}


function print_my_moodle() {
    throw new coding_exception('Function print_my_moodle() is removed, please use course renderer function frontpage_my_courses()');
}


function print_remote_course($course, $width="100%") {
    throw new coding_exception('Function print_remote_course() is removed, please use course renderer');
}


function print_remote_host($host, $width="100%") {
    throw new coding_exception('Function print_remote_host() is removed, please use course renderer');
}


function print_whole_category_list($category=NULL, $displaylist=NULL, $parentslist=NULL, $depth=-1, $showcourses = true, $categorycourses=NULL) {
    throw new coding_exception('Function print_whole_category_list() is removed, please use course renderer');
}


function print_category_info($category, $depth = 0, $showcourses = false, array $courses = null) {
    throw new coding_exception('Function print_category_info() is removed, please use course renderer');
}


function get_course_category_tree($id = 0, $depth = 0) {
    throw new coding_exception('Function get_course_category_tree() is removed, please use course renderer or coursecat class,
            see function phpdocs for more info');
}


function print_courses($category) {
    throw new coding_exception('Function print_courses() is removed, please use course renderer');
}


function print_course($course, $highlightterms = '') {
    throw new coding_exception('Function print_course() is removed, please use course renderer');
}


function get_category_courses_array($categoryid = 0) {
    throw new coding_exception('Function get_category_courses_array() is removed, please use methods of coursecat class');
}


function get_category_courses_array_recursively(array &$flattened, $category) {
    throw new coding_exception('Function get_category_courses_array_recursively() is removed, please use methods of coursecat class', DEBUG_DEVELOPER);
}


function blog_get_context_url($context=null) {
    throw new coding_exception('Function  blog_get_context_url() is removed, getting params from context is not reliable for blogs.');
}


function get_courses_wmanagers($categoryid=0, $sort="c.sortorder ASC", $fields=array()) {
    throw new coding_exception('Function get_courses_wmanagers() is removed, please use coursecat::get_courses()');
}


function convert_tree_to_html($tree, $row=0) {
    throw new coding_exception('Function convert_tree_to_html() is removed. Consider using class tabtree and core_renderer::render_tabtree()');
}


function convert_tabrows_to_tree($tabrows, $selected, $inactive, $activated) {
    throw new coding_exception('Function convert_tabrows_to_tree() is removed. Consider using class tabtree');
}


function can_use_rotated_text() {
    debugging('can_use_rotated_text() is removed. JS feature detection is used automatically.');
}


function get_context_instance_by_id($id, $strictness = IGNORE_MISSING) {
    throw new coding_exception('get_context_instance_by_id() is now removed, please use context::instance_by_id($id) instead.');
}


function get_system_context($cache = true) {
    debugging('get_system_context() is deprecated, please use context_system::instance() instead.', DEBUG_DEVELOPER);
    return context_system::instance(0, IGNORE_MISSING, $cache);
}


function get_parent_contexts(context $context, $includeself = false) {
    throw new coding_exception('get_parent_contexts() is removed, please use $context->get_parent_context_ids() instead.');
}


function get_parent_contextid(context $context) {
    throw new coding_exception('get_parent_contextid() is removed, please use $context->get_parent_context() instead.');
}


function get_child_contexts(context $context) {
    throw new coding_exception('get_child_contexts() is removed, please use $context->get_child_contexts() instead.');
}


function create_contexts($contextlevel = null, $buildpaths = true) {
    throw new coding_exception('create_contexts() is removed, please use context_helper::create_instances() instead.');
}


function cleanup_contexts() {
    throw new coding_exception('cleanup_contexts() is removed, please use context_helper::cleanup_instances() instead.');
}


function build_context_path($force = false) {
    throw new coding_exception('build_context_path() is removed, please use context_helper::build_all_paths() instead.');
}


function rebuild_contexts(array $fixcontexts) {
    throw new coding_exception('rebuild_contexts() is removed, please use $context->reset_paths(true) instead.');
}


function preload_course_contexts($courseid) {
    throw new coding_exception('preload_course_contexts() is removed, please use context_helper::preload_course() instead.');
}


function context_moved(context $context, context $newparent) {
    throw new coding_exception('context_moved() is removed, please use context::update_moved() instead.');
}


function fetch_context_capabilities(context $context) {
    throw new coding_exception('fetch_context_capabilities() is removed, please use $context->get_capabilities() instead.');
}


function context_instance_preload(stdClass $rec) {
    throw new coding_exception('context_instance_preload() is removed, please use context_helper::preload_from_record() instead.');
}


function get_contextlevel_name($contextlevel) {
    throw new coding_exception('get_contextlevel_name() is removed, please use context_helper::get_level_name() instead.');
}


function print_context_name(context $context, $withprefix = true, $short = false) {
    throw new coding_exception('print_context_name() is removed, please use $context->get_context_name() instead.');
}


function mark_context_dirty($path) {
    throw new coding_exception('mark_context_dirty() is removed, please use $context->mark_dirty() instead.');
}


function delete_context($contextlevel, $instanceid, $deleterecord = true) {
    if ($deleterecord) {
        throw new coding_exception('delete_context() is removed, please use context_helper::delete_instance() instead.');
    } else {
        throw new coding_exception('delete_context() is removed, please use $context->delete_content() instead.');
    }
}


function get_context_url(context $context) {
    throw new coding_exception('get_context_url() is removed, please use $context->get_url() instead.');
}


function get_course_context(context $context) {
    throw new coding_exception('get_course_context() is removed, please use $context->get_course_context(true) instead.');
}


function get_user_courses_bycap($userid, $cap, $accessdata_ignored, $doanything_ignored, $sort = 'c.sortorder ASC', $fields = null, $limit_ignored = 0) {

    throw new coding_exception('get_user_courses_bycap() is removed, please use enrol_get_users_courses() instead.');
}


function get_role_context_caps($roleid, context $context) {
    throw new coding_exception('get_role_context_caps() is removed, it is really slow. Don\'t use it.');
}


function get_courseid_from_context(context $context) {
    throw new coding_exception('get_courseid_from_context() is removed, please use $context->get_course_context(false) instead.');
}


function context_instance_preload_sql($joinon, $contextlevel, $tablealias) {
    throw new coding_exception('context_instance_preload_sql() is removed, please use context_helper::get_preload_record_columns_sql() instead.');
}


function get_related_contexts_string(context $context) {
    throw new coding_exception('get_related_contexts_string() is removed, please use $context->get_parent_context_ids(true) instead.');
}


function get_plugin_list_with_file($plugintype, $file, $include = false) {
    throw new coding_exception('get_plugin_list_with_file() is removed, please use core_component::get_plugin_list_with_file() instead.');
}


function check_browser_operating_system($brand) {
    throw new coding_exception('check_browser_operating_system is removed, please update your code to use core_useragent instead.');
}


function check_browser_version($brand, $version = null) {
    throw new coding_exception('check_browser_version is removed, please update your code to use core_useragent instead.');
}


function get_device_type() {
    throw new coding_exception('get_device_type is removed, please update your code to use core_useragent instead.');
}


function get_device_type_list($incusertypes = true) {
    throw new coding_exception('get_device_type_list is removed, please update your code to use core_useragent instead.');
}


function get_selected_theme_for_device_type($devicetype = null) {
    throw new coding_exception('get_selected_theme_for_device_type is removed, please update your code to use core_useragent instead.');
}


function get_device_cfg_var_name($devicetype = null) {
    throw new coding_exception('get_device_cfg_var_name is removed, please update your code to use core_useragent instead.');
}


function set_user_device_type($newdevice) {
    throw new coding_exception('set_user_device_type is removed, please update your code to use core_useragent instead.');
}


function get_user_device_type() {
    throw new coding_exception('get_user_device_type is removed, please update your code to use core_useragent instead.');
}


function get_browser_version_classes() {
    throw new coding_exception('get_browser_version_classes is removed, please update your code to use core_useragent instead.');
}


function generate_email_supportuser() {
    throw new coding_exception('generate_email_supportuser is removed, please use core_user::get_support_user');
}


function badges_get_issued_badge_info($hash) {
    throw new coding_exception('Function badges_get_issued_badge_info() is removed. Please use core_badges_assertion class and methods to generate badge assertion.');
}


function can_use_html_editor() {
    throw new coding_exception('can_use_html_editor is removed, please update your code to assume it returns true.');
}



function count_login_failures($mode, $username, $lastlogin) {
    throw new coding_exception('count_login_failures() can not be used any more, please use user_count_login_failures().');
}


function ajaxenabled(array $browsers = null) {
    throw new coding_exception('ajaxenabled() can not be used anymore. Update your code to work with JS at all times.');
}


function coursemodule_visible_for_user($cm, $userid=0) {
    throw new coding_exception('coursemodule_visible_for_user() can not be used any more,
            please use \core_availability\info_module::is_user_visible()');
}


function enrol_cohort_get_cohorts(course_enrolment_manager $manager) {
    throw new coding_exception('Function enrol_cohort_get_cohorts() is removed, use enrol_cohort_search_cohorts() or '.
        'cohort_get_available_cohorts() instead');
}


function enrol_cohort_can_view_cohort($cohortid) {
    throw new coding_exception('Function enrol_cohort_can_view_cohort() is removed, use cohort_can_view_cohort() instead');
}


function cohort_get_visible_list($course, $onlyenrolled=true) {
    throw new coding_exception('Function cohort_get_visible_list() is removed. Please use function cohort_get_available_cohorts() ".
        "that correctly checks capabilities.');
}


function enrol_cohort_enrol_all_users(course_enrolment_manager $manager, $cohortid, $roleid) {
    throw new coding_exception('enrol_cohort_enrol_all_users() is removed. This functionality is moved to enrol_manual.');
}


function enrol_cohort_search_cohorts(course_enrolment_manager $manager, $offset = 0, $limit = 25, $search = '') {
    throw new coding_exception('enrol_cohort_search_cohorts() is removed. This functionality is moved to enrol_manual.');
}




function message_current_user_is_involved($user1, $user2) {
    throw new coding_exception('message_current_user_is_involved() can not be used any more.');
}


function profile_display_badges($userid, $courseid = 0) {
    throw new coding_exception('profile_display_badges() can not be used any more.');
}


function useredit_shared_definition_preferences($user, &$mform, $editoroptions = null, $filemanageroptions = null) {
    throw new coding_exception('useredit_shared_definition_preferences() can not be used any more.');
}



function calendar_normalize_tz($tz) {
    throw new coding_exception('calendar_normalize_tz() can not be used any more, please use core_date::normalise_timezone() instead.');
}


function get_user_timezone_offset($tz = 99) {
    throw new coding_exception('get_user_timezone_offset() can not be used any more, please use standard PHP DateTimeZone class instead');

}


function get_timezone_offset($tz) {
    throw new coding_exception('get_timezone_offset() can not be used any more, please use standard PHP DateTimeZone class instead');
}


function get_list_of_timezones() {
    throw new coding_exception('get_list_of_timezones() can not be used any more, please use core_date::get_list_of_timezones() instead');
}


function update_timezone_records($timezones) {
    throw new coding_exception('update_timezone_records() can not be used any more, please use standard PHP DateTime class instead');
}


function calculate_user_dst_table($fromyear = null, $toyear = null, $strtimezone = null) {
    throw new coding_exception('calculate_user_dst_table() can not be used any more, please use standard PHP DateTime class instead');
}


function dst_changes_for_year($year, $timezone) {
    throw new coding_exception('dst_changes_for_year() can not be used any more, please use standard DateTime class instead');
}


function get_timezone_record($timezonename) {
    throw new coding_exception('get_timezone_record() can not be used any more, please use standard PHP DateTime class instead');
}



function get_referer($stripquery = true) {
    debugging('get_referer() is deprecated. Please use get_local_referer() instead.', DEBUG_DEVELOPER);
    if (isset($_SERVER['HTTP_REFERER'])) {
        if ($stripquery) {
            return strip_querystring($_SERVER['HTTP_REFERER']);
        } else {
            return $_SERVER['HTTP_REFERER'];
        }
    } else {
        return '';
    }
}


function is_web_crawler() {
    debugging('is_web_crawler() has been deprecated, please use core_useragent::is_web_crawler() instead.', DEBUG_DEVELOPER);
    return core_useragent::is_web_crawler();
}


function completion_cron() {
    global $CFG;
    require_once($CFG->dirroot.'/completion/cron.php');

    debugging('completion_cron() is deprecated. Functionality has been moved to scheduled tasks.', DEBUG_DEVELOPER);
    completion_cron_mark_started();

    completion_cron_criteria();

    completion_cron_completions();
}


function coursetag_get_tags($courseid, $userid=0, $tagtype='', $numtags=0, $unused = '') {
    debugging('Function coursetag_get_tags() is deprecated. Userid is no longer used for tagging courses.', DEBUG_DEVELOPER);

    global $CFG, $DB;

        $courselist = array();
    if ($courseid === 0) {
        if ($courses = $DB->get_records_select('course', 'visible=1 AND category>0', null, '', 'id')) {
            foreach ($courses as $key => $value) {
                $courselist[] = $key;
            }
        }
    }

        $params = array();
    $sql = "SELECT id as tkey, name, id, isstandard, rawname, f.timemodified, flag, count
              FROM {tag} t,
                 (SELECT tagid, MAX(timemodified) as timemodified, COUNT(id) as count
                    FROM {tag_instance}
                   WHERE itemtype = 'course' ";

    if ($courseid > 0) {
        $sql .= "    AND itemid = :courseid ";
        $params['courseid'] = $courseid;
    } else {
        if (!empty($courselist)) {
            list($usql, $uparams) = $DB->get_in_or_equal($courselist, SQL_PARAMS_NAMED);
            $sql .= "AND itemid $usql ";
            $params = $params + $uparams;
        }
    }

    if ($userid > 0) {
        $sql .= "    AND tiuserid = :userid ";
        $params['userid'] = $userid;
    }

    $sql .= "   GROUP BY tagid) f
             WHERE t.id = f.tagid ";
    if ($tagtype != '') {
        $sql .= "AND isstandard = :isstandard ";
        $params['isstandard'] = ($tagtype === 'official') ? 1 : 0;
    }
    $sql .= "ORDER BY count DESC, name ASC";

        if ($numtags == 0) {
        $tags = $DB->get_records_sql($sql, $params);
    } else {
        $tags = $DB->get_records_sql($sql, $params, 0, $numtags);
    }

        $return = array();
    if ($tags) {
                foreach ($tags as $value) {
            $return[] = $value;
        }
    }

    return $return;

}


function coursetag_get_all_tags($unused='', $numtags=0) {
    debugging('Function coursetag_get_all_tag() is deprecated. Userid is no longer used for tagging courses.', DEBUG_DEVELOPER);

    global $CFG, $DB;

        $sql = "SELECT id, name, isstandard, rawname, f.timemodified, flag, count
        FROM {tag} t,
        (SELECT tagid, MAX(timemodified) as timemodified, COUNT(id) as count
            FROM {tag_instance} WHERE tagid NOT IN
                (SELECT tagid FROM {tag_instance} ti, {course} c
                WHERE c.visible = 0
                AND ti.itemtype = 'course'
                AND ti.itemid = c.id)
        GROUP BY tagid) f
        WHERE t.id = f.tagid
        ORDER BY count DESC, name ASC";
    if ($numtags == 0) {
        $tags = $DB->get_records_sql($sql);
    } else {
        $tags = $DB->get_records_sql($sql, null, 0, $numtags);
    }

    $return = array();
    if ($tags) {
        foreach ($tags as $value) {
            $return[] = $value;
        }
    }

    return $return;
}


function coursetag_get_jscript() {
    debugging('Function coursetag_get_jscript() is deprecated and obsolete.', DEBUG_DEVELOPER);
    return '';
}


function coursetag_get_jscript_links($elementid, $coursetagslinks) {
    debugging('Function coursetag_get_jscript_links() is deprecated and obsolete.', DEBUG_DEVELOPER);
    return '';
}


function coursetag_get_records($courseid, $userid) {
    debugging('Function coursetag_get_records() is deprecated. Userid is no longer used for tagging courses.', DEBUG_DEVELOPER);

    global $CFG, $DB;

    $sql = "SELECT t.id, name, rawname
              FROM {tag} t, {tag_instance} ti
             WHERE t.id = ti.tagid
                 AND ti.tiuserid = :userid
                 AND ti.itemid = :courseid
          ORDER BY name ASC";

    return $DB->get_records_sql($sql, array('userid'=>$userid, 'courseid'=>$courseid));
}


function coursetag_store_keywords($tags, $courseid, $userid=0, $tagtype='official', $myurl='') {
    debugging('Function coursetag_store_keywords() is deprecated. Userid is no longer used for tagging courses.', DEBUG_DEVELOPER);

    global $CFG;

    if (is_array($tags) and !empty($tags)) {
        if ($tagtype === 'official') {
            $tagcoll = core_tag_area::get_collection('core', 'course');
                        core_tag_tag::create_if_missing($tagcoll, $tags, true);
        }
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (strlen($tag) > 0) {
                core_tag_tag::add_item_tag('core', 'course', $courseid, context_course::instance($courseid), $tag, $userid);
            }
        }
    }

}


function coursetag_delete_keyword($tagid, $userid, $courseid) {
    debugging('Function coursetag_delete_keyword() is deprecated. Userid is no longer used for tagging courses.', DEBUG_DEVELOPER);

    $tag = core_tag_tag::get($tagid);
    core_tag_tag::remove_item_tag('core', 'course', $courseid, $tag->rawname, $userid);
}


function coursetag_get_tagged_courses($tagid) {
    debugging('Function coursetag_get_tagged_courses() is deprecated. Userid is no longer used for tagging courses.', DEBUG_DEVELOPER);

    global $DB;

    $courses = array();

    $ctxselect = context_helper::get_preload_record_columns_sql('ctx');

    $sql = "SELECT c.*, $ctxselect
            FROM {course} c
            JOIN {tag_instance} t ON t.itemid = c.id
            JOIN {context} ctx ON ctx.instanceid = c.id
            WHERE t.tagid = :tagid AND
            t.itemtype = 'course' AND
            ctx.contextlevel = :contextlevel
            ORDER BY c.sortorder ASC";
    $params = array('tagid' => $tagid, 'contextlevel' => CONTEXT_COURSE);
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $course) {
        context_helper::preload_from_record($course);
        if ($course->visible == 1 || has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
            $courses[$course->id] = $course;
        }
    }
    return $courses;
}


function coursetag_delete_course_tags($courseid, $showfeedback=false) {
    debugging('Function coursetag_delete_course_tags() is deprecated. Use core_tag_tag::remove_all_item_tags().', DEBUG_DEVELOPER);

    global $OUTPUT;
    core_tag_tag::remove_all_item_tags('core', 'course', $courseid);

    if ($showfeedback) {
        echo $OUTPUT->notification(get_string('deletedcoursetags', 'tag'), 'notifysuccess');
    }
}


function tag_type_set($tagid, $type) {
    debugging('Function tag_type_set() is deprecated and can be replaced with use core_tag_tag::get($tagid)->update().', DEBUG_DEVELOPER);
    if ($tag = core_tag_tag::get($tagid, '*')) {
        return $tag->update(array('isstandard' => ($type === 'official') ? 1 : 0));
    }
    return false;
}


function tag_description_set($tagid, $description, $descriptionformat) {
    debugging('Function tag_type_set() is deprecated and can be replaced with core_tag_tag::get($tagid)->update().', DEBUG_DEVELOPER);
    if ($tag = core_tag_tag::get($tagid, '*')) {
        return $tag->update(array('description' => $description, 'descriptionformat' => $descriptionformat));
    }
    return false;
}


function tag_get_tags($record_type, $record_id, $type=null, $userid=0) {
    debugging('Method tag_get_tags() is deprecated and replaced with core_tag_tag::get_item_tags(). ' .
        'Component is now required when retrieving tag instances.', DEBUG_DEVELOPER);
    $standardonly = ($type === 'official' ? core_tag_tag::STANDARD_ONLY :
        (!empty($type) ? core_tag_tag::NOT_STANDARD_ONLY : core_tag_tag::BOTH_STANDARD_AND_NOT));
    $tags = core_tag_tag::get_item_tags(null, $record_type, $record_id, $standardonly, $userid);
    $rv = array();
    foreach ($tags as $id => $t) {
        $rv[$id] = $t->to_object();
    }
    return $rv;
}


function tag_get_tags_array($record_type, $record_id, $type=null) {
    debugging('Method tag_get_tags_array() is deprecated and replaced with core_tag_tag::get_item_tags_array(). ' .
        'Component is now required when retrieving tag instances.', DEBUG_DEVELOPER);
    $standardonly = ($type === 'official' ? core_tag_tag::STANDARD_ONLY :
        (!empty($type) ? core_tag_tag::NOT_STANDARD_ONLY : core_tag_tag::BOTH_STANDARD_AND_NOT));
    return core_tag_tag::get_item_tags_array('', $record_type, $record_id, $standardonly);
}


function tag_get_tags_csv($record_type, $record_id, $html=null, $type=null) {
    global $CFG, $OUTPUT;
    debugging('Method tag_get_tags_csv() is deprecated. Instead you should use either ' .
            'core_tag_tag::get_item_tags_array() or $OUTPUT->tag_list(core_tag_tag::get_item_tags()). ' .
        'Component is now required when retrieving tag instances.', DEBUG_DEVELOPER);
    $standardonly = ($type === 'official' ? core_tag_tag::STANDARD_ONLY :
        (!empty($type) ? core_tag_tag::NOT_STANDARD_ONLY : core_tag_tag::BOTH_STANDARD_AND_NOT));
    if ($html != TAG_RETURN_TEXT) {
        return $OUTPUT->tag_list(core_tag_tag::get_item_tags('', $record_type, $record_id, $standardonly), '');
    } else {
        return join(', ', core_tag_tag::get_item_tags_array('', $record_type, $record_id, $standardonly, 0, false));
    }
}


function tag_get_tags_ids($record_type, $record_id) {
    debugging('Method tag_get_tags_ids() is deprecated. Please consider using core_tag_tag::get_item_tags() or similar methods.', DEBUG_DEVELOPER);
    $tag_ids = array();
    $tagobjects = core_tag_tag::get_item_tags(null, $record_type, $record_id);
    foreach ($tagobjects as $tagobject) {
        $tag = $tagobject->to_object();
        if ( array_key_exists($tag->ordering, $tag_ids) ) {
            $tag->ordering++;
        }
        $tag_ids[$tag->ordering] = $tag->id;
    }
    ksort($tag_ids);
    return $tag_ids;
}


function tag_get_id($tags, $return_value = null) {
    global $CFG, $DB;
    debugging('Method tag_get_id() is deprecated and can be replaced with core_tag_tag::get_by_name() or core_tag_tag::get_by_name_bulk(). ' .
        'You need to specify tag collection when retrieving tag by name', DEBUG_DEVELOPER);

    if (!is_array($tags)) {
        if(is_null($return_value) || $return_value == TAG_RETURN_OBJECT) {
            if ($tagobject = core_tag_tag::get_by_name(core_tag_collection::get_default(), $tags)) {
                return $tagobject->id;
            } else {
                return 0;
            }
        }
        $tags = array($tags);
    }

    $records = core_tag_tag::get_by_name_bulk(core_tag_collection::get_default(), $tags,
        $return_value == TAG_RETURN_OBJECT ? '*' : 'id, name');
    foreach ($records as $name => $record) {
        if ($return_value != TAG_RETURN_OBJECT) {
            $records[$name] = $record->id ? $record->id : null;
        } else {
            $records[$name] = $record->to_object();
        }
    }
    return $records;
}


function tag_rename($tagid, $newrawname) {
    debugging('Function tag_rename() is deprecated and may be replaced with core_tag_tag::get($tagid)->update().', DEBUG_DEVELOPER);
    if ($tag = core_tag_tag::get($tagid, '*')) {
        return $tag->update(array('rawname' => $newrawname));
    }
    return false;
}


function tag_delete_instance($record_type, $record_id, $tagid, $userid = null) {
    debugging('Function tag_delete_instance() is deprecated and replaced with core_tag_tag::remove_item_tag() instead. ' .
        'Component is required for retrieving instances', DEBUG_DEVELOPER);
    $tag = core_tag_tag::get($tagid);
    core_tag_tag::remove_item_tag('', $record_type, $record_id, $tag->rawname, $userid);
}


function tag_find_records($tag, $type, $limitfrom='', $limitnum='') {
    debugging('Function tag_find_records() is deprecated and replaced with core_tag_tag::get_by_name()->get_tagged_items(). '.
        'You need to specify tag collection when retrieving tag by name', DEBUG_DEVELOPER);

    if (!$tag || !$type) {
        return array();
    }

    $tagobject = core_tag_tag::get_by_name(core_tag_area::get_collection('', $type), $tag);
    return $tagobject->get_tagged_items('', $type, $limitfrom, $limitnum);
}


function tag_add($tags, $type="default") {
    debugging('Function tag_add() is deprecated. You can use core_tag_tag::create_if_missing(), however it should not be necessary ' .
        'since tags are created automatically when assigned to items', DEBUG_DEVELOPER);
    if (!is_array($tags)) {
        $tags = array($tags);
    }
    $objects = core_tag_tag::create_if_missing(core_tag_collection::get_default(), $tags,
            $type === 'official');

        $rv = array();
    foreach ($objects as $name => $tagobject) {
        if (isset($tagobject->id)) {
            $rv[$tagobject->name] = $tagobject->id;
        } else {
            $rv[$name] = false;
        }
    }
    return $rv;
}


function tag_assign($record_type, $record_id, $tagid, $ordering, $userid = 0, $component = null, $contextid = null) {
    global $DB;
    $message = 'Function tag_assign() is deprecated. Use core_tag_tag::set_item_tags() or core_tag_tag::add_item_tag() instead. ' .
        'Tag instance ordering should not be set manually';
    if ($component === null || $contextid === null) {
        $message .= '. You should specify the component and contextid of the item being tagged in your call to tag_assign.';
    }
    debugging($message, DEBUG_DEVELOPER);

    if ($contextid) {
        $context = context::instance_by_id($contextid);
    } else {
        $context = context_system::instance();
    }

        $tag = $DB->get_record('tag', array('id' => $tagid), 'name, rawname', MUST_EXIST);

    $taginstanceid = core_tag_tag::add_item_tag($component, $record_type, $record_id, $context, $tag->rawname, $userid);

        $taginstance = new stdClass();
    $taginstance->id = $taginstanceid;
    $taginstance->ordering     = $ordering;
    $taginstance->timemodified = time();

    $DB->update_record('tag_instance', $taginstance);

    return true;
}


function tag_record_count($record_type, $tagid) {
    debugging('Method tag_record_count() is deprecated and replaced with core_tag_tag::get($tagid)->count_tagged_items(). '.
        'Component is now required when retrieving tag instances.', DEBUG_DEVELOPER);
    return core_tag_tag::get($tagid)->count_tagged_items('', $record_type);
}


function tag_record_tagged_with($record_type, $record_id, $tag) {
    debugging('Method tag_record_tagged_with() is deprecated and replaced with core_tag_tag::get($tagid)->is_item_tagged_with(). '.
        'Component is now required when retrieving tag instances.', DEBUG_DEVELOPER);
    return core_tag_tag::is_item_tagged_with('', $record_type, $record_id, $tag);
}


function tag_set_flag($tagids) {
    debugging('Function tag_set_flag() is deprecated and replaced with core_tag_tag::get($tagid)->flag().', DEBUG_DEVELOPER);
    $tagids = (array) $tagids;
    foreach ($tagids as $tagid) {
        if ($tag = core_tag_tag::get($tagid, '*')) {
            $tag->flag();
        }
    }
}


function tag_unset_flag($tagids) {
    debugging('Function tag_unset_flag() is deprecated and replaced with core_tag_tag::get($tagid)->reset_flag().', DEBUG_DEVELOPER);
    $tagids = (array) $tagids;
    foreach ($tagids as $tagid) {
        if ($tag = core_tag_tag::get($tagid, '*')) {
            $tag->reset_flag();
        }
    }
}


function tag_print_cloud($tagset=null, $nr_of_tags=150, $return=false, $sort='') {
    global $OUTPUT;

    debugging('Function tag_print_cloud() is deprecated and replaced with function core_tag_collection::get_tag_cloud(), '
            . 'templateable core_tag\output\tagcloud and template core_tag/tagcloud.', DEBUG_DEVELOPER);

        if ($sort == 'popularity') {
        $sort = 'count';
    } else if ($sort == 'date') {
        $sort = 'timemodified';
    } else {
        $sort = 'name';
    }

    if (is_null($tagset)) {
                        $tagcloud = core_tag_collection::get_tag_cloud(0, false, $nr_of_tags, $sort);
    } else {
        $tagsincloud = $tagset;

        $etags = array();
        foreach ($tagsincloud as $tag) {
            $etags[] = $tag;
        }

        core_tag_collection::$cloudsortfield = $sort;
        usort($tagsincloud, "core_tag_collection::cloud_sort");

        $tagcloud = new \core_tag\output\tagcloud($tagsincloud);
    }

    $output = $OUTPUT->render_from_template('core_tag/tagcloud', $tagcloud->export_for_template($OUTPUT));
    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function tag_autocomplete($text) {
    debugging('Function tag_autocomplete() is deprecated without replacement. ' .
            'New form element "tags" does proper autocomplete.', DEBUG_DEVELOPER);
    global $DB;
    return $DB->get_records_sql("SELECT tg.id, tg.name, tg.rawname
                                   FROM {tag} tg
                                  WHERE tg.name LIKE ?", array(core_text::strtolower($text)."%"));
}


function tag_print_description_box($tag_object, $return=false) {
    global $USER, $CFG, $OUTPUT;
    require_once($CFG->libdir.'/filelib.php');

    debugging('Function tag_print_description_box() is deprecated without replacement. ' .
            'See core_tag_renderer for similar code.', DEBUG_DEVELOPER);

    $relatedtags = array();
    if ($tag = core_tag_tag::get($tag_object->id)) {
        $relatedtags = $tag->get_related_tags();
    }

    $content = !empty($tag_object->description);
    $output = '';

    if ($content) {
        $output .= $OUTPUT->box_start('generalbox tag-description');
    }

    if (!empty($tag_object->description)) {
        $options = new stdClass();
        $options->para = false;
        $options->overflowdiv = true;
        $tag_object->description = file_rewrite_pluginfile_urls($tag_object->description, 'pluginfile.php', context_system::instance()->id, 'tag', 'description', $tag_object->id);
        $output .= format_text($tag_object->description, $tag_object->descriptionformat, $options);
    }

    if ($content) {
        $output .= $OUTPUT->box_end();
    }

    if ($relatedtags) {
        $output .= $OUTPUT->tag_list($relatedtags, get_string('relatedtags', 'tag'), 'tag-relatedtags');
    }

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function tag_print_management_box($tag_object, $return=false) {
    global $USER, $CFG, $OUTPUT;

    debugging('Function tag_print_description_box() is deprecated without replacement. ' .
            'See core_tag_renderer for similar code.', DEBUG_DEVELOPER);

    $tagname  = core_tag_tag::make_display_name($tag_object);
    $output = '';

    if (!isguestuser()) {
        $output .= $OUTPUT->box_start('box','tag-management-box');
        $systemcontext   = context_system::instance();
        $links = array();

                if (core_tag_tag::is_enabled('core', 'user') && core_tag_area::get_collection('core', 'user') == $tag_object->tagcollid) {
            if (core_tag_tag::is_item_tagged_with('core', 'user', $USER->id, $tag_object->name)) {
                $links[] = '<a href="'. $CFG->wwwroot .'/tag/user.php?action=removeinterest&amp;sesskey='. sesskey() .
                        '&amp;tag='. rawurlencode($tag_object->name) .'">'.
                        get_string('removetagfrommyinterests', 'tag', $tagname) .'</a>';
            } else {
                $links[] = '<a href="'. $CFG->wwwroot .'/tag/user.php?action=addinterest&amp;sesskey='. sesskey() .
                        '&amp;tag='. rawurlencode($tag_object->name) .'">'.
                        get_string('addtagtomyinterests', 'tag', $tagname) .'</a>';
            }
        }

                if (has_capability('moodle/tag:flag', $systemcontext)) {
            $links[] = '<a href="'. $CFG->wwwroot .'/tag/user.php?action=flaginappropriate&amp;sesskey='.
                    sesskey() . '&amp;id='. $tag_object->id . '">'. get_string('flagasinappropriate',
                            'tag', rawurlencode($tagname)) .'</a>';
        }

                if (has_capability('moodle/tag:edit', $systemcontext) ||
            has_capability('moodle/tag:manage', $systemcontext)) {
            $links[] = '<a href="' . $CFG->wwwroot . '/tag/edit.php?id=' . $tag_object->id . '">' .
                    get_string('edittag', 'tag') . '</a>';
        }

        $output .= implode(' | ', $links);
        $output .= $OUTPUT->box_end();
    }

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function tag_print_search_box($return=false) {
    global $CFG, $OUTPUT;

    debugging('Function tag_print_search_box() is deprecated without replacement. ' .
            'See core_tag_renderer for similar code.', DEBUG_DEVELOPER);

    $query = optional_param('query', '', PARAM_RAW);

    $output = $OUTPUT->box_start('','tag-search-box');
    $output .= '<form action="'.$CFG->wwwroot.'/tag/search.php" style="display:inline">';
    $output .= '<div>';
    $output .= '<label class="accesshide" for="searchform_search">'.get_string('searchtags', 'tag').'</label>';
    $output .= '<input id="searchform_search" name="query" type="text" size="40" value="'.s($query).'" />';
    $output .= '<button id="searchform_button" type="submit">'. get_string('search', 'tag') .'</button><br />';
    $output .= '</div>';
    $output .= '</form>';
    $output .= $OUTPUT->box_end();

    if ($return) {
        return $output;
    }
    else {
        echo $output;
    }
}


function tag_print_search_results($query,  $page, $perpage, $return=false) {
    global $CFG, $USER, $OUTPUT;

    debugging('Function tag_print_search_results() is deprecated without replacement. ' .
            'In /tag/search.php the search results are printed using the core_tag/tagcloud template.', DEBUG_DEVELOPER);

    $query = clean_param($query, PARAM_TAG);

    $count = count(tag_find_tags($query, false));
    $tags = array();

    if ( $found_tags = tag_find_tags($query, true,  $page * $perpage, $perpage) ) {
        $tags = array_values($found_tags);
    }

    $baseurl = $CFG->wwwroot.'/tag/search.php?query='. rawurlencode($query);
    $output = '';

        $addtaglink = '';
    if (core_tag_tag::is_enabled('core', 'user') && !core_tag_tag::is_item_tagged_with('core', 'user', $USER->id, $query)) {
        $addtaglink = html_writer::link(new moodle_url('/tag/user.php', array('action' => 'addinterest', 'sesskey' => sesskey(),
            'tag' => $query)), get_string('addtagtomyinterests', 'tag', s($query)));
    }

    if ( !empty($tags) ) {         $output .= $OUTPUT->heading(get_string('searchresultsfor', 'tag', htmlspecialchars($query)) ." : {$count}", 3, 'main');

                if (!empty($addtaglink)) {
            $output .= $OUTPUT->box($addtaglink, 'box', 'tag-management-box');
        }

        $nr_of_lis_per_ul = 6;
        $nr_of_uls = ceil( sizeof($tags) / $nr_of_lis_per_ul );

        $output .= '<ul id="tag-search-results">';
        for($i = 0; $i < $nr_of_uls; $i++) {
            foreach (array_slice($tags, $i * $nr_of_lis_per_ul, $nr_of_lis_per_ul) as $tag) {
                $output .= '<li>';
                $tag_link = html_writer::link(core_tag_tag::make_url($tag->tagcollid, $tag->rawname),
                    core_tag_tag::make_display_name($tag));
                $output .= $tag_link;
                $output .= '</li>';
            }
        }
        $output .= '</ul>';
        $output .= '<div>&nbsp;</div>'; 
        $output .= $OUTPUT->paging_bar($count, $page, $perpage, $baseurl);
    }
    else {         $output .= $OUTPUT->heading(get_string('noresultsfor', 'tag', htmlspecialchars($query)), 3, 'main');

                if (!empty($addtaglink)) {
            $output .= $OUTPUT->box($addtaglink, 'box', 'tag-management-box');
        }
    }

    if ($return) {
        return $output;
    }
    else {
        echo $output;
    }
}


function tag_print_tagged_users_table($tagobject, $limitfrom='', $limitnum='', $return=false) {

    debugging('Function tag_print_tagged_users_table() is deprecated without replacement. ' .
            'See core_user_renderer for similar code.', DEBUG_DEVELOPER);

        $tagobject = core_tag_tag::get($tagobject->id);
    $userlist = $tagobject->get_tagged_items('core', 'user', $limitfrom, $limitnum);

    $output = tag_print_user_list($userlist, true);

    if ($return) {
        return $output;
    }
    else {
        echo $output;
    }
}


function tag_print_user_box($user, $return=false) {
    global $CFG, $OUTPUT;

    debugging('Function tag_print_user_box() is deprecated without replacement. ' .
            'See core_user_renderer for similar code.', DEBUG_DEVELOPER);

    $usercontext = context_user::instance($user->id);
    $profilelink = '';

    if ($usercontext and (has_capability('moodle/user:viewdetails', $usercontext) || has_coursecontact_role($user->id))) {
        $profilelink = $CFG->wwwroot .'/user/view.php?id='. $user->id;
    }

    $output = $OUTPUT->box_start('user-box', 'user'. $user->id);
    $fullname = fullname($user);
    $alt = '';

    if (!empty($profilelink)) {
        $output .= '<a href="'. $profilelink .'">';
        $alt = $fullname;
    }

    $output .= $OUTPUT->user_picture($user, array('size'=>100));
    $output .= '<br />';

    if (!empty($profilelink)) {
        $output .= '</a>';
    }

        if (core_text::strlen($fullname) > 26) {
        $fullname = core_text::substr($fullname, 0, 26) .'...';
    }

    $output .= '<strong>'. $fullname .'</strong>';
    $output .= $OUTPUT->box_end();

    if ($return) {
        return $output;
    }
    else {
        echo $output;
    }
}


function tag_print_user_list($userlist, $return=false) {

    debugging('Function tag_print_user_list() is deprecated without replacement. ' .
            'See core_user_renderer for similar code.', DEBUG_DEVELOPER);

    $output = '<div><ul class="inline-list">';

    foreach ($userlist as $user){
        $output .= '<li>'. tag_print_user_box($user, true) ."</li>\n";
    }
    $output .= "</ul></div>\n";

    if ($return) {
        return $output;
    }
    else {
        echo $output;
    }
}


function tag_display_name($tagobject, $html=TAG_RETURN_HTML) {
    debugging('Function tag_display_name() is deprecated. Use core_tag_tag::make_display_name().', DEBUG_DEVELOPER);
    if (!isset($tagobject->name)) {
        return '';
    }
    return core_tag_tag::make_display_name($tagobject, $html != TAG_RETURN_TEXT);
}


function tag_normalize($rawtags, $case = TAG_CASE_LOWER) {
    debugging('Function tag_normalize() is deprecated. Use core_tag_tag::normalize().', DEBUG_DEVELOPER);

    if ( !is_array($rawtags) ) {
        $rawtags = array($rawtags);
    }

    return core_tag_tag::normalize($rawtags, $case == TAG_CASE_LOWER);
}


function tag_get_related_tags_csv($related_tags, $html=TAG_RETURN_HTML) {
    global $OUTPUT;
    debugging('Method tag_get_related_tags_csv() is deprecated. Consider '
            . 'looping through array or using $OUTPUT->tag_list(core_tag_tag::get_item_tags())',
        DEBUG_DEVELOPER);
    if ($html != TAG_RETURN_TEXT) {
        return $OUTPUT->tag_list($related_tags, '');
    }

    $tagsnames = array();
    foreach ($related_tags as $tag) {
        $tagsnames[] = core_tag_tag::make_display_name($tag, false);
    }
    return implode(', ', $tagsnames);
}


define('TAG_RETURN_ARRAY', 0);

define('TAG_RETURN_OBJECT', 1);

define('TAG_RETURN_TEXT', 2);

define('TAG_RETURN_HTML', 3);


define('TAG_CASE_LOWER', 0);

define('TAG_CASE_ORIGINAL', 1);


define('TAG_RELATED_ALL', 0);

define('TAG_RELATED_MANUAL', 1);

define('TAG_RELATED_CORRELATED', 2);


function tag_set($itemtype, $itemid, $tags, $component = null, $contextid = null) {
    debugging('Function tag_set() is deprecated. Use ' .
        ' core_tag_tag::set_item_tags() instead', DEBUG_DEVELOPER);

    if ($itemtype === 'tag') {
        return core_tag_tag::get($itemid, '*', MUST_EXIST)->set_related_tags($tags);
    } else {
        $context = $contextid ? context::instance_by_id($contextid) : context_system::instance();
        return core_tag_tag::set_item_tags($component, $itemtype, $itemid, $context, $tags);
    }
}


function tag_set_add($itemtype, $itemid, $tag, $component = null, $contextid = null) {
    debugging('Function tag_set_add() is deprecated. Use ' .
        ' core_tag_tag::add_item_tag() instead', DEBUG_DEVELOPER);

    if ($itemtype === 'tag') {
        return core_tag_tag::get($itemid, '*', MUST_EXIST)->add_related_tags(array($tag));
    } else {
        $context = $contextid ? context::instance_by_id($contextid) : context_system::instance();
        return core_tag_tag::add_item_tag($component, $itemtype, $itemid, $context, $tag);
    }
}


function tag_set_delete($itemtype, $itemid, $tag, $component = null, $contextid = null) {
    debugging('Function tag_set_delete() is deprecated. Use ' .
        ' core_tag_tag::remove_item_tag() instead', DEBUG_DEVELOPER);
    return core_tag_tag::remove_item_tag($component, $itemtype, $itemid, $tag);
}


function tag_get($field, $value, $returnfields='id, name, rawname, tagcollid') {
    global $DB;
    debugging('Function tag_get() is deprecated. Use ' .
        ' core_tag_tag::get() or core_tag_tag::get_by_name()',
        DEBUG_DEVELOPER);
    if ($field === 'id') {
        $tag = core_tag_tag::get((int)$value, $returnfields);
    } else if ($field === 'name') {
        $tag = core_tag_tag::get_by_name(0, $value, $returnfields);
    } else {
        $params = array($field => $value);
        return $DB->get_record('tag', $params, $returnfields);
    }
    if ($tag) {
        return $tag->to_object();
    }
    return null;
}


function tag_get_related_tags($tagid, $type=TAG_RELATED_ALL, $limitnum=10) {
    debugging('Method tag_get_related_tags() is deprecated, '
        . 'use core_tag_tag::get_correlated_tags(), core_tag_tag::get_related_tags() or '
        . 'core_tag_tag::get_manual_related_tags()', DEBUG_DEVELOPER);
    $result = array();
    if ($tag = core_tag_tag::get($tagid)) {
        if ($type == TAG_RELATED_CORRELATED) {
            $tags = $tag->get_correlated_tags();
        } else if ($type == TAG_RELATED_MANUAL) {
            $tags = $tag->get_manual_related_tags();
        } else {
            $tags = $tag->get_related_tags();
        }
        $tags = array_slice($tags, 0, $limitnum);
        foreach ($tags as $id => $tag) {
            $result[$id] = $tag->to_object();
        }
    }
    return $result;
}


function tag_delete($tagids) {
    debugging('Method tag_delete() is deprecated, use core_tag_tag::delete_tags()',
        DEBUG_DEVELOPER);
    return core_tag_tag::delete_tags($tagids);
}


function tag_delete_instances($component, $contextid = null) {
    debugging('Method tag_delete() is deprecated, use core_tag_tag::delete_instances()',
        DEBUG_DEVELOPER);
    core_tag_tag::delete_instances($component, null, $contextid);
}


function tag_cleanup() {
    debugging('Method tag_cleanup() is deprecated, use \core\task\tag_cron_task::cleanup()',
        DEBUG_DEVELOPER);

    $task = new \core\task\tag_cron_task();
    return $task->cleanup();
}


function tag_bulk_delete_instances($instances) {
    debugging('Method tag_bulk_delete_instances() is deprecated, '
        . 'use \core\task\tag_cron_task::bulk_delete_instances()',
        DEBUG_DEVELOPER);

    $task = new \core\task\tag_cron_task();
    return $task->bulk_delete_instances($instances);
}


function tag_compute_correlations($mincorrelation = 2) {
    debugging('Method tag_compute_correlations() is deprecated, '
        . 'use \core\task\tag_cron_task::compute_correlations()',
        DEBUG_DEVELOPER);

    $task = new \core\task\tag_cron_task();
    return $task->compute_correlations($mincorrelation);
}


function tag_process_computed_correlation(stdClass $tagcorrelation) {
    debugging('Method tag_process_computed_correlation() is deprecated, '
        . 'use \core\task\tag_cron_task::process_computed_correlation()',
        DEBUG_DEVELOPER);

    $task = new \core\task\tag_cron_task();
    return $task->process_computed_correlation($tagcorrelation);
}


function tag_cron() {
    debugging('Method tag_cron() is deprecated, use \core\task\tag_cron_task::execute()',
        DEBUG_DEVELOPER);

    $task = new \core\task\tag_cron_task();
    $task->execute();
}


function tag_find_tags($text, $ordered=true, $limitfrom='', $limitnum='', $tagcollid = null) {
    debugging('Method tag_find_tags() is deprecated without replacement', DEBUG_DEVELOPER);
    global $DB;

    $text = core_text::strtolower(clean_param($text, PARAM_TAG));

    list($sql, $params) = $DB->get_in_or_equal($tagcollid ? array($tagcollid) :
        array_keys(core_tag_collection::get_collections(true)));
    array_unshift($params, "%{$text}%");

    if ($ordered) {
        $query = "SELECT tg.id, tg.name, tg.rawname, tg.tagcollid, COUNT(ti.id) AS count
                    FROM {tag} tg LEFT JOIN {tag_instance} ti ON tg.id = ti.tagid
                   WHERE tg.name LIKE ? AND tg.tagcollid $sql
                GROUP BY tg.id, tg.name, tg.rawname
                ORDER BY count DESC";
    } else {
        $query = "SELECT tg.id, tg.name, tg.rawname, tg.tagcollid
                    FROM {tag} tg
                   WHERE tg.name LIKE ? AND tg.tagcollid $sql";
    }
    return $DB->get_records_sql($query, $params, $limitfrom , $limitnum);
}


function tag_get_name($tagids) {
    debugging('Method tag_get_name() is deprecated without replacement', DEBUG_DEVELOPER);
    global $DB;

    if (!is_array($tagids)) {
        if ($tag = $DB->get_record('tag', array('id'=>$tagids))) {
            return $tag->name;
        }
        return false;
    }

    $tag_names = array();
    foreach($DB->get_records_list('tag', 'id', $tagids) as $tag) {
        $tag_names[$tag->id] = $tag->name;
    }

    return $tag_names;
}


function tag_get_correlated($tagid, $notused = null) {
    debugging('Method tag_get_correlated() is deprecated, '
        . 'use core_tag_tag::get_correlated_tags()', DEBUG_DEVELOPER);
    $result = array();
    if ($tag = core_tag_tag::get($tagid)) {
        $tags = $tag->get_correlated_tags(true);
                foreach ($tags as $id => $tag) {
            $result[$id] = $tag->to_object();
        }
    }
    return $result;
}


function tag_cloud_sort($a, $b) {
    debugging('Method tag_cloud_sort() is deprecated, similar method can be found in core_tag_collection::cloud_sort()', DEBUG_DEVELOPER);
    global $CFG;

    if (empty($CFG->tagsort)) {
        $tagsort = 'name';     } else {
        $tagsort = $CFG->tagsort;
    }

    if (is_numeric($a->$tagsort)) {
        return ($a->$tagsort == $b->$tagsort) ? 0 : ($a->$tagsort > $b->$tagsort) ? 1 : -1;
    } elseif (is_string($a->$tagsort)) {
        return strcmp($a->$tagsort, $b->$tagsort);
    } else {
        return 0;
    }
}


function events_load_def($component) {
    global $CFG;
    if ($component === 'unittest') {
        $defpath = $CFG->dirroot.'/lib/tests/fixtures/events.php';
    } else {
        $defpath = core_component::get_component_directory($component).'/db/events.php';
    }

    $handlers = array();

    if (file_exists($defpath)) {
        require($defpath);
    }

        foreach ($handlers as $eventname => $handler) {
        if ($eventname === 'reset') {
            debugging("'reset' can not be used as event name.");
            unset($handlers['reset']);
            continue;
        }
        if (!is_array($handler)) {
            debugging("Handler of '$eventname' must be specified as array'");
            unset($handlers[$eventname]);
            continue;
        }
        if (!isset($handler['handlerfile'])) {
            debugging("Handler of '$eventname' must include 'handlerfile' key'");
            unset($handlers[$eventname]);
            continue;
        }
        if (!isset($handler['handlerfunction'])) {
            debugging("Handler of '$eventname' must include 'handlerfunction' key'");
            unset($handlers[$eventname]);
            continue;
        }
        if (!isset($handler['schedule'])) {
            $handler['schedule'] = 'instant';
        }
        if ($handler['schedule'] !== 'instant' and $handler['schedule'] !== 'cron') {
            debugging("Handler of '$eventname' must include valid 'schedule' type (instant or cron)'");
            unset($handlers[$eventname]);
            continue;
        }
        if (!isset($handler['internal'])) {
            $handler['internal'] = 1;
        }
        $handlers[$eventname] = $handler;
    }

    return $handlers;
}


function events_queue_handler($handler, $event, $errormessage) {
    global $DB;

    if ($qhandler = $DB->get_record('events_queue_handlers', array('queuedeventid'=>$event->id, 'handlerid'=>$handler->id))) {
        debugging("Please check code: Event id $event->id is already queued in handler id $qhandler->id");
        return $qhandler->id;
    }

        $qhandler = new stdClass();
    $qhandler->queuedeventid  = $event->id;
    $qhandler->handlerid      = $handler->id;
    $qhandler->errormessage   = $errormessage;
    $qhandler->timemodified   = time();
    if ($handler->schedule === 'instant' and $handler->status == 1) {
        $qhandler->status     = 1;     } else {
        $qhandler->status     = 0;
    }

    return $DB->insert_record('events_queue_handlers', $qhandler);
}


function events_dispatch($handler, $eventdata, &$errormessage) {
    global $CFG;

    debugging('Events API using $handlers array has been deprecated in favour of Events 2 API, please use it instead.', DEBUG_DEVELOPER);

    $function = unserialize($handler->handlerfunction);

    if (is_callable($function)) {
        
    } else if (file_exists($CFG->dirroot.$handler->handlerfile)) {
        include_once($CFG->dirroot.$handler->handlerfile);

    } else {
        $errormessage = "Handler file of component $handler->component: $handler->handlerfile can not be found!";
        return null;
    }

        if (is_callable($function)) {
        $result = call_user_func($function, $eventdata);
        if ($result === false) {
            $errormessage = "Handler function of component $handler->component: $handler->handlerfunction requested resending of event!";
            return false;
        }
        return true;

    } else {
        $errormessage = "Handler function of component $handler->component: $handler->handlerfunction not callable function or class method!";
        return null;
    }
}


function events_process_queued_handler($qhandler) {
    global $DB;

        if (!$handler = $DB->get_record('events_handlers', array('id'=>$qhandler->handlerid))) {
        debugging("Error processing queue handler $qhandler->id, missing handler id: $qhandler->handlerid");
                events_dequeue($qhandler);
        return NULL;
    }

        if (!$event = $DB->get_record('events_queue', array('id'=>$qhandler->queuedeventid))) {
                debugging("Error processing queue handler $qhandler->id, missing event id: $qhandler->queuedeventid");
                events_dequeue($qhandler);
        return NULL;
    }

        try {
        $errormessage = 'Unknown error';
        if (events_dispatch($handler, unserialize(base64_decode($event->eventdata)), $errormessage)) {
                        events_dequeue($qhandler);
            return true;
        }
    } catch (Exception $e) {
                        $errormessage = "Handler function of component $handler->component: $handler->handlerfunction threw exception :" .
                $e->getMessage() . "\n" . format_backtrace($e->getTrace(), true);
        if (!empty($e->debuginfo)) {
            $errormessage .= $e->debuginfo;
        }
    }

        $qh = new stdClass();
    $qh->id           = $qhandler->id;
    $qh->errormessage = $errormessage;
    $qh->timemodified = time();
    $qh->status       = $qhandler->status + 1;
    $DB->update_record('events_queue_handlers', $qh);

    debugging($errormessage);

    return false;
}


function events_update_definition($component='moodle') {
    global $DB;

        $filehandlers = events_load_def($component);

    if ($filehandlers) {
        debugging('Events API using $handlers array has been deprecated in favour of Events 2 API, please use it instead.', DEBUG_DEVELOPER);
    }

                $cachedhandlers = events_get_cached($component);

    foreach ($filehandlers as $eventname => $filehandler) {
        if (!empty($cachedhandlers[$eventname])) {
            if ($cachedhandlers[$eventname]['handlerfile'] === $filehandler['handlerfile'] &&
                $cachedhandlers[$eventname]['handlerfunction'] === serialize($filehandler['handlerfunction']) &&
                $cachedhandlers[$eventname]['schedule'] === $filehandler['schedule'] &&
                $cachedhandlers[$eventname]['internal'] == $filehandler['internal']) {
                
                unset($cachedhandlers[$eventname]);
                continue;

            } else {
                                $handler = new stdClass();
                $handler->id              = $cachedhandlers[$eventname]['id'];
                $handler->handlerfile     = $filehandler['handlerfile'];
                $handler->handlerfunction = serialize($filehandler['handlerfunction']);                 $handler->schedule        = $filehandler['schedule'];
                $handler->internal        = $filehandler['internal'];

                $DB->update_record('events_handlers', $handler);

                unset($cachedhandlers[$eventname]);
                continue;
            }

        } else {
                                    $handler = new stdClass();
            $handler->eventname       = $eventname;
            $handler->component       = $component;
            $handler->handlerfile     = $filehandler['handlerfile'];
            $handler->handlerfunction = serialize($filehandler['handlerfunction']);             $handler->schedule        = $filehandler['schedule'];
            $handler->status          = 0;
            $handler->internal        = $filehandler['internal'];

            $DB->insert_record('events_handlers', $handler);
        }
    }

            events_cleanup($component, $cachedhandlers);

    events_get_handlers('reset');

    return true;
}


function events_cron($eventname='') {
    global $DB;

    $failed = array();
    $processed = 0;

    if ($eventname) {
        $sql = "SELECT qh.*
                  FROM {events_queue_handlers} qh, {events_handlers} h
                 WHERE qh.handlerid = h.id AND h.eventname=?
              ORDER BY qh.id";
        $params = array($eventname);
    } else {
        $sql = "SELECT *
                  FROM {events_queue_handlers}
              ORDER BY id";
        $params = array();
    }

    $rs = $DB->get_recordset_sql($sql, $params);
    if ($rs->valid()) {
        debugging('Events API using $handlers array has been deprecated in favour of Events 2 API, please use it instead.', DEBUG_DEVELOPER);
    }

    foreach ($rs as $qhandler) {
        if (isset($failed[$qhandler->handlerid])) {
                        continue;
        }
        $status = events_process_queued_handler($qhandler);
        if ($status === false) {
                        $failed[$qhandler->handlerid] = $qhandler->handlerid;
        } else if ($status === NULL) {
                        $failed[$qhandler->handlerid] = $qhandler->handlerid;
        } else {
            $processed++;
        }
    }
    $rs->close();

        $sql = "SELECT eq.id
              FROM {events_queue} eq
              LEFT JOIN {events_queue_handlers} qh ON qh.queuedeventid = eq.id
             WHERE qh.id IS NULL";
    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $event) {
                $DB->delete_records('events_queue', array('id'=>$event->id));
    }
    $rs->close();

    return $processed;
}


function events_trigger_legacy($eventname, $eventdata) {
    global $CFG, $USER, $DB;

    $failedcount = 0; 
        if ($handlers = events_get_handlers($eventname)) {
        foreach ($handlers as $handler) {
            $errormessage = '';

            if ($handler->schedule === 'instant') {
                if ($handler->status) {
                                        if (!$DB->record_exists('events_queue_handlers', array('handlerid'=>$handler->id))) {
                                                $handler->status = 0;
                        $DB->set_field('events_handlers', 'status', 0, array('id'=>$handler->id));
                                                events_get_handlers('reset');
                    }
                }

                                if ($handler->status or (!$handler->internal and $DB->is_transaction_started())) {
                                        $handler->status++;
                    $DB->set_field('events_handlers', 'status', $handler->status, array('id'=>$handler->id));
                                        events_get_handlers('reset');

                } else {
                    $errormessage = 'Unknown error';
                    $result = events_dispatch($handler, $eventdata, $errormessage);
                    if ($result === true) {
                                                continue;
                    } else if ($result === false) {
                                                $DB->set_field('events_handlers', 'status', 1, array('id'=>$handler->id));
                                                events_get_handlers('reset');
                    } else {
                                                $failedcount ++;
                        continue;
                    }
                }

                                $failedcount ++;

            } else if ($handler->schedule === 'cron') {
                
            } else {
                                debugging("Unknown handler schedule type: $handler->schedule");
                $failedcount ++;
                continue;
            }

                        $event = new stdClass();
            $event->userid      = $USER->id;
            $event->eventdata   = base64_encode(serialize($eventdata));
            $event->timecreated = time();
            if (debugging()) {
                $dump = '';
                $callers = debug_backtrace();
                foreach ($callers as $caller) {
                    if (!isset($caller['line'])) {
                        $caller['line'] = '?';
                    }
                    if (!isset($caller['file'])) {
                        $caller['file'] = '?';
                    }
                    $dump .= 'line ' . $caller['line'] . ' of ' . substr($caller['file'], strlen($CFG->dirroot) + 1);
                    if (isset($caller['function'])) {
                        $dump .= ': call to ';
                        if (isset($caller['class'])) {
                            $dump .= $caller['class'] . $caller['type'];
                        }
                        $dump .= $caller['function'] . '()';
                    }
                    $dump .= "\n";
                }
                $event->stackdump = $dump;
            } else {
                $event->stackdump = '';
            }
            $event->id = $DB->insert_record('events_queue', $event);
            events_queue_handler($handler, $event, $errormessage);
        }
    } else {
            }

    return $failedcount;
}


function events_is_registered($eventname, $component) {
    global $DB;

    debugging('events_is_registered() has been deprecated along with all Events 1 API in favour of Events 2 API,' .
        ' please use it instead.', DEBUG_DEVELOPER);

    return $DB->record_exists('events_handlers', array('component'=>$component, 'eventname'=>$eventname));
}


function events_pending_count($eventname) {
    global $DB;

    debugging('events_pending_count() has been deprecated along with all Events 1 API in favour of Events 2 API,' .
        ' please use it instead.', DEBUG_DEVELOPER);

    $sql = "SELECT COUNT('x')
              FROM {events_queue_handlers} qh
              JOIN {events_handlers} h ON h.id = qh.handlerid
             WHERE h.eventname = ?";

    return $DB->count_records_sql($sql, array($eventname));
}


function clam_message_admins($notice) {
    debugging('clam_message_admins() is deprecated, please use message_admins() method of \antivirus_clamav\scanner class.', DEBUG_DEVELOPER);

    $antivirus = \core\antivirus\manager::get_antivirus('clamav');
    $antivirus->message_admins($notice);
}


function get_clam_error_code($returncode) {
    debugging('get_clam_error_code() is deprecated, please use get_clam_error_code() method of \antivirus_clamav\scanner class.', DEBUG_DEVELOPER);

    $antivirus = \core\antivirus\manager::get_antivirus('clamav');
    return $antivirus->get_clam_error_code($returncode);
}


function course_get_cm_rename_action(cm_info $mod, $sr = null) {
    global $COURSE, $OUTPUT;

    static $str;
    static $baseurl;

    debugging('Function course_get_cm_rename_action() is deprecated. Please use inplace_editable ' .
        'https://docs.moodle.org/dev/Inplace_editable', DEBUG_DEVELOPER);

    $modcontext = context_module::instance($mod->id);
    $hasmanageactivities = has_capability('moodle/course:manageactivities', $modcontext);

    if (!isset($str)) {
        $str = get_strings(array('edittitle'));
    }

    if (!isset($baseurl)) {
        $baseurl = new moodle_url('/course/mod.php', array('sesskey' => sesskey()));
    }

    if ($sr !== null) {
        $baseurl->param('sr', $sr);
    }

        if ($mod->has_view() && $hasmanageactivities && course_ajax_enabled($COURSE) &&
        (($mod->course == $COURSE->id) || ($mod->course == SITEID))) {
                return html_writer::span(
            html_writer::link(
                new moodle_url($baseurl, array('update' => $mod->id)),
                $OUTPUT->pix_icon('t/editstring', '', 'moodle', array('class' => 'iconsmall visibleifjs', 'title' => '')),
                array(
                    'class' => 'editing_title',
                    'data-action' => 'edittitle',
                    'title' => $str->edittitle,
                )
            )
        );
    }
    return '';
}


function course_scale_used($courseid, $scaleid) {
    global $CFG, $DB;

    debugging('course_scale_used() is deprecated and never used, plugins can implement <modname>_scale_used_anywhere, '.
        'all implementations of <modname>_scale_used are now ignored', DEBUG_DEVELOPER);

    $return = 0;

    if (!empty($scaleid)) {
        if ($cms = get_course_mods($courseid)) {
            foreach ($cms as $cm) {
                                if (file_exists($CFG->dirroot.'/mod/'.$cm->modname.'/lib.php')) {
                    include_once($CFG->dirroot.'/mod/'.$cm->modname.'/lib.php');
                    $functionname = $cm->modname.'_scale_used';
                    if (function_exists($functionname)) {
                        if ($functionname($cm->instance, $scaleid)) {
                            $return++;
                        }
                    }
                }
            }
        }

                $return += $DB->count_records('grade_items', array('courseid' => $courseid, 'scaleid' => $scaleid));

                $return += $DB->count_records_sql("SELECT COUNT('x')
                                             FROM {grade_outcomes_courses} goc,
                                                  {grade_outcomes} go
                                            WHERE go.id = goc.outcomeid
                                                  AND go.scaleid = ? AND goc.courseid = ?",
            array($scaleid, $courseid));
    }
    return $return;
}


function site_scale_used($scaleid, &$courses) {
    $return = 0;

    debugging('site_scale_used() is deprecated and never used, plugins can implement <modname>_scale_used_anywhere, '.
        'all implementations of <modname>_scale_used are now ignored', DEBUG_DEVELOPER);

    if (!is_array($courses) || count($courses) == 0) {
        $courses = get_courses("all", false, "c.id, c.shortname");
    }

    if (!empty($scaleid)) {
        if (is_array($courses) && count($courses) > 0) {
            foreach ($courses as $course) {
                $return += course_scale_used($course->id, $scaleid);
            }
        }
    }
    return $return;
}


function external_function_info($function, $strictness=MUST_EXIST) {
    debugging('external_function_info() is deprecated. Please use external_api::external_function_info() instead.',
              DEBUG_DEVELOPER);
    return external_api::external_function_info($function, $strictness);
}


function file_modify_html_header($text) {
    debugging('file_modify_html_header() is deprecated and will not be replaced.', DEBUG_DEVELOPER);
    return $text;
}
