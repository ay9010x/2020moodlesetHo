<?php



class Google_Service_GroupsMigration extends Google_Service
{
  
  const APPS_GROUPS_MIGRATION =
      "https://www.googleapis.com/auth/apps.groups.migration";

  public $archive;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'groups/v1/groups/';
    $this->version = 'v1';
    $this->serviceName = 'groupsmigration';

    $this->archive = new Google_Service_GroupsMigration_Archive_Resource(
        $this,
        $this->serviceName,
        'archive',
        array(
          'methods' => array(
            'insert' => array(
              'path' => '{groupId}/archive',
              'httpMethod' => 'POST',
              'parameters' => array(
                'groupId' => array(
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



class Google_Service_GroupsMigration_Archive_Resource extends Google_Service_Resource
{

  
  public function insert($groupId, $optParams = array())
  {
    $params = array('groupId' => $groupId);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_GroupsMigration_Groups");
  }
}




class Google_Service_GroupsMigration_Groups extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  public $responseCode;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setResponseCode($responseCode)
  {
    $this->responseCode = $responseCode;
  }
  public function getResponseCode()
  {
    return $this->responseCode;
  }
}
