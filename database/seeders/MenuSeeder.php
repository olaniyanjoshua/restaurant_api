<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Starters' => 1,
            'Mains' => 2,
            'Desserts' => 3,
            'Drinks' => 4,
        ];

        $categoryModels = [];
        foreach ($categories as $name => $order) {
            $categoryModels[$name] = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $order]
            );
        }

        $items = [
            [
                'name' => 'Burrata Caprese',
                'description' => 'Creamy burrata, heirloom tomatoes, basil oil, aged balsamic.',
                'price' => 15,
                'category' => 'Starters',
                'image' => 'https://picsum.photos/seed/burrata/600/400',
            ],
            [
                'name' => 'Seared Scallops',
                'description' => 'Pan-seared scallops, cauliflower puree, brown butter.',
                'price' => 17,
                'category' => 'Starters',
                'image' => 'https://picsum.photos/seed/scallops/600/400',
            ],
            [
                'name' => 'Truffle Ribeye',
                'description' => 'Aged beef with truffle butter reduction.',
                'price' => 38,
                'category' => 'Mains',
                'image' => 'https://images.unsplash.com/photo-1544148103-0773bf10d330?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Handmade Pappardelle',
                'description' => 'Slow-cooked wild boar ragu.',
                'price' => 27,
                'category' => 'Mains',
                'image' => 'https://images.unsplash.com/photo-1559847844-5315695dadae?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Herb-Crusted Salmon',
                'description' => 'Wild salmon, herb crust, lemon beurre blanc, seasonal greens.',
                'price' => 29,
                'category' => 'Mains',
                'image' => 'https://picsum.photos/seed/salmon/600/400',
            ],
            [
                'name' => 'Midnight Ganache',
                'description' => '70% dark chocolate with sea salt.',
                'price' => 11,
                'category' => 'Desserts',
                'image' => 'https://images.unsplash.com/photo-1484723091739-30a097e8f296?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Creme Brulee',
                'description' => 'Classic vanilla custard, caramelized sugar crust.',
                'price' => 10,
                'category' => 'Desserts',
                'image' => 'https://picsum.photos/seed/brulee/600/400',
            ],
            [
                'name' => 'Old Fashioned',
                'description' => 'Bourbon, bitters, orange peel, hand-cut ice.',
                'price' => 14,
                'category' => 'Drinks',
                'image' => 'https://picsum.photos/seed/oldfashioned/600/400',
            ],
            [
                'name' => "Sommelier's Red Blend",
                'description' => 'House red blend, glass.',
                'price' => 13,
                'category' => 'Drinks',
                'image' => 'https://picsum.photos/seed/redwine/600/400',
            ],
            [
                'name' => 'Sparkling Elderflower',
                'description' => 'Non-alcoholic, elderflower, soda, mint.',
                'price' => 9,
                'category' => 'Drinks',
                'image' => 'https://picsum.photos/seed/elderflower/600/400',
            ],
        ];

        foreach ($items as $item) {
            MenuItem::firstOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $categoryModels[$item['category']]->id,
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'image' => $item['image'],
                    'is_available' => true,
                ]
            );
        }
    }
}
