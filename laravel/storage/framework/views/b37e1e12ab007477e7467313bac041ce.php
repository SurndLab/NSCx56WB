<?php $__env->startSection('title', '出版社列表'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;">出版社管理</h1>
    <div class="flex gap-1">
        <a href="<?php echo e(route('publishers.inactive')); ?>" class="btn btn-secondary">已停用出版社</a>
        <a href="<?php echo e(route('publishers.create')); ?>" class="btn btn-primary">+ 新增出版社</a>
    </div>
</div>
<div class="card">
    <?php if($publishers->isEmpty()): ?>
        <p class="text-muted">目前沒有出版社資料。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>名稱</th>
                    <th class="hide-mobile">ISBN 代碼</th>
                    <th class="hide-mobile">電話</th>
                    <th>聯絡人</th>
                    <th>管理員</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $publishers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><a href="<?php echo e(route('publishers.show', $pub->id)); ?>"><?php echo e($pub->publisher_name); ?></a></td>
                    <td class="hide-mobile" style="font-family:monospace;"><?php echo e($pub->publisher_isbn_code); ?></td>
                    <td class="hide-mobile"><?php echo e($pub->publisher_phone); ?></td>
                    <td><?php echo e($pub->contacts->count()); ?></td>
                    <td><?php echo e($pub->admins->count()); ?></td>
                    <td>
                        <div class="flex gap-1 flex-wrap">
                            <a href="<?php echo e(route('publishers.edit', $pub->id)); ?>" class="btn btn-warning btn-sm">編輯</a>
                            <form method="POST" action="<?php echo e(route('publishers.disable', $pub->id)); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button class="btn btn-danger btn-sm">停用</button>
                            </form>
                        </div>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/publishers/index.blade.php ENDPATH**/ ?>