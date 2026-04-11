<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '書籍管理系統')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #fff; color: #333; line-height: 1.6; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { max-width: 900px; margin: 0 auto; padding: 1.5rem 1rem; }
        h1 { font-size: 1.75rem; margin-bottom: 1rem; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: .5rem .75rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { font-weight: 600; background: #f9fafb; }
        .btn { display: inline-block; padding: .5rem 1rem; border-radius: 6px; font-size: .875rem; border: none; cursor: pointer; text-decoration: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: .25rem; }
        .form-group textarea { width: 100%; padding: .5rem .75rem; border: 1px solid #d1d5db; border-radius: 6px; resize: vertical; min-height: 120px; font-size: .95rem; }
        .alert { padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .text-muted { color: #6b7280; }
        .image-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin: 1rem 0; }
        .image-gallery img { width: 100%; border-radius: 6px; border: 1px solid #e5e7eb; }
        .book-card { display: flex; gap: 1rem; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: .75rem; }
        .book-card img { width: 80px; height: 110px; object-fit: cover; border-radius: 4px; flex-shrink: 0; }
        .book-card .no-img { width: 80px; height: 110px; background: #e5e7eb; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: .75rem; color: #9ca3af; flex-shrink: 0; }
        .book-card .info { flex: 1; }
        .book-card .info h3 { margin-bottom: .25rem; font-size: 1rem; }
        .book-card .info p { font-size: .875rem; color: #6b7280; margin-bottom: .25rem; }
        /* ISBN validation */
        .isbn-result { padding: .5rem .75rem; border-radius: 4px; margin-bottom: .25rem; font-size: .9rem; }
        .isbn-valid { background: #dcfce7; color: #166534; }
        .isbn-invalid { background: #fee2e2; color: #991b1b; }
        .all-valid-banner { background: #dcfce7; border: 2px solid #16a34a; border-radius: 8px; padding: 1rem; text-align: center; margin-bottom: 1rem; font-size: 1.1rem; color: #166534; }
        .all-valid-banner .check { font-size: 2rem; color: #16a34a; }
        /* Responsive */
        @media (max-width: 768px) {
            .container { padding: 1rem .75rem; }
            .book-card { flex-direction: column; }
            .book-card img, .book-card .no-img { width: 100%; height: 200px; }
            .image-gallery { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>
