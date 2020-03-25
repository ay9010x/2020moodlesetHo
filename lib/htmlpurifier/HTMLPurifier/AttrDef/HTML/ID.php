<?php



class HTMLPurifier_AttrDef_HTML_ID extends HTMLPurifier_AttrDef
{

        
    
    protected $selector;

    
    public function __construct($selector = false)
    {
        $this->selector = $selector;
    }

    
    public function validate($id, $config, $context)
    {
        if (!$this->selector && !$config->get('Attr.EnableID')) {
            return false;
        }

        $id = trim($id); 
        if ($id === '') {
            return false;
        }

        $prefix = $config->get('Attr.IDPrefix');
        if ($prefix !== '') {
            $prefix .= $config->get('Attr.IDPrefixLocal');
                        if (strpos($id, $prefix) !== 0) {
                $id = $prefix . $id;
            }
        } elseif ($config->get('Attr.IDPrefixLocal') !== '') {
            trigger_error(
                '%Attr.IDPrefixLocal cannot be used unless ' .
                '%Attr.IDPrefix is set',
                E_USER_WARNING
            );
        }

        if (!$this->selector) {
            $id_accumulator =& $context->get('IDAccumulator');
            if (isset($id_accumulator->ids[$id])) {
                return false;
            }
        }

        
        if (ctype_alpha($id)) {
            $result = true;
        } else {
            if (!ctype_alpha(@$id[0])) {
                return false;
            }
                        $trim = trim(
                $id,
                'A..Za..z0..9:-._'
            );
            $result = ($trim === '');
        }

        $regexp = $config->get('Attr.IDBlacklistRegexp');
        if ($regexp && preg_match($regexp, $id)) {
            return false;
        }

        if (!$this->selector && $result) {
            $id_accumulator->add($id);
        }

                                return $result ? $id : false;
    }
}

