<?php



class Google_Service_DataTransfer extends Google_Service
{
  
  const ADMIN_DATATRANSFER =
      "https://www.googleapis.com/auth/admin.datatransfer";
  
  const ADMIN_DATATRANSFER_READONLY =
      "https://www.googleapis.com/auth/admin.datatransfer.readonly";

  public $applications;
  public $transfers;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'admin/datatransfer/v1/';
    $this->version = 'datatransfer_v1';
    $this->serviceName = 'admin';

    $this->applications = new Google_Service_DataTransfer_Applications_Resource(
        $this,
        $this->serviceName,
        'applications',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'applications/{applicationId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'applicationId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'applications',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'customerId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->transfers = new Google_Service_DataTransfer_Transfers_Resource(
        $this,
        $this->serviceName,
        'transfers',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'transfers/{dataTransferId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'dataTransferId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'transfers',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'list' => array(
              'path' => 'transfers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'newOwnerUserId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'oldOwnerUserId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'customerId' => array(
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



class Google_Service_DataTransfer_Applications_Resource extends Google_Service_Resource
{

  
  public function get($applicationId, $optParams = array())
  {
    $params = array('applicationId' => $applicationId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_DataTransfer_Application");
  }

  
  public function listApplications($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_DataTransfer_ApplicationsListResponse");
  }
}


class Google_Service_DataTransfer_Transfers_Resource extends Google_Service_Resource
{

  
  public function get($dataTransferId, $optParams = array())
  {
    $params = array('dataTransferId' => $dataTransferId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_DataTransfer_DataTransfer");
  }

  
  public function insert(Google_Service_DataTransfer_DataTransfer $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_DataTransfer_DataTransfer");
  }

  
  public function listTransfers($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_DataTransfer_DataTransfersListResponse");
  }
}




class Google_Service_DataTransfer_Application extends Google_Collection
{
  protected $collection_key = 'transferParams';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  public $name;
  protected $transferParamsType = 'Google_Service_DataTransfer_ApplicationTransferParam';
  protected $transferParamsDataType = 'array';


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setTransferParams($transferParams)
  {
    $this->transferParams = $transferParams;
  }
  public function getTransferParams()
  {
    return $this->transferParams;
  }
}

class Google_Service_DataTransfer_ApplicationDataTransfer extends Google_Collection
{
  protected $collection_key = 'applicationTransferParams';
  protected $internal_gapi_mappings = array(
  );
  public $applicationId;
  protected $applicationTransferParamsType = 'Google_Service_DataTransfer_ApplicationTransferParam';
  protected $applicationTransferParamsDataType = 'array';
  public $applicationTransferStatus;


  public function setApplicationId($applicationId)
  {
    $this->applicationId = $applicationId;
  }
  public function getApplicationId()
  {
    return $this->applicationId;
  }
  public function setApplicationTransferParams($applicationTransferParams)
  {
    $this->applicationTransferParams = $applicationTransferParams;
  }
  public function getApplicationTransferParams()
  {
    return $this->applicationTransferParams;
  }
  public function setApplicationTransferStatus($applicationTransferStatus)
  {
    $this->applicationTransferStatus = $applicationTransferStatus;
  }
  public function getApplicationTransferStatus()
  {
    return $this->applicationTransferStatus;
  }
}

class Google_Service_DataTransfer_ApplicationTransferParam extends Google_Collection
{
  protected $collection_key = 'value';
  protected $internal_gapi_mappings = array(
  );
  public $key;
  public $value;


  public function setKey($key)
  {
    $this->key = $key;
  }
  public function getKey()
  {
    return $this->key;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Google_Service_DataTransfer_ApplicationsListResponse extends Google_Collection
{
  protected $collection_key = 'applications';
  protected $internal_gapi_mappings = array(
  );
  protected $applicationsType = 'Google_Service_DataTransfer_Application';
  protected $applicationsDataType = 'array';
  public $etag;
  public $kind;
  public $nextPageToken;


  public function setApplications($applications)
  {
    $this->applications = $applications;
  }
  public function getApplications()
  {
    return $this->applications;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
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

class Google_Service_DataTransfer_DataTransfer extends Google_Collection
{
  protected $collection_key = 'applicationDataTransfers';
  protected $internal_gapi_mappings = array(
  );
  protected $applicationDataTransfersType = 'Google_Service_DataTransfer_ApplicationDataTransfer';
  protected $applicationDataTransfersDataType = 'array';
  public $etag;
  public $id;
  public $kind;
  public $newOwnerUserId;
  public $oldOwnerUserId;
  public $overallTransferStatusCode;
  public $requestTime;


  public function setApplicationDataTransfers($applicationDataTransfers)
  {
    $this->applicationDataTransfers = $applicationDataTransfers;
  }
  public function getApplicationDataTransfers()
  {
    return $this->applicationDataTransfers;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNewOwnerUserId($newOwnerUserId)
  {
    $this->newOwnerUserId = $newOwnerUserId;
  }
  public function getNewOwnerUserId()
  {
    return $this->newOwnerUserId;
  }
  public function setOldOwnerUserId($oldOwnerUserId)
  {
    $this->oldOwnerUserId = $oldOwnerUserId;
  }
  public function getOldOwnerUserId()
  {
    return $this->oldOwnerUserId;
  }
  public function setOverallTransferStatusCode($overallTransferStatusCode)
  {
    $this->overallTransferStatusCode = $overallTransferStatusCode;
  }
  public function getOverallTransferStatusCode()
  {
    return $this->overallTransferStatusCode;
  }
  public function setRequestTime($requestTime)
  {
    $this->requestTime = $requestTime;
  }
  public function getRequestTime()
  {
    return $this->requestTime;
  }
}

class Google_Service_DataTransfer_DataTransfersListResponse extends Google_Collection
{
  protected $collection_key = 'dataTransfers';
  protected $internal_gapi_mappings = array(
  );
  protected $dataTransfersType = 'Google_Service_DataTransfer_DataTransfer';
  protected $dataTransfersDataType = 'array';
  public $etag;
  public $kind;
  public $nextPageToken;


  public function setDataTransfers($dataTransfers)
  {
    $this->dataTransfers = $dataTransfers;
  }
  public function getDataTransfers()
  {
    return $this->dataTransfers;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
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
