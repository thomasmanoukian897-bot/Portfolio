<?php

use App\Enums\ConversationType;
use App\Enums\GroupAddPermission;
use App\Enums\MessagePermission;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Notifications\NewMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function unreadMessageDotCountInSidebar(string $html): int
{
    if (! preg_match('/<aside[^>]*data-mobile-drawer[^>]*>.*?<\/aside>/s', $html, $matches)) {
        return 0;
    }

    return substr_count($matches[0], 'data-unread-message-dot');
}

test('guests cannot access messages', function () {
    $this->get(route('messages.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view the messages page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('messages.index'))
        ->assertSuccessful()
        ->assertSee('Messages')
        ->assertSee('No conversations yet')
        ->assertSee(route('messages.groups.store'), false)
        ->assertSee('data-messages-group-submit', false)
        ->assertDontSee('data-messages-group-submit" disabled', false);
});

test('users can start a direct conversation', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create(['name' => 'Jamie Recipient']);

    $this->actingAs($initiator)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertRedirect();

    $conversation = Conversation::query()->first();

    expect($conversation)->not->toBeNull();
    expect($conversation->type)->toBe(ConversationType::Direct);
    expect($conversation->users()->pluck('users.id')->sort()->values()->all())
        ->toBe(collect([$initiator->id, $recipient->id])->sort()->values()->all());
});

test('starting a direct conversation reuses an existing one', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create();

    $existing = Conversation::findOrCreateDirect($initiator, $recipient);

    $this->actingAs($initiator)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertRedirect(route('messages.show', $existing));

    expect(Conversation::query()->count())->toBe(1);
});

test('users cannot message themselves', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('messages.store'), ['user_id' => $user->id])
        ->assertSessionHasErrors('user_id');
});

test('users cannot start a conversation with blocked users', function () {
    $initiator = User::factory()->create();
    $blocked = User::factory()->create();

    UserBlock::query()->create([
        'blocker_id' => $initiator->id,
        'blocked_id' => $blocked->id,
    ]);

    $this->actingAs($initiator)
        ->post(route('messages.store'), ['user_id' => $blocked->id])
        ->assertForbidden();
});

test('users can create a group conversation', function () {
    $creator = User::factory()->create();
    $memberOne = User::factory()->create();
    $memberTwo = User::factory()->create();

    $this->actingAs($creator)
        ->post(route('messages.groups.store'), [
            'name' => 'Project Team',
            'user_ids' => [$memberOne->id, $memberTwo->id],
        ])
        ->assertRedirect();

    $conversation = Conversation::query()->first();

    expect($conversation)->not->toBeNull();
    expect($conversation->type)->toBe(ConversationType::Group);
    expect($conversation->name)->toBe('Project Team');
    expect($conversation->users()->count())->toBe(3);
});

test('users cannot be added to group chats when they only allow people they follow', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create([
        'group_add_permission' => GroupAddPermission::FollowingOnly,
    ]);

    $this->actingAs($creator)
        ->post(route('messages.groups.store'), [
            'name' => 'Project Team',
            'user_ids' => [$member->id],
        ])
        ->assertForbidden();

    expect(Conversation::query()->count())->toBe(0);
});

test('users can be added to group chats when they follow the creator and restrict adds to people they follow', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create([
        'group_add_permission' => GroupAddPermission::FollowingOnly,
    ]);

    UserFollow::query()->create([
        'follower_id' => $member->id,
        'following_id' => $creator->id,
    ]);

    $this->actingAs($creator)
        ->post(route('messages.groups.store'), [
            'name' => 'Project Team',
            'user_ids' => [$member->id],
        ])
        ->assertRedirect();

    expect(Conversation::query()->count())->toBe(1);
});

test('group members can view the group members list', function () {
    $creator = User::factory()->create(['name' => 'Group Creator']);
    $member = User::factory()->create(['name' => 'Group Member']);
    $conversation = Conversation::factory()->group('Design Team')->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
        $member->id => ['last_read_at' => null],
    ]);

    $this->actingAs($member)
        ->get(route('messages.show', $conversation))
        ->assertSuccessful()
        ->assertSee('data-messages-group-members-open', false)
        ->assertSee('data-messages-group-members-modal', false)
        ->assertSee('data-messages-notifications-toggle', false)
        ->assertSee('fa-bell', false)
        ->assertSee('Group Creator')
        ->assertSee('Group Member')
        ->assertSee('Admin');
});

