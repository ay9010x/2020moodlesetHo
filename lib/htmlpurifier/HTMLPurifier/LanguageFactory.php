<?php


class HTMLPurifier_LanguageFactory
{

    
    public $cache;

    
    public $keys = array('fallback', 'messages', 'errorNames');

    
    protected $validator;

    
    protected $dir;

    
    protected $mergeable_keys_map = array('messages' => true, 'errorNames' => true);

    
    protected $mergeable_keys_list = array();

    
    public static function instance($prototype = null)
    {
        static $instance = null;
        if ($prototype !== null) {
            $instance = $prototype;
        } elseif ($instance === null || $prototype == true) {
            $instance = new HTMLPurifier_LanguageFactory();
            $instance->setup();
        }
        return $instance;
    }

    
    public function setup()
    {
        $this->validator = new HTMLPurifier_AttrDef_Lang();
        $this->dir = HTMLPURIFIER_PREFIX . '/HTMLPurifier';
    }

    
    public function create($config, $context, $code = false)
    {
                if ($code === false) {
            $code = $this->validator->validate(
                $config->get('Core.Language'),
                $config,
                $context
            );
        } else {
            $code = $this->validator->validate($code, $config, $context);
        }
        if ($code === false) {
            $code = 'en';         }

        $pcode = str_replace('-', '_', $code);         static $depth = 0; 
        if ($code == 'en') {
            $lang = new HTMLPurifier_Language($config, $context);
        } else {
            $class = 'HTMLPurifier_Language_' . $pcode;
            $file  = $this->dir . '/Language/classes/' . $code . '.php';
            if (file_exists($file) || class_exists($class, false)) {
                $lang = new $class($config, $context);
            } else {
                                $raw_fallback = $this->getFallbackFor($code);
                $fallback = $raw_fallback ? $raw_fallback : 'en';
                $depth++;
                $lang = $this->create($config, $context, $fallback);
                if (!$raw_fallback) {
                    $lang->error = true;
                }
                $depth--;
            }
        }
        $lang->code = $code;
        return $lang;
    }

    
    public function getFallbackFor($code)
    {
        $this->loadLanguage($code);
        return $this->cache[$code]['fallback'];
    }

    
    public function loadLanguage($code)
    {
        static $languages_seen = array(); 
                if (isset($this->cache[$code])) {
            return;
        }

                $filename = $this->dir . '/Language/messages/' . $code . '.php';

                $fallback = ($code != 'en') ? 'en' : false;

                if (!file_exists($filename)) {
                        $filename = $this->dir . '/Language/messages/en.php';
            $cache = array();
        } else {
            include $filename;
            $cache = compact($this->keys);
        }

                if (!empty($fallback)) {

                        if (isset($languages_seen[$code])) {
                trigger_error(
                    'Circular fallback reference in language ' .
                    $code,
                    E_USER_ERROR
                );
                $fallback = 'en';
            }
            $language_seen[$code] = true;

                        $this->loadLanguage($fallback);
            $fallback_cache = $this->cache[$fallback];

                        foreach ($this->keys as $key) {
                if (isset($cache[$key]) && isset($fallback_cache[$key])) {
                    if (isset($this->mergeable_keys_map[$key])) {
                        $cache[$key] = $cache[$key] + $fallback_cache[$key];
                    } elseif (isset($this->mergeable_keys_list[$key])) {
                        $cache[$key] = array_merge($fallback_cache[$key], $cache[$key]);
                    }
                } else {
                    $cache[$key] = $fallback_cache[$key];
                }
            }
        }

                $this->cache[$code] = $cache;
        return;
    }
}

