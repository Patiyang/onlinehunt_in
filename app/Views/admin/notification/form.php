<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<form action="<?= $action; ?>" method="post"
    class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 mb-5">

    <?= csrf_field(); ?>

    <div class="d-flex flex-column flex-row-fluid gap-7">

        <div class="card card-flush py-4">

            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans('notifications'); ?></h2>
                </div>
            </div>

            <div class="card-body pt-5">

                <!-- Title -->
                <div class="mb-6 fv-row">

                    <label class="required form-label">
                        <?= trans('title'); ?>
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        placeholder="<?= trans('title', 'attr'); ?>"
                        value="<?= esc(old('title', '')); ?>"
                        required>

                    <?= validationError('title'); ?>

                </div>


                <!-- Message -->
                <div class="mb-6 fv-row">

                    <label class="required form-label">
                        <?= trans('message'); ?>
                    </label>

                    <textarea
                        name="message"
                        class="form-control"
                        rows="6"
                        placeholder="<?= trans('message', 'attr'); ?>"
                        required><?= esc(old('message', '')); ?></textarea>

                    <?= validationError('message'); ?>

                </div>


                <!-- Image -->
                <div class="mb-6 fv-row">

                    <label class="form-label mb-4">
                        <?= trans('image'); ?>
                    </label>

                    <div class="d-flex">

                        <div class="image-input image-input-outline">

                            <div class="image-input-wrapper image-input-placeholder w-200px h-200px">

                                <img
                                    src=""
                                    id="notification-image-preview"
                                    class="w-100 h-100 object-fit-cover">

                            </div>

                            <!-- Select image -->
                            <button
                                type="button"
                                class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                data-bs-toggle="modal"
                                data-bs-target="#fileManagerModal"
                                data-modal-title="<?= trans('image', 'attr'); ?>"
                                data-source="image"
                                data-select-mode="single"
                                data-view-mode="image"
                                data-allowed-extensions="<?= esc(getAllowedExtensionsBySource('image')); ?>"
                                data-target-input="#input-notification-image"
                                data-target-preview="#notification-image-preview"
                                data-kt-image-input-action="change"
                                data-bs-dismiss="click">

                                <i class="ki-duotone ki-pencil fs-6">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>

                            </button>


                            <!-- Remove image -->
                            <button
                                type="button"
                                class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                data-kt-image-input-action="remove"
                                data-bs-dismiss="click"
                                onclick="
                                    $('#input-notification-image').val('');
                                    $('#notification-image-preview').attr('src', '');
                                    $(this).hide();
                                ">

                                <i class="ki-outline ki-cross fs-3"></i>

                            </button>

                        </div>

                    </div>


                    <input
                        type="hidden"
                        name="image_id"
                        id="input-notification-image"
                        value="<?= old('image_id', ''); ?>">

                </div>

            </div>

        </div>


        <!-- Submit -->
        <div class="d-flex justify-content-end">

            <button
                type="submit"
                class="btn btn-primary"
                data-kt-indicator="off">

                <span class="indicator-label">
                    <?= trans('send_notification'); ?>
                </span>

                <span class="indicator-progress">
                    <?= trans('submitting'); ?>

                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>

            </button>

        </div>

    </div>

</form>


<?= view("admin/media/file_manager", ['isModal' => true]); ?>

<?= $this->endSection(); ?>