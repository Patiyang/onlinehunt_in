<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>
<?php
$selectedLangId = old('lang_id') ?? $publication?->lang_id ?? $activeLang?->id ?? 1;
?>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data"
    id="form-post"
    class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

    <?= csrf_field(); ?>

    <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">

        <div class="card card-flush py-4">

            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("settings"); ?></h2>
                </div>
            </div>

            <div class="card-body pt-5">

                <div class="d-flex flex-column gap-8">

                    <div class="fv-row">
                        <label class="form-label required"><?= trans("language"); ?></label>

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

                    </div>

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

                    <div class="fv-row ">

                        <label class="required form-label">
                            <!-- Publication Type -->
                            <?= trans('type'); ?>

                        </label>

                        <select name="publication_type"
                            class="form-select"
                            required>

                            <?php

                            $types = [
                                'newspaper' => 'Daily newspaper',
                                'weekly' => 'Weekly',
                                'fortnightly' => 'Fortnightly',
                                'monthly' => 'Monthly',
                                'magazine' => 'Magazine'
                            ];

                            $selected = old('publication_type', $publication->publication_type ?? 'newspaper');

                            foreach ($types as $key => $label): ?>

                                <option value="<?= $key; ?>"
                                    <?= $selected == $key ? 'selected' : ''; ?>>

                                    <?= trans($key); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="fv-row">

                        <label class="form-label">
                            <!-- Sort Order -->
                            <?= trans('sort_order'); ?>
                        </label>

                        <input type="number"
                            name="sort_order"
                            class="form-control"
                            value="<?= old('sort_order', $publication->sort_order ?? 0); ?>">

                    </div>

                    <div class="fv-row">

                        <label class="form-label">
                            <?= trans('status'); ?>

                        </label>

                        <select name="status" class="form-select">

                            <option value="1"
                                <?= old('status', $publication->status ?? 1) == 1 ? 'selected' : ''; ?>>
                                <?= trans('active'); ?>

                            </option>

                            <option value="0"
                                <?= old('status', $publication->status ?? 1) == 0 ? 'selected' : ''; ?>>
                                <?= trans('disabled'); ?>

                            </option>

                        </select>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">

        <div class="card card-flush py-4">

            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("general"); ?></h2>
                </div>
            </div>

            <div class="card-body pt-5">

                <div class="mb-6 fv-row">

                    <label class="required form-label">
                        <?= trans('title'); ?>

                    </label>

                    <input type="text"
                        name="title"
                        class="form-control"
                        value="<?= esc(old('title', $publication->title ?? '')); ?>"
                        required>

                    <?= validationError('title'); ?>

                </div>

                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('slug'); ?>

                    </label>

                    <input type="text"
                        name="slug"
                        class="form-control"
                        value="<?= esc(old('slug', $publication->slug ?? '')); ?>"
                        required>

                    <?= validationError('slug'); ?>

                </div>

                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('website_url'); ?>
                    </label>

                    <input type="url"
                        name="website_url"
                        class="form-control"
                        value="<?= esc(old('website_url', $publication->website_url ?? '')); ?>">

                    <?= validationError('website_url'); ?>

                </div>

                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('description'); ?>

                    </label>

                    <textarea name="description"
                        rows="5"
                        class="form-control"><?= esc(old('description', $publication->description ?? '')); ?></textarea>

                    <?= validationError('description'); ?>

                </div>

                <div class="mb-6 fv-row">

                    <label class="form-label">
                        <?= trans('keywords'); ?>

                    </label>

                    <input type="text"
                        name="keywords"
                        class="form-control"
                        value="<?= esc(old('keywords', $publication->keywords ?? '')); ?>">

                    <?= validationError('keywords'); ?>

                </div>

                <div class="mb-6 fv-row">

                    <label class="form-label">
                        Publication Logo
                    </label>

                    <input type="file"
                        name="logo"
                        class="form-control"
                        accept="image/*">

                    <?php if (!empty($publication->logo)): ?>

                        <div class="mt-3">

                            <img src="<?= base_url($publication->logo); ?>"
                                class="img-fluid rounded"
                                style="max-height:80px;">

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="d-flex justify-content-end">

            <button type="submit"
                class="btn btn-primary">

                <?= !empty($publication)
                    ? trans("save_changes")
                    : "Add Publication"; ?>

            </button>

        </div>

    </div>

</form>
<?= $this->endSection(); ?>