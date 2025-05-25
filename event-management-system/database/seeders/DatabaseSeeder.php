<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Venue;
use App\Models\Category;
use App\Models\Event;
use App\Models\Ticket;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
           
        ]);

        // Create Event Manager
        User::create([
            'name' => 'Event Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'EVENT_MANAGER',
         
        ]);

        // Create Usher
        User::create([
            'name' => 'Usher User',
            'email' => 'usher@example.com',
            'password' => bcrypt('password'),
            'role' => 'USHER',
         
        ]);

        // Create sample venues
        $venues = [
            ['name' => 'Grand Convention Center', 'address' => '123 Main St, City', 'capacity' => 1000],
            ['name' => 'Community Hall', 'address' => '456 Oak Ave, Town', 'capacity' => 500],
            ['name' => 'Outdoor Amphitheater', 'address' => '789 Park Rd, City', 'capacity' => 2000],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }

        // Create sample categories
        $categories = [
            ['name' => 'Conference'],
            ['name' => 'Workshop'],
            ['name' => 'Concert'],
            ['name' => 'Seminar'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create sample events
        $event = Event::create([
            'name' => 'Tech Conference 2024',
            'description' => 'Annual technology conference featuring latest trends and innovations.',
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(31),
            'venue_id' => 1,
            'category_id' => 1,
            'is_active' => true,
        ]);

        // Create sample tickets
        Ticket::create([
            'event_id' => $event->id,
            'created_by' => 1,
            'name' => 'General Admission',
            'price' => 99.99,
            'capacity' => 800,
        ]);

        Ticket::create([
            'event_id' => $event->id,
            'created_by' => 1,
            'name' => 'VIP Access',
            'price' => 199.99,
            'capacity' => 100,
        ]);
    }
}