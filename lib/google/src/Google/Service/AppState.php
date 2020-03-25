<?php



class Google_Service_AppState extends Google_Service
{
  
  const APPSTATE =
      "https://www.googleapis.com/auth/appstate";

  public $states;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'appstate/v1/';
    $this->version = 'v1';
    $this->serviceName = 'appstate';

    $this->states = new Google_Service_AppState_States_Resource(
        $this,
        $this->serviceName,
        'states',
        array(
          'methods' => array(
            'clear' => array(
              'path' => 'states/{stateKey}/clear',
              'httpMethod' => 'POST',
              'parameters' => array(
                'stateKey' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'currentDataVersion' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'states/{stateKey}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'stateKey' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'states/{stateKey}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'stateKey' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'states',
              'httpMethod' => 'GET',
              'parameters' => array(
                'includeData' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'states/{stateKey}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'stateKey' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'currentStateVersion' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_AppState_States_Resource extends Google_Service_Resource
{

  
  public function clear($stateKey, $optParams = array())
  {
    $params = array('stateKey' => $stateKey);
    $params = array_merge($params, $optParams);
    return $this->call('clear', array($params), "Google_Service_AppState_WriteResult");
  }

  
  public function delete($stateKey, $optParams = array())
  {
    $params = array('stateKey' => $stateKey);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($stateKey, $optParams = array())
  {
    $params = array('stateKey' => $stateKey);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AppState_GetResponse");
  }

  
  public function listStates($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AppState_ListResponse");
  }

  
  public function update($stateKey, Google_Service_AppState_UpdateRequest $postBody, $optParams = array())
  {
    $params = array('stateKey' => $stateKey, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AppState_WriteResult");
  }
}




class Google_Service_AppState_GetResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $currentStateVersion;
  public $data;
  public $kind;
  public $stateKey;


  public function setCurrentStateVersion($currentStateVersion)
  {
    $this->currentStateVersion = $currentStateVersion;
  }
  public function getCurrentStateVersion()
  {
    return $this->currentStateVersion;
  }
  public function setData($data)
  {
    $this->data = $data;
  }
  public function getData()
  {
    return $this->data;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setStateKey($stateKey)
  {
    $this->stateKey = $stateKey;
  }
  public function getStateKey()
  {
    return $this->stateKey;
  }
}

class Google_Service_AppState_ListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_AppState_GetResponse';
  protected $itemsDataType = 'array';
  public $kind;
  public $maximumKeyCount;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setMaximumKeyCount($maximumKeyCount)
  {
    $this->maximumKeyCount = $maximumKeyCount;
  }
  public function getMaximumKeyCount()
  {
    return $this->maximumKeyCount;
  }
}

class Google_Service_AppState_UpdateRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $data;
  public $kind;


  public function setData($data)
  {
    $this->data = $data;
  }
  public function getData()
  {
    return $this->data;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}

class Google_Service_AppState_WriteResult extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $currentStateVersion;
  public $kind;
  public $stateKey;


  public function setCurrentStateVersion($currentStateVersion)
  {
    $this->currentStateVersion = $currentStateVersion;
  }
  public function getCurrentStateVersion()
  {
    return $this->currentStateVersion;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setStateKey($stateKey)
  {
    $this->stateKey = $stateKey;
  }
  public function getStateKey()
  {
    return $this->stateKey;
  }
}
