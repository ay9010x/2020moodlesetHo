<?php




defined('MOODLE_INTERNAL') || die();

define ('DATA_MAX_ENTRIES', 50);
define ('DATA_PERPAGE_SINGLE', 1);

define ('DATA_FIRSTNAME', -1);
define ('DATA_LASTNAME', -2);
define ('DATA_APPROVED', -3);
define ('DATA_TIMEADDED', 0);
define ('DATA_TIMEMODIFIED', -4);

define ('DATA_CAP_EXPORT', 'mod/data:viewalluserpresets');

define('DATA_PRESET_COMPONENT', 'mod_data');
define('DATA_PRESET_FILEAREA', 'site_presets');
define('DATA_PRESET_CONTEXT', SYSCONTEXTID);



class data_field_base {     
    
    var $type = 'unknown';
    
    var $data = NULL;
    
    var $field = NULL;
    
    var $iconwidth = 16;
    
    var $iconheight = 16;
    
    var $cm;
    
    var $context;

    
    function __construct($field=0, $data=0, $cm=0) {           global $DB;

        if (empty($field) && empty($data)) {
            print_error('missingfield', 'data');
        }

        if (!empty($field)) {
            if (is_object($field)) {
                $this->field = $field;              } else if (!$this->field = $DB->get_record('data_fields', array('id'=>$field))) {
                print_error('invalidfieldid', 'data');
            }
            if (empty($data)) {
                if (!$this->data = $DB->get_record('data', array('id'=>$this->field->dataid))) {
                    print_error('invalidid', 'data');
                }
            }
        }

        if (empty($this->data)) {                     if (!empty($data)) {
                if (is_object($data)) {
                    $this->data = $data;                  } else if (!$this->data = $DB->get_record('data', array('id'=>$data))) {
                    print_error('invalidid', 'data');
                }
            } else {                                      print_error('missingdata', 'data');
            }
        }

        if ($cm) {
            $this->cm = $cm;
        } else {
            $this->cm = get_coursemodule_from_instance('data', $this->data->id);
        }

        if (empty($this->field)) {                     $this->define_default_field();
        }

        $this->context = context_module::instance($this->cm->id);
    }


    
    function define_default_field() {
        global $OUTPUT;
        if (empty($this->data->id)) {
            echo $OUTPUT->notification('Programmer error: dataid not defined in field class');
        }
        $this->field = new stdClass();
        $this->field->id = 0;
        $this->field->dataid = $this->data->id;
        $this->field->type   = $this->type;
        $this->field->param1 = '';
        $this->field->param2 = '';
        $this->field->param3 = '';
        $this->field->name = '';
        $this->field->description = '';
        $this->field->required = false;

        return true;
    }

    
    function define_field($data) {
        $this->field->type        = $this->type;
        $this->field->dataid      = $this->data->id;

        $this->field->name        = trim($data->name);
        $this->field->description = trim($data->description);
        $this->field->required    = !empty($data->required) ? 1 : 0;

        if (isset($data->param1)) {
            $this->field->param1 = trim($data->param1);
        }
        if (isset($data->param2)) {
            $this->field->param2 = trim($data->param2);
        }
        if (isset($data->param3)) {
            $this->field->param3 = trim($data->param3);
        }
        if (isset($data->param4)) {
            $this->field->param4 = trim($data->param4);
        }
        if (isset($data->param5)) {
            $this->field->param5 = trim($data->param5);
        }

        return true;
    }

    
    function insert_field() {
        global $DB, $OUTPUT;

        if (empty($this->field)) {
            echo $OUTPUT->notification('Programmer error: Field has not been defined yet!  See define_field()');
            return false;
        }

        $this->field->id = $DB->insert_record('data_fields',$this->field);

                $event = \mod_data\event\field_created::create(array(
            'objectid' => $this->field->id,
            'context' => $this->context,
            'other' => array(
                'fieldname' => $this->field->name,
                'dataid' => $this->data->id
            )
        ));
        $event->trigger();

        return true;
    }


    
    function update_field() {
        global $DB;

        $DB->update_record('data_fields', $this->field);

                $event = \mod_data\event\field_updated::create(array(
            'objectid' => $this->field->id,
            'context' => $this->context,
            'other' => array(
                'fieldname' => $this->field->name,
                'dataid' => $this->data->id
            )
        ));
        $event->trigger();

        return true;
    }

    
    function delete_field() {
        global $DB;

        if (!empty($this->field->id)) {
                        $field = $DB->get_record('data_fields', array('id' => $this->field->id));

            $this->delete_content();
            $DB->delete_records('data_fields', array('id'=>$this->field->id));

                        $event = \mod_data\event\field_deleted::create(array(
                'objectid' => $this->field->id,
                'context' => $this->context,
                'other' => array(
                    'fieldname' => $this->field->name,
                    'dataid' => $this->data->id
                 )
            ));
            $event->add_record_snapshot('data_fields', $field);
            $event->trigger();
        }

        return true;
    }

    
    function display_add_field($recordid=0, $formdata=null) {
        global $DB, $OUTPUT;

        if ($formdata) {
            $fieldname = 'field_' . $this->field->id;
            $content = $formdata->$fieldname;
        } else if ($recordid) {
            $content = $DB->get_field('data_content', 'content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid));
        } else {
            $content = '';
        }

                if ($content===false) {
            $content='';
        }

        $str = '<div title="' . s($this->field->description) . '">';
        $str .= '<label for="field_'.$this->field->id.'"><span class="accesshide">'.$this->field->name.'</span>';
        if ($this->field->required) {
            $image = html_writer::img($OUTPUT->pix_url('req'), get_string('requiredelement', 'form'),
                                     array('class' => 'req', 'title' => get_string('requiredelement', 'form')));
            $str .= html_writer::div($image, 'inline-req');
        }
        $str .= '</label><input class="basefieldinput mod-data-input" type="text" name="field_'.$this->field->id.'"';
        $str .= ' id="field_' . $this->field->id . '" value="'.s($content).'" />';
        $str .= '</div>';

        return $str;
    }

    
    function display_edit_field() {
        global $CFG, $DB, $OUTPUT;

        if (empty($this->field)) {               $this->define_default_field();
        }
        echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');

        echo '<form id="editfield" action="'.$CFG->wwwroot.'/mod/data/field.php" method="post">'."\n";
        echo '<input type="hidden" name="d" value="'.$this->data->id.'" />'."\n";
        if (empty($this->field->id)) {
            echo '<input type="hidden" name="mode" value="add" />'."\n";
            $savebutton = get_string('add');
        } else {
            echo '<input type="hidden" name="fid" value="'.$this->field->id.'" />'."\n";
            echo '<input type="hidden" name="mode" value="update" />'."\n";
            $savebutton = get_string('savechanges');
        }
        echo '<input type="hidden" name="type" value="'.$this->type.'" />'."\n";
        echo '<input name="sesskey" value="'.sesskey().'" type="hidden" />'."\n";

        echo $OUTPUT->heading($this->name(), 3);

        require_once($CFG->dirroot.'/mod/data/field/'.$this->type.'/mod.html');

        echo '<div class="mdl-align">';
        echo '<input type="submit" value="'.$savebutton.'" />'."\n";
        echo '<input type="submit" name="cancel" value="'.get_string('cancel').'" />'."\n";
        echo '</div>';

        echo '</form>';

        echo $OUTPUT->box_end();
    }

    
    function display_browse_field($recordid, $template) {
        global $DB;

        if ($content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            if (isset($content->content)) {
                $options = new stdClass();
                if ($this->field->param1 == '1') {                                                              $options->filter=false;
                }
                $options->para = false;
                $str = format_text($content->content, $content->content1, $options);
            } else {
                $str = '';
            }
            return $str;
        }
        return false;
    }

    
    function update_content($recordid, $value, $name=''){
        global $DB;

        $content = new stdClass();
        $content->fieldid = $this->field->id;
        $content->recordid = $recordid;
        $content->content = clean_param($value, PARAM_NOTAGS);

        if ($oldcontent = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            $content->id = $oldcontent->id;
            return $DB->update_record('data_content', $content);
        } else {
            return $DB->insert_record('data_content', $content);
        }
    }

    
    function delete_content($recordid=0) {
        global $DB;

        if ($recordid) {
            $conditions = array('fieldid'=>$this->field->id, 'recordid'=>$recordid);
        } else {
            $conditions = array('fieldid'=>$this->field->id);
        }

        $rs = $DB->get_recordset('data_content', $conditions);
        if ($rs->valid()) {
            $fs = get_file_storage();
            foreach ($rs as $content) {
                $fs->delete_area_files($this->context->id, 'mod_data', 'content', $content->id);
            }
        }
        $rs->close();

        return $DB->delete_records('data_content', $conditions);
    }

    
    function notemptyfield($value, $name) {
        return !empty($value);
    }

    
    function print_before_form() {
    }

    
    function print_after_form() {
    }


    
    function get_sort_field() {
        return 'content';
    }

    
    function get_sort_sql($fieldname) {
        return $fieldname;
    }

    
    function name() {
        return get_string('name'.$this->type, 'data');
    }

    
    function image() {
        global $OUTPUT;

        $params = array('d'=>$this->data->id, 'fid'=>$this->field->id, 'mode'=>'display', 'sesskey'=>sesskey());
        $link = new moodle_url('/mod/data/field.php', $params);
        $str = '<a href="'.$link->out().'">';
        $str .= '<img src="'.$OUTPUT->pix_url('field/'.$this->type, 'data') . '" ';
        $str .= 'height="'.$this->iconheight.'" width="'.$this->iconwidth.'" alt="'.$this->type.'" title="'.$this->type.'" /></a>';
        return $str;
    }

    
    function text_export_supported() {
        return true;
    }

    
    function export_text_value($record) {
        if ($this->text_export_supported()) {
            return $record->content;
        }
    }

    
    function file_ok($relativepath) {
        return false;
    }
}



function data_generate_default_template(&$data, $template, $recordid=0, $form=false, $update=true) {
    global $DB;

    if (!$data && !$template) {
        return false;
    }
    if ($template == 'csstemplate' or $template == 'jstemplate' ) {
        return '';
    }

        if ($fields = $DB->get_records('data_fields', array('dataid'=>$data->id), 'id')) {

        $table = new html_table();
        $table->attributes['class'] = 'mod-data-default-template ##approvalstatus##';
        $table->colclasses = array('template-field', 'template-token');
        $table->data = array();
        foreach ($fields as $field) {
            if ($form) {                   $fieldobj = data_get_field($field, $data);
                $token = $fieldobj->display_add_field($recordid, null);
            } else {                           $token = '[['.$field->name.']]';
            }
            $table->data[] = array(
                $field->name.': ',
                $token
            );
        }
        if ($template == 'listtemplate') {
            $cell = new html_table_cell('##edit##  ##more##  ##delete##  ##approve##  ##disapprove##  ##export##');
            $cell->colspan = 2;
            $cell->attributes['class'] = 'controls';
            $table->data[] = new html_table_row(array($cell));
        } else if ($template == 'singletemplate') {
            $cell = new html_table_cell('##edit##  ##delete##  ##approve##  ##disapprove##  ##export##');
            $cell->colspan = 2;
            $cell->attributes['class'] = 'controls';
            $table->data[] = new html_table_row(array($cell));
        } else if ($template == 'asearchtemplate') {
            $row = new html_table_row(array(get_string('authorfirstname', 'data').': ', '##firstname##'));
            $row->attributes['class'] = 'searchcontrols';
            $table->data[] = $row;
            $row = new html_table_row(array(get_string('authorlastname', 'data').': ', '##lastname##'));
            $row->attributes['class'] = 'searchcontrols';
            $table->data[] = $row;
        }

        $str = '';
        if ($template == 'listtemplate'){
            $str .= '##delcheck##';
            $str .= html_writer::empty_tag('br');
        }

        $str .= html_writer::start_tag('div', array('class' => 'defaulttemplate'));
        $str .= html_writer::table($table);
        $str .= html_writer::end_tag('div');
        if ($template == 'listtemplate'){
            $str .= html_writer::empty_tag('hr');
        }

        if ($update) {
            $newdata = new stdClass();
            $newdata->id = $data->id;
            $newdata->{$template} = $str;
            $DB->update_record('data', $newdata);
            $data->{$template} = $str;
        }

        return $str;
    }
}



function data_replace_field_in_templates($data, $searchfieldname, $newfieldname) {
    global $DB;

    if (!empty($newfieldname)) {
        $prestring = '[[';
        $poststring = ']]';
        $idpart = '#id';

    } else {
        $prestring = '';
        $poststring = '';
        $idpart = '';
    }

    $newdata = new stdClass();
    $newdata->id = $data->id;
    $newdata->singletemplate = str_ireplace('[['.$searchfieldname.']]',
            $prestring.$newfieldname.$poststring, $data->singletemplate);

    $newdata->listtemplate = str_ireplace('[['.$searchfieldname.']]',
            $prestring.$newfieldname.$poststring, $data->listtemplate);

    $newdata->addtemplate = str_ireplace('[['.$searchfieldname.']]',
            $prestring.$newfieldname.$poststring, $data->addtemplate);

    $newdata->addtemplate = str_ireplace('[['.$searchfieldname.'#id]]',
            $prestring.$newfieldname.$idpart.$poststring, $data->addtemplate);

    $newdata->rsstemplate = str_ireplace('[['.$searchfieldname.']]',
            $prestring.$newfieldname.$poststring, $data->rsstemplate);

    return $DB->update_record('data', $newdata);
}



function data_append_new_field_to_templates($data, $newfieldname) {
    global $DB;

    $newdata = new stdClass();
    $newdata->id = $data->id;
    $change = false;

    if (!empty($data->singletemplate)) {
        $newdata->singletemplate = $data->singletemplate.' [[' . $newfieldname .']]';
        $change = true;
    }
    if (!empty($data->addtemplate)) {
        $newdata->addtemplate = $data->addtemplate.' [[' . $newfieldname . ']]';
        $change = true;
    }
    if (!empty($data->rsstemplate)) {
        $newdata->rsstemplate = $data->singletemplate.' [[' . $newfieldname . ']]';
        $change = true;
    }
    if ($change) {
        $DB->update_record('data', $newdata);
    }
}



function data_get_field_from_name($name, $data){
    global $DB;

    $field = $DB->get_record('data_fields', array('name'=>$name, 'dataid'=>$data->id));

    if ($field) {
        return data_get_field($field, $data);
    } else {
        return false;
    }
}


function data_get_field_from_id($fieldid, $data){
    global $DB;

    $field = $DB->get_record('data_fields', array('id'=>$fieldid, 'dataid'=>$data->id));

    if ($field) {
        return data_get_field($field, $data);
    } else {
        return false;
    }
}


function data_get_field_new($type, $data) {
    global $CFG;

    require_once($CFG->dirroot.'/mod/data/field/'.$type.'/field.class.php');
    $newfield = 'data_field_'.$type;
    $newfield = new $newfield(0, $data);
    return $newfield;
}


function data_get_field($field, $data, $cm=null) {
    global $CFG;

    if ($field) {
        require_once('field/'.$field->type.'/field.class.php');
        $newfield = 'data_field_'.$field->type;
        $newfield = new $newfield($field, $data, $cm);
        return $newfield;
    }
}



function data_isowner($record) {
    global $USER, $DB;

    if (!isloggedin()) {         return false;
    }

    if (!is_object($record)) {
        if (!$record = $DB->get_record('data_records', array('id'=>$record))) {
            return false;
        }
    }

    return ($record->userid == $USER->id);
}


function data_atmaxentries($data){
    if (!$data->maxentries){
        return false;

    } else {
        return (data_numentries($data) >= $data->maxentries);
    }
}


function data_numentries($data){
    global $USER, $DB;
    $sql = 'SELECT COUNT(*) FROM {data_records} WHERE dataid=? AND userid=?';
    return $DB->count_records_sql($sql, array($data->id, $USER->id));
}


function data_add_record($data, $groupid=0){
    global $USER, $DB;

    $cm = get_coursemodule_from_instance('data', $data->id);
    $context = context_module::instance($cm->id);

    $record = new stdClass();
    $record->userid = $USER->id;
    $record->dataid = $data->id;
    $record->groupid = $groupid;
    $record->timecreated = $record->timemodified = time();
    if (has_capability('mod/data:approve', $context)) {
        $record->approved = 1;
    } else {
        $record->approved = 0;
    }
    $record->id = $DB->insert_record('data_records', $record);

        $event = \mod_data\event\record_created::create(array(
        'objectid' => $record->id,
        'context' => $context,
        'other' => array(
            'dataid' => $data->id
        )
    ));
    $event->trigger();

    return $record->id;
}


function data_tags_check($dataid, $template) {
    global $DB, $OUTPUT;

        $fields = $DB->get_records('data_fields', array('dataid'=>$dataid));
        $tagsok = true;     foreach ($fields as $field){
        $pattern="/\[\[" . preg_quote($field->name, '/') . "\]\]/i";
        if (preg_match_all($pattern, $template, $dummy)>1){
            $tagsok = false;
            echo $OUTPUT->notification('[['.$field->name.']] - '.get_string('multipletags','data'));
        }
    }
        return $tagsok;
}


function data_add_instance($data, $mform = null) {
    global $DB;

    if (empty($data->assessed)) {
        $data->assessed = 0;
    }

    if (empty($data->ratingtime) || empty($data->assessed)) {
        $data->assesstimestart  = 0;
        $data->assesstimefinish = 0;
    }

    $data->timemodified = time();

    $data->id = $DB->insert_record('data', $data);

    data_grade_item_update($data);

    return $data->id;
}


function data_update_instance($data) {
    global $DB, $OUTPUT;

    $data->timemodified = time();
    $data->id           = $data->instance;

    if (empty($data->assessed)) {
        $data->assessed = 0;
    }

    if (empty($data->ratingtime) or empty($data->assessed)) {
        $data->assesstimestart  = 0;
        $data->assesstimefinish = 0;
    }

    if (empty($data->notification)) {
        $data->notification = 0;
    }

    $DB->update_record('data', $data);

    data_grade_item_update($data);

    return true;

}


function data_delete_instance($id) {        global $DB, $CFG;

    if (!$data = $DB->get_record('data', array('id'=>$id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('data', $data->id);
    $context = context_module::instance($cm->id);


        $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_data');

        $sql = "SELECT r.id
              FROM {data_records} r
             WHERE r.dataid = ?";

    $DB->delete_records_select('data_content', "recordid IN ($sql)", array($id));

        $DB->delete_records('data_records', array('dataid'=>$id));
    $DB->delete_records('data_fields', array('dataid'=>$id));

        $result = $DB->delete_records('data', array('id'=>$id));

        data_grade_item_delete($data);

    return $result;
}


function data_user_outline($course, $user, $mod, $data) {
    global $DB, $CFG;
    require_once("$CFG->libdir/gradelib.php");

    $grades = grade_get_grades($course->id, 'mod', 'data', $data->id, $user->id);
    if (empty($grades->items[0]->grades)) {
        $grade = false;
    } else {
        $grade = reset($grades->items[0]->grades);
    }


    if ($countrecords = $DB->count_records('data_records', array('dataid'=>$data->id, 'userid'=>$user->id))) {
        $result = new stdClass();
        $result->info = get_string('numrecords', 'data', $countrecords);
        $lastrecord   = $DB->get_record_sql('SELECT id,timemodified FROM {data_records}
                                              WHERE dataid = ? AND userid = ?
                                           ORDER BY timemodified DESC', array($data->id, $user->id), true);
        $result->time = $lastrecord->timemodified;
        if ($grade) {
            $result->info .= ', ' . get_string('grade') . ': ' . $grade->str_long_grade;
        }
        return $result;
    } else if ($grade) {
        $result = new stdClass();
        $result->info = get_string('grade') . ': ' . $grade->str_long_grade;

                                if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
            $result->time = $grade->dategraded;
        } else {
            $result->time = $grade->datesubmitted;
        }

        return $result;
    }
    return NULL;
}


function data_user_complete($course, $user, $mod, $data) {
    global $DB, $CFG, $OUTPUT;
    require_once("$CFG->libdir/gradelib.php");

    $grades = grade_get_grades($course->id, 'mod', 'data', $data->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        echo $OUTPUT->container(get_string('grade').': '.$grade->str_long_grade);
        if ($grade->str_feedback) {
            echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
        }
    }

    if ($records = $DB->get_records('data_records', array('dataid'=>$data->id,'userid'=>$user->id), 'timemodified DESC')) {
        data_print_template('singletemplate', $records, $data);
    }
}


function data_get_user_grades($data, $userid=0) {
    global $CFG;

    require_once($CFG->dirroot.'/rating/lib.php');

    $ratingoptions = new stdClass;
    $ratingoptions->component = 'mod_data';
    $ratingoptions->ratingarea = 'entry';
    $ratingoptions->modulename = 'data';
    $ratingoptions->moduleid   = $data->id;

    $ratingoptions->userid = $userid;
    $ratingoptions->aggregationmethod = $data->assessed;
    $ratingoptions->scaleid = $data->scale;
    $ratingoptions->itemtable = 'data_records';
    $ratingoptions->itemtableusercolumn = 'userid';

    $rm = new rating_manager();
    return $rm->get_user_grades($ratingoptions);
}


function data_update_grades($data, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (!$data->assessed) {
        data_grade_item_update($data);

    } else if ($grades = data_get_user_grades($data, $userid)) {
        data_grade_item_update($data, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = NULL;
        data_grade_item_update($data, $grade);

    } else {
        data_grade_item_update($data);
    }
}


function data_grade_item_update($data, $grades=NULL) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array('itemname'=>$data->name, 'idnumber'=>$data->cmidnumber);

    if (!$data->assessed or $data->scale == 0) {
        $params['gradetype'] = GRADE_TYPE_NONE;

    } else if ($data->scale > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $data->scale;
        $params['grademin']  = 0;

    } else if ($data->scale < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$data->scale;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/data', $data->course, 'mod', 'data', $data->id, 0, $grades, $params);
}


function data_grade_item_delete($data) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/data', $data->course, 'mod', 'data', $data->id, 0, NULL, array('deleted'=>1));
}


function data_print_template($template, $records, $data, $search='', $page=0, $return=false, moodle_url $jumpurl=null) {
    global $CFG, $DB, $OUTPUT;

    $cm = get_coursemodule_from_instance('data', $data->id);
    $context = context_module::instance($cm->id);

    static $fields = array();
    static $dataid = null;

    if (empty($dataid)) {
        $dataid = $data->id;
    } else if ($dataid != $data->id) {
        $fields = array();
    }

    if (empty($fields)) {
        $fieldrecords = $DB->get_records('data_fields', array('dataid'=>$data->id));
        foreach ($fieldrecords as $fieldrecord) {
            $fields[]= data_get_field($fieldrecord, $data);
        }
    }

    if (empty($records)) {
        return;
    }

    if (!$jumpurl) {
        $jumpurl = new moodle_url('/mod/data/view.php', array('d' => $data->id));
    }
    $jumpurl = new moodle_url($jumpurl, array('page' => $page, 'sesskey' => sesskey()));

    foreach ($records as $record) {   
            $patterns = array();
        $replacement = array();

            foreach ($fields as $field) {
            $patterns[]='[['.$field->field->name.']]';
            $replacement[] = highlight($search, $field->display_browse_field($record->id, $template));
        }

        $canmanageentries = has_capability('mod/data:manageentries', $context);

            $patterns[]='##edit##';
        $patterns[]='##delete##';
        if (data_user_can_manage_entry($record, $data, $context)) {
            $replacement[] = '<a href="'.$CFG->wwwroot.'/mod/data/edit.php?d='
                             .$data->id.'&amp;rid='.$record->id.'&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('t/edit') . '" class="iconsmall" alt="'.get_string('edit').'" title="'.get_string('edit').'" /></a>';
            $replacement[] = '<a href="'.$CFG->wwwroot.'/mod/data/view.php?d='
                             .$data->id.'&amp;delete='.$record->id.'&amp;sesskey='.sesskey().'"><img src="'.$OUTPUT->pix_url('t/delete') . '" class="iconsmall" alt="'.get_string('delete').'" title="'.get_string('delete').'" /></a>';
        } else {
            $replacement[] = '';
            $replacement[] = '';
        }

        $moreurl = $CFG->wwwroot . '/mod/data/view.php?d=' . $data->id . '&amp;rid=' . $record->id;
        if ($search) {
            $moreurl .= '&amp;filter=1';
        }
        $patterns[]='##more##';
        $replacement[] = '<a href="'.$moreurl.'"><img src="'.$OUTPUT->pix_url('t/preview').
                        '" class="iconsmall" alt="'.get_string('more', 'data').'" title="'.get_string('more', 'data').
                        '" /></a>';

        $patterns[]='##moreurl##';
        $replacement[] = $moreurl;

        $patterns[]='##delcheck##';
        if ($canmanageentries) {
            $replacement[] = html_writer::checkbox('delcheck[]', $record->id, false, '', array('class' => 'recordcheckbox'));
        } else {
            $replacement[] = '';
        }

        $patterns[]='##user##';
        $replacement[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$record->userid.
                               '&amp;course='.$data->course.'">'.fullname($record).'</a>';

        $patterns[] = '##userpicture##';
        $ruser = user_picture::unalias($record, null, 'userid');
        $replacement[] = $OUTPUT->user_picture($ruser, array('courseid' => $data->course));

        $patterns[]='##export##';

        if (!empty($CFG->enableportfolios) && ($template == 'singletemplate' || $template == 'listtemplate')
            && ((has_capability('mod/data:exportentry', $context)
                || (data_isowner($record->id) && has_capability('mod/data:exportownentry', $context))))) {
            require_once($CFG->libdir . '/portfoliolib.php');
            $button = new portfolio_add_button();
            $button->set_callback_options('data_portfolio_caller', array('id' => $cm->id, 'recordid' => $record->id), 'mod_data');
            list($formats, $files) = data_portfolio_caller::formats($fields, $record);
            $button->set_formats($formats);
            $replacement[] = $button->to_html(PORTFOLIO_ADD_ICON_LINK);
        } else {
            $replacement[] = '';
        }

        $patterns[] = '##timeadded##';
        $replacement[] = userdate($record->timecreated);

        $patterns[] = '##timemodified##';
        $replacement [] = userdate($record->timemodified);

        $patterns[]='##approve##';
        if (has_capability('mod/data:approve', $context) && ($data->approval) && (!$record->approved)) {
            $approveurl = new moodle_url($jumpurl, array('approve' => $record->id));
            $approveicon = new pix_icon('t/approve', get_string('approve', 'data'), '', array('class' => 'iconsmall'));
            $replacement[] = html_writer::tag('span', $OUTPUT->action_icon($approveurl, $approveicon),
                    array('class' => 'approve'));
        } else {
            $replacement[] = '';
        }

        $patterns[]='##disapprove##';
        if (has_capability('mod/data:approve', $context) && ($data->approval) && ($record->approved)) {
            $disapproveurl = new moodle_url($jumpurl, array('disapprove' => $record->id));
            $disapproveicon = new pix_icon('t/block', get_string('disapprove', 'data'), '', array('class' => 'iconsmall'));
            $replacement[] = html_writer::tag('span', $OUTPUT->action_icon($disapproveurl, $disapproveicon),
                    array('class' => 'disapprove'));
        } else {
            $replacement[] = '';
        }

        $patterns[] = '##approvalstatus##';
        if (!$data->approval) {
            $replacement[] = '';
        } else if ($record->approved) {
            $replacement[] = get_string('approved', 'data');
        } else {
            $replacement[] = get_string('notapproved', 'data');
        }

        $patterns[]='##comments##';
        if (($template == 'listtemplate') && ($data->comments)) {

            if (!empty($CFG->usecomments)) {
                require_once($CFG->dirroot  . '/comment/lib.php');
                list($context, $course, $cm) = get_context_info_array($context->id);
                $cmt = new stdClass();
                $cmt->context = $context;
                $cmt->course  = $course;
                $cmt->cm      = $cm;
                $cmt->area    = 'database_entry';
                $cmt->itemid  = $record->id;
                $cmt->showcount = true;
                $cmt->component = 'mod_data';
                $comment = new comment($cmt);
                $replacement[] = $comment->output(true);
            }
        } else {
            $replacement[] = '';
        }

                $newtext = str_ireplace($patterns, $replacement, $data->{$template});

                if ($return) {
            return $newtext;
        } else {
            echo $newtext;

                        
            if ($template == 'singletemplate') {                    data_print_ratings($data, $record);
            }

            
            if (($template == 'singletemplate') && ($data->comments)) {
                if (!empty($CFG->usecomments)) {
                    require_once($CFG->dirroot . '/comment/lib.php');
                    list($context, $course, $cm) = get_context_info_array($context->id);
                    $cmt = new stdClass();
                    $cmt->context = $context;
                    $cmt->course  = $course;
                    $cmt->cm      = $cm;
                    $cmt->area    = 'database_entry';
                    $cmt->itemid  = $record->id;
                    $cmt->showcount = true;
                    $cmt->component = 'mod_data';
                    $comment = new comment($cmt);
                    $comment->output(false);
                }
            }
        }
    }
}


function data_rating_permissions($contextid, $component, $ratingarea) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($component != 'mod_data' || $ratingarea != 'entry') {
        return null;
    }
    return array(
        'view'    => has_capability('mod/data:viewrating',$context),
        'viewany' => has_capability('mod/data:viewanyrating',$context),
        'viewall' => has_capability('mod/data:viewallratings',$context),
        'rate'    => has_capability('mod/data:rate',$context)
    );
}


function data_rating_validate($params) {
    global $DB, $USER;

        if ($params['component'] != 'mod_data') {
        throw new rating_exception('invalidcomponent');
    }

        if ($params['ratingarea'] != 'entry') {
        throw new rating_exception('invalidratingarea');
    }

        if ($params['rateduserid'] == $USER->id) {
        throw new rating_exception('nopermissiontorate');
    }

    $datasql = "SELECT d.id as dataid, d.scale, d.course, r.userid as userid, d.approval, r.approved, r.timecreated, d.assesstimestart, d.assesstimefinish, r.groupid
                  FROM {data_records} r
                  JOIN {data} d ON r.dataid = d.id
                 WHERE r.id = :itemid";
    $dataparams = array('itemid'=>$params['itemid']);
    if (!$info = $DB->get_record_sql($datasql, $dataparams)) {
                throw new rating_exception('invaliditemid');
    }

    if ($info->scale != $params['scaleid']) {
                throw new rating_exception('invalidscaleid');
    }

    
        if ($params['rating'] < 0  && $params['rating'] != RATING_UNSET_RATING) {
        throw new rating_exception('invalidnum');
    }

        if ($info->scale < 0) {
                $scalerecord = $DB->get_record('scale', array('id' => -$info->scale));
        if ($scalerecord) {
            $scalearray = explode(',', $scalerecord->scale);
            if ($params['rating'] > count($scalearray)) {
                throw new rating_exception('invalidnum');
            }
        } else {
            throw new rating_exception('invalidscaleid');
        }
    } else if ($params['rating'] > $info->scale) {
                throw new rating_exception('invalidnum');
    }

    if ($info->approval && !$info->approved) {
                throw new rating_exception('nopermissiontorate');
    }

        if (!empty($info->assesstimestart) && !empty($info->assesstimefinish)) {
        if ($info->timecreated < $info->assesstimestart || $info->timecreated > $info->assesstimefinish) {
            throw new rating_exception('notavailable');
        }
    }

    $course = $DB->get_record('course', array('id'=>$info->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('data', $info->dataid, $course->id, false, MUST_EXIST);
    $context = context_module::instance($cm->id);

        if ($context->id != $params['context']->id) {
        throw new rating_exception('invalidcontext');
    }

        $groupid = $info->groupid;
    if ($groupid > 0 and $groupmode = groups_get_activity_groupmode($cm, $course)) {           if (!groups_group_exists($groupid)) {             throw new rating_exception('cannotfindgroup');        }

        if (!groups_is_member($groupid) and !has_capability('moodle/site:accessallgroups', $context)) {
                        throw new rating_exception('notmemberofgroup');
        }
    }

    return true;
}


function mod_data_rating_can_see_item_ratings($params) {
    global $DB;

        if (!isset($params['component']) || $params['component'] != 'mod_data') {
        throw new rating_exception('invalidcomponent');
    }

        if (!isset($params['ratingarea']) || $params['ratingarea'] != 'entry') {
        throw new rating_exception('invalidratingarea');
    }

    if (!isset($params['itemid'])) {
        throw new rating_exception('invaliditemid');
    }

    $datasql = "SELECT d.id as dataid, d.course, r.groupid
                  FROM {data_records} r
                  JOIN {data} d ON r.dataid = d.id
                 WHERE r.id = :itemid";
    $dataparams = array('itemid' => $params['itemid']);
    if (!$info = $DB->get_record_sql($datasql, $dataparams)) {
                throw new rating_exception('invaliditemid');
    }

        if ($info->groupid == 0) {
        return true;
    }

    $course = $DB->get_record('course', array('id' => $info->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('data', $info->dataid, $course->id, false, MUST_EXIST);

        return groups_group_visible($info->groupid, $course, $cm);
}



function data_print_preference_form($data, $perpage, $search, $sort='', $order='ASC', $search_array = '', $advanced = 0, $mode= ''){
    global $CFG, $DB, $PAGE, $OUTPUT;

    $cm = get_coursemodule_from_instance('data', $data->id);
    $context = context_module::instance($cm->id);
    echo '<br /><div class="datapreferences">';
    echo '<form id="options" action="view.php" method="get">';
    echo '<div>';
    echo '<input type="hidden" name="d" value="'.$data->id.'" />';
    if ($mode =='asearch') {
        $advanced = 1;
        echo '<input type="hidden" name="mode" value="list" />';
    }
    echo '<label for="pref_perpage">'.get_string('pagesize','data').'</label> ';
    $pagesizes = array(2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,15=>15,
                       20=>20,30=>30,40=>40,50=>50,100=>100,200=>200,300=>300,400=>400,500=>500,1000=>1000);
    echo html_writer::select($pagesizes, 'perpage', $perpage, false, array('id'=>'pref_perpage'));

    if ($advanced) {
        $regsearchclass = 'search_none';
        $advancedsearchclass = 'search_inline';
    } else {
        $regsearchclass = 'search_inline';
        $advancedsearchclass = 'search_none';
    }
    echo '<div id="reg_search" class="' . $regsearchclass . '" >&nbsp;&nbsp;&nbsp;';
    echo '<label for="pref_search">'.get_string('search').'</label> <input type="text" size="16" name="search" id= "pref_search" value="'.s($search).'" /></div>';
    echo '&nbsp;&nbsp;&nbsp;<label for="pref_sortby">'.get_string('sortby').'</label> ';
        echo '<select name="sort" id="pref_sortby">';
    if ($fields = $DB->get_records('data_fields', array('dataid'=>$data->id), 'name')) {
        echo '<optgroup label="'.get_string('fields', 'data').'">';
        foreach ($fields as $field) {
            if ($field->id == $sort) {
                echo '<option value="'.$field->id.'" selected="selected">'.$field->name.'</option>';
            } else {
                echo '<option value="'.$field->id.'">'.$field->name.'</option>';
            }
        }
        echo '</optgroup>';
    }
    $options = array();
    $options[DATA_TIMEADDED]    = get_string('timeadded', 'data');
    $options[DATA_TIMEMODIFIED] = get_string('timemodified', 'data');
    $options[DATA_FIRSTNAME]    = get_string('authorfirstname', 'data');
    $options[DATA_LASTNAME]     = get_string('authorlastname', 'data');
    if ($data->approval and has_capability('mod/data:approve', $context)) {
        $options[DATA_APPROVED] = get_string('approved', 'data');
    }
    echo '<optgroup label="'.get_string('other', 'data').'">';
    foreach ($options as $key => $name) {
        if ($key == $sort) {
            echo '<option value="'.$key.'" selected="selected">'.$name.'</option>';
        } else {
            echo '<option value="'.$key.'">'.$name.'</option>';
        }
    }
    echo '</optgroup>';
    echo '</select>';
    echo '<label for="pref_order" class="accesshide">'.get_string('order').'</label>';
    echo '<select id="pref_order" name="order">';
    if ($order == 'ASC') {
        echo '<option value="ASC" selected="selected">'.get_string('ascending','data').'</option>';
    } else {
        echo '<option value="ASC">'.get_string('ascending','data').'</option>';
    }
    if ($order == 'DESC') {
        echo '<option value="DESC" selected="selected">'.get_string('descending','data').'</option>';
    } else {
        echo '<option value="DESC">'.get_string('descending','data').'</option>';
    }
    echo '</select>';

    if ($advanced) {
        $checked = ' checked="checked" ';
    }
    else {
        $checked = '';
    }
    $PAGE->requires->js('/mod/data/data.js');
    echo '&nbsp;<input type="hidden" name="advanced" value="0" />';
    echo '&nbsp;<input type="hidden" name="filter" value="1" />';
    echo '&nbsp;<input type="checkbox" id="advancedcheckbox" name="advanced" value="1" '.$checked.' onchange="showHideAdvSearch(this.checked);" /><label for="advancedcheckbox">'.get_string('advancedsearch', 'data').'</label>';
    echo '&nbsp;<input type="submit" value="'.get_string('savesettings','data').'" />';

    echo '<br />';
    echo '<div class="' . $advancedsearchclass . '" id="data_adv_form">';
    echo '<table class="boxaligncenter">';

        echo '<tr><td colspan="2">&nbsp;</td></tr>';
    $i = 0;

            if(empty($data->asearchtemplate)) {
        data_generate_default_template($data, 'asearchtemplate');
    }

    static $fields = array();
    static $dataid = null;

    if (empty($dataid)) {
        $dataid = $data->id;
    } else if ($dataid != $data->id) {
        $fields = array();
    }

    if (empty($fields)) {
        $fieldrecords = $DB->get_records('data_fields', array('dataid'=>$data->id));
        foreach ($fieldrecords as $fieldrecord) {
            $fields[]= data_get_field($fieldrecord, $data);
        }
    }

        $patterns = array();
    $replacement = array();

        foreach ($fields as $field) {
        $fieldname = $field->field->name;
        $fieldname = preg_quote($fieldname, '/');
        $patterns[] = "/\[\[$fieldname\]\]/i";
        $searchfield = data_get_field_from_id($field->field->id, $data);
        if (!empty($search_array[$field->field->id]->data)) {
            $replacement[] = $searchfield->display_search_field($search_array[$field->field->id]->data);
        } else {
            $replacement[] = $searchfield->display_search_field();
        }
    }
    $fn = !empty($search_array[DATA_FIRSTNAME]->data) ? $search_array[DATA_FIRSTNAME]->data : '';
    $ln = !empty($search_array[DATA_LASTNAME]->data) ? $search_array[DATA_LASTNAME]->data : '';
    $patterns[]    = '/##firstname##/';
    $replacement[] = '<label class="accesshide" for="u_fn">'.get_string('authorfirstname', 'data').'</label><input type="text" size="16" id="u_fn" name="u_fn" value="'.s($fn).'" />';
    $patterns[]    = '/##lastname##/';
    $replacement[] = '<label class="accesshide" for="u_ln">'.get_string('authorlastname', 'data').'</label><input type="text" size="16" id="u_ln" name="u_ln" value="'.s($ln).'" />';

        $newtext = preg_replace($patterns, $replacement, $data->asearchtemplate);

    $options = new stdClass();
    $options->para=false;
    $options->noclean=true;
    echo '<tr><td>';
    echo format_text($newtext, FORMAT_HTML, $options);
    echo '</td></tr>';

    echo '<tr><td colspan="4"><br/><input type="submit" value="'.get_string('savesettings','data').'" /><input type="submit" name="resetadv" value="'.get_string('resetsettings','data').'" /></td></tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
}


function data_print_ratings($data, $record) {
    global $OUTPUT;
    if (!empty($record->rating)){
        echo $OUTPUT->render($record->rating);
    }
}


function data_get_view_actions() {
    return array('view');
}


function data_get_post_actions() {
    return array('add','update','record delete');
}


function data_fieldname_exists($name, $dataid, $fieldid = 0) {
    global $DB;

    if (!is_numeric($name)) {
        $like = $DB->sql_like('df.name', ':name', false);
    } else {
        $like = "df.name = :name";
    }
    $params = array('name'=>$name);
    if ($fieldid) {
        $params['dataid']   = $dataid;
        $params['fieldid1'] = $fieldid;
        $params['fieldid2'] = $fieldid;
        return $DB->record_exists_sql("SELECT * FROM {data_fields} df
                                        WHERE $like AND df.dataid = :dataid
                                              AND ((df.id < :fieldid1) OR (df.id > :fieldid2))", $params);
    } else {
        $params['dataid']   = $dataid;
        return $DB->record_exists_sql("SELECT * FROM {data_fields} df
                                        WHERE $like AND df.dataid = :dataid", $params);
    }
}


function data_convert_arrays_to_strings(&$fieldinput) {
    foreach ($fieldinput as $key => $val) {
        if (is_array($val)) {
            $str = '';
            foreach ($val as $inner) {
                $str .= $inner . ',';
            }
            $str = substr($str, 0, -1);

            $fieldinput->$key = $str;
        }
    }
}



function data_convert_to_roles($data, $teacherroles=array(), $studentroles=array(), $cmid=NULL) {
    global $CFG, $DB, $OUTPUT;

    if (!isset($data->participants) && !isset($data->assesspublic)
            && !isset($data->groupmode)) {
                                return false;
    }

    if (empty($cmid)) {
                if (!$cm = get_coursemodule_from_instance('data', $data->id)) {
            echo $OUTPUT->notification('Could not get the course module for the data');
            return false;
        } else {
            $cmid = $cm->id;
        }
    }
    $context = context_module::instance($cmid);


                switch ($data->participants) {
        case 1:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/data:writeentry', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/data:writeentry', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
        case 3:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/data:writeentry', CAP_ALLOW, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/data:writeentry', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
    }

                    switch ($data->assessed) {
        case 0:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/data:rate', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/data:rate', CAP_PREVENT, $teacherrole->id, $context->id);
            }
            break;
        case 1:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/data:rate', CAP_ALLOW, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/data:rate', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
        case 2:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/data:rate', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/data:rate', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
    }

                switch ($data->assesspublic) {
        case 0:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/data:viewrating', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/data:viewrating', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
        case 1:
            foreach ($studentroles as $studentrole) {
                assign_capability('mod/data:viewrating', CAP_ALLOW, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('mod/data:viewrating', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
    }

    if (empty($cm)) {
        $cm = $DB->get_record('course_modules', array('id'=>$cmid));
    }

    switch ($cm->groupmode) {
        case NOGROUPS:
            break;
        case SEPARATEGROUPS:
            foreach ($studentroles as $studentrole) {
                assign_capability('moodle/site:accessallgroups', CAP_PREVENT, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
        case VISIBLEGROUPS:
            foreach ($studentroles as $studentrole) {
                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $studentrole->id, $context->id);
            }
            foreach ($teacherroles as $teacherrole) {
                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $teacherrole->id, $context->id);
            }
            break;
    }
    return true;
}


function data_preset_name($shortname, $path) {

        $string = get_string('modulename', 'datapreset_'.$shortname);

    if (substr($string, 0, 1) == '[') {
        return $shortname;
    } else {
        return $string;
    }
}


function data_get_available_presets($context) {
    global $CFG, $USER;

    $presets = array();

        if ($dirs = core_component::get_plugin_list('datapreset')) {
        foreach ($dirs as $dir=>$fulldir) {
            if (is_directory_a_preset($fulldir)) {
                $preset = new stdClass();
                $preset->path = $fulldir;
                $preset->userid = 0;
                $preset->shortname = $dir;
                $preset->name = data_preset_name($dir, $fulldir);
                if (file_exists($fulldir.'/screenshot.jpg')) {
                    $preset->screenshot = $CFG->wwwroot.'/mod/data/preset/'.$dir.'/screenshot.jpg';
                } else if (file_exists($fulldir.'/screenshot.png')) {
                    $preset->screenshot = $CFG->wwwroot.'/mod/data/preset/'.$dir.'/screenshot.png';
                } else if (file_exists($fulldir.'/screenshot.gif')) {
                    $preset->screenshot = $CFG->wwwroot.'/mod/data/preset/'.$dir.'/screenshot.gif';
                }
                $presets[] = $preset;
            }
        }
    }
        $presets = data_get_available_site_presets($context, $presets);
    return $presets;
}


function data_get_available_site_presets($context, array $presets=array()) {
    global $USER;

    $fs = get_file_storage();
    $files = $fs->get_area_files(DATA_PRESET_CONTEXT, DATA_PRESET_COMPONENT, DATA_PRESET_FILEAREA);
    $canviewall = has_capability('mod/data:viewalluserpresets', $context);
    if (empty($files)) {
        return $presets;
    }
    foreach ($files as $file) {
        if (($file->is_directory() && $file->get_filepath()=='/') || !$file->is_directory() || (!$canviewall && $file->get_userid() != $USER->id)) {
            continue;
        }
        $preset = new stdClass;
        $preset->path = $file->get_filepath();
        $preset->name = trim($preset->path, '/');
        $preset->shortname = $preset->name;
        $preset->userid = $file->get_userid();
        $preset->id = $file->get_id();
        $preset->storedfile = $file;
        $presets[] = $preset;
    }
    return $presets;
}


function data_delete_site_preset($name) {
    $fs = get_file_storage();

    $files = $fs->get_directory_files(DATA_PRESET_CONTEXT, DATA_PRESET_COMPONENT, DATA_PRESET_FILEAREA, 0, '/'.$name.'/');
    if (!empty($files)) {
        foreach ($files as $file) {
            $file->delete();
        }
    }

    $dir = $fs->get_file(DATA_PRESET_CONTEXT, DATA_PRESET_COMPONENT, DATA_PRESET_FILEAREA, 0, '/'.$name.'/', '.');
    if (!empty($dir)) {
        $dir->delete();
    }
    return true;
}


function data_print_header($course, $cm, $data, $currenttab='') {

    global $CFG, $displaynoticegood, $displaynoticebad, $OUTPUT, $PAGE;

    $PAGE->set_title($data->name);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($data->name), 2);
    echo $OUTPUT->box(format_module_intro('data', $data, $cm->id), 'generalbox', 'intro');

        $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

    
    if ($currenttab) {
        include('tabs.php');
    }

    
    if (!empty($displaynoticegood)) {
        echo $OUTPUT->notification($displaynoticegood, 'notifysuccess');        } else if (!empty($displaynoticebad)) {
        echo $OUTPUT->notification($displaynoticebad);                         }
}


function data_user_can_add_entry($data, $currentgroup, $groupmode, $context = null) {
    global $USER;

    if (empty($context)) {
        $cm = get_coursemodule_from_instance('data', $data->id, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
    }

    if (has_capability('mod/data:manageentries', $context)) {
        
    } else if (!has_capability('mod/data:writeentry', $context)) {
        return false;

    } else if (data_atmaxentries($data)) {
        return false;
    } else if (data_in_readonly_period($data)) {
                return false;
    }

    if (!$groupmode or has_capability('moodle/site:accessallgroups', $context)) {
        return true;
    }

    if ($currentgroup) {
        return groups_is_member($currentgroup);
    } else {
                if ($groupmode == VISIBLEGROUPS){
            return true;
        } else {
            return false;
        }
    }
}


function data_user_can_manage_entry($record, $data, $context) {
    global $DB;

    if (has_capability('mod/data:manageentries', $context)) {
        return true;
    }

        $readonly = data_in_readonly_period($data);

    if (!$readonly) {
                        if (!is_object($record)) {
            if (!$record = $DB->get_record('data_records', array('id' => $record))) {
                return false;
            }
        }
        if (data_isowner($record)) {
            if ($data->approval && $record->approved) {
                return $data->manageapproved == 1;
            } else {
                return true;
            }
        }
    }

    return false;
}


function data_in_readonly_period($data) {
    $now = time();
    if (!$data->timeviewfrom && !$data->timeviewto) {
        return false;
    } else if (($data->timeviewfrom && $now < $data->timeviewfrom) || ($data->timeviewto && $now > $data->timeviewto)) {
        return false;
    }
    return true;
}


function is_directory_a_preset($directory) {
    $directory = rtrim($directory, '/\\') . '/';
    $status = file_exists($directory.'singletemplate.html') &&
              file_exists($directory.'listtemplate.html') &&
              file_exists($directory.'listtemplateheader.html') &&
              file_exists($directory.'listtemplatefooter.html') &&
              file_exists($directory.'addtemplate.html') &&
              file_exists($directory.'rsstemplate.html') &&
              file_exists($directory.'rsstitletemplate.html') &&
              file_exists($directory.'csstemplate.css') &&
              file_exists($directory.'jstemplate.js') &&
              file_exists($directory.'preset.xml');

    return $status;
}


abstract class data_preset_importer {

    protected $course;
    protected $cm;
    protected $module;
    protected $directory;

    
    public function __construct($course, $cm, $module, $directory) {
        $this->course = $course;
        $this->cm = $cm;
        $this->module = $module;
        $this->directory = $directory;
    }

    
    public function get_directory() {
        return basename($this->directory);
    }

    
    public function data_preset_get_file_contents(&$filestorage, &$fileobj, $dir, $filename) {
        if(empty($filestorage) || empty($fileobj)) {
            if (substr($dir, -1)!='/') {
                $dir .= '/';
            }
            if (file_exists($dir.$filename)) {
                return file_get_contents($dir.$filename);
            } else {
                return null;
            }
        } else {
            if ($filestorage->file_exists(DATA_PRESET_CONTEXT, DATA_PRESET_COMPONENT, DATA_PRESET_FILEAREA, 0, $fileobj->get_filepath(), $filename)) {
                $file = $filestorage->get_file(DATA_PRESET_CONTEXT, DATA_PRESET_COMPONENT, DATA_PRESET_FILEAREA, 0, $fileobj->get_filepath(), $filename);
                return $file->get_content();
            } else {
                return null;
            }
        }

    }
    
    public function get_preset_settings() {
        global $DB;

        $fs = $fileobj = null;
        if (!is_directory_a_preset($this->directory)) {
            
            $fs = get_file_storage();
            $files = $fs->get_area_files(DATA_PRESET_CONTEXT, DATA_PRESET_COMPONENT, DATA_PRESET_FILEAREA);

                        $explodeddirectory = explode('/', $this->directory);
            $presettofind = end($explodeddirectory);

                        foreach ($files as $file) {
                if (($file->is_directory() && $file->get_filepath()=='/') || !$file->is_directory()) {
                    continue;
                }
                $presetname = trim($file->get_filepath(), '/');
                if ($presetname==$presettofind) {
                    $this->directory = $presetname;
                    $fileobj = $file;
                }
            }

            if (empty($fileobj)) {
                print_error('invalidpreset', 'data', '', $this->directory);
            }
        }

        $allowed_settings = array(
            'intro',
            'comments',
            'requiredentries',
            'requiredentriestoview',
            'maxentries',
            'rssarticles',
            'approval',
            'defaultsortdir',
            'defaultsort');

        $result = new stdClass;
        $result->settings = new stdClass;
        $result->importfields = array();
        $result->currentfields = $DB->get_records('data_fields', array('dataid'=>$this->module->id));
        if (!$result->currentfields) {
            $result->currentfields = array();
        }


        
        $presetxml = $this->data_preset_get_file_contents($fs, $fileobj, $this->directory,'preset.xml');
        $parsedxml = xmlize($presetxml, 0);

        
        $settingsarray = $parsedxml['preset']['#']['settings'][0]['#'];
        $result->settings = new StdClass();
        foreach ($settingsarray as $setting => $value) {
            if (!is_array($value) || !in_array($setting, $allowed_settings)) {
                                continue;
            }
            $result->settings->$setting = $value[0]['#'];
        }

        
        $fieldsarray = $parsedxml['preset']['#']['field'];
        foreach ($fieldsarray as $field) {
            if (!is_array($field)) {
                continue;
            }
            $f = new StdClass();
            foreach ($field['#'] as $param => $value) {
                if (!is_array($value)) {
                    continue;
                }
                $f->$param = $value[0]['#'];
            }
            $f->dataid = $this->module->id;
            $f->type = clean_param($f->type, PARAM_ALPHA);
            $result->importfields[] = $f;
        }
        
        $result->settings->singletemplate     = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"singletemplate.html");
        $result->settings->listtemplate       = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"listtemplate.html");
        $result->settings->listtemplateheader = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"listtemplateheader.html");
        $result->settings->listtemplatefooter = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"listtemplatefooter.html");
        $result->settings->addtemplate        = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"addtemplate.html");
        $result->settings->rsstemplate        = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"rsstemplate.html");
        $result->settings->rsstitletemplate   = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"rsstitletemplate.html");
        $result->settings->csstemplate        = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"csstemplate.css");
        $result->settings->jstemplate         = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"jstemplate.js");
        $result->settings->asearchtemplate    = $this->data_preset_get_file_contents($fs, $fileobj,$this->directory,"asearchtemplate.html");

        $result->settings->instance = $this->module->id;
        return $result;
    }

    
    function import($overwritesettings) {
        global $DB, $CFG;

        $params = $this->get_preset_settings();
        $settings = $params->settings;
        $newfields = $params->importfields;
        $currentfields = $params->currentfields;
        $preservedfields = array();

        
        if (!empty($newfields)) {
            
            foreach ($newfields as $nid => $newfield) {
                $cid = optional_param("field_$nid", -1, PARAM_INT);
                if ($cid == -1) {
                    continue;
                }
                if (array_key_exists($cid, $preservedfields)){
                    print_error('notinjectivemap', 'data');
                }
                else $preservedfields[$cid] = true;
            }

            foreach ($newfields as $nid => $newfield) {
                $cid = optional_param("field_$nid", -1, PARAM_INT);

                
                if ($cid != -1 and isset($currentfields[$cid])) {
                    $fieldobject = data_get_field_from_id($currentfields[$cid]->id, $this->module);
                    foreach ($newfield as $param => $value) {
                        if ($param != "id") {
                            $fieldobject->field->$param = $value;
                        }
                    }
                    unset($fieldobject->field->similarfield);
                    $fieldobject->update_field();
                    unset($fieldobject);
                } else {
                    
                    include_once("field/$newfield->type/field.class.php");

                    if (!isset($newfield->description)) {
                        $newfield->description = '';
                    }
                    $classname = 'data_field_'.$newfield->type;
                    $fieldclass = new $classname($newfield, $this->module);
                    $fieldclass->insert_field();
                    unset($fieldclass);
                }
            }
        }

        
        if (!empty($preservedfields)) {
            foreach ($currentfields as $cid => $currentfield) {
                if (!array_key_exists($cid, $preservedfields)) {
                    
                    print "Deleting field $currentfield->name<br />";

                    $id = $currentfield->id;
                                        $DB->delete_records('data_content', array('fieldid'=>$id));
                    $DB->delete_records('data_fields', array('id'=>$id));
                }
            }
        }

                if (!empty($settings->defaultsort)) {
            if (is_numeric($settings->defaultsort)) {
                                $settings->defaultsort = 0;
            } else {
                $settings->defaultsort = (int)$DB->get_field('data_fields', 'id', array('dataid'=>$this->module->id, 'name'=>$settings->defaultsort));
            }
        } else {
            $settings->defaultsort = 0;
        }

                if ($overwritesettings) {
                        $overwrite = array_keys((array)$settings);
        } else {
                        $overwrite = array('singletemplate', 'listtemplate', 'listtemplateheader', 'listtemplatefooter',
                               'addtemplate', 'rsstemplate', 'rsstitletemplate', 'csstemplate', 'jstemplate',
                               'asearchtemplate', 'defaultsortdir', 'defaultsort');
        }

                foreach ($this->module as $prop=>$unused) {
            if (in_array($prop, $overwrite)) {
                $this->module->$prop = $settings->$prop;
            }
        }

        data_update_instance($this->module);

        return $this->cleanup();
    }

    
    public function cleanup() {
        return true;
    }
}


class data_preset_upload_importer extends data_preset_importer {
    public function __construct($course, $cm, $module, $filepath) {
        global $USER;
        if (is_file($filepath)) {
            $fp = get_file_packer();
            if ($fp->extract_to_pathname($filepath, $filepath.'_extracted')) {
                fulldelete($filepath);
            }
            $filepath .= '_extracted';
        }
        parent::__construct($course, $cm, $module, $filepath);
    }
    public function cleanup() {
        return fulldelete($this->directory);
    }
}


class data_preset_existing_importer extends data_preset_importer {
    protected $userid;
    public function __construct($course, $cm, $module, $fullname) {
        global $USER;
        list($userid, $shortname) = explode('/', $fullname, 2);
        $context = context_module::instance($cm->id);
        if ($userid && ($userid != $USER->id) && !has_capability('mod/data:manageuserpresets', $context) && !has_capability('mod/data:viewalluserpresets', $context)) {
           throw new coding_exception('Invalid preset provided');
        }

        $this->userid = $userid;
        $filepath = data_preset_path($course, $userid, $shortname);
        parent::__construct($course, $cm, $module, $filepath);
    }
    public function get_userid() {
        return $this->userid;
    }
}


function data_preset_path($course, $userid, $shortname) {
    global $USER, $CFG;

    $context = context_course::instance($course->id);

    $userid = (int)$userid;

    $path = null;
    if ($userid > 0 && ($userid == $USER->id || has_capability('mod/data:viewalluserpresets', $context))) {
        $path = $CFG->dataroot.'/data/preset/'.$userid.'/'.$shortname;
    } else if ($userid == 0) {
        $path = $CFG->dirroot.'/mod/data/preset/'.$shortname;
    } else if ($userid < 0) {
        $path = $CFG->tempdir.'/data/'.-$userid.'/'.$shortname;
    }

    return $path;
}


function data_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'dataheader', get_string('modulenameplural', 'data'));
    $mform->addElement('checkbox', 'reset_data', get_string('deleteallentries','data'));

