<?php



class Google_Service_SiteVerification extends Google_Service
{
  
  const SITEVERIFICATION =
      "https://www.googleapis.com/auth/siteverification";
  
  const SITEVERIFICATION_VERIFY_ONLY =
      "https://www.googleapis.com/auth/siteverification.verify_only";

  public $webResource;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'siteVerification/v1/';
    $this->version = 'v1';
    $this->serviceName = 'siteVerification';

    $this->webResource = new Google_Service_SiteVerification_WebResource_Resource(
        $this,
        $this->serviceName,
        'webResource',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'webResource/{id}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'webResource/{id}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getToken' => array(
              'path' => 'token',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'insert' => array(
              'path' => 'webResource',
              'httpMethod' => 'POST',
              'parameters' => array(
                'verificationMethod' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'webResource',
              'httpMethod' => 'GET',
              'parameters' => array(),
            ),'patch' => array(
              'path' => 'webResource/{id}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'webResource/{id}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_SiteVerification_WebResource_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_SiteVerification_SiteVerificationWebResourceResource");
  }

  
  public function getToken(Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('getToken', array($params), "Google_Service_SiteVerification_SiteVerificationWebResourceGettokenResponse");
  }

  
  public function insert($verificationMethod, Google_Service_SiteVerification_SiteVerificationWebResourceResource $postBody, $optParams = array())
  {
    $params = array('verificationMethod' => $verificationMethod, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_SiteVerification_SiteVerificationWebResourceResource");
  }

  
  public function listWebResource($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_SiteVerification_SiteVerificationWebResourceListResponse");
  }

  
  public function patch($id, Google_Service_SiteVerification_SiteVerificationWebResourceResource $postBody, $optParams = array())
  {
    $params = array('id' => $id, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_SiteVerification_SiteVerificationWebResourceResource");
  }

  
  public function update($id, Google_Service_SiteVerification_SiteVerificationWebResourceResource $postBody, $optParams = array())
  {
    $params = array('id' => $id, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_SiteVerification_SiteVerificationWebResourceResource");
  }
}




class Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $siteType = 'Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequestSite';
  protected $siteDataType = '';
  public $verificationMethod;


  public function setSite(Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequestSite $site)
  {
    $this->site = $site;
  }
  public function getSite()
  {
    return $this->site;
  }
  public function setVerificationMethod($verificationMethod)
  {
    $this->verificationMethod = $verificationMethod;
  }
  public function getVerificationMethod()
  {
    return $this->verificationMethod;
  }
}

class Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequestSite extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $identifier;
  public $type;


  public function setIdentifier($identifier)
  {
    $this->identifier = $identifier;
  }
  public function getIdentifier()
  {
    return $this->identifier;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}

class Google_Service_SiteVerification_SiteVerificationWebResourceGettokenResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $method;
  public $token;


  public function setMethod($method)
  {
    $this->method = $method;
  }
  public function getMethod()
  {
    return $this->method;
  }
  public function setToken($token)
  {
    $this->token = $token;
  }
  public function getToken()
  {
    return $this->token;
  }
}

class Google_Service_SiteVerification_SiteVerificationWebResourceListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_SiteVerification_SiteVerificationWebResourceResource';
  protected $itemsDataType = 'array';


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
}

class Google_Service_SiteVerification_SiteVerificationWebResourceResource extends Google_Collection
{
  protected $collection_key = 'owners';
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $owners;
  protected $siteType = 'Google_Service_SiteVerification_SiteVerificationWebResourceResourceSite';
  protected $siteDataType = '';


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setOwners($owners)
  {
    $this->owners = $owners;
  }
  public function getOwners()
  {
    return $this->owners;
  }
  public function setSite(Google_Service_SiteVerification_SiteVerificationWebResourceResourceSite $site)
  {
    $this->site = $site;
  }
  public function getSite()
  {
    return $this->site;
  }
}

class Google_Service_SiteVerification_SiteVerificationWebResourceResourceSite extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $identifier;
  public $type;


  public function setIdentifier($identifier)
  {
    $this->identifier = $identifier;
  }
  public function getIdentifier()
  {
    return $this->identifier;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}
