<?php $__env->startSection('title', '新增書籍'); ?>
<?php $__env->startSection('content'); ?>
<h1 style="font-size:1.5rem; margin-bottom:1rem;">新增書籍</h1>
<div class="card">
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($e); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('books.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="publisher_id">出版社</label>
            <select id="publisher_id" name="publisher_id" required>
                <option value="">-- 選擇出版社 --</option>
                <?php $__currentLoopData = $publishers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($pub->id); ?>" <?php echo e(old('publisher_id') == $pub->id ? 'selected' : ''); ?>>
                        <?php echo e($pub->publisher_name); ?> (代碼: <?php echo e($pub->publisher_isbn_code); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="book_name">書籍名稱</label>
            <input type="text" id="book_name" name="book_name" value="<?php echo e(old('book_name')); ?>" required>
        </div>
        <div class="form-group">
            <label for="book_description">描述</label>
            <textarea id="book_description" name="book_description" rows="4" required><?php echo e(old('book_description')); ?></textarea>
        </div>
        <div class="form-group">
            <label for="book_author">作者</label>
            <input type="text" id="book_author" name="book_author" value="<?php echo e(old('book_author')); ?>" required>
        </div>
        <div class="form-group">
            <label for="isbn_12">ISBN 前 12 位</label>
            <input type="text" id="isbn_12" name="isbn_12" value="<?php echo e(old('isbn_12')); ?>" placeholder="例: 978-986-181-728" required>
            <div class="text-sm text-muted mt-1">系統將自動計算校驗碼。出版社代碼需與所選出版社一致。</div>
        </div>
        <div class="form-group">
            <label for="images">書籍圖片（可多選）</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
            <div class="text-sm text-muted mt-1">第一張圖片將作為封面。</div>
        </div>
        <div class="flex gap-1">
            <button type="submit" class="btn btn-primary">建立書籍</button>
            <a href="<?php echo e(route('books.index')); ?>" class="btn btn-secondary">取消</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/books/create.blade.php ENDPATH**/ ?>