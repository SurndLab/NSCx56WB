<?php $__env->startSection('title', $publisher->publisher_name); ?>
<?php $__env->startSection('content'); ?>
<h1><?php echo e($publisher->publisher_name); ?></h1>

<div class="card">
    <h2 style="margin-bottom:.75rem; font-size:1.1rem;">出版社資訊</h2>
    <table>
        <tr><th style="width:120px;">地址</th><td><?php echo e($publisher->publisher_address); ?></td></tr>
        <tr><th>電話</th><td><?php echo e($publisher->publisher_phone); ?></td></tr>
        <tr><th>ISBN 代碼</th><td style="font-family:monospace;"><?php echo e($publisher->publisher_isbn_code); ?></td></tr>
    </table>
</div>

<div class="card">
    <h2 style="margin-bottom:.75rem; font-size:1.1rem;">聯絡人</h2>
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

<h2 style="margin:1.5rem 0 .75rem; font-size:1.25rem;">書籍列表</h2>
<?php if($books->isEmpty()): ?>
    <p class="text-muted">此出版社目前沒有可顯示的書籍。</p>
<?php else: ?>
    <?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="book-card">
        <?php if($book->images->first()): ?>
            <img src="<?php echo e(asset('storage/' . $book->images->first()->image_path)); ?>" alt="<?php echo e($book->book_name); ?>">
        <?php else: ?>
            <div class="no-img">無圖片</div>
        <?php endif; ?>
        <div class="info">
            <h3><a href="<?php echo e(route('public.book.show', $book->isbn13_digits)); ?>"><?php echo e($book->book_name); ?></a></h3>
            <p><strong>作者：</strong><?php echo e($book->book_author); ?></p>
            <p><strong>ISBN：</strong><span style="font-family:monospace;"><?php echo e($book->isbn13_hyphenated); ?></span></p>
            <p><?php echo e(Str::limit($book->book_description, 120)); ?></p>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/public/publisher.blade.php ENDPATH**/ ?>