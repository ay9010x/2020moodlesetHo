<?php




require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filelib.php');

$fetchupdates = optional_param('fetchupdates', false, PARAM_BOOL); $updatesonly = optional_param('updatesonly', false, PARAM_BOOL); $contribonly = optional_param('contribonly', false, PARAM_BOOL); $uninstall = optional_param('uninstall', '', PARAM_COMPONENT); $delete = optional_param('delete', '', PARAM_COMPONENT); $confirmed = optional_param('confirm', false, PARAM_BOOL); $return = optional_param('return', 'overview', PARAM_ALPHA); $installupdate = optional_param('installupdate', null, PARAM_COMPONENT); $installupdateversion = optional_param('installupdateversion', null, PARAM_INT); $installupdatex = optional_param('installupdatex', false, PARAM_BOOL); $confirminstallupdate = optional_param('confirminstallupdate', false, PARAM_BOOL); 

require_login();
$syscontext = context_system::instance();
require_capability('moodle/site:config', $syscontext);

$pageparams = array('updatesonly' => $updatesonly, 'contribonly' => $contribonly);
$pageurl = new moodle_url('/admin/plugins.php', $pageparams);

$pluginman = core_plugin_manager::instance();

if ($uninstall) {
    require_sesskey();

    if (!$confirmed) {
        admin_externalpage_setup('pluginsoverview', '', $pageparams);
    } else {
        $PAGE->set_url($pageurl);
        $PAGE->set_context($syscontext);
        $PAGE->set_pagelayout('maintenance');
        $PAGE->set_popup_notification_allowed(false);
    }

    
    $output = $PAGE->get_renderer('core', 'admin');

    $pluginfo = $pluginman->get_plugin_info($uninstall);

        if (is_null($pluginfo)) {
        throw new moodle_exception('err_uninstalling_unknown_plugin', 'core_plugin', '', array('plugin' => $uninstall),
            'core_plugin_manager::get_plugin_info() returned null for the plugin to be uninstalled');
    }

    $pluginname = $pluginman->plugin_name($pluginfo->component);
    $PAGE->set_title($pluginname);
    $PAGE->navbar->add(get_string('uninstalling', 'core_plugin', array('name' => $pluginname)));

    if (!$pluginman->can_uninstall_plugin($pluginfo->component)) {
        throw new moodle_exception('err_cannot_uninstall_plugin', 'core_plugin', '',
            array('plugin' => $pluginfo->component),
            'core_plugin_manager::can_uninstall_plugin() returned false');
    }

    if (!$confirmed) {
        $continueurl = new moodle_url($PAGE->url, array('uninstall' => $pluginfo->component, 'sesskey' => sesskey(), 'confirm' => 1, 'return'=>$return));
        $cancelurl = $pluginfo->get_return_url_after_uninstall($return);
        echo $output->plugin_uninstall_confirm_page($pluginman, $pluginfo, $continueurl, $cancelurl);
        exit();

    } else {
        $SESSION->pluginuninstallreturn = $pluginfo->get_return_url_after_uninstall($return);
        $progress = new progress_trace_buffer(new text_progress_trace(), false);
        $pluginman->uninstall_plugin($pluginfo->component, $progress);
        $progress->finished();

        if ($pluginman->is_plugin_folder_removable($pluginfo->component)) {
            $continueurl = new moodle_url($PAGE->url, array('delete' => $pluginfo->component, 'sesskey' => sesskey(), 'confirm' => 1));
            echo $output->plugin_uninstall_results_removable_page($pluginman, $pluginfo, $progress, $continueurl);
                        if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            exit();

        } else {
            echo $output->plugin_uninstall_results_page($pluginman, $pluginfo, $progress);
                        if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            exit();
        }
    }
}

if ($delete and $confirmed) {
    require_sesskey();

    $PAGE->set_url($pageurl);
    $PAGE->set_context($syscontext);
    $PAGE->set_pagelayout('maintenance');
    $PAGE->set_popup_notification_allowed(false);

    
    $output = $PAGE->get_renderer('core', 'admin');

    $pluginfo = $pluginman->get_plugin_info($delete);

        if (is_null($pluginfo)) {
        throw new moodle_exception('err_removing_unknown_plugin', 'core_plugin', '', array('plugin' => $delete),
            'core_plugin_manager::get_plugin_info() returned null for the plugin to be deleted');
    }

    $pluginname = $pluginman->plugin_name($pluginfo->component);
    $PAGE->set_title($pluginname);
    $PAGE->navbar->add(get_string('uninstalling', 'core_plugin', array('name' => $pluginname)));

        if (!is_null($pluginfo->versiondb)) {
        throw new moodle_exception('err_removing_installed_plugin', 'core_plugin', '',
            array('plugin' => $pluginfo->component, 'versiondb' => $pluginfo->versiondb),
            'core_plugin_manager::get_plugin_info() returned not-null versiondb for the plugin to be deleted');
    }

        if (strpos($pluginfo->rootdir, $CFG->dirroot) !== 0) {
        throw new moodle_exception('err_unexpected_plugin_rootdir', 'core_plugin', '',
            array('plugin' => $pluginfo->component, 'rootdir' => $pluginfo->rootdir, 'dirroot' => $CFG->dirroot),
            'plugin root folder not in the moodle dirroot');
    }

        $pluginman->remove_plugin_folder($pluginfo);

        redirect(new moodle_url('/admin/index.php'));
}

if ($installupdatex) {
    require_once($CFG->libdir.'/upgradelib.php');
    require_sesskey();

    $PAGE->set_url($pageurl);
    $PAGE->set_context($syscontext);
    $PAGE->set_pagelayout('maintenance');
    $PAGE->set_popup_notification_allowed(false);

    $installable = $pluginman->filter_installable($pluginman->available_updates());
    upgrade_install_plugins($installable, $confirminstallupdate,
        get_string('updateavailableinstallallhead', 'core_admin'),
        new moodle_url($PAGE->url, array('installupdatex' => 1, 'confirminstallupdate' => 1))
    );
}

if ($installupdate and $installupdateversion) {
    require_once($CFG->libdir.'/upgradelib.php');
    require_sesskey();

    $PAGE->set_url($pageurl);
    $PAGE->set_context($syscontext);
    $PAGE->set_pagelayout('maintenance');
    $PAGE->set_popup_notification_allowed(false);

    if ($pluginman->is_remote_plugin_installable($installupdate, $installupdateversion)) {
        $installable = array($pluginman->get_remote_plugin_info($installupdate, $installupdateversion, true));
        upgrade_install_plugins($installable, $confirminstallupdate,
            get_string('updateavailableinstallallhead', 'core_admin'),
            new moodle_url($PAGE->url, array('installupdate' => $installupdate,
                'installupdateversion' => $installupdateversion, 'confirminstallupdate' => 1)
            )
        );
    }
}

admin_externalpage_setup('pluginsoverview', '', $pageparams);


$output = $PAGE->get_renderer('core', 'admin');

$checker = \core\update\checker::instance();

if ($fetchupdates) {
    require_sesskey();
    $checker->fetch();
    redirect($PAGE->url);
}

echo $output->plugin_management_page($pluginman, $checker, $pageparams);
