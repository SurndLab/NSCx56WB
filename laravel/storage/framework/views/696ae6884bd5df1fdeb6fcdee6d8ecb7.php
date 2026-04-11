<?php $__env->startSection('title', isset($publisher) ? '編輯出版社' : '新增出版社'); ?>
<?php $__env->startSection('content'); ?>
<h1 style="font-size:1.5rem; margin-bottom:1rem;"><?php echo e(isset($publisher) ? '編輯出版社' : '新增出版社'); ?></h1>
<div class="card">
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($e); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(isset($publisher) ? route('publishers.update', $publisher->id) : route('publishers.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if(isset($publisher)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div class="form-group">
            <label for="publisher_name">出版社名稱</label>
            <input type="text" id="publisher_name" name="publisher_name" value="<?php echo e(old('publisher_name', $publisher->publisher_name ?? '')); ?>" required>
        </div>
        <div class="form-group">
            <label for="publisher_address">地址</label>
            <input type="text" id="publisher_address" name="publisher_address" value="<?php echo e(old('publisher_address', $publisher->publisher_address ?? '')); ?>" required>
        </div>
        <div class="form-group">
            <label for="publisher_phone">電話號碼</label>
            <input type="text" id="publisher_phone" name="publisher_phone" value="<?php echo e(old('publisher_phone', $publisher->publisher_phone ?? '')); ?>" required>
        </div>
        <div class="form-group">
            <label for="publisher_isbn_code">ISBN 出版社代碼</label>
            <input type="text" id="publisher_isbn_code" name="publisher_isbn_code" value="<?php echo e(old('publisher_isbn_code', $publisher->publisher_isbn_code ?? '')); ?>" required>
        </div>

        <h3 class="mb-1">聯絡人（至少一位）</h3>
        <div id="contacts-container">
            <?php
                $contacts = old('contacts', isset($publisher) ? $publisher->contacts->map(fn($c) => [
                    'contact_name' => $c->contact_name,
                    'contact_phone' => $c->contact_phone,
                    'contact_email' => $c->contact_email,
                ])->toArray() : [['contact_name' => '', 'contact_phone' => '', 'contact_email' => '']]);
            ?>
            <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="contact-row card" style="padding:.75rem; margin-bottom:.5rem; background:#f9fafb;">
                <div class="flex justify-between items-center mb-1">
                    <strong class="text-sm">聯絡人 #<?php echo e($idx + 1); ?></strong>
                    <?php if($idx > 0): ?>
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.contact-row').remove()">移除</button>
                    <?php endif; ?>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.5rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>姓名</label>
                        <input type="text" name="contacts[<?php echo e($idx); ?>][contact_name]" value="<?php echo e($contact['contact_name']); ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>電話</label>
                        <input type="text" name="contacts[<?php echo e($idx); ?>][contact_phone]" value="<?php echo e($contact['contact_phone']); ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Email</label>
                        <input type="email" name="contacts[<?php echo e($idx); ?>][contact_email]" value="<?php echo e($contact['contact_email']); ?>" required>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button type="button" class="btn btn-secondary btn-sm mb-2" onclick="addContact()">+ 新增聯絡人</button>

        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary"><?php echo e(isset($publisher) ? '儲存變更' : '建立出版社'); ?></button>
            <a href="<?php echo e(route('publishers.index')); ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>
</div>

<script>
let contactIdx = <?php echo e(count($contacts)); ?>;
function addContact() {
    const container = document.getElementById('contacts-container');
    const html = `<div class="contact-row card" style="padding:.75rem; margin-bottom:.5rem; background:#f9fafb;">
        <div class="flex justify-between items-center mb-1">
            <strong class="text-sm">聯絡人 #${contactIdx + 1}</strong>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.contact-row').remove()">移除</button>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.5rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label>姓名</label>
                <input type="text" name="contacts[${contactIdx}][contact_name]" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>電話</label>
                <input type="text" name="contacts[${contactIdx}][contact_phone]" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Email</label>
                <input type="email" name="contacts[${contactIdx}][contact_email]" required>
            </div>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    contactIdx++;
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/publishers/form.blade.php ENDPATH**/ ?>