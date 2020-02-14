<?php


class HTMLPurifier_Strategy_MakeWellFormed extends HTMLPurifier_Strategy
{

    
    protected $tokens;

    
    protected $token;

    
    protected $zipper;

    
    protected $stack;

    
    protected $injectors;

    
    protected $config;

    
    protected $context;

    
    public function execute($tokens, $config, $context)
    {
        $definition = $config->getHTMLDefinition();

                $generator = new HTMLPurifier_Generator($config, $context);
        $escape_invalid_tags = $config->get('Core.EscapeInvalidTags');
                $global_parent_allowed_elements = $definition->info_parent_def->child->getAllowedElements($config);
        $e = $context->get('ErrorCollector', true);
        $i = false;         list($zipper, $token) = HTMLPurifier_Zipper::fromArray($tokens);
        if ($token === NULL) {
            return array();
        }
        $reprocess = false;         $stack = array();

                $this->stack =& $stack;
        $this->tokens =& $tokens;
        $this->token =& $token;
        $this->zipper =& $zipper;
        $this->config = $config;
        $this->context = $context;

                $context->register('CurrentNesting', $stack);
        $context->register('InputZipper', $zipper);
        $context->register('CurrentToken', $token);

        
        $this->injectors = array();

        $injectors = $config->getBatch('AutoFormat');
        $def_injectors = $definition->info_injector;
        $custom_injectors = $injectors['Custom'];
        unset($injectors['Custom']);         foreach ($injectors as $injector => $b) {
                        if (strpos($injector, '.') !== false) {
                continue;
            }
            $injector = "HTMLPurifier_Injector_$injector";
            if (!$b) {
                continue;
            }
            $this->injectors[] = new $injector;
        }
        foreach ($def_injectors as $injector) {
                        $this->injectors[] = $injector;
        }
        foreach ($custom_injectors as $injector) {
            if (!$injector) {
                continue;
            }
            if (is_string($injector)) {
                $injector = "HTMLPurifier_Injector_$injector";
                $injector = new $injector;
            }
            $this->injectors[] = $injector;
        }

                        foreach ($this->injectors as $ix => $injector) {
            $error = $injector->prepare($config, $context);
            if (!$error) {
                continue;
            }
            array_splice($this->injectors, $ix, 1);             trigger_error("Cannot enable {$injector->name} injector because $error is not allowed", E_USER_WARNING);
        }

        
                                                        
                for (;;
                          $reprocess ? $reprocess = false : $token = $zipper->next($token)) {

                        if (is_int($i)) {
                                                                $rewind_offset = $this->injectors[$i]->getRewindOffset();
                if (is_int($rewind_offset)) {
                    for ($j = 0; $j < $rewind_offset; $j++) {
                        if (empty($zipper->front)) break;
                        $token = $zipper->prev($token);
                                                                        unset($token->skip[$i]);
                        $token->rewind = $i;
                        if ($token instanceof HTMLPurifier_Token_Start) {
                            array_pop($this->stack);
                        } elseif ($token instanceof HTMLPurifier_Token_End) {
                            $this->stack[] = $token->start;
                        }
                    }
                }
                $i = false;
            }

                        if ($token === NULL) {
                                if (empty($this->stack)) {
                    break;
                }

                                $top_nesting = array_pop($this->stack);
                $this->stack[] = $top_nesting;

                                if ($e && !isset($top_nesting->armor['MakeWellFormed_TagClosedError'])) {
                    $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by document end', $top_nesting);
                }

                                $token = new HTMLPurifier_Token_End($top_nesting->name);

                                $reprocess = true;
                continue;
            }

                        
                        if (empty($token->is_tag)) {
                if ($token instanceof HTMLPurifier_Token_Text) {
                    foreach ($this->injectors as $i => $injector) {
                        if (isset($token->skip[$i])) {
                            continue;
                        }
                        if ($token->rewind !== null && $token->rewind !== $i) {
                            continue;
                        }
                                                $r = $token;
                        $injector->handleText($r);
                        $token = $this->processToken($r, $i);
                        $reprocess = true;
                        break;
                    }
                }
                                continue;
            }

            if (isset($definition->info[$token->name])) {
                $type = $definition->info[$token->name]->child->type;
            } else {
                $type = false;             }

                        $ok = false;
            if ($type === 'empty' && $token instanceof HTMLPurifier_Token_Start) {
                                $token = new HTMLPurifier_Token_Empty(
                    $token->name,
                    $token->attr,
                    $token->line,
                    $token->col,
                    $token->armor
                );
                $ok = true;
            } elseif ($type && $type !== 'empty' && $token instanceof HTMLPurifier_Token_Empty) {
                                                $old_token = $token;
                $token = new HTMLPurifier_Token_End($token->name);
                $token = $this->insertBefore(
                    new HTMLPurifier_Token_Start($old_token->name, $old_token->attr, $old_token->line, $old_token->col, $old_token->armor)
                );
                                $reprocess = true;
                continue;
            } elseif ($token instanceof HTMLPurifier_Token_Empty) {
                                $ok = true;
            } elseif ($token instanceof HTMLPurifier_Token_Start) {
                
                                if (!empty($this->stack)) {

                                                                                                                                                                                                                                                
                    $parent = array_pop($this->stack);
                    $this->stack[] = $parent;

                    $parent_def = null;
                    $parent_elements = null;
                    $autoclose = false;
                    if (isset($definition->info[$parent->name])) {
                        $parent_def = $definition->info[$parent->name];
                        $parent_elements = $parent_def->child->getAllowedElements($config);
                        $autoclose = !isset($parent_elements[$token->name]);
                    }

                    if ($autoclose && $definition->info[$token->name]->wrap) {
                                                                                                $wrapname = $definition->info[$token->name]->wrap;
                        $wrapdef = $definition->info[$wrapname];
                        $elements = $wrapdef->child->getAllowedElements($config);
                        if (isset($elements[$token->name]) && isset($parent_elements[$wrapname])) {
                            $newtoken = new HTMLPurifier_Token_Start($wrapname);
                            $token = $this->insertBefore($newtoken);
                            $reprocess = true;
                            continue;
                        }
                    }

                    $carryover = false;
                    if ($autoclose && $parent_def->formatting) {
                        $carryover = true;
                    }

                    if ($autoclose) {
                                                                        $autoclose_ok = isset($global_parent_allowed_elements[$token->name]);
                        if (!$autoclose_ok) {
                            foreach ($this->stack as $ancestor) {
                                $elements = $definition->info[$ancestor->name]->child->getAllowedElements($config);
                                if (isset($elements[$token->name])) {
                                    $autoclose_ok = true;
                                    break;
                                }
                                if ($definition->info[$token->name]->wrap) {
                                    $wrapname = $definition->info[$token->name]->wrap;
                                    $wrapdef = $definition->info[$wrapname];
                                    $wrap_elements = $wrapdef->child->getAllowedElements($config);
                                    if (isset($wrap_elements[$token->name]) && isset($elements[$wrapname])) {
                                        $autoclose_ok = true;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($autoclose_ok) {
                                                        $new_token = new HTMLPurifier_Token_End($parent->name);
                            $new_token->start = $parent;
                                                        if ($e && !isset($parent->armor['MakeWellFormed_TagClosedError'])) {
                                if (!$carryover) {
                                    $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag auto closed', $parent);
                                } else {
                                    $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag carryover', $parent);
                                }
                            }
                            if ($carryover) {
                                $element = clone $parent;
                                                                $element->armor['MakeWellFormed_TagClosedError'] = true;
                                $element->carryover = true;
                                $token = $this->processToken(array($new_token, $token, $element));
                            } else {
                                $token = $this->insertBefore($new_token);
                            }
                        } else {
                            $token = $this->remove();
                        }
                        $reprocess = true;
                        continue;
                    }

                }
                $ok = true;
            }

            if ($ok) {
                foreach ($this->injectors as $i => $injector) {
                    if (isset($token->skip[$i])) {
                        continue;
                    }
                    if ($token->rewind !== null && $token->rewind !== $i) {
                        continue;
                    }
                    $r = $token;
                    $injector->handleElement($r);
                    $token = $this->processToken($r, $i);
                    $reprocess = true;
                    break;
                }
                if (!$reprocess) {
                                        if ($token instanceof HTMLPurifier_Token_Start) {
                        $this->stack[] = $token;
                    } elseif ($token instanceof HTMLPurifier_Token_End) {
                        throw new HTMLPurifier_Exception(
                            'Improper handling of end tag in start code; possible error in MakeWellFormed'
                        );
                    }
                }
                continue;
            }

                        if (!$token instanceof HTMLPurifier_Token_End) {
                throw new HTMLPurifier_Exception('Unaccounted for tag token in input stream, bug in HTML Purifier');
            }

                        if (empty($this->stack)) {
                if ($escape_invalid_tags) {
                    if ($e) {
                        $e->send(E_WARNING, 'Strategy_MakeWellFormed: Unnecessary end tag to text');
                    }
                    $token = new HTMLPurifier_Token_Text($generator->generateFromToken($token));
                } else {
                    if ($e) {
                        $e->send(E_WARNING, 'Strategy_MakeWellFormed: Unnecessary end tag removed');
                    }
                    $token = $this->remove();
                }
                $reprocess = true;
                continue;
            }

                                                            $current_parent = array_pop($this->stack);
            if ($current_parent->name == $token->name) {
                $token->start = $current_parent;
                foreach ($this->injectors as $i => $injector) {
                    if (isset($token->skip[$i])) {
                        continue;
                    }
                    if ($token->rewind !== null && $token->rewind !== $i) {
                        continue;
                    }
                    $r = $token;
                    $injector->handleEnd($r);
                    $token = $this->processToken($r, $i);
                    $this->stack[] = $current_parent;
                    $reprocess = true;
                    break;
                }
                continue;
            }

            
                        $this->stack[] = $current_parent;

                                    $size = count($this->stack);
                        $skipped_tags = false;
            for ($j = $size - 2; $j >= 0; $j--) {
                if ($this->stack[$j]->name == $token->name) {
                    $skipped_tags = array_slice($this->stack, $j);
                    break;
                }
            }

                        if ($skipped_tags === false) {
                if ($escape_invalid_tags) {
                    if ($e) {
                        $e->send(E_WARNING, 'Strategy_MakeWellFormed: Stray end tag to text');
                    }
                    $token = new HTMLPurifier_Token_Text($generator->generateFromToken($token));
                } else {
                    if ($e) {
                        $e->send(E_WARNING, 'Strategy_MakeWellFormed: Stray end tag removed');
                    }
                    $token = $this->remove();
                }
                $reprocess = true;
                continue;
            }

                        $c = count($skipped_tags);
            if ($e) {
                for ($j = $c - 1; $j > 0; $j--) {
                                                            if (!isset($skipped_tags[$j]->armor['MakeWellFormed_TagClosedError'])) {
                        $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by element end', $skipped_tags[$j]);
                    }
                }
            }

                        $replace = array($token);
            for ($j = 1; $j < $c; $j++) {
                                $new_token = new HTMLPurifier_Token_End($skipped_tags[$j]->name);
                $new_token->start = $skipped_tags[$j];
                array_unshift($replace, $new_token);
                if (isset($definition->info[$new_token->name]) && $definition->info[$new_token->name]->formatting) {
                                        $element = clone $skipped_tags[$j];
                    $element->carryover = true;
                    $element->armor['MakeWellFormed_TagClosedError'] = true;
                    $replace[] = $element;
                }
            }
            $token = $this->processToken($replace);
            $reprocess = true;
            continue;
        }

        $context->destroy('CurrentToken');
        $context->destroy('CurrentNesting');
        $context->destroy('InputZipper');

        unset($this->injectors, $this->stack, $this->tokens);
        return $zipper->toArray($token);
    }

    
    protected function processToken($token, $injector = -1)
    {
                if (is_object($token)) {
            $token = array(1, $token);
        }
        if (is_int($token)) {
            $token = array($token);
        }
        if ($token === false) {
            $token = array(1);
        }
        if (!is_array($token)) {
            throw new HTMLPurifier_Exception('Invalid token type from injector');
        }
        if (!is_int($token[0])) {
            array_unshift($token, 1);
        }
        if ($token[0] === 0) {
            throw new HTMLPurifier_Exception('Deleting zero tokens is not valid');
        }

                
        $delete = array_shift($token);
        list($old, $r) = $this->zipper->splice($this->token, $delete, $token);

        if ($injector > -1) {
                        $oldskip = isset($old[0]) ? $old[0]->skip : array();
            foreach ($token as $object) {
                $object->skip = $oldskip;
                $object->skip[$injector] = true;
            }
        }

        return $r;

    }

    
    private function insertBefore($token)
    {
                        $splice = $this->zipper->splice($this->token, 0, array($token));

        return $splice[1];
    }

    
    private function remove()
    {
        return $this->zipper->delete();
    }
}

