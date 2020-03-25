<?php


class HTMLPurifier_Injector_RemoveSpansWithoutAttributes extends HTMLPurifier_Injector
{
    
    public $name = 'RemoveSpansWithoutAttributes';

    
    public $needed = array('span');

    
    private $attrValidator;

    
    private $config;

    
    private $context;

    public function prepare($config, $context)
    {
        $this->attrValidator = new HTMLPurifier_AttrValidator();
        $this->config = $config;
        $this->context = $context;
        return parent::prepare($config, $context);
    }

    
    public function handleElement(&$token)
    {
        if ($token->name !== 'span' || !$token instanceof HTMLPurifier_Token_Start) {
            return;
        }

                                $this->attrValidator->validateToken($token, $this->config, $this->context);
        $token->armor['ValidateAttributes'] = true;

        if (!empty($token->attr)) {
            return;
        }

        $nesting = 0;
        while ($this->forwardUntilEndToken($i, $current, $nesting)) {
        }

        if ($current instanceof HTMLPurifier_Token_End && $current->name === 'span') {
                        $current->markForDeletion = true;
                        $token = false;
        }
    }

    
    public function handleEnd(&$token)
    {
        if ($token->markForDeletion) {
            $token = false;
        }
    }
}

