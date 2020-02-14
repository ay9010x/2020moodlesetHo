<?php




defined('MOODLE_INTERNAL') || die();


define('UPGRADE_LOG_NORMAL', 0);

define('UPGRADE_LOG_NOTICE', 1);

define('UPGRADE_LOG_ERROR',  2);


class upgrade_exception extends moodle_exception {
    function __construct($plugin, $version, $debuginfo=NULL) {
        global $CFG;
        $a = (object)array('plugin'=>$plugin, 'version'=>$version);
        parent::__construct('upgradeerror', 'admin', "$CFG->wwwroot/$CFG->admin/index.php", $a, $debuginfo);
    }
}


class downgrade_exception extends moodle_exception {
    function __construct($plugin, $oldversion, $newversion) {
        global $CFG;
        $plugin = is_null($plugin) ? 'moodle' : $plugin;
        $a = (object)array('plugin'=>$plugin, 'oldversion'=>$oldversion, 'newversion'=>$newversion);
        parent::__construct('cannotdowngrade', 'debug', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }
}


class upgrade_requires_exception extends moodle_exception {
    function __construct($plugin, $pluginversion, $currentmoodle, $requiremoodle) {
        global $CFG;
        $a = new stdClass();
        $a->pluginname     = $plugin;
        $a->pluginversion  = $pluginversion;
        $a->currentmoodle  = $currentmoodle;
        $a->requiremoodle  = $requiremoodle;
        parent::__construct('pluginrequirementsnotmet', 'error', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }
}


class plugin_defective_exception extends moodle_exception {
    function __construct($plugin, $details) {
        global $CFG;
        parent::__construct('detectedbrokenplugin', 'error', "$CFG->wwwroot/$CFG->admin/index.php", $plugin, $details);
    }
}


class plugin_misplaced_exception extends moodle_exception {
    
