<?php


class HTMLPurifier_AttrDef_CSS_ListStyle extends HTMLPurifier_AttrDef
{

    
    protected $info;

    
    public function __construct($config)
    {
        $def = $config->getCSSDefinition();
        $this->info['list-style-type'] = $def->info['list-style-type'];
        $this->info['list-style-position'] = $def->info['list-style-position'];
        $this->info['list-style-image'] = $def->info['list-style-image'];
    }

    
    public function validate($string, $config, $context)
    {
                $string = $this->parseCDATA($string);
        if ($string === '') {
            return false;
        }

                $bits = explode(' ', strtolower($string)); 
        $caught = array();
        $caught['type'] = false;
        $caught['position'] = false;
        $caught['image'] = false;

        $i = 0;         $none = false;

        foreach ($bits as $bit) {
            if ($i >= 3) {
                return;
            }             if ($bit === '') {
                continue;
            }
            foreach ($caught as $key => $status) {
                if ($status !== false) {
                    continue;
                }
                $r = $this->info['list-style-' . $key]->validate($bit, $config, $context);
                if ($r === false) {
                    continue;
                }
                if ($r === 'none') {
                    if ($none) {
                        continue;
                    } else {
                        $none = true;
                    }
                    if ($key == 'image') {
                        continue;
                    }
                }
                $caught[$key] = $r;
                $i++;
                break;
            }
        }

        if (!$i) {
            return false;
        }

        $ret = array();

                if ($caught['type']) {
            $ret[] = $caught['type'];
        }

                if ($caught['image']) {
            $ret[] = $caught['image'];
        }

                if ($caught['position']) {
            $ret[] = $caught['position'];
        }

        if (empty($ret)) {
            return false;
        }
        return implode(' ', $ret);
    }
}

