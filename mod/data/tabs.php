<?php



    if (empty($currenttab) or empty($data) or empty($course)) {
        print_error('cannotcallscript');
    }

    $context = context_module::instance($cm->id);

    $row = array();

    $row[] = new tabobject('list', new moodle_url('/mod/data/view.php', array('d' => $data->id)), get_string('list','data'));

    if (isset($record)) {
        $row[] = new tabobject('single', new moodle_url('/mod/data/view.php', array('d' => $data->id, 'rid' => $record->id)), get_string('single','data'));
    } else {
        $row[] = new tabobject('single', new moodle_url('/mod/data/view.php', array('d' => $data->id, 'mode' => 'single')), get_string('single','data'));
    }

        $row[] = new tabobject('asearch', new moodle_url('/mod/data/view.php', array('d' => $data->id, 'mode' => 'asearch')), get_string('search', 'data'));

    if (isloggedin()) {         if (data_user_can_add_entry($data, $currentgroup, $groupmode, $context)) {             $addstring = empty($editentry) ? get_string('add', 'data') : get_string('editentry', 'data');
            $row[] = new tabobject('add', new moodle_url('/mod/data/edit.php', array('d' => $data->id)), $addstring);
        }
        if (has_capability(DATA_CAP_EXPORT, $context)) {
                                    $row[] = new tabobject('export', new moodle_url('/mod/data/export.php', array('d' => $data->id)),
                         get_string('export', 'data'));
        }
        if (has_capability('mod/data:managetemplates', $context)) {
            if ($currenttab == 'list') {
                $defaultemplate = 'listtemplate';
            } else if ($currenttab == 'add') {
                $defaultemplate = 'addtemplate';
            } else if ($currenttab == 'asearch') {
                $defaultemplate = 'asearchtemplate';
            } else {
                $defaultemplate = 'singletemplate';
            }

            $templatestab = new tabobject('templates', new moodle_url('/mod/data/templates.php', array('d' => $data->id, 'mode' => $defaultemplate)),
                         get_string('templates','data'));
            $row[] = $templatestab;
            $row[] = new tabobject('fields', new moodle_url('/mod/data/field.php', array('d' => $data->id)),
                         get_string('fields','data'));
            $row[] = new tabobject('presets', new moodle_url('/mod/data/preset.php', array('d' => $data->id)),
                         get_string('presets', 'data'));
        }
    }

    if ($currenttab == 'templates' and isset($mode) && isset($templatestab)) {
        $templatestab->inactive = true;
        $templatelist = array ('listtemplate', 'singletemplate', 'asearchtemplate', 'addtemplate', 'rsstemplate', 'csstemplate', 'jstemplate');

        $currenttab ='';
        foreach ($templatelist as $template) {
            $templatestab->subtree[] = new tabobject($template, new moodle_url('/mod/data/templates.php', array('d' => $data->id, 'mode' => $template)), get_string($template, 'data'));
            if ($template == $mode) {
                $currenttab = $template;
            }
        }
        if ($currenttab == '') {
            $currenttab = $mode = 'singletemplate';
        }
    }

    echo $OUTPUT->tabtree($row, $currenttab);