    public function __construct($component, $expected, $current) {
        global $CFG;
        if (empty($expected)) {
            list($type, $plugin) = core_component::normalize_component($component);
            $plugintypes = core_component::get_plugin_types();
            if (isset($plugintypes[$type])) {
                $expected = $plugintypes[$type] . '/' . $plugin;
            }
        }
        if (strpos($expected, '$CFG->dirroot') !== 0) {
            $expected = str_replace($CFG->dirroot, '$CFG->dirroot', $expected);
        }
        if (strpos($current, '$CFG->dirroot') !== 0) {
            $current = str_replace($CFG->dirroot, '$CFG->dirroot', $current);
        }
        $a = new stdClass();
        $a->component = $component;
        $a->expected  = $expected;
        $a->current   = $current;
        parent::__construct('detectedmisplacedplugin', 'core_plugin', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }
}


function upgrade_set_timeout($max_execution_time=300) {
    global $CFG;

    if (!isset($CFG->upgraderunning) or $CFG->upgraderunning < time()) {
        $upgraderunning = get_config(null, 'upgraderunning');
    } else {
        $upgraderunning = $CFG->upgraderunning;
    }

    if (!$upgraderunning) {
        if (CLI_SCRIPT) {
                        $upgraderunning = 0;
        } else {
                        print_error('upgradetimedout', 'admin', "$CFG->wwwroot/$CFG->admin/");
        }
    }

    if ($max_execution_time < 60) {
                $max_execution_time = 60;
    }

    $expected_end = time() + $max_execution_time;

    if ($expected_end < $upgraderunning + 10 and $expected_end > $upgraderunning - 10) {
                return;
    }

    if (CLI_SCRIPT) {
                core_php_time_limit::raise();
    } else {
        core_php_time_limit::raise($max_execution_time);
    }
    set_config('upgraderunning', $expected_end); }


function upgrade_main_savepoint($result, $version, $allowabort=true) {
    global $CFG;

        if (!is_bool($allowabort)) {
        $errormessage = 'Parameter type mismatch. Are you mixing up upgrade_main_savepoint() and upgrade_mod_savepoint()?';
        throw new coding_exception($errormessage);
    }

    if (!$result) {
        throw new upgrade_exception(null, $version);
    }

    if ($CFG->version >= $version) {
                throw new downgrade_exception(null, $CFG->version, $version);
    }

    set_config('version', $version);
    upgrade_log(UPGRADE_LOG_NORMAL, null, 'Upgrade savepoint reached');

        upgrade_set_timeout();

        if ($allowabort and connection_aborted()) {
        die;
    }
}


function upgrade_mod_savepoint($result, $version, $modname, $allowabort=true) {
    global $DB;

    $component = 'mod_'.$modname;

    if (!$result) {
        throw new upgrade_exception($component, $version);
    }

    $dbversion = $DB->get_field('config_plugins', 'value', array('plugin'=>$component, 'name'=>'version'));

    if (!$module = $DB->get_record('modules', array('name'=>$modname))) {
        print_error('modulenotexist', 'debug', '', $modname);
    }

    if ($dbversion >= $version) {
                throw new downgrade_exception($component, $dbversion, $version);
    }
    set_config('version', $version, $component);

    upgrade_log(UPGRADE_LOG_NORMAL, $component, 'Upgrade savepoint reached');

        upgrade_set_timeout();

        if ($allowabort and connection_aborted()) {
        die;
    }
}


function upgrade_block_savepoint($result, $version, $blockname, $allowabort=true) {
    global $DB;

    $component = 'block_'.$blockname;

    if (!$result) {
        throw new upgrade_exception($component, $version);
    }

    $dbversion = $DB->get_field('config_plugins', 'value', array('plugin'=>$component, 'name'=>'version'));

    if (!$block = $DB->get_record('block', array('name'=>$blockname))) {
        print_error('blocknotexist', 'debug', '', $blockname);
    }

    if ($dbversion >= $version) {
                throw new downgrade_exception($component, $dbversion, $version);
    }
    set_config('version', $version, $component);

    upgrade_log(UPGRADE_LOG_NORMAL, $component, 'Upgrade savepoint reached');

        upgrade_set_timeout();

        if ($allowabort and connection_aborted()) {
        die;
    }
}


function upgrade_plugin_savepoint($result, $version, $type, $plugin, $allowabort=true) {
    global $DB;

    $component = $type.'_'.$plugin;

    if (!$result) {
        throw new upgrade_exception($component, $version);
    }

    $dbversion = $DB->get_field('config_plugins', 'value', array('plugin'=>$component, 'name'=>'version'));

    if ($dbversion >= $version) {
                throw new downgrade_exception($component, $dbversion, $version);
    }
    set_config('version', $version, $component);
    upgrade_log(UPGRADE_LOG_NORMAL, $component, 'Upgrade savepoint reached');

        upgrade_set_timeout();

        if ($allowabort and connection_aborted()) {
        die;
    }
}


function upgrade_stale_php_files_present() {
    global $CFG;

    $someexamplesofremovedfiles = array(
                '/lib/classes/log/sql_internal_reader.php',
        '/lib/zend/',
        '/mod/forum/pix/icon.gif',
        '/tag/templates/tagname.mustache',
                '/mod/lti/grade.php',
        '/tag/coursetagslib.php',
                '/lib/timezone.txt',
                '/course/delete_category_form.php',
                '/admin/tool/qeupgradehelper/version.php',
                '/admin/block.php',
        '/admin/oacleanup.php',
                '/backup/lib.php',
        '/backup/bb/README.txt',
        '/lib/excel/test.php',
                '/admin/tool/unittest/simpletestlib.php',
                '/lib/minify/builder/',
                '/lib/yui/3.4.1pr1/',
                '/search/cron_php5.php',
        '/course/report/log/indexlive.php',
        '/admin/report/backups/index.php',
        '/admin/generator.php',
                '/lib/yui/2.8.0r4/',
                '/blocks/admin/block_admin.php',
        '/blocks/admin_tree/block_admin_tree.php',
    );

    foreach ($someexamplesofremovedfiles as $file) {
        if (file_exists($CFG->dirroot.$file)) {
            return true;
        }
    }

    return false;
}


function upgrade_plugins($type, $startcallback, $endcallback, $verbose) {
    global $CFG, $DB;

    if ($type === 'mod') {
        return upgrade_plugins_modules($startcallback, $endcallback, $verbose);
    } else if ($type === 'block') {
        return upgrade_plugins_blocks($startcallback, $endcallback, $verbose);
    }

    $plugs = core_component::get_plugin_list($type);

    foreach ($plugs as $plug=>$fullplug) {
                core_php_time_limit::raise(600);
        $component = clean_param($type.'_'.$plug, PARAM_COMPONENT); 
                if (empty($component)) {
            throw new plugin_defective_exception($type.'_'.$plug, 'Invalid plugin directory name.');
        }

        if (!is_readable($fullplug.'/version.php')) {
            continue;
        }

        $plugin = new stdClass();
        $plugin->version = null;
        $module = $plugin;         require($fullplug.'/version.php');          unset($module);

        if (empty($plugin->version)) {
            throw new plugin_defective_exception($component, 'Missing $plugin->version number in version.php.');
        }

        if (empty($plugin->component)) {
            throw new plugin_defective_exception($component, 'Missing $plugin->component declaration in version.php.');
        }

        if ($plugin->component !== $component) {
            throw new plugin_misplaced_exception($plugin->component, null, $fullplug);
        }

        $plugin->name     = $plug;
        $plugin->fullname = $component;

        if (!empty($plugin->requires)) {
            if ($plugin->requires > $CFG->version) {
                throw new upgrade_requires_exception($component, $plugin->version, $CFG->version, $plugin->requires);
            } else if ($plugin->requires < 2010000000) {
                throw new plugin_defective_exception($component, 'Plugin is not compatible with Moodle 2.x or later.');
            }
        }

                if (file_exists($fullplug.'/db/install.php')) {
            if (get_config($plugin->fullname, 'installrunning')) {
                require_once($fullplug.'/db/install.php');
                $recover_install_function = 'xmldb_'.$plugin->fullname.'_install_recovery';
                if (function_exists($recover_install_function)) {
                    $startcallback($component, true, $verbose);
                    $recover_install_function();
                    unset_config('installrunning', $plugin->fullname);
                    update_capabilities($component);
                    log_update_descriptions($component);
                    external_update_descriptions($component);
                    events_update_definition($component);
                    \core\task\manager::reset_scheduled_tasks_for_component($component);
                    message_update_providers($component);
                    \core\message\inbound\manager::update_handlers_for_component($component);
                    if ($type === 'message') {
                        message_update_processors($plug);
                    }
                    upgrade_plugin_mnet_functions($component);
                    core_tag_area::reset_definitions_for_component($component);
                    $endcallback($component, true, $verbose);
                }
            }
        }

        $installedversion = $DB->get_field('config_plugins', 'value', array('name'=>'version', 'plugin'=>$component));         if (empty($installedversion)) {             $startcallback($component, true, $verbose);

                    if (file_exists($fullplug.'/db/install.xml')) {
                $DB->get_manager()->install_from_xmldb_file($fullplug.'/db/install.xml');
            }

                    upgrade_plugin_savepoint(true, $plugin->version, $type, $plug, false);

                    if (file_exists($fullplug.'/db/install.php')) {
                require_once($fullplug.'/db/install.php');
                set_config('installrunning', 1, $plugin->fullname);
                $post_install_function = 'xmldb_'.$plugin->fullname.'_install';
                $post_install_function();
                unset_config('installrunning', $plugin->fullname);
            }

                    update_capabilities($component);
            log_update_descriptions($component);
            external_update_descriptions($component);
            events_update_definition($component);
            \core\task\manager::reset_scheduled_tasks_for_component($component);
            message_update_providers($component);
            \core\message\inbound\manager::update_handlers_for_component($component);
            if ($type === 'message') {
                message_update_processors($plug);
            }
            upgrade_plugin_mnet_functions($component);
            core_tag_area::reset_definitions_for_component($component);
            $endcallback($component, true, $verbose);

        } else if ($installedversion < $plugin->version) {                     $startcallback($component, false, $verbose);

            if (is_readable($fullplug.'/db/upgrade.php')) {
                require_once($fullplug.'/db/upgrade.php');  
                $newupgrade_function = 'xmldb_'.$plugin->fullname.'_upgrade';
                $result = $newupgrade_function($installedversion);
            } else {
                $result = true;
            }

            $installedversion = $DB->get_field('config_plugins', 'value', array('name'=>'version', 'plugin'=>$component));             if ($installedversion < $plugin->version) {
                                upgrade_plugin_savepoint($result, $plugin->version, $type, $plug, false);
            }

                    update_capabilities($component);
            log_update_descriptions($component);
            external_update_descriptions($component);
            events_update_definition($component);
            \core\task\manager::reset_scheduled_tasks_for_component($component);
            message_update_providers($component);
            \core\message\inbound\manager::update_handlers_for_component($component);
            if ($type === 'message') {
                                message_update_processors($plug);
            }
            upgrade_plugin_mnet_functions($component);
            core_tag_area::reset_definitions_for_component($component);
            $endcallback($component, false, $verbose);

        } else if ($installedversion > $plugin->version) {
            throw new downgrade_exception($component, $installedversion, $plugin->version);
        }
    }
}


function upgrade_plugins_modules($startcallback, $endcallback, $verbose) {
    global $CFG, $DB;

    $mods = core_component::get_plugin_list('mod');

    foreach ($mods as $mod=>$fullmod) {

        if ($mod === 'NEWMODULE') {               continue;
        }

        $component = clean_param('mod_'.$mod, PARAM_COMPONENT);

                if (empty($component)) {
            throw new plugin_defective_exception('mod_'.$mod, 'Invalid plugin directory name.');
        }

        if (!is_readable($fullmod.'/version.php')) {
            throw new plugin_defective_exception($component, 'Missing version.php');
        }

        $module = new stdClass();
        $plugin = new stdClass();
        $plugin->version = null;
        require($fullmod .'/version.php');  
                if (!is_object($module) or (count((array)$module) > 0)) {
            throw new plugin_defective_exception($component, 'Unsupported $module syntax detected in version.php');
        }

                $module = clone($plugin);
        unset($module->version);
        unset($module->component);
        unset($module->dependencies);
        unset($module->release);

        if (empty($plugin->version)) {
            throw new plugin_defective_exception($component, 'Missing $plugin->version number in version.php.');
        }

        if (empty($plugin->component)) {
            throw new plugin_defective_exception($component, 'Missing $plugin->component declaration in version.php.');
        }

        if ($plugin->component !== $component) {
            throw new plugin_misplaced_exception($plugin->component, null, $fullmod);
        }

        if (!empty($plugin->requires)) {
            if ($plugin->requires > $CFG->version) {
                throw new upgrade_requires_exception($component, $plugin->version, $CFG->version, $plugin->requires);
            } else if ($plugin->requires < 2010000000) {
                throw new plugin_defective_exception($component, 'Plugin is not compatible with Moodle 2.x or later.');
            }
        }

        if (empty($module->cron)) {
            $module->cron = 0;
        }

                if (!is_readable("$fullmod/lang/en/$mod.php")) {
            throw new plugin_defective_exception($component, 'Missing mandatory en language pack.');
        }

        $module->name = $mod;   
        $installedversion = $DB->get_field('config_plugins', 'value', array('name'=>'version', 'plugin'=>$component)); 
        if (file_exists($fullmod.'/db/install.php')) {
            if (get_config($module->name, 'installrunning')) {
                require_once($fullmod.'/db/install.php');
                $recover_install_function = 'xmldb_'.$module->name.'_install_recovery';
                if (function_exists($recover_install_function)) {
                    $startcallback($component, true, $verbose);
                    $recover_install_function();
                    unset_config('installrunning', $module->name);
                                        update_capabilities($component);
                    log_update_descriptions($component);
                    external_update_descriptions($component);
                    events_update_definition($component);
                    \core\task\manager::reset_scheduled_tasks_for_component($component);
                    message_update_providers($component);
                    \core\message\inbound\manager::update_handlers_for_component($component);
                    upgrade_plugin_mnet_functions($component);
                    core_tag_area::reset_definitions_for_component($component);
                    $endcallback($component, true, $verbose);
                }
            }
        }

        if (empty($installedversion)) {
            $startcallback($component, true, $verbose);

                    $DB->get_manager()->install_from_xmldb_file($fullmod.'/db/install.xml');

                    $module->id = $DB->insert_record('modules', $module);
            upgrade_mod_savepoint(true, $plugin->version, $module->name, false);

                    if (file_exists("$fullmod/db/install.php")) {
                require_once("$fullmod/db/install.php");
                                set_config('installrunning', 1, $module->name);
                $post_install_function = 'xmldb_'.$module->name.'_install';
                $post_install_function();
                unset_config('installrunning', $module->name);
            }
                    update_capabilities($component);
            log_update_descriptions($component);
            external_update_descriptions($component);
            events_update_definition($component);
            \core\task\manager::reset_scheduled_tasks_for_component($component);
            message_update_providers($component);
            \core\message\inbound\manager::update_handlers_for_component($component);
            upgrade_plugin_mnet_functions($component);
            core_tag_area::reset_definitions_for_component($component);

            $endcallback($component, true, $verbose);

        } else if ($installedversion < $plugin->version) {
                    $startcallback($component, false, $verbose);

            if (is_readable($fullmod.'/db/upgrade.php')) {
                require_once($fullmod.'/db/upgrade.php');                  $newupgrade_function = 'xmldb_'.$module->name.'_upgrade';
                $result = $newupgrade_function($installedversion, $module);
            } else {
                $result = true;
            }

            $installedversion = $DB->get_field('config_plugins', 'value', array('name'=>'version', 'plugin'=>$component));             $currmodule = $DB->get_record('modules', array('name'=>$module->name));
            if ($installedversion < $plugin->version) {
                                upgrade_mod_savepoint($result, $plugin->version, $mod, false);
            }

                        if ($currmodule->cron != $module->cron) {
                $DB->set_field('modules', 'cron', $module->cron, array('name' => $module->name));
            }

                        update_capabilities($component);
            log_update_descriptions($component);
            external_update_descriptions($component);
            events_update_definition($component);
            \core\task\manager::reset_scheduled_tasks_for_component($component);
            message_update_providers($component);
            \core\message\inbound\manager::update_handlers_for_component($component);
            upgrade_plugin_mnet_functions($component);
            core_tag_area::reset_definitions_for_component($component);

            $endcallback($component, false, $verbose);

        } else if ($installedversion > $plugin->version) {
            throw new downgrade_exception($component, $installedversion, $plugin->version);
        }
    }
}



function upgrade_plugins_blocks($startcallback, $endcallback, $verbose) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/blocks/moodleblock.class.php');

