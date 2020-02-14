<?php


class PHPExcel_Writer_OpenDocument extends PHPExcel_Writer_Abstract implements PHPExcel_Writer_IWriter
{
    
    private $writerParts = array();

    
    private $spreadSheet;

    
    public function __construct(PHPExcel $pPHPExcel = null)
    {
        $this->setPHPExcel($pPHPExcel);

        $writerPartsArray = array(
            'content'    => 'PHPExcel_Writer_OpenDocument_Content',
            'meta'       => 'PHPExcel_Writer_OpenDocument_Meta',
            'meta_inf'   => 'PHPExcel_Writer_OpenDocument_MetaInf',
            'mimetype'   => 'PHPExcel_Writer_OpenDocument_Mimetype',
            'settings'   => 'PHPExcel_Writer_OpenDocument_Settings',
            'styles'     => 'PHPExcel_Writer_OpenDocument_Styles',
            'thumbnails' => 'PHPExcel_Writer_OpenDocument_Thumbnails'
        );

        foreach ($writerPartsArray as $writer => $class) {
            $this->writerParts[$writer] = new $class($this);
        }
    }

    
    public function getWriterPart($pPartName = '')
    {
        if ($pPartName != '' && isset($this->writerParts[strtolower($pPartName)])) {
            return $this->writerParts[strtolower($pPartName)];
        } else {
            return null;
        }
    }

    
    public function save($pFilename = null)
    {
        if (!$this->spreadSheet) {
            throw new PHPExcel_Writer_Exception('PHPExcel object unassigned.');
        }

                $this->spreadSheet->garbageCollect();

                $originalFilename = $pFilename;
        if (strtolower($pFilename) == 'php://output' || strtolower($pFilename) == 'php://stdout') {
            $pFilename = @tempnam(PHPExcel_Shared_File::sys_get_temp_dir(), 'phpxltmp');
            if ($pFilename == '') {
                $pFilename = $originalFilename;
            }
        }

        $objZip = $this->createZip($pFilename);

        $objZip->addFromString('META-INF/manifest.xml', $this->getWriterPart('meta_inf')->writeManifest());
        $objZip->addFromString('Thumbnails/thumbnail.png', $this->getWriterPart('thumbnails')->writeThumbnail());
        $objZip->addFromString('content.xml', $this->getWriterPart('content')->write());
        $objZip->addFromString('meta.xml', $this->getWriterPart('meta')->write());
        $objZip->addFromString('mimetype', $this->getWriterPart('mimetype')->write());
        $objZip->addFromString('settings.xml', $this->getWriterPart('settings')->write());
        $objZip->addFromString('styles.xml', $this->getWriterPart('styles')->write());

                if ($objZip->close() === false) {
            throw new PHPExcel_Writer_Exception("Could not close zip file $pFilename.");
        }

                if ($originalFilename != $pFilename) {
            if (copy($pFilename, $originalFilename) === false) {
                throw new PHPExcel_Writer_Exception("Could not copy temporary zip file $pFilename to $originalFilename.");
            }
            @unlink($pFilename);
        }
    }

    
    private function createZip($pFilename)
    {
                $zipClass = PHPExcel_Settings::getZipClass();
        $objZip = new $zipClass();

                        $ro = new ReflectionObject($objZip);
        $zipOverWrite = $ro->getConstant('OVERWRITE');
        $zipCreate = $ro->getConstant('CREATE');

        if (file_exists($pFilename)) {
            unlink($pFilename);
        }
                if ($objZip->open($pFilename, $zipOverWrite) !== true) {
            if ($objZip->open($pFilename, $zipCreate) !== true) {
                throw new PHPExcel_Writer_Exception("Could not open $pFilename for writing.");
            }
        }

        return $objZip;
    }

    
    public function getPHPExcel()
    {
        if ($this->spreadSheet !== null) {
            return $this->spreadSheet;
        } else {
            throw new PHPExcel_Writer_Exception('No PHPExcel assigned.');
        }
    }

    
    public function setPHPExcel(PHPExcel $pPHPExcel = null)
    {
        $this->spreadSheet = $pPHPExcel;
        return $this;
    }
}
