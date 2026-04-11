<?php $__env->startSection('title', '編輯書籍 - ' . $book->book_name); ?>
<?php $__env->startSection('content'); ?>
<h1 style="font-size:1.5rem; margin-bottom:1rem;">編輯書籍</h1>
<div class="card">
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($e); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('books.update', $book->id)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="form-group">
            <label>ISBN</label>
            <input type="text" value="<?php echo e($book->isbn13_hyphenated); ?>" disabled style="background:#f3f4f6;">
        </div>
        <div class="form-group">
            <label>出版社</label>
            <input type="text" value="<?php echo e($book->publisher->publisher_name ?? '-'); ?>" disabled style="background:#f3f4f6;">
        </div>
        <div class="form-group">
            <label for="book_name">書籍名稱</label>
            <input type="text" id="book_name" name="book_name" value="<?php echo e(old('book_name', $book->book_name)); ?>" required>
        </div>
        <div class="form-group">
            <label for="book_description">描述</label>
            <textarea id="book_description" name="book_description" rows="4" required><?php echo e(old('book_description', $book->book_description)); ?></textarea>
        </div>
        <div class="form-group">
            <label for="book_author">作者</label>
            <input type="text" id="book_author" name="book_author" value="<?php echo e(old('book_author', $book->book_author)); ?>" required>
        </div>

        <h3 class="mb-1">現有圖片</h3>
        <?php if($book->images->isEmpty()): ?>
            <p class="text-muted mb-2">尚未上傳圖片。</p>
        <?php else: ?>
            <div class="image-grid mb-2">
                <?php $__currentLoopData = $book->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="img-wrapper">
                        <img src="<?php echo e(asset('storage/' . $img->image_path)); ?>" alt="圖片 <?php echo e($i + 1); ?>">
                        <label style="position:absolute; bottom:4px; left:4px;">
                            <input type="checkbox" name="remove_images[]" value="<?php echo e($img->id); ?>"> 移除
                        </label>
                        <?php if($i === 0): ?>
                            <span class="badge badge-green" style="position:absolute; top:4px; left:4px;">封面</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="images">上傳新圖片（可多選）</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
        </div>

        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">儲存變更</button>
            <a href="<?php echo e(route('books.show', $book->isbn13_hyphenated)); ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/books/edit.blade.php ENDPATH**/ ?>