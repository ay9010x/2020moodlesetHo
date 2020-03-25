<?php




class Mustache_Compiler
{
    private $pragmas;
    private $defaultPragmas = array();
    private $sections;
    private $blocks;
    private $source;
    private $indentNextLine;
    private $customEscape;
    private $entityFlags;
    private $charset;
    private $strictCallables;

    
    public function compile($source, array $tree, $name, $customEscape = false, $charset = 'UTF-8', $strictCallables = false, $entityFlags = ENT_COMPAT)
    {
        $this->pragmas         = $this->defaultPragmas;
        $this->sections        = array();
        $this->blocks          = array();
        $this->source          = $source;
        $this->indentNextLine  = true;
        $this->customEscape    = $customEscape;
        $this->entityFlags     = $entityFlags;
        $this->charset         = $charset;
        $this->strictCallables = $strictCallables;

        return $this->writeCode($tree, $name);
    }

    
    public function setPragmas(array $pragmas)
    {
        $this->pragmas = array();
        foreach ($pragmas as $pragma) {
            $this->pragmas[$pragma] = true;
        }
        $this->defaultPragmas = $this->pragmas;
    }

    
    private function walk(array $tree, $level = 0)
    {
        $code = '';
        $level++;
        foreach ($tree as $node) {
            switch ($node[Mustache_Tokenizer::TYPE]) {
                case Mustache_Tokenizer::T_PRAGMA:
                    $this->pragmas[$node[Mustache_Tokenizer::NAME]] = true;
                    break;

                case Mustache_Tokenizer::T_SECTION:
                    $code .= $this->section(
                        $node[Mustache_Tokenizer::NODES],
                        $node[Mustache_Tokenizer::NAME],
                        isset($node[Mustache_Tokenizer::FILTERS]) ? $node[Mustache_Tokenizer::FILTERS] : array(),
                        $node[Mustache_Tokenizer::INDEX],
                        $node[Mustache_Tokenizer::END],
                        $node[Mustache_Tokenizer::OTAG],
                        $node[Mustache_Tokenizer::CTAG],
                        $level
                    );
                    break;

                case Mustache_Tokenizer::T_INVERTED:
                    $code .= $this->invertedSection(
                        $node[Mustache_Tokenizer::NODES],
                        $node[Mustache_Tokenizer::NAME],
                        isset($node[Mustache_Tokenizer::FILTERS]) ? $node[Mustache_Tokenizer::FILTERS] : array(),
                        $level
                    );
                    break;

                case Mustache_Tokenizer::T_PARTIAL:
                    $code .= $this->partial(
                        $node[Mustache_Tokenizer::NAME],
                        isset($node[Mustache_Tokenizer::INDENT]) ? $node[Mustache_Tokenizer::INDENT] : '',
                        $level
                    );
                    break;

                case Mustache_Tokenizer::T_PARENT:
                    $code .= $this->parent(
                        $node[Mustache_Tokenizer::NAME],
                        isset($node[Mustache_Tokenizer::INDENT]) ? $node[Mustache_Tokenizer::INDENT] : '',
                        $node[Mustache_Tokenizer::NODES],
                        $level
                    );
                    break;

                case Mustache_Tokenizer::T_BLOCK_ARG:
                    $code .= $this->blockArg(
                        $node[Mustache_Tokenizer::NODES],
                        $node[Mustache_Tokenizer::NAME],
                        $node[Mustache_Tokenizer::INDEX],
                        $node[Mustache_Tokenizer::END],
                        $node[Mustache_Tokenizer::OTAG],
                        $node[Mustache_Tokenizer::CTAG],
                        $level
                    );
                    break;

                case Mustache_Tokenizer::T_BLOCK_VAR:
                    $code .= $this->blockVar(
                        $node[Mustache_Tokenizer::NODES],
                        $node[Mustache_Tokenizer::NAME],
                        $node[Mustache_Tokenizer::INDEX],
                        $node[Mustache_Tokenizer::END],
                        $node[Mustache_Tokenizer::OTAG],
                        $node[Mustache_Tokenizer::CTAG],
                        $level
                    );
                    break;

                case Mustache_Tokenizer::T_COMMENT:
                    break;

                case Mustache_Tokenizer::T_ESCAPED:
                case Mustache_Tokenizer::T_UNESCAPED:
                case Mustache_Tokenizer::T_UNESCAPED_2:
                    $code .= $this->variable(
                        $node[Mustache_Tokenizer::NAME],
                        isset($node[Mustache_Tokenizer::FILTERS]) ? $node[Mustache_Tokenizer::FILTERS] : array(),
                        $node[Mustache_Tokenizer::TYPE] === Mustache_Tokenizer::T_ESCAPED,
                        $level
                    );
                    break;

                case Mustache_Tokenizer::T_TEXT:
                    $code .= $this->text($node[Mustache_Tokenizer::VALUE], $level);
                    break;

                default:
                    throw new Mustache_Exception_SyntaxException(sprintf('Unknown token type: %s', $node[Mustache_Tokenizer::TYPE]), $node);
            }
        }

        return $code;
    }

