<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NotificationEntityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entityTypes = [
            [
                'entity_table' => 'order',
                'notification_type'=> 'new',
                'description'=>'ordered in your restaurant.'
            ],
            [
                'entity_table' => 'order',
                'notification_type'=> 'update',
                'description'=>'has updated their order.'
            ],
            [
                'entity_table' => 'order',
                'notification_type'=> 'cancel',
                'description'=>'canceled their order.'
            ],

        ];
        foreach ($entityTypes as $entityType) {
            \App\Models\NotificationEntityType::create($entityType);
        }
    }
}
