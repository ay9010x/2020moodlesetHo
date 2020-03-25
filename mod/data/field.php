<?php




require_once('../../config.php');
require_once('lib.php');

$id             = optional_param('id', 0, PARAM_INT);            $d              = optional_param('d', 0, PARAM_INT);             $fid            = optional_param('fid', 0 , PARAM_INT);          $newtype        = optional_param('newtype','',PARAM_ALPHA);      $mode           = optional_param('mode','',PARAM_ALPHA);
$defaultsort    = optional_param('defaultsort', 0, PARAM_INT);
$defaultsortdir = optional_param('defaultsortdir', 0, PARAM_INT);
$cancel         = optional_param('cancel', 0, PARAM_BOOL);

if ($cancel) {
    $mode = 'list';
}

$url = new moodle_url('/mod/data/field.php');
if ($fid !== 0) {
    $url->param('fid', $fid);
}
if ($newtype !== '') {
    $url->param('newtype', $newtype);
}
if ($mode !== '') {
    $url->param('mode', $mode);
}
if ($defaultsort !== 0) {
    $url->param('defaultsort', $defaultsort);
}
if ($defaultsortdir !== 0) {
    $url->param('defaultsortdir', $defaultsortdir);
}
if ($cancel !== 0) {
    $url->param('cancel', $cancel);
}

