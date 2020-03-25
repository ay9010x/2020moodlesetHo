<?php



defined('MOODLE_INTERNAL') || die;


require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/dtllib.php');


function tool_dbtransfer_export_xml_database($description, $mdb) {
    core_php_time_limit::raise();

    \core\session\manager::write_close(); 
    header('Content-Type: application/xhtml+xml; charset=utf-8');
    header('Content-Disposition: attachment; filename=database.xml');
    header('Expires: 0');
    header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
    header('Pragma: public');

    while(@ob_flush());

    $var = new file_xml_database_exporter('php://output', $mdb);
    $var->export_database($description);

        die;
}


function tool_dbtransfer_transfer_database(moodle_database $sourcedb, moodle_database $targetdb, progress_trace $feedback = null) {
    core_php_time_limit::raise();

    \core\session\manager::write_close(); 
    $var = new database_mover($sourcedb, $targetdb, true, $feedback);
    $var->export_database(null);

    tool_dbtransfer_rebuild_target_log_actions($targetdb, $feedback);
}


function tool_dbtransfer_rebuild_target_log_actions(moodle_database $target, progress_trace $feedback = null) {
    global $DB, $CFG;
    require_once("$CFG->libdir/upgradelib.php");

    $feedback->output(get_string('convertinglogdisplay', 'tool_dbtransfer'));

    $olddb = $DB;
    $DB = $target;
    try {
        $DB->delete_records('log_display', array('component'=>'moodle'));
        log_update_descriptions('moodle');
        $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $type => $location) {
            $plugs = core_component::get_plugin_list($type);
            foreach ($plugs as $plug => $fullplug) {
                $component = $type.'_'.$plug;
                $DB->delete_records('log_display', array('component'=>$component));
                log_update_descriptions($component);
            }
        }
    } catch (Exception $e) {
        $DB = $olddb;
        throw $e;
    }
    $DB = $olddb;
    $feedback->output(get_string('done', 'core_dbtransfer', null), 1);
}


function tool_dbtransfer_get_drivers() {
    global $CFG;

    $files = new RegexIterator(new DirectoryIterator("$CFG->libdir/dml"), '|^.*_moodle_database\.php$|');
    $drivers = array();

    foreach ($files as $file) {
        $matches = null;
        preg_match('|^([a-z0-9]+)_([a-z]+)_moodle_database\.php$|', $file->getFilename(), $matches);
        if (!$matches) {
            continue;
        }
        $dbtype = $matches[1];
        $dblibrary = $matches[2];

        if ($dbtype === 'sqlite3') {
                        continue;
        }

        $targetdb = moodle_database::get_driver_instance($dbtype, $dblibrary, false);
        if ($targetdb->driver_installed() !== true) {
            continue;
        }

        $driver = $dbtype.'/'.$dblibrary;

        $drivers[$driver] = $targetdb->get_name();
    };

    return $drivers;
}


function tool_dbtransfer_create_maintenance_file() {
    global $CFG;

    core_shutdown_manager::register_function('tool_dbtransfer_maintenance_callback');

    $options = new stdClass();
    $options->trusted = false;
    $options->noclean = false;
    $options->smiley = false;
    $options->filter = false;
    $options->para = true;
    $options->newlines = false;

    $message = format_text(get_string('climigrationnotice', 'tool_dbtransfer'), FORMAT_MARKDOWN, $options);
    $message = bootstrap_renderer::early_error_content($message, '', '', array());
    $html = <<<OET
<!DOCTYPE html>
<html>
<header><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><header/>
<body>$message</body>
</html>
OET;

    file_put_contents("$CFG->dataroot/climaintenance.html", $html);
    @chmod("$CFG->dataroot/climaintenance.html", $CFG->filepermissions);
}


function tool_dbtransfer_maintenance_callback() {
    global $CFG;

    if (empty($CFG->tool_dbransfer_migration_running)) {
                return;
    }

    if (file_exists("$CFG->dataroot/climaintenance.html")) {
                unlink("$CFG->dataroot/climaintenance.html");
        error_log('tool_dbtransfer: Interrupted database migration detected, switching off CLI maintenance mode.');
    }
}
