<?php

namespace bot_lib;

/**
 * The main telegram Api methods are here
 */

class Api extends Http
{
    public function sendMessage($id, $text, $replyMarkup = null, $replyMessage = null, $entities = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['text'] = $this->text_adjust($text);
        $data['parse_mode'] = $this->config->ParseMode;
        $data['disable_web_page_preview'] = $this->config->webPagePreview;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['entities'] = $entities;
        $data['protect_content'] = $protectContent;
        $data['allow_sending_without_reply'] = true;
        return $this->ApiRequest('sendMessage', $data);
    }
    public function forwardMessage($id, $fromChatId, $messageId)
    {
        $data['chat_id'] = $id;
        $data['from_chat_id'] = $fromChatId;
        $data['disable_notification'] = $this->config->Notification;
        $data['message_id'] = $messageId;
        return $this->ApiRequest('forwardMessage', $data);
    }
    public function copyMessage($id, $from, $messageId, $replyMessage = null, $replyMarkup = null, $caption = null, $captionEnt = null)
    {
        $data['chat_id'] = $id;
        $data['from_chat_id'] = $from;
        $data['message_id'] = $messageId;
        $data['caption'] = $caption;
        $data['parse_mode'] = $this->config->ParseMode;
        $data['caption_entities'] = $captionEnt;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['allow_sending_without_reply'] = true;
        $data['reply_markup'] = $replyMarkup;
        return $this->ApiRequest('copyMessage', $data);
    }

    public function sendPhoto($id, $photo, $caption = null, $replyMessage = null, $replyMarkup = null, $entities = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['photo'] = $photo;
        $data['caption'] = $this->text_adjust($caption);
        $data['parse_mode'] = $this->config->ParseMode;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendPhoto', $data);
    }
    public function sendAudio($id, $audio, $caption = null, $thumb = null, $duration = null, $performer = null, $title = null, $replyMessage = null, $replyMarkup = null, $entities = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['audio'] = $audio;
        $data['caption'] = $caption;
        $data['thumb'] = $thumb;
        $data['duration'] = $duration;
        $data['performer'] = $performer;
        $data['title'] = $title;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendAudio', $data);
    }
    public function sendDocument($id, $document, $caption = null, $replyMessage = null, $replyMarkup = null, $entities = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['document'] = $document;
        $data['caption'] = $this->text_adjust($caption);
        $data['parse_mode'] = $this->config->ParseMode;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendDocument', $data);
    }
    public function sendSticker($id, $sticker, $replyMessage = null, $replyMarkup = null, $entities = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['sticker'] = $sticker;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendSticker', $data);
    }
    public function sendVideo($id, $video, $caption = null, $duration = null, $width = null, $height = null, $replyMessage = null, $replyMarkup = null, $entities = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['video'] = $video;
        $data['duration'] = $duration;
        $data['width'] = $width;
        $data['height'] = $height;
        $data['caption'] = $this->text_adjust($caption);
        $data['parse_mode'] = $this->ParseMode;
        $data['disable_notification'] = $this->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendVideo', $data);
    }
    public function sendVoice($id, $voice, $duration = null, $replyMessage = null, $replyMarkup = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['voice'] = $voice;
        $data['duration'] = $duration;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        return $this->ApiRequest('sendVoice', $data);
    }
    public function sendLocation($id, $latitude, $longitude, $replyMessage = null, $replyMarkup = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendLocation', $data);
    }
    public function sendVenue($id, $latitude, $longitude, $title, $address, $foursquare = null, $replyMessage = null, $replyMarkup = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;
        $data['title'] = $title;
        $data['address'] = $address;
        $data['foursquare_id'] = $foursquare;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendVenue', $data);
    }
    public function sendContact($id, $phoneNumber, $firstName, $lastName = null, $replyMessage = null, $replyMarkup = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['phone_number'] = $phoneNumber;
        $data['first_name'] = $firstName;
        $data['last_name'] = $lastName;
        $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendContact', $data);
    }
    public function sendDice($id, $emoji, $replyTo = null, $replyMarkup = null, bool $protectContent = false)
    {
        $data['chat_id'] = $id;
        $data['emoji'] = $emoji;
        $data['reply_to_message_id'] = $replyTo;
        $data['allow_sending_without_reply'] = true;
        $data['reply_markup'] = $replyMarkup;
        $data['disable_notification'] = $this->config->Notification;
        $data['protect_content'] = $protectContent;
        return $this->ApiRequest('sendDice', $data);
    }
    public function sendChatAction($id, $action)
    {
        if (!in_array($action, ['typing', 'upload_photo', 'record_video', 'upload_video', 'record_voice', 'upload_voice', 'upload_document', 'find_location', 'record_video_note', 'upload_video_note']))
            return false;
        $data['chat_id'] = $id;
        $data['action'] = $action;
        return $this->ApiRequest('sendChatAction', $data);
    }
    /** only available in non-official bot api */
    public function getMessage($chatId, $messageId)
    {
        $data['chat_id'] = $chatId;
        $data['message_id'] = $messageId;
        return $this->ApiRequest('getMessageInfo', $data);
    }
    public function getUserProfilePhotos($uId, $offset = null, $limit = null)
    {
        $data['user_id'] = $uId;
        $data['offset'] = $offset;
        $data['limit'] = $limit;
        return $this->ApiRequest('getUserProfilePhotos', $data);
    }
    public function banChatMember($id, $uId, $until = 0, $deleteAllMessages = false)
    {
        $data['chat_id'] = $id;
        $data['user_id'] = $uId;
        $data['until_date'] = $until;
        $data['revoke_messages'] = $deleteAllMessages;
        return $this->ApiRequest('kickChatMember', $data);
    }
    public function unbanChatMember($id, $uId)
    {
        $data['chat_id'] = $id;
        $data['user_id'] = $uId;
        return $this->ApiRequest('unbanChatMember', $data);
    }

