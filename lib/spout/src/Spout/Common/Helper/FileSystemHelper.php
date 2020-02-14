<?php

namespace Box\Spout\Common\Helper;

use Box\Spout\Common\Exception\IOException;


class FileSystemHelper
{
    
    protected $baseFolderPath;

    
    public function __construct($baseFolderPath)
    {
        $this->baseFolderPath = $baseFolderPath;
    }

    
    public function createFolder($parentFolderPath, $folderName)
    {
        $this->throwIfOperationNotInBaseFolder($parentFolderPath);

        $folderPath = $parentFolderPath . '/' . $folderName;

        $wasCreationSuccessful = mkdir($folderPath, 0777, true);
        if (!$wasCreationSuccessful) {
            throw new IOException("Unable to create folder: $folderPath");
        }

        return $folderPath;
    }

    
    public function createFileWithContents($parentFolderPath, $fileName, $fileContents)
    {
        $this->throwIfOperationNotInBaseFolder($parentFolderPath);

        $filePath = $parentFolderPath . '/' . $fileName;

        $wasCreationSuccessful = file_put_contents($filePath, $fileContents);
        if ($wasCreationSuccessful === false) {
            throw new IOException("Unable to create file: $filePath");
        }

        return $filePath;
    }

    
    public function deleteFile($filePath)
    {
        $this->throwIfOperationNotInBaseFolder($filePath);

        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }

    
    public function deleteFolderRecursively($folderPath)
    {
        $this->throwIfOperationNotInBaseFolder($folderPath);

        $itemIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folderPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($itemIterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($folderPath);
    }

    
    protected function throwIfOperationNotInBaseFolder($operationFolderPath)
    {
        $isInBaseFolder = (strpos($operationFolderPath, $this->baseFolderPath) === 0);
        if (!$isInBaseFolder) {
            throw new IOException("Cannot perform I/O operation outside of the base folder: {$this->baseFolderPath}");
        }
    }
}
