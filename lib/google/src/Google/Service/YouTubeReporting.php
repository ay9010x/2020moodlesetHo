<?php



class Google_Service_YouTubeReporting extends Google_Service
{
  
  const YT_ANALYTICS_MONETARY_READONLY =
      "https://www.googleapis.com/auth/yt-analytics-monetary.readonly";
  
  const YT_ANALYTICS_READONLY =
      "https://www.googleapis.com/auth/yt-analytics.readonly";

  public $jobs;
  public $jobs_reports;
  public $media;
  public $reportTypes;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://youtubereporting.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1';
    $this->serviceName = 'youtubereporting';

    $this->jobs = new Google_Service_YouTubeReporting_Jobs_Resource(
        $this,
        $this->serviceName,
        'jobs',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/jobs',
              'httpMethod' => 'POST',
              'parameters' => array(
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/jobs/{jobId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => 'v1/jobs/{jobId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/jobs',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
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
    $this->jobs_reports = new Google_Service_YouTubeReporting_JobsReports_Resource(
        $this,
        $this->serviceName,
        'reports',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/jobs/{jobId}/reports/{reportId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'reportId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/jobs/{jobId}/reports',
              'httpMethod' => 'GET',
              'parameters' => array(
                'jobId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
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
    $this->media = new Google_Service_YouTubeReporting_Media_Resource(
        $this,
        $this->serviceName,
        'media',
        array(
          'methods' => array(
            'download' => array(
              'path' => 'v1/media/{+resourceName}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'resourceName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->reportTypes = new Google_Service_YouTubeReporting_ReportTypes_Resource(
        $this,
        $this->serviceName,
        'reportTypes',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/reportTypes',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
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
  }
}



class Google_Service_YouTubeReporting_Jobs_Resource extends Google_Service_Resource
{

  
  public function create(Google_Service_YouTubeReporting_Job $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_YouTubeReporting_Job");
  }

  
  public function delete($jobId, $optParams = array())
  {
    $params = array('jobId' => $jobId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_YouTubeReporting_Empty");
  }

  
  public function get($jobId, $optParams = array())
  {
    $params = array('jobId' => $jobId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_YouTubeReporting_Job");
  }

  
  public function listJobs($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTubeReporting_ListJobsResponse");
  }
}


class Google_Service_YouTubeReporting_JobsReports_Resource extends Google_Service_Resource
{

  
  public function get($jobId, $reportId, $optParams = array())
  {
    $params = array('jobId' => $jobId, 'reportId' => $reportId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_YouTubeReporting_Report");
  }

  
  public function listJobsReports($jobId, $optParams = array())
  {
    $params = array('jobId' => $jobId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTubeReporting_ListReportsResponse");
  }
}


class Google_Service_YouTubeReporting_Media_Resource extends Google_Service_Resource
{

  
  public function download($resourceName, $optParams = array())
  {
    $params = array('resourceName' => $resourceName);
    $params = array_merge($params, $optParams);
    return $this->call('download', array($params), "Google_Service_YouTubeReporting_Media");
  }
}


class Google_Service_YouTubeReporting_ReportTypes_Resource extends Google_Service_Resource
{

  
  public function listReportTypes($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTubeReporting_ListReportTypesResponse");
  }
}




class Google_Service_YouTubeReporting_Empty extends Google_Model
{
}

class Google_Service_YouTubeReporting_Job extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $createTime;
  public $id;
  public $name;
  public $reportTypeId;


  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  public function getCreateTime()
  {
    return $this->createTime;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setReportTypeId($reportTypeId)
  {
    $this->reportTypeId = $reportTypeId;
  }
  public function getReportTypeId()
  {
    return $this->reportTypeId;
  }
}

class Google_Service_YouTubeReporting_ListJobsResponse extends Google_Collection
{
  protected $collection_key = 'jobs';
  protected $internal_gapi_mappings = array(
  );
  protected $jobsType = 'Google_Service_YouTubeReporting_Job';
  protected $jobsDataType = 'array';
  public $nextPageToken;


  public function setJobs($jobs)
  {
    $this->jobs = $jobs;
  }
  public function getJobs()
  {
    return $this->jobs;
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

class Google_Service_YouTubeReporting_ListReportTypesResponse extends Google_Collection
{
  protected $collection_key = 'reportTypes';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $reportTypesType = 'Google_Service_YouTubeReporting_ReportType';
  protected $reportTypesDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setReportTypes($reportTypes)
  {
    $this->reportTypes = $reportTypes;
  }
  public function getReportTypes()
  {
    return $this->reportTypes;
  }
}

class Google_Service_YouTubeReporting_ListReportsResponse extends Google_Collection
{
  protected $collection_key = 'reports';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $reportsType = 'Google_Service_YouTubeReporting_Report';
  protected $reportsDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setReports($reports)
  {
    $this->reports = $reports;
  }
  public function getReports()
  {
    return $this->reports;
  }
}

class Google_Service_YouTubeReporting_Media extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $resourceName;


  public function setResourceName($resourceName)
  {
    $this->resourceName = $resourceName;
  }
  public function getResourceName()
  {
    return $this->resourceName;
  }
}

class Google_Service_YouTubeReporting_Report extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $createTime;
  public $downloadUrl;
  public $endTime;
  public $id;
  public $jobId;
  public $startTime;


  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  public function getCreateTime()
  {
    return $this->createTime;
  }
  public function setDownloadUrl($downloadUrl)
  {
    $this->downloadUrl = $downloadUrl;
  }
  public function getDownloadUrl()
  {
    return $this->downloadUrl;
  }
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setJobId($jobId)
  {
    $this->jobId = $jobId;
  }
  public function getJobId()
  {
    return $this->jobId;
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

class Google_Service_YouTubeReporting_ReportType extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $name;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
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
