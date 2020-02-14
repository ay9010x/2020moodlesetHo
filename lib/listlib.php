<?php




defined('MOODLE_INTERNAL') || die();


abstract class moodle_list {
    public $attributes;
    public $listitemclassname = 'list_item';

    
    public $items = array();

    
    public $type;

    
    public $parentitem = null;
    public $table;
    public $fieldnamesparent = 'parent';

    
    public $records = array();

    public $editable;

    
    public $childparent;

    public $page = 0;     public $firstitem = 1;
    public $lastitem = 999999;
    public $pagecount;
    public $paged = false;
    public $offset = 0;
    public $pageurl;
    public $pageparamname;

    
    public function __construct($type='ul', $attributes='', $editable = false, $pageurl=null, $page = 0, $pageparamname = 'page', $itemsperpage = 20) {
        global $PAGE;

        $this->editable = $editable;
        $this->attributes = $attributes;
        $this->type = $type;
        $this->page = $page;
        $this->pageparamname = $pageparamname;
        $this->itemsperpage = $itemsperpage;
        if ($pageurl === null) {
            $this->pageurl = new moodle_url($PAGE->url);
            $this->pageurl->params(array($this->pageparamname => $this->page));
        } else {
            $this->pageurl = $pageurl;
        }
    }

    
    public function to_html($indent=0, $extraargs=array()) {
        if (count($this->items)) {
            $tabs = str_repeat("\t", $indent);
            $first = true;
            $itemiter = 1;
            $lastitem = '';
            $html = '';

            foreach ($this->items as $item) {
                $last = (count($this->items) == $itemiter);
                if ($this->editable) {
                    $item->set_icon_html($first, $last, $lastitem);
                }
                if ($itemhtml = $item->to_html($indent+1, $extraargs)) {
                    $html .= "$tabs\t<li".((!empty($item->attributes))?(' '.$item->attributes):'').">";
                    $html .= $itemhtml;
                    $html .= "</li>\n";
                }
                $first = false;
                $lastitem = $item;
                $itemiter++;
            }
        } else {
            $html = '';
        }
        if ($html) {             $tabs = str_repeat("\t", $indent);
            $html = $tabs.'<'.$this->type.((!empty($this->attributes))?(' '.$this->attributes):'').">\n".$html;
            $html .= $tabs."</".$this->type.">\n";
        } else {
            $html ='';
        }
        return $html;
    }

    
    public function find_item($id, $suppresserror = false) {
        if (isset($this->items)) {
            foreach ($this->items as $key => $child) {
                if ($child->id == $id) {
                    return $this->items[$key];
                }
            }
            foreach (array_keys($this->items) as $key) {
                $thischild = $this->items[$key];
                $ref = $thischild->children->find_item($id, true);                if ($ref !== null) {
                    return $ref;
                }
            }
        }

        if (!$suppresserror) {
            print_error('listnoitem');
        }
        return null;
    }

    public function add_item($item) {
        $this->items[] = $item;
    }

    public function set_parent($parent) {
        $this->parentitem = $parent;
    }

    
    public function list_from_records($paged = false, $offset = 0) {
        $this->paged = $paged;
        $this->offset = $offset;
        $this->get_records();
        $records = $this->records;
        $page = $this->page;
        if (!empty($page)) {
            $this->firstitem = ($page - 1) * $this->itemsperpage;
            $this->lastitem = $this->firstitem + $this->itemsperpage - 1;
        }
        $itemiter = $offset;
                $this->childparent = array();
        foreach ($records as $record) {
            $this->childparent[$record->id] = $record->parent;
        }

                foreach ($records as $record) {
            if (array_key_exists($record->parent, $this->childparent)) {
                                                continue;
            }

            $inpage = $itemiter >= $this->firstitem && $itemiter <= $this->lastitem;

                                    if ($this->parentitem !== null) {
                $newattributes = $this->parentitem->attributes;
            } else {
                $newattributes = '';
            }

            $this->items[$itemiter] = new $this->listitemclassname($record, $this, $newattributes, $inpage);

            if ($inpage) {
                $this->items[$itemiter]->create_children($records, $this->childparent, $record->id);
            } else {
                                $this->paged = true;
            }

            $itemiter++;
        }
        return array($this->paged, $itemiter);
    }

    
    public abstract function get_records();

    
    public function display_page_numbers() {
        $html = '';
        $topcount = count($this->items);
        $this->pagecount = (integer) ceil(($topcount + $this->offset)/ QUESTION_PAGE_LENGTH );
        if (!empty($this->page) && ($this->paged)) {
            $html = "<div class=\"paging\">".get_string('page').":\n";
            foreach (range(1,$this->pagecount) as $currentpage) {
                if ($this->page == $currentpage) {
                    $html .= " $currentpage \n";
                }
                else {
                    $html .= "<a href=\"".$this->pageurl->out(true, array($this->pageparamname => $currentpage))."\">";
                    $html .= " $currentpage </a>\n";
                }
            }
            $html .= "</div>";
        }
        return $html;
    }

    
    public function get_items_peers($itemid) {
        $itemref = $this->find_item($itemid);
        $peerids = $itemref->parentlist->get_child_ids();
        return $peerids;
    }

    
    public function get_child_ids() {
        $childids = array();
        foreach ($this->items as $child) {
           $childids[] = $child->id;
        }
        return $childids;
    }

    
    public function move_item_up_down($direction, $id) {
        $peers = $this->get_items_peers($id);
        $itemkey = array_search($id, $peers);
        switch ($direction) {
            case 'down' :
                if (isset($peers[$itemkey+1])) {
                    $olditem = $peers[$itemkey+1];
                    $peers[$itemkey+1] = $id;
                    $peers[$itemkey] = $olditem;
                } else {
                    print_error('listcantmoveup');
                }
                break;

            case 'up' :
                if (isset($peers[$itemkey-1])) {
                    $olditem = $peers[$itemkey-1];
                    $peers[$itemkey-1] = $id;
                    $peers[$itemkey] = $olditem;
                } else {
                    print_error('listcantmovedown');
                }
                break;
        }
        $this->reorder_peers($peers);
    }

