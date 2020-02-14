<?php



defined('MOODLE_INTERNAL') || die();


class xmldb_structure extends xmldb_object {

    
    protected $path;

    
    protected $version;

    
    protected $tables;

    
    public function __construct($name) {
        parent::__construct($name);
        $this->path = null;
        $this->version = null;
        $this->tables = array();
    }

    
    public function getPath() {
        return $this->path;
    }

    
    public function getVersion() {
        return $this->version;
    }

    
    public function getTable($tablename) {
        $i = $this->findTableInArray($tablename);
        if ($i !== null) {
            return $this->tables[$i];
        }
        return null;
    }

    
    public function findTableInArray($tablename) {
        foreach ($this->tables as $i => $table) {
            if ($tablename == $table->getName()) {
                return $i;
            }
        }
        return null;
    }

    
    public function orderTables() {
        $result = $this->orderElements($this->tables);
        if ($result) {
            $this->setTables($result);
            return true;
        } else {
            return false;
        }
    }

    
    public function getTables() {
        return $this->tables;
    }

    
    public function setVersion($version) {
        $this->version = $version;
    }

    
    public function addTable($table, $after=null) {

                $prevtable = null;
        $nexttable = null;

        if (!$after) {
            if ($this->tables) {
                end($this->tables);
                $prevtable = $this->tables[key($this->tables)];
            }
        } else {
            $prevtable = $this->getTable($after);
        }
        if ($prevtable && $prevtable->getNext()) {
            $nexttable = $this->getTable($prevtable->getNext());
        }

                if ($prevtable) {
            $table->setPrevious($prevtable->getName());
            $prevtable->setNext($table->getName());
        }
        if ($nexttable) {
            $table->setNext($nexttable->getName());
            $nexttable->setPrevious($table->getName());
        }
                $table->setLoaded(true);
        $table->setChanged(true);
                $this->tables[] = $table;
                $this->orderTables($this->tables);
                $this->calculateHash(true);
                $this->setVersion(userdate(time(), '%Y%m%d', 99, false));
        $this->setChanged(true);
    }

    
    public function deleteTable($tablename) {

        $table = $this->getTable($tablename);
        if ($table) {
            $i = $this->findTableInArray($tablename);
                        $prevtable = $this->getTable($table->getPrevious());
            $nexttable = $this->getTable($table->getNext());
                        if ($prevtable) {
                $prevtable->setNext($table->getNext());
            }
            if ($nexttable) {
                $nexttable->setPrevious($table->getPrevious());
            }
                        unset($this->tables[$i]);
                        $this->orderTables($this->tables);
                        $this->calculateHash(true);
                        $this->setVersion(userdate(time(), '%Y%m%d', 99, false));
            $this->setChanged(true);
        }
    }

    
    public function setTables($tables) {
        $this->tables = $tables;
    }

    
    public function arr2xmldb_structure($xmlarr) {

        global $CFG;

        $result = true;

                                
                if (isset($xmlarr['XMLDB']['@']['PATH'])) {
            $this->path = trim($xmlarr['XMLDB']['@']['PATH']);
        } else {
            $this->errormsg = 'Missing PATH attribute';
            $this->debug($this->errormsg);
            $result = false;
        }
        if (isset($xmlarr['XMLDB']['@']['VERSION'])) {
            $this->version = trim($xmlarr['XMLDB']['@']['VERSION']);
        } else {
            $this->errormsg = 'Missing VERSION attribute';
            $this->debug($this->errormsg);
            $result = false;
        }
        if (isset($xmlarr['XMLDB']['@']['COMMENT'])) {
            $this->comment = trim($xmlarr['XMLDB']['@']['COMMENT']);
        } else if (!empty($CFG->xmldbdisablecommentchecking)) {
            $this->comment = '';
        } else {
            $this->errormsg = 'Missing COMMENT attribute';
            $this->debug($this->errormsg);
            $result = false;
        }

                if (isset($xmlarr['XMLDB']['#']['TABLES']['0']['#']['TABLE'])) {
            foreach ($xmlarr['XMLDB']['#']['TABLES']['0']['#']['TABLE'] as $xmltable) {
                if (!$result) {                     continue;
                }
                $name = trim($xmltable['@']['NAME']);
                $table = new xmldb_table($name);
                $table->arr2xmldb_table($xmltable);
                $this->tables[] = $table;
                if (!$table->isLoaded()) {
                    $this->errormsg = 'Problem loading table ' . $name;
                    $this->debug($this->errormsg);
                    $result = false;
                }
            }
        } else {
            $this->errormsg = 'Missing TABLES section';
            $this->debug($this->errormsg);
            $result = false;
        }

                if ($result && $this->tables) {
                        if (!$this->checkNameValues($this->tables)) {
                $this->errormsg = 'Some TABLES name values are incorrect';
                $this->debug($this->errormsg);
                $result = false;
            }
                        $this->fixPrevNext($this->tables);
                        if ($result && !$this->orderTables($this->tables)) {
                $this->errormsg = 'Error ordering the tables';
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
            $key = $this->name . $this->path . $this->comment;
            if ($this->tables) {
                foreach ($this->tables as $tbl) {
                    $table = $this->getTable($tbl->getName());
                    if ($recursive) {
                        $table->calculateHash($recursive);
                    }
                    $key .= $table->getHash();
                }
            }
            $this->hash = md5($key);
        }
    }

    
    public function xmlOutput() {
        $o = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
        $o.= '<XMLDB PATH="' . $this->path . '"';
        $o.= ' VERSION="' . $this->version . '"';
        if ($this->comment) {
            $o.= ' COMMENT="' . htmlspecialchars($this->comment) . '"'."\n";
        }
        $rel = array_fill(0, count(explode('/', $this->path)), '..');
        $rel = implode('/', $rel);
        $o.= '    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n";
        $o.= '    xsi:noNamespaceSchemaLocation="'.$rel.'/lib/xmldb/xmldb.xsd"'."\n";
        $o.= '>' . "\n";
                if ($this->tables) {
            $o.= '  <TABLES>' . "\n";
            foreach ($this->tables as $table) {
                $o.= $table->xmlOutput();
            }
            $o.= '  </TABLES>' . "\n";
        }
        $o.= '</XMLDB>';

        return $o;
    }

    
    public function getTableUses($tablename) {

        $uses = array();

                        if ($this->tables) {
            foreach ($this->tables as $table) {
                $keys = $table->getKeys();
                if ($keys) {
                    foreach ($keys as $key) {
                        if ($key->getType() == XMLDB_KEY_FOREIGN) {
                            if ($tablename == $key->getRefTable()) {
                                $uses[] = 'table ' . $table->getName() . ' key ' . $key->getName();
                            }
                        }
                    }
                }
            }
        }

                if (!empty($uses)) {
            return $uses;
        } else {
            return false;
        }
    }

    
    public function getFieldUses($tablename, $fieldname) {

        $uses = array();

                $table = $this->getTable($tablename);
        if ($keys = $table->getKeys()) {
            foreach ($keys as $key) {
                if (in_array($fieldname, $key->getFields()) ||
                    in_array($fieldname, $key->getRefFields())) {
                        $uses[] = 'table ' . $table->getName() . ' key ' . $key->getName();
                }
            }
        }
                $table = $this->getTable($tablename);
        if ($indexes = $table->getIndexes()) {
            foreach ($indexes as $index) {
                if (in_array($fieldname, $index->getFields())) {
                    $uses[] = 'table ' . $table->getName() . ' index ' . $index->getName();
                }
            }
        }
                        if ($this->tables) {
            foreach ($this->tables as $table) {
                $keys = $table->getKeys();
                if ($keys) {
                    foreach ($keys as $key) {
                        if ($key->getType() == XMLDB_KEY_FOREIGN) {
                            if ($tablename == $key->getRefTable()) {
                                $reffieds = $key->getRefFields();
                                if (in_array($fieldname, $key->getRefFields())) {
                                    $uses[] = 'table ' . $table->getName() . ' key ' . $key->getName();
                                }
                            }
                        }
                    }
                }
            }
        }

                if (!empty($uses)) {
            return $uses;
        } else {
            return false;
        }
    }

    
    public function getKeyUses($tablename, $keyname) {

        $uses = array();

                        $mytable = $this->getTable($tablename);
        $mykey = $mytable->getKey($keyname);
        if ($this->tables && $mykey) {
            foreach ($this->tables as $table) {
                $allkeys = $table->getKeys();
                if ($allkeys) {
                    foreach ($allkeys as $key) {
                        if ($key->getType() != XMLDB_KEY_FOREIGN) {
                            continue;
                        }
                        if ($key->getRefTable() == $tablename &&
                            implode(',', $key->getRefFields()) == implode(',', $mykey->getFields())) {
                                $uses[] = 'table ' . $table->getName() . ' key ' . $key->getName();
                        }
                    }
                }
            }
        }

                if (!empty($uses)) {
            return $uses;
        } else {
            return false;
        }
    }

    
    public function getIndexUses($tablename, $indexname) {

        $uses = array();

                
                if (!empty($uses)) {
            return $uses;
        } else {
            return false;
        }
    }

    
    public function getAllErrors() {

        $errors = array();
                if ($this->getError()) {
            $errors[] = $this->getError();
        }
                if ($this->tables) {
            foreach ($this->tables as $table) {
                if ($tableerrors = $table->getAllErrors()) {

                }
            }
                        if ($tableerrors) {
                $errors = array_merge($errors, $tableerrors);
            }
        }
                if (count($errors)) {
            return $errors;
        } else {
            return false;
        }
    }
}
