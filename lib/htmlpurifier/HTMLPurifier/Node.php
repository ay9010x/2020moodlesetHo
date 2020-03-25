<?php


abstract class HTMLPurifier_Node
{
    
    public $line;

    
    public $col;

    
    public $armor = array();

    
    public $dead = false;

    
    abstract public function toTokenPair();
}

