<?php


class PHPExcel_Shared_ZipStreamWrapper
{
    
    private $archive;

    
    private $fileNameInArchive = '';

    
    private $position = 0;

    
    private $data = '';

    
    public static function register()
    {
        @stream_wrapper_unregister('zip');
        @stream_wrapper_register('zip', __CLASS__);
    }

    
    public function stream_open($path, $mode, $options, &$opened_path)
    {
                if ($mode{0} != 'r') {
            throw new PHPExcel_Reader_Exception('Mode ' . $mode . ' is not supported. Only read mode is supported.');
        }

        $pos = strrpos($path, '#');
        $url['host'] = substr($path, 6, $pos - 6);         $url['fragment'] = substr($path, $pos + 1);

                $this->archive = new ZipArchive();
        $this->archive->open($url['host']);

        $this->fileNameInArchive = $url['fragment'];
        $this->position = 0;
        $this->data = $this->archive->getFromName($this->fileNameInArchive);

        return true;
    }

    
    public function statName()
    {
        return $this->fileNameInArchive;
    }

    
    public function url_stat()
    {
        return $this->statName($this->fileNameInArchive);
    }

    
    public function stream_stat()
    {
        return $this->archive->statName($this->fileNameInArchive);
    }

    
    public function stream_read($count)
    {
        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    
    public function stream_tell()
    {
        return $this->position;
    }

    
    public function stream_eof()
    {
        return $this->position >= strlen($this->data);
    }

    
    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->data) && $offset >= 0) {
                     $this->position = $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            case SEEK_END:
                if (strlen($this->data) + $offset >= 0) {
                     $this->position = strlen($this->data) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            default:
                return false;
        }
    }
}
