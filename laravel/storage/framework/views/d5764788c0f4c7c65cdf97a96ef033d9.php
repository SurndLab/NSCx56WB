<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', '書籍管理系統'); ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; color: #333; line-height: 1.6; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 1rem; }
        /* Navbar */
        .navbar { background: #1e3a5f; color: #fff; padding: .75rem 0; }
        .navbar .container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem; }
        .navbar a { color: #cbd5e1; }
        .navbar a:hover { color: #fff; text-decoration: none; }
        .navbar .brand { font-size: 1.25rem; font-weight: 700; color: #fff; }
        .nav-links { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .nav-links form { display: inline; }
        .nav-links button { background: none; border: 1px solid #cbd5e1; color: #cbd5e1; padding: .25rem .75rem; border-radius: 4px; cursor: pointer; font-size: .875rem; }
        .nav-links button:hover { background: rgba(255,255,255,.1); color: #fff; }
        /* Main */
        .main { padding: 1.5rem 0; }
        /* Cards & Panels */
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 1.5rem; margin-bottom: 1rem; }
        .card h2 { margin-bottom: 1rem; font-size: 1.25rem; }
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .6rem .75rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; font-size: .875rem; }
        tr:hover { background: #f9fafb; }
        /* Forms */
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: .25rem; font-size: .875rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: .95rem; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
        .form-group .error { color: #dc2626; font-size: .8rem; margin-top: .25rem; }
        /* Buttons */
        .btn { display: inline-block; padding: .5rem 1rem; border-radius: 6px; font-size: .875rem; font-weight: 500; cursor: pointer; border: none; text-decoration: none; text-align: center; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; text-decoration: none; }
        .btn-success { background: #16a34a; color: #fff; }
        .btn-success:hover { background: #15803d; text-decoration: none; }
        .btn-warning { background: #f59e0b; color: #fff; }
        .btn-warning:hover { background: #d97706; text-decoration: none; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; text-decoration: none; }
        .btn-secondary { background: #6b7280; color: #fff; }
        .btn-secondary:hover { background: #4b5563; text-decoration: none; }
        .btn-sm { padding: .3rem .6rem; font-size: .8rem; }
        /* Alerts */
        .alert { padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: .9rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        /* Badge */
        .badge { display: inline-block; padding: .15rem .5rem; border-radius: 9999px; font-size: .75rem; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        /* Pagination */
        .pagination { display: flex; gap: .25rem; justify-content: center; margin-top: 1rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .4rem .75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: .85rem; }
        .pagination .active { background: #2563eb; color: #fff; border-color: #2563eb; }
        .pagination a:hover { background: #eff6ff; text-decoration: none; }
        /* Flex helpers */
        .flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .gap-1 { gap: .5rem; }
        .gap-2 { gap: 1rem; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .mb-1 { margin-bottom: .5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mt-1 { margin-top: .5rem; }
        .mt-2 { margin-top: 1rem; }
        .text-sm { font-size: .875rem; }
        .text-muted { color: #6b7280; }
        /* Image grid */
        .image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
        .image-grid img { width: 100%; height: 150px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb; }
        .image-grid .img-wrapper { position: relative; }
        .image-grid .img-remove { position: absolute; top: 4px; right: 4px; background: #dc2626; color: #fff; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: .8rem; line-height: 24px; text-align: center; }
        /* Responsive */
        @media (max-width: 768px) {
            .container { padding: 0 .75rem; }
            table { font-size: .85rem; }
            th, td { padding: .4rem .5rem; }
            .hide-mobile { display: none; }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo e(route('books.index')); ?>" class="brand">📚 書籍管理系統</a>
            <div class="nav-links">
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('books.index')); ?>">書籍列表</a>
                    <?php if(auth()->user()->isSuperAdmin()): ?>
                        <a href="<?php echo e(route('publishers.index')); ?>">出版社管理</a>
                    <?php endif; ?>
                    <span class="text-sm"><?php echo e(auth()->user()->display_name); ?></span>
                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit">登出</button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>">登入</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="main">
        <div class="container">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</body>
</html>
<?php /**PATH /Users/peakerlee/surndLab/NSCx56WB/laravel/resources/views/layouts/app.blade.php ENDPATH**/ ?>