    $mform->addElement('checkbox', 'reset_data_notenrolled', get_string('deletenotenrolled', 'data'));
    $mform->disabledIf('reset_data_notenrolled', 'reset_data', 'checked');

    $mform->addElement('checkbox', 'reset_data_ratings', get_string('deleteallratings'));
    $mform->disabledIf('reset_data_ratings', 'reset_data', 'checked');

    $mform->addElement('checkbox', 'reset_data_comments', get_string('deleteallcomments'));
    $mform->disabledIf('reset_data_comments', 'reset_data', 'checked');
}


function data_reset_course_form_defaults($course) {
    return array('reset_data'=>0, 'reset_data_ratings'=>1, 'reset_data_comments'=>1, 'reset_data_notenrolled'=>0);
}


function data_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $sql = "SELECT d.*, cm.idnumber as cmidnumber, d.course as courseid
              FROM {data} d, {course_modules} cm, {modules} m
             WHERE m.name='data' AND m.id=cm.module AND cm.instance=d.id AND d.course=?";

    if ($datas = $DB->get_records_sql($sql, array($courseid))) {
        foreach ($datas as $data) {
            data_grade_item_update($data, 'reset');
        }
    }
}


function data_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->dirroot.'/rating/lib.php');

    $componentstr = get_string('modulenameplural', 'data');
    $status = array();

    $allrecordssql = "SELECT r.id
                        FROM {data_records} r
                             INNER JOIN {data} d ON r.dataid = d.id
                       WHERE d.course = ?";

    $alldatassql = "SELECT d.id
                      FROM {data} d
                     WHERE d.course=?";

    $rm = new rating_manager();
    $ratingdeloptions = new stdClass;
    $ratingdeloptions->component = 'mod_data';
    $ratingdeloptions->ratingarea = 'entry';

        $fs = get_file_storage();

        if (!empty($data->reset_data)) {
        $DB->delete_records_select('comments', "itemid IN ($allrecordssql) AND commentarea='database_entry'", array($data->courseid));
        $DB->delete_records_select('data_content', "recordid IN ($allrecordssql)", array($data->courseid));
        $DB->delete_records_select('data_records', "dataid IN ($alldatassql)", array($data->courseid));

        if ($datas = $DB->get_records_sql($alldatassql, array($data->courseid))) {
            foreach ($datas as $dataid=>$unused) {
                if (!$cm = get_coursemodule_from_instance('data', $dataid)) {
                    continue;
                }
                $datacontext = context_module::instance($cm->id);

                                $fs->delete_area_files($datacontext->id, 'mod_data', 'content');

                $ratingdeloptions->contextid = $datacontext->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

        if (empty($data->reset_gradebook_grades)) {
                        data_reset_gradebook($data->courseid);
        }
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallentries', 'data'), 'error'=>false);
    }

        if (!empty($data->reset_data_notenrolled)) {
        $recordssql = "SELECT r.id, r.userid, r.dataid, u.id AS userexists, u.deleted AS userdeleted
                         FROM {data_records} r
                              JOIN {data} d ON r.dataid = d.id
                              LEFT JOIN {user} u ON r.userid = u.id
                        WHERE d.course = ? AND r.userid > 0";

        $course_context = context_course::instance($data->courseid);
        $notenrolled = array();
        $fields = array();
        $rs = $DB->get_recordset_sql($recordssql, array($data->courseid));
        foreach ($rs as $record) {
            if (array_key_exists($record->userid, $notenrolled) or !$record->userexists or $record->userdeleted
              or !is_enrolled($course_context, $record->userid)) {
                                if (!$cm = get_coursemodule_from_instance('data', $record->dataid)) {
                    continue;
                }
                $datacontext = context_module::instance($cm->id);
                $ratingdeloptions->contextid = $datacontext->id;
                $ratingdeloptions->itemid = $record->id;
                $rm->delete_ratings($ratingdeloptions);

                                if ($contents = $DB->get_records('data_content', array('recordid' => $record->id), '', 'id')) {
                    foreach ($contents as $content) {
                        $fs->delete_area_files($datacontext->id, 'mod_data', 'content', $content->id);
                    }
                }
                $notenrolled[$record->userid] = true;

                $DB->delete_records('comments', array('itemid' => $record->id, 'commentarea' => 'database_entry'));
                $DB->delete_records('data_content', array('recordid' => $record->id));
                $DB->delete_records('data_records', array('id' => $record->id));
            }
        }
        $rs->close();
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deletenotenrolled', 'data'), 'error'=>false);
    }

        if (!empty($data->reset_data_ratings)) {
        if ($datas = $DB->get_records_sql($alldatassql, array($data->courseid))) {
            foreach ($datas as $dataid=>$unused) {
                if (!$cm = get_coursemodule_from_instance('data', $dataid)) {
                    continue;
                }
                $datacontext = context_module::instance($cm->id);

                $ratingdeloptions->contextid = $datacontext->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

        if (empty($data->reset_gradebook_grades)) {
                        data_reset_gradebook($data->courseid);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallratings'), 'error'=>false);
    }

        if (!empty($data->reset_data_comments)) {
        $DB->delete_records_select('comments', "itemid IN ($allrecordssql) AND commentarea='database_entry'", array($data->courseid));
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallcomments'), 'error'=>false);
    }

        if ($data->timeshift) {
        shift_course_mod_dates('data', array('timeavailablefrom', 'timeavailableto', 'timeviewfrom', 'timeviewto'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}


function data_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames', 'moodle/rating:view', 'moodle/rating:viewany', 'moodle/rating:viewall', 'moodle/rating:rate', 'moodle/comment:view', 'moodle/comment:post', 'moodle/comment:delete');
}


function data_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_RATE:                    return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

function data_export_csv($export, $delimiter_name, $database, $count, $return=false) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');

    $filename = $database . '-' . $count . '-record';
    if ($count > 1) {
        $filename .= 's';
    }
    if ($return) {
        return csv_export_writer::print_array($export, $delimiter_name, '"', true);
    } else {
        csv_export_writer::download_array($filename, $export, $delimiter_name);
    }
}


function data_export_xls($export, $dataname, $count) {
    global $CFG;
    require_once("$CFG->libdir/excellib.class.php");
    $filename = clean_filename("{$dataname}-{$count}_record");
    if ($count > 1) {
        $filename .= 's';
    }
    $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
    $filename .= '.xls';

    $filearg = '-';
    $workbook = new MoodleExcelWorkbook($filearg);
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] = $workbook->add_worksheet('');
    $rowno = 0;
    foreach ($export as $row) {
        $colno = 0;
        foreach($row as $col) {
            $worksheet[0]->write($rowno, $colno, $col);
            $colno++;
        }
        $rowno++;
    }
    $workbook->close();
    return $filename;
}


function data_export_ods($export, $dataname, $count) {
    global $CFG;
    require_once("$CFG->libdir/odslib.class.php");
    $filename = clean_filename("{$dataname}-{$count}_record");
    if ($count > 1) {
        $filename .= 's';
    }
    $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
    $filename .= '.ods';
    $filearg = '-';
    $workbook = new MoodleODSWorkbook($filearg);
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] = $workbook->add_worksheet('');
    $rowno = 0;
    foreach ($export as $row) {
        $colno = 0;
        foreach($row as $col) {
            $worksheet[0]->write($rowno, $colno, $col);
            $colno++;
        }
        $rowno++;
    }
    $workbook->close();
    return $filename;
}


function data_get_exportdata($dataid, $fields, $selectedfields, $currentgroup=0, $context=null,
                             $userdetails=false, $time=false, $approval=false) {
    global $DB;

    if (is_null($context)) {
        $context = context_system::instance();
    }
        $userdetails = $userdetails && has_capability('mod/data:exportuserinfo', $context);

    $exportdata = array();

        foreach($fields as $key => $field) {
        if (!in_array($field->field->id, $selectedfields)) {
                        unset($fields[$key]);
        } else {
            $exportdata[0][] = $field->field->name;
        }
    }
    if ($userdetails) {
        $exportdata[0][] = get_string('user');
        $exportdata[0][] = get_string('username');
        $exportdata[0][] = get_string('email');
    }
    if ($time) {
        $exportdata[0][] = get_string('timeadded', 'data');
        $exportdata[0][] = get_string('timemodified', 'data');
    }
    if ($approval) {
        $exportdata[0][] = get_string('approved', 'data');
    }

    $datarecords = $DB->get_records('data_records', array('dataid'=>$dataid));
    ksort($datarecords);
    $line = 1;
    foreach($datarecords as $record) {
                if ($currentgroup) {
            $select = 'SELECT c.fieldid, c.content, c.content1, c.content2, c.content3, c.content4 FROM {data_content} c, {data_records} r WHERE c.recordid = ? AND r.id = c.recordid AND r.groupid = ?';
            $where = array($record->id, $currentgroup);
        } else {
            $select = 'SELECT fieldid, content, content1, content2, content3, content4 FROM {data_content} WHERE recordid = ?';
            $where = array($record->id);
        }

        if( $content = $DB->get_records_sql($select, $where) ) {
            foreach($fields as $field) {
                $contents = '';
                if(isset($content[$field->field->id])) {
                    $contents = $field->export_text_value($content[$field->field->id]);
                }
                $exportdata[$line][] = $contents;
            }
            if ($userdetails) {                 $userdata = get_complete_user_data('id', $record->userid);
                $exportdata[$line][] = fullname($userdata);
                $exportdata[$line][] = $userdata->username;
                $exportdata[$line][] = $userdata->email;
            }
            if ($time) {                 $exportdata[$line][] = userdate($record->timecreated);
                $exportdata[$line][] = userdate($record->timemodified);
            }
            if ($approval) {                 $exportdata[$line][] = (int) $record->approved;
            }
        }
        $line++;
    }
    $line--;
    return $exportdata;
}



function data_get_file_areas($course, $cm, $context) {
    return array('content' => get_string('areacontent', 'mod_data'));
}


function data_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    if (!isset($areas[$filearea])) {
        return null;
    }

    if (is_null($itemid)) {
        require_once($CFG->dirroot.'/mod/data/locallib.php');
        return new data_file_info_container($browser, $course, $cm, $context, $areas, $filearea);
    }

    if (!$content = $DB->get_record('data_content', array('id'=>$itemid))) {
        return null;
    }

    if (!$field = $DB->get_record('data_fields', array('id'=>$content->fieldid))) {
        return null;
    }

    if (!$record = $DB->get_record('data_records', array('id'=>$content->recordid))) {
        return null;
    }

    if (!$data = $DB->get_record('data', array('id'=>$field->dataid))) {
        return null;
    }

        if ($data->approval and !$record->approved and !data_isowner($record) and !has_capability('mod/data:approve', $context)) {
        return null;
    }

        if ($record->groupid) {
        $groupmode = groups_get_activity_groupmode($cm, $course);
        if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
            if (!groups_is_member($record->groupid)) {
                return null;
            }
        }
    }

    $fieldobj = data_get_field($field, $data, $cm);

    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!$fieldobj->file_ok($filepath.$filename)) {
        return null;
    }

    $fs = get_file_storage();
    if (!($storedfile = $fs->get_file($context->id, 'mod_data', $filearea, $itemid, $filepath, $filename))) {
        return null;
    }

            if (!has_capability('moodle/course:managefiles', $context) && $storedfile->get_userid() != $USER->id) {
        return null;
    }

    $urlbase = $CFG->wwwroot.'/pluginfile.php';

    return new file_info_stored($browser, $context, $storedfile, $urlbase, $itemid, true, true, false, false);
}


