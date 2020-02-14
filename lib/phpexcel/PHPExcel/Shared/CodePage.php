<?php


class PHPExcel_Shared_CodePage
{
    
    public static function NumberToName($codePage = 1252)
    {
        switch ($codePage) {
            case 367:
                return 'ASCII';                case 437:
                return 'CP437';                case 720:
                throw new PHPExcel_Exception('Code page 720 not supported.');                case 737:
                return 'CP737';                case 775:
                return 'CP775';                case 850:
                return 'CP850';                case 852:
                return 'CP852';                case 855:
                return 'CP855';                case 857:
                return 'CP857';                case 858:
                return 'CP858';                case 860:
                return 'CP860';                case 861:
                return 'CP861';                case 862:
                return 'CP862';                case 863:
                return 'CP863';                case 864:
                return 'CP864';                case 865:
                return 'CP865';                case 866:
                return 'CP866';                case 869:
                return 'CP869';                case 874:
                return 'CP874';                case 932:
                return 'CP932';                case 936:
                return 'CP936';                case 949:
                return 'CP949';                case 950:
                return 'CP950';                case 1200:
                return 'UTF-16LE';             case 1250:
                return 'CP1250';               case 1251:
                return 'CP1251';               case 0:
                            case 1252:
                return 'CP1252';               case 1253:
                return 'CP1253';               case 1254:
                return 'CP1254';               case 1255:
                return 'CP1255';               case 1256:
                return 'CP1256';               case 1257:
                return 'CP1257';               case 1258:
                return 'CP1258';               case 1361:
                return 'CP1361';               case 10000:
                return 'MAC';                  case 10001:
                return 'CP932';                case 10002:
                return 'CP950';                case 10003:
                return 'CP1361';               case 10006:
                return 'MACGREEK';              case 10007:
                return 'MACCYRILLIC';              case 10008:
                return 'CP936';              case 10029:
                return 'MACCENTRALEUROPE';              case 10079:
                return 'MACICELAND';              case 10081:
                return 'MACTURKISH';              case 21010:
                return 'UTF-16LE';              case 32768:
                return 'MAC';                  case 32769:
                throw new PHPExcel_Exception('Code page 32769 not supported.');              case 65000:
                return 'UTF-7';                case 65001:
                return 'UTF-8';            }
        throw new PHPExcel_Exception('Unknown codepage: ' . $codePage);
    }
}
