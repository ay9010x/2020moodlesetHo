<?php


class HTMLPurifier_ChildDef_Table extends HTMLPurifier_ChildDef
{
    
    public $allow_empty = false;

    
    public $type = 'table';

    
    public $elements = array(
        'tr' => true,
        'tbody' => true,
        'thead' => true,
        'tfoot' => true,
        'caption' => true,
        'colgroup' => true,
        'col' => true
    );

    public function __construct()
    {
    }

    
    public function validateChildren($children, $config, $context)
    {
        if (empty($children)) {
            return false;
        }

                $caption = false;
        $thead = false;
        $tfoot = false;

                $initial_ws = array();
        $after_caption_ws = array();
        $after_thead_ws = array();
        $after_tfoot_ws = array();

                $cols = array();
        $content = array();

        $tbody_mode = false;                              
        $ws_accum =& $initial_ws;

        foreach ($children as $node) {
            if ($node instanceof HTMLPurifier_Node_Comment) {
                $ws_accum[] = $node;
                continue;
            }
            switch ($node->name) {
            case 'tbody':
                $tbody_mode = true;
                            case 'tr':
                $content[] = $node;
                $ws_accum =& $content;
                break;
            case 'caption':
                                if ($caption !== false)  break;
                $caption = $node;
                $ws_accum =& $after_caption_ws;
                break;
            case 'thead':
                $tbody_mode = true;
                                                                                                                if ($thead === false) {
                    $thead = $node;
                    $ws_accum =& $after_thead_ws;
                } else {
                                                                                                                                                                                                        $node->name = 'tbody';
                    $content[] = $node;
                    $ws_accum =& $content;
                }
                break;
            case 'tfoot':
                                $tbody_mode = true;
                if ($tfoot === false) {
                    $tfoot = $node;
                    $ws_accum =& $after_tfoot_ws;
                } else {
                    $node->name = 'tbody';
                    $content[] = $node;
                    $ws_accum =& $content;
                }
                break;
            case 'colgroup':
            case 'col':
                $cols[] = $node;
                $ws_accum =& $cols;
                break;
            case '#PCDATA':
                                                                                if (!empty($node->is_whitespace)) {
                    $ws_accum[] = $node;
                }
                break;
            }
        }

        if (empty($content)) {
            return false;
        }

        $ret = $initial_ws;
        if ($caption !== false) {
            $ret[] = $caption;
            $ret = array_merge($ret, $after_caption_ws);
        }
        if ($cols !== false) {
            $ret = array_merge($ret, $cols);
        }
        if ($thead !== false) {
            $ret[] = $thead;
            $ret = array_merge($ret, $after_thead_ws);
        }
        if ($tfoot !== false) {
            $ret[] = $tfoot;
            $ret = array_merge($ret, $after_tfoot_ws);
        }

        if ($tbody_mode) {
                        $current_tr_tbody = null;

            foreach($content as $node) {
                switch ($node->name) {
                case 'tbody':
                    $current_tr_tbody = null;
                    $ret[] = $node;
                    break;
                case 'tr':
                    if ($current_tr_tbody === null) {
                        $current_tr_tbody = new HTMLPurifier_Node_Element('tbody');
                        $ret[] = $current_tr_tbody;
                    }
                    $current_tr_tbody->children[] = $node;
                    break;
                case '#PCDATA':
                    assert($node->is_whitespace);
                    if ($current_tr_tbody === null) {
                        $ret[] = $node;
                    } else {
                        $current_tr_tbody->children[] = $node;
                    }
                    break;
                }
            }
        } else {
            $ret = array_merge($ret, $content);
        }

        return $ret;

    }
}

