<?php



namespace Html2Text;

class Html2Text
{
    const ENCODING = 'UTF-8';

    protected $htmlFuncFlags;

    
    protected $html;

    
    protected $text;

    
    protected $search = array(
        "/\r/",                                                   "/[\n\t]+/",                                              '/<head\b[^>]*>.*?<\/head>/i',                            '/<script\b[^>]*>.*?<\/script>/i',                        '/<style\b[^>]*>.*?<\/style>/i',                          '/<i\b[^>]*>(.*?)<\/i>/i',                                '/<em\b[^>]*>(.*?)<\/em>/i',                              '/(<ul\b[^>]*>|<\/ul>)/i',                                '/(<ol\b[^>]*>|<\/ol>)/i',                                '/(<dl\b[^>]*>|<\/dl>)/i',                                '/<li\b[^>]*>(.*?)<\/li>/i',                              '/<dd\b[^>]*>(.*?)<\/dd>/i',                              '/<dt\b[^>]*>(.*?)<\/dt>/i',                              '/<li\b[^>]*>/i',                                         '/<hr\b[^>]*>/i',                                         '/<div\b[^>]*>/i',                                        '/(<table\b[^>]*>|<\/table>)/i',                          '/(<tr\b[^>]*>|<\/tr>)/i',                                '/<td\b[^>]*>(.*?)<\/td>/i',                              '/<span class="_html2text_ignore">.+?<\/span>/i',         '/<(img)\b[^>]*alt=\"([^>"]+)\"[^>]*>/i',             );

    
    protected $replace = array(
        '',                                      ' ',                                     '',                                      '',                                      '',                                      '_\\1_',                                 '_\\1_',                                 "\n\n",                                  "\n\n",                                  "\n\n",                                  "\t* \\1\n",                             " \\1\n",                                "\t* \\1",                               "\n\t* ",                                "\n-------------------------\n",         "<div>\n",                               "\n\n",                                  "\n",                                    "\t\t\\1\n",                             "",                                      '[\\2]',                             );

    
    protected $entSearch = array(
        '/&#153;/i',                                             '/&#151;/i',                                             '/&(amp|#38);/i',                                        '/[ ]{2,}/',                                         );

    
    protected $entReplace = array(
        '™',                 '—',                 '|+|amp|+|',         ' ',             );

    
    protected $callbackSearch = array(
        '/<(h)[123456]( [^>]*)?>(.*?)<\/h[123456]>/i',                   '/[ ]*<(p)( [^>]*)?>(.*?)<\/p>[ ]*/si',                          '/<(br)[^>]*>[ ]*/i',                                            '/<(b)( [^>]*)?>(.*?)<\/b>/i',                                   '/<(strong)( [^>]*)?>(.*?)<\/strong>/i',                         '/<(th)( [^>]*)?>(.*?)<\/th>/i',                                 '/<(a) [^>]*href=("|\')([^"\']+)\2([^>]*)>(.*?)<\/a>/i'      );

    
    protected $preSearch = array(
        "/\n/",
        "/\t/",
        '/ /',
        '/<pre[^>]*>/',
        '/<\/pre>/'
    );

    
    protected $preReplace = array(
        '<br>',
        '&nbsp;&nbsp;&nbsp;&nbsp;',
        '&nbsp;',
        '',
        '',
    );

    
    protected $preContent = '';

    
    protected $baseurl = '';

    
    protected $converted = false;

    
    protected $linkList = array();

    
    protected $options = array(
        'do_links' => 'inline',                                                                                                                                 
        'width' => 70,                                                                              );

