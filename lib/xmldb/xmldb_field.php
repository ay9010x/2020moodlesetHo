<?php



defined('MOODLE_INTERNAL') || die();


class xmldb_field extends xmldb_object {

    
    protected $type;

    
    protected $length;

    
    protected $notnull;

    
    protected $default;

    
    protected $sequence;

    
    protected $decimals;

    
    const CHAR_MAX_LENGTH = 1333;


    
    const INTEGER_MAX_LENGTH = 20;

    
    const NUMBER_MAX_LENGTH = 20;

    
    const FLOAT_MAX_LENGTH = 20;

    
    const NAME_MAX_LENGTH = 30;

    
    public function __construct($name, $type=null, $precision=null, $unsigned=null, $notnull=null, $sequence=null, $default=null, $previous=null) {
        $this->type = null;
        $this->length = null;
        $this->notnull = false;
        $this->default = null;
        $this->sequence = false;
        $this->decimals = null;
        parent::__construct($name);
        $this->set_attributes($type, $precision, $unsigned, $notnull, $sequence, $default, $previous);
    }

    
    public function set_attributes($type, $precision=null, $unsigned=null, $notnull=null, $sequence=null, $default=null, $previous=null) {
        $this->type = $type;
                $precisionarr = explode(',', $precision);
        if (isset($precisionarr[0])) {
            $this->length = trim($precisionarr[0]);
        }
        if (isset($precisionarr[1])) {
            $this->decimals = trim($precisionarr[1]);
        }
        $this->precision = $type;
        $this->notnull = !empty($notnull) ? true : false;
        $this->sequence = !empty($sequence) ? true : false;
        $this->setDefault($default);

        if ($this->type == XMLDB_TYPE_BINARY || $this->type == XMLDB_TYPE_TEXT) {
            $this->length = null;
            $this->decimals = null;
        }

        $this->previous = $previous;
    }

    
    public function getType() {
        return $this->type;
    }

    
    public function getLength() {
        return $this->length;
    }

    
    public function getDecimals() {
        return $this->decimals;
    }

    
    public function getNotNull() {
        return $this->notnull;
    }

    
    public function getUnsigned() {
        return false;
    }

    
    public function getSequence() {
        return $this->sequence;
    }

    
    public function getDefault() {
        return $this->default;
    }

    
    public function setType($type) {
        $this->type = $type;
    }

    
    public function setLength($length) {
        $this->length = $length;
    }

    
    public function setDecimals($decimals) {
        $this->decimals = $decimals;
    }

    
    public function setUnsigned($unsigned=true) {
    }

    
    public function setNotNull($notnull=true) {
        $this->notnull = $notnull;
    }

    
    public function setSequence($sequence=true) {
        $this->sequence = $sequence;
    }

    
    public function setDefault($default) {
                        if ($this->type == XMLDB_TYPE_CHAR && $this->notnull && $default === '') {
            $this->errormsg = 'XMLDB has detected one CHAR NOT NULL column (' . $this->name . ") with '' (empty string) as DEFAULT value. This type of columns must have one meaningful DEFAULT declared or none (NULL). XMLDB have fixed it automatically changing it to none (NULL). The process will continue ok and proper defaults will be created accordingly with each DB requirements. Please fix it in source (XML and/or upgrade script) to avoid this message to be displayed.";
            $this->debug($this->errormsg);
            $default = null;
        }
                if (($this->type == XMLDB_TYPE_TEXT || $this->type == XMLDB_TYPE_BINARY) && $default !== null) {
            $this->errormsg = 'XMLDB has detected one TEXT/BINARY column (' . $this->name . ") with some DEFAULT defined. This type of columns cannot have any default value. Please fix it in source (XML and/or upgrade script) to avoid this message to be displayed.";
            $this->debug($this->errormsg);
            $default = null;
        }
        $this->default = $default;
    }

    
    public function arr2xmldb_field($xmlarr) {

        $result = true;

                                
                        if (isset($xmlarr['@']['NAME'])) {
            $this->name = trim($xmlarr['@']['NAME']);
        } else {
            $this->errormsg = 'Missing NAME attribute';
            $this->debug($this->errormsg);
            $result = false;
        }

        if (isset($xmlarr['@']['TYPE'])) {
                        $type = $this->getXMLDBFieldType(trim($xmlarr['@']['TYPE']));
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

        if (isset($xmlarr['@']['LENGTH'])) {
            $length = trim($xmlarr['@']['LENGTH']);
                        if ($this->type == XMLDB_TYPE_INTEGER ||
                $this->type == XMLDB_TYPE_NUMBER ||
                $this->type == XMLDB_TYPE_CHAR) {
                if (!(is_numeric($length)&&(intval($length)==floatval($length)))) {
                    $this->errormsg = 'Incorrect LENGTH attribute for int, number or char fields';
                    $this->debug($this->errormsg);
                    $result = false;
                } else if (!$length) {
                    $this->errormsg = 'Zero LENGTH attribute';
                    $this->debug($this->errormsg);
                    $result = false;
                }
            }
                        if ($this->type == XMLDB_TYPE_TEXT ||
                $this->type == XMLDB_TYPE_BINARY) {
                $length = null;
            }
                        $this->length = $length;
        }

        if (isset($xmlarr['@']['NOTNULL'])) {
            $notnull = strtolower(trim($xmlarr['@']['NOTNULL']));
            if ($notnull == 'true') {
                $this->notnull = true;
            } else if ($notnull == 'false') {
                $this->notnull = false;
            } else {
                $this->errormsg = 'Incorrect NOTNULL attribute (true/false allowed)';
                $this->debug($this->errormsg);
                $result = false;
            }
        }

        if (isset($xmlarr['@']['SEQUENCE'])) {
            $sequence = strtolower(trim($xmlarr['@']['SEQUENCE']));
            if ($sequence == 'true') {
                $this->sequence = true;
            } else if ($sequence == 'false') {
                $this->sequence = false;
            } else {
                $this->errormsg = 'Incorrect SEQUENCE attribute (true/false allowed)';
                $this->debug($this->errormsg);
                $result = false;
            }
        }

        if (isset($xmlarr['@']['DEFAULT'])) {
            $this->setDefault(trim($xmlarr['@']['DEFAULT']));
        }

        $decimals = null;
        if (isset($xmlarr['@']['DECIMALS'])) {
            $decimals = trim($xmlarr['@']['DECIMALS']);
                        if ($this->type == XMLDB_TYPE_NUMBER ||
                $this->type == XMLDB_TYPE_FLOAT) {
                if (!(is_numeric($decimals)&&(intval($decimals)==floatval($decimals)))) {
                    $this->errormsg = 'Incorrect DECIMALS attribute for number field';
                    $this->debug($this->errormsg);
                    $result = false;
                } else if ($this->length <= $decimals){
                    $this->errormsg = 'Incorrect DECIMALS attribute (bigget than length)';
                    $this->debug($this->errormsg);
                    $result = false;
                }
            } else {
                $this->errormsg = 'Incorrect DECIMALS attribute for non-number field';
                $this->debug($this->errormsg);
                $result = false;
            }
        } else {
            if ($this->type == XMLDB_TYPE_NUMBER) {
                $decimals = 0;
            }
        }
                if ($this->type == XMLDB_TYPE_NUMBER ||
            $this->type == XMLDB_TYPE_FLOAT) {
            $this->decimals = $decimals;
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

    
    public function getXMLDBFieldType($type) {

        $result = XMLDB_TYPE_INCORRECT;

        switch (strtolower($type)) {
            case 'int':
                $result = XMLDB_TYPE_INTEGER;
                break;
            case 'number':
                $result = XMLDB_TYPE_NUMBER;
                break;
            case 'float':
                $result = XMLDB_TYPE_FLOAT;
                break;
            case 'char':
                $result = XMLDB_TYPE_CHAR;
                break;
            case 'text':
                $result = XMLDB_TYPE_TEXT;
                break;
            case 'binary':
                $result = XMLDB_TYPE_BINARY;
                break;
            case 'datetime':
                $result = XMLDB_TYPE_DATETIME;
                break;
        }
                return $result;
    }

    
    public function getXMLDBTypeName($type) {

        $result = "";

        switch (strtolower($type)) {
            case XMLDB_TYPE_INTEGER:
                $result = 'int';
                break;
            case XMLDB_TYPE_NUMBER:
                $result = 'number';
                break;
            case XMLDB_TYPE_FLOAT:
                $result = 'float';
                break;
            case XMLDB_TYPE_CHAR:
                $result = 'char';
                break;
            case XMLDB_TYPE_TEXT:
                $result = 'text';
                break;
            case XMLDB_TYPE_BINARY:
                $result = 'binary';
                break;
            case XMLDB_TYPE_DATETIME:
                $result = 'datetime';
                break;
        }
                return $result;
    }

    
     public function calculateHash($recursive = false) {
        if (!$this->loaded) {
            $this->hash = null;
        } else {
            $defaulthash = is_null($this->default) ? '' : sha1($this->default);
            $key = $this->name . $this->type . $this->length .
                   $this->notnull . $this->sequence .
                   $this->decimals . $this->comment . $defaulthash;
            $this->hash = md5($key);
        }
    }

    
    public function xmlOutput() {
        $o = '';
        $o.= '        <FIELD NAME="' . $this->name . '"';
        $o.= ' TYPE="' . $this->getXMLDBTypeName($this->type) . '"';
        if ($this->length) {
            $o.= ' LENGTH="' . $this->length . '"';
        }
        if ($this->notnull) {
            $notnull = 'true';
        } else {
            $notnull = 'false';
        }
        $o.= ' NOTNULL="' . $notnull . '"';
        if (!$this->sequence && $this->default !== null) {
            $o.= ' DEFAULT="' . $this->default . '"';
        }
        if ($this->sequence) {
            $sequence = 'true';
        } else {
            $sequence = 'false';
        }
        $o.= ' SEQUENCE="' . $sequence . '"';
        if ($this->decimals !== null) {
            $o.= ' DECIMALS="' . $this->decimals . '"';
        }
        if ($this->comment) {
            $o.= ' COMMENT="' . htmlspecialchars($this->comment) . '"';
        }
        $o.= '/>' . "\n";

        return $o;
    }

    
    public function setFromADOField($adofield) {

                switch (strtolower($adofield->type)) {
            case 'int':
            case 'tinyint':
            case 'smallint':
            case 'bigint':
            case 'integer':
                $this->type = XMLDB_TYPE_INTEGER;
                break;
            case 'number':
            case 'decimal':
            case 'dec':
            case 'numeric':
                $this->type = XMLDB_TYPE_NUMBER;
                break;
            case 'float':
            case 'double':
                $this->type = XMLDB_TYPE_FLOAT;
                break;
            case 'char':
            case 'varchar':
            case 'enum':
                $this->type = XMLDB_TYPE_CHAR;
                break;
            case 'text':
            case 'tinytext':
            case 'mediumtext':
            case 'longtext':
                $this->type = XMLDB_TYPE_TEXT;
                break;
            case 'blob':
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
                $this->type = XMLDB_TYPE_BINARY;
                break;
            case 'datetime':
            case 'timestamp':
                $this->type = XMLDB_TYPE_DATETIME;
                break;
            default:
                $this->type = XMLDB_TYPE_TEXT;
        }
                if ($adofield->max_length > 0 &&
               ($this->type == XMLDB_TYPE_INTEGER ||
                $this->type == XMLDB_TYPE_NUMBER  ||
                $this->type == XMLDB_TYPE_FLOAT   ||
                $this->type == XMLDB_TYPE_CHAR)) {
            $this->length = $adofield->max_length;
        }
        if ($this->type == XMLDB_TYPE_TEXT) {
            $this->length = null;
        }
        if ($this->type == XMLDB_TYPE_BINARY) {
            $this->length = null;
        }
                if ($adofield->max_length > 0 &&
            $adofield->scale &&
               ($this->type == XMLDB_TYPE_NUMBER ||
                $this->type == XMLDB_TYPE_FLOAT)) {
            $this->decimals = $adofield->scale;
        }
                if ($adofield->not_null) {
            $this->notnull = true;
        }
                if ($adofield->has_default) {
            $this->default = $adofield->default_value;
        }
                if ($adofield->auto_increment) {
            $this->sequence = true;
        }
                $this->loaded = true;
        $this->changed = true;
    }

    
    public function getPHP($includeprevious=true) {

        $result = '';

                switch ($this->getType()) {
            case XMLDB_TYPE_INTEGER:
                $result .= 'XMLDB_TYPE_INTEGER' . ', ';
                break;
            case XMLDB_TYPE_NUMBER:
                $result .= 'XMLDB_TYPE_NUMBER' . ', ';
                break;
            case XMLDB_TYPE_FLOAT:
                $result .= 'XMLDB_TYPE_FLOAT' . ', ';
                break;
            case XMLDB_TYPE_CHAR:
                $result .= 'XMLDB_TYPE_CHAR' . ', ';
                break;
            case XMLDB_TYPE_TEXT:
                $result .= 'XMLDB_TYPE_TEXT' . ', ';
                break;
            case XMLDB_TYPE_BINARY:
                $result .= 'XMLDB_TYPE_BINARY' . ', ';
                break;
            case XMLDB_TYPE_DATETIME:
                $result .= 'XMLDB_TYPE_DATETIME' . ', ';
                break;
            case XMLDB_TYPE_TIMESTAMP:
                $result .= 'XMLDB_TYPE_TIMESTAMP' . ', ';
                break;
        }
                $length = $this->getLength();
        $decimals = $this->getDecimals();
        if (!empty($length)) {
            $result .= "'" . $length;
            if (!empty($decimals)) {
                $result .= ', ' . $decimals;
            }
            $result .= "', ";
        } else {
            $result .= 'null, ';
        }
                $result .= 'null, ';
                $notnull = $this->getNotnull();
        if (!empty($notnull)) {
            $result .= 'XMLDB_NOTNULL' . ', ';
        } else {
            $result .= 'null, ';
        }
                $sequence = $this->getSequence();
        if (!empty($sequence)) {
            $result .= 'XMLDB_SEQUENCE' . ', ';
        } else {
            $result .= 'null, ';
        }
                $default =  $this->getDefault();
        if ($default !== null && !$this->getSequence()) {
            $result .= "'" . $default . "'";
        } else {
            $result .= 'null';
        }
                if ($includeprevious) {
            $previous = $this->getPrevious();
            if (!empty($previous)) {
                $result .= ", '" . $previous . "'";
            } else {
                $result .= ', null';
            }
        }
                return $result;
    }

    
    public function readableInfo() {
        $o = '';
                $o .= $this->getXMLDBTypeName($this->type);
                if ($this->type == XMLDB_TYPE_INTEGER ||
            $this->type == XMLDB_TYPE_NUMBER  ||
            $this->type == XMLDB_TYPE_FLOAT   ||
            $this->type == XMLDB_TYPE_CHAR) {
            if ($this->length) {
                $o .= ' (' . $this->length;
                if ($this->type == XMLDB_TYPE_NUMBER  ||
                    $this->type == XMLDB_TYPE_FLOAT) {
                    if ($this->decimals !== null) {
                        $o .= ', ' . $this->decimals;
                    }
                }
                $o .= ')';
            }
        }
                if ($this->notnull) {
            $o .= ' not null';
        }
                if ($this->default !== null) {
            $o .= ' default ';
            if ($this->type == XMLDB_TYPE_CHAR ||
                $this->type == XMLDB_TYPE_TEXT) {
                    $o .= "'" . $this->default . "'";
            } else {
                $o .= $this->default;
            }
        }
                if ($this->sequence) {
            $o .= ' auto-numbered';
        }

        return $o;
    }

    
    public function validateDefinition(xmldb_table $xmldb_table=null) {
        if (!$xmldb_table) {
            return 'Invalid xmldb_field->validateDefinition() call, $xmldb_table is required.';
        }

        $name = $this->getName();
        if (strlen($name) > self::NAME_MAX_LENGTH) {
            return 'Invalid field name in table {'.$xmldb_table->getName().'}: field "'.$this->getName().'" name is too long.'
                .' Limit is '.self::NAME_MAX_LENGTH.' chars.';
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            return 'Invalid field name in table {'.$xmldb_table->getName().'}: field "'.$this->getName().'" name includes invalid characters.';
        }

        switch ($this->getType()) {
            case XMLDB_TYPE_INTEGER:
                $length = $this->getLength();
                if (!is_number($length) or $length <= 0 or $length > self::INTEGER_MAX_LENGTH) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName().'}: XMLDB_TYPE_INTEGER field "'.$this->getName().'" has invalid length';
                }
                $default = $this->getDefault();
                if (!empty($default) and !is_number($default)) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName().'}: XMLDB_TYPE_INTEGER field "'.$this->getName().'" has invalid default';
                }
                break;

            case XMLDB_TYPE_NUMBER:
                $maxlength = self::NUMBER_MAX_LENGTH;
                if ($xmldb_table->getName() === 'question_numerical_units' and $name === 'multiplier') {
                                        $maxlength = 40;
                }
                $length = $this->getLength();
                if (!is_number($length) or $length <= 0 or $length > $maxlength) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName().'}: XMLDB_TYPE_NUMBER field "'.$this->getName().'" has invalid length';
                }
                $decimals = $this->getDecimals();
                $decimals = empty($decimals) ? 0 : $decimals;                 if (!is_number($decimals) or $decimals < 0 or $decimals > $length) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName().'}: XMLDB_TYPE_NUMBER field "'.$this->getName().'" has invalid decimals';
                }
                $default = $this->getDefault();
                if (!empty($default) and !is_numeric($default)) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName().'}: XMLDB_TYPE_NUMBER field "'.$this->getName().'" has invalid default';
                }
                break;

