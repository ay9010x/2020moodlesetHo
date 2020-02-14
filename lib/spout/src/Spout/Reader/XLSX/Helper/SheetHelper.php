<?php

namespace Box\Spout\Reader\XLSX\Helper;

use Box\Spout\Reader\Wrapper\SimpleXMLElement;
use Box\Spout\Reader\XLSX\Sheet;


class SheetHelper
{
    
    const CONTENT_TYPES_XML_FILE_PATH = '[Content_Types].xml';
    const WORKBOOK_XML_RELS_FILE_PATH = 'xl/_rels/workbook.xml.rels';
    const WORKBOOK_XML_FILE_PATH = 'xl/workbook.xml';

    
    const MAIN_NAMESPACE_FOR_CONTENT_TYPES_XML = 'http://schemas.openxmlformats.org/package/2006/content-types';
    const MAIN_NAMESPACE_FOR_WORKBOOK_XML_RELS = 'http://schemas.openxmlformats.org/package/2006/relationships';
    const MAIN_NAMESPACE_FOR_WORKBOOK_XML = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    
    const OVERRIDE_CONTENT_TYPES_ATTRIBUTE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml';

    
    protected $filePath;

    
    protected $sharedStringsHelper;

    
    protected $globalFunctionsHelper;

    
    protected $workbookXMLRelsAsXMLElement;

    
    protected $workbookXMLAsXMLElement;

    
    public function __construct($filePath, $sharedStringsHelper, $globalFunctionsHelper)
    {
        $this->filePath = $filePath;
        $this->sharedStringsHelper = $sharedStringsHelper;
        $this->globalFunctionsHelper = $globalFunctionsHelper;
    }

    
    public function getSheets()
    {
        $sheets = [];

        $contentTypesAsXMLElement = $this->getFileAsXMLElementWithNamespace(
            self::CONTENT_TYPES_XML_FILE_PATH,
            self::MAIN_NAMESPACE_FOR_CONTENT_TYPES_XML
        );

                $sheetNodes = $contentTypesAsXMLElement->xpath('//ns:Override[@ContentType="' . self::OVERRIDE_CONTENT_TYPES_ATTRIBUTE . '"]');
        $numSheetNodes = count($sheetNodes);

        for ($i = 0; $i < $numSheetNodes; $i++) {
            $sheetNode = $sheetNodes[$i];
            $sheetDataXMLFilePath = $sheetNode->getAttribute('PartName');

            $sheets[] = $this->getSheetFromXML($sheetDataXMLFilePath);
        }

                        usort($sheets, function ($sheet1, $sheet2) {
            return ($sheet1->getIndex() - $sheet2->getIndex());
        });

        return $sheets;
    }

    
    protected function getSheetFromXML($sheetDataXMLFilePath)
    {
                        $sheetDataXMLFilePathInWorkbookXMLRels = ltrim($sheetDataXMLFilePath, '/xl/');

                $workbookXMLResElement = $this->getWorkbookXMLRelsAsXMLElement();
        $relationshipNodes = $workbookXMLResElement->xpath('//ns:Relationship[@Target="' . $sheetDataXMLFilePathInWorkbookXMLRels . '"]');
        $relationshipNode = $relationshipNodes[0];

        $relationshipSheetId = $relationshipNode->getAttribute('Id');

        $workbookXMLElement = $this->getWorkbookXMLAsXMLElement();
        $sheetNodes = $workbookXMLElement->xpath('//ns:sheet[@r:id="' . $relationshipSheetId . '"]');
        $sheetNode = $sheetNodes[0];

        $escapedSheetName = $sheetNode->getAttribute('name');
        $sheetIdOneBased = $sheetNode->getAttribute('sheetId');
        $sheetIndexZeroBased = $sheetIdOneBased - 1;

        
        $escaper = new \Box\Spout\Common\Escaper\XLSX();
        $sheetName = $escaper->unescape($escapedSheetName);

        return new Sheet($this->filePath, $sheetDataXMLFilePath, $this->sharedStringsHelper, $sheetIndexZeroBased, $sheetName);
    }

    
    protected function getWorkbookXMLRelsAsXMLElement()
    {
        if (!$this->workbookXMLRelsAsXMLElement) {
            $this->workbookXMLRelsAsXMLElement = $this->getFileAsXMLElementWithNamespace(
                self::WORKBOOK_XML_RELS_FILE_PATH,
                self::MAIN_NAMESPACE_FOR_WORKBOOK_XML_RELS
            );
        }

        return $this->workbookXMLRelsAsXMLElement;
    }

    
    protected function getWorkbookXMLAsXMLElement()
    {
        if (!$this->workbookXMLAsXMLElement) {
            $this->workbookXMLAsXMLElement = $this->getFileAsXMLElementWithNamespace(
                self::WORKBOOK_XML_FILE_PATH,
                self::MAIN_NAMESPACE_FOR_WORKBOOK_XML
            );
        }

        return $this->workbookXMLAsXMLElement;
    }

    
    protected function getFileAsXMLElementWithNamespace($xmlFilePath, $mainNamespace)
    {
        $xmlContents = $this->globalFunctionsHelper->file_get_contents('zip://' . $this->filePath . '#' . $xmlFilePath);

        $xmlElement = new SimpleXMLElement($xmlContents);
        $xmlElement->registerXPathNamespace('ns', $mainNamespace);

        return $xmlElement;
    }
}
