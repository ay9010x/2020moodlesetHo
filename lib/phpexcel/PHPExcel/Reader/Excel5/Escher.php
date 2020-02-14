<?php


class PHPExcel_Reader_Excel5_Escher
{
    const DGGCONTAINER      = 0xF000;
    const BSTORECONTAINER   = 0xF001;
    const DGCONTAINER       = 0xF002;
    const SPGRCONTAINER     = 0xF003;
    const SPCONTAINER       = 0xF004;
    const DGG               = 0xF006;
    const BSE               = 0xF007;
    const DG                = 0xF008;
    const SPGR              = 0xF009;
    const SP                = 0xF00A;
    const OPT               = 0xF00B;
    const CLIENTTEXTBOX     = 0xF00D;
    const CLIENTANCHOR      = 0xF010;
    const CLIENTDATA        = 0xF011;
    const BLIPJPEG          = 0xF01D;
    const BLIPPNG           = 0xF01E;
    const SPLITMENUCOLORS   = 0xF11E;
    const TERTIARYOPT       = 0xF122;

    
    private $data;

    
    private $dataSize;

    
    private $pos;

    
    private $object;

    
    public function __construct($object)
    {
        $this->object = $object;
    }

    
    public function load($data)
    {
        $this->data = $data;

                $this->dataSize = strlen($this->data);

        $this->pos = 0;

                while ($this->pos < $this->dataSize) {
                        $fbt = PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos + 2);

            switch ($fbt) {
                case self::DGGCONTAINER:
                    $this->readDggContainer();
                    break;
                case self::DGG:
                    $this->readDgg();
                    break;
                case self::BSTORECONTAINER:
                    $this->readBstoreContainer();
                    break;
                case self::BSE:
                    $this->readBSE();
                    break;
                case self::BLIPJPEG:
                    $this->readBlipJPEG();
                    break;
                case self::BLIPPNG:
                    $this->readBlipPNG();
                    break;
                case self::OPT:
                    $this->readOPT();
                    break;
                case self::TERTIARYOPT:
                    $this->readTertiaryOPT();
                    break;
                case self::SPLITMENUCOLORS:
                    $this->readSplitMenuColors();
                    break;
                case self::DGCONTAINER:
                    $this->readDgContainer();
                    break;
                case self::DG:
                    $this->readDg();
                    break;
                case self::SPGRCONTAINER:
                    $this->readSpgrContainer();
                    break;
                case self::SPCONTAINER:
                    $this->readSpContainer();
                    break;
                case self::SPGR:
                    $this->readSpgr();
                    break;
                case self::SP:
                    $this->readSp();
                    break;
                case self::CLIENTTEXTBOX:
                    $this->readClientTextbox();
                    break;
                case self::CLIENTANCHOR:
                    $this->readClientAnchor();
                    break;
                case self::CLIENTDATA:
                    $this->readClientData();
                    break;
                default:
                    $this->readDefault();
                    break;
            }
        }

