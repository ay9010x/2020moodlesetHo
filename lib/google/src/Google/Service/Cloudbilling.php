<?php



class Google_Service_Cloudbilling extends Google_Service
{
  
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";

  public $billingAccounts;
  public $billingAccounts_projects;
  public $projects;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://cloudbilling.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1';
    $this->serviceName = 'cloudbilling';

    $this->billingAccounts = new Google_Service_Cloudbilling_BillingAccounts_Resource(
        $this,
        $this->serviceName,
        'billingAccounts',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/billingAccounts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->billingAccounts_projects = new Google_Service_Cloudbilling_BillingAccountsProjects_Resource(
        $this,
        $this->serviceName,
        'projects',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+name}/projects',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->projects = new Google_Service_Cloudbilling_Projects_Resource(
        $this,
        $this->serviceName,
        'projects',
        array(
          'methods' => array(
            'getBillingInfo' => array(
              'path' => 'v1/{+name}/billingInfo',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateBillingInfo' => array(
              'path' => 'v1/{+name}/billingInfo',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
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



class Google_Service_Cloudbilling_BillingAccounts_Resource extends Google_Service_Resource
{

  
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Cloudbilling_BillingAccount");
  }

  
  public function listBillingAccounts($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Cloudbilling_ListBillingAccountsResponse");
  }
}


class Google_Service_Cloudbilling_BillingAccountsProjects_Resource extends Google_Service_Resource
{

  
  public function listBillingAccountsProjects($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Cloudbilling_ListProjectBillingInfoResponse");
  }
}


class Google_Service_Cloudbilling_Projects_Resource extends Google_Service_Resource
{

  
  public function getBillingInfo($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('getBillingInfo', array($params), "Google_Service_Cloudbilling_ProjectBillingInfo");
  }

  
  public function updateBillingInfo($name, Google_Service_Cloudbilling_ProjectBillingInfo $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updateBillingInfo', array($params), "Google_Service_Cloudbilling_ProjectBillingInfo");
  }
}




class Google_Service_Cloudbilling_BillingAccount extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $displayName;
  public $name;
  public $open;


  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOpen($open)
  {
    $this->open = $open;
  }
  public function getOpen()
  {
    return $this->open;
  }
}

class Google_Service_Cloudbilling_ListBillingAccountsResponse extends Google_Collection
{
  protected $collection_key = 'billingAccounts';
  protected $internal_gapi_mappings = array(
  );
  protected $billingAccountsType = 'Google_Service_Cloudbilling_BillingAccount';
  protected $billingAccountsDataType = 'array';
  public $nextPageToken;


  public function setBillingAccounts($billingAccounts)
  {
    $this->billingAccounts = $billingAccounts;
  }
  public function getBillingAccounts()
  {
    return $this->billingAccounts;
  }
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

class Google_Service_Cloudbilling_ListProjectBillingInfoResponse extends Google_Collection
{
  protected $collection_key = 'projectBillingInfo';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $projectBillingInfoType = 'Google_Service_Cloudbilling_ProjectBillingInfo';
  protected $projectBillingInfoDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setProjectBillingInfo($projectBillingInfo)
  {
    $this->projectBillingInfo = $projectBillingInfo;
  }
  public function getProjectBillingInfo()
  {
    return $this->projectBillingInfo;
  }
}

class Google_Service_Cloudbilling_ProjectBillingInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $billingAccountName;
  public $billingEnabled;
  public $name;
  public $projectId;


  public function setBillingAccountName($billingAccountName)
  {
    $this->billingAccountName = $billingAccountName;
  }
  public function getBillingAccountName()
  {
    return $this->billingAccountName;
  }
  public function setBillingEnabled($billingEnabled)
  {
    $this->billingEnabled = $billingEnabled;
  }
  public function getBillingEnabled()
  {
    return $this->billingEnabled;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setProjectId($projectId)
  {
    $this->projectId = $projectId;
  }
  public function getProjectId()
  {
    return $this->projectId;
  }
}
