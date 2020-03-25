<?php



defined('MOODLE_INTERNAL') || die();


abstract class portfolio_caller_base {

    
    protected $course;

    
    protected $exportconfig = array();

    
    protected $user;

    
    protected $exporter;

    
    protected $supportedformats;

    
    protected $singlefile;

    
    protected $multifiles;

    
    protected $intendedmimetype;

    
    public function __construct($callbackargs) {
        $expected = call_user_func(array(get_class($this), 'expected_callbackargs'));
        foreach ($expected as $key => $required) {
            if (!array_key_exists($key, $callbackargs)) {
                if ($required) {
                    $a = (object)array('arg' => $key, 'class' => get_class($this));
                    throw new portfolio_caller_exception('missingcallbackarg', 'portfolio', null, $a);
                }
                continue;
            }
            $this->{$key} = $callbackargs[$key];
        }
    }

    
    public function export_config_form(&$mform, $instance) {}


    
    public function has_export_config() {
        return false;
    }

    
    public function export_config_validation($data) {}

    
    public abstract function expected_time();

    
    public function expected_time_file() {
        if ($this->multifiles) {
            return portfolio_expected_time_file($this->multifiles);
        }
        else if ($this->singlefile) {
            return portfolio_expected_time_file($this->singlefile);
        }
        return PORTFOLIO_TIME_LOW;
    }

    
    public abstract function get_navigation();

    
    public abstract function get_sha1();

    
    public function get_sha1_file() {
        if (empty($this->singlefile) && empty($this->multifiles)) {
            throw new portfolio_caller_exception('invalidsha1file', 'portfolio', $this->get_return_url());
        }
        if ($this->singlefile) {
            return $this->singlefile->get_contenthash();
        }
        $sha1s = array();
        foreach ($this->multifiles as $file) {
            $sha1s[] = $file->get_contenthash();
        }
        asort($sha1s);
        return sha1(implode('', $sha1s));
    }

    
    public function get($field) {
        if (property_exists($this, $field)) {
            return $this->{$field};
        }
        $a = (object)array('property' => $field, 'class' => get_class($this));
        throw new portfolio_export_exception($this->get('exporter'), 'invalidproperty', 'portfolio', $this->get_return_url(), $a);
    }

    
    public final function set($field, &$value) {
        if (property_exists($this, $field)) {
            $this->{$field} =& $value;
            $this->dirty = true;
            return true;
        }
        $a = (object)array('property' => $field, 'class' => get_class($this));
        throw new portfolio_export_exception($this->get('exporter'), 'invalidproperty', 'portfolio', $this->get_return_url(), $a);
    }

    
    public final function set_export_config($config) {
        $allowed = array_merge(
            array('wait', 'hidewait', 'format', 'hideformat'),
            $this->get_allowed_export_config()
        );
        foreach ($config as $key => $value) {
            if (!in_array($key, $allowed)) {
                $a = (object)array('property' => $key, 'class' => get_class($this));
                throw new portfolio_export_exception($this->get('exporter'), 'invalidexportproperty', 'portfolio', $this->get_return_url(), $a);
            }
            $this->exportconfig[$key] = $value;
        }
    }

    
    public final function get_export_config($key) {
        $allowed = array_merge(
            array('wait', 'hidewait', 'format', 'hideformat'),
            $this->get_allowed_export_config()
        );
        if (!in_array($key, $allowed)) {
            $a = (object)array('property' => $key, 'class' => get_class($this));
            throw new portfolio_export_exception($this->get('exporter'), 'invalidexportproperty', 'portfolio', $this->get_return_url(), $a);
        }
        if (!array_key_exists($key, $this->exportconfig)) {
            return null;
        }
        return $this->exportconfig[$key];
    }

    
    public function get_allowed_export_config() {
        return array();
    }

    
    public function get_export_summary() {
        return false;
    }

    
    public abstract function prepare_package();

    
    public function prepare_package_file() {
        if (empty($this->singlefile) && empty($this->multifiles)) {
            throw new portfolio_caller_exception('invalidpreparepackagefile', 'portfolio', $this->get_return_url());
        }
        if ($this->singlefile) {
            return $this->exporter->copy_existing_file($this->singlefile);
        }
        foreach ($this->multifiles as $file) {
            $this->exporter->copy_existing_file($file);
        }
    }

    
    public final function supported_formats() {
        $basic = $this->base_supported_formats();
        if (empty($this->supportedformats)) {
            $specific = array();
        } else if (!is_array($this->supportedformats)) {
            debugging(get_class($this) . ' has set a non array value of member variable supported formats - working around but should be fixed in code');
            $specific = array($this->supportedformats);
        } else {
            $specific = $this->supportedformats;
        }
        return portfolio_most_specific_formats($specific, $basic);
    }

    
    public static function base_supported_formats() {
        throw new coding_exception('base_supported_formats() method needs to be overridden in each subclass of portfolio_caller_base');
    }

    
    public abstract function get_return_url();

    
    public abstract function check_permissions();

    
    public static function display_name() {
        throw new coding_exception('display_name() method needs to be overridden in each subclass of portfolio_caller_base');
    }

    
    public function heading_summary() {
        return get_string('exportingcontentfrom', 'portfolio', $this->display_name());
    }

    
    public abstract function load_data();

    
    public function set_file_and_format_data($ids=null ) {
        $args = func_get_args();
        array_shift($args);         if (empty($ids) && count($args) == 0) {
            return;
        }
        $files = array();
        $fs = get_file_storage();
        if (!empty($ids)) {
            if (is_numeric($ids) || $ids instanceof stored_file) {
                $ids = array($ids);
            }
            foreach ($ids as $id) {
                if ($id instanceof stored_file) {
                    $files[] = $id;
                } else {
                    $files[] = $fs->get_file_by_id($id);
                }
            }
        } else if (count($args) != 0) {
            if (count($args) < 4) {
                throw new portfolio_caller_exception('invalidfileareaargs', 'portfolio');
            }
            $files = array_values(call_user_func_array(array($fs, 'get_area_files'), $args));
        }
        switch (count($files)) {
            case 0: return;
            case 1: {
                $this->singlefile = $files[0];
                return;
            }
            default: {
                $this->multifiles = $files;
            }
        }
    }

    
    public function set_formats_from_button($formats) {
        $base = $this->base_supported_formats();
        if (count($base) != count($formats)
                || count($base) != count(array_intersect($base, $formats))) {
                $this->supportedformats = portfolio_most_specific_formats($formats, $base);
                return;
        }
                                $this->supportedformats = portfolio_most_specific_formats($formats, $formats);
    }

    
    protected function add_format($format) {
        if (in_array($format, $this->supportedformats)) {
            return;
        }
        $this->supportedformats = portfolio_most_specific_formats(array($format), $this->supportedformats);
    }

    
    public function get_mimetype() {
        if ($this->singlefile instanceof stored_file) {
            return $this->singlefile->get_mimetype();
        } else if (!empty($this->intendedmimetype)) {
            return $this->intendedmimetype;
        }
    }

    
    public static function expected_callbackargs() {
        throw new coding_exception('expected_callbackargs() method needs to be overridden in each subclass of portfolio_caller_base');
    }


    
    public abstract function set_context($PAGE);
}


abstract class portfolio_module_caller_base extends portfolio_caller_base {

    
    protected $cm;

    
    protected $id;

    
    protected $course;

    
    public function get_navigation() {
                $extranav = array();
        return array($extranav, $this->cm);
    }

    
    public function get_return_url() {
        global $CFG;
        return $CFG->wwwroot . '/mod/' . $this->cm->modname . '/view.php?id=' . $this->cm->id;
    }

    
    public function get($key) {
        if ($key != 'course') {
            return parent::get($key);
        }
        global $DB;
        if (empty($this->course)) {
            $this->course = $DB->get_record('course', array('id' => $this->cm->course));
        }
        return $this->course;
    }

    
    public function heading_summary() {
        return get_string('exportingcontentfrom', 'portfolio', $this->display_name() . ': ' . $this->cm->name);
    }

    
    public function set_context($PAGE) {
        $PAGE->set_cm($this->cm);
    }
}
