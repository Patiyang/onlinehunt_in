<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="card">
    <form action="<?= adminUrl('publications/' . $publication->id . '/issues'); ?>" method="get" class="form-filter">

        <div class="card-header border-0 pt-6">

            <div class="card-title">
                <?= view('admin/includes/_filter_rows'); ?>
            </div>

            <div class="card-toolbar">

                <div class="d-flex justify-content-end gap-3">

                    <?= view('admin/includes/_filters', [
                        'filterPageUrl' => adminUrl('publications/' . $publication->id . '/issues'),
                        'filters' => [
                            'search' => trans('search')
                        ]
                    ]); ?>

                    <a href="<?= adminUrl('publications/' . $publication->id . '/issues/add'); ?>"
                        class="btn btn-primary">

                        <i class="ki-duotone ki-plus fs-2"></i>

                        Add Issue

                    </a>

                </div>

            </div>

        </div>

    </form>

    <div class="card-body pt-5">

        <div class="mb-8">

            <h2 class="fw-bold mb-1">
                <?= esc($publication->title); ?>
            </h2>

            <div class="text-muted">
                <?= ucfirst($publication->publication_type); ?>
            </div>

        </div>

        <div class="table-responsive">

            <table class="table align-middle table-row-dashed fs-6 gy-5">

                <thead>

                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">

                        <th width="80">Cover</th>

                        <th>Issue</th>

                        <th width="140">Issue Date</th>

                        <th width="120">Source</th>

                        <th width="100">Featured</th>

                        <th width="100">Views</th>

                        <th width="100">Status</th>

                        <th class="text-end" width="80">
                            <?= trans('options'); ?>
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (!empty($issues)): ?>

                        <?php foreach ($issues as $item): ?>

                            <tr>

                                <td>

                                    <?php if (!empty($item->cover_image)): ?>

                                        <img src="<?= base_url($item->cover_image); ?>"
                                            class="rounded"
                                            style="width:55px;height:75px;object-fit:cover;">

                                    <?php else: ?>

                                        <div class="symbol symbol-55px">
                                            <div class="symbol-label bg-light-primary">
                                                <i class="fas fa-newspaper fs-2 text-primary"></i>
                                            </div>
                                        </div>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="fw-bold">

                                        <?= esc($item->title ?: $publication->title); ?>

                                    </div>

                                </td>

                                <td>

                                    <?= date('d M Y', strtotime($item->issue_date)); ?>

                                </td>

                                <td>

                                    <?php if ($item->source_type == 'pdf'): ?>

                                        <span class="badge badge-light-danger">

                                            PDF

                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-light-success">

                                            Website

                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($item->is_featured): ?>

                                        <span class="badge badge-light-success">

                                            Yes

                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-light-secondary">

                                            No

                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?= number_format($item->total_views); ?>

                                </td>

                                <td>

                                    <?php if ($item->status): ?>

                                        <span class="badge badge-light-success">

                                            Active

                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-light-danger">

                                            Disabled

                                        </span>

                                    <?php endif; ?>

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

                                                <a href="<?= adminUrl('issues/edit/' . $item->id); ?>"
                                                    class="menu-link px-3">

                                                    <?= trans('edit'); ?>

                                                </a>

                                            </div>

                                            <?php if ($item->source_type == 'pdf'): ?>

                                                <div class="menu-item px-3">

                                                    <a href="<?= base_url($item->pdf_file); ?>"
                                                        target="_blank"
                                                        class="menu-link px-3">

                                                        View PDF

                                                    </a>

                                                </div>

                                            <?php else: ?>

                                                <div class="menu-item px-3">

                                                    <a href="<?= esc($item->website_url); ?>"
                                                        target="_blank"
                                                        class="menu-link px-3">

                                                        Open Website

                                                    </a>

                                                </div>

                                            <?php endif; ?>

                                            <div class="menu-item px-3">

                                                <a href="javascript:void(0)"
                                                    class="menu-link px-3 text-danger js-action-trigger"
                                                    data-url="<?= base_url('Epaper/deleteIssue'); ?>"
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

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <?php if (empty($issues)): ?>

            <p class="text-muted text-center mt-6">

                <?= trans('no_records_found'); ?>

            </p>

        <?php endif; ?>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>

            <div class="d-flex justify-content-end mt-5">

                <?= $pager->links(); ?>

            </div>

        <?php endif; ?>

    </div>

</div>
<?= $this->endSection(); ?>