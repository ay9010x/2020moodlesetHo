<?php



class HTMLPurifier_Lexer_DOMLex extends HTMLPurifier_Lexer
{

    
    private $factory;

    public function __construct()
    {
                parent::__construct();
        $this->factory = new HTMLPurifier_TokenFactory();
    }

    
    public function tokenizeHTML($html, $config, $context)
    {
        $html = $this->normalize($html, $config, $context);

                        if ($config->get('Core.AggressivelyFixLt')) {
            $char = '[^a-z!\/]';
            $comment = "/<!--(.*?)(-->|\z)/is";
            $html = preg_replace_callback($comment, array($this, 'callbackArmorCommentEntities'), $html);
            do {
                $old = $html;
                $html = preg_replace("/<($char)/i", '&lt;\\1', $html);
            } while ($html !== $old);
            $html = preg_replace_callback($comment, array($this, 'callbackUndoCommentSubst'), $html);         }

                $html = $this->wrapHTML($html, $config, $context);

        $doc = new DOMDocument();
        $doc->encoding = 'UTF-8'; 
        set_error_handler(array($this, 'muteErrorHandler'));
        $doc->loadHTML($html);
        restore_error_handler();

        $tokens = array();
        $this->tokenizeDOM(
            $doc->getElementsByTagName('html')->item(0)->             getElementsByTagName('body')->item(0),             $tokens
        );
        return $tokens;
    }

    
    protected function tokenizeDOM($node, &$tokens)
    {
        $level = 0;
        $nodes = array($level => new HTMLPurifier_Queue(array($node)));
        $closingNodes = array();
        do {
            while (!$nodes[$level]->isEmpty()) {
                $node = $nodes[$level]->shift();                 $collect = $level > 0 ? true : false;
                $needEndingTag = $this->createStartNode($node, $tokens, $collect);
                if ($needEndingTag) {
                    $closingNodes[$level][] = $node;
                }
                if ($node->childNodes && $node->childNodes->length) {
                    $level++;
                    $nodes[$level] = new HTMLPurifier_Queue();
                    foreach ($node->childNodes as $childNode) {
                        $nodes[$level]->push($childNode);
                    }
                }
            }
            $level--;
            if ($level && isset($closingNodes[$level])) {
                while ($node = array_pop($closingNodes[$level])) {
                    $this->createEndNode($node, $tokens);
                }
            }
        } while ($level > 0);
    }

    
    protected function createStartNode($node, &$tokens, $collect)
    {
                                if ($node->nodeType === XML_TEXT_NODE) {
            $tokens[] = $this->factory->createText($node->data);
            return false;
        } elseif ($node->nodeType === XML_CDATA_SECTION_NODE) {
                        $last = end($tokens);
            $data = $node->data;
                        if ($last instanceof HTMLPurifier_Token_Start && ($last->name == 'script' || $last->name == 'style')) {
                $new_data = trim($data);
                if (substr($new_data, 0, 4) === '<!--') {
                    $data = substr($new_data, 4);
                    if (substr($data, -3) === '-->') {
                        $data = substr($data, 0, -3);
                    } else {
                                            }
                }
            }
            $tokens[] = $this->factory->createText($this->parseData($data));
            return false;
        } elseif ($node->nodeType === XML_COMMENT_NODE) {
                                                $tokens[] = $this->factory->createComment($node->data);
            return false;
        } elseif ($node->nodeType !== XML_ELEMENT_NODE) {
                        return false;
        }

        $attr = $node->hasAttributes() ? $this->transformAttrToAssoc($node->attributes) : array();

                if (!$node->childNodes->length) {
            if ($collect) {
                $tokens[] = $this->factory->createEmpty($node->tagName, $attr);
            }
            return false;
        } else {
            if ($collect) {
                $tokens[] = $this->factory->createStart(
                    $tag_name = $node->tagName,                     $attr
                );
            }
            return true;
        }
    }

    
    protected function createEndNode($node, &$tokens)
    {
        $tokens[] = $this->factory->createEnd($node->tagName);
    }


    
    protected function transformAttrToAssoc($node_map)
    {
                                if ($node_map->length === 0) {
            return array();
        }
        $array = array();
        foreach ($node_map as $attr) {
            $array[$attr->name] = $attr->value;
        }
        return $array;
    }

    
    public function muteErrorHandler($errno, $errstr)
    {
    }

    
    public function callbackUndoCommentSubst($matches)
    {
        return '<!--' . strtr($matches[1], array('&amp;' => '&', '&lt;' => '<')) . $matches[2];
    }

    
    public function callbackArmorCommentEntities($matches)
    {
        return '<!--' . str_replace('&', '&amp;', $matches[1]) . $matches[2];
    }

    
    protected function wrapHTML($html, $config, $context)
    {
        $def = $config->getDefinition('HTML');
        $ret = '';

        if (!empty($def->doctype->dtdPublic) || !empty($def->doctype->dtdSystem)) {
            $ret .= '<!DOCTYPE html ';
            if (!empty($def->doctype->dtdPublic)) {
                $ret .= 'PUBLIC "' . $def->doctype->dtdPublic . '" ';
            }
            if (!empty($def->doctype->dtdSystem)) {
                $ret .= '"' . $def->doctype->dtdSystem . '" ';
            }
            $ret .= '>';
        }

        $ret .= '<html><head>';
        $ret .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                $ret .= '</head><body>' . $html . '</body></html>';
        return $ret;
    }
}

