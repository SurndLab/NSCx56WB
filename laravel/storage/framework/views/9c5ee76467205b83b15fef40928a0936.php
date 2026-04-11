<?php $__env->startSection('title', isset($admin) ? '編輯出版社管理員' : '新增出版社管理員'); ?>
<?php $__env->startSection('content'); ?>
<h1 style="font-size:1.5rem; margin-bottom:1rem;"><?php echo e(isset($admin) ? '編輯管理員' : '新增管理員'); ?> — <?php echo e($publisher->publisher_name); ?></h1>
<div class="card">
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($e); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(isset($admin) ? route('publisher-admins.update', [$publisher->id, $admin->id]) : route('publisher-admins.store', $publisher->id)); ?>">
        <?php echo csrf_field(); ?>
        <?php if(isset($admin)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div class="form-group">
            <label for="username">帳號</label>
            <input type="text" id="username" name="username" value="<?php echo e(old('username', $admin->username ?? '')); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">密碼<?php echo e(isset($admin) ? '（留空則不更改）' : ''); ?></label>
            <input type="password" id="password" name="password" <?php echo e(isset($admin) ? '' : 'required'); ?>>
        </div>
        <div class="form-group">
            <label for="display_name">姓名</label>
            <input type="text" id="display_name" name="display_name" value="<?php echo e(old('display_name', $admin->display_name ?? '')); ?>" required>
        </div>
        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary"><?php echo e(isset($admin) ? '儲存變更' : '建立管理員'); ?></button>
            <a href="<?php echo e(route('publishers.show', $publisher->id)); ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/publisher-admins/form.blade.php ENDPATH**/ ?>