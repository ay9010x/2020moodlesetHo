<?php


class HTMLPurifier_AttrDef_URI extends HTMLPurifier_AttrDef
{

    
    protected $parser;

    
    protected $embedsResource;

    
    public function __construct($embeds_resource = false)
    {
        $this->parser = new HTMLPurifier_URIParser();
        $this->embedsResource = (bool)$embeds_resource;
    }

    
    public function make($string)
    {
        $embeds = ($string === 'embedded');
        return new HTMLPurifier_AttrDef_URI($embeds);
    }

    
    public function validate($uri, $config, $context)
    {
        if ($config->get('URI.Disable')) {
            return false;
        }

        $uri = $this->parseCDATA($uri);

                $uri = $this->parser->parse($uri);
        if ($uri === false) {
            return false;
        }

                $context->register('EmbeddedURI', $this->embedsResource);

        $ok = false;
        do {

                        $result = $uri->validate($config, $context);
            if (!$result) {
                break;
            }

                        $uri_def = $config->getDefinition('URI');
            $result = $uri_def->filter($uri, $config, $context);
            if (!$result) {
                break;
            }

                        $scheme_obj = $uri->getSchemeObj($config, $context);
            if (!$scheme_obj) {
                break;
            }
            if ($this->embedsResource && !$scheme_obj->browsable) {
                break;
            }
            $result = $scheme_obj->validate($uri, $config, $context);
            if (!$result) {
                break;
            }

                        $result = $uri_def->postFilter($uri, $config, $context);
            if (!$result) {
                break;
            }

                        $ok = true;

        } while (false);

        $context->destroy('EmbeddedURI');
        if (!$ok) {
            return false;
        }
                return $uri->toString();
    }
}

