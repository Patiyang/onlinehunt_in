<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $live?->lang_id ?? $activeLang?->id ?? 1;
// $imageSavingMethod = !empty($feed) ? $feed->image_saving_method : 'url';
// $autoUpdate = !empty($feed) ? (int)$feed->auto_update : 1;
// $readMoreButton = !empty($feed) ? (int)$feed->read_more_button : 1;
// $addPostsAsDraft = !empty($feed) ? (int)$feed->add_posts_as_draft : 0;
// $generateKeywordsFromTitle = !empty($feed) ? (int)$feed->generate_keywords_from_title : 0;

// $defaultImgId = !empty($defaultImage) ? (int)$defaultImage->id : 0;
// $defaultImgUrl = '';
// if (!empty($defaultImage)) {
//     $defaultImgUrl = getStorageFileUrl($defaultImage->image_mid, $defaultImage->storage);
// }
?>

<form action="<?= $action ?>" method="post" id="form-post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
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
                        <select name="lang_id" id="languageSwitcher" class="form-select" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="false" data-hide-search="true" required>
                            <option></option>
                            <?php foreach ($activeLanguages as $language): ?>
                                <option value="<?= $language->id; ?>" <?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("category"); ?></label>
                        <?= view("admin/category/_category_selector", [
                            'selectorName'         => 'category_id',
                            'selectorLangId'       => $selectedLangId,
                            'selectorInitialValue' => !empty($live) ? $live->category_id : 0,
                            'selectorInitialData'  => !empty($categorySelectorData) ? $categorySelectorData : null,
                        ]); ?>
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
                    <label class="required form-label">Live Name</label>
                    <input type="text" name="title" class="form-control mb-2" placeholder="<?= trans('title', 'attr'); ?>" value="<?= esc(old('title', $live->title ?? '')); ?>" required>
                    <?= validationError('title'); ?>
                </div>

                <div class="mb-6 fv-row">
                    <label class="required form-label">Live URL</label>
                    <input type="text" name="url" class="form-control mb-2" placeholder="<?= trans('url', 'attr'); ?>" value="<?= esc(old('url', $live->url ?? '')); ?>" required>
                    <?= validationError('url'); ?>
                </div>


                <div class="mb-6 fv-row">
                    <label class="form-label">Live Keywords</label>
                    <input type="text" name="keywords" class="form-control mb-2" placeholder="<?= trans('keywords', 'attr'); ?>" value="<?= esc(old('keywords', $live->keywords ?? '')); ?>" required>
                    <?= validationError('keywords'); ?>
                </div>



            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                <span class="indicator-label"><?= !empty($live) ? trans("save_changes") : "Add Link"; ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>

    </div>
</form>


<?= $this->endSection(); ?>