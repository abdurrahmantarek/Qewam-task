<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerOneId = Customer::find(1);
        $customerTwoId = Customer::find(1);

        User::factory()->create([
            'email' => 'user1@mail.com',
            'customer_id' => $customerOneId
        ]);

        User::factory()->create([
            'email' => 'user2@mail.com',
            'customer_id' => $customerOneId
        ]);

        User::factory()->create([
            'email' => 'user3@mail.com',
            'customer_id' => $customerOneId
        ]);

        User::factory()->create([
            'email' => 'user4@mail.com',
            'customer_id' => $customerOneId
        ]);

        User::factory()->create([
            'email' => 'user5@mail.com',
            'customer_id' => $customerOneId
        ]);

        User::factory()->create([
            'email' => 'user6@mail.com',
            'customer_id' => $customerOneId
        ]);

        User::factory()->create([
            'email' => 'user7@mail.com',
            'customer_id' => $customerTwoId
        ]);

        User::factory()->create([
            'email' => 'user8@mail.com',
            'customer_id' => $customerTwoId
        ]);

        User::factory()->create([
            'email' => 'user9@mail.com',
            'customer_id' => $customerTwoId
        ]);

        User::factory()->create([
            'email' => 'user10@mail.com',
            'customer_id' => $customerTwoId
        ]);
    }
}
