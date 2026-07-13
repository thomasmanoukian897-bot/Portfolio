<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_message_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'contact_user_id']);
        });

        $acceptedPairs = DB::table('conversation_user')
            ->join('conversations', 'conversations.id', '=', 'conversation_user.conversation_id')
            ->where('conversations.type', 'direct')
            ->whereNotNull('conversation_user.accepted_at')
            ->select('conversation_user.conversation_id', 'conversation_user.user_id')
            ->get()
            ->groupBy('conversation_id');

        $now = now();

        foreach ($acceptedPairs as $participants) {
            $userIds = $participants->pluck('user_id')->unique()->values();

            if ($userIds->count() !== 2) {
                continue;
            }

            foreach ($userIds as $userId) {
                $contactId = $userIds->first(fn (int $id): bool => $id !== $userId);

                DB::table('user_message_contacts')->insertOrIgnore([
                    'user_id' => $userId,
                    'contact_user_id' => $contactId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_message_contacts');
    }
};