    public function reorder_peers($peers) {
        global $DB;
        foreach ($peers as $key => $peer) {
            $DB->set_field($this->table, "sortorder", $key, array("id"=>$peer));
        }
    }

    
    public function move_item_left($id) {
        global $DB;

        $item = $this->find_item($id);
        if (!isset($item->parentlist->parentitem->parentlist)) {
            print_error('listcantmoveleft');
        } else {
            $newpeers = $this->get_items_peers($item->parentlist->parentitem->id);
            if (isset($item->parentlist->parentitem->parentlist->parentitem)) {
                $newparent = $item->parentlist->parentitem->parentlist->parentitem->id;
            } else {
                $newparent = 0;             }
            $DB->set_field($this->table, "parent", $newparent, array("id"=>$item->id));
            $oldparentkey = array_search($item->parentlist->parentitem->id, $newpeers);
            $neworder = array_merge(array_slice($newpeers, 0, $oldparentkey+1), array($item->id), array_slice($newpeers, $oldparentkey+1));
            $this->reorder_peers($neworder);
        }
        return $item->parentlist->parentitem;
    }

    
    public function move_item_right($id) {
        global $DB;

        $peers = $this->get_items_peers($id);
        $itemkey = array_search($id, $peers);
        if (!isset($peers[$itemkey-1])) {
            print_error('listcantmoveright');
        } else {
            $DB->set_field($this->table, "parent", $peers[$itemkey-1], array("id"=>$peers[$itemkey]));
            $newparent = $this->find_item($peers[$itemkey-1]);
            if (isset($newparent->children)) {
                $newpeers = $newparent->children->get_child_ids();
            }
            if ($newpeers) {
                $newpeers[] = $peers[$itemkey];
                $this->reorder_peers($newpeers);
            }
        }
    }

    
    public function process_actions($left, $right, $moveup, $movedown) {
                if (!(array_key_exists($left, $this->records) || array_key_exists($right, $this->records) || array_key_exists($moveup, $this->records) || array_key_exists($movedown, $this->records))) {
            return false;
        }
        if (!empty($left)) {
            $oldparentitem = $this->move_item_left($left);
            if ($this->item_is_last_on_page($oldparentitem->id)) {
                                $this->page ++;
                $this->pageurl->params(array($this->pageparamname => $this->page));
            }
        } else if (!empty($right)) {
            $this->move_item_right($right);
            if ($this->item_is_first_on_page($right)) {
                                $this->page --;
                $this->pageurl->params(array($this->pageparamname => $this->page));
            }
        } else if (!empty($moveup)) {
            $this->move_item_up_down('up', $moveup);
            if ($this->item_is_first_on_page($moveup)) {
                                $this->page --;
                $this->pageurl->params(array($this->pageparamname => $this->page));
            }
        } else if (!empty($movedown)) {
            $this->move_item_up_down('down', $movedown);
            if ($this->item_is_last_on_page($movedown)) {
                                $this->page ++;
                $this->pageurl->params(array($this->pageparamname => $this->page));
            }
        } else {
            return false;
        }

        redirect($this->pageurl);
    }

    
    public function item_is_first_on_page($itemid) {
        return $this->page && isset($this->items[$this->firstitem]) &&
                $itemid == $this->items[$this->firstitem]->id;
    }

    
    public function item_is_last_on_page($itemid) {
        return $this->page && isset($this->items[$this->lastitem]) &&
                $itemid == $this->items[$this->lastitem]->id;
    }
}