    const KLASS = '<?php

        class %s extends Mustache_Template
        {
            private $lambdaHelper;%s

            public function renderInternal(Mustache_Context $context, $indent = \'\')
            {
                $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
                $buffer = \'\';
                $newContext = array();
        %s

                return $buffer;
            }
        %s
        %s
        }';

    const KLASS_NO_LAMBDAS = '<?php

        class %s extends Mustache_Template
        {%s
            public function renderInternal(Mustache_Context $context, $indent = \'\')
            {
                $buffer = \'\';
                $newContext = array();
        %s

                return $buffer;
            }
        }';

    const STRICT_CALLABLE = 'protected $strictCallables = true;';

    
    private function writeCode($tree, $name)
    {
        $code     = $this->walk($tree);
        $sections = implode("\n", $this->sections);
        $blocks   = implode("\n", $this->blocks);
        $klass    = empty($this->sections) && empty($this->blocks) ? self::KLASS_NO_LAMBDAS : self::KLASS;

        $callable = $this->strictCallables ? $this->prepare(self::STRICT_CALLABLE) : '';

        return sprintf($this->prepare($klass, 0, false, true), $name, $callable, $code, $sections, $blocks);
    }

    const BLOCK_VAR = '
        $blockFunction = $context->findInBlock(%s);
        if (is_callable($blockFunction)) {
            $buffer .= call_user_func($blockFunction, $context);
        } else {%s
        }
    ';

    
    private function blockVar($nodes, $id, $start, $end, $otag, $ctag, $level)
    {
        $id = var_export($id, true);

        return sprintf($this->prepare(self::BLOCK_VAR, $level), $id, $this->walk($nodes, $level));
    }

    const BLOCK_ARG = '$newContext[%s] = array($this, \'block%s\');';

    
    private function blockArg($nodes, $id, $start, $end, $otag, $ctag, $level)
    {
        $key = $this->block($nodes);
        $keystr = var_export($key, true);
        $id = var_export($id, true);

        return sprintf($this->prepare(self::BLOCK_ARG, 1), $id, $key);
    }

    const BLOCK_FUNCTION = '
        public function block%s($context)
        {
            $indent = $buffer = \'\';%s

            return $buffer;
        }
    ';

    
    private function block($nodes)
    {
        $code = $this->walk($nodes, 0);
        $key = ucfirst(md5($code));

        if (!isset($this->blocks[$key])) {
            $this->blocks[$key] = sprintf($this->prepare(self::BLOCK_FUNCTION, 0), $key, $code);
        }

        return $key;
    }

    const SECTION_CALL = '
        // %s section
        $value = $context->%s(%s);%s
        $buffer .= $this->section%s($context, $indent, $value);
    ';

