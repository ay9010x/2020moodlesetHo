<?php

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$repository       = optional_param('repos', '', PARAM_ALPHANUMEXT);
$action           = optional_param('action', '', PARAM_ALPHANUMEXT);
$sure             = optional_param('sure', '', PARAM_ALPHA);
$downloadcontents = optional_param('downloadcontents', false, PARAM_BOOL);

$display = true; 
$pagename = 'managerepositories';

if ($action == 'edit') {
    $pagename = 'repositorysettings' . $repository;
} else if ($action == 'delete') {
    $pagename = 'repositorydelete';
} else if (($action == 'newon') || ($action == 'newoff')) {
    $pagename = 'repositorynew';
}

$formaction = $action;

if ($action == 'newon') {
    $action = 'new';
    $visible = true;
} else if ($action == 'newoff') {
    $action = 'new';
    $visible = false;
}

require_capability('moodle/site:config', context_system::instance());
admin_externalpage_setup($pagename);

$sesskeyurl = $CFG->wwwroot.'/'.$CFG->admin.'/repository.php?sesskey=' . sesskey();
$baseurl    = $CFG->wwwroot.'/'.$CFG->admin.'/repository.php';

$configstr  = get_string('manage', 'repository');

$return = true;

if (!empty($action)) {
    require_sesskey();
}


function repository_action_url($repository) {
    global $baseurl;
    return new moodle_url($baseurl, array('sesskey'=>sesskey(), 'repos'=>$repository));
}

