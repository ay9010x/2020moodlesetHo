<?php


class HTMLPurifier_AttrDef_URI_Host extends HTMLPurifier_AttrDef
{

    
    protected $ipv4;

    
    protected $ipv6;

    public function __construct()
    {
        $this->ipv4 = new HTMLPurifier_AttrDef_URI_IPv4();
        $this->ipv6 = new HTMLPurifier_AttrDef_URI_IPv6();
    }

    
    public function validate($string, $config, $context)
    {
        $length = strlen($string);
                                                        if ($string === '') {
            return '';
        }
        if ($length > 1 && $string[0] === '[' && $string[$length - 1] === ']') {
                        $ip = substr($string, 1, $length - 2);
            $valid = $this->ipv6->validate($ip, $config, $context);
            if ($valid === false) {
                return false;
            }
            return '[' . $valid . ']';
        }

                $ipv4 = $this->ipv4->validate($string, $config, $context);
        if ($ipv4 !== false) {
            return $ipv4;
        }

        
                
                                                                                                $underscore = $config->get('Core.AllowHostnameUnderscore') ? '_' : '';

                $a   = '[a-z]';             $an  = '[a-z0-9]';          $and = "[a-z0-9-$underscore]";                 $domainlabel = "$an($and*$an)?";
                $toplabel = "$a($and*$an)?";
                if (preg_match("/^($domainlabel\.)*$toplabel\.?$/i", $string)) {
            return $string;
        }

                        
        if ($config->get('Core.EnableIDNA')) {
            $idna = new Net_IDNA2(array('encoding' => 'utf8', 'overlong' => false, 'strict' => true));
                        $parts = explode('.', $string);
            try {
                $new_parts = array();
                foreach ($parts as $part) {
                    $encodable = false;
                    for ($i = 0, $c = strlen($part); $i < $c; $i++) {
                        if (ord($part[$i]) > 0x7a) {
                            $encodable = true;
                            break;
                        }
                    }
                    if (!$encodable) {
                        $new_parts[] = $part;
                    } else {
                        $new_parts[] = $idna->encode($part);
                    }
                }
                $string = implode('.', $new_parts);
                if (preg_match("/^($domainlabel\.)*$toplabel\.?$/i", $string)) {
                    return $string;
                }
            } catch (Exception $e) {
                            }
        }
        return false;
    }
}

