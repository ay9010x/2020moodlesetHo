<?php



class Google_Service_Storagetransfer extends Google_Service
{
  
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";

  public $googleServiceAccounts;
  public $transferJobs;
  public $transferOperations;
  public $v1;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://storagetransfer.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1';
    $this->serviceName = 'storagetransfer';

    $this->googleServiceAccounts = new Google_Service_Storagetransfer_GoogleServiceAccounts_Resource(
        $this,
        $this->serviceName,
        'googleServiceAccounts',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/googleServiceAccounts/{projectId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'projectId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->transferJobs = new Google_Service_Storagetransfer_TransferJobs_Resource(
        $this,
        $this->serviceName,
        'transferJobs',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/transferJobs',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'get' => array(
              'path' => 'v1/{+jobName}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'jobName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'projectId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/transferJobs',
              'httpMethod' => 'GET',
              'parameters' => array(
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
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
            ),'patch' => array(
              'path' => 'v1/{+jobName}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'jobName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->transferOperations = new Google_Service_Storagetransfer_TransferOperations_Resource(
        $this,
        $this->serviceName,
        'transferOperations',
        array(
          'methods' => array(
            'cancel' => array(
              'path' => 'v1/{+name}:cancel',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
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
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
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
            ),'pause' => array(
              'path' => 'v1/{+name}:pause',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'resume' => array(
              'path' => 'v1/{+name}:resume',
              'httpMethod' => 'POST',
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
    $this->v1 = new Google_Service_Storagetransfer_V1_Resource(
        $this,
        $this->serviceName,
        'v1',
        array(
          'methods' => array(
            'getGoogleServiceAccount' => array(
              'path' => 'v1:getGoogleServiceAccount',
              'httpMethod' => 'GET',
              'parameters' => array(
                'projectId' => array(
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



class Google_Service_Storagetransfer_GoogleServiceAccounts_Resource extends Google_Service_Resource
{

  
  public function get($projectId, $optParams = array())
  {
    $params = array('projectId' => $projectId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Storagetransfer_GoogleServiceAccount");
  }
}


class Google_Service_Storagetransfer_TransferJobs_Resource extends Google_Service_Resource
{

  
  public function create(Google_Service_Storagetransfer_TransferJob $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Storagetransfer_TransferJob");
  }

  
  public function get($jobName, $optParams = array())
  {
    $params = array('jobName' => $jobName);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Storagetransfer_TransferJob");
  }

  
  public function listTransferJobs($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Storagetransfer_ListTransferJobsResponse");
  }

  
  public function patch($jobName, Google_Service_Storagetransfer_UpdateTransferJobRequest $postBody, $optParams = array())
  {
    $params = array('jobName' => $jobName, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Storagetransfer_TransferJob");
  }
}


class Google_Service_Storagetransfer_TransferOperations_Resource extends Google_Service_Resource
{

  
  public function cancel($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('cancel', array($params), "Google_Service_Storagetransfer_Empty");
  }

  
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Storagetransfer_Empty");
  }

  
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Storagetransfer_Operation");
  }

  
  public function listTransferOperations($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Storagetransfer_ListOperationsResponse");
  }

  
  public function pause($name, Google_Service_Storagetransfer_PauseTransferOperationRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('pause', array($params), "Google_Service_Storagetransfer_Empty");
  }

  
  public function resume($name, Google_Service_Storagetransfer_ResumeTransferOperationRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('resume', array($params), "Google_Service_Storagetransfer_Empty");
  }
}


class Google_Service_Storagetransfer_V1_Resource extends Google_Service_Resource
{

  
  public function getGoogleServiceAccount($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('getGoogleServiceAccount', array($params), "Google_Service_Storagetransfer_GoogleServiceAccount");
  }
}




class Google_Service_Storagetransfer_AwsAccessKey extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accessKeyId;
  public $secretAccessKey;


  public function setAccessKeyId($accessKeyId)
  {
    $this->accessKeyId = $accessKeyId;
  }
  public function getAccessKeyId()
  {
    return $this->accessKeyId;
  }
  public function setSecretAccessKey($secretAccessKey)
  {
    $this->secretAccessKey = $secretAccessKey;
  }
  public function getSecretAccessKey()
  {
    return $this->secretAccessKey;
  }
}

class Google_Service_Storagetransfer_AwsS3Data extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $awsAccessKeyType = 'Google_Service_Storagetransfer_AwsAccessKey';
  protected $awsAccessKeyDataType = '';
  public $bucketName;


  public function setAwsAccessKey(Google_Service_Storagetransfer_AwsAccessKey $awsAccessKey)
  {
    $this->awsAccessKey = $awsAccessKey;
  }
  public function getAwsAccessKey()
  {
    return $this->awsAccessKey;
  }
  public function setBucketName($bucketName)
  {
    $this->bucketName = $bucketName;
  }
  public function getBucketName()
  {
    return $this->bucketName;
  }
}

class Google_Service_Storagetransfer_Date extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $day;
  public $month;
  public $year;


  public function setDay($day)
  {
    $this->day = $day;
  }
  public function getDay()
  {
    return $this->day;
  }
  public function setMonth($month)
  {
    $this->month = $month;
  }
  public function getMonth()
  {
    return $this->month;
  }
  public function setYear($year)
  {
    $this->year = $year;
  }
  public function getYear()
  {
    return $this->year;
  }
}

class Google_Service_Storagetransfer_Empty extends Google_Model
{
}

class Google_Service_Storagetransfer_ErrorLogEntry extends Google_Collection
{
  protected $collection_key = 'errorDetails';
  protected $internal_gapi_mappings = array(
  );
  public $errorDetails;
  public $url;


  public function setErrorDetails($errorDetails)
  {
    $this->errorDetails = $errorDetails;
  }
  public function getErrorDetails()
  {
    return $this->errorDetails;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Storagetransfer_ErrorSummary extends Google_Collection
{
  protected $collection_key = 'errorLogEntries';
  protected $internal_gapi_mappings = array(
  );
  public $errorCode;
  public $errorCount;
  protected $errorLogEntriesType = 'Google_Service_Storagetransfer_ErrorLogEntry';
  protected $errorLogEntriesDataType = 'array';


  public function setErrorCode($errorCode)
  {
    $this->errorCode = $errorCode;
  }
  public function getErrorCode()
  {
    return $this->errorCode;
  }
  public function setErrorCount($errorCount)
  {
    $this->errorCount = $errorCount;
  }
  public function getErrorCount()
  {
    return $this->errorCount;
  }
  public function setErrorLogEntries($errorLogEntries)
  {
    $this->errorLogEntries = $errorLogEntries;
  }
  public function getErrorLogEntries()
  {
    return $this->errorLogEntries;
  }
}

class Google_Service_Storagetransfer_GcsData extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $bucketName;


  public function setBucketName($bucketName)
  {
    $this->bucketName = $bucketName;
  }
  public function getBucketName()
  {
    return $this->bucketName;
  }
}

class Google_Service_Storagetransfer_GoogleServiceAccount extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountEmail;


  public function setAccountEmail($accountEmail)
  {
    $this->accountEmail = $accountEmail;
  }
  public function getAccountEmail()
  {
    return $this->accountEmail;
  }
}

class Google_Service_Storagetransfer_HttpData extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $listUrl;


  public function setListUrl($listUrl)
  {
    $this->listUrl = $listUrl;
  }
  public function getListUrl()
  {
    return $this->listUrl;
  }
}

class Google_Service_Storagetransfer_ListOperationsResponse extends Google_Collection
{
  protected $collection_key = 'operations';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $operationsType = 'Google_Service_Storagetransfer_Operation';
  protected $operationsDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setOperations($operations)
  {
    $this->operations = $operations;
  }
  public function getOperations()
  {
    return $this->operations;
  }
}

class Google_Service_Storagetransfer_ListTransferJobsResponse extends Google_Collection
{
  protected $collection_key = 'transferJobs';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $transferJobsType = 'Google_Service_Storagetransfer_TransferJob';
  protected $transferJobsDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setTransferJobs($transferJobs)
  {
    $this->transferJobs = $transferJobs;
  }
  public function getTransferJobs()
  {
    return $this->transferJobs;
  }
}

class Google_Service_Storagetransfer_ObjectConditions extends Google_Collection
{
  protected $collection_key = 'includePrefixes';
  protected $internal_gapi_mappings = array(
  );
  public $excludePrefixes;
  public $includePrefixes;
  public $maxTimeElapsedSinceLastModification;
  public $minTimeElapsedSinceLastModification;


  public function setExcludePrefixes($excludePrefixes)
  {
    $this->excludePrefixes = $excludePrefixes;
  }
  public function getExcludePrefixes()
  {
    return $this->excludePrefixes;
  }
  public function setIncludePrefixes($includePrefixes)
  {
    $this->includePrefixes = $includePrefixes;
  }
  public function getIncludePrefixes()
  {
    return $this->includePrefixes;
  }
  public function setMaxTimeElapsedSinceLastModification($maxTimeElapsedSinceLastModification)
  {
    $this->maxTimeElapsedSinceLastModification = $maxTimeElapsedSinceLastModification;
  }
  public function getMaxTimeElapsedSinceLastModification()
  {
    return $this->maxTimeElapsedSinceLastModification;
  }
  public function setMinTimeElapsedSinceLastModification($minTimeElapsedSinceLastModification)
  {
    $this->minTimeElapsedSinceLastModification = $minTimeElapsedSinceLastModification;
  }
  public function getMinTimeElapsedSinceLastModification()
  {
    return $this->minTimeElapsedSinceLastModification;
  }
}

class Google_Service_Storagetransfer_Operation extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $done;
  protected $errorType = 'Google_Service_Storagetransfer_Status';
  protected $errorDataType = '';
  public $metadata;
  public $name;
  public $response;


  public function setDone($done)
  {
    $this->done = $done;
  }
  public function getDone()
  {
    return $this->done;
  }
  public function setError(Google_Service_Storagetransfer_Status $error)
  {
    $this->error = $error;
  }
  public function getError()
  {
    return $this->error;
  }
  public function setMetadata($metadata)
  {
    $this->metadata = $metadata;
  }
  public function getMetadata()
  {
    return $this->metadata;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setResponse($response)
  {
    $this->response = $response;
  }
  public function getResponse()
  {
    return $this->response;
  }
}

class Google_Service_Storagetransfer_OperationMetadata extends Google_Model
{
}

class Google_Service_Storagetransfer_OperationResponse extends Google_Model
{
}

class Google_Service_Storagetransfer_PauseTransferOperationRequest extends Google_Model
{
}

class Google_Service_Storagetransfer_ResumeTransferOperationRequest extends Google_Model
{
}

class Google_Service_Storagetransfer_Schedule extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $scheduleEndDateType = 'Google_Service_Storagetransfer_Date';
  protected $scheduleEndDateDataType = '';
  protected $scheduleStartDateType = 'Google_Service_Storagetransfer_Date';
  protected $scheduleStartDateDataType = '';
  protected $startTimeOfDayType = 'Google_Service_Storagetransfer_TimeOfDay';
  protected $startTimeOfDayDataType = '';


  public function setScheduleEndDate(Google_Service_Storagetransfer_Date $scheduleEndDate)
  {
    $this->scheduleEndDate = $scheduleEndDate;
  }
  public function getScheduleEndDate()
  {
    return $this->scheduleEndDate;
  }
  public function setScheduleStartDate(Google_Service_Storagetransfer_Date $scheduleStartDate)
  {
    $this->scheduleStartDate = $scheduleStartDate;
  }
  public function getScheduleStartDate()
  {
    return $this->scheduleStartDate;
  }
  public function setStartTimeOfDay(Google_Service_Storagetransfer_TimeOfDay $startTimeOfDay)
  {
    $this->startTimeOfDay = $startTimeOfDay;
  }
  public function getStartTimeOfDay()
  {
    return $this->startTimeOfDay;
  }
}

class Google_Service_Storagetransfer_Status extends Google_Collection
{
  protected $collection_key = 'details';
  protected $internal_gapi_mappings = array(
  );
  public $code;
  public $details;
  public $message;


  public function setCode($code)
  {
    $this->code = $code;
  }
  public function getCode()
  {
    return $this->code;
  }
  public function setDetails($details)
  {
    $this->details = $details;
  }
  public function getDetails()
  {
    return $this->details;
  }
  public function setMessage($message)
  {
    $this->message = $message;
  }
  public function getMessage()
  {
    return $this->message;
  }
}

class Google_Service_Storagetransfer_StatusDetails extends Google_Model
{
}

class Google_Service_Storagetransfer_TimeOfDay extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $hours;
  public $minutes;
  public $nanos;
  public $seconds;


  public function setHours($hours)
  {
    $this->hours = $hours;
  }
  public function getHours()
  {
    return $this->hours;
  }
  public function setMinutes($minutes)
  {
    $this->minutes = $minutes;
  }
  public function getMinutes()
  {
    return $this->minutes;
  }
  public function setNanos($nanos)
  {
    $this->nanos = $nanos;
  }
  public function getNanos()
  {
    return $this->nanos;
  }
  public function setSeconds($seconds)
  {
    $this->seconds = $seconds;
  }
  public function getSeconds()
  {
    return $this->seconds;
  }
}

class Google_Service_Storagetransfer_TransferCounters extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $bytesCopiedToSink;
  public $bytesDeletedFromSink;
  public $bytesDeletedFromSource;
  public $bytesFailedToDeleteFromSink;
  public $bytesFoundFromSource;
  public $bytesFoundOnlyFromSink;
  public $bytesFromSourceFailed;
  public $bytesFromSourceSkippedBySync;
  public $objectsCopiedToSink;
  public $objectsDeletedFromSink;
  public $objectsDeletedFromSource;
  public $objectsFailedToDeleteFromSink;
  public $objectsFoundFromSource;
  public $objectsFoundOnlyFromSink;
  public $objectsFromSourceFailed;
  public $objectsFromSourceSkippedBySync;


  public function setBytesCopiedToSink($bytesCopiedToSink)
  {
    $this->bytesCopiedToSink = $bytesCopiedToSink;
  }
  public function getBytesCopiedToSink()
  {
    return $this->bytesCopiedToSink;
  }
  public function setBytesDeletedFromSink($bytesDeletedFromSink)
  {
    $this->bytesDeletedFromSink = $bytesDeletedFromSink;
  }
  public function getBytesDeletedFromSink()
  {
    return $this->bytesDeletedFromSink;
  }
  public function setBytesDeletedFromSource($bytesDeletedFromSource)
  {
    $this->bytesDeletedFromSource = $bytesDeletedFromSource;
  }
  public function getBytesDeletedFromSource()
  {
    return $this->bytesDeletedFromSource;
  }
  public function setBytesFailedToDeleteFromSink($bytesFailedToDeleteFromSink)
  {
    $this->bytesFailedToDeleteFromSink = $bytesFailedToDeleteFromSink;
  }
  public function getBytesFailedToDeleteFromSink()
  {
    return $this->bytesFailedToDeleteFromSink;
  }
  public function setBytesFoundFromSource($bytesFoundFromSource)
  {
    $this->bytesFoundFromSource = $bytesFoundFromSource;
  }
  public function getBytesFoundFromSource()
  {
    return $this->bytesFoundFromSource;
  }
  public function setBytesFoundOnlyFromSink($bytesFoundOnlyFromSink)
  {
    $this->bytesFoundOnlyFromSink = $bytesFoundOnlyFromSink;
  }
  public function getBytesFoundOnlyFromSink()
  {
    return $this->bytesFoundOnlyFromSink;
  }
  public function setBytesFromSourceFailed($bytesFromSourceFailed)
  {
    $this->bytesFromSourceFailed = $bytesFromSourceFailed;
  }
  public function getBytesFromSourceFailed()
  {
    return $this->bytesFromSourceFailed;
  }
  public function setBytesFromSourceSkippedBySync($bytesFromSourceSkippedBySync)
  {
    $this->bytesFromSourceSkippedBySync = $bytesFromSourceSkippedBySync;
  }
  public function getBytesFromSourceSkippedBySync()
  {
    return $this->bytesFromSourceSkippedBySync;
  }
  public function setObjectsCopiedToSink($objectsCopiedToSink)
  {
    $this->objectsCopiedToSink = $objectsCopiedToSink;
  }
  public function getObjectsCopiedToSink()
  {
    return $this->objectsCopiedToSink;
  }
  public function setObjectsDeletedFromSink($objectsDeletedFromSink)
  {
    $this->objectsDeletedFromSink = $objectsDeletedFromSink;
  }
  public function getObjectsDeletedFromSink()
  {
    return $this->objectsDeletedFromSink;
  }
  public function setObjectsDeletedFromSource($objectsDeletedFromSource)
  {
    $this->objectsDeletedFromSource = $objectsDeletedFromSource;
  }
  public function getObjectsDeletedFromSource()
  {
    return $this->objectsDeletedFromSource;
  }
  public function setObjectsFailedToDeleteFromSink($objectsFailedToDeleteFromSink)
  {
    $this->objectsFailedToDeleteFromSink = $objectsFailedToDeleteFromSink;
  }
  public function getObjectsFailedToDeleteFromSink()
  {
    return $this->objectsFailedToDeleteFromSink;
  }
  public function setObjectsFoundFromSource($objectsFoundFromSource)
  {
    $this->objectsFoundFromSource = $objectsFoundFromSource;
  }
  public function getObjectsFoundFromSource()
  {
    return $this->objectsFoundFromSource;
  }
  public function setObjectsFoundOnlyFromSink($objectsFoundOnlyFromSink)
  {
    $this->objectsFoundOnlyFromSink = $objectsFoundOnlyFromSink;
  }
  public function getObjectsFoundOnlyFromSink()
  {
    return $this->objectsFoundOnlyFromSink;
  }
  public function setObjectsFromSourceFailed($objectsFromSourceFailed)
  {
    $this->objectsFromSourceFailed = $objectsFromSourceFailed;
  }
  public function getObjectsFromSourceFailed()
  {
    return $this->objectsFromSourceFailed;
  }
  public function setObjectsFromSourceSkippedBySync($objectsFromSourceSkippedBySync)
  {
    $this->objectsFromSourceSkippedBySync = $objectsFromSourceSkippedBySync;
  }
  public function getObjectsFromSourceSkippedBySync()
  {
    return $this->objectsFromSourceSkippedBySync;
  }
}

class Google_Service_Storagetransfer_TransferJob extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $creationTime;
  public $deletionTime;
  public $description;
  public $lastModificationTime;
  public $name;
  public $projectId;
  protected $scheduleType = 'Google_Service_Storagetransfer_Schedule';
  protected $scheduleDataType = '';
  public $status;
  protected $transferSpecType = 'Google_Service_Storagetransfer_TransferSpec';
  protected $transferSpecDataType = '';


  public function setCreationTime($creationTime)
  {
    $this->creationTime = $creationTime;
  }
  public function getCreationTime()
  {
    return $this->creationTime;
  }
  public function setDeletionTime($deletionTime)
  {
    $this->deletionTime = $deletionTime;
  }
  public function getDeletionTime()
  {
    return $this->deletionTime;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLastModificationTime($lastModificationTime)
  {
    $this->lastModificationTime = $lastModificationTime;
  }
  public function getLastModificationTime()
  {
    return $this->lastModificationTime;
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
  public function setSchedule(Google_Service_Storagetransfer_Schedule $schedule)
  {
    $this->schedule = $schedule;
  }
  public function getSchedule()
  {
    return $this->schedule;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTransferSpec(Google_Service_Storagetransfer_TransferSpec $transferSpec)
  {
    $this->transferSpec = $transferSpec;
  }
  public function getTransferSpec()
  {
    return $this->transferSpec;
  }
}

class Google_Service_Storagetransfer_TransferOperation extends Google_Collection
{
  protected $collection_key = 'errorBreakdowns';
  protected $internal_gapi_mappings = array(
  );
  protected $countersType = 'Google_Service_Storagetransfer_TransferCounters';
  protected $countersDataType = '';
  public $endTime;
  protected $errorBreakdownsType = 'Google_Service_Storagetransfer_ErrorSummary';
  protected $errorBreakdownsDataType = 'array';
  public $name;
  public $projectId;
  public $startTime;
  public $status;
  public $transferJobName;
  protected $transferSpecType = 'Google_Service_Storagetransfer_TransferSpec';
  protected $transferSpecDataType = '';


  public function setCounters(Google_Service_Storagetransfer_TransferCounters $counters)
  {
    $this->counters = $counters;
  }
  public function getCounters()
  {
    return $this->counters;
  }
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setErrorBreakdowns($errorBreakdowns)
  {
    $this->errorBreakdowns = $errorBreakdowns;
  }
  public function getErrorBreakdowns()
  {
    return $this->errorBreakdowns;
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
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  public function getStartTime()
  {
    return $this->startTime;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTransferJobName($transferJobName)
  {
    $this->transferJobName = $transferJobName;
  }
  public function getTransferJobName()
  {
    return $this->transferJobName;
  }
  public function setTransferSpec(Google_Service_Storagetransfer_TransferSpec $transferSpec)
  {
    $this->transferSpec = $transferSpec;
  }
  public function getTransferSpec()
  {
    return $this->transferSpec;
  }
}

class Google_Service_Storagetransfer_TransferOptions extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $deleteObjectsFromSourceAfterTransfer;
  public $deleteObjectsUniqueInSink;
  public $overwriteObjectsAlreadyExistingInSink;


  public function setDeleteObjectsFromSourceAfterTransfer($deleteObjectsFromSourceAfterTransfer)
  {
    $this->deleteObjectsFromSourceAfterTransfer = $deleteObjectsFromSourceAfterTransfer;
  }
  public function getDeleteObjectsFromSourceAfterTransfer()
  {
    return $this->deleteObjectsFromSourceAfterTransfer;
  }
  public function setDeleteObjectsUniqueInSink($deleteObjectsUniqueInSink)
  {
    $this->deleteObjectsUniqueInSink = $deleteObjectsUniqueInSink;
  }
  public function getDeleteObjectsUniqueInSink()
  {
    return $this->deleteObjectsUniqueInSink;
  }
  public function setOverwriteObjectsAlreadyExistingInSink($overwriteObjectsAlreadyExistingInSink)
  {
    $this->overwriteObjectsAlreadyExistingInSink = $overwriteObjectsAlreadyExistingInSink;
  }
  public function getOverwriteObjectsAlreadyExistingInSink()
  {
    return $this->overwriteObjectsAlreadyExistingInSink;
  }
}

class Google_Service_Storagetransfer_TransferSpec extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $awsS3DataSourceType = 'Google_Service_Storagetransfer_AwsS3Data';
  protected $awsS3DataSourceDataType = '';
  protected $gcsDataSinkType = 'Google_Service_Storagetransfer_GcsData';
  protected $gcsDataSinkDataType = '';
  protected $gcsDataSourceType = 'Google_Service_Storagetransfer_GcsData';
  protected $gcsDataSourceDataType = '';
  protected $httpDataSourceType = 'Google_Service_Storagetransfer_HttpData';
  protected $httpDataSourceDataType = '';
  protected $objectConditionsType = 'Google_Service_Storagetransfer_ObjectConditions';
  protected $objectConditionsDataType = '';
  protected $transferOptionsType = 'Google_Service_Storagetransfer_TransferOptions';
  protected $transferOptionsDataType = '';


  public function setAwsS3DataSource(Google_Service_Storagetransfer_AwsS3Data $awsS3DataSource)
  {
    $this->awsS3DataSource = $awsS3DataSource;
  }
  public function getAwsS3DataSource()
  {
    return $this->awsS3DataSource;
  }
  public function setGcsDataSink(Google_Service_Storagetransfer_GcsData $gcsDataSink)
  {
    $this->gcsDataSink = $gcsDataSink;
  }
  public function getGcsDataSink()
  {
    return $this->gcsDataSink;
  }
  public function setGcsDataSource(Google_Service_Storagetransfer_GcsData $gcsDataSource)
  {
    $this->gcsDataSource = $gcsDataSource;
  }
  public function getGcsDataSource()
  {
    return $this->gcsDataSource;
  }
  public function setHttpDataSource(Google_Service_Storagetransfer_HttpData $httpDataSource)
  {
    $this->httpDataSource = $httpDataSource;
  }
  public function getHttpDataSource()
  {
    return $this->httpDataSource;
  }
  public function setObjectConditions(Google_Service_Storagetransfer_ObjectConditions $objectConditions)
  {
    $this->objectConditions = $objectConditions;
  }
  public function getObjectConditions()
  {
    return $this->objectConditions;
  }
  public function setTransferOptions(Google_Service_Storagetransfer_TransferOptions $transferOptions)
  {
    $this->transferOptions = $transferOptions;
  }
  public function getTransferOptions()
  {
    return $this->transferOptions;
  }
}

class Google_Service_Storagetransfer_UpdateTransferJobRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $projectId;
  protected $transferJobType = 'Google_Service_Storagetransfer_TransferJob';
  protected $transferJobDataType = '';
  public $updateTransferJobFieldMask;


  public function setProjectId($projectId)
  {
    $this->projectId = $projectId;
  }
  public function getProjectId()
  {
    return $this->projectId;
  }
  public function setTransferJob(Google_Service_Storagetransfer_TransferJob $transferJob)
  {
    $this->transferJob = $transferJob;
  }
  public function getTransferJob()
  {
    return $this->transferJob;
  }
  public function setUpdateTransferJobFieldMask($updateTransferJobFieldMask)
  {
    $this->updateTransferJobFieldMask = $updateTransferJobFieldMask;
  }
  public function getUpdateTransferJobFieldMask()
  {
    return $this->updateTransferJobFieldMask;
  }
}
