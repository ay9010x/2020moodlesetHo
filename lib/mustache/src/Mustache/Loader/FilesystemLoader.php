<?php




class Mustache_Loader_FilesystemLoader implements Mustache_Loader
{
    private $baseDir;
    private $extension = '.mustache';
    private $templates = array();

    
    public function __construct($baseDir, array $options = array())
    {
        $this->baseDir = $baseDir;

        if (strpos($this->baseDir, '://') === false) {
            $this->baseDir = realpath($this->baseDir);
        }

        if (!is_dir($this->baseDir)) {
            throw new Mustache_Exception_RuntimeException(sprintf('FilesystemLoader baseDir must be a directory: %s', $baseDir));
        }

        if (array_key_exists('extension', $options)) {
            if (empty($options['extension'])) {
                $this->extension = '';
            } else {
                $this->extension = '.' . ltrim($options['extension'], '.');
            }
        }
    }

    
    public function load($name)
    {
        if (!isset($this->templates[$name])) {
            $this->templates[$name] = $this->loadFile($name);
        }

        return $this->templates[$name];
    }

    
    protected function loadFile($name)
    {
        $fileName = $this->getFileName($name);

        if (!file_exists($fileName)) {
            throw new Mustache_Exception_UnknownTemplateException($name);
        }

        return file_get_contents($fileName);
    }

    
    protected function getFileName($name)
    {
        $fileName = $this->baseDir . '/' . $name;
        if (substr($fileName, 0 - strlen($this->extension)) !== $this->extension) {
            $fileName .= $this->extension;
        }

        return $fileName;
    }
}
