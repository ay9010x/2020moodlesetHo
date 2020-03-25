<?php


class PHPExcel_RichText_Run extends PHPExcel_RichText_TextElement implements PHPExcel_RichText_ITextElement
{
    
    private $font;

    
    public function __construct($pText = '')
    {
                $this->setText($pText);
        $this->font = new PHPExcel_Style_Font();
    }

    
    public function getFont()
    {
        return $this->font;
    }

    
    public function setFont(PHPExcel_Style_Font $pFont = null)
    {
        $this->font = $pFont;
        return $this;
    }

    
    public function getHashCode()
    {
        return md5(
            $this->getText() .
            $this->font->getHashCode() .
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
