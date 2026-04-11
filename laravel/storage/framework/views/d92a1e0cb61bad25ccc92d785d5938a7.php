<?php $__env->startSection('title', $publisher->publisher_name); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;"><?php echo e($publisher->publisher_name); ?></h1>
    <div class="flex gap-1">
        <a href="<?php echo e(route('publishers.edit', $publisher->id)); ?>" class="btn btn-warning">編輯</a>
        <a href="<?php echo e(route('publishers.index')); ?>" class="btn btn-secondary">返回列表</a>
    </div>
</div>

<div class="card">
    <h2>出版社資訊</h2>
    <table>
        <tr><th style="width:140px;">ISBN 代碼</th><td style="font-family:monospace;"><?php echo e($publisher->publisher_isbn_code); ?></td></tr>
        <tr><th>地址</th><td><?php echo e($publisher->publisher_address); ?></td></tr>
        <tr><th>電話</th><td><?php echo e($publisher->publisher_phone); ?></td></tr>
        <tr>
            <th>狀態</th>
            <td>
                <?php if($publisher->is_active): ?>
                    <span class="badge badge-green">啟用中</span>
                <?php else: ?>
                    <span class="badge badge-red">已停用</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<div class="card">
    <h2>聯絡人</h2>
    <table>
        <thead><tr><th>姓名</th><th>電話</th><th>Email</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $publisher->contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($c->contact_name); ?></td>
                <td><?php echo e($c->contact_phone); ?></td>
                <td><?php echo e($c->contact_email); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<div class="card">
    <div class="flex justify-between items-center flex-wrap gap-1 mb-1">
        <h2>出版社管理員</h2>
        <a href="<?php echo e(route('publisher-admins.create', $publisher->id)); ?>" class="btn btn-primary btn-sm">+ 新增管理員</a>
    </div>
    <?php if($publisher->admins->isEmpty()): ?>
        <p class="text-muted">尚未設定管理員。</p>
    <?php else: ?>
        <table>
            <thead><tr><th>帳號</th><th>姓名</th><th>狀態</th><th>操作</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $publisher->admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($admin->username); ?></td>
                    <td><?php echo e($admin->display_name); ?></td>
                    <td>
                        <?php if($admin->is_active): ?>
                            <span class="badge badge-green">啟用</span>
                        <?php else: ?>
                            <span class="badge badge-red">停用</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-1">
                            <a href="<?php echo e(route('publisher-admins.edit', [$publisher->id, $admin->id])); ?>" class="btn btn-warning btn-sm">編輯</a>
                            <form method="POST" action="<?php echo e(route('publisher-admins.destroy', [$publisher->id, $admin->id])); ?>" onsubmit="return confirm('確定刪除此管理員？')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-danger btn-sm">刪除</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>所屬書籍</h2>
    <?php $pubBooks = $publisher->books()->with('images')->get(); ?>
    <?php if($pubBooks->isEmpty()): ?>
        <p class="text-muted">尚無書籍。</p>
    <?php else: ?>
        <table>
            <thead><tr><th>書名</th><th class="hide-mobile">ISBN</th><th class="hide-mobile">作者</th><th>狀態</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $pubBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><a href="<?php echo e(route('books.show', $book->isbn13_hyphenated)); ?>"><?php echo e($book->book_name); ?></a></td>
                    <td class="hide-mobile" style="font-family:monospace;"><?php echo e($book->isbn13_hyphenated); ?></td>
                    <td class="hide-mobile"><?php echo e($book->book_author); ?></td>
                    <td>
                        <?php if($book->is_hidden): ?>
                            <span class="badge badge-red">隱藏</span>
                        <?php else: ?>
                            <span class="badge badge-green">顯示</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/publishers/show.blade.php ENDPATH**/ ?>