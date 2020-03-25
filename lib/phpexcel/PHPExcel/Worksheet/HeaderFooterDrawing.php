<?php


class PHPExcel_Worksheet_HeaderFooterDrawing extends PHPExcel_Worksheet_Drawing implements PHPExcel_IComparable
{
    
    private $path;

    
    protected $name;

    
    protected $offsetX;

    
    protected $offsetY;

    
    protected $width;

    
    protected $height;

    
    protected $resizeProportional;

    
    public function __construct()
    {
                $this->path                = '';
        $this->name                = '';
        $this->offsetX             = 0;
        $this->offsetY             = 0;
        $this->width               = 0;
        $this->height              = 0;
        $this->resizeProportional  = true;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function setName($pValue = '')
    {
        $this->name = $pValue;
        return $this;
    }

    
    public function getOffsetX()
    {
        return $this->offsetX;
    }

    
    public function setOffsetX($pValue = 0)
    {
        $this->offsetX = $pValue;
        return $this;
    }

    
    public function getOffsetY()
    {
        return $this->offsetY;
    }

    
    public function setOffsetY($pValue = 0)
    {
        $this->offsetY = $pValue;
        return $this;
    }

    
    public function getWidth()
    {
        return $this->width;
    }

    
    public function setWidth($pValue = 0)
    {
                if ($this->resizeProportional && $pValue != 0) {
            $ratio = $this->width / $this->height;
            $this->height = round($ratio * $pValue);
        }

                $this->width = $pValue;

        return $this;
    }

    
    public function getHeight()
    {
        return $this->height;
    }

    
    public function setHeight($pValue = 0)
    {
                if ($this->resizeProportional && $pValue != 0) {
            $ratio = $this->width / $this->height;
            $this->width = round($ratio * $pValue);
        }

                $this->height = $pValue;

        return $this;
    }

    
    public function setWidthAndHeight($width = 0, $height = 0)
    {
        $xratio = $width / $this->width;
        $yratio = $height / $this->height;
        if ($this->resizeProportional && !($width == 0 || $height == 0)) {
            if (($xratio * $this->height) < $height) {
                $this->height = ceil($xratio * $this->height);
                $this->width  = $width;
            } else {
                $this->width    = ceil($yratio * $this->width);
                $this->height    = $height;
            }
        }
        return $this;
    }

    
    public function getResizeProportional()
    {
        return $this->resizeProportional;
    }

    
    public function setResizeProportional($pValue = true)
    {
        $this->resizeProportional = $pValue;
        return $this;
    }

    
    public function getFilename()
    {
        return basename($this->path);
    }

    
    public function getExtension()
    {
        $parts = explode(".", basename($this->path));
        return end($parts);
    }

    
    public function getPath()
    {
        return $this->path;
    }

    
    public function setPath($pValue = '', $pVerifyFile = true)
    {
        if ($pVerifyFile) {
            if (file_exists($pValue)) {
                $this->path = $pValue;

                if ($this->width == 0 && $this->height == 0) {
                                        list($this->width, $this->height) = getimagesize($pValue);
                }
            } else {
                throw new PHPExcel_Exception("File $pValue not found!");
            }
        } else {
            $this->path = $pValue;
        }
        return $this;
    }

    
    public function getHashCode()
    {
        return md5(
            $this->path .
            $this->name .
            $this->offsetX .
            $this->offsetY .
            $this->width .
            $this->height .
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
