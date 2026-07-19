<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$sourceType = old('source_type', $issue->source_type ?? 'pdf');
// $publication->category = model('CategoryModel')->find($publication->category_id);
$selectedLangId = old('lang_id') ?? $publication?->lang_id ?? $activeLang?->id ?? 1;

?>

<form action="<?= $action; ?>"
    method="post"
    enctype="multipart/form-data"
    id="form-post"
    class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5">

    <?= csrf_field(); ?>

    <!-- Sidebar -->
    <div class="w-100 flex-xl-row-auto w-xl-300px">

        <div class="card card-flush py-4">

            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans('settings'); ?></h2>
                </div>
            </div>

            <div class="card-body">

                <div class="mb-8">

                    <label class="form-label fw-bold">
                        Publication
                    </label>

                    <div class="form-control form-control-solid">
                        <?= esc($publication->title); ?>
                    </div>

                </div>
                <div class="mb-8" hidden>
                    <div class="fv-row">

                        <label class="required form-label">
                            <?= trans("category"); ?>
                        </label>

                        <?= view('admin/category/_category_selector', [
                            'selectorName' => 'category_id',
                            'selectorLangId' => $selectedLangId,
                            'selectorInitialValue' => !empty($publication) ? $publication->category_id : 0,
                            'selectorInitialData' => !empty($categorySelectorData) ? $categorySelectorData : null,
                        ]); ?>

                    </div>
                </div>


                <div class="mb-8">

                    <label class="required form-label">
                        Issue Date
                    </label>

                    <input
                        type="date"
                        name="issue_date"
                        class="form-control"
                        value="<?= old('issue_date', !empty($issue->issue_date) ? date('Y-m-d', strtotime($issue->issue_date)) : date('Y-m-d')); ?>"
                        required>

                </div>

                <div class="mb-8">

                    <label class="required form-label">
                        Source Type
                    </label>

                    <select
                        id="source_type"
                        name="source_type"
                        class="form-select">

                        <option value="pdf" <?= $sourceType == 'pdf' ? 'selected' : ''; ?>>
                            PDF
                        </option>

                        <option value="website" <?= $sourceType == 'website' ? 'selected' : ''; ?>>
                            Website
                        </option>

                    </select>

                </div>

                <div class="mb-8">

                    <label class="form-label">
                        Featured
                    </label>

                    <select
                        name="is_featured"
                        class="form-select">

                        <option value="0"
                            <?= old('is_featured', $issue->is_featured ?? 0) == 0 ? 'selected' : ''; ?>>
                            No
                        </option>

                        <option value="1"
                            <?= old('is_featured', $issue->is_featured ?? 0) == 1 ? 'selected' : ''; ?>>
                            Yes
                        </option>

                    </select>

                </div>

                <div class="mb-8">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select">

                        <option value="1"
                            <?= old('status', $issue->status ?? 1) == 1 ? 'selected' : ''; ?>>
                            Active
                        </option>

                        <option value="0"
                            <?= old('status', $issue->status ?? 1) == 0 ? 'selected' : ''; ?>>
                            Disabled
                        </option>

                    </select>

                </div>

                <div>

                    <label class="form-label">
                        Sort Order
                    </label>

                    <input
                        type="number"
                        name="sort_order"
                        class="form-control"
                        value="<?= old('sort_order', $issue->sort_order ?? 0); ?>">

                </div>

            </div>

        </div>

    </div>

    <!-- Main -->

    <div class="d-flex flex-column flex-row-fluid gap-7">

        <div class="card card-flush py-4">

            <div class="card-header">

                <div class="card-title">

                    <h2>Issue Details</h2>

                </div>

            </div>

            <div class="card-body">

                <div class="mb-6">

                    <label class="form-label">
                        Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        value="<?= old('title', $issue->title ?? ''); ?>"
                        placeholder="Optional">

                </div>

                <div id="pdf-section">

                    <div class="mb-6">

                        <label class="form-label">
                            PDF File
                        </label>

                        <input
                            type="file"
                            name="pdf_file"
                            class="form-control"
                            accept=".pdf">

                        <?php if (!empty($issue->pdf_file)): ?>

                            <div class="mt-3">

                                <a href="<?= base_url($issue->pdf_file); ?>"
                                    target="_blank">

                                    View Current PDF

                                </a>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <div id="website-section">

                    <div class="mb-6">

                        <label class="form-label">
                            Website URL
                        </label>

                        <input
                            type="url"
                            name="website_url"
                            class="form-control"
                            value="<?= old('website_url', $issue->website_url ?? ''); ?>">

                    </div>

                </div>

                <div class="mb-6">

                    <label class="form-label">
                        Cover Image
                    </label>

                    <input
                        type="file"
                        name="cover_image"
                        class="form-control"
                        accept="image/*">

                    <?php if (!empty($issue->cover_image)): ?>

                        <div class="mt-3">

                            <img
                                src="<?= base_url($issue->cover_image); ?>"
                                style="height:120px;"
                                class="rounded border">

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="d-flex justify-content-end">

            <button
                type="submit"
                class="btn btn-primary">

                <?= !empty($issue)
                    ? trans('save_changes')
                    : 'Add Issue'; ?>

            </button>

        </div>

    </div>

</form>

<script>
    function toggleSourceFields() {

        const type = document.getElementById('source_type').value;

        document.getElementById('pdf-section').style.display =
            type === 'pdf' ? 'block' : 'none';

        document.getElementById('website-section').style.display =
            type === 'website' ? 'block' : 'none';
    }

    document
        .getElementById('source_type')
        .addEventListener('change', toggleSourceFields);

    toggleSourceFields();
</script>
<?= $this->endSection(); ?>