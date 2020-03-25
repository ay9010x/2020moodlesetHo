<?php



class Google_Service_Cloudtrace extends Google_Service
{
  
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";

  public $projects;
  public $projects_traces;
  public $v1;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://cloudtrace.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1';
    $this->serviceName = 'cloudtrace';

    $this->projects = new Google_Service_Cloudtrace_Projects_Resource(
        $this,
        $this->serviceName,
        'projects',
        array(
          'methods' => array(
            'patchTraces' => array(
              'path' => 'v1/projects/{projectId}/traces',
              'httpMethod' => 'PATCH',
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
    $this->projects_traces = new Google_Service_Cloudtrace_ProjectsTraces_Resource(
        $this,
        $this->serviceName,
        'traces',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/projects/{projectId}/traces/{traceId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'projectId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'traceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/projects/{projectId}/traces',
              'httpMethod' => 'GET',
              'parameters' => array(
                'projectId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endTime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->v1 = new Google_Service_Cloudtrace_V1_Resource(
        $this,
        $this->serviceName,
        'v1',
        array(
          'methods' => array(
            'getDiscovery' => array(
              'path' => 'v1/discovery',
              'httpMethod' => 'GET',
              'parameters' => array(
                'labels' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'version' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'args' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'format' => array(
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



class Google_Service_Cloudtrace_Projects_Resource extends Google_Service_Resource
{

  
  public function patchTraces($projectId, Google_Service_Cloudtrace_Traces $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patchTraces', array($params), "Google_Service_Cloudtrace_Empty");
  }
}


class Google_Service_Cloudtrace_ProjectsTraces_Resource extends Google_Service_Resource
{

  
  public function get($projectId, $traceId, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'traceId' => $traceId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Cloudtrace_Trace");
  }

  
  public function listProjectsTraces($projectId, $optParams = array())
  {
    $params = array('projectId' => $projectId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Cloudtrace_ListTracesResponse");
  }
}


class Google_Service_Cloudtrace_V1_Resource extends Google_Service_Resource
{

  
  public function getDiscovery($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('getDiscovery', array($params));
  }
}




class Google_Service_Cloudtrace_Empty extends Google_Model
{
}

class Google_Service_Cloudtrace_ListTracesResponse extends Google_Collection
{
  protected $collection_key = 'traces';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $tracesType = 'Google_Service_Cloudtrace_Trace';
  protected $tracesDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setTraces($traces)
  {
    $this->traces = $traces;
  }
  public function getTraces()
  {
    return $this->traces;
  }
}

class Google_Service_Cloudtrace_Trace extends Google_Collection
{
  protected $collection_key = 'spans';
  protected $internal_gapi_mappings = array(
  );
  public $projectId;
  protected $spansType = 'Google_Service_Cloudtrace_TraceSpan';
  protected $spansDataType = 'array';
  public $traceId;


  public function setProjectId($projectId)
  {
    $this->projectId = $projectId;
  }
  public function getProjectId()
  {
    return $this->projectId;
  }
  public function setSpans($spans)
  {
    $this->spans = $spans;
  }
  public function getSpans()
  {
    return $this->spans;
  }
  public function setTraceId($traceId)
  {
    $this->traceId = $traceId;
  }
  public function getTraceId()
  {
    return $this->traceId;
  }
}

class Google_Service_Cloudtrace_TraceSpan extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $endTime;
  public $kind;
  public $labels;
  public $name;
  public $parentSpanId;
  public $spanId;
  public $startTime;


  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
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
  public function setParentSpanId($parentSpanId)
  {
    $this->parentSpanId = $parentSpanId;
  }
  public function getParentSpanId()
  {
    return $this->parentSpanId;
  }
  public function setSpanId($spanId)
  {
    $this->spanId = $spanId;
  }
  public function getSpanId()
  {
    return $this->spanId;
  }
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  public function getStartTime()
  {
    return $this->startTime;
  }
}

class Google_Service_Cloudtrace_TraceSpanLabels extends Google_Model
{
}

class Google_Service_Cloudtrace_Traces extends Google_Collection
{
  protected $collection_key = 'traces';
  protected $internal_gapi_mappings = array(
  );
  protected $tracesType = 'Google_Service_Cloudtrace_Trace';
  protected $tracesDataType = 'array';


  public function setTraces($traces)
  {
    $this->traces = $traces;
  }
  public function getTraces()
  {
    return $this->traces;
  }
}
