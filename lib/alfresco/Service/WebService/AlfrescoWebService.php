<?php



class AlfrescoWebService extends SoapClient
{
   private $securityExtNS = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
   private $wsUtilityNS   = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd";
   private $passwordType  = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText";

   private $ticket;
   
   public function __construct($wsdl, $options = array('trace' => true, 'exceptions' => true), $ticket = null)
   {
            $this->ticket = $ticket;

            parent::__construct($wsdl, $options);
   }

   public function __call($function_name, $arguments=array())
   {
      return $this->__soapCall($function_name, $arguments);
   }

   public function __soapCall($function_name, $arguments=array(), $options=array(), $input_headers=array(), &$output_headers=array())
   {
      if (isset($this->ticket))
      {
                  $input_headers[] = new SoapHeader($this->securityExtNS, "Security", null, 1);
         
                  $sessionId = Alfresco_Repository::getSessionId($this->ticket);
         if ($sessionId != null)
         {
         	$this->__setCookie("JSESSIONID", $sessionId);
         }
      }
      
      return parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);   
   }
   
   public function __doRequest($request, $location, $action, $version, $one_way = 0)
   {
                  if (isset($this->ticket))
      { 
         $dom = new DOMDocument("1.0");
         $dom->loadXML($request);

         $securityHeader = $dom->getElementsByTagName("Security");

         if ($securityHeader->length != 1)
         {
            throw new Exception("Expected length: 1, Received: " . $securityHeader->length . ". No Security Header, or more than one element called Security!");
         }
      
         $securityHeader = $securityHeader->item(0);

                  $timeStamp = $dom->createElementNS($this->wsUtilityNS, "Timestamp");
         $createdDate = gmdate("Y-m-d\TH:i:s\Z", gmmktime(gmdate("H"), gmdate("i"), gmdate("s"), gmdate("m"), gmdate("d"), gmdate("Y")));
         $expiresDate = gmdate("Y-m-d\TH:i:s\Z", gmmktime(gmdate("H")+1, gmdate("i"), gmdate("s"), gmdate("m"), gmdate("d"), gmdate("Y")));
         $created = new DOMElement("Created", $createdDate, $this->wsUtilityNS);
         $expires = new DOMElement("Expires", $expiresDate, $this->wsUtilityNS);
         $timeStamp->appendChild($created);
         $timeStamp->appendChild($expires);

                  $userNameToken = $dom->createElementNS($this->securityExtNS, "UsernameToken");
         $userName = new DOMElement("Username", "username", $this->securityExtNS);
         $passWord = $dom->createElementNS($this->securityExtNS, "Password");
         $typeAttr = new DOMAttr("Type", $this->passwordType);
         $passWord->appendChild($typeAttr);
         $passWord->appendChild($dom->createTextNode($this->ticket));
         $userNameToken->appendChild($userName);
         $userNameToken->appendChild($passWord);

                  $securityHeader->appendChild($timeStamp);
         $securityHeader->appendChild($userNameToken);

                  $request = $dom->saveXML();
      }

      return parent::__doRequest($request, $location, $action, $version);
   }
}

?>
