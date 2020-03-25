<?php



defined('MOODLE_INTERNAL') || die();


class xmldb_key extends xmldb_object {

    
    protected $type;

    
    protected $fields;

    
    protected $reftable;

    
    protected $reffields;

    
    public function __construct($name, $type=null, $fields=array(), $reftable=null, $reffields=null) {
        $this->type = null;
        $this->fields = array();
        $this->reftable = null;
        $this->reffields = array();
        parent::__construct($name);
        $this->set_attributes($type, $fields, $reftable, $reffields);
    }

    
    public function set_attributes($type, $fields, $reftable=null, $reffields=null) {
        $this->type = $type;
        $this->fields = $fields;
        $this->reftable = $reftable;
        $this->reffields = empty($reffields) ? array() : $reffields;
    }

    
    public function getType() {
        return $this->type;
    }

    
    public function setType($type) {
        $this->type = $type;
    }

    
    public function setFields($fields) {
        $this->fields = $fields;
    }

    
    public function setRefTable($reftable) {
        $this->reftable = $reftable;
    }

    
    public function setRefFields($reffields) {
        $this->reffields = $reffields;
    }

    
    public function getFields() {
        return $this->fields;
    }

    
    public function getRefTable() {
        return $this->reftable;
    }

    
    public function getRefFields() {
        return $this->reffields;
    }

    
    public function arr2xmldb_key($xmlarr) {

        $result = true;

                                
                        if (isset($xmlarr['@']['NAME'])) {
            $this->name = trim($xmlarr['@']['NAME']);
        } else {
            $this->errormsg = 'Missing NAME attribute';
            $this->debug($this->errormsg);
            $result = false;
        }

        if (isset($xmlarr['@']['TYPE'])) {
                        $type = $this->getXMLDBKeyType(trim($xmlarr['@']['TYPE']));
            if ($type) {
                $this->type = $type;
            } else {
                $this->errormsg = 'Invalid TYPE attribute';
                $this->debug($this->errormsg);
                $result = false;
            }
        } else {
            $this->errormsg = 'Missing TYPE attribute';
            $this->debug($this->errormsg);
            $result = false;
        }

        if (isset($xmlarr['@']['FIELDS'])) {
            $fields = strtolower(trim($xmlarr['@']['FIELDS']));
            if ($fields) {
                $fieldsarr = explode(',',$fields);
                if ($fieldsarr) {
                    foreach ($fieldsarr as $key => $element) {
                        $fieldsarr [$key] = trim($element);
                    }
                } else {
                    $this->errormsg = 'Incorrect FIELDS attribute (comma separated of fields)';
                    $this->debug($this->errormsg);
                    $result = false;
                }
            } else {
                $this->errormsg = 'Empty FIELDS attribute';
                $this->debug($this->errormsg);
                $result = false;
            }
        } else {
            $this->errormsg = 'Missing FIELDS attribute';
            $this->debug($this->errormsg);
            $result = false;
        }
                $this->fields = $fieldsarr;

        if (isset($xmlarr['@']['REFTABLE'])) {
                        if ($this->type == XMLDB_KEY_FOREIGN ||
                $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
                $reftable = strtolower(trim($xmlarr['@']['REFTABLE']));
                if (!$reftable) {
                    $this->errormsg = 'Empty REFTABLE attribute';
                    $this->debug($this->errormsg);
                    $result = false;
                }
            } else {
                $this->errormsg = 'Wrong REFTABLE attribute (only FK can have it)';
                $this->debug($this->errormsg);
                $result = false;
            }
        } else if ($this->type == XMLDB_KEY_FOREIGN ||
                   $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
            $this->errormsg = 'Missing REFTABLE attribute';
            $this->debug($this->errormsg);
            $result = false;
        }
                if ($this->type == XMLDB_KEY_FOREIGN ||
            $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
            $this->reftable = $reftable;
        }

        if (isset($xmlarr['@']['REFFIELDS'])) {
                        if ($this->type == XMLDB_KEY_FOREIGN ||
                $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
                $reffields = strtolower(trim($xmlarr['@']['REFFIELDS']));
                if ($reffields) {
                    $reffieldsarr = explode(',',$reffields);
                    if ($reffieldsarr) {
                        foreach ($reffieldsarr as $key => $element) {
                            $reffieldsarr [$key] = trim($element);
                        }
                    } else {
                        $this->errormsg = 'Incorrect REFFIELDS attribute (comma separated of fields)';
                        $this->debug($this->errormsg);
                        $result = false;
                    }
                } else {
                    $this->errormsg = 'Empty REFFIELDS attribute';
                    $this->debug($this->errormsg);
                    $result = false;
                }
            } else {
                $this->errormsg = 'Wrong REFFIELDS attribute (only FK can have it)';
                $this->debug($this->errormsg);
                $result = false;
            }
        } else if ($this->type == XMLDB_KEY_FOREIGN ||
                   $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
            $this->errormsg = 'Missing REFFIELDS attribute';
            $this->debug($this->errormsg);
            $result = false;
        }
                if ($this->type == XMLDB_KEY_FOREIGN ||
            $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
            $this->reffields = $reffieldsarr;
        }

        if (isset($xmlarr['@']['COMMENT'])) {
            $this->comment = trim($xmlarr['@']['COMMENT']);
        }

                if ($result) {
            $this->loaded = true;
        }
        $this->calculateHash();
        return $result;
    }

    
    public function getXMLDBKeyType($type) {

        $result = XMLDB_KEY_INCORRECT;

        switch (strtolower($type)) {
            case 'primary':
                $result = XMLDB_KEY_PRIMARY;
                break;
            case 'unique':
                $result = XMLDB_KEY_UNIQUE;
                break;
            case 'foreign':
                $result = XMLDB_KEY_FOREIGN;
                break;
            case 'foreign-unique':
                $result = XMLDB_KEY_FOREIGN_UNIQUE;
                break;
                                            }
                return $result;
    }

    
    public function getXMLDBKeyName($type) {

        $result = '';

        switch ($type) {
            case XMLDB_KEY_PRIMARY:
                $result = 'primary';
                break;
            case XMLDB_KEY_UNIQUE:
                $result = 'unique';
                break;
            case XMLDB_KEY_FOREIGN:
                $result = 'foreign';
                break;
            case XMLDB_KEY_FOREIGN_UNIQUE:
                $result = 'foreign-unique';
                break;
                                            }
                return $result;
    }

    
     public function calculateHash($recursive = false) {
        if (!$this->loaded) {
            $this->hash = null;
        } else {
            $key = $this->type . implode(', ', $this->fields);
            if ($this->type == XMLDB_KEY_FOREIGN ||
                $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
                $key .= $this->reftable . implode(', ', $this->reffields);
            }
                    ;
            $this->hash = md5($key);
        }
    }

    
    public function xmlOutput() {
        $o = '';
        $o.= '        <KEY NAME="' . $this->name . '"';
        $o.= ' TYPE="' . $this->getXMLDBKeyName($this->type) . '"';
        $o.= ' FIELDS="' . implode(', ', $this->fields) . '"';
        if ($this->type == XMLDB_KEY_FOREIGN ||
            $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
            $o.= ' REFTABLE="' . $this->reftable . '"';
            $o.= ' REFFIELDS="' . implode(', ', $this->reffields) . '"';
        }
        if ($this->comment) {
            $o.= ' COMMENT="' . htmlspecialchars($this->comment) . '"';
        }
        $o.= '/>' . "\n";

        return $o;
    }

    
    public function setFromADOKey($adokey) {

                switch (strtolower($adokey['name'])) {
            case 'primary':
                $this->type = XMLDB_KEY_PRIMARY;
                break;
            default:
                $this->type = XMLDB_KEY_UNIQUE;
        }
                $fields = array_flip(array_change_key_case(array_flip($adokey['columns'])));
        $this->fields = $fields;
                $this->loaded = true;
        $this->changed = true;
    }

    
    public function getPHP() {

        $result = '';

                switch ($this->getType()) {
            case XMLDB_KEY_PRIMARY:
                $result .= 'XMLDB_KEY_PRIMARY' . ', ';
                break;
            case XMLDB_KEY_UNIQUE:
                $result .= 'XMLDB_KEY_UNIQUE' . ', ';
                break;
            case XMLDB_KEY_FOREIGN:
                $result .= 'XMLDB_KEY_FOREIGN' . ', ';
                break;
            case XMLDB_KEY_FOREIGN_UNIQUE:
                $result .= 'XMLDB_KEY_FOREIGN_UNIQUE' . ', ';
                break;
        }
                $keyfields = $this->getFields();
        if (!empty($keyfields)) {
            $result .= 'array(' . "'".  implode("', '", $keyfields) . "')";
        } else {
            $result .= 'null';
        }
                if ($this->getType() == XMLDB_KEY_FOREIGN ||
            $this->getType() == XMLDB_KEY_FOREIGN_UNIQUE) {
                        $reftable = $this->getRefTable();
            if (!empty($reftable)) {
                $result .= ", '" . $reftable . "', ";
            } else {
                $result .= 'null, ';
            }
                        $reffields = $this->getRefFields();
            if (!empty($reffields)) {
                $result .= 'array(' . "'".  implode("', '", $reffields) . "')";
            } else {
                $result .= 'null';
            }
        }
                return $result;
    }

    
    public function readableInfo() {
        $o = '';
                $o .= $this->getXMLDBKeyName($this->type);
                $o .= ' (' . implode(', ', $this->fields) . ')';
                if ($this->type == XMLDB_KEY_FOREIGN ||
            $this->type == XMLDB_KEY_FOREIGN_UNIQUE) {
            $o .= ' references ' . $this->reftable . ' (' . implode(', ', $this->reffields) . ')';
        }

        return $o;
    }
}