    private function legacyConstruct($html = '', $fromFile = false, array $options = array())
    {
        $this->set_html($html, $fromFile);
        $this->options = array_merge($this->options, $options);
    }

    
    public function __construct($html = '', $options = array())
    {
                if (!is_array($options)) {
            return call_user_func_array(array($this, 'legacyConstruct'), func_get_args());
        }

        $this->html = $html;
        $this->options = array_merge($this->options, $options);
        $this->htmlFuncFlags = (PHP_VERSION_ID < 50400)
            ? ENT_COMPAT
            : ENT_COMPAT | ENT_HTML5;
    }

    
    public function setHtml($html)
    {
        $this->html = $html;
        $this->converted = false;
    }

    
    public function set_html($html, $from_file = false)
    {
        if ($from_file) {
            throw new \InvalidArgumentException("Argument from_file no longer supported");
        }

        return $this->setHtml($html);
    }

    
    public function getText()
    {
        if (!$this->converted) {
            $this->convert();
        }

        return $this->text;
    }

    
    public function get_text()
    {
        return $this->getText();
    }

    
    public function print_text()
    {
        print $this->getText();
    }

    
    public function p()
    {
        return $this->print_text();
    }

    
    public function setBaseUrl($baseurl)
    {
        $this->baseurl = $baseurl;
    }

    
    public function set_base_url($baseurl)
    {
        return $this->setBaseUrl($baseurl);
    }

    protected function convert()
    {
       $origEncoding = mb_internal_encoding();
       mb_internal_encoding(self::ENCODING);

       $this->doConvert();

       mb_internal_encoding($origEncoding);
    }

    protected function doConvert()
    {
        $this->linkList = array();

        $text = trim($this->html);

        $this->converter($text);

        if ($this->linkList) {
            $text .= "\n\nLinks:\n------\n";
            foreach ($this->linkList as $i => $url) {
                $text .= '[' . ($i + 1) . '] ' . $url . "\n";
            }
        }

        $this->text = $text;

        $this->converted = true;
    }

    protected function converter(&$text)
    {
        $this->convertBlockquotes($text);
        $this->convertPre($text);
        $text = preg_replace($this->search, $this->replace, $text);
        $text = preg_replace_callback($this->callbackSearch, array($this, 'pregCallback'), $text);
        $text = strip_tags($text);
        $text = preg_replace($this->entSearch, $this->entReplace, $text);
        $text = html_entity_decode($text, $this->htmlFuncFlags, self::ENCODING);

                $text = preg_replace('/&([a-zA-Z0-9]{2,6}|#[0-9]{2,4});/', '', $text);

                        $text = str_replace('|+|amp|+|', '&', $text);

                $text = preg_replace("/\n\s+\n/", "\n\n", $text);
        $text = preg_replace("/[\n]{3,}/", "\n\n", $text);

                $text = ltrim($text, "\n");

        if ($this->options['width'] > 0) {
            $text = wordwrap($text, $this->options['width']);
        }
    }

    
    protected function buildlinkList($link, $display, $linkOverride = null)
    {
        $linkMethod = ($linkOverride) ? $linkOverride : $this->options['do_links'];
        if ($linkMethod == 'none') {
            return $display;
        }

                if (preg_match('!^(javascript:|mailto:|#)!i', $link)) {
            return $display;
        }

        if (preg_match('!^([a-z][a-z0-9.+-]+:)!i', $link)) {
            $url = $link;
        } else {
            $url = $this->baseurl;
            if (mb_substr($link, 0, 1) != '/') {
                $url .= '/';
            }
            $url .= $link;
        }

        if ($linkMethod == 'table') {
            if (($index = array_search($url, $this->linkList)) === false) {
                $index = count($this->linkList);
                $this->linkList[] = $url;
            }

            return $display . ' [' . ($index + 1) . ']';
        } elseif ($linkMethod == 'nextline') {
            return $display . "\n[" . $url . ']';
        } elseif ($linkMethod == 'bbcode') {
            return sprintf('[url=%s]%s[/url]', $url, $display);
        } else {             return $display . ' [' . $url . ']';
        }
    }

