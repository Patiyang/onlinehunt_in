<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>


<div class="card">
    <form action="<?= adminUrl('live-news'); ?>" method="get" class="form-filter">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <?= view('admin/includes/_filter_rows'); ?>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">

                    <?= view('admin/includes/_filters', [
                        'filterPageUrl' => adminUrl('live-news'),
                        'filters' => [
                            'language',
                            // 'category',
                            'search' => trans('search')
                        ]
                    ]); ?>

                    <a href="<?= adminUrl('live-news/add'); ?>" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        <?= trans('add_live_news'); ?>
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
                        <th class="min-w-200px">Title</th>
                        <th class="min-w-250px">URL</th>
                        <th class="min-w-125px"><?= trans('language'); ?></th>
                        <th class="min-w-125px"><?= trans('date_added'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">

                    <?php if (!empty($links)):
                        foreach ($links as $item):
                            $language = getLanguageClient($item->lang_id, $config); ?>
                            <tr>
                                <td><?= esc($item->id); ?></td>
                                <td>
                                    <div class="text-gray-900 mb-1"><?= esc($item->title); ?></div>
                                    <?php if ((int)$item->auto_update === 1): ?>
                                        <span class="badge badge-light-success pt-2"><?= trans("auto_update"); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($item->url); ?></td>

                                <td><?= !empty($language) ? esc($language->name) : ''; ?></td>
                                <td><?= formatDate($item->created_at); ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                        </a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                            <div class="menu-item px-3">
                                                <a href="<?= adminUrl('live-news/edit/' . esc($item->id)); ?>" class="menu-link px-3"><?= trans('edit'); ?></a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 text-danger js-action-trigger"
                                                    data-url="<?= base_url('LiveNews/deleteFeed'); ?>"
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

        <?php if (empty($links)): ?>
            <p class="text-muted text-center mt-6">
                <?= trans("no_records_found"); ?>
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