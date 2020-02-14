<?php
// Reset the 'url' information in the field "plugin=block_course_menu" in the table "mdl_config_plugins" of database

define('CLI_SCRIPT', true);

require_once('config.php');
require_once($CFG->libdir.'/clilib.php');

    $i = 1;
    while($i<=24){
        $teacher = array();
        $name = 'tmenu'.$i;
        
        $teacher['anchor'] = '';
        switch($i){
            case 1:
                $teacher['name'] = '課程資訊';
                $teacher['ename'] = 'Information';
                $teacher['parent'] = 0;
                $teacher['url'] = '';
                $teacher['params'] = '';
                $teacher['icon'] = 'fa-list';
                $teacher['color'] = '#083f8e';
                break;
            case 2:
                $teacher['name'] = '章節綱要';
                $teacher['ename'] = 'Outline';
                $teacher['parent'] = '1';
                $teacher['url'] = '/blocks/course_menu/information.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 3:
                $teacher['name'] = '預定進度';
                $teacher['ename'] = 'Week Syllabus';
                $teacher['parent'] = '1';
                $teacher['url'] = '/local/syllabus_week/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 4:
                $teacher['name'] = '時數分配表';
                $teacher['ename'] = 'Allocation';
                $teacher['parent'] = '1';
                $teacher['url'] = '/local/syllabus_timeline/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 5:
                $teacher['name'] = '參考書目';
                $teacher['ename'] = 'Bibliography';
                $teacher['parent'] = '1';
                $teacher['url'] = '/blocks/course_menu/information.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                $teacher['anchor'] = '#ccbible';     // by YCJ
                break;
            case 6:
                $teacher['name'] = '常見問答集';
                $teacher['ename'] = 'Q&A';
                $teacher['parent'] = '1';
                $teacher['url'] = '/blocks/course_menu/information.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                $teacher['anchor'] = '#ccqna';       // by YCJ
                break;
            case 7:
                $teacher['name'] = '內容管理';
                $teacher['ename'] = 'Content';
                $teacher['parent'] = 0;
                $teacher['url'] = '';
                $teacher['params'] = '';
                $teacher['icon'] = 'fa-book';
                $teacher['color'] = '#083f8e';                
                break;
            case 8:
                $teacher['name'] = '課程地圖';
                $teacher['ename'] = 'Index';
                $teacher['parent'] = '7';
                $teacher['url'] = '/course/view.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 9:
                $teacher['name'] = '公佈欄';
                $teacher['ename'] = 'News';
                $teacher['parent'] = '7';
                $teacher['url'] = '/blocks/course_menu/news.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 10:
                $teacher['name'] = '討論區';
                $teacher['ename'] = 'Forums';
                $teacher['parent'] = '7';
                $teacher['url'] = '/blocks/course_menu/format/forum/view.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 11:
                $teacher['name'] = '通知信';
                $teacher['ename'] = 'Message';
                $teacher['parent'] = '7';
                $teacher['url'] = '/user/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 12:
                $teacher['name'] = '題庫維護';
                $teacher['ename'] = 'QuestionBank';
                $teacher['parent'] = '7';
                $teacher['url'] = '/question/edit.php';
                $teacher['params'] = 'courseid';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 13:
                $teacher['name'] = '學習管理';
                $teacher['ename'] = 'Management';
                $teacher['parent'] = 0;
                $teacher['url'] = '';
                $teacher['params'] = '';
                $teacher['icon'] = 'fa-pencil-square-o';
                $teacher['color'] = '#083f8e';
                break;
            case 14:
                $teacher['name'] = '成員管理';
                $teacher['ename'] = 'Members';
                $teacher['parent'] = '13';
                $teacher['url'] = '/enrol/users.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 15:
                $teacher['name'] = '作業管理';
                $teacher['ename'] = 'Assign';
                $teacher['parent'] = '13';
                $teacher['url'] = '/blocks/course_menu/format/assign/view.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 16:
                $teacher['name'] = '出席管理';
                $teacher['ename'] = 'Attendance';
                $teacher['parent'] = '13';
                $teacher['url'] = '/blocks/course_menu/attendance.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 17:
                $teacher['name'] = '成績檢視';
                $teacher['ename'] = 'GradeBook';
                $teacher['parent'] = '13';
                $teacher['url'] = '/grade/report/grader/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 18:
                $teacher['name'] = '成績計分設定';
                $teacher['ename'] = 'Grade Setup';
                $teacher['parent'] = '13';
                $teacher['url'] = '/grade/edit/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 19:
                $teacher['name'] = '報表管理';
                $teacher['ename'] = 'Report';
                $teacher['parent'] = 0;
                $teacher['url'] = '';
                $teacher['params'] = '';
                $teacher['icon'] = 'fa-file-text';
                $teacher['color'] = '#083f8e';
                break;
            case 20:
                $teacher['name'] = '學習歷程報表';
                $teacher['ename'] = 'Real-time Report';
                $teacher['parent'] = '19';
                $teacher['url'] = '/report/outline/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 21:
                $teacher['name'] = '進入課程報表';
                $teacher['ename'] = 'Land View';
                $teacher['parent'] = '19';
                $teacher['url'] = '/report/landview/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 22:
                $teacher['name'] = '教材瀏覽報表';
                $teacher['ename'] = 'Material View';
                $teacher['parent'] = '19';
                $teacher['url'] = '/report/materialview/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 23:
                $teacher['name'] = '測驗及填答報表';
                $teacher['ename'] = 'Quiz View';
                $teacher['parent'] = '19';
                $teacher['url'] = '/report/quizview/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            case 24:
                $teacher['name'] = '討論區報表';
                $teacher['ename'] = 'Forum View';
                $teacher['parent'] = '19';
                $teacher['url'] = '/report/forumview/index.php';
                $teacher['params'] = 'id';
                $teacher['paramsvalue'] = '0';
                $teacher['icon'] = '';
                $teacher['color'] = '#5096FB';
                break;
            default :
                break;
        }
        $teacher['id'] = $i;
        $teacher['sortorder'] = $i;
        $teacher['visible'] = 1;        
        
        $serialize = serialize($teacher);
        set_config($name, $serialize, 'block_course_menu');
        $i++;
    }
    
    $i = 1;
    while($i<=14){
        $student = array();
        $name = 'smenu'.$i;
        
        $student['anchor'] = '';
        switch($i){
            case 1:
                $student['name'] = '課程資訊';
                $student['ename'] = 'Information';
                $student['parent'] = 0;
                $student['url'] = '';
                $student['params'] = '';
                $student['icon'] = 'fa-list';
                $student['color'] = '#cc6c95';
                break;
            case 2:
                $student['name'] = '章節綱要';
                $student['ename'] = 'Outline';
                $student['parent'] = '1';
                $student['url'] = '/blocks/course_menu/information.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            case 3:
                $student['name'] = '預定進度';
                $student['ename'] = 'Week Syllabus';
                $student['parent'] = '1';
                $student['url'] = '/local/syllabus_week/index.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            case 4:
                $student['name'] = '時數分配表';
                $student['ename'] = 'Allocation';
                $student['parent'] = '1';
                $student['url'] = '/local/syllabus_timeline/index.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            case 5:
                $student['name'] = '參考書目';
                $student['ename'] = 'Bibliography';
                $student['parent'] = '1';
                $student['url'] = '/blocks/course_menu/information.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                $student['anchor'] = '#ccbible';     // by YCJ
                break;
            case 6:
                $student['name'] = '常見問答集';
                $student['ename'] = 'Q&A';
                $student['parent'] = '1';
                $student['url'] = '/blocks/course_menu/information.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                $student['anchor'] = '#ccqna';       // by YCJ
                break;
            case 7:
                $student['name'] = '內容管理';
                $student['ename'] = 'Content';
                $student['parent'] = 0;
                $student['url'] = '';
                $student['params'] = '';
                $student['icon'] = 'fa-book';
                $student['color'] = '#cc6c95';                
                break;
            case 8:
                $student['name'] = '課程地圖';
                $student['ename'] = 'Index';
                $student['parent'] = '7';
                $student['url'] = '/course/view.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            case 9:
                $student['name'] = '公佈欄';
                $student['ename'] = 'News';
                $student['parent'] = '7';
                $student['url'] = '/blocks/course_menu/news.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            case 10:
                $student['name'] = '討論區';
                $student['ename'] = 'Forums';
                $student['parent'] = '7';
                $student['url'] = '/blocks/course_menu/format/forum/view.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            case 11:
                $student['name'] = '通知信';
                $student['ename'] = 'Message';
                $student['parent'] = '7';
                $student['url'] = '/blocks/course_menu/message.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            case 12:
                $student['name'] = '學習管理';
                $student['ename'] = 'Management';
                $student['parent'] = 0;
                $student['url'] = '';
                $student['params'] = '';
                $student['icon'] = 'fa-pencil-square-o';
                $student['color'] = '#cc6c95';
        break;
            case 13:
                $student['name'] = '出席紀錄';
                $student['ename'] = 'Attendance';
                $student['parent'] = '12';
                $student['url'] = '/blocks/course_menu/attendance.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            case 14:
                $student['name'] = '成績檢視';
                $student['ename'] = 'GradeBook';
                $student['parent'] = '12';
                $student['url'] = '/grade/report/user/index.php';
                $student['params'] = 'id';
                $student['paramsvalue'] = '0';
                $student['icon'] = '';
                $student['color'] = '#f6c0d7';
                break;
            default :
                break;
        }
        $student['id'] = $i;
        $student['sortorder'] = $i;
        $student['visible'] = 1;        
        
        $serialize = serialize($student);
        set_config($name, $serialize, 'block_course_menu');
        $i++;
    }

exit(0);