    $blocktitles   = array(); 
        $first_install = null;

    $blocks = core_component::get_plugin_list('block');

    foreach ($blocks as $blockname=>$fullblock) {

        if (is_null($first_install)) {
            $first_install = ($DB->count_records('block_instances') == 0);
        }

        if ($blockname === 'NEWBLOCK') {               continue;
        }

        $component = clean_param('block_'.$blockname, PARAM_COMPONENT);

                if (empty($component)) {
            throw new plugin_defective_exception('block_'.$blockname, 'Invalid plugin directory name.');
        }

        if (!is_readable($fullblock.'/version.php')) {
            throw new plugin_defective_exception('block/'.$blockname, 'Missing version.php file.');
        }
        $plugin = new stdClass();
        $plugin->version = null;
        $plugin->cron    = 0;
        $module = $plugin;         include($fullblock.'/version.php');
        unset($module);
        $block = clone($plugin);
        unset($block->version);
        unset($block->component);
        unset($block->dependencies);
        unset($block->release);

        if (empty($plugin->version)) {
            throw new plugin_defective_exception($component, 'Missing block version number in version.php.');
        }

        if (empty($plugin->component)) {
            throw new plugin_defective_exception($component, 'Missing $plugin->component declaration in version.php.');
        }

        if ($plugin->component !== $component) {
            throw new plugin_misplaced_exception($plugin->component, null, $fullblock);
        }

        if (!empty($plugin->requires)) {
            if ($plugin->requires > $CFG->version) {
                throw new upgrade_requires_exception($component, $plugin->version, $CFG->version, $plugin->requires);
            } else if ($plugin->requires < 2010000000) {
                throw new plugin_defective_exception($component, 'Plugin is not compatible with Moodle 2.x or later.');
            }
        }

        if (!is_readable($fullblock.'/block_'.$blockname.'.php')) {
            throw new plugin_defective_exception('block/'.$blockname, 'Missing main block class file.');
        }
        include_once($fullblock.'/block_'.$blockname.'.php');

        $classname = 'block_'.$blockname;

        if (!class_exists($classname)) {
            throw new plugin_defective_exception($component, 'Can not load main class.');
        }

        $blockobj    = new $classname;           $blocktitle  = $blockobj->get_title();

                if (!$blockobj->_self_test()) {
            throw new plugin_defective_exception($component, 'Self test failed.');
        }

        $block->name     = $blockname;   
        $installedversion = $DB->get_field('config_plugins', 'value', array('name'=>'version', 'plugin'=>$component)); 
        if (file_exists($fullblock.'/db/install.php')) {
            if (get_config('block_'.$blockname, 'installrunning')) {
                require_once($fullblock.'/db/install.php');
                $recover_install_function = 'xmldb_block_'.$blockname.'_install_recovery';
                if (function_exists($recover_install_function)) {
                    $startcallback($component, true, $verbose);
                    $recover_install_function();
                    unset_config('installrunning', 'block_'.$blockname);
                                        update_capabilities($component);
                    log_update_descriptions($component);
                    external_update_descriptions($component);
                    events_update_definition($component);
                    \core\task\manager::reset_scheduled_tasks_for_component($component);
                    message_update_providers($component);
                    \core\message\inbound\manager::update_handlers_for_component($component);
                    upgrade_plugin_mnet_functions($component);
                    core_tag_area::reset_definitions_for_component($component);
                    $endcallback($component, true, $verbose);
                }
            }
        }

        if (empty($installedversion)) {             $conflictblock = array_search($blocktitle, $blocktitles);
            if ($conflictblock !== false) {
                                                throw new plugin_defective_exception($component, get_string('blocknameconflict', 'error', (object)array('name'=>$block->name, 'conflict'=>$conflictblock)));
            }
            $startcallback($component, true, $verbose);

            if (file_exists($fullblock.'/db/install.xml')) {
                $DB->get_manager()->install_from_xmldb_file($fullblock.'/db/install.xml');
            }
            $block->id = $DB->insert_record('block', $block);
            upgrade_block_savepoint(true, $plugin->version, $block->name, false);

            if (file_exists($fullblock.'/db/install.php')) {
                require_once($fullblock.'/db/install.php');
                                set_config('installrunning', 1, 'block_'.$blockname);
                $post_install_function = 'xmldb_block_'.$blockname.'_install';
                $post_install_function();
                unset_config('installrunning', 'block_'.$blockname);
            }

            $blocktitles[$block->name] = $blocktitle;

                        update_capabilities($component);
            log_update_descriptions($component);
            external_update_descriptions($component);
            events_update_definition($component);
            \core\task\manager::reset_scheduled_tasks_for_component($component);
            message_update_providers($component);
            \core\message\inbound\manager::update_handlers_for_component($component);
            core_tag_area::reset_definitions_for_component($component);
            upgrade_plugin_mnet_functions($component);

            $endcallback($component, true, $verbose);

        } else if ($installedversion < $plugin->version) {
            $startcallback($component, false, $verbose);

            if (is_readable($fullblock.'/db/upgrade.php')) {
                require_once($fullblock.'/db/upgrade.php');                  $newupgrade_function = 'xmldb_block_'.$blockname.'_upgrade';
                $result = $newupgrade_function($installedversion, $block);
            } else {
                $result = true;
            }

            $installedversion = $DB->get_field('config_plugins', 'value', array('name'=>'version', 'plugin'=>$component));             $currblock = $DB->get_record('block', array('name'=>$block->name));
            if ($installedversion < $plugin->version) {
                                upgrade_block_savepoint($result, $plugin->version, $block->name, false);
            }

            if ($currblock->cron != $block->cron) {
                                $DB->set_field('block', 'cron', $block->cron, array('id' => $currblock->id));
            }

                        update_capabilities($component);
            log_update_descriptions($component);
            external_update_descriptions($component);
            events_update_definition($component);
            \core\task\manager::reset_scheduled_tasks_for_component($component);
            message_update_providers($component);
            \core\message\inbound\manager::update_handlers_for_component($component);
            upgrade_plugin_mnet_functions($component);
            core_tag_area::reset_definitions_for_component($component);

            $endcallback($component, false, $verbose);

        } else if ($installedversion > $plugin->version) {
            throw new downgrade_exception($component, $installedversion, $plugin->version);
        }
    }


