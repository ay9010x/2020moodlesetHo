<?php

namespace Box\Spout\Reader\XLSX\Helper;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Exception\XMLProcessingException;
use Box\Spout\Reader\Wrapper\SimpleXMLElement;
use Box\Spout\Reader\Wrapper\XMLReader;
use Box\Spout\Reader\XLSX\Helper\SharedStringsCaching\CachingStrategyFactory;
use Box\Spout\Reader\XLSX\Helper\SharedStringsCaching\CachingStrategyInterface;


class SharedStringsHelper
{
    
    const SHARED_STRINGS_XML_FILE_PATH = 'xl/sharedStrings.xml';

    
    const MAIN_NAMESPACE_FOR_SHARED_STRINGS_XML = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    
    protected $filePath;

    
    protected $tempFolder;

    
    protected $cachingStrategy;

    
    public function __construct($filePath, $tempFolder = null)
    {
        $this->filePath = $filePath;
        $this->tempFolder = $tempFolder;
    }

    
    public function hasSharedStrings()
    {
        $hasSharedStrings = false;
        $zip = new \ZipArchive();

        if ($zip->open($this->filePath) === true) {
            $hasSharedStrings = ($zip->locateName(self::SHARED_STRINGS_XML_FILE_PATH) !== false);
            $zip->close();
        }

        return $hasSharedStrings;
    }

    
    public function extractSharedStrings()
    {
        $xmlReader = new XMLReader();
        $sharedStringIndex = 0;
        
        $escaper = new \Box\Spout\Common\Escaper\XLSX();

        $sharedStringsFilePath = $this->getSharedStringsFilePath();
        if ($xmlReader->open($sharedStringsFilePath) === false) {
            throw new IOException('Could not open "' . self::SHARED_STRINGS_XML_FILE_PATH . '".');
        }

        try {
            $sharedStringsUniqueCount = $this->getSharedStringsUniqueCount($xmlReader);
            $this->cachingStrategy = $this->getBestSharedStringsCachingStrategy($sharedStringsUniqueCount);

            $xmlReader->readUntilNodeFound('si');

            while ($xmlReader->name === 'si') {
                $node = $this->getSimpleXmlElementNodeFromXMLReader($xmlReader);
                $node->registerXPathNamespace('ns', self::MAIN_NAMESPACE_FOR_SHARED_STRINGS_XML);

                                $cleanNode = $this->removeSuperfluousTextNodes($node);

                                $textNodes = $cleanNode->xpath('//ns:t');

                $textValue = '';
                foreach ($textNodes as $textNode) {
                    if ($this->shouldPreserveWhitespace($textNode)) {
                        $textValue .= $textNode->__toString();
                    } else {
                        $textValue .= trim($textNode->__toString());
                    }
                }

                $unescapedTextValue = $escaper->unescape($textValue);
                $this->cachingStrategy->addStringForIndex($unescapedTextValue, $sharedStringIndex);

                $sharedStringIndex++;

                                $xmlReader->next('si');
            }

        } catch (XMLProcessingException $exception) {
            throw new IOException("The sharedStrings.xml file is invalid and cannot be read. [{$exception->getMessage()}]");
        }

        $this->cachingStrategy->closeCache();

        $xmlReader->close();
    }

    
    protected function getSharedStringsFilePath()
    {
        return 'zip://' . $this->filePath . '#' . self::SHARED_STRINGS_XML_FILE_PATH;
    }

    
    protected function getSharedStringsUniqueCount($xmlReader)
    {
        $xmlReader->next('sst');

                while ($xmlReader->name === 'sst' && $xmlReader->nodeType !== XMLReader::ELEMENT) {
            $xmlReader->read();
        }

        return intval($xmlReader->getAttribute('uniqueCount'));
    }

    
    protected function getBestSharedStringsCachingStrategy($sharedStringsUniqueCount)
    {
        return CachingStrategyFactory::getInstance()
                ->getBestCachingStrategy($sharedStringsUniqueCount, $this->tempFolder);
    }

    
    protected function getSimpleXmlElementNodeFromXMLReader($xmlReader)
    {
        $node = null;
        try {
            $node = new SimpleXMLElement($xmlReader->readOuterXml());
        } catch (XMLProcessingException $exception) {
            throw new IOException("The sharedStrings.xml file contains unreadable data [{$exception->getMessage()}].");
        }

        return $node;
    }

    
    protected function removeSuperfluousTextNodes($parentNode)
    {
        $tagsToRemove = [
            'rPh',         ];

        foreach ($tagsToRemove as $tagToRemove) {
            $xpath = '//ns:' . $tagToRemove;
            $parentNode->removeNodesMatchingXPath($xpath);
        }

        return $parentNode;
    }

    
    protected function shouldPreserveWhitespace($textNode)
    {
        $spaceValue = $textNode->getAttribute('space', 'xml');
        return ($spaceValue === 'preserve');
    }

    
    public function getStringAtIndex($sharedStringIndex)
    {
        return $this->cachingStrategy->getStringAtIndex($sharedStringIndex);
    }

    
    public function cleanup()
    {
        if ($this->cachingStrategy) {
            $this->cachingStrategy->clearCache();
        }
    }
}
