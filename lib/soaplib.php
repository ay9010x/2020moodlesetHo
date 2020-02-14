<?php




defined('MOODLE_INTERNAL') || die();


function soap_connect($wsdl, $trace=false) {
    try {
        $connection = new SoapClient($wsdl, array('soap_version'=>SOAP_1_1, 'exceptions'=>true, 'trace'=>$trace));
    }
    catch (SoapFault $f) {
        $connection = $f;
    }
    catch (Exception $e) {
        $connection = new SoapFault('client', 'Could not connect to the service');
    }
    return $connection;
}


function soap_call($connection, $call, $params) {
    try {
        $return = $connection->__soapCall($call, $params);
    }
    catch (SoapFault $f) {
        $return = $f;
    }
    catch (Exception $e) {
        $return = new SoapFault('client', 'Could call the method');
    }
        if (is_array($return)) {
        $keys = array_keys($return);
        $assoc = true;
        foreach ($keys as $key) {
            if (!is_string($key)) {
                $assoc = false;
                break;
            }
        }
        if ($assoc)
            $return = (object) $return;
    }
    return $return;
}

function soap_serve($wsdl, $functions) {
        $s = new SoapServer($wsdl);
        foreach ($functions as $func)
        $s->addFunction($func);
        $s->handle();
}

function make_soap_fault($faultcode, $faultstring, $faultactor='', $detail='', $faultname='', $headerfault='') {
    return new SoapFault($faultcode, $faultstring, $faultactor, $detail, $faultname, $headerfault);
}

function get_last_soap_messages($connection) {
    return array('request'=>$connection->__getLastRequest(), 'response'=>$connection->__getLastResponse());
}

function soap_encode($value, $name, $type, $namespace, $encode=XSD_STRING) {
    $value = new SoapVar($value, $encode, $type, $namespace);
    if ('' === $name)
        return $value;
    return new SoapParam($value, $name);
}

function soap_encode_object($value, $name, $type, $namespace) {
    if (!is_object($value))
        return $value;
    $value = new SoapVar($value, SOAP_ENC_OBJECT, $type, $namespace);
    if ('' === $name)
        return $value;
    return new SoapParam($value, $name);
}

function soap_encode_array($value, $name, $type, $namespace) {
    if (!is_array($value))
        return $value;
    $value = new SoapVar($value, SOAP_ENC_ARRAY, 'ArrayOf' . $type, $namespace);
    if ('' === $name)
        return $value;
    return new SoapParam($value, $name);
}

function handle_soap_wsdl_request($wsdlfile, $address=false) {
    header('Content-type: application/wsdl+xml');
    $wsdl = file_get_contents($wsdlfile);
    if (false !== $address) {
        if (true === $address) {
            $address = (($_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
        }
        $wsdl = str_replace('###SERVER_ADDRESS###', $address, $wsdl);
    }
    echo $wsdl;
    exit;
}
