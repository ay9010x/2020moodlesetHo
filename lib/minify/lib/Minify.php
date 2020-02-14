<?php

 

class Minify {
    
    const VERSION = '2.2.0';
    const TYPE_CSS = 'text/css';
    const TYPE_HTML = 'text/html';
            const TYPE_JS = 'application/x-javascript';
    const URL_DEBUG = 'http://code.google.com/p/minify/wiki/Debugging';
    
    
    public static $uploaderHoursBehind = 0;
    
    
    public static $importWarning = "/* See http://code.google.com/p/minify/wiki/CommonProblems#@imports_can_appear_in_invalid_locations_in_combined_CSS_files */\n";

    
    public static $isDocRootSet = false;

    
    public static function setCache($cache = '', $fileLocking = true)
    {
        if (is_string($cache)) {
            self::$_cache = new Minify_Cache_File($cache, $fileLocking);
        } else {
            self::$_cache = $cache;
        }
    }
    
    
    public static function serve($controller, $options = array())
    {
        if (! self::$isDocRootSet && 0 === stripos(PHP_OS, 'win')) {
            self::setDocRoot();
        }

        if (is_string($controller)) {
                        $class = 'Minify_Controller_' . $controller;
            $controller = new $class();
            
        }
        
                        $options = $controller->setupSources($options);
        $options = $controller->analyzeSources($options);
        self::$_options = $controller->mixInDefaultOptions($options);
        
                if (! $controller->sources) {
                        if (! self::$_options['quiet']) {
                self::_errorExit(self::$_options['badRequestHeader'], self::URL_DEBUG);
            } else {
                list(,$statusCode) = explode(' ', self::$_options['badRequestHeader']);
                return array(
                    'success' => false
                    ,'statusCode' => (int)$statusCode
                    ,'content' => ''
                    ,'headers' => array()
                );
            }
        }
        
        self::$_controller = $controller;
        
        if (self::$_options['debug']) {
            self::_setupDebug($controller->sources);
            self::$_options['maxAge'] = 0;
        }
        
                if (self::$_options['encodeOutput']) {
            $sendVary = true;
            if (self::$_options['encodeMethod'] !== null) {
                                $contentEncoding = self::$_options['encodeMethod'];
            } else {
                                                                                list(self::$_options['encodeMethod'], $contentEncoding) = HTTP_Encoder::getAcceptedEncoding(false, false);
                $sendVary = ! HTTP_Encoder::isBuggyIe();
            }
        } else {
            self::$_options['encodeMethod'] = '';         }
        
                $cgOptions = array(
            'lastModifiedTime' => self::$_options['lastModifiedTime']
            ,'isPublic' => self::$_options['isPublic']
            ,'encoding' => self::$_options['encodeMethod']
        );
        if (self::$_options['maxAge'] > 0) {
            $cgOptions['maxAge'] = self::$_options['maxAge'];
        } elseif (self::$_options['debug']) {
            $cgOptions['invalidate'] = true;
        }
        $cg = new HTTP_ConditionalGet($cgOptions);
        if ($cg->cacheIsValid) {
                        if (! self::$_options['quiet']) {
                $cg->sendHeaders();
                return;
            } else {
                return array(
                    'success' => true
                    ,'statusCode' => 304
                    ,'content' => ''
                    ,'headers' => $cg->getHeaders()
                );
            }
        } else {
                        $headers = $cg->getHeaders();
            unset($cg);
        }
        
        if (self::$_options['contentType'] === self::TYPE_CSS
            && self::$_options['rewriteCssUris']) {
            foreach($controller->sources as $key => $source) {
                if ($source->filepath 
                    && !isset($source->minifyOptions['currentDir'])
                    && !isset($source->minifyOptions['prependRelativePath'])
                ) {
                    $source->minifyOptions['currentDir'] = dirname($source->filepath);
                }
            }
        }
        
                if (null !== self::$_cache && ! self::$_options['debug']) {
                                                            $cacheId = self::_getCacheId();
            $fullCacheId = (self::$_options['encodeMethod'])
                ? $cacheId . '.gz'
                : $cacheId;
                        $cacheIsReady = self::$_cache->isValid($fullCacheId, self::$_options['lastModifiedTime']); 
            if ($cacheIsReady) {
                $cacheContentLength = self::$_cache->getSize($fullCacheId);    
            } else {
                                try {
                    $content = self::_combineMinify();
                } catch (Exception $e) {
                    self::$_controller->log($e->getMessage());
                    if (! self::$_options['quiet']) {
                        self::_errorExit(self::$_options['errorHeader'], self::URL_DEBUG);
                    }
                    throw $e;
                }
                self::$_cache->store($cacheId, $content);
                if (function_exists('gzencode') && self::$_options['encodeMethod']) {
                    self::$_cache->store($cacheId . '.gz', gzencode($content, self::$_options['encodeLevel']));
                }
            }
        } else {
                        $cacheIsReady = false;
            try {
                $content = self::_combineMinify();
            } catch (Exception $e) {
                self::$_controller->log($e->getMessage());
                if (! self::$_options['quiet']) {
                    self::_errorExit(self::$_options['errorHeader'], self::URL_DEBUG);
                }
                throw $e;
            }
        }
        if (! $cacheIsReady && self::$_options['encodeMethod']) {
                        $content = gzencode($content, self::$_options['encodeLevel']);
        }
        
                $headers['Content-Length'] = $cacheIsReady
            ? $cacheContentLength
            : ((function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2))
                ? mb_strlen($content, '8bit')
                : strlen($content)
            );
        $headers['Content-Type'] = self::$_options['contentTypeCharset']
            ? self::$_options['contentType'] . '; charset=' . self::$_options['contentTypeCharset']
            : self::$_options['contentType'];
        if (self::$_options['encodeMethod'] !== '') {
            $headers['Content-Encoding'] = $contentEncoding;
        }
        if (self::$_options['encodeOutput'] && $sendVary) {
            $headers['Vary'] = 'Accept-Encoding';
        }

