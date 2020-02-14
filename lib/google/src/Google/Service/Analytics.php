<?php



class Google_Service_Analytics extends Google_Service
{
  
  const ANALYTICS =
      "https://www.googleapis.com/auth/analytics";
  
  const ANALYTICS_EDIT =
      "https://www.googleapis.com/auth/analytics.edit";
  
  const ANALYTICS_MANAGE_USERS =
      "https://www.googleapis.com/auth/analytics.manage.users";
  
  const ANALYTICS_MANAGE_USERS_READONLY =
      "https://www.googleapis.com/auth/analytics.manage.users.readonly";
  
  const ANALYTICS_PROVISION =
      "https://www.googleapis.com/auth/analytics.provision";
  
  const ANALYTICS_READONLY =
      "https://www.googleapis.com/auth/analytics.readonly";

  public $data_ga;
  public $data_mcf;
  public $data_realtime;
  public $management_accountSummaries;
  public $management_accountUserLinks;
  public $management_accounts;
  public $management_customDataSources;
  public $management_customDimensions;
  public $management_customMetrics;
  public $management_experiments;
  public $management_filters;
  public $management_goals;
  public $management_profileFilterLinks;
  public $management_profileUserLinks;
  public $management_profiles;
  public $management_segments;
  public $management_unsampledReports;
  public $management_uploads;
  public $management_webPropertyAdWordsLinks;
  public $management_webproperties;
  public $management_webpropertyUserLinks;
  public $metadata_columns;
  public $provisioning;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'analytics/v3/';
    $this->version = 'v3';
    $this->serviceName = 'analytics';

