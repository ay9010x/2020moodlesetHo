<?php


class PHPExcel_Style_NumberFormat extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    const FORMAT_GENERAL                 = 'General';

    const FORMAT_TEXT                    = '@';

    const FORMAT_NUMBER                  = '0';
    const FORMAT_NUMBER_00               = '0.00';
    const FORMAT_NUMBER_COMMA_SEPARATED1 = '#,##0.00';
    const FORMAT_NUMBER_COMMA_SEPARATED2 = '#,##0.00_-';

    const FORMAT_PERCENTAGE              = '0%';
    const FORMAT_PERCENTAGE_00           = '0.00%';

    const FORMAT_DATE_YYYYMMDD2          = 'yyyy-mm-dd';
    const FORMAT_DATE_YYYYMMDD           = 'yy-mm-dd';
    const FORMAT_DATE_DDMMYYYY           = 'dd/mm/yy';
    const FORMAT_DATE_DMYSLASH           = 'd/m/y';
    const FORMAT_DATE_DMYMINUS           = 'd-m-y';
    const FORMAT_DATE_DMMINUS            = 'd-m';
    const FORMAT_DATE_MYMINUS            = 'm-y';
    const FORMAT_DATE_XLSX14             = 'mm-dd-yy';
    const FORMAT_DATE_XLSX15             = 'd-mmm-yy';
    const FORMAT_DATE_XLSX16             = 'd-mmm';
    const FORMAT_DATE_XLSX17             = 'mmm-yy';
    const FORMAT_DATE_XLSX22             = 'm/d/yy h:mm';
    const FORMAT_DATE_DATETIME           = 'd/m/y h:mm';
    const FORMAT_DATE_TIME1              = 'h:mm AM/PM';
    const FORMAT_DATE_TIME2              = 'h:mm:ss AM/PM';
    const FORMAT_DATE_TIME3              = 'h:mm';
    const FORMAT_DATE_TIME4              = 'h:mm:ss';
    const FORMAT_DATE_TIME5              = 'mm:ss';
    const FORMAT_DATE_TIME6              = 'h:mm:ss';
    const FORMAT_DATE_TIME7              = 'i:s.S';
    const FORMAT_DATE_TIME8              = 'h:mm:ss;@';
    const FORMAT_DATE_YYYYMMDDSLASH      = 'yy/mm/dd;@';

    const FORMAT_CURRENCY_USD_SIMPLE     = '"$"#,##0.00_-';
    const FORMAT_CURRENCY_USD            = '$#,##0_-';
    const FORMAT_CURRENCY_EUR_SIMPLE     = '[$EUR ]#,##0.00_-';

    
    protected static $builtInFormats;

    
    protected static $flippedBuiltInFormats;

    
    protected $formatCode = PHPExcel_Style_NumberFormat::FORMAT_GENERAL;

    
    protected $builtInFormatCode    = 0;

    
    public function __construct($isSupervisor = false, $isConditional = false)
    {
                parent::__construct($isSupervisor);

        if ($isConditional) {
            $this->formatCode = null;
            $this->builtInFormatCode = false;
        }
    }

    
    public function getSharedComponent()
    {
        return $this->parent->getSharedComponent()->getNumberFormat();
    }

    
    public function getStyleArray($array)
    {
        return array('numberformat' => $array);
    }

    
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
            } else {
                if (array_key_exists('code', $pStyles)) {
                    $this->setFormatCode($pStyles['code']);
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getFormatCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getFormatCode();
        }
        if ($this->builtInFormatCode !== false) {
            return self::builtInFormatCode($this->builtInFormatCode);
        }
        return $this->formatCode;
    }

    
    public function setFormatCode($pValue = PHPExcel_Style_NumberFormat::FORMAT_GENERAL)
    {
        if ($pValue == '') {
            $pValue = PHPExcel_Style_NumberFormat::FORMAT_GENERAL;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('code' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->formatCode = $pValue;
            $this->builtInFormatCode = self::builtInFormatCodeIndex($pValue);
        }
        return $this;
    }

    
    public function getBuiltInFormatCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getBuiltInFormatCode();
        }
        return $this->builtInFormatCode;
    }

    
    public function setBuiltInFormatCode($pValue = 0)
    {

        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('code' => self::builtInFormatCode($pValue)));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->builtInFormatCode = $pValue;
            $this->formatCode = self::builtInFormatCode($pValue);
        }
        return $this;
    }

    
    private static function fillBuiltInFormatCodes()
    {
                                                                                                                                                                         
                if (is_null(self::$builtInFormats)) {
            self::$builtInFormats = array();

                        self::$builtInFormats[0] = PHPExcel_Style_NumberFormat::FORMAT_GENERAL;
            self::$builtInFormats[1] = '0';
            self::$builtInFormats[2] = '0.00';
            self::$builtInFormats[3] = '#,##0';
            self::$builtInFormats[4] = '#,##0.00';

            self::$builtInFormats[9] = '0%';
            self::$builtInFormats[10] = '0.00%';
            self::$builtInFormats[11] = '0.00E+00';
            self::$builtInFormats[12] = '# ?/?';
            self::$builtInFormats[13] = '# ??/??';
            self::$builtInFormats[14] = 'm/d/yyyy';                                 self::$builtInFormats[15] = 'd-mmm-yy';
            self::$builtInFormats[16] = 'd-mmm';
            self::$builtInFormats[17] = 'mmm-yy';
            self::$builtInFormats[18] = 'h:mm AM/PM';
            self::$builtInFormats[19] = 'h:mm:ss AM/PM';
            self::$builtInFormats[20] = 'h:mm';
            self::$builtInFormats[21] = 'h:mm:ss';
            self::$builtInFormats[22] = 'm/d/yyyy h:mm';                
            self::$builtInFormats[37] = '#,##0_);(#,##0)';                          self::$builtInFormats[38] = '#,##0_);[Red](#,##0)';                     self::$builtInFormats[39] = '#,##0.00_);(#,##0.00)';                    self::$builtInFormats[40] = '#,##0.00_);[Red](#,##0.00)';   
            self::$builtInFormats[44] = '_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)';
            self::$builtInFormats[45] = 'mm:ss';
            self::$builtInFormats[46] = '[h]:mm:ss';
            self::$builtInFormats[47] = 'mm:ss.0';                                  self::$builtInFormats[48] = '##0.0E+0';
            self::$builtInFormats[49] = '@';

                        self::$builtInFormats[27] = '[$-404]e/m/d';
            self::$builtInFormats[30] = 'm/d/yy';
            self::$builtInFormats[36] = '[$-404]e/m/d';
            self::$builtInFormats[50] = '[$-404]e/m/d';
            self::$builtInFormats[57] = '[$-404]e/m/d';

                        self::$builtInFormats[59] = 't0';
            self::$builtInFormats[60] = 't0.00';
            self::$builtInFormats[61] = 't#,##0';
            self::$builtInFormats[62] = 't#,##0.00';
            self::$builtInFormats[67] = 't0%';
            self::$builtInFormats[68] = 't0.00%';
            self::$builtInFormats[69] = 't# ?/?';
            self::$builtInFormats[70] = 't# ??/??';

                        self::$flippedBuiltInFormats = array_flip(self::$builtInFormats);
        }
    }

    
    public static function builtInFormatCode($pIndex)
    {
                $pIndex = intval($pIndex);

                self::fillBuiltInFormatCodes();

                if (isset(self::$builtInFormats[$pIndex])) {
            return self::$builtInFormats[$pIndex];
        }

        return '';
    }

    
    public static function builtInFormatCodeIndex($formatCode)
    {
                self::fillBuiltInFormatCodes();

                if (isset(self::$flippedBuiltInFormats[$formatCode])) {
            return self::$flippedBuiltInFormats[$formatCode];
        }

        return false;
    }

    
    public function getHashCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHashCode();
        }
        return md5(
            $this->formatCode .
            $this->builtInFormatCode .
            __CLASS__
        );
    }

    
    private static $dateFormatReplacements = array(
                        '\\'    => '',
                        'am/pm' => 'A',
                        'e'     => 'Y',
            'yyyy'  => 'Y',
                        'yy'    => 'y',
                        'mmmmm' => 'M',
                        'mmmm'  => 'F',
                        'mmm'   => 'M',
                                                ':mm'   => ':i',
            'mm:'   => 'i:',
                        'mm'    => 'm',
                        'm'     => 'n',
                        'dddd'  => 'l',
                        'ddd'   => 'D',
                        'dd'    => 'd',
                        'd'     => 'j',
                        'ss'    => 's',
                        '.s'    => ''
        );
    
    private static $dateFormatReplacements24 = array(
            'hh' => 'H',
            'h'  => 'G'
        );
    
    private static $dateFormatReplacements12 = array(
            'hh' => 'h',
            'h'  => 'g'
        );

    private static function setLowercaseCallback($matches) {
        return mb_strtolower($matches[0]);
    }

    private static function escapeQuotesCallback($matches) {
        return '\\' . implode('\\', str_split($matches[1]));
    }

    private static function formatAsDate(&$value, &$format)
    {
                                $format = preg_replace('/^(\[\$[A-Z]*-[0-9A-F]*\])/i', '', $format);

                        $format = preg_replace_callback('/(?:^|")([^"]*)(?:$|")/', array('self', 'setLowercaseCallback'), $format);

                $blocks = explode('"', $format);
        foreach($blocks as $key => &$block) {
            if ($key % 2 == 0) {
                $block = strtr($block, self::$dateFormatReplacements);
                if (!strpos($block, 'A')) {
                                        $block = strtr($block, self::$dateFormatReplacements24);
                } else {
                                        $block = strtr($block, self::$dateFormatReplacements12);
                }
            }
        }
        $format = implode('"', $blocks);

                $format = preg_replace_callback('/"(.*)"/U', array('self', 'escapeQuotesCallback'), $format);

        $dateObj = PHPExcel_Shared_Date::ExcelToPHPObject($value);
        $value = $dateObj->format($format);
    }

    private static function formatAsPercentage(&$value, &$format)
    {
        if ($format === self::FORMAT_PERCENTAGE) {
            $value = round((100 * $value), 0) . '%';
        } else {
            if (preg_match('/\.[#0]+/i', $format, $m)) {
                $s = substr($m[0], 0, 1) . (strlen($m[0]) - 1);
                $format = str_replace($m[0], $s, $format);
            }
            if (preg_match('/^[#0]+/', $format, $m)) {
                $format = str_replace($m[0], strlen($m[0]), $format);
            }
            $format = '%' . str_replace('%', 'f%%', $format);

            $value = sprintf($format, 100 * $value);
        }
    }

    private static function formatAsFraction(&$value, &$format)
    {
        $sign = ($value < 0) ? '-' : '';

        $integerPart = floor(abs($value));
        $decimalPart = trim(fmod(abs($value), 1), '0.');
        $decimalLength = strlen($decimalPart);
        $decimalDivisor = pow(10, $decimalLength);

        $GCD = PHPExcel_Calculation_MathTrig::GCD($decimalPart, $decimalDivisor);

        $adjustedDecimalPart = $decimalPart/$GCD;
        $adjustedDecimalDivisor = $decimalDivisor/$GCD;

        if ((strpos($format, '0') !== false) || (strpos($format, '#') !== false) || (substr($format, 0, 3) == '? ?')) {
            if ($integerPart == 0) {
                $integerPart = '';
            }
            $value = "$sign$integerPart $adjustedDecimalPart/$adjustedDecimalDivisor";
        } else {
            $adjustedDecimalPart += $integerPart * $adjustedDecimalDivisor;
            $value = "$sign$adjustedDecimalPart/$adjustedDecimalDivisor";
        }
    }

    private static function complexNumberFormatMask($number, $mask, $level = 0)
    {
        $sign = ($number < 0.0);
        $number = abs($number);
        if (strpos($mask, '.') !== false) {
            $numbers = explode('.', $number . '.0');
            $masks = explode('.', $mask . '.0');
            $result1 = self::complexNumberFormatMask($numbers[0], $masks[0], 1);
            $result2 = strrev(self::complexNumberFormatMask(strrev($numbers[1]), strrev($masks[1]), 1));
            return (($sign) ? '-' : '') . $result1 . '.' . $result2;
        }

        $r = preg_match_all('/0+/', $mask, $result, PREG_OFFSET_CAPTURE);
        if ($r > 1) {
            $result = array_reverse($result[0]);

            foreach ($result as $block) {
                $divisor = 1 . $block[0];
                $size = strlen($block[0]);
                $offset = $block[1];

                $blockValue = sprintf(
                    '%0' . $size . 'd',
                    fmod($number, $divisor)
                );
                $number = floor($number / $divisor);
                $mask = substr_replace($mask, $blockValue, $offset, $size);
            }
            if ($number > 0) {
                $mask = substr_replace($mask, $number, $offset, 0);
            }
            $result = $mask;
        } else {
            $result = $number;
        }

        return (($sign) ? '-' : '') . $result;
    }

    
    public static function toFormattedString($value = '0', $format = PHPExcel_Style_NumberFormat::FORMAT_GENERAL, $callBack = null)
    {
                if (!is_numeric($value)) {
            return $value;
        }

                        if (($format === PHPExcel_Style_NumberFormat::FORMAT_GENERAL) || ($format === PHPExcel_Style_NumberFormat::FORMAT_TEXT)) {
            return $value;
        }

                $format = preg_replace('/(\\\(.))(?=(?:[^"]|"[^"]*")*$)/u', '"${2}"', $format);

                $sections = preg_split('/(;)(?=(?:[^"]|"[^"]*")*$)/u', $format);

                                                                switch (count($sections)) {
            case 1:
                $format = $sections[0];
                break;
            case 2:
                $format = ($value >= 0) ? $sections[0] : $sections[1];
                $value = abs($value);                 break;
            case 3:
                $format = ($value > 0) ?
                    $sections[0] : ( ($value < 0) ?
                        $sections[1] : $sections[2]);
                $value = abs($value);                 break;
            case 4:
                $format = ($value > 0) ?
                    $sections[0] : ( ($value < 0) ?
                        $sections[1] : $sections[2]);
                $value = abs($value);                 break;
            default:
                                $format = $sections[0];
                break;
        }

                        $format = preg_replace('/_./', ' ', $format);

                $formatColor = $format;

                $color_regex = '/^\\[[a-zA-Z]+\\]/';
        $format = preg_replace($color_regex, '', $format);

        
                if (preg_match('/(\[\$[A-Z]*-[0-9A-F]*\])*[hmsdy](?=(?:[^"]|"[^"]*")*$)/miu', $format, $matches)) {
                        self::formatAsDate($value, $format);
        } elseif (preg_match('/%$/', $format)) {
                        self::formatAsPercentage($value, $format);
        } else {
            if ($format === self::FORMAT_CURRENCY_EUR_SIMPLE) {
                $value = 'EUR ' . sprintf('%1.2f', $value);
            } else {
                                $format = str_replace(array('"', '*'), '', $format);

                                                                $useThousands = preg_match('/(#,#|0,0)/', $format);
                if ($useThousands) {
                    $format = preg_replace('/0,0/', '00', $format);
                    $format = preg_replace('/#,#/', '##', $format);
                }

                                                                $scale = 1;                 $matches = array();
                if (preg_match('/(#|0)(,+)/', $format, $matches)) {
                    $scale = pow(1000, strlen($matches[2]));

                                        $format = preg_replace('/0,+/', '0', $format);
                    $format = preg_replace('/#,+/', '#', $format);
                }

                if (preg_match('/#?.*\?\/\?/', $format, $m)) {
                                        if ($value != (int)$value) {
                        self::formatAsFraction($value, $format);
                    }

                } else {
                    
                                        $value = $value / $scale;

                                        $format = preg_replace('/\\#/', '0', $format);

                    $n = "/\[[^\]]+\]/";
                    $m = preg_replace($n, '', $format);
                    $number_regex = "/(0+)(\.?)(0*)/";
                    if (preg_match($number_regex, $m, $matches)) {
                        $left = $matches[1];
                        $dec = $matches[2];
                        $right = $matches[3];

                                                $minWidth = strlen($left) + strlen($dec) + strlen($right);
                        if ($useThousands) {
                            $value = number_format(
                                $value,
                                strlen($right),
                                PHPExcel_Shared_String::getDecimalSeparator(),
                                PHPExcel_Shared_String::getThousandsSeparator()
                            );
                            $value = preg_replace($number_regex, $value, $format);
                        } else {
                            if (preg_match('/[0#]E[+-]0/i', $format)) {
                                                                $value = sprintf('%5.2E', $value);
                            } elseif (preg_match('/0([^\d\.]+)0/', $format)) {
                                $value = self::complexNumberFormatMask($value, $format);
                            } else {
                                $sprintf_pattern = "%0$minWidth." . strlen($right) . "f";
                                $value = sprintf($sprintf_pattern, $value);
                                $value = preg_replace($number_regex, $value, $format);
                            }
                        }
                    }
                }
                if (preg_match('/\[\$(.*)\]/u', $format, $m)) {
                                        $currencyFormat = $m[0];
                    $currencyCode = $m[1];
                    list($currencyCode) = explode('-', $currencyCode);
                    if ($currencyCode == '') {
                        $currencyCode = PHPExcel_Shared_String::getCurrencyCode();
                    }
                    $value = preg_replace('/\[\$([^\]]*)\]/u', $currencyCode, $value);
                }
            }
        }

                $format = preg_replace("/\\\\/u", '\\', $format);

                if ($callBack !== null) {
            list($writerInstance, $function) = $callBack;
            $value = $writerInstance->$function($value, $formatColor);
        }

        return $value;
    }
}
