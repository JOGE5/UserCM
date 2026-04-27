<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Seeder;

class GeneralChatSeeder extends Seeder
{
    public function run(): void
    {
        $chat = Chat::firstOrCreate(
            ['tipo' => 'general'],
            ['nombre' => 'Chat General Unifranz']
        );

        $existingUserIds = $chat->users()->pluck('users.id')->toArray();

        $newUserIds = User::whereNotIn('id', $existingUserIds)->pluck('id');

        $attach = [];
        foreach ($newUserIds as $userId) {
            $attach[$userId] = ['last_read_at' => now()];
        }

        if ($attach) {
            $chat->users()->attach($attach);
        }
    }
}