        if ($first_install) {
                if ($courses = $DB->get_records('course')) {
            foreach ($courses as $course) {
                blocks_add_default_course_blocks($course);
            }
        }

        blocks_add_default_system_blocks();
    }
}



function log_update_descriptions($component) {
    global $DB;

    $defpath = core_component::get_component_directory($component).'/db/log.php';

    if (!file_exists($defpath)) {
        $DB->delete_records('log_display', array('component'=>$component));
        return;
    }

        $logs = array();
    include($defpath);
    $newlogs = array();
    foreach ($logs as $log) {
        $newlogs[$log['module'].'-'.$log['action']] = $log;     }
    unset($logs);
    $logs = $newlogs;

    $fields = array('module', 'action', 'mtable', 'field');
        $dblogs = $DB->get_records('log_display', array('component'=>$component));
    foreach ($dblogs as $dblog) {
        $name = $dblog->module.'-'.$dblog->action;

        if (empty($logs[$name])) {
            $DB->delete_records('log_display', array('id'=>$dblog->id));
            continue;
        }

        $log = $logs[$name];
        unset($logs[$name]);

        $update = false;
        foreach ($fields as $field) {
            if ($dblog->$field != $log[$field]) {
                $dblog->$field = $log[$field];
                $update = true;
            }
        }
        if ($update) {
            $DB->update_record('log_display', $dblog);
        }
    }
    foreach ($logs as $log) {
        $dblog = (object)$log;
        $dblog->component = $component;
        $DB->insert_record('log_display', $dblog);
    }
}


function external_update_descriptions($component) {
    global $DB, $CFG;

    $defpath = core_component::get_component_directory($component).'/db/services.php';

    if (!file_exists($defpath)) {
        require_once($CFG->dirroot.'/lib/externallib.php');
        external_delete_descriptions($component);
        return;
    }
        $functions = array();
    $services = array();
    include($defpath);

        $dbfunctions = $DB->get_records('external_functions', array('component'=>$component));
    foreach ($dbfunctions as $dbfunction) {
        if (empty($functions[$dbfunction->name])) {
            $DB->delete_records('external_functions', array('id'=>$dbfunction->id));
                        
                        continue;
        }

        $function = $functions[$dbfunction->name];
        unset($functions[$dbfunction->name]);
        $function['classpath'] = empty($function['classpath']) ? null : $function['classpath'];

        $update = false;
        if ($dbfunction->classname != $function['classname']) {
            $dbfunction->classname = $function['classname'];
            $update = true;
        }
        if ($dbfunction->methodname != $function['methodname']) {
            $dbfunction->methodname = $function['methodname'];
            $update = true;
        }
        if ($dbfunction->classpath != $function['classpath']) {
            $dbfunction->classpath = $function['classpath'];
            $update = true;
        }
        $functioncapabilities = array_key_exists('capabilities', $function)?$function['capabilities']:'';
        if ($dbfunction->capabilities != $functioncapabilities) {
            $dbfunction->capabilities = $functioncapabilities;
            $update = true;
        }

        if (isset($function['services']) and is_array($function['services'])) {
            sort($function['services']);
            $functionservices = implode(',', $function['services']);
        } else {
                        $functionservices = null;
        }

        if ($dbfunction->services != $functionservices) {
                        $servicesremoved = array_diff(explode(",", $dbfunction->services), explode(",", $functionservices));
            foreach ($servicesremoved as $removedshortname) {
                if ($externalserviceid = $DB->get_field('external_services', 'id', array("shortname" => $removedshortname))) {
                    $DB->delete_records('external_services_functions', array('functionname' => $dbfunction->name,
                                                                                'externalserviceid' => $externalserviceid));
                }
            }

            $dbfunction->services = $functionservices;
            $update = true;
        }
        if ($update) {
            $DB->update_record('external_functions', $dbfunction);
        }
    }
    foreach ($functions as $fname => $function) {
        $dbfunction = new stdClass();
        $dbfunction->name       = $fname;
        $dbfunction->classname  = $function['classname'];
        $dbfunction->methodname = $function['methodname'];
        $dbfunction->classpath  = empty($function['classpath']) ? null : $function['classpath'];
        $dbfunction->component  = $component;
        $dbfunction->capabilities = array_key_exists('capabilities', $function)?$function['capabilities']:'';

        if (isset($function['services']) and is_array($function['services'])) {
            sort($function['services']);
            $dbfunction->services = implode(',', $function['services']);
        } else {
                        $dbfunction->services = null;
        }

        $dbfunction->id = $DB->insert_record('external_functions', $dbfunction);
    }
    unset($functions);

        $dbservices = $DB->get_records('external_services', array('component'=>$component));
    foreach ($dbservices as $dbservice) {
        if (empty($services[$dbservice->name])) {
            $DB->delete_records('external_tokens', array('externalserviceid'=>$dbservice->id));
            $DB->delete_records('external_services_functions', array('externalserviceid'=>$dbservice->id));
            $DB->delete_records('external_services_users', array('externalserviceid'=>$dbservice->id));
            $DB->delete_records('external_services', array('id'=>$dbservice->id));
            continue;
        }
        $service = $services[$dbservice->name];
        unset($services[$dbservice->name]);
        $service['enabled'] = empty($service['enabled']) ? 0 : $service['enabled'];
        $service['requiredcapability'] = empty($service['requiredcapability']) ? null : $service['requiredcapability'];
        $service['restrictedusers'] = !isset($service['restrictedusers']) ? 1 : $service['restrictedusers'];
        $service['downloadfiles'] = !isset($service['downloadfiles']) ? 0 : $service['downloadfiles'];
        $service['uploadfiles'] = !isset($service['uploadfiles']) ? 0 : $service['uploadfiles'];
        $service['shortname'] = !isset($service['shortname']) ? null : $service['shortname'];

        $update = false;
        if ($dbservice->requiredcapability != $service['requiredcapability']) {
            $dbservice->requiredcapability = $service['requiredcapability'];
            $update = true;
        }
        if ($dbservice->restrictedusers != $service['restrictedusers']) {
            $dbservice->restrictedusers = $service['restrictedusers'];
            $update = true;
        }
        if ($dbservice->downloadfiles != $service['downloadfiles']) {
            $dbservice->downloadfiles = $service['downloadfiles'];
            $update = true;
        }
        if ($dbservice->uploadfiles != $service['uploadfiles']) {
            $dbservice->uploadfiles = $service['uploadfiles'];
            $update = true;
        }
                if (isset($service['shortname']) and
                (clean_param($service['shortname'], PARAM_ALPHANUMEXT) != $service['shortname'])) {
            throw new moodle_exception('installserviceshortnameerror', 'webservice', '', $service['shortname']);
        }
        if ($dbservice->shortname != $service['shortname']) {
                        if (isset($service['shortname'])) {                 $existingservice = $DB->get_record('external_services',
                        array('shortname' => $service['shortname']));
                if (!empty($existingservice)) {
                    throw new moodle_exception('installexistingserviceshortnameerror', 'webservice', '', $service['shortname']);
                }
            }
            $dbservice->shortname = $service['shortname'];
            $update = true;
        }
        if ($update) {
            $DB->update_record('external_services', $dbservice);
        }

        $functions = $DB->get_records('external_services_functions', array('externalserviceid'=>$dbservice->id));
        foreach ($functions as $function) {
            $key = array_search($function->functionname, $service['functions']);
            if ($key === false) {
                $DB->delete_records('external_services_functions', array('id'=>$function->id));
            } else {
                unset($service['functions'][$key]);
            }
        }
        foreach ($service['functions'] as $fname) {
            $newf = new stdClass();
            $newf->externalserviceid = $dbservice->id;
            $newf->functionname      = $fname;
            $DB->insert_record('external_services_functions', $newf);
        }
        unset($functions);
    }
    foreach ($services as $name => $service) {
                if (isset($service['shortname'])) {             $existingservice = $DB->get_record('external_services',
                    array('shortname' => $service['shortname']));
            if (!empty($existingservice)) {
                throw new moodle_exception('installserviceshortnameerror', 'webservice');
            }
        }

        $dbservice = new stdClass();
        $dbservice->name               = $name;
        $dbservice->enabled            = empty($service['enabled']) ? 0 : $service['enabled'];
        $dbservice->requiredcapability = empty($service['requiredcapability']) ? null : $service['requiredcapability'];
        $dbservice->restrictedusers    = !isset($service['restrictedusers']) ? 1 : $service['restrictedusers'];
        $dbservice->downloadfiles      = !isset($service['downloadfiles']) ? 0 : $service['downloadfiles'];
        $dbservice->uploadfiles        = !isset($service['uploadfiles']) ? 0 : $service['uploadfiles'];
        $dbservice->shortname          = !isset($service['shortname']) ? null : $service['shortname'];
        $dbservice->component          = $component;
        $dbservice->timecreated        = time();
        $dbservice->id = $DB->insert_record('external_services', $dbservice);
        foreach ($service['functions'] as $fname) {
            $newf = new stdClass();
            $newf->externalserviceid = $dbservice->id;
            $newf->functionname      = $fname;
            $DB->insert_record('external_services_functions', $newf);
        }
    }
}


function external_update_services() {
    global $DB;

        $functions = $DB->get_records_select('external_functions', 'services IS NOT NULL');
    $servicescache = array();
    foreach ($functions as $function) {
                if (empty($function->services)) {
            continue;
        }
        $services = explode(',', $function->services);

        foreach ($services as $serviceshortname) {
                        if (!empty($servicescache[$serviceshortname])) {
                $serviceid = $servicescache[$serviceshortname];
            } else if ($service = $DB->get_record('external_services', array('shortname' => $serviceshortname))) {
                                                if (empty($service->component)) {
                    continue;
                }
                $serviceid = $service->id;
                $servicescache[$serviceshortname] = $serviceid;
            } else {
                                continue;
            }
                        $newf = new stdClass();
            $newf->externalserviceid = $serviceid;
            $newf->functionname      = $function->name;

            if (!$DB->record_exists('external_services_functions', (array)$newf)) {
                $DB->insert_record('external_services_functions', $newf);
            }
        }
    }
}


function upgrade_handle_exception($ex, $plugin = null) {
    global $CFG;

        abort_all_db_transactions();

    $info = get_exception_info($ex);

        upgrade_log(UPGRADE_LOG_ERROR, $plugin, 'Exception: ' . get_class($ex), $info->message, $info->backtrace);

        set_debugging(DEBUG_DEVELOPER, true);

    default_exception_handler($ex, true, $plugin);
}


function upgrade_log($type, $plugin, $info, $details=null, $backtrace=null) {
    global $DB, $USER, $CFG;

    if (empty($plugin)) {
        $plugin = 'core';
    }

    list($plugintype, $pluginname) = core_component::normalize_component($plugin);
    $component = is_null($pluginname) ? $plugintype : $plugintype . '_' . $pluginname;

    $backtrace = format_backtrace($backtrace, true);

    $currentversion = null;
    $targetversion  = null;

        if ($plugintype === 'core') {
                $currentversion = $CFG->version;

        $version = null;
        include("$CFG->dirroot/version.php");
        $targetversion = $version;

    } else {
        $pluginversion = get_config($component, 'version');
        if (!empty($pluginversion)) {
            $currentversion = $pluginversion;
        }
        $cd = core_component::get_component_directory($component);
        if (file_exists("$cd/version.php")) {
            $plugin = new stdClass();
            $plugin->version = null;
            $module = $plugin;
            include("$cd/version.php");
            $targetversion = $plugin->version;
        }
    }

    $log = new stdClass();
    $log->type          = $type;
    $log->plugin        = $component;
    $log->version       = $currentversion;
    $log->targetversion = $targetversion;
    $log->info          = $info;
    $log->details       = $details;
    $log->backtrace     = $backtrace;
    $log->userid        = $USER->id;
    $log->timemodified  = time();
    try {
        $DB->insert_record('upgrade_log', $log);
    } catch (Exception $ignored) {
            }
}


function upgrade_started($preinstall=false) {
    global $CFG, $DB, $PAGE, $OUTPUT;

    static $started = false;

    if ($preinstall) {
        ignore_user_abort(true);
        upgrade_setup_debug(true);

    } else if ($started) {
        upgrade_set_timeout(120);

    } else {
        if (!CLI_SCRIPT and !$PAGE->headerprinted) {
            $strupgrade  = get_string('upgradingversion', 'admin');
            $PAGE->set_pagelayout('maintenance');
            upgrade_init_javascript();
            $PAGE->set_title($strupgrade.' - Moodle '.$CFG->target_release);
            $PAGE->set_heading($strupgrade);
            $PAGE->navbar->add($strupgrade);
            $PAGE->set_cacheable(false);
            echo $OUTPUT->header();
        }

        ignore_user_abort(true);
        core_shutdown_manager::register_function('upgrade_finished_handler');
        upgrade_setup_debug(true);
        set_config('upgraderunning', time()+300);
        $started = true;
    }
}


function upgrade_finished_handler() {
    upgrade_finished();
}


function upgrade_finished($continueurl=null) {
    global $CFG, $DB, $OUTPUT;

    if (!empty($CFG->upgraderunning)) {
        unset_config('upgraderunning');
                                                cache_helper::purge_all(true);
        upgrade_setup_debug(false);
        ignore_user_abort(false);
        if ($continueurl) {
            echo $OUTPUT->continue_button($continueurl);
            echo $OUTPUT->footer();
            die;
        }
    }
}


function upgrade_setup_debug($starting) {
    global $CFG, $DB;

    static $originaldebug = null;

    if ($starting) {
        if ($originaldebug === null) {
            $originaldebug = $DB->get_debug();
        }
        if (!empty($CFG->upgradeshowsql)) {
            $DB->set_debug(true);
        }
    } else {
        $DB->set_debug($originaldebug);
    }
}

function print_upgrade_separator() {
    if (!CLI_SCRIPT) {
        echo '<hr />';
    }
}


function print_upgrade_part_start($plugin, $installation, $verbose) {
    global $OUTPUT;
    if (empty($plugin) or $plugin == 'moodle') {
        upgrade_started($installation);         if ($verbose) {
            echo $OUTPUT->heading(get_string('coresystem'));
        }
    } else {
        upgrade_started();
        if ($verbose) {
            echo $OUTPUT->heading($plugin);
        }
    }
    if ($installation) {
        if (empty($plugin) or $plugin == 'moodle') {
                    } else {
            upgrade_log(UPGRADE_LOG_NORMAL, $plugin, 'Starting plugin installation');
        }
    } else {
        if (empty($plugin) or $plugin == 'moodle') {
            upgrade_log(UPGRADE_LOG_NORMAL, $plugin, 'Starting core upgrade');
        } else {
            upgrade_log(UPGRADE_LOG_NORMAL, $plugin, 'Starting plugin upgrade');
        }
    }
}


function print_upgrade_part_end($plugin, $installation, $verbose) {
    global $OUTPUT;
    upgrade_started();
    if ($installation) {
        if (empty($plugin) or $plugin == 'moodle') {
            upgrade_log(UPGRADE_LOG_NORMAL, $plugin, 'Core installed');
        } else {
            upgrade_log(UPGRADE_LOG_NORMAL, $plugin, 'Plugin installed');
        }
    } else {
        if (empty($plugin) or $plugin == 'moodle') {
            upgrade_log(UPGRADE_LOG_NORMAL, $plugin, 'Core upgraded');
        } else {
            upgrade_log(UPGRADE_LOG_NORMAL, $plugin, 'Plugin upgraded');
        }
    }
    if ($verbose) {
        $notification = new \core\output\notification(get_string('success'), \core\output\notification::NOTIFY_SUCCESS);
        $notification->set_show_closebutton(false);
        echo $OUTPUT->render($notification);
        print_upgrade_separator();
    }
}


function upgrade_init_javascript() {
    global $PAGE;
            $js = "window.scrollTo(0, 5000000);";
    $PAGE->requires->js_init_code($js);
}


function upgrade_language_pack($lang = null) {
    global $CFG;

    if (!empty($CFG->skiplangupgrade)) {
        return;
    }

    if (!file_exists("$CFG->dirroot/$CFG->admin/tool/langimport/lib.php")) {
                return;
    }

    if (!$lang) {
        $lang = current_language();
    }

    if (!get_string_manager()->translation_exists($lang)) {
        return;
    }

    get_string_manager()->reset_caches();

    if ($lang === 'en') {
        return;      }

    upgrade_started(false);

    require_once("$CFG->dirroot/$CFG->admin/tool/langimport/lib.php");
    tool_langimport_preupgrade_update($lang);

    get_string_manager()->reset_caches();

    print_upgrade_separator();
}


function install_core($version, $verbose) {
    global $CFG, $DB;

        remove_dir($CFG->cachedir.'', true);
    make_cache_directory('', true);

    remove_dir($CFG->localcachedir.'', true);
    make_localcache_directory('', true);

    remove_dir($CFG->tempdir.'', true);
    make_temp_directory('', true);

    remove_dir($CFG->dataroot.'/muc', true);
    make_writable_directory($CFG->dataroot.'/muc', true);

    try {
        core_php_time_limit::raise(600);
        print_upgrade_part_start('moodle', true, $verbose); 
        $DB->get_manager()->install_from_xmldb_file("$CFG->libdir/db/install.xml");
        upgrade_started();     
                require_once("$CFG->libdir/db/install.php");
        xmldb_main_install(); 
                upgrade_main_savepoint(true, $version, false);

                log_update_descriptions('moodle');
        external_update_descriptions('moodle');
                $modsetwsid = get_config('modset', 'modsetws');
        require_once($CFG->dirroot . "/webservice/lib.php");
        $webservicemanager = new webservice();
                $service = $webservicemanager->get_modset_service();
        $serviceuser = new stdClass();
        $serviceuser->externalserviceid = $service->id;
        $serviceuser->userid = $modsetwsid;
        $webservicemanager->add_ws_authorised_user($serviceuser);
        
                require_once($CFG->libdir . '/externallib.php');
        external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id, $modsetwsid, context_system::instance());
        
        events_update_definition('moodle');
        \core\task\manager::reset_scheduled_tasks_for_component('moodle');
        message_update_providers('moodle');
        \core\message\inbound\manager::update_handlers_for_component('moodle');
        core_tag_area::reset_definitions_for_component('moodle');

                admin_apply_default_settings(NULL, true);

        print_upgrade_part_end(null, true, $verbose);

                        cache_helper::purge_all();
    } catch (exception $ex) {
        upgrade_handle_exception($ex);
    } catch (Throwable $ex) {
                upgrade_handle_exception($ex);
    }
}


function upgrade_core($version, $verbose) {
    global $CFG, $SITE, $DB, $COURSE;

    raise_memory_limit(MEMORY_EXTRA);

    require_once($CFG->libdir.'/db/upgrade.php');    
    try {
                cache_helper::purge_all(true);
        purge_all_caches();

                upgrade_language_pack();

        print_upgrade_part_start('moodle', false, $verbose);

                $preupgradefile = "$CFG->dirroot/local/preupgrade.php";
        if (file_exists($preupgradefile)) {
            core_php_time_limit::raise();
            require($preupgradefile);
                        upgrade_set_timeout();
        }

        $result = xmldb_main_upgrade($CFG->version);
        if ($version > $CFG->version) {
                        upgrade_main_savepoint($result, $version, false);
        }

                $SITE = $DB->get_record('course', array('id' => $SITE->id));
        $COURSE = clone($SITE);

                update_capabilities('moodle');
        log_update_descriptions('moodle');
        external_update_descriptions('moodle');
        events_update_definition('moodle');
        \core\task\manager::reset_scheduled_tasks_for_component('moodle');
        message_update_providers('moodle');
        \core\message\inbound\manager::update_handlers_for_component('moodle');
        core_tag_area::reset_definitions_for_component('moodle');
                cache_helper::update_definitions(true);

                cache_helper::purge_all(true);
        purge_all_caches();

                context_helper::cleanup_instances();
        context_helper::create_instances(null, false);
        context_helper::build_all_paths(false);
        $syscontext = context_system::instance();
        $syscontext->mark_dirty();

        print_upgrade_part_end('moodle', false, $verbose);
    } catch (Exception $ex) {
        upgrade_handle_exception($ex);
    } catch (Throwable $ex) {
                upgrade_handle_exception($ex);
    }
}


function upgrade_noncore($verbose) {
    global $CFG;

    raise_memory_limit(MEMORY_EXTRA);

        try {
                cache_helper::purge_all(true);
        purge_all_caches();

        $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $type=>$location) {
            upgrade_plugins($type, 'print_upgrade_part_start', 'print_upgrade_part_end', $verbose);
        }
                        external_update_services();

                cache_helper::update_definitions();
                set_config('allversionshash', core_component::get_all_versions_hash());

                cache_helper::purge_all(true);
        purge_all_caches();

    } catch (Exception $ex) {
        upgrade_handle_exception($ex);
    } catch (Throwable $ex) {
                upgrade_handle_exception($ex);
    }
}


