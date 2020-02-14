<?php



defined('MOODLE_INTERNAL') || die();


class xmldb_table extends xmldb_object {

    
    protected $fields;

    
    protected $keys;

    
    protected $indexes;

    
    const NAME_MAX_LENGTH = 28;

    
    public function __construct($name) {
        parent::__construct($name);
        $this->fields = array();
        $this->keys = array();
        $this->indexes = array();
    }

    
    public function addField($field, $after=null) {

                if ($this->getField($field->getName())) {
            throw new coding_exception('Duplicate field '.$field->getName().' specified in table '.$this->getName());
        }

                $prevfield = null;
        $nextfield = null;

        if (!$after) {
            $allfields = $this->getFields();
            if (!empty($allfields)) {
                end($allfields);
                $prevfield = $allfields[key($allfields)];
            }
        } else {
            $prevfield = $this->getField($after);
        }
        if ($prevfield && $prevfield->getNext()) {
            $nextfield = $this->getField($prevfield->getNext());
        }

                if ($prevfield) {
            $field->setPrevious($prevfield->getName());
            $prevfield->setNext($field->getName());
        }
        if ($nextfield) {
            $field->setNext($nextfield->getName());
            $nextfield->setPrevious($field->getName());
        }
                $field->setLoaded(true);
        $field->setChanged(true);
                $this->fields[] = $field;
                $this->orderFields($this->fields);
                $this->calculateHash(true);
                $this->setChanged(true);

        return $field;
    }

    
    public function addKey($key, $after=null) {

                if ($this->getKey($key->getName())) {
            throw new coding_exception('Duplicate key '.$key->getName().' specified in table '.$this->getName());
        }

                $newfields = $key->getFields();
        $allindexes = $this->getIndexes();
        foreach ($allindexes as $index) {
            $fields = $index->getFields();
            if ($fields === $newfields) {
                throw new coding_exception('Index '.$index->getName().' collides with key'.$key->getName().' specified in table '.$this->getName());
            }
        }

                $prevkey = null;
        $nextkey = null;

        if (!$after) {
            $allkeys = $this->getKeys();
            if (!empty($allkeys)) {
                end($allkeys);
                $prevkey = $allkeys[key($allkeys)];
            }
        } else {
            $prevkey = $this->getKey($after);
        }
        if ($prevkey && $prevkey->getNext()) {
            $nextkey = $this->getKey($prevkey->getNext());
        }

                if ($prevkey) {
            $key->setPrevious($prevkey->getName());
            $prevkey->setNext($key->getName());
        }
        if ($nextkey) {
            $key->setNext($nextkey->getName());
            $nextkey->setPrevious($key->getName());
        }
                $key->setLoaded(true);
        $key->setChanged(true);
                $this->keys[] = $key;
                $this->orderKeys($this->keys);
                $this->calculateHash(true);
                $this->setChanged(true);
    }

    
    public function addIndex($index, $after=null) {

                if ($this->getIndex($index->getName())) {
            throw new coding_exception('Duplicate index '.$index->getName().' specified in table '.$this->getName());
        }

                $newfields = $index->getFields();
        $allkeys = $this->getKeys();
        foreach ($allkeys as $key) {
            $fields = $key->getFields();
            if ($fields === $newfields) {
                throw new coding_exception('Key '.$key->getName().' collides with index'.$index->getName().' specified in table '.$this->getName());
            }
        }

                $previndex = null;
        $nextindex = null;

        if (!$after) {
            $allindexes = $this->getIndexes();
            if (!empty($allindexes)) {
                end($allindexes);
                $previndex = $allindexes[key($allindexes)];
            }
        } else {
            $previndex = $this->getIndex($after);
        }
        if ($previndex && $previndex->getNext()) {
            $nextindex = $this->getIndex($previndex->getNext());
        }

                if ($previndex) {
            $index->setPrevious($previndex->getName());
            $previndex->setNext($index->getName());
        }
        if ($nextindex) {
            $index->setNext($nextindex->getName());
            $nextindex->setPrevious($index->getName());
        }

                $index->setLoaded(true);
        $index->setChanged(true);
                $this->indexes[] = $index;
                $this->orderIndexes($this->indexes);
                $this->calculateHash(true);
                $this->setChanged(true);
    }

    
    public function getFields() {
        return $this->fields;
    }

    
    public function getKeys() {
        return $this->keys;
    }

    
    public function getIndexes() {
        return $this->indexes;
    }

    
    public function getField($fieldname) {
        $i = $this->findFieldInArray($fieldname);
        if ($i !== null) {
            return $this->fields[$i];
        }
        return null;
    }

    
    public function findFieldInArray($fieldname) {
        foreach ($this->fields as $i => $field) {
            if ($fieldname == $field->getName()) {
                return $i;
            }
        }
        return null;
    }

    
    public function orderFields() {
        $result = $this->orderElements($this->fields);
        if ($result) {
            $this->setFields($result);
            return true;
        } else {
            return false;
        }
    }

    
    public function getKey($keyname) {
        $i = $this->findKeyInArray($keyname);
        if ($i !== null) {
            return $this->keys[$i];
        }
        return null;
    }

    
    public function findKeyInArray($keyname) {
        foreach ($this->keys as $i => $key) {
            if ($keyname == $key->getName()) {
                return $i;
            }
        }
        return null;
    }

    
    public function orderKeys() {
        $result = $this->orderElements($this->keys);
        if ($result) {
            $this->setKeys($result);
            return true;
        } else {
            return false;
        }
    }

    
    public function getIndex($indexname) {
        $i = $this->findIndexInArray($indexname);
        if ($i !== null) {
            return $this->indexes[$i];
        }
        return null;
    }

    
    public function findIndexInArray($indexname) {
        foreach ($this->indexes as $i => $index) {
            if ($indexname == $index->getName()) {
                return $i;
            }
        }
        return null;
    }

    
    public function orderIndexes() {
        $result = $this->orderElements($this->indexes);
        if ($result) {
            $this->setIndexes($result);
            return true;
        } else {
            return false;
        }
    }

    
    public function setFields($fields) {
        $this->fields = $fields;
    }

    
    public function setKeys($keys) {
        $this->keys = $keys;
    }

    
    public function setIndexes($indexes) {
        $this->indexes = $indexes;
    }

    
    public function deleteField($fieldname) {

        $field = $this->getField($fieldname);
        if ($field) {
            $i = $this->findFieldInArray($fieldname);
                        $prevfield = $this->getField($field->getPrevious());
            $nextfield = $this->getField($field->getNext());
                        if ($prevfield) {
                $prevfield->setNext($field->getNext());
            }
            if ($nextfield) {
                $nextfield->setPrevious($field->getPrevious());
            }
                        unset($this->fields[$i]);
                        $this->orderFields($this->fields);
                        $this->calculateHash(true);
                        $this->setChanged(true);
        }
    }

    
    public function deleteKey($keyname) {

        $key = $this->getKey($keyname);
        if ($key) {
            $i = $this->findKeyInArray($keyname);
                        $prevkey = $this->getKey($key->getPrevious());
            $nextkey = $this->getKey($key->getNext());
                        if ($prevkey) {
                $prevkey->setNext($key->getNext());
            }
            if ($nextkey) {
                $nextkey->setPrevious($key->getPrevious());
            }
                        unset($this->keys[$i]);
                        $this->orderKeys($this->keys);
                        $this->calculateHash(true);
                        $this->setChanged(true);
        }
    }

    
    public function deleteIndex($indexname) {

        $index = $this->getIndex($indexname);
        if ($index) {
            $i = $this->findIndexInArray($indexname);
                        $previndex = $this->getIndex($index->getPrevious());
            $nextindex = $this->getIndex($index->getNext());
                        if ($previndex) {
                $previndex->setNext($index->getNext());
            }
            if ($nextindex) {
                $nextindex->setPrevious($index->getPrevious());
            }
                        unset($this->indexes[$i]);
                        $this->orderIndexes($this->indexes);
                        $this->calculateHash(true);
                        $this->setChanged(true);
        }
    }

    
    public function arr2xmldb_table($xmlarr) {

        global $CFG;

        $result = true;

                                
                if (isset($xmlarr['@']['NAME'])) {
            $this->name = trim($xmlarr['@']['NAME']);
        } else {
            $this->errormsg = 'Missing NAME attribute';
            $this->debug($this->errormsg);
            $result = false;
        }
        if (isset($xmlarr['@']['COMMENT'])) {
            $this->comment = trim($xmlarr['@']['COMMENT']);
        } else if (!empty($CFG->xmldbdisablecommentchecking)) {
            $this->comment = '';
        } else {
            $this->errormsg = 'Missing COMMENT attribute';
            $this->debug($this->errormsg);
            $result = false;
        }

                if (isset($xmlarr['#']['FIELDS']['0']['#']['FIELD'])) {
            foreach ($xmlarr['#']['FIELDS']['0']['#']['FIELD'] as $xmlfield) {
                if (!$result) {                     continue;
                }
                $name = trim($xmlfield['@']['NAME']);
                $field = new xmldb_field($name);
                $field->arr2xmldb_field($xmlfield);
                $this->fields[] = $field;
                if (!$field->isLoaded()) {
                    $this->errormsg = 'Problem loading field ' . $name;
                    $this->debug($this->errormsg);
                    $result = false;
                }
            }
        } else {
            $this->errormsg = 'Missing FIELDS section';
            $this->debug($this->errormsg);
            $result = false;
        }

                if ($result && $this->fields) {
                        if (!$this->checkNameValues($this->fields)) {
                $this->errormsg = 'Some FIELDS name values are incorrect';
                $this->debug($this->errormsg);
                $result = false;
            }
                        $this->fixPrevNext($this->fields);
                        if ($result && !$this->orderFields($this->fields)) {
                $this->errormsg = 'Error ordering the fields';
                $this->debug($this->errormsg);
                $result = false;
            }
        }

                if (isset($xmlarr['#']['KEYS']['0']['#']['KEY'])) {
            foreach ($xmlarr['#']['KEYS']['0']['#']['KEY'] as $xmlkey) {
                if (!$result) {                     continue;
                }
                $name = trim($xmlkey['@']['NAME']);
                $key = new xmldb_key($name);
                $key->arr2xmldb_key($xmlkey);
                $this->keys[] = $key;
                if (!$key->isLoaded()) {
                    $this->errormsg = 'Problem loading key ' . $name;
                    $this->debug($this->errormsg);
                    $result = false;
                }
            }
        } else {
            $this->errormsg = 'Missing KEYS section (at least one PK must exist)';
            $this->debug($this->errormsg);
            $result = false;
        }

                if ($result && $this->keys) {
                        if (!$this->checkNameValues($this->keys)) {
                $this->errormsg = 'Some KEYS name values are incorrect';
                $this->debug($this->errormsg);
                $result = false;
            }
                        $this->fixPrevNext($this->keys);
                        if ($result && !$this->orderKeys($this->keys)) {
                $this->errormsg = 'Error ordering the keys';
                $this->debug($this->errormsg);
                $result = false;
            }
                                            }

                if (isset($xmlarr['#']['INDEXES']['0']['#']['INDEX'])) {
            foreach ($xmlarr['#']['INDEXES']['0']['#']['INDEX'] as $xmlindex) {
                if (!$result) {                     continue;
                }
                $name = trim($xmlindex['@']['NAME']);
                $index = new xmldb_index($name);
                $index->arr2xmldb_index($xmlindex);
                $this->indexes[] = $index;
                if (!$index->isLoaded()) {
                    $this->errormsg = 'Problem loading index ' . $name;
                    $this->debug($this->errormsg);
                    $result = false;
                }
            }
        }

                if ($result && $this->indexes) {
                        if (!$this->checkNameValues($this->indexes)) {
                $this->errormsg = 'Some INDEXES name values are incorrect';
                $this->debug($this->errormsg);
                $result = false;
            }
                        $this->fixPrevNext($this->indexes);
                        if ($result && !$this->orderIndexes($this->indexes)) {
                $this->errormsg = 'Error ordering the indexes';
                $this->debug($this->errormsg);
                $result = false;
            }
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
            $key = $this->name . $this->comment;
            if ($this->fields) {
                foreach ($this->fields as $fie) {
                    $field = $this->getField($fie->getName());
                    if ($recursive) {
                        $field->calculateHash($recursive);
                    }
                    $key .= $field->getHash();
                }
            }
            if ($this->keys) {
                foreach ($this->keys as $ke) {
                    $k = $this->getKey($ke->getName());
                    if ($recursive) {
                        $k->calculateHash($recursive);
                    }
                    $key .= $k->getHash();
                }
            }
            if ($this->indexes) {
                foreach ($this->indexes as $in) {
                    $index = $this->getIndex($in->getName());
                    if ($recursive) {
                        $index->calculateHash($recursive);
                    }
                    $key .= $index->getHash();
                }
            }
            $this->hash = md5($key);
        }
    }

    
    public function validateDefinition(xmldb_table $xmldb_table=null) {
                $name = $this->getName();
        if (strlen($name) > self::NAME_MAX_LENGTH) {
            return 'Invalid table name {'.$name.'}: name is too long. Limit is 28 chars.';
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            return 'Invalid table name {'.$name.'}: name includes invalid characters.';
        }

        return null;
    }

    
    public function xmlOutput() {
        $o = '';
        $o.= '    <TABLE NAME="' . $this->name . '"';
        if ($this->comment) {
            $o.= ' COMMENT="' . htmlspecialchars($this->comment) . '"';
        }
        $o.= '>' . "\n";
                if ($this->fields) {
            $o.= '      <FIELDS>' . "\n";
            foreach ($this->fields as $field) {
                $o.= $field->xmlOutput();
            }
            $o.= '      </FIELDS>' . "\n";
        }
                if ($this->keys) {
            $o.= '      <KEYS>' . "\n";
            foreach ($this->keys as $key) {
                $o.= $key->xmlOutput();
            }
            $o.= '      </KEYS>' . "\n";
        }
                if ($this->indexes) {
            $o.= '      <INDEXES>' . "\n";
            foreach ($this->indexes as $index) {
                $o.= $index->xmlOutput();
            }
            $o.= '      </INDEXES>' . "\n";
        }
        $o.= '    </TABLE>' . "\n";

        return $o;
    }

    
    public function add_field($name, $type, $precision=null, $unsigned=null, $notnull=null, $sequence=null, $default=null, $previous=null) {
        $field = new xmldb_field($name, $type, $precision, $unsigned, $notnull, $sequence, $default);
        $this->addField($field, $previous);

        return $field;
    }

    
    public function add_key($name, $type, $fields, $reftable=null, $reffields=null) {
        $key = new xmldb_key($name, $type, $fields, $reftable, $reffields);
        $this->addKey($key);
    }

    
    public function add_index($name, $type, $fields, $hints = array()) {
        $index = new xmldb_index($name, $type, $fields, $hints);
        $this->addIndex($index);
    }

    
    public function getAllErrors() {

        $errors = array();
                if ($this->getError()) {
            $errors[] = $this->getError();
        }
                if ($fields = $this->getFields()) {
            foreach ($fields as $field) {
                if ($field->getError()) {
                    $errors[] = $field->getError();
                }
            }
        }
                if ($keys = $this->getKeys()) {
            foreach ($keys as $key) {
                if ($key->getError()) {
                    $errors[] = $key->getError();
                }
            }
        }
                if ($indexes = $this->getIndexes()) {
            foreach ($indexes as $index) {
                if ($index->getError()) {
                    $errors[] = $index->getError();
                }
            }
        }
                if (count($errors)) {
            return $errors;
        } else {
            return false;
        }
    }
}
