<?php

namespace Database\Seeders;

use App\Models\EventCost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventCosts = [
            ['type' => 'appointment', 'cost' => 200],
            ['type' => 'activated', 'cost' => 100],
            ['type' => 'registered', 'cost' => 50],
        ];

        foreach ($eventCosts as $eventCost) {
            EventCost::create($eventCost);
        }
    }
}
