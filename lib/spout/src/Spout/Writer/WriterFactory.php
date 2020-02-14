<?php

namespace Box\Spout\Writer;

use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Common\Type;


class WriterFactory
{
    
    public static function create($writerType)
    {
        $writer = null;

        switch ($writerType) {
            case Type::CSV:
                $writer = new CSV\Writer();
                break;
            case Type::XLSX:
                $writer = new XLSX\Writer();
                break;
            case Type::ODS:
                $writer = new ODS\Writer();
                break;
            default:
                throw new UnsupportedTypeException('No writers supporting the given type: ' . $writerType);
        }

        $writer->setGlobalFunctionsHelper(new GlobalFunctionsHelper());

        return $writer;
    }
}