if ($id) {
    $url->param('id', $id);
    $PAGE->set_url($url);
    if (! $cm = get_coursemodule_from_id('data', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
        print_error('coursemisconf');
    }
    if (! $data = $DB->get_record('data', array('id'=>$cm->instance))) {
        print_error('invalidcoursemodule');
    }

} else {
    $url->param('d', $d);
    $PAGE->set_url($url);
    if (! $data = $DB->get_record('data', array('id'=>$d))) {
        print_error('invalidid', 'data');
    }
    if (! $course = $DB->get_record('course', array('id'=>$data->course))) {
        print_error('invalidcoursemodule');
    }
    if (! $cm = get_coursemodule_from_instance('data', $data->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/data:managetemplates', $context);


switch ($mode) {

    case 'add':            if (confirm_sesskey() and $fieldinput = data_submitted()){

            
                    if (($fieldinput->name == '') or data_fieldname_exists($fieldinput->name, $data->id)) {

                $displaynoticebad = get_string('invalidfieldname','data');

            } else {

                            data_convert_arrays_to_strings($fieldinput);

                            $type = required_param('type', PARAM_FILE);
                $field = data_get_field_new($type, $data);

                $field->define_field($fieldinput);
                $field->insert_field();

                            data_append_new_field_to_templates($data, $fieldinput->name);

                $displaynoticegood = get_string('fieldadded','data');
            }
        }
        break;


    case 'update':            if (confirm_sesskey() and $fieldinput = data_submitted()){

            
            if (($fieldinput->name == '') or data_fieldname_exists($fieldinput->name, $data->id, $fieldinput->fid)) {

                $displaynoticebad = get_string('invalidfieldname','data');

            } else {
                            data_convert_arrays_to_strings($fieldinput);

                            $field = data_get_field_from_id($fid, $data);
                $oldfieldname = $field->field->name;

                $field->field->name = $fieldinput->name;
                $field->field->description = $fieldinput->description;
                $field->field->required = !empty($fieldinput->required) ? 1 : 0;

                for ($i=1; $i<=10; $i++) {
                    if (isset($fieldinput->{'param'.$i})) {
                        $field->field->{'param'.$i} = $fieldinput->{'param'.$i};
                    } else {
                        $field->field->{'param'.$i} = '';
                    }
                }

                $field->update_field();

                            data_replace_field_in_templates($data, $oldfieldname, $field->field->name);

                $displaynoticegood = get_string('fieldupdated','data');
            }
        }
        break;


    case 'delete':            if (confirm_sesskey()){

            if ($confirm = optional_param('confirm', 0, PARAM_INT)) {


                                if ($field = data_get_field_from_id($fid, $data)) {
                    $field->delete_field();

                                        data_replace_field_in_templates($data, $field->field->name, '');

                                        if ($fid == $data->defaultsort) {
                        $rec = new stdClass();
                        $rec->id = $data->id;
                        $rec->defaultsort = 0;
                        $rec->defaultsortdir = 0;
                        $DB->update_record('data', $rec);
                    }

                    $displaynoticegood = get_string('fielddeleted', 'data');
                }

            } else {

                data_print_header($course,$cm,$data, false);

                                $field = data_get_field_from_id($fid, $data);

                echo $OUTPUT->confirm('<strong>'.$field->name().': '.$field->field->name.'</strong><br /><br />'. get_string('confirmdeletefield','data'),
                             'field.php?d='.$data->id.'&mode=delete&fid='.$fid.'&confirm=1',
                             'field.php?d='.$data->id);

                echo $OUTPUT->footer();
                exit;
            }
        }
        break;


    case 'sort':            if (confirm_sesskey()) {
            $rec = new stdClass();
            $rec->id = $data->id;
            $rec->defaultsort = $defaultsort;
            $rec->defaultsortdir = $defaultsortdir;

            $DB->update_record('data', $rec);
            redirect($CFG->wwwroot.'/mod/data/field.php?d='.$data->id, get_string('changessaved'), 2);
            exit;
        }
        break;

    default:
        break;
}




$plugins = core_component::get_plugin_list('datafield');
$menufield = array();

foreach ($plugins as $plugin=>$fulldir){
    $menufield[$plugin] = get_string('pluginname', 'datafield_'.$plugin);    }
asort($menufield);    $PAGE->set_title(get_string('course') . ': ' . $course->fullname);
$PAGE->set_heading($course->fullname);

$PAGE->set_pagetype('mod-data-field-' . $newtype);
if (($mode == 'new') && (!empty($newtype)) && confirm_sesskey()) {              data_print_header($course, $cm, $data,'fields');

    $field = data_get_field_new($newtype, $data);
    $field->display_edit_field();

} else if ($mode == 'display' && confirm_sesskey()) {     data_print_header($course, $cm, $data,'fields');

    $field = data_get_field_from_id($fid, $data);
    $field->display_edit_field();

} else {                                                  data_print_header($course, $cm, $data,'fields');

    if (!$DB->record_exists('data_fields', array('dataid'=>$data->id))) {
        echo $OUTPUT->notification(get_string('nofieldindatabase','data'));          echo $OUTPUT->notification(get_string('pleaseaddsome','data', 'preset.php?id='.$cm->id));      
    } else {    
        $table = new html_table();
        $table->head = array(
            get_string('fieldname', 'data'),
            get_string('type', 'data'),
            get_string('required', 'data'),
            get_string('fielddescription', 'data'),
            get_string('action', 'data'),
        );
        $table->align = array('left','left','left', 'center');
        $table->wrap = array(false,false,false,false);

        if ($fff = $DB->get_records('data_fields', array('dataid'=>$data->id),'id')){
            foreach ($fff as $ff) {

                $field = data_get_field($ff, $data);

                $baseurl = new moodle_url('/mod/data/field.php', array(
                    'd'         => $data->id,
                    'fid'       => $field->field->id,
                    'sesskey'   => sesskey(),
                ));

                $displayurl = new moodle_url($baseurl, array(
                    'mode'      => 'display',
                ));

                $deleteurl = new moodle_url($baseurl, array(
                    'mode'      => 'delete',
                ));

                $table->data[] = array(
                    html_writer::link($displayurl, $field->field->name),
                    $field->image() . '&nbsp;' . $field->name(),
                    $field->field->required ? get_string('yes') : get_string('no'),
                    shorten_text($field->field->description, 30),
                    html_writer::link($displayurl, $OUTPUT->pix_icon('t/edit', get_string('edit'))) .
                        '&nbsp;' .
                        html_writer::link($deleteurl, $OUTPUT->pix_icon('t/delete', get_string('delete'))),
                );
            }
        }
        echo html_writer::table($table);
    }


    echo '<div class="fieldadd">';
    $popupurl = $CFG->wwwroot.'/mod/data/field.php?d='.$data->id.'&mode=new&sesskey='.  sesskey();
    echo $OUTPUT->single_select(new moodle_url($popupurl), 'newtype', $menufield, null, array('' => 'choosedots'),
        'fieldform', array('label' => get_string('newfield', 'data') . $OUTPUT->help_icon('newfield', 'data')));
    echo '</div>';

    echo '<div class="sortdefault">';
    echo '<form id="sortdefault" action="'.$CFG->wwwroot.'/mod/data/field.php" method="get">';
    echo '<div>';
    echo '<input type="hidden" name="d" value="'.$data->id.'" />';
    echo '<input type="hidden" name="mode" value="sort" />';
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
    echo '<label for="defaultsort">'.get_string('defaultsortfield','data').'</label>';
    echo '<select id="defaultsort" name="defaultsort">';
    if ($fields = $DB->get_records('data_fields', array('dataid'=>$data->id))) {
        echo '<optgroup label="'.get_string('fields', 'data').'">';
        foreach ($fields as $field) {
            if ($data->defaultsort == $field->id) {
                echo '<option value="'.$field->id.'" selected="selected">'.$field->name.'</option>';
            } else {
                echo '<option value="'.$field->id.'">'.$field->name.'</option>';
            }
        }
        echo '</optgroup>';
    }
    $options = array();
    $options[DATA_TIMEADDED]    = get_string('timeadded', 'data');

    echo '<optgroup label="'.get_string('other', 'data').'">';
    foreach ($options as $key => $name) {
        if ($data->defaultsort == $key) {
            echo '<option value="'.$key.'" selected="selected">'.$name.'</option>';
        } else {
            echo '<option value="'.$key.'">'.$name.'</option>';
        }
    }
    echo '</optgroup>';
    echo '</select>';

    $options = array(0 => get_string('ascending', 'data'),
                     1 => get_string('descending', 'data'));
    echo html_writer::label(get_string('sortby'), 'menudefaultsortdir', false, array('class' => 'accesshide'));
    echo html_writer::select($options, 'defaultsortdir', $data->defaultsortdir, false);
    echo '<input type="submit" value="'.get_string('save', 'data').'" />';
    echo '</div>';
    echo '</form>';
    echo '</div>';

}

echo $OUTPUT->footer();

