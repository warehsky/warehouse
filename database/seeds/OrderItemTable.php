<?php

use Illuminate\Database\Seeder;
use App\Model\OrderItem;

class OrderItemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderItem::create([
            "order_id" => 1,
            "item_id" => 5,
            "warehouse_id" => 1,
            "quantity" => 5,
            "price"  => 10,
            "priceType" => 2,
            "percent" => 1,
            "guid" => "order-item-order1-1",
            "is_new" => 0,
        ]);
        OrderItem::create([
            "order_id" => 1,
            "item_id" => 6,
            "warehouse_id" => 2,
            "quantity" => 5,
            "price"  => 10,
            "priceType" => 2,
            "percent" => 1,
            "guid" => "order-item-order1-2",
            "is_new" => 0,
        ]);
        OrderItem::create([
            "order_id" => 2,
            "item_id" => 7,
            "warehouse_id" => 1,
            "quantity" => 5,
            "price"  => 20,
            "priceType" => 2,
            "percent" => 1,
            "guid" => "order-item-order2-1",
            "is_new" => 0,
        ]);
        OrderItem::create([
            "order_id" => 2,
            "item_id" => 8,
            "warehouse_id" => 2,
            "quantity" => 5,
            "price"  => 20,
            "priceType" => 2,
            "percent" => 1,
            "guid" => "order-item-order2-2",
            "is_new" => 0,
        ]);
    }
}
