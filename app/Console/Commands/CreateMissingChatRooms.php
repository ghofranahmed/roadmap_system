<?php

namespace App\Console\Commands;

use App\Models\Roadmap;
use App\Models\ChatRoom;
use Illuminate\Console\Command;

class CreateMissingChatRooms extends Command
{
    protected $signature = 'chat:create-missing-rooms';

    protected $description = 'Create chat rooms for roadmaps that do not have one yet';

    public function handle(): int
    {
        $roadmapsWithoutRoom = Roadmap::whereDoesntHave('chatRoom')->get();

        if ($roadmapsWithoutRoom->isEmpty()) {
            $this->info('All roadmaps already have a chat room. Nothing to do.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($roadmapsWithoutRoom as $roadmap) {
            ChatRoom::create([
                'name'       => "غرفة دردشة - {$roadmap->title}",
                'roadmap_id' => $roadmap->id,
                'is_active'  => true,
            ]);
            $count++;
            $this->line("  Created room for roadmap #{$roadmap->id}: {$roadmap->title}");
        }

        $this->info("Done. Created {$count} chat room(s).");
        return self::SUCCESS;
    }
}

