<?php

namespace App\Plugins\Telegram\Commands;

use App\Models\User;
use App\Plugins\Telegram\Telegram;

class Bind extends Telegram {
    public $command = '/bind';
    public $description = 'Bind your Telegram account to the site';

    public function handle($message, $match = []) {
        if (!$message->is_private) return;
        if (!isset($message->args[0])) {
            abort(500, 'There is an error in the parameters, please send with your subscription address');
        }
        $subscribeUrl = $message->args[0];
        $subscribeUrl = parse_url($subscribeUrl);
        parse_str($subscribeUrl['query'], $query);
        $token = $query['token'];
        if (!$token) {
            abort(500, 'The subscription address is invalid');
        }
        $user = User::where('token', $token)->first();
        if (!$user) {
            abort(500, 'The user does not exist');
        }
        if ($user->telegram_id) {
            abort(500, 'The account is already bind to a Telegram account');
        }
        $user->telegram_id = $message->chat_id;
        if (!$user->save()) {
            abort(500, 'Setup failure');
        }
        $telegramService = $this->telegramService;
        $telegramService->sendMessage($message->chat_id, 'Binding successfully');
    }
}
