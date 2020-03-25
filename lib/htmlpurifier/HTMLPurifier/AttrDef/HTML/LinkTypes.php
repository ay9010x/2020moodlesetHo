<?php


class HTMLPurifier_AttrDef_HTML_LinkTypes extends HTMLPurifier_AttrDef
{

    
    protected $name;

    
    public function __construct($name)
    {
        $configLookup = array(
            'rel' => 'AllowedRel',
            'rev' => 'AllowedRev'
        );
        if (!isset($configLookup[$name])) {
            trigger_error(
                'Unrecognized attribute name for link ' .
                'relationship.',
                E_USER_ERROR
            );
            return;
        }
        $this->name = $configLookup[$name];
    }

    
    public function validate($string, $config, $context)
    {
        $allowed = $config->get('Attr.' . $this->name);
        if (empty($allowed)) {
            return false;
        }

        $string = $this->parseCDATA($string);
        $parts = explode(' ', $string);

                $ret_lookup = array();
        foreach ($parts as $part) {
            $part = strtolower(trim($part));
            if (!isset($allowed[$part])) {
                continue;
            }
            $ret_lookup[$part] = true;
        }

        if (empty($ret_lookup)) {
            return false;
        }
        $string = implode(' ', array_keys($ret_lookup));
        return $string;
    }
}