function data_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea === 'content') {
        $contentid = (int)array_shift($args);

        if (!$content = $DB->get_record('data_content', array('id'=>$contentid))) {
            return false;
        }

        if (!$field = $DB->get_record('data_fields', array('id'=>$content->fieldid))) {
            return false;
        }

        if (!$record = $DB->get_record('data_records', array('id'=>$content->recordid))) {
            return false;
        }

        if (!$data = $DB->get_record('data', array('id'=>$field->dataid))) {
            return false;
        }

        if ($data->id != $cm->instance) {
                        return false;
        }

                if ($data->approval and !$record->approved and !data_isowner($record) and !has_capability('mod/data:approve', $context)) {
            return false;
        }

                if ($record->groupid) {
            $groupmode = groups_get_activity_groupmode($cm, $course);
            if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                if (!groups_is_member($record->groupid)) {
                    return false;
                }
            }
        }

        $fieldobj = data_get_field($field, $data, $cm);

        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_data/content/$content->id/$relativepath";

        if (!$fieldobj->file_ok($relativepath)) {
            return false;
        }

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

                send_stored_file($file, 0, 0, true, $options);     }

    return false;
}


function data_extend_navigation($navigation, $course, $module, $cm) {
    global $CFG, $OUTPUT, $USER, $DB;

    $rid = optional_param('rid', 0, PARAM_INT);

    $data = $DB->get_record('data', array('id'=>$cm->instance));
    $currentgroup = groups_get_activity_group($cm);
    $groupmode = groups_get_activity_groupmode($cm);

     $numentries = data_numentries($data);
        if ($data->requiredentries > 0 && $numentries < $data->requiredentries && !has_capability('mod/data:manageentries', context_module::instance($cm->id))) {
        $data->entriesleft = $data->requiredentries - $numentries;
        $entriesnode = $navigation->add(get_string('entrieslefttoadd', 'data', $data));
        $entriesnode->add_class('note');
    }

    $navigation->add(get_string('list', 'data'), new moodle_url('/mod/data/view.php', array('d'=>$cm->instance)));
    if (!empty($rid)) {
        $navigation->add(get_string('single', 'data'), new moodle_url('/mod/data/view.php', array('d'=>$cm->instance, 'rid'=>$rid)));
    } else {
        $navigation->add(get_string('single', 'data'), new moodle_url('/mod/data/view.php', array('d'=>$cm->instance, 'mode'=>'single')));
    }
    $navigation->add(get_string('search', 'data'), new moodle_url('/mod/data/view.php', array('d'=>$cm->instance, 'mode'=>'asearch')));
}