function core_tables_exist() {
    global $DB;

    if (!$tables = $DB->get_tables(false) ) {            return false;

    } else {                                         $mtables = array('config', 'course', 'groupings');         foreach ($mtables as $mtable) {
            if (!in_array($mtable, $tables)) {
                return false;
            }
        }
        return true;
    }
}


function upgrade_plugin_mnet_functions($component) {
    global $DB, $CFG;

    list($type, $plugin) = core_component::normalize_component($component);
    $path = core_component::get_plugin_directory($type, $plugin);

    $publishes = array();
    $subscribes = array();
    if (file_exists($path . '/db/mnet.php')) {
        require_once($path . '/db/mnet.php');     }
    if (empty($publishes)) {
        $publishes = array();     }
    if (empty($subscribes)) {
        $subscribes = array();     }

    static $servicecache = array();

        $publishmethodservices = array();
    $subscribemethodservices = array();
    foreach($publishes as $servicename => $service) {
        if (is_array($service['methods'])) {
            foreach($service['methods'] as $methodname) {
                $service['servicename'] = $servicename;
                $publishmethodservices[$methodname][] = $service;
            }
        }
    }

            foreach ($DB->get_records('mnet_rpc', array('pluginname'=>$plugin, 'plugintype'=>$type), 'functionname ASC ') as $rpc) {
        if (!array_key_exists($rpc->functionname, $publishmethodservices) && $rpc->enabled) {
            $DB->set_field('mnet_rpc', 'enabled', 0, array('id' => $rpc->id));
        } else if (array_key_exists($rpc->functionname, $publishmethodservices) && !$rpc->enabled) {
            $DB->set_field('mnet_rpc', 'enabled', 1, array('id' => $rpc->id));
        }
    }

        static $cachedclasses = array();     foreach ($publishes as $service => $data) {
        $f = $data['filename'];
        $c = $data['classname'];
        foreach ($data['methods'] as $method) {
            $dataobject = new stdClass();
            $dataobject->plugintype  = $type;
            $dataobject->pluginname  = $plugin;
            $dataobject->enabled     = 1;
            $dataobject->classname   = $c;
            $dataobject->filename    = $f;

            if (is_string($method)) {
                $dataobject->functionname = $method;

            } else if (is_array($method)) {                 $dataobject->functionname = $method['method'];
                $dataobject->classname     = $method['classname'];
                $dataobject->filename      = $method['filename'];
            }
            $dataobject->xmlrpcpath = $type.'/'.$plugin.'/'.$dataobject->filename.'/'.$method;
            $dataobject->static = false;

            require_once($path . '/' . $dataobject->filename);
            $functionreflect = null;             if (!empty($dataobject->classname)) {
                if (!class_exists($dataobject->classname)) {
                    throw new moodle_exception('installnosuchmethod', 'mnet', '', (object)array('method' => $dataobject->functionname, 'class' => $dataobject->classname));
                }
                $key = $dataobject->filename . '|' . $dataobject->classname;
                if (!array_key_exists($key, $cachedclasses)) {                     try {
                        $cachedclasses[$key] = new ReflectionClass($dataobject->classname);
                    } catch (ReflectionException $e) {                         throw new moodle_exception('installreflectionclasserror', 'mnet', '', (object)array('method' => $dataobject->functionname, 'class' => $dataobject->classname, 'error' => $e->getMessage()));
                    }
                }
                $r =& $cachedclasses[$key];
                if (!$r->hasMethod($dataobject->functionname)) {
                    throw new moodle_exception('installnosuchmethod', 'mnet', '', (object)array('method' => $dataobject->functionname, 'class' => $dataobject->classname));
                }
                $functionreflect = $r->getMethod($dataobject->functionname);
                $dataobject->static = (int)$functionreflect->isStatic();
            } else {
                if (!function_exists($dataobject->functionname)) {
                    throw new moodle_exception('installnosuchfunction', 'mnet', '', (object)array('method' => $dataobject->functionname, 'file' => $dataobject->filename));
                }
                try {
                    $functionreflect = new ReflectionFunction($dataobject->functionname);
                } catch (ReflectionException $e) {                     throw new moodle_exception('installreflectionfunctionerror', 'mnet', '', (object)array('method' => $dataobject->functionname, '' => $dataobject->filename, 'error' => $e->getMessage()));
                }
            }
            $dataobject->profile =  serialize(admin_mnet_method_profile($functionreflect));
            $dataobject->help = admin_mnet_method_get_help($functionreflect);

            if ($record_exists = $DB->get_record('mnet_rpc', array('xmlrpcpath'=>$dataobject->xmlrpcpath))) {
                $dataobject->id      = $record_exists->id;
                $dataobject->enabled = $record_exists->enabled;
                $DB->update_record('mnet_rpc', $dataobject);
            } else {
                $dataobject->id = $DB->insert_record('mnet_rpc', $dataobject, true);
            }

                                    foreach ($publishmethodservices[$dataobject->functionname] as $service) {
                if ($serviceobj = $DB->get_record('mnet_service', array('name'=>$service['servicename']))) {
                    $serviceobj->apiversion = $service['apiversion'];
                    $DB->update_record('mnet_service', $serviceobj);
                } else {
                    $serviceobj = new stdClass();
                    $serviceobj->name        = $service['servicename'];
                    $serviceobj->description = empty($service['description']) ? '' : $service['description'];
                    $serviceobj->apiversion  = $service['apiversion'];
                    $serviceobj->offer       = 1;
                    $serviceobj->id          = $DB->insert_record('mnet_service', $serviceobj);
                }
                $servicecache[$service['servicename']] = $serviceobj;
                if (!$DB->record_exists('mnet_service2rpc', array('rpcid'=>$dataobject->id, 'serviceid'=>$serviceobj->id))) {
                    $obj = new stdClass();
                    $obj->rpcid = $dataobject->id;
                    $obj->serviceid = $serviceobj->id;
                    $DB->insert_record('mnet_service2rpc', $obj, true);
                }
            }
        }
    }
        foreach($subscribes as $service => $methods) {
        if (!array_key_exists($service, $servicecache)) {
            if (!$serviceobj = $DB->get_record('mnet_service', array('name' =>  $service))) {
                debugging("TODO: skipping unknown service $service - somebody needs to fix MDL-21993");
                continue;
            }
            $servicecache[$service] = $serviceobj;
        } else {
            $serviceobj = $servicecache[$service];
        }
        foreach ($methods as $method => $xmlrpcpath) {
            if (!$rpcid = $DB->get_field('mnet_remote_rpc', 'id', array('xmlrpcpath'=>$xmlrpcpath))) {
                $remoterpc = (object)array(
                    'functionname' => $method,
                    'xmlrpcpath' => $xmlrpcpath,
                    'plugintype' => $type,
                    'pluginname' => $plugin,
                    'enabled'    => 1,
                );
                $rpcid = $remoterpc->id = $DB->insert_record('mnet_remote_rpc', $remoterpc, true);
            }
            if (!$DB->record_exists('mnet_remote_service2rpc', array('rpcid'=>$rpcid, 'serviceid'=>$serviceobj->id))) {
                $obj = new stdClass();
                $obj->rpcid = $rpcid;
                $obj->serviceid = $serviceobj->id;
                $DB->insert_record('mnet_remote_service2rpc', $obj, true);
            }
            $subscribemethodservices[$method][] = $service;
        }
    }

    foreach ($DB->get_records('mnet_remote_rpc', array('pluginname'=>$plugin, 'plugintype'=>$type), 'functionname ASC ') as $rpc) {
        if (!array_key_exists($rpc->functionname, $subscribemethodservices) && $rpc->enabled) {
            $DB->set_field('mnet_remote_rpc', 'enabled', 0, array('id' => $rpc->id));
        } else if (array_key_exists($rpc->functionname, $subscribemethodservices) && !$rpc->enabled) {
            $DB->set_field('mnet_remote_rpc', 'enabled', 1, array('id' => $rpc->id));
        }
    }

    return true;
}


