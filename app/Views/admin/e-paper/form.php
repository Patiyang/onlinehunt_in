<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$sourceType = old('source_type', $issue->source_type ?? 'pdf');
// $publication->category = model('CategoryModel')->find($publication->category_id);
$selectedLangId = old('lang_id') ?? $publication?->lang_id ?? $activeLang?->id ?? 1;
$selectedDistrict = old('district', $issue->district ?? '');
$districts = [
    "Bagalkot",
    "Ballari (Bellary)",
    "Belagavi (Belgaum)",
    "Bengaluru (Bangalore) Rural",
    "Bengaluru (Bangalore) Urban",
    "Bidar",
    "Chamarajanagar",
    "Chikballapur",
    "Chikkamagaluru (Chikmagalur)",
    "Chitradurga",
    "Dakshina Kannada",
    "Davangere",
    "Dharwad",
    "Gadag",
    "Hassan",
    "Haveri",
    "Kalaburagi (Gulbarga)",
    "Kodagu",
    "Kolar",
    "Koppal",
    "Mandya",
    "Mysuru (Mysore)",
    "Raichur",
    "Ramanagara",
    "Shivamogga (Shimoga)",
    "Tumakuru (Tumkur)",
    "Udupi",
    "Uttara Kannada (Karwar)",
    "Vijayapura (Bijapur)",
    "Yadgir",
    "Vijayanagara"
];
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

                    <label class="form-label fw-bold ">
                        <?= trans('publication'); ?>
                    </label>

                    <div class="form-control form-control-solid ">
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
                        <?= trans('issue_date'); ?>
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
                        <?= trans('district'); ?>
                    </label>
                    <select
                        name="district"
                        id="district"
                        class="form-select">

                        <option value="">All Districts</option>

                        <?php foreach ($districts as $district): ?>

                            <option
                                value="<?= esc($district); ?>"
                                <?= $selectedDistrict === $district ? 'selected' : ''; ?>>
                                <?= esc($district); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>
                <div class="mb-8">

                    <label class="required form-label">
                        <?= trans('source_type'); ?>
                    </label>

                    <select
                        id="source_type"
                        name="source_type"
                        class="form-select">

                        <option value="pdf" <?= $sourceType == 'pdf' ? 'selected' : ''; ?>>
                            <?= trans('pdf'); ?>
                        </option>

                        <option value="website" <?= $sourceType == 'website' ? 'selected' : ''; ?>>
                            <?= trans('website'); ?>
                        </option>

                    </select>

                </div>

                <div class="mb-8">

                    <label class="form-label">
                        <?= trans('featured'); ?>
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
                        <?= trans('status'); ?>
                    </label>

                    <select
                        name="status"
                        class="form-select">

                        <option value="1"
                            <?= old('status', $issue->status ?? 1) == 1 ? 'selected' : ''; ?>>
                            <?= trans('active'); ?>
                        </option>

                        <option value="0"
                            <?= old('status', $issue->status ?? 1) == 0 ? 'selected' : ''; ?>>
                            <?= trans('disabled'); ?>
                        </option>

                    </select>

                </div>

                <div>

                    <label class="form-label">
                        <?= trans('sort_order'); ?>
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

                    <h2><?= trans('issue_details'); ?></h2>

                </div>

            </div>

            <div class="card-body">

                <div class="mb-6">

                    <label class="form-label required">
                        <?= trans('title'); ?>
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        value="<?= old('title', $issue->title ?? ''); ?>"
                        placeholder="Optional" required>
                </div>

                <div id="pdf-section">

                    <div class="mb-6">

                        <label class="form-label">
                            <?= trans('pdf_file'); ?>
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

                                    <?= trans('view_pdf'); ?>

                                </a>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <div id="website-section">

                    <div class="mb-6">

                        <label class="required form-label">
                            <?= trans('website_url'); ?>
                        </label>

                        <input
                            type="url"
                            name="website_url"
                            class="form-control"
                            value="<?= old('website_url', $issue->website_url ?? ''); ?>" required>

                    </div>

                </div>

                <div class="mb-6">

                    <label class="form-label">
                        <?= trans('cover_image'); ?>

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
                    : trans('add_issue'); ?>

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