function data_extend_settings_navigation(settings_navigation $settings, navigation_node $datanode) {
    global $PAGE, $DB, $CFG, $USER;

    $data = $DB->get_record('data', array("id" => $PAGE->cm->instance));

    $currentgroup = groups_get_activity_group($PAGE->cm);
    $groupmode = groups_get_activity_groupmode($PAGE->cm);

    if (data_user_can_add_entry($data, $currentgroup, $groupmode, $PAGE->cm->context)) {         if (empty($editentry)) {             $addstring = get_string('add', 'data');
        } else {
            $addstring = get_string('editentry', 'data');
        }
        $datanode->add($addstring, new moodle_url('/mod/data/edit.php', array('d'=>$PAGE->cm->instance)));
    }

    if (has_capability(DATA_CAP_EXPORT, $PAGE->cm->context)) {
                        $datanode->add(get_string('exportentries', 'data'), new moodle_url('/mod/data/export.php', array('d'=>$data->id)));
    }
    if (has_capability('mod/data:manageentries', $PAGE->cm->context)) {
        $datanode->add(get_string('importentries', 'data'), new moodle_url('/mod/data/import.php', array('d'=>$data->id)));
    }

    if (has_capability('mod/data:managetemplates', $PAGE->cm->context)) {
        $currenttab = '';
        if ($currenttab == 'list') {
            $defaultemplate = 'listtemplate';
        } else if ($currenttab == 'add') {
            $defaultemplate = 'addtemplate';
        } else if ($currenttab == 'asearch') {
            $defaultemplate = 'asearchtemplate';
        } else {
            $defaultemplate = 'singletemplate';
        }

        $templates = $datanode->add(get_string('templates', 'data'));

        $templatelist = array ('listtemplate', 'singletemplate', 'asearchtemplate', 'addtemplate', 'rsstemplate', 'csstemplate', 'jstemplate');
        foreach ($templatelist as $template) {
            $templates->add(get_string($template, 'data'), new moodle_url('/mod/data/templates.php', array('d'=>$data->id,'mode'=>$template)));
        }

        $datanode->add(get_string('fields', 'data'), new moodle_url('/mod/data/field.php', array('d'=>$data->id)));
        $datanode->add(get_string('presets', 'data'), new moodle_url('/mod/data/preset.php', array('d'=>$data->id)));
    }

    if (!empty($CFG->enablerssfeeds) && !empty($CFG->data_enablerssfeeds) && $data->rssarticles > 0) {
        require_once("$CFG->libdir/rsslib.php");

        $string = get_string('rsstype','forum');

        $url = new moodle_url(rss_get_url($PAGE->cm->context->id, $USER->id, 'mod_data', $data->id));
        $datanode->add($string, $url, settings_navigation::TYPE_SETTING, null, null, new pix_icon('i/rss', ''));
    }
}