function admin_mnet_method_profile(ReflectionFunctionAbstract $function) {
    $commentlines = admin_mnet_method_get_docblock($function);
    $getkey = function($key) use ($commentlines) {
        return array_values(array_filter($commentlines, function($line) use ($key) {
            return $line[0] == $key;
        }));
    };
    $returnline = $getkey('@return');
    return array (
        'parameters' => array_map(function($line) {
            return array(
                'name' => trim($line[2], " \t\n\r\0\x0B$"),
                'type' => $line[1],
                'description' => $line[3]
            );
        }, $getkey('@param')),

        'return' => array(
            'type' => !empty($returnline[0][1]) ? $returnline[0][1] : 'void',
            'description' => !empty($returnline[0][2]) ? $returnline[0][2] : ''
        )
    );
}


function admin_mnet_method_get_docblock(ReflectionFunctionAbstract $function) {
    return array_map(function($line) {
        $text = trim($line, " \t\n\r\0\x0B*/");
        if (strpos($text, '@param') === 0) {
            return preg_split('/\s+/', $text, 4);
        }

        if (strpos($text, '@return') === 0) {
            return preg_split('/\s+/', $text, 3);
        }

        return array($text);
    }, explode("\n", $function->getDocComment()));
}


function admin_mnet_method_get_help(ReflectionFunctionAbstract $function) {
    $helplines = array_map(function($line) {
        return implode(' ', $line);
    }, array_values(array_filter(admin_mnet_method_get_docblock($function), function($line) {
        return strpos($line[0], '@') !== 0 && !empty($line[0]);
    })));

    return implode("\n", $helplines);
}


