<?php




abstract class HTMLPurifier_Strategy
{

    
    abstract public function execute($tokens, $config, $context);
}

