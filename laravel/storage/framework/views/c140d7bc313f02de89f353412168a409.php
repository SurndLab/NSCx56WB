<?php $__env->startSection('title', $book->book_name); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;"><?php echo e($book->book_name); ?></h1>
    <div class="flex gap-1">
        <a href="<?php echo e(route('books.edit', $book->isbn13_hyphenated)); ?>" class="btn btn-warning">編輯</a>
        <a href="<?php echo e(route('books.index')); ?>" class="btn btn-secondary">返回列表</a>
    </div>
</div>
<div class="card">
    <table>
        <tr><th style="width:120px;">ISBN</th><td style="font-family:monospace;"><?php echo e($book->isbn13_hyphenated); ?></td></tr>
        <tr><th>作者</th><td><?php echo e($book->book_author); ?></td></tr>
        <tr><th>出版社</th><td><?php echo e($book->publisher->publisher_name ?? '-'); ?></td></tr>
        <tr>
            <th>狀態</th>
            <td>
                <?php if($book->is_hidden): ?>
                    <span class="badge badge-red">已隱藏</span>
                <?php else: ?>
                    <span class="badge badge-green">顯示中</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr><th>描述</th><td><?php echo nl2br(e($book->book_description)); ?></td></tr>
    </table>
</div>

<div class="card">
    <h2>書籍圖片</h2>
    <?php if($book->images->isEmpty()): ?>
        <p class="text-muted">尚未上傳圖片（使用預設圖片）。</p>
    <?php else: ?>
        <div class="image-grid">
            <?php $__currentLoopData = $book->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="img-wrapper">
                    <img src="<?php echo e(asset('storage/' . $img->image_path)); ?>" alt="圖片 <?php echo e($i + 1); ?>">
                    <?php if($i === 0): ?>
                        <span class="badge badge-green" style="position:absolute; bottom:4px; left:4px;">封面</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>操作</h2>
    <div class="flex gap-1 flex-wrap">
        <?php if($book->is_hidden): ?>
            <form method="POST" action="<?php echo e(route('books.show-book', $book->id)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-success">重新顯示</button>
            </form>
            <form method="POST" action="<?php echo e(route('books.destroy', $book->id)); ?>" onsubmit="return confirm('確定要永久刪除此書籍？此操作無法復原！')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="btn btn-danger">永久刪除</button>
            </form>
        <?php else: ?>
            <form method="POST" action="<?php echo e(route('books.hide', $book->id)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-secondary">隱藏書籍</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/books/show.blade.php ENDPATH**/ ?>