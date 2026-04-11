<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookImage;
use App\Models\Publisher;
use App\Models\PublisherContact;
use App\Models\User;
use App\Services\IsbnService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'publisher_id' => null,
                'display_name' => 'Super Admin',
                'role' => User::ROLE_SUPER_ADMIN,
                'password' => Hash::make('1234'),
                'is_active' => true,
            ]
        );

        $isbnService = new IsbnService();

        // Publisher 1: Worldskills 出版社
        $pub1 = Publisher::updateOrCreate(
            ['publisher_isbn_code' => '5678'],
            [
                'publisher_name' => 'Worldskills 出版社',
                'publisher_address' => '台北市 11568 南港區經貿二路 2 號',
                'publisher_phone' => '+886 2 1234 5678',
                'is_active' => true,
            ]
        );
        $pub1->contacts()->delete();
        $pub1->contacts()->createMany([
            ['contact_name' => 'John Doe', 'contact_phone' => '+886 912 345 678', 'contact_email' => 'john.doe@example.com'],
            ['contact_name' => 'Jane Smith', 'contact_phone' => '+886 923 456 789', 'contact_email' => 'jane.smith@example.com'],
        ]);

        // Publisher 1 admin
        User::updateOrCreate(
            ['username' => 'ws_admin'],
            [
                'publisher_id' => $pub1->id,
                'display_name' => 'WS管理員',
                'role' => User::ROLE_PUBLISHER_ADMIN,
                'password' => Hash::make('1234'),
                'is_active' => true,
            ]
        );

        // Publisher 2: 台灣技能出版社
        $pub2 = Publisher::updateOrCreate(
            ['publisher_isbn_code' => '181'],
            [
                'publisher_name' => '台灣技能出版社',
                'publisher_address' => '台北市大安區忠孝東路三段 1 號',
                'publisher_phone' => '+886 2 8765 4321',
                'is_active' => true,
            ]
        );
        $pub2->contacts()->delete();
        $pub2->contacts()->createMany([
            ['contact_name' => '王小明', 'contact_phone' => '+886 933 111 222', 'contact_email' => 'wang@example.com'],
        ]);

        User::updateOrCreate(
            ['username' => 'tw_admin'],
            [
                'publisher_id' => $pub2->id,
                'display_name' => '台灣技能管理員',
                'role' => User::ROLE_PUBLISHER_ADMIN,
                'password' => Hash::make('1234'),
                'is_active' => true,
            ]
        );

        // Publisher 3 (inactive): 停用測試出版社
        $pub3 = Publisher::updateOrCreate(
            ['publisher_isbn_code' => '999'],
            [
                'publisher_name' => '停用測試出版社',
                'publisher_address' => '高雄市前鎮區中山二路 100 號',
                'publisher_phone' => '+886 7 1234 5678',
                'is_active' => false,
            ]
        );
        $pub3->contacts()->delete();
        $pub3->contacts()->createMany([
            ['contact_name' => '李大華', 'contact_phone' => '+886 955 666 777', 'contact_email' => 'li@example.com'],
        ]);

        // Books for Publisher 2 (isbn_code = 181, group = 986)
        $booksData = [
            ['name' => 'Organic Apple Juice', 'desc' => "Our organic apple juice is pressed from 100% fresh organic apples.\nWith no added sugars or preservatives.", 'author' => 'Green Orchard', 'isbn12' => '978986181728'],
            ['name' => 'Web Development Basics', 'desc' => '從 HTML、CSS 到 JavaScript，一步步帶你進入網頁開發的世界。', 'author' => '陳建宏', 'isbn12' => '978986181001'],
            ['name' => 'Laravel 實戰手冊', 'desc' => '以實際專案為導向，深入淺出學習 Laravel 框架的核心功能與最佳實踐。', 'author' => '林志玲', 'isbn12' => '978986181018'],
            ['name' => 'Python 資料分析', 'desc' => '使用 Python 進行資料清洗、視覺化與機器學習入門。', 'author' => '張偉', 'isbn12' => '978986181025'],
        ];

        foreach ($booksData as $bd) {
            $check = $isbnService->computeCheckDigitFrom12($bd['isbn12']);
            $isbn13 = $bd['isbn12'] . $check;
            $hyphenated = $isbnService->hyphenate($isbn13, '181');

            Book::updateOrCreate(
                ['isbn13_digits' => $isbn13],
                [
                    'publisher_id' => $pub2->id,
                    'book_name' => $bd['name'],
                    'book_description' => $bd['desc'],
                    'book_author' => $bd['author'],
                    'isbn13_hyphenated' => $hyphenated,
                    'isbn_prefix' => '978',
                    'registration_group' => '986',
                    'publisher_code' => '181',
                    'publication_code' => substr($isbn13, 9, 3),
                    'check_digit' => (string) $check,
                    'is_hidden' => false,
                ]
            );
        }

        // Books for Publisher 1 (isbn_code = 5678, group = 986)
        $wsBooksData = [
            ['name' => 'Skills Competition Guide', 'desc' => 'A comprehensive guide to preparing for national and international skills competitions.', 'author' => 'WorldSkills Team', 'isbn12' => '978986567801'],
            ['name' => '技能競賽實務', 'desc' => '涵蓋網頁設計、程式設計等競賽項目的考前準備與實戰技巧。', 'author' => '競賽團隊', 'isbn12' => '978986567818'],
        ];

        foreach ($wsBooksData as $bd) {
            $check = $isbnService->computeCheckDigitFrom12($bd['isbn12']);
            $isbn13 = $bd['isbn12'] . $check;
            $hyphenated = $isbnService->hyphenate($isbn13, '5678');

            Book::updateOrCreate(
                ['isbn13_digits' => $isbn13],
                [
                    'publisher_id' => $pub1->id,
                    'book_name' => $bd['name'],
                    'book_description' => $bd['desc'],
                    'book_author' => $bd['author'],
                    'isbn13_hyphenated' => $hyphenated,
                    'isbn_prefix' => '978',
                    'registration_group' => '986',
                    'publisher_code' => '5678',
                    'publication_code' => substr($isbn13, 10, 2),
                    'check_digit' => (string) $check,
                    'is_hidden' => false,
                ]
            );
        }

        // One hidden book
        $hiddenIsbn12 = '978986181032';
        $hiddenCheck = $isbnService->computeCheckDigitFrom12($hiddenIsbn12);
        $hiddenIsbn13 = $hiddenIsbn12 . $hiddenCheck;
        $hiddenHyphenated = $isbnService->hyphenate($hiddenIsbn13, '181');

        Book::updateOrCreate(
            ['isbn13_digits' => $hiddenIsbn13],
            [
                'publisher_id' => $pub2->id,
                'book_name' => '已隱藏的書籍',
                'book_description' => '此書籍已被隱藏，不應顯示在公開頁面。',
                'book_author' => '測試作者',
                'isbn13_hyphenated' => $hiddenHyphenated,
                'isbn_prefix' => '978',
                'registration_group' => '986',
                'publisher_code' => '181',
                'publication_code' => substr($hiddenIsbn13, 9, 3),
                'check_digit' => (string) $hiddenCheck,
                'is_hidden' => true,
            ]
        );
    }
}