function data_presets_save($course, $cm, $data, $path) {
    global $USER;
    $fs = get_file_storage();
    $filerecord = new stdClass;
    $filerecord->contextid = DATA_PRESET_CONTEXT;
    $filerecord->component = DATA_PRESET_COMPONENT;
    $filerecord->filearea = DATA_PRESET_FILEAREA;
    $filerecord->itemid = 0;
    $filerecord->filepath = '/'.$path.'/';
    $filerecord->userid = $USER->id;

    $filerecord->filename = 'preset.xml';
    $fs->create_file_from_string($filerecord, data_presets_generate_xml($course, $cm, $data));

    $filerecord->filename = 'singletemplate.html';
    $fs->create_file_from_string($filerecord, $data->singletemplate);

    $filerecord->filename = 'listtemplateheader.html';
    $fs->create_file_from_string($filerecord, $data->listtemplateheader);

    $filerecord->filename = 'listtemplate.html';
    $fs->create_file_from_string($filerecord, $data->listtemplate);

    $filerecord->filename = 'listtemplatefooter.html';
    $fs->create_file_from_string($filerecord, $data->listtemplatefooter);

    $filerecord->filename = 'addtemplate.html';
    $fs->create_file_from_string($filerecord, $data->addtemplate);

    $filerecord->filename = 'rsstemplate.html';
    $fs->create_file_from_string($filerecord, $data->rsstemplate);

    $filerecord->filename = 'rsstitletemplate.html';
    $fs->create_file_from_string($filerecord, $data->rsstitletemplate);

    $filerecord->filename = 'csstemplate.css';
    $fs->create_file_from_string($filerecord, $data->csstemplate);

    $filerecord->filename = 'jstemplate.js';
    $fs->create_file_from_string($filerecord, $data->jstemplate);

    $filerecord->filename = 'asearchtemplate.html';
    $fs->create_file_from_string($filerecord, $data->asearchtemplate);

    return true;
}


