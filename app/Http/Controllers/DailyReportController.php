<?php

namespace Jitamin\Http\Controllers;
use PDO;

class DailyReportController extends Controller
{
    public function index()
    {
        $date = $this->request->getStringParam('date');
        if (!preg_match('/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/',$date)) {
            $this->response->html('Error!');
            exit();
        }
        $curtime = strtotime($date,time());
        if (empty($curtime)) {
            $this->response->html('Error!');
            exit();
        }
        $echoval = array();
        $tempreport = $this->db->execute("
                        SELECT
                            *
                        FROM project_daily_user
                        WHERE date_num={$curtime}
                    ")->fetchAll(PDO::FETCH_ASSOC);
        $tempuids = $temptasks = $tempfiles = $tempcomments = array();
        foreach ($tempreport as $tempvalue) {
            $tempvalue['task_info'] = \json_decode($tempvalue['task_info'], true);
            $tempvalue['task_count'] = \json_decode($tempvalue['task_count'], true);
            $echoval['datalist'][] = $tempvalue;
            $tempuids[] = $tempvalue['user_id'];
            foreach ($tempvalue['task_info'] as $tempvalue2) {
                $temptasks[] = $tempvalue2['task_id'];
                if (!empty($tempvalue2['files'])) {
                    $tempfiles += $tempvalue2['files'];
                }
                if (!empty($tempvalue2['comments'])) {
                    $tempcomments += $tempvalue2['comments'];
                }
            }
        }
        if (empty($echoval['datalist'])) {
            $this->response->html('No data.');
            exit();
        }
        if (!empty($tempuids)) {
            $tempdata = $this->db->execute("
                        SELECT
                            id, username, name, email, role
                        FROM users
                        WHERE id IN (".\implode(',',$tempuids).")
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['users'] = array_column($tempdata, null, 'id');
            }
        }
        if (!empty($temptasks)) {
            $tempdata = $this->db->execute("
                        SELECT
                            id, title, project_id
                        FROM tasks
                        WHERE id IN (".\implode(',',$temptasks).")
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['tasks'] = array_column($tempdata, null, 'id');
                $tempdata1 = $this->db->execute("
                        SELECT
                            id, status, title, task_id, user_id
                        FROM subtasks
                        WHERE task_id IN (".\implode(',',$temptasks).")
                        ORDER BY id ASC
                    ")->fetchAll(PDO::FETCH_ASSOC);
                $echoval['subtasks'] = array();
                foreach ($tempdata1 as $subvalue) {
                    if (!isset($echoval['subtasks'][$subvalue['task_id']])) {
                        $echoval['subtasks'][$subvalue['task_id']] = array();
                    }
                    $echoval['subtasks'][$subvalue['task_id']][$subvalue['id']] = $subvalue;
                }
            }
        }
        if (!empty($tempfiles)) {
            $tempdata = $this->db->execute("
                        SELECT
                            id, name, path, is_image
                        FROM task_has_files
                        WHERE id IN (".\implode(',',$tempfiles).")
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['files'] = array_column($tempdata, null, 'id');
            }
        }
        if (!empty($tempcomments)) {
            $tempdata = $this->db->execute("
                        SELECT
                            id, comment
                        FROM comments
                        WHERE id IN (".\implode(',',$tempcomments).")
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['comments'] = array_column($tempdata, null, 'id');
            }
        }
        //print_r($echoval);exit();
        $this->response->html($this->template->render('extends/daily', ['date'=>$date, 'echoval'=>$echoval]));
    }

    public function user()
    {
        $userid = $this->request->getIntegerParam('userid');
        $this->response->html('ok '.$userid);
    }

    public function month()
    {
        $month = $this->request->getStringParam('month');
        $this->response->html('ok '.$month);
    }
}
