<?php




class PHPExcel_Worksheet_PageSetup
{
    
    const PAPERSIZE_LETTER                          = 1;
    const PAPERSIZE_LETTER_SMALL                    = 2;
    const PAPERSIZE_TABLOID                         = 3;
    const PAPERSIZE_LEDGER                          = 4;
    const PAPERSIZE_LEGAL                           = 5;
    const PAPERSIZE_STATEMENT                       = 6;
    const PAPERSIZE_EXECUTIVE                       = 7;
    const PAPERSIZE_A3                              = 8;
    const PAPERSIZE_A4                              = 9;
    const PAPERSIZE_A4_SMALL                        = 10;
    const PAPERSIZE_A5                              = 11;
    const PAPERSIZE_B4                              = 12;
    const PAPERSIZE_B5                              = 13;
    const PAPERSIZE_FOLIO                           = 14;
    const PAPERSIZE_QUARTO                          = 15;
    const PAPERSIZE_STANDARD_1                      = 16;
    const PAPERSIZE_STANDARD_2                      = 17;
    const PAPERSIZE_NOTE                            = 18;
    const PAPERSIZE_NO9_ENVELOPE                    = 19;
    const PAPERSIZE_NO10_ENVELOPE                   = 20;
    const PAPERSIZE_NO11_ENVELOPE                   = 21;
    const PAPERSIZE_NO12_ENVELOPE                   = 22;
    const PAPERSIZE_NO14_ENVELOPE                   = 23;
    const PAPERSIZE_C                               = 24;
    const PAPERSIZE_D                               = 25;
    const PAPERSIZE_E                               = 26;
    const PAPERSIZE_DL_ENVELOPE                     = 27;
    const PAPERSIZE_C5_ENVELOPE                     = 28;
    const PAPERSIZE_C3_ENVELOPE                     = 29;
    const PAPERSIZE_C4_ENVELOPE                     = 30;
    const PAPERSIZE_C6_ENVELOPE                     = 31;
    const PAPERSIZE_C65_ENVELOPE                    = 32;
    const PAPERSIZE_B4_ENVELOPE                     = 33;
    const PAPERSIZE_B5_ENVELOPE                     = 34;
    const PAPERSIZE_B6_ENVELOPE                     = 35;
    const PAPERSIZE_ITALY_ENVELOPE                  = 36;
    const PAPERSIZE_MONARCH_ENVELOPE                = 37;
    const PAPERSIZE_6_3_4_ENVELOPE                  = 38;
    const PAPERSIZE_US_STANDARD_FANFOLD             = 39;
    const PAPERSIZE_GERMAN_STANDARD_FANFOLD         = 40;
    const PAPERSIZE_GERMAN_LEGAL_FANFOLD            = 41;
    const PAPERSIZE_ISO_B4                          = 42;
    const PAPERSIZE_JAPANESE_DOUBLE_POSTCARD        = 43;
    const PAPERSIZE_STANDARD_PAPER_1                = 44;
    const PAPERSIZE_STANDARD_PAPER_2                = 45;
    const PAPERSIZE_STANDARD_PAPER_3                = 46;
    const PAPERSIZE_INVITE_ENVELOPE                 = 47;
    const PAPERSIZE_LETTER_EXTRA_PAPER              = 48;
    const PAPERSIZE_LEGAL_EXTRA_PAPER               = 49;
    const PAPERSIZE_TABLOID_EXTRA_PAPER             = 50;
    const PAPERSIZE_A4_EXTRA_PAPER                  = 51;
    const PAPERSIZE_LETTER_TRANSVERSE_PAPER         = 52;
    const PAPERSIZE_A4_TRANSVERSE_PAPER             = 53;
    const PAPERSIZE_LETTER_EXTRA_TRANSVERSE_PAPER   = 54;
    const PAPERSIZE_SUPERA_SUPERA_A4_PAPER          = 55;
    const PAPERSIZE_SUPERB_SUPERB_A3_PAPER          = 56;
    const PAPERSIZE_LETTER_PLUS_PAPER               = 57;
    const PAPERSIZE_A4_PLUS_PAPER                   = 58;
    const PAPERSIZE_A5_TRANSVERSE_PAPER             = 59;
    const PAPERSIZE_JIS_B5_TRANSVERSE_PAPER         = 60;
    const PAPERSIZE_A3_EXTRA_PAPER                  = 61;
    const PAPERSIZE_A5_EXTRA_PAPER                  = 62;
    const PAPERSIZE_ISO_B5_EXTRA_PAPER              = 63;
    const PAPERSIZE_A2_PAPER                        = 64;
    const PAPERSIZE_A3_TRANSVERSE_PAPER             = 65;
    const PAPERSIZE_A3_EXTRA_TRANSVERSE_PAPER       = 66;

    
    const ORIENTATION_DEFAULT                       = 'default';
    const ORIENTATION_LANDSCAPE                     = 'landscape';
    const ORIENTATION_PORTRAIT                      = 'portrait';

    
    const SETPRINTRANGE_OVERWRITE                   = 'O';
    const SETPRINTRANGE_INSERT                      = 'I';


    
    private $paperSize = PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER;

    
    private $orientation = PHPExcel_Worksheet_PageSetup::ORIENTATION_DEFAULT;

    
    private $scale = 100;

    
    private $fitToPage = false;

    
    private $fitToHeight    = 1;

    
    private $fitToWidth    = 1;

    
    private $columnsToRepeatAtLeft = array('', '');

    
    private $rowsToRepeatAtTop = array(0, 0);

    
    private $horizontalCentered = false;

    
    private $verticalCentered = false;

    
    private $printArea = null;

    
    private $firstPageNumber = null;

    
    public function __construct()
    {
    }

    
    public function getPaperSize()
    {
        return $this->paperSize;
    }

    
    public function setPaperSize($pValue = PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER)
    {
        $this->paperSize = $pValue;
        return $this;
    }

    
    public function getOrientation()
    {
        return $this->orientation;
    }

    
    public function setOrientation($pValue = PHPExcel_Worksheet_PageSetup::ORIENTATION_DEFAULT)
    {
        $this->orientation = $pValue;
        return $this;
    }

    
    public function getScale()
    {
        return $this->scale;
    }

    
    public function setScale($pValue = 100, $pUpdate = true)
    {
                        if (($pValue >= 0) || is_null($pValue)) {
            $this->scale = $pValue;
            if ($pUpdate) {
                $this->fitToPage = false;
            }
        } else {
            throw new PHPExcel_Exception("Scale must not be negative");
        }
        return $this;
    }

    
    public function getFitToPage()
    {
        return $this->fitToPage;
    }

    
    public function setFitToPage($pValue = true)
    {
        $this->fitToPage = $pValue;
        return $this;
    }

    
    public function getFitToHeight()
    {
        return $this->fitToHeight;
    }

    
    public function setFitToHeight($pValue = 1, $pUpdate = true)
    {
        $this->fitToHeight = $pValue;
        if ($pUpdate) {
            $this->fitToPage = true;
        }
        return $this;
    }

    
    public function getFitToWidth()
    {
        return $this->fitToWidth;
    }

    
    public function setFitToWidth($pValue = 1, $pUpdate = true)
    {
        $this->fitToWidth = $pValue;
        if ($pUpdate) {
            $this->fitToPage = true;
        }
        return $this;
    }

    
    public function isColumnsToRepeatAtLeftSet()
    {
        if (is_array($this->columnsToRepeatAtLeft)) {
            if ($this->columnsToRepeatAtLeft[0] != '' && $this->columnsToRepeatAtLeft[1] != '') {
                return true;
            }
        }

        return false;
    }

    
    public function getColumnsToRepeatAtLeft()
    {
        return $this->columnsToRepeatAtLeft;
    }

    
    public function setColumnsToRepeatAtLeft($pValue = null)
    {
        if (is_array($pValue)) {
            $this->columnsToRepeatAtLeft = $pValue;
        }
        return $this;
    }

    
    public function setColumnsToRepeatAtLeftByStartAndEnd($pStart = 'A', $pEnd = 'A')
    {
        $this->columnsToRepeatAtLeft = array($pStart, $pEnd);
        return $this;
    }

    
    public function isRowsToRepeatAtTopSet()
    {
        if (is_array($this->rowsToRepeatAtTop)) {
            if ($this->rowsToRepeatAtTop[0] != 0 && $this->rowsToRepeatAtTop[1] != 0) {
                return true;
            }
        }

        return false;
    }

    
    public function getRowsToRepeatAtTop()
    {
        return $this->rowsToRepeatAtTop;
    }

    
    public function setRowsToRepeatAtTop($pValue = null)
    {
        if (is_array($pValue)) {
            $this->rowsToRepeatAtTop = $pValue;
        }
        return $this;
    }

    
    public function setRowsToRepeatAtTopByStartAndEnd($pStart = 1, $pEnd = 1)
    {
        $this->rowsToRepeatAtTop = array($pStart, $pEnd);
        return $this;
    }

    
    public function getHorizontalCentered()
    {
        return $this->horizontalCentered;
    }

    
    public function setHorizontalCentered($value = false)
    {
        $this->horizontalCentered = $value;
        return $this;
    }

    
    public function getVerticalCentered()
    {
        return $this->verticalCentered;
    }

    
    public function setVerticalCentered($value = false)
    {
        $this->verticalCentered = $value;
        return $this;
    }

    
    public function getPrintArea($index = 0)
    {
        if ($index == 0) {
            return $this->printArea;
        }
        $printAreas = explode(',', $this->printArea);
        if (isset($printAreas[$index-1])) {
            return $printAreas[$index-1];
        }
        throw new PHPExcel_Exception("Requested Print Area does not exist");
    }

    
    public function isPrintAreaSet($index = 0)
    {
        if ($index == 0) {
            return !is_null($this->printArea);
        }
        $printAreas = explode(',', $this->printArea);
        return isset($printAreas[$index-1]);
    }

    
    public function clearPrintArea($index = 0)
    {
        if ($index == 0) {
            $this->printArea = null;
        } else {
            $printAreas = explode(',', $this->printArea);
            if (isset($printAreas[$index-1])) {
                unset($printAreas[$index-1]);
                $this->printArea = implode(',', $printAreas);
            }
        }

        return $this;
    }

    
    public function setPrintArea($value, $index = 0, $method = self::SETPRINTRANGE_OVERWRITE)
    {
        if (strpos($value, '!') !== false) {
            throw new PHPExcel_Exception('Cell coordinate must not specify a worksheet.');
        } elseif (strpos($value, ':') === false) {
            throw new PHPExcel_Exception('Cell coordinate must be a range of cells.');
        } elseif (strpos($value, '$') !== false) {
            throw new PHPExcel_Exception('Cell coordinate must not be absolute.');
        }
        $value = strtoupper($value);

        if ($method == self::SETPRINTRANGE_OVERWRITE) {
            if ($index == 0) {
                $this->printArea = $value;
            } else {
                $printAreas = explode(',', $this->printArea);
                if ($index < 0) {
                    $index = count($printAreas) - abs($index) + 1;
                }
                if (($index <= 0) || ($index > count($printAreas))) {
                    throw new PHPExcel_Exception('Invalid index for setting print range.');
                }
                $printAreas[$index-1] = $value;
                $this->printArea = implode(',', $printAreas);
            }
        } elseif ($method == self::SETPRINTRANGE_INSERT) {
            if ($index == 0) {
                $this->printArea .= ($this->printArea == '') ? $value : ','.$value;
            } else {
                $printAreas = explode(',', $this->printArea);
                if ($index < 0) {
                    $index = abs($index) - 1;
                }
                if ($index > count($printAreas)) {
                    throw new PHPExcel_Exception('Invalid index for setting print range.');
                }
                $printAreas = array_merge(array_slice($printAreas, 0, $index), array($value), array_slice($printAreas, $index));
                $this->printArea = implode(',', $printAreas);
            }
        } else {
            throw new PHPExcel_Exception('Invalid method for setting print range.');
        }

        return $this;
    }

    
    public function addPrintArea($value, $index = -1)
    {
        return $this->setPrintArea($value, $index, self::SETPRINTRANGE_INSERT);
    }

    
    public function setPrintAreaByColumnAndRow($column1, $row1, $column2, $row2, $index = 0, $method = self::SETPRINTRANGE_OVERWRITE)
    {
        return $this->setPrintArea(
            PHPExcel_Cell::stringFromColumnIndex($column1) . $row1 . ':' . PHPExcel_Cell::stringFromColumnIndex($column2) . $row2,
            $index,
            $method
        );
    }

    
    public function addPrintAreaByColumnAndRow($column1, $row1, $column2, $row2, $index = -1)
    {
        return $this->setPrintArea(
            PHPExcel_Cell::stringFromColumnIndex($column1) . $row1 . ':' . PHPExcel_Cell::stringFromColumnIndex($column2) . $row2,
            $index,
            self::SETPRINTRANGE_INSERT
        );
    }

    
    public function getFirstPageNumber()
    {
        return $this->firstPageNumber;
    }

    
    public function setFirstPageNumber($value = null)
    {
        $this->firstPageNumber = $value;
        return $this;
    }

    
    public function resetFirstPageNumber()
    {
        return $this->setFirstPageNumber(null);
    }

    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }
}
