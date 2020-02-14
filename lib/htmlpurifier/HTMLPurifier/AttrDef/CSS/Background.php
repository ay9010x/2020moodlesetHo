<?php


class HTMLPurifier_AttrDef_CSS_Background extends HTMLPurifier_AttrDef
{

    
    protected $info;

    
    public function __construct($config)
    {
        $def = $config->getCSSDefinition();
        $this->info['background-color'] = $def->info['background-color'];
        $this->info['background-image'] = $def->info['background-image'];
        $this->info['background-repeat'] = $def->info['background-repeat'];
        $this->info['background-attachment'] = $def->info['background-attachment'];
        $this->info['background-position'] = $def->info['background-position'];
    }

    
    public function validate($string, $config, $context)
    {
                $string = $this->parseCDATA($string);
        if ($string === '') {
            return false;
        }

                $string = $this->mungeRgb($string);

                $bits = explode(' ', $string); 
        $caught = array();
        $caught['color'] = false;
        $caught['image'] = false;
        $caught['repeat'] = false;
        $caught['attachment'] = false;
        $caught['position'] = false;

        $i = 0; 
        foreach ($bits as $bit) {
            if ($bit === '') {
                continue;
            }
            foreach ($caught as $key => $status) {
                if ($key != 'position') {
                    if ($status !== false) {
                        continue;
                    }
                    $r = $this->info['background-' . $key]->validate($bit, $config, $context);
                } else {
                    $r = $bit;
                }
                if ($r === false) {
                    continue;
                }
                if ($key == 'position') {
                    if ($caught[$key] === false) {
                        $caught[$key] = '';
                    }
                    $caught[$key] .= $r . ' ';
                } else {
                    $caught[$key] = $r;
                }
                $i++;
                break;
            }
        }

        if (!$i) {
            return false;
        }
        if ($caught['position'] !== false) {
            $caught['position'] = $this->info['background-position']->
                validate($caught['position'], $config, $context);
        }

        $ret = array();
        foreach ($caught as $value) {
            if ($value === false) {
                continue;
            }
            $ret[] = $value;
        }

        if (empty($ret)) {
            return false;
        }
        return implode(' ', $ret);
    }
}