    const SECTION = '
        private function section%s(Mustache_Context $context, $indent, $value)
        {
            $buffer = \'\';
            if (%s) {
                $source = %s;
                $result = call_user_func($value, $source, %s);
                if (strpos($result, \'{{\') === false) {
                    $buffer .= $result;
                } else {
                    $buffer .= $this->mustache
                        ->loadLambda((string) $result%s)
                        ->renderInternal($context);
                }
            } elseif (!empty($value)) {
                $values = $this->isIterable($value) ? $value : array($value);
                foreach ($values as $value) {
                    $context->push($value);
                    %s
                    $context->pop();
                }
            }

            return $buffer;
        }
    ';

    
    private function section($nodes, $id, $filters, $start, $end, $otag, $ctag, $level, $arg = false)
    {
        $source   = var_export(substr($this->source, $start, $end - $start), true);
        $callable = $this->getCallable();

        if ($otag !== '{{' || $ctag !== '}}') {
            $delimTag = var_export(sprintf('{{= %s %s =}}', $otag, $ctag), true);
            $helper = sprintf('$this->lambdaHelper->withDelimiters(%s)', $delimTag);
            $delims = ', ' . $delimTag;
        } else {
            $helper = '$this->lambdaHelper';
            $delims = '';
        }

        $key = ucfirst(md5($delims . "\n" . $source));

        if (!isset($this->sections[$key])) {
            $this->sections[$key] = sprintf($this->prepare(self::SECTION), $key, $callable, $source, $helper, $delims, $this->walk($nodes, 2));
        }

        if ($arg === true) {
            return $key;
        } else {
            $method  = $this->getFindMethod($id);
            $id      = var_export($id, true);
            $filters = $this->getFilters($filters, $level);

            return sprintf($this->prepare(self::SECTION_CALL, $level), $id, $method, $id, $filters, $key);
        }
    }

    const INVERTED_SECTION = '
        // %s inverted section
        $value = $context->%s(%s);%s
        if (empty($value)) {
            %s
        }
    ';

    
    private function invertedSection($nodes, $id, $filters, $level)
    {
        $method  = $this->getFindMethod($id);
        $id      = var_export($id, true);
        $filters = $this->getFilters($filters, $level);

        return sprintf($this->prepare(self::INVERTED_SECTION, $level), $id, $method, $id, $filters, $this->walk($nodes, $level));
    }

    const PARTIAL_INDENT = ', $indent . %s';
    const PARTIAL = '
        if ($partial = $this->mustache->loadPartial(%s)) {
            $buffer .= $partial->renderInternal($context%s);
        }
    ';

    
    private function partial($id, $indent, $level)
    {
        if ($indent !== '') {
            $indentParam = sprintf(self::PARTIAL_INDENT, var_export($indent, true));
        } else {
            $indentParam = '';
        }

        return sprintf(
            $this->prepare(self::PARTIAL, $level),
            var_export($id, true),
            $indentParam
        );
    }

    const PARENT = '
        %s

        if ($parent = $this->mustache->LoadPartial(%s)) {
            $context->pushBlockContext($newContext);
            $buffer .= $parent->renderInternal($context, $indent);
            $context->popBlockContext();
        }
    ';

    
    private function parent($id, $indent, array $children, $level)
    {
        $realChildren = array_filter($children, array(__CLASS__, 'onlyBlockArgs'));

        return sprintf(
            $this->prepare(self::PARENT, $level),
            $this->walk($realChildren, $level),
            var_export($id, true),
            var_export($indent, true)
        );
    }

    
    private static function onlyBlockArgs(array $node)
    {
        return $node[Mustache_Tokenizer::TYPE] === Mustache_Tokenizer::T_BLOCK_ARG;
    }

    const VARIABLE = '
        $value = $this->resolveValue($context->%s(%s), $context);%s
        $buffer .= %s%s;
    ';

    
    private function variable($id, $filters, $escape, $level)
    {
        $method  = $this->getFindMethod($id);
        $id      = ($method !== 'last') ? var_export($id, true) : '';
        $filters = $this->getFilters($filters, $level);
        $value   = $escape ? $this->getEscape() : '$value';

        return sprintf($this->prepare(self::VARIABLE, $level), $method, $id, $filters, $this->flushIndent(), $value);
    }

    const FILTER = '
        $filter = $context->%s(%s);
        if (!(%s)) {
            throw new Mustache_Exception_UnknownFilterException(%s);
        }
        $value = call_user_func($filter, $value);%s
    ';

    
    private function getFilters(array $filters, $level)
    {
        if (empty($filters)) {
            return '';
        }

        $name     = array_shift($filters);
        $method   = $this->getFindMethod($name);
        $filter   = ($method !== 'last') ? var_export($name, true) : '';
        $callable = $this->getCallable('$filter');
        $msg      = var_export($name, true);

        return sprintf($this->prepare(self::FILTER, $level), $method, $filter, $callable, $msg, $this->getFilters($filters, $level));
    }

    const LINE = '$buffer .= "\n";';
    const TEXT = '$buffer .= %s%s;';

    
    private function text($text, $level)
    {
        $indentNextLine = (substr($text, -1) === "\n");
        $code = sprintf($this->prepare(self::TEXT, $level), $this->flushIndent(), var_export($text, true));
        $this->indentNextLine = $indentNextLine;

        return $code;
    }

    
    private function prepare($text, $bonus = 0, $prependNewline = true, $appendNewline = false)
    {
        $text = ($prependNewline ? "\n" : '') . trim($text);
        if ($prependNewline) {
            $bonus++;
        }
        if ($appendNewline) {
            $text .= "\n";
        }

        return preg_replace("/\n( {8})?/", "\n" . str_repeat(' ', $bonus * 4), $text);
    }

    const DEFAULT_ESCAPE = 'htmlspecialchars(%s, %s, %s)';
    const CUSTOM_ESCAPE  = 'call_user_func($this->mustache->getEscape(), %s)';

    
    private function getEscape($value = '$value')
    {
        if ($this->customEscape) {
            return sprintf(self::CUSTOM_ESCAPE, $value);
        }

        return sprintf(self::DEFAULT_ESCAPE, $value, var_export($this->entityFlags, true), var_export($this->charset, true));
    }

    
    private function getFindMethod($id)
    {
        if ($id === '.') {
            return 'last';
        }

        if (isset($this->pragmas[Mustache_Engine::PRAGMA_ANCHORED_DOT]) && $this->pragmas[Mustache_Engine::PRAGMA_ANCHORED_DOT]) {
            if (substr($id, 0, 1) === '.') {
                return 'findAnchoredDot';
            }
        }

        if (strpos($id, '.') === false) {
            return 'find';
        }

        return 'findDot';
    }

    const IS_CALLABLE        = '!is_string(%s) && is_callable(%s)';
    const STRICT_IS_CALLABLE = 'is_object(%s) && is_callable(%s)';

    
    private function getCallable($variable = '$value')
    {
        $tpl = $this->strictCallables ? self::STRICT_IS_CALLABLE : self::IS_CALLABLE;

        return sprintf($tpl, $variable, $variable);
    }

    const LINE_INDENT = '$indent . ';

    
    private function flushIndent()
    {
        if (!$this->indentNextLine) {
            return '';
        }

        $this->indentNextLine = false;

        return self::LINE_INDENT;
    }
}
