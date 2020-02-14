<?php



class Google_Service_CloudMonitoring extends Google_Service
{
  
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";
  
  const MONITORING =
      "https://www.googleapis.com/auth/monitoring";

  public $metricDescriptors;
  public $timeseries;
  public $timeseriesDescriptors;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'cloudmonitoring/v2beta2/projects/';
    $this->version = 'v2beta2';
    $this->serviceName = 'cloudmonitoring';

    $this->metricDescriptors = new Google_Service_CloudMonitoring_MetricDescriptors_Resource(
        $this,
        $this->serviceName,
        'metricDescriptors',
        array(
          'methods' => array(
            'create' => array(
              'path' => '{project}/metricDescriptors',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => '{project}/metricDescriptors/{metric}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'metric' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{project}/metricDescriptors',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'count' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'query' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->timeseries = new Google_Service_CloudMonitoring_Timeseries_Resource(
        $this,
        $this->serviceName,
        'timeseries',
        array(
          'methods' => array(
            'list' => array(
              'path' => '{project}/timeseries/{metric}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'metric' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'youngest' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'count' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'timespan' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'aggregator' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'labels' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'window' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'oldest' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'write' => array(
              'path' => '{project}/timeseries:write',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->timeseriesDescriptors = new Google_Service_CloudMonitoring_TimeseriesDescriptors_Resource(
        $this,
        $this->serviceName,
        'timeseriesDescriptors',
        array(
          'methods' => array(
            'list' => array(
              'path' => '{project}/timeseriesDescriptors/{metric}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'metric' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'youngest' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'count' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'timespan' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'aggregator' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'labels' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'window' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'oldest' => array(
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



class Google_Service_CloudMonitoring_MetricDescriptors_Resource extends Google_Service_Resource
{

  
  public function create($project, Google_Service_CloudMonitoring_MetricDescriptor $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_CloudMonitoring_MetricDescriptor");
  }

  
  public function delete($project, $metric, $optParams = array())
  {
    $params = array('project' => $project, 'metric' => $metric);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_CloudMonitoring_DeleteMetricDescriptorResponse");
  }

  
  public function listMetricDescriptors($project, $optParams = array())
  {
    $params = array('project' => $project);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudMonitoring_ListMetricDescriptorsResponse");
  }
}


class Google_Service_CloudMonitoring_Timeseries_Resource extends Google_Service_Resource
{

  
  public function listTimeseries($project, $metric, $youngest, $optParams = array())
  {
    $params = array('project' => $project, 'metric' => $metric, 'youngest' => $youngest);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudMonitoring_ListTimeseriesResponse");
  }

  
  public function write($project, Google_Service_CloudMonitoring_WriteTimeseriesRequest $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('write', array($params), "Google_Service_CloudMonitoring_WriteTimeseriesResponse");
  }
}


class Google_Service_CloudMonitoring_TimeseriesDescriptors_Resource extends Google_Service_Resource
{

  
  public function listTimeseriesDescriptors($project, $metric, $youngest, $optParams = array())
  {
    $params = array('project' => $project, 'metric' => $metric, 'youngest' => $youngest);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudMonitoring_ListTimeseriesDescriptorsResponse");
  }
}




class Google_Service_CloudMonitoring_DeleteMetricDescriptorResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}

class Google_Service_CloudMonitoring_ListMetricDescriptorsRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}

class Google_Service_CloudMonitoring_ListMetricDescriptorsResponse extends Google_Collection
{
  protected $collection_key = 'metrics';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $metricsType = 'Google_Service_CloudMonitoring_MetricDescriptor';
  protected $metricsDataType = 'array';
  public $nextPageToken;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
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

class Google_Service_CloudMonitoring_ListTimeseriesDescriptorsRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}

class Google_Service_CloudMonitoring_ListTimeseriesDescriptorsResponse extends Google_Collection
{
  protected $collection_key = 'timeseries';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  public $nextPageToken;
  public $oldest;
  protected $timeseriesType = 'Google_Service_CloudMonitoring_TimeseriesDescriptor';
  protected $timeseriesDataType = 'array';
  public $youngest;


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
  public function setOldest($oldest)
  {
    $this->oldest = $oldest;
  }
  public function getOldest()
  {
    return $this->oldest;
  }
  public function setTimeseries($timeseries)
  {
    $this->timeseries = $timeseries;
  }
  public function getTimeseries()
  {
    return $this->timeseries;
  }
  public function setYoungest($youngest)
  {
    $this->youngest = $youngest;
  }
  public function getYoungest()
  {
    return $this->youngest;
  }
}

class Google_Service_CloudMonitoring_ListTimeseriesRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}

class Google_Service_CloudMonitoring_ListTimeseriesResponse extends Google_Collection
{
  protected $collection_key = 'timeseries';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  public $nextPageToken;
  public $oldest;
  protected $timeseriesType = 'Google_Service_CloudMonitoring_Timeseries';
  protected $timeseriesDataType = 'array';
  public $youngest;


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
  public function setOldest($oldest)
  {
    $this->oldest = $oldest;
  }
  public function getOldest()
  {
    return $this->oldest;
  }
  public function setTimeseries($timeseries)
  {
    $this->timeseries = $timeseries;
  }
  public function getTimeseries()
  {
    return $this->timeseries;
  }
  public function setYoungest($youngest)
  {
    $this->youngest = $youngest;
  }
  public function getYoungest()
  {
    return $this->youngest;
  }
}

class Google_Service_CloudMonitoring_MetricDescriptor extends Google_Collection
{
  protected $collection_key = 'labels';
  protected $internal_gapi_mappings = array(
  );
  public $description;
  protected $labelsType = 'Google_Service_CloudMonitoring_MetricDescriptorLabelDescriptor';
  protected $labelsDataType = 'array';
  public $name;
  public $project;
  protected $typeDescriptorType = 'Google_Service_CloudMonitoring_MetricDescriptorTypeDescriptor';
  protected $typeDescriptorDataType = '';


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  public function getLabels()
  {
    return $this->labels;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setProject($project)
  {
    $this->project = $project;
  }
  public function getProject()
  {
    return $this->project;
  }
  public function setTypeDescriptor(Google_Service_CloudMonitoring_MetricDescriptorTypeDescriptor $typeDescriptor)
  {
    $this->typeDescriptor = $typeDescriptor;
  }
  public function getTypeDescriptor()
  {
    return $this->typeDescriptor;
  }
}

class Google_Service_CloudMonitoring_MetricDescriptorLabelDescriptor extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $description;
  public $key;


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setKey($key)
  {
    $this->key = $key;
  }
  public function getKey()
  {
    return $this->key;
  }
}

class Google_Service_CloudMonitoring_MetricDescriptorTypeDescriptor extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $metricType;
  public $valueType;


  public function setMetricType($metricType)
  {
    $this->metricType = $metricType;
  }
  public function getMetricType()
  {
    return $this->metricType;
  }
  public function setValueType($valueType)
  {
    $this->valueType = $valueType;
  }
  public function getValueType()
  {
    return $this->valueType;
  }
}

class Google_Service_CloudMonitoring_Point extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $boolValue;
  protected $distributionValueType = 'Google_Service_CloudMonitoring_PointDistribution';
  protected $distributionValueDataType = '';
  public $doubleValue;
  public $end;
  public $int64Value;
  public $start;
  public $stringValue;


  public function setBoolValue($boolValue)
  {
    $this->boolValue = $boolValue;
  }
  public function getBoolValue()
  {
    return $this->boolValue;
  }
  public function setDistributionValue(Google_Service_CloudMonitoring_PointDistribution $distributionValue)
  {
    $this->distributionValue = $distributionValue;
  }
  public function getDistributionValue()
  {
    return $this->distributionValue;
  }
  public function setDoubleValue($doubleValue)
  {
    $this->doubleValue = $doubleValue;
  }
  public function getDoubleValue()
  {
    return $this->doubleValue;
  }
  public function setEnd($end)
  {
    $this->end = $end;
  }
  public function getEnd()
  {
    return $this->end;
  }
  public function setInt64Value($int64Value)
  {
    $this->int64Value = $int64Value;
  }
  public function getInt64Value()
  {
    return $this->int64Value;
  }
  public function setStart($start)
  {
    $this->start = $start;
  }
  public function getStart()
  {
    return $this->start;
  }
  public function setStringValue($stringValue)
  {
    $this->stringValue = $stringValue;
  }
  public function getStringValue()
  {
    return $this->stringValue;
  }
}

class Google_Service_CloudMonitoring_PointDistribution extends Google_Collection
{
  protected $collection_key = 'buckets';
  protected $internal_gapi_mappings = array(
  );
  protected $bucketsType = 'Google_Service_CloudMonitoring_PointDistributionBucket';
  protected $bucketsDataType = 'array';
  protected $overflowBucketType = 'Google_Service_CloudMonitoring_PointDistributionOverflowBucket';
  protected $overflowBucketDataType = '';
  protected $underflowBucketType = 'Google_Service_CloudMonitoring_PointDistributionUnderflowBucket';
  protected $underflowBucketDataType = '';


  public function setBuckets($buckets)
  {
    $this->buckets = $buckets;
  }
  public function getBuckets()
  {
    return $this->buckets;
  }
  public function setOverflowBucket(Google_Service_CloudMonitoring_PointDistributionOverflowBucket $overflowBucket)
  {
    $this->overflowBucket = $overflowBucket;
  }
  public function getOverflowBucket()
  {
    return $this->overflowBucket;
  }
  public function setUnderflowBucket(Google_Service_CloudMonitoring_PointDistributionUnderflowBucket $underflowBucket)
  {
    $this->underflowBucket = $underflowBucket;
  }
  public function getUnderflowBucket()
  {
    return $this->underflowBucket;
  }
}

class Google_Service_CloudMonitoring_PointDistributionBucket extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $count;
  public $lowerBound;
  public $upperBound;


  public function setCount($count)
  {
    $this->count = $count;
  }
  public function getCount()
  {
    return $this->count;
  }
  public function setLowerBound($lowerBound)
  {
    $this->lowerBound = $lowerBound;
  }
  public function getLowerBound()
  {
    return $this->lowerBound;
  }
  public function setUpperBound($upperBound)
  {
    $this->upperBound = $upperBound;
  }
  public function getUpperBound()
  {
    return $this->upperBound;
  }
}

class Google_Service_CloudMonitoring_PointDistributionOverflowBucket extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $count;
  public $lowerBound;


  public function setCount($count)
  {
    $this->count = $count;
  }
  public function getCount()
  {
    return $this->count;
  }
  public function setLowerBound($lowerBound)
  {
    $this->lowerBound = $lowerBound;
  }
  public function getLowerBound()
  {
    return $this->lowerBound;
  }
}

class Google_Service_CloudMonitoring_PointDistributionUnderflowBucket extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $count;
  public $upperBound;


  public function setCount($count)
  {
    $this->count = $count;
  }
  public function getCount()
  {
    return $this->count;
  }
  public function setUpperBound($upperBound)
  {
    $this->upperBound = $upperBound;
  }
  public function getUpperBound()
  {
    return $this->upperBound;
  }
}

class Google_Service_CloudMonitoring_Timeseries extends Google_Collection
{
  protected $collection_key = 'points';
  protected $internal_gapi_mappings = array(
  );
  protected $pointsType = 'Google_Service_CloudMonitoring_Point';
  protected $pointsDataType = 'array';
  protected $timeseriesDescType = 'Google_Service_CloudMonitoring_TimeseriesDescriptor';
  protected $timeseriesDescDataType = '';


  public function setPoints($points)
  {
    $this->points = $points;
  }
  public function getPoints()
  {
    return $this->points;
  }
  public function setTimeseriesDesc(Google_Service_CloudMonitoring_TimeseriesDescriptor $timeseriesDesc)
  {
    $this->timeseriesDesc = $timeseriesDesc;
  }
  public function getTimeseriesDesc()
  {
    return $this->timeseriesDesc;
  }
}

class Google_Service_CloudMonitoring_TimeseriesDescriptor extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $labels;
  public $metric;
  public $project;


  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  public function getLabels()
  {
    return $this->labels;
  }
  public function setMetric($metric)
  {
    $this->metric = $metric;
  }
  public function getMetric()
  {
    return $this->metric;
  }
  public function setProject($project)
  {
    $this->project = $project;
  }
  public function getProject()
  {
    return $this->project;
  }
}

class Google_Service_CloudMonitoring_TimeseriesDescriptorLabel extends Google_Model
{
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

class Google_Service_CloudMonitoring_TimeseriesDescriptorLabels extends Google_Model
{
}

class Google_Service_CloudMonitoring_TimeseriesPoint extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $pointType = 'Google_Service_CloudMonitoring_Point';
  protected $pointDataType = '';
  protected $timeseriesDescType = 'Google_Service_CloudMonitoring_TimeseriesDescriptor';
  protected $timeseriesDescDataType = '';


  public function setPoint(Google_Service_CloudMonitoring_Point $point)
  {
    $this->point = $point;
  }
  public function getPoint()
  {
    return $this->point;
  }
  public function setTimeseriesDesc(Google_Service_CloudMonitoring_TimeseriesDescriptor $timeseriesDesc)
  {
    $this->timeseriesDesc = $timeseriesDesc;
  }
  public function getTimeseriesDesc()
  {
    return $this->timeseriesDesc;
  }
}

class Google_Service_CloudMonitoring_WriteTimeseriesRequest extends Google_Collection
{
  protected $collection_key = 'timeseries';
  protected $internal_gapi_mappings = array(
  );
  public $commonLabels;
  protected $timeseriesType = 'Google_Service_CloudMonitoring_TimeseriesPoint';
  protected $timeseriesDataType = 'array';


  public function setCommonLabels($commonLabels)
  {
    $this->commonLabels = $commonLabels;
  }
  public function getCommonLabels()
  {
    return $this->commonLabels;
  }
  public function setTimeseries($timeseries)
  {
    $this->timeseries = $timeseries;
  }
  public function getTimeseries()
  {
    return $this->timeseries;
  }
}

class Google_Service_CloudMonitoring_WriteTimeseriesRequestCommonLabels extends Google_Model
{
}

class Google_Service_CloudMonitoring_WriteTimeseriesResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}
