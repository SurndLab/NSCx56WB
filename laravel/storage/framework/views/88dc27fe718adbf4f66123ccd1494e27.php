<?php $__env->startSection('title', '書籍列表'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center flex-wrap gap-1 mb-2">
    <h1 style="font-size:1.5rem;">書籍列表</h1>
    <a href="<?php echo e(route('books.create')); ?>" class="btn btn-primary">+ 新增書籍</a>
</div>

<div class="card">
    <form method="GET" action="<?php echo e(route('books.index')); ?>" class="flex gap-1 mb-2">
        <input type="text" name="query" value="<?php echo e(request('query')); ?>" placeholder="搜尋書名、作者、ISBN..." style="flex:1; padding:.5rem .75rem; border:1px solid #d1d5db; border-radius:6px;">
        <button type="submit" class="btn btn-primary">搜尋</button>
        <?php if(request('query')): ?>
            <a href="<?php echo e(route('books.index')); ?>" class="btn btn-secondary">清除</a>
        <?php endif; ?>
    </form>

    <?php if($books->isEmpty()): ?>
        <p class="text-muted">目前沒有書籍資料。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>封面</th>
                    <th>書名</th>
                    <th class="hide-mobile">作者</th>
                    <th class="hide-mobile">ISBN</th>
                    <th class="hide-mobile">出版社</th>
                    <th>狀態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <?php if($book->images->first()): ?>
                            <img src="<?php echo e(asset('storage/' . $book->images->first()->image_path)); ?>" alt="封面" style="width:50px; height:65px; object-fit:cover; border-radius:4px;">
                        <?php else: ?>
                            <div style="width:50px; height:65px; background:#e5e7eb; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:.7rem; color:#9ca3af;">無圖</div>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?php echo e(route('books.show', $book->isbn13_hyphenated)); ?>"><?php echo e($book->book_name); ?></a></td>
                    <td class="hide-mobile"><?php echo e($book->book_author); ?></td>
                    <td class="hide-mobile" style="font-family:monospace; font-size:.85rem;"><?php echo e($book->isbn13_hyphenated); ?></td>
                    <td class="hide-mobile"><?php echo e($book->publisher->publisher_name ?? '-'); ?></td>
                    <td>
                        <?php if($book->is_hidden): ?>
                            <span class="badge badge-red">已隱藏</span>
                        <?php else: ?>
                            <span class="badge badge-green">顯示中</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-1 flex-wrap">
                            <a href="<?php echo e(route('books.edit', $book->isbn13_hyphenated)); ?>" class="btn btn-warning btn-sm">編輯</a>
                            <?php if($book->is_hidden): ?>
                                <form method="POST" action="<?php echo e(route('books.show-book', $book->id)); ?>">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button class="btn btn-success btn-sm">顯示</button>
                                </form>
                                <form method="POST" action="<?php echo e(route('books.destroy', $book->id)); ?>" onsubmit="return confirm('確定要永久刪除此書籍？')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-danger btn-sm">刪除</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?php echo e(route('books.hide', $book->id)); ?>">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button class="btn btn-secondary btn-sm">隱藏</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php echo e($books->appends(request()->query())->links('vendor.pagination')); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/books/index.blade.php ENDPATH**/ ?>