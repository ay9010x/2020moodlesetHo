<?php



class Minify_ImportProcessor {

    public static $filesIncluded = array();

    public static function process($file)
    {
        self::$filesIncluded = array();
        self::$_isCss = (strtolower(substr($file, -4)) === '.css');
        $obj = new Minify_ImportProcessor(dirname($file));
        return $obj->_getContent($file);
    }

        private $_currentDir = null;

        private $_previewsDir = null;

        private $_importedContent = '';

    private static $_isCss = null;

    
    private function __construct($currentDir, $previewsDir = "")
    {
        $this->_currentDir = $currentDir;
        $this->_previewsDir = $previewsDir;
    }

    private function _getContent($file, $is_imported = false)
    {
        $file = realpath($file);
        if (! $file
            || in_array($file, self::$filesIncluded)
            || false === ($content = @file_get_contents($file))
        ) {
                        return '';
        }
        self::$filesIncluded[] = realpath($file);
        $this->_currentDir = dirname($file);

                if (pack("CCC",0xef,0xbb,0xbf) === substr($content, 0, 3)) {
            $content = substr($content, 3);
        }
                $content = str_replace("\r\n", "\n", $content);

                $content = preg_replace_callback(
            '/
                @import\\s+
                (?:url\\(\\s*)?      # maybe url(
                [\'"]?               # maybe quote
                (.*?)                # 1 = URI
                [\'"]?               # maybe end quote
                (?:\\s*\\))?         # maybe )
                ([a-zA-Z,\\s]*)?     # 2 = media list
                ;                    # end token
            /x'
            ,array($this, '_importCB')
            ,$content
        );

                if (self::$_isCss && $is_imported) {
                        $content = preg_replace_callback(
                '/url\\(\\s*([^\\)\\s]+)\\s*\\)/'
                ,array($this, '_urlCB')
                ,$content
            );
        }

        return $this->_importedContent . $content;
    }

    private function _importCB($m)
    {
        $url = $m[1];
        $mediaList = preg_replace('/\\s+/', '', $m[2]);

        if (strpos($url, '://') > 0) {
                        return self::$_isCss
                ? $m[0]
                : "/* Minify_ImportProcessor will not include remote content */";
        }
        if ('/' === $url[0]) {
                        $url = ltrim($url, '/');
            $file = realpath($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR
                . strtr($url, '/', DIRECTORY_SEPARATOR);
        } else {
                        $file = $this->_currentDir . DIRECTORY_SEPARATOR
                . strtr($url, '/', DIRECTORY_SEPARATOR);
        }
        $obj = new Minify_ImportProcessor(dirname($file), $this->_currentDir);
        $content = $obj->_getContent($file, true);
        if ('' === $content) {
                        return self::$_isCss
                ? $m[0]
                : "/* Minify_ImportProcessor could not fetch '{$file}' */";
        }
        return (!self::$_isCss || preg_match('@(?:^$|\\ball\\b)@', $mediaList))
            ? $content
            : "@media {$mediaList} {\n{$content}\n}\n";
    }

    private function _urlCB($m)
    {
                $quote = ($m[1][0] === "'" || $m[1][0] === '"')
            ? $m[1][0]
            : '';
        $url = ($quote === '')
            ? $m[1]
            : substr($m[1], 1, strlen($m[1]) - 2);
        if ('/' !== $url[0]) {
            if (strpos($url, '//') > 0) {
                            } else {
                                $path = $this->_currentDir
                    . DIRECTORY_SEPARATOR . strtr($url, '/', DIRECTORY_SEPARATOR);
                                $url = self::getPathDiff(realpath($this->_previewsDir), $path);
            }
        }
        return "url({$quote}{$url}{$quote})";
    }

    
    private function getPathDiff($from, $to, $ps = DIRECTORY_SEPARATOR)
    {
        $realFrom = $this->truepath($from);
        $realTo = $this->truepath($to);

        $arFrom = explode($ps, rtrim($realFrom, $ps));
        $arTo = explode($ps, rtrim($realTo, $ps));
        while (count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0]))
        {
            array_shift($arFrom);
            array_shift($arTo);
        }
        return str_pad("", count($arFrom) * 3, '..' . $ps) . implode($ps, $arTo);
    }

    
    function truepath($path)
    {
                $unipath = strlen($path) == 0 || $path{0} != '/';
                if (strpos($path, ':') === false && $unipath)
            $path = $this->_currentDir . DIRECTORY_SEPARATOR . $path;

                $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part)
                continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $path = implode(DIRECTORY_SEPARATOR, $absolutes);
                if (file_exists($path) && linkinfo($path) > 0)
            $path = readlink($path);
                $path = !$unipath ? '/' . $path : $path;
        return $path;
    }
}
