<?php

namespace MrClay\Cli;

use BadMethodCallException;


class Arg {
    
    public function getDefaultSpec()
    {
        return array(
            'mayHaveValue' => false,
            'mustHaveValue' => false,
            'assertFile' => false,
            'assertDir' => false,
            'assertReadable' => false,
            'assertWritable' => false,
            'useAsInfile' => false,
            'useAsOutfile' => false,
        );
    }

    
    protected $spec = array();

    
    protected $required = false;

    
    protected $description = '';

    
    public function __construct($isRequired = false)
    {
        $this->spec = $this->getDefaultSpec();
        $this->required = (bool) $isRequired;
        if ($isRequired) {
            $this->spec['mustHaveValue'] = true;
        }
    }

    
    public function useAsOutfile()
    {
        $this->spec['useAsOutfile'] = true;
        return $this->assertFile()->assertWritable();
    }

    
    public function useAsInfile()
    {
        $this->spec['useAsInfile'] = true;
        return $this->assertFile()->assertReadable();
    }

    
    public function getSpec()
    {
        return $this->spec;
    }

    
    public function setDescription($desc)
    {
        $this->description = $desc;
        return $this;
    }

    
    public function getDescription()
    {
        return $this->description;
    }

    
    public function isRequired()
    {
        return $this->required;
    }

    
    public function __call($name, array $args = array())
    {
        if (array_key_exists($name, $this->spec)) {
            $this->spec[$name] = true;
            if ($name === 'assertFile' || $name === 'assertDir') {
                $this->spec['mustHaveValue'] = true;
            }
        } else {
            throw new BadMethodCallException('Method does not exist');
        }
        return $this;
    }

    
    public function __get($name)
    {
        if (array_key_exists($name, $this->spec)) {
            return $this->spec[$name];
        }
        return null;
    }
}
