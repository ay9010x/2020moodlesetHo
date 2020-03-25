<?php


class HTMLPurifier_Token_Empty extends HTMLPurifier_Token_Tag
{
    public function toNode() {
        $n = parent::toNode();
        $n->empty = true;
        return $n;
    }
}

