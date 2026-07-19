<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>


<div class="card">
    <form action="<?= adminUrl('publications'); ?>" method="get" class="form-filter">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <?= view('admin/includes/_filter_rows'); ?>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end gap-3">

                    <?= view('admin/includes/_filters', [
                        'filterPageUrl' => adminUrl('publications'),
                        'filters' => [
                            'language',
                            'search' => trans('search')
                        ]
                    ]); ?>

                    <a href="<?= adminUrl('publications/add'); ?>" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Add Publication
                    </a>

                </div>
            </div>
        </div>
    </form>

    <div class="card-body pt-5">

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-20px"><?= trans('id'); ?></th>
                        <th class="min-w-250px">Publication</th>
                        <th class="min-w-140px">Type</th>
                        <th class="min-w-120px"><?= trans('language'); ?></th>
                        <th class="min-w-80px text-center">Issues</th>
                        <th class="min-w-140px"><?= trans('date_added'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                </thead>

                <tbody class="text-gray-600 fw-semibold">

                    <?php if (!empty($publications)):
                        foreach ($publications as $item):
                            $language = getLanguageClient($item->lang_id, $config); ?>

                            <tr>

                                <td><?= esc($item->id); ?></td>

                                <td>
                                    <div class="fw-bold text-gray-900">
                                        <?= esc($item->title); ?>
                                    </div>

                                    <?php if (!empty($item->description)): ?>
                                        <div class="text-muted fs-7">
                                            <?= character_limiter(strip_tags($item->description), 80); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge badge-light-primary">
                                        <?= ucwords(str_replace('_', ' ', $item->publication_type)); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($language) ? esc($language->name) : ''; ?>
                                </td>

                                <td class="text-center">
                                    <a href="<?= adminUrl('publications/' . $item->id . '/issues'); ?>"
                                        class="badge badge-light-primary">
                                        <?= (int)$item->issue_count; ?> Issues
                                    </a>
                                </td>

                                <td>
                                    <?= formatDate($item->created_at); ?>
                                </td>

                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-3">

                                        <a href="#"
                                            class="btn btn-light btn-active-light-primary btn-sm"
                                            data-kt-menu-trigger="click"
                                            data-kt-menu-placement="bottom-end">

                                            <?= trans('select'); ?>
                                            <i class="ki-duotone ki-down fs-5 ms-2"></i>

                                        </a>

                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">


                                            <div class="menu-item px-3">
                                                <a href="<?= adminUrl('publications/edit/' . esc($item->id)); ?>"
                                                    class="menu-link px-3">
                                                    <?= trans('edit'); ?>
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)"
                                                    class="menu-link px-3 text-danger js-action-trigger"
                                                    data-url="<?= base_url('Publication/deletePublication'); ?>"
                                                    data-action="delete"
                                                    data-id="<?= $item->id; ?>"
                                                    data-message="<?= trans("confirm_delete", "attr"); ?>"
                                                    data-confirm="1">

                                                    <?= trans('delete'); ?>

                                                </a>
                                            </div>

                                        </div>

                                    </div>
                                </td>

                            </tr>

                    <?php endforeach;
                    endif; ?>

                </tbody>
            </table>
        </div>

        <?php if (empty($publications)): ?>

            <p class="text-muted text-center mt-6">
                <?= trans('no_records_found'); ?>
            </p>

        <?php endif; ?>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>

            <div class="d-flex justify-content-end align-items-center mt-5">
                <?= $pager->links(); ?>
            </div>

        <?php endif; ?>

    </div>
</div>
<?= $this->endSection(); ?>