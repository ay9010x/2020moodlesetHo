<?php



class Horde_Domhtml implements Iterator
{
    
    public $dom;

    
    protected $_iterator = null;

    
    protected $_origCharset;

    
    protected $_xmlencoding = '';

    
    public function __construct($text, $charset = null)
    {
        if (!extension_loaded('dom')) {
            throw new Exception('DOM extension is not available.');
        }

                if (!strlen($text)) {
            $text = '<html></html>';
        }

        $old_error = libxml_use_internal_errors(true);
        $this->dom = new DOMDocument();

        if (is_null($charset)) {
            
            $this->_loadHTML($text);
            $this->_origCharset = $this->dom->encoding
                ? $this->dom->encoding
                : 'iso-8859-1';
        } else {
            
            $this->_origCharset = Horde_String::lower($charset);
            $this->_xmlencoding = '<?xml encoding="UTF-8"?>';
            $this->_loadHTML(
                $this->_xmlencoding . Horde_String::convertCharset($text, $charset, 'UTF-8')
            );

            if ($this->dom->encoding &&
                (Horde_String::lower($this->dom->encoding) != 'utf-8')) {
                
                $this->_loadHTML(
                    Horde_String::convertCharset($text, $charset, $this->dom->encoding)
                );
                $this->_xmlencoding = '';
            }
        }

        if ($old_error) {
            libxml_use_internal_errors(false);
        }

        
        if (!$this->dom->documentElement) {
            $this->dom->appendChild($this->dom->createElement('html'));
        }

        
        $xpath = new DOMXPath($this->dom);
        $domlist = $xpath->query('/html/head/meta[@http-equiv="content-type"]');
        for ($i = $domlist->length; $i > 0; --$i) {
            $meta = $domlist->item($i - 1);
            $meta->parentNode->removeChild($meta);
        }
    }

    
    public function getHead()
    {
        $head = $this->dom->getElementsByTagName('head');
        if ($head->length) {
            return $head->item(0);
        }

        $headelt = $this->dom->createElement('head');
        $this->dom->documentElement->insertBefore($headelt, $this->dom->documentElement->firstChild);

        return $headelt;
    }

    
    public function getBody()
    {
        $body = $this->dom->getElementsByTagName('body');
        if ($body->length) {
            return $body->item(0);
        }

        $bodyelt = $this->dom->createElement('body');
        $this->dom->documentElement->appendChild($bodyelt);

        return $bodyelt;
    }

    
    public function returnHtml(array $opts = array())
    {
        $curr_charset = $this->getCharset();
        if (strcasecmp($curr_charset, 'US-ASCII') === 0) {
            $curr_charset = 'UTF-8';
        }
        $charset = array_key_exists('charset', $opts)
            ? (empty($opts['charset']) ? $curr_charset : $opts['charset'])
            : $this->_origCharset;

        if (empty($opts['metacharset'])) {
            $text = $this->dom->saveHTML();
        } else {
            
            $meta = $this->dom->createElement('meta');
            $meta->setAttribute('http-equiv', 'content-type');
            $meta->setAttribute('horde_dom_html_charset', '');

            $head = $this->getHead();
            $head->insertBefore($meta, $head->firstChild);

            $text = str_replace(
                'horde_dom_html_charset=""',
                'content="text/html; charset=' . $charset . '"',
                $this->dom->saveHTML()
            );

            $head->removeChild($meta);
        }

        if (strcasecmp($curr_charset, $charset) !== 0) {
            $text = Horde_String::convertCharset($text, $curr_charset, $charset);
        }

        if (!$this->_xmlencoding ||
            (($pos = strpos($text, $this->_xmlencoding)) === false)) {
            return $text;
        }

        return substr_replace($text, '', $pos, strlen($this->_xmlencoding));
    }

    
    public function returnBody()
    {
        $body = $this->getBody();
        $text = '';

        if ($body->hasChildNodes()) {
            foreach ($body->childNodes as $child) {
                $text .= $this->dom->saveXML($child);
            }
        }

        return Horde_String::convertCharset($text, 'UTF-8', $this->_origCharset);
    }

    
    public function getCharset()
    {
        return $this->dom->encoding
            ? $this->dom->encoding
            : ($this->_xmlencoding ? 'UTF-8' : $this->_origCharset);
    }

    
    protected function _loadHTML($html)
    {
        if (PHP_MINOR_VERSION >= 4) {
            $mask = defined('LIBXML_PARSEHUGE')
                ? LIBXML_PARSEHUGE
                : 0;
            $mask |= defined('LIBXML_COMPACT')
                ? LIBXML_COMPACT
                : 0;
            $this->dom->loadHTML($html, $mask);
        } else {
            $this->dom->loadHTML($html);
        }
    }

    

    
    public function current()
    {
        if ($this->_iterator instanceof DOMDocument) {
            return $this->_iterator;
        }

        $curr = end($this->_iterator);
        return $curr['list']->item($curr['i']);
    }

    
    public function key()
    {
        return 0;
    }

    
    public function next()
    {
        

        if ($this->_iterator instanceof DOMDocument) {
            $this->_iterator = array();
            $curr = array();
            $node = $this->dom;
        } elseif (empty($this->_iterator)) {
            $this->_iterator = null;
            return;
        } else {
            $curr = &$this->_iterator[count($this->_iterator) - 1];
            $node = $curr['list']->item($curr['i']);
        }

        if (empty($curr['child']) &&
            ($node instanceof DOMNode) &&
            $node->hasChildNodes()) {
            $curr['child'] = true;
            $this->_iterator[] = array(
                'child' => false,
                'i' => $node->childNodes->length - 1,
                'list' => $node->childNodes
            );
        } elseif (--$curr['i'] < 0) {
            array_pop($this->_iterator);
            $this->next();
        } else {
            $curr['child'] = false;
        }
    }

    
    public function rewind()
    {
        $this->_iterator = $this->dom;
    }

    
    public function valid()
    {
        return !is_null($this->_iterator);
    }

}