function upgrade_fix_missing_root_folders_draft() {
    global $DB;

    $transaction = $DB->start_delegated_transaction();

    $sql = "SELECT contextid, itemid, MAX(timecreated) AS timecreated, MAX(timemodified) AS timemodified
              FROM {files}
             WHERE (component = 'user' AND filearea = 'draft')
          GROUP BY contextid, itemid
            HAVING MAX(CASE WHEN filename = '.' AND filepath = '/' THEN 1 ELSE 0 END) = 0";

    $rs = $DB->get_recordset_sql($sql);
    $defaults = array('component' => 'user',
        'filearea' => 'draft',
        'filepath' => '/',
        'filename' => '.',
        'userid' => 0,         'filesize' => 0,
        'contenthash' => sha1(''));
    foreach ($rs as $r) {
        $r->pathnamehash = sha1("/$r->contextid/user/draft/$r->itemid/.");
        $DB->insert_record('files', (array)$r + $defaults);
    }
    $rs->close();
    $transaction->allow_commit();
}


function check_database_storage_engine(environment_results $result) {
    global $DB;

        if ($DB->get_dbfamily() == 'mysql') {
                $engine = $DB->get_dbengine();
                if ($engine == 'MyISAM') {
            $result->setInfo('unsupported_db_storage_engine');
            $result->setStatus(false);
            return $result;
        }
    }

    return null;
}


