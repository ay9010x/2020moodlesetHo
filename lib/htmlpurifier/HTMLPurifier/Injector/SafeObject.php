<?php


class HTMLPurifier_Injector_SafeObject extends HTMLPurifier_Injector
{
    
    public $name = 'SafeObject';

    
    public $needed = array('object', 'param');

    
    protected $objectStack = array();

    
    protected $paramStack = array();

    
    protected $addParam = array(
        'allowScriptAccess' => 'never',
        'allowNetworking' => 'internal',
    );

    
    protected $allowedParam = array(
        'wmode' => true,
        'movie' => true,
        'flashvars' => true,
        'src' => true,
        'allowFullScreen' => true,     );

    
    public function prepare($config, $context)
    {
        parent::prepare($config, $context);
    }

    
    public function handleElement(&$token)
    {
        if ($token->name == 'object') {
            $this->objectStack[] = $token;
            $this->paramStack[] = array();
            $new = array($token);
            foreach ($this->addParam as $name => $value) {
                $new[] = new HTMLPurifier_Token_Empty('param', array('name' => $name, 'value' => $value));
            }
            $token = $new;
        } elseif ($token->name == 'param') {
            $nest = count($this->currentNesting) - 1;
            if ($nest >= 0 && $this->currentNesting[$nest]->name === 'object') {
                $i = count($this->objectStack) - 1;
                if (!isset($token->attr['name'])) {
                    $token = false;
                    return;
                }
                $n = $token->attr['name'];
                                                                if (!isset($this->objectStack[$i]->attr['data']) &&
                    ($token->attr['name'] == 'movie' || $token->attr['name'] == 'src')
                ) {
                    $this->objectStack[$i]->attr['data'] = $token->attr['value'];
                }
                                                if (!isset($this->paramStack[$i][$n]) &&
                    isset($this->addParam[$n]) &&
                    $token->attr['name'] === $this->addParam[$n]) {
                                        $this->paramStack[$i][$n] = true;
                } elseif (isset($this->allowedParam[$n])) {
                                                        } else {
                    $token = false;
                }
            } else {
                                $token = false;
            }
        }
    }

    public function handleEnd(&$token)
    {
                                if ($token->name == 'object') {
            array_pop($this->objectStack);
            array_pop($this->paramStack);
        }
    }
}

