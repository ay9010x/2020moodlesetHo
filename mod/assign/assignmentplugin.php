<?php



defined('MOODLE_INTERNAL') || die();


abstract class assign_plugin {

    
    protected $assignment;
    
    private $type = '';
    
    private $error = '';
    
    private $enabledcache = null;
    
    private $visiblecache = null;

    
    public final function __construct(assign $assignment, $type) {
        $this->assignment = $assignment;
        $this->type = $type;
    }

    
    public final function is_first() {
        $order = get_config($this->get_subtype() . '_' . $this->get_type(), 'sortorder');

        if ($order == 0) {
            return true;
        }
        return false;
    }

    
    public final function is_last() {
        $lastindex = count(core_component::get_plugin_list($this->get_subtype()))-1;
        $currentindex = get_config($this->get_subtype() . '_' . $this->get_type(), 'sortorder');
        if ($lastindex == $currentindex) {
            return true;
        }

        return false;
    }

    
    public function get_settings(MoodleQuickForm $mform) {
        return;
    }

    
    public function data_preprocessing(&$defaultvalues) {
        return;
    }

    
    public function save_settings(stdClass $formdata) {
        return true;
    }

    
    protected final function set_error($msg) {
        $this->error = $msg;
    }

    
    public final function get_error() {
        return $this->error;
    }

    
    public abstract function get_name();

    
    public abstract function get_subtype();

    
    public final function get_type() {
        return $this->type;
    }

    
    public final function get_version() {
        $version = get_config($this->get_subtype() . '_' . $this->get_type(), 'version');
        if ($version) {
            return $version;
        } else {
            return '';
        }
    }

    
    public final function get_requires() {
        $requires = get_config($this->get_subtype() . '_' . $this->get_type(), 'requires');
        if ($requires) {
            return $requires;
        } else {
            return '';
        }
    }

    
    public function save(stdClass $submissionorgrade, stdClass $data) {
        return true;
    }

    
    public final function enable() {
        $this->enabledcache = true;
        return $this->set_config('enabled', 1);
    }

    
    public final function disable() {
        $this->enabledcache = false;
        return $this->set_config('enabled', 0);
    }

    
    public function is_enabled() {
        if ($this->enabledcache === null) {
            $this->enabledcache = $this->get_config('enabled');
        }
        return $this->enabledcache;
    }


    
    public function get_form_elements_for_user($submissionorgrade, MoodleQuickForm $mform, stdClass $data, $userid) {
        return $this->get_form_elements($submissionorgrade, $mform, $data);
    }

    
    public function get_form_elements($submissionorgrade, MoodleQuickForm $mform, stdClass $data) {
        return false;
    }

    
    public function view(stdClass $submissionorgrade) {
        return '';
    }

    
    public final function get_sort_order() {
        $order = get_config($this->get_subtype() . '_' . $this->get_type(), 'sortorder');
        return $order?$order:0;
    }

    
    public final function is_visible() {
        if ($this->visiblecache === null) {
            $disabled = get_config($this->get_subtype() . '_' . $this->get_type(), 'disabled');
            $this->visiblecache = !$disabled;
        }
        return $this->visiblecache;
    }


    
    public final function has_admin_settings() {
        global $CFG;

        $pluginroot = $CFG->dirroot . '/mod/assign/' . substr($this->get_subtype(), strlen('assign')) . '/' . $this->get_type();
        $settingsfile = $pluginroot . '/settings.php';
        return file_exists($settingsfile);
    }

    
    public final function set_config($name, $value) {
        global $DB;

        $dbparams = array('assignment'=>$this->assignment->get_instance()->id,
                          'subtype'=>$this->get_subtype(),
                          'plugin'=>$this->get_type(),
                          'name'=>$name);
        $current = $DB->get_record('assign_plugin_config', $dbparams, '*', IGNORE_MISSING);

        if ($current) {
            $current->value = $value;
            return $DB->update_record('assign_plugin_config', $current);
        } else {
            $setting = new stdClass();
            $setting->assignment = $this->assignment->get_instance()->id;
            $setting->subtype = $this->get_subtype();
            $setting->plugin = $this->get_type();
            $setting->name = $name;
            $setting->value = $value;

            return $DB->insert_record('assign_plugin_config', $setting) > 0;
        }
    }

    
    public final function get_config($setting = null) {
        global $DB;

        if ($setting) {
            if (!$this->assignment->has_instance()) {
                return false;
            }
            $assignment = $this->assignment->get_instance();
            if ($assignment) {
                $dbparams = array('assignment'=>$assignment->id,
                                  'subtype'=>$this->get_subtype(),
                                  'plugin'=>$this->get_type(),
                                  'name'=>$setting);
                $result = $DB->get_record('assign_plugin_config', $dbparams, '*', IGNORE_MISSING);
                if ($result) {
                    return $result->value;
                }
            }
            return false;
        }
        $dbparams = array('assignment'=>$this->assignment->get_instance()->id,
                          'subtype'=>$this->get_subtype(),
                           'plugin'=>$this->get_type());
        $results = $DB->get_records('assign_plugin_config', $dbparams);

        $config = new stdClass();
        if (is_array($results)) {
            foreach ($results as $setting) {
                $name = $setting->name;
                $config->$name = $setting->value;
            }
        }
        return $config;
    }

    
    public function view_summary(stdClass $submissionorgrade, & $showviewlink) {
        return '';
    }

    
    public function set_editor_text($name, $value, $submissionorgradeid) {
        return false;
    }

    
    public function set_editor_format($name, $format, $submissionorgradeid) {
        return false;
    }

    
    public function get_editor_fields() {
        return array();
    }

    
    public function get_editor_text($name, $submissionorgradeid) {
        return '';
    }

    
    public function get_files(stdClass $submissionorgrade, stdClass $user) {
        return array();
    }

    
    public function get_editor_format($name, $submissionid) {
        return 0;
    }

    
    public function can_upgrade($type, $version) {
        return false;
    }

    
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        $params = array('type'=>$this->type, 'subtype'=>$this->get_subtype());
        $log .= ' ' . get_string('upgradenotimplemented', 'mod_assign', $params);
        return false;
    }

    
    public function upgrade(context $oldcontext,
                            stdClass $oldassignment,
                            stdClass $oldsubmissionorgrade,
                            stdClass $submissionorgrade,
                            & $log) {
        $params = array('type'=>$this->type, 'subtype'=>$this->get_subtype());
        $log = $log . ' ' . get_string('upgradenotimplemented', 'mod_assign', $params);
        return false;
    }

    
    public function format_for_log(stdClass $submissionorgrade) {
                return '';
    }

    
    public function delete_instance() {
        return true;
    }

    
    public static function cron() {
    }

    
    public function is_empty(stdClass $submissionorgrade) {
        return true;
    }

    
    public function get_file_areas() {
        return array();
    }


    
    public function get_file_info($browser, $filearea, $itemid, $filepath, $filename) {
        global $CFG, $DB, $USER;
        $urlbase = $CFG->wwwroot.'/pluginfile.php';

        
        if ($this->get_subtype() == 'assignsubmission') {
            if ($itemid) {
                $record = $DB->get_record('assign_submission', array('id'=>$itemid), 'userid', IGNORE_MISSING);
                if (!$record) {
                    return null;
                }
                if (!$this->assignment->can_view_submission($record->userid)) {
                    return null;
                }
            }
        } else {
                        return null;
        }

        $fs = get_file_storage();
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!($storedfile = $fs->get_file($this->assignment->get_context()->id,
                                          $this->get_subtype() . '_' . $this->get_type(),
                                          $filearea,
                                          $itemid,
                                          $filepath,
                                          $filename))) {
            return null;
        }
        return new file_info_stored($browser,
                                    $this->assignment->get_context(),
                                    $storedfile,
                                    $urlbase,
                                    $filearea,
                                    $itemid,
                                    true,
                                    true,
                                    false);
    }

    
    public function view_page($action) {
        return '';
    }

    
    public function view_header() {
        return '';
    }

    
    public function has_user_summary() {
        return true;
    }

    
    public function get_external_parameters() {
        return null;
    }

    
    public function is_configurable() {
        return true;
    }
}
