<?php $__env->startSection('title', '已停用出版社'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;">已停用出版社</h1>
    <a href="<?php echo e(route('publishers.index')); ?>" class="btn btn-secondary">← 返回出版社列表</a>
</div>
<div class="card">
    <?php if($publishers->isEmpty()): ?>
        <p class="text-muted">目前沒有已停用的出版社。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>名稱</th>
                    <th class="hide-mobile">ISBN 代碼</th>
                    <th class="hide-mobile">電話</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $publishers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($pub->publisher_name); ?></td>
                    <td class="hide-mobile" style="font-family:monospace;"><?php echo e($pub->publisher_isbn_code); ?></td>
                    <td class="hide-mobile"><?php echo e($pub->publisher_phone); ?></td>
                    <td>
                        <form method="POST" action="<?php echo e(route('publishers.enable', $pub->id)); ?>">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button class="btn btn-success btn-sm">重新啟用</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php echo e($publishers->links('vendor.pagination')); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/publishers/inactive.blade.php ENDPATH**/ ?>