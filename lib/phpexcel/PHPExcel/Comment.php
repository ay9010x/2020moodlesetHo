<?php


class PHPExcel_Comment implements PHPExcel_IComparable
{
    
    private $author;

    
    private $text;

    
    private $width = '96pt';

    
    private $marginLeft = '59.25pt';

    
    private $marginTop = '1.5pt';

    
    private $visible = false;

    
    private $height = '55.5pt';

    
    private $fillColor;

    
    private $alignment;

    
    public function __construct()
    {
                $this->author    = 'Author';
        $this->text      = new PHPExcel_RichText();
        $this->fillColor = new PHPExcel_Style_Color('FFFFFFE1');
        $this->alignment = PHPExcel_Style_Alignment::HORIZONTAL_GENERAL;
    }

    
    public function getAuthor()
    {
        return $this->author;
    }

    
    public function setAuthor($pValue = '')
    {
        $this->author = $pValue;
        return $this;
    }

    
    public function getText()
    {
        return $this->text;
    }

    
    public function setText(PHPExcel_RichText $pValue)
    {
        $this->text = $pValue;
        return $this;
    }

    
    public function getWidth()
    {
        return $this->width;
    }

    
    public function setWidth($value = '96pt')
    {
        $this->width = $value;
        return $this;
    }

    
    public function getHeight()
    {
        return $this->height;
    }

    
    public function setHeight($value = '55.5pt')
    {
        $this->height = $value;
        return $this;
    }

    
    public function getMarginLeft()
    {
        return $this->marginLeft;
    }

    
    public function setMarginLeft($value = '59.25pt')
    {
        $this->marginLeft = $value;
        return $this;
    }

    
    public function getMarginTop()
    {
        return $this->marginTop;
    }

    
    public function setMarginTop($value = '1.5pt')
    {
        $this->marginTop = $value;
        return $this;
    }

    
    public function getVisible()
    {
        return $this->visible;
    }

    
    public function setVisible($value = false)
    {
        $this->visible = $value;
        return $this;
    }

    
    public function getFillColor()
    {
        return $this->fillColor;
    }

    
    public function setAlignment($pValue = PHPExcel_Style_Alignment::HORIZONTAL_GENERAL)
    {
        $this->alignment = $pValue;
        return $this;
    }

    
    public function getAlignment()
    {
        return $this->alignment;
    }

    
    public function getHashCode()
    {
        return md5(
            $this->author .
            $this->text->getHashCode() .
            $this->width .
            $this->height .
            $this->marginLeft .
            $this->marginTop .
            ($this->visible ? 1 : 0) .
            $this->fillColor->getHashCode() .
            $this->alignment .
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

    
    public function __toString()
    {
        return $this->text->getPlainText();
    }
}
