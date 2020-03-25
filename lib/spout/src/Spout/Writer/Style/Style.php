<?php

namespace Box\Spout\Writer\Style;


class Style
{
    
    const DEFAULT_FONT_SIZE = 11;
    const DEFAULT_FONT_COLOR = Color::BLACK;
    const DEFAULT_FONT_NAME = 'Arial';

    
    protected $id = null;

    
    protected $fontBold = false;
    
    protected $hasSetFontBold = false;

    
    protected $fontItalic = false;
    
    protected $hasSetFontItalic = false;

    
    protected $fontUnderline = false;
    
    protected $hasSetFontUnderline = false;

    
    protected $fontStrikethrough = false;
    
    protected $hasSetFontStrikethrough = false;

    
    protected $fontSize = self::DEFAULT_FONT_SIZE;
    
    protected $hasSetFontSize = false;

    
    protected $fontColor = self::DEFAULT_FONT_COLOR;
    
    protected $hasSetFontColor = false;

    
    protected $fontName = self::DEFAULT_FONT_NAME;
    
    protected $hasSetFontName = false;

    
    protected $shouldApplyFont = false;

    
    protected $shouldWrapText = false;
    
    protected $hasSetWrapText = false;

    
    public function getId()
    {
        return $this->id;
    }

    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    
    public function isFontBold()
    {
        return $this->fontBold;
    }

    
    public function setFontBold()
    {
        $this->fontBold = true;
        $this->hasSetFontBold = true;
        $this->shouldApplyFont = true;
        return $this;
    }

    
    public function isFontItalic()
    {
        return $this->fontItalic;
    }

    
    public function setFontItalic()
    {
        $this->fontItalic = true;
        $this->hasSetFontItalic = true;
        $this->shouldApplyFont = true;
        return $this;
    }

    
    public function isFontUnderline()
    {
        return $this->fontUnderline;
    }

    
    public function setFontUnderline()
    {
        $this->fontUnderline = true;
        $this->hasSetFontUnderline = true;
        $this->shouldApplyFont = true;
        return $this;
    }

    
    public function isFontStrikethrough()
    {
        return $this->fontStrikethrough;
    }

    
    public function setFontStrikethrough()
    {
        $this->fontStrikethrough = true;
        $this->hasSetFontStrikethrough = true;
        $this->shouldApplyFont = true;
        return $this;
    }

    
    public function getFontSize()
    {
        return $this->fontSize;
    }

    
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;
        $this->hasSetFontSize = true;
        $this->shouldApplyFont = true;
        return $this;
    }

    
    public function getFontColor()
    {
        return $this->fontColor;
    }

    
    public function setFontColor($fontColor)
    {
        $this->fontColor = $fontColor;
        $this->hasSetFontColor = true;
        $this->shouldApplyFont = true;
        return $this;
    }

    
    public function getFontName()
    {
        return $this->fontName;
    }

    
    public function setFontName($fontName)
    {
        $this->fontName = $fontName;
        $this->hasSetFontName = true;
        $this->shouldApplyFont = true;
        return $this;
    }

    
    public function shouldWrapText()
    {
        return $this->shouldWrapText;
    }

    
    public function setShouldWrapText()
    {
        $this->shouldWrapText = true;
        $this->hasSetWrapText = true;
        return $this;
    }

    
    public function shouldApplyFont()
    {
        return $this->shouldApplyFont;
    }

    
    public function serialize()
    {
                $currentId = $this->id;
        $this->setId(0);

        $serializedStyle = serialize($this);

        $this->setId($currentId);

        return $serializedStyle;
    }

    
    public function mergeWith($baseStyle)
    {
        $mergedStyle = clone $this;

        if (!$this->hasSetFontBold && $baseStyle->isFontBold()) {
            $mergedStyle->setFontBold();
        }
        if (!$this->hasSetFontItalic && $baseStyle->isFontItalic()) {
            $mergedStyle->setFontItalic();
        }
        if (!$this->hasSetFontUnderline && $baseStyle->isFontUnderline()) {
            $mergedStyle->setFontUnderline();
        }
        if (!$this->hasSetFontStrikethrough && $baseStyle->isFontStrikethrough()) {
            $mergedStyle->setFontStrikethrough();
        }
        if (!$this->hasSetFontSize && $baseStyle->getFontSize() !== self::DEFAULT_FONT_SIZE) {
            $mergedStyle->setFontSize($baseStyle->getFontSize());
        }
        if (!$this->hasSetFontColor && $baseStyle->getFontColor() !== self::DEFAULT_FONT_COLOR) {
            $mergedStyle->setFontColor($baseStyle->getFontColor());
        }
        if (!$this->hasSetFontName && $baseStyle->getFontName() !== self::DEFAULT_FONT_NAME) {
            $mergedStyle->setFontName($baseStyle->getFontName());
        }
        if (!$this->hasSetWrapText && $baseStyle->shouldWrapText()) {
            $mergedStyle->setShouldWrapText();
        }

        return $mergedStyle;
    }
}
