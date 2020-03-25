<?php



defined('MOODLE_INTERNAL') || die();


class xmldb_file extends xmldb_object {

    
    protected $path;

    
    protected $schema;

    
    protected $dtd;

    
    protected $xmldb_structure;

    
    public function __construct($path) {
        parent::__construct($path);
        $this->path = $path;
        $this->xmldb_structure = null;
    }

    
    public function fileExists() {
        if (file_exists($this->path) && is_readable($this->path)) {
            return true;
        }
        return false;
    }

    
    public function fileWriteable() {
        if (is_writeable(dirname($this->path))) {
            return true;
        }
        return false;
    }

    public function getStructure() {
        return $this->xmldb_structure;
    }

    
    public function validateXMLStructure() {

                $parser = new DOMDocument();
        $contents = file_get_contents($this->path);
        if (strpos($contents, '<STATEMENTS>')) {
                        $contents = preg_replace('|<STATEMENTS>.*</STATEMENTS>|s', '', $contents);
        }

                $olderrormode = libxml_use_internal_errors(true);

                        libxml_clear_errors();

        $parser->loadXML($contents);
                if (!empty($this->schema) && file_exists($this->schema)) {
            $parser->schemaValidate($this->schema);
        }
                $errors = libxml_get_errors();

                libxml_use_internal_errors($olderrormode);

                if (!empty($errors)) {
                        $structure = new xmldb_structure($this->path);
                        $structure->errormsg = 'XML Error: ';
            foreach ($errors as $error) {
                $structure->errormsg .= sprintf("%s at line %d. ",
                                                 trim($error->message, "\n\r\t ."),
                                                 $error->line);
            }
                        $this->xmldb_structure = $structure;
                        return false;
        }

        return true;
    }

    
    public function loadXMLStructure() {
        if ($this->fileExists()) {
                        if (!$this->validateXMLStructure()) {
                return false;
            }
            $contents = file_get_contents($this->path);
            if (strpos($contents, '<STATEMENTS>')) {
                                $contents = preg_replace('|<STATEMENTS>.*</STATEMENTS>|s', '', $contents);
                debugging('STATEMENTS section is not supported any more, please use db/install.php or db/log.php');
            }
                                    $xmlarr = xmlize($contents);
                        $this->xmldb_structure = $this->arr2xmldb_structure($xmlarr);
                        if ($this->xmldb_structure->isLoaded()) {
                $this->loaded = true;
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    
    public function arr2xmldb_structure ($xmlarr) {
        $structure = new xmldb_structure($this->path);
        $structure->arr2xmldb_structure($xmlarr);
        return $structure;
    }

    
    public function setDTD($path) {
        $this->dtd = $path;
    }

    
    public function setSchema($path) {
        $this->schema = $path;
    }

    
    public function saveXMLFile() {

        $structure = $this->getStructure();

        $result = file_put_contents($this->path, $structure->xmlOutput());

        return $result;
    }
}
