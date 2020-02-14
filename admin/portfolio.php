<?php

require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir . '/portfoliolib.php');
require_once($CFG->libdir . '/portfolio/forms.php');
require_once($CFG->libdir . '/adminlib.php');

$portfolio     = optional_param('pf', '', PARAM_ALPHANUMEXT);
$action        = optional_param('action', '', PARAM_ALPHA);
$sure          = optional_param('sure', '', PARAM_ALPHA);

$display = true; 
$pagename = 'manageportfolios';

if ($action == 'edit') {
    $pagename = 'portfoliosettings' . $portfolio;
} else if ($action == 'delete') {
    $pagename = 'portfoliodelete';
} else if (($action == 'newon') || ($action == 'newoff')) {
    $pagename = 'portfolionew';
}

$formaction = $action;

if ($action == 'newon') {
    $action = 'new';
    $visible = 1;
} else if ($action == 'newoff') {
    $action = 'new';
    $visible = 0;
}

admin_externalpage_setup($pagename);

require_capability('moodle/site:config', context_system::instance());

$baseurl    = "$CFG->wwwroot/$CFG->admin/portfolio.php";
$sesskeyurl = "$CFG->wwwroot/$CFG->admin/portfolio.php?sesskey=" . sesskey();
$configstr  = get_string('manageportfolios', 'portfolio');

$return = true; 

function portfolio_action_url($portfolio) {
    global $baseurl;
    return new moodle_url($baseurl, array('sesskey'=>sesskey(), 'pf'=>$portfolio));
}

if (($action == 'edit') || ($action == 'new')) {
    if (($action == 'edit')) {
        $instance = portfolio_instance($portfolio);
        $plugin = $instance->get('plugin');

                                                $visible = $instance->get('visible');
    } else {
        $instance = null;
        $plugin = $portfolio;
    }

    $PAGE->set_pagetype('admin-portfolio-' . $plugin);

        $mform = new portfolio_admin_form('', array('plugin' => $plugin, 'instance' => $instance, 'portfolio' => $portfolio, 'action' => $formaction, 'visible' => $visible));
        if ($mform->is_cancelled()){
        redirect($baseurl);
        exit;
    } else if (($fromform = $mform->get_data()) && (confirm_sesskey())) {
                foreach (array('pf', 'action', 'plugin', 'sesskey', 'submitbutton') as $key) {
            unset($fromform->{$key});
        }
                if ($action == 'edit') {
            $instance->set_config((array)$fromform);
            $instance->save();
        } else {
            portfolio_static_function($plugin, 'create_instance', $plugin, $fromform->name, $fromform);
        }
        core_plugin_manager::reset_caches();
        $savedstr = get_string('instancesaved', 'portfolio');
        redirect($baseurl, $savedstr, 1);
        exit;
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('configplugin', 'portfolio'));
        echo $OUTPUT->box_start();
        $mform->display();
        echo $OUTPUT->box_end();
        $return = false;
    }
} else if (($action == 'hide') || ($action == 'show')) {
    require_sesskey();

    $instance = portfolio_instance($portfolio);
    $current = $instance->get('visible');
    if (empty($current) && $instance->instance_sanity_check()) {
        print_error('cannotsetvisible', 'portfolio', $baseurl);
    }

    if ($action == 'show') {
        $visible = 1;
    } else {
        $visible = 0;
    }

    $instance->set('visible', $visible);
    $instance->save();
    core_plugin_manager::reset_caches();
    $return = true;
} else if ($action == 'delete') {
    $instance = portfolio_instance($portfolio);
    if ($sure) {
        if (!confirm_sesskey()) {
            print_error('confirmsesskeybad', '', $baseurl);
        }
        if ($instance->delete()) {
            $deletedstr = get_string('instancedeleted', 'portfolio');
            redirect($baseurl, $deletedstr, 1);
        } else {
            print_error('instancenotdeleted', 'portfolio', $baseurl);
        }
        exit;
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('sure', 'portfolio', $instance->get('name')), $sesskeyurl . '&pf='.$portfolio.'&action=delete&sure=yes', $baseurl);
        $return = false;
    }
} else {
        echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('manageportfolios', 'portfolio'));

        $strshow = get_string('on', 'portfolio');
    $strhide = get_string('off', 'portfolio');
    $strdelete = get_string('disabledinstance', 'portfolio');
    $strsettings = get_string('settings');

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

    $output = $OUTPUT->box_start('generalbox');

    $plugins = core_component::get_plugin_list('portfolio');
    $plugins = array_keys($plugins);
    $instances = portfolio_instances(false, false);
    $usedplugins = array();

        define('ADMIN_EDITING_PORTFOLIO', true);

    $insane = portfolio_plugin_sanity_check($plugins);
    $insaneinstances = portfolio_instance_sanity_check($instances);

    $table = new html_table();
    $table->head = array(get_string('plugin', 'portfolio'), '', '');
    $table->data = array();

    foreach ($instances as $i) {
        $settings = '<a href="' . $sesskeyurl . '&amp;action=edit&amp;pf=' . $i->get('id') . '">' . $strsettings .'</a>';
                $pluginid = $i->get('id');
        $plugin = $i->get('plugin');
        $pluginname = $i->get('name');

                if (array_key_exists($plugin, $insane) || array_key_exists($pluginid, $insaneinstances)) {
            if (!empty($insane[$plugin])) {
                $information = $insane[$plugin];
            } else if (!empty($insaneinstances[$pluginid])) {
                $information = $insaneinstances[$pluginid];
            }
            $table->data[] = array($pluginname, $strdelete  . " " . $OUTPUT->help_icon($information, 'portfolio_' .  $plugin), $settings);
        } else {
            if ($i->get('visible')) {
                $currentaction = 'show';
            } else {
                $currentaction = 'hide';
            }
            $select = new single_select(portfolio_action_url($pluginid, 'pf'), 'action', $actionchoicesforexisting, $currentaction, null, 'applyto' . $pluginid);
            $select->set_label(get_string('action'), array('class' => 'accesshide'));
            $table->data[] = array($pluginname, $OUTPUT->render($select), $settings);
        }
        if (!in_array($plugin, $usedplugins)) {
            $usedplugins[] = $plugin;
        }
    }

        $insaneplugins = array();
    if (!empty($plugins)) {
        foreach ($plugins as $p) {
                        if (!portfolio_static_function($p, 'allows_multiple_instances') && in_array($p, $usedplugins)) {
                continue;
            }

                        if (array_key_exists($p, $insane)) {
                $insaneplugins[] = $p;
            } else {
                $select = new single_select(portfolio_action_url($p, 'pf'), 'action', $actionchoicesfornew, 'delete', null, 'applyto' . $p);
                $select->set_label(get_string('action'), array('class' => 'accesshide'));
                $table->data[] = array(portfolio_static_function($p, 'get_name'), $OUTPUT->render($select), '');
            }
        }
    }

        if (!empty($insaneplugins)) {
        foreach ($insaneplugins as $p) {
            $table->data[] = array(portfolio_static_function($p, 'get_name'), $strdelete . " " . $OUTPUT->help_icon($insane[$p], 'portfolio_' .  $p), '');
        }
    }

    $output .= html_writer::table($table);

    $output .= $OUTPUT->box_end();

    echo $output;
    $return = false;
}

if ($return) {
        redirect($baseurl);
}

echo $OUTPUT->footer();

