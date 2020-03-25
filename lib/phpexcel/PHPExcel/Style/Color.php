<?php


class PHPExcel_Style_Color extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    const COLOR_BLACK      = 'FF000000';
    const COLOR_WHITE      = 'FFFFFFFF';
    const COLOR_RED        = 'FFFF0000';
    const COLOR_DARKRED    = 'FF800000';
    const COLOR_BLUE       = 'FF0000FF';
    const COLOR_DARKBLUE   = 'FF000080';
    const COLOR_GREEN      = 'FF00FF00';
    const COLOR_DARKGREEN  = 'FF008000';
    const COLOR_YELLOW     = 'FFFFFF00';
    const COLOR_DARKYELLOW = 'FF808000';

    
    protected static $indexedColors;

    
    protected $argb = null;

    
    protected $parentPropertyName;


    
    public function __construct($pARGB = PHPExcel_Style_Color::COLOR_BLACK, $isSupervisor = false, $isConditional = false)
    {
                parent::__construct($isSupervisor);

                if (!$isConditional) {
            $this->argb = $pARGB;
        }
    }

    
    public function bindParent($parent, $parentPropertyName = null)
    {
        $this->parent = $parent;
        $this->parentPropertyName = $parentPropertyName;
        return $this;
    }

    
    public function getSharedComponent()
    {
        switch ($this->parentPropertyName) {
            case 'endColor':
                return $this->parent->getSharedComponent()->getEndColor();
            case 'color':
                return $this->parent->getSharedComponent()->getColor();
            case 'startColor':
                return $this->parent->getSharedComponent()->getStartColor();
        }
    }

    
    public function getStyleArray($array)
    {
        switch ($this->parentPropertyName) {
            case 'endColor':
                $key = 'endcolor';
                break;
            case 'color':
                $key = 'color';
                break;
            case 'startColor':
                $key = 'startcolor';
                break;

        }
        return $this->parent->getStyleArray(array($key => $array));
    }

    
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
            } else {
                if (array_key_exists('rgb', $pStyles)) {
                    $this->setRGB($pStyles['rgb']);
                }
                if (array_key_exists('argb', $pStyles)) {
                    $this->setARGB($pStyles['argb']);
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getARGB()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getARGB();
        }
        return $this->argb;
    }

    
    public function setARGB($pValue = PHPExcel_Style_Color::COLOR_BLACK)
    {
        if ($pValue == '') {
            $pValue = PHPExcel_Style_Color::COLOR_BLACK;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('argb' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->argb = $pValue;
        }
        return $this;
    }

    
    public function getRGB()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getRGB();
        }
        return substr($this->argb, 2);
    }

    
    public function setRGB($pValue = '000000')
    {
        if ($pValue == '') {
            $pValue = '000000';
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('argb' => 'FF' . $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->argb = 'FF' . $pValue;
        }
        return $this;
    }

    
    private static function getColourComponent($RGB, $offset, $hex = true)
    {
        $colour = substr($RGB, $offset, 2);
        if (!$hex) {
            $colour = hexdec($colour);
        }
        return $colour;
    }

    
    public static function getRed($RGB, $hex = true)
    {
        return self::getColourComponent($RGB, strlen($RGB) - 6, $hex);
    }

    
    public static function getGreen($RGB, $hex = true)
    {
        return self::getColourComponent($RGB, strlen($RGB) - 4, $hex);
    }

    
    public static function getBlue($RGB, $hex = true)
    {
        return self::getColourComponent($RGB, strlen($RGB) - 2, $hex);
    }

    
    public static function changeBrightness($hex, $adjustPercentage)
    {
        $rgba = (strlen($hex) == 8);

        $red = self::getRed($hex, false);
        $green = self::getGreen($hex, false);
        $blue = self::getBlue($hex, false);
        if ($adjustPercentage > 0) {
            $red += (255 - $red) * $adjustPercentage;
            $green += (255 - $green) * $adjustPercentage;
            $blue += (255 - $blue) * $adjustPercentage;
        } else {
            $red += $red * $adjustPercentage;
            $green += $green * $adjustPercentage;
            $blue += $blue * $adjustPercentage;
        }

        if ($red < 0) {
            $red = 0;
        } elseif ($red > 255) {
            $red = 255;
        }
        if ($green < 0) {
            $green = 0;
        } elseif ($green > 255) {
            $green = 255;
        }
        if ($blue < 0) {
            $blue = 0;
        } elseif ($blue > 255) {
            $blue = 255;
        }

        $rgb = strtoupper(
            str_pad(dechex($red), 2, '0', 0) .
            str_pad(dechex($green), 2, '0', 0) .
            str_pad(dechex($blue), 2, '0', 0)
        );
        return (($rgba) ? 'FF' : '') . $rgb;
    }

    
    public static function indexedColor($pIndex, $background = false)
    {
                $pIndex = intval($pIndex);

                if (is_null(self::$indexedColors)) {
            self::$indexedColors = array(
                    1    => 'FF000000',                        2    => 'FFFFFFFF',                        3    => 'FFFF0000',                        4    => 'FF00FF00',                        5    => 'FF0000FF',                        6    => 'FFFFFF00',                        7    => 'FFFF00FF',                        8    => 'FF00FFFF',                        9    => 'FF800000',                        10    => 'FF008000',                        11    => 'FF000080',                        12    => 'FF808000',                        13    => 'FF800080',                        14    => 'FF008080',                        15    => 'FFC0C0C0',                        16    => 'FF808080',                        17    => 'FF9999FF',                        18    => 'FF993366',                        19    => 'FFFFFFCC',                        20    => 'FFCCFFFF',                        21    => 'FF660066',                        22    => 'FFFF8080',                        23    => 'FF0066CC',                        24    => 'FFCCCCFF',                        25    => 'FF000080',                        26    => 'FFFF00FF',                        27    => 'FFFFFF00',                        28    => 'FF00FFFF',                        29    => 'FF800080',                        30    => 'FF800000',                        31    => 'FF008080',                        32    => 'FF0000FF',                        33    => 'FF00CCFF',                        34    => 'FFCCFFFF',                        35    => 'FFCCFFCC',                        36    => 'FFFFFF99',                        37    => 'FF99CCFF',                        38    => 'FFFF99CC',                        39    => 'FFCC99FF',                        40    => 'FFFFCC99',                        41    => 'FF3366FF',                        42    => 'FF33CCCC',                        43    => 'FF99CC00',                        44    => 'FFFFCC00',                        45    => 'FFFF9900',                        46    => 'FFFF6600',                        47    => 'FF666699',                        48    => 'FF969696',                        49    => 'FF003366',                        50    => 'FF339966',                        51    => 'FF003300',                        52    => 'FF333300',                        53    => 'FF993300',                        54    => 'FF993366',                        55    => 'FF333399',                        56    => 'FF333333'                    );
        }

        if (array_key_exists($pIndex, self::$indexedColors)) {
            return new PHPExcel_Style_Color(self::$indexedColors[$pIndex]);
        }

        if ($background) {
            return new PHPExcel_Style_Color(self::COLOR_WHITE);
        }
        return new PHPExcel_Style_Color(self::COLOR_BLACK);
    }

    
    public function getHashCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHashCode();
        }
        return md5(
            $this->argb .
            __CLASS__
        );
    }
}
