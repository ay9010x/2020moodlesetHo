<?php





class CAS_PGTStorage_File extends CAS_PGTStorage_AbstractStorage
{
    

    
    var $_path;

    
    function getPath()
    {
        return $this->_path;
    }

            
    
    function getStorageType()
    {
        return "file";
    }

    
    function getStorageInfo()
    {
        return 'path=`'.$this->getPath().'\'';
    }

            
    
    function __construct($cas_parent,$path)
    {
        phpCAS::traceBegin();
                parent::__construct($cas_parent);

        if (empty($path)) {
            $path = CAS_PGT_STORAGE_FILE_DEFAULT_PATH;
        }
                if (getenv("OS")=="Windows_NT") {

            if (!preg_match('`^[a-zA-Z]:`', $path)) {
                phpCAS::error('an absolute path is needed for PGT storage to file');
            }

        } else {

            if ( $path[0] != '/' ) {
                phpCAS::error('an absolute path is needed for PGT storage to file');
            }

                        $path = preg_replace('|[/]*$|', '/', $path);
            $path = preg_replace('|^[/]*|', '/', $path);
        }

        $this->_path = $path;
        phpCAS::traceEnd();
    }

            
    
    function init()
    {
        phpCAS::traceBegin();
                if ($this->isInitialized()) {
            return;
        }
                parent::init();
        phpCAS::traceEnd();
    }

            
    
    function getPGTIouFilename($pgt_iou)
    {
        phpCAS::traceBegin();
        $filename = $this->getPath().$pgt_iou.'.plain';
        phpCAS::traceEnd($filename);
        return $filename;
    }

    
    function write($pgt,$pgt_iou)
    {
        phpCAS::traceBegin();
        $fname = $this->getPGTIouFilename($pgt_iou);
        if (!file_exists($fname)) {
            touch($fname);
                        @chmod($fname, 0600);
            if ($f=fopen($fname, "w")) {
                if (fputs($f, $pgt) === false) {
                    phpCAS::error('could not write PGT to `'.$fname.'\'');
                }
                phpCAS::trace('Successful write of PGT to `'.$fname.'\'');
                fclose($f);
            } else {
                phpCAS::error('could not open `'.$fname.'\'');
            }
        } else {
            phpCAS::error('File exists: `'.$fname.'\'');
        }
        phpCAS::traceEnd();
    }

    
    function read($pgt_iou)
    {
        phpCAS::traceBegin();
        $pgt = false;
        $fname = $this->getPGTIouFilename($pgt_iou);
        if (file_exists($fname)) {
            if (!($f=fopen($fname, "r"))) {
                phpCAS::error('could not open `'.$fname.'\'');
            } else {
                if (($pgt=fgets($f)) === false) {
                    phpCAS::error('could not read PGT from `'.$fname.'\'');
                }
                phpCAS::trace('Successful read of PGT to `'.$fname.'\'');
                fclose($f);
            }
                        @unlink($fname);
        } else {
            phpCAS::error('No such file `'.$fname.'\'');
        }
        phpCAS::traceEnd($pgt);
        return $pgt;
    }

    

}
?>