function data_presets_generate_xml($course, $cm, $data) {
    global $DB;

        $presetxmldata = "<preset>\n\n";

        $raw_settings = array(
        'intro',
        'comments',
        'requiredentries',
        'requiredentriestoview',
        'maxentries',
        'rssarticles',
        'approval',
        'manageapproved',
        'defaultsortdir'
    );

    $presetxmldata .= "<settings>\n";
        foreach ($raw_settings as $setting) {
        $presetxmldata .= "<$setting>" . htmlspecialchars($data->$setting) . "</$setting>\n";
    }

        if ($data->defaultsort > 0 && $sortfield = data_get_field_from_id($data->defaultsort, $data)) {
        $presetxmldata .= '<defaultsort>' . htmlspecialchars($sortfield->field->name) . "</defaultsort>\n";
    } else {
        $presetxmldata .= "<defaultsort>0</defaultsort>\n";
    }
    $presetxmldata .= "</settings>\n\n";
        $fields = $DB->get_records('data_fields', array('dataid'=>$data->id));
    ksort($fields);
    if (!empty($fields)) {
        foreach ($fields as $field) {
            $presetxmldata .= "<field>\n";
            foreach ($field as $key => $value) {
                if ($value != '' && $key != 'id' && $key != 'dataid') {
                    $presetxmldata .= "<$key>" . htmlspecialchars($value) . "</$key>\n";
                }
            }
            $presetxmldata .= "</field>\n\n";
        }
    }
    $presetxmldata .= '</preset>';
    return $presetxmldata;
}

