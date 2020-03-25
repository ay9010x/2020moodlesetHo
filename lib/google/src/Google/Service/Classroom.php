<?php



class Google_Service_Classroom extends Google_Service
{
  
  const CLASSROOM_COURSES =
      "https://www.googleapis.com/auth/classroom.courses";
  
  const CLASSROOM_COURSES_READONLY =
      "https://www.googleapis.com/auth/classroom.courses.readonly";
  
  const CLASSROOM_PROFILE_EMAILS =
      "https://www.googleapis.com/auth/classroom.profile.emails";
  
  const CLASSROOM_PROFILE_PHOTOS =
      "https://www.googleapis.com/auth/classroom.profile.photos";
  
  const CLASSROOM_ROSTERS =
      "https://www.googleapis.com/auth/classroom.rosters";
  
  const CLASSROOM_ROSTERS_READONLY =
      "https://www.googleapis.com/auth/classroom.rosters.readonly";

  public $courses;
  public $courses_aliases;
  public $courses_students;
  public $courses_teachers;
  public $invitations;
  public $userProfiles;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://classroom.googleapis.com/';
    $this->servicePath = '';
    $this->version = 'v1';
    $this->serviceName = 'classroom';

    $this->courses = new Google_Service_Classroom_Courses_Resource(
        $this,
        $this->serviceName,
        'courses',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/courses',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'delete' => array(
              'path' => 'v1/courses/{id}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/courses/{id}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/courses',
              'httpMethod' => 'GET',
              'parameters' => array(
                'teacherId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'studentId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'v1/courses/{id}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'updateMask' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'v1/courses/{id}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->courses_aliases = new Google_Service_Classroom_CoursesAliases_Resource(
        $this,
        $this->serviceName,
        'aliases',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/courses/{courseId}/aliases',
              'httpMethod' => 'POST',
              'parameters' => array(
                'courseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/courses/{courseId}/aliases/{alias}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'courseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'alias' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/courses/{courseId}/aliases',
              'httpMethod' => 'GET',
              'parameters' => array(
                'courseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
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
            ),
          )
        )
    );
    $this->courses_students = new Google_Service_Classroom_CoursesStudents_Resource(
        $this,
        $this->serviceName,
        'students',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/courses/{courseId}/students',
              'httpMethod' => 'POST',
              'parameters' => array(
                'courseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'enrollmentCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/courses/{courseId}/students/{userId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'courseId' => array(
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
              'path' => 'v1/courses/{courseId}/students/{userId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'courseId' => array(
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
              'path' => 'v1/courses/{courseId}/students',
              'httpMethod' => 'GET',
              'parameters' => array(
                'courseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
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
            ),
          )
        )
    );
    $this->courses_teachers = new Google_Service_Classroom_CoursesTeachers_Resource(
        $this,
        $this->serviceName,
        'teachers',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/courses/{courseId}/teachers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'courseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/courses/{courseId}/teachers/{userId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'courseId' => array(
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
              'path' => 'v1/courses/{courseId}/teachers/{userId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'courseId' => array(
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
              'path' => 'v1/courses/{courseId}/teachers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'courseId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
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
            ),
          )
        )
    );
    $this->invitations = new Google_Service_Classroom_Invitations_Resource(
        $this,
        $this->serviceName,
        'invitations',
        array(
          'methods' => array(
            'accept' => array(
              'path' => 'v1/invitations/{id}:accept',
              'httpMethod' => 'POST',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'create' => array(
              'path' => 'v1/invitations',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'delete' => array(
              'path' => 'v1/invitations/{id}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/invitations/{id}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/invitations',
              'httpMethod' => 'GET',
              'parameters' => array(
                'courseId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'userId' => array(
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
    $this->userProfiles = new Google_Service_Classroom_UserProfiles_Resource(
        $this,
        $this->serviceName,
        'userProfiles',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/userProfiles/{userId}',
              'httpMethod' => 'GET',
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
  }
}



class Google_Service_Classroom_Courses_Resource extends Google_Service_Resource
{

  
  public function create(Google_Service_Classroom_Course $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Classroom_Course");
  }

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Classroom_Empty");
  }

  
  public function get($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Classroom_Course");
  }

  
  public function listCourses($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Classroom_ListCoursesResponse");
  }

  
  public function patch($id, Google_Service_Classroom_Course $postBody, $optParams = array())
  {
    $params = array('id' => $id, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Classroom_Course");
  }

  
  public function update($id, Google_Service_Classroom_Course $postBody, $optParams = array())
  {
    $params = array('id' => $id, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Classroom_Course");
  }
}


class Google_Service_Classroom_CoursesAliases_Resource extends Google_Service_Resource
{

  
  public function create($courseId, Google_Service_Classroom_CourseAlias $postBody, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Classroom_CourseAlias");
  }

  
  public function delete($courseId, $alias, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'alias' => $alias);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Classroom_Empty");
  }

  
  public function listCoursesAliases($courseId, $optParams = array())
  {
    $params = array('courseId' => $courseId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Classroom_ListCourseAliasesResponse");
  }
}

class Google_Service_Classroom_CoursesStudents_Resource extends Google_Service_Resource
{

  
  public function create($courseId, Google_Service_Classroom_Student $postBody, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Classroom_Student");
  }

  
  public function delete($courseId, $userId, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Classroom_Empty");
  }

  
  public function get($courseId, $userId, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Classroom_Student");
  }

  
  public function listCoursesStudents($courseId, $optParams = array())
  {
    $params = array('courseId' => $courseId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Classroom_ListStudentsResponse");
  }
}

class Google_Service_Classroom_CoursesTeachers_Resource extends Google_Service_Resource
{

  
  public function create($courseId, Google_Service_Classroom_Teacher $postBody, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Classroom_Teacher");
  }

  
  public function delete($courseId, $userId, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Classroom_Empty");
  }

  
  public function get($courseId, $userId, $optParams = array())
  {
    $params = array('courseId' => $courseId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Classroom_Teacher");
  }

  
  public function listCoursesTeachers($courseId, $optParams = array())
  {
    $params = array('courseId' => $courseId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Classroom_ListTeachersResponse");
  }
}


class Google_Service_Classroom_Invitations_Resource extends Google_Service_Resource
{

  
  public function accept($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('accept', array($params), "Google_Service_Classroom_Empty");
  }

  
  public function create(Google_Service_Classroom_Invitation $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Classroom_Invitation");
  }

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Classroom_Empty");
  }

  
  public function get($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Classroom_Invitation");
  }

  
  public function listInvitations($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Classroom_ListInvitationsResponse");
  }
}


class Google_Service_Classroom_UserProfiles_Resource extends Google_Service_Resource
{

  
  public function get($userId, $optParams = array())
  {
    $params = array('userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Classroom_UserProfile");
  }
}




class Google_Service_Classroom_Course extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $alternateLink;
  public $courseState;
  public $creationTime;
  public $description;
  public $descriptionHeading;
  public $enrollmentCode;
  public $id;
  public $name;
  public $ownerId;
  public $room;
  public $section;
  public $updateTime;


  public function setAlternateLink($alternateLink)
  {
    $this->alternateLink = $alternateLink;
  }
  public function getAlternateLink()
  {
    return $this->alternateLink;
  }
  public function setCourseState($courseState)
  {
    $this->courseState = $courseState;
  }
  public function getCourseState()
  {
    return $this->courseState;
  }
  public function setCreationTime($creationTime)
  {
    $this->creationTime = $creationTime;
  }
  public function getCreationTime()
  {
    return $this->creationTime;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setDescriptionHeading($descriptionHeading)
  {
    $this->descriptionHeading = $descriptionHeading;
  }
  public function getDescriptionHeading()
  {
    return $this->descriptionHeading;
  }
  public function setEnrollmentCode($enrollmentCode)
  {
    $this->enrollmentCode = $enrollmentCode;
  }
  public function getEnrollmentCode()
  {
    return $this->enrollmentCode;
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
  public function setOwnerId($ownerId)
  {
    $this->ownerId = $ownerId;
  }
  public function getOwnerId()
  {
    return $this->ownerId;
  }
  public function setRoom($room)
  {
    $this->room = $room;
  }
  public function getRoom()
  {
    return $this->room;
  }
  public function setSection($section)
  {
    $this->section = $section;
  }
  public function getSection()
  {
    return $this->section;
  }
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

class Google_Service_Classroom_CourseAlias extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $alias;


  public function setAlias($alias)
  {
    $this->alias = $alias;
  }
  public function getAlias()
  {
    return $this->alias;
  }
}

class Google_Service_Classroom_Empty extends Google_Model
{
}

class Google_Service_Classroom_GlobalPermission extends Google_Model
{
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

class Google_Service_Classroom_Invitation extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $courseId;
  public $id;
  public $role;
  public $userId;


  public function setCourseId($courseId)
  {
    $this->courseId = $courseId;
  }
  public function getCourseId()
  {
    return $this->courseId;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setRole($role)
  {
    $this->role = $role;
  }
  public function getRole()
  {
    return $this->role;
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

class Google_Service_Classroom_ListCourseAliasesResponse extends Google_Collection
{
  protected $collection_key = 'aliases';
  protected $internal_gapi_mappings = array(
  );
  protected $aliasesType = 'Google_Service_Classroom_CourseAlias';
  protected $aliasesDataType = 'array';
  public $nextPageToken;


  public function setAliases($aliases)
  {
    $this->aliases = $aliases;
  }
  public function getAliases()
  {
    return $this->aliases;
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

class Google_Service_Classroom_ListCoursesResponse extends Google_Collection
{
  protected $collection_key = 'courses';
  protected $internal_gapi_mappings = array(
  );
  protected $coursesType = 'Google_Service_Classroom_Course';
  protected $coursesDataType = 'array';
  public $nextPageToken;


  public function setCourses($courses)
  {
    $this->courses = $courses;
  }
  public function getCourses()
  {
    return $this->courses;
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

class Google_Service_Classroom_ListInvitationsResponse extends Google_Collection
{
  protected $collection_key = 'invitations';
  protected $internal_gapi_mappings = array(
  );
  protected $invitationsType = 'Google_Service_Classroom_Invitation';
  protected $invitationsDataType = 'array';
  public $nextPageToken;


  public function setInvitations($invitations)
  {
    $this->invitations = $invitations;
  }
  public function getInvitations()
  {
    return $this->invitations;
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

class Google_Service_Classroom_ListStudentsResponse extends Google_Collection
{
  protected $collection_key = 'students';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $studentsType = 'Google_Service_Classroom_Student';
  protected $studentsDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setStudents($students)
  {
    $this->students = $students;
  }
  public function getStudents()
  {
    return $this->students;
  }
}

class Google_Service_Classroom_ListTeachersResponse extends Google_Collection
{
  protected $collection_key = 'teachers';
  protected $internal_gapi_mappings = array(
  );
  public $nextPageToken;
  protected $teachersType = 'Google_Service_Classroom_Teacher';
  protected $teachersDataType = 'array';


  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
  public function setTeachers($teachers)
  {
    $this->teachers = $teachers;
  }
  public function getTeachers()
  {
    return $this->teachers;
  }
}

class Google_Service_Classroom_Name extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $familyName;
  public $fullName;
  public $givenName;


  public function setFamilyName($familyName)
  {
    $this->familyName = $familyName;
  }
  public function getFamilyName()
  {
    return $this->familyName;
  }
  public function setFullName($fullName)
  {
    $this->fullName = $fullName;
  }
  public function getFullName()
  {
    return $this->fullName;
  }
  public function setGivenName($givenName)
  {
    $this->givenName = $givenName;
  }
  public function getGivenName()
  {
    return $this->givenName;
  }
}

class Google_Service_Classroom_Student extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $courseId;
  protected $profileType = 'Google_Service_Classroom_UserProfile';
  protected $profileDataType = '';
  public $userId;


  public function setCourseId($courseId)
  {
    $this->courseId = $courseId;
  }
  public function getCourseId()
  {
    return $this->courseId;
  }
  public function setProfile(Google_Service_Classroom_UserProfile $profile)
  {
    $this->profile = $profile;
  }
  public function getProfile()
  {
    return $this->profile;
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

class Google_Service_Classroom_Teacher extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $courseId;
  protected $profileType = 'Google_Service_Classroom_UserProfile';
  protected $profileDataType = '';
  public $userId;


  public function setCourseId($courseId)
  {
    $this->courseId = $courseId;
  }
  public function getCourseId()
  {
    return $this->courseId;
  }
  public function setProfile(Google_Service_Classroom_UserProfile $profile)
  {
    $this->profile = $profile;
  }
  public function getProfile()
  {
    return $this->profile;
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

class Google_Service_Classroom_UserProfile extends Google_Collection
{
  protected $collection_key = 'permissions';
  protected $internal_gapi_mappings = array(
  );
  public $emailAddress;
  public $id;
  protected $nameType = 'Google_Service_Classroom_Name';
  protected $nameDataType = '';
  protected $permissionsType = 'Google_Service_Classroom_GlobalPermission';
  protected $permissionsDataType = 'array';
  public $photoUrl;


  public function setEmailAddress($emailAddress)
  {
    $this->emailAddress = $emailAddress;
  }
  public function getEmailAddress()
  {
    return $this->emailAddress;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setName(Google_Service_Classroom_Name $name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setPermissions($permissions)
  {
    $this->permissions = $permissions;
  }
  public function getPermissions()
  {
    return $this->permissions;
  }
  public function setPhotoUrl($photoUrl)
  {
    $this->photoUrl = $photoUrl;
  }
  public function getPhotoUrl()
  {
    return $this->photoUrl;
  }
}
