<?php

class Horde_Mime_Related implements IteratorAggregate
{
    
    protected $_cids = array();

    
    protected $_start;

    
    public function __construct(Horde_Mime_Part $mime_part)
    {
        if ($mime_part->getType() != 'multipart/related') {
            throw new InvalidArgumentException('MIME part must be of type multipart/related');
        }

        $ids = array_keys($mime_part->contentTypeMap());
        $related_id = $mime_part->getMimeId();
        $id = null;

        
        foreach ($ids as $val) {
            if ((strcmp($related_id, $val) !== 0) &&
                ($cid = $mime_part->getPart($val)->getContentId())) {
                $this->_cids[$val] = $cid;
            }
        }

        
        $start = $mime_part->getContentTypeParameter('start');
        if (!empty($start)) {
            $id = $this->cidSearch($start);
        }

        if (empty($id)) {
            reset($ids);
            $id = next($ids);
        }

        $this->_start = $id;
    }

    
    public function startId()
    {
        return $this->_start;
    }

    
    public function cidSearch($cid)
    {
        return array_search($cid, $this->_cids);
    }

    
    public function cidReplace($text, $callback, $charset = 'UTF-8')
    {
        $dom = ($text instanceof Horde_Domhtml)
            ? $text
            : new Horde_Domhtml($text, $charset);

        foreach ($dom as $node) {
            if ($node instanceof DOMElement) {
                switch (Horde_String::lower($node->tagName)) {
                case 'body':
                case 'td':
                    $this->_cidReplace($node, 'background', $callback);
                    break;

                case 'img':
                    $this->_cidReplace($node, 'src', $callback);
                    break;
                }
            }
        }

        return $dom;
    }

    
    protected function _cidReplace($node, $attribute, $callback)
    {
        if ($node->hasAttribute($attribute)) {
            $val = $node->getAttribute($attribute);
            if ((strpos($val, 'cid:') === 0) &&
                ($id = $this->cidSearch(substr($val, 4)))) {
                $node->setAttribute($attribute, call_user_func($callback, $id, $attribute, $node));
            }
        }
    }

    

    public function getIterator()
    {
        return new ArrayIterator($this->_cids);
    }

}
