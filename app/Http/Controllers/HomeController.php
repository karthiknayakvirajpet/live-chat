<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
    /************************************************************************
    *Index page
    *************************************************************************/
    public function index()
    {
        //get customers with chats
        $customer_ids = ChatMessage::select('sender_id')->distinct()->pluck('sender_id');
        $chat_list = User::whereIn('id', $customer_ids)->where('role', 2)->get();
        return view('home', compact('chat_list'));
    }
}
