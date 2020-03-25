<?php






class HTMLPurifier
{

    
    public $version = '4.7.0';

    
    const VERSION = '4.7.0';

    
    public $config;

    
    private $filters = array();

    
    private static $instance;

    
    protected $strategy;

    
    protected $generator;

    
    public $context;

    
    public function __construct($config = null)
    {
        $this->config = HTMLPurifier_Config::create($config);
        $this->strategy = new HTMLPurifier_Strategy_Core();
    }

    
    public function addFilter($filter)
    {
        trigger_error(
            'HTMLPurifier->addFilter() is deprecated, use configuration directives' .
            ' in the Filter namespace or Filter.Custom',
            E_USER_WARNING
        );
        $this->filters[] = $filter;
    }

    
    public function purify($html, $config = null)
    {
                $config = $config ? HTMLPurifier_Config::create($config) : $this->config;

                        $lexer = HTMLPurifier_Lexer::create($config);

        $context = new HTMLPurifier_Context();

                $this->generator = new HTMLPurifier_Generator($config, $context);
        $context->register('Generator', $this->generator);

                if ($config->get('Core.CollectErrors')) {
                        $language_factory = HTMLPurifier_LanguageFactory::instance();
            $language = $language_factory->create($config, $context);
            $context->register('Locale', $language);

            $error_collector = new HTMLPurifier_ErrorCollector($context);
            $context->register('ErrorCollector', $error_collector);
        }

                        $id_accumulator = HTMLPurifier_IDAccumulator::build($config, $context);
        $context->register('IDAccumulator', $id_accumulator);

        $html = HTMLPurifier_Encoder::convertToUTF8($html, $config, $context);

                $filter_flags = $config->getBatch('Filter');
        $custom_filters = $filter_flags['Custom'];
        unset($filter_flags['Custom']);
        $filters = array();
        foreach ($filter_flags as $filter => $flag) {
            if (!$flag) {
                continue;
            }
            if (strpos($filter, '.') !== false) {
                continue;
            }
            $class = "HTMLPurifier_Filter_$filter";
            $filters[] = new $class;
        }
        foreach ($custom_filters as $filter) {
                        $filters[] = $filter;
        }
        $filters = array_merge($filters, $this->filters);
        
        for ($i = 0, $filter_size = count($filters); $i < $filter_size; $i++) {
            $html = $filters[$i]->preFilter($html, $config, $context);
        }

                $html =
            $this->generator->generateFromTokens(
                                $this->strategy->execute(
                                        $lexer->tokenizeHTML(
                                                $html,
                        $config,
                        $context
                    ),
                    $config,
                    $context
                )
            );

        for ($i = $filter_size - 1; $i >= 0; $i--) {
            $html = $filters[$i]->postFilter($html, $config, $context);
        }

        $html = HTMLPurifier_Encoder::convertFromUTF8($html, $config, $context);
        $this->context =& $context;
        return $html;
    }

    
    public function purifyArray($array_of_html, $config = null)
    {
        $context_array = array();
        foreach ($array_of_html as $key => $html) {
            $array_of_html[$key] = $this->purify($html, $config);
            $context_array[$key] = $this->context;
        }
        $this->context = $context_array;
        return $array_of_html;
    }

    
    public static function instance($prototype = null)
    {
        if (!self::$instance || $prototype) {
            if ($prototype instanceof HTMLPurifier) {
                self::$instance = $prototype;
            } elseif ($prototype) {
                self::$instance = new HTMLPurifier($prototype);
            } else {
                self::$instance = new HTMLPurifier();
            }
        }
        return self::$instance;
    }

    
    public static function getInstance($prototype = null)
    {
        return HTMLPurifier::instance($prototype);
    }
}

