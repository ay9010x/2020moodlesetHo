<?php


class HTMLPurifier_Node_Text extends HTMLPurifier_Node
{

    
    public $name = '#PCDATA';

    
    public $data;
    

    
    public $is_whitespace;

    

    
    public function __construct($data, $is_whitespace, $line = null, $col = null)
    {
        $this->data = $data;
        $this->is_whitespace = $is_whitespace;
        $this->line = $line;
        $this->col = $col;
    }

    public function toTokenPair() {
        return array(new HTMLPurifier_Token_Text($this->data, $this->line, $this->col), null);
    }
}

