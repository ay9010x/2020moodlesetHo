<?php

namespace Box\Spout\Writer\Style;


class StyleBuilder
{
    
    protected $style;

    
    public function __construct()
    {
        $this->style = new Style();
    }

    
    public function setFontBold()
    {
        $this->style->setFontBold();
        return $this;
    }

    
    public function setFontItalic()
    {
        $this->style->setFontItalic();
        return $this;
    }

    
    public function setFontUnderline()
    {
        $this->style->setFontUnderline();
        return $this;
    }

    
    public function setFontStrikethrough()
    {
        $this->style->setFontStrikethrough();
        return $this;
    }

    
    public function setFontSize($fontSize)
    {
        $this->style->setFontSize($fontSize);
        return $this;
    }

    
    public function setFontColor($fontColor)
    {
        $this->style->setFontColor($fontColor);
        return $this;
    }

    
    public function setFontName($fontName)
    {
        $this->style->setFontName($fontName);
        return $this;
    }

    
    public function setShouldWrapText()
    {
        $this->style->setShouldWrapText();
        return $this;
    }

    
    public function build()
    {
        return $this->style;
    }
}
