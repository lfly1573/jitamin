<?php

namespace Jitamin\Http\Controllers;

use PDO;

class DailyReportController extends Controller
{
    public function index()
    {
        $userinfo = $this->getUser();
        // print_r($userinfo);exit();
        $date = $this->request->getStringParam('date');
        $projectid = $this->request->getIntegerParam('projectid');
        if (!preg_match('/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $date)) {
            $this->response->html('Error!');
            exit();
        }
        $curtime = strtotime($date, time());
        if (empty($curtime)) {
            $this->response->html('Error!');
            exit();
        }
        $echoval = array();

        $tempdata = $this->db->execute("
                        SELECT
                            project_id
                            FROM project_has_users WHERE user_id={$userinfo['id']}
                    ")->fetchAll(PDO::FETCH_ASSOC);
        $myproject = !empty($tempdata) ? array_column($tempdata, 'project_id') : array();
        if (empty($myproject)) {
            $this->response->html('No data.');
            exit();
        }

        $echoval['project'] = $echoval['project_user'] = array();
        $tempdata = $this->db->execute("
                        SELECT
                            id, name
                        FROM projects WHERE is_active=1 AND id IN (".implode(',', $myproject).") ORDER BY last_modified DESC
                    ")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($tempdata)) {
            $echoval['project'] = array_column($tempdata, null, 'id');
        }
        if (empty($echoval['project'])) {
            $this->response->html('No data.');
            exit();
        }

        if ($projectid==0) {
            $tempdata = reset($myproject);
            $projectid = $tempdata['id'];
        }

        if ($projectid>0 && isset($echoval['project'][$projectid])) {
            $tempdata = $this->db->execute("
                        SELECT
                            user_id
                        FROM project_has_users WHERE project_id={$projectid}
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['project_user'] = array_column($tempdata, null, 'user_id');
            }
        }

        $tempreport = $this->db->execute("
                        SELECT
                            *
                        FROM project_daily_user
                        WHERE date_num={$curtime}
                    ")->fetchAll(PDO::FETCH_ASSOC);
        $tempuids = $temptasks = $tempfiles = $tempcomments = array();
        foreach ($tempreport as $tempvalue) {
            if ($projectid==0 || isset($echoval['project_user'][$tempvalue['user_id']])) {
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
        }
        /*
        if (empty($echoval['datalist'])) {
            $this->response->html('No data.');
            exit();
        }
        */
        if (!empty($tempuids)) {
            $tempdata = $this->db->execute("
                        SELECT
                            id, username, name, email, role
                        FROM users
                        WHERE id IN (" . \implode(',', $tempuids) . ")
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
                        WHERE id IN (" . \implode(',', $temptasks) . ")
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['tasks'] = array_column($tempdata, null, 'id');
                $tempdata1 = $this->db->execute("
                        SELECT
                            id, status, title, task_id, user_id
                        FROM subtasks
                        WHERE task_id IN (" . \implode(',', $temptasks) . ")
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
                        WHERE id IN (" . \implode(',', $tempfiles) . ")
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
                        WHERE id IN (" . \implode(',', $tempcomments) . ")
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['comments'] = array_column($tempdata, null, 'id');
            }
        }
        //print_r($echoval);exit();
        $this->response->html($this->template->render('extends/daily', ['date' => $date, 'projectid'=>$projectid, 'echoval' => $echoval]));
    }

    public function user()
    {
        global $config;
        $userid = $this->request->getIntegerParam('userid');
        $begininput = $this->request->getStringParam('begin');
        $endinput = $this->request->getStringParam('end');
        if ($userid <= 0 || (!empty($begininput) && !preg_match('/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $begininput)) || (!empty($endinput) && !preg_match('/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $endinput))) {
            $this->response->html('Error!');
            exit();
        }
        $curtimeinfo = explode('-', date('Y-n-j-N-t'));
        $begintime = $endtime = 0;
        if (!empty($begininput)) {
            $begintime = intval(strtotime($begininput));
        }
        if (!empty($endinput)) {
            $endtime = intval(strtotime($endinput));
        }
        if ($begintime == 0) {
            if ($curtimeinfo[2]==1) {
                if ($curtimeinfo[1]==1) {
                    $begintime = intval(strtotime(($curtimeinfo[0]-1)."-12-1"));
                } else {
                    $begintime = intval(strtotime("{$curtimeinfo[0]}-".($curtimeinfo[1]-1)."-1"));
                }
            } else {
                $begintime = intval(strtotime("{$curtimeinfo[0]}-{$curtimeinfo[1]}-1"));
            }
        }
        if ($endtime == 0) {
            $endtime = intval(strtotime("{$curtimeinfo[0]}-{$curtimeinfo[1]}-{$curtimeinfo[2]}")-86400);
        }
        if ($begintime > $endtime || $endtime - $begintime > 5356800) {
            $this->response->html('Date error!');
            exit();
        }
        $echoval = array();

        $echoval['project'] = array();
        $tempdata = $this->db->execute("
                        SELECT
                            id, name
                        FROM projects
                    ")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($tempdata)) {
            $echoval['project'] = array_column($tempdata, null, 'id');
        }

        $echoval['users'] = array();
        $tempdata = $this->db->execute("
                        SELECT
                            id, username, name, email, role
                        FROM users WHERE role='app-user' AND is_active=1
                    ")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($tempdata)) {
            $echoval['users'] = array_column($tempdata, null, 'id');
        }

        if (!isset($echoval['users'][$userid])) {
            $this->response->html('User error!');
            exit();
        }

        $echoval['datalist'] = array();
        $tempreport = $this->db->execute("
                        SELECT
                            *
                        FROM project_daily_user
                        WHERE user_id={$userid} AND date_num>={$begintime} AND date_num<={$endtime}
                    ")->fetchAll(PDO::FETCH_ASSOC);
        $temptasks = $tempfiles = $tempcomments = array();
        foreach ($tempreport as $tempvalue) {
            $tempvalue['task_info'] = \json_decode($tempvalue['task_info'], true);
            $tempvalue['task_count'] = \json_decode($tempvalue['task_count'], true);
            $echoval['datalist'][$tempvalue['date_num']] = $tempvalue;
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

        if (!empty($temptasks)) {
            $tempdata = $this->db->execute("
                        SELECT
                            id, title, project_id
                        FROM tasks
                        WHERE id IN (" . \implode(',', $temptasks) . ")
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['tasks'] = array_column($tempdata, null, 'id');
                $tempdata1 = $this->db->execute("
                        SELECT
                            id, status, title, task_id, user_id
                        FROM subtasks
                        WHERE task_id IN (" . \implode(',', $temptasks) . ")
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
                        WHERE id IN (" . \implode(',', $tempfiles) . ")
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
                        WHERE id IN (" . \implode(',', $tempcomments) . ")
                    ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tempdata)) {
                $echoval['comments'] = array_column($tempdata, null, 'id');
            }
        }
        //print_r($echoval);exit();
        $this->response->html($this->template->render('extends/dailyuser', ['userid' => $userid, 'begintime' => $begintime, 'endtime' => $endtime, 'config' => $config, 'echoval' => $echoval]));
    }

    public function month()
    {
        $month = $this->request->getStringParam('month');
        $this->response->html('ok ' . $month);
    }
}
