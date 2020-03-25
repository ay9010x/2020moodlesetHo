<?php

if (!defined('PCLZIP_TEMPORARY_DIR')) {
    define('PCLZIP_TEMPORARY_DIR', PHPExcel_Shared_File::sys_get_temp_dir() . DIRECTORY_SEPARATOR);
}
require_once PHPEXCEL_ROOT . 'PHPExcel/Shared/PCLZip/pclzip.lib.php';


class PHPExcel_Shared_ZipArchive
{

    
    const OVERWRITE = 'OVERWRITE';
    const CREATE    = 'CREATE';


    
    private $tempDir;

    
    private $zip;


    
    public function open($fileName)
    {
        $this->tempDir = PHPExcel_Shared_File::sys_get_temp_dir();
        $this->zip = new PclZip($fileName);

        return true;
    }


    
    public function close()
    {
    }


    
    public function addFromString($localname, $contents)
    {
        $filenameParts = pathinfo($localname);

        $handle = fopen($this->tempDir.'/'.$filenameParts["basename"], "wb");
        fwrite($handle, $contents);
        fclose($handle);

        $res = $this->zip->add($this->tempDir.'/'.$filenameParts["basename"], PCLZIP_OPT_REMOVE_PATH, $this->tempDir, PCLZIP_OPT_ADD_PATH, $filenameParts["dirname"]);
        if ($res == 0) {
            throw new PHPExcel_Writer_Exception("Error zipping files : " . $this->zip->errorInfo(true));
        }

        unlink($this->tempDir.'/'.$filenameParts["basename"]);
    }

    
    public function locateName($fileName)
    {
        $list = $this->zip->listContent();
        $listCount = count($list);
        $list_index = -1;
        for ($i = 0; $i < $listCount; ++$i) {
            if (strtolower($list[$i]["filename"]) == strtolower($fileName) ||
                strtolower($list[$i]["stored_filename"]) == strtolower($fileName)) {
                $list_index = $i;
                break;
            }
        }
        return ($list_index > -1);
    }

    
    public function getFromName($fileName)
    {
        $list = $this->zip->listContent();
        $listCount = count($list);
        $list_index = -1;
        for ($i = 0; $i < $listCount; ++$i) {
            if (strtolower($list[$i]["filename"]) == strtolower($fileName) ||
                strtolower($list[$i]["stored_filename"]) == strtolower($fileName)) {
                $list_index = $i;
                break;
            }
        }

        $extracted = "";
        if ($list_index != -1) {
            $extracted = $this->zip->extractByIndex($list_index, PCLZIP_OPT_EXTRACT_AS_STRING);
        } else {
            $filename = substr($fileName, 1);
            $list_index = -1;
            for ($i = 0; $i < $listCount; ++$i) {
                if (strtolower($list[$i]["filename"]) == strtolower($fileName) ||
                    strtolower($list[$i]["stored_filename"]) == strtolower($fileName)) {
                    $list_index = $i;
                    break;
                }
            }
            $extracted = $this->zip->extractByIndex($list_index, PCLZIP_OPT_EXTRACT_AS_STRING);
        }
        if ((is_array($extracted)) && ($extracted != 0)) {
            $contents = $extracted[0]["content"];
        }

        return $contents;
    }
}
