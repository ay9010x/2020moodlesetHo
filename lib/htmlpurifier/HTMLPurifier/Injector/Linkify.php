<?php


class HTMLPurifier_Injector_Linkify extends HTMLPurifier_Injector
{
    
    public $name = 'Linkify';

    
    public $needed = array('a' => array('href'));

    
    public function handleText(&$token)
    {
        if (!$this->allowsElement('a')) {
            return;
        }

        if (strpos($token->data, '://') === false) {
                                                return;
        }

                        $bits = preg_split('#((?:https?|ftp)://[^\s\'",<>()]+)#Su', $token->data, -1, PREG_SPLIT_DELIM_CAPTURE);


        $token = array();

                                for ($i = 0, $c = count($bits), $l = false; $i < $c; $i++, $l = !$l) {
            if (!$l) {
                if ($bits[$i] === '') {
                    continue;
                }
                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
            } else {
                $token[] = new HTMLPurifier_Token_Start('a', array('href' => $bits[$i]));
                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
                $token[] = new HTMLPurifier_Token_End('a');
            }
        }
    }
}

