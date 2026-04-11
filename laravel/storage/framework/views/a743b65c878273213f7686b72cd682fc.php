<?php $__env->startSection('title', $book->book_name); ?>
<?php $__env->startSection('content'); ?>
<h1><?php echo e($book->book_name); ?></h1>

<div class="card">
    <?php if($book->images->isNotEmpty()): ?>
        <div class="image-gallery">
            <?php $__currentLoopData = $book->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <img src="<?php echo e(asset('storage/' . $img->image_path)); ?>" alt="<?php echo e($book->book_name); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <table>
        <tr><th style="width:100px;">作者</th><td><?php echo e($book->book_author); ?></td></tr>
        <tr><th>ISBN</th><td style="font-family:monospace;"><?php echo e($book->isbn13_hyphenated); ?></td></tr>
        <tr>
            <th>出版社</th>
            <td><a href="<?php echo e(route('public.publisher.show', $book->publisher_id)); ?>"><?php echo e($book->publisher->publisher_name); ?></a></td>
        </tr>
    </table>

    <div style="margin-top:1rem;">
        <h3 style="margin-bottom:.5rem;">書籍描述</h3>
        <p><?php echo nl2br(e($book->book_description)); ?></p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/public/book.blade.php ENDPATH**/ ?>