        if (! self::$_options['quiet']) {
                        foreach ($headers as $name => $val) {
                header($name . ': ' . $val);
            }
            if ($cacheIsReady) {
                self::$_cache->display($fullCacheId);
            } else {
                echo $content;
            }
        } else {
            return array(
                'success' => true
                ,'statusCode' => 200
                ,'content' => $cacheIsReady
                    ? self::$_cache->fetch($fullCacheId)
                    : $content
                ,'headers' => $headers
            );
        }
    }
    
    
    public static function combine($sources, $options = array())
    {
        $cache = self::$_cache;
        self::$_cache = null;
        $options = array_merge(array(
            'files' => (array)$sources
            ,'quiet' => true
            ,'encodeMethod' => ''
            ,'lastModifiedTime' => 0
        ), $options);
        $out = self::serve('Files', $options);
        self::$_cache = $cache;
        return $out['content'];
    }
    
    
    public static function setDocRoot($docRoot = '')
    {
        self::$isDocRootSet = true;
        if ($docRoot) {
            $_SERVER['DOCUMENT_ROOT'] = $docRoot;
        } elseif (isset($_SERVER['SERVER_SOFTWARE'])
                  && 0 === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/')) {
            $_SERVER['DOCUMENT_ROOT'] = substr(
                $_SERVER['SCRIPT_FILENAME']
                ,0
                ,strlen($_SERVER['SCRIPT_FILENAME']) - strlen($_SERVER['SCRIPT_NAME']));
            $_SERVER['DOCUMENT_ROOT'] = rtrim($_SERVER['DOCUMENT_ROOT'], '\\');
        }
    }
    
    
    private static $_cache = null;
    
    
    protected static $_controller = null;
    
    
    protected static $_options = null;

    
    protected static function _errorExit($header, $url)
    {
        $url = htmlspecialchars($url, ENT_QUOTES);
        list(,$h1) = explode(' ', $header, 2);
        $h1 = htmlspecialchars($h1);
                list(, $code) = explode(' ', $header, 3);
        header($header, true, $code);
        header('Content-Type: text/html; charset=utf-8');
        echo "<h1>$h1</h1>";
        echo "<p>Please see <a href='$url'>$url</a>.</p>";
        exit;
    }

    
    protected static function _setupDebug($sources)
    {
        foreach ($sources as $source) {
            $source->minifier = array('Minify_Lines', 'minify');
            $id = $source->getId();
            $source->minifyOptions = array(
                'id' => (is_file($id) ? basename($id) : $id)
            );
        }
    }
    
    
    protected static function _combineMinify()
    {
        $type = self::$_options['contentType'];         
                        $implodeSeparator = ($type === self::TYPE_JS)
            ? "\n;"
            : '';
                                $defaultOptions = isset(self::$_options['minifierOptions'][$type])
            ? self::$_options['minifierOptions'][$type]
            : array();
                        $defaultMinifier = isset(self::$_options['minifiers'][$type])
            ? self::$_options['minifiers'][$type]
            : false;

                $content = array();
        $i = 0;
        $l = count(self::$_controller->sources);
        $groupToProcessTogether = array();
        $lastMinifier = null;
        $lastOptions = null;
        do {
                        $source = null;
            if ($i < $l) {
                $source = self::$_controller->sources[$i];
                
                $sourceContent = $source->getContent();

                                $minifier = (null !== $source->minifier)
                    ? $source->minifier
                    : $defaultMinifier;
                $options = (null !== $source->minifyOptions)
                    ? array_merge($defaultOptions, $source->minifyOptions)
                    : $defaultOptions;
            }
                        if ($i > 0                                               && (
                    ! $source                                            || $type === self::TYPE_CSS                          || $minifier !== $lastMinifier                       || $options !== $lastOptions)                    )
            {
                                $imploded = implode($implodeSeparator, $groupToProcessTogether);
                $groupToProcessTogether = array();
                if ($lastMinifier) {
                    try {
                        $content[] = call_user_func($lastMinifier, $imploded, $lastOptions);
                    } catch (Exception $e) {
                        throw new Exception("Exception in minifier: " . $e->getMessage());
                    }
                } else {
                    $content[] = $imploded;
                }
            }
                        if ($source) {
                $groupToProcessTogether[] = $sourceContent;
                $lastMinifier = $minifier;
                $lastOptions = $options;
            }
            $i++;
        } while ($source);

        $content = implode($implodeSeparator, $content);
        
        if ($type === self::TYPE_CSS && false !== strpos($content, '@import')) {
            $content = self::_handleCssImports($content);
        }
        
                if (self::$_options['postprocessorRequire']) {
            require_once self::$_options['postprocessorRequire'];
        }
        if (self::$_options['postprocessor']) {
            $content = call_user_func(self::$_options['postprocessor'], $content, $type);
        }
        return $content;
    }
    
    
    protected static function _getCacheId($prefix = 'minify')
    {
        $name = preg_replace('/[^a-zA-Z0-9\\.=_,]/', '', self::$_controller->selectionId);
        $name = preg_replace('/\\.+/', '.', $name);
        $name = substr($name, 0, 100 - 34 - strlen($prefix));
        $md5 = md5(serialize(array(
            Minify_Source::getDigest(self::$_controller->sources)
            ,self::$_options['minifiers'] 
            ,self::$_options['minifierOptions']
            ,self::$_options['postprocessor']
            ,self::$_options['bubbleCssImports']
            ,self::VERSION
        )));
        return "{$prefix}_{$name}_{$md5}";
    }
    
    
    protected static function _handleCssImports($css)
    {
        if (self::$_options['bubbleCssImports']) {
                        preg_match_all('/@import.*?;/', $css, $imports);
            $css = implode('', $imports[0]) . preg_replace('/@import.*?;/', '', $css);
        } else if ('' !== self::$importWarning) {
                        $noCommentCss = preg_replace('@/\\*[\\s\\S]*?\\*/@', '', $css);
            $lastImportPos = strrpos($noCommentCss, '@import');
            $firstBlockPos = strpos($noCommentCss, '{');
            if (false !== $lastImportPos
                && false !== $firstBlockPos
                && $firstBlockPos < $lastImportPos
            ) {
                                $css = self::$importWarning . $css;
            }
        }
        return $css;
    }
}
