<?php




class Mustache_Parser
{
    private $lineNum;
    private $lineTokens;
    private $pragmas;
    private $defaultPragmas = array();

    private $pragmaFilters;
    private $pragmaBlocks;

    
    public function parse(array $tokens = array())
    {
        $this->lineNum    = -1;
        $this->lineTokens = 0;
        $this->pragmas    = $this->defaultPragmas;

        $this->pragmaFilters = isset($this->pragmas[Mustache_Engine::PRAGMA_FILTERS]);
        $this->pragmaBlocks  = isset($this->pragmas[Mustache_Engine::PRAGMA_BLOCKS]);

        return $this->buildTree($tokens);
    }

    
    public function setPragmas(array $pragmas)
    {
        $this->pragmas = array();
        foreach ($pragmas as $pragma) {
            $this->enablePragma($pragma);
        }
        $this->defaultPragmas = $this->pragmas;
    }

    
    private function buildTree(array &$tokens, array $parent = null)
    {
        $nodes = array();

        while (!empty($tokens)) {
            $token = array_shift($tokens);

            if ($token[Mustache_Tokenizer::LINE] === $this->lineNum) {
                $this->lineTokens++;
            } else {
                $this->lineNum    = $token[Mustache_Tokenizer::LINE];
                $this->lineTokens = 0;
            }

            if ($this->pragmaFilters && isset($token[Mustache_Tokenizer::NAME])) {
                list($name, $filters) = $this->getNameAndFilters($token[Mustache_Tokenizer::NAME]);
                if (!empty($filters)) {
                    $token[Mustache_Tokenizer::NAME]    = $name;
                    $token[Mustache_Tokenizer::FILTERS] = $filters;
                }
            }

            switch ($token[Mustache_Tokenizer::TYPE]) {
                case Mustache_Tokenizer::T_DELIM_CHANGE:
                    $this->checkIfTokenIsAllowedInParent($parent, $token);
                    $this->clearStandaloneLines($nodes, $tokens);
                    break;

                case Mustache_Tokenizer::T_SECTION:
                case Mustache_Tokenizer::T_INVERTED:
                    $this->checkIfTokenIsAllowedInParent($parent, $token);
                    $this->clearStandaloneLines($nodes, $tokens);
                    $nodes[] = $this->buildTree($tokens, $token);
                    break;

                case Mustache_Tokenizer::T_END_SECTION:
                    if (!isset($parent)) {
                        $msg = sprintf(
                            'Unexpected closing tag: /%s on line %d',
                            $token[Mustache_Tokenizer::NAME],
                            $token[Mustache_Tokenizer::LINE]
                        );
                        throw new Mustache_Exception_SyntaxException($msg, $token);
                    }

                    if ($token[Mustache_Tokenizer::NAME] !== $parent[Mustache_Tokenizer::NAME]) {
                        $msg = sprintf(
                            'Nesting error: %s (on line %d) vs. %s (on line %d)',
                            $parent[Mustache_Tokenizer::NAME],
                            $parent[Mustache_Tokenizer::LINE],
                            $token[Mustache_Tokenizer::NAME],
                            $token[Mustache_Tokenizer::LINE]
                        );
                        throw new Mustache_Exception_SyntaxException($msg, $token);
                    }

                    $this->clearStandaloneLines($nodes, $tokens);
                    $parent[Mustache_Tokenizer::END]   = $token[Mustache_Tokenizer::INDEX];
                    $parent[Mustache_Tokenizer::NODES] = $nodes;

                    return $parent;

                case Mustache_Tokenizer::T_PARTIAL:
                    $this->checkIfTokenIsAllowedInParent($parent, $token);
                                        if ($indent = $this->clearStandaloneLines($nodes, $tokens)) {
                        $token[Mustache_Tokenizer::INDENT] = $indent[Mustache_Tokenizer::VALUE];
                    }
                    $nodes[] = $token;
                    break;

                case Mustache_Tokenizer::T_PARENT:
                    $this->checkIfTokenIsAllowedInParent($parent, $token);
                    $nodes[] = $this->buildTree($tokens, $token);
                    break;

                case Mustache_Tokenizer::T_BLOCK_VAR:
                    if ($this->pragmaBlocks) {
                                                if ($parent[Mustache_Tokenizer::TYPE] === Mustache_Tokenizer::T_PARENT) {
                            $token[Mustache_Tokenizer::TYPE] = Mustache_Tokenizer::T_BLOCK_ARG;
                        }
                        $this->clearStandaloneLines($nodes, $tokens);
                        $nodes[] = $this->buildTree($tokens, $token);
                    } else {
                                                $token[Mustache_Tokenizer::TYPE] = Mustache_Tokenizer::T_ESCAPED;
                                                $token[Mustache_Tokenizer::NAME] = '$' . $token[Mustache_Tokenizer::NAME];
                        $nodes[] = $token;
                    }
                    break;

                case Mustache_Tokenizer::T_PRAGMA:
                    $this->enablePragma($token[Mustache_Tokenizer::NAME]);
                    
                case Mustache_Tokenizer::T_COMMENT:
                    $this->clearStandaloneLines($nodes, $tokens);
                    $nodes[] = $token;
                    break;

                default:
                    $nodes[] = $token;
                    break;
            }
        }

        if (isset($parent)) {
            $msg = sprintf(
                'Missing closing tag: %s opened on line %d',
                $parent[Mustache_Tokenizer::NAME],
                $parent[Mustache_Tokenizer::LINE]
            );
            throw new Mustache_Exception_SyntaxException($msg, $parent);
        }

        return $nodes;
    }

    
    private function clearStandaloneLines(array &$nodes, array &$tokens)
    {
        if ($this->lineTokens > 1) {
                        return;
        }

        $prev = null;
        if ($this->lineTokens === 1) {
                                    if ($prev = end($nodes)) {
                if (!$this->tokenIsWhitespace($prev)) {
                    return;
                }
            }
        }

        if ($next = reset($tokens)) {
                        if ($next[Mustache_Tokenizer::LINE] !== $this->lineNum) {
                return;
            }

                        if (!$this->tokenIsWhitespace($next)) {
                return;
            }

            if (count($tokens) !== 1) {
                                                if (substr($next[Mustache_Tokenizer::VALUE], -1) !== "\n") {
                    return;
                }
            }

                        array_shift($tokens);
        }

        if ($prev) {
                        return array_pop($nodes);
        }
    }

    
    private function tokenIsWhitespace(array $token)
    {
        if ($token[Mustache_Tokenizer::TYPE] === Mustache_Tokenizer::T_TEXT) {
            return preg_match('/^\s*$/', $token[Mustache_Tokenizer::VALUE]);
        }

        return false;
    }

    
    private function checkIfTokenIsAllowedInParent($parent, array $token)
    {
        if ($parent[Mustache_Tokenizer::TYPE] === Mustache_Tokenizer::T_PARENT) {
            throw new Mustache_Exception_SyntaxException('Illegal content in < parent tag', $token);
        }
    }

    
    private function getNameAndFilters($name)
    {
        $filters = array_map('trim', explode('|', $name));
        $name    = array_shift($filters);

        return array($name, $filters);
    }

    
    private function enablePragma($name)
    {
        $this->pragmas[$name] = true;

        switch ($name) {
            case Mustache_Engine::PRAGMA_BLOCKS:
                $this->pragmaBlocks = true;
                break;

            case Mustache_Engine::PRAGMA_FILTERS:
                $this->pragmaFilters = true;
                break;
        }
    }
}