test('group members can update the group name', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $conversation = Conversation::factory()->group('Old Name')->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
        $member->id => ['last_read_at' => null],
    ]);

    $this->actingAs($member)
        ->patch(route('messages.groups.name.update', $conversation), [
            'name' => 'New Name',
        ])
        ->assertRedirect(route('messages.show', $conversation));

    expect($conversation->fresh()->name)->toBe('New Name');

    $systemMessage = Message::query()->first();

    expect($systemMessage)->not->toBeNull();
    expect($systemMessage->type)->toBe(MessageType::GroupNameChanged);
    expect($systemMessage->body)->toBe("{$member->name} changed the group name");
});

test('users cannot update group names for groups they are not in', function () {
    $creator = User::factory()->create();
    $outsider = User::factory()->create();
    $conversation = Conversation::factory()->group('Private Group')->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
    ]);

    $this->actingAs($outsider)
        ->patch(route('messages.groups.name.update', $conversation), [
            'name' => 'Hacked Name',
        ])
        ->assertForbidden();
});

test('group admins can kick members', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $admin->id,
    ]);
    $conversation->users()->attach([
        $admin->id => ['last_read_at' => now()],
        $member->id => ['last_read_at' => null],
    ]);

    $this->actingAs($admin)
        ->delete(route('messages.groups.members.kick', [$conversation, $member]))
        ->assertRedirect(route('messages.show', $conversation));

    expect($conversation->users()->where('users.id', $member->id)->exists())->toBeFalse();
});

test('non-admin group members cannot kick other members', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $otherMember = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $admin->id,
    ]);
    $conversation->users()->attach([
        $admin->id => ['last_read_at' => now()],
        $member->id => ['last_read_at' => null],
        $otherMember->id => ['last_read_at' => null],
    ]);

    $this->actingAs($member)
        ->delete(route('messages.groups.members.kick', [$conversation, $otherMember]))
        ->assertForbidden();

    expect($conversation->users()->where('users.id', $otherMember->id)->exists())->toBeTrue();
});

test('group admins cannot kick themselves', function () {
    $admin = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $admin->id,
    ]);
    $conversation->users()->attach([
        $admin->id => ['last_read_at' => now()],
    ]);

    $this->actingAs($admin)
        ->delete(route('messages.groups.members.kick', [$conversation, $admin]))
        ->assertForbidden();
});

test('group members can leave a group', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $admin->id,
    ]);
    $conversation->users()->attach([
        $admin->id => ['last_read_at' => now()],
        $member->id => ['last_read_at' => null],
    ]);

    $this->actingAs($member)
        ->delete(route('messages.groups.leave', $conversation))
        ->assertRedirect(route('messages.index'));

    expect($conversation->users()->where('users.id', $member->id)->exists())->toBeFalse();
    expect($conversation->fresh())->not->toBeNull();
});

test('leaving as the last member deletes the group', function () {
    $admin = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $admin->id,
    ]);
    $conversation->users()->attach([
        $admin->id => ['last_read_at' => now()],
    ]);

    $this->actingAs($admin)
        ->delete(route('messages.groups.leave', $conversation))
        ->assertRedirect(route('messages.index'));

    expect(Conversation::query()->find($conversation->id))->toBeNull();
});

test('users cannot leave groups they are not in', function () {
    $creator = User::factory()->create();
    $outsider = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
    ]);

    $this->actingAs($outsider)
        ->delete(route('messages.groups.leave', $conversation))
        ->assertForbidden();
});

test('users cannot leave direct conversations', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($user, $other);

    $this->actingAs($user)
        ->delete(route('messages.groups.leave', $conversation))
        ->assertForbidden();
});

test('group members can mute and unmute conversation notifications', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now(), 'notifications_muted' => false],
        $member->id => ['last_read_at' => null, 'notifications_muted' => false],
    ]);

    $this->actingAs($member)
        ->patchJson(route('messages.notifications.toggle', $conversation))
        ->assertSuccessful()
        ->assertJsonPath('notifications_muted', true);

    expect($conversation->fresh()->notificationsMutedFor($member))->toBeTrue();

    $this->actingAs($member)
        ->patchJson(route('messages.notifications.toggle', $conversation))
        ->assertSuccessful()
        ->assertJsonPath('notifications_muted', false);

    expect($conversation->fresh()->notificationsMutedFor($member))->toBeFalse();
});

