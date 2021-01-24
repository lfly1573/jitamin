<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="https://www.layuicdn.com/layui/css/layui.css" />
    <title><?= $date ?>项目日报</title>
  </head>
  <body>
    <div style="padding:10px">
    <form class="layui-form" action="/dailyreport" style="text-align:center;">
    <div class="layui-form-item"><div class="layui-inline">
    <div class="layui-input-inline" style="width: 150px;">
      <select name="projectid" lay-verify="">
      <option value="0">- 请选择项目 -</option>
      <?php foreach ($echoval['project'] as $project): ?>
        <option value="<?= $project['id'] ?>"<?php if ($projectid==$project['id']): ?> selected<?php endif; ?>><?= $project['name'] ?></option>
      <?php endforeach; ?>
      </select></div>
      <div class="layui-input-inline" style="width: 140px;font-size:22px;"><input type="text" class="layui-input" name="date" id="date"></div>
      <div class="layui-form-mid" style="font-size:22px;">项目日报</div>
      <div class="layui-input-inline" style="width: 100px;"><button class="layui-btn layui-btn-primary" lay-submit lay-filter="formDemo">查看</button></div>
    </div></div>
    </form>
    <table class="layui-table">
      <colgroup>
        <col width="150">
        <col>
        <col width="200">
      </colgroup>
      <thead>
        <tr>
          <th>姓名</th>
          <th>任务情况</th>
          <th>统计</th>
        </tr> 
      </thead>
      <tbody>
        <?php foreach ($echoval['datalist'] as $report): ?>
          <?php if ($echoval['users'][$report['user_id']]['role']=='app-user'): ?>
          <tr>
            <td><a href="/dailyreport/user/show?userid=<?= $echoval['users'][$report['user_id']]['id'] ?>" target="_blank"><?= $echoval['users'][$report['user_id']]['name'] ?></a></td>
            <td>
                <?php foreach ($report['task_info'] as $taskone): ?>
                  <?php if ($projectid==0 || $projectid==$echoval['tasks'][$taskone['task_id']]['project_id']): ?>
                  <div style="marin: 5px;padding-bottom:5px;line-height:180%;">
                    <h3 style="padding-bottom:3px;color:#009688;">
                      <i class="layui-icon layui-icon-note"></i> 
                      <?php if (!isset($echoval['tasks'][$taskone['task_id']])): ?>
                        #<?= $taskone['task_id'] ?> 已删除任务 
                      <?php else: ?>
                        <a href="/project/<?= $echoval['tasks'][$taskone['task_id']]['project_id'] ?>/task/<?= $taskone['task_id'] ?>" target="_blank" style="color:#009688;">
                        <?php if ($projectid==0): ?>【<?= $echoval['project'][$echoval['tasks'][$taskone['task_id']]['project_id']]['name'] ?>】<?php endif; ?><?= $echoval['tasks'][$taskone['task_id']]['title'] ?>
                        </a> 
                      <?php endif; ?>
                      <?php if (!isset($taskone['user_id'])): ?>
                        <span class="layui-badge layui-bg-gray">未开始或非本人任务</span>
                      <?php elseif ($taskone['isend']): ?>
                        <span class="layui-badge layui-bg-green">已完成</span>
                      <?php else: ?>
                        <span class="layui-badge layui-bg-blue">进度: <?= $taskone['progress'] ?>%</span>
                      <?php endif; ?>
                      <?php if (isset($taskone['isunchanged'])): ?><span class="layui-badge layui-bg-orange">进度无变化</span><?php endif; ?>
                      <?php if (isset($taskone['isfruitless'])): ?><span class="layui-badge">完成无成果</span><?php endif; ?>
                      <?php if (isset($taskone['isunplanned'])): ?><span class="layui-badge layui-bg-orange">无预计时间</span><?php endif; ?>
                      <?php if (isset($taskone['isovertime'])): ?><span class="layui-badge">超期未完成</span><?php endif; ?>
                      <?php if (!empty($taskone['date_due'])): ?><span class="layui-badge-rim">预计完成时间: <?= date("m-d",$taskone['date_due']) ?></span><?php endif; ?>
                    </h3>
                    <?php if (!empty($taskone['subtasks'])): ?><?php foreach ($taskone['subtasks'] as $subtask): ?>
                      <i class="layui-icon layui-icon-form"></i> <span class="layui-badge layui-bg-gray">子任务</span> <?= $echoval['subtasks'][$taskone['task_id']][$subtask['id']]['title'] ?>
                      <?php if ($subtask['status']==2): ?><span class="layui-badge-rim">已完成</span><?php endif; ?>
                      <?php if ($subtask['status']==1): ?><span class="layui-badge-rim">进行中</span><?php endif; ?>
                      <br />
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($taskone['comments'])): ?><?php foreach ($taskone['comments'] as $commentone): ?>
                      <i class="layui-icon layui-icon-edit"></i> <span class="layui-badge layui-bg-gray">评论</span> <?= $echoval['comments'][$commentone]['comment'] ?><br />
                    <?php endforeach; ?><?php endif; ?>
                    <?php if (!empty($taskone['files'])): ?><?php foreach ($taskone['files'] as $fileone): ?>
                      <i class="layui-icon layui-icon-file-b"></i> <span class="layui-badge layui-bg-gray">文件</span> <a style="text-decoration:underline;" href="/?controller=AttachmentController&action=<?php if($echoval['files'][$fileone]['is_image']){ echo 'show'; } else { echo 'download'; } ?>&task_id=<?= $taskone['task_id'] ?>&project_id=<?= $echoval['tasks'][$taskone['task_id']]['project_id'] ?>&file_id=<?= $echoval['files'][$fileone]['id'] ?>" target="_blank"><?= $echoval['files'][$fileone]['name'] ?></a><br />
                    <?php endforeach; ?><?php endif; ?>
                  </div>
                  <?php endif; ?>
                <?php endforeach; ?>
            </td>
            <td>
              <div style="marin: 5px;line-height:180%;">
                <?php if (!empty($report['task_count']['all'])): ?>总任务数：<?= $report['task_count']['all'] ?><br /><?php endif; ?>
                <?php if (!empty($report['task_count']['new'])): ?>新任务数：<?= $report['task_count']['new'] ?><br /><?php endif; ?>
                <?php if (!empty($report['task_count']['ing'])): ?>进行中的：<?= $report['task_count']['ing'] ?><br /><?php endif; ?>
                <?php if (!empty($report['task_count']['end'])): ?>已完成的：<?= $report['task_count']['end'] ?><br /><?php endif; ?>
                <?php if (!empty($report['task_count']['unchanged'])): ?>进度无变化：<?= $report['task_count']['unchanged'] ?><br /><?php endif; ?>
                <?php if (!empty($report['task_count']['fruitless'])): ?>完成无成果：<?= $report['task_count']['fruitless'] ?><br /><?php endif; ?>
                <?php if (!empty($report['task_count']['unplanned'])): ?>无预计时间：<?= $report['task_count']['unplanned'] ?><br /><?php endif; ?>
                <?php if (!empty($report['task_count']['overtime'])): ?>超期未完成：<?= $report['task_count']['overtime'] ?><br /><?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <script src="https://www.layuicdn.com/layui/layui.js"></script>
    <script>
      layui.use(['layer', 'table', 'element', 'laydate'], function(){
        var layer = layui.layer
            ,table = layui.table
            ,element = layui.element
            ,laydate = layui.laydate;

            laydate.render({
              elem: '#date'
              ,max: -1
              ,value: '<?= $date ?>'
            });
      });
    </script>
  </body>
</html>