function data_presets_export($course, $cm, $data, $tostorage=false) {
    global $CFG, $DB;

    $presetname = clean_filename($data->name) . '-preset-' . gmdate("Ymd_Hi");
    $exportsubdir = "mod_data/presetexport/$presetname";
    make_temp_directory($exportsubdir);
    $exportdir = "$CFG->tempdir/$exportsubdir";

        $presetxmldata = data_presets_generate_xml($course, $cm, $data);

        $presetxmlfile = fopen($exportdir . '/preset.xml', 'w');
    fwrite($presetxmlfile, $presetxmldata);
    fclose($presetxmlfile);

        $singletemplate = fopen($exportdir . '/singletemplate.html', 'w');
    fwrite($singletemplate, $data->singletemplate);
    fclose($singletemplate);

    $listtemplateheader = fopen($exportdir . '/listtemplateheader.html', 'w');
    fwrite($listtemplateheader, $data->listtemplateheader);
    fclose($listtemplateheader);

    $listtemplate = fopen($exportdir . '/listtemplate.html', 'w');
    fwrite($listtemplate, $data->listtemplate);
    fclose($listtemplate);

    $listtemplatefooter = fopen($exportdir . '/listtemplatefooter.html', 'w');
    fwrite($listtemplatefooter, $data->listtemplatefooter);
    fclose($listtemplatefooter);

    $addtemplate = fopen($exportdir . '/addtemplate.html', 'w');
    fwrite($addtemplate, $data->addtemplate);
    fclose($addtemplate);

    $rsstemplate = fopen($exportdir . '/rsstemplate.html', 'w');
    fwrite($rsstemplate, $data->rsstemplate);
    fclose($rsstemplate);

    $rsstitletemplate = fopen($exportdir . '/rsstitletemplate.html', 'w');
    fwrite($rsstitletemplate, $data->rsstitletemplate);
    fclose($rsstitletemplate);

    $csstemplate = fopen($exportdir . '/csstemplate.css', 'w');
    fwrite($csstemplate, $data->csstemplate);
    fclose($csstemplate);

    $jstemplate = fopen($exportdir . '/jstemplate.js', 'w');
    fwrite($jstemplate, $data->jstemplate);
    fclose($jstemplate);

    $asearchtemplate = fopen($exportdir . '/asearchtemplate.html', 'w');
    fwrite($asearchtemplate, $data->asearchtemplate);
    fclose($asearchtemplate);

        if (! is_directory_a_preset($exportdir)) {
        print_error('generateerror', 'data');
    }

    $filenames = array(
        'preset.xml',
        'singletemplate.html',
        'listtemplateheader.html',
        'listtemplate.html',
        'listtemplatefooter.html',
        'addtemplate.html',
        'rsstemplate.html',
        'rsstitletemplate.html',
        'csstemplate.css',
        'jstemplate.js',
        'asearchtemplate.html'
    );

    $filelist = array();
    foreach ($filenames as $filename) {
        $filelist[$filename] = $exportdir . '/' . $filename;
    }

    $exportfile = $exportdir.'.zip';
    file_exists($exportfile) && unlink($exportfile);

    $fp = get_file_packer('application/zip');
    $fp->archive_to_pathname($filelist, $exportfile);

    foreach ($filelist as $file) {
        unlink($file);
    }
    rmdir($exportdir);

        return $exportfile;
}


