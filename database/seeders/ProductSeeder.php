<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /* --------------------------------- */
        /*  Create Categories                */
        /* --------------------------------- */

        $mobiles = Category::firstOrCreate(['name' => 'موبايلات']);
        $laptops = Category::firstOrCreate(['name' => 'لابتوبات']);
        $accessories = Category::firstOrCreate(['name' => 'اكسسوارات']);
        $screens = Category::firstOrCreate(['name' => 'شاشات']);

        /* --------------------------------- */
        /*  Mobiles                          */
        /* --------------------------------- */

        Product::create([
            'name' => 'Samsung A24',
            'description' => 'موبايل ممتاز للاستخدام اليومي وبطارية قوية.',
            'price' => 7800,
            'stock' => 10,
            'is_active' => true,
            'category_id' => $mobiles->id,
        ]);

        Product::create([
            'name' => 'Xiaomi Note 12',
            'description' => 'أداء سريع وشاشة AMOLED رائعة.',
            'price' => 6900,
            'stock' => 8,
            'is_active' => true,
            'category_id' => $mobiles->id,
        ]);

        /* --------------------------------- */
        /*  Laptops                          */
        /* --------------------------------- */

        Product::create([
            'name' => 'HP Pavilion 15',
            'description' => 'لابتوب مناسب للشغل والدراسة.',
            'price' => 22000,
            'stock' => 5,
            'is_active' => true,
            'category_id' => $laptops->id,
        ]);

        Product::create([
            'name' => 'Lenovo IdeaPad 3',
            'description' => 'أداء قوي بسعر مناسب.',
            'price' => 19500,
            'stock' => 7,
            'is_active' => true,
            'category_id' => $laptops->id,
        ]);

        /* --------------------------------- */
        /*  Accessories                      */
        /* --------------------------------- */

        Product::create([
            'name' => 'Wireless Mouse',
            'description' => 'ماوس لاسلكي سريع الاستجابة.',
            'price' => 350,
            'stock' => 25,
            'is_active' => true,
            'category_id' => $accessories->id,
        ]);

        Product::create([
            'name' => 'Phone Charger 25W',
            'description' => 'شاحن سريع يدعم معظم الهواتف.',
            'price' => 450,
            'stock' => 20,
            'is_active' => true,
            'category_id' => $accessories->id,
        ]);

        /* --------------------------------- */
        /*  Screens                          */
        /* --------------------------------- */

        Product::create([
            'name' => 'Samsung 24" Monitor',
            'description' => 'شاشة FHD مناسبة للأعمال والألعاب.',
            'price' => 4200,
            'stock' => 6,
            'is_active' => true,
            'category_id' => $screens->id,
        ]);

        Product::create([
            'name' => 'LG 27" IPS Monitor',
            'description' => 'ألوان دقيقة وزاوية رؤية واسعة.',
            'price' => 5800,
            'stock' => 4,
            'is_active' => true,
            'category_id' => $screens->id,
        ]);
    }
}
