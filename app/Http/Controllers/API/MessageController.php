<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Events\MessageSentEvent;
use App\Models\Chat;


class MessageController extends Controller
{
    public function getUsers() {

        $users = User::where('id', '!=', auth()->user()->id)->get();

        return response()->json($users);
    }

    public function sendMessage(Request $request) {

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required',
            'type' => 'required|in:text,video,photo',
        ]);

        $chat = Chat::where(function ($query) use ($request) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $request->receiver_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->receiver_id)
                ->where('receiver_id', auth()->id());
        })->first();

        if (empty($chat)) {
            $chat = Chat::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $request->receiver_id,
            ]);
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'type' => $request->type,
            'sender_id' => auth()->user()->id,
        ]);

        broadcast(new MessageSentEvent($message))->toOthers();

        return response()->json([
            'message' => 'Message sent!',
        ]);
    }

    public function getMessages ($id)
    {
        $user = auth()->user();

        // fetch
        $messages = Message::where(function ($query) use ($user, $id) {
            $query->where('sender_id', $user->id)->where('receiver_id', $id);
        })->orWhere(function ($query) use ($user, $id) {
            $query->where('sender_id', $id)->where('receiver_id', $user->id);
        })->get();

        $messages = $messages->map(function ($message) use ($user) {
            $message->is_me = $message->sender_id == $user->id;
            return $message;
        });

        return response()->json($messages);
    }
}