        return $this->object;
    }

    
    private function readDefault()
    {
                $verInstance = PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos);

                $fbt = PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos + 2);

                $recVer = (0x000F & $verInstance) >> 0;

        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readDggContainer()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

                $dggContainer = new PHPExcel_Shared_Escher_DggContainer();
        $this->object->setDggContainer($dggContainer);
        $reader = new PHPExcel_Reader_Excel5_Escher($dggContainer);
        $reader->load($recordData);
    }

    
    private function readDgg()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readBstoreContainer()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

                $bstoreContainer = new PHPExcel_Shared_Escher_DggContainer_BstoreContainer();
        $this->object->setBstoreContainer($bstoreContainer);
        $reader = new PHPExcel_Reader_Excel5_Escher($bstoreContainer);
        $reader->load($recordData);
    }

    
    private function readBSE()
    {
        
                $recInstance = (0xFFF0 & PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos)) >> 4;

        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

                $BSE = new PHPExcel_Shared_Escher_DggContainer_BstoreContainer_BSE();
        $this->object->addBSE($BSE);

        $BSE->setBLIPType($recInstance);

                $btWin32 = ord($recordData[0]);

                $btMacOS = ord($recordData[1]);

                $rgbUid = substr($recordData, 2, 16);

                $tag = PHPExcel_Reader_Excel5::getInt2d($recordData, 18);

                $size = PHPExcel_Reader_Excel5::getInt4d($recordData, 20);

                $cRef = PHPExcel_Reader_Excel5::getInt4d($recordData, 24);

                $foDelay = PHPExcel_Reader_Excel5::getInt4d($recordData, 28);

                $unused1 = ord($recordData{32});

                $cbName = ord($recordData{33});

                $unused2 = ord($recordData{34});

                $unused3 = ord($recordData{35});

                $nameData = substr($recordData, 36, $cbName);

                $blipData = substr($recordData, 36 + $cbName);

                $reader = new PHPExcel_Reader_Excel5_Escher($BSE);
        $reader->load($blipData);
    }

    
    private function readBlipJPEG()
    {
        
                $recInstance = (0xFFF0 & PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos)) >> 4;

        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

        $pos = 0;

                $rgbUid1 = substr($recordData, 0, 16);
        $pos += 16;

                if (in_array($recInstance, array(0x046B, 0x06E3))) {
            $rgbUid2 = substr($recordData, 16, 16);
            $pos += 16;
        }

                $tag = ord($recordData{$pos});
        $pos += 1;

                $data = substr($recordData, $pos);

        $blip = new PHPExcel_Shared_Escher_DggContainer_BstoreContainer_BSE_Blip();
        $blip->setData($data);

        $this->object->setBlip($blip);
    }

    
    private function readBlipPNG()
    {
        
                $recInstance = (0xFFF0 & PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos)) >> 4;

        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

        $pos = 0;

                $rgbUid1 = substr($recordData, 0, 16);
        $pos += 16;

                if ($recInstance == 0x06E1) {
            $rgbUid2 = substr($recordData, 16, 16);
            $pos += 16;
        }

                $tag = ord($recordData{$pos});
        $pos += 1;

                $data = substr($recordData, $pos);

        $blip = new PHPExcel_Shared_Escher_DggContainer_BstoreContainer_BSE_Blip();
        $blip->setData($data);

        $this->object->setBlip($blip);
    }

    
    private function readOPT()
    {
        
                $recInstance = (0xFFF0 & PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos)) >> 4;

        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

        $this->readOfficeArtRGFOPTE($recordData, $recInstance);
    }

    
    private function readTertiaryOPT()
    {
        
                $recInstance = (0xFFF0 & PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos)) >> 4;

        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readSplitMenuColors()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readDgContainer()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

                $dgContainer = new PHPExcel_Shared_Escher_DgContainer();
        $this->object->setDgContainer($dgContainer);
        $reader = new PHPExcel_Reader_Excel5_Escher($dgContainer);
        $escher = $reader->load($recordData);
    }

    
    private function readDg()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readSpgrContainer()
    {
        
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

                $spgrContainer = new PHPExcel_Shared_Escher_DgContainer_SpgrContainer();

        if ($this->object instanceof PHPExcel_Shared_Escher_DgContainer) {
                        $this->object->setSpgrContainer($spgrContainer);
        } else {
                        $this->object->addChild($spgrContainer);
        }

        $reader = new PHPExcel_Reader_Excel5_Escher($spgrContainer);
        $escher = $reader->load($recordData);
    }

    
    private function readSpContainer()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $spContainer = new PHPExcel_Shared_Escher_DgContainer_SpgrContainer_SpContainer();
        $this->object->addChild($spContainer);

                $this->pos += 8 + $length;

                $reader = new PHPExcel_Reader_Excel5_Escher($spContainer);
        $escher = $reader->load($recordData);
    }

    
    private function readSpgr()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readSp()
    {
        
                $recInstance = (0xFFF0 & PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos)) >> 4;

        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readClientTextbox()
    {
        
                $recInstance = (0xFFF0 & PHPExcel_Reader_Excel5::getInt2d($this->data, $this->pos)) >> 4;

        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readClientAnchor()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;

                $c1 = PHPExcel_Reader_Excel5::getInt2d($recordData, 2);

                $startOffsetX = PHPExcel_Reader_Excel5::getInt2d($recordData, 4);

                $r1 = PHPExcel_Reader_Excel5::getInt2d($recordData, 6);

                $startOffsetY = PHPExcel_Reader_Excel5::getInt2d($recordData, 8);

                $c2 = PHPExcel_Reader_Excel5::getInt2d($recordData, 10);

                $endOffsetX = PHPExcel_Reader_Excel5::getInt2d($recordData, 12);

                $r2 = PHPExcel_Reader_Excel5::getInt2d($recordData, 14);

                $endOffsetY = PHPExcel_Reader_Excel5::getInt2d($recordData, 16);

                $this->object->setStartCoordinates(PHPExcel_Cell::stringFromColumnIndex($c1) . ($r1 + 1));

                $this->object->setStartOffsetX($startOffsetX);

                $this->object->setStartOffsetY($startOffsetY);

                $this->object->setEndCoordinates(PHPExcel_Cell::stringFromColumnIndex($c2) . ($r2 + 1));

                $this->object->setEndOffsetX($endOffsetX);

                $this->object->setEndOffsetY($endOffsetY);
    }

    
    private function readClientData()
    {
        $length = PHPExcel_Reader_Excel5::getInt4d($this->data, $this->pos + 4);
        $recordData = substr($this->data, $this->pos + 8, $length);

                $this->pos += 8 + $length;
    }

    
    private function readOfficeArtRGFOPTE($data, $n)
    {
        $splicedComplexData = substr($data, 6 * $n);

                for ($i = 0; $i < $n; ++$i) {
                        $fopte = substr($data, 6 * $i, 6);

                        $opid = PHPExcel_Reader_Excel5::getInt2d($fopte, 0);

                        $opidOpid = (0x3FFF & $opid) >> 0;

                        $opidFBid = (0x4000 & $opid) >> 14;

                        $opidFComplex = (0x8000 & $opid) >> 15;

                        $op = PHPExcel_Reader_Excel5::getInt4d($fopte, 2);

            if ($opidFComplex) {
                $complexData = substr($splicedComplexData, 0, $op);
                $splicedComplexData = substr($splicedComplexData, $op);

                                $value = $complexData;
            } else {
                                $value = $op;
            }

            $this->object->setOPT($opidOpid, $value);
        }
    }
}
