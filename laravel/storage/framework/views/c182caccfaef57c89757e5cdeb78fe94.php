<?php $__env->startSection('title', '登入 - 書籍管理系統'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width: 400px; margin: 3rem auto;">
    <div class="card">
        <h2 style="text-align: center;">管理員登入</h2>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($e); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('login.submit')); ?>">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="username">帳號</label>
                <input type="text" id="username" name="username" value="<?php echo e(old('username')); ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">密碼</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">登入</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/auth/login.blade.php ENDPATH**/ ?>