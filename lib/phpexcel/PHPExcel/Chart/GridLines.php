<?php



class PHPExcel_Chart_GridLines extends PHPExcel_Chart_Properties
{

  

    private $objectState = false;

    private $lineProperties = array(
        'color' => array(
            'type' => self::EXCEL_COLOR_TYPE_STANDARD,
            'value' => null,
            'alpha' => 0
        ),
        'style' => array(
            'width' => '9525',
            'compound' => self::LINE_STYLE_COMPOUND_SIMPLE,
            'dash' => self::LINE_STYLE_DASH_SOLID,
            'cap' => self::LINE_STYLE_CAP_FLAT,
            'join' => self::LINE_STYLE_JOIN_BEVEL,
            'arrow' => array(
                'head' => array(
                    'type' => self::LINE_STYLE_ARROW_TYPE_NOARROW,
                    'size' => self::LINE_STYLE_ARROW_SIZE_5
                ),
                'end' => array(
                    'type' => self::LINE_STYLE_ARROW_TYPE_NOARROW,
                    'size' => self::LINE_STYLE_ARROW_SIZE_8
                ),
            )
        )
    );

    private $shadowProperties = array(
        'presets' => self::SHADOW_PRESETS_NOSHADOW,
        'effect' => null,
        'color' => array(
            'type' => self::EXCEL_COLOR_TYPE_STANDARD,
            'value' => 'black',
            'alpha' => 85,
        ),
        'size' => array(
            'sx' => null,
            'sy' => null,
            'kx' => null
        ),
        'blur' => null,
        'direction' => null,
        'distance' => null,
        'algn' => null,
        'rotWithShape' => null
    );

    private $glowProperties = array(
        'size' => null,
        'color' => array(
            'type' => self::EXCEL_COLOR_TYPE_STANDARD,
            'value' => 'black',
            'alpha' => 40
        )
    );

    private $softEdges = array(
        'size' => null
     );

    

    public function getObjectState()
    {
        return $this->objectState;
    }

    

    private function activateObject()
    {
        $this->objectState = true;

        return $this;
    }

    

    public function setLineColorProperties($value, $alpha = 0, $type = self::EXCEL_COLOR_TYPE_STANDARD)
    {
        $this->activateObject()
            ->lineProperties['color'] = $this->setColorProperties(
                $value,
                $alpha,
                $type
            );
    }

    

    public function setLineStyleProperties($line_width = null, $compound_type = null, $dash_type = null, $cap_type = null, $join_type = null, $head_arrow_type = null, $head_arrow_size = null, $end_arrow_type = null, $end_arrow_size = null)
    {
        $this->activateObject();
        (!is_null($line_width))
                ? $this->lineProperties['style']['width'] = $this->getExcelPointsWidth((float) $line_width)
                : null;
        (!is_null($compound_type))
                ? $this->lineProperties['style']['compound'] = (string) $compound_type
                : null;
        (!is_null($dash_type))
                ? $this->lineProperties['style']['dash'] = (string) $dash_type
                : null;
        (!is_null($cap_type))
                ? $this->lineProperties['style']['cap'] = (string) $cap_type
                : null;
        (!is_null($join_type))
                ? $this->lineProperties['style']['join'] = (string) $join_type
                : null;
        (!is_null($head_arrow_type))
                ? $this->lineProperties['style']['arrow']['head']['type'] = (string) $head_arrow_type
                : null;
        (!is_null($head_arrow_size))
                ? $this->lineProperties['style']['arrow']['head']['size'] = (string) $head_arrow_size
                : null;
        (!is_null($end_arrow_type))
                ? $this->lineProperties['style']['arrow']['end']['type'] = (string) $end_arrow_type
                : null;
        (!is_null($end_arrow_size))
                ? $this->lineProperties['style']['arrow']['end']['size'] = (string) $end_arrow_size
                : null;
    }

    

    public function getLineColorProperty($parameter)
    {
        return $this->lineProperties['color'][$parameter];
    }

    

    public function getLineStyleProperty($elements)
    {
        return $this->getArrayElementsValue($this->lineProperties['style'], $elements);
    }

    

    public function setGlowProperties($size, $color_value = null, $color_alpha = null, $color_type = null)
    {
        $this
                ->activateObject()
                ->setGlowSize($size)
                ->setGlowColor($color_value, $color_alpha, $color_type);
    }

    

