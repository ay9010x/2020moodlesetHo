<?php




class edit_xml_file_save extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
                    ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_NONE;
        
                global $CFG, $XMLDB;

        
        if (!data_submitted()) {             print_error('wrongcall', 'error');
        }

                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;

        $comment = required_param('comment', PARAM_CLEAN);
        $comment = $comment;

                $editeddir = $XMLDB->editeddirs[$dirpath];
        $structure = $editeddir->xml_file->getStructure();
        $structure->setComment($comment);
        $structure->calculateHash(true);


                        $origdir = $XMLDB->dbdirs[$dirpath];
        $origstructure = $origdir->xml_file->getStructure();
        if ($structure->getHash() != $origstructure->getHash()) {
            $structure->setVersion(userdate(time(), '%Y%m%d', 99, false));
            $structure->setChanged(true);
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

