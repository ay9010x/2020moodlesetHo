<?php




class Mustache_Engine
{
    const VERSION        = '2.10.0';
    const SPEC_VERSION   = '1.1.2';

    const PRAGMA_FILTERS      = 'FILTERS';
    const PRAGMA_BLOCKS       = 'BLOCKS';
    const PRAGMA_ANCHORED_DOT = 'ANCHORED-DOT';

        private static $knownPragmas = array(
        self::PRAGMA_FILTERS      => true,
        self::PRAGMA_BLOCKS       => true,
        self::PRAGMA_ANCHORED_DOT => true,
    );

        private $templates = array();

        private $templateClassPrefix = '__Mustache_';
    private $cache;
    private $lambdaCache;
    private $cacheLambdaTemplates = false;
    private $loader;
    private $partialsLoader;
    private $helpers;
    private $escape;
    private $entityFlags = ENT_COMPAT;
    private $charset = 'UTF-8';
    private $logger;
    private $strictCallables = false;
    private $pragmas = array();

        private $tokenizer;
    private $parser;
    private $compiler;

    
    public function __construct(array $options = array())
    {
        if (isset($options['template_class_prefix'])) {
            $this->templateClassPrefix = $options['template_class_prefix'];
        }

        if (isset($options['cache'])) {
            $cache = $options['cache'];

            if (is_string($cache)) {
                $mode  = isset($options['cache_file_mode']) ? $options['cache_file_mode'] : null;
                $cache = new Mustache_Cache_FilesystemCache($cache, $mode);
            }

            $this->setCache($cache);
        }

        if (isset($options['cache_lambda_templates'])) {
            $this->cacheLambdaTemplates = (bool) $options['cache_lambda_templates'];
        }

        if (isset($options['loader'])) {
            $this->setLoader($options['loader']);
        }

        if (isset($options['partials_loader'])) {
            $this->setPartialsLoader($options['partials_loader']);
        }

        if (isset($options['partials'])) {
            $this->setPartials($options['partials']);
        }

        if (isset($options['helpers'])) {
            $this->setHelpers($options['helpers']);
        }

        if (isset($options['escape'])) {
            if (!is_callable($options['escape'])) {
                throw new Mustache_Exception_InvalidArgumentException('Mustache Constructor "escape" option must be callable');
            }

            $this->escape = $options['escape'];
        }

        if (isset($options['entity_flags'])) {
            $this->entityFlags = $options['entity_flags'];
        }

        if (isset($options['charset'])) {
            $this->charset = $options['charset'];
        }

        if (isset($options['logger'])) {
            $this->setLogger($options['logger']);
        }

        if (isset($options['strict_callables'])) {
            $this->strictCallables = $options['strict_callables'];
        }

        if (isset($options['pragmas'])) {
            foreach ($options['pragmas'] as $pragma) {
                if (!isset(self::$knownPragmas[$pragma])) {
                    throw new Mustache_Exception_InvalidArgumentException(sprintf('Unknown pragma: "%s".', $pragma));
                }
                $this->pragmas[$pragma] = true;
            }
        }
    }

    
    public function render($template, $context = array())
    {
        return $this->loadTemplate($template)->render($context);
    }

    
    public function getEscape()
    {
        return $this->escape;
    }

    
    public function getEntityFlags()
    {
        return $this->entityFlags;
    }

    
    public function getCharset()
    {
        return $this->charset;
    }

    
    public function getPragmas()
    {
        return array_keys($this->pragmas);
    }

    
    public function setLoader(Mustache_Loader $loader)
    {
        $this->loader = $loader;
    }

    
    public function getLoader()
    {
        if (!isset($this->loader)) {
            $this->loader = new Mustache_Loader_StringLoader();
        }

        return $this->loader;
    }

    
    public function setPartialsLoader(Mustache_Loader $partialsLoader)
    {
        $this->partialsLoader = $partialsLoader;
    }

    
    public function getPartialsLoader()
    {
        if (!isset($this->partialsLoader)) {
            $this->partialsLoader = new Mustache_Loader_ArrayLoader();
        }

        return $this->partialsLoader;
    }

    
    public function setPartials(array $partials = array())
    {
        if (!isset($this->partialsLoader)) {
            $this->partialsLoader = new Mustache_Loader_ArrayLoader();
        }

        if (!$this->partialsLoader instanceof Mustache_Loader_MutableLoader) {
            throw new Mustache_Exception_RuntimeException('Unable to set partials on an immutable Mustache Loader instance');
        }

        $this->partialsLoader->setTemplates($partials);
    }

    
    public function setHelpers($helpers)
    {
        if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new Mustache_Exception_InvalidArgumentException('setHelpers expects an array of helpers');
        }

        $this->getHelpers()->clear();

