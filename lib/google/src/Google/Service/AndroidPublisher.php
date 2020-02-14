<?php



class Google_Service_AndroidPublisher extends Google_Service
{
  
  const ANDROIDPUBLISHER =
      "https://www.googleapis.com/auth/androidpublisher";

  public $edits;
  public $edits_apklistings;
  public $edits_apks;
  public $edits_details;
  public $edits_expansionfiles;
  public $edits_images;
  public $edits_listings;
  public $edits_testers;
  public $edits_tracks;
  public $entitlements;
  public $inappproducts;
  public $purchases_products;
  public $purchases_subscriptions;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'androidpublisher/v2/applications/';
    $this->version = 'v2';
    $this->serviceName = 'androidpublisher';

    $this->edits = new Google_Service_AndroidPublisher_Edits_Resource(
        $this,
        $this->serviceName,
        'edits',
        array(
          'methods' => array(
            'commit' => array(
              'path' => '{packageName}/edits/{editId}:commit',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => '{packageName}/edits/{editId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{packageName}/edits/{editId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => '{packageName}/edits',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'validate' => array(
              'path' => '{packageName}/edits/{editId}:validate',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->edits_apklistings = new Google_Service_AndroidPublisher_EditsApklistings_Resource(
        $this,
        $this->serviceName,
        'apklistings',
        array(
          'methods' => array(
            'delete' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/listings/{language}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'deleteall' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/listings',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/listings/{language}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/listings',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/listings/{language}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/listings/{language}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->edits_apks = new Google_Service_AndroidPublisher_EditsApks_Resource(
        $this,
        $this->serviceName,
        'apks',
        array(
          'methods' => array(
            'addexternallyhosted' => array(
              'path' => '{packageName}/edits/{editId}/apks/externallyHosted',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{packageName}/edits/{editId}/apks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'upload' => array(
              'path' => '{packageName}/edits/{editId}/apks',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->edits_details = new Google_Service_AndroidPublisher_EditsDetails_Resource(
        $this,
        $this->serviceName,
        'details',
        array(
          'methods' => array(
            'get' => array(
              'path' => '{packageName}/edits/{editId}/details',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => '{packageName}/edits/{editId}/details',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{packageName}/edits/{editId}/details',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->edits_expansionfiles = new Google_Service_AndroidPublisher_EditsExpansionfiles_Resource(
        $this,
        $this->serviceName,
        'expansionfiles',
        array(
          'methods' => array(
            'get' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/expansionFiles/{expansionFileType}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'expansionFileType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/expansionFiles/{expansionFileType}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'expansionFileType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/expansionFiles/{expansionFileType}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'expansionFileType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'upload' => array(
              'path' => '{packageName}/edits/{editId}/apks/{apkVersionCode}/expansionFiles/{expansionFileType}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apkVersionCode' => array(
                  'location' => 'path',
                  'type' => 'integer',
                  'required' => true,
                ),
                'expansionFileType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->edits_images = new Google_Service_AndroidPublisher_EditsImages_Resource(
        $this,
        $this->serviceName,
        'images',
        array(
          'methods' => array(
            'delete' => array(
              'path' => '{packageName}/edits/{editId}/listings/{language}/{imageType}/{imageId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'imageType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'imageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'deleteall' => array(
              'path' => '{packageName}/edits/{editId}/listings/{language}/{imageType}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'imageType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{packageName}/edits/{editId}/listings/{language}/{imageType}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'imageType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'upload' => array(
              'path' => '{packageName}/edits/{editId}/listings/{language}/{imageType}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'imageType' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->edits_listings = new Google_Service_AndroidPublisher_EditsListings_Resource(
        $this,
        $this->serviceName,
        'listings',
        array(
          'methods' => array(
            'delete' => array(
              'path' => '{packageName}/edits/{editId}/listings/{language}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'deleteall' => array(
              'path' => '{packageName}/edits/{editId}/listings',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{packageName}/edits/{editId}/listings/{language}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{packageName}/edits/{editId}/listings',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => '{packageName}/edits/{editId}/listings/{language}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{packageName}/edits/{editId}/listings/{language}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->edits_testers = new Google_Service_AndroidPublisher_EditsTesters_Resource(
        $this,
        $this->serviceName,
        'testers',
        array(
          'methods' => array(
            'get' => array(
              'path' => '{packageName}/edits/{editId}/testers/{track}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'track' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => '{packageName}/edits/{editId}/testers/{track}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'track' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{packageName}/edits/{editId}/testers/{track}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'track' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->edits_tracks = new Google_Service_AndroidPublisher_EditsTracks_Resource(
        $this,
        $this->serviceName,
        'tracks',
        array(
          'methods' => array(
            'get' => array(
              'path' => '{packageName}/edits/{editId}/tracks/{track}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'track' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => '{packageName}/edits/{editId}/tracks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => '{packageName}/edits/{editId}/tracks/{track}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'track' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{packageName}/edits/{editId}/tracks/{track}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'editId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'track' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->entitlements = new Google_Service_AndroidPublisher_Entitlements_Resource(
        $this,
        $this->serviceName,
        'entitlements',
        array(
          'methods' => array(
            'list' => array(
              'path' => '{packageName}/entitlements',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'token' => array(
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
                'productId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->inappproducts = new Google_Service_AndroidPublisher_Inappproducts_Resource(
        $this,
        $this->serviceName,
        'inappproducts',
        array(
          'methods' => array(
            'batch' => array(
              'path' => 'inappproducts/batch',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'delete' => array(
              'path' => '{packageName}/inappproducts/{sku}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sku' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{packageName}/inappproducts/{sku}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sku' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => '{packageName}/inappproducts',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'autoConvertMissingPrices' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'list' => array(
              'path' => '{packageName}/inappproducts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'token' => array(
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
            ),'patch' => array(
              'path' => '{packageName}/inappproducts/{sku}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sku' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'autoConvertMissingPrices' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => '{packageName}/inappproducts/{sku}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sku' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'autoConvertMissingPrices' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->purchases_products = new Google_Service_AndroidPublisher_PurchasesProducts_Resource(
        $this,
        $this->serviceName,
        'products',
        array(
          'methods' => array(
            'get' => array(
              'path' => '{packageName}/purchases/products/{productId}/tokens/{token}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'token' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->purchases_subscriptions = new Google_Service_AndroidPublisher_PurchasesSubscriptions_Resource(
        $this,
        $this->serviceName,
        'subscriptions',
        array(
          'methods' => array(
            'cancel' => array(
              'path' => '{packageName}/purchases/subscriptions/{subscriptionId}/tokens/{token}:cancel',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'subscriptionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'token' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'defer' => array(
              'path' => '{packageName}/purchases/subscriptions/{subscriptionId}/tokens/{token}:defer',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'subscriptionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'token' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{packageName}/purchases/subscriptions/{subscriptionId}/tokens/{token}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'subscriptionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'token' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'refund' => array(
              'path' => '{packageName}/purchases/subscriptions/{subscriptionId}/tokens/{token}:refund',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'subscriptionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'token' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'revoke' => array(
              'path' => '{packageName}/purchases/subscriptions/{subscriptionId}/tokens/{token}:revoke',
              'httpMethod' => 'POST',
              'parameters' => array(
                'packageName' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'subscriptionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'token' => array(
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



class Google_Service_AndroidPublisher_Edits_Resource extends Google_Service_Resource
{

  
  public function commit($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('commit', array($params), "Google_Service_AndroidPublisher_AppEdit");
  }

  
  public function delete($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_AppEdit");
  }

  
  public function insert($packageName, Google_Service_AndroidPublisher_AppEdit $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_AndroidPublisher_AppEdit");
  }

  
  public function validate($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('validate', array($params), "Google_Service_AndroidPublisher_AppEdit");
  }
}


class Google_Service_AndroidPublisher_EditsApklistings_Resource extends Google_Service_Resource
{

  
  public function delete($packageName, $editId, $apkVersionCode, $language, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode, 'language' => $language);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function deleteall($packageName, $editId, $apkVersionCode, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode);
    $params = array_merge($params, $optParams);
    return $this->call('deleteall', array($params));
  }

  
  public function get($packageName, $editId, $apkVersionCode, $language, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode, 'language' => $language);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_ApkListing");
  }

  
  public function listEditsApklistings($packageName, $editId, $apkVersionCode, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidPublisher_ApkListingsListResponse");
  }

  
  public function patch($packageName, $editId, $apkVersionCode, $language, Google_Service_AndroidPublisher_ApkListing $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode, 'language' => $language, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidPublisher_ApkListing");
  }

  
  public function update($packageName, $editId, $apkVersionCode, $language, Google_Service_AndroidPublisher_ApkListing $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode, 'language' => $language, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidPublisher_ApkListing");
  }
}

class Google_Service_AndroidPublisher_EditsApks_Resource extends Google_Service_Resource
{

  
  public function addexternallyhosted($packageName, $editId, Google_Service_AndroidPublisher_ApksAddExternallyHostedRequest $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('addexternallyhosted', array($params), "Google_Service_AndroidPublisher_ApksAddExternallyHostedResponse");
  }

  
  public function listEditsApks($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidPublisher_ApksListResponse");
  }

  
  public function upload($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('upload', array($params), "Google_Service_AndroidPublisher_Apk");
  }
}

class Google_Service_AndroidPublisher_EditsDetails_Resource extends Google_Service_Resource
{

  
  public function get($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_AppDetails");
  }

  
  public function patch($packageName, $editId, Google_Service_AndroidPublisher_AppDetails $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidPublisher_AppDetails");
  }

  
  public function update($packageName, $editId, Google_Service_AndroidPublisher_AppDetails $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidPublisher_AppDetails");
  }
}

class Google_Service_AndroidPublisher_EditsExpansionfiles_Resource extends Google_Service_Resource
{

  
  public function get($packageName, $editId, $apkVersionCode, $expansionFileType, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode, 'expansionFileType' => $expansionFileType);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_ExpansionFile");
  }

  
  public function patch($packageName, $editId, $apkVersionCode, $expansionFileType, Google_Service_AndroidPublisher_ExpansionFile $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode, 'expansionFileType' => $expansionFileType, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidPublisher_ExpansionFile");
  }

  
  public function update($packageName, $editId, $apkVersionCode, $expansionFileType, Google_Service_AndroidPublisher_ExpansionFile $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode, 'expansionFileType' => $expansionFileType, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidPublisher_ExpansionFile");
  }

  
  public function upload($packageName, $editId, $apkVersionCode, $expansionFileType, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'apkVersionCode' => $apkVersionCode, 'expansionFileType' => $expansionFileType);
    $params = array_merge($params, $optParams);
    return $this->call('upload', array($params), "Google_Service_AndroidPublisher_ExpansionFilesUploadResponse");
  }
}

class Google_Service_AndroidPublisher_EditsImages_Resource extends Google_Service_Resource
{

  
  public function delete($packageName, $editId, $language, $imageType, $imageId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'language' => $language, 'imageType' => $imageType, 'imageId' => $imageId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function deleteall($packageName, $editId, $language, $imageType, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'language' => $language, 'imageType' => $imageType);
    $params = array_merge($params, $optParams);
    return $this->call('deleteall', array($params), "Google_Service_AndroidPublisher_ImagesDeleteAllResponse");
  }

  
  public function listEditsImages($packageName, $editId, $language, $imageType, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'language' => $language, 'imageType' => $imageType);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidPublisher_ImagesListResponse");
  }

  
  public function upload($packageName, $editId, $language, $imageType, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'language' => $language, 'imageType' => $imageType);
    $params = array_merge($params, $optParams);
    return $this->call('upload', array($params), "Google_Service_AndroidPublisher_ImagesUploadResponse");
  }
}

class Google_Service_AndroidPublisher_EditsListings_Resource extends Google_Service_Resource
{

  
  public function delete($packageName, $editId, $language, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'language' => $language);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function deleteall($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('deleteall', array($params));
  }

  
  public function get($packageName, $editId, $language, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'language' => $language);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_Listing");
  }

  
  public function listEditsListings($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidPublisher_ListingsListResponse");
  }

  
  public function patch($packageName, $editId, $language, Google_Service_AndroidPublisher_Listing $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'language' => $language, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidPublisher_Listing");
  }

  
  public function update($packageName, $editId, $language, Google_Service_AndroidPublisher_Listing $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'language' => $language, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidPublisher_Listing");
  }
}

class Google_Service_AndroidPublisher_EditsTesters_Resource extends Google_Service_Resource
{

  
  public function get($packageName, $editId, $track, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'track' => $track);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_Testers");
  }

  
  public function patch($packageName, $editId, $track, Google_Service_AndroidPublisher_Testers $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'track' => $track, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidPublisher_Testers");
  }

  
  public function update($packageName, $editId, $track, Google_Service_AndroidPublisher_Testers $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'track' => $track, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidPublisher_Testers");
  }
}

class Google_Service_AndroidPublisher_EditsTracks_Resource extends Google_Service_Resource
{

  
  public function get($packageName, $editId, $track, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'track' => $track);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_Track");
  }

  
  public function listEditsTracks($packageName, $editId, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidPublisher_TracksListResponse");
  }

  
  public function patch($packageName, $editId, $track, Google_Service_AndroidPublisher_Track $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'track' => $track, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidPublisher_Track");
  }

  
  public function update($packageName, $editId, $track, Google_Service_AndroidPublisher_Track $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'editId' => $editId, 'track' => $track, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidPublisher_Track");
  }
}


class Google_Service_AndroidPublisher_Entitlements_Resource extends Google_Service_Resource
{

  
  public function listEntitlements($packageName, $optParams = array())
  {
    $params = array('packageName' => $packageName);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidPublisher_EntitlementsListResponse");
  }
}


class Google_Service_AndroidPublisher_Inappproducts_Resource extends Google_Service_Resource
{

  
  public function batch(Google_Service_AndroidPublisher_InappproductsBatchRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('batch', array($params), "Google_Service_AndroidPublisher_InappproductsBatchResponse");
  }

  
  public function delete($packageName, $sku, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'sku' => $sku);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($packageName, $sku, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'sku' => $sku);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_InAppProduct");
  }

  
  public function insert($packageName, Google_Service_AndroidPublisher_InAppProduct $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_AndroidPublisher_InAppProduct");
  }

  
  public function listInappproducts($packageName, $optParams = array())
  {
    $params = array('packageName' => $packageName);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidPublisher_InappproductsListResponse");
  }

  
  public function patch($packageName, $sku, Google_Service_AndroidPublisher_InAppProduct $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'sku' => $sku, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidPublisher_InAppProduct");
  }

  
  public function update($packageName, $sku, Google_Service_AndroidPublisher_InAppProduct $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'sku' => $sku, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidPublisher_InAppProduct");
  }
}


class Google_Service_AndroidPublisher_Purchases_Resource extends Google_Service_Resource
{
}


class Google_Service_AndroidPublisher_PurchasesProducts_Resource extends Google_Service_Resource
{

  
  public function get($packageName, $productId, $token, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'productId' => $productId, 'token' => $token);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_ProductPurchase");
  }
}

class Google_Service_AndroidPublisher_PurchasesSubscriptions_Resource extends Google_Service_Resource
{

  
  public function cancel($packageName, $subscriptionId, $token, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'subscriptionId' => $subscriptionId, 'token' => $token);
    $params = array_merge($params, $optParams);
    return $this->call('cancel', array($params));
  }

  
  public function defer($packageName, $subscriptionId, $token, Google_Service_AndroidPublisher_SubscriptionPurchasesDeferRequest $postBody, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'subscriptionId' => $subscriptionId, 'token' => $token, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('defer', array($params), "Google_Service_AndroidPublisher_SubscriptionPurchasesDeferResponse");
  }

  
  public function get($packageName, $subscriptionId, $token, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'subscriptionId' => $subscriptionId, 'token' => $token);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidPublisher_SubscriptionPurchase");
  }

  
  public function refund($packageName, $subscriptionId, $token, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'subscriptionId' => $subscriptionId, 'token' => $token);
    $params = array_merge($params, $optParams);
    return $this->call('refund', array($params));
  }

  
  public function revoke($packageName, $subscriptionId, $token, $optParams = array())
  {
    $params = array('packageName' => $packageName, 'subscriptionId' => $subscriptionId, 'token' => $token);
    $params = array_merge($params, $optParams);
    return $this->call('revoke', array($params));
  }
}




class Google_Service_AndroidPublisher_Apk extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $binaryType = 'Google_Service_AndroidPublisher_ApkBinary';
  protected $binaryDataType = '';
  public $versionCode;


  public function setBinary(Google_Service_AndroidPublisher_ApkBinary $binary)
  {
    $this->binary = $binary;
  }
  public function getBinary()
  {
    return $this->binary;
  }
  public function setVersionCode($versionCode)
  {
    $this->versionCode = $versionCode;
  }
  public function getVersionCode()
  {
    return $this->versionCode;
  }
}

class Google_Service_AndroidPublisher_ApkBinary extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $sha1;


  public function setSha1($sha1)
  {
    $this->sha1 = $sha1;
  }
  public function getSha1()
  {
    return $this->sha1;
  }
}

class Google_Service_AndroidPublisher_ApkListing extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $language;
  public $recentChanges;


  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
  public function setRecentChanges($recentChanges)
  {
    $this->recentChanges = $recentChanges;
  }
  public function getRecentChanges()
  {
    return $this->recentChanges;
  }
}

class Google_Service_AndroidPublisher_ApkListingsListResponse extends Google_Collection
{
  protected $collection_key = 'listings';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $listingsType = 'Google_Service_AndroidPublisher_ApkListing';
  protected $listingsDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setListings($listings)
  {
    $this->listings = $listings;
  }
  public function getListings()
  {
    return $this->listings;
  }
}

class Google_Service_AndroidPublisher_ApksAddExternallyHostedRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $externallyHostedApkType = 'Google_Service_AndroidPublisher_ExternallyHostedApk';
  protected $externallyHostedApkDataType = '';


  public function setExternallyHostedApk(Google_Service_AndroidPublisher_ExternallyHostedApk $externallyHostedApk)
  {
    $this->externallyHostedApk = $externallyHostedApk;
  }
  public function getExternallyHostedApk()
  {
    return $this->externallyHostedApk;
  }
}

class Google_Service_AndroidPublisher_ApksAddExternallyHostedResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $externallyHostedApkType = 'Google_Service_AndroidPublisher_ExternallyHostedApk';
  protected $externallyHostedApkDataType = '';


  public function setExternallyHostedApk(Google_Service_AndroidPublisher_ExternallyHostedApk $externallyHostedApk)
  {
    $this->externallyHostedApk = $externallyHostedApk;
  }
  public function getExternallyHostedApk()
  {
    return $this->externallyHostedApk;
  }
}

class Google_Service_AndroidPublisher_ApksListResponse extends Google_Collection
{
  protected $collection_key = 'apks';
  protected $internal_gapi_mappings = array(
  );
  protected $apksType = 'Google_Service_AndroidPublisher_Apk';
  protected $apksDataType = 'array';
  public $kind;


  public function setApks($apks)
  {
    $this->apks = $apks;
  }
  public function getApks()
  {
    return $this->apks;
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

class Google_Service_AndroidPublisher_AppDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $contactEmail;
  public $contactPhone;
  public $contactWebsite;
  public $defaultLanguage;


  public function setContactEmail($contactEmail)
  {
    $this->contactEmail = $contactEmail;
  }
  public function getContactEmail()
  {
    return $this->contactEmail;
  }
  public function setContactPhone($contactPhone)
  {
    $this->contactPhone = $contactPhone;
  }
  public function getContactPhone()
  {
    return $this->contactPhone;
  }
  public function setContactWebsite($contactWebsite)
  {
    $this->contactWebsite = $contactWebsite;
  }
  public function getContactWebsite()
  {
    return $this->contactWebsite;
  }
  public function setDefaultLanguage($defaultLanguage)
  {
    $this->defaultLanguage = $defaultLanguage;
  }
  public function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
}

class Google_Service_AndroidPublisher_AppEdit extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $expiryTimeSeconds;
  public $id;


  public function setExpiryTimeSeconds($expiryTimeSeconds)
  {
    $this->expiryTimeSeconds = $expiryTimeSeconds;
  }
  public function getExpiryTimeSeconds()
  {
    return $this->expiryTimeSeconds;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
}

class Google_Service_AndroidPublisher_Entitlement extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  public $productId;
  public $productType;
  public $token;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setProductId($productId)
  {
    $this->productId = $productId;
  }
  public function getProductId()
  {
    return $this->productId;
  }
  public function setProductType($productType)
  {
    $this->productType = $productType;
  }
  public function getProductType()
  {
    return $this->productType;
  }
  public function setToken($token)
  {
    $this->token = $token;
  }
  public function getToken()
  {
    return $this->token;
  }
}

class Google_Service_AndroidPublisher_EntitlementsListResponse extends Google_Collection
{
  protected $collection_key = 'resources';
  protected $internal_gapi_mappings = array(
  );
  protected $pageInfoType = 'Google_Service_AndroidPublisher_PageInfo';
  protected $pageInfoDataType = '';
  protected $resourcesType = 'Google_Service_AndroidPublisher_Entitlement';
  protected $resourcesDataType = 'array';
  protected $tokenPaginationType = 'Google_Service_AndroidPublisher_TokenPagination';
  protected $tokenPaginationDataType = '';


  public function setPageInfo(Google_Service_AndroidPublisher_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setResources($resources)
  {
    $this->resources = $resources;
  }
  public function getResources()
  {
    return $this->resources;
  }
  public function setTokenPagination(Google_Service_AndroidPublisher_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
}

class Google_Service_AndroidPublisher_ExpansionFile extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $fileSize;
  public $referencesVersion;


  public function setFileSize($fileSize)
  {
    $this->fileSize = $fileSize;
  }
  public function getFileSize()
  {
    return $this->fileSize;
  }
  public function setReferencesVersion($referencesVersion)
  {
    $this->referencesVersion = $referencesVersion;
  }
  public function getReferencesVersion()
  {
    return $this->referencesVersion;
  }
}

class Google_Service_AndroidPublisher_ExpansionFilesUploadResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $expansionFileType = 'Google_Service_AndroidPublisher_ExpansionFile';
  protected $expansionFileDataType = '';


  public function setExpansionFile(Google_Service_AndroidPublisher_ExpansionFile $expansionFile)
  {
    $this->expansionFile = $expansionFile;
  }
  public function getExpansionFile()
  {
    return $this->expansionFile;
  }
}

class Google_Service_AndroidPublisher_ExternallyHostedApk extends Google_Collection
{
  protected $collection_key = 'usesPermissions';
  protected $internal_gapi_mappings = array(
  );
  public $applicationLabel;
  public $certificateBase64s;
  public $externallyHostedUrl;
  public $fileSha1Base64;
  public $fileSha256Base64;
  public $fileSize;
  public $iconBase64;
  public $maximumSdk;
  public $minimumSdk;
  public $nativeCodes;
  public $packageName;
  public $usesFeatures;
  protected $usesPermissionsType = 'Google_Service_AndroidPublisher_ExternallyHostedApkUsesPermission';
  protected $usesPermissionsDataType = 'array';
  public $versionCode;
  public $versionName;


  public function setApplicationLabel($applicationLabel)
  {
    $this->applicationLabel = $applicationLabel;
  }
  public function getApplicationLabel()
  {
    return $this->applicationLabel;
  }
  public function setCertificateBase64s($certificateBase64s)
  {
    $this->certificateBase64s = $certificateBase64s;
  }
  public function getCertificateBase64s()
  {
    return $this->certificateBase64s;
  }
  public function setExternallyHostedUrl($externallyHostedUrl)
  {
    $this->externallyHostedUrl = $externallyHostedUrl;
  }
  public function getExternallyHostedUrl()
  {
    return $this->externallyHostedUrl;
  }
  public function setFileSha1Base64($fileSha1Base64)
  {
    $this->fileSha1Base64 = $fileSha1Base64;
  }
  public function getFileSha1Base64()
  {
    return $this->fileSha1Base64;
  }
  public function setFileSha256Base64($fileSha256Base64)
  {
    $this->fileSha256Base64 = $fileSha256Base64;
  }
  public function getFileSha256Base64()
  {
    return $this->fileSha256Base64;
  }
  public function setFileSize($fileSize)
  {
    $this->fileSize = $fileSize;
  }
  public function getFileSize()
  {
    return $this->fileSize;
  }
  public function setIconBase64($iconBase64)
  {
    $this->iconBase64 = $iconBase64;
  }
  public function getIconBase64()
  {
    return $this->iconBase64;
  }
  public function setMaximumSdk($maximumSdk)
  {
    $this->maximumSdk = $maximumSdk;
  }
  public function getMaximumSdk()
  {
    return $this->maximumSdk;
  }
  public function setMinimumSdk($minimumSdk)
  {
    $this->minimumSdk = $minimumSdk;
  }
  public function getMinimumSdk()
  {
    return $this->minimumSdk;
  }
  public function setNativeCodes($nativeCodes)
  {
    $this->nativeCodes = $nativeCodes;
  }
  public function getNativeCodes()
  {
    return $this->nativeCodes;
  }
  public function setPackageName($packageName)
  {
    $this->packageName = $packageName;
  }
  public function getPackageName()
  {
    return $this->packageName;
  }
  public function setUsesFeatures($usesFeatures)
  {
    $this->usesFeatures = $usesFeatures;
  }
  public function getUsesFeatures()
  {
    return $this->usesFeatures;
  }
  public function setUsesPermissions($usesPermissions)
  {
    $this->usesPermissions = $usesPermissions;
  }
  public function getUsesPermissions()
  {
    return $this->usesPermissions;
  }
  public function setVersionCode($versionCode)
  {
    $this->versionCode = $versionCode;
  }
  public function getVersionCode()
  {
    return $this->versionCode;
  }
  public function setVersionName($versionName)
  {
    $this->versionName = $versionName;
  }
  public function getVersionName()
  {
    return $this->versionName;
  }
}

class Google_Service_AndroidPublisher_ExternallyHostedApkUsesPermission extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $maxSdkVersion;
  public $name;


  public function setMaxSdkVersion($maxSdkVersion)
  {
    $this->maxSdkVersion = $maxSdkVersion;
  }
  public function getMaxSdkVersion()
  {
    return $this->maxSdkVersion;
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

class Google_Service_AndroidPublisher_Image extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $sha1;
  public $url;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setSha1($sha1)
  {
    $this->sha1 = $sha1;
  }
  public function getSha1()
  {
    return $this->sha1;
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

class Google_Service_AndroidPublisher_ImagesDeleteAllResponse extends Google_Collection
{
  protected $collection_key = 'deleted';
  protected $internal_gapi_mappings = array(
  );
  protected $deletedType = 'Google_Service_AndroidPublisher_Image';
  protected $deletedDataType = 'array';


  public function setDeleted($deleted)
  {
    $this->deleted = $deleted;
  }
  public function getDeleted()
  {
    return $this->deleted;
  }
}

class Google_Service_AndroidPublisher_ImagesListResponse extends Google_Collection
{
  protected $collection_key = 'images';
  protected $internal_gapi_mappings = array(
  );
  protected $imagesType = 'Google_Service_AndroidPublisher_Image';
  protected $imagesDataType = 'array';


  public function setImages($images)
  {
    $this->images = $images;
  }
  public function getImages()
  {
    return $this->images;
  }
}

class Google_Service_AndroidPublisher_ImagesUploadResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $imageType = 'Google_Service_AndroidPublisher_Image';
  protected $imageDataType = '';


  public function setImage(Google_Service_AndroidPublisher_Image $image)
  {
    $this->image = $image;
  }
  public function getImage()
  {
    return $this->image;
  }
}

class Google_Service_AndroidPublisher_InAppProduct extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $defaultLanguage;
  protected $defaultPriceType = 'Google_Service_AndroidPublisher_Price';
  protected $defaultPriceDataType = '';
  protected $listingsType = 'Google_Service_AndroidPublisher_InAppProductListing';
  protected $listingsDataType = 'map';
  public $packageName;
  protected $pricesType = 'Google_Service_AndroidPublisher_Price';
  protected $pricesDataType = 'map';
  public $purchaseType;
  protected $seasonType = 'Google_Service_AndroidPublisher_Season';
  protected $seasonDataType = '';
  public $sku;
  public $status;
  public $subscriptionPeriod;
  public $trialPeriod;


  public function setDefaultLanguage($defaultLanguage)
  {
    $this->defaultLanguage = $defaultLanguage;
  }
  public function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
  public function setDefaultPrice(Google_Service_AndroidPublisher_Price $defaultPrice)
  {
    $this->defaultPrice = $defaultPrice;
  }
  public function getDefaultPrice()
  {
    return $this->defaultPrice;
  }
  public function setListings($listings)
  {
    $this->listings = $listings;
  }
  public function getListings()
  {
    return $this->listings;
  }
  public function setPackageName($packageName)
  {
    $this->packageName = $packageName;
  }
  public function getPackageName()
  {
    return $this->packageName;
  }
  public function setPrices($prices)
  {
    $this->prices = $prices;
  }
  public function getPrices()
  {
    return $this->prices;
  }
  public function setPurchaseType($purchaseType)
  {
    $this->purchaseType = $purchaseType;
  }
  public function getPurchaseType()
  {
    return $this->purchaseType;
  }
  public function setSeason(Google_Service_AndroidPublisher_Season $season)
  {
    $this->season = $season;
  }
  public function getSeason()
  {
    return $this->season;
  }
  public function setSku($sku)
  {
    $this->sku = $sku;
  }
  public function getSku()
  {
    return $this->sku;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setSubscriptionPeriod($subscriptionPeriod)
  {
    $this->subscriptionPeriod = $subscriptionPeriod;
  }
  public function getSubscriptionPeriod()
  {
    return $this->subscriptionPeriod;
  }
  public function setTrialPeriod($trialPeriod)
  {
    $this->trialPeriod = $trialPeriod;
  }
  public function getTrialPeriod()
  {
    return $this->trialPeriod;
  }
}

class Google_Service_AndroidPublisher_InAppProductListing extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $description;
  public $title;


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_AndroidPublisher_InAppProductListings extends Google_Model
{
}

class Google_Service_AndroidPublisher_InAppProductPrices extends Google_Model
{
}

class Google_Service_AndroidPublisher_InappproductsBatchRequest extends Google_Collection
{
  protected $collection_key = 'entrys';
  protected $internal_gapi_mappings = array(
  );
  protected $entrysType = 'Google_Service_AndroidPublisher_InappproductsBatchRequestEntry';
  protected $entrysDataType = 'array';


  public function setEntrys($entrys)
  {
    $this->entrys = $entrys;
  }
  public function getEntrys()
  {
    return $this->entrys;
  }
}

class Google_Service_AndroidPublisher_InappproductsBatchRequestEntry extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $batchId;
  protected $inappproductsinsertrequestType = 'Google_Service_AndroidPublisher_InappproductsInsertRequest';
  protected $inappproductsinsertrequestDataType = '';
  protected $inappproductsupdaterequestType = 'Google_Service_AndroidPublisher_InappproductsUpdateRequest';
  protected $inappproductsupdaterequestDataType = '';
  public $methodName;


  public function setBatchId($batchId)
  {
    $this->batchId = $batchId;
  }
  public function getBatchId()
  {
    return $this->batchId;
  }
  public function setInappproductsinsertrequest(Google_Service_AndroidPublisher_InappproductsInsertRequest $inappproductsinsertrequest)
  {
    $this->inappproductsinsertrequest = $inappproductsinsertrequest;
  }
  public function getInappproductsinsertrequest()
  {
    return $this->inappproductsinsertrequest;
  }
  public function setInappproductsupdaterequest(Google_Service_AndroidPublisher_InappproductsUpdateRequest $inappproductsupdaterequest)
  {
    $this->inappproductsupdaterequest = $inappproductsupdaterequest;
  }
  public function getInappproductsupdaterequest()
  {
    return $this->inappproductsupdaterequest;
  }
  public function setMethodName($methodName)
  {
    $this->methodName = $methodName;
  }
  public function getMethodName()
  {
    return $this->methodName;
  }
}

class Google_Service_AndroidPublisher_InappproductsBatchResponse extends Google_Collection
{
  protected $collection_key = 'entrys';
  protected $internal_gapi_mappings = array(
  );
  protected $entrysType = 'Google_Service_AndroidPublisher_InappproductsBatchResponseEntry';
  protected $entrysDataType = 'array';
  public $kind;


  public function setEntrys($entrys)
  {
    $this->entrys = $entrys;
  }
  public function getEntrys()
  {
    return $this->entrys;
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

class Google_Service_AndroidPublisher_InappproductsBatchResponseEntry extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $batchId;
  protected $inappproductsinsertresponseType = 'Google_Service_AndroidPublisher_InappproductsInsertResponse';
  protected $inappproductsinsertresponseDataType = '';
  protected $inappproductsupdateresponseType = 'Google_Service_AndroidPublisher_InappproductsUpdateResponse';
  protected $inappproductsupdateresponseDataType = '';


  public function setBatchId($batchId)
  {
    $this->batchId = $batchId;
  }
  public function getBatchId()
  {
    return $this->batchId;
  }
  public function setInappproductsinsertresponse(Google_Service_AndroidPublisher_InappproductsInsertResponse $inappproductsinsertresponse)
  {
    $this->inappproductsinsertresponse = $inappproductsinsertresponse;
  }
  public function getInappproductsinsertresponse()
  {
    return $this->inappproductsinsertresponse;
  }
  public function setInappproductsupdateresponse(Google_Service_AndroidPublisher_InappproductsUpdateResponse $inappproductsupdateresponse)
  {
    $this->inappproductsupdateresponse = $inappproductsupdateresponse;
  }
  public function getInappproductsupdateresponse()
  {
    return $this->inappproductsupdateresponse;
  }
}

class Google_Service_AndroidPublisher_InappproductsInsertRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $inappproductType = 'Google_Service_AndroidPublisher_InAppProduct';
  protected $inappproductDataType = '';


  public function setInappproduct(Google_Service_AndroidPublisher_InAppProduct $inappproduct)
  {
    $this->inappproduct = $inappproduct;
  }
  public function getInappproduct()
  {
    return $this->inappproduct;
  }
}

class Google_Service_AndroidPublisher_InappproductsInsertResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $inappproductType = 'Google_Service_AndroidPublisher_InAppProduct';
  protected $inappproductDataType = '';


  public function setInappproduct(Google_Service_AndroidPublisher_InAppProduct $inappproduct)
  {
    $this->inappproduct = $inappproduct;
  }
  public function getInappproduct()
  {
    return $this->inappproduct;
  }
}

class Google_Service_AndroidPublisher_InappproductsListResponse extends Google_Collection
{
  protected $collection_key = 'inappproduct';
  protected $internal_gapi_mappings = array(
  );
  protected $inappproductType = 'Google_Service_AndroidPublisher_InAppProduct';
  protected $inappproductDataType = 'array';
  public $kind;
  protected $pageInfoType = 'Google_Service_AndroidPublisher_PageInfo';
  protected $pageInfoDataType = '';
  protected $tokenPaginationType = 'Google_Service_AndroidPublisher_TokenPagination';
  protected $tokenPaginationDataType = '';


  public function setInappproduct($inappproduct)
  {
    $this->inappproduct = $inappproduct;
  }
  public function getInappproduct()
  {
    return $this->inappproduct;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPageInfo(Google_Service_AndroidPublisher_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setTokenPagination(Google_Service_AndroidPublisher_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
}

class Google_Service_AndroidPublisher_InappproductsUpdateRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $inappproductType = 'Google_Service_AndroidPublisher_InAppProduct';
  protected $inappproductDataType = '';


  public function setInappproduct(Google_Service_AndroidPublisher_InAppProduct $inappproduct)
  {
    $this->inappproduct = $inappproduct;
  }
  public function getInappproduct()
  {
    return $this->inappproduct;
  }
}

class Google_Service_AndroidPublisher_InappproductsUpdateResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $inappproductType = 'Google_Service_AndroidPublisher_InAppProduct';
  protected $inappproductDataType = '';


  public function setInappproduct(Google_Service_AndroidPublisher_InAppProduct $inappproduct)
  {
    $this->inappproduct = $inappproduct;
  }
  public function getInappproduct()
  {
    return $this->inappproduct;
  }
}

class Google_Service_AndroidPublisher_Listing extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $fullDescription;
  public $language;
  public $shortDescription;
  public $title;
  public $video;


  public function setFullDescription($fullDescription)
  {
    $this->fullDescription = $fullDescription;
  }
  public function getFullDescription()
  {
    return $this->fullDescription;
  }
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
  public function setShortDescription($shortDescription)
  {
    $this->shortDescription = $shortDescription;
  }
  public function getShortDescription()
  {
    return $this->shortDescription;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setVideo($video)
  {
    $this->video = $video;
  }
  public function getVideo()
  {
    return $this->video;
  }
}

class Google_Service_AndroidPublisher_ListingsListResponse extends Google_Collection
{
  protected $collection_key = 'listings';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $listingsType = 'Google_Service_AndroidPublisher_Listing';
  protected $listingsDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setListings($listings)
  {
    $this->listings = $listings;
  }
  public function getListings()
  {
    return $this->listings;
  }
}

class Google_Service_AndroidPublisher_MonthDay extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $day;
  public $month;


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
}

class Google_Service_AndroidPublisher_PageInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $resultPerPage;
  public $startIndex;
  public $totalResults;


  public function setResultPerPage($resultPerPage)
  {
    $this->resultPerPage = $resultPerPage;
  }
  public function getResultPerPage()
  {
    return $this->resultPerPage;
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

class Google_Service_AndroidPublisher_Price extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $currency;
  public $priceMicros;


  public function setCurrency($currency)
  {
    $this->currency = $currency;
  }
  public function getCurrency()
  {
    return $this->currency;
  }
  public function setPriceMicros($priceMicros)
  {
    $this->priceMicros = $priceMicros;
  }
  public function getPriceMicros()
  {
    return $this->priceMicros;
  }
}

class Google_Service_AndroidPublisher_ProductPurchase extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $consumptionState;
  public $developerPayload;
  public $kind;
  public $purchaseState;
  public $purchaseTimeMillis;


  public function setConsumptionState($consumptionState)
  {
    $this->consumptionState = $consumptionState;
  }
  public function getConsumptionState()
  {
    return $this->consumptionState;
  }
  public function setDeveloperPayload($developerPayload)
  {
    $this->developerPayload = $developerPayload;
  }
  public function getDeveloperPayload()
  {
    return $this->developerPayload;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPurchaseState($purchaseState)
  {
    $this->purchaseState = $purchaseState;
  }
  public function getPurchaseState()
  {
    return $this->purchaseState;
  }
  public function setPurchaseTimeMillis($purchaseTimeMillis)
  {
    $this->purchaseTimeMillis = $purchaseTimeMillis;
  }
  public function getPurchaseTimeMillis()
  {
    return $this->purchaseTimeMillis;
  }
}

class Google_Service_AndroidPublisher_Season extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $endType = 'Google_Service_AndroidPublisher_MonthDay';
  protected $endDataType = '';
  protected $startType = 'Google_Service_AndroidPublisher_MonthDay';
  protected $startDataType = '';


  public function setEnd(Google_Service_AndroidPublisher_MonthDay $end)
  {
    $this->end = $end;
  }
  public function getEnd()
  {
    return $this->end;
  }
  public function setStart(Google_Service_AndroidPublisher_MonthDay $start)
  {
    $this->start = $start;
  }
  public function getStart()
  {
    return $this->start;
  }
}

class Google_Service_AndroidPublisher_SubscriptionDeferralInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $desiredExpiryTimeMillis;
  public $expectedExpiryTimeMillis;


  public function setDesiredExpiryTimeMillis($desiredExpiryTimeMillis)
  {
    $this->desiredExpiryTimeMillis = $desiredExpiryTimeMillis;
  }
  public function getDesiredExpiryTimeMillis()
  {
    return $this->desiredExpiryTimeMillis;
  }
  public function setExpectedExpiryTimeMillis($expectedExpiryTimeMillis)
  {
    $this->expectedExpiryTimeMillis = $expectedExpiryTimeMillis;
  }
  public function getExpectedExpiryTimeMillis()
  {
    return $this->expectedExpiryTimeMillis;
  }
}

class Google_Service_AndroidPublisher_SubscriptionPurchase extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $autoRenewing;
  public $expiryTimeMillis;
  public $kind;
  public $startTimeMillis;


  public function setAutoRenewing($autoRenewing)
  {
    $this->autoRenewing = $autoRenewing;
  }
  public function getAutoRenewing()
  {
    return $this->autoRenewing;
  }
  public function setExpiryTimeMillis($expiryTimeMillis)
  {
    $this->expiryTimeMillis = $expiryTimeMillis;
  }
  public function getExpiryTimeMillis()
  {
    return $this->expiryTimeMillis;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
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

class Google_Service_AndroidPublisher_SubscriptionPurchasesDeferRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $deferralInfoType = 'Google_Service_AndroidPublisher_SubscriptionDeferralInfo';
  protected $deferralInfoDataType = '';


  public function setDeferralInfo(Google_Service_AndroidPublisher_SubscriptionDeferralInfo $deferralInfo)
  {
    $this->deferralInfo = $deferralInfo;
  }
  public function getDeferralInfo()
  {
    return $this->deferralInfo;
  }
}

class Google_Service_AndroidPublisher_SubscriptionPurchasesDeferResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $newExpiryTimeMillis;


  public function setNewExpiryTimeMillis($newExpiryTimeMillis)
  {
    $this->newExpiryTimeMillis = $newExpiryTimeMillis;
  }
  public function getNewExpiryTimeMillis()
  {
    return $this->newExpiryTimeMillis;
  }
}

class Google_Service_AndroidPublisher_Testers extends Google_Collection
{
  protected $collection_key = 'googlePlusCommunities';
  protected $internal_gapi_mappings = array(
  );
  public $googleGroups;
  public $googlePlusCommunities;


  public function setGoogleGroups($googleGroups)
  {
    $this->googleGroups = $googleGroups;
  }
  public function getGoogleGroups()
  {
    return $this->googleGroups;
  }
  public function setGooglePlusCommunities($googlePlusCommunities)
  {
    $this->googlePlusCommunities = $googlePlusCommunities;
  }
  public function getGooglePlusCommunities()
  {
    return $this->googlePlusCommunities;
  }
}

class Google_Service_AndroidPublisher_TokenPagination extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  public $previousPageToken;


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setPreviousPageToken($previousPageToken)
  {
    $this->previousPageToken = $previousPageToken;
  }
  public function getPreviousPageToken()
  {
    return $this->previousPageToken;
  }
}

class Google_Service_AndroidPublisher_Track extends Google_Collection
{
  protected $collection_key = 'versionCodes';
  protected $internal_gapi_mappings = array(
  );
  public $track;
  public $userFraction;
  public $versionCodes;


  public function setTrack($track)
  {
    $this->track = $track;
  }
  public function getTrack()
  {
    return $this->track;
  }
  public function setUserFraction($userFraction)
  {
    $this->userFraction = $userFraction;
  }
  public function getUserFraction()
  {
    return $this->userFraction;
  }
  public function setVersionCodes($versionCodes)
  {
    $this->versionCodes = $versionCodes;
  }
  public function getVersionCodes()
  {
    return $this->versionCodes;
  }
}

class Google_Service_AndroidPublisher_TracksListResponse extends Google_Collection
{
  protected $collection_key = 'tracks';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $tracksType = 'Google_Service_AndroidPublisher_Track';
  protected $tracksDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setTracks($tracks)
  {
    $this->tracks = $tracks;
  }
  public function getTracks()
  {
    return $this->tracks;
  }
}
