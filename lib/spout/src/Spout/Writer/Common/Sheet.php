<?php

namespace Box\Spout\Writer\Common;

use Box\Spout\Common\Helper\StringHelper;
use Box\Spout\Writer\Exception\InvalidSheetNameException;


class Sheet
{
    const DEFAULT_SHEET_NAME_PREFIX = 'Sheet';

    
    const MAX_LENGTH_SHEET_NAME = 31;

    
    private static $INVALID_CHARACTERS_IN_SHEET_NAME = ['\\', '/', '?', '*', ':', '[', ']'];

    
    protected static $SHEETS_NAME_USED = [];

    
    protected $index;

    
    protected $name;

    
    protected $stringHelper;

    
    public function __construct($sheetIndex)
    {
        $this->index = $sheetIndex;
        $this->stringHelper = new StringHelper();
        $this->setName(self::DEFAULT_SHEET_NAME_PREFIX . ($sheetIndex + 1));
    }

    
    public function getIndex()
    {
        return $this->index;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function setName($name)
    {
        if (!$this->isNameValid($name)) {
            $errorMessage = "The sheet's name is invalid. It did not meet at least one of these requirements:\n";
            $errorMessage .= " - It should not be blank\n";
            $errorMessage .= " - It should not exceed 31 characters\n";
            $errorMessage .= " - It should not contain these characters: \\ / ? * : [ or ]\n";
            $errorMessage .= " - It should be unique";
            throw new InvalidSheetNameException($errorMessage);
        }

        $this->name = $name;
        self::$SHEETS_NAME_USED[$this->index] = $name;

        return $this;
    }

    
    protected function isNameValid($name)
    {
        if (!is_string($name)) {
            return false;
        }

        $nameLength = $this->stringHelper->getStringLength($name);

        return (
            $nameLength > 0 &&
            $nameLength <= self::MAX_LENGTH_SHEET_NAME &&
            !$this->doesContainInvalidCharacters($name) &&
            $this->isNameUnique($name) &&
            !$this->doesStartOrEndWithSingleQuote($name)
        );
    }

    
    protected function doesContainInvalidCharacters($name)
    {
        return (str_replace(self::$INVALID_CHARACTERS_IN_SHEET_NAME, '', $name) !== $name);
    }

    
    protected function doesStartOrEndWithSingleQuote($name)
    {
        $startsWithSingleQuote = ($this->stringHelper->getCharFirstOccurrencePosition('\'', $name) === 0);
        $endsWithSingleQuote = ($this->stringHelper->getCharLastOccurrencePosition('\'', $name) === ($this->stringHelper->getStringLength($name) - 1));

        return ($startsWithSingleQuote || $endsWithSingleQuote);
    }

    
    protected function isNameUnique($name)
    {
        foreach (self::$SHEETS_NAME_USED as $sheetIndex => $sheetName) {
            if ($sheetIndex !== $this->index && $sheetName === $name) {
                return false;
            }
        }

        return true;
    }
}
