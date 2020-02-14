<?php



class Google_Service_Fitness extends Google_Service
{
  
  const FITNESS_ACTIVITY_READ =
      "https://www.googleapis.com/auth/fitness.activity.read";
  
  const FITNESS_ACTIVITY_WRITE =
      "https://www.googleapis.com/auth/fitness.activity.write";
  
  const FITNESS_BODY_READ =
      "https://www.googleapis.com/auth/fitness.body.read";
  
  const FITNESS_BODY_WRITE =
      "https://www.googleapis.com/auth/fitness.body.write";
  
  const FITNESS_LOCATION_READ =
      "https://www.googleapis.com/auth/fitness.location.read";
  
  const FITNESS_LOCATION_WRITE =
      "https://www.googleapis.com/auth/fitness.location.write";

  public $users_dataSources;
  public $users_dataSources_datasets;
  public $users_dataset;
  public $users_sessions;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'fitness/v1/users/';
    $this->version = 'v1';
    $this->serviceName = 'fitness';

    $this->users_dataSources = new Google_Service_Fitness_UsersDataSources_Resource(
        $this,
        $this->serviceName,
        'dataSources',
        array(
          'methods' => array(
            'create' => array(
              'path' => '{userId}/dataSources',
              'httpMethod' => 'POST',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => '{userId}/dataSources/{dataSourceId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{userId}/dataSources/{dataSourceId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{userId}/dataSources',
              'httpMethod' => 'GET',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dataTypeName' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),'patch' => array(
              'path' => '{userId}/dataSources/{dataSourceId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{userId}/dataSources/{dataSourceId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->users_dataSources_datasets = new Google_Service_Fitness_UsersDataSourcesDatasets_Resource(
        $this,
        $this->serviceName,
        'datasets',
        array(
          'methods' => array(
            'delete' => array(
              'path' => '{userId}/dataSources/{dataSourceId}/datasets/{datasetId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'datasetId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'modifiedTimeMillis' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'currentTimeMillis' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => '{userId}/dataSources/{dataSourceId}/datasets/{datasetId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'datasetId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'limit' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => '{userId}/dataSources/{dataSourceId}/datasets/{datasetId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'dataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'datasetId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'currentTimeMillis' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->users_dataset = new Google_Service_Fitness_UsersDataset_Resource(
        $this,
        $this->serviceName,
        'dataset',
        array(
          'methods' => array(
            'aggregate' => array(
              'path' => '{userId}/dataset:aggregate',
              'httpMethod' => 'POST',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->users_sessions = new Google_Service_Fitness_UsersSessions_Resource(
        $this,
        $this->serviceName,
        'sessions',
        array(
          'methods' => array(
            'delete' => array(
              'path' => '{userId}/sessions/{sessionId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sessionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'currentTimeMillis' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => '{userId}/sessions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'includeDeleted' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'startTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => '{userId}/sessions/{sessionId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sessionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'currentTimeMillis' => array(
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



class Google_Service_Fitness_Users_Resource extends Google_Service_Resource
{
}


class Google_Service_Fitness_UsersDataSources_Resource extends Google_Service_Resource
{

  
  public function create($userId, Google_Service_Fitness_DataSource $postBody, $optParams = array())
  {
    $params = array('userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Fitness_DataSource");
  }

  
  public function delete($userId, $dataSourceId, $optParams = array())
  {
    $params = array('userId' => $userId, 'dataSourceId' => $dataSourceId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Fitness_DataSource");
  }

  
  public function get($userId, $dataSourceId, $optParams = array())
  {
    $params = array('userId' => $userId, 'dataSourceId' => $dataSourceId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Fitness_DataSource");
  }

  
  public function listUsersDataSources($userId, $optParams = array())
  {
    $params = array('userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Fitness_ListDataSourcesResponse");
  }

  
  public function patch($userId, $dataSourceId, Google_Service_Fitness_DataSource $postBody, $optParams = array())
  {
    $params = array('userId' => $userId, 'dataSourceId' => $dataSourceId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Fitness_DataSource");
  }

  
  public function update($userId, $dataSourceId, Google_Service_Fitness_DataSource $postBody, $optParams = array())
  {
    $params = array('userId' => $userId, 'dataSourceId' => $dataSourceId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Fitness_DataSource");
  }
}


class Google_Service_Fitness_UsersDataSourcesDatasets_Resource extends Google_Service_Resource
{

  
  public function delete($userId, $dataSourceId, $datasetId, $optParams = array())
  {
    $params = array('userId' => $userId, 'dataSourceId' => $dataSourceId, 'datasetId' => $datasetId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($userId, $dataSourceId, $datasetId, $optParams = array())
  {
    $params = array('userId' => $userId, 'dataSourceId' => $dataSourceId, 'datasetId' => $datasetId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Fitness_Dataset");
  }

  
  public function patch($userId, $dataSourceId, $datasetId, Google_Service_Fitness_Dataset $postBody, $optParams = array())
  {
    $params = array('userId' => $userId, 'dataSourceId' => $dataSourceId, 'datasetId' => $datasetId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Fitness_Dataset");
  }
}

class Google_Service_Fitness_UsersDataset_Resource extends Google_Service_Resource
{

  
  public function aggregate($userId, Google_Service_Fitness_AggregateRequest $postBody, $optParams = array())
  {
    $params = array('userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('aggregate', array($params), "Google_Service_Fitness_AggregateResponse");
  }
}

class Google_Service_Fitness_UsersSessions_Resource extends Google_Service_Resource
{

  
  public function delete($userId, $sessionId, $optParams = array())
  {
    $params = array('userId' => $userId, 'sessionId' => $sessionId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function listUsersSessions($userId, $optParams = array())
  {
    $params = array('userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Fitness_ListSessionsResponse");
  }

  
  public function update($userId, $sessionId, Google_Service_Fitness_Session $postBody, $optParams = array())
  {
    $params = array('userId' => $userId, 'sessionId' => $sessionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Fitness_Session");
  }
}




class Google_Service_Fitness_AggregateBucket extends Google_Collection
{
  protected $collection_key = 'dataset';
  protected $internal_gapi_mappings = array(
  );
  public $activity;
  protected $datasetType = 'Google_Service_Fitness_Dataset';
  protected $datasetDataType = 'array';
  public $endTimeMillis;
  protected $sessionType = 'Google_Service_Fitness_Session';
  protected $sessionDataType = '';
  public $startTimeMillis;
  public $type;


  public function setActivity($activity)
  {
    $this->activity = $activity;
  }
  public function getActivity()
  {
    return $this->activity;
  }
  public function setDataset($dataset)
  {
    $this->dataset = $dataset;
  }
  public function getDataset()
  {
    return $this->dataset;
  }
  public function setEndTimeMillis($endTimeMillis)
  {
    $this->endTimeMillis = $endTimeMillis;
  }
  public function getEndTimeMillis()
  {
    return $this->endTimeMillis;
  }
  public function setSession(Google_Service_Fitness_Session $session)
  {
    $this->session = $session;
  }
  public function getSession()
  {
    return $this->session;
  }
  public function setStartTimeMillis($startTimeMillis)
  {
    $this->startTimeMillis = $startTimeMillis;
  }
  public function getStartTimeMillis()
  {
    return $this->startTimeMillis;
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

class Google_Service_Fitness_AggregateBy extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $dataSourceId;
  public $dataTypeName;


  public function setDataSourceId($dataSourceId)
  {
    $this->dataSourceId = $dataSourceId;
  }
  public function getDataSourceId()
  {
    return $this->dataSourceId;
  }
  public function setDataTypeName($dataTypeName)
  {
    $this->dataTypeName = $dataTypeName;
  }
  public function getDataTypeName()
  {
    return $this->dataTypeName;
  }
}

class Google_Service_Fitness_AggregateRequest extends Google_Collection
{
  protected $collection_key = 'aggregateBy';
  protected $internal_gapi_mappings = array(
  );
  protected $aggregateByType = 'Google_Service_Fitness_AggregateBy';
  protected $aggregateByDataType = 'array';
  protected $bucketByActivitySegmentType = 'Google_Service_Fitness_BucketByActivity';
  protected $bucketByActivitySegmentDataType = '';
  protected $bucketByActivityTypeType = 'Google_Service_Fitness_BucketByActivity';
  protected $bucketByActivityTypeDataType = '';
  protected $bucketBySessionType = 'Google_Service_Fitness_BucketBySession';
  protected $bucketBySessionDataType = '';
  protected $bucketByTimeType = 'Google_Service_Fitness_BucketByTime';
  protected $bucketByTimeDataType = '';
  public $endTimeMillis;
  public $startTimeMillis;


  public function setAggregateBy($aggregateBy)
  {
    $this->aggregateBy = $aggregateBy;
  }
  public function getAggregateBy()
  {
    return $this->aggregateBy;
  }
  public function setBucketByActivitySegment(Google_Service_Fitness_BucketByActivity $bucketByActivitySegment)
  {
    $this->bucketByActivitySegment = $bucketByActivitySegment;
  }
  public function getBucketByActivitySegment()
  {
    return $this->bucketByActivitySegment;
  }
  public function setBucketByActivityType(Google_Service_Fitness_BucketByActivity $bucketByActivityType)
  {
    $this->bucketByActivityType = $bucketByActivityType;
  }
  public function getBucketByActivityType()
  {
    return $this->bucketByActivityType;
  }
  public function setBucketBySession(Google_Service_Fitness_BucketBySession $bucketBySession)
  {
    $this->bucketBySession = $bucketBySession;
  }
  public function getBucketBySession()
  {
    return $this->bucketBySession;
  }
  public function setBucketByTime(Google_Service_Fitness_BucketByTime $bucketByTime)
  {
    $this->bucketByTime = $bucketByTime;
  }
  public function getBucketByTime()
  {
    return $this->bucketByTime;
  }
  public function setEndTimeMillis($endTimeMillis)
  {
    $this->endTimeMillis = $endTimeMillis;
  }
  public function getEndTimeMillis()
  {
    return $this->endTimeMillis;
  }
  public function setStartTimeMillis($startTimeMillis)
  {
    $this->startTimeMillis = $startTimeMillis;
  }
  public function getStartTimeMillis()
  {
    return $this->startTimeMillis;
  }
}

class Google_Service_Fitness_AggregateResponse extends Google_Collection
{
  protected $collection_key = 'bucket';
  protected $internal_gapi_mappings = array(
  );
  protected $bucketType = 'Google_Service_Fitness_AggregateBucket';
  protected $bucketDataType = 'array';


  public function setBucket($bucket)
  {
    $this->bucket = $bucket;
  }
  public function getBucket()
  {
    return $this->bucket;
  }
}

class Google_Service_Fitness_Application extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $detailsUrl;
  public $name;
  public $packageName;
  public $version;


  public function setDetailsUrl($detailsUrl)
  {
    $this->detailsUrl = $detailsUrl;
  }
  public function getDetailsUrl()
  {
    return $this->detailsUrl;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setPackageName($packageName)
  {
    $this->packageName = $packageName;
  }
  public function getPackageName()
  {
    return $this->packageName;
  }
  public function setVersion($version)
  {
    $this->version = $version;
  }
  public function getVersion()
  {
    return $this->version;
  }
}

class Google_Service_Fitness_BucketByActivity extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $activityDataSourceId;
  public $minDurationMillis;


  public function setActivityDataSourceId($activityDataSourceId)
  {
    $this->activityDataSourceId = $activityDataSourceId;
  }
  public function getActivityDataSourceId()
  {
    return $this->activityDataSourceId;
  }
  public function setMinDurationMillis($minDurationMillis)
  {
    $this->minDurationMillis = $minDurationMillis;
  }
  public function getMinDurationMillis()
  {
    return $this->minDurationMillis;
  }
}

class Google_Service_Fitness_BucketBySession extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $minDurationMillis;


  public function setMinDurationMillis($minDurationMillis)
  {
    $this->minDurationMillis = $minDurationMillis;
  }
  public function getMinDurationMillis()
  {
    return $this->minDurationMillis;
  }
}

class Google_Service_Fitness_BucketByTime extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $durationMillis;


  public function setDurationMillis($durationMillis)
  {
    $this->durationMillis = $durationMillis;
  }
  public function getDurationMillis()
  {
    return $this->durationMillis;
  }
}

class Google_Service_Fitness_DataPoint extends Google_Collection
{
  protected $collection_key = 'value';
  protected $internal_gapi_mappings = array(
  );
  public $computationTimeMillis;
  public $dataTypeName;
  public $endTimeNanos;
  public $modifiedTimeMillis;
  public $originDataSourceId;
  public $rawTimestampNanos;
  public $startTimeNanos;
  protected $valueType = 'Google_Service_Fitness_Value';
  protected $valueDataType = 'array';


  public function setComputationTimeMillis($computationTimeMillis)
  {
    $this->computationTimeMillis = $computationTimeMillis;
  }
  public function getComputationTimeMillis()
  {
    return $this->computationTimeMillis;
  }
  public function setDataTypeName($dataTypeName)
  {
    $this->dataTypeName = $dataTypeName;
  }
  public function getDataTypeName()
  {
    return $this->dataTypeName;
  }
  public function setEndTimeNanos($endTimeNanos)
  {
    $this->endTimeNanos = $endTimeNanos;
  }
  public function getEndTimeNanos()
  {
    return $this->endTimeNanos;
  }
  public function setModifiedTimeMillis($modifiedTimeMillis)
  {
    $this->modifiedTimeMillis = $modifiedTimeMillis;
  }
  public function getModifiedTimeMillis()
  {
    return $this->modifiedTimeMillis;
  }
  public function setOriginDataSourceId($originDataSourceId)
  {
    $this->originDataSourceId = $originDataSourceId;
  }
  public function getOriginDataSourceId()
  {
    return $this->originDataSourceId;
  }
  public function setRawTimestampNanos($rawTimestampNanos)
  {
    $this->rawTimestampNanos = $rawTimestampNanos;
  }
  public function getRawTimestampNanos()
  {
    return $this->rawTimestampNanos;
  }
  public function setStartTimeNanos($startTimeNanos)
  {
    $this->startTimeNanos = $startTimeNanos;
  }
  public function getStartTimeNanos()
  {
    return $this->startTimeNanos;
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

class Google_Service_Fitness_DataSource extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $applicationType = 'Google_Service_Fitness_Application';
  protected $applicationDataType = '';
  public $dataStreamId;
  public $dataStreamName;
  protected $dataTypeType = 'Google_Service_Fitness_DataType';
  protected $dataTypeDataType = '';
  protected $deviceType = 'Google_Service_Fitness_Device';
  protected $deviceDataType = '';
  public $name;
  public $type;


  public function setApplication(Google_Service_Fitness_Application $application)
  {
    $this->application = $application;
  }
  public function getApplication()
  {
    return $this->application;
  }
  public function setDataStreamId($dataStreamId)
  {
    $this->dataStreamId = $dataStreamId;
  }
  public function getDataStreamId()
  {
    return $this->dataStreamId;
  }
  public function setDataStreamName($dataStreamName)
  {
    $this->dataStreamName = $dataStreamName;
  }
  public function getDataStreamName()
  {
    return $this->dataStreamName;
  }
  public function setDataType(Google_Service_Fitness_DataType $dataType)
  {
    $this->dataType = $dataType;
  }
  public function getDataType()
  {
    return $this->dataType;
  }
  public function setDevice(Google_Service_Fitness_Device $device)
  {
    $this->device = $device;
  }
  public function getDevice()
  {
    return $this->device;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
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

class Google_Service_Fitness_DataType extends Google_Collection
{
  protected $collection_key = 'field';
  protected $internal_gapi_mappings = array(
  );
  protected $fieldType = 'Google_Service_Fitness_DataTypeField';
  protected $fieldDataType = 'array';
  public $name;


  public function setField($field)
  {
    $this->field = $field;
  }
  public function getField()
  {
    return $this->field;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
}

class Google_Service_Fitness_DataTypeField extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $format;
  public $name;
  public $optional;


  public function setFormat($format)
  {
    $this->format = $format;
  }
  public function getFormat()
  {
    return $this->format;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOptional($optional)
  {
    $this->optional = $optional;
  }
  public function getOptional()
  {
    return $this->optional;
  }
}

class Google_Service_Fitness_Dataset extends Google_Collection
{
  protected $collection_key = 'point';
  protected $internal_gapi_mappings = array(
  );
  public $dataSourceId;
  public $maxEndTimeNs;
  public $minStartTimeNs;
  public $nextPageToken;
  protected $pointType = 'Google_Service_Fitness_DataPoint';
  protected $pointDataType = 'array';


  public function setDataSourceId($dataSourceId)
  {
    $this->dataSourceId = $dataSourceId;
  }
  public function getDataSourceId()
  {
    return $this->dataSourceId;
  }
  public function setMaxEndTimeNs($maxEndTimeNs)
  {
    $this->maxEndTimeNs = $maxEndTimeNs;
  }
  public function getMaxEndTimeNs()
  {
    return $this->maxEndTimeNs;
  }
  public function setMinStartTimeNs($minStartTimeNs)
  {
    $this->minStartTimeNs = $minStartTimeNs;
  }
  public function getMinStartTimeNs()
  {
    return $this->minStartTimeNs;
  }
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setPoint($point)
  {
    $this->point = $point;
  }
  public function getPoint()
  {
    return $this->point;
  }
}

class Google_Service_Fitness_Device extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $manufacturer;
  public $model;
  public $type;
  public $uid;
  public $version;


  public function setManufacturer($manufacturer)
  {
    $this->manufacturer = $manufacturer;
  }
  public function getManufacturer()
  {
    return $this->manufacturer;
  }
  public function setModel($model)
  {
    $this->model = $model;
  }
  public function getModel()
  {
    return $this->model;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUid($uid)
  {
    $this->uid = $uid;
  }
  public function getUid()
  {
    return $this->uid;
  }
  public function setVersion($version)
  {
    $this->version = $version;
  }
  public function getVersion()
  {
    return $this->version;
  }
}

class Google_Service_Fitness_ListDataSourcesResponse extends Google_Collection
{
  protected $collection_key = 'dataSource';
  protected $internal_gapi_mappings = array(
  );
  protected $dataSourceType = 'Google_Service_Fitness_DataSource';
  protected $dataSourceDataType = 'array';


  public function setDataSource($dataSource)
  {
    $this->dataSource = $dataSource;
  }
  public function getDataSource()
  {
    return $this->dataSource;
  }
}

class Google_Service_Fitness_ListSessionsResponse extends Google_Collection
{
  protected $collection_key = 'session';
  protected $internal_gapi_mappings = array(
  );
  protected $deletedSessionType = 'Google_Service_Fitness_Session';
  protected $deletedSessionDataType = 'array';
  public $nextPageToken;
  protected $sessionType = 'Google_Service_Fitness_Session';
  protected $sessionDataType = 'array';


  public function setDeletedSession($deletedSession)
  {
    $this->deletedSession = $deletedSession;
  }
  public function getDeletedSession()
  {
    return $this->deletedSession;
  }
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setSession($session)
  {
    $this->session = $session;
  }
  public function getSession()
  {
    return $this->session;
  }
}

class Google_Service_Fitness_MapValue extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $fpVal;


  public function setFpVal($fpVal)
  {
    $this->fpVal = $fpVal;
  }
  public function getFpVal()
  {
    return $this->fpVal;
  }
}

class Google_Service_Fitness_Session extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $activeTimeMillis;
  public $activityType;
  protected $applicationType = 'Google_Service_Fitness_Application';
  protected $applicationDataType = '';
  public $description;
  public $endTimeMillis;
  public $id;
  public $modifiedTimeMillis;
  public $name;
  public $startTimeMillis;


  public function setActiveTimeMillis($activeTimeMillis)
  {
    $this->activeTimeMillis = $activeTimeMillis;
  }
  public function getActiveTimeMillis()
  {
    return $this->activeTimeMillis;
  }
  public function setActivityType($activityType)
  {
    $this->activityType = $activityType;
  }
  public function getActivityType()
  {
    return $this->activityType;
  }
  public function setApplication(Google_Service_Fitness_Application $application)
  {
    $this->application = $application;
  }
  public function getApplication()
  {
    return $this->application;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setEndTimeMillis($endTimeMillis)
  {
    $this->endTimeMillis = $endTimeMillis;
  }
  public function getEndTimeMillis()
  {
    return $this->endTimeMillis;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setModifiedTimeMillis($modifiedTimeMillis)
  {
    $this->modifiedTimeMillis = $modifiedTimeMillis;
  }
  public function getModifiedTimeMillis()
  {
    return $this->modifiedTimeMillis;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setStartTimeMillis($startTimeMillis)
  {
    $this->startTimeMillis = $startTimeMillis;
  }
  public function getStartTimeMillis()
  {
    return $this->startTimeMillis;
  }
}

class Google_Service_Fitness_Value extends Google_Collection
{
  protected $collection_key = 'mapVal';
  protected $internal_gapi_mappings = array(
  );
  public $fpVal;
  public $intVal;
  protected $mapValType = 'Google_Service_Fitness_ValueMapValEntry';
  protected $mapValDataType = 'array';
  public $stringVal;


  public function setFpVal($fpVal)
  {
    $this->fpVal = $fpVal;
  }
  public function getFpVal()
  {
    return $this->fpVal;
  }
  public function setIntVal($intVal)
  {
    $this->intVal = $intVal;
  }
  public function getIntVal()
  {
    return $this->intVal;
  }
  public function setMapVal($mapVal)
  {
    $this->mapVal = $mapVal;
  }
  public function getMapVal()
  {
    return $this->mapVal;
  }
  public function setStringVal($stringVal)
  {
    $this->stringVal = $stringVal;
  }
  public function getStringVal()
  {
    return $this->stringVal;
  }
}

class Google_Service_Fitness_ValueMapValEntry extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $key;
  protected $valueType = 'Google_Service_Fitness_MapValue';
  protected $valueDataType = '';


  public function setKey($key)
  {
    $this->key = $key;
  }
  public function getKey()
  {
    return $this->key;
  }
  public function setValue(Google_Service_Fitness_MapValue $value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}
