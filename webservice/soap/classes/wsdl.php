<?php


namespace webservice_soap;


class wsdl {
    
    const NS_WSDL = 'http://schemas.xmlsoap.org/wsdl/';

    
    const NS_SOAP_ENC = 'http://schemas.xmlsoap.org/soap/encoding/';

    
    const NS_SOAP = 'http://schemas.xmlsoap.org/wsdl/soap/';

    
    const NS_XSD = 'http://www.w3.org/2001/XMLSchema';

    
    const NS_SOAP_TRANSPORT = 'http://schemas.xmlsoap.org/soap/http';

    
    const BINDING = 'Binding';

    
    const IN = 'In';

    
    const OUT = 'Out';

    
    const PORT = 'Port';

    
    const SERVICE = 'Service';

    
    private $serviceclass;

    
    private $namespace;

    
    private $messagenodes;

    
    private $nodebinding;

    
    private $nodedefinitions;

    
    private $nodeporttype;

    
    private $nodeservice;

    
    private $nodetypes;

    
    public function __construct($serviceclass, $namespace) {
        $this->serviceclass = $serviceclass;
        $this->namespace = $namespace;

                $this->nodedefinitions = new \SimpleXMLElement('<definitions />');
        $this->nodedefinitions->addAttribute('xmlns', self::NS_WSDL);
        $this->nodedefinitions->addAttribute('x:xmlns:tns', $namespace);
        $this->nodedefinitions->addAttribute('x:xmlns:soap', self::NS_SOAP);
        $this->nodedefinitions->addAttribute('x:xmlns:xsd', self::NS_XSD);
        $this->nodedefinitions->addAttribute('x:xmlns:soap-enc', self::NS_SOAP_ENC);
        $this->nodedefinitions->addAttribute('x:xmlns:wsdl', self::NS_WSDL);
        $this->nodedefinitions->addAttribute('name', $serviceclass);
        $this->nodedefinitions->addAttribute('targetNamespace', $namespace);

                $this->nodetypes = $this->nodedefinitions->addChild('types');
        $typeschema = $this->nodetypes->addChild('x:xsd:schema');
        $typeschema->addAttribute('targetNamespace', $namespace);

                $this->nodeporttype = $this->nodedefinitions->addChild('portType');
        $this->nodeporttype->addAttribute('name', $serviceclass . self::PORT);

                $this->nodebinding = $this->nodedefinitions->addChild('binding');
        $this->nodebinding->addAttribute('name', $serviceclass . self::BINDING);
        $this->nodebinding->addAttribute('type', 'tns:' . $serviceclass . self::PORT);
        $soapbinding = $this->nodebinding->addChild('x:soap:binding');
        $soapbinding->addAttribute('style', 'rpc');
        $soapbinding->addAttribute('transport', self::NS_SOAP_TRANSPORT);

                $this->nodeservice = $this->nodedefinitions->addChild('service');
        $this->nodeservice->addAttribute('name', $serviceclass . self::SERVICE);
        $serviceport = $this->nodeservice->addChild('port');
        $serviceport->addAttribute('name', $serviceclass . self::PORT);
        $serviceport->addAttribute('binding', 'tns:' . $serviceclass . self::BINDING);
        $soapaddress = $serviceport->addChild('x:soap:address');
        $soapaddress->addAttribute('location', $namespace);

                $this->messagenodes = array();
    }

    
    public function add_complex_type($classname, $properties) {
        $typeschema = $this->nodetypes->children();
                $complextype = $typeschema->addChild('x:xsd:complexType');
        $complextype->addAttribute('name', $classname);
        $child = $complextype->addChild('x:xsd:all');
        foreach ($properties as $name => $options) {
            $param = $child->addChild('x:xsd:element');
            $param->addAttribute('name', $name);
            $param->addAttribute('type', $this->get_soap_type($options['type']));
            if (!empty($options['nillable'])) {
                $param->addAttribute('nillable', 'true');
            }
        }
    }

    
    public function register($functionname, $inputparams = array(), $outputparams = array(), $documentation = '') {
                $porttypeoperation = $this->nodeporttype->addChild('operation');
        $porttypeoperation->addAttribute('name', $functionname);
                $porttypeoperation->addChild('documentation', $documentation);

                $bindingoperation = $this->nodebinding->addChild('operation');
        $bindingoperation->addAttribute('name', $functionname);
        $soapoperation = $bindingoperation->addChild('x:soap:operation');
        $soapoperation->addAttribute('soapAction', $this->namespace . '#' . $functionname);

                $this->process_params($functionname, $porttypeoperation, $bindingoperation, $inputparams);

                $this->process_params($functionname, $porttypeoperation, $bindingoperation, $outputparams, true);
    }

    
    public function to_xml() {
                return $this->nodedefinitions->asXML();
    }

    
    private function get_soap_type($type) {
        switch($type) {
            case 'int':
            case 'double':
            case 'string':
                return 'xsd:' . $type;
            case 'array':
                return 'soap-enc:Array';
            default:
                return 'tns:' . $type;
        }
    }

    
    private function process_params($functionname, \SimpleXMLElement $porttypeoperation, \SimpleXMLElement $bindingoperation,
                                    array $params = null, $isoutput = false) {
                if (empty($params)) {
            return;
        }

        $postfix = self::IN;
        $childtype = 'input';
        if ($isoutput) {
            $postfix = self::OUT;
            $childtype = 'output';
        }

                $child = $porttypeoperation->addChild($childtype);
        $child->addAttribute('message', 'tns:' . $functionname . $postfix);

                $child = $bindingoperation->addChild($childtype);
        $soapbody = $child->addChild('x:soap:body');
        $soapbody->addAttribute('use', 'encoded');
        $soapbody->addAttribute('encodingStyle', self::NS_SOAP_ENC);
        $soapbody->addAttribute('namespace', $this->namespace);

                $messagein = $this->nodedefinitions->addChild('message');
        $messagein->addAttribute('name', $functionname . $postfix);
        foreach ($params as $name => $options) {
            $part = $messagein->addChild('part');
            $part->addAttribute('name', $name);
            $part->addAttribute('type', $this->get_soap_type($options['type']));
        }
    }
}
