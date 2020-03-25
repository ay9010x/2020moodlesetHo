<?php


class PHPExcel_Worksheet_MemoryDrawing extends PHPExcel_Worksheet_BaseDrawing implements PHPExcel_IComparable
{
    
    const RENDERING_DEFAULT = 'imagepng';
    const RENDERING_PNG     = 'imagepng';
    const RENDERING_GIF     = 'imagegif';
    const RENDERING_JPEG    = 'imagejpeg';

    
    const MIMETYPE_DEFAULT  = 'image/png';
    const MIMETYPE_PNG      = 'image/png';
    const MIMETYPE_GIF      = 'image/gif';
    const MIMETYPE_JPEG     = 'image/jpeg';

    
    private $imageResource;

    
    private $renderingFunction;

    
    private $mimeType;

    
    private $uniqueName;

    
    public function __construct()
    {
                $this->imageResource     = null;
        $this->renderingFunction = self::RENDERING_DEFAULT;
        $this->mimeType          = self::MIMETYPE_DEFAULT;
        $this->uniqueName        = md5(rand(0, 9999). time() . rand(0, 9999));

                parent::__construct();
    }

    
    public function getImageResource()
    {
        return $this->imageResource;
    }

    
    public function setImageResource($value = null)
    {
        $this->imageResource = $value;

        if (!is_null($this->imageResource)) {
                        $this->width  = imagesx($this->imageResource);
            $this->height = imagesy($this->imageResource);
        }
        return $this;
    }

    
    public function getRenderingFunction()
    {
        return $this->renderingFunction;
    }

    
    public function setRenderingFunction($value = PHPExcel_Worksheet_MemoryDrawing::RENDERING_DEFAULT)
    {
        $this->renderingFunction = $value;
        return $this;
    }

    
    public function getMimeType()
    {
        return $this->mimeType;
    }

    
    public function setMimeType($value = PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT)
    {
        $this->mimeType = $value;
        return $this;
    }

    
    public function getIndexedFilename()
    {
        $extension = strtolower($this->getMimeType());
        $extension = explode('/', $extension);
        $extension = $extension[1];

        return $this->uniqueName . $this->getImageIndex() . '.' . $extension;
    }

    
    public function getHashCode()
    {
        return md5(
            $this->renderingFunction .
            $this->mimeType .
            $this->uniqueName .
            parent::getHashCode() .
            __CLASS__
        );
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