if (($action == 'edit') || ($action == 'new')) {
    $pluginname = '';
    if ($action == 'edit') {
        $repositorytype = repository::get_type_by_typename($repository);
        $classname = 'repository_' . $repositorytype->get_typename();
        $configs = call_user_func(array($classname, 'get_type_option_names'));
        $plugin = $repositorytype->get_typename();
                $instanceoptions = call_user_func(array($classname, 'get_instance_option_names'));
        if (empty($instanceoptions)) {
            $params = array();
            $params['type'] = $plugin;
            $instances = repository::get_instances($params);
            if ($instance = array_pop($instances)) {
                                $pluginname = $instance->instance->name;
            }
        }

    } else {
        $repositorytype = null;
        $plugin = $repository;
        $typeid = $repository;
    }
    $PAGE->set_pagetype('admin-repository-' . $plugin);
        $mform = new repository_type_form('', array('pluginname'=>$pluginname, 'plugin' => $plugin, 'instance' => $repositorytype, 'action' => $formaction));
    $fromform = $mform->get_data();

        $nosettings = false;
    if ($action == 'new') {
        $adminconfignames = repository::static_function($repository, 'get_type_option_names');
        $nosettings = empty($adminconfignames);
    }
    
    if ($mform->is_cancelled()){
        redirect($baseurl);
    } else if (!empty($fromform) || $nosettings) {
        require_sesskey();
        if ($action == 'edit') {
            $settings = array();
            foreach($configs as $config) {
                if (!empty($fromform->$config)) {
                    $settings[$config] = $fromform->$config;
                } else {
                                                            $settings[$config] = '';
                }
            }
            $instanceoptionnames = repository::static_function($repository, 'get_instance_option_names');
            if (!empty($instanceoptionnames)) {
                if (array_key_exists('enablecourseinstances', $fromform)) {
                    $settings['enablecourseinstances'] = $fromform->enablecourseinstances;
                }
                else {
                    $settings['enablecourseinstances'] = 0;
                }
                if (array_key_exists('enableuserinstances', $fromform)) {
                    $settings['enableuserinstances'] = $fromform->enableuserinstances;
                }
                else {
                    $settings['enableuserinstances'] = 0;
                }
            }
            $success = $repositorytype->update_options($settings);
        } else {
            $type = new repository_type($plugin, (array)$fromform, $visible);
            $success = true;
            if (!$repoid = $type->create()) {
                $success = false;
            }
            $data = data_submitted();
        }
        if ($success) {
                        core_plugin_manager::reset_caches();
            redirect($baseurl);
        } else {
            print_error('instancenotsaved', 'repository', $baseurl);
        }
        exit;
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('configplugin', 'repository_'.$plugin));
        $displaysettingform = true;
        if ($action == 'edit') {
            $typeoptionnames = repository::static_function($repository, 'get_type_option_names');
            $instanceoptionnames = repository::static_function($repository, 'get_instance_option_names');
            if (empty($typeoptionnames) && empty($instanceoptionnames)) {
                $displaysettingform = false;
            }
        }
        if ($displaysettingform){
            $OUTPUT->box_start();
            $mform->display();
            $OUTPUT->box_end();
        }
        $return = false;

                if ($action == 'edit') {
            $instanceoptionnames = repository::static_function($repository, 'get_instance_option_names');
            if (!empty($instanceoptionnames)) {
                repository::display_instances_list(context_system::instance(), $repository);
            }
        }
    }
} else if ($action == 'show') {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', '', $baseurl);
    }
    $repositorytype = repository::get_type_by_typename($repository);
    if (empty($repositorytype)) {
        print_error('invalidplugin', 'repository', '', $repository);
    }
    $repositorytype->update_visibility(true);
    core_plugin_manager::reset_caches();
    $return = true;
} else if ($action == 'hide') {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', '', $baseurl);
    }
    $repositorytype = repository::get_type_by_typename($repository);
    if (empty($repositorytype)) {
        print_error('invalidplugin', 'repository', '', $repository);
    }
    $repositorytype->update_visibility(false);
    core_plugin_manager::reset_caches();
    $return = true;
} else if ($action == 'delete') {
    $repositorytype = repository::get_type_by_typename($repository);
    if ($sure) {
        $PAGE->set_pagetype('admin-repository-' . $repository);
        if (!confirm_sesskey()) {
            print_error('confirmsesskeybad', '', $baseurl);
        }

        if ($repositorytype->delete($downloadcontents)) {
            core_plugin_manager::reset_caches();
            redirect($baseurl);
        } else {
            print_error('instancenotdeleted', 'repository', $baseurl);
        }
        exit;
    } else {
        echo $OUTPUT->header();

        $message = get_string('confirmremove', 'repository', $repositorytype->get_readablename());

        $output = $OUTPUT->box_start('generalbox', 'notice');
        $output .= html_writer::tag('p', $message);

        $removeurl = new moodle_url($sesskeyurl);
        $removeurl->params(array(
            'action' =>'delete',
            'repos' => $repository,
            'sure' => 'yes',
        ));

        $removeanddownloadurl = new moodle_url($sesskeyurl);
        $removeanddownloadurl->params(array(
            'action' =>'delete',
            'repos'=> $repository,
            'sure' => 'yes',
            'downloadcontents' => 1,
        ));

        $output .= $OUTPUT->single_button($removeurl, get_string('continueuninstall', 'repository'));
        $output .= $OUTPUT->single_button($removeanddownloadurl, get_string('continueuninstallanddownload', 'repository'));
        $output .= $OUTPUT->single_button($baseurl, get_string('cancel'));
        $output .= $OUTPUT->box_end();

        echo $output;

        $return = false;
    }
} else if ($action == 'moveup') {
    $repositorytype = repository::get_type_by_typename($repository);
    $repositorytype->move_order('up');
} else if ($action == 'movedown') {
    $repositorytype = repository::get_type_by_typename($repository);
    $repositorytype->move_order('down');
} else {
        echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('manage', 'repository'));

        $strshow = get_string('on', 'repository');
    $strhide = get_string('off', 'repository');
    $strdelete = get_string('disabled', 'repository');
    $struninstall = get_string('uninstallplugin', 'core_admin');

    $actionchoicesforexisting = array(
        'show' => $strshow,
        'hide' => $strhide,
        'delete' => $strdelete
    );

    $actionchoicesfornew = array(
        'newon' => $strshow,
        'newoff' => $strhide,
        'delete' => $strdelete
    );

    $output = '';
    $output .= $OUTPUT->box_start('generalbox');

        $settingsstr = get_string('settings');
    $disablestr = get_string('disable');

        $table = new html_table();
    $table->head = array(get_string('name'), get_string('isactive', 'repository'), get_string('order'), $settingsstr, $struninstall);

    $table->colclasses = array('leftalign', 'centeralign', 'centeralign', 'centeralign', 'centeralign', 'centeralign');
    $table->id = 'repositoriessetting';
    $table->data = array();
    $table->attributes['class'] = 'admintable generaltable';

        $repositorytypes = repository::get_types();
        $alreadyplugins = array();
    if (!empty($repositorytypes)) {
        $totalrepositorytypes = count($repositorytypes);
        $updowncount = 1;
        foreach ($repositorytypes as $i) {
            $settings = '';
            $typename = $i->get_typename();
                        $typeoptionnames = repository::static_function($typename, 'get_type_option_names');
            $instanceoptionnames = repository::static_function($typename, 'get_instance_option_names');

            if (!empty($typeoptionnames) || !empty($instanceoptionnames)) {
                                if (!empty($instanceoptionnames)) {
                    $params = array();
                    $params['context'] = array(context_system::instance());
                    $params['onlyvisible'] = false;
                    $params['type'] = $typename;
                    $admininstancenumber = count(repository::static_function($typename, 'get_instances', $params));
                                        $admininstancenumbertext = get_string('instancesforsite', 'repository', $admininstancenumber);
                    $params['context'] = array();
                    $instances = repository::static_function($typename, 'get_instances', $params);
                    $courseinstances = array();
                    $userinstances = array();

                    foreach ($instances as $instance) {
                        $repocontext = context::instance_by_id($instance->instance->contextid);
                        if ($repocontext->contextlevel == CONTEXT_COURSE) {
                            $courseinstances[] = $instance;
                        } else if ($repocontext->contextlevel == CONTEXT_USER) {
                            $userinstances[] = $instance;
                        }
                    }
                                        $instancenumber = count($courseinstances);
                    $courseinstancenumbertext = get_string('instancesforcourses', 'repository', $instancenumber);

                                        $instancenumber =  count($userinstances);
                    $userinstancenumbertext = get_string('instancesforusers', 'repository', $instancenumber);
                } else {
                    $admininstancenumbertext = "";
                    $courseinstancenumbertext = "";
                    $userinstancenumbertext = "";
                }

                $settings .= '<a href="' . $sesskeyurl . '&amp;action=edit&amp;repos=' . $typename . '">' . $settingsstr .'</a>';

                $settings .= $OUTPUT->container_start('mdl-left');
                $settings .= '<br/>';
                $settings .= $admininstancenumbertext;
                $settings .= '<br/>';
                $settings .= $courseinstancenumbertext;
                $settings .= '<br/>';
                $settings .= $userinstancenumbertext;
                $settings .= $OUTPUT->container_end();
            }
                        if ($i->get_visible()) {
                $currentaction = 'show';
            } else {
                $currentaction = 'hide';
            }

            $select = new single_select(repository_action_url($typename, 'repos'), 'action', $actionchoicesforexisting, $currentaction, null, 'applyto' . basename($typename));
            $select->set_label(get_string('action'), array('class' => 'accesshide'));
                        $updown = '';
            $spacer = $OUTPUT->spacer(array('height'=>15, 'width'=>15)); 
            if ($updowncount > 1) {
                $updown .= "<a href=\"$sesskeyurl&amp;action=moveup&amp;repos=".$typename."\">";
                $updown .= "<img src=\"" . $OUTPUT->pix_url('t/up') . "\" alt=\"up\" /></a>&nbsp;";
            }
            else {
                $updown .= $spacer;
            }
            if ($updowncount < $totalrepositorytypes) {
                $updown .= "<a href=\"$sesskeyurl&amp;action=movedown&amp;repos=".$typename."\">";
                $updown .= "<img src=\"" . $OUTPUT->pix_url('t/down') . "\" alt=\"down\" /></a>";
            }
            else {
                $updown .= $spacer;
            }

            $updowncount++;

            $uninstall = '';
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('repository_' . $typename, 'manage')) {
                $uninstall = html_writer::link($uninstallurl, $struninstall);
            }

            $table->data[] = array($i->get_readablename(), $OUTPUT->render($select), $updown, $settings, $uninstall);

            if (!in_array($typename, $alreadyplugins)) {
                $alreadyplugins[] = $typename;
            }
        }
    }

        $plugins = core_component::get_plugin_list('repository');
    if (!empty($plugins)) {
        foreach ($plugins as $plugin => $dir) {
                        if (!in_array($plugin, $alreadyplugins)) {
                $select = new single_select(repository_action_url($plugin, 'repos'), 'action', $actionchoicesfornew, 'delete', null, 'applyto' . basename($plugin));
                $select->set_label(get_string('action'), array('class' => 'accesshide'));
                $uninstall = '';
                if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('repository_' . $plugin, 'manage')) {
                    $uninstall = html_writer::link($uninstallurl, $struninstall);
                }
                $table->data[] = array(get_string('pluginname', 'repository_'.$plugin), $OUTPUT->render($select), '', '', $uninstall);
            }
        }
    }

    $output .= html_writer::table($table);
    $output .= $OUTPUT->box_end();
    print $output;
    $return = false;
}

if ($return) {
    redirect($baseurl);
}
echo $OUTPUT->footer();