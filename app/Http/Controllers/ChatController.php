<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ChatController extends Controller
{
    private function ensureInGeneralChat(): void
    {
        $userId = Auth::id();
        $generalChat = Chat::where('tipo', 'general')->first();
        if ($generalChat && !$generalChat->users()->where('user_id', $userId)->exists()) {
            $generalChat->users()->attach($userId, ['last_read_at' => now()]);
        }
    }

    public function index()
    {
        $this->ensureInGeneralChat();

        $userId = Auth::id();

        $chats = Auth::user()
            ->chats()
            ->with([
                'users',
                'messages' => fn($q) => $q->latest()->limit(1),
            ])
            ->get()
            ->map(function ($chat) use ($userId) {
                $lastReadAt = $chat->pivot->last_read_at;

                $unreadCount = $chat->messages()
                    ->where('sender_id', '!=', $userId)
                    ->when($lastReadAt, fn($q) => $q->where('created_at', '>', $lastReadAt))
                    ->count();

                $arr = $chat->toArray();
                $arr['unread_count'] = $unreadCount;
                $arr['is_muted']     = (bool) $chat->pivot->is_muted;
                $arr['is_hidden']    = (bool) $chat->pivot->is_hidden;

                return $arr;
            })
            ->sortByDesc(fn($c) => $c['tipo'] === 'general' ? '9999' : ($c['messages'][0]['created_at'] ?? $c['created_at']))
            ->values();

        return Inertia::render('Mensajes/Index', ['chats' => $chats]);
    }

    public function createPrivateChat(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users,id|different:' . Auth::id(),
        ]);

        $userId        = $request->seller_id;
        $currentUserId = Auth::id();

        $existingChat = Chat::where('tipo', 'privado')
            ->whereHas('users', fn($q) => $q->where('user_id', $currentUserId))
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->first();

        if ($existingChat) {
            return response()->json(['chat_id' => $existingChat->id, 'is_new' => false]);
        }

        $chat = Chat::create(['tipo' => 'privado']);
        $chat->users()->attach([$currentUserId, $userId]);

        return response()->json(['chat_id' => $chat->id, 'is_new' => true]);
    }

    public function show(Chat $chat)
    {
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        // Mark all as read
        $chat->users()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

        $pivot = $chat->users()->where('user_id', Auth::id())->first()->pivot;

        $chat->load('users', 'messages.sender', 'messages.reactions', 'messages.replyTo.sender');

        return Inertia::render('Mensajes/Show', [
            'chat'      => $chat,
            'is_muted'  => (bool) $pivot->is_muted,
            'is_hidden' => (bool) $pivot->is_hidden,
        ]);
    }

    public function storeMessage(Request $request, Chat $chat)
    {
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $request->validate([
            'contenido'   => 'nullable|string|max:2000',
            'attachment'  => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip,txt',
            'type'        => 'nullable|in:text,product_card',
            'metadata'    => 'nullable|array',
            'reply_to_id' => 'nullable|exists:messages,id',
        ]);

        $data = [
            'chat_id'     => $chat->id,
            'sender_id'   => Auth::id(),
            'reply_to_id' => $request->reply_to_id,
            'contenido'   => $request->contenido,
            'type'        => $request->type ?? 'text',
            'metadata'    => $request->metadata,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat-attachments', 'public');
            $mime = $file->getMimeType();

            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_type'] = str_starts_with($mime, 'image/') ? 'image' : 'file';
        }

        if (empty($data['contenido']) && empty($data['attachment_path']) && $data['type'] === 'text') {
            return response()->json(['error' => 'El mensaje no puede estar vacío.'], 422);
        }

        $message = Message::create($data);

        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            Log::error('Error broadcasting MessageSent', ['error' => $e->getMessage()]);
        }

        return response()->json($message->load('sender', 'reactions', 'replyTo.sender'));
    }

    public function toggleReaction(Request $request, Chat $chat, Message $message)
    {
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        if ($message->chat_id !== $chat->id) {
            abort(404);
        }

        $request->validate(['emoji' => 'required|string|max:20']);

        $existing = MessageReaction::where([
            'message_id' => $message->id,
            'user_id'    => Auth::id(),
            'emoji'      => $request->emoji,
        ])->first();

        if ($existing) {
            $existing->delete();
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id'    => Auth::id(),
                'emoji'      => $request->emoji,
            ]);
        }

        return response()->json([
            'reactions' => $message->reactions()->get(),
        ]);
    }

    public function typing(Request $request, Chat $chat)
    {
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $request->validate(['is_typing' => 'required|boolean']);

        try {
            broadcast(new UserTyping(
                $chat->id,
                Auth::id(),
                Auth::user()->name,
                $request->boolean('is_typing')
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::error('Error broadcasting UserTyping', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    public function toggleMute(Chat $chat)
    {
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $pivot    = $chat->users()->where('user_id', Auth::id())->first()->pivot;
        $newValue = ! $pivot->is_muted;
        $chat->users()->updateExistingPivot(Auth::id(), ['is_muted' => $newValue]);

        return response()->json(['is_muted' => $newValue]);
    }

    public function toggleHide(Chat $chat)
    {
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        // General chat can only be muted, not permanently hidden
        if ($chat->tipo === 'general') {
            abort(422, 'El chat general no se puede ocultar permanentemente.');
        }

        $pivot    = $chat->users()->where('user_id', Auth::id())->first()->pivot;
        $newValue = ! $pivot->is_hidden;
        $chat->users()->updateExistingPivot(Auth::id(), ['is_hidden' => $newValue]);

        return response()->json(['is_hidden' => $newValue]);
    }

    public function markRead(Chat $chat)
    {
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $chat->users()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
