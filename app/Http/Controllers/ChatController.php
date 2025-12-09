<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Events\MessageSent;

class ChatController extends Controller
{
    /**
     * @return \Inertia\Response
     */
    public function index()
    {
        $chats = Auth::user()->chats()->with('users', 'messages.sender')->get();

        return Inertia::render('Mensajes/Index', ['chats' => $chats]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPrivateChat(Request $request)
    {
        \Log::info('createPrivateChat called', ['request' => $request->all()]);

        $request->validate([
            'seller_id' => 'required|exists:users,id|different:'.Auth::id(),
        ]);

        $userId = $request->seller_id;
        $currentUserId = Auth::id();

        \Log::info('Creating chat between users', ['current' => $currentUserId, 'target' => $userId]);

        // Verificar si ya existe un chat privado entre estos usuarios
        $existingChat = Chat::where('tipo', 'privado')
            ->whereHas('users', function ($query) use ($currentUserId) {
                $query->where('user_id', $currentUserId);
            })
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();

        if ($existingChat) {
            \Log::info('Existing chat found', ['chat_id' => $existingChat->id]);

            return response()->json(['chat_id' => $existingChat->id]);
        }

        // Crear nuevo chat privado
        $chat = Chat::create(['tipo' => 'privado']);
        \Log::info('New chat created', ['chat_id' => $chat->id]);

        // Agregar usuarios al chat
        $chat->users()->attach([$currentUserId, $userId]);
        \Log::info('Users attached to chat', ['chat_id' => $chat->id, 'users' => [$currentUserId, $userId]]);

        return response()->json(['chat_id' => $chat->id]);
    }

    /**
     * @return \Inertia\Response
     */
    public function show(Chat $chat)
    {
        // Verificar que el usuario esté en el chat
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $chat->load('users', 'messages.sender');

        return Inertia::render('Mensajes/Show', ['chat' => $chat]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeMessage(Request $request, Chat $chat)
    {
        // Verificar que el usuario esté en el chat
        if (! $chat->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $request->validate([
            'contenido' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => Auth::id(),
            'contenido' => $request->contenido,
        ]);

        // Broadcast the new message to others in the chat
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            \Log::error('Error broadcasting MessageSent', ['error' => $e->getMessage()]);
        }

        return response()->json($message->load('sender'));
    }
}
