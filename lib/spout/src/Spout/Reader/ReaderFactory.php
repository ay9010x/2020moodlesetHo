<?php

namespace Box\Spout\Reader;

use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Common\Type;


class ReaderFactory
{
    
    public static function create($readerType)
    {
        $reader = null;

        switch ($readerType) {
            case Type::CSV:
                $reader = new CSV\Reader();
                break;
            case Type::XLSX:
                $reader = new XLSX\Reader();
                break;
            case Type::ODS:
                $reader = new ODS\Reader();
                break;
            default:
                throw new UnsupportedTypeException('No readers supporting the given type: ' . $readerType);
        }

        $reader->setGlobalFunctionsHelper(new GlobalFunctionsHelper());

        return $reader;
    }
}
