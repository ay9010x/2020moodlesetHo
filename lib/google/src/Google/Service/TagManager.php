<?php



class Google_Service_TagManager extends Google_Service
{
  
  const TAGMANAGER_DELETE_CONTAINERS =
      "https://www.googleapis.com/auth/tagmanager.delete.containers";
  
  const TAGMANAGER_EDIT_CONTAINERS =
      "https://www.googleapis.com/auth/tagmanager.edit.containers";
  
  const TAGMANAGER_EDIT_CONTAINERVERSIONS =
      "https://www.googleapis.com/auth/tagmanager.edit.containerversions";
  
  const TAGMANAGER_MANAGE_ACCOUNTS =
      "https://www.googleapis.com/auth/tagmanager.manage.accounts";
  
  const TAGMANAGER_MANAGE_USERS =
      "https://www.googleapis.com/auth/tagmanager.manage.users";
  
  const TAGMANAGER_PUBLISH =
      "https://www.googleapis.com/auth/tagmanager.publish";
  
  const TAGMANAGER_READONLY =
      "https://www.googleapis.com/auth/tagmanager.readonly";

  public $accounts;
  public $accounts_containers;
  public $accounts_containers_folders;
  public $accounts_containers_folders_entities;
  public $accounts_containers_move_folders;
  public $accounts_containers_tags;
  public $accounts_containers_triggers;
  public $accounts_containers_variables;
  public $accounts_containers_versions;
  public $accounts_permissions;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'tagmanager/v1/';
    $this->version = 'v1';
    $this->serviceName = 'tagmanager';

