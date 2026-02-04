<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => '1-xona', 'capacity' => 12],
            ['name' => '2-xona', 'capacity' => 10],
            ['name' => '3-xona', 'capacity' => 10],
            ['name' => '4-xona', 'capacity' => 8],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
