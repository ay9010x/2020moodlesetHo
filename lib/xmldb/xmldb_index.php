<?php



defined('MOODLE_INTERNAL') || die();


class xmldb_index extends xmldb_object {

    
    protected $unique;

    
    protected $fields;

    
    protected $hints;

    
    const INDEX_COMPOSED_MAX_BYTES = 999;

    
    const INDEX_MAX_BYTES = 765;

    
    public function __construct($name, $type=null, $fields=array(), $hints=array()) {
        $this->unique = false;
        $this->fields = array();
        $this->hints = array();
        parent::__construct($name);
        $this->set_attributes($type, $fields, $hints);
    }

    
    public function set_attributes($type, $fields, $hints = array()) {
        $this->unique = !empty($type) ? true : false;
        $this->fields = $fields;
        $this->hints = $hints;
    }

    
    public function getUnique() {
        return $this->unique;
    }

    
    public function setUnique($unique = true) {
        $this->unique = $unique;
    }

    
    public function setFields($fields) {
        $this->fields = $fields;
    }

    
    public function getFields() {
        return $this->fields;
    }

    
    public function setHints($hints) {
        $this->hints = $hints;
    }

    
    public function getHints() {
        return $this->hints;
    }

    
    public function arr2xmldb_index($xmlarr) {

        $result = true;

                                
                if (isset($xmlarr['@']['NAME'])) {
            $this->name = trim($xmlarr['@']['NAME']);
        } else {
            $this->errormsg = 'Missing NAME attribute';
            $this->debug($this->errormsg);
            $result = false;
        }

        if (isset($xmlarr['@']['UNIQUE'])) {
            $unique = strtolower(trim($xmlarr['@']['UNIQUE']));
            if ($unique == 'true') {
                $this->unique = true;
            } else if ($unique == 'false') {
                $this->unique = false;
            } else {
                $this->errormsg = 'Incorrect UNIQUE attribute (true/false allowed)';
                $this->debug($this->errormsg);
                $result = false;
            }
        } else {
                $this->errormsg = 'Undefined UNIQUE attribute';
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

        if (isset($xmlarr['@']['HINTS'])) {
            $this->hints = array();
            $hints = strtolower(trim($xmlarr['@']['HINTS']));
            if ($hints !== '') {
                $hints = explode(',', $hints);
                $this->hints = array_map('trim', $hints);
            }
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

    
     public function calculateHash($recursive = false) {
        if (!$this->loaded) {
            $this->hash = null;
        } else {
            $key = $this->unique . implode (', ', $this->fields) . implode (', ', $this->hints);
            $this->hash = md5($key);
        }
    }

    
    public function xmlOutput() {
        $o = '';
        $o.= '        <INDEX NAME="' . $this->name . '"';
        if ($this->unique) {
            $unique = 'true';
        } else {
            $unique = 'false';
        }
        $o.= ' UNIQUE="' . $unique . '"';
        $o.= ' FIELDS="' . implode(', ', $this->fields) . '"';
        if ($this->hints) {
            $o.= ' HINTS="' . implode(', ', $this->hints) . '"';
        }
        if ($this->comment) {
            $o.= ' COMMENT="' . htmlspecialchars($this->comment) . '"';
        }
        $o.= '/>' . "\n";

        return $o;
    }

    
    public function setFromADOIndex($adoindex) {

                $this->unique = false;
                $fields = array_flip(array_change_key_case(array_flip($adoindex['columns'])));
        $this->fields = $fields;
                $this->loaded = true;
        $this->changed = true;
    }

    
    public function getPHP() {

        $result = '';

                $unique = $this->getUnique();
        if (!empty($unique)) {
            $result .= 'XMLDB_INDEX_UNIQUE, ';
        } else {
            $result .= 'XMLDB_INDEX_NOTUNIQUE, ';
        }
                $indexfields = $this->getFields();
        if (!empty($indexfields)) {
            $result .= 'array(' . "'".  implode("', '", $indexfields) . "')";
        } else {
            $result .= 'null';
        }
                $hints = $this->getHints();
        if (!empty($hints)) {
            $result .= ', array(' . "'".  implode("', '", $hints) . "')";
        }

                return $result;
    }

    
    public function readableInfo() {
        $o = '';
                if ($this->unique) {
            $o .= 'unique';
        } else {
            $o .= 'not unique';
        }
                $o .= ' (' . implode(', ', $this->fields) . ')';

        if ($this->hints) {
            $o .= ' [' . implode(', ', $this->hints) . ']';
        }

        return $o;
    }

    
    public function validateDefinition(xmldb_table $xmldb_table=null) {
        if (!$xmldb_table) {
            return 'Invalid xmldb_index->validateDefinition() call, $xmldb_table si required.';
        }

        $total = 0;
        foreach ($this->getFields() as $fieldname) {
            if (!$field = $xmldb_table->getField($fieldname)) {
                                continue;
            }

            switch ($field->getType()) {
                case XMLDB_TYPE_INTEGER:
                    $total += 8;                     break;

                case XMLDB_TYPE_NUMBER:
                    $total += 12;                     break;

                case XMLDB_TYPE_FLOAT:
                    $total += 8;                     break;

                case XMLDB_TYPE_CHAR:
                    if ($field->getLength() > self::INDEX_MAX_BYTES / 3) {
                        return 'Invalid index definition in table {'.$xmldb_table->getName(). '}: XMLDB_TYPE_CHAR field "'.$field->getName().'" can not be indexed because it is too long.'
                                .' Limit is '.(self::INDEX_MAX_BYTES/3).' chars.';
                    }
                    $total += ($field->getLength() * 3);                     break;

                case XMLDB_TYPE_TEXT:
                    return 'Invalid index definition in table {'.$xmldb_table->getName(). '}: XMLDB_TYPE_TEXT field "'.$field->getName().'" can not be indexed';
                    break;

                case XMLDB_TYPE_BINARY:
                    return 'Invalid index definition in table {'.$xmldb_table->getName(). '}: XMLDB_TYPE_BINARY field "'.$field->getName().'" can not be indexed';
                    break;

                case XMLDB_TYPE_DATETIME:
                    $total += 8;                     break;

                case XMLDB_TYPE_TIMESTAMP:
                    $total += 8;                     break;
            }
        }

        if ($total > self::INDEX_COMPOSED_MAX_BYTES) {
            return 'Invalid index definition in table {'.$xmldb_table->getName(). '}: the composed index on fields "'.implode(',', $this->getFields()).'" is too long.'
                    .' Limit is '.self::INDEX_COMPOSED_MAX_BYTES.' bytes / '.(self::INDEX_COMPOSED_MAX_BYTES/3).' chars.';
        }

        return null;
    }
}
