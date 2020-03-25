<?php


class HTMLPurifier_ChildDef_List extends HTMLPurifier_ChildDef
{
    
    public $type = 'list';
    
            public $elements = array('li' => true, 'ul' => true, 'ol' => true);

    
    public function validateChildren($children, $config, $context)
    {
                $this->whitespace = false;

                if (empty($children)) {
            return false;
        }

                $result = array();

                $all_whitespace = true;

        $current_li = false;

        foreach ($children as $node) {
            if (!empty($node->is_whitespace)) {
                $result[] = $node;
                continue;
            }
            $all_whitespace = false; 
            if ($node->name === 'li') {
                                $current_li = $node;
                $result[] = $node;
            } else {
                                                                                                                                if ($current_li === false) {
                    $current_li = new HTMLPurifier_Node_Element('li');
                    $result[] = $current_li;
                }
                $current_li->children[] = $node;
                $current_li->empty = false;             }
        }
        if (empty($result)) {
            return false;
        }
        if ($all_whitespace) {
            return false;
        }
        return $result;
    }
}

