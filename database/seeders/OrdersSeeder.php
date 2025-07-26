<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $i = 1;
        foreach (Orders::all() as $order) {
            Orders::where('id', $order['id'])->update([
                'order_number' => 'ORDER_#'.$i
            ]);
            $i++;
        }
    }
}