function data_comment_permissions($comment_param) {
    global $CFG, $DB;
    if (!$record = $DB->get_record('data_records', array('id'=>$comment_param->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    if (!$data = $DB->get_record('data', array('id'=>$record->dataid))) {
        throw new comment_exception('invalidid', 'data');
    }
    if ($data->comments) {
        return array('post'=>true, 'view'=>true);
    } else {
        return array('post'=>false, 'view'=>false);
    }
}


function data_comment_validate($comment_param) {
    global $DB;
        if ($comment_param->commentarea != 'database_entry') {
        throw new comment_exception('invalidcommentarea');
    }
        if (!$record = $DB->get_record('data_records', array('id'=>$comment_param->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    if (!$data = $DB->get_record('data', array('id'=>$record->dataid))) {
        throw new comment_exception('invalidid', 'data');
    }
    if (!$course = $DB->get_record('course', array('id'=>$data->course))) {
        throw new comment_exception('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('data', $data->id, $course->id)) {
        throw new comment_exception('invalidcoursemodule');
    }
    if (!$data->comments) {
        throw new comment_exception('commentsoff', 'data');
    }
    $context = context_module::instance($cm->id);

        if ($data->approval and !$record->approved and !data_isowner($record) and !has_capability('mod/data:approve', $context)) {
        throw new comment_exception('notapproved', 'data');
    }

        if ($record->groupid) {
        $groupmode = groups_get_activity_groupmode($cm, $course);
        if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
            if (!groups_is_member($record->groupid)) {
                throw new comment_exception('notmemberofgroup');
            }
        }
    }
        if ($context->id != $comment_param->context->id) {
        throw new comment_exception('invalidcontext');
    }
        if (!empty($comment_param->commentid)) {
        if ($comment = $DB->get_record('comments', array('id'=>$comment_param->commentid))) {
            if ($comment->commentarea != 'database_entry') {
                throw new comment_exception('invalidcommentarea');
            }
            if ($comment->contextid != $comment_param->context->id) {
                throw new comment_exception('invalidcontext');
            }
            if ($comment->itemid != $comment_param->itemid) {
                throw new comment_exception('invalidcommentitemid');
            }
        } else {
            throw new comment_exception('invalidcommentid');
        }
    }
    return true;
}


function data_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-data-*'=>get_string('page-mod-data-x', 'data'));
    return $module_pagetype;
}


function data_get_all_recordids($dataid, $selectdata = '', $params = null) {
    global $DB;
    $initsql = 'SELECT r.id
                  FROM {data_records} r
                 WHERE r.dataid = :dataid';
    if ($selectdata != '') {
        $initsql .= $selectdata;
        $params = array_merge(array('dataid' => $dataid), $params);
    } else {
        $params = array('dataid' => $dataid);
    }
    $initsql .= ' GROUP BY r.id';
    $initrecord = $DB->get_recordset_sql($initsql, $params);
    $idarray = array();
    foreach ($initrecord as $data) {
        $idarray[] = $data->id;
    }
        $initrecord->close();
    return $idarray;
}


function data_get_advance_search_ids($recordids, $searcharray, $dataid) {
        if (empty($recordids)) {
                return array();
    }
    $searchcriteria = array_keys($searcharray);
        foreach ($searchcriteria as $key) {
        $recordids = data_get_recordids($key, $searcharray, $dataid, $recordids);
                if (!$recordids) {
            break;
        }
    }
    return $recordids;
}


function data_get_recordids($alias, $searcharray, $dataid, $recordids) {
    global $DB;

    $nestsearch = $searcharray[$alias];
        if ($alias < 0) {
        $alias = '';
    }
    list($insql, $params) = $DB->get_in_or_equal($recordids, SQL_PARAMS_NAMED);
    $nestselect = 'SELECT c' . $alias . '.recordid
                     FROM {data_content} c' . $alias . ',
                          {data_fields} f,
                          {data_records} r,
                          {user} u ';
    $nestwhere = 'WHERE u.id = r.userid
                    AND f.id = c' . $alias . '.fieldid
                    AND r.id = c' . $alias . '.recordid
                    AND r.dataid = :dataid
                    AND c' . $alias .'.recordid ' . $insql . '
                    AND ';

    $params['dataid'] = $dataid;
    if (count($nestsearch->params) != 0) {
        $params = array_merge($params, $nestsearch->params);
        $nestsql = $nestselect . $nestwhere . $nestsearch->sql;
    } else {
        $thing = $DB->sql_like($nestsearch->field, ':search1', false);
        $nestsql = $nestselect . $nestwhere . $thing . ' GROUP BY c' . $alias . '.recordid';
        $params['search1'] = "%$nestsearch->data%";
    }
    $nestrecords = $DB->get_recordset_sql($nestsql, $params);
    $nestarray = array();
    foreach ($nestrecords as $data) {
        $nestarray[] = $data->recordid;
    }
        $nestrecords->close();
    return $nestarray;
}


function data_get_advanced_search_sql($sort, $data, $recordids, $selectdata, $sortorder) {
    global $DB;

    $namefields = user_picture::fields('u');
        $namefields = str_replace('u.id,', '', $namefields);

    if ($sort == 0) {
        $nestselectsql = 'SELECT r.id, r.approved, r.timecreated, r.timemodified, r.userid, ' . $namefields . '
                        FROM {data_content} c,
                             {data_records} r,
                             {user} u ';
        $groupsql = ' GROUP BY r.id, r.approved, r.timecreated, r.timemodified, r.userid, u.firstname, u.lastname, ' . $namefields;
    } else {
                if ($sort <= 0) {
            switch ($sort) {
                case DATA_LASTNAME:
                    $sortcontentfull = "u.lastname";
                    break;
                case DATA_FIRSTNAME:
                    $sortcontentfull = "u.firstname";
                    break;
                case DATA_APPROVED:
                    $sortcontentfull = "r.approved";
                    break;
                case DATA_TIMEMODIFIED:
                    $sortcontentfull = "r.timemodified";
                    break;
                case DATA_TIMEADDED:
                default:
                    $sortcontentfull = "r.timecreated";
            }
        } else {
            $sortfield = data_get_field_from_id($sort, $data);
            $sortcontent = $DB->sql_compare_text('c.' . $sortfield->get_sort_field());
            $sortcontentfull = $sortfield->get_sort_sql($sortcontent);
        }

        $nestselectsql = 'SELECT r.id, r.approved, r.timecreated, r.timemodified, r.userid, ' . $namefields . ',
                                 ' . $sortcontentfull . '
                              AS sortorder
                            FROM {data_content} c,
                                 {data_records} r,
                                 {user} u ';
        $groupsql = ' GROUP BY r.id, r.approved, r.timecreated, r.timemodified, r.userid, ' . $namefields . ', ' .$sortcontentfull;
    }

        if ($selectdata == '') {
        $selectdata = 'WHERE c.recordid = r.id
                         AND r.dataid = :dataid
                         AND r.userid = u.id ';
    }

        if ($sort > 0 or data_get_field_from_id($sort, $data)) {
        $selectdata .= ' AND c.fieldid = :sort';
    }

        if (count($recordids) != 0) {
        list($insql, $inparam) = $DB->get_in_or_equal($recordids, SQL_PARAMS_NAMED);
    } else {
        list($insql, $inparam) = $DB->get_in_or_equal(array('-1'), SQL_PARAMS_NAMED);
    }
    $nestfromsql = $selectdata . ' AND c.recordid ' . $insql . $groupsql;
    $sqlselect['sql'] = "$nestselectsql $nestfromsql $sortorder";
    $sqlselect['params'] = $inparam;
    return $sqlselect;
}


function data_user_can_delete_preset($context, $preset) {
    global $USER;

    if (has_capability('mod/data:manageuserpresets', $context)) {
        return true;
    } else {
        $candelete = false;
        if ($preset->userid == $USER->id) {
            $candelete = true;
        }
        return $candelete;
    }
}


function data_delete_record($recordid, $data, $courseid, $cmid) {
    global $DB, $CFG;

    if ($deleterecord = $DB->get_record('data_records', array('id' => $recordid))) {
        if ($deleterecord->dataid == $data->id) {
            if ($contents = $DB->get_records('data_content', array('recordid' => $deleterecord->id))) {
                foreach ($contents as $content) {
                    if ($field = data_get_field_from_id($content->fieldid, $data)) {
                        $field->delete_content($content->recordid);
                    }
                }
                $DB->delete_records('data_content', array('recordid'=>$deleterecord->id));
                $DB->delete_records('data_records', array('id'=>$deleterecord->id));

                                if (!empty($CFG->enablerssfeeds)) {
                    require_once($CFG->dirroot.'/mod/data/rsslib.php');
                    data_rss_delete_file($data);
                }

                                $event = \mod_data\event\record_deleted::create(array(
                    'objectid' => $deleterecord->id,
                    'context' => context_module::instance($cmid),
                    'courseid' => $courseid,
                    'other' => array(
                        'dataid' => $deleterecord->dataid
                    )
                ));
                $event->add_record_snapshot('data_records', $deleterecord);
                $event->trigger();

                return true;
            }
        }
    }
    return false;
}


function data_process_submission(stdClass $mod, $fields, stdClass $datarecord) {
    $result = new stdClass();

        $emptyform = true;
    $requiredfieldsfilled = true;
    $fieldsvalidated = true;

        $result->generalnotifications = array();
    $result->fieldnotifications = array();

            $result->fields = array();

    $submitteddata = array();
    foreach ($datarecord as $fieldname => $fieldvalue) {
        if (strpos($fieldname, '_')) {
            $namearray = explode('_', $fieldname, 3);
            $fieldid = $namearray[1];
            if (!isset($submitteddata[$fieldid])) {
                $submitteddata[$fieldid] = array();
            }
            if (count($namearray) === 2) {
                $subfieldid = 0;
            } else {
                $subfieldid = $namearray[2];
            }

            $fielddata = new stdClass();
            $fielddata->fieldname = $fieldname;
            $fielddata->value = $fieldvalue;
            $submitteddata[$fieldid][$subfieldid] = $fielddata;
        }
    }

        foreach ($fields as $fieldrecord) {
                $fieldhascontent = false;

        $field = data_get_field($fieldrecord, $mod);
        if (isset($submitteddata[$fieldrecord->id])) {
                        if (method_exists($field, 'field_validation')) {
                $errormessage = $field->field_validation($submitteddata[$fieldrecord->id]);
                if ($errormessage) {
                    $result->fieldnotifications[$field->field->name][] = $errormessage;
                    $fieldsvalidated = false;
                }
            }
            foreach ($submitteddata[$fieldrecord->id] as $fieldname => $value) {
                if ($field->notemptyfield($value->value, $value->fieldname)) {
                                        $fieldhascontent = true;
                    $emptyform = false;
                }
            }
        }

                if ($field->field->required && !$fieldhascontent) {
            if (!isset($result->fieldnotifications[$field->field->name])) {
                $result->fieldnotifications[$field->field->name] = array();
            }
            $result->fieldnotifications[$field->field->name][] = get_string('errormustsupplyvalue', 'data');
            $requiredfieldsfilled = false;
        }

                if (isset($submitteddata[$fieldrecord->id])) {
            foreach ($submitteddata[$fieldrecord->id] as $value) {
                $result->fields[$value->fieldname] = $field;
            }
        }
    }

    if ($emptyform) {
                $result->generalnotifications[] = get_string('emptyaddform', 'data');
    }

    $result->validated = $requiredfieldsfilled && !$emptyform && $fieldsvalidated;

    return $result;
}
