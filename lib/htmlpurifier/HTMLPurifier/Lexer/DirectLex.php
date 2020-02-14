<?php


class HTMLPurifier_Lexer_DirectLex extends HTMLPurifier_Lexer
{
    
    public $tracksLineNumbers = true;

    
    protected $_whitespace = "\x20\x09\x0D\x0A";

    
    protected function scriptCallback($matches)
    {
        return $matches[1] . htmlspecialchars($matches[2], ENT_COMPAT, 'UTF-8') . $matches[3];
    }

    
    public function tokenizeHTML($html, $config, $context)
    {
                                if ($config->get('HTML.Trusted')) {
            $html = preg_replace_callback(
                '#(<script[^>]*>)(\s*[^<].+?)(</script>)#si',
                array($this, 'scriptCallback'),
                $html
            );
        }

        $html = $this->normalize($html, $config, $context);

        $cursor = 0;         $inside_tag = false;         $array = array(); 
                $maintain_line_numbers = $config->get('Core.MaintainLineNumbers');

        if ($maintain_line_numbers === null) {
                                    $maintain_line_numbers = $config->get('Core.CollectErrors');
        }

        if ($maintain_line_numbers) {
            $current_line = 1;
            $current_col = 0;
            $length = strlen($html);
        } else {
            $current_line = false;
            $current_col = false;
            $length = false;
        }
        $context->register('CurrentLine', $current_line);
        $context->register('CurrentCol', $current_col);
        $nl = "\n";
                        $synchronize_interval = $config->get('Core.DirectLexLineNumberSyncInterval');

        $e = false;
        if ($config->get('Core.CollectErrors')) {
            $e =& $context->get('ErrorCollector');
        }

                $loops = 0;

        while (++$loops) {
                                    
            if ($maintain_line_numbers) {
                                $rcursor = $cursor - (int)$inside_tag;

                                                                                $nl_pos = strrpos($html, $nl, $rcursor - $length);
                $current_col = $rcursor - (is_bool($nl_pos) ? 0 : $nl_pos + 1);

                                if ($synchronize_interval &&                     $cursor > 0 &&                     $loops % $synchronize_interval === 0) {                     $current_line = 1 + $this->substrCount($html, $nl, 0, $cursor);
                }
            }

            $position_next_lt = strpos($html, '<', $cursor);
            $position_next_gt = strpos($html, '>', $cursor);

                                    if ($position_next_lt === $cursor) {
                $inside_tag = true;
                $cursor++;
            }

            if (!$inside_tag && $position_next_lt !== false) {
                                $token = new
                HTMLPurifier_Token_Text(
                    $this->parseData(
                        substr(
                            $html,
                            $cursor,
                            $position_next_lt - $cursor
                        )
                    )
                );
                if ($maintain_line_numbers) {
                    $token->rawPosition($current_line, $current_col);
                    $current_line += $this->substrCount($html, $nl, $cursor, $position_next_lt - $cursor);
                }
                $array[] = $token;
                $cursor = $position_next_lt + 1;
                $inside_tag = true;
                continue;
            } elseif (!$inside_tag) {
                                                if ($cursor === strlen($html)) {
                    break;
                }
                                $token = new
                HTMLPurifier_Token_Text(
                    $this->parseData(
                        substr(
                            $html,
                            $cursor
                        )
                    )
                );
                if ($maintain_line_numbers) {
                    $token->rawPosition($current_line, $current_col);
                }
                $array[] = $token;
                break;
            } elseif ($inside_tag && $position_next_gt !== false) {
                                                $strlen_segment = $position_next_gt - $cursor;

                if ($strlen_segment < 1) {
                                        $token = new HTMLPurifier_Token_Text('<');
                    $cursor++;
                    continue;
                }

                $segment = substr($html, $cursor, $strlen_segment);

                if ($segment === false) {
                                                            break;
                }

                                if (substr($segment, 0, 3) === '!--') {
                                        $position_comment_end = strpos($html, '-->', $cursor);
                    if ($position_comment_end === false) {
                                                                                                if ($e) {
                            $e->send(E_WARNING, 'Lexer: Unclosed comment');
                        }
                        $position_comment_end = strlen($html);
                        $end = true;
                    } else {
                        $end = false;
                    }
                    $strlen_segment = $position_comment_end - $cursor;
                    $segment = substr($html, $cursor, $strlen_segment);
                    $token = new
                    HTMLPurifier_Token_Comment(
                        substr(
                            $segment,
                            3,
                            $strlen_segment - 3
                        )
                    );
                    if ($maintain_line_numbers) {
                        $token->rawPosition($current_line, $current_col);
                        $current_line += $this->substrCount($html, $nl, $cursor, $strlen_segment);
                    }
                    $array[] = $token;
                    $cursor = $end ? $position_comment_end : $position_comment_end + 3;
                    $inside_tag = false;
                    continue;
                }

                                $is_end_tag = (strpos($segment, '/') === 0);
                if ($is_end_tag) {
                    $type = substr($segment, 1);
                    $token = new HTMLPurifier_Token_End($type);
                    if ($maintain_line_numbers) {
                        $token->rawPosition($current_line, $current_col);
                        $current_line += $this->substrCount($html, $nl, $cursor, $position_next_gt - $cursor);
                    }
                    $array[] = $token;
                    $inside_tag = false;
                    $cursor = $position_next_gt + 1;
                    continue;
                }

                                                                if (!ctype_alpha($segment[0])) {
                                        if ($e) {
                        $e->send(E_NOTICE, 'Lexer: Unescaped lt');
                    }
                    $token = new HTMLPurifier_Token_Text('<');
                    if ($maintain_line_numbers) {
                        $token->rawPosition($current_line, $current_col);
                        $current_line += $this->substrCount($html, $nl, $cursor, $position_next_gt - $cursor);
                    }
                    $array[] = $token;
                    $inside_tag = false;
                    continue;
                }

                                                                                $is_self_closing = (strrpos($segment, '/') === $strlen_segment - 1);
                if ($is_self_closing) {
                    $strlen_segment--;
                    $segment = substr($segment, 0, $strlen_segment);
                }

                                $position_first_space = strcspn($segment, $this->_whitespace);

                if ($position_first_space >= $strlen_segment) {
                    if ($is_self_closing) {
                        $token = new HTMLPurifier_Token_Empty($segment);
                    } else {
                        $token = new HTMLPurifier_Token_Start($segment);
                    }
                    if ($maintain_line_numbers) {
                        $token->rawPosition($current_line, $current_col);
                        $current_line += $this->substrCount($html, $nl, $cursor, $position_next_gt - $cursor);
                    }
                    $array[] = $token;
                    $inside_tag = false;
                    $cursor = $position_next_gt + 1;
                    continue;
                }

                                $type = substr($segment, 0, $position_first_space);
                $attribute_string =
                    trim(
                        substr(
                            $segment,
                            $position_first_space
                        )
                    );
                if ($attribute_string) {
                    $attr = $this->parseAttributeString(
                        $attribute_string,
                        $config,
                        $context
                    );
                } else {
                    $attr = array();
                }

                if ($is_self_closing) {
                    $token = new HTMLPurifier_Token_Empty($type, $attr);
                } else {
                    $token = new HTMLPurifier_Token_Start($type, $attr);
                }
                if ($maintain_line_numbers) {
                    $token->rawPosition($current_line, $current_col);
                    $current_line += $this->substrCount($html, $nl, $cursor, $position_next_gt - $cursor);
                }
                $array[] = $token;
                $cursor = $position_next_gt + 1;
                $inside_tag = false;
                continue;
            } else {
                                if ($e) {
                    $e->send(E_WARNING, 'Lexer: Missing gt');
                }
                $token = new
                HTMLPurifier_Token_Text(
                    '<' .
                    $this->parseData(
                        substr($html, $cursor)
                    )
                );
                if ($maintain_line_numbers) {
                    $token->rawPosition($current_line, $current_col);
                }
                                $array[] = $token;
                break;
            }
            break;
        }

        $context->destroy('CurrentLine');
        $context->destroy('CurrentCol');
        return $array;
    }

    
    protected function substrCount($haystack, $needle, $offset, $length)
    {
        static $oldVersion;
        if ($oldVersion === null) {
            $oldVersion = version_compare(PHP_VERSION, '5.1', '<');
        }
        if ($oldVersion) {
            $haystack = substr($haystack, $offset, $length);
            return substr_count($haystack, $needle);
        } else {
            return substr_count($haystack, $needle, $offset, $length);
        }
    }

    
    public function parseAttributeString($string, $config, $context)
    {
        $string = (string)$string; 
        if ($string == '') {
            return array();
        } 
        $e = false;
        if ($config->get('Core.CollectErrors')) {
            $e =& $context->get('ErrorCollector');
        }

                        $num_equal = substr_count($string, '=');
        $has_space = strpos($string, ' ');
        if ($num_equal === 0 && !$has_space) {
                        return array($string => $string);
        } elseif ($num_equal === 1 && !$has_space) {
                        list($key, $quoted_value) = explode('=', $string);
            $quoted_value = trim($quoted_value);
            if (!$key) {
                if ($e) {
                    $e->send(E_ERROR, 'Lexer: Missing attribute key');
                }
                return array();
            }
            if (!$quoted_value) {
                return array($key => '');
            }
            $first_char = @$quoted_value[0];
            $last_char = @$quoted_value[strlen($quoted_value) - 1];

            $same_quote = ($first_char == $last_char);
            $open_quote = ($first_char == '"' || $first_char == "'");

            if ($same_quote && $open_quote) {
                                $value = substr($quoted_value, 1, strlen($quoted_value) - 2);
            } else {
                                if ($open_quote) {
                    if ($e) {
                        $e->send(E_ERROR, 'Lexer: Missing end quote');
                    }
                    $value = substr($quoted_value, 1);
                } else {
                    $value = $quoted_value;
                }
            }
            if ($value === false) {
                $value = '';
            }
            return array($key => $this->parseData($value));
        }

                $array = array();         $cursor = 0;         $size = strlen($string); 
                        $string .= ' ';

        $old_cursor = -1;
        while ($cursor < $size) {
            if ($old_cursor >= $cursor) {
                throw new Exception("Infinite loop detected");
            }
            $old_cursor = $cursor;

            $cursor += ($value = strspn($string, $this->_whitespace, $cursor));
            
            $key_begin = $cursor; 
                        $cursor += strcspn($string, $this->_whitespace . '=', $cursor);

            $key_end = $cursor; 
            $key = substr($string, $key_begin, $key_end - $key_begin);

            if (!$key) {
                if ($e) {
                    $e->send(E_ERROR, 'Lexer: Missing attribute key');
                }
                $cursor += 1 + strcspn($string, $this->_whitespace, $cursor + 1);                 continue;             }

                        $cursor += strspn($string, $this->_whitespace, $cursor);

            if ($cursor >= $size) {
                $array[$key] = $key;
                break;
            }

                                    $first_char = @$string[$cursor];

            if ($first_char == '=') {
                
                $cursor++;
                $cursor += strspn($string, $this->_whitespace, $cursor);

                if ($cursor === false) {
                    $array[$key] = '';
                    break;
                }

                
                $char = @$string[$cursor];

                if ($char == '"' || $char == "'") {
                                        $cursor++;
                    $value_begin = $cursor;
                    $cursor = strpos($string, $char, $cursor);
                    $value_end = $cursor;
                } else {
                                        $value_begin = $cursor;
                    $cursor += strcspn($string, $this->_whitespace, $cursor);
                    $value_end = $cursor;
                }

                                if ($cursor === false) {
                    $cursor = $size;
                    $value_end = $cursor;
                }

                $value = substr($string, $value_begin, $value_end - $value_begin);
                if ($value === false) {
                    $value = '';
                }
                $array[$key] = $this->parseData($value);
                $cursor++;
            } else {
                                if ($key !== '') {
                    $array[$key] = $key;
                } else {
                                        if ($e) {
                        $e->send(E_ERROR, 'Lexer: Missing attribute key');
                    }
                }
            }
        }
        return $array;
    }
}

