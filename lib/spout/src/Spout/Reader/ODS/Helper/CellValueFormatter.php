<?php

namespace Box\Spout\Reader\ODS\Helper;


class CellValueFormatter
{
    
    const CELL_TYPE_STRING = 'string';
    const CELL_TYPE_FLOAT = 'float';
    const CELL_TYPE_BOOLEAN = 'boolean';
    const CELL_TYPE_DATE = 'date';
    const CELL_TYPE_TIME = 'time';
    const CELL_TYPE_CURRENCY = 'currency';
    const CELL_TYPE_PERCENTAGE = 'percentage';
    const CELL_TYPE_VOID = 'void';

    
    const XML_NODE_P = 'p';
    const XML_NODE_S = 'text:s';

    
    const XML_ATTRIBUTE_TYPE = 'office:value-type';
    const XML_ATTRIBUTE_VALUE = 'office:value';
    const XML_ATTRIBUTE_BOOLEAN_VALUE = 'office:boolean-value';
    const XML_ATTRIBUTE_DATE_VALUE = 'office:date-value';
    const XML_ATTRIBUTE_TIME_VALUE = 'office:time-value';
    const XML_ATTRIBUTE_CURRENCY = 'office:currency';
    const XML_ATTRIBUTE_C = 'text:c';

    
    protected $escaper;

    
    public function __construct()
    {
        
        $this->escaper = new \Box\Spout\Common\Escaper\ODS();
    }

    
    public function extractAndFormatNodeValue($node)
    {
        $cellType = $node->getAttribute(self::XML_ATTRIBUTE_TYPE);

        switch ($cellType) {
            case self::CELL_TYPE_STRING:
                return $this->formatStringCellValue($node);
            case self::CELL_TYPE_FLOAT:
                return $this->formatFloatCellValue($node);
            case self::CELL_TYPE_BOOLEAN:
                return $this->formatBooleanCellValue($node);
            case self::CELL_TYPE_DATE:
                return $this->formatDateCellValue($node);
            case self::CELL_TYPE_TIME:
                return $this->formatTimeCellValue($node);
            case self::CELL_TYPE_CURRENCY:
                return $this->formatCurrencyCellValue($node);
            case self::CELL_TYPE_PERCENTAGE:
                return $this->formatPercentageCellValue($node);
            case self::CELL_TYPE_VOID:
            default:
                return '';
        }
    }

    
    protected function formatStringCellValue($node)
    {
        $pNodeValues = [];
        $pNodes = $node->getElementsByTagName(self::XML_NODE_P);

        foreach ($pNodes as $pNode) {
            $currentPValue = '';

            foreach ($pNode->childNodes as $childNode) {
                if ($childNode instanceof \DOMText) {
                    $currentPValue .= $childNode->nodeValue;
                } else if ($childNode->nodeName === self::XML_NODE_S) {
                    $spaceAttribute = $childNode->getAttribute(self::XML_ATTRIBUTE_C);
                    $numSpaces = (!empty($spaceAttribute)) ? intval($spaceAttribute) : 1;
                    $currentPValue .= str_repeat(' ', $numSpaces);
                }
            }

            $pNodeValues[] = $currentPValue;
        }

        $escapedCellValue = implode("\n", $pNodeValues);
        $cellValue = $this->escaper->unescape($escapedCellValue);
        return $cellValue;
    }

    
    protected function formatFloatCellValue($node)
    {
        $nodeValue = $node->getAttribute(self::XML_ATTRIBUTE_VALUE);
        $nodeIntValue = intval($nodeValue);
        $cellValue = ($nodeIntValue == $nodeValue) ? $nodeIntValue : floatval($nodeValue);
        return $cellValue;
    }

    
    protected function formatBooleanCellValue($node)
    {
        $nodeValue = $node->getAttribute(self::XML_ATTRIBUTE_BOOLEAN_VALUE);
                $cellValue = !!$nodeValue;
        return $cellValue;
    }

    
    protected function formatDateCellValue($node)
    {
        try {
            $nodeValue = $node->getAttribute(self::XML_ATTRIBUTE_DATE_VALUE);
            return new \DateTime($nodeValue);
        } catch (\Exception $e) {
            return null;
        }
    }

    
    protected function formatTimeCellValue($node)
    {
        try {
            $nodeValue = $node->getAttribute(self::XML_ATTRIBUTE_TIME_VALUE);
            return new \DateInterval($nodeValue);
        } catch (\Exception $e) {
            return null;
        }
    }

    
    protected function formatCurrencyCellValue($node)
    {
        $value = $node->getAttribute(self::XML_ATTRIBUTE_VALUE);
        $currency = $node->getAttribute(self::XML_ATTRIBUTE_CURRENCY);

        return "$value $currency";
    }

    
    protected function formatPercentageCellValue($node)
    {
                return $this->formatFloatCellValue($node);
    }
}
