<?php



class Google_Service_AdSense extends Google_Service
{
  
  const ADSENSE =
      "https://www.googleapis.com/auth/adsense";
  
  const ADSENSE_READONLY =
      "https://www.googleapis.com/auth/adsense.readonly";

  public $accounts;
  public $accounts_adclients;
  public $accounts_adunits;
  public $accounts_adunits_customchannels;
  public $accounts_alerts;
  public $accounts_customchannels;
  public $accounts_customchannels_adunits;
  public $accounts_payments;
  public $accounts_reports;
  public $accounts_reports_saved;
  public $accounts_savedadstyles;
  public $accounts_urlchannels;
  public $adclients;
  public $adunits;
  public $adunits_customchannels;
  public $alerts;
  public $customchannels;
  public $customchannels_adunits;
  public $metadata_dimensions;
  public $metadata_metrics;
  public $payments;
  public $reports;
  public $reports_saved;
  public $savedadstyles;
  public $urlchannels;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'adsense/v1.4/';
    $this->version = 'v1.4';
    $this->serviceName = 'adsense';

    $this->accounts = new Google_Service_AdSense_Accounts_Resource(
        $this,
        $this->serviceName,
        'accounts',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'accounts/{accountId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tree' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'list' => array(
              'path' => 'accounts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
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
    $this->accounts_adclients = new Google_Service_AdSense_AccountsAdclients_Resource(
        $this,
        $this->serviceName,
        'adclients',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/adclients',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
    $this->accounts_adunits = new Google_Service_AdSense_AccountsAdunits_Resource(
        $this,
        $this->serviceName,
        'adunits',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/adunits/{adUnitId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adUnitId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getAdCode' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/adunits/{adUnitId}/adcode',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adUnitId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/adunits',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'includeInactive' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'pageToken' => array(
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
    $this->accounts_adunits_customchannels = new Google_Service_AdSense_AccountsAdunitsCustomchannels_Resource(
        $this,
        $this->serviceName,
        'customchannels',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/adunits/{adUnitId}/customchannels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adUnitId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
    $this->accounts_alerts = new Google_Service_AdSense_AccountsAlerts_Resource(
        $this,
        $this->serviceName,
        'alerts',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'accounts/{accountId}/alerts/{alertId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'alertId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/alerts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_customchannels = new Google_Service_AdSense_AccountsCustomchannels_Resource(
        $this,
        $this->serviceName,
        'customchannels',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/customchannels/{customChannelId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customChannelId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/customchannels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
    $this->accounts_customchannels_adunits = new Google_Service_AdSense_AccountsCustomchannelsAdunits_Resource(
        $this,
        $this->serviceName,
        'adunits',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/customchannels/{customChannelId}/adunits',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customChannelId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'includeInactive' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_payments = new Google_Service_AdSense_AccountsPayments_Resource(
        $this,
        $this->serviceName,
        'payments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/payments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_reports = new Google_Service_AdSense_AccountsReports_Resource(
        $this,
        $this->serviceName,
        'reports',
        array(
          'methods' => array(
            'generate' => array(
              'path' => 'accounts/{accountId}/reports',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'startDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'endDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'metric' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'currency' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startIndex' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'useTimezoneReporting' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'dimension' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_reports_saved = new Google_Service_AdSense_AccountsReportsSaved_Resource(
        $this,
        $this->serviceName,
        'saved',
        array(
          'methods' => array(
            'generate' => array(
              'path' => 'accounts/{accountId}/reports/{savedReportId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'savedReportId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startIndex' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/reports/saved',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
    $this->accounts_savedadstyles = new Google_Service_AdSense_AccountsSavedadstyles_Resource(
        $this,
        $this->serviceName,
        'savedadstyles',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'accounts/{accountId}/savedadstyles/{savedAdStyleId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'savedAdStyleId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/savedadstyles',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
    $this->accounts_urlchannels = new Google_Service_AdSense_AccountsUrlchannels_Resource(
        $this,
        $this->serviceName,
        'urlchannels',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/adclients/{adClientId}/urlchannels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
    $this->adclients = new Google_Service_AdSense_Adclients_Resource(
        $this,
        $this->serviceName,
        'adclients',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'adclients',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
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
    $this->adunits = new Google_Service_AdSense_Adunits_Resource(
        $this,
        $this->serviceName,
        'adunits',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'adclients/{adClientId}/adunits/{adUnitId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adUnitId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getAdCode' => array(
              'path' => 'adclients/{adClientId}/adunits/{adUnitId}/adcode',
              'httpMethod' => 'GET',
              'parameters' => array(
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adUnitId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'adclients/{adClientId}/adunits',
              'httpMethod' => 'GET',
              'parameters' => array(
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'includeInactive' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'pageToken' => array(
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
    $this->adunits_customchannels = new Google_Service_AdSense_AdunitsCustomchannels_Resource(
        $this,
        $this->serviceName,
        'customchannels',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'adclients/{adClientId}/adunits/{adUnitId}/customchannels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'adUnitId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
    $this->alerts = new Google_Service_AdSense_Alerts_Resource(
        $this,
        $this->serviceName,
        'alerts',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'alerts/{alertId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'alertId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'alerts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->customchannels = new Google_Service_AdSense_Customchannels_Resource(
        $this,
        $this->serviceName,
        'customchannels',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'adclients/{adClientId}/customchannels/{customChannelId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customChannelId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'adclients/{adClientId}/customchannels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
    $this->customchannels_adunits = new Google_Service_AdSense_CustomchannelsAdunits_Resource(
        $this,
        $this->serviceName,
        'adunits',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'adclients/{adClientId}/customchannels/{customChannelId}/adunits',
              'httpMethod' => 'GET',
              'parameters' => array(
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customChannelId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'includeInactive' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'pageToken' => array(
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
    $this->metadata_dimensions = new Google_Service_AdSense_MetadataDimensions_Resource(
        $this,
        $this->serviceName,
        'dimensions',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'metadata/dimensions',
              'httpMethod' => 'GET',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->metadata_metrics = new Google_Service_AdSense_MetadataMetrics_Resource(
        $this,
        $this->serviceName,
        'metrics',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'metadata/metrics',
              'httpMethod' => 'GET',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->payments = new Google_Service_AdSense_Payments_Resource(
        $this,
        $this->serviceName,
        'payments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'payments',
              'httpMethod' => 'GET',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->reports = new Google_Service_AdSense_Reports_Resource(
        $this,
        $this->serviceName,
        'reports',
        array(
          'methods' => array(
            'generate' => array(
              'path' => 'reports',
              'httpMethod' => 'GET',
              'parameters' => array(
                'startDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'endDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'metric' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'currency' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startIndex' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'useTimezoneReporting' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'dimension' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'accountId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->reports_saved = new Google_Service_AdSense_ReportsSaved_Resource(
        $this,
        $this->serviceName,
        'saved',
        array(
          'methods' => array(
            'generate' => array(
              'path' => 'reports/{savedReportId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'savedReportId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startIndex' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'list' => array(
              'path' => 'reports/saved',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
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
    $this->savedadstyles = new Google_Service_AdSense_Savedadstyles_Resource(
        $this,
        $this->serviceName,
        'savedadstyles',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'savedadstyles/{savedAdStyleId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'savedAdStyleId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'savedadstyles',
              'httpMethod' => 'GET',
              'parameters' => array(
                'pageToken' => array(
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
    $this->urlchannels = new Google_Service_AdSense_Urlchannels_Resource(
        $this,
        $this->serviceName,
        'urlchannels',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'adclients/{adClientId}/urlchannels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'adClientId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
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
  }
}



class Google_Service_AdSense_Accounts_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdSense_Account");
  }

  
  public function listAccounts($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_Accounts");
  }
}


class Google_Service_AdSense_AccountsAdclients_Resource extends Google_Service_Resource
{

  
  public function listAccountsAdclients($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_AdClients");
  }
}

class Google_Service_AdSense_AccountsAdunits_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $adClientId, $adUnitId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId, 'adUnitId' => $adUnitId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdSense_AdUnit");
  }

  
  public function getAdCode($accountId, $adClientId, $adUnitId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId, 'adUnitId' => $adUnitId);
    $params = array_merge($params, $optParams);
    return $this->call('getAdCode', array($params), "Google_Service_AdSense_AdCode");
  }

  
  public function listAccountsAdunits($accountId, $adClientId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_AdUnits");
  }
}


class Google_Service_AdSense_AccountsAdunitsCustomchannels_Resource extends Google_Service_Resource
{

  
  public function listAccountsAdunitsCustomchannels($accountId, $adClientId, $adUnitId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId, 'adUnitId' => $adUnitId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_CustomChannels");
  }
}

class Google_Service_AdSense_AccountsAlerts_Resource extends Google_Service_Resource
{

  
  public function delete($accountId, $alertId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'alertId' => $alertId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function listAccountsAlerts($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_Alerts");
  }
}

class Google_Service_AdSense_AccountsCustomchannels_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $adClientId, $customChannelId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId, 'customChannelId' => $customChannelId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdSense_CustomChannel");
  }

  
  public function listAccountsCustomchannels($accountId, $adClientId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_CustomChannels");
  }
}


class Google_Service_AdSense_AccountsCustomchannelsAdunits_Resource extends Google_Service_Resource
{

  
  public function listAccountsCustomchannelsAdunits($accountId, $adClientId, $customChannelId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId, 'customChannelId' => $customChannelId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_AdUnits");
  }
}

class Google_Service_AdSense_AccountsPayments_Resource extends Google_Service_Resource
{

  
  public function listAccountsPayments($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_Payments");
  }
}

class Google_Service_AdSense_AccountsReports_Resource extends Google_Service_Resource
{

  
  public function generate($accountId, $startDate, $endDate, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'startDate' => $startDate, 'endDate' => $endDate);
    $params = array_merge($params, $optParams);
    return $this->call('generate', array($params), "Google_Service_AdSense_AdsenseReportsGenerateResponse");
  }
}


class Google_Service_AdSense_AccountsReportsSaved_Resource extends Google_Service_Resource
{

  
  public function generate($accountId, $savedReportId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'savedReportId' => $savedReportId);
    $params = array_merge($params, $optParams);
    return $this->call('generate', array($params), "Google_Service_AdSense_AdsenseReportsGenerateResponse");
  }

  
  public function listAccountsReportsSaved($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_SavedReports");
  }
}

class Google_Service_AdSense_AccountsSavedadstyles_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $savedAdStyleId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'savedAdStyleId' => $savedAdStyleId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdSense_SavedAdStyle");
  }

  
  public function listAccountsSavedadstyles($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_SavedAdStyles");
  }
}

class Google_Service_AdSense_AccountsUrlchannels_Resource extends Google_Service_Resource
{

  
  public function listAccountsUrlchannels($accountId, $adClientId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'adClientId' => $adClientId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_UrlChannels");
  }
}


class Google_Service_AdSense_Adclients_Resource extends Google_Service_Resource
{

  
  public function listAdclients($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_AdClients");
  }
}


class Google_Service_AdSense_Adunits_Resource extends Google_Service_Resource
{

  
  public function get($adClientId, $adUnitId, $optParams = array())
  {
    $params = array('adClientId' => $adClientId, 'adUnitId' => $adUnitId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdSense_AdUnit");
  }

  
  public function getAdCode($adClientId, $adUnitId, $optParams = array())
  {
    $params = array('adClientId' => $adClientId, 'adUnitId' => $adUnitId);
    $params = array_merge($params, $optParams);
    return $this->call('getAdCode', array($params), "Google_Service_AdSense_AdCode");
  }

  
  public function listAdunits($adClientId, $optParams = array())
  {
    $params = array('adClientId' => $adClientId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_AdUnits");
  }
}


class Google_Service_AdSense_AdunitsCustomchannels_Resource extends Google_Service_Resource
{

  
  public function listAdunitsCustomchannels($adClientId, $adUnitId, $optParams = array())
  {
    $params = array('adClientId' => $adClientId, 'adUnitId' => $adUnitId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_CustomChannels");
  }
}


class Google_Service_AdSense_Alerts_Resource extends Google_Service_Resource
{

  
  public function delete($alertId, $optParams = array())
  {
    $params = array('alertId' => $alertId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function listAlerts($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_Alerts");
  }
}


class Google_Service_AdSense_Customchannels_Resource extends Google_Service_Resource
{

  
  public function get($adClientId, $customChannelId, $optParams = array())
  {
    $params = array('adClientId' => $adClientId, 'customChannelId' => $customChannelId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdSense_CustomChannel");
  }

  
  public function listCustomchannels($adClientId, $optParams = array())
  {
    $params = array('adClientId' => $adClientId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_CustomChannels");
  }
}


class Google_Service_AdSense_CustomchannelsAdunits_Resource extends Google_Service_Resource
{

  
  public function listCustomchannelsAdunits($adClientId, $customChannelId, $optParams = array())
  {
    $params = array('adClientId' => $adClientId, 'customChannelId' => $customChannelId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_AdUnits");
  }
}


class Google_Service_AdSense_Metadata_Resource extends Google_Service_Resource
{
}


class Google_Service_AdSense_MetadataDimensions_Resource extends Google_Service_Resource
{

  
  public function listMetadataDimensions($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_Metadata");
  }
}

class Google_Service_AdSense_MetadataMetrics_Resource extends Google_Service_Resource
{

  
  public function listMetadataMetrics($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_Metadata");
  }
}


class Google_Service_AdSense_Payments_Resource extends Google_Service_Resource
{

  
  public function listPayments($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_Payments");
  }
}


class Google_Service_AdSense_Reports_Resource extends Google_Service_Resource
{

  
  public function generate($startDate, $endDate, $optParams = array())
  {
    $params = array('startDate' => $startDate, 'endDate' => $endDate);
    $params = array_merge($params, $optParams);
    return $this->call('generate', array($params), "Google_Service_AdSense_AdsenseReportsGenerateResponse");
  }
}


class Google_Service_AdSense_ReportsSaved_Resource extends Google_Service_Resource
{

  
  public function generate($savedReportId, $optParams = array())
  {
    $params = array('savedReportId' => $savedReportId);
    $params = array_merge($params, $optParams);
    return $this->call('generate', array($params), "Google_Service_AdSense_AdsenseReportsGenerateResponse");
  }

  
  public function listReportsSaved($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_SavedReports");
  }
}


class Google_Service_AdSense_Savedadstyles_Resource extends Google_Service_Resource
{

  
  public function get($savedAdStyleId, $optParams = array())
  {
    $params = array('savedAdStyleId' => $savedAdStyleId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AdSense_SavedAdStyle");
  }

  
  public function listSavedadstyles($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_SavedAdStyles");
  }
}


class Google_Service_AdSense_Urlchannels_Resource extends Google_Service_Resource
{

  
  public function listUrlchannels($adClientId, $optParams = array())
  {
    $params = array('adClientId' => $adClientId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AdSense_UrlChannels");
  }
}




class Google_Service_AdSense_Account extends Google_Collection
{
  protected $collection_key = 'subAccounts';
  protected $internal_gapi_mappings = array(
        "creationTime" => "creation_time",
  );
  public $creationTime;
  public $id;
  public $kind;
  public $name;
  public $premium;
  protected $subAccountsType = 'Google_Service_AdSense_Account';
  protected $subAccountsDataType = 'array';
  public $timezone;


  public function setCreationTime($creationTime)
  {
    $this->creationTime = $creationTime;
  }
  public function getCreationTime()
  {
    return $this->creationTime;
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
  public function setPremium($premium)
  {
    $this->premium = $premium;
  }
  public function getPremium()
  {
    return $this->premium;
  }
  public function setSubAccounts($subAccounts)
  {
    $this->subAccounts = $subAccounts;
  }
  public function getSubAccounts()
  {
    return $this->subAccounts;
  }
  public function setTimezone($timezone)
  {
    $this->timezone = $timezone;
  }
  public function getTimezone()
  {
    return $this->timezone;
  }
}

class Google_Service_AdSense_Accounts extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdSense_Account';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

class Google_Service_AdSense_AdClient extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $arcOptIn;
  public $arcReviewMode;
  public $id;
  public $kind;
  public $productCode;
  public $supportsReporting;


  public function setArcOptIn($arcOptIn)
  {
    $this->arcOptIn = $arcOptIn;
  }
  public function getArcOptIn()
  {
    return $this->arcOptIn;
  }
  public function setArcReviewMode($arcReviewMode)
  {
    $this->arcReviewMode = $arcReviewMode;
  }
  public function getArcReviewMode()
  {
    return $this->arcReviewMode;
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
  public function setProductCode($productCode)
  {
    $this->productCode = $productCode;
  }
  public function getProductCode()
  {
    return $this->productCode;
  }
  public function setSupportsReporting($supportsReporting)
  {
    $this->supportsReporting = $supportsReporting;
  }
  public function getSupportsReporting()
  {
    return $this->supportsReporting;
  }
}

class Google_Service_AdSense_AdClients extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdSense_AdClient';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

class Google_Service_AdSense_AdCode extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $adCode;
  public $kind;


  public function setAdCode($adCode)
  {
    $this->adCode = $adCode;
  }
  public function getAdCode()
  {
    return $this->adCode;
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

class Google_Service_AdSense_AdStyle extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $colorsType = 'Google_Service_AdSense_AdStyleColors';
  protected $colorsDataType = '';
  public $corners;
  protected $fontType = 'Google_Service_AdSense_AdStyleFont';
  protected $fontDataType = '';
  public $kind;


  public function setColors(Google_Service_AdSense_AdStyleColors $colors)
  {
    $this->colors = $colors;
  }
  public function getColors()
  {
    return $this->colors;
  }
  public function setCorners($corners)
  {
    $this->corners = $corners;
  }
  public function getCorners()
  {
    return $this->corners;
  }
  public function setFont(Google_Service_AdSense_AdStyleFont $font)
  {
    $this->font = $font;
  }
  public function getFont()
  {
    return $this->font;
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

class Google_Service_AdSense_AdStyleColors extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $background;
  public $border;
  public $text;
  public $title;
  public $url;


  public function setBackground($background)
  {
    $this->background = $background;
  }
  public function getBackground()
  {
    return $this->background;
  }
  public function setBorder($border)
  {
    $this->border = $border;
  }
  public function getBorder()
  {
    return $this->border;
  }
  public function setText($text)
  {
    $this->text = $text;
  }
  public function getText()
  {
    return $this->text;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
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

class Google_Service_AdSense_AdStyleFont extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $family;
  public $size;


  public function setFamily($family)
  {
    $this->family = $family;
  }
  public function getFamily()
  {
    return $this->family;
  }
  public function setSize($size)
  {
    $this->size = $size;
  }
  public function getSize()
  {
    return $this->size;
  }
}

class Google_Service_AdSense_AdUnit extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $code;
  protected $contentAdsSettingsType = 'Google_Service_AdSense_AdUnitContentAdsSettings';
  protected $contentAdsSettingsDataType = '';
  protected $customStyleType = 'Google_Service_AdSense_AdStyle';
  protected $customStyleDataType = '';
  protected $feedAdsSettingsType = 'Google_Service_AdSense_AdUnitFeedAdsSettings';
  protected $feedAdsSettingsDataType = '';
  public $id;
  public $kind;
  protected $mobileContentAdsSettingsType = 'Google_Service_AdSense_AdUnitMobileContentAdsSettings';
  protected $mobileContentAdsSettingsDataType = '';
  public $name;
  public $savedStyleId;
  public $status;


  public function setCode($code)
  {
    $this->code = $code;
  }
  public function getCode()
  {
    return $this->code;
  }
  public function setContentAdsSettings(Google_Service_AdSense_AdUnitContentAdsSettings $contentAdsSettings)
  {
    $this->contentAdsSettings = $contentAdsSettings;
  }
  public function getContentAdsSettings()
  {
    return $this->contentAdsSettings;
  }
  public function setCustomStyle(Google_Service_AdSense_AdStyle $customStyle)
  {
    $this->customStyle = $customStyle;
  }
  public function getCustomStyle()
  {
    return $this->customStyle;
  }
  public function setFeedAdsSettings(Google_Service_AdSense_AdUnitFeedAdsSettings $feedAdsSettings)
  {
    $this->feedAdsSettings = $feedAdsSettings;
  }
  public function getFeedAdsSettings()
  {
    return $this->feedAdsSettings;
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
  public function setMobileContentAdsSettings(Google_Service_AdSense_AdUnitMobileContentAdsSettings $mobileContentAdsSettings)
  {
    $this->mobileContentAdsSettings = $mobileContentAdsSettings;
  }
  public function getMobileContentAdsSettings()
  {
    return $this->mobileContentAdsSettings;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setSavedStyleId($savedStyleId)
  {
    $this->savedStyleId = $savedStyleId;
  }
  public function getSavedStyleId()
  {
    return $this->savedStyleId;
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

class Google_Service_AdSense_AdUnitContentAdsSettings extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $backupOptionType = 'Google_Service_AdSense_AdUnitContentAdsSettingsBackupOption';
  protected $backupOptionDataType = '';
  public $size;
  public $type;


  public function setBackupOption(Google_Service_AdSense_AdUnitContentAdsSettingsBackupOption $backupOption)
  {
    $this->backupOption = $backupOption;
  }
  public function getBackupOption()
  {
    return $this->backupOption;
  }
  public function setSize($size)
  {
    $this->size = $size;
  }
  public function getSize()
  {
    return $this->size;
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

class Google_Service_AdSense_AdUnitContentAdsSettingsBackupOption extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $color;
  public $type;
  public $url;


  public function setColor($color)
  {
    $this->color = $color;
  }
  public function getColor()
  {
    return $this->color;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
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

class Google_Service_AdSense_AdUnitFeedAdsSettings extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $adPosition;
  public $frequency;
  public $minimumWordCount;
  public $type;


  public function setAdPosition($adPosition)
  {
    $this->adPosition = $adPosition;
  }
  public function getAdPosition()
  {
    return $this->adPosition;
  }
  public function setFrequency($frequency)
  {
    $this->frequency = $frequency;
  }
  public function getFrequency()
  {
    return $this->frequency;
  }
  public function setMinimumWordCount($minimumWordCount)
  {
    $this->minimumWordCount = $minimumWordCount;
  }
  public function getMinimumWordCount()
  {
    return $this->minimumWordCount;
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

class Google_Service_AdSense_AdUnitMobileContentAdsSettings extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $markupLanguage;
  public $scriptingLanguage;
  public $size;
  public $type;


  public function setMarkupLanguage($markupLanguage)
  {
    $this->markupLanguage = $markupLanguage;
  }
  public function getMarkupLanguage()
  {
    return $this->markupLanguage;
  }
  public function setScriptingLanguage($scriptingLanguage)
  {
    $this->scriptingLanguage = $scriptingLanguage;
  }
  public function getScriptingLanguage()
  {
    return $this->scriptingLanguage;
  }
  public function setSize($size)
  {
    $this->size = $size;
  }
  public function getSize()
  {
    return $this->size;
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

class Google_Service_AdSense_AdUnits extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdSense_AdUnit';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

class Google_Service_AdSense_AdsenseReportsGenerateResponse extends Google_Collection
{
  protected $collection_key = 'warnings';
  protected $internal_gapi_mappings = array(
  );
  public $averages;
  public $endDate;
  protected $headersType = 'Google_Service_AdSense_AdsenseReportsGenerateResponseHeaders';
  protected $headersDataType = 'array';
  public $kind;
  public $rows;
  public $startDate;
  public $totalMatchedRows;
  public $totals;
  public $warnings;


  public function setAverages($averages)
  {
    $this->averages = $averages;
  }
  public function getAverages()
  {
    return $this->averages;
  }
  public function setEndDate($endDate)
  {
    $this->endDate = $endDate;
  }
  public function getEndDate()
  {
    return $this->endDate;
  }
  public function setHeaders($headers)
  {
    $this->headers = $headers;
  }
  public function getHeaders()
  {
    return $this->headers;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setRows($rows)
  {
    $this->rows = $rows;
  }
  public function getRows()
  {
    return $this->rows;
  }
  public function setStartDate($startDate)
  {
    $this->startDate = $startDate;
  }
  public function getStartDate()
  {
    return $this->startDate;
  }
  public function setTotalMatchedRows($totalMatchedRows)
  {
    $this->totalMatchedRows = $totalMatchedRows;
  }
  public function getTotalMatchedRows()
  {
    return $this->totalMatchedRows;
  }
  public function setTotals($totals)
  {
    $this->totals = $totals;
  }
  public function getTotals()
  {
    return $this->totals;
  }
  public function setWarnings($warnings)
  {
    $this->warnings = $warnings;
  }
  public function getWarnings()
  {
    return $this->warnings;
  }
}

class Google_Service_AdSense_AdsenseReportsGenerateResponseHeaders extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $currency;
  public $name;
  public $type;


  public function setCurrency($currency)
  {
    $this->currency = $currency;
  }
  public function getCurrency()
  {
    return $this->currency;
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

class Google_Service_AdSense_Alert extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $isDismissible;
  public $kind;
  public $message;
  public $severity;
  public $type;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIsDismissible($isDismissible)
  {
    $this->isDismissible = $isDismissible;
  }
  public function getIsDismissible()
  {
    return $this->isDismissible;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setMessage($message)
  {
    $this->message = $message;
  }
  public function getMessage()
  {
    return $this->message;
  }
  public function setSeverity($severity)
  {
    $this->severity = $severity;
  }
  public function getSeverity()
  {
    return $this->severity;
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

class Google_Service_AdSense_Alerts extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_AdSense_Alert';
  protected $itemsDataType = 'array';
  public $kind;


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
}

class Google_Service_AdSense_CustomChannel extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $code;
  public $id;
  public $kind;
  public $name;
  protected $targetingInfoType = 'Google_Service_AdSense_CustomChannelTargetingInfo';
  protected $targetingInfoDataType = '';


  public function setCode($code)
  {
    $this->code = $code;
  }
  public function getCode()
  {
    return $this->code;
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
  public function setTargetingInfo(Google_Service_AdSense_CustomChannelTargetingInfo $targetingInfo)
  {
    $this->targetingInfo = $targetingInfo;
  }
  public function getTargetingInfo()
  {
    return $this->targetingInfo;
  }
}

class Google_Service_AdSense_CustomChannelTargetingInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $adsAppearOn;
  public $description;
  public $location;
  public $siteLanguage;


  public function setAdsAppearOn($adsAppearOn)
  {
    $this->adsAppearOn = $adsAppearOn;
  }
  public function getAdsAppearOn()
  {
    return $this->adsAppearOn;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLocation($location)
  {
    $this->location = $location;
  }
  public function getLocation()
  {
    return $this->location;
  }
  public function setSiteLanguage($siteLanguage)
  {
    $this->siteLanguage = $siteLanguage;
  }
  public function getSiteLanguage()
  {
    return $this->siteLanguage;
  }
}

class Google_Service_AdSense_CustomChannels extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdSense_CustomChannel';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

class Google_Service_AdSense_Metadata extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_AdSense_ReportingMetadataEntry';
  protected $itemsDataType = 'array';
  public $kind;


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
}

class Google_Service_AdSense_Payment extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $paymentAmount;
  public $paymentAmountCurrencyCode;
  public $paymentDate;


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
  public function setPaymentAmount($paymentAmount)
  {
    $this->paymentAmount = $paymentAmount;
  }
  public function getPaymentAmount()
  {
    return $this->paymentAmount;
  }
  public function setPaymentAmountCurrencyCode($paymentAmountCurrencyCode)
  {
    $this->paymentAmountCurrencyCode = $paymentAmountCurrencyCode;
  }
  public function getPaymentAmountCurrencyCode()
  {
    return $this->paymentAmountCurrencyCode;
  }
  public function setPaymentDate($paymentDate)
  {
    $this->paymentDate = $paymentDate;
  }
  public function getPaymentDate()
  {
    return $this->paymentDate;
  }
}

class Google_Service_AdSense_Payments extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_AdSense_Payment';
  protected $itemsDataType = 'array';
  public $kind;


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
}

class Google_Service_AdSense_ReportingMetadataEntry extends Google_Collection
{
  protected $collection_key = 'supportedProducts';
  protected $internal_gapi_mappings = array(
  );
  public $compatibleDimensions;
  public $compatibleMetrics;
  public $id;
  public $kind;
  public $requiredDimensions;
  public $requiredMetrics;
  public $supportedProducts;


  public function setCompatibleDimensions($compatibleDimensions)
  {
    $this->compatibleDimensions = $compatibleDimensions;
  }
  public function getCompatibleDimensions()
  {
    return $this->compatibleDimensions;
  }
  public function setCompatibleMetrics($compatibleMetrics)
  {
    $this->compatibleMetrics = $compatibleMetrics;
  }
  public function getCompatibleMetrics()
  {
    return $this->compatibleMetrics;
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
  public function setRequiredDimensions($requiredDimensions)
  {
    $this->requiredDimensions = $requiredDimensions;
  }
  public function getRequiredDimensions()
  {
    return $this->requiredDimensions;
  }
  public function setRequiredMetrics($requiredMetrics)
  {
    $this->requiredMetrics = $requiredMetrics;
  }
  public function getRequiredMetrics()
  {
    return $this->requiredMetrics;
  }
  public function setSupportedProducts($supportedProducts)
  {
    $this->supportedProducts = $supportedProducts;
  }
  public function getSupportedProducts()
  {
    return $this->supportedProducts;
  }
}

class Google_Service_AdSense_SavedAdStyle extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $adStyleType = 'Google_Service_AdSense_AdStyle';
  protected $adStyleDataType = '';
  public $id;
  public $kind;
  public $name;


  public function setAdStyle(Google_Service_AdSense_AdStyle $adStyle)
  {
    $this->adStyle = $adStyle;
  }
  public function getAdStyle()
  {
    return $this->adStyle;
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

class Google_Service_AdSense_SavedAdStyles extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdSense_SavedAdStyle';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

class Google_Service_AdSense_SavedReport extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $name;


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

class Google_Service_AdSense_SavedReports extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdSense_SavedReport';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

class Google_Service_AdSense_UrlChannel extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $urlPattern;


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
  public function setUrlPattern($urlPattern)
  {
    $this->urlPattern = $urlPattern;
  }
  public function getUrlPattern()
  {
    return $this->urlPattern;
  }
}

class Google_Service_AdSense_UrlChannels extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_AdSense_UrlChannel';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}