test('users do not receive message notifications when muted', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $sender->id,
    ]);
    $conversation->users()->attach([
        $sender->id => ['last_read_at' => now(), 'notifications_muted' => false],
        $recipient->id => ['last_read_at' => null, 'notifications_muted' => true],
    ]);

    $this->actingAs($sender)
        ->postJson(route('messages.messages.store', $conversation), [
            'body' => 'Hello group!',
        ])
        ->assertSuccessful();

    Notification::assertNothingSent();
});

test('users cannot toggle notifications for conversations they are not in', function () {
    $creator = User::factory()->create();
    $outsider = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
    ]);

    $this->actingAs($outsider)
        ->patchJson(route('messages.notifications.toggle', $conversation))
        ->assertForbidden();
});

test('group members can update the group avatar', function () {
    Storage::fake('public');

    $creator = User::factory()->create();
    $member = User::factory()->create();
    $conversation = Conversation::factory()->group('Photo Group')->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
        $member->id => ['last_read_at' => null],
    ]);

    $this->actingAs($member)
        ->patch(route('messages.groups.avatar.update', $conversation), [
            'avatar' => UploadedFile::fake()->image('group.jpg'),
        ])
        ->assertRedirect(route('messages.show', $conversation));

    $conversation->refresh();

    expect($conversation->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($conversation->avatar_path);

    $systemMessage = Message::query()->first();

    expect($systemMessage)->not->toBeNull();
    expect($systemMessage->type)->toBe(MessageType::GroupAvatarChanged);
    expect($systemMessage->body)->toBe("{$member->name} changed the group avatar");
});

test('replacing a group avatar deletes the old file', function () {
    Storage::fake('public');

    $creator = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
    ]);

    $oldPath = UploadedFile::fake()->image('old.jpg')->store('group-avatars', 'public');
    $conversation->update(['avatar_path' => $oldPath]);

    $this->actingAs($creator)
        ->patch(route('messages.groups.avatar.update', $conversation), [
            'avatar' => UploadedFile::fake()->image('new.jpg'),
        ])
        ->assertRedirect(route('messages.show', $conversation));

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($conversation->fresh()->avatar_path);
});

test('users cannot update avatars for groups they are not in', function () {
    $creator = User::factory()->create();
    $outsider = User::factory()->create();
    $conversation = Conversation::factory()->group()->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
    ]);

    $this->actingAs($outsider)
        ->patch(route('messages.groups.avatar.update', $conversation), [
            'avatar' => UploadedFile::fake()->image('group.jpg'),
        ])
        ->assertForbidden();
});

test('users cannot update avatars for direct conversations', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $other = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($user, $other);

    $this->actingAs($user)
        ->patch(route('messages.groups.avatar.update', $conversation), [
            'avatar' => UploadedFile::fake()->image('group.jpg'),
        ])
        ->assertForbidden();
});

test('users can send messages in a conversation', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($sender, $recipient);

    $this->actingAs($sender)
        ->postJson(route('messages.messages.store', $conversation), [
            'body' => 'Hello there!',
        ])
        ->assertSuccessful()
        ->assertJsonPath('message.body', 'Hello there!');

    expect(Message::query()->count())->toBe(1);
    Notification::assertSentTo($recipient, NewMessageNotification::class);
});

test('users cannot view conversations they are not part of', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($otherUser, User::factory()->create());

    $this->actingAs($user)
        ->get(route('messages.show', $conversation))
        ->assertForbidden();
});

test('users cannot send messages to conversations they are not part of', function () {
    $user = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect(
        User::factory()->create(),
        User::factory()->create(),
    );

    $this->actingAs($user)
        ->postJson(route('messages.messages.store', $conversation), [
            'body' => 'Should not send',
        ])
        ->assertForbidden();
});

test('blocked users cannot send messages in a direct conversation', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($sender, $recipient);

    UserBlock::query()->create([
        'blocker_id' => $recipient->id,
        'blocked_id' => $sender->id,
    ]);

    $this->actingAs($sender)
        ->postJson(route('messages.messages.store', $conversation), [
            'body' => 'Blocked message',
        ])
        ->assertForbidden();
});

