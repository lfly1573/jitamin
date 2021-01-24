<div class="page-header">
    <h2><?= $this->text->e($file['name']) ?><?php if ($file['is_image']): ?><span style="font-size:14px;margin-left:20px;">(点击图片新窗口查看)</span><?php endif ?></h2>
</div>
<div class="file-viewer">
    <?php if ($file['is_image']): ?>
        <a href="<?= $this->url->href('AttachmentController', 'image', $params) ?>" target="_blank"><img src="<?= $this->url->href('AttachmentController', 'image', $params) ?>" alt="<?= $this->text->e($file['name']) ?>"></a>
    <?php elseif ($type === 'markdown'): ?>
        <article class="markdown">
            <?= $this->text->markdown($content) ?>
        </article>
    <?php elseif ($type === 'text'): ?>
        <pre><?= $content ?></pre>
    <?php endif ?>
</div>
