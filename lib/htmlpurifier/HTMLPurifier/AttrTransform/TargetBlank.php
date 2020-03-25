<?php



class HTMLPurifier_AttrTransform_TargetBlank extends HTMLPurifier_AttrTransform
{
    
    private $parser;

    public function __construct()
    {
        $this->parser = new HTMLPurifier_URIParser();
    }

    
    public function transform($attr, $config, $context)
    {
        if (!isset($attr['href'])) {
            return $attr;
        }

                $url = $this->parser->parse($attr['href']);
        $scheme = $url->getSchemeObj($config, $context);

        if ($scheme->browsable && !$url->isBenign($config, $context)) {
            $attr['target'] = '_blank';
        }
        return $attr;
    }
}

