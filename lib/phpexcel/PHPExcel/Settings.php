<?php


if (!defined('PHPEXCEL_ROOT')) {
    
    define('PHPEXCEL_ROOT', dirname(__FILE__) . '/../');
    require(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
}


class PHPExcel_Settings
{
    
    
    const PCLZIP     = 'PHPExcel_Shared_ZipArchive';
    const ZIPARCHIVE = 'ZipArchive';

    
    const CHART_RENDERER_JPGRAPH = 'jpgraph';

    
    const PDF_RENDERER_TCPDF  = 'tcPDF';
    const PDF_RENDERER_DOMPDF = 'DomPDF';
    const PDF_RENDERER_MPDF   = 'mPDF';


    private static $chartRenderers = array(
        self::CHART_RENDERER_JPGRAPH,
    );

    private static $pdfRenderers = array(
        self::PDF_RENDERER_TCPDF,
        self::PDF_RENDERER_DOMPDF,
        self::PDF_RENDERER_MPDF,
    );


    
    private static $zipClass = self::ZIPARCHIVE;


    
    private static $chartRendererName;

    
    private static $chartRendererPath;


    
    private static $pdfRendererName;

    
    private static $pdfRendererPath;

    
    private static $libXmlLoaderOptions = null;

    
    public static function setZipClass($zipClass)
    {
        if (($zipClass === self::PCLZIP) ||
            ($zipClass === self::ZIPARCHIVE)) {
            self::$zipClass = $zipClass;
            return true;
        }
        return false;
    }


    
    public static function getZipClass()
    {
        return self::$zipClass;
    }


    
    public static function getCacheStorageMethod()
    {
        return PHPExcel_CachedObjectStorageFactory::getCacheStorageMethod();
    }


    
    public static function getCacheStorageClass()
    {
        return PHPExcel_CachedObjectStorageFactory::getCacheStorageClass();
    }


    
    public static function setCacheStorageMethod($method = PHPExcel_CachedObjectStorageFactory::cache_in_memory, $arguments = array())
    {
        return PHPExcel_CachedObjectStorageFactory::initialize($method, $arguments);
    }


    
    public static function setLocale($locale = 'en_us')
    {
        return PHPExcel_Calculation::getInstance()->setLocale($locale);
    }


    
    public static function setChartRenderer($libraryName, $libraryBaseDir)
    {
        if (!self::setChartRendererName($libraryName)) {
            return false;
        }
        return self::setChartRendererPath($libraryBaseDir);
    }


    
    public static function setChartRendererName($libraryName)
    {
        if (!in_array($libraryName, self::$chartRenderers)) {
            return false;
        }
        self::$chartRendererName = $libraryName;

        return true;
    }


    
    public static function setChartRendererPath($libraryBaseDir)
    {
        if ((file_exists($libraryBaseDir) === false) || (is_readable($libraryBaseDir) === false)) {
            return false;
        }
        self::$chartRendererPath = $libraryBaseDir;

        return true;
    }


    
    public static function getChartRendererName()
    {
        return self::$chartRendererName;
    }


    
    public static function getChartRendererPath()
    {
        return self::$chartRendererPath;
    }


    
    public static function setPdfRenderer($libraryName, $libraryBaseDir)
    {
        if (!self::setPdfRendererName($libraryName)) {
            return false;
        }
        return self::setPdfRendererPath($libraryBaseDir);
    }


    
    public static function setPdfRendererName($libraryName)
    {
        if (!in_array($libraryName, self::$pdfRenderers)) {
            return false;
        }
        self::$pdfRendererName = $libraryName;

        return true;
    }


    
    public static function setPdfRendererPath($libraryBaseDir)
    {
        if ((file_exists($libraryBaseDir) === false) || (is_readable($libraryBaseDir) === false)) {
            return false;
        }
        self::$pdfRendererPath = $libraryBaseDir;

        return true;
    }


    
    public static function getPdfRendererName()
    {
        return self::$pdfRendererName;
    }

    
    public static function getPdfRendererPath()
    {
        return self::$pdfRendererPath;
    }

    
    public static function setLibXmlLoaderOptions($options = null)
    {
        if (is_null($options) && defined(LIBXML_DTDLOAD)) {
            $options = LIBXML_DTDLOAD | LIBXML_DTDATTR;
        }
        if (version_compare(PHP_VERSION, '5.2.11') >= 0) {
            @libxml_disable_entity_loader($options == (LIBXML_DTDLOAD | LIBXML_DTDATTR));
        }
        self::$libXmlLoaderOptions = $options;
    }

    
    public static function getLibXmlLoaderOptions()
    {
        if (is_null(self::$libXmlLoaderOptions) && defined(LIBXML_DTDLOAD)) {
            self::setLibXmlLoaderOptions(LIBXML_DTDLOAD | LIBXML_DTDATTR);
        }
        if (version_compare(PHP_VERSION, '5.2.11') >= 0) {
            @libxml_disable_entity_loader(self::$libXmlLoaderOptions == (LIBXML_DTDLOAD | LIBXML_DTDATTR));
        }
        return self::$libXmlLoaderOptions;
    }
}
