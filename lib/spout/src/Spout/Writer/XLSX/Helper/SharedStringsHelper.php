<?php

namespace Box\Spout\Writer\XLSX\Helper;

use Box\Spout\Common\Exception\IOException;


class SharedStringsHelper
{
    const SHARED_STRINGS_FILE_NAME = 'sharedStrings.xml';

    const SHARED_STRINGS_XML_FILE_FIRST_PART_HEADER = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
EOD;

    
    const DEFAULT_STRINGS_COUNT_PART = 'count="9999999999999" uniqueCount="9999999999999"';

    
    protected $sharedStringsFilePointer;

    
    protected $numSharedStrings = 0;

    
    protected $stringsEscaper;

    
    public function __construct($xlFolder)
    {
        $sharedStringsFilePath = $xlFolder . '/' . self::SHARED_STRINGS_FILE_NAME;
        $this->sharedStringsFilePointer = fopen($sharedStringsFilePath, 'w');

        $this->throwIfSharedStringsFilePointerIsNotAvailable();

                $header = self::SHARED_STRINGS_XML_FILE_FIRST_PART_HEADER . ' ' . self::DEFAULT_STRINGS_COUNT_PART . '>';
        fwrite($this->sharedStringsFilePointer, $header);

        
        $this->stringsEscaper = new \Box\Spout\Common\Escaper\XLSX();
    }

    
    protected function throwIfSharedStringsFilePointerIsNotAvailable()
    {
        if (!$this->sharedStringsFilePointer) {
            throw new IOException('Unable to open shared strings file for writing.');
        }
    }

    
    public function writeString($string)
    {
        fwrite($this->sharedStringsFilePointer, '<si><t xml:space="preserve">' . $this->stringsEscaper->escape($string) . '</t></si>');
        $this->numSharedStrings++;

                return ($this->numSharedStrings - 1);
    }

    
    public function close()
    {
        fwrite($this->sharedStringsFilePointer, '</sst>');

                $firstPartHeaderLength = strlen(self::SHARED_STRINGS_XML_FILE_FIRST_PART_HEADER);
        $defaultStringsCountPartLength = strlen(self::DEFAULT_STRINGS_COUNT_PART);

                fseek($this->sharedStringsFilePointer, $firstPartHeaderLength + 1);
        fwrite($this->sharedStringsFilePointer, sprintf("%-{$defaultStringsCountPartLength}s", 'count="' . $this->numSharedStrings . '" uniqueCount="' . $this->numSharedStrings . '"'));

        fclose($this->sharedStringsFilePointer);
    }
}
