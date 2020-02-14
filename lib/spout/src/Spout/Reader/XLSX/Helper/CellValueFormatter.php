<?php

namespace Box\Spout\Reader\XLSX\Helper;


class CellValueFormatter
{
    
    const CELL_TYPE_INLINE_STRING = 'inlineStr';
    const CELL_TYPE_STR = 'str';
    const CELL_TYPE_SHARED_STRING = 's';
    const CELL_TYPE_BOOLEAN = 'b';
    const CELL_TYPE_NUMERIC = 'n';
    const CELL_TYPE_DATE = 'd';
    const CELL_TYPE_ERROR = 'e';

    
    const XML_NODE_VALUE = 'v';
    const XML_NODE_INLINE_STRING_VALUE = 't';

    
    const XML_ATTRIBUTE_TYPE = 't';
    const XML_ATTRIBUTE_STYLE_ID = 's';

    
    const NUM_SECONDS_IN_ONE_DAY = 86400;

    
    const ERRONEOUS_EXCEL_LEAP_YEAR_DAY = 60;

    
    protected $sharedStringsHelper;

    
    protected $styleHelper;

    
    protected $escaper;

    
    public function __construct($sharedStringsHelper, $styleHelper)
    {
        $this->sharedStringsHelper = $sharedStringsHelper;
        $this->styleHelper = $styleHelper;

        
        $this->escaper = new \Box\Spout\Common\Escaper\XLSX();
    }

    
    public function extractAndFormatNodeValue($node)
    {
                $cellType = $node->getAttribute(self::XML_ATTRIBUTE_TYPE) ?: self::CELL_TYPE_NUMERIC;
        $cellStyleId = intval($node->getAttribute(self::XML_ATTRIBUTE_STYLE_ID));
        $vNodeValue = $this->getVNodeValue($node);

        if (($vNodeValue === '') && ($cellType !== self::CELL_TYPE_INLINE_STRING)) {
            return $vNodeValue;
        }

        switch ($cellType) {
            case self::CELL_TYPE_INLINE_STRING:
                return $this->formatInlineStringCellValue($node);
            case self::CELL_TYPE_SHARED_STRING:
                return $this->formatSharedStringCellValue($vNodeValue);
            case self::CELL_TYPE_STR:
                return $this->formatStrCellValue($vNodeValue);
            case self::CELL_TYPE_BOOLEAN:
                return $this->formatBooleanCellValue($vNodeValue);
            case self::CELL_TYPE_NUMERIC:
                return $this->formatNumericCellValue($vNodeValue, $cellStyleId);
            case self::CELL_TYPE_DATE:
                return $this->formatDateCellValue($vNodeValue);
            default:
                return null;
        }
    }

    
    protected function getVNodeValue($node)
    {
                        $vNode = $node->getElementsByTagName(self::XML_NODE_VALUE)->item(0);
        return ($vNode !== null) ? $vNode->nodeValue : '';
    }

    
    protected function formatInlineStringCellValue($node)
    {
                        $tNode = $node->getElementsByTagName(self::XML_NODE_INLINE_STRING_VALUE)->item(0);
        $escapedCellValue = trim($tNode->nodeValue);
        $cellValue = $this->escaper->unescape($escapedCellValue);
        return $cellValue;
    }

    
    protected function formatSharedStringCellValue($nodeValue)
    {
                        $sharedStringIndex = intval($nodeValue);
        $escapedCellValue = $this->sharedStringsHelper->getStringAtIndex($sharedStringIndex);
        $cellValue = $this->escaper->unescape($escapedCellValue);
        return $cellValue;
    }

    
    protected function formatStrCellValue($nodeValue)
    {
        $escapedCellValue = trim($nodeValue);
        $cellValue = $this->escaper->unescape($escapedCellValue);
        return $cellValue;
    }

    
    protected function formatNumericCellValue($nodeValue, $cellStyleId)
    {
                        $shouldFormatAsDate = $this->styleHelper->shouldFormatNumericValueAsDate($cellStyleId);

        if ($shouldFormatAsDate) {
            return $this->formatExcelTimestampValue(floatval($nodeValue));
        } else {
            $nodeIntValue = intval($nodeValue);
            return ($nodeIntValue == $nodeValue) ? $nodeIntValue : floatval($nodeValue);
        }
    }

    
    protected function formatExcelTimestampValue($nodeValue)
    {
                if (ceil($nodeValue) > self::ERRONEOUS_EXCEL_LEAP_YEAR_DAY) {
            --$nodeValue;
        }

                if ($nodeValue < 1.0) {
            return null;
        }

                        $secondsRemainder = fmod($nodeValue, 1) * self::NUM_SECONDS_IN_ONE_DAY;
        $secondsRemainder = round($secondsRemainder, 0);

        try {
            $cellValue = \DateTime::createFromFormat('|Y-m-d', '1899-12-31');
            $cellValue->modify('+' . intval($nodeValue) . 'days');
            $cellValue->modify('+' . $secondsRemainder . 'seconds');

            return $cellValue;
        } catch (\Exception $e) {
            return null;
        }
    }

    
    protected function formatBooleanCellValue($nodeValue)
    {
                $cellValue = !!$nodeValue;
        return $cellValue;
    }

    
    protected function formatDateCellValue($nodeValue)
    {
                try {
            $cellValue = new \DateTime($nodeValue);
            return $cellValue;
        } catch (\Exception $e) {
            return null;
        }
    }
}
