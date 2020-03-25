<?php



defined('MOODLE_INTERNAL') || die();


class database_column_info {

    
    protected $data;

    
    public function __set($name, $value) {
        throw new coding_exception('database_column_info is a ready only object to allow for faster caching.');
    }

    
    public function __get($variablename) {
        if (isset($this->data[$variablename]) || array_key_exists($variablename, $this->data)) {
            return $this->data[$variablename];
        }
        throw new coding_exception('Asked for a variable that is not available . ('.$variablename.').');
    }

    
    public function __isset($variablename) {
        return isset($this->data[$variablename]);
    }

    
    public function __construct($data) {
                $validelements = array('name', 'type', 'max_length', 'scale', 'not_null', 'primary_key',
                               'auto_increment', 'binary', 'has_default',  'default_value',
                               'unique', 'meta_type');
        foreach ($validelements as $element) {
            if (isset($data->$element)) {
                $this->data[$element] = $data->$element;
            } else {
                $this->data[$element] = null;
            }
        }

        switch ($this->data['meta_type']) {
            case 'R':                 $this->data['binary']         = false;
                $this->data['has_default']    = false;
                $this->data['default_value']  = null;
                $this->data['unique']         = true;
                break;
            case 'C':
                $this->data['auto_increment'] = false;
                $this->data['binary']         = false;
                break;
        }
    }
}