function check_slasharguments(environment_results $result){
    global $CFG;

    if (!during_initial_install() && empty($CFG->slasharguments)) {
        $result->setInfo('slasharguments');
        $result->setStatus(false);
        return $result;
    }

    return null;
}


function check_database_tables_row_format(environment_results $result) {
    global $DB;

    if ($DB->get_dbfamily() == 'mysql') {
        $generator = $DB->get_manager()->generator;

        foreach ($DB->get_tables(false) as $table) {
            $columns = $DB->get_columns($table, false);
            $size = $generator->guess_antelope_row_size($columns);
            $format = $DB->get_row_format($table);

            if ($size <= $generator::ANTELOPE_MAX_ROW_SIZE) {
                continue;
            }

            if ($format === 'Compact' or $format === 'Redundant') {
                $result->setInfo('unsupported_db_table_row_format');
                $result->setStatus(false);
                return $result;
            }
        }
    }

    return null;
}


function upgrade_minmaxgrade() {
    global $CFG, $DB;

        $settingvalue = 2;

                $setcoursesetting = !isset($CFG->grade_minmaxtouse) || $CFG->grade_minmaxtouse != $settingvalue;

        $sql = "SELECT DISTINCT(gi.courseid)
              FROM {grade_grades} gg
              JOIN {grade_items} gi
                ON gg.itemid = gi.id
             WHERE gi.itemtype NOT IN (?, ?)
               AND (gg.rawgrademax != gi.grademax OR gg.rawgrademin != gi.grademin)";

    $rs = $DB->get_recordset_sql($sql, array('course', 'category'));
    foreach ($rs as $record) {
                set_config('show_min_max_grades_changed_' . $record->courseid, 1);

                $configname = 'minmaxtouse';
        if ($setcoursesetting &&
                !$DB->record_exists('grade_settings', array('courseid' => $record->courseid, 'name' => $configname))) {
                        $data = new stdClass();
            $data->courseid = $record->courseid;
            $data->name     = $configname;
            $data->value    = $settingvalue;
            $DB->insert_record('grade_settings', $data);
        }

                $DB->set_field('grade_items', 'needsupdate', 1, array('courseid' => $record->courseid));
    }
    $rs->close();
}



function check_upgrade_key($upgradekeyhash) {
    global $CFG, $PAGE;

    if (isset($CFG->config_php_settings['upgradekey'])) {
        if ($upgradekeyhash === null or $upgradekeyhash !== sha1($CFG->config_php_settings['upgradekey'])) {
            if (!$PAGE->headerprinted) {
                $output = $PAGE->get_renderer('core', 'admin');
                echo $output->upgradekey_form_page(new moodle_url('/admin/index.php', array('cache' => 0)));
                die();
            } else {
                                die('Upgrade locked');
            }
        }
    }
}


function upgrade_install_plugins(array $installable, $confirmed, $heading='', $continue=null, $return=null) {
    global $CFG, $PAGE;

    if (empty($return)) {
        $return = $PAGE->url;
    }

    if (!empty($CFG->disableupdateautodeploy)) {
        redirect($return);
    }

    if (empty($installable)) {
        redirect($return);
    }

    $pluginman = core_plugin_manager::instance();

    if ($confirmed) {
                if (!$pluginman->install_plugins($installable, true, true)) {
            throw new moodle_exception('install_plugins_failed', 'core_plugin', $return);
        }

                                        $mustgoto = new moodle_url('/admin/index.php', array('cache' => 0, 'confirmplugincheck' => 0));
        if ($mustgoto->compare($PAGE->url, URL_MATCH_PARAMS)) {
            redirect($PAGE->url);
        } else {
            redirect($mustgoto);
        }

    } else {
        $output = $PAGE->get_renderer('core', 'admin');
        echo $output->header();
        if ($heading) {
            echo $output->heading($heading, 3);
        }
        echo html_writer::start_tag('pre', array('class' => 'plugin-install-console'));
        $validated = $pluginman->install_plugins($installable, false, false);
        echo html_writer::end_tag('pre');
        if ($validated) {
            echo $output->plugins_management_confirm_buttons($continue, $return);
        } else {
            echo $output->plugins_management_confirm_buttons(null, $return);
        }
        echo $output->footer();
        die();
    }
}

function check_unoconv_version(environment_results $result) {
    global $CFG;

    if (!during_initial_install() && !empty($CFG->pathtounoconv) && file_is_executable(trim($CFG->pathtounoconv))) {
        $currentversion = 0;
        $supportedversion = 0.7;
        $unoconvbin = \escapeshellarg($CFG->pathtounoconv);
        $command = "$unoconvbin --version";
        exec($command, $output);

                if ($output) {
            foreach ($output as $response) {
                if (preg_match('/unoconv (\\d+\\.\\d+)/', $response, $matches)) {
                    $currentversion = (float)$matches[1];
                }
            }
        }

        if ($currentversion < $supportedversion) {
            $result->setInfo('unoconv version not supported');
            $result->setStatus(false);
            return $result;
        }
    }
    return null;
}


function check_libcurl_version(environment_results $result) {

    if (!function_exists('curl_version')) {
        $result->setInfo('cURL PHP extension is not installed');
        $result->setStatus(false);
        return $result;
    }

        $supportedversion = 0x071304;
    $supportedversionstring = "7.19.4";

        $curlinfo = curl_version();
    $currentversion = $curlinfo['version_number'];

    if ($currentversion < $supportedversion) {
                        $result->setInfo('Libcurl version check');
        $result->setNeededVersion($supportedversionstring);
        $result->setCurrentVersion($curlinfo['version']);
        $result->setStatus(false);
        return $result;
    }

    return null;
}
