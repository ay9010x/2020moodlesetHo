<?php



defined('MOODLE_INTERNAL') || die();


class file_exception extends moodle_exception {
    
    function __construct($errorcode, $a=NULL, $debuginfo = NULL) {
        parent::__construct($errorcode, '', '', $a, $debuginfo);
    }
}


class stored_file_creation_exception extends file_exception {
    
    function __construct($contextid, $component, $filearea, $itemid, $filepath, $filename, $debuginfo = null) {
        $a = new stdClass();
        $a->contextid = $contextid;
        $a->component = $component;
        $a->filearea  = $filearea;
        $a->itemid    = $itemid;
        $a->filepath  = $filepath;
        $a->filename  = $filename;
        parent::__construct('storedfilenotcreated', $a, $debuginfo);
    }
}


class file_access_exception extends file_exception {
    
    public function __construct($debuginfo = null) {
        parent::__construct('nopermissions', null, $debuginfo);
    }
}


class file_pool_content_exception extends file_exception {
    
    public function __construct($contenthash, $debuginfo = null) {
        parent::__construct('hashpoolproblem', $contenthash, $debuginfo);
    }
}



class file_reference_exception extends file_exception {
    
    function __construct($repositoryid, $reference, $referencefileid=null, $fileid=null, $debuginfo=null) {
        $a = new stdClass();
        $a->repositoryid = $repositoryid;
        $a->reference = $reference;
        $a->referencefileid = $referencefileid;
        $a->fileid = $fileid;
        parent::__construct('filereferenceproblem', $a, $debuginfo);
    }
}
