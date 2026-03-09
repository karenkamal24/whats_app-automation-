<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_images')->truncate();
        Product::truncate();
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /* --------------------------------- */
        /*  Categories                       */
        /* --------------------------------- */

        $mobiles     = Category::create(['name' => 'Mobiles',     'name_ar' => 'موبايلات']);
        $laptops     = Category::create(['name' => 'Laptops',     'name_ar' => 'لابتوبات']);
        $accessories = Category::create(['name' => 'Accessories', 'name_ar' => 'اكسسوارات']);
        $screens     = Category::create(['name' => 'Screens',     'name_ar' => 'شاشات']);

        /* --------------------------------- */
        /*  Mobiles                          */
        /* --------------------------------- */

        Product::create([
            'name'           => 'Samsung A24',
            'name_ar'        => 'سامسونج A24',
            'description'    => 'Great phone for daily use with strong battery.',
            'description_ar' => 'موبايل ممتاز للاستخدام اليومي وبطارية قوية.',
            'keywords'       => 'samsung,سامسونج,a24,galaxy,جالاكسي,android,اندرويد',
            'price'          => 7800,
            'stock'          => 10,
            'is_active'      => true,
            'category_id'    => $mobiles->id,
        ]);

        Product::create([
            'name'           => 'Xiaomi Note 12',
            'name_ar'        => 'شاومي نوت 12',
            'description'    => 'Fast performance with beautiful AMOLED display.',
            'description_ar' => 'أداء سريع وشاشة AMOLED رائعة.',
            'keywords'       => 'xiaomi,شاومي,note,12,نوت,redmi,ريدمي,amoled',
            'price'          => 6900,
            'stock'          => 8,
            'is_active'      => true,
            'category_id'    => $mobiles->id,
        ]);

        /* --------------------------------- */
        /*  Laptops                          */
        /* --------------------------------- */

        Product::create([
            'name'           => 'HP Pavilion 15',
            'name_ar'        => 'اتش بي بافيليون 15',
            'description'    => 'Laptop suitable for work and study.',
            'description_ar' => 'لابتوب مناسب للشغل والدراسة.',
            'keywords'       => 'hp,اتش بي,pavilion,بافيليون,15,laptop,لابتوب,كمبيوتر',
            'price'          => 22000,
            'stock'          => 5,
            'is_active'      => true,
            'category_id'    => $laptops->id,
        ]);

        Product::create([
            'name'           => 'Lenovo IdeaPad 3',
            'name_ar'        => 'لينوفو ايديا باد 3',
            'description'    => 'Strong performance with affordable price.',
            'description_ar' => 'أداء قوي بسعر مناسب.',
            'keywords'       => 'lenovo,لينوفو,ideapad,ايديا باد,3,laptop,لابتوب,كمبيوتر',
            'price'          => 19500,
            'stock'          => 7,
            'is_active'      => true,
            'category_id'    => $laptops->id,
        ]);

        /* --------------------------------- */
        /*  Accessories                      */
        /* --------------------------------- */

        Product::create([
            'name'           => 'Wireless Mouse',
            'name_ar'        => 'ماوس لاسلكي',
            'description'    => 'Responsive wireless mouse.',
            'description_ar' => 'ماوس لاسلكي سريع الاستجابة.',
            'keywords'       => 'mouse,ماوس,wireless,لاسلكي,mice,فأرة,كمبيوتر',
            'price'          => 350,
            'stock'          => 25,
            'is_active'      => true,
            'category_id'    => $accessories->id,
        ]);

        Product::create([
            'name'           => 'Phone Charger 25W',
            'name_ar'        => 'شاحن موبايل 25 وات',
            'description'    => 'Fast charger compatible with most phones.',
            'description_ar' => 'شاحن سريع يدعم معظم الهواتف.',
            'keywords'       => 'charger,شاحن,25w,25,وات,fast charge,شحن سريع,type-c,usb',
            'price'          => 450,
            'stock'          => 20,
            'is_active'      => true,
            'category_id'    => $accessories->id,
        ]);

        /* --------------------------------- */
        /*  Screens                          */
        /* --------------------------------- */

        Product::create([
            'name'           => 'Samsung 24 Monitor',
            'name_ar'        => 'شاشة سامسونج 24 بوصة',
            'description'    => 'FHD monitor suitable for work and gaming.',
            'description_ar' => 'شاشة FHD مناسبة للأعمال والألعاب.',
            'keywords'       => 'samsung,سامسونج,24,fhd,monitor,gaming,جيمينج,العاب',
            'price'          => 4200,
            'stock'          => 6,
            'is_active'      => true,
            'category_id'    => $screens->id,
        ]);

        Product::create([
            'name'           => 'LG 27 IPS Monitor',
            'name_ar'        => 'شاشة LG 27 بوصة IPS',
            'description'    => 'Accurate colors with wide viewing angles.',
            'description_ar' => 'ألوان دقيقة وزاوية رؤية واسعة.',
            'keywords'       => 'lg,ال جي,27,ips,monitor,wide,واسعة,تصميم,design',
            'price'          => 5800,
            'stock'          => 4,
            'is_active'      => true,
            'category_id'    => $screens->id,
        ]);
    }
}
