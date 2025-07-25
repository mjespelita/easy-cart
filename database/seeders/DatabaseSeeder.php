<?php

namespace Database\Seeders;

use App\Models\Discounts;
use App\Models\Products;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@system.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Counter Staff',
            'email' => 'counter@system.com',
            'role' => 'counter',
        ]);

        User::factory()->create([
            'name' => 'Kitchen Staff',
            'email' => 'kitchen@example.com',
            'role' => 'counter',
        ]);

        // Discounts::create([
        //     'users_id' => 1,
        //     'name' => 'Senior Citizen',
        //     'discount' => 40,
        // ]);
        // Discounts::create([
        //     'users_id' => 1,
        //     'name' => 'Student',
        //     'discount' => 30,
        // ]);
        // Discounts::create([
        //     'users_id' => 1,
        //     'name' => 'PWDs',
        //     'discount' => 10,
        // ]);

        // $names = [
        //     'Cheeseburger', 'Double Cheeseburger', 'Spaghetti', 'Iced Tea', 'Fries', 'Burger Steak', 'Hotdog Sandwich',
        //     'Pizza Slice', 'Coke', 'Lemonade', 'Fried Chicken', 'Chicken Wings', 'Pancit Canton', 'Beef Tapa', 'Tocino Meal',
        //     'Garlic Rice', 'Sisig', 'Halo-Halo', 'Mango Float', 'Chocolate Cake', 'Vanilla Ice Cream', 'Grilled Liempo',
        //     'Buko Juice', 'Taho', 'Kare-Kare', 'Sinigang na Baboy', 'Bangus Belly', 'Milk Tea', 'Ube Cake', 'Macaroni Salad'
        // ];
        // $descriptions = [
        //     'Delicious and filling', 'Perfect for any meal', 'Fresh and tasty', 'House specialty', 'Crispy and flavorful',
        //     'Best seller', 'Customer favorite', 'Served cold and sweet', 'Classic Filipino taste', 'Made with love',
        //     'Rich and creamy', 'Locally sourced ingredients', 'A hearty serving', 'A crowd-pleaser', 'Great for sharing',
        //     'Cooked to perfection', 'Comfort food at its best', 'Sweet and savory combo', 'Light and refreshing',
        //     'A spicy twist', 'Golden and crunchy', 'Melt-in-your-mouth goodness', 'Soft and fluffy', 'A family favorite',
        //     'Guilt-free indulgence', 'Perfectly balanced flavors', 'A taste of home', 'Affordable and tasty',
        //     'Irresistibly sweet', 'Goes well with rice'
        // ];


        // for ($i = 0; $i < 50; $i++) {
        //     Products::create([
        //         'product_id' => Str::random(4),
        //         'name' => $names[array_rand($names)],
        //         'description' => $descriptions[array_rand($descriptions)],
        //         'price' => rand(30, 250), // Random price between 30 and 250
        //         'isTrash' => 0,
        //     ]);
        // }
    }
}