test('direct conversation header links to the other users profile', function () {
    $viewer = User::factory()->create();
    $other = User::factory()->create(['name' => 'Profile Target']);
    $conversation = Conversation::findOrCreateDirect($viewer, $other);

    $this->actingAs($viewer)
        ->get(route('messages.show', $conversation))
        ->assertSuccessful()
        ->assertSee('href="'.route('users.show', $other).'"', false)
        ->assertSee('Profile Target');
});

test('message sender names link to their profile', function () {
    $viewer = User::factory()->create();
    $sender = User::factory()->create(['name' => 'Sender Profile']);
    $conversation = Conversation::findOrCreateDirect($viewer, $sender);

    $conversation->messages()->create([
        'user_id' => $sender->id,
        'body' => 'Hello from sender',
    ]);

    $this->actingAs($viewer)
        ->get(route('messages.show', $conversation))
        ->assertSuccessful()
        ->assertSee('href="'.route('users.show', $sender).'"', false)
        ->assertSee('Sender Profile');
});

test('viewing a conversation marks it as read', function () {
    $viewer = User::factory()->create();
    $sender = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($viewer, $sender);

    $conversation->messages()->create([
        'user_id' => $sender->id,
        'body' => 'Unread message',
    ]);

    expect($conversation->hasUnreadMessagesFor($viewer))->toBeTrue();

    $this->actingAs($viewer)
        ->get(route('messages.show', $conversation))
        ->assertSuccessful()
        ->assertSee('Unread message');

    expect($conversation->fresh()->hasUnreadMessagesFor($viewer))->toBeFalse();
});

test('messages sidebar link is visible to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('href="'.route('messages.index').'"', false)
        ->assertSee('paper-plane', false)
        ->assertSee('Messages');
});

test('messages sidebar shows a red dot when the user has unread messages', function () {
    $viewer = User::factory()->create();
    $sender = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($viewer, $sender);

    $conversation->messages()->create([
        'user_id' => $sender->id,
        'body' => 'Hey!',
    ]);

    $response = $this->actingAs($viewer)->get(route('home'));

    expect(unreadMessageDotCountInSidebar($response->getContent()))->toBe(1);
});

test('messages sidebar hides the red dot after reading messages', function () {
    $viewer = User::factory()->create();
    $sender = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($viewer, $sender);

    $conversation->messages()->create([
        'user_id' => $sender->id,
        'body' => 'Hey!',
    ]);

    $this->actingAs($viewer)
        ->get(route('messages.show', $conversation))
        ->assertSuccessful();

    $response = $this->get(route('home'));

    expect(unreadMessageDotCountInSidebar($response->getContent()))->toBe(0);
});

test('messages sidebar link is hidden from guests', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee(route('messages.index'), false);
});

test('updating a group name to the same value does not create a system message', function () {
    $creator = User::factory()->create();
    $conversation = Conversation::factory()->group('Same Name')->create([
        'created_by' => $creator->id,
    ]);
    $conversation->users()->attach([
        $creator->id => ['last_read_at' => now()],
    ]);

    $this->actingAs($creator)
        ->patch(route('messages.groups.name.update', $conversation), [
            'name' => 'Same Name',
        ])
        ->assertRedirect(route('messages.show', $conversation));

    expect(Message::query()->count())->toBe(0);
});

test('group name changes appear as centered system messages in the chat', function () {
    $member = User::factory()->create(['name' => 'Jamie Member']);
    $conversation = Conversation::factory()->group('Old Name')->create([
        'created_by' => $member->id,
    ]);
    $conversation->users()->attach([
        $member->id => ['last_read_at' => now()],
    ]);

    $conversation->recordSystemMessage($member, MessageType::GroupNameChanged);

    $this->actingAs($member)
        ->get(route('messages.show', $conversation))
        ->assertSuccessful()
        ->assertSee('Jamie Member changed the group name');
});

test('users can poll for new messages', function () {
    $viewer = User::factory()->create();
    $sender = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($viewer, $sender);

    $firstMessage = $conversation->messages()->create([
        'user_id' => $sender->id,
        'body' => 'First',
    ]);

    $secondMessage = $conversation->messages()->create([
        'user_id' => $sender->id,
        'body' => 'Second',
    ]);

    $this->actingAs($viewer)
        ->getJson(route('messages.messages.index', $conversation).'?after_id='.$firstMessage->id)
        ->assertSuccessful()
        ->assertJsonCount(1, 'messages')
        ->assertJsonPath('messages.0.body', 'Second');
});