    $this->data_ga = new Google_Service_Analytics_DataGa_Resource(
        $this,
        $this->serviceName,
        'ga',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'data/ga',
              'httpMethod' => 'GET',
              'parameters' => array(
                'ids' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'start-date' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'end-date' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'metrics' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'dimensions' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'segment' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'samplingLevel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'filters' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'output' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->data_mcf = new Google_Service_Analytics_DataMcf_Resource(
        $this,
        $this->serviceName,
        'mcf',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'data/mcf',
              'httpMethod' => 'GET',
              'parameters' => array(
                'ids' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'start-date' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'end-date' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'metrics' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'dimensions' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'samplingLevel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'filters' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->data_realtime = new Google_Service_Analytics_DataRealtime_Resource(
        $this,
        $this->serviceName,
        'realtime',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'data/realtime',
              'httpMethod' => 'GET',
              'parameters' => array(
                'ids' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'metrics' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'dimensions' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'filters' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->management_accountSummaries = new Google_Service_Analytics_ManagementAccountSummaries_Resource(
        $this,
        $this->serviceName,
        'accountSummaries',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'management/accountSummaries',
              'httpMethod' => 'GET',
              'parameters' => array(
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->management_accountUserLinks = new Google_Service_Analytics_ManagementAccountUserLinks_Resource(
        $this,
        $this->serviceName,
        'accountUserLinks',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'management/accounts/{accountId}/entityUserLinks/{linkId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/entityUserLinks',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/entityUserLinks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/entityUserLinks/{linkId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_accounts = new Google_Service_Analytics_ManagementAccounts_Resource(
        $this,
        $this->serviceName,
        'accounts',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'management/accounts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->management_customDataSources = new Google_Service_Analytics_ManagementCustomDataSources_Resource(
        $this,
        $this->serviceName,
        'customDataSources',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDataSources',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->management_customDimensions = new Google_Service_Analytics_ManagementCustomDimensions_Resource(
        $this,
        $this->serviceName,
        'customDimensions',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDimensions/{customDimensionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customDimensionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDimensions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDimensions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDimensions/{customDimensionId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customDimensionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ignoreCustomDataSourceLinks' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDimensions/{customDimensionId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customDimensionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ignoreCustomDataSourceLinks' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->management_customMetrics = new Google_Service_Analytics_ManagementCustomMetrics_Resource(
        $this,
        $this->serviceName,
        'customMetrics',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customMetrics/{customMetricId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customMetricId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customMetrics',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customMetrics',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customMetrics/{customMetricId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customMetricId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ignoreCustomDataSourceLinks' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customMetrics/{customMetricId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customMetricId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ignoreCustomDataSourceLinks' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->management_experiments = new Google_Service_Analytics_ManagementExperiments_Resource(
        $this,
        $this->serviceName,
        'experiments',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/experiments/{experimentId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'experimentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/experiments/{experimentId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'experimentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/experiments',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/experiments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/experiments/{experimentId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'experimentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/experiments/{experimentId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'experimentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_filters = new Google_Service_Analytics_ManagementFilters_Resource(
        $this,
        $this->serviceName,
        'filters',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'management/accounts/{accountId}/filters/{filterId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filterId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'management/accounts/{accountId}/filters/{filterId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filterId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/filters',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/filters',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/filters/{filterId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filterId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/filters/{filterId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'filterId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_goals = new Google_Service_Analytics_ManagementGoals_Resource(
        $this,
        $this->serviceName,
        'goals',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/goals/{goalId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'goalId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/goals',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/goals',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/goals/{goalId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'goalId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/goals/{goalId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'goalId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_profileFilterLinks = new Google_Service_Analytics_ManagementProfileFilterLinks_Resource(
        $this,
        $this->serviceName,
        'profileFilterLinks',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/profileFilterLinks/{linkId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/profileFilterLinks/{linkId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/profileFilterLinks',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/profileFilterLinks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/profileFilterLinks/{linkId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/profileFilterLinks/{linkId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_profileUserLinks = new Google_Service_Analytics_ManagementProfileUserLinks_Resource(
        $this,
        $this->serviceName,
        'profileUserLinks',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/entityUserLinks/{linkId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/entityUserLinks',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/entityUserLinks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/entityUserLinks/{linkId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_profiles = new Google_Service_Analytics_ManagementProfiles_Resource(
        $this,
        $this->serviceName,
        'profiles',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_segments = new Google_Service_Analytics_ManagementSegments_Resource(
        $this,
        $this->serviceName,
        'segments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'management/segments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->management_unsampledReports = new Google_Service_Analytics_ManagementUnsampledReports_Resource(
        $this,
        $this->serviceName,
        'unsampledReports',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/unsampledReports/{unsampledReportId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'unsampledReportId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/unsampledReports',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/profiles/{profileId}/unsampledReports',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'profileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->management_uploads = new Google_Service_Analytics_ManagementUploads_Resource(
        $this,
        $this->serviceName,
        'uploads',
        array(
          'methods' => array(
            'deleteUploadData' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDataSources/{customDataSourceId}/deleteUploadData',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customDataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDataSources/{customDataSourceId}/uploads/{uploadId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customDataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'uploadId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDataSources/{customDataSourceId}/uploads',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customDataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'uploadData' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/customDataSources/{customDataSourceId}/uploads',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customDataSourceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_webPropertyAdWordsLinks = new Google_Service_Analytics_ManagementWebPropertyAdWordsLinks_Resource(
        $this,
        $this->serviceName,
        'webPropertyAdWordsLinks',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityAdWordsLinks/{webPropertyAdWordsLinkId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyAdWordsLinkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityAdWordsLinks/{webPropertyAdWordsLinkId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyAdWordsLinkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityAdWordsLinks',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityAdWordsLinks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityAdWordsLinks/{webPropertyAdWordsLinkId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyAdWordsLinkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityAdWordsLinks/{webPropertyAdWordsLinkId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyAdWordsLinkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_webproperties = new Google_Service_Analytics_ManagementWebproperties_Resource(
        $this,
        $this->serviceName,
        'webproperties',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->management_webpropertyUserLinks = new Google_Service_Analytics_ManagementWebpropertyUserLinks_Resource(
        $this,
        $this->serviceName,
        'webpropertyUserLinks',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityUserLinks/{linkId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityUserLinks',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityUserLinks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'max-results' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'start-index' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'update' => array(
              'path' => 'management/accounts/{accountId}/webproperties/{webPropertyId}/entityUserLinks/{linkId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'webPropertyId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'linkId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->metadata_columns = new Google_Service_Analytics_MetadataColumns_Resource(
        $this,
        $this->serviceName,
        'columns',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'metadata/{reportType}/columns',
              'httpMethod' => 'GET',
              'parameters' => array(
                'reportType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->provisioning = new Google_Service_Analytics_Provisioning_Resource(
        $this,
        $this->serviceName,
        'provisioning',
        array(
          'methods' => array(
            'createAccountTicket' => array(
              'path' => 'provisioning/createAccountTicket',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
  }
}



class Google_Service_Analytics_Data_Resource extends Google_Service_Resource
{
}


class Google_Service_Analytics_DataGa_Resource extends Google_Service_Resource
{

  
  public function get($ids, $startDate, $endDate, $metrics, $optParams = array())
  {
    $params = array('ids' => $ids, 'start-date' => $startDate, 'end-date' => $endDate, 'metrics' => $metrics);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_GaData");
  }
}

class Google_Service_Analytics_DataMcf_Resource extends Google_Service_Resource
{

  
  public function get($ids, $startDate, $endDate, $metrics, $optParams = array())
  {
    $params = array('ids' => $ids, 'start-date' => $startDate, 'end-date' => $endDate, 'metrics' => $metrics);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_McfData");
  }
}

class Google_Service_Analytics_DataRealtime_Resource extends Google_Service_Resource
{

  
  public function get($ids, $metrics, $optParams = array())
  {
    $params = array('ids' => $ids, 'metrics' => $metrics);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_RealtimeData");
  }
}


class Google_Service_Analytics_Management_Resource extends Google_Service_Resource
{
}


class Google_Service_Analytics_ManagementAccountSummaries_Resource extends Google_Service_Resource
{

  
  public function listManagementAccountSummaries($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_AccountSummaries");
  }
}

class Google_Service_Analytics_ManagementAccountUserLinks_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $linkId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'linkId' => $linkId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($accountId, Google_Service_Analytics_EntityUserLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_EntityUserLink");
  }

  
  public function listManagementAccountUserLinks($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_EntityUserLinks");
  }

  
  public function update($accountId, $linkId, Google_Service_Analytics_EntityUserLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'linkId' => $linkId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_EntityUserLink");
  }
}

class Google_Service_Analytics_ManagementAccounts_Resource extends Google_Service_Resource
{

  
  public function listManagementAccounts($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Accounts");
  }
}

class Google_Service_Analytics_ManagementCustomDataSources_Resource extends Google_Service_Resource
{

  
  public function listManagementCustomDataSources($accountId, $webPropertyId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_CustomDataSources");
  }
}

class Google_Service_Analytics_ManagementCustomDimensions_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $webPropertyId, $customDimensionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customDimensionId' => $customDimensionId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_CustomDimension");
  }

  
  public function insert($accountId, $webPropertyId, Google_Service_Analytics_CustomDimension $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_CustomDimension");
  }

  
  public function listManagementCustomDimensions($accountId, $webPropertyId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_CustomDimensions");
  }

  
  public function patch($accountId, $webPropertyId, $customDimensionId, Google_Service_Analytics_CustomDimension $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customDimensionId' => $customDimensionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_CustomDimension");
  }

  
  public function update($accountId, $webPropertyId, $customDimensionId, Google_Service_Analytics_CustomDimension $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customDimensionId' => $customDimensionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_CustomDimension");
  }
}

class Google_Service_Analytics_ManagementCustomMetrics_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $webPropertyId, $customMetricId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customMetricId' => $customMetricId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_CustomMetric");
  }

  
  public function insert($accountId, $webPropertyId, Google_Service_Analytics_CustomMetric $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_CustomMetric");
  }

  
  public function listManagementCustomMetrics($accountId, $webPropertyId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_CustomMetrics");
  }

  
  public function patch($accountId, $webPropertyId, $customMetricId, Google_Service_Analytics_CustomMetric $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customMetricId' => $customMetricId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_CustomMetric");
  }

  
  public function update($accountId, $webPropertyId, $customMetricId, Google_Service_Analytics_CustomMetric $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customMetricId' => $customMetricId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_CustomMetric");
  }
}

class Google_Service_Analytics_ManagementExperiments_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $webPropertyId, $profileId, $experimentId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'experimentId' => $experimentId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $webPropertyId, $profileId, $experimentId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'experimentId' => $experimentId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_Experiment");
  }

  
  public function insert($accountId, $webPropertyId, $profileId, Google_Service_Analytics_Experiment $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_Experiment");
  }

  
  public function listManagementExperiments($accountId, $webPropertyId, $profileId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Experiments");
  }

  
  public function patch($accountId, $webPropertyId, $profileId, $experimentId, Google_Service_Analytics_Experiment $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'experimentId' => $experimentId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_Experiment");
  }

  
  public function update($accountId, $webPropertyId, $profileId, $experimentId, Google_Service_Analytics_Experiment $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'experimentId' => $experimentId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_Experiment");
  }
}

class Google_Service_Analytics_ManagementFilters_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $filterId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'filterId' => $filterId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Analytics_Filter");
  }

  
  public function get($accountId, $filterId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'filterId' => $filterId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_Filter");
  }

  
  public function insert($accountId, Google_Service_Analytics_Filter $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_Filter");
  }

  
  public function listManagementFilters($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Filters");
  }

  
  public function patch($accountId, $filterId, Google_Service_Analytics_Filter $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'filterId' => $filterId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_Filter");
  }

  
  public function update($accountId, $filterId, Google_Service_Analytics_Filter $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'filterId' => $filterId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_Filter");
  }
}

class Google_Service_Analytics_ManagementGoals_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $webPropertyId, $profileId, $goalId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'goalId' => $goalId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_Goal");
  }

  
  public function insert($accountId, $webPropertyId, $profileId, Google_Service_Analytics_Goal $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_Goal");
  }

  
  public function listManagementGoals($accountId, $webPropertyId, $profileId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Goals");
  }

  
  public function patch($accountId, $webPropertyId, $profileId, $goalId, Google_Service_Analytics_Goal $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'goalId' => $goalId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_Goal");
  }

  
  public function update($accountId, $webPropertyId, $profileId, $goalId, Google_Service_Analytics_Goal $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'goalId' => $goalId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_Goal");
  }
}

class Google_Service_Analytics_ManagementProfileFilterLinks_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $webPropertyId, $profileId, $linkId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'linkId' => $linkId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $webPropertyId, $profileId, $linkId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'linkId' => $linkId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_ProfileFilterLink");
  }

  
  public function insert($accountId, $webPropertyId, $profileId, Google_Service_Analytics_ProfileFilterLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_ProfileFilterLink");
  }

  
  public function listManagementProfileFilterLinks($accountId, $webPropertyId, $profileId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_ProfileFilterLinks");
  }

  
  public function patch($accountId, $webPropertyId, $profileId, $linkId, Google_Service_Analytics_ProfileFilterLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'linkId' => $linkId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_ProfileFilterLink");
  }

  
  public function update($accountId, $webPropertyId, $profileId, $linkId, Google_Service_Analytics_ProfileFilterLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'linkId' => $linkId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_ProfileFilterLink");
  }
}

class Google_Service_Analytics_ManagementProfileUserLinks_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $webPropertyId, $profileId, $linkId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'linkId' => $linkId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($accountId, $webPropertyId, $profileId, Google_Service_Analytics_EntityUserLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_EntityUserLink");
  }

  
  public function listManagementProfileUserLinks($accountId, $webPropertyId, $profileId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_EntityUserLinks");
  }

  
  public function update($accountId, $webPropertyId, $profileId, $linkId, Google_Service_Analytics_EntityUserLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'linkId' => $linkId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_EntityUserLink");
  }
}

class Google_Service_Analytics_ManagementProfiles_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $webPropertyId, $profileId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $webPropertyId, $profileId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_Profile");
  }

  
  public function insert($accountId, $webPropertyId, Google_Service_Analytics_Profile $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_Profile");
  }

  
  public function listManagementProfiles($accountId, $webPropertyId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Profiles");
  }

  
  public function patch($accountId, $webPropertyId, $profileId, Google_Service_Analytics_Profile $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_Profile");
  }

  
  public function update($accountId, $webPropertyId, $profileId, Google_Service_Analytics_Profile $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_Profile");
  }
}

class Google_Service_Analytics_ManagementSegments_Resource extends Google_Service_Resource
{

  
  public function listManagementSegments($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Segments");
  }
}

class Google_Service_Analytics_ManagementUnsampledReports_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $webPropertyId, $profileId, $unsampledReportId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'unsampledReportId' => $unsampledReportId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_UnsampledReport");
  }

  
  public function insert($accountId, $webPropertyId, $profileId, Google_Service_Analytics_UnsampledReport $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_UnsampledReport");
  }

  
  public function listManagementUnsampledReports($accountId, $webPropertyId, $profileId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'profileId' => $profileId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_UnsampledReports");
  }
}

class Google_Service_Analytics_ManagementUploads_Resource extends Google_Service_Resource
{

  
  public function deleteUploadData($accountId, $webPropertyId, $customDataSourceId, Google_Service_Analytics_AnalyticsDataimportDeleteUploadDataRequest $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customDataSourceId' => $customDataSourceId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('deleteUploadData', array($params));
  }

  
  public function get($accountId, $webPropertyId, $customDataSourceId, $uploadId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customDataSourceId' => $customDataSourceId, 'uploadId' => $uploadId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_Upload");
  }

  
  public function listManagementUploads($accountId, $webPropertyId, $customDataSourceId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customDataSourceId' => $customDataSourceId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Uploads");
  }

  
  public function uploadData($accountId, $webPropertyId, $customDataSourceId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'customDataSourceId' => $customDataSourceId);
    $params = array_merge($params, $optParams);
    return $this->call('uploadData', array($params), "Google_Service_Analytics_Upload");
  }
}

class Google_Service_Analytics_ManagementWebPropertyAdWordsLinks_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $webPropertyId, $webPropertyAdWordsLinkId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'webPropertyAdWordsLinkId' => $webPropertyAdWordsLinkId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $webPropertyId, $webPropertyAdWordsLinkId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'webPropertyAdWordsLinkId' => $webPropertyAdWordsLinkId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_EntityAdWordsLink");
  }

  
  public function insert($accountId, $webPropertyId, Google_Service_Analytics_EntityAdWordsLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_EntityAdWordsLink");
  }

  
  public function listManagementWebPropertyAdWordsLinks($accountId, $webPropertyId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_EntityAdWordsLinks");
  }

  
  public function patch($accountId, $webPropertyId, $webPropertyAdWordsLinkId, Google_Service_Analytics_EntityAdWordsLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'webPropertyAdWordsLinkId' => $webPropertyAdWordsLinkId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_EntityAdWordsLink");
  }

  
  public function update($accountId, $webPropertyId, $webPropertyAdWordsLinkId, Google_Service_Analytics_EntityAdWordsLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'webPropertyAdWordsLinkId' => $webPropertyAdWordsLinkId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_EntityAdWordsLink");
  }
}

class Google_Service_Analytics_ManagementWebproperties_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $webPropertyId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Analytics_Webproperty");
  }

  
  public function insert($accountId, Google_Service_Analytics_Webproperty $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_Webproperty");
  }

  
  public function listManagementWebproperties($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Webproperties");
  }

  
  public function patch($accountId, $webPropertyId, Google_Service_Analytics_Webproperty $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Analytics_Webproperty");
  }

  
  public function update($accountId, $webPropertyId, Google_Service_Analytics_Webproperty $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_Webproperty");
  }
}

class Google_Service_Analytics_ManagementWebpropertyUserLinks_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $webPropertyId, $linkId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'linkId' => $linkId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($accountId, $webPropertyId, Google_Service_Analytics_EntityUserLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Analytics_EntityUserLink");
  }

  
  public function listManagementWebpropertyUserLinks($accountId, $webPropertyId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_EntityUserLinks");
  }

  
  public function update($accountId, $webPropertyId, $linkId, Google_Service_Analytics_EntityUserLink $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'webPropertyId' => $webPropertyId, 'linkId' => $linkId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Analytics_EntityUserLink");
  }
}


class Google_Service_Analytics_Metadata_Resource extends Google_Service_Resource
{
}


class Google_Service_Analytics_MetadataColumns_Resource extends Google_Service_Resource
{

  
  public function listMetadataColumns($reportType, $optParams = array())
  {
    $params = array('reportType' => $reportType);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Analytics_Columns");
  }
}


class Google_Service_Analytics_Provisioning_Resource extends Google_Service_Resource
{

  
  public function createAccountTicket(Google_Service_Analytics_AccountTicket $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('createAccountTicket', array($params), "Google_Service_Analytics_AccountTicket");
  }
}




class Google_Service_Analytics_Account extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $childLinkType = 'Google_Service_Analytics_AccountChildLink';
  protected $childLinkDataType = '';
  public $created;
  public $id;
  public $kind;
  public $name;
  protected $permissionsType = 'Google_Service_Analytics_AccountPermissions';
  protected $permissionsDataType = '';
  public $selfLink;
  public $updated;


  public function setChildLink(Google_Service_Analytics_AccountChildLink $childLink)
  {
    $this->childLink = $childLink;
  }
  public function getChildLink()
  {
    return $this->childLink;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
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
  public function setPermissions(Google_Service_Analytics_AccountPermissions $permissions)
  {
    $this->permissions = $permissions;
  }
  public function getPermissions()
  {
    return $this->permissions;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
}

class Google_Service_Analytics_AccountChildLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_AccountPermissions extends Google_Collection
{
  protected $collection_key = 'effective';
  protected $internal_gapi_mappings = array(
  );
  public $effective;


  public function setEffective($effective)
  {
    $this->effective = $effective;
  }
  public function getEffective()
  {
    return $this->effective;
  }
}

class Google_Service_Analytics_AccountRef extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $id;
  public $kind;
  public $name;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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
}

class Google_Service_Analytics_AccountSummaries extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_AccountSummary';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_AccountSummary extends Google_Collection
{
  protected $collection_key = 'webProperties';
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $name;
  protected $webPropertiesType = 'Google_Service_Analytics_WebPropertySummary';
  protected $webPropertiesDataType = 'array';


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
  public function setWebProperties($webProperties)
  {
    $this->webProperties = $webProperties;
  }
  public function getWebProperties()
  {
    return $this->webProperties;
  }
}

class Google_Service_Analytics_AccountTicket extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $accountType = 'Google_Service_Analytics_Account';
  protected $accountDataType = '';
  public $id;
  public $kind;
  protected $profileType = 'Google_Service_Analytics_Profile';
  protected $profileDataType = '';
  public $redirectUri;
  protected $webpropertyType = 'Google_Service_Analytics_Webproperty';
  protected $webpropertyDataType = '';


  public function setAccount(Google_Service_Analytics_Account $account)
  {
    $this->account = $account;
  }
  public function getAccount()
  {
    return $this->account;
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
  public function setProfile(Google_Service_Analytics_Profile $profile)
  {
    $this->profile = $profile;
  }
  public function getProfile()
  {
    return $this->profile;
  }
  public function setRedirectUri($redirectUri)
  {
    $this->redirectUri = $redirectUri;
  }
  public function getRedirectUri()
  {
    return $this->redirectUri;
  }
  public function setWebproperty(Google_Service_Analytics_Webproperty $webproperty)
  {
    $this->webproperty = $webproperty;
  }
  public function getWebproperty()
  {
    return $this->webproperty;
  }
}

class Google_Service_Analytics_Accounts extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_Account';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_AdWordsAccount extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $autoTaggingEnabled;
  public $customerId;
  public $kind;


  public function setAutoTaggingEnabled($autoTaggingEnabled)
  {
    $this->autoTaggingEnabled = $autoTaggingEnabled;
  }
  public function getAutoTaggingEnabled()
  {
    return $this->autoTaggingEnabled;
  }
  public function setCustomerId($customerId)
  {
    $this->customerId = $customerId;
  }
  public function getCustomerId()
  {
    return $this->customerId;
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

class Google_Service_Analytics_AnalyticsDataimportDeleteUploadDataRequest extends Google_Collection
{
  protected $collection_key = 'customDataImportUids';
  protected $internal_gapi_mappings = array(
  );
  public $customDataImportUids;


  public function setCustomDataImportUids($customDataImportUids)
  {
    $this->customDataImportUids = $customDataImportUids;
  }
  public function getCustomDataImportUids()
  {
    return $this->customDataImportUids;
  }
}

class Google_Service_Analytics_Column extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $attributes;
  public $id;
  public $kind;


  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }
  public function getAttributes()
  {
    return $this->attributes;
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
}

class Google_Service_Analytics_ColumnAttributes extends Google_Model
{
}

class Google_Service_Analytics_Columns extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $attributeNames;
  public $etag;
  protected $itemsType = 'Google_Service_Analytics_Column';
  protected $itemsDataType = 'array';
  public $kind;
  public $totalResults;


  public function setAttributeNames($attributeNames)
  {
    $this->attributeNames = $attributeNames;
  }
  public function getAttributeNames()
  {
    return $this->attributeNames;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
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
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
}

class Google_Service_Analytics_CustomDataSource extends Google_Collection
{
  protected $collection_key = 'profilesLinked';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  protected $childLinkType = 'Google_Service_Analytics_CustomDataSourceChildLink';
  protected $childLinkDataType = '';
  public $created;
  public $description;
  public $id;
  public $importBehavior;
  public $kind;
  public $name;
  protected $parentLinkType = 'Google_Service_Analytics_CustomDataSourceParentLink';
  protected $parentLinkDataType = '';
  public $profilesLinked;
  public $selfLink;
  public $type;
  public $updated;
  public $uploadType;
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setChildLink(Google_Service_Analytics_CustomDataSourceChildLink $childLink)
  {
    $this->childLink = $childLink;
  }
  public function getChildLink()
  {
    return $this->childLink;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setImportBehavior($importBehavior)
  {
    $this->importBehavior = $importBehavior;
  }
  public function getImportBehavior()
  {
    return $this->importBehavior;
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
  public function setParentLink(Google_Service_Analytics_CustomDataSourceParentLink $parentLink)
  {
    $this->parentLink = $parentLink;
  }
  public function getParentLink()
  {
    return $this->parentLink;
  }
  public function setProfilesLinked($profilesLinked)
  {
    $this->profilesLinked = $profilesLinked;
  }
  public function getProfilesLinked()
  {
    return $this->profilesLinked;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setUploadType($uploadType)
  {
    $this->uploadType = $uploadType;
  }
  public function getUploadType()
  {
    return $this->uploadType;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_CustomDataSourceChildLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_CustomDataSourceParentLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_CustomDataSources extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_CustomDataSource';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_CustomDimension extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $active;
  public $created;
  public $id;
  public $index;
  public $kind;
  public $name;
  protected $parentLinkType = 'Google_Service_Analytics_CustomDimensionParentLink';
  protected $parentLinkDataType = '';
  public $scope;
  public $selfLink;
  public $updated;
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setActive($active)
  {
    $this->active = $active;
  }
  public function getActive()
  {
    return $this->active;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIndex($index)
  {
    $this->index = $index;
  }
  public function getIndex()
  {
    return $this->index;
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
  public function setParentLink(Google_Service_Analytics_CustomDimensionParentLink $parentLink)
  {
    $this->parentLink = $parentLink;
  }
  public function getParentLink()
  {
    return $this->parentLink;
  }
  public function setScope($scope)
  {
    $this->scope = $scope;
  }
  public function getScope()
  {
    return $this->scope;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_CustomDimensionParentLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_CustomDimensions extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_CustomDimension';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_CustomMetric extends Google_Model
{
  protected $internal_gapi_mappings = array(
        "maxValue" => "max_value",
        "minValue" => "min_value",
  );
  public $accountId;
  public $active;
  public $created;
  public $id;
  public $index;
  public $kind;
  public $maxValue;
  public $minValue;
  public $name;
  protected $parentLinkType = 'Google_Service_Analytics_CustomMetricParentLink';
  protected $parentLinkDataType = '';
  public $scope;
  public $selfLink;
  public $type;
  public $updated;
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setActive($active)
  {
    $this->active = $active;
  }
  public function getActive()
  {
    return $this->active;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIndex($index)
  {
    $this->index = $index;
  }
  public function getIndex()
  {
    return $this->index;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setMaxValue($maxValue)
  {
    $this->maxValue = $maxValue;
  }
  public function getMaxValue()
  {
    return $this->maxValue;
  }
  public function setMinValue($minValue)
  {
    $this->minValue = $minValue;
  }
  public function getMinValue()
  {
    return $this->minValue;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setParentLink(Google_Service_Analytics_CustomMetricParentLink $parentLink)
  {
    $this->parentLink = $parentLink;
  }
  public function getParentLink()
  {
    return $this->parentLink;
  }
  public function setScope($scope)
  {
    $this->scope = $scope;
  }
  public function getScope()
  {
    return $this->scope;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_CustomMetricParentLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_CustomMetrics extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_CustomMetric';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_EntityAdWordsLink extends Google_Collection
{
  protected $collection_key = 'profileIds';
  protected $internal_gapi_mappings = array(
  );
  protected $adWordsAccountsType = 'Google_Service_Analytics_AdWordsAccount';
  protected $adWordsAccountsDataType = 'array';
  protected $entityType = 'Google_Service_Analytics_EntityAdWordsLinkEntity';
  protected $entityDataType = '';
  public $id;
  public $kind;
  public $name;
  public $profileIds;
  public $selfLink;


  public function setAdWordsAccounts($adWordsAccounts)
  {
    $this->adWordsAccounts = $adWordsAccounts;
  }
  public function getAdWordsAccounts()
  {
    return $this->adWordsAccounts;
  }
  public function setEntity(Google_Service_Analytics_EntityAdWordsLinkEntity $entity)
  {
    $this->entity = $entity;
  }
  public function getEntity()
  {
    return $this->entity;
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
  public function setProfileIds($profileIds)
  {
    $this->profileIds = $profileIds;
  }
  public function getProfileIds()
  {
    return $this->profileIds;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
}

class Google_Service_Analytics_EntityAdWordsLinkEntity extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $webPropertyRefType = 'Google_Service_Analytics_WebPropertyRef';
  protected $webPropertyRefDataType = '';


  public function setWebPropertyRef(Google_Service_Analytics_WebPropertyRef $webPropertyRef)
  {
    $this->webPropertyRef = $webPropertyRef;
  }
  public function getWebPropertyRef()
  {
    return $this->webPropertyRef;
  }
}

class Google_Service_Analytics_EntityAdWordsLinks extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_EntityAdWordsLink';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
}

class Google_Service_Analytics_EntityUserLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $entityType = 'Google_Service_Analytics_EntityUserLinkEntity';
  protected $entityDataType = '';
  public $id;
  public $kind;
  protected $permissionsType = 'Google_Service_Analytics_EntityUserLinkPermissions';
  protected $permissionsDataType = '';
  public $selfLink;
  protected $userRefType = 'Google_Service_Analytics_UserRef';
  protected $userRefDataType = '';


  public function setEntity(Google_Service_Analytics_EntityUserLinkEntity $entity)
  {
    $this->entity = $entity;
  }
  public function getEntity()
  {
    return $this->entity;
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
  public function setPermissions(Google_Service_Analytics_EntityUserLinkPermissions $permissions)
  {
    $this->permissions = $permissions;
  }
  public function getPermissions()
  {
    return $this->permissions;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setUserRef(Google_Service_Analytics_UserRef $userRef)
  {
    $this->userRef = $userRef;
  }
  public function getUserRef()
  {
    return $this->userRef;
  }
}

class Google_Service_Analytics_EntityUserLinkEntity extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $accountRefType = 'Google_Service_Analytics_AccountRef';
  protected $accountRefDataType = '';
  protected $profileRefType = 'Google_Service_Analytics_ProfileRef';
  protected $profileRefDataType = '';
  protected $webPropertyRefType = 'Google_Service_Analytics_WebPropertyRef';
  protected $webPropertyRefDataType = '';


  public function setAccountRef(Google_Service_Analytics_AccountRef $accountRef)
  {
    $this->accountRef = $accountRef;
  }
  public function getAccountRef()
  {
    return $this->accountRef;
  }
  public function setProfileRef(Google_Service_Analytics_ProfileRef $profileRef)
  {
    $this->profileRef = $profileRef;
  }
  public function getProfileRef()
  {
    return $this->profileRef;
  }
  public function setWebPropertyRef(Google_Service_Analytics_WebPropertyRef $webPropertyRef)
  {
    $this->webPropertyRef = $webPropertyRef;
  }
  public function getWebPropertyRef()
  {
    return $this->webPropertyRef;
  }
}

class Google_Service_Analytics_EntityUserLinkPermissions extends Google_Collection
{
  protected $collection_key = 'local';
  protected $internal_gapi_mappings = array(
  );
  public $effective;
  public $local;


  public function setEffective($effective)
  {
    $this->effective = $effective;
  }
  public function getEffective()
  {
    return $this->effective;
  }
  public function setLocal($local)
  {
    $this->local = $local;
  }
  public function getLocal()
  {
    return $this->local;
  }
}

class Google_Service_Analytics_EntityUserLinks extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_EntityUserLink';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
}

class Google_Service_Analytics_Experiment extends Google_Collection
{
  protected $collection_key = 'variations';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $created;
  public $description;
  public $editableInGaUi;
  public $endTime;
  public $equalWeighting;
  public $id;
  public $internalWebPropertyId;
  public $kind;
  public $minimumExperimentLengthInDays;
  public $name;
  public $objectiveMetric;
  public $optimizationType;
  protected $parentLinkType = 'Google_Service_Analytics_ExperimentParentLink';
  protected $parentLinkDataType = '';
  public $profileId;
  public $reasonExperimentEnded;
  public $rewriteVariationUrlsAsOriginal;
  public $selfLink;
  public $servingFramework;
  public $snippet;
  public $startTime;
  public $status;
  public $trafficCoverage;
  public $updated;
  protected $variationsType = 'Google_Service_Analytics_ExperimentVariations';
  protected $variationsDataType = 'array';
  public $webPropertyId;
  public $winnerConfidenceLevel;
  public $winnerFound;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setEditableInGaUi($editableInGaUi)
  {
    $this->editableInGaUi = $editableInGaUi;
  }
  public function getEditableInGaUi()
  {
    return $this->editableInGaUi;
  }
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setEqualWeighting($equalWeighting)
  {
    $this->equalWeighting = $equalWeighting;
  }
  public function getEqualWeighting()
  {
    return $this->equalWeighting;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setMinimumExperimentLengthInDays($minimumExperimentLengthInDays)
  {
    $this->minimumExperimentLengthInDays = $minimumExperimentLengthInDays;
  }
  public function getMinimumExperimentLengthInDays()
  {
    return $this->minimumExperimentLengthInDays;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setObjectiveMetric($objectiveMetric)
  {
    $this->objectiveMetric = $objectiveMetric;
  }
  public function getObjectiveMetric()
  {
    return $this->objectiveMetric;
  }
  public function setOptimizationType($optimizationType)
  {
    $this->optimizationType = $optimizationType;
  }
  public function getOptimizationType()
  {
    return $this->optimizationType;
  }
  public function setParentLink(Google_Service_Analytics_ExperimentParentLink $parentLink)
  {
    $this->parentLink = $parentLink;
  }
  public function getParentLink()
  {
    return $this->parentLink;
  }
  public function setProfileId($profileId)
  {
    $this->profileId = $profileId;
  }
  public function getProfileId()
  {
    return $this->profileId;
  }
  public function setReasonExperimentEnded($reasonExperimentEnded)
  {
    $this->reasonExperimentEnded = $reasonExperimentEnded;
  }
  public function getReasonExperimentEnded()
  {
    return $this->reasonExperimentEnded;
  }
  public function setRewriteVariationUrlsAsOriginal($rewriteVariationUrlsAsOriginal)
  {
    $this->rewriteVariationUrlsAsOriginal = $rewriteVariationUrlsAsOriginal;
  }
  public function getRewriteVariationUrlsAsOriginal()
  {
    return $this->rewriteVariationUrlsAsOriginal;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setServingFramework($servingFramework)
  {
    $this->servingFramework = $servingFramework;
  }
  public function getServingFramework()
  {
    return $this->servingFramework;
  }
  public function setSnippet($snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
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
  public function setTrafficCoverage($trafficCoverage)
  {
    $this->trafficCoverage = $trafficCoverage;
  }
  public function getTrafficCoverage()
  {
    return $this->trafficCoverage;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setVariations($variations)
  {
    $this->variations = $variations;
  }
  public function getVariations()
  {
    return $this->variations;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
  public function setWinnerConfidenceLevel($winnerConfidenceLevel)
  {
    $this->winnerConfidenceLevel = $winnerConfidenceLevel;
  }
  public function getWinnerConfidenceLevel()
  {
    return $this->winnerConfidenceLevel;
  }
  public function setWinnerFound($winnerFound)
  {
    $this->winnerFound = $winnerFound;
  }
  public function getWinnerFound()
  {
    return $this->winnerFound;
  }
}

class Google_Service_Analytics_ExperimentParentLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_ExperimentVariations extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $name;
  public $status;
  public $url;
  public $weight;
  public $won;


  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
  public function setWeight($weight)
  {
    $this->weight = $weight;
  }
  public function getWeight()
  {
    return $this->weight;
  }
  public function setWon($won)
  {
    $this->won = $won;
  }
  public function getWon()
  {
    return $this->won;
  }
}

class Google_Service_Analytics_Experiments extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_Experiment';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_Filter extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  protected $advancedDetailsType = 'Google_Service_Analytics_FilterAdvancedDetails';
  protected $advancedDetailsDataType = '';
  public $created;
  protected $excludeDetailsType = 'Google_Service_Analytics_FilterExpression';
  protected $excludeDetailsDataType = '';
  public $id;
  protected $includeDetailsType = 'Google_Service_Analytics_FilterExpression';
  protected $includeDetailsDataType = '';
  public $kind;
  protected $lowercaseDetailsType = 'Google_Service_Analytics_FilterLowercaseDetails';
  protected $lowercaseDetailsDataType = '';
  public $name;
  protected $parentLinkType = 'Google_Service_Analytics_FilterParentLink';
  protected $parentLinkDataType = '';
  protected $searchAndReplaceDetailsType = 'Google_Service_Analytics_FilterSearchAndReplaceDetails';
  protected $searchAndReplaceDetailsDataType = '';
  public $selfLink;
  public $type;
  public $updated;
  protected $uppercaseDetailsType = 'Google_Service_Analytics_FilterUppercaseDetails';
  protected $uppercaseDetailsDataType = '';


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setAdvancedDetails(Google_Service_Analytics_FilterAdvancedDetails $advancedDetails)
  {
    $this->advancedDetails = $advancedDetails;
  }
  public function getAdvancedDetails()
  {
    return $this->advancedDetails;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setExcludeDetails(Google_Service_Analytics_FilterExpression $excludeDetails)
  {
    $this->excludeDetails = $excludeDetails;
  }
  public function getExcludeDetails()
  {
    return $this->excludeDetails;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIncludeDetails(Google_Service_Analytics_FilterExpression $includeDetails)
  {
    $this->includeDetails = $includeDetails;
  }
  public function getIncludeDetails()
  {
    return $this->includeDetails;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLowercaseDetails(Google_Service_Analytics_FilterLowercaseDetails $lowercaseDetails)
  {
    $this->lowercaseDetails = $lowercaseDetails;
  }
  public function getLowercaseDetails()
  {
    return $this->lowercaseDetails;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setParentLink(Google_Service_Analytics_FilterParentLink $parentLink)
  {
    $this->parentLink = $parentLink;
  }
  public function getParentLink()
  {
    return $this->parentLink;
  }
  public function setSearchAndReplaceDetails(Google_Service_Analytics_FilterSearchAndReplaceDetails $searchAndReplaceDetails)
  {
    $this->searchAndReplaceDetails = $searchAndReplaceDetails;
  }
  public function getSearchAndReplaceDetails()
  {
    return $this->searchAndReplaceDetails;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setUppercaseDetails(Google_Service_Analytics_FilterUppercaseDetails $uppercaseDetails)
  {
    $this->uppercaseDetails = $uppercaseDetails;
  }
  public function getUppercaseDetails()
  {
    return $this->uppercaseDetails;
  }
}

class Google_Service_Analytics_FilterAdvancedDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $caseSensitive;
  public $extractA;
  public $extractB;
  public $fieldA;
  public $fieldAIndex;
  public $fieldARequired;
  public $fieldB;
  public $fieldBIndex;
  public $fieldBRequired;
  public $outputConstructor;
  public $outputToField;
  public $outputToFieldIndex;
  public $overrideOutputField;


  public function setCaseSensitive($caseSensitive)
  {
    $this->caseSensitive = $caseSensitive;
  }
  public function getCaseSensitive()
  {
    return $this->caseSensitive;
  }
  public function setExtractA($extractA)
  {
    $this->extractA = $extractA;
  }
  public function getExtractA()
  {
    return $this->extractA;
  }
  public function setExtractB($extractB)
  {
    $this->extractB = $extractB;
  }
  public function getExtractB()
  {
    return $this->extractB;
  }
  public function setFieldA($fieldA)
  {
    $this->fieldA = $fieldA;
  }
  public function getFieldA()
  {
    return $this->fieldA;
  }
  public function setFieldAIndex($fieldAIndex)
  {
    $this->fieldAIndex = $fieldAIndex;
  }
  public function getFieldAIndex()
  {
    return $this->fieldAIndex;
  }
  public function setFieldARequired($fieldARequired)
  {
    $this->fieldARequired = $fieldARequired;
  }
  public function getFieldARequired()
  {
    return $this->fieldARequired;
  }
  public function setFieldB($fieldB)
  {
    $this->fieldB = $fieldB;
  }
  public function getFieldB()
  {
    return $this->fieldB;
  }
  public function setFieldBIndex($fieldBIndex)
  {
    $this->fieldBIndex = $fieldBIndex;
  }
  public function getFieldBIndex()
  {
    return $this->fieldBIndex;
  }
  public function setFieldBRequired($fieldBRequired)
  {
    $this->fieldBRequired = $fieldBRequired;
  }
  public function getFieldBRequired()
  {
    return $this->fieldBRequired;
  }
  public function setOutputConstructor($outputConstructor)
  {
    $this->outputConstructor = $outputConstructor;
  }
  public function getOutputConstructor()
  {
    return $this->outputConstructor;
  }
  public function setOutputToField($outputToField)
  {
    $this->outputToField = $outputToField;
  }
  public function getOutputToField()
  {
    return $this->outputToField;
  }
  public function setOutputToFieldIndex($outputToFieldIndex)
  {
    $this->outputToFieldIndex = $outputToFieldIndex;
  }
  public function getOutputToFieldIndex()
  {
    return $this->outputToFieldIndex;
  }
  public function setOverrideOutputField($overrideOutputField)
  {
    $this->overrideOutputField = $overrideOutputField;
  }
  public function getOverrideOutputField()
  {
    return $this->overrideOutputField;
  }
}

class Google_Service_Analytics_FilterExpression extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $caseSensitive;
  public $expressionValue;
  public $field;
  public $fieldIndex;
  public $kind;
  public $matchType;


  public function setCaseSensitive($caseSensitive)
  {
    $this->caseSensitive = $caseSensitive;
  }
  public function getCaseSensitive()
  {
    return $this->caseSensitive;
  }
  public function setExpressionValue($expressionValue)
  {
    $this->expressionValue = $expressionValue;
  }
  public function getExpressionValue()
  {
    return $this->expressionValue;
  }
  public function setField($field)
  {
    $this->field = $field;
  }
  public function getField()
  {
    return $this->field;
  }
  public function setFieldIndex($fieldIndex)
  {
    $this->fieldIndex = $fieldIndex;
  }
  public function getFieldIndex()
  {
    return $this->fieldIndex;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setMatchType($matchType)
  {
    $this->matchType = $matchType;
  }
  public function getMatchType()
  {
    return $this->matchType;
  }
}

class Google_Service_Analytics_FilterLowercaseDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $field;
  public $fieldIndex;


  public function setField($field)
  {
    $this->field = $field;
  }
  public function getField()
  {
    return $this->field;
  }
  public function setFieldIndex($fieldIndex)
  {
    $this->fieldIndex = $fieldIndex;
  }
  public function getFieldIndex()
  {
    return $this->fieldIndex;
  }
}

class Google_Service_Analytics_FilterParentLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_FilterRef extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $href;
  public $id;
  public $kind;
  public $name;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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
}

class Google_Service_Analytics_FilterSearchAndReplaceDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $caseSensitive;
  public $field;
  public $fieldIndex;
  public $replaceString;
  public $searchString;


  public function setCaseSensitive($caseSensitive)
  {
    $this->caseSensitive = $caseSensitive;
  }
  public function getCaseSensitive()
  {
    return $this->caseSensitive;
  }
  public function setField($field)
  {
    $this->field = $field;
  }
  public function getField()
  {
    return $this->field;
  }
  public function setFieldIndex($fieldIndex)
  {
    $this->fieldIndex = $fieldIndex;
  }
  public function getFieldIndex()
  {
    return $this->fieldIndex;
  }
  public function setReplaceString($replaceString)
  {
    $this->replaceString = $replaceString;
  }
  public function getReplaceString()
  {
    return $this->replaceString;
  }
  public function setSearchString($searchString)
  {
    $this->searchString = $searchString;
  }
  public function getSearchString()
  {
    return $this->searchString;
  }
}

class Google_Service_Analytics_FilterUppercaseDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $field;
  public $fieldIndex;


  public function setField($field)
  {
    $this->field = $field;
  }
  public function getField()
  {
    return $this->field;
  }
  public function setFieldIndex($fieldIndex)
  {
    $this->fieldIndex = $fieldIndex;
  }
  public function getFieldIndex()
  {
    return $this->fieldIndex;
  }
}

class Google_Service_Analytics_Filters extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_Filter';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_GaData extends Google_Collection
{
  protected $collection_key = 'rows';
  protected $internal_gapi_mappings = array(
  );
  protected $columnHeadersType = 'Google_Service_Analytics_GaDataColumnHeaders';
  protected $columnHeadersDataType = 'array';
  public $containsSampledData;
  protected $dataTableType = 'Google_Service_Analytics_GaDataDataTable';
  protected $dataTableDataType = '';
  public $id;
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  protected $profileInfoType = 'Google_Service_Analytics_GaDataProfileInfo';
  protected $profileInfoDataType = '';
  protected $queryType = 'Google_Service_Analytics_GaDataQuery';
  protected $queryDataType = '';
  public $rows;
  public $sampleSize;
  public $sampleSpace;
  public $selfLink;
  public $totalResults;
  public $totalsForAllResults;


  public function setColumnHeaders($columnHeaders)
  {
    $this->columnHeaders = $columnHeaders;
  }
  public function getColumnHeaders()
  {
    return $this->columnHeaders;
  }
  public function setContainsSampledData($containsSampledData)
  {
    $this->containsSampledData = $containsSampledData;
  }
  public function getContainsSampledData()
  {
    return $this->containsSampledData;
  }
  public function setDataTable(Google_Service_Analytics_GaDataDataTable $dataTable)
  {
    $this->dataTable = $dataTable;
  }
  public function getDataTable()
  {
    return $this->dataTable;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setProfileInfo(Google_Service_Analytics_GaDataProfileInfo $profileInfo)
  {
    $this->profileInfo = $profileInfo;
  }
  public function getProfileInfo()
  {
    return $this->profileInfo;
  }
  public function setQuery(Google_Service_Analytics_GaDataQuery $query)
  {
    $this->query = $query;
  }
  public function getQuery()
  {
    return $this->query;
  }
  public function setRows($rows)
  {
    $this->rows = $rows;
  }
  public function getRows()
  {
    return $this->rows;
  }
  public function setSampleSize($sampleSize)
  {
    $this->sampleSize = $sampleSize;
  }
  public function getSampleSize()
  {
    return $this->sampleSize;
  }
  public function setSampleSpace($sampleSpace)
  {
    $this->sampleSpace = $sampleSpace;
  }
  public function getSampleSpace()
  {
    return $this->sampleSpace;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setTotalsForAllResults($totalsForAllResults)
  {
    $this->totalsForAllResults = $totalsForAllResults;
  }
  public function getTotalsForAllResults()
  {
    return $this->totalsForAllResults;
  }
}

class Google_Service_Analytics_GaDataColumnHeaders extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $columnType;
  public $dataType;
  public $name;


  public function setColumnType($columnType)
  {
    $this->columnType = $columnType;
  }
  public function getColumnType()
  {
    return $this->columnType;
  }
  public function setDataType($dataType)
  {
    $this->dataType = $dataType;
  }
  public function getDataType()
  {
    return $this->dataType;
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

class Google_Service_Analytics_GaDataDataTable extends Google_Collection
{
  protected $collection_key = 'rows';
  protected $internal_gapi_mappings = array(
  );
  protected $colsType = 'Google_Service_Analytics_GaDataDataTableCols';
  protected $colsDataType = 'array';
  protected $rowsType = 'Google_Service_Analytics_GaDataDataTableRows';
  protected $rowsDataType = 'array';


  public function setCols($cols)
  {
    $this->cols = $cols;
  }
  public function getCols()
  {
    return $this->cols;
  }
  public function setRows($rows)
  {
    $this->rows = $rows;
  }
  public function getRows()
  {
    return $this->rows;
  }
}

class Google_Service_Analytics_GaDataDataTableCols extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $label;
  public $type;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
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

class Google_Service_Analytics_GaDataDataTableRows extends Google_Collection
{
  protected $collection_key = 'c';
  protected $internal_gapi_mappings = array(
  );
  protected $cType = 'Google_Service_Analytics_GaDataDataTableRowsC';
  protected $cDataType = 'array';


  public function setC($c)
  {
    $this->c = $c;
  }
  public function getC()
  {
    return $this->c;
  }
}

class Google_Service_Analytics_GaDataDataTableRowsC extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $v;


  public function setV($v)
  {
    $this->v = $v;
  }
  public function getV()
  {
    return $this->v;
  }
}

class Google_Service_Analytics_GaDataProfileInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $internalWebPropertyId;
  public $profileId;
  public $profileName;
  public $tableId;
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
  }
  public function setProfileId($profileId)
  {
    $this->profileId = $profileId;
  }
  public function getProfileId()
  {
    return $this->profileId;
  }
  public function setProfileName($profileName)
  {
    $this->profileName = $profileName;
  }
  public function getProfileName()
  {
    return $this->profileName;
  }
  public function setTableId($tableId)
  {
    $this->tableId = $tableId;
  }
  public function getTableId()
  {
    return $this->tableId;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_GaDataQuery extends Google_Collection
{
  protected $collection_key = 'sort';
  protected $internal_gapi_mappings = array(
        "endDate" => "end-date",
        "maxResults" => "max-results",
        "startDate" => "start-date",
        "startIndex" => "start-index",
  );
  public $dimensions;
  public $endDate;
  public $filters;
  public $ids;
  public $maxResults;
  public $metrics;
  public $samplingLevel;
  public $segment;
  public $sort;
  public $startDate;
  public $startIndex;


  public function setDimensions($dimensions)
  {
    $this->dimensions = $dimensions;
  }
  public function getDimensions()
  {
    return $this->dimensions;
  }
  public function setEndDate($endDate)
  {
    $this->endDate = $endDate;
  }
  public function getEndDate()
  {
    return $this->endDate;
  }
  public function setFilters($filters)
  {
    $this->filters = $filters;
  }
  public function getFilters()
  {
    return $this->filters;
  }
  public function setIds($ids)
  {
    $this->ids = $ids;
  }
  public function getIds()
  {
    return $this->ids;
  }
  public function setMaxResults($maxResults)
  {
    $this->maxResults = $maxResults;
  }
  public function getMaxResults()
  {
    return $this->maxResults;
  }
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
  }
  public function setSamplingLevel($samplingLevel)
  {
    $this->samplingLevel = $samplingLevel;
  }
  public function getSamplingLevel()
  {
    return $this->samplingLevel;
  }
  public function setSegment($segment)
  {
    $this->segment = $segment;
  }
  public function getSegment()
  {
    return $this->segment;
  }
  public function setSort($sort)
  {
    $this->sort = $sort;
  }
  public function getSort()
  {
    return $this->sort;
  }
  public function setStartDate($startDate)
  {
    $this->startDate = $startDate;
  }
  public function getStartDate()
  {
    return $this->startDate;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
}

class Google_Service_Analytics_GaDataTotalsForAllResults extends Google_Model
{
}

class Google_Service_Analytics_Goal extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $active;
  public $created;
  protected $eventDetailsType = 'Google_Service_Analytics_GoalEventDetails';
  protected $eventDetailsDataType = '';
  public $id;
  public $internalWebPropertyId;
  public $kind;
  public $name;
  protected $parentLinkType = 'Google_Service_Analytics_GoalParentLink';
  protected $parentLinkDataType = '';
  public $profileId;
  public $selfLink;
  public $type;
  public $updated;
  protected $urlDestinationDetailsType = 'Google_Service_Analytics_GoalUrlDestinationDetails';
  protected $urlDestinationDetailsDataType = '';
  public $value;
  protected $visitNumPagesDetailsType = 'Google_Service_Analytics_GoalVisitNumPagesDetails';
  protected $visitNumPagesDetailsDataType = '';
  protected $visitTimeOnSiteDetailsType = 'Google_Service_Analytics_GoalVisitTimeOnSiteDetails';
  protected $visitTimeOnSiteDetailsDataType = '';
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setActive($active)
  {
    $this->active = $active;
  }
  public function getActive()
  {
    return $this->active;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setEventDetails(Google_Service_Analytics_GoalEventDetails $eventDetails)
  {
    $this->eventDetails = $eventDetails;
  }
  public function getEventDetails()
  {
    return $this->eventDetails;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
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
  public function setParentLink(Google_Service_Analytics_GoalParentLink $parentLink)
  {
    $this->parentLink = $parentLink;
  }
  public function getParentLink()
  {
    return $this->parentLink;
  }
  public function setProfileId($profileId)
  {
    $this->profileId = $profileId;
  }
  public function getProfileId()
  {
    return $this->profileId;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setUrlDestinationDetails(Google_Service_Analytics_GoalUrlDestinationDetails $urlDestinationDetails)
  {
    $this->urlDestinationDetails = $urlDestinationDetails;
  }
  public function getUrlDestinationDetails()
  {
    return $this->urlDestinationDetails;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
  public function setVisitNumPagesDetails(Google_Service_Analytics_GoalVisitNumPagesDetails $visitNumPagesDetails)
  {
    $this->visitNumPagesDetails = $visitNumPagesDetails;
  }
  public function getVisitNumPagesDetails()
  {
    return $this->visitNumPagesDetails;
  }
  public function setVisitTimeOnSiteDetails(Google_Service_Analytics_GoalVisitTimeOnSiteDetails $visitTimeOnSiteDetails)
  {
    $this->visitTimeOnSiteDetails = $visitTimeOnSiteDetails;
  }
  public function getVisitTimeOnSiteDetails()
  {
    return $this->visitTimeOnSiteDetails;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_GoalEventDetails extends Google_Collection
{
  protected $collection_key = 'eventConditions';
  protected $internal_gapi_mappings = array(
  );
  protected $eventConditionsType = 'Google_Service_Analytics_GoalEventDetailsEventConditions';
  protected $eventConditionsDataType = 'array';
  public $useEventValue;


  public function setEventConditions($eventConditions)
  {
    $this->eventConditions = $eventConditions;
  }
  public function getEventConditions()
  {
    return $this->eventConditions;
  }
  public function setUseEventValue($useEventValue)
  {
    $this->useEventValue = $useEventValue;
  }
  public function getUseEventValue()
  {
    return $this->useEventValue;
  }
}

class Google_Service_Analytics_GoalEventDetailsEventConditions extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $comparisonType;
  public $comparisonValue;
  public $expression;
  public $matchType;
  public $type;


  public function setComparisonType($comparisonType)
  {
    $this->comparisonType = $comparisonType;
  }
  public function getComparisonType()
  {
    return $this->comparisonType;
  }
  public function setComparisonValue($comparisonValue)
  {
    $this->comparisonValue = $comparisonValue;
  }
  public function getComparisonValue()
  {
    return $this->comparisonValue;
  }
  public function setExpression($expression)
  {
    $this->expression = $expression;
  }
  public function getExpression()
  {
    return $this->expression;
  }
  public function setMatchType($matchType)
  {
    $this->matchType = $matchType;
  }
  public function getMatchType()
  {
    return $this->matchType;
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

class Google_Service_Analytics_GoalParentLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_GoalUrlDestinationDetails extends Google_Collection
{
  protected $collection_key = 'steps';
  protected $internal_gapi_mappings = array(
  );
  public $caseSensitive;
  public $firstStepRequired;
  public $matchType;
  protected $stepsType = 'Google_Service_Analytics_GoalUrlDestinationDetailsSteps';
  protected $stepsDataType = 'array';
  public $url;


  public function setCaseSensitive($caseSensitive)
  {
    $this->caseSensitive = $caseSensitive;
  }
  public function getCaseSensitive()
  {
    return $this->caseSensitive;
  }
  public function setFirstStepRequired($firstStepRequired)
  {
    $this->firstStepRequired = $firstStepRequired;
  }
  public function getFirstStepRequired()
  {
    return $this->firstStepRequired;
  }
  public function setMatchType($matchType)
  {
    $this->matchType = $matchType;
  }
  public function getMatchType()
  {
    return $this->matchType;
  }
  public function setSteps($steps)
  {
    $this->steps = $steps;
  }
  public function getSteps()
  {
    return $this->steps;
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

class Google_Service_Analytics_GoalUrlDestinationDetailsSteps extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $name;
  public $number;
  public $url;


  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNumber($number)
  {
    $this->number = $number;
  }
  public function getNumber()
  {
    return $this->number;
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

class Google_Service_Analytics_GoalVisitNumPagesDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $comparisonType;
  public $comparisonValue;


  public function setComparisonType($comparisonType)
  {
    $this->comparisonType = $comparisonType;
  }
  public function getComparisonType()
  {
    return $this->comparisonType;
  }
  public function setComparisonValue($comparisonValue)
  {
    $this->comparisonValue = $comparisonValue;
  }
  public function getComparisonValue()
  {
    return $this->comparisonValue;
  }
}

class Google_Service_Analytics_GoalVisitTimeOnSiteDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $comparisonType;
  public $comparisonValue;


  public function setComparisonType($comparisonType)
  {
    $this->comparisonType = $comparisonType;
  }
  public function getComparisonType()
  {
    return $this->comparisonType;
  }
  public function setComparisonValue($comparisonValue)
  {
    $this->comparisonValue = $comparisonValue;
  }
  public function getComparisonValue()
  {
    return $this->comparisonValue;
  }
}

class Google_Service_Analytics_Goals extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_Goal';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_McfData extends Google_Collection
{
  protected $collection_key = 'rows';
  protected $internal_gapi_mappings = array(
  );
  protected $columnHeadersType = 'Google_Service_Analytics_McfDataColumnHeaders';
  protected $columnHeadersDataType = 'array';
  public $containsSampledData;
  public $id;
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  protected $profileInfoType = 'Google_Service_Analytics_McfDataProfileInfo';
  protected $profileInfoDataType = '';
  protected $queryType = 'Google_Service_Analytics_McfDataQuery';
  protected $queryDataType = '';
  protected $rowsType = 'Google_Service_Analytics_McfDataRows';
  protected $rowsDataType = 'array';
  public $sampleSize;
  public $sampleSpace;
  public $selfLink;
  public $totalResults;
  public $totalsForAllResults;


  public function setColumnHeaders($columnHeaders)
  {
    $this->columnHeaders = $columnHeaders;
  }
  public function getColumnHeaders()
  {
    return $this->columnHeaders;
  }
  public function setContainsSampledData($containsSampledData)
  {
    $this->containsSampledData = $containsSampledData;
  }
  public function getContainsSampledData()
  {
    return $this->containsSampledData;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setProfileInfo(Google_Service_Analytics_McfDataProfileInfo $profileInfo)
  {
    $this->profileInfo = $profileInfo;
  }
  public function getProfileInfo()
  {
    return $this->profileInfo;
  }
  public function setQuery(Google_Service_Analytics_McfDataQuery $query)
  {
    $this->query = $query;
  }
  public function getQuery()
  {
    return $this->query;
  }
  public function setRows($rows)
  {
    $this->rows = $rows;
  }
  public function getRows()
  {
    return $this->rows;
  }
  public function setSampleSize($sampleSize)
  {
    $this->sampleSize = $sampleSize;
  }
  public function getSampleSize()
  {
    return $this->sampleSize;
  }
  public function setSampleSpace($sampleSpace)
  {
    $this->sampleSpace = $sampleSpace;
  }
  public function getSampleSpace()
  {
    return $this->sampleSpace;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setTotalsForAllResults($totalsForAllResults)
  {
    $this->totalsForAllResults = $totalsForAllResults;
  }
  public function getTotalsForAllResults()
  {
    return $this->totalsForAllResults;
  }
}

class Google_Service_Analytics_McfDataColumnHeaders extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $columnType;
  public $dataType;
  public $name;


  public function setColumnType($columnType)
  {
    $this->columnType = $columnType;
  }
  public function getColumnType()
  {
    return $this->columnType;
  }
  public function setDataType($dataType)
  {
    $this->dataType = $dataType;
  }
  public function getDataType()
  {
    return $this->dataType;
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

class Google_Service_Analytics_McfDataProfileInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $internalWebPropertyId;
  public $profileId;
  public $profileName;
  public $tableId;
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
  }
  public function setProfileId($profileId)
  {
    $this->profileId = $profileId;
  }
  public function getProfileId()
  {
    return $this->profileId;
  }
  public function setProfileName($profileName)
  {
    $this->profileName = $profileName;
  }
  public function getProfileName()
  {
    return $this->profileName;
  }
  public function setTableId($tableId)
  {
    $this->tableId = $tableId;
  }
  public function getTableId()
  {
    return $this->tableId;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_McfDataQuery extends Google_Collection
{
  protected $collection_key = 'sort';
  protected $internal_gapi_mappings = array(
        "endDate" => "end-date",
        "maxResults" => "max-results",
        "startDate" => "start-date",
        "startIndex" => "start-index",
  );
  public $dimensions;
  public $endDate;
  public $filters;
  public $ids;
  public $maxResults;
  public $metrics;
  public $samplingLevel;
  public $segment;
  public $sort;
  public $startDate;
  public $startIndex;


  public function setDimensions($dimensions)
  {
    $this->dimensions = $dimensions;
  }
  public function getDimensions()
  {
    return $this->dimensions;
  }
  public function setEndDate($endDate)
  {
    $this->endDate = $endDate;
  }
  public function getEndDate()
  {
    return $this->endDate;
  }
  public function setFilters($filters)
  {
    $this->filters = $filters;
  }
  public function getFilters()
  {
    return $this->filters;
  }
  public function setIds($ids)
  {
    $this->ids = $ids;
  }
  public function getIds()
  {
    return $this->ids;
  }
  public function setMaxResults($maxResults)
  {
    $this->maxResults = $maxResults;
  }
  public function getMaxResults()
  {
    return $this->maxResults;
  }
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
  }
  public function setSamplingLevel($samplingLevel)
  {
    $this->samplingLevel = $samplingLevel;
  }
  public function getSamplingLevel()
  {
    return $this->samplingLevel;
  }
  public function setSegment($segment)
  {
    $this->segment = $segment;
  }
  public function getSegment()
  {
    return $this->segment;
  }
  public function setSort($sort)
  {
    $this->sort = $sort;
  }
  public function getSort()
  {
    return $this->sort;
  }
  public function setStartDate($startDate)
  {
    $this->startDate = $startDate;
  }
  public function getStartDate()
  {
    return $this->startDate;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
}

class Google_Service_Analytics_McfDataRows extends Google_Collection
{
  protected $collection_key = 'conversionPathValue';
  protected $internal_gapi_mappings = array(
  );
  protected $conversionPathValueType = 'Google_Service_Analytics_McfDataRowsConversionPathValue';
  protected $conversionPathValueDataType = 'array';
  public $primitiveValue;


  public function setConversionPathValue($conversionPathValue)
  {
    $this->conversionPathValue = $conversionPathValue;
  }
  public function getConversionPathValue()
  {
    return $this->conversionPathValue;
  }
  public function setPrimitiveValue($primitiveValue)
  {
    $this->primitiveValue = $primitiveValue;
  }
  public function getPrimitiveValue()
  {
    return $this->primitiveValue;
  }
}

class Google_Service_Analytics_McfDataRowsConversionPathValue extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $interactionType;
  public $nodeValue;


  public function setInteractionType($interactionType)
  {
    $this->interactionType = $interactionType;
  }
  public function getInteractionType()
  {
    return $this->interactionType;
  }
  public function setNodeValue($nodeValue)
  {
    $this->nodeValue = $nodeValue;
  }
  public function getNodeValue()
  {
    return $this->nodeValue;
  }
}

class Google_Service_Analytics_McfDataTotalsForAllResults extends Google_Model
{
}

class Google_Service_Analytics_Profile extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  protected $childLinkType = 'Google_Service_Analytics_ProfileChildLink';
  protected $childLinkDataType = '';
  public $created;
  public $currency;
  public $defaultPage;
  public $eCommerceTracking;
  public $enhancedECommerceTracking;
  public $excludeQueryParameters;
  public $id;
  public $internalWebPropertyId;
  public $kind;
  public $name;
  protected $parentLinkType = 'Google_Service_Analytics_ProfileParentLink';
  protected $parentLinkDataType = '';
  protected $permissionsType = 'Google_Service_Analytics_ProfilePermissions';
  protected $permissionsDataType = '';
  public $selfLink;
  public $siteSearchCategoryParameters;
  public $siteSearchQueryParameters;
  public $stripSiteSearchCategoryParameters;
  public $stripSiteSearchQueryParameters;
  public $timezone;
  public $type;
  public $updated;
  public $webPropertyId;
  public $websiteUrl;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setChildLink(Google_Service_Analytics_ProfileChildLink $childLink)
  {
    $this->childLink = $childLink;
  }
  public function getChildLink()
  {
    return $this->childLink;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setCurrency($currency)
  {
    $this->currency = $currency;
  }
  public function getCurrency()
  {
    return $this->currency;
  }
  public function setDefaultPage($defaultPage)
  {
    $this->defaultPage = $defaultPage;
  }
  public function getDefaultPage()
  {
    return $this->defaultPage;
  }
  public function setECommerceTracking($eCommerceTracking)
  {
    $this->eCommerceTracking = $eCommerceTracking;
  }
  public function getECommerceTracking()
  {
    return $this->eCommerceTracking;
  }
  public function setEnhancedECommerceTracking($enhancedECommerceTracking)
  {
    $this->enhancedECommerceTracking = $enhancedECommerceTracking;
  }
  public function getEnhancedECommerceTracking()
  {
    return $this->enhancedECommerceTracking;
  }
  public function setExcludeQueryParameters($excludeQueryParameters)
  {
    $this->excludeQueryParameters = $excludeQueryParameters;
  }
  public function getExcludeQueryParameters()
  {
    return $this->excludeQueryParameters;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
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
  public function setParentLink(Google_Service_Analytics_ProfileParentLink $parentLink)
  {
    $this->parentLink = $parentLink;
  }
  public function getParentLink()
  {
    return $this->parentLink;
  }
  public function setPermissions(Google_Service_Analytics_ProfilePermissions $permissions)
  {
    $this->permissions = $permissions;
  }
  public function getPermissions()
  {
    return $this->permissions;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setSiteSearchCategoryParameters($siteSearchCategoryParameters)
  {
    $this->siteSearchCategoryParameters = $siteSearchCategoryParameters;
  }
  public function getSiteSearchCategoryParameters()
  {
    return $this->siteSearchCategoryParameters;
  }
  public function setSiteSearchQueryParameters($siteSearchQueryParameters)
  {
    $this->siteSearchQueryParameters = $siteSearchQueryParameters;
  }
  public function getSiteSearchQueryParameters()
  {
    return $this->siteSearchQueryParameters;
  }
  public function setStripSiteSearchCategoryParameters($stripSiteSearchCategoryParameters)
  {
    $this->stripSiteSearchCategoryParameters = $stripSiteSearchCategoryParameters;
  }
  public function getStripSiteSearchCategoryParameters()
  {
    return $this->stripSiteSearchCategoryParameters;
  }
  public function setStripSiteSearchQueryParameters($stripSiteSearchQueryParameters)
  {
    $this->stripSiteSearchQueryParameters = $stripSiteSearchQueryParameters;
  }
  public function getStripSiteSearchQueryParameters()
  {
    return $this->stripSiteSearchQueryParameters;
  }
  public function setTimezone($timezone)
  {
    $this->timezone = $timezone;
  }
  public function getTimezone()
  {
    return $this->timezone;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
  public function setWebsiteUrl($websiteUrl)
  {
    $this->websiteUrl = $websiteUrl;
  }
  public function getWebsiteUrl()
  {
    return $this->websiteUrl;
  }
}

class Google_Service_Analytics_ProfileChildLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_ProfileFilterLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $filterRefType = 'Google_Service_Analytics_FilterRef';
  protected $filterRefDataType = '';
  public $id;
  public $kind;
  protected $profileRefType = 'Google_Service_Analytics_ProfileRef';
  protected $profileRefDataType = '';
  public $rank;
  public $selfLink;


  public function setFilterRef(Google_Service_Analytics_FilterRef $filterRef)
  {
    $this->filterRef = $filterRef;
  }
  public function getFilterRef()
  {
    return $this->filterRef;
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
  public function setProfileRef(Google_Service_Analytics_ProfileRef $profileRef)
  {
    $this->profileRef = $profileRef;
  }
  public function getProfileRef()
  {
    return $this->profileRef;
  }
  public function setRank($rank)
  {
    $this->rank = $rank;
  }
  public function getRank()
  {
    return $this->rank;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
}

class Google_Service_Analytics_ProfileFilterLinks extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_ProfileFilterLink';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_ProfileParentLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_ProfilePermissions extends Google_Collection
{
  protected $collection_key = 'effective';
  protected $internal_gapi_mappings = array(
  );
  public $effective;


  public function setEffective($effective)
  {
    $this->effective = $effective;
  }
  public function getEffective()
  {
    return $this->effective;
  }
}

class Google_Service_Analytics_ProfileRef extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $href;
  public $id;
  public $internalWebPropertyId;
  public $kind;
  public $name;
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
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
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_ProfileSummary extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $name;
  public $type;


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
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}

class Google_Service_Analytics_Profiles extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_Profile';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_RealtimeData extends Google_Collection
{
  protected $collection_key = 'rows';
  protected $internal_gapi_mappings = array(
  );
  protected $columnHeadersType = 'Google_Service_Analytics_RealtimeDataColumnHeaders';
  protected $columnHeadersDataType = 'array';
  public $id;
  public $kind;
  protected $profileInfoType = 'Google_Service_Analytics_RealtimeDataProfileInfo';
  protected $profileInfoDataType = '';
  protected $queryType = 'Google_Service_Analytics_RealtimeDataQuery';
  protected $queryDataType = '';
  public $rows;
  public $selfLink;
  public $totalResults;
  public $totalsForAllResults;


  public function setColumnHeaders($columnHeaders)
  {
    $this->columnHeaders = $columnHeaders;
  }
  public function getColumnHeaders()
  {
    return $this->columnHeaders;
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
  public function setProfileInfo(Google_Service_Analytics_RealtimeDataProfileInfo $profileInfo)
  {
    $this->profileInfo = $profileInfo;
  }
  public function getProfileInfo()
  {
    return $this->profileInfo;
  }
  public function setQuery(Google_Service_Analytics_RealtimeDataQuery $query)
  {
    $this->query = $query;
  }
  public function getQuery()
  {
    return $this->query;
  }
  public function setRows($rows)
  {
    $this->rows = $rows;
  }
  public function getRows()
  {
    return $this->rows;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setTotalsForAllResults($totalsForAllResults)
  {
    $this->totalsForAllResults = $totalsForAllResults;
  }
  public function getTotalsForAllResults()
  {
    return $this->totalsForAllResults;
  }
}

class Google_Service_Analytics_RealtimeDataColumnHeaders extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $columnType;
  public $dataType;
  public $name;


  public function setColumnType($columnType)
  {
    $this->columnType = $columnType;
  }
  public function getColumnType()
  {
    return $this->columnType;
  }
  public function setDataType($dataType)
  {
    $this->dataType = $dataType;
  }
  public function getDataType()
  {
    return $this->dataType;
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

class Google_Service_Analytics_RealtimeDataProfileInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $internalWebPropertyId;
  public $profileId;
  public $profileName;
  public $tableId;
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
  }
  public function setProfileId($profileId)
  {
    $this->profileId = $profileId;
  }
  public function getProfileId()
  {
    return $this->profileId;
  }
  public function setProfileName($profileName)
  {
    $this->profileName = $profileName;
  }
  public function getProfileName()
  {
    return $this->profileName;
  }
  public function setTableId($tableId)
  {
    $this->tableId = $tableId;
  }
  public function getTableId()
  {
    return $this->tableId;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_RealtimeDataQuery extends Google_Collection
{
  protected $collection_key = 'sort';
  protected $internal_gapi_mappings = array(
        "maxResults" => "max-results",
  );
  public $dimensions;
  public $filters;
  public $ids;
  public $maxResults;
  public $metrics;
  public $sort;


  public function setDimensions($dimensions)
  {
    $this->dimensions = $dimensions;
  }
  public function getDimensions()
  {
    return $this->dimensions;
  }
  public function setFilters($filters)
  {
    $this->filters = $filters;
  }
  public function getFilters()
  {
    return $this->filters;
  }
  public function setIds($ids)
  {
    $this->ids = $ids;
  }
  public function getIds()
  {
    return $this->ids;
  }
  public function setMaxResults($maxResults)
  {
    $this->maxResults = $maxResults;
  }
  public function getMaxResults()
  {
    return $this->maxResults;
  }
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
  }
  public function setSort($sort)
  {
    $this->sort = $sort;
  }
  public function getSort()
  {
    return $this->sort;
  }
}

class Google_Service_Analytics_RealtimeDataTotalsForAllResults extends Google_Model
{
}

class Google_Service_Analytics_Segment extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $created;
  public $definition;
  public $id;
  public $kind;
  public $name;
  public $segmentId;
  public $selfLink;
  public $type;
  public $updated;


  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setDefinition($definition)
  {
    $this->definition = $definition;
  }
  public function getDefinition()
  {
    return $this->definition;
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
  public function setSegmentId($segmentId)
  {
    $this->segmentId = $segmentId;
  }
  public function getSegmentId()
  {
    return $this->segmentId;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
}

class Google_Service_Analytics_Segments extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_Segment';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_UnsampledReport extends Google_Model
{
  protected $internal_gapi_mappings = array(
        "endDate" => "end-date",
        "startDate" => "start-date",
  );
  public $accountId;
  protected $cloudStorageDownloadDetailsType = 'Google_Service_Analytics_UnsampledReportCloudStorageDownloadDetails';
  protected $cloudStorageDownloadDetailsDataType = '';
  public $created;
  public $dimensions;
  public $downloadType;
  protected $driveDownloadDetailsType = 'Google_Service_Analytics_UnsampledReportDriveDownloadDetails';
  protected $driveDownloadDetailsDataType = '';
  public $endDate;
  public $filters;
  public $id;
  public $kind;
  public $metrics;
  public $profileId;
  public $segment;
  public $selfLink;
  public $startDate;
  public $status;
  public $title;
  public $updated;
  public $webPropertyId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setCloudStorageDownloadDetails(Google_Service_Analytics_UnsampledReportCloudStorageDownloadDetails $cloudStorageDownloadDetails)
  {
    $this->cloudStorageDownloadDetails = $cloudStorageDownloadDetails;
  }
  public function getCloudStorageDownloadDetails()
  {
    return $this->cloudStorageDownloadDetails;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setDimensions($dimensions)
  {
    $this->dimensions = $dimensions;
  }
  public function getDimensions()
  {
    return $this->dimensions;
  }
  public function setDownloadType($downloadType)
  {
    $this->downloadType = $downloadType;
  }
  public function getDownloadType()
  {
    return $this->downloadType;
  }
  public function setDriveDownloadDetails(Google_Service_Analytics_UnsampledReportDriveDownloadDetails $driveDownloadDetails)
  {
    $this->driveDownloadDetails = $driveDownloadDetails;
  }
  public function getDriveDownloadDetails()
  {
    return $this->driveDownloadDetails;
  }
  public function setEndDate($endDate)
  {
    $this->endDate = $endDate;
  }
  public function getEndDate()
  {
    return $this->endDate;
  }
  public function setFilters($filters)
  {
    $this->filters = $filters;
  }
  public function getFilters()
  {
    return $this->filters;
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
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
  }
  public function setProfileId($profileId)
  {
    $this->profileId = $profileId;
  }
  public function getProfileId()
  {
    return $this->profileId;
  }
  public function setSegment($segment)
  {
    $this->segment = $segment;
  }
  public function getSegment()
  {
    return $this->segment;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setStartDate($startDate)
  {
    $this->startDate = $startDate;
  }
  public function getStartDate()
  {
    return $this->startDate;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setWebPropertyId($webPropertyId)
  {
    $this->webPropertyId = $webPropertyId;
  }
  public function getWebPropertyId()
  {
    return $this->webPropertyId;
  }
}

class Google_Service_Analytics_UnsampledReportCloudStorageDownloadDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $bucketId;
  public $objectId;


  public function setBucketId($bucketId)
  {
    $this->bucketId = $bucketId;
  }
  public function getBucketId()
  {
    return $this->bucketId;
  }
  public function setObjectId($objectId)
  {
    $this->objectId = $objectId;
  }
  public function getObjectId()
  {
    return $this->objectId;
  }
}

class Google_Service_Analytics_UnsampledReportDriveDownloadDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $documentId;


  public function setDocumentId($documentId)
  {
    $this->documentId = $documentId;
  }
  public function getDocumentId()
  {
    return $this->documentId;
  }
}

class Google_Service_Analytics_UnsampledReports extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_UnsampledReport';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_Upload extends Google_Collection
{
  protected $collection_key = 'errors';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $customDataSourceId;
  public $errors;
  public $id;
  public $kind;
  public $status;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setCustomDataSourceId($customDataSourceId)
  {
    $this->customDataSourceId = $customDataSourceId;
  }
  public function getCustomDataSourceId()
  {
    return $this->customDataSourceId;
  }
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  public function getErrors()
  {
    return $this->errors;
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
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}

class Google_Service_Analytics_Uploads extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_Upload';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
}

class Google_Service_Analytics_UserRef extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $email;
  public $id;
  public $kind;


  public function setEmail($email)
  {
    $this->email = $email;
  }
  public function getEmail()
  {
    return $this->email;
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
}

class Google_Service_Analytics_WebPropertyRef extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $href;
  public $id;
  public $internalWebPropertyId;
  public $kind;
  public $name;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
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
}

class Google_Service_Analytics_WebPropertySummary extends Google_Collection
{
  protected $collection_key = 'profiles';
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $internalWebPropertyId;
  public $kind;
  public $level;
  public $name;
  protected $profilesType = 'Google_Service_Analytics_ProfileSummary';
  protected $profilesDataType = 'array';
  public $websiteUrl;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLevel($level)
  {
    $this->level = $level;
  }
  public function getLevel()
  {
    return $this->level;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setProfiles($profiles)
  {
    $this->profiles = $profiles;
  }
  public function getProfiles()
  {
    return $this->profiles;
  }
  public function setWebsiteUrl($websiteUrl)
  {
    $this->websiteUrl = $websiteUrl;
  }
  public function getWebsiteUrl()
  {
    return $this->websiteUrl;
  }
}

class Google_Service_Analytics_Webproperties extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Analytics_Webproperty';
  protected $itemsDataType = 'array';
  public $itemsPerPage;
  public $kind;
  public $nextLink;
  public $previousLink;
  public $startIndex;
  public $totalResults;
  public $username;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setItemsPerPage($itemsPerPage)
  {
    $this->itemsPerPage = $itemsPerPage;
  }
  public function getItemsPerPage()
  {
    return $this->itemsPerPage;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNextLink($nextLink)
  {
    $this->nextLink = $nextLink;
  }
  public function getNextLink()
  {
    return $this->nextLink;
  }
  public function setPreviousLink($previousLink)
  {
    $this->previousLink = $previousLink;
  }
  public function getPreviousLink()
  {
    return $this->previousLink;
  }
  public function setStartIndex($startIndex)
  {
    $this->startIndex = $startIndex;
  }
  public function getStartIndex()
  {
    return $this->startIndex;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
  public function setUsername($username)
  {
    $this->username = $username;
  }
  public function getUsername()
  {
    return $this->username;
  }
}

class Google_Service_Analytics_Webproperty extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  protected $childLinkType = 'Google_Service_Analytics_WebpropertyChildLink';
  protected $childLinkDataType = '';
  public $created;
  public $defaultProfileId;
  public $id;
  public $industryVertical;
  public $internalWebPropertyId;
  public $kind;
  public $level;
  public $name;
  protected $parentLinkType = 'Google_Service_Analytics_WebpropertyParentLink';
  protected $parentLinkDataType = '';
  protected $permissionsType = 'Google_Service_Analytics_WebpropertyPermissions';
  protected $permissionsDataType = '';
  public $profileCount;
  public $selfLink;
  public $updated;
  public $websiteUrl;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setChildLink(Google_Service_Analytics_WebpropertyChildLink $childLink)
  {
    $this->childLink = $childLink;
  }
  public function getChildLink()
  {
    return $this->childLink;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setDefaultProfileId($defaultProfileId)
  {
    $this->defaultProfileId = $defaultProfileId;
  }
  public function getDefaultProfileId()
  {
    return $this->defaultProfileId;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIndustryVertical($industryVertical)
  {
    $this->industryVertical = $industryVertical;
  }
  public function getIndustryVertical()
  {
    return $this->industryVertical;
  }
  public function setInternalWebPropertyId($internalWebPropertyId)
  {
    $this->internalWebPropertyId = $internalWebPropertyId;
  }
  public function getInternalWebPropertyId()
  {
    return $this->internalWebPropertyId;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLevel($level)
  {
    $this->level = $level;
  }
  public function getLevel()
  {
    return $this->level;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setParentLink(Google_Service_Analytics_WebpropertyParentLink $parentLink)
  {
    $this->parentLink = $parentLink;
  }
  public function getParentLink()
  {
    return $this->parentLink;
  }
  public function setPermissions(Google_Service_Analytics_WebpropertyPermissions $permissions)
  {
    $this->permissions = $permissions;
  }
  public function getPermissions()
  {
    return $this->permissions;
  }
  public function setProfileCount($profileCount)
  {
    $this->profileCount = $profileCount;
  }
  public function getProfileCount()
  {
    return $this->profileCount;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setWebsiteUrl($websiteUrl)
  {
    $this->websiteUrl = $websiteUrl;
  }
  public function getWebsiteUrl()
  {
    return $this->websiteUrl;
  }
}

class Google_Service_Analytics_WebpropertyChildLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_WebpropertyParentLink extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $href;
  public $type;


  public function setHref($href)
  {
    $this->href = $href;
  }
  public function getHref()
  {
    return $this->href;
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

class Google_Service_Analytics_WebpropertyPermissions extends Google_Collection
{
  protected $collection_key = 'effective';
  protected $internal_gapi_mappings = array(
  );
  public $effective;


  public function setEffective($effective)
  {
    $this->effective = $effective;
  }
  public function getEffective()
  {
    return $this->effective;
  }
}
