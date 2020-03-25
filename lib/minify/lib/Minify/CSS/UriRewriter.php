<?php



class Minify_CSS_UriRewriter {
    
    
    public static $debugText = '';
    
    
    public static function rewrite($css, $currentDir, $docRoot = null, $symlinks = array()) 
    {
        self::$_docRoot = self::_realpath(
            $docRoot ? $docRoot : $_SERVER['DOCUMENT_ROOT']
        );
        self::$_currentDir = self::_realpath($currentDir);
        self::$_symlinks = array();
        
                foreach ($symlinks as $link => $target) {
            $link = ($link === '//')
                ? self::$_docRoot
                : str_replace('//', self::$_docRoot . '/', $link);
            $link = strtr($link, '/', DIRECTORY_SEPARATOR);
            self::$_symlinks[$link] = self::_realpath($target);
        }
        
        self::$debugText .= "docRoot    : " . self::$_docRoot . "\n"
                          . "currentDir : " . self::$_currentDir . "\n";
        if (self::$_symlinks) {
            self::$debugText .= "symlinks : " . var_export(self::$_symlinks, 1) . "\n";
        }
        self::$debugText .= "\n";
        
        $css = self::_trimUrls($css);
        
                $css = preg_replace_callback('/@import\\s+([\'"])(.*?)[\'"]/'
            ,array(self::$className, '_processUriCB'), $css);
        $css = preg_replace_callback('/url\\(\\s*([\'"](.*?)[\'"]|[^\\)\\s]+)\\s*\\)/'
            ,array(self::$className, '_processUriCB'), $css);

        return $css;
    }
    
    
    public static function prepend($css, $path)
    {
        self::$_prependPath = $path;
        
        $css = self::_trimUrls($css);
        
                $css = preg_replace_callback('/@import\\s+([\'"])(.*?)[\'"]/'
            ,array(self::$className, '_processUriCB'), $css);
        $css = preg_replace_callback('/url\\(\\s*([\'"](.*?)[\'"]|[^\\)\\s]+)\\s*\\)/'
            ,array(self::$className, '_processUriCB'), $css);

        self::$_prependPath = null;
        return $css;
    }
    
    
    public static function rewriteRelative($uri, $realCurrentDir, $realDocRoot, $symlinks = array())
    {
                $path = strtr($realCurrentDir, '/', DIRECTORY_SEPARATOR)  
            . DIRECTORY_SEPARATOR . strtr($uri, '/', DIRECTORY_SEPARATOR);
        
        self::$debugText .= "file-relative URI  : {$uri}\n"
                          . "path prepended     : {$path}\n";
        
                foreach ($symlinks as $link => $target) {
            if (0 === strpos($path, $target)) {
                                $path = $link . substr($path, strlen($target));
                
                self::$debugText .= "symlink unresolved : {$path}\n";
                
                break;
            }
        }
                $path = substr($path, strlen($realDocRoot));
        
        self::$debugText .= "docroot stripped   : {$path}\n";
        
                $uri = strtr($path, '/\\', '//');
        $uri = self::removeDots($uri);
      
        self::$debugText .= "traversals removed : {$uri}\n\n";
        
        return $uri;
    }

    
    public static function removeDots($uri)
    {
        $uri = str_replace('/./', '/', $uri);
                do {
            $uri = preg_replace('@/[^/]+/\\.\\./@', '/', $uri, 1, $changed);
        } while ($changed);
        return $uri;
    }
    
    
    protected static $className = 'Minify_CSS_UriRewriter';

    
    protected static function _realpath($path)
    {
        $realPath = realpath($path);
        if ($realPath !== false) {
            $path = $realPath;
        }
        return rtrim($path, '/\\');
    }

    
    private static $_currentDir = '';

    
    private static $_docRoot = '';

    
    private static $_symlinks = array();

    
    private static $_prependPath = null;

    
    private static function _trimUrls($css)
    {
        return preg_replace('/
            url\\(      # url(
            \\s*
            ([^\\)]+?)  # 1 = URI (assuming does not contain ")")
            \\s*
            \\)         # )
        /x', 'url($1)', $css);
    }

    
    private static function _processUriCB($m)
    {
                $isImport = ($m[0][0] === '@');
                if ($isImport) {
            $quoteChar = $m[1];
            $uri = $m[2];
        } else {
                        $quoteChar = ($m[1][0] === "'" || $m[1][0] === '"')
                ? $m[1][0]
                : '';
            $uri = ($quoteChar === '')
                ? $m[1]
                : substr($m[1], 1, strlen($m[1]) - 2);
        }
                if (!preg_match('~^(/|[a-z]+\:)~', $uri)) {
                        if (self::$_prependPath === null) {
                $uri = self::rewriteRelative($uri, self::$_currentDir, self::$_docRoot, self::$_symlinks);
            } else {
                $uri = self::$_prependPath . $uri;
                if ($uri[0] === '/') {
                    $root = '';
                    $rootRelative = $uri;
                    $uri = $root . self::removeDots($rootRelative);
                } elseif (preg_match('@^((https?\:)?//([^/]+))/@', $uri, $m) && (false !== strpos($m[3], '.'))) {
                    $root = $m[1];
                    $rootRelative = substr($uri, strlen($root));
                    $uri = $root . self::removeDots($rootRelative);
                }
            }
        }
        return $isImport
            ? "@import {$quoteChar}{$uri}{$quoteChar}"
            : "url({$quoteChar}{$uri}{$quoteChar})";
    }
}
