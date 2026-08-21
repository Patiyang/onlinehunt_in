<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $mobileAd?->lang_id ?? $activeLang?->id ?? 1;
?>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data"
    id="form-mobile-ad"
    class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

    <?= csrf_field(); ?>

    <!-- LEFT SETTINGS -->
    <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">

        <div class="card card-flush py-4">

            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("settings"); ?></h2>
                </div>
            </div>

            <div class="card-body pt-5">

                <div class="d-flex flex-column gap-8">

                    <!-- Language -->
                    <div class="fv-row">

                        <label class="form-label required">
                            <?= trans("language"); ?>
                        </label>

                        <select name="lang_id"
                            id="languageSwitcher"
                            class="form-select"
                            data-kt-select2="true"
                            data-placeholder="<?= trans("select_an_option", "attr"); ?>"
                            data-hide-search="true"
                            required>

                            <option></option>

                            <?php foreach ($activeLanguages as $language): ?>

                                <option value="<?= $language->id; ?>"
                                    <?= $selectedLangId == $language->id ? 'selected' : ''; ?>>

                                    <?= esc($language->name); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <?= validationError('lang_id'); ?>

                    </div>

                    <!-- Ad Dimensions -->
                    <div class="fv-row">

                        <label class="form-label required">
                            <?= trans('ad_dimensions'); ?> </label>

                        <?php
                        $selectedWidth = old('width', $mobileAd->width ?? '');
                        $selectedHeight = old('height', $mobileAd->height ?? '');

                        $selectedDimension = '';

                        if (!empty($selectedWidth) && !empty($selectedHeight)) {
                            $selectedDimension = $selectedWidth . 'x' . $selectedHeight;
                        }
                        ?>

                        <select
                            id="adDimension"
                            class="form-select"
                            data-kt-select2="true"
                            data-placeholder="Select ad dimensions"
                            data-hide-search="true"
                            onchange="updateAdDimensions(this)"
                            required>

                            <option value=""></option>

                            <option value="300x250" data-width="300" data-height="250"
                                <?= $selectedDimension === '300x250' ? 'selected' : ''; ?>>
                                300 × 250
                            </option>

                            <option value="320x50" data-width="320" data-height="50"
                                <?= $selectedDimension === '320x50' ? 'selected' : ''; ?>>
                                320 × 50
                            </option>

                            <option value="320x100" data-width="320" data-height="100"
                                <?= $selectedDimension === '320x100' ? 'selected' : ''; ?>>
                                320 × 100
                            </option>

                            <option value="300x600" data-width="300" data-height="600"
                                <?= $selectedDimension === '300x600' ? 'selected' : ''; ?>>
                                300 × 600
                            </option>

                            <option value="336x280" data-width="336" data-height="280"
                                <?= $selectedDimension === '336x280' ? 'selected' : ''; ?>>
                                336 × 280
                            </option>

                        </select>

                    </div>


                    <!-- Width -->
                    <div class="fv-row">

                        <label class="form-label required">
                            <?= trans('width'); ?>
                        </label>

                        <div class="input-group">

                            <input
                                type="number"
                                name="width"
                                id="adWidth"
                                class="form-control"
                                min="1"
                                value="<?= esc($selectedWidth); ?>"
                                readonly
                                required>

                            <span class="input-group-text">
                                px
                            </span>

                        </div>

                        <?= validationError('width'); ?>

                    </div>


                    <!-- Height -->
                    <div class="fv-row">

                        <label class="form-label required">
                            <?= trans('height'); ?>
                        </label>

                        <div class="input-group">

                            <input
                                type="number"
                                name="height"
                                id="adHeight"
                                class="form-control"
                                min="1"
                                value="<?= esc($selectedHeight); ?>"
                                readonly
                                required>

                            <span class="input-group-text">
                                px
                            </span>

                        </div>

                        <?= validationError('height'); ?>

                    </div>


                    <!-- Status -->
                    <div class="fv-row">

                        <label class="form-label">
                            <?= trans('status'); ?>
                        </label>

                        <select name="status"
                            class="form-select">

                            <option value="1"
                                <?= old('status', $mobileAd->status ?? 1) == 1 ? 'selected' : ''; ?>>

                                <?= trans('active'); ?>

                            </option>

                            <option value="0"
                                <?= old('status', $mobileAd->status ?? 1) == 0 ? 'selected' : ''; ?>>

                                <?= trans('disabled'); ?>

                            </option>

                        </select>

                        <?= validationError('status'); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- RIGHT CONTENT -->
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">

        <div class="card card-flush py-4">

            <div class="card-header">

                <div class="card-title">
                    <h2><?= trans("general"); ?></h2>
                </div>

            </div>

            <div class="card-body pt-5">

                <!-- Ad Title -->
                <div class="mb-6 fv-row">

                    <label class="required form-label">
                        <?= trans('title'); ?>
                    </label>

                    <input type="text"
                        name="ad_title"
                        class="form-control"
                        value="<?= esc(old('ad_title', $mobileAd->ad_title ?? '')); ?>"
                        required>

                    <?= validationError('ad_title'); ?>

                </div>


                <!-- Slug -->
                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('slug'); ?> (<?= trans('optional'); ?>)
                    </label>

                    <input type="text"
                        name="slug"
                        class="form-control"
                        value="<?= esc(old('slug', $mobileAd->slug ?? '')); ?>"
                        >

                    <?= validationError('slug'); ?>

                </div>


                <!-- Company Name -->
                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('company_name'); ?> </label>

                    <input type="text"
                        name="company_name"
                        class="form-control"
                        value="<?= esc(old('company_name', $mobileAd->company_name ?? '')); ?>">

                    <?= validationError('company_name'); ?>

                </div>


                <!-- Button Text -->
                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('button_text'); ?> </label>

                    <input type="text"
                        name="button_text"
                        class="form-control"
                        value="<?= esc(old('button_text', $mobileAd->button_text ?? '')); ?>"
                        required>

                    <?= validationError('button_text'); ?>

                </div>


                <!-- URL -->
                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('website_url'); ?>
                    </label>

                    <input type="url"
                        name="url"
                        class="form-control"
                        value="<?= esc(old('url', $mobileAd->url ?? '')); ?>"
                        required>

                    <?= validationError('url'); ?>

                </div>


                <!-- Description -->
                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('description'); ?>
                    </label>

                    <textarea name="ad_description"
                        rows="5"
                        class="form-control"><?= esc(old('ad_description', $mobileAd->ad_description ?? '')); ?></textarea>

                    <?= validationError('ad_description'); ?>

                </div>


                <!-- Image -->
                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('image'); ?>
                    </label>

                    <input type="file"
                        name="image"
                        class="form-control"
                        accept="image/*"
                        required>

                    <?php if (!empty($mobileAd->image)): ?>

                        <div class="mt-3">

                            <img src="<?= base_url($mobileAd->image); ?>"
                                class="img-fluid rounded"
                                style="max-width:100%; max-height:250px;">

                        </div>

                    <?php endif; ?>

                    <?= validationError('image'); ?>

                </div>

            </div>

        </div>


        <!-- Submit -->
        <div class="d-flex justify-content-end">

            <button type="submit"
                class="btn btn-primary">

                <?= !empty($mobileAd)
                    ? trans("save_changes")
                    : "Add Mobile Ad"; ?>

            </button>

        </div>

    </div>

</form>
<script>
    function updateAdDimensions(select) {

        const option = select.options[select.selectedIndex];

        if (!option || !option.value) {
            document.getElementById('adWidth').value = '';
            document.getElementById('adHeight').value = '';
            return;
        }

        document.getElementById('adWidth').value =
            option.getAttribute('data-width');

        document.getElementById('adHeight').value =
            option.getAttribute('data-height');
    }

    document.addEventListener('DOMContentLoaded', function() {

        const dimensionSelect = document.getElementById('adDimension');

        if (dimensionSelect && dimensionSelect.value) {
            updateAdDimensions(dimensionSelect);
        }

    });
</script>
<?= $this->endSection(); ?>