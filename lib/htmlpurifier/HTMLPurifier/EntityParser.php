<?php



class HTMLPurifier_EntityParser
{

    
    protected $_entity_lookup;

    
    protected $_substituteEntitiesRegex =
        '/&(?:[#]x([a-fA-F0-9]+)|[#]0*(\d+)|([A-Za-z_:][A-Za-z0-9.\-_:]*));?/';
        
    
    protected $_special_dec2str =
            array(
                    34 => '"',
                    38 => '&',
                    39 => "'",
                    60 => '<',
                    62 => '>'
            );

    
    protected $_special_ent2dec =
            array(
                    'quot' => 34,
                    'amp'  => 38,
                    'lt'   => 60,
                    'gt'   => 62
            );

    
    public function substituteNonSpecialEntities($string)
    {
                return preg_replace_callback(
            $this->_substituteEntitiesRegex,
            array($this, 'nonSpecialEntityCallback'),
            $string
        );
    }

    

    protected function nonSpecialEntityCallback($matches)
    {
                $entity = $matches[0];
        $is_num = (@$matches[0][1] === '#');
        if ($is_num) {
            $is_hex = (@$entity[2] === 'x');
            $code = $is_hex ? hexdec($matches[1]) : (int) $matches[2];
                        if (isset($this->_special_dec2str[$code])) {
                return $entity;
            }
            return HTMLPurifier_Encoder::unichr($code);
        } else {
            if (isset($this->_special_ent2dec[$matches[3]])) {
                return $entity;
            }
            if (!$this->_entity_lookup) {
                $this->_entity_lookup = HTMLPurifier_EntityLookup::instance();
            }
            if (isset($this->_entity_lookup->table[$matches[3]])) {
                return $this->_entity_lookup->table[$matches[3]];
            } else {
                return $entity;
            }
        }
    }

    
    public function substituteSpecialEntities($string)
    {
        return preg_replace_callback(
            $this->_substituteEntitiesRegex,
            array($this, 'specialEntityCallback'),
            $string
        );
    }

    
    protected function specialEntityCallback($matches)
    {
        $entity = $matches[0];
        $is_num = (@$matches[0][1] === '#');
        if ($is_num) {
            $is_hex = (@$entity[2] === 'x');
            $int = $is_hex ? hexdec($matches[1]) : (int) $matches[2];
            return isset($this->_special_dec2str[$int]) ?
                $this->_special_dec2str[$int] :
                $entity;
        } else {
            return isset($this->_special_ent2dec[$matches[3]]) ?
                $this->_special_ent2dec[$matches[3]] :
                $entity;
        }
    }
}