            case XMLDB_TYPE_FLOAT:
                $length = $this->getLength();
                $length = empty($length) ? 6 : $length;                 if (!is_number($length) or $length <= 0 or $length > self::FLOAT_MAX_LENGTH) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName().'}: XMLDB_TYPE_FLOAT field "'.$this->getName().'" has invalid length';
                }
                $decimals = $this->getDecimals();
                $decimals = empty($decimals) ? 0 : $decimals;                 if (!is_number($decimals) or $decimals < 0 or $decimals > $length) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName().'}: XMLDB_TYPE_FLOAT field "'.$this->getName().'" has invalid decimals';
                }
                $default = $this->getDefault();
                if (!empty($default) and !is_numeric($default)) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName().'}: XMLDB_TYPE_FLOAT field "'.$this->getName().'" has invalid default';
                }
                break;

            case XMLDB_TYPE_CHAR:
                if ($this->getLength() > self::CHAR_MAX_LENGTH) {
                    return 'Invalid field definition in table {'.$xmldb_table->getName(). '}: XMLDB_TYPE_CHAR field "'.$this->getName().'" is too long.'
                           .' Limit is '.self::CHAR_MAX_LENGTH.' chars.';
                }
                break;

            case XMLDB_TYPE_TEXT:
                break;

            case XMLDB_TYPE_BINARY:
                break;

            case XMLDB_TYPE_DATETIME:
                break;

            case XMLDB_TYPE_TIMESTAMP:
                break;
        }

        return null;
    }
}