    $this->accounts = new Google_Service_TagManager_Accounts_Resource(
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
              ),
            ),'list' => array(
              'path' => 'accounts',
              'httpMethod' => 'GET',
              'parameters' => array(),
            ),'update' => array(
              'path' => 'accounts/{accountId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers = new Google_Service_TagManager_AccountsContainers_Resource(
        $this,
        $this->serviceName,
        'containers',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_folders = new Google_Service_TagManager_AccountsContainersFolders_Resource(
        $this,
        $this->serviceName,
        'folders',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders/{folderId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders/{folderId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders/{folderId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_folders_entities = new Google_Service_TagManager_AccountsContainersFoldersEntities_Resource(
        $this,
        $this->serviceName,
        'entities',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/folders/{folderId}/entities',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_move_folders = new Google_Service_TagManager_AccountsContainersMoveFolders_Resource(
        $this,
        $this->serviceName,
        'move_folders',
        array(
          'methods' => array(
            'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/move_folders/{folderId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'folderId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'variableId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'tagId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'triggerId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_tags = new Google_Service_TagManager_AccountsContainersTags_Resource(
        $this,
        $this->serviceName,
        'tags',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags/{tagId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tagId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags/{tagId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tagId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/tags/{tagId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tagId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_triggers = new Google_Service_TagManager_AccountsContainersTriggers_Resource(
        $this,
        $this->serviceName,
        'triggers',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers/{triggerId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'triggerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers/{triggerId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'triggerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/triggers/{triggerId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'triggerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_variables = new Google_Service_TagManager_AccountsContainersVariables_Resource(
        $this,
        $this->serviceName,
        'variables',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables/{variableId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'variableId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables/{variableId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'variableId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/variables/{variableId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'variableId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_containers_versions = new Google_Service_TagManager_AccountsContainersVersions_Resource(
        $this,
        $this->serviceName,
        'versions',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'headers' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'publish' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}/publish',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'restore' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}/restore',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'undelete' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}/undelete',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/containers/{containerId}/versions/{containerVersionId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'containerVersionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fingerprint' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->accounts_permissions = new Google_Service_TagManager_AccountsPermissions_Resource(
        $this,
        $this->serviceName,
        'permissions',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'accounts/{accountId}/permissions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'accounts/{accountId}/permissions/{permissionId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'permissionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'accounts/{accountId}/permissions/{permissionId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'permissionId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'accounts/{accountId}/permissions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'accounts/{accountId}/permissions/{permissionId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'accountId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'permissionId' => array(
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



class Google_Service_TagManager_Accounts_Resource extends Google_Service_Resource
{

  
  public function get($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Account");
  }

  
  public function listAccounts($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListAccountsResponse");
  }

  
  public function update($accountId, Google_Service_TagManager_Account $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Account");
  }
}


class Google_Service_TagManager_AccountsContainers_Resource extends Google_Service_Resource
{

  
  public function create($accountId, Google_Service_TagManager_Container $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Container");
  }

  
  public function delete($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Container");
  }

  
  public function listAccountsContainers($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListContainersResponse");
  }

  
  public function update($accountId, $containerId, Google_Service_TagManager_Container $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Container");
  }
}


class Google_Service_TagManager_AccountsContainersFolders_Resource extends Google_Service_Resource
{

  
  public function create($accountId, $containerId, Google_Service_TagManager_Folder $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Folder");
  }

  
  public function delete($accountId, $containerId, $folderId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $containerId, $folderId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Folder");
  }

  
  public function listAccountsContainersFolders($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListFoldersResponse");
  }

  
  public function update($accountId, $containerId, $folderId, Google_Service_TagManager_Folder $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Folder");
  }
}


class Google_Service_TagManager_AccountsContainersFoldersEntities_Resource extends Google_Service_Resource
{

  
  public function listAccountsContainersFoldersEntities($accountId, $containerId, $folderId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_FolderEntities");
  }
}

class Google_Service_TagManager_AccountsContainersMoveFolders_Resource extends Google_Service_Resource
{

  
  public function update($accountId, $containerId, $folderId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params));
  }
}

class Google_Service_TagManager_AccountsContainersTags_Resource extends Google_Service_Resource
{

  
  public function create($accountId, $containerId, Google_Service_TagManager_Tag $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Tag");
  }

  
  public function delete($accountId, $containerId, $tagId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'tagId' => $tagId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $containerId, $tagId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'tagId' => $tagId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Tag");
  }

  
  public function listAccountsContainersTags($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListTagsResponse");
  }

  
  public function update($accountId, $containerId, $tagId, Google_Service_TagManager_Tag $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'tagId' => $tagId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Tag");
  }
}

class Google_Service_TagManager_AccountsContainersTriggers_Resource extends Google_Service_Resource
{

  
  public function create($accountId, $containerId, Google_Service_TagManager_Trigger $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Trigger");
  }

  
  public function delete($accountId, $containerId, $triggerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'triggerId' => $triggerId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $containerId, $triggerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'triggerId' => $triggerId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Trigger");
  }

  
  public function listAccountsContainersTriggers($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListTriggersResponse");
  }

  
  public function update($accountId, $containerId, $triggerId, Google_Service_TagManager_Trigger $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'triggerId' => $triggerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Trigger");
  }
}

class Google_Service_TagManager_AccountsContainersVariables_Resource extends Google_Service_Resource
{

  
  public function create($accountId, $containerId, Google_Service_TagManager_Variable $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Variable");
  }

  
  public function delete($accountId, $containerId, $variableId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'variableId' => $variableId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $containerId, $variableId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'variableId' => $variableId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Variable");
  }

  
  public function listAccountsContainersVariables($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListVariablesResponse");
  }

  
  public function update($accountId, $containerId, $variableId, Google_Service_TagManager_Variable $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'variableId' => $variableId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Variable");
  }
}

class Google_Service_TagManager_AccountsContainersVersions_Resource extends Google_Service_Resource
{

  
  public function create($accountId, $containerId, Google_Service_TagManager_CreateContainerVersionRequestVersionOptions $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_CreateContainerVersionResponse");
  }

  
  public function delete($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_ContainerVersion");
  }

  
  public function listAccountsContainersVersions($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListContainerVersionsResponse");
  }

  
  public function publish($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('publish', array($params), "Google_Service_TagManager_PublishContainerVersionResponse");
  }

  
  public function restore($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('restore', array($params), "Google_Service_TagManager_ContainerVersion");
  }

  
  public function undelete($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('undelete', array($params), "Google_Service_TagManager_ContainerVersion");
  }

  
  public function update($accountId, $containerId, $containerVersionId, Google_Service_TagManager_ContainerVersion $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_ContainerVersion");
  }
}

class Google_Service_TagManager_AccountsPermissions_Resource extends Google_Service_Resource
{

  
  public function create($accountId, Google_Service_TagManager_UserAccess $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_UserAccess");
  }

  
  public function delete($accountId, $permissionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'permissionId' => $permissionId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($accountId, $permissionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'permissionId' => $permissionId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_UserAccess");
  }

  
  public function listAccountsPermissions($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListAccountUsersResponse");
  }

  
  public function update($accountId, $permissionId, Google_Service_TagManager_UserAccess $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'permissionId' => $permissionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_UserAccess");
  }
}




class Google_Service_TagManager_Account extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $fingerprint;
  public $name;
  public $shareData;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setShareData($shareData)
  {
    $this->shareData = $shareData;
  }
  public function getShareData()
  {
    return $this->shareData;
  }
}

class Google_Service_TagManager_AccountAccess extends Google_Collection
{
  protected $collection_key = 'permission';
  protected $internal_gapi_mappings = array(
  );
  public $permission;


  public function setPermission($permission)
  {
    $this->permission = $permission;
  }
  public function getPermission()
  {
    return $this->permission;
  }
}

class Google_Service_TagManager_Condition extends Google_Collection
{
  protected $collection_key = 'parameter';
  protected $internal_gapi_mappings = array(
  );
  protected $parameterType = 'Google_Service_TagManager_Parameter';
  protected $parameterDataType = 'array';
  public $type;


  public function setParameter($parameter)
  {
    $this->parameter = $parameter;
  }
  public function getParameter()
  {
    return $this->parameter;
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

class Google_Service_TagManager_Container extends Google_Collection
{
  protected $collection_key = 'usageContext';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $containerId;
  public $domainName;
  public $enabledBuiltInVariable;
  public $fingerprint;
  public $name;
  public $notes;
  public $publicId;
  public $timeZoneCountryId;
  public $timeZoneId;
  public $usageContext;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setDomainName($domainName)
  {
    $this->domainName = $domainName;
  }
  public function getDomainName()
  {
    return $this->domainName;
  }
  public function setEnabledBuiltInVariable($enabledBuiltInVariable)
  {
    $this->enabledBuiltInVariable = $enabledBuiltInVariable;
  }
  public function getEnabledBuiltInVariable()
  {
    return $this->enabledBuiltInVariable;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setPublicId($publicId)
  {
    $this->publicId = $publicId;
  }
  public function getPublicId()
  {
    return $this->publicId;
  }
  public function setTimeZoneCountryId($timeZoneCountryId)
  {
    $this->timeZoneCountryId = $timeZoneCountryId;
  }
  public function getTimeZoneCountryId()
  {
    return $this->timeZoneCountryId;
  }
  public function setTimeZoneId($timeZoneId)
  {
    $this->timeZoneId = $timeZoneId;
  }
  public function getTimeZoneId()
  {
    return $this->timeZoneId;
  }
  public function setUsageContext($usageContext)
  {
    $this->usageContext = $usageContext;
  }
  public function getUsageContext()
  {
    return $this->usageContext;
  }
}

class Google_Service_TagManager_ContainerAccess extends Google_Collection
{
  protected $collection_key = 'permission';
  protected $internal_gapi_mappings = array(
  );
  public $containerId;
  public $permission;


  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setPermission($permission)
  {
    $this->permission = $permission;
  }
  public function getPermission()
  {
    return $this->permission;
  }
}

class Google_Service_TagManager_ContainerVersion extends Google_Collection
{
  protected $collection_key = 'variable';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  protected $containerType = 'Google_Service_TagManager_Container';
  protected $containerDataType = '';
  public $containerId;
  public $containerVersionId;
  public $deleted;
  public $fingerprint;
  protected $folderType = 'Google_Service_TagManager_Folder';
  protected $folderDataType = 'array';
  protected $macroType = 'Google_Service_TagManager_Macro';
  protected $macroDataType = 'array';
  public $name;
  public $notes;
  protected $ruleType = 'Google_Service_TagManager_Rule';
  protected $ruleDataType = 'array';
  protected $tagType = 'Google_Service_TagManager_Tag';
  protected $tagDataType = 'array';
  protected $triggerType = 'Google_Service_TagManager_Trigger';
  protected $triggerDataType = 'array';
  protected $variableType = 'Google_Service_TagManager_Variable';
  protected $variableDataType = 'array';


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainer(Google_Service_TagManager_Container $container)
  {
    $this->container = $container;
  }
  public function getContainer()
  {
    return $this->container;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setContainerVersionId($containerVersionId)
  {
    $this->containerVersionId = $containerVersionId;
  }
  public function getContainerVersionId()
  {
    return $this->containerVersionId;
  }
  public function setDeleted($deleted)
  {
    $this->deleted = $deleted;
  }
  public function getDeleted()
  {
    return $this->deleted;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setFolder($folder)
  {
    $this->folder = $folder;
  }
  public function getFolder()
  {
    return $this->folder;
  }
  public function setMacro($macro)
  {
    $this->macro = $macro;
  }
  public function getMacro()
  {
    return $this->macro;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setRule($rule)
  {
    $this->rule = $rule;
  }
  public function getRule()
  {
    return $this->rule;
  }
  public function setTag($tag)
  {
    $this->tag = $tag;
  }
  public function getTag()
  {
    return $this->tag;
  }
  public function setTrigger($trigger)
  {
    $this->trigger = $trigger;
  }
  public function getTrigger()
  {
    return $this->trigger;
  }
  public function setVariable($variable)
  {
    $this->variable = $variable;
  }
  public function getVariable()
  {
    return $this->variable;
  }
}

class Google_Service_TagManager_ContainerVersionHeader extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $containerId;
  public $containerVersionId;
  public $deleted;
  public $name;
  public $numMacros;
  public $numRules;
  public $numTags;
  public $numTriggers;
  public $numVariables;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setContainerVersionId($containerVersionId)
  {
    $this->containerVersionId = $containerVersionId;
  }
  public function getContainerVersionId()
  {
    return $this->containerVersionId;
  }
  public function setDeleted($deleted)
  {
    $this->deleted = $deleted;
  }
  public function getDeleted()
  {
    return $this->deleted;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNumMacros($numMacros)
  {
    $this->numMacros = $numMacros;
  }
  public function getNumMacros()
  {
    return $this->numMacros;
  }
  public function setNumRules($numRules)
  {
    $this->numRules = $numRules;
  }
  public function getNumRules()
  {
    return $this->numRules;
  }
  public function setNumTags($numTags)
  {
    $this->numTags = $numTags;
  }
  public function getNumTags()
  {
    return $this->numTags;
  }
  public function setNumTriggers($numTriggers)
  {
    $this->numTriggers = $numTriggers;
  }
  public function getNumTriggers()
  {
    return $this->numTriggers;
  }
  public function setNumVariables($numVariables)
  {
    $this->numVariables = $numVariables;
  }
  public function getNumVariables()
  {
    return $this->numVariables;
  }
}

class Google_Service_TagManager_CreateContainerVersionRequestVersionOptions extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $name;
  public $notes;
  public $quickPreview;


  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setQuickPreview($quickPreview)
  {
    $this->quickPreview = $quickPreview;
  }
  public function getQuickPreview()
  {
    return $this->quickPreview;
  }
}

class Google_Service_TagManager_CreateContainerVersionResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $compilerError;
  protected $containerVersionType = 'Google_Service_TagManager_ContainerVersion';
  protected $containerVersionDataType = '';


  public function setCompilerError($compilerError)
  {
    $this->compilerError = $compilerError;
  }
  public function getCompilerError()
  {
    return $this->compilerError;
  }
  public function setContainerVersion(Google_Service_TagManager_ContainerVersion $containerVersion)
  {
    $this->containerVersion = $containerVersion;
  }
  public function getContainerVersion()
  {
    return $this->containerVersion;
  }
}

class Google_Service_TagManager_Folder extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $containerId;
  public $fingerprint;
  public $folderId;
  public $name;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setFolderId($folderId)
  {
    $this->folderId = $folderId;
  }
  public function getFolderId()
  {
    return $this->folderId;
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

class Google_Service_TagManager_FolderEntities extends Google_Collection
{
  protected $collection_key = 'variable';
  protected $internal_gapi_mappings = array(
  );
  protected $tagType = 'Google_Service_TagManager_Tag';
  protected $tagDataType = 'array';
  protected $triggerType = 'Google_Service_TagManager_Trigger';
  protected $triggerDataType = 'array';
  protected $variableType = 'Google_Service_TagManager_Variable';
  protected $variableDataType = 'array';


  public function setTag($tag)
  {
    $this->tag = $tag;
  }
  public function getTag()
  {
    return $this->tag;
  }
  public function setTrigger($trigger)
  {
    $this->trigger = $trigger;
  }
  public function getTrigger()
  {
    return $this->trigger;
  }
  public function setVariable($variable)
  {
    $this->variable = $variable;
  }
  public function getVariable()
  {
    return $this->variable;
  }
}

class Google_Service_TagManager_ListAccountUsersResponse extends Google_Collection
{
  protected $collection_key = 'userAccess';
  protected $internal_gapi_mappings = array(
  );
  protected $userAccessType = 'Google_Service_TagManager_UserAccess';
  protected $userAccessDataType = 'array';


  public function setUserAccess($userAccess)
  {
    $this->userAccess = $userAccess;
  }
  public function getUserAccess()
  {
    return $this->userAccess;
  }
}

class Google_Service_TagManager_ListAccountsResponse extends Google_Collection
{
  protected $collection_key = 'accounts';
  protected $internal_gapi_mappings = array(
  );
  protected $accountsType = 'Google_Service_TagManager_Account';
  protected $accountsDataType = 'array';


  public function setAccounts($accounts)
  {
    $this->accounts = $accounts;
  }
  public function getAccounts()
  {
    return $this->accounts;
  }
}

class Google_Service_TagManager_ListContainerVersionsResponse extends Google_Collection
{
  protected $collection_key = 'containerVersionHeader';
  protected $internal_gapi_mappings = array(
  );
  protected $containerVersionType = 'Google_Service_TagManager_ContainerVersion';
  protected $containerVersionDataType = 'array';
  protected $containerVersionHeaderType = 'Google_Service_TagManager_ContainerVersionHeader';
  protected $containerVersionHeaderDataType = 'array';


  public function setContainerVersion($containerVersion)
  {
    $this->containerVersion = $containerVersion;
  }
  public function getContainerVersion()
  {
    return $this->containerVersion;
  }
  public function setContainerVersionHeader($containerVersionHeader)
  {
    $this->containerVersionHeader = $containerVersionHeader;
  }
  public function getContainerVersionHeader()
  {
    return $this->containerVersionHeader;
  }
}

class Google_Service_TagManager_ListContainersResponse extends Google_Collection
{
  protected $collection_key = 'containers';
  protected $internal_gapi_mappings = array(
  );
  protected $containersType = 'Google_Service_TagManager_Container';
  protected $containersDataType = 'array';


  public function setContainers($containers)
  {
    $this->containers = $containers;
  }
  public function getContainers()
  {
    return $this->containers;
  }
}

class Google_Service_TagManager_ListFoldersResponse extends Google_Collection
{
  protected $collection_key = 'folders';
  protected $internal_gapi_mappings = array(
  );
  protected $foldersType = 'Google_Service_TagManager_Folder';
  protected $foldersDataType = 'array';


  public function setFolders($folders)
  {
    $this->folders = $folders;
  }
  public function getFolders()
  {
    return $this->folders;
  }
}

class Google_Service_TagManager_ListTagsResponse extends Google_Collection
{
  protected $collection_key = 'tags';
  protected $internal_gapi_mappings = array(
  );
  protected $tagsType = 'Google_Service_TagManager_Tag';
  protected $tagsDataType = 'array';


  public function setTags($tags)
  {
    $this->tags = $tags;
  }
  public function getTags()
  {
    return $this->tags;
  }
}

class Google_Service_TagManager_ListTriggersResponse extends Google_Collection
{
  protected $collection_key = 'triggers';
  protected $internal_gapi_mappings = array(
  );
  protected $triggersType = 'Google_Service_TagManager_Trigger';
  protected $triggersDataType = 'array';


  public function setTriggers($triggers)
  {
    $this->triggers = $triggers;
  }
  public function getTriggers()
  {
    return $this->triggers;
  }
}

class Google_Service_TagManager_ListVariablesResponse extends Google_Collection
{
  protected $collection_key = 'variables';
  protected $internal_gapi_mappings = array(
  );
  protected $variablesType = 'Google_Service_TagManager_Variable';
  protected $variablesDataType = 'array';


  public function setVariables($variables)
  {
    $this->variables = $variables;
  }
  public function getVariables()
  {
    return $this->variables;
  }
}

class Google_Service_TagManager_Macro extends Google_Collection
{
  protected $collection_key = 'parameter';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $containerId;
  public $disablingRuleId;
  public $enablingRuleId;
  public $fingerprint;
  public $macroId;
  public $name;
  public $notes;
  protected $parameterType = 'Google_Service_TagManager_Parameter';
  protected $parameterDataType = 'array';
  public $parentFolderId;
  public $scheduleEndMs;
  public $scheduleStartMs;
  public $type;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setDisablingRuleId($disablingRuleId)
  {
    $this->disablingRuleId = $disablingRuleId;
  }
  public function getDisablingRuleId()
  {
    return $this->disablingRuleId;
  }
  public function setEnablingRuleId($enablingRuleId)
  {
    $this->enablingRuleId = $enablingRuleId;
  }
  public function getEnablingRuleId()
  {
    return $this->enablingRuleId;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setMacroId($macroId)
  {
    $this->macroId = $macroId;
  }
  public function getMacroId()
  {
    return $this->macroId;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setParameter($parameter)
  {
    $this->parameter = $parameter;
  }
  public function getParameter()
  {
    return $this->parameter;
  }
  public function setParentFolderId($parentFolderId)
  {
    $this->parentFolderId = $parentFolderId;
  }
  public function getParentFolderId()
  {
    return $this->parentFolderId;
  }
  public function setScheduleEndMs($scheduleEndMs)
  {
    $this->scheduleEndMs = $scheduleEndMs;
  }
  public function getScheduleEndMs()
  {
    return $this->scheduleEndMs;
  }
  public function setScheduleStartMs($scheduleStartMs)
  {
    $this->scheduleStartMs = $scheduleStartMs;
  }
  public function getScheduleStartMs()
  {
    return $this->scheduleStartMs;
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

class Google_Service_TagManager_Parameter extends Google_Collection
{
  protected $collection_key = 'map';
  protected $internal_gapi_mappings = array(
  );
  public $key;
  protected $listType = 'Google_Service_TagManager_Parameter';
  protected $listDataType = 'array';
  protected $mapType = 'Google_Service_TagManager_Parameter';
  protected $mapDataType = 'array';
  public $type;
  public $value;


  public function setKey($key)
  {
    $this->key = $key;
  }
  public function getKey()
  {
    return $this->key;
  }
  public function setList($list)
  {
    $this->list = $list;
  }
  public function getList()
  {
    return $this->list;
  }
  public function setMap($map)
  {
    $this->map = $map;
  }
  public function getMap()
  {
    return $this->map;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
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

class Google_Service_TagManager_PublishContainerVersionResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $compilerError;
  protected $containerVersionType = 'Google_Service_TagManager_ContainerVersion';
  protected $containerVersionDataType = '';


  public function setCompilerError($compilerError)
  {
    $this->compilerError = $compilerError;
  }
  public function getCompilerError()
  {
    return $this->compilerError;
  }
  public function setContainerVersion(Google_Service_TagManager_ContainerVersion $containerVersion)
  {
    $this->containerVersion = $containerVersion;
  }
  public function getContainerVersion()
  {
    return $this->containerVersion;
  }
}

class Google_Service_TagManager_Rule extends Google_Collection
{
  protected $collection_key = 'condition';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  protected $conditionType = 'Google_Service_TagManager_Condition';
  protected $conditionDataType = 'array';
  public $containerId;
  public $fingerprint;
  public $name;
  public $notes;
  public $ruleId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setCondition($condition)
  {
    $this->condition = $condition;
  }
  public function getCondition()
  {
    return $this->condition;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setRuleId($ruleId)
  {
    $this->ruleId = $ruleId;
  }
  public function getRuleId()
  {
    return $this->ruleId;
  }
}

class Google_Service_TagManager_SetupTag extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $stopOnSetupFailure;
  public $tagName;


  public function setStopOnSetupFailure($stopOnSetupFailure)
  {
    $this->stopOnSetupFailure = $stopOnSetupFailure;
  }
  public function getStopOnSetupFailure()
  {
    return $this->stopOnSetupFailure;
  }
  public function setTagName($tagName)
  {
    $this->tagName = $tagName;
  }
  public function getTagName()
  {
    return $this->tagName;
  }
}

class Google_Service_TagManager_Tag extends Google_Collection
{
  protected $collection_key = 'teardownTag';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $blockingRuleId;
  public $blockingTriggerId;
  public $containerId;
  public $fingerprint;
  public $firingRuleId;
  public $firingTriggerId;
  public $liveOnly;
  public $name;
  public $notes;
  protected $parameterType = 'Google_Service_TagManager_Parameter';
  protected $parameterDataType = 'array';
  public $parentFolderId;
  protected $priorityType = 'Google_Service_TagManager_Parameter';
  protected $priorityDataType = '';
  public $scheduleEndMs;
  public $scheduleStartMs;
  protected $setupTagType = 'Google_Service_TagManager_SetupTag';
  protected $setupTagDataType = 'array';
  public $tagFiringOption;
  public $tagId;
  protected $teardownTagType = 'Google_Service_TagManager_TeardownTag';
  protected $teardownTagDataType = 'array';
  public $type;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setBlockingRuleId($blockingRuleId)
  {
    $this->blockingRuleId = $blockingRuleId;
  }
  public function getBlockingRuleId()
  {
    return $this->blockingRuleId;
  }
  public function setBlockingTriggerId($blockingTriggerId)
  {
    $this->blockingTriggerId = $blockingTriggerId;
  }
  public function getBlockingTriggerId()
  {
    return $this->blockingTriggerId;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setFiringRuleId($firingRuleId)
  {
    $this->firingRuleId = $firingRuleId;
  }
  public function getFiringRuleId()
  {
    return $this->firingRuleId;
  }
  public function setFiringTriggerId($firingTriggerId)
  {
    $this->firingTriggerId = $firingTriggerId;
  }
  public function getFiringTriggerId()
  {
    return $this->firingTriggerId;
  }
  public function setLiveOnly($liveOnly)
  {
    $this->liveOnly = $liveOnly;
  }
  public function getLiveOnly()
  {
    return $this->liveOnly;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setParameter($parameter)
  {
    $this->parameter = $parameter;
  }
  public function getParameter()
  {
    return $this->parameter;
  }
  public function setParentFolderId($parentFolderId)
  {
    $this->parentFolderId = $parentFolderId;
  }
  public function getParentFolderId()
  {
    return $this->parentFolderId;
  }
  public function setPriority(Google_Service_TagManager_Parameter $priority)
  {
    $this->priority = $priority;
  }
  public function getPriority()
  {
    return $this->priority;
  }
  public function setScheduleEndMs($scheduleEndMs)
  {
    $this->scheduleEndMs = $scheduleEndMs;
  }
  public function getScheduleEndMs()
  {
    return $this->scheduleEndMs;
  }
  public function setScheduleStartMs($scheduleStartMs)
  {
    $this->scheduleStartMs = $scheduleStartMs;
  }
  public function getScheduleStartMs()
  {
    return $this->scheduleStartMs;
  }
  public function setSetupTag($setupTag)
  {
    $this->setupTag = $setupTag;
  }
  public function getSetupTag()
  {
    return $this->setupTag;
  }
  public function setTagFiringOption($tagFiringOption)
  {
    $this->tagFiringOption = $tagFiringOption;
  }
  public function getTagFiringOption()
  {
    return $this->tagFiringOption;
  }
  public function setTagId($tagId)
  {
    $this->tagId = $tagId;
  }
  public function getTagId()
  {
    return $this->tagId;
  }
  public function setTeardownTag($teardownTag)
  {
    $this->teardownTag = $teardownTag;
  }
  public function getTeardownTag()
  {
    return $this->teardownTag;
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

class Google_Service_TagManager_TeardownTag extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $stopTeardownOnFailure;
  public $tagName;


  public function setStopTeardownOnFailure($stopTeardownOnFailure)
  {
    $this->stopTeardownOnFailure = $stopTeardownOnFailure;
  }
  public function getStopTeardownOnFailure()
  {
    return $this->stopTeardownOnFailure;
  }
  public function setTagName($tagName)
  {
    $this->tagName = $tagName;
  }
  public function getTagName()
  {
    return $this->tagName;
  }
}

class Google_Service_TagManager_Trigger extends Google_Collection
{
  protected $collection_key = 'filter';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  protected $autoEventFilterType = 'Google_Service_TagManager_Condition';
  protected $autoEventFilterDataType = 'array';
  protected $checkValidationType = 'Google_Service_TagManager_Parameter';
  protected $checkValidationDataType = '';
  public $containerId;
  protected $customEventFilterType = 'Google_Service_TagManager_Condition';
  protected $customEventFilterDataType = 'array';
  protected $enableAllVideosType = 'Google_Service_TagManager_Parameter';
  protected $enableAllVideosDataType = '';
  protected $eventNameType = 'Google_Service_TagManager_Parameter';
  protected $eventNameDataType = '';
  protected $filterType = 'Google_Service_TagManager_Condition';
  protected $filterDataType = 'array';
  public $fingerprint;
  protected $intervalType = 'Google_Service_TagManager_Parameter';
  protected $intervalDataType = '';
  protected $limitType = 'Google_Service_TagManager_Parameter';
  protected $limitDataType = '';
  public $name;
  public $parentFolderId;
  public $triggerId;
  public $type;
  protected $uniqueTriggerIdType = 'Google_Service_TagManager_Parameter';
  protected $uniqueTriggerIdDataType = '';
  protected $videoPercentageListType = 'Google_Service_TagManager_Parameter';
  protected $videoPercentageListDataType = '';
  protected $waitForTagsType = 'Google_Service_TagManager_Parameter';
  protected $waitForTagsDataType = '';
  protected $waitForTagsTimeoutType = 'Google_Service_TagManager_Parameter';
  protected $waitForTagsTimeoutDataType = '';


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setAutoEventFilter($autoEventFilter)
  {
    $this->autoEventFilter = $autoEventFilter;
  }
  public function getAutoEventFilter()
  {
    return $this->autoEventFilter;
  }
  public function setCheckValidation(Google_Service_TagManager_Parameter $checkValidation)
  {
    $this->checkValidation = $checkValidation;
  }
  public function getCheckValidation()
  {
    return $this->checkValidation;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setCustomEventFilter($customEventFilter)
  {
    $this->customEventFilter = $customEventFilter;
  }
  public function getCustomEventFilter()
  {
    return $this->customEventFilter;
  }
  public function setEnableAllVideos(Google_Service_TagManager_Parameter $enableAllVideos)
  {
    $this->enableAllVideos = $enableAllVideos;
  }
  public function getEnableAllVideos()
  {
    return $this->enableAllVideos;
  }
  public function setEventName(Google_Service_TagManager_Parameter $eventName)
  {
    $this->eventName = $eventName;
  }
  public function getEventName()
  {
    return $this->eventName;
  }
  public function setFilter($filter)
  {
    $this->filter = $filter;
  }
  public function getFilter()
  {
    return $this->filter;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setInterval(Google_Service_TagManager_Parameter $interval)
  {
    $this->interval = $interval;
  }
  public function getInterval()
  {
    return $this->interval;
  }
  public function setLimit(Google_Service_TagManager_Parameter $limit)
  {
    $this->limit = $limit;
  }
  public function getLimit()
  {
    return $this->limit;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setParentFolderId($parentFolderId)
  {
    $this->parentFolderId = $parentFolderId;
  }
  public function getParentFolderId()
  {
    return $this->parentFolderId;
  }
  public function setTriggerId($triggerId)
  {
    $this->triggerId = $triggerId;
  }
  public function getTriggerId()
  {
    return $this->triggerId;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUniqueTriggerId(Google_Service_TagManager_Parameter $uniqueTriggerId)
  {
    $this->uniqueTriggerId = $uniqueTriggerId;
  }
  public function getUniqueTriggerId()
  {
    return $this->uniqueTriggerId;
  }
  public function setVideoPercentageList(Google_Service_TagManager_Parameter $videoPercentageList)
  {
    $this->videoPercentageList = $videoPercentageList;
  }
  public function getVideoPercentageList()
  {
    return $this->videoPercentageList;
  }
  public function setWaitForTags(Google_Service_TagManager_Parameter $waitForTags)
  {
    $this->waitForTags = $waitForTags;
  }
  public function getWaitForTags()
  {
    return $this->waitForTags;
  }
  public function setWaitForTagsTimeout(Google_Service_TagManager_Parameter $waitForTagsTimeout)
  {
    $this->waitForTagsTimeout = $waitForTagsTimeout;
  }
  public function getWaitForTagsTimeout()
  {
    return $this->waitForTagsTimeout;
  }
}

class Google_Service_TagManager_UserAccess extends Google_Collection
{
  protected $collection_key = 'containerAccess';
  protected $internal_gapi_mappings = array(
  );
  protected $accountAccessType = 'Google_Service_TagManager_AccountAccess';
  protected $accountAccessDataType = '';
  public $accountId;
  protected $containerAccessType = 'Google_Service_TagManager_ContainerAccess';
  protected $containerAccessDataType = 'array';
  public $emailAddress;
  public $permissionId;


  public function setAccountAccess(Google_Service_TagManager_AccountAccess $accountAccess)
  {
    $this->accountAccess = $accountAccess;
  }
  public function getAccountAccess()
  {
    return $this->accountAccess;
  }
  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainerAccess($containerAccess)
  {
    $this->containerAccess = $containerAccess;
  }
  public function getContainerAccess()
  {
    return $this->containerAccess;
  }
  public function setEmailAddress($emailAddress)
  {
    $this->emailAddress = $emailAddress;
  }
  public function getEmailAddress()
  {
    return $this->emailAddress;
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

class Google_Service_TagManager_Variable extends Google_Collection
{
  protected $collection_key = 'parameter';
  protected $internal_gapi_mappings = array(
  );
  public $accountId;
  public $containerId;
  public $disablingTriggerId;
  public $enablingTriggerId;
  public $fingerprint;
  public $name;
  public $notes;
  protected $parameterType = 'Google_Service_TagManager_Parameter';
  protected $parameterDataType = 'array';
  public $parentFolderId;
  public $scheduleEndMs;
  public $scheduleStartMs;
  public $type;
  public $variableId;


  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setDisablingTriggerId($disablingTriggerId)
  {
    $this->disablingTriggerId = $disablingTriggerId;
  }
  public function getDisablingTriggerId()
  {
    return $this->disablingTriggerId;
  }
  public function setEnablingTriggerId($enablingTriggerId)
  {
    $this->enablingTriggerId = $enablingTriggerId;
  }
  public function getEnablingTriggerId()
  {
    return $this->enablingTriggerId;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setParameter($parameter)
  {
    $this->parameter = $parameter;
  }
  public function getParameter()
  {
    return $this->parameter;
  }
  public function setParentFolderId($parentFolderId)
  {
    $this->parentFolderId = $parentFolderId;
  }
  public function getParentFolderId()
  {
    return $this->parentFolderId;
  }
  public function setScheduleEndMs($scheduleEndMs)
  {
    $this->scheduleEndMs = $scheduleEndMs;
  }
  public function getScheduleEndMs()
  {
    return $this->scheduleEndMs;
  }
  public function setScheduleStartMs($scheduleStartMs)
  {
    $this->scheduleStartMs = $scheduleStartMs;
  }
  public function getScheduleStartMs()
  {
    return $this->scheduleStartMs;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setVariableId($variableId)
  {
    $this->variableId = $variableId;
  }
  public function getVariableId()
  {
    return $this->variableId;
  }
}
