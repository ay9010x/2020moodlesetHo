<?php


class HTMLPurifier_Injector_AutoParagraph extends HTMLPurifier_Injector
{
    
    public $name = 'AutoParagraph';

    
    public $needed = array('p');

    
    private function _pStart()
    {
        $par = new HTMLPurifier_Token_Start('p');
        $par->armor['MakeWellFormed_TagClosedError'] = true;
        return $par;
    }

    
    public function handleText(&$token)
    {
        $text = $token->data;
                if ($this->allowsElement('p')) {
            if (empty($this->currentNesting) || strpos($text, "\n\n") !== false) {
                                                                
                $i = $nesting = null;
                if (!$this->forwardUntilEndToken($i, $current, $nesting) && $token->is_whitespace) {
                                                                            } else {
                    if (!$token->is_whitespace || $this->_isInline($current)) {
                                                
                                                
                                                                        $token = array($this->_pStart());
                        $this->_splitText($text, $token);
                    } else {
                                                                    }
                }
            } else {
                                
                                                if ($this->_pLookAhead()) {
                                                                                                                                            $token = array($this->_pStart(), $token);
                } else {
                                        
                                                        }
            }
                    } elseif (!empty($this->currentNesting) &&
            $this->currentNesting[count($this->currentNesting) - 1]->name == 'p') {
                        
                                    $token = array();
            $this->_splitText($text, $token);
                    } else {
                        
                                }
    }

    
    public function handleElement(&$token)
    {
                        if ($this->allowsElement('p')) {
            if (!empty($this->currentNesting)) {
                if ($this->_isInline($token)) {
                                                                                                    $i = null;
                    $this->backward($i, $prev);

                    if (!$prev instanceof HTMLPurifier_Token_Start) {
                                                if ($prev instanceof HTMLPurifier_Token_Text &&
                            substr($prev->data, -2) === "\n\n"
                        ) {
                                                                                                                $token = array($this->_pStart(), $token);
                        } else {
                                                                                                                                                                                                }
                    } else {
                                                                                                if ($this->_pLookAhead()) {
                                                                                    $token = array($this->_pStart(), $token);
                        } else {
                                                        
                                                                                }
                    }
                } else {
                                                        }
            } else {
                if ($this->_isInline($token)) {
                                                                                                    $token = array($this->_pStart(), $token);
                } else {
                                                        }

                $i = null;
                if ($this->backward($i, $prev)) {
                    if (!$prev instanceof HTMLPurifier_Token_Text) {
                                                                                                                        if (!is_array($token)) {
                            $token = array($token);
                        }
                        array_unshift($token, new HTMLPurifier_Token_Text("\n\n"));
                    } else {
                                                                                                                                                                    }
                }
            }
        } else {
                                                        }
    }

    
    private function _splitText($data, &$result)
    {
        $raw_paragraphs = explode("\n\n", $data);
        $paragraphs = array();         $needs_start = false;
        $needs_end = false;

        $c = count($raw_paragraphs);
        if ($c == 1) {
                                    $result[] = new HTMLPurifier_Token_Text($data);
            return;
        }
        for ($i = 0; $i < $c; $i++) {
            $par = $raw_paragraphs[$i];
            if (trim($par) !== '') {
                $paragraphs[] = $par;
            } else {
                if ($i == 0) {
                                        if (empty($result)) {
                                                                                                                        $result[] = new HTMLPurifier_Token_End('p');
                        $result[] = new HTMLPurifier_Token_Text("\n\n");
                                                                                                                                                $needs_start = true;
                    } else {
                                                                                                array_unshift($result, new HTMLPurifier_Token_Text("\n\n"));
                    }
                } elseif ($i + 1 == $c) {
                                                            $needs_end = true;
                }
            }
        }

                        if (empty($paragraphs)) {
            return;
        }

                if ($needs_start) {
            $result[] = $this->_pStart();
        }

                foreach ($paragraphs as $par) {
            $result[] = new HTMLPurifier_Token_Text($par);
            $result[] = new HTMLPurifier_Token_End('p');
            $result[] = new HTMLPurifier_Token_Text("\n\n");
            $result[] = $this->_pStart();
        }

                                array_pop($result);

                        if (!$needs_end) {
            array_pop($result);             array_pop($result);         }
    }

    
    private function _isInline($token)
    {
        return isset($this->htmlDefinition->info['p']->child->elements[$token->name]);
    }

    
    private function _pLookAhead()
    {
        if ($this->currentToken instanceof HTMLPurifier_Token_Start) {
            $nesting = 1;
        } else {
            $nesting = 0;
        }
        $ok = false;
        $i = null;
        while ($this->forwardUntilEndToken($i, $current, $nesting)) {
            $result = $this->_checkNeedsP($current);
            if ($result !== null) {
                $ok = $result;
                break;
            }
        }
        return $ok;
    }

    
    private function _checkNeedsP($current)
    {
        if ($current instanceof HTMLPurifier_Token_Start) {
            if (!$this->_isInline($current)) {
                                                                return false;
            }
        } elseif ($current instanceof HTMLPurifier_Token_Text) {
            if (strpos($current->data, "\n\n") !== false) {
                                                return true;
            } else {
                                            }
        }
        return null;
    }
}

