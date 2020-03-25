<?php

namespace Box\Spout\Reader\XLSX\Helper;

use Box\Spout\Reader\Wrapper\SimpleXMLElement;
use Box\Spout\Reader\Wrapper\XMLReader;


class StyleHelper
{
    
    const STYLES_XML_FILE_PATH = 'xl/styles.xml';

    
    const XML_NODE_NUM_FMTS = 'numFmts';
    const XML_NODE_NUM_FMT = 'numFmt';
    const XML_NODE_CELL_XFS = 'cellXfs';
    const XML_NODE_XF = 'xf';

    
    const XML_ATTRIBUTE_NUM_FMT_ID = 'numFmtId';
    const XML_ATTRIBUTE_FORMAT_CODE = 'formatCode';
    const XML_ATTRIBUTE_APPLY_NUMBER_FORMAT = 'applyNumberFormat';

    
    const DEFAULT_STYLE_ID = 0;

    
    protected $filePath;

    
    protected $customNumberFormats;

    
    protected $stylesAttributes;

    
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    
    protected function extractRelevantInfo()
    {
        $this->customNumberFormats = [];
        $this->stylesAttributes = [];

        $stylesXmlFilePath = $this->filePath .'#' . self::STYLES_XML_FILE_PATH;
        $xmlReader = new XMLReader();

        if ($xmlReader->open('zip://' . $stylesXmlFilePath)) {
            while ($xmlReader->read()) {
                if ($xmlReader->isPositionedOnStartingNode(self::XML_NODE_NUM_FMTS)) {
                    $numFmtsNode = new SimpleXMLElement($xmlReader->readOuterXml());
                    $this->extractNumberFormats($numFmtsNode);

                } else if ($xmlReader->isPositionedOnStartingNode(self::XML_NODE_CELL_XFS)) {
                    $cellXfsNode = new SimpleXMLElement($xmlReader->readOuterXml());
                    $this->extractStyleAttributes($cellXfsNode);
                }
            }

            $xmlReader->close();
        }
    }

    
    protected function extractNumberFormats($numFmtsNode)
    {
        foreach ($numFmtsNode->children() as $numFmtNode) {
            $numFmtId = intval($numFmtNode->getAttribute(self::XML_ATTRIBUTE_NUM_FMT_ID));
            $formatCode = $numFmtNode->getAttribute(self::XML_ATTRIBUTE_FORMAT_CODE);
            $this->customNumberFormats[$numFmtId] = $formatCode;
        }
    }

    
    protected function extractStyleAttributes($cellXfsNode)
    {
        foreach ($cellXfsNode->children() as $xfNode) {
            $this->stylesAttributes[] = [
                self::XML_ATTRIBUTE_NUM_FMT_ID => intval($xfNode->getAttribute(self::XML_ATTRIBUTE_NUM_FMT_ID)),
                self::XML_ATTRIBUTE_APPLY_NUMBER_FORMAT => !!($xfNode->getAttribute(self::XML_ATTRIBUTE_APPLY_NUMBER_FORMAT)),
            ];
        }
    }

    
    protected function getCustomNumberFormats()
    {
        if (!isset($this->customNumberFormats)) {
            $this->extractRelevantInfo();
        }

        return $this->customNumberFormats;
    }

    
    protected function getStylesAttributes()
    {
        if (!isset($this->stylesAttributes)) {
            $this->extractRelevantInfo();
        }

        return $this->stylesAttributes;
    }

    
    public function shouldFormatNumericValueAsDate($styleId)
    {
        $stylesAttributes = $this->getStylesAttributes();

                                if ($styleId === self::DEFAULT_STYLE_ID || !isset($stylesAttributes[$styleId])) {
            return false;
        }

        $styleAttributes = $stylesAttributes[$styleId];

        $applyNumberFormat = $styleAttributes[self::XML_ATTRIBUTE_APPLY_NUMBER_FORMAT];
        if (!$applyNumberFormat) {
            return false;
        }

        $numFmtId = $styleAttributes[self::XML_ATTRIBUTE_NUM_FMT_ID];
        return $this->doesNumFmtIdIndicateDate($numFmtId);
    }

    
    protected function doesNumFmtIdIndicateDate($numFmtId)
    {
        return (
            $this->isNumFmtIdBuiltInDateFormat($numFmtId) ||
            $this->isNumFmtIdCustomDateFormat($numFmtId)
        );
    }

    
    protected function isNumFmtIdBuiltInDateFormat($numFmtId)
    {
        $builtInDateFormatIds = [14, 15, 16, 17, 18, 19, 20, 21, 22, 45, 46, 47];
        return in_array($numFmtId, $builtInDateFormatIds);
    }

    
    protected function isNumFmtIdCustomDateFormat($numFmtId)
    {
        $customNumberFormats = $this->getCustomNumberFormats();

                if (!isset($customNumberFormats[$numFmtId])) {
            return false;
        }

        $customNumberFormat = $customNumberFormats[$numFmtId];

                $pattern = '((?<!\\\)\[.+?(?<!\\\)\])';
        $customNumberFormat = preg_replace($pattern, '', $customNumberFormat);

                                $dateFormatCharacters = ['e', 'yy', 'm', 'd', 'h', 's'];

        $hasFoundDateFormatCharacter = false;
        foreach ($dateFormatCharacters as $dateFormatCharacter) {
                        $pattern = '/(?<!\\\)' . $dateFormatCharacter . '/';

            if (preg_match($pattern, $customNumberFormat)) {
                $hasFoundDateFormatCharacter = true;
                break;
            }
        }

        return $hasFoundDateFormatCharacter;
    }
}
