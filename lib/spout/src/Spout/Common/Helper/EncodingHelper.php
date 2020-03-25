<?php

namespace Box\Spout\Common\Helper;

use Box\Spout\Common\Exception\EncodingConversionException;


class EncodingHelper
{
    
    const ENCODING_UTF8     = 'UTF-8';
    const ENCODING_UTF16_LE = 'UTF-16LE';
    const ENCODING_UTF16_BE = 'UTF-16BE';
    const ENCODING_UTF32_LE = 'UTF-32LE';
    const ENCODING_UTF32_BE = 'UTF-32BE';

    
    const BOM_UTF8     = "\xEF\xBB\xBF";
    const BOM_UTF16_LE = "\xFF\xFE";
    const BOM_UTF16_BE = "\xFE\xFF";
    const BOM_UTF32_LE = "\xFF\xFE\x00\x00";
    const BOM_UTF32_BE = "\x00\x00\xFE\xFF";

    
    protected $globalFunctionsHelper;

    
    protected $supportedEncodingsWithBom;

    
    public function __construct($globalFunctionsHelper)
    {
        $this->globalFunctionsHelper = $globalFunctionsHelper;

        $this->supportedEncodingsWithBom = [
            self::ENCODING_UTF8     => self::BOM_UTF8,
            self::ENCODING_UTF16_LE => self::BOM_UTF16_LE,
            self::ENCODING_UTF16_BE => self::BOM_UTF16_BE,
            self::ENCODING_UTF32_LE => self::BOM_UTF32_LE,
            self::ENCODING_UTF32_BE => self::BOM_UTF32_BE,
        ];
    }

    
    public function getBytesOffsetToSkipBOM($filePointer, $encoding)
    {
        $byteOffsetToSkipBom = 0;

        if ($this->hasBom($filePointer, $encoding)) {
            $bomUsed = $this->supportedEncodingsWithBom[$encoding];

                        $byteOffsetToSkipBom = strlen($bomUsed);
        }

        return $byteOffsetToSkipBom;
    }

    
    protected function hasBOM($filePointer, $encoding)
    {
        $hasBOM = false;

        $this->globalFunctionsHelper->rewind($filePointer);

        if (array_key_exists($encoding, $this->supportedEncodingsWithBom)) {
            $potentialBom = $this->supportedEncodingsWithBom[$encoding];
            $numBytesInBom = strlen($potentialBom);

            $hasBOM = ($this->globalFunctionsHelper->fgets($filePointer, $numBytesInBom + 1) === $potentialBom);
        }

        return $hasBOM;
    }

    
    public function attemptConversionToUTF8($string, $sourceEncoding)
    {
        return $this->attemptConversion($string, $sourceEncoding, self::ENCODING_UTF8);
    }

    
    public function attemptConversionFromUTF8($string, $targetEncoding)
    {
        return $this->attemptConversion($string, self::ENCODING_UTF8, $targetEncoding);
    }

    
    protected function attemptConversion($string, $sourceEncoding, $targetEncoding)
    {
                if ($sourceEncoding === $targetEncoding) {
            return $string;
        }

        $convertedString = null;

        if ($this->canUseIconv()) {
            $convertedString = $this->globalFunctionsHelper->iconv($string, $sourceEncoding, $targetEncoding);
        } else if ($this->canUseMbString()) {
            $convertedString = $this->globalFunctionsHelper->mb_convert_encoding($string, $sourceEncoding, $targetEncoding);
        } else {
            throw new EncodingConversionException("The conversion from $sourceEncoding to $targetEncoding is not supported. Please install \"iconv\" or \"PHP Intl\".");
        }

        if ($convertedString === false) {
            throw new EncodingConversionException("The conversion from $sourceEncoding to $targetEncoding failed.");
        }

        return $convertedString;
    }

    
    protected function canUseIconv()
    {
        return $this->globalFunctionsHelper->function_exists('iconv');
    }

    
    protected function canUseMbString()
    {
        return $this->globalFunctionsHelper->function_exists('mb_convert_encoding');
    }
}
