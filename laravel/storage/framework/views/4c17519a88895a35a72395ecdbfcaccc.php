<?php $__env->startSection('title', 'ISBN 批量驗證'); ?>
<?php $__env->startSection('content'); ?>
<h1>ISBN 批量驗證</h1>

<?php if(isset($allValid) && $allValid): ?>
    <div class="all-valid-banner">
        <div class="check">✅</div>
        All valid
    </div>
<?php endif; ?>

<?php if(isset($results)): ?>
    <div class="card" style="margin-bottom:1rem;">
        <h2 style="margin-bottom:.75rem; font-size:1.1rem;">驗證結果</h2>
        <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="isbn-result <?php echo e($r['valid'] ? 'isbn-valid' : 'isbn-invalid'); ?>">
                <strong><?php echo e($r['input']); ?></strong>
                → <?php echo e($r['valid'] ? '✅ 有效' : '❌ 無效'); ?>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="<?php echo e(route('public.isbn.validate.submit')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="isbns">請輸入 ISBN（每行一個，連字號可省略）</label>
            <textarea id="isbns" name="isbns" rows="8" placeholder="978-986-181-728-6&#10;9789861817286&#10;978-957-123-456-7"><?php echo e(old('isbns', $inputIsbns ?? '')); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">驗證</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/public/isbn-validate.blade.php ENDPATH**/ ?>