        foreach ($helpers as $name => $helper) {
            $this->addHelper($name, $helper);
        }
    }

    
    public function getHelpers()
    {
        if (!isset($this->helpers)) {
            $this->helpers = new Mustache_HelperCollection();
        }

        return $this->helpers;
    }

    
    public function addHelper($name, $helper)
    {
        $this->getHelpers()->add($name, $helper);
    }

    
    public function getHelper($name)
    {
        return $this->getHelpers()->get($name);
    }

    
    public function hasHelper($name)
    {
        return $this->getHelpers()->has($name);
    }

    
    public function removeHelper($name)
    {
        $this->getHelpers()->remove($name);
    }

    
    public function setLogger($logger = null)
    {
        if ($logger !== null && !($logger instanceof Mustache_Logger || is_a($logger, 'Psr\\Log\\LoggerInterface'))) {
            throw new Mustache_Exception_InvalidArgumentException('Expected an instance of Mustache_Logger or Psr\\Log\\LoggerInterface.');
        }

        if ($this->getCache()->getLogger() === null) {
            $this->getCache()->setLogger($logger);
        }

        $this->logger = $logger;
    }

    
    public function getLogger()
    {
        return $this->logger;
    }

    
    public function setTokenizer(Mustache_Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    
    public function getTokenizer()
    {
        if (!isset($this->tokenizer)) {
            $this->tokenizer = new Mustache_Tokenizer();
        }

        return $this->tokenizer;
    }

    
    public function setParser(Mustache_Parser $parser)
    {
        $this->parser = $parser;
    }

    
    public function getParser()
    {
        if (!isset($this->parser)) {
            $this->parser = new Mustache_Parser();
        }

        return $this->parser;
    }

    
    public function setCompiler(Mustache_Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    
    public function getCompiler()
    {
        if (!isset($this->compiler)) {
            $this->compiler = new Mustache_Compiler();
        }

        return $this->compiler;
    }

    
    public function setCache(Mustache_Cache $cache)
    {
        if (isset($this->logger) && $cache->getLogger() === null) {
            $cache->setLogger($this->getLogger());
        }

        $this->cache = $cache;
    }

    
    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->setCache(new Mustache_Cache_NoopCache());
        }

        return $this->cache;
    }

    
    protected function getLambdaCache()
    {
        if ($this->cacheLambdaTemplates) {
            return $this->getCache();
        }

        if (!isset($this->lambdaCache)) {
            $this->lambdaCache = new Mustache_Cache_NoopCache();
        }

        return $this->lambdaCache;
    }

    
    public function getTemplateClassName($source)
    {
        return $this->templateClassPrefix . md5(sprintf(
            'version:%s,escape:%s,entity_flags:%i,charset:%s,strict_callables:%s,pragmas:%s,source:%s',
            self::VERSION,
            isset($this->escape) ? 'custom' : 'default',
            $this->entityFlags,
            $this->charset,
            $this->strictCallables ? 'true' : 'false',
            implode(' ', $this->getPragmas()),
            $source
        ));
    }

    
    public function loadTemplate($name)
    {
        return $this->loadSource($this->getLoader()->load($name));
    }

    
    public function loadPartial($name)
    {
        try {
            if (isset($this->partialsLoader)) {
                $loader = $this->partialsLoader;
            } elseif (isset($this->loader) && !$this->loader instanceof Mustache_Loader_StringLoader) {
                $loader = $this->loader;
            } else {
                throw new Mustache_Exception_UnknownTemplateException($name);
            }

            return $this->loadSource($loader->load($name));
        } catch (Mustache_Exception_UnknownTemplateException $e) {
                        $this->log(
                Mustache_Logger::WARNING,
                'Partial not found: "{name}"',
                array('name' => $e->getTemplateName())
            );
        }
    }

    
    public function loadLambda($source, $delims = null)
    {
        if ($delims !== null) {
            $source = $delims . "\n" . $source;
        }

        return $this->loadSource($source, $this->getLambdaCache());
    }

    
    private function loadSource($source, Mustache_Cache $cache = null)
    {
        $className = $this->getTemplateClassName($source);

        if (!isset($this->templates[$className])) {
            if ($cache === null) {
                $cache = $this->getCache();
            }

            if (!class_exists($className, false)) {
                if (!$cache->load($className)) {
                    $compiled = $this->compile($source);
                    $cache->cache($className, $compiled);
                }
            }

            $this->log(
                Mustache_Logger::DEBUG,
                'Instantiating template: "{className}"',
                array('className' => $className)
            );

            $this->templates[$className] = new $className($this);
        }

        return $this->templates[$className];
    }

    
    private function tokenize($source)
    {
        return $this->getTokenizer()->scan($source);
    }

    
    private function parse($source)
    {
        $parser = $this->getParser();
        $parser->setPragmas($this->getPragmas());

        return $parser->parse($this->tokenize($source));
    }

    
    private function compile($source)
    {
        $tree = $this->parse($source);
        $name = $this->getTemplateClassName($source);

        $this->log(
            Mustache_Logger::INFO,
            'Compiling template to "{className}" class',
            array('className' => $name)
        );

        $compiler = $this->getCompiler();
        $compiler->setPragmas($this->getPragmas());

        return $compiler->compile($source, $tree, $name, isset($this->escape), $this->charset, $this->strictCallables, $this->entityFlags);
    }

    
    private function log($level, $message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $message, $context);
        }
    }
}
