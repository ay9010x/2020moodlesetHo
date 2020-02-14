<?php

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/adminlib.php');

require_sesskey();

$edit    = optional_param('edit', 0, PARAM_INT);
$new     = optional_param('new', '', PARAM_PLUGIN);
$hide    = optional_param('hide', 0, PARAM_INT);
$delete  = optional_param('delete', 0, PARAM_INT);
$sure    = optional_param('sure', '', PARAM_ALPHA);
$type    = optional_param('type', '', PARAM_PLUGIN);
$downloadcontents = optional_param('downloadcontents', false, PARAM_BOOL);

$context = context_system::instance();

$pagename = 'repositorycontroller';

if ($edit){
    $pagename = 'repositoryinstanceedit';
} else if ($delete) {
    $pagename = 'repositorydelete';
} else if ($new) {
    $pagename = 'repositoryinstancenew';
}

admin_externalpage_setup($pagename, '', null, new moodle_url('/admin/repositoryinstance.php'));
require_capability('moodle/site:config', $context);

$baseurl = new moodle_url("/$CFG->admin/repositoryinstance.php", array('sesskey'=>sesskey()));

$parenturl = new moodle_url("/$CFG->admin/repository.php", array(
    'sesskey'=>sesskey(),
    'action'=>'edit',
));

if ($new) {
    $parenturl->param('repos', $new);
} else {
    $parenturl->param('repos', $type);
}

$return = true;

if (!empty($edit) || !empty($new)) {
    if (!empty($edit)) {
        $instance = repository::get_instance($edit);
        if (!$instance->can_be_edited_by_user()) {
            throw new repository_exception('nopermissiontoaccess', 'repository');
        }
        $instancetype = repository::get_type_by_id($instance->options['typeid']);
        $classname = 'repository_' . $instancetype->get_typename();
        $configs  = $instance->get_instance_option_names();
        $plugin = $instancetype->get_typename();
        $typeid = $instance->options['typeid'];
    } else {
        $plugin = $new;
        $typeid = null;
        $instance = null;
    }

        $mform = new repository_instance_form('', array('plugin' => $plugin, 'typeid' => $typeid, 'instance' => $instance, 'contextid' => $context->id));
    
    if ($mform->is_cancelled()){
        redirect($parenturl);
        exit;
    } else if ($fromform = $mform->get_data()){
        if ($edit) {
            $settings = array();
            $settings['name'] = $fromform->name;
            if (!$instance->readonly) {
                foreach($configs as $config) {
                    if (isset($fromform->$config)) {
                        $settings[$config] = $fromform->$config;
                    } else {
                        $settings[$config] = null;
                    }
                }
            }
            $success = $instance->set_option($settings);
        } else {
            $success = repository::static_function($plugin, 'create', $plugin, 0, $context, $fromform);
            $data = data_submitted();
        }
        if ($success) {
            core_plugin_manager::reset_caches();
            redirect($parenturl);
        } else {
            print_error('instancenotsaved', 'repository', $parenturl);
        }
        exit;
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('configplugin', 'repository_'.$plugin));
        echo $OUTPUT->box_start();
        $mform->display();
        echo $OUTPUT->box_end();
        $return = false;
    }
} else if (!empty($hide)) {
    $instance = repository::get_type_by_typename($hide);
    $instance->hide();
    core_plugin_manager::reset_caches();
    $return = true;
} else if (!empty($delete)) {
    $instance = repository::get_instance($delete);
    if ($instance->readonly) {
                throw new repository_exception('readonlyinstance', 'repository');
    } else if (!$instance->can_be_edited_by_user()) {
        throw new repository_exception('nopermissiontoaccess', 'repository');
    }
    if ($sure) {
        if ($instance->delete($downloadcontents)) {
            $deletedstr = get_string('instancedeleted', 'repository');
            core_plugin_manager::reset_caches();
            redirect($parenturl, $deletedstr, 3);
        } else {
            print_error('instancenotdeleted', 'repository', $parenturl);
        }
        exit;
    }

    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox', 'notice');
    $continueurl = new moodle_url($baseurl, array(
        'type' => $type,
        'delete' => $delete,
        'sure' => 'yes',
    ));
    $continueanddownloadurl = new moodle_url($continueurl, array(
        'downloadcontents' => 1
    ));
    $message = get_string('confirmdelete', 'repository', $instance->name);
    echo html_writer::tag('p', $message);

    echo $OUTPUT->single_button($continueurl, get_string('continueuninstall', 'repository'));
    echo $OUTPUT->single_button($continueanddownloadurl, get_string('continueuninstallanddownload', 'repository'));
    echo $OUTPUT->single_button($parenturl, get_string('cancel'));

    echo $OUTPUT->box_end();

    $return = false;
}

if (!empty($return)) {
    redirect($parenturl);
}
echo $OUTPUT->footer();
