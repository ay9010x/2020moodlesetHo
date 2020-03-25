<?php

namespace Box\Spout\Common\Helper;


class GlobalFunctionsHelper
{
    
    public function fopen($fileName, $mode)
    {
        return fopen($fileName, $mode);
    }

    
    public function fgets($handle, $length = null)
    {
        return fgets($handle, $length);
    }

    
    public function fputs($handle, $string)
    {
        return fputs($handle, $string);
    }

    
    public function fflush($handle)
    {
        return fflush($handle);
    }

    
    public function fseek($handle, $offset)
    {
        return fseek($handle, $offset);
    }

    
    public function fgetcsv($handle, $length = null, $delimiter = null, $enclosure = null)
    {
        return fgetcsv($handle, $length, $delimiter, $enclosure);
    }

    
    public function fputcsv($handle, array $fields, $delimiter = null, $enclosure = null)
    {
        return fputcsv($handle, $fields, $delimiter, $enclosure);
    }

    
    public function fwrite($handle, $string)
    {
        return fwrite($handle, $string);
    }

    
    public function fclose($handle)
    {
        return fclose($handle);
    }

    
    public function rewind($handle)
    {
        return rewind($handle);
    }

    
    public function file_exists($fileName)
    {
        return file_exists($fileName);
    }

    
    public function file_get_contents($filePath)
    {
        $realFilePath = $this->convertToUseRealPath($filePath);
        return file_get_contents($realFilePath);
    }

    
    protected function convertToUseRealPath($filePath)
    {
        $realFilePath = $filePath;

        if ($this->isZipStream($filePath)) {
            if (preg_match('/zip:\/\/(.*)#(.*)/', $filePath, $matches)) {
                $documentPath = $matches[1];
                $documentInsideZipPath = $matches[2];
                $realFilePath = 'zip://' . realpath($documentPath) . '#' . $documentInsideZipPath;
            }
        } else {
            $realFilePath = realpath($filePath);
        }

        return $realFilePath;
    }

    
    protected function isZipStream($path)
    {
        return (strpos($path, 'zip://') === 0);
    }

    
    public function feof($handle)
    {
        return feof($handle);
    }

    
    public function is_readable($fileName)
    {
        return is_readable($fileName);
    }

    
    public function basename($path, $suffix = null)
    {
        return basename($path, $suffix);
    }

    
    public function header($string)
    {
        header($string);
    }

    
    public function iconv($string, $sourceEncoding, $targetEncoding)
    {
        return iconv($sourceEncoding, $targetEncoding, $string);
    }

    
    public function mb_convert_encoding($string, $sourceEncoding, $targetEncoding)
    {
        return mb_convert_encoding($string, $targetEncoding, $sourceEncoding);
    }

    
    public function stream_get_wrappers()
    {
        return stream_get_wrappers();
    }

    
    public function function_exists($functionName)
    {
        return function_exists($functionName);
    }
}
