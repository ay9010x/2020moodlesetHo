<?php


require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('edit_form.php');

admin_externalpage_setup('tool_filetypes');

$oldextension = optional_param('oldextension', '', PARAM_ALPHANUMEXT);
$mform = new tool_filetypes_form('edit.php', array('oldextension' => $oldextension));
$title = get_string('addfiletypes', 'tool_filetypes');

if ($oldextension) {
        $mimetypes = get_mimetypes_array();
    if (!array_key_exists($oldextension, $mimetypes)) {
        throw new moodle_exception('error_notfound', 'tool_filetypes');
    }
    $typeinfo = $mimetypes[$oldextension];
    $formdata = array(
        'extension' => $oldextension,
        'mimetype' => $typeinfo['type'],
        'icon' => $typeinfo['icon'],
        'oldextension' => $oldextension,
        'description' => '',
        'groups' => '',
        'corestring' => '',
        'defaulticon' => 0
    );
    if (!empty($typeinfo['customdescription'])) {
        $formdata['description'] = $typeinfo['customdescription'];
    }
    if (!empty($typeinfo['groups'])) {
        $formdata['groups'] = implode(', ', $typeinfo['groups']);
    }
    if (!empty($typeinfo['string'])) {
        $formdata['corestring'] = $typeinfo['string'];
    }
    if (!empty($typeinfo['defaulticon'])) {
        $formdata['defaulticon'] = 1;
    }

    $mform->set_data($formdata);
    $title = get_string('editfiletypes', 'tool_filetypes');
}

$backurl = new \moodle_url('/admin/tool/filetypes/index.php');
if ($mform->is_cancelled()) {
    redirect($backurl);
} else if ($data = $mform->get_data()) {
        $data->groups = trim($data->groups);
    if ($data->groups) {
        $data->groups = preg_split('~,\s*~', $data->groups);
    } else {
        $data->groups = array();
    }
    if (empty($data->defaulticon)) {
        $data->defaulticon = 0;
    }
    if (empty($data->corestring)) {
        $data->corestring = '';
    }
    if (empty($data->description)) {
        $data->description = '';
    }
    if ($data->oldextension) {
                core_filetypes::update_type($data->oldextension, $data->extension, $data->mimetype, $data->icon,
            $data->groups, $data->corestring, $data->description, (bool)$data->defaulticon);
    } else {
                core_filetypes::add_type($data->extension, $data->mimetype, $data->icon,
            $data->groups, $data->corestring, $data->description, (bool)$data->defaulticon);
    }
    redirect($backurl);
}

$context = context_system::instance();
$PAGE->set_url(new \moodle_url('/admin/tool/filetypes/edit.php', array('oldextension' => $oldextension)));
$PAGE->navbar->add($oldextension ? s($oldextension) : $title);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname. ': ' . $title);

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
