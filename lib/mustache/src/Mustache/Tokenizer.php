<?php




class Mustache_Tokenizer
{
        const IN_TEXT     = 0;
    const IN_TAG_TYPE = 1;
    const IN_TAG      = 2;

        const T_SECTION      = '#';
    const T_INVERTED     = '^';
    const T_END_SECTION  = '/';
    const T_COMMENT      = '!';
    const T_PARTIAL      = '>';
    const T_PARENT       = '<';
    const T_DELIM_CHANGE = '=';
    const T_ESCAPED      = '_v';
    const T_UNESCAPED    = '{';
    const T_UNESCAPED_2  = '&';
    const T_TEXT         = '_t';
    const T_PRAGMA       = '%';
    const T_BLOCK_VAR    = '$';
    const T_BLOCK_ARG    = '$arg';

        private static $tagTypes = array(
        self::T_SECTION      => true,
        self::T_INVERTED     => true,
        self::T_END_SECTION  => true,
        self::T_COMMENT      => true,
        self::T_PARTIAL      => true,
        self::T_PARENT       => true,
        self::T_DELIM_CHANGE => true,
        self::T_ESCAPED      => true,
        self::T_UNESCAPED    => true,
        self::T_UNESCAPED_2  => true,
        self::T_PRAGMA       => true,
        self::T_BLOCK_VAR    => true,
    );

        const TYPE    = 'type';
    const NAME    = 'name';
    const OTAG    = 'otag';
    const CTAG    = 'ctag';
    const LINE    = 'line';
    const INDEX   = 'index';
    const END     = 'end';
    const INDENT  = 'indent';
    const NODES   = 'nodes';
    const VALUE   = 'value';
    const FILTERS = 'filters';

    private $state;
    private $tagType;
    private $buffer;
    private $tokens;
    private $seenTag;
    private $line;
    private $otag;
    private $ctag;
    private $otagLen;
    private $ctagLen;

    
    public function scan($text, $delimiters = null)
    {
                        $encoding = null;
        if (function_exists('mb_internal_encoding') && ini_get('mbstring.func_overload') & 2) {
            $encoding = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        }

        $this->reset();

        if ($delimiters = trim($delimiters)) {
            $this->setDelimiters($delimiters);
        }

        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            switch ($this->state) {
                case self::IN_TEXT:
                    if ($this->tagChange($this->otag, $this->otagLen, $text, $i)) {
                        $i--;
                        $this->flushBuffer();
                        $this->state = self::IN_TAG_TYPE;
                    } else {
                        $char = $text[$i];
                        $this->buffer .= $char;
                        if ($char === "\n") {
                            $this->flushBuffer();
                            $this->line++;
                        }
                    }
                    break;

                case self::IN_TAG_TYPE:
                    $i += $this->otagLen - 1;
                    $char = $text[$i + 1];
                    if (isset(self::$tagTypes[$char])) {
                        $tag = $char;
                        $this->tagType = $tag;
                    } else {
                        $tag = null;
                        $this->tagType = self::T_ESCAPED;
                    }

                    if ($this->tagType === self::T_DELIM_CHANGE) {
                        $i = $this->changeDelimiters($text, $i);
                        $this->state = self::IN_TEXT;
                    } elseif ($this->tagType === self::T_PRAGMA) {
                        $i = $this->addPragma($text, $i);
                        $this->state = self::IN_TEXT;
                    } else {
                        if ($tag !== null) {
                            $i++;
                        }
                        $this->state = self::IN_TAG;
                    }
                    $this->seenTag = $i;
                    break;

                default:
                    if ($this->tagChange($this->ctag, $this->ctagLen, $text, $i)) {
                        $token = array(
                            self::TYPE  => $this->tagType,
                            self::NAME  => trim($this->buffer),
                            self::OTAG  => $this->otag,
                            self::CTAG  => $this->ctag,
                            self::LINE  => $this->line,
                            self::INDEX => ($this->tagType === self::T_END_SECTION) ? $this->seenTag - $this->otagLen : $i + $this->ctagLen,
                        );

                        if ($this->tagType === self::T_UNESCAPED) {
                                                        if ($this->ctag === '}}') {
                                if (($i + 2 < $len) && $text[$i + 2] === '}') {
                                    $i++;
                                } else {
                                    $msg = sprintf(
                                        'Mismatched tag delimiters: %s on line %d',
                                        $token[self::NAME],
                                        $token[self::LINE]
                                    );

                                    throw new Mustache_Exception_SyntaxException($msg, $token);
                                }
                            } else {
                                $lastName = $token[self::NAME];
                                if (substr($lastName, -1) === '}') {
                                    $token[self::NAME] = trim(substr($lastName, 0, -1));
                                } else {
                                    $msg = sprintf(
                                        'Mismatched tag delimiters: %s on line %d',
                                        $token[self::NAME],
                                        $token[self::LINE]
                                    );

                                    throw new Mustache_Exception_SyntaxException($msg, $token);
                                }
                            }
                        }

                        $this->buffer = '';
                        $i += $this->ctagLen - 1;
                        $this->state = self::IN_TEXT;
                        $this->tokens[] = $token;
                    } else {
                        $this->buffer .= $text[$i];
                    }
                    break;
            }
        }

        $this->flushBuffer();

                if ($encoding) {
            mb_internal_encoding($encoding);
        }

        return $this->tokens;
    }

    
    private function reset()
    {
        $this->state   = self::IN_TEXT;
        $this->tagType = null;
        $this->buffer  = '';
        $this->tokens  = array();
        $this->seenTag = false;
        $this->line    = 0;
        $this->otag    = '{{';
        $this->ctag    = '}}';
        $this->otagLen = 2;
        $this->ctagLen = 2;
    }

    
    private function flushBuffer()
    {
        if (strlen($this->buffer) > 0) {
            $this->tokens[] = array(
                self::TYPE  => self::T_TEXT,
                self::LINE  => $this->line,
                self::VALUE => $this->buffer,
            );
            $this->buffer   = '';
        }
    }

    
    private function changeDelimiters($text, $index)
    {
        $startIndex = strpos($text, '=', $index) + 1;
        $close      = '=' . $this->ctag;
        $closeIndex = strpos($text, $close, $index);

        $this->setDelimiters(trim(substr($text, $startIndex, $closeIndex - $startIndex)));

        $this->tokens[] = array(
            self::TYPE => self::T_DELIM_CHANGE,
            self::LINE => $this->line,
        );

        return $closeIndex + strlen($close) - 1;
    }

    
    private function setDelimiters($delimiters)
    {
        list($otag, $ctag) = explode(' ', $delimiters);
        $this->otag = $otag;
        $this->ctag = $ctag;
        $this->otagLen = strlen($otag);
        $this->ctagLen = strlen($ctag);
    }

    
    private function addPragma($text, $index)
    {
        $end    = strpos($text, $this->ctag, $index);
        $pragma = trim(substr($text, $index + 2, $end - $index - 2));

                array_unshift($this->tokens, array(
            self::TYPE => self::T_PRAGMA,
            self::NAME => $pragma,
            self::LINE => 0,
        ));

        return $end + $this->ctagLen - 1;
    }

    
    private function tagChange($tag, $tagLen, $text, $index)
    {
        return substr($text, $index, $tagLen) === $tag;
    }
}
