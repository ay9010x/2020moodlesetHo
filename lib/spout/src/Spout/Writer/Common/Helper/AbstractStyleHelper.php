<?php

namespace Box\Spout\Writer\Common\Helper;


abstract class AbstractStyleHelper
{
    
    protected $serializedStyleToStyleIdMappingTable = [];

    
    protected $styleIdToStyleMappingTable = [];

    
    public function __construct($defaultStyle)
    {
                $this->registerStyle($defaultStyle);
    }

    
    public function registerStyle($style)
    {
        $serializedStyle = $style->serialize();

        if (!$this->hasStyleAlreadyBeenRegistered($style)) {
            $nextStyleId = count($this->serializedStyleToStyleIdMappingTable);
            $style->setId($nextStyleId);

            $this->serializedStyleToStyleIdMappingTable[$serializedStyle] = $nextStyleId;
            $this->styleIdToStyleMappingTable[$nextStyleId] = $style;
        }

        return $this->getStyleFromSerializedStyle($serializedStyle);
    }

    
    protected function hasStyleAlreadyBeenRegistered($style)
    {
        $serializedStyle = $style->serialize();

                return isset($this->serializedStyleToStyleIdMappingTable[$serializedStyle]);
    }

    
    protected function getStyleFromSerializedStyle($serializedStyle)
    {
        $styleId = $this->serializedStyleToStyleIdMappingTable[$serializedStyle];
        return $this->styleIdToStyleMappingTable[$styleId];
    }

    
    protected function getRegisteredStyles()
    {
        return array_values($this->styleIdToStyleMappingTable);
    }

    
    protected function getDefaultStyle()
    {
                return $this->styleIdToStyleMappingTable[0];
    }

    
    public function applyExtraStylesIfNeeded($style, $dataRow)
    {
        $updatedStyle = $this->applyWrapTextIfCellContainsNewLine($style, $dataRow);
        return $updatedStyle;
    }

    
    protected function applyWrapTextIfCellContainsNewLine($style, $dataRow)
    {
                if ($style->shouldWrapText()) {
            return $style;
        }

        foreach ($dataRow as $cell) {
            if (is_string($cell) && strpos($cell, "\n") !== false) {
                $style->setShouldWrapText();
                break;
            }
        }

        return $style;
    }
}
