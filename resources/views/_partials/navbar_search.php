<form class="navbar-form navbar-right search" role="search" action="<?= $this->url->dir() ?>" method="get">
    <?= $this->form->hidden('controller', $filters) ?>
        <?= $this->form->hidden('action', $filters) ?>
        <?= $this->form->hidden('project_id', $filters) ?>

        <div class="input-addon">
            <span class="input-addon-item"><?= t('This project') ?></span>
            <?= $this->form->text('q', $filters, [], ['placeholder="'.t('Filter').'"'], 'input-addon-field') ?>
            <div class="input-addon-item">
                <?= $this->render('_partials/filters_helper', ['reset' => 'status:open', 'project' => $project]) ?>
            </div>

            <?php if (isset($custom_filters_list) && !empty($custom_filters_list)): ?>
            <div class="input-addon-item">
                <div class="dropdown">
                    <a href="#" class="dropdown-menu dropdown-menu-link-icon" title="<?= t('Custom filters') ?>"><i class="fa fa-anchor"></i> <i class="fa fa-caret-down"></i></a>
                    <ul>
                        <?php foreach ($custom_filters_list as $filter): ?>
                            <li><a href="#" class="filter-helper" data-<?php if ($filter['append']): ?><?= 'append-' ?><?php endif ?>filter='<?= $this->text->e($filter['filter']) ?>'><?= $this->text->e($filter['name']) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <?php endif ?>

            <?php if (isset($users_list)): ?>
            <div class="input-addon-item">
                <div class="dropdown">
                    <a href="#" class="dropdown-menu dropdown-menu-link-icon" title="<?= t('User filters') ?>"><i class="fa fa-user"></i> <i class="fa fa-caret-down"></i></a>
                    <ul>
                        <li><a href="#" class="filter-helper filter-reset" data-filter="status:open"><?= t('Reset filters') ?></a></li>
                        <li><a href="#" class="filter-helper" data-unique-filter="assignee:nobody"><?= t('Not assigned') ?></a></li>
                        <?php foreach ($users_list as $user): ?>
                            <li><a href="#" class="filter-helper" data-unique-filter='assignee:"<?= $this->text->e($user) ?>"'><?= $this->text->e($user) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <?php endif ?>

            <?php if (isset($categories_list) && !empty($categories_list)): ?>
            <div class="input-addon-item">
                <div class="dropdown">
                    <a href="#" class="dropdown-menu dropdown-menu-link-icon" title="<?= t('Category filters') ?>"><i class="fa fa-sitemap"></i> <i class="fa fa-caret-down"></i></a>
                    <ul>
                        <li><a href="#" class="filter-helper filter-reset" data-filter="status:open"><?= t('Reset filters') ?></a></li>
                        <li><a href="#" class="filter-helper" data-unique-filter="category:none"><?= t('No category') ?></a></li>
                        <?php foreach ($categories_list as $category): ?>
                            <li><a href="#" class="filter-helper" data-unique-filter='category:"<?= $this->text->e($category) ?>"'><?= $this->text->e($category) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <?php endif ?>

            <?php if (isset($swimlanes_list) && !empty($swimlanes_list)): ?>
            <div class="input-addon-item">
                <div class="dropdown">
                    <a href="#" class="dropdown-menu dropdown-menu-link-icon" title="<?= t('Swimlanes') ?>"><i class="fa fa-bars"></i> <i class="fa fa-caret-down"></i></a>
                    <ul>
                        <li><a href="#" class="filter-helper filter-reset" data-filter="status:open"><?= t('Reset filters') ?></a></li>
                        <?php foreach ($swimlanes_list as $swimlane): ?>
                            <li><a href="#" class="filter-helper" data-unique-filter='swimlane:"<?= $this->text->e($swimlane) ?>"'><?= $this->text->e($swimlane) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <?php endif ?>

            <div class="input-addon-item">
                <div class="dropdown">
                    <a href="#" class="dropdown-menu dropdown-menu-link-icon" title="<?= t('Column') ?>"><i class="fa fa-columns"></i> <i class="fa fa-caret-down"></i></a>
                    <ul>
                        <li><a href="#" class="filter-helper filter-reset" data-filter="status:open"><?= t('Reset filters') ?></a></li>
                        <li><a href="#" class="filter-helper" data-unique-filter="column:<?= t('Backlog') ?>"><?= t('Backlog') ?></a></li>
                        <li><a href="#" class="filter-helper" data-unique-filter="column:<?= t('Ready') ?>"><?= t('Ready') ?></a></li>
                        <li><a href="#" class="filter-helper" data-unique-filter="column:<?= t('Work in progress') ?>"><?= t('Work in progress') ?></a></li>
                        <li><a href="#" class="filter-helper" data-unique-filter="column:<?= t('Done') ?>"><?= t('Done') ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
</form>