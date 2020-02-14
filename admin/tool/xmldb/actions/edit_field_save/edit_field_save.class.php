<?php




class edit_field_save extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
            'fieldnameempty' => 'tool_xmldb',
            'incorrectfieldname' => 'tool_xmldb',
            'duplicatefieldname' => 'tool_xmldb',
            'integerincorrectlength' => 'tool_xmldb',
            'numberincorrectlength' => 'tool_xmldb',
            'floatincorrectlength' => 'tool_xmldb',
            'charincorrectlength' => 'tool_xmldb',
            'numberincorrectdecimals' => 'tool_xmldb',
            'floatincorrectdecimals' => 'tool_xmldb',
            'defaultincorrect' => 'tool_xmldb',
            'back' => 'tool_xmldb',
            'administration' => ''
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                        $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB;

        
        if (!data_submitted()) {             print_error('wrongcall', 'error');
        }

                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;

        $tableparam = strtolower(required_param('table', PARAM_PATH));
        $fieldparam = strtolower(required_param('field', PARAM_PATH));
        $name = substr(trim(strtolower(optional_param('name', $fieldparam, PARAM_PATH))),0,xmldb_field::NAME_MAX_LENGTH);

        $comment = required_param('comment', PARAM_CLEAN);
        $comment = trim($comment);

        $type       = required_param('type', PARAM_INT);
        $length     = strtolower(optional_param('length', NULL, PARAM_ALPHANUM));
        $decimals   = optional_param('decimals', NULL, PARAM_INT);
        $notnull    = optional_param('notnull', false, PARAM_BOOL);
        $sequence   = optional_param('sequence', false, PARAM_BOOL);
        $default    = optional_param('default', NULL, PARAM_PATH);
        $default    = trim($default);

        $editeddir = $XMLDB->editeddirs[$dirpath];
        $structure = $editeddir->xml_file->getStructure();
        $table = $structure->getTable($tableparam);
        $field = $table->getField($fieldparam);
        $oldhash = $field->getHash();

        $errors = array(); 
                if ($sequence) {
            $notnull  = true;
            $default  = NULL;
        }
        if ($type != XMLDB_TYPE_NUMBER && $type != XMLDB_TYPE_FLOAT) {
            $decimals = NULL;
        }
        if ($type == XMLDB_TYPE_BINARY) {
            $default = NULL;
        }
        if ($default === '') {
            $default = NULL;
        }

                        if (empty($name)) {
            $errors[] = $this->str['fieldnameempty'];
        }
                if ($name == 'changeme') {
            $errors[] = $this->str['incorrectfieldname'];
        }
                if ($fieldparam != $name && $table->getField($name)) {
            $errors[] = $this->str['duplicatefieldname'];
        }
                if ($type == XMLDB_TYPE_INTEGER) {
            if (!(is_numeric($length) && !empty($length) && intval($length)==floatval($length) &&
                  $length > 0 && $length <= xmldb_field::INTEGER_MAX_LENGTH)) {
                $errors[] = $this->str['integerincorrectlength'];
            }
            if (!(empty($default) || (is_numeric($default) &&
                                       !empty($default) &&
                                       intval($default)==floatval($default)))) {
                $errors[] = $this->str['defaultincorrect'];
            }
        }
                if ($type == XMLDB_TYPE_NUMBER) {
            if (!(is_numeric($length) && !empty($length) && intval($length)==floatval($length) &&
                  $length > 0 && $length <= xmldb_field::NUMBER_MAX_LENGTH)) {
                $errors[] = $this->str['numberincorrectlength'];
            }
            if (!(empty($decimals) || (is_numeric($decimals) &&
                                       !empty($decimals) &&
                                       intval($decimals)==floatval($decimals) &&
                                       $decimals >= 0 &&
                                       $decimals < $length))) {
                $errors[] = $this->str['numberincorrectdecimals'];
            }
            if (!(empty($default) || (is_numeric($default) &&
                                       !empty($default)))) {
                $errors[] = $this->str['defaultincorrect'];
            }
        }
                if ($type == XMLDB_TYPE_FLOAT) {
            if (!(empty($length) || (is_numeric($length) &&
                                     !empty($length) &&
                                     intval($length)==floatval($length) &&
                                     $length > 0 &&
                                     $length <= xmldb_field::FLOAT_MAX_LENGTH))) {
                $errors[] = $this->str['floatincorrectlength'];
            }
            if (!(empty($decimals) || (is_numeric($decimals) &&
                                       !empty($decimals) &&
                                       intval($decimals)==floatval($decimals) &&
                                       $decimals >= 0 &&
                                       $decimals < $length))) {
                $errors[] = $this->str['floatincorrectdecimals'];
            }
            if (!(empty($default) || (is_numeric($default) &&
                                       !empty($default)))) {
                $errors[] = $this->str['defaultincorrect'];
            }
        }
                if ($type == XMLDB_TYPE_CHAR) {
            if (!(is_numeric($length) && !empty($length) && intval($length)==floatval($length) &&
                  $length > 0 && $length <= xmldb_field::CHAR_MAX_LENGTH)) {
                $errors[] = $this->str['charincorrectlength'];
            }
            if ($default !== NULL && $default !== '') {
                if (substr($default, 0, 1) == "'" ||
                    substr($default, -1, 1) == "'") {
                    $errors[] = $this->str['defaultincorrect'];
                }
            }
        }
                
        if (!empty($errors)) {
            $tempfield = new xmldb_field($name);
            $tempfield->setType($type);
            $tempfield->setLength($length);
            $tempfield->setDecimals($decimals);
            $tempfield->setNotNull($notnull);
            $tempfield->setSequence($sequence);
            $tempfield->setDefault($default);
                        $o = '<p>' .implode(', ', $errors) . '</p>
                  <p>' . $name . ': ' . $tempfield->readableInfo() . '</p>';
            $o.= '<a href="index.php?action=edit_field&amp;field=' . $field->getName() . '&amp;table=' . $table->getName() .
                 '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a>';
            $this->output = $o;
        }

                if (empty($errors)) {
                                    if ($fieldparam != $name) {
                $field->setName($name);
                if ($field->getPrevious()) {
                    $prev = $table->getField($field->getPrevious());
                    $prev->setNext($name);
                    $prev->setChanged(true);
                }
                if ($field->getNext()) {
                    $next = $table->getField($field->getNext());
                    $next->setPrevious($name);
                    $next->setChanged(true);
                }
            }

                        $field->setComment($comment);

                        $field->setType($type);
            $field->setLength($length);
            $field->setDecimals($decimals);
            $field->setNotNull($notnull);
            $field->setSequence($sequence);
            $field->setDefault($default);

                                    $field->calculateHash(true);
            if ($oldhash != $field->getHash()) {
                $field->setChanged(true);
                $table->setChanged(true);
                                $structure->calculateHash(true);
                $structure->setVersion(userdate(time(), '%Y%m%d', 99, false));
                                $structure->setChanged(true);
            }

                        if ($this->getPostAction() && $result) {
                return $this->launch($this->getPostAction());
            }
        }

                return $result;
    }
}

