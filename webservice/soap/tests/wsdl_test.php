<?php


namespace webservice_soap;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/soap/classes/wsdl.php');


class wsdl_test extends \advanced_testcase {

    
    public function test_minimum_wsdl() {
        $this->resetAfterTest();

        $serviceclass = 'testserviceclass';
        $namespace = 'testnamespace';
        $wsdl = new wsdl($serviceclass, $namespace);

                $definitions = new \SimpleXMLElement($wsdl->to_xml());
        $defattrs = $definitions->attributes();
        $this->assertEquals($serviceclass, $defattrs->name);
        $this->assertEquals($namespace, $defattrs->targetNamespace);

                $this->assertNotNull($definitions->types);
        $this->assertEquals($namespace, $definitions->types->children('xsd', true)->schema->attributes()->targetNamespace);

                $this->assertNotNull($definitions->portType);
        $this->assertEquals($serviceclass . wsdl::PORT, $definitions->portType->attributes()->name);

                $this->assertNotNull($definitions->binding);
        $this->assertEquals($serviceclass . wsdl::BINDING, $definitions->binding->attributes()->name);
        $this->assertEquals('tns:' . $serviceclass . wsdl::PORT, $definitions->binding->attributes()->type);

        $bindingattrs = $definitions->binding->children('soap', true)->binding->attributes();
        $this->assertNotEmpty('rpc', $bindingattrs);
        $this->assertEquals('rpc', $bindingattrs->style);
        $this->assertEquals(wsdl::NS_SOAP_TRANSPORT, $bindingattrs->transport);

                $this->assertNotNull($definitions->service);
        $this->assertEquals($serviceclass . wsdl::SERVICE, $definitions->service->attributes()->name);

        $serviceport = $definitions->service->children()->port;
        $this->assertNotEmpty($serviceport);
        $this->assertEquals($serviceclass . wsdl::PORT, $serviceport->attributes()->name);
        $this->assertEquals('tns:' . $serviceclass . wsdl::BINDING, $serviceport->attributes()->binding);

        $serviceportaddress = $serviceport->children('soap', true)->address;
        $this->assertNotEmpty($serviceportaddress);
        $this->assertEquals($namespace, $serviceportaddress->attributes()->location);
    }

    
    public function test_add_complex_type() {
        $this->resetAfterTest();

        $classname = 'testcomplextype';
        $classattrs = array(
            'doubleparam' => array(
                'type' => 'double',
                'nillable' => true
            ),
            'stringparam' => array(
                'type' => 'string',
                'nillable' => true
            ),
            'intparam' => array(
                'type' => 'int',
                'nillable' => true
            ),
            'boolparam' => array(
                'type' => 'int',
                'nillable' => true
            ),
            'classparam' => array(
                'type' => 'teststruct'
            ),
            'arrayparam' => array(
                'type' => 'array',
                'nillable' => true
            ),
        );

        $serviceclass = 'testserviceclass';
        $namespace = 'testnamespace';
        $wsdl = new wsdl($serviceclass, $namespace);
        $wsdl->add_complex_type($classname, $classattrs);

        $definitions = new \SimpleXMLElement($wsdl->to_xml());

                $this->assertNotNull($definitions->types);
        $this->assertEquals($namespace, $definitions->types->children('xsd', true)->schema->attributes()->targetNamespace);
        $complextype = $definitions->types->children('xsd', true)->schema->children('xsd', true);
        $this->assertNotEmpty($complextype);

                foreach ($complextype->children('xsd', true)->all->children('xsd', true) as $element) {
            foreach ($classattrs as $name => $options) {
                if (strcmp($name, $element->attributes()->name) != 0) {
                    continue;
                }
                switch ($options['type']) {
                    case 'double':
                    case 'int':
                    case 'string':
                        $this->assertEquals('xsd:' . $options['type'], $element->attributes()->type);
                        break;
                    case 'array':
                        $this->assertEquals('soap-enc:' . ucfirst($options['type']), $element->attributes()->type);
                        break;
                    default:
                        $this->assertEquals('tns:' . $options['type'], $element->attributes()->type);
                        break;
                }
                if (!empty($options['nillable'])) {
                    $this->assertEquals('true', $element->attributes()->nillable);
                }
                break;
            }
        }
    }

    
    public function test_register() {
        $this->resetAfterTest();

        $serviceclass = 'testserviceclass';
        $namespace = 'testnamespace';
        $wsdl = new wsdl($serviceclass, $namespace);

        $functionname = 'testfunction';
        $documentation = 'This is a test function';
        $in = array(
            'doubleparam' => array(
                'type' => 'double'
            ),
            'stringparam' => array(
                'type' => 'string'
            ),
            'intparam' => array(
                'type' => 'int'
            ),
            'boolparam' => array(
                'type' => 'int'
            ),
            'classparam' => array(
                'type' => 'teststruct'
            ),
            'arrayparam' => array(
                'type' => 'array'
            )
        );
        $out = array(
            'doubleparam' => array(
                'type' => 'double'
            ),
            'stringparam' => array(
                'type' => 'string'
            ),
            'intparam' => array(
                'type' => 'int'
            ),
            'boolparam' => array(
                'type' => 'int'
            ),
            'classparam' => array(
                'type' => 'teststruct'
            ),
            'arrayparam' => array(
                'type' => 'array'
            ),
            'return' => array(
                'type' => 'teststruct2'
            )
        );
        $wsdl->register($functionname, $in, $out, $documentation);

        $definitions = new \SimpleXMLElement($wsdl->to_xml());

                $porttypeoperation = $definitions->portType->operation;
        $this->assertEquals($documentation, $porttypeoperation->documentation);
        $this->assertEquals('tns:' . $functionname . wsdl::IN, $porttypeoperation->input->attributes()->message);
        $this->assertEquals('tns:' . $functionname . wsdl::OUT, $porttypeoperation->output->attributes()->message);

                $bindingoperation = $definitions->binding->operation;
        $soapoperation = $bindingoperation->children('soap', true)->operation;
        $this->assertEquals($namespace . '#' . $functionname, $soapoperation->attributes()->soapAction);
        $inputbody = $bindingoperation->input->children('soap', true);
        $this->assertEquals('encoded', $inputbody->attributes()->use);
        $this->assertEquals(wsdl::NS_SOAP_ENC, $inputbody->attributes()->encodingStyle);
        $this->assertEquals($namespace, $inputbody->attributes()->namespace);
        $outputbody = $bindingoperation->output->children('soap', true);
        $this->assertEquals('encoded', $outputbody->attributes()->use);
        $this->assertEquals(wsdl::NS_SOAP_ENC, $outputbody->attributes()->encodingStyle);
        $this->assertEquals($namespace, $outputbody->attributes()->namespace);

                $messagein = $definitions->message[0];
        $this->assertEquals($functionname . wsdl::IN, $messagein->attributes()->name);
        foreach ($messagein->children() as $part) {
            foreach ($in as $name => $options) {
                if (strcmp($name, $part->attributes()->name) != 0) {
                    continue;
                }
                switch ($options['type']) {
                    case 'double':
                    case 'int':
                    case 'string':
                        $this->assertEquals('xsd:' . $options['type'], $part->attributes()->type);
                        break;
                    case 'array':
                        $this->assertEquals('soap-enc:' . ucfirst($options['type']), $part->attributes()->type);
                        break;
                    default:
                        $this->assertEquals('tns:' . $options['type'], $part->attributes()->type);
                        break;
                }
                break;
            }
        }
        $messageout = $definitions->message[1];
        $this->assertEquals($functionname . wsdl::OUT, $messageout->attributes()->name);
        foreach ($messageout->children() as $part) {
            foreach ($out as $name => $options) {
                if (strcmp($name, $part->attributes()->name) != 0) {
                    continue;
                }
                switch ($options['type']) {
                    case 'double':
                    case 'int':
                    case 'string':
                        $this->assertEquals('xsd:' . $options['type'], $part->attributes()->type);
                        break;
                    case 'array':
                        $this->assertEquals('soap-enc:' . ucfirst($options['type']), $part->attributes()->type);
                        break;
                    default:
                        $this->assertEquals('tns:' . $options['type'], $part->attributes()->type);
                        break;
                }
                break;
            }
        }
    }

    
    public function test_register_without_input() {
        $this->resetAfterTest();

        $serviceclass = 'testserviceclass';
        $namespace = 'testnamespace';
        $wsdl = new wsdl($serviceclass, $namespace);

        $functionname = 'testfunction';
        $documentation = 'This is a test function';

        $out = array(
            'return' => array(
                'type' => 'teststruct2'
            )
        );
        $wsdl->register($functionname, null, $out, $documentation);

        $definitions = new \SimpleXMLElement($wsdl->to_xml());

                $porttypeoperation = $definitions->portType->operation;
        $this->assertEquals($documentation, $porttypeoperation->documentation);
        $this->assertFalse(isset($porttypeoperation->input));
        $this->assertTrue(isset($porttypeoperation->output));

                $bindingoperation = $definitions->binding->operation;
                $this->assertFalse(isset($bindingoperation->input));
        $this->assertTrue(isset($bindingoperation->output));

                        $this->assertEquals(1, count($definitions->message));
        $messageout = $definitions->message[0];
        $this->assertEquals($functionname . wsdl::OUT, $messageout->attributes()->name);

    }

    
    public function test_register_without_output() {
        $this->resetAfterTest();

        $serviceclass = 'testserviceclass';
        $namespace = 'testnamespace';
        $wsdl = new wsdl($serviceclass, $namespace);

        $functionname = 'testfunction';
        $documentation = 'This is a test function';

        $in = array(
            'return' => array(
                'type' => 'teststruct2'
            )
        );
        $wsdl->register($functionname, $in, null, $documentation);

        $definitions = new \SimpleXMLElement($wsdl->to_xml());

                $porttypeoperation = $definitions->portType->operation;
        $this->assertEquals($documentation, $porttypeoperation->documentation);
        $this->assertTrue(isset($porttypeoperation->input));
        $this->assertFalse(isset($porttypeoperation->output));

                $bindingoperation = $definitions->binding->operation;
                $this->assertTrue(isset($bindingoperation->input));
        $this->assertFalse(isset($bindingoperation->output));

                        $this->assertEquals(1, count($definitions->message));
        $messagein = $definitions->message[0];
        $this->assertEquals($functionname . wsdl::IN, $messagein->attributes()->name);

    }
}
