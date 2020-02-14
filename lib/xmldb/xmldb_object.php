<?php



defined('MOODLE_INTERNAL') || die();


class xmldb_object {

    
    protected $name;

    
    protected $comment;

    
    protected $previous;

    
    protected $next;

    
    protected $hash;

    
    protected $loaded;

    
    protected $changed;

    
    protected $errormsg;

    
    public function __construct($name) {
        $this->name = $name;
        $this->comment = null;
        $this->previous = null;
        $this->next = null;
        $this->hash = null;
        $this->loaded = false;
        $this->changed = false;
        $this->errormsg = null;
    }

    
    public function isLoaded() {
        return $this->loaded;
    }

    
    public function hasChanged() {
        return $this->changed;
    }

    
    public function getComment() {
        return $this->comment;
    }

    
    public function getHash() {
        return $this->hash;
    }

    
    public function getPrevious() {
        return $this->previous;
    }

    
    public function getNext() {
        return $this->next;
    }

    
    public function getName() {
        return $this->name;
    }

    
    public function getError() {
        return $this->errormsg;
    }

    
    public function setComment($comment) {
        $this->comment = $comment;
    }

    
    public function setPrevious($previous) {
        $this->previous = $previous;
    }

    
    public function setNext($next) {
        $this->next = $next;
    }

    
    public function setHash($hash) {
        $this->hash = $hash;
    }

    
    public function setLoaded($loaded = true) {
        $this->loaded = $loaded;
    }

    
    public function setChanged($changed = true) {
        $this->changed = $changed;
    }
    
    public function setName($name) {
        $this->name = $name;
    }


    
    public function checkName () {
        $result = true;

        if ($this->name != preg_replace('/[^a-z0-9_ -]/i', '', $this->name)) {
            $result = false;
        }
        return $result;
    }

    
    public function checkNameValues($arr) {
        $result = true;
        
                if ($arr) {
            foreach($arr as $element) {
                if (!$element->checkName()) {
                    $result = false;
                }
            }
        }
                if ($arr) {
            $existing_fields = array();
            foreach($arr as $element) {
                if (in_array($element->getName(), $existing_fields)) {
                    debugging('Object ' . $element->getName() . ' is duplicated!', DEBUG_DEVELOPER);
                    $result = false;
                }
                $existing_fields[] = $element->getName();
            }
        }
        return $result;
    }

    
    public function fixPrevNext(&$arr) {
        $tweaked = false;

        $prev = null;
        foreach ($arr as $key=>$el) {
            $prev_value = $arr[$key]->previous;
            $next_value = $arr[$key]->next;

            $arr[$key]->next     = null;
            $arr[$key]->previous = null;
            if ($prev !== null) {
                $arr[$prev]->next    = $arr[$key]->name;
                $arr[$key]->previous = $arr[$prev]->name;
            }
            $prev = $key;

            if ($prev_value != $arr[$key]->previous or $next_value != $arr[$key]->next) {
                $tweaked = true;
            }
        }

        return $tweaked;
    }

    
    public function orderElements($arr) {
        $result = true;

                $newarr = array();
        if (!empty($arr)) {
            $currentelement = null;
                        foreach($arr as $key => $element) {
                if (!$element->getPrevious()) {
                    $currentelement = $arr[$key];
                    $newarr[0] = $arr[$key];
                }
            }
            if (!$currentelement) {
                $result = false;
            }
                        $counter = 1;
            while ($result && $currentelement->getNext()) {
                $i = $this->findObjectInArray($currentelement->getNext(), $arr);
                $currentelement = $arr[$i];
                $newarr[$counter] = $arr[$i];
                $counter++;
            }
                        if ($result && count($arr) != count($newarr)) {
                $result = false;
            } else if ($newarr) {
                $result = $newarr;
            } else {
                $result = false;
            }
        } else {
            $result = array();
        }
        return $result;
    }

    
    public function findObjectInArray($objectname, $arr) {
        foreach ($arr as $i => $object) {
            if ($objectname == $object->getName()) {
                return $i;
            }
        }
        return null;
    }

    
    public function readableInfo() {
        return get_class($this);
    }

    
    public function debug($message) {

                $funcname = 'xmldb_debug';
                if (function_exists($funcname) && !defined('XMLDB_SKIP_DEBUG_HOOK')) {
            $funcname($message, $this);
        }
    }

    
    public function comma2array($string) {

        $foundquotes  = array();
        $foundconcats = array();

                preg_match_all("/(CONCAT\(.*?\))/is", $string, $matches);
        foreach (array_unique($matches[0]) as $key=>$value) {
            $foundconcats['<#'.$key.'#>'] = $value;
        }
        if (!empty($foundconcats)) {
            $string = str_replace($foundconcats,array_keys($foundconcats),$string);
        }

                        preg_match_all("/(''|'.*?[^\\\\]')/is", $string, $matches);
        foreach (array_unique($matches[0]) as $key=>$value) {
            $foundquotes['<%'.$key.'%>'] = $value;
        }
        if (!empty($foundquotes)) {
            $string = str_replace($foundquotes,array_keys($foundquotes),$string);
        }

                $arr = explode (',', $string);

                if ($arr) {
            foreach ($arr as $key => $element) {
                                $element = trim($element);
                                if (!empty($foundquotes)) {
                    $element = str_replace(array_keys($foundquotes), $foundquotes, $element);
                }
                                if (!empty($foundconcats)) {
                    $element = str_replace(array_keys($foundconcats), $foundconcats, $element);
                }
                                $arr[$key] = str_replace("\\'", "'", $element);
            }
        }

        return $arr;
    }

    
    public function validateDefinition(xmldb_table $xmldb_table=null) {
        return null;
    }
}