    protected function convertPre(&$text)
    {
                while (preg_match('/<pre[^>]*>(.*)<\/pre>/ismU', $text, $matches)) {
                        $this->preContent = preg_replace('/(<br\b[^>]*>)/i', "\n", $matches[1]);

                        $this->preContent = preg_replace_callback(
                $this->callbackSearch,
                array($this, 'pregCallback'),
                $this->preContent
            );

                        $this->preContent = sprintf(
                '<div><br>%s<br></div>',
                preg_replace($this->preSearch, $this->preReplace, $this->preContent)
            );

                        $text = preg_replace_callback(
                '/<pre[^>]*>.*<\/pre>/ismU',
                array($this, 'pregPreCallback'),
                $text,
                1
            );

                        $this->preContent = '';
        }
    }

    
    protected function convertBlockquotes(&$text)
    {
        if (preg_match_all('/<\/*blockquote[^>]*>/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
            $originalText = $text;
            $start = 0;
            $taglen = 0;
            $level = 0;
            $diff = 0;
            foreach ($matches[0] as $m) {
                $m[1] = mb_strlen(substr($originalText, 0, $m[1]));
                if ($m[0][0] == '<' && $m[0][1] == '/') {
                    $level--;
                    if ($level < 0) {
                        $level = 0;                     } elseif ($level > 0) {
                                            } else {
                        $end = $m[1];
                        $len = $end - $taglen - $start;
                                                $body = mb_substr($text, $start + $taglen - $diff, $len);

                                                $pWidth = $this->options['width'];
                        if ($this->options['width'] > 0) $this->options['width'] -= 2;
                                                $body = trim($body);
                        $this->converter($body);
                                                $body = preg_replace('/((^|\n)>*)/', '\\1> ', trim($body));
                        $body = '<pre>' . htmlspecialchars($body, $this->htmlFuncFlags, self::ENCODING) . '</pre>';
                                                $this->options['width'] = $pWidth;
                                                $text = mb_substr($text, 0, $start - $diff)
                            . $body
                            . mb_substr($text, $end + mb_strlen($m[0]) - $diff);

                        $diff += $len + $taglen + mb_strlen($m[0]) - mb_strlen($body);
                        unset($body);
                    }
                } else {
                    if ($level == 0) {
                        $start = $m[1];
                        $taglen = mb_strlen($m[0]);
                    }
                    $level++;
                }
            }
        }
    }

    
    protected function pregCallback($matches)
    {
        switch (mb_strtolower($matches[1])) {
            case 'p':
                                $para = str_replace("\n", " ", $matches[3]);

                                $para = trim($para);

                                return "\n" . $para . "\n";
            case 'br':
                return "\n";
            case 'b':
            case 'strong':
                return $this->toupper($matches[3]);
            case 'th':
                return $this->toupper("\t\t" . $matches[3] . "\n");
            case 'h':
                return $this->toupper("\n\n" . $matches[3] . "\n\n");
            case 'a':
                                $linkOverride = null;
                if (preg_match('/_html2text_link_(\w+)/', $matches[4], $linkOverrideMatch)) {
                    $linkOverride = $linkOverrideMatch[1];
                }
                                $url = str_replace(' ', '', $matches[3]);

                return $this->buildlinkList($url, $matches[5], $linkOverride);
        }

        return '';
    }

    
    protected function pregPreCallback( $matches)
    {
        return $this->preContent;
    }

    
    protected function toupper($str)
    {
                $chunks = preg_split('/(<[^>]*>)/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

                foreach ($chunks as $i => $chunk) {
            if ($chunk[0] != '<') {
                $chunks[$i] = $this->strtoupper($chunk);
            }
        }

        return implode($chunks);
    }

    
    protected function strtoupper($str)
    {
        $str = html_entity_decode($str, $this->htmlFuncFlags, self::ENCODING);
        $str = mb_strtoupper($str);
        $str = htmlspecialchars($str, $this->htmlFuncFlags, self::ENCODING);

        return $str;
    }
}