    public function getGlowColor($property)
    {
        return $this->glowProperties['color'][$property];
    }

    

    public function getGlowSize()
    {
        return $this->glowProperties['size'];
    }

    

    private function setGlowSize($size)
    {
        $this->glowProperties['size'] = $this->getExcelPointsWidth((float) $size);

        return $this;
    }

    

    private function setGlowColor($color, $alpha, $type)
    {
        if (!is_null($color)) {
            $this->glowProperties['color']['value'] = (string) $color;
        }
        if (!is_null($alpha)) {
            $this->glowProperties['color']['alpha'] = $this->getTrueAlpha((int) $alpha);
        }
        if (!is_null($type)) {
            $this->glowProperties['color']['type'] = (string) $type;
        }

        return $this;
    }

    

    public function getLineStyleArrowParameters($arrow_selector, $property_selector)
    {
        return $this->getLineStyleArrowSize($this->lineProperties['style']['arrow'][$arrow_selector]['size'], $property_selector);
    }

    

    public function setShadowProperties($sh_presets, $sh_color_value = null, $sh_color_type = null, $sh_color_alpha = null, $sh_blur = null, $sh_angle = null, $sh_distance = null)
    {
        $this->activateObject()
            ->setShadowPresetsProperties((int) $sh_presets)
            ->setShadowColor(
                is_null($sh_color_value) ? $this->shadowProperties['color']['value'] : $sh_color_value,
                is_null($sh_color_alpha) ? (int) $this->shadowProperties['color']['alpha'] : $this->getTrueAlpha($sh_color_alpha),
                is_null($sh_color_type) ? $this->shadowProperties['color']['type'] : $sh_color_type
            )
            ->setShadowBlur($sh_blur)
            ->setShadowAngle($sh_angle)
            ->setShadowDistance($sh_distance);
    }

    

    private function setShadowPresetsProperties($shadow_presets)
    {
        $this->shadowProperties['presets'] = $shadow_presets;
        $this->setShadowProperiesMapValues($this->getShadowPresetsMap($shadow_presets));

        return $this;
    }

    

    private function setShadowProperiesMapValues(array $properties_map, &$reference = null)
    {
        $base_reference = $reference;
        foreach ($properties_map as $property_key => $property_val) {
            if (is_array($property_val)) {
                if ($reference === null) {
                    $reference = & $this->shadowProperties[$property_key];
                } else {
                    $reference = & $reference[$property_key];
                }
                $this->setShadowProperiesMapValues($property_val, $reference);
            } else {
                if ($base_reference === null) {
                    $this->shadowProperties[$property_key] = $property_val;
                } else {
                    $reference[$property_key] = $property_val;
                }
            }
        }

        return $this;
    }

    
    private function setShadowColor($color, $alpha, $type)
    {
        if (!is_null($color)) {
            $this->shadowProperties['color']['value'] = (string) $color;
        }
        if (!is_null($alpha)) {
            $this->shadowProperties['color']['alpha'] = $this->getTrueAlpha((int) $alpha);
        }
        if (!is_null($type)) {
            $this->shadowProperties['color']['type'] = (string) $type;
        }

        return $this;
    }

    
    private function setShadowBlur($blur)
    {
        if ($blur !== null) {
            $this->shadowProperties['blur'] = (string) $this->getExcelPointsWidth($blur);
        }

        return $this;
    }

    

    private function setShadowAngle($angle)
    {
        if ($angle !== null) {
            $this->shadowProperties['direction'] = (string) $this->getExcelPointsAngle($angle);
        }

        return $this;
    }

    
    private function setShadowDistance($distance)
    {
        if ($distance !== null) {
            $this->shadowProperties['distance'] = (string) $this->getExcelPointsWidth($distance);
        }

        return $this;
    }

    
    public function getShadowProperty($elements)
    {
        return $this->getArrayElementsValue($this->shadowProperties, $elements);
    }

    
    public function setSoftEdgesSize($size)
    {
        if (!is_null($size)) {
            $this->activateObject();
            $softEdges['size'] = (string) $this->getExcelPointsWidth($size);
        }
    }

    
    public function getSoftEdgesSize()
    {
        return $this->softEdges['size'];
    }
}
