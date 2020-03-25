<?php

namespace Box\Spout\Reader\Wrapper;

use Box\Spout\Reader\Exception\XMLProcessingException;



class SimpleXMLElement
{
    use XMLInternalErrorsHelper;

    
    protected $simpleXMLElement;

    
    public function __construct($xmlData)
    {
        $this->useXMLInternalErrors();

        try {
            $this->simpleXMLElement = new \SimpleXMLElement($xmlData);
        } catch (\Exception $exception) {
                        $this->resetXMLInternalErrorsSetting();
            throw new XMLProcessingException($this->getLastXMLErrorMessage());
        }

        $this->resetXMLInternalErrorsSetting();

        return $this->simpleXMLElement;
    }

    
    public function getAttribute($name, $namespace = null)
    {
        $isPrefix = ($namespace !== null);
        $attributes = $this->simpleXMLElement->attributes($namespace, $isPrefix);
        $attributeValue = $attributes->{$name};

        return ($attributeValue !== null) ? (string) $attributeValue : null;
    }

    
    public function registerXPathNamespace($prefix, $namespace)
    {
        return $this->simpleXMLElement->registerXPathNamespace($prefix, $namespace);
    }

    
    public function xpath($path)
    {
        $elements = $this->simpleXMLElement->xpath($path);

        if ($elements !== false) {
            $wrappedElements = [];
            foreach ($elements as $element) {
                $wrappedElement = $this->wrapSimpleXMLElement($element);

                if ($wrappedElement !== null) {
                    $wrappedElements[] = $this->wrapSimpleXMLElement($element);
                }
            }

            $elements = $wrappedElements;
        }

        return $elements;
    }

    
    protected function wrapSimpleXMLElement(\SimpleXMLElement $element)
    {
        $wrappedElement = null;
        $elementAsXML = $element->asXML();

        if ($elementAsXML !== false) {
            $wrappedElement = new SimpleXMLElement($elementAsXML);
        }

        return $wrappedElement;
    }

    
    public function removeNodesMatchingXPath($path)
    {
        $nodesToRemove = $this->simpleXMLElement->xpath($path);

        foreach ($nodesToRemove as $nodeToRemove) {
            unset($nodeToRemove[0]);
        }
    }

    
    public function getFirstChildByTagName($tagName)
    {
        $doesElementExist = isset($this->simpleXMLElement->{$tagName});

        
        $realElement = $this->simpleXMLElement->{$tagName};

        return $doesElementExist ? $this->wrapSimpleXMLElement($realElement) : null;
    }

    
    public function children()
    {
        $children = [];

        foreach ($this->simpleXMLElement->children() as $child) {
            $children[] = $this->wrapSimpleXMLElement($child);
        }

        return $children;
    }

    
    public function __toString()
    {
        return $this->simpleXMLElement->__toString();
    }
}
