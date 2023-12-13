<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Customer;
use App\Models\Session;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $customer = Customer::factory()->create([
            'email' => 'customer1@customer.com',
        ]);

        $user1 = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        $user2 = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        Session::factory(1)->create([
            'user_id' => $user1->id,
            'event_type' => 'registration',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2023-12-01')
        ]);

        Session::factory(3)->create([
            'user_id' => $user1->id,
            'event_type' => 'activation',
            'event_date' => Carbon::now()->format('Y-m-d')
        ]);

        Session::factory(1)->create([
            'user_id' => $user1->id,
            'event_type' => 'appointment',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2023-12-01')
        ]);
    }
}
