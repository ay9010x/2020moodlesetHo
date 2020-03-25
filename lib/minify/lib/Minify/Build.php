<?php



class Minify_Build {
    
    
    public $lastModified = 0;
    
    
    public static $ampersand = '&amp;';
    
    
    public function uri($uri, $forceAmpersand = false) {
        $sep = ($forceAmpersand || strpos($uri, '?') !== false)
            ? self::$ampersand
            : '?';
        return "{$uri}{$sep}{$this->lastModified}";
    }

	
    public function __construct($sources) 
    {
        $max = 0;
        foreach ((array)$sources as $source) {
            if ($source instanceof Minify_Source) {
                $max = max($max, $source->lastModified);
            } elseif (is_string($source)) {
                if (0 === strpos($source, '//')) {
                    $source = $_SERVER['DOCUMENT_ROOT'] . substr($source, 1);
                }
                if (is_file($source)) {
                    $max = max($max, filemtime($source));
                }
            }
        }
        $this->lastModified = $max;
    }
}
