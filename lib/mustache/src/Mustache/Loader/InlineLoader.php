<?php




class Mustache_Loader_InlineLoader implements Mustache_Loader
{
    protected $fileName;
    protected $offset;
    protected $templates;

    
    public function __construct($fileName, $offset)
    {
        if (!is_file($fileName)) {
            throw new Mustache_Exception_InvalidArgumentException('InlineLoader expects a valid filename.');
        }

        if (!is_int($offset) || $offset < 0) {
            throw new Mustache_Exception_InvalidArgumentException('InlineLoader expects a valid file offset.');
        }

        $this->fileName = $fileName;
        $this->offset   = $offset;
    }

    
    public function load($name)
    {
        $this->loadTemplates();

        if (!array_key_exists($name, $this->templates)) {
            throw new Mustache_Exception_UnknownTemplateException($name);
        }

        return $this->templates[$name];
    }

    
    protected function loadTemplates()
    {
        if ($this->templates === null) {
            $this->templates = array();
            $data = file_get_contents($this->fileName, false, null, $this->offset);
            foreach (preg_split("/^@@(?= [\w\d\.]+$)/m", $data, -1) as $chunk) {
                if (trim($chunk)) {
                    list($name, $content)         = explode("\n", $chunk, 2);
                    $this->templates[trim($name)] = trim($content);
                }
            }
        }
    }
}
