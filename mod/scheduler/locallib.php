<?php



defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/customlib.php');

require_once(dirname(__FILE__).'/model/scheduler_instance.php');
require_once(dirname(__FILE__).'/model/scheduler_slot.php');
require_once(dirname(__FILE__).'/model/scheduler_appointment.php');





function scheduler_delete_calendar_events($slot) {
    global $DB;

    $scheduler = $DB->get_record('scheduler', array('id' => $slot->schedulerid));

    if (!$scheduler) {
        return false;
    }

    $teachereventtype = "SSsup:{$slot->id}:{$scheduler->course}";
    $studenteventtype = "SSstu:{$slot->id}:{$scheduler->course}";

    $teacherdeletionsuccess = $DB->delete_records('event', array('eventtype' => $teachereventtype));
    $studentdeletionsuccess = $DB->delete_records('event', array('eventtype' => $studenteventtype));

    return ($teacherdeletionsuccess && $studentdeletionsuccess);
    }





function scheduler_print_user($user, $course, $messageselect=false, $return=false) {

    global $CFG, $USER, $OUTPUT;

    $output = '';

    static $string;
    static $datestring;
    static $countries;

    $context = context_course::instance($course->id);
    if (isset($user->context->id)) {
        $usercontext = $user->context;
    } else {
        $usercontext = context_user::instance($user->id);
    }

    if (empty($string)) {     
        $string = new stdClass();
        $string->email       = get_string('email');
        $string->lastaccess  = get_string('lastaccess');
        $string->activity    = get_string('activity');
        $string->loginas     = get_string('loginas');
        $string->fullprofile = get_string('fullprofile');
        $string->role        = get_string('role');
        $string->name        = get_string('name');
        $string->never       = get_string('never');

        $datestring = new stdClass();
        $datestring->day     = get_string('day');
        $datestring->days    = get_string('days');
        $datestring->hour    = get_string('hour');
        $datestring->hours   = get_string('hours');
        $datestring->min     = get_string('min');
        $datestring->mins    = get_string('mins');
        $datestring->sec     = get_string('sec');
        $datestring->secs    = get_string('secs');
        $datestring->year    = get_string('year');
        $datestring->years   = get_string('years');

    }

        if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
        $hiddenfields = array();
    } else {
        $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
    }

    $output .= '<table class="userinfobox">';
    $output .= '<tr>';
    $output .= '<td class="left side">';
    $output .= $OUTPUT->user_picture($user, array('size' => 100));
    $output .= '</td>';
    $output .= '<td class="content">';
    $output .= '<div class="username">'.fullname($user, has_capability('moodle/site:viewfullnames', $context)).'</div>';
    $output .= '<div class="info">';
    if (!empty($user->role) and ($user->role <> $course->teacher)) {
        $output .= $string->role .': '. $user->role .'<br />';
    }

    $extrafields = scheduler_get_user_fields($user);
    foreach ($extrafields as $field) {
        $output .= $field->title . ': ' . $field->value . '<br />';
    }

    if (!isset($hiddenfields['lastaccess'])) {
        if ($user->lastaccess) {
            $output .= $string->lastaccess .': '. userdate($user->lastaccess);
            $output .= '&nbsp; ('. format_time(time() - $user->lastaccess, $datestring) .')';
        } else {
            $output .= $string->lastaccess .': '. $string->never;
        }
    }
    $output .= '</div></td><td class="links">';
        if ($CFG->bloglevel > 0) {
        $output .= '<a href="'.$CFG->wwwroot.'/blog/index.php?userid='.$user->id.'">'.get_string('blogs', 'blog').'</a><br />';
    }
        if (!empty($CFG->enablenotes) and (has_capability('moodle/notes:manage', $context)
            || has_capability('moodle/notes:view', $context))) {
        $output .= '<a href="'.$CFG->wwwroot.'/notes/index.php?course=' . $course->id. '&amp;user='.$user->id.'">'.
                    get_string('notes', 'notes').'</a><br />';
    }

    if (has_capability('moodle/site:viewreports', $context) or
            has_capability('moodle/user:viewuseractivitiesreport', $usercontext)) {
        $output .= '<a href="'. $CFG->wwwroot .'/course/user.php?id='. $course->id .'&amp;user='. $user->id .'">'.
                    $string->activity .'</a><br />';
    }
    $output .= '<a href="'. $CFG->wwwroot .'/user/profile.php?id='. $user->id .'">'. $string->fullprofile .'...</a>';

    if (!empty($messageselect)) {
        $output .= '<br /><input type="checkbox" name="user'.$user->id.'" /> ';
    }

    $output .= '</td></tr></table>';

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}



class scheduler_file_info extends file_info {
    
    protected $course;
    
    protected $cm;
    
    protected $areas;
    
    protected $filearea;
    
    protected $scheduler;

    
    public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
        parent::__construct($browser, $context);
        $this->course   = $course;
        $this->cm       = $cm;
        $this->areas    = $areas;
        $this->filearea = $filearea;
        $this->scheduler = scheduler_instance::load_by_coursemodule_id($cm->id);
    }

    
    public function get_params() {
        return array('contextid' => $this->context->id,
                     'component' => 'mod_scheduler',
                     'filearea'  => $this->filearea,
                     'itemid'    => null,
                     'filepath'  => null,
                     'filename'  => null);
    }

    
    public function get_visible_name() {
        return $this->areas[$this->filearea];
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        global $DB;

        $params = array('contextid' => $this->context->id,
                        'component' => 'mod_scheduler',
                        'filearea' => $this->filearea);
        $sql = "SELECT DISTINCT f.itemid AS id
                           FROM {files} f
                          WHERE f.contextid = :contextid
                                AND f.component = :component
                                AND f.filearea = :filearea";
        if (!$returnemptyfolders) {
            $sql .= ' AND filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }
        list($sql2, $params2) = $this->build_search_files_sql($extensions, 'f');
        $sql .= ' '.$sql2;
        $params = array_merge($params, $params2);

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = array();
        foreach ($rs as $record) {
            if ($child = $this->browser->get_file_info($this->context, 'mod_scheduler', $this->filearea, $record->id)) {
                if ($returnemptyfolders || $child->count_non_empty_children($extensions)) {
                    $children[] = $child;
                }
            }
            if ($countonly !== false && count($children) >= $countonly) {
                break;
            }
        }
        $rs->close();
        if ($countonly !== false) {
            return count($children);
        }
        return $children;
    }

    
    public function get_non_empty_children($extensions = '*') {
        return $this->get_filtered_children($extensions, false);
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        return $this->get_filtered_children($extensions, $limit);
    }

    
    public function get_parent() {
        return $this->browser->get_file_info($this->context);
    }
}
