<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jitamin\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PDO;

class ProjectDailyReportCommand extends BaseCommand
{
    /**
     * Configure the console command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('projects:daily-report')
            ->setDescription('Send daily report');
    }

    /**
     * Execute the console command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $curtime = strtotime(date("Y-m-d"),time());
        echo $curtime.PHP_EOL;

        $tempcolumn = $this->db->execute("
                        SELECT
                            id,title
                        FROM columns
                        WHERE title='正在做' OR title='已完成'
                    ")->fetchAll(PDO::FETCH_ASSOC);

        $columnarray = array('ing'=>array(),'end'=>array());
        foreach ($tempcolumn as $tempvalue) {
            if ($tempvalue['title']=='正在做') {
                $columnarray['ing'][] = $tempvalue['id'];
            } else {
                $columnarray['end'][] = $tempvalue['id'];
            }
        }
        echo '--- column ---'.PHP_EOL;
        print_r($columnarray);
        
        $datalist = $datacount = $oldtask = $tasklist = array();
        $task = $this->db->execute("
                        SELECT
                            id AS task_id, column_id, owner_id AS user_id, date_due, progress
                        FROM tasks
                        WHERE is_active=1 AND owner_id>0 AND (column_id IN (".implode(',', $columnarray['ing']).") OR (column_id IN (".implode(',', $columnarray['end']).") AND date_modification>={$curtime}))
                        ORDER BY id ASC
                    ")->fetchAll(PDO::FETCH_ASSOC);
        $file = $this->db->execute("
                        SELECT
                            id, task_id, user_id
                        FROM task_has_files
                        WHERE date>={$curtime}
                    ")->fetchAll(PDO::FETCH_ASSOC);
        $comment = $this->db->execute("
                    SELECT
                        id, task_id, user_id
                    FROM comments
                    WHERE date_creation>={$curtime}
                ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($task as $tempvalue) {
            if (!isset($datalist[$tempvalue['user_id']])) {
                $datalist[$tempvalue['user_id']] = array();
            }
            $tempvalue['isend'] = (in_array($tempvalue['column_id'], $columnarray['end']) || $tempvalue['progress']==100) ? 1 : 0;
            $datalist[$tempvalue['user_id']][$tempvalue['task_id']] = $tempvalue;
            $tasklist[$tempvalue['task_id']] = $tempvalue['user_id'];
        }
        foreach ($file as $tempvalue) {
            if (!isset($datalist[$tempvalue['user_id']])) {
                $datalist[$tempvalue['user_id']] = array();
            }
            if (!isset($datalist[$tempvalue['user_id']][$tempvalue['task_id']])) {
                $datalist[$tempvalue['user_id']][$tempvalue['task_id']] = array('task_id'=>$tempvalue['task_id']);
            }
            if (!isset($datalist[$tempvalue['user_id']][$tempvalue['task_id']]['files'])) {
                $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['files'] = array();
            }
            $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['files'][$tempvalue['id']] = $tempvalue['id'];
        }
        foreach ($comment as $tempvalue) {
            if (!isset($datalist[$tempvalue['user_id']])) {
                $datalist[$tempvalue['user_id']] = array();
            }
            if (!isset($datalist[$tempvalue['user_id']][$tempvalue['task_id']])) {
                $datalist[$tempvalue['user_id']][$tempvalue['task_id']] = array('task_id'=>$tempvalue['task_id']);
            }
            if (!isset($datalist[$tempvalue['user_id']][$tempvalue['task_id']]['comments'])) {
                $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['comments'] = array();
            }
            $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['comments'][$tempvalue['id']] = $tempvalue['id'];
        }

        if (!empty($tasklist)) {
            $tempdata1 = $this->db->execute("
                    SELECT
                        id, status, task_id, user_id
                    FROM subtasks
                    WHERE task_id IN (".\implode(',',array_keys($tasklist)).")
                    ORDER BY id ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tempdata1 as $subvalue) {
                if (!isset($datalist[$tasklist[$subvalue['task_id']]][$subvalue['task_id']]['subtasks'])) {
                    $datalist[$tasklist[$subvalue['task_id']]][$subvalue['task_id']]['subtasks'] = array();
                }
                $datalist[$tasklist[$subvalue['task_id']]][$subvalue['task_id']]['subtasks'][$subvalue['id']] = $subvalue;
            }
        }

        /*
            总任务数 all
            新任务数 new
            进行中的任务数 ing
            已完成任务数 end
            没有进度变化的任务 unchanged
            完成任务没有评论或附件的 fruitless
            没有完成时间的任务数 unplanned
            超期的任务数 overtime
        */
        $tempoldtasklist = $this->db->execute("
                        SELECT
                            *
                        FROM project_daily_user
                        WHERE date_num=".($curtime-86400)."
                    ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tempoldtasklist as $tempvalue) {
            $oldtask[$tempvalue['user_id']] = \json_decode($tempvalue['task_info'], true);
        }
        foreach ($task as $tempvalue) {
            if (!isset($datacount[$tempvalue['user_id']])) {
                $datacount[$tempvalue['user_id']] = array('all'=>0,'new'=>0,'ing'=>0,'end'=>0,'unchanged'=>0,'fruitless'=>0,'unplanned'=>0,'overtime'=>0);
            }
            $datacount[$tempvalue['user_id']]['all']++;
            if ($datalist[$tempvalue['user_id']][$tempvalue['task_id']]['isend']) {
                $datacount[$tempvalue['user_id']]['end']++;
                if (empty($datalist[$tempvalue['user_id']][$tempvalue['task_id']]['comments']) && empty($datalist[$tempvalue['user_id']][$tempvalue['task_id']]['files'])) {
                    $datacount[$tempvalue['user_id']]['fruitless']++;
                    $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['isfruitless'] = 1;
                }
            } else {
                $datacount[$tempvalue['user_id']]['ing']++;
                if (isset($oldtask[$tempvalue['user_id']][$tempvalue['task_id']]['progress']) && $oldtask[$tempvalue['user_id']][$tempvalue['task_id']]['progress']==$tempvalue['progress']) {
                    $datacount[$tempvalue['user_id']]['unchanged']++;
                    $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['isunchanged'] = 1;
                }
            }
            if (!isset($oldtask[$tempvalue['user_id']][$tempvalue['task_id']])) {
                $datacount[$tempvalue['user_id']]['new']++;
                $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['isnew'] = 1;
            }
            if (empty($tempvalue['date_due'])) {
                $datacount[$tempvalue['user_id']]['unplanned']++;
                $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['isunplanned'] = 1;
            } elseif ($tempvalue['date_due'] < $curtime) {
                $datacount[$tempvalue['user_id']]['overtime']++;
                $datalist[$tempvalue['user_id']][$tempvalue['task_id']]['isovertime'] = 1;
            }
        }
        echo '--- task ---'.PHP_EOL;
        print_r($datalist);
        echo '--- count ---'.PHP_EOL;
        print_r($datacount);
        
        foreach ($datalist as $key => $value) {
            $dataone = array('date_num'=>$curtime, 'user_id'=>$key, 'task_info'=>\json_encode($value), 'task_count'=>\json_encode(isset($datacount[$key]) ? $datacount[$key] : array()));
            $this->db
                ->table('project_daily_user')
                ->insert($dataone);
        }
        
        $url = env('APP_URL').'/dailyreport/'.date("Y-m-d");
        $sendemail = explode(';', env('DAILY_REPORT_EMAIL'));
        foreach ($sendemail as $tempvalue) {
            $this->emailClient->send(
                $tempvalue,
                'admin',
                date("Y-m-d").'自动日报',
                '今日自动日报已生成，请点击 <a href="'.$url.'">'.$url.'</a> 查看。'
            );
        }
        
        echo "=== end ===".PHP_EOL;
    }
}