    public function banChatSenderChat($id, $uId, $until = 0)
    {
        $data['chat_id'] = $id;
        $data['sender_chat_id'] = $uId;
        $data['until_date'] = $until;
        return $this->ApiRequest('banChatSenderChat', $data);
    }

    public function unbanChatSenderChat($id, $uId)
    {
        $data['chat_id'] = $id;
        $data['sender_chat_id'] = $uId;
        return $this->ApiRequest('unbanChatSenderChat', $data);
    }

    public function restrictChatMember($id, $user, $prem = null, $until = 0)
    {
        $data['chat_id'] = $id;
        $data['user_id'] = $user;
        $data['permissions'] = $prem ?? Helpers::build_prem();
        $data['until_date'] = $until;
        return $this->ApiRequest('restrictChatMember', $data);
    }

    public function promoteChatMember($id, $user, $is_anonymous, $can_manage_chat, $can_post_messages, $can_edit_messages, $can_delete_messages, $can_manage_video_chats, $can_restrict_members, $can_promote_members, $can_change_info, $can_invite_users, $can_pin_messages)
    {
        // TODO
    }

    public function setChatMenuButton($chat_id, $menu_button)
    {
        $data['chat_id'] = $chat_id;
        $data['menu_button'] = $menu_button;
        return $this->ApiRequest('setChatMenuButton', $data);
    }

    public function getChatMenuButton($chat_id)
    {
        $data['chat_id'] = $chat_id;
        return $this->ApiRequest('getChatMenuButton', $data);
    }