test('strangers receive a message request when messaged', function () {
    $initiator = User::factory()->create(['name' => 'Stranger Sender']);
    $recipient = User::factory()->create();

    $this->actingAs($initiator)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertRedirect();

    $conversation = Conversation::query()->first();

    expect($conversation)->not->toBeNull();
    expect($conversation->isPendingRequestFor($recipient))->toBeTrue();
    expect($conversation->isPendingRequestFor($initiator))->toBeFalse();

    $this->actingAs($recipient)
        ->get(route('messages.index'))
        ->assertSuccessful()
        ->assertSee('Requests')
        ->assertSee('Stranger Sender')
        ->assertDontSee('No conversations yet');
});

test('people you follow can message you directly without a request', function () {
    $sender = User::factory()->create(['name' => 'Followed Sender']);
    $recipient = User::factory()->create();

    UserFollow::query()->create([
        'follower_id' => $recipient->id,
        'following_id' => $sender->id,
    ]);

    $this->actingAs($sender)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertRedirect();

    $conversation = Conversation::query()->first();

    expect($conversation->isPendingRequestFor($recipient))->toBeFalse();

    $this->actingAs($recipient)
        ->get(route('messages.index'))
        ->assertSuccessful()
        ->assertDontSee('Requests')
        ->assertSee('Followed Sender');
});

test('users cannot message someone who only allows followers when they do not follow them', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create([
        'message_permission' => MessagePermission::FollowersOnly,
    ]);

    $this->actingAs($initiator)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertForbidden();

    expect(Conversation::query()->count())->toBe(0);
});

test('followers can send a message request when recipient only allows followers', function () {
    $initiator = User::factory()->create(['name' => 'Follower Sender']);
    $recipient = User::factory()->create([
        'message_permission' => MessagePermission::FollowersOnly,
    ]);

    UserFollow::query()->create([
        'follower_id' => $initiator->id,
        'following_id' => $recipient->id,
    ]);

    $this->actingAs($initiator)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertRedirect();

    $conversation = Conversation::query()->first();

    expect($conversation->isPendingRequestFor($recipient))->toBeTrue();
});

test('users cannot message someone who allows no one unless they follow or have chatted before', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create([
        'message_permission' => MessagePermission::NoOne,
    ]);

    $this->actingAs($initiator)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertForbidden();
});

test('users can message someone with no one permission when the recipient follows them', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'message_permission' => MessagePermission::NoOne,
    ]);

    UserFollow::query()->create([
        'follower_id' => $recipient->id,
        'following_id' => $sender->id,
    ]);

    $this->actingAs($sender)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertRedirect();

    expect(Conversation::query()->count())->toBe(1);
});

test('recipients can accept message requests', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($initiator, $recipient);

    expect($conversation->isPendingRequestFor($recipient))->toBeTrue();

    $this->actingAs($recipient)
        ->post(route('messages.requests.accept', $conversation))
        ->assertRedirect(route('messages.show', $conversation));

    expect($conversation->fresh()->isPendingRequestFor($recipient))->toBeFalse();
});

test('recipients can decline message requests', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($initiator, $recipient);

    $this->actingAs($recipient)
        ->delete(route('messages.requests.decline', $conversation))
        ->assertRedirect(route('messages.index'));

    expect(Conversation::query()->count())->toBe(0);
});

test('recipients cannot reply to pending message requests', function () {
    $initiator = User::factory()->create();
    $recipient = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($initiator, $recipient);

    $this->actingAs($recipient)
        ->postJson(route('messages.messages.store', $conversation), [
            'body' => 'Not allowed yet',
        ])
        ->assertForbidden();
});

test('people with a previously accepted conversation can message again without a request', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $conversation = Conversation::findOrCreateDirect($sender, $recipient);
    $conversation->acceptFor($recipient);
    $conversation->delete();

    $this->actingAs($sender)
        ->post(route('messages.store'), ['user_id' => $recipient->id])
        ->assertRedirect();

    $newConversation = Conversation::query()->first();

    expect($newConversation->isPendingRequestFor($recipient))->toBeFalse();
});
