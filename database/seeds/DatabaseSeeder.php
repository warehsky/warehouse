<?php
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AreasTableSeeder::class);
        $this->call(ClientsTableSeeder::class);
        $this->call(ContractTypesTableSeeder::class);
        $this->call(DeliveryTypesTableSeeder::class);
        $this->call(FiltersTableSeeder::class);
        $this->call(HotItemsTableSeeder::class);
        $this->call(LicenseTypesTableSeeder::class);
        $this->call(PriceTypesTableSeeder::class);
        $this->call(TradeAreasTableSeeder::class);
        $this->call(TradeDirectionsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
        $this->call(TradePointTypesTableSeeder::class);
        $this->call(TradePointsTableSeeder::class);
        $this->call(TradeMarkTableSeeder::class);
        $this->call(WarehousesTableSeeder::class);
        $this->call(WarehouseByDirectionTableSeeder::class);
        $this->call(ClientDeferringTimeTableSeeder::class);
        $this->call(RouteEventsTableSeeder::class);
        $this->call(TradePointDebtsTableSeeder::class);
        $this->call(ItemsTableSeeder::class);
        $this->call(WarehouseItemsTableSeeder::class);
        $this->call(PricesTableSeeder::class);
        $this->call(ActiveItemGroupsTableSeeder::class);
        $this->call(OrderAttributesTableSeeder::class);
        $this->call(OrdersTableSeeder::class);
        $this->call(OrderItemTableSeeder::class);
        $this->call(RequestMoneyTableSeeder::class);
        $this->call(TradePointScheduleTypesTableSeeder::class);
    }
}
