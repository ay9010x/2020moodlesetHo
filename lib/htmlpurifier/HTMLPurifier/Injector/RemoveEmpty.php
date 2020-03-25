<?php

class HTMLPurifier_Injector_RemoveEmpty extends HTMLPurifier_Injector
{
    
    private $context;

    
    private $config;

    
    private $attrValidator;

    
    private $removeNbsp;

    
    private $removeNbspExceptions;

    
    private $exclude;

    
    public function prepare($config, $context)
    {
        parent::prepare($config, $context);
        $this->config = $config;
        $this->context = $context;
        $this->removeNbsp = $config->get('AutoFormat.RemoveEmpty.RemoveNbsp');
        $this->removeNbspExceptions = $config->get('AutoFormat.RemoveEmpty.RemoveNbsp.Exceptions');
        $this->exclude = $config->get('AutoFormat.RemoveEmpty.Predicate');
        $this->attrValidator = new HTMLPurifier_AttrValidator();
    }

    
    public function handleElement(&$token)
    {
        if (!$token instanceof HTMLPurifier_Token_Start) {
            return;
        }
        $next = false;
        $deleted = 1;         for ($i = count($this->inputZipper->back) - 1; $i >= 0; $i--, $deleted++) {
            $next = $this->inputZipper->back[$i];
            if ($next instanceof HTMLPurifier_Token_Text) {
                if ($next->is_whitespace) {
                    continue;
                }
                if ($this->removeNbsp && !isset($this->removeNbspExceptions[$token->name])) {
                    $plain = str_replace("\xC2\xA0", "", $next->data);
                    $isWsOrNbsp = $plain === '' || ctype_space($plain);
                    if ($isWsOrNbsp) {
                        continue;
                    }
                }
            }
            break;
        }
        if (!$next || ($next instanceof HTMLPurifier_Token_End && $next->name == $token->name)) {
            $this->attrValidator->validateToken($token, $this->config, $this->context);
            $token->armor['ValidateAttributes'] = true;
            if (isset($this->exclude[$token->name])) {
                $r = true;
                foreach ($this->exclude[$token->name] as $elem) {
                    if (!isset($token->attr[$elem])) $r = false;
                }
                if ($r) return;
            }
            if (isset($token->attr['id']) || isset($token->attr['name'])) {
                return;
            }
            $token = $deleted + 1;
            for ($b = 0, $c = count($this->inputZipper->front); $b < $c; $b++) {
                $prev = $this->inputZipper->front[$b];
                if ($prev instanceof HTMLPurifier_Token_Text && $prev->is_whitespace) {
                    continue;
                }
                break;
            }
                        $this->rewindOffset($b+$deleted);
            return;
        }
    }
}

