<?php



defined('MOODLE_INTERNAL') || die();


abstract class portfolio_plugin_base {

    
    protected $dirty;

    
    protected $id;

    
    protected $name;

    
    protected $plugin;

    
    protected $visible;

    
    protected $config;

    
    protected $userconfig;

    
    protected $exportconfig;

    
    protected $user;

    
    protected $exporter;

    
    public function supported_formats() {
        return array(PORTFOLIO_FORMAT_FILE, PORTFOLIO_FORMAT_RICH);
    }

    
    public static function file_mime_check($mimetype) {
        return true;
    }


    
    public abstract function expected_time($callertime);

    
    public abstract function is_push();

    
    public static function get_name() {
        throw new coding_exception('get_name() method needs to be overridden in each subclass of portfolio_plugin_base');
    }

    
    public static function plugin_sanity_check() {
        return 0;
    }

    
    public function instance_sanity_check() {
        return 0;
    }

    
    public static function has_admin_config() {
        return false;
    }

    
    public function has_user_config() {
        return false;
    }

    
    public function has_export_config() {
        return false;
    }

    
    public function export_config_validation(array $data) {}

    
    public function user_config_validation(array $data) {}

    
    public function set_export_config($config) {
        $allowed = array_merge(
            array('wait', 'hidewait', 'format', 'hideformat'),
            $this->get_allowed_export_config()
        );
        foreach ($config as $key => $value) {
            if (!in_array($key, $allowed)) {
                $a = (object)array('property' => $key, 'class' => get_class($this));
                throw new portfolio_export_exception($this->get('exporter'), 'invalidexportproperty', 'portfolio', null, $a);
            }
            $this->exportconfig[$key] = $value;
        }
    }

    
    public final function get_export_config($key) {
        $allowed = array_merge(
            array('hidewait', 'wait', 'format', 'hideformat'),
            $this->get_allowed_export_config()
        );
        if (!in_array($key, $allowed)) {
            $a = (object)array('property' => $key, 'class' => get_class($this));
            throw new portfolio_export_exception($this->get('exporter'), 'invalidexportproperty', 'portfolio', null, $a);
        }
        if (!array_key_exists($key, $this->exportconfig)) {
            return null;
        }
        return $this->exportconfig[$key];
    }

    
    public function get_export_summary() {
        return false;
    }

    
    public abstract function prepare_package();

    
    public abstract function send_package();


    
    public function get_extra_finish_options() {
        return false;
    }

    
    public abstract function get_interactive_continue_url();

    
    public function get_static_continue_url() {
        return $this->get_interactive_continue_url();
    }

    
    public function resolve_static_continue_url($url) {
        return $url;
    }

    
    public function user_config_form(&$mform) {}

    
    public static function admin_config_form(&$mform) {}

    
    public static function admin_config_validation($data) {}

    
    public function export_config_form(&$mform) {}

    
    public static function allows_multiple_instances() {
        return true;
    }

    
    public function steal_control($stage) {
        return false;
    }

    
    public function post_control($stage, $params) { }

    
    public static function create_instance($plugin, $name, $config) {
        global $DB, $CFG;
        $new = (object)array(
            'plugin' => $plugin,
            'name'   => $name,
        );
        if (!portfolio_static_function($plugin, 'allows_multiple_instances')) {
                        if ($DB->record_exists('portfolio_instance', array('plugin' => $plugin))) {
                throw new portfolio_exception('multipleinstancesdisallowed', 'portfolio', '', $plugin);
            }
        }
        $newid = $DB->insert_record('portfolio_instance', $new);
        require_once($CFG->dirroot . '/portfolio/' . $plugin . '/lib.php');
        $classname = 'portfolio_plugin_'  . $plugin;
        $obj = new $classname($newid);
        $obj->set_config($config);
        $obj->save();
        return $obj;
    }

    
    public function __construct($instanceid, $record=null) {
        global $DB;
        if (!$record) {
            if (!$record = $DB->get_record('portfolio_instance', array('id' => $instanceid))) {
                throw new portfolio_exception('invalidinstance', 'portfolio');
            }
        }
        foreach ((array)$record as $key =>$value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        $this->config = new StdClass;
        $this->userconfig = array();
        $this->exportconfig = array();
        foreach ($DB->get_records('portfolio_instance_config', array('instance' => $instanceid)) as $config) {
            $this->config->{$config->name} = $config->value;
        }
        $this->init();
        return $this;
    }

    
    protected function init() { }

    
    public static function get_allowed_config() {
        return array();
    }

    
    public function get_allowed_user_config() {
        return array();
    }

    
    public function get_allowed_export_config() {
        return array();
    }

    
    public final function set_config($config) {
        global $DB;
        foreach ($config as $key => $value) {
                        try {
                $this->set($key, $value);
                continue;
            } catch (portfolio_exception $e) { }
            if (!in_array($key, $this->get_allowed_config())) {
                $a = (object)array('property' => $key, 'class' => get_class($this));
                throw new portfolio_export_exception($this->get('exporter'), 'invalidconfigproperty', 'portfolio', null, $a);
            }
            if (!isset($this->config->{$key})) {
                $DB->insert_record('portfolio_instance_config', (object)array(
                    'instance' => $this->id,
                    'name' => $key,
                    'value' => $value,
                ));
            } else if ($this->config->{$key} != $value) {
                $DB->set_field('portfolio_instance_config', 'value', $value, array('name' => $key, 'instance' => $this->id));
            }
            $this->config->{$key} = $value;
        }
    }

    
    public final function get_config($key) {
        if (!in_array($key, $this->get_allowed_config())) {
            $a = (object)array('property' => $key, 'class' => get_class($this));
            throw new portfolio_export_exception($this->get('exporter'), 'invalidconfigproperty', 'portfolio', null, $a);
        }
        if (isset($this->config->{$key})) {
            return $this->config->{$key};
        }
        return null;
    }

    
    public final function get_user_config($key, $userid=0) {
        global $DB;

        if (empty($userid)) {
            $userid = $this->user->id;
        }

        if ($key != 'visible') {             if (!in_array($key, $this->get_allowed_user_config())) {
                $a = (object)array('property' => $key, 'class' => get_class($this));
                throw new portfolio_export_exception($this->get('exporter'), 'invaliduserproperty', 'portfolio', null, $a);
            }
        }
        if (!array_key_exists($userid, $this->userconfig)) {
            $this->userconfig[$userid] = (object)array_fill_keys(array_merge(array('visible'), $this->get_allowed_user_config()), null);
            foreach ($DB->get_records('portfolio_instance_user', array('instance' => $this->id, 'userid' => $userid)) as $config) {
                $this->userconfig[$userid]->{$config->name} = $config->value;
            }
        }
        if ($this->userconfig[$userid]->visible === null) {
            $this->set_user_config(array('visible' => 1), $userid);
        }
        return $this->userconfig[$userid]->{$key};

    }

    
    public final function set_user_config($config, $userid=0) {
        global $DB;

        if (empty($userid)) {
            $userid = $this->user->id;
        }

        foreach ($config as $key => $value) {
            if ($key != 'visible' && !in_array($key, $this->get_allowed_user_config())) {
                $a = (object)array('property' => $key, 'class' => get_class($this));
                throw new portfolio_export_exception($this->get('exporter'), 'invaliduserproperty', 'portfolio', null, $a);
            }
            if (!$existing = $DB->get_record('portfolio_instance_user', array('instance'=> $this->id, 'userid' => $userid, 'name' => $key))) {
                $DB->insert_record('portfolio_instance_user', (object)array(
                    'instance' => $this->id,
                    'name' => $key,
                    'value' => $value,
                    'userid' => $userid,
                ));
            } else if ($existing->value != $value) {
                $DB->set_field('portfolio_instance_user', 'value', $value, array('name' => $key, 'instance' => $this->id, 'userid' => $userid));
            }
            $this->userconfig[$userid]->{$key} = $value;
        }

    }

    
    public final function get($field) {
        if (property_exists($this, $field)) {
            return $this->{$field};
        }
        $a = (object)array('property' => $field, 'class' => get_class($this));
        throw new portfolio_export_exception($this->get('exporter'), 'invalidproperty', 'portfolio', null, $a);
    }

    
    public final function set($field, $value) {
        if (property_exists($this, $field)) {
            $this->{$field} =& $value;
            $this->dirty = true;
            return true;
        }
        $a = (object)array('property' => $field, 'class' => get_class($this));
        if ($this->get('exporter')) {
            throw new portfolio_export_exception($this->get('exporter'), 'invalidproperty', 'portfolio', null, $a);
        }
        throw new portfolio_exception('invalidproperty', 'portfolio', null, $a); 
    }

    
    public function save() {
        global $DB;
        if (!$this->dirty) {
            return true;
        }
        $fordb = new StdClass();
        foreach (array('id', 'name', 'plugin', 'visible') as $field) {
            $fordb->{$field} = $this->{$field};
        }
        $DB->update_record('portfolio_instance', $fordb);
        $this->dirty = false;
        return true;
    }

    
    public function delete() {
        global $DB;
        $DB->delete_records('portfolio_instance_config', array('instance' => $this->get('id')));
        $DB->delete_records('portfolio_instance_user', array('instance' => $this->get('id')));
        $DB->delete_records('portfolio_tempdata', array('instance' => $this->get('id')));
        $DB->delete_records('portfolio_instance', array('id' => $this->get('id')));
        $this->dirty = false;
        return true;
    }

    
    public function cleanup() {
        return true;
    }

    
    public static function allows_multiple_exports() {
        return true;
    }

    
    public function heading_summary() {
        return get_string('exportingcontentto', 'portfolio', $this->name);
    }
}


abstract class portfolio_plugin_push_base extends portfolio_plugin_base {

    
    public function is_push() {
        return true;
    }
}


abstract class portfolio_plugin_pull_base extends portfolio_plugin_base {

    
    protected $file;

    
    public function is_push() {
        return false;
    }

    
    public function get_base_file_url() {
        global $CFG;
        return $CFG->wwwroot . '/portfolio/file.php?id=' . $this->exporter->get('id');
    }

    
    public abstract function verify_file_request_params($params);

    
    public function send_file() {
        $file = $this->get('file');
        if (!($file instanceof stored_file)) {
            throw new portfolio_export_exception($this->get('exporter'), 'filenotfound', 'portfolio');
        }
                send_stored_file($file, 0, 0, true, array('dontdie' => true));
        $this->get('exporter')->log_transfer();
    }

}
