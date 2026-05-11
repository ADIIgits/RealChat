<?php
namespace Database\Seeders;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo users
        $alice = User::firstOrCreate(['email' => 'alice@example.com'], [
            'name'     => 'Alice',
            'password' => Hash::make('password'),
        ]);

        $bob = User::firstOrCreate(['email' => 'bob@example.com'], [
            'name'     => 'Bob',
            'password' => Hash::make('password'),
        ]);

        // Create a general channel
        if (!Channel::where('name', 'general')->exists()) {
            $general = Channel::create([
                'name'        => 'general',
                'description' => 'General discussion for everyone',
                'is_private'  => false,
                'created_by'  => $alice->id,
            ]);
            $general->members()->attach([$alice->id, $bob->id]);

            $channel2 = Channel::create([
                'name'        => 'random',
                'description' => 'Random chatter',
                'is_private'  => false,
                'created_by'  => $alice->id,
            ]);
            $channel2->members()->attach([$alice->id, $bob->id]);
        }
    }
}
