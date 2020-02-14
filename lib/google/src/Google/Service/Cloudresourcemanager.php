<?php



class Google_Service_Cloudresourcemanager extends Google_Service
{
  
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";

  public $organizations;
  public $projects;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://cloudresourcemanager.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1beta1';
    $this->serviceName = 'cloudresourcemanager';

    $this->organizations = new Google_Service_Cloudresourcemanager_Organizations_Resource(
        $this,
        $this->serviceName,
        'organizations',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1beta1/organizations/{organizationId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'organizationId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getIamPolicy' => array(
              'path' => 'v1beta1/organizations/{resource}:getIamPolicy',
              'httpMethod' => 'POST',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1beta1/organizations',
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
            ),'setIamPolicy' => array(
              'path' => 'v1beta1/organizations/{resource}:setIamPolicy',
              'httpMethod' => 'POST',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'testIamPermissions' => array(
              'path' => 'v1beta1/organizations/{resource}:testIamPermissions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'v1beta1/organizations/{organizationId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'organizationId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->projects = new Google_Service_Cloudresourcemanager_Projects_Resource(
        $this,
        $this->serviceName,
        'projects',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1beta1/projects',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'delete' => array(
              'path' => 'v1beta1/projects/{projectId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'projectId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1beta1/projects/{projectId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'projectId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getIamPolicy' => array(
              'path' => 'v1beta1/projects/{resource}:getIamPolicy',
              'httpMethod' => 'POST',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1beta1/projects',
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
            ),'setIamPolicy' => array(
              'path' => 'v1beta1/projects/{resource}:setIamPolicy',
              'httpMethod' => 'POST',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'testIamPermissions' => array(
              'path' => 'v1beta1/projects/{resource}:testIamPermissions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'undelete' => array(
              'path' => 'v1beta1/projects/{projectId}:undelete',
              'httpMethod' => 'POST',
              'parameters' => array(
                'projectId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'v1beta1/projects/{projectId}',
              'httpMethod' => 'PUT',
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
  }
}



class Google_Service_Cloudresourcemanager_Organizations_Resource extends Google_Service_Resource
{

  
  public function get($organizationId, $optParams = array())
  {
    $params = array('organizationId' => $organizationId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Cloudresourcemanager_Organization");
  }

  
  public function getIamPolicy($resource, Google_Service_Cloudresourcemanager_GetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('getIamPolicy', array($params), "Google_Service_Cloudresourcemanager_Policy");
  }

  
  public function listOrganizations($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Cloudresourcemanager_ListOrganizationsResponse");
  }

  
  public function setIamPolicy($resource, Google_Service_Cloudresourcemanager_SetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setIamPolicy', array($params), "Google_Service_Cloudresourcemanager_Policy");
  }

  
  public function testIamPermissions($resource, Google_Service_Cloudresourcemanager_TestIamPermissionsRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('testIamPermissions', array($params), "Google_Service_Cloudresourcemanager_TestIamPermissionsResponse");
  }

  
  public function update($organizationId, Google_Service_Cloudresourcemanager_Organization $postBody, $optParams = array())
  {
    $params = array('organizationId' => $organizationId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Cloudresourcemanager_Organization");
  }
}


class Google_Service_Cloudresourcemanager_Projects_Resource extends Google_Service_Resource
{

  
  public function create(Google_Service_Cloudresourcemanager_Project $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Cloudresourcemanager_Project");
  }

  
  public function delete($projectId, $optParams = array())
  {
    $params = array('projectId' => $projectId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Cloudresourcemanager_Empty");
  }

  
  public function get($projectId, $optParams = array())
  {
    $params = array('projectId' => $projectId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Cloudresourcemanager_Project");
  }

  
  public function getIamPolicy($resource, Google_Service_Cloudresourcemanager_GetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('getIamPolicy', array($params), "Google_Service_Cloudresourcemanager_Policy");
  }

  
  public function listProjects($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Cloudresourcemanager_ListProjectsResponse");
  }

  
  public function setIamPolicy($resource, Google_Service_Cloudresourcemanager_SetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setIamPolicy', array($params), "Google_Service_Cloudresourcemanager_Policy");
  }

  
  public function testIamPermissions($resource, Google_Service_Cloudresourcemanager_TestIamPermissionsRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('testIamPermissions', array($params), "Google_Service_Cloudresourcemanager_TestIamPermissionsResponse");
  }

  
  public function undelete($projectId, $optParams = array())
  {
    $params = array('projectId' => $projectId);
    $params = array_merge($params, $optParams);
    return $this->call('undelete', array($params), "Google_Service_Cloudresourcemanager_Empty");
  }

  
  public function update($projectId, Google_Service_Cloudresourcemanager_Project $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Cloudresourcemanager_Project");
  }
}




class Google_Service_Cloudresourcemanager_Binding extends Google_Collection
{
  protected $collection_key = 'members';
  protected $internal_gapi_mappings = array(
  );
  public $members;
  public $role;


  public function setMembers($members)
  {
    $this->members = $members;
  }
  public function getMembers()
  {
    return $this->members;
  }
  public function setRole($role)
  {
    $this->role = $role;
  }
  public function getRole()
  {
    return $this->role;
  }
}

class Google_Service_Cloudresourcemanager_Empty extends Google_Model
{
}

class Google_Service_Cloudresourcemanager_GetIamPolicyRequest extends Google_Model
{
}

class Google_Service_Cloudresourcemanager_ListOrganizationsResponse extends Google_Collection
{
  protected $collection_key = 'organizations';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $organizationsType = 'Google_Service_Cloudresourcemanager_Organization';
  protected $organizationsDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setOrganizations($organizations)
  {
    $this->organizations = $organizations;
  }
  public function getOrganizations()
  {
    return $this->organizations;
  }
}

class Google_Service_Cloudresourcemanager_ListProjectsResponse extends Google_Collection
{
  protected $collection_key = 'projects';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $projectsType = 'Google_Service_Cloudresourcemanager_Project';
  protected $projectsDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setProjects($projects)
  {
    $this->projects = $projects;
  }
  public function getProjects()
  {
    return $this->projects;
  }
}

class Google_Service_Cloudresourcemanager_Organization extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $displayName;
  public $organizationId;
  protected $ownerType = 'Google_Service_Cloudresourcemanager_OrganizationOwner';
  protected $ownerDataType = '';


  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setOrganizationId($organizationId)
  {
    $this->organizationId = $organizationId;
  }
  public function getOrganizationId()
  {
    return $this->organizationId;
  }
  public function setOwner(Google_Service_Cloudresourcemanager_OrganizationOwner $owner)
  {
    $this->owner = $owner;
  }
  public function getOwner()
  {
    return $this->owner;
  }
}

class Google_Service_Cloudresourcemanager_OrganizationOwner extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $directoryCustomerId;


  public function setDirectoryCustomerId($directoryCustomerId)
  {
    $this->directoryCustomerId = $directoryCustomerId;
  }
  public function getDirectoryCustomerId()
  {
    return $this->directoryCustomerId;
  }
}

class Google_Service_Cloudresourcemanager_Policy extends Google_Collection
{
  protected $collection_key = 'bindings';
  protected $internal_gapi_mappings = array(
  );
  protected $bindingsType = 'Google_Service_Cloudresourcemanager_Binding';
  protected $bindingsDataType = 'array';
  public $etag;
  public $version;


  public function setBindings($bindings)
  {
    $this->bindings = $bindings;
  }
  public function getBindings()
  {
    return $this->bindings;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
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

class Google_Service_Cloudresourcemanager_Project extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $createTime;
  public $labels;
  public $lifecycleState;
  public $name;
  protected $parentType = 'Google_Service_Cloudresourcemanager_ResourceId';
  protected $parentDataType = '';
  public $projectId;
  public $projectNumber;


  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  public function getCreateTime()
  {
    return $this->createTime;
  }
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  public function getLabels()
  {
    return $this->labels;
  }
  public function setLifecycleState($lifecycleState)
  {
    $this->lifecycleState = $lifecycleState;
  }
  public function getLifecycleState()
  {
    return $this->lifecycleState;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setParent(Google_Service_Cloudresourcemanager_ResourceId $parent)
  {
    $this->parent = $parent;
  }
  public function getParent()
  {
    return $this->parent;
  }
  public function setProjectId($projectId)
  {
    $this->projectId = $projectId;
  }
  public function getProjectId()
  {
    return $this->projectId;
  }
  public function setProjectNumber($projectNumber)
  {
    $this->projectNumber = $projectNumber;
  }
  public function getProjectNumber()
  {
    return $this->projectNumber;
  }
}

class Google_Service_Cloudresourcemanager_ProjectLabels extends Google_Model
{
}

class Google_Service_Cloudresourcemanager_ResourceId extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $type;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
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

class Google_Service_Cloudresourcemanager_SetIamPolicyRequest extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $policyType = 'Google_Service_Cloudresourcemanager_Policy';
  protected $policyDataType = '';


  public function setPolicy(Google_Service_Cloudresourcemanager_Policy $policy)
  {
    $this->policy = $policy;
  }
  public function getPolicy()
  {
    return $this->policy;
  }
}

class Google_Service_Cloudresourcemanager_TestIamPermissionsRequest extends Google_Collection
{
  protected $collection_key = 'permissions';
  protected $internal_gapi_mappings = array(
  );
  public $permissions;


  public function setPermissions($permissions)
  {
    $this->permissions = $permissions;
  }
  public function getPermissions()
  {
    return $this->permissions;
  }
}

class Google_Service_Cloudresourcemanager_TestIamPermissionsResponse extends Google_Collection
{
  protected $collection_key = 'permissions';
  protected $internal_gapi_mappings = array(
  );
  public $permissions;


  public function setPermissions($permissions)
  {
    $this->permissions = $permissions;
  }
  public function getPermissions()
  {
    return $this->permissions;
  }
}
