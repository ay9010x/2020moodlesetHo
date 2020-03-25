<?php




require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/grade/lib.php');

$id = required_param('id', PARAM_INT); 
$PAGE->set_url('/grade/import/keymanager.php', array('id' => $id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('nocourseid');
}

require_login($course);
$context = context_course::instance($id);

require_capability('moodle/grade:import', $context);

$plugins = grade_helper::get_plugins_import($course->id);
if (!isset($plugins['keymanager'])) {
    print_error('nopermissions');
}

print_grade_page_head($course->id, 'import', 'keymanager', get_string('keymanager', 'grades'));

$stredit   = get_string('edit');
$strdelete = get_string('delete');

$data = array();
$params = array($course->id, $USER->id);
if ($keys = $DB->get_records_select('user_private_key', "script='grade/import' AND instance=? AND userid=?", $params)) {
    foreach($keys as $key) {
        $line = array();
        $line[0] = format_string($key->value);
        $line[1] = $key->iprestriction;
        $line[2] = empty($key->validuntil) ? get_string('always') : userdate($key->validuntil);

        $url = new moodle_url('key.php', array('id' => $key->id));

        $buttons = $OUTPUT->action_icon($url, new pix_icon('t/edit', $stredit));

        $url->param('delete', 1);
        $url->param('sesskey', sesskey());
        $buttons .= $OUTPUT->action_icon($url, new pix_icon('t/delete', $strdelete));

        $line[3] = $buttons;
        $data[] = $line;
    }
}
$table = new html_table();
$table->head  = array(get_string('keyvalue', 'userkey'), get_string('keyiprestriction', 'userkey'), get_string('keyvaliduntil', 'userkey'), $stredit);
$table->size  = array('50%', '30%', '10%', '10%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '90%';
$table->data  = $data;
echo html_writer::table($table);

echo $OUTPUT->container_start('buttons mdl-align');
echo $OUTPUT->single_button(new moodle_url('key.php', array('courseid'=>$course->id)), get_string('newuserkey', 'userkey'));
echo $OUTPUT->container_end();

echo $OUTPUT->footer();

