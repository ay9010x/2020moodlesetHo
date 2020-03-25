<?php



class Google_Service_AndroidEnterprise extends Google_Service
{
  
  const ANDROIDENTERPRISE =
      "https://www.googleapis.com/auth/androidenterprise";

  public $collections;
  public $collectionviewers;
  public $devices;
  public $enterprises;
  public $entitlements;
  public $grouplicenses;
  public $grouplicenseusers;
  public $installs;
  public $permissions;
  public $products;
  public $users;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'androidenterprise/v1/';
    $this->version = 'v1';
    $this->serviceName = 'androidenterprise';

    $this->collections = new Google_Service_AndroidEnterprise_Collections_Resource(
        $this,
        $this->serviceName,
        'collections',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'enterprises/{enterpriseId}/collections',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/collections',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->collectionviewers = new Google_Service_AndroidEnterprise_Collectionviewers_Resource(
        $this,
        $this->serviceName,
        'collectionviewers',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users/{userId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users/{userId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users/{userId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/collections/{collectionId}/users/{userId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'collectionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
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
    $this->devices = new Google_Service_AndroidEnterprise_Devices_Resource(
        $this,
        $this->serviceName,
        'devices',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getState' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/state',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setState' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/state',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->enterprises = new Google_Service_AndroidEnterprise_Enterprises_Resource(
        $this,
        $this->serviceName,
        'enterprises',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'enroll' => array(
              'path' => 'enterprises/enroll',
              'httpMethod' => 'POST',
              'parameters' => array(
                'token' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'enterprises',
              'httpMethod' => 'POST',
              'parameters' => array(
                'token' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises',
              'httpMethod' => 'GET',
              'parameters' => array(
                'domain' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'sendTestPushNotification' => array(
              'path' => 'enterprises/{enterpriseId}/sendTestPushNotification',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setAccount' => array(
              'path' => 'enterprises/{enterpriseId}/account',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'unenroll' => array(
              'path' => 'enterprises/{enterpriseId}/unenroll',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->entitlements = new Google_Service_AndroidEnterprise_Entitlements_Resource(
        $this,
        $this->serviceName,
        'entitlements',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements/{entitlementId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entitlementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements/{entitlementId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entitlementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements/{entitlementId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entitlementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'install' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/entitlements/{entitlementId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entitlementId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'install' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->grouplicenses = new Google_Service_AndroidEnterprise_Grouplicenses_Resource(
        $this,
        $this->serviceName,
        'grouplicenses',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'enterprises/{enterpriseId}/groupLicenses/{groupLicenseId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'groupLicenseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/groupLicenses',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->grouplicenseusers = new Google_Service_AndroidEnterprise_Grouplicenseusers_Resource(
        $this,
        $this->serviceName,
        'grouplicenseusers',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'enterprises/{enterpriseId}/groupLicenses/{groupLicenseId}/users',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'groupLicenseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->installs = new Google_Service_AndroidEnterprise_Installs_Resource(
        $this,
        $this->serviceName,
        'installs',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs/{installId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'installId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs/{installId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'installId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs/{installId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'installId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/devices/{deviceId}/installs/{installId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'deviceId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'installId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->permissions = new Google_Service_AndroidEnterprise_Permissions_Resource(
        $this,
        $this->serviceName,
        'permissions',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'permissions/{permissionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'permissionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->products = new Google_Service_AndroidEnterprise_Products_Resource(
        $this,
        $this->serviceName,
        'products',
        array(
          'methods' => array(
            'approve' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/approve',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'generateApprovalUrl' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/generateApprovalUrl',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'languageCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'getAppRestrictionsSchema' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/appRestrictionsSchema',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'language' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'getPermissions' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/permissions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updatePermissions' => array(
              'path' => 'enterprises/{enterpriseId}/products/{productId}/permissions',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->users = new Google_Service_AndroidEnterprise_Users_Resource(
        $this,
        $this->serviceName,
        'users',
        array(
          'methods' => array(
            'generateToken' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/token',
              'httpMethod' => 'POST',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getAvailableProductSet' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/availableProductSet',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'enterprises/{enterpriseId}/users',
              'httpMethod' => 'GET',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'email' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'revokeToken' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/token',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setAvailableProductSet' => array(
              'path' => 'enterprises/{enterpriseId}/users/{userId}/availableProductSet',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'enterpriseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
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
  }
}



class Google_Service_AndroidEnterprise_Collections_Resource extends Google_Service_Resource
{

  
  public function delete($enterpriseId, $collectionId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($enterpriseId, $collectionId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Collection");
  }

  
  public function insert($enterpriseId, Google_Service_AndroidEnterprise_Collection $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_AndroidEnterprise_Collection");
  }

  
  public function listCollections($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_CollectionsListResponse");
  }

  
  public function patch($enterpriseId, $collectionId, Google_Service_AndroidEnterprise_Collection $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidEnterprise_Collection");
  }

  
  public function update($enterpriseId, $collectionId, Google_Service_AndroidEnterprise_Collection $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidEnterprise_Collection");
  }
}


class Google_Service_AndroidEnterprise_Collectionviewers_Resource extends Google_Service_Resource
{

  
  public function delete($enterpriseId, $collectionId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($enterpriseId, $collectionId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_User");
  }

  
  public function listCollectionviewers($enterpriseId, $collectionId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_CollectionViewersListResponse");
  }

  
  public function patch($enterpriseId, $collectionId, $userId, Google_Service_AndroidEnterprise_User $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidEnterprise_User");
  }

  
  public function update($enterpriseId, $collectionId, $userId, Google_Service_AndroidEnterprise_User $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidEnterprise_User");
  }
}


class Google_Service_AndroidEnterprise_Devices_Resource extends Google_Service_Resource
{

  
  public function get($enterpriseId, $userId, $deviceId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'deviceId' => $deviceId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Device");
  }

  
  public function getState($enterpriseId, $userId, $deviceId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'deviceId' => $deviceId);
    $params = array_merge($params, $optParams);
    return $this->call('getState', array($params), "Google_Service_AndroidEnterprise_DeviceState");
  }

  
  public function listDevices($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_DevicesListResponse");
  }

  
  public function setState($enterpriseId, $userId, $deviceId, Google_Service_AndroidEnterprise_DeviceState $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'deviceId' => $deviceId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setState', array($params), "Google_Service_AndroidEnterprise_DeviceState");
  }
}


class Google_Service_AndroidEnterprise_Enterprises_Resource extends Google_Service_Resource
{

  
  public function delete($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function enroll($token, Google_Service_AndroidEnterprise_Enterprise $postBody, $optParams = array())
  {
    $params = array('token' => $token, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('enroll', array($params), "Google_Service_AndroidEnterprise_Enterprise");
  }

  
  public function get($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Enterprise");
  }

  
  public function insert($token, Google_Service_AndroidEnterprise_Enterprise $postBody, $optParams = array())
  {
    $params = array('token' => $token, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_AndroidEnterprise_Enterprise");
  }

  
  public function listEnterprises($domain, $optParams = array())
  {
    $params = array('domain' => $domain);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_EnterprisesListResponse");
  }

  
  public function sendTestPushNotification($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('sendTestPushNotification', array($params), "Google_Service_AndroidEnterprise_EnterprisesSendTestPushNotificationResponse");
  }

  
  public function setAccount($enterpriseId, Google_Service_AndroidEnterprise_EnterpriseAccount $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setAccount', array($params), "Google_Service_AndroidEnterprise_EnterpriseAccount");
  }

  
  public function unenroll($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('unenroll', array($params));
  }
}


class Google_Service_AndroidEnterprise_Entitlements_Resource extends Google_Service_Resource
{

  
  public function delete($enterpriseId, $userId, $entitlementId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'entitlementId' => $entitlementId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($enterpriseId, $userId, $entitlementId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'entitlementId' => $entitlementId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Entitlement");
  }

  
  public function listEntitlements($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_EntitlementsListResponse");
  }

  
  public function patch($enterpriseId, $userId, $entitlementId, Google_Service_AndroidEnterprise_Entitlement $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'entitlementId' => $entitlementId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidEnterprise_Entitlement");
  }

  
  public function update($enterpriseId, $userId, $entitlementId, Google_Service_AndroidEnterprise_Entitlement $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'entitlementId' => $entitlementId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidEnterprise_Entitlement");
  }
}


class Google_Service_AndroidEnterprise_Grouplicenses_Resource extends Google_Service_Resource
{

  
  public function get($enterpriseId, $groupLicenseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'groupLicenseId' => $groupLicenseId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_GroupLicense");
  }

  
  public function listGrouplicenses($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_GroupLicensesListResponse");
  }
}


class Google_Service_AndroidEnterprise_Grouplicenseusers_Resource extends Google_Service_Resource
{

  
  public function listGrouplicenseusers($enterpriseId, $groupLicenseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'groupLicenseId' => $groupLicenseId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_GroupLicenseUsersListResponse");
  }
}


class Google_Service_AndroidEnterprise_Installs_Resource extends Google_Service_Resource
{

  
  public function delete($enterpriseId, $userId, $deviceId, $installId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'deviceId' => $deviceId, 'installId' => $installId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($enterpriseId, $userId, $deviceId, $installId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'deviceId' => $deviceId, 'installId' => $installId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Install");
  }

  
  public function listInstalls($enterpriseId, $userId, $deviceId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'deviceId' => $deviceId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_InstallsListResponse");
  }

  
  public function patch($enterpriseId, $userId, $deviceId, $installId, Google_Service_AndroidEnterprise_Install $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'deviceId' => $deviceId, 'installId' => $installId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidEnterprise_Install");
  }

  
  public function update($enterpriseId, $userId, $deviceId, $installId, Google_Service_AndroidEnterprise_Install $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'deviceId' => $deviceId, 'installId' => $installId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidEnterprise_Install");
  }
}


class Google_Service_AndroidEnterprise_Permissions_Resource extends Google_Service_Resource
{

  
  public function get($permissionId, $optParams = array())
  {
    $params = array('permissionId' => $permissionId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Permission");
  }
}


class Google_Service_AndroidEnterprise_Products_Resource extends Google_Service_Resource
{

  
  public function approve($enterpriseId, $productId, Google_Service_AndroidEnterprise_ProductsApproveRequest $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'productId' => $productId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('approve', array($params));
  }

  
  public function generateApprovalUrl($enterpriseId, $productId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'productId' => $productId);
    $params = array_merge($params, $optParams);
    return $this->call('generateApprovalUrl', array($params), "Google_Service_AndroidEnterprise_ProductsGenerateApprovalUrlResponse");
  }

  
  public function get($enterpriseId, $productId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'productId' => $productId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Product");
  }

  
  public function getAppRestrictionsSchema($enterpriseId, $productId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'productId' => $productId);
    $params = array_merge($params, $optParams);
    return $this->call('getAppRestrictionsSchema', array($params), "Google_Service_AndroidEnterprise_AppRestrictionsSchema");
  }

  
  public function getPermissions($enterpriseId, $productId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'productId' => $productId);
    $params = array_merge($params, $optParams);
    return $this->call('getPermissions', array($params), "Google_Service_AndroidEnterprise_ProductPermissions");
  }

  
  public function updatePermissions($enterpriseId, $productId, Google_Service_AndroidEnterprise_ProductPermissions $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'productId' => $productId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updatePermissions', array($params), "Google_Service_AndroidEnterprise_ProductPermissions");
  }
}


class Google_Service_AndroidEnterprise_Users_Resource extends Google_Service_Resource
{

  
  public function generateToken($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('generateToken', array($params), "Google_Service_AndroidEnterprise_UserToken");
  }

  
  public function get($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_User");
  }

  
  public function getAvailableProductSet($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('getAvailableProductSet', array($params), "Google_Service_AndroidEnterprise_ProductSet");
  }

  
  public function listUsers($enterpriseId, $email, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'email' => $email);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_UsersListResponse");
  }

  
  public function revokeToken($enterpriseId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('revokeToken', array($params));
  }

  
  public function setAvailableProductSet($enterpriseId, $userId, Google_Service_AndroidEnterprise_ProductSet $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setAvailableProductSet', array($params), "Google_Service_AndroidEnterprise_ProductSet");
  }
}




class Google_Service_AndroidEnterprise_AppRestrictionsSchema extends Google_Collection
{
  protected $collection_key = 'restrictions';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $restrictionsType = 'Google_Service_AndroidEnterprise_AppRestrictionsSchemaRestriction';
  protected $restrictionsDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setRestrictions($restrictions)
  {
    $this->restrictions = $restrictions;
  }
  public function getRestrictions()
  {
    return $this->restrictions;
  }
}

class Google_Service_AndroidEnterprise_AppRestrictionsSchemaRestriction extends Google_Collection
{
  protected $collection_key = 'entryValue';
  protected $internal_gapi_mappings = array(
  );
  protected $defaultValueType = 'Google_Service_AndroidEnterprise_AppRestrictionsSchemaRestrictionRestrictionValue';
  protected $defaultValueDataType = '';
  public $description;
  public $entry;
  public $entryValue;
  public $key;
  public $restrictionType;
  public $title;


  public function setDefaultValue(Google_Service_AndroidEnterprise_AppRestrictionsSchemaRestrictionRestrictionValue $defaultValue)
  {
    $this->defaultValue = $defaultValue;
  }
  public function getDefaultValue()
  {
    return $this->defaultValue;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setEntry($entry)
  {
    $this->entry = $entry;
  }
  public function getEntry()
  {
    return $this->entry;
  }
  public function setEntryValue($entryValue)
  {
    $this->entryValue = $entryValue;
  }
  public function getEntryValue()
  {
    return $this->entryValue;
  }
  public function setKey($key)
  {
    $this->key = $key;
  }
  public function getKey()
  {
    return $this->key;
  }
  public function setRestrictionType($restrictionType)
  {
    $this->restrictionType = $restrictionType;
  }
  public function getRestrictionType()
  {
    return $this->restrictionType;
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

class Google_Service_AndroidEnterprise_AppRestrictionsSchemaRestrictionRestrictionValue extends Google_Collection
{
  protected $collection_key = 'valueMultiselect';
  protected $internal_gapi_mappings = array(
  );
  public $type;
  public $valueBool;
  public $valueInteger;
  public $valueMultiselect;
  public $valueString;


  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setValueBool($valueBool)
  {
    $this->valueBool = $valueBool;
  }
  public function getValueBool()
  {
    return $this->valueBool;
  }
  public function setValueInteger($valueInteger)
  {
    $this->valueInteger = $valueInteger;
  }
  public function getValueInteger()
  {
    return $this->valueInteger;
  }
  public function setValueMultiselect($valueMultiselect)
  {
    $this->valueMultiselect = $valueMultiselect;
  }
  public function getValueMultiselect()
  {
    return $this->valueMultiselect;
  }
  public function setValueString($valueString)
  {
    $this->valueString = $valueString;
  }
  public function getValueString()
  {
    return $this->valueString;
  }
}

class Google_Service_AndroidEnterprise_AppVersion extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $versionCode;
  public $versionString;


  public function setVersionCode($versionCode)
  {
    $this->versionCode = $versionCode;
  }
  public function getVersionCode()
  {
    return $this->versionCode;
  }
  public function setVersionString($versionString)
  {
    $this->versionString = $versionString;
  }
  public function getVersionString()
  {
    return $this->versionString;
  }
}

class Google_Service_AndroidEnterprise_ApprovalUrlInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $approvalUrl;
  public $kind;


  public function setApprovalUrl($approvalUrl)
  {
    $this->approvalUrl = $approvalUrl;
  }
  public function getApprovalUrl()
  {
    return $this->approvalUrl;
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

class Google_Service_AndroidEnterprise_Collection extends Google_Collection
{
  protected $collection_key = 'productId';
  protected $internal_gapi_mappings = array(
  );
  public $collectionId;
  public $kind;
  public $name;
  public $productId;
  public $visibility;


  public function setCollectionId($collectionId)
  {
    $this->collectionId = $collectionId;
  }
  public function getCollectionId()
  {
    return $this->collectionId;
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
  public function setProductId($productId)
  {
    $this->productId = $productId;
  }
  public function getProductId()
  {
    return $this->productId;
  }
  public function setVisibility($visibility)
  {
    $this->visibility = $visibility;
  }
  public function getVisibility()
  {
    return $this->visibility;
  }
}

class Google_Service_AndroidEnterprise_CollectionViewersListResponse extends Google_Collection
{
  protected $collection_key = 'user';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $userType = 'Google_Service_AndroidEnterprise_User';
  protected $userDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setUser($user)
  {
    $this->user = $user;
  }
  public function getUser()
  {
    return $this->user;
  }
}

class Google_Service_AndroidEnterprise_CollectionsListResponse extends Google_Collection
{
  protected $collection_key = 'collection';
  protected $internal_gapi_mappings = array(
  );
  protected $collectionType = 'Google_Service_AndroidEnterprise_Collection';
  protected $collectionDataType = 'array';
  public $kind;


  public function setCollection($collection)
  {
    $this->collection = $collection;
  }
  public function getCollection()
  {
    return $this->collection;
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

class Google_Service_AndroidEnterprise_Device extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $androidId;
  public $kind;
  public $managementType;


  public function setAndroidId($androidId)
  {
    $this->androidId = $androidId;
  }
  public function getAndroidId()
  {
    return $this->androidId;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setManagementType($managementType)
  {
    $this->managementType = $managementType;
  }
  public function getManagementType()
  {
    return $this->managementType;
  }
}

class Google_Service_AndroidEnterprise_DeviceState extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountState;
  public $kind;


  public function setAccountState($accountState)
  {
    $this->accountState = $accountState;
  }
  public function getAccountState()
  {
    return $this->accountState;
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

class Google_Service_AndroidEnterprise_DevicesListResponse extends Google_Collection
{
  protected $collection_key = 'device';
  protected $internal_gapi_mappings = array(
  );
  protected $deviceType = 'Google_Service_AndroidEnterprise_Device';
  protected $deviceDataType = 'array';
  public $kind;


  public function setDevice($device)
  {
    $this->device = $device;
  }
  public function getDevice()
  {
    return $this->device;
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

class Google_Service_AndroidEnterprise_Enterprise extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $name;
  public $primaryDomain;


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
  public function setPrimaryDomain($primaryDomain)
  {
    $this->primaryDomain = $primaryDomain;
  }
  public function getPrimaryDomain()
  {
    return $this->primaryDomain;
  }
}

class Google_Service_AndroidEnterprise_EnterpriseAccount extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountEmail;
  public $kind;


  public function setAccountEmail($accountEmail)
  {
    $this->accountEmail = $accountEmail;
  }
  public function getAccountEmail()
  {
    return $this->accountEmail;
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

class Google_Service_AndroidEnterprise_EnterprisesListResponse extends Google_Collection
{
  protected $collection_key = 'enterprise';
  protected $internal_gapi_mappings = array(
  );
  protected $enterpriseType = 'Google_Service_AndroidEnterprise_Enterprise';
  protected $enterpriseDataType = 'array';
  public $kind;


  public function setEnterprise($enterprise)
  {
    $this->enterprise = $enterprise;
  }
  public function getEnterprise()
  {
    return $this->enterprise;
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

class Google_Service_AndroidEnterprise_EnterprisesSendTestPushNotificationResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $messageId;
  public $topicName;


  public function setMessageId($messageId)
  {
    $this->messageId = $messageId;
  }
  public function getMessageId()
  {
    return $this->messageId;
  }
  public function setTopicName($topicName)
  {
    $this->topicName = $topicName;
  }
  public function getTopicName()
  {
    return $this->topicName;
  }
}

class Google_Service_AndroidEnterprise_Entitlement extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  public $productId;
  public $reason;


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
  public function setReason($reason)
  {
    $this->reason = $reason;
  }
  public function getReason()
  {
    return $this->reason;
  }
}

class Google_Service_AndroidEnterprise_EntitlementsListResponse extends Google_Collection
{
  protected $collection_key = 'entitlement';
  protected $internal_gapi_mappings = array(
  );
  protected $entitlementType = 'Google_Service_AndroidEnterprise_Entitlement';
  protected $entitlementDataType = 'array';
  public $kind;


  public function setEntitlement($entitlement)
  {
    $this->entitlement = $entitlement;
  }
  public function getEntitlement()
  {
    return $this->entitlement;
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

class Google_Service_AndroidEnterprise_GroupLicense extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $acquisitionKind;
  public $approval;
  public $kind;
  public $numProvisioned;
  public $numPurchased;
  public $productId;


  public function setAcquisitionKind($acquisitionKind)
  {
    $this->acquisitionKind = $acquisitionKind;
  }
  public function getAcquisitionKind()
  {
    return $this->acquisitionKind;
  }
  public function setApproval($approval)
  {
    $this->approval = $approval;
  }
  public function getApproval()
  {
    return $this->approval;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNumProvisioned($numProvisioned)
  {
    $this->numProvisioned = $numProvisioned;
  }
  public function getNumProvisioned()
  {
    return $this->numProvisioned;
  }
  public function setNumPurchased($numPurchased)
  {
    $this->numPurchased = $numPurchased;
  }
  public function getNumPurchased()
  {
    return $this->numPurchased;
  }
  public function setProductId($productId)
  {
    $this->productId = $productId;
  }
  public function getProductId()
  {
    return $this->productId;
  }
}

class Google_Service_AndroidEnterprise_GroupLicenseUsersListResponse extends Google_Collection
{
  protected $collection_key = 'user';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $userType = 'Google_Service_AndroidEnterprise_User';
  protected $userDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setUser($user)
  {
    $this->user = $user;
  }
  public function getUser()
  {
    return $this->user;
  }
}

class Google_Service_AndroidEnterprise_GroupLicensesListResponse extends Google_Collection
{
  protected $collection_key = 'groupLicense';
  protected $internal_gapi_mappings = array(
  );
  protected $groupLicenseType = 'Google_Service_AndroidEnterprise_GroupLicense';
  protected $groupLicenseDataType = 'array';
  public $kind;


  public function setGroupLicense($groupLicense)
  {
    $this->groupLicense = $groupLicense;
  }
  public function getGroupLicense()
  {
    return $this->groupLicense;
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

class Google_Service_AndroidEnterprise_Install extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $installState;
  public $kind;
  public $productId;
  public $versionCode;


  public function setInstallState($installState)
  {
    $this->installState = $installState;
  }
  public function getInstallState()
  {
    return $this->installState;
  }
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
  public function setVersionCode($versionCode)
  {
    $this->versionCode = $versionCode;
  }
  public function getVersionCode()
  {
    return $this->versionCode;
  }
}

class Google_Service_AndroidEnterprise_InstallsListResponse extends Google_Collection
{
  protected $collection_key = 'install';
  protected $internal_gapi_mappings = array(
  );
  protected $installType = 'Google_Service_AndroidEnterprise_Install';
  protected $installDataType = 'array';
  public $kind;


  public function setInstall($install)
  {
    $this->install = $install;
  }
  public function getInstall()
  {
    return $this->install;
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

class Google_Service_AndroidEnterprise_Permission extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $description;
  public $kind;
  public $name;
  public $permissionId;


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
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
  public function setPermissionId($permissionId)
  {
    $this->permissionId = $permissionId;
  }
  public function getPermissionId()
  {
    return $this->permissionId;
  }
}

class Google_Service_AndroidEnterprise_Product extends Google_Collection
{
  protected $collection_key = 'appVersion';
  protected $internal_gapi_mappings = array(
  );
  protected $appVersionType = 'Google_Service_AndroidEnterprise_AppVersion';
  protected $appVersionDataType = 'array';
  public $authorName;
  public $detailsUrl;
  public $distributionChannel;
  public $iconUrl;
  public $kind;
  public $productId;
  public $requiresContainerApp;
  public $title;
  public $workDetailsUrl;


  public function setAppVersion($appVersion)
  {
    $this->appVersion = $appVersion;
  }
  public function getAppVersion()
  {
    return $this->appVersion;
  }
  public function setAuthorName($authorName)
  {
    $this->authorName = $authorName;
  }
  public function getAuthorName()
  {
    return $this->authorName;
  }
  public function setDetailsUrl($detailsUrl)
  {
    $this->detailsUrl = $detailsUrl;
  }
  public function getDetailsUrl()
  {
    return $this->detailsUrl;
  }
  public function setDistributionChannel($distributionChannel)
  {
    $this->distributionChannel = $distributionChannel;
  }
  public function getDistributionChannel()
  {
    return $this->distributionChannel;
  }
  public function setIconUrl($iconUrl)
  {
    $this->iconUrl = $iconUrl;
  }
  public function getIconUrl()
  {
    return $this->iconUrl;
  }
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
  public function setRequiresContainerApp($requiresContainerApp)
  {
    $this->requiresContainerApp = $requiresContainerApp;
  }
  public function getRequiresContainerApp()
  {
    return $this->requiresContainerApp;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setWorkDetailsUrl($workDetailsUrl)
  {
    $this->workDetailsUrl = $workDetailsUrl;
  }
  public function getWorkDetailsUrl()
  {
    return $this->workDetailsUrl;
  }
}

class Google_Service_AndroidEnterprise_ProductPermission extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $permissionId;
  public $state;


  public function setPermissionId($permissionId)
  {
    $this->permissionId = $permissionId;
  }
  public function getPermissionId()
  {
    return $this->permissionId;
  }
  public function setState($state)
  {
    $this->state = $state;
  }
  public function getState()
  {
    return $this->state;
  }
}

class Google_Service_AndroidEnterprise_ProductPermissions extends Google_Collection
{
  protected $collection_key = 'permission';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $permissionType = 'Google_Service_AndroidEnterprise_ProductPermission';
  protected $permissionDataType = 'array';
  public $productId;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPermission($permission)
  {
    $this->permission = $permission;
  }
  public function getPermission()
  {
    return $this->permission;
  }
  public function setProductId($productId)
  {
    $this->productId = $productId;
  }
  public function getProductId()
  {
    return $this->productId;
  }
}

class Google_Service_AndroidEnterprise_ProductSet extends Google_Collection
{
  protected $collection_key = 'productId';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  public $productId;


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
}

class Google_Service_AndroidEnterprise_ProductsApproveRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $approvalUrlInfoType = 'Google_Service_AndroidEnterprise_ApprovalUrlInfo';
  protected $approvalUrlInfoDataType = '';


  public function setApprovalUrlInfo(Google_Service_AndroidEnterprise_ApprovalUrlInfo $approvalUrlInfo)
  {
    $this->approvalUrlInfo = $approvalUrlInfo;
  }
  public function getApprovalUrlInfo()
  {
    return $this->approvalUrlInfo;
  }
}

class Google_Service_AndroidEnterprise_ProductsGenerateApprovalUrlResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $url;


  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_AndroidEnterprise_User extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $kind;
  public $primaryEmail;


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
  public function setPrimaryEmail($primaryEmail)
  {
    $this->primaryEmail = $primaryEmail;
  }
  public function getPrimaryEmail()
  {
    return $this->primaryEmail;
  }
}

class Google_Service_AndroidEnterprise_UserToken extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  public $token;
  public $userId;


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setToken($token)
  {
    $this->token = $token;
  }
  public function getToken()
  {
    return $this->token;
  }
  public function setUserId($userId)
  {
    $this->userId = $userId;
  }
  public function getUserId()
  {
    return $this->userId;
  }
}

class Google_Service_AndroidEnterprise_UsersListResponse extends Google_Collection
{
  protected $collection_key = 'user';
  protected $internal_gapi_mappings = array(
  );
  public $kind;
  protected $userType = 'Google_Service_AndroidEnterprise_User';
  protected $userDataType = 'array';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setUser($user)
  {
    $this->user = $user;
  }
  public function getUser()
  {
    return $this->user;
  }
}
