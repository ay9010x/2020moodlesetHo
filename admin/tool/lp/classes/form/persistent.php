<?php



namespace tool_lp\form;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use moodleform;
use stdClass;

require_once($CFG->libdir.'/formslib.php');


abstract class persistent extends moodleform {

    
    protected static $persistentclass = null;

    
    protected static $fieldstoremove = array('submitbutton');

    
    protected static $foreignfields = array();

    
    private $persistent = null;

    
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '',
                                $attributes = null, $editable = true) {
        if (empty(static::$persistentclass)) {
            throw new coding_exception('Static property $persistentclass must be set.');
        } else if (!is_subclass_of(static::$persistentclass, 'core_competency\\persistent')) {
            throw new coding_exception('Static property $persistentclass is not valid.');
        } else if (!array_key_exists('persistent', $customdata)) {
            throw new coding_exception('The custom data \'persistent\' key must be set, even if it is null.');
        }

                $persistendata = new stdClass();
        $persistent = isset($customdata['persistent']) ? $customdata['persistent'] : null;
        if ($persistent) {
            if (!($persistent instanceof static::$persistentclass)) {
                throw new coding_exception('Invalid persistent');
            }
            $persistendata = $persistent->to_record();
            unset($persistent);
        }

        $this->persistent = new static::$persistentclass();
        $this->persistent->from_record($persistendata);

        unset($customdata['persistent']);
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable);

                $this->set_data($this->get_default_data());
    }

    
    protected static function convert_fields(stdClass $data) {
        $class = static::$persistentclass;
        $properties = $class::get_formatted_properties();

        foreach ($data as $field => $value) {
                        if (isset($properties[$field])) {
                $formatfield = $properties[$field];
                $data->$formatfield = $data->{$field}['format'];
                $data->$field = $data->{$field}['text'];
            }
        }

        return $data;
    }

    
    protected function extra_validation($data, $files, array &$errors) {
        return array();
    }

    
    protected function filter_data_for_persistent($data) {
        return (object) array_diff_key((array) $data, array_flip((array) static::$foreignfields));
    }

    
    protected function get_default_data() {
        $data = $this->get_persistent()->to_record();
        $class = static::$persistentclass;
        $properties = $class::get_formatted_properties();

        foreach ($data as $field => $value) {
                        if (isset($properties[$field])) {
                $data->$field = array(
                    'text' => $data->$field,
                    'format' => $data->{$properties[$field]}
                );
                unset($data->{$properties[$field]});
            }
        }

        return $data;
    }

    
    public function get_data() {
        $data = parent::get_data();
        if (is_object($data)) {
            foreach (static::$fieldstoremove as $field) {
                unset($data->{$field});
            }
            $data = static::convert_fields($data);

                        $data->id = $this->persistent->get_id();
        }
        return $data;
    }

    
    final protected function get_persistent() {
        return $this->persistent;
    }

    
    public function get_submitted_data() {
        $data = parent::get_submitted_data();
        if (is_object($data)) {
            foreach (static::$fieldstoremove as $field) {
                unset($data->{$field});
            }
            $data = static::convert_fields($data);
        }
        return $data;
    }

    
    public final function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $data = $this->get_submitted_data();

                $persistentdata = $this->filter_data_for_persistent($data);
        $persistent = $this->get_persistent();
        $persistent->from_record((object) $persistentdata);
        $errors = array_merge($errors, $persistent->get_errors());

                $extraerrors = $this->extra_validation($data, $files, $errors);
        $errors = array_merge($errors, (array) $extraerrors);

        return $errors;
    }
}
