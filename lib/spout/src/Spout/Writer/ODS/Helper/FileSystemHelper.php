<?php

namespace Box\Spout\Writer\ODS\Helper;

use Box\Spout\Writer\Common\Helper\ZipHelper;
use Box\Spout\Writer\ODS\Internal\Worksheet;


class FileSystemHelper extends \Box\Spout\Common\Helper\FileSystemHelper
{
    const APP_NAME = 'Spout';
    const MIMETYPE = 'application/vnd.oasis.opendocument.spreadsheet';

    const META_INF_FOLDER_NAME = 'META-INF';
    const SHEETS_CONTENT_TEMP_FOLDER_NAME = 'worksheets-temp';

    const MANIFEST_XML_FILE_NAME = 'manifest.xml';
    const CONTENT_XML_FILE_NAME = 'content.xml';
    const META_XML_FILE_NAME = 'meta.xml';
    const MIMETYPE_FILE_NAME = 'mimetype';
    const STYLES_XML_FILE_NAME = 'styles.xml';

    
    protected $rootFolder;

    
    protected $metaInfFolder;

    
    protected $sheetsContentTempFolder;

    
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    
    public function getSheetsContentTempFolder()
    {
        return $this->sheetsContentTempFolder;
    }

    
    public function createBaseFilesAndFolders()
    {
        $this
            ->createRootFolder()
            ->createMetaInfoFolderAndFile()
            ->createSheetsContentTempFolder()
            ->createMetaFile()
            ->createMimetypeFile();
    }

    
    protected function createRootFolder()
    {
        $this->rootFolder = $this->createFolder($this->baseFolderPath, uniqid('ods'));
        return $this;
    }

    
    protected function createMetaInfoFolderAndFile()
    {
        $this->metaInfFolder = $this->createFolder($this->rootFolder, self::META_INF_FOLDER_NAME);

        $this->createManifestFile();

        return $this;
    }

    
    protected function createManifestFile()
    {
        $manifestXmlFileContents = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.2">
    <manifest:file-entry manifest:full-path="/" manifest:media-type="application/vnd.oasis.opendocument.spreadsheet"/>
    <manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>
    <manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>
    <manifest:file-entry manifest:full-path="meta.xml" manifest:media-type="text/xml"/>
</manifest:manifest>
EOD;

        $this->createFileWithContents($this->metaInfFolder, self::MANIFEST_XML_FILE_NAME, $manifestXmlFileContents);

        return $this;
    }

    
    protected function createSheetsContentTempFolder()
    {
        $this->sheetsContentTempFolder = $this->createFolder($this->rootFolder, self::SHEETS_CONTENT_TEMP_FOLDER_NAME);
        return $this;
    }

    
    protected function createMetaFile()
    {
        $appName = self::APP_NAME;
        $createdDate = (new \DateTime())->format(\DateTime::W3C);

        $metaXmlFileContents = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<office:document-meta office:version="1.2" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
    <office:meta>
        <dc:creator>$appName</dc:creator>
        <meta:creation-date>$createdDate</meta:creation-date>
        <dc:date>$createdDate</dc:date>
    </office:meta>
</office:document-meta>
EOD;

        $this->createFileWithContents($this->rootFolder, self::META_XML_FILE_NAME, $metaXmlFileContents);

        return $this;
    }

    
    protected function createMimetypeFile()
    {
        $this->createFileWithContents($this->rootFolder, self::MIMETYPE_FILE_NAME, self::MIMETYPE);
        return $this;
    }

    
    public function createContentFile($worksheets, $styleHelper)
    {
        $contentXmlFileContents = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<office:document-content office:version="1.2" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:msoxl="http://schemas.microsoft.com/office/excel/formula" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
EOD;

        $contentXmlFileContents .= $styleHelper->getContentXmlFontFaceSectionContent();
        $contentXmlFileContents .= $styleHelper->getContentXmlAutomaticStylesSectionContent(count($worksheets));

        $contentXmlFileContents .= '<office:body><office:spreadsheet>';

        $this->createFileWithContents($this->rootFolder, self::CONTENT_XML_FILE_NAME, $contentXmlFileContents);

                $contentXmlFilePath = $this->rootFolder . '/' . self::CONTENT_XML_FILE_NAME;
        $contentXmlHandle = fopen($contentXmlFilePath, 'a');

        foreach ($worksheets as $worksheet) {
                        fwrite($contentXmlHandle, $worksheet->getTableElementStartAsString());

            $worksheetFilePath = $worksheet->getWorksheetFilePath();
            $this->copyFileContentsToTarget($worksheetFilePath, $contentXmlHandle);

            fwrite($contentXmlHandle, '</table:table>');
        }

        $contentXmlFileContents = '</office:spreadsheet></office:body></office:document-content>';

        fwrite($contentXmlHandle, $contentXmlFileContents);
        fclose($contentXmlHandle);

        return $this;
    }

    
    protected function copyFileContentsToTarget($sourceFilePath, $targetResource)
    {
        $sourceHandle = fopen($sourceFilePath, 'r');
        stream_copy_to_stream($sourceHandle, $targetResource);
        fclose($sourceHandle);
    }

    
    public function deleteWorksheetTempFolder()
    {
        $this->deleteFolderRecursively($this->sheetsContentTempFolder);
        return $this;
    }


    
    public function createStylesFile($styleHelper, $numWorksheets)
    {
        $stylesXmlFileContents = $styleHelper->getStylesXMLFileContent($numWorksheets);
        $this->createFileWithContents($this->rootFolder, self::STYLES_XML_FILE_NAME, $stylesXmlFileContents);

        return $this;
    }

    
    public function zipRootFolderAndCopyToStream($streamPointer)
    {
        $zipHelper = new ZipHelper($this->rootFolder);

                                $zipHelper->addUncompressedFileToArchive($this->rootFolder, self::MIMETYPE_FILE_NAME);

        $zipHelper->addFolderToArchive($this->rootFolder, ZipHelper::EXISTING_FILES_SKIP);
        $zipHelper->closeArchiveAndCopyToStream($streamPointer);

                $this->deleteFile($zipHelper->getZipFilePath());
    }
}
