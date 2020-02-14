<?php

class Horde_Support_Inflector
{
    
    protected $_cache = array();

    
    protected $_pluralizationRules = array(
        '/move$/i' => 'moves',
        '/sex$/i' => 'sexes',
        '/child$/i' => 'children',
        '/man$/i' => 'men',
        '/foot$/i' => 'feet',
        '/person$/i' => 'people',
        '/(quiz)$/i' => '$1zes',
        '/^(ox)$/i' => '$1en',
        '/(m|l)ouse$/i' => '$1ice',
        '/(matr|vert|ind)ix|ex$/i' => '$1ices',
        '/(x|ch|ss|sh)$/i' => '$1es',
        '/([^aeiouy]|qu)ies$/i' => '$1y',
        '/([^aeiouy]|qu)y$/i' => '$1ies',
        '/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
        '/sis$/i' => 'ses',
        '/([ti])um$/i' => '$1a',
        '/(buffal|tomat)o$/i' => '$1oes',
        '/(bu)s$/i' => '$1ses',
        '/(alias|status)$/i' => '$1es',
        '/(octop|vir)us$/i' => '$1i',
        '/(ax|test)is$/i' => '$1es',
        '/s$/i' => 's',
        '/$/' => 's',
    );

    
    protected $_singularizationRules = array(
        '/cookies$/i' => 'cookie',
        '/moves$/i' => 'move',
        '/sexes$/i' => 'sex',
        '/children$/i' => 'child',
        '/men$/i' => 'man',
        '/feet$/i' => 'foot',
        '/people$/i' => 'person',
        '/databases$/i'=> 'database',
        '/(quiz)zes$/i' => '\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias|status)es$/i' => '\1',
        '/([octop|vir])i$/i' => '\1us',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/(bus)es$/i' => '\1',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1ovie',
        '/(s)eries$/i' => '\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/([^f])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(n)ews$/i' => '\1ews',
        '/(.*)s$/i' => '\1',
    );

    
    protected $_uncountables = array(
        'aircraft',
        'cannon',
        'deer',
        'equipment',
        'fish',
        'information',
        'money',
        'moose',
        'rice',
        'series',
        'sheep',
        'species',
        'swine',
    );

    
    public function __construct()
    {
        $this->_uncountables_keys = array_flip($this->_uncountables);
    }

    
    public function uncountable($word)
    {
        $this->_uncountables[] = $word;
        $this->_uncountables_keys[$word] = true;
    }

    
    public function pluralize($word)
    {
        if ($plural = $this->getCache($word, 'pluralize')) {
            return $plural;
        }

        if (isset($this->_uncountables_keys[$word])) {
            return $word;
        }

        foreach ($this->_pluralizationRules as $regexp => $replacement) {
            $plural = preg_replace($regexp, $replacement, $word, -1, $matches);
            if ($matches > 0) {
                return $this->setCache($word, 'pluralize', $plural);
            }
        }

        return $this->setCache($word, 'pluralize', $word);
    }

    
    public function singularize($word)
    {
        if ($singular = $this->getCache($word, 'singularize')) {
            return $singular;
        }

        if (isset($this->_uncountables_keys[$word])) {
            return $word;
        }

        foreach ($this->_singularizationRules as $regexp => $replacement) {
            $singular = preg_replace($regexp, $replacement, $word, -1, $matches);
            if ($matches > 0) {
                return $this->setCache($word, 'singularize', $singular);
            }
        }

        return $this->setCache($word, 'singularize', $word);
    }

    
    public function camelize($word, $firstLetter = 'upper')
    {
        if ($camelized = $this->getCache($word, 'camelize' . $firstLetter)) {
            return $camelized;
        }

        $camelized = $word;
        if (strtolower($camelized) != $camelized &&
            strpos($camelized, '_') !== false) {
            $camelized = str_replace('_', '/', $camelized);
        }
        if (strpos($camelized, '/') !== false) {
            $camelized = str_replace('/', '/ ', $camelized);
        }
        if (strpos($camelized, '_') !== false) {
            $camelized = strtr($camelized, '_', ' ');
        }

        $camelized = str_replace(' ', '', ucwords($camelized));

        if ($firstLetter == 'lower') {
            $parts = array();
            foreach (explode('/', $camelized) as $part) {
                $part[0] = strtolower($part[0]);
                $parts[] = $part;
            }
            $camelized = implode('/', $parts);
        }

        return $this->setCache($word, 'camelize' . $firstLetter, $camelized);
    }

    
    public function titleize($word)
    {
        throw new Exception('not implemented yet');
    }

    
    public function underscore($camelCasedWord)
    {
        $word = $camelCasedWord;
        if ($result = $this->getCache($word, 'underscore')) {
            return $result;
        }
        $result = strtolower(preg_replace('/([a-z])([A-Z])/', "\${1}_\${2}", $word));
        return $this->setCache($word, 'underscore', $result);
    }

    
    public function dasherize($underscoredWord)
    {
        if ($result = $this->getCache($underscoredWord, 'dasherize')) {
            return $result;
        }

        $result = str_replace('_', '-', $this->underscore($underscoredWord));
        return $this->setCache($underscoredWord, 'dasherize', $result);
    }

    
    public function humanize($lowerCaseAndUnderscoredWord)
    {
        $word = $lowerCaseAndUnderscoredWord;
        if ($result = $this->getCache($word, 'humanize')) {
            return $result;
        }

        $result = ucfirst(str_replace('_', ' ', $this->underscore($word)));
        if (substr($result, -3, 3) == ' id') {
            $result = str_replace(' id', '', $result);
        }
        return $this->setCache($word, 'humanize', $result);
    }

    
    public function demodulize($classNameInModule)
    {
        $result = explode('_', $classNameInModule);
        return array_pop($result);
    }

    
    public function tableize($className)
    {
        if ($result = $this->getCache($className, 'tableize')) {
            return $result;
        }

        $result = $this->pluralize($this->underscore($className));
        $result = str_replace('/', '_', $result);
        return $this->setCache($className, 'tableize', $result);
    }

    
    public function classify($tableName)
    {
        if ($result = $this->getCache($tableName, 'classify')) {
            return $result;
        }
        $result = $this->camelize($this->singularize($tableName));

                $result = str_replace('/', '_', $result);
        return $this->setCache($tableName, 'classify', $result);
    }

    
    public function foreignKey($className, $separateClassNameAndIdWithUnderscore = true)
    {
        throw new Exception('not implemented yet');
    }

    
    public function ordinalize($number)
    {
        throw new Exception('not implemented yet');
    }

    
    public function clearCache()
    {
        $this->_cache = array();
    }

    
    public function getCache($word, $rule)
    {
        return isset($this->_cache[$word . '|' . $rule]) ?
            $this->_cache[$word . '|' . $rule] : false;
    }

    
    public function setCache($word, $rule, $value)
    {
        $this->_cache[$word . '|' . $rule] = $value;
        return $value;
    }
}
