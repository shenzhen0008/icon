<?php

namespace App\Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class StreamChatAgentPageController extends Controller
{
    public function __invoke(): View
    {
        $streamEnabled = filled(config('stream_chat.api_key')) && filled(config('stream_chat.api_secret'));

        return view('support.stream-chat-agent', [
            'streamEnabled' => $streamEnabled,
        ]);
    }
}
