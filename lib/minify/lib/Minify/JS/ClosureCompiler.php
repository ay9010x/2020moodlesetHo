<?php



class Minify_JS_ClosureCompiler {

    
    const OPTION_MAX_BYTES = 'maxBytes';

    
    const OPTION_ADDITIONAL_OPTIONS = 'additionalParams';

    
    const OPTION_FALLBACK_FUNCTION = 'fallbackFunc';

    
    const OPTION_COMPILER_URL = 'compilerUrl';

    
    const DEFAULT_MAX_BYTES = 200000;

    
    private static $DEFAULT_OPTIONS = array(
        'output_format' => 'text',
        'compilation_level' => 'SIMPLE_OPTIMIZATIONS'
    );

    
    protected $serviceUrl = 'http://closure-compiler.appspot.com/compile';

    
    protected $maxBytes = self::DEFAULT_MAX_BYTES;

    
    protected $additionalOptions = array();

    
    protected $fallbackMinifier = array('JSMin', 'minify');

    
    public static function minify($js, array $options = array())
    {
        $obj = new self($options);
        return $obj->min($js);
    }

    
    public function __construct(array $options = array())
    {
        if (isset($options[self::OPTION_FALLBACK_FUNCTION])) {
            $this->fallbackMinifier = $options[self::OPTION_FALLBACK_FUNCTION];
        }
        if (isset($options[self::OPTION_COMPILER_URL])) {
            $this->serviceUrl = $options[self::OPTION_COMPILER_URL];
        }
        if (isset($options[self::OPTION_ADDITIONAL_OPTIONS]) && is_array($options[self::OPTION_ADDITIONAL_OPTIONS])) {
            $this->additionalOptions = $options[self::OPTION_ADDITIONAL_OPTIONS];
        }
        if (isset($options[self::OPTION_MAX_BYTES])) {
            $this->maxBytes = (int) $options[self::OPTION_MAX_BYTES];
        }
    }

    
    public function min($js)
    {
        $postBody = $this->buildPostBody($js);

        if ($this->maxBytes > 0) {
            $bytes = (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2))
                ? mb_strlen($postBody, '8bit')
                : strlen($postBody);
            if ($bytes > $this->maxBytes) {
                throw new Minify_JS_ClosureCompiler_Exception(
                    'POST content larger than ' . $this->maxBytes . ' bytes'
                );
            }
        }

        $response = $this->getResponse($postBody);

        if (preg_match('/^Error\(\d\d?\):/', $response)) {
            if (is_callable($this->fallbackMinifier)) {
                                $response = "/* Received errors from Closure Compiler API:\n$response"
                          . "\n(Using fallback minifier)\n*/\n";
                $response .= call_user_func($this->fallbackMinifier, $js);
            } else {
                throw new Minify_JS_ClosureCompiler_Exception($response);
            }
        }

        if ($response === '') {
            $errors = $this->getResponse($this->buildPostBody($js, true));
            throw new Minify_JS_ClosureCompiler_Exception($errors);
        }

        return $response;
    }

    
    protected function getResponse($postBody)
    {
        $allowUrlFopen = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));

        if ($allowUrlFopen) {
            $contents = file_get_contents($this->serviceUrl, false, stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\nConnection: close\r\n",
                    'content' => $postBody,
                    'max_redirects' => 0,
                    'timeout' => 15,
                )
            )));
        } elseif (defined('CURLOPT_POST')) {
            $ch = curl_init($this->serviceUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            $contents = curl_exec($ch);
            curl_close($ch);
        } else {
            throw new Minify_JS_ClosureCompiler_Exception(
               "Could not make HTTP request: allow_url_open is false and cURL not available"
            );
        }

        if (false === $contents) {
            throw new Minify_JS_ClosureCompiler_Exception(
               "No HTTP response from server"
            );
        }

        return trim($contents);
    }

    
    protected function buildPostBody($js, $returnErrors = false)
    {
        return http_build_query(
            array_merge(
                self::$DEFAULT_OPTIONS,
                $this->additionalOptions,
                array(
                    'js_code' => $js,
                    'output_info' => ($returnErrors ? 'errors' : 'compiled_code')
                )
            ),
            null,
            '&'
        );
    }
}

class Minify_JS_ClosureCompiler_Exception extends Exception {}