abstract class list_item {
    
    public $id;

    
    public $name;

    
    public $item;
    public $fieldnamesname = 'name';
    public $attributes;
    public $display;
    public $icons = array();

    
    public $parentlist;

    
    public $children;

    
    public function __construct($item, $parent, $attributes = '', $display = true) {
        $this->item = $item;
        if (is_object($this->item)) {
            $this->id = $this->item->id;
            $this->name = $this->item->{$this->fieldnamesname};
        }
        $this->set_parent($parent);
        $this->attributes = $attributes;
        $parentlistclass = get_class($parent);
        $this->children = new $parentlistclass($parent->type, $parent->attributes, $parent->editable, $parent->pageurl, 0);
        $this->children->set_parent($this);
        $this->display = $display;
    }

    
    public function item_html($extraargs = array()) {
        if (is_string($this->item)) {
            $html = $this->item;
        } elseif (is_object($this->item)) {
                                    $html = join(', ', (array)$this->item);
        }
        return $html;
    }

    
    public function to_html($indent = 0, $extraargs = array()) {
        if (!$this->display) {
            return '';
        }
        $tabs = str_repeat("\t", $indent);

        if (isset($this->children)) {
            $childrenhtml = $this->children->to_html($indent+1, $extraargs);
        } else {
            $childrenhtml = '';
        }
        return $this->item_html($extraargs).'&nbsp;'.(join($this->icons, '')).(($childrenhtml !='')?("\n".$childrenhtml):'');
    }

    public function set_icon_html($first, $last, $lastitem) {
        global $CFG;
        $strmoveup = get_string('moveup');
        $strmovedown = get_string('movedown');
        $strmoveleft = get_string('maketoplevelitem', 'question');

        if (right_to_left()) {               $rightarrow = 'left';
            $leftarrow  = 'right';
        } else {
            $rightarrow = 'right';
            $leftarrow  = 'left';
        }

        if (isset($this->parentlist->parentitem)) {
            $parentitem = $this->parentlist->parentitem;
            if (isset($parentitem->parentlist->parentitem)) {
                $action = get_string('makechildof', 'question', $parentitem->parentlist->parentitem->name);
            } else {
                $action = $strmoveleft;
            }
            $url = new moodle_url($this->parentlist->pageurl, (array('sesskey'=>sesskey(), 'left'=>$this->id)));
            $this->icons['left'] = $this->image_icon($action, $url, $leftarrow);
        } else {
            $this->icons['left'] =  $this->image_spacer();
        }

        if (!$first) {
            $url = new moodle_url($this->parentlist->pageurl, (array('sesskey'=>sesskey(), 'moveup'=>$this->id)));
            $this->icons['up'] = $this->image_icon($strmoveup, $url, 'up');
        } else {
            $this->icons['up'] =  $this->image_spacer();
        }

        if (!$last) {
            $url = new moodle_url($this->parentlist->pageurl, (array('sesskey'=>sesskey(), 'movedown'=>$this->id)));
            $this->icons['down'] = $this->image_icon($strmovedown, $url, 'down');
        } else {
            $this->icons['down'] =  $this->image_spacer();
        }

        if (!empty($lastitem)) {
            $makechildof = get_string('makechildof', 'question', $lastitem->name);
            $url = new moodle_url($this->parentlist->pageurl, (array('sesskey'=>sesskey(), 'right'=>$this->id)));
            $this->icons['right'] = $this->image_icon($makechildof, $url, $rightarrow);
        } else {
            $this->icons['right'] =  $this->image_spacer();
        }
    }

    public function image_icon($action, $url, $icon) {
        global $OUTPUT;
        return '<a title="' . s($action) .'" href="'.$url.'">
                <img src="' . $OUTPUT->pix_url('t/'.$icon) . '" class="iconsmall" alt="' . s($action). '" /></a> ';
    }

    public function image_spacer() {
        global $OUTPUT;
        return '<img src="' . $OUTPUT->pix_url('spacer') . '" class="iconsmall" alt="" />';
    }

    
    public function create_children(&$records, &$children, $thisrecordid) {
                $thischildren = array_keys($children, $thisrecordid);
        foreach ($thischildren as $child) {
            $thisclass = get_class($this);
            $newlistitem = new $thisclass($records[$child], $this->children, $this->attributes);
            $this->children->add_item($newlistitem);
            $newlistitem->create_children($records, $children, $records[$child]->id);
        }
    }

    public function set_parent($parent) {
        $this->parentlist = $parent;
    }
}