    public function getFile($fileId)
    {
        $data['file_id'] = $fileId;
        return $this->ApiRequest('getFile', $data);
    }
    public function leaveChat($id)
    {
        $data['chat_id'] = $id;
        return $this->ApiRequest('leaveChat', $data);
    }
    public function getChats()
    {
        return $this->ApiRequest('getChats');
    }
    public function getChat($id)
    {
        $data['chat_id'] = $id;
        return $this->ApiRequest('getChat', $data);
    }
    public function getChatAdministrators($id)
    {
        $data['chat_id'] = $id;
        return $this->ApiRequest('getChatAdministrators', $data);
    }
    public function getChatMemberCount($id)
    {
        $data['chat_id'] = $id;
        return $this->ApiRequest('getChatMemberCount', $data);
    }
    public function getChatMember($id, $uId)
    {
        $data['chat_id'] = $id;
        $data['user_id'] = $uId;
        return $this->ApiRequest('getChatMember', $data);
    }
    public function answerCallbackQuery($callback, $text = null, $alert = false)
    {
        $this->cb_answered = true;
        $data['callback_query_id'] = $callback;
        $data['text'] = $this->text_adjust($text);
        $data['show_alert'] = $alert;
        return $this->ApiRequest('answerCallbackQuery', $data);
    }
    public function editMessageText($id, $messageId, $inlineMessage, $text, $replyMarkup = null, $entities = null)
    {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['inline_message_id'] = $inlineMessage;
        $data['text'] = $this->text_adjust($text);
        $data['parse_mode'] = $this->config->ParseMode;
        $data['disable_web_page_preview'] = $this->config->webPagePreview;
        $data['reply_markup'] = $replyMarkup;
        $data['entities'] = $entities;
        return $this->ApiRequest('editMessageText', $data);
    }
    public function editMessageCaption($id = null, $messageId = null, $inlineMessage = null, $caption = null, $replyMarkup = null, $entities = null)
    {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['inline_message_id'] = $inlineMessage;
        $data['caption'] = $this->text_adjust($caption);
        $data['reply_markup'] = $replyMarkup;
        $data['caption_entities'] = $entities;
        return $this->ApiRequest('editMessageCaption', $data);
    }
    public function editMessageMedia($id = null, $messageId = null, $inlineMessage = null, $media = null, $replyMarkup = null)
    {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['inline_message_id'] = $inlineMessage;
        $data['media'] = $media;
        $data['reply_markup'] = $replyMarkup;
        return $this->ApiRequest('editMessageMedia', $data);
    }
    public function editMessageReplyMarkup($id = null, $messageId = null, $inlineMessage = null, $replyMarkup = null)
    {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['inline_message_id'] = $inlineMessage;
        $data['reply_markup'] = $replyMarkup;
        return $this->ApiRequest('editMessageReplyMarkup', $data);
    }
    public function deleteMessage($id, $messageId)
    {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        return $this->ApiRequest('deleteMessage', $data);
    }
    public function answerInlineQuery($inlineMessage, $res, $cacheTime = null, $isPersonal = null, $nextOffset = null, $switchPmText = null, $switchPmParameter = null)
    {
        $data['inline_query_id'] = $inlineMessage;
        $data['results'] = $res;
        $data['cache_time'] = $cacheTime;
        $data['is_personal'] = $isPersonal;
        $data['next_offset'] = $nextOffset;
        $data['switch_pm_text'] = $switchPmText;
        $data['switch_pm_parameter'] = $switchPmParameter;
        return $this->ApiRequest('answerInlineQuery', $data);
    }

    public function pinChatMessage($id, $messageId, $disable_notification = false)
    {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['disable_notification'] = $disable_notification;
        return $this->ApiRequest('pinChatMessage', $data);
    }

    public function getStickerSet($name)
    {
        $data['name'] = $name;
        return $this->ApiRequest('getStickerSet', $data);
    }

    public function logOut()
    {
        return $this->ApiRequest('logOut');
    }

    public function close()
    {
        return $this->ApiRequest('close');
    }

    public function setMyCommands($commands, $scope = null, $lang = null)
    {
        $data['commands'] = $commands;
        $data['scope'] = $scope;
        $data['language_code'] = $lang;
        return $this->ApiRequest('setMyCommands', $data);
    }

    public function deleteMyCommands($scope = null, $lang = null)
    {
        $data['scope'] = $scope;
        $data['language_code'] = $lang;
        return $this->ApiRequest('deleteMyCommands', $data);
    }

    public function getMyCommands($scope = null, $lang = null)
    {
        $data['scope'] = $scope;
        $data['language_code'] = $lang;
        return $this->ApiRequest('getMyCommands', $data);
    }

    public function createNewStickerSet(int $user, string $name, string $title, $tgs_sticker, $stickerType, $emojis, $maskPosition = null)
    {
        // TODO
    }

    /**
     * prepare the text to avoid send errors
     */
    public function text_adjust($text)
    {
        $type = gettype($text);
        if ($type == 'array' || $type == 'object') {
            $text = json_encode($text, TRUE | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } elseif ($type == 'NULL') {
            $text = 'NULL';
        }

        if (mb_strlen($text) > 4096) {
            $text = 'message is too long. ';
            // . $this->Request('https://nbots.ga/deldog/index.php', ['data' => $text]);
        }

        if ($this->config->ParseMode == 'markdown' && preg_match_all('/(@|(?<!\()http)\S+_\S*/', $text, $m) != 0) {
            foreach ($m[0] as $username) {
                $text = str_replace($username, str_replace('_', '\_', $username), $text);
            }
        }
        return $text;
    }

    function __call($func, $args)
    {
        $camelCaseFunc = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $func)));
        if (method_exists($this, $camelCaseFunc)) {
            return $this->$camelCaseFunc(...$args);
        } else {
            throw new \BadMethodCallException('call to undefined method ' . $func);
        }
    }
}
