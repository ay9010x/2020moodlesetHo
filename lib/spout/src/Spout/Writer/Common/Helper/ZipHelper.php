<?php

namespace Box\Spout\Writer\Common\Helper;


class ZipHelper
{
    const ZIP_EXTENSION = '.zip';

    
    const EXISTING_FILES_SKIP = 'skip';
    const EXISTING_FILES_OVERWRITE = 'overwrite';

    
    protected $tmpFolderPath;

    
    protected $zip;

    
    public function __construct($tmpFolderPath)
    {
        $this->tmpFolderPath = $tmpFolderPath;
    }

    
    protected function createOrGetZip()
    {
        if (!isset($this->zip)) {
            $this->zip = new \ZipArchive();
            $zipFilePath = $this->getZipFilePath();

            $this->zip->open($zipFilePath, \ZipArchive::CREATE|\ZipArchive::OVERWRITE);
        }

        return $this->zip;
    }

    
    public function getZipFilePath()
    {
        return $this->tmpFolderPath . self::ZIP_EXTENSION;
    }

    
    public function addFileToArchive($rootFolderPath, $localFilePath, $existingFileMode = self::EXISTING_FILES_OVERWRITE)
    {
        $this->addFileToArchiveWithCompressionMethod(
            $rootFolderPath,
            $localFilePath,
            $existingFileMode,
            \ZipArchive::CM_DEFAULT
        );
    }

    
    public function addUncompressedFileToArchive($rootFolderPath, $localFilePath, $existingFileMode = self::EXISTING_FILES_OVERWRITE)
    {
        $this->addFileToArchiveWithCompressionMethod(
            $rootFolderPath,
            $localFilePath,
            $existingFileMode,
            \ZipArchive::CM_STORE
        );
    }

    
    protected function addFileToArchiveWithCompressionMethod($rootFolderPath, $localFilePath, $existingFileMode, $compressionMethod)
    {
        $zip = $this->createOrGetZip();

        if (!$this->shouldSkipFile($zip, $localFilePath, $existingFileMode)) {
            $normalizedFullFilePath = $this->getNormalizedRealPath($rootFolderPath . '/' . $localFilePath);
            $zip->addFile($normalizedFullFilePath, $localFilePath);

            if (self::canChooseCompressionMethod()) {
                $zip->setCompressionName($localFilePath, $compressionMethod);
            }
        }
    }

    
    public static function canChooseCompressionMethod()
    {
                return (method_exists(new \ZipArchive(), 'setCompressionName'));
    }

    
    public function addFolderToArchive($folderPath, $existingFileMode = self::EXISTING_FILES_OVERWRITE)
    {
        $zip = $this->createOrGetZip();

        $folderRealPath = $this->getNormalizedRealPath($folderPath) . '/';
        $itemIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folderPath, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($itemIterator as $itemInfo) {
            $itemRealPath = $this->getNormalizedRealPath($itemInfo->getPathname());
            $itemLocalPath = str_replace($folderRealPath, '', $itemRealPath);

            if ($itemInfo->isFile() && !$this->shouldSkipFile($zip, $itemLocalPath, $existingFileMode)) {
                $zip->addFile($itemRealPath, $itemLocalPath);
            }
        }
    }

    
    protected function shouldSkipFile($zip, $itemLocalPath, $existingFileMode)
    {
                                return ($existingFileMode === self::EXISTING_FILES_SKIP && $zip->locateName($itemLocalPath) !== false);
    }

    
    protected function getNormalizedRealPath($path)
    {
        $realPath = realpath($path);
        return str_replace(DIRECTORY_SEPARATOR, '/', $realPath);
    }

    
    public function closeArchiveAndCopyToStream($streamPointer)
    {
        $zip = $this->createOrGetZip();
        $zip->close();
        unset($this->zip);

        $this->copyZipToStream($streamPointer);
    }

    
    protected function copyZipToStream($pointer)
    {
        $zipFilePointer = fopen($this->getZipFilePath(), 'r');
        stream_copy_to_stream($zipFilePointer, $pointer);
        fclose($zipFilePointer);
    }
}
