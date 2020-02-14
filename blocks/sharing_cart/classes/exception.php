<?php

namespace sharing_cart;


class exception extends \moodle_exception
{
    
    public function __construct($errcode, $a = null)
    {
        parent::__construct($errcode, 'block_sharing_cart', '', $a);
    }
}
