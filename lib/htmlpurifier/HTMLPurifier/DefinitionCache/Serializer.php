<?php

class HTMLPurifier_DefinitionCache_Serializer extends HTMLPurifier_DefinitionCache
{

    
    public function add($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }
        $file = $this->generateFilePath($config);
        if (file_exists($file)) {
            return false;
        }
        if (!$this->_prepareDir($config)) {
            return false;
        }
        return $this->_write($file, serialize($def), $config);
    }

    
    public function set($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }
        $file = $this->generateFilePath($config);
        if (!$this->_prepareDir($config)) {
            return false;
        }
        return $this->_write($file, serialize($def), $config);
    }

    
    public function replace($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }
        $file = $this->generateFilePath($config);
        if (!file_exists($file)) {
            return false;
        }
        if (!$this->_prepareDir($config)) {
            return false;
        }
        return $this->_write($file, serialize($def), $config);
    }

    
    public function get($config)
    {
        $file = $this->generateFilePath($config);
        if (!file_exists($file)) {
            return false;
        }
        return unserialize(file_get_contents($file));
    }

    
    public function remove($config)
    {
        $file = $this->generateFilePath($config);
        if (!file_exists($file)) {
            return false;
        }
        return unlink($file);
    }

    
    public function flush($config)
    {
        if (!$this->_prepareDir($config)) {
            return false;
        }
        $dir = $this->generateDirectoryPath($config);
        $dh = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            if (empty($filename)) {
                continue;
            }
            if ($filename[0] === '.') {
                continue;
            }
            unlink($dir . '/' . $filename);
        }
    }

    
    public function cleanup($config)
    {
        if (!$this->_prepareDir($config)) {
            return false;
        }
        $dir = $this->generateDirectoryPath($config);
        $dh = opendir($dir);
        while (false !== ($filename = readdir($dh))) {
            if (empty($filename)) {
                continue;
            }
            if ($filename[0] === '.') {
                continue;
            }
            $key = substr($filename, 0, strlen($filename) - 4);
            if ($this->isOld($key, $config)) {
                unlink($dir . '/' . $filename);
            }
        }
    }

    
    public function generateFilePath($config)
    {
        $key = $this->generateKey($config);
        return $this->generateDirectoryPath($config) . '/' . $key . '.ser';
    }

    
    public function generateDirectoryPath($config)
    {
        $base = $this->generateBaseDirectoryPath($config);
        return $base . '/' . $this->type;
    }

    
    public function generateBaseDirectoryPath($config)
    {
        $base = $config->get('Cache.SerializerPath');
        $base = is_null($base) ? HTMLPURIFIER_PREFIX . '/HTMLPurifier/DefinitionCache/Serializer' : $base;
        return $base;
    }

    
    private function _write($file, $data, $config)
    {
        $result = file_put_contents($file, $data);
        if ($result !== false) {
                        $chmod = $config->get('Cache.SerializerPermissions');
            if (!$chmod) {
                $chmod = 0644;             }
            $chmod = $chmod & 0666;
            chmod($file, $chmod);
        }
        return $result;
    }

    
    private function _prepareDir($config)
    {
        $directory = $this->generateDirectoryPath($config);
        $chmod = $config->get('Cache.SerializerPermissions');
        if (!$chmod) {
            $chmod = 0755;         }
        if (!is_dir($directory)) {
            $base = $this->generateBaseDirectoryPath($config);
            if (!is_dir($base)) {
                trigger_error(
                    'Base directory ' . $base . ' does not exist,
                    please create or change using %Cache.SerializerPath',
                    E_USER_WARNING
                );
                return false;
            } elseif (!$this->_testPermissions($base, $chmod)) {
                return false;
            }
            mkdir($directory, $chmod);
            if (!$this->_testPermissions($directory, $chmod)) {
                trigger_error(
                    'Base directory ' . $base . ' does not exist,
                    please create or change using %Cache.SerializerPath',
                    E_USER_WARNING
                );
                return false;
            }
        } elseif (!$this->_testPermissions($directory, $chmod)) {
            return false;
        }
        return true;
    }

    
    private function _testPermissions($dir, $chmod)
    {
                if (is_writable($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
                                    trigger_error(
                'Directory ' . $dir . ' does not exist',
                E_USER_WARNING
            );
            return false;
        }
        if (function_exists('posix_getuid')) {
                        if (fileowner($dir) === posix_getuid()) {
                                $chmod = $chmod | 0700;
                if (chmod($dir, $chmod)) {
                    return true;
                }
            } elseif (filegroup($dir) === posix_getgid()) {
                $chmod = $chmod | 0070;
            } else {
                                                $chmod = $chmod | 0777;
            }
            trigger_error(
                'Directory ' . $dir . ' not writable, ' .
                'please chmod to ' . decoct($chmod),
                E_USER_WARNING
            );
        } else {
                        trigger_error(
                'Directory ' . $dir . ' not writable, ' .
                'please alter file permissions',
                E_USER_WARNING
            );
        }
        return false;
    }
}

