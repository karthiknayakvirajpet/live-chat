<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatMessage;
use App\Models\User;

class LiveChatController extends Controller
{
    /************************************************************************
    *Chat index page
    *************************************************************************/
    public function index($user_chat_id = null)
    {
        $user_chat_id = $user_chat_id ?: auth()->user()->id;

        $user_chat_name = User::where('id', $user_chat_id)->first();

        $support_agent = User::where('role', 1)->first();
        return view('chat.chat', compact('support_agent', 'user_chat_id', 'user_chat_name'));
    }

    /************************************************************************
    *Message send function
    *************************************************************************/
    public function sendMessage(Request $request)
    {
        $message = $request->message;

        $user = Auth::id();
        $sender_name = auth()->user()->name;

        if($user == 1) //Support Agent
        {
            $sender_id = 1;
            $receiver_id = $request->user_chat_id;
        }
        else //Customer
        {
            $sender_id = $request->user_chat_id;
            $receiver_id = 1;
        }

        $receiver_name = User::where('id', $receiver_id)->pluck('name');

        //Storing message data
        $chatMessage = new ChatMessage();
        $chatMessage->sender_id = $sender_id;
        $chatMessage->receiver_id = $receiver_id;
        $chatMessage->message = $message;
        $chatMessage->save();

        //Calling an event
        event(new \App\Events\NewMessage($sender_id, $sender_name, $receiver_id, $message));

        // Show Notification - Broadcast the new message
        broadcast(new \App\Events\NewMessageEvent($sender_id, $sender_name, $receiver_id, $receiver_name, $message))->toOthers();

        return response()->json(['status' => 'Message sent']);
    }

    /************************************************************************
    *Fetching messages
    *************************************************************************/
    public function getMessages($user_chat_id)
    {
        $messages = ChatMessage::select('users.*', 'chat_messages.*', 'chat_messages.created_at as message_date')
                        ->leftJoin('users', 'chat_messages.sender_id', '=', 'users.id')
                        ->orderBy('chat_messages.created_at', 'asc')
                        ->where('sender_id', $user_chat_id)
                        ->orWhere('receiver_id', $user_chat_id)
                        ->get();

        return response()->json(['messages' => $messages]);
    }


    /************************************************************************
    *Clear Chat
    *************************************************************************/
    public function clearChat(Request $request, $id)
    {
        if ($request->ajax())
        {
            $activity = ChatMessage::where('sender_id', $id)->orWhere('receiver_id', $id)->delete();
        }
        return response()->json(['success'=>'Chat cleared successfully.']);
    }


    /************************************************************************
    *Update Agent Status
    *************************************************************************/
    public function updateStatus(Request $request)
    {
        $userId = $request->input('user_id');
        $isActive = $request->input('is_active');

        // Update the active status in the database
        User::where('id', $userId)->update(['active' => $isActive]);

        return response()->json(['message' => 'Active status updated successfully']);
    }
}
