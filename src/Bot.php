<?php

namespace bot_lib;

use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\ByteStream;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

/**
 *
 * The main class for each bot
 *
 * an instance of this class will be created for each update
 *
 * the file name of your bot must be the same as the name of the class
 */
abstract class Bot
{
    public Http $http;
    private Logger $log;
    public function __construct(public Config $config)
    {
        $this->http = new Http($this->config);

        $logHandler = new StreamHandler(ByteStream\getStdout());
        $logHandler->pushProcessor(new PsrLogMessageProcessor());
        $logHandler->setFormatter(new ConsoleFormatter());
        $logHandler->setLevel('Info');
        $logger = new Logger('Bot ' . get_class($this));
        $logger->pushHandler($logHandler);
        $this->log = $logger;
    }
    /**
     * the function that will be called to handle the update
     * @param \bot_lib\Update $update
     * @return void
     */
    abstract public function handleUpdate(Update $update);

    /**
     * override this method to be called in case of an error
     * @param \Throwable $e
     * @return void
     */
    public function onError(\Throwable $e)
    {
    }

    /**
     * optionally override this function that will be called before the main handleUpdate
     */
    public function before(Update $update)
    {
    }

    /**
     * optionally override this function that will bw called after the main handleUpdate
     */
    public function after(Update $update)
    {
    }

    /***********
     * API Methods
     ***********/

    /**
     * get me
     */
    public function getMe()
    {
        return $this->http->apiRequest('getMe');
    }
    public function sendMessage(
        $id,
        $text,
        $replyMarkup = null,
        $replyMessage = null,
        $entities = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['text'] = $this->textAdjust($text);
        // $data['parse_mode'] = $this->config->ParseMode;
        // $data['disable_web_page_preview'] = $this->config->webPagePreview;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['entities'] = $entities;
        $data['protect_content'] = $protectContent;
        $data['allow_sending_without_reply'] = true;
        return $this->http->apiRequest('sendMessage', $data);
    }
    public function forwardMessage(
        $id,
        $fromChatId,
        $messageId,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['from_chat_id'] = $fromChatId;
        // $data['disable_notification'] = $this->config->Notification;
        $data['message_id'] = $messageId;
        return $this->http->apiRequest('forwardMessage', $data);
    }
    public function copyMessage(
        $id,
        $from,
        $messageId,
        $replyMessage = null,
        $replyMarkup = null,
        $caption = null,
        $captionEnt = null,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['from_chat_id'] = $from;
        $data['message_id'] = $messageId;
        $data['caption'] = $caption;
        // $data['parse_mode'] = $this->config->ParseMode;
        $data['caption_entities'] = $captionEnt;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['allow_sending_without_reply'] = true;
        $data['reply_markup'] = $replyMarkup;
        return $this->http->apiRequest('copyMessage', $data);
    }

    public function sendMediaGroup(
        $chat,
        $media,
        $reply_to = null,
        $protected = null,
        $thread = null
    ) {
        $data['chat_id'] = $chat;
        $data['media'] = $media;
        $data['protect_content'] = $protected;
        $data['reply_to_message_id'] = $reply_to;
        $data['allow_sending_without_reply'] = true;
        $data['message_thread_id'] = $thread;

        return $this->http->apiRequest('sendMediaGroup', $data);
    }

    public function sendPhoto(
        $id,
        $photo,
        $caption = null,
        $replyMessage = null,
        $replyMarkup = null,
        $entities = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['photo'] = $photo;
        $data['caption'] = $this->textAdjust($caption);
        // $data['parse_mode'] = $this->config->ParseMode;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->http->apiRequest('sendPhoto', $data);
    }
    public function sendAudio(
        $id,
        $audio,
        $caption = null,
        $thumb = null,
        $duration = null,
        $performer = null,
        $title = null,
        $replyMessage = null,
        $replyMarkup = null,
        $entities = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['audio'] = $audio;
        $data['caption'] = $caption;
        $data['thumbnail'] = $thumb;
        $data['duration'] = $duration;
        $data['performer'] = $performer;
        $data['title'] = $title;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->http->apiRequest('sendAudio', $data);
    }
    public function sendDocument(
        $id,
        $document,
        $caption = null,
        $replyMessage = null,
        $replyMarkup = null,
        $entities = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['document'] = $document;
        $data['caption'] = $this->textAdjust($caption);
        // $data['parse_mode'] = $this->config->ParseMode;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->http->apiRequest('sendDocument', $data);
    }
    public function sendSticker(
        $id,
        $sticker,
        $replyMessage = null,
        $replyMarkup = null,
        $entities = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['sticker'] = $sticker;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->http->apiRequest('sendSticker', $data);
    }
    public function sendVideo(
        $id,
        $video,
        $caption = null,
        $duration = null,
        $width = null,
        $height = null,
        $replyMessage = null,
        $replyMarkup = null,
        $entities = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['video'] = $video;
        $data['duration'] = $duration;
        $data['width'] = $width;
        $data['height'] = $height;
        $data['caption'] = $this->textAdjust($caption);
        // $data['parse_mode'] = $this->ParseMode;
        // $data['disable_notification'] = $this->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['allow_sending_without_reply'] = true;
        $data['caption_entities'] = $entities;
        $data['protect_content'] = $protectContent;
        return $this->http->apiRequest('sendVideo', $data);
    }
    public function sendVoice(
        $id,
        $voice,
        $duration = null,
        $replyMessage = null,
        $replyMarkup = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['voice'] = $voice;
        $data['duration'] = $duration;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        return $this->http->apiRequest('sendVoice', $data);
    }
    public function sendLocation(
        $id,
        $latitude,
        $longitude,
        $replyMessage = null,
        $replyMarkup = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['protect_content'] = $protectContent;
        return $this->http->apiRequest('sendLocation', $data);
    }
    public function sendVenue(
        $id,
        $latitude,
        $longitude,
        $title,
        $address,
        $foursquare = null,
        $replyMessage = null,
        $replyMarkup = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;
        $data['title'] = $title;
        $data['address'] = $address;
        $data['foursquare_id'] = $foursquare;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['protect_content'] = $protectContent;
        return $this->http->apiRequest('sendVenue', $data);
    }
    public function sendContact(
        $id,
        $phoneNumber,
        $firstName,
        $lastName = null,
        $replyMessage = null,
        $replyMarkup = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['phone_number'] = $phoneNumber;
        $data['first_name'] = $firstName;
        $data['last_name'] = $lastName;
        // $data['disable_notification'] = $this->config->Notification;
        $data['reply_to_message_id'] = $replyMessage;
        $data['reply_markup'] = $replyMarkup;
        $data['protect_content'] = $protectContent;
        return $this->http->apiRequest('sendContact', $data);
    }
    public function sendDice(
        $id,
        $emoji,
        $replyTo = null,
        $replyMarkup = null,
        bool $protectContent = false,
        $threadId = null
    ) {
        $data['chat_id'] = $id;
        $data['emoji'] = $emoji;
        $data['reply_to_message_id'] = $replyTo;
        $data['allow_sending_without_reply'] = true;
        $data['reply_markup'] = $replyMarkup;
        // $data['disable_notification'] = $this->config->Notification;
        $data['protect_content'] = $protectContent;
        $data['message_thread_id'] = $threadId;
        return $this->http->apiRequest('sendDice', $data);
    }
    public function sendChatAction(
        $id,
        $action
    ) {
        if (!in_array($action, ['typing', 'upload_photo', 'record_video', 'upload_video', 'record_voice', 'upload_voice', 'upload_document', 'find_location', 'record_video_note', 'upload_video_note'])) {
            return false;
        }
        $data['chat_id'] = $id;
        $data['action'] = $action;
        return $this->http->apiRequest('sendChatAction', $data);
    }
    /** only available in non-official bot api */
    public function getMessage(
        $chatId,
        $messageId
    ) {
        $data['chat_id'] = $chatId;
        $data['message_id'] = $messageId;
        return $this->http->apiRequest('getMessageInfo', $data);
    }
    public function getUserProfilePhotos(
        $uId,
        $offset = null,
        $limit = null
    ) {
        $data['user_id'] = $uId;
        $data['offset'] = $offset;
        $data['limit'] = $limit;
        return $this->http->apiRequest('getUserProfilePhotos', $data);
    }
    public function banChatMember(
        $id,
        $uId,
        $until = 0,
        $deleteAllMessages = false
    ) {
        $data['chat_id'] = $id;
        $data['user_id'] = $uId;
        $data['until_date'] = $until;
        $data['revoke_messages'] = $deleteAllMessages;
        return $this->http->apiRequest('kickChatMember', $data);
    }
    public function unbanChatMember(
        $id,
        $uId
    ) {
        $data['chat_id'] = $id;
        $data['user_id'] = $uId;
        return $this->http->apiRequest('unbanChatMember', $data);
    }

    public function banChatSenderChat(
        $id,
        $uId,
        $until = 0
    ) {
        $data['chat_id'] = $id;
        $data['sender_chat_id'] = $uId;
        $data['until_date'] = $until;
        return $this->http->apiRequest('banChatSenderChat', $data);
    }

    public function unbanChatSenderChat(
        $id,
        $uId
    ) {
        $data['chat_id'] = $id;
        $data['sender_chat_id'] = $uId;
        return $this->http->apiRequest('unbanChatSenderChat', $data);
    }

    public function restrictChatMember(
        $id,
        $user,
        $prem = null,
        $until = 0
    ) {
        $data['chat_id'] = $id;
        $data['user_id'] = $user;
        // $data['permissions'] = $prem ?? Helpers::permissions('block');
        $data['until_date'] = $until;
        return $this->http->apiRequest('restrictChatMember', $data);
    }

    public function promoteChatMember(
        $id,
        $user,
        $is_anonymous,
        $can_manage_chat,
        $can_post_messages,
        $can_edit_messages,
        $can_delete_messages,
        $can_manage_video_chats,
        $can_restrict_members,
        $can_promote_members,
        $can_change_info,
        $can_invite_users,
        $can_pin_messages
    ) {
        // TODO
    }

    public function setChatMenuButton(
        $chat_id,
        $menu_button
    ) {
        $data['chat_id'] = $chat_id;
        $data['menu_button'] = $menu_button;
        return $this->http->apiRequest('setChatMenuButton', $data);
    }

    public function getChatMenuButton(
        $chat_id
    ) {
        $data['chat_id'] = $chat_id;
        return $this->http->apiRequest('getChatMenuButton', $data);
    }

    public function getFile(
        $fileId
    ) {
        $data['file_id'] = $fileId;
        return $this->http->apiRequest('getFile', $data);
    }
    public function leaveChat(
        $id
    ) {
        $data['chat_id'] = $id;
        return $this->http->apiRequest('leaveChat', $data);
    }
    public function getChats()
    {
        return $this->http->apiRequest('getChats');
    }
    public function getChat(
        $id
    ) {
        $data['chat_id'] = $id;
        return $this->http->apiRequest('getChat', $data);
    }
    public function getChatAdministrators(
        $id
    ) {
        $data['chat_id'] = $id;
        return $this->http->apiRequest('getChatAdministrators', $data);
    }
    public function getChatMemberCount(
        $id
    ) {
        $data['chat_id'] = $id;
        return $this->http->apiRequest('getChatMemberCount', $data);
    }
    public function getChatMember(
        $chat_id,
        $user_id
    ) {
        $data['chat_id'] = $chat_id;
        $data['user_id'] = $user_id;
        return $this->http->apiRequest('getChatMember', $data);
    }
    public function answerCallbackQuery(
        $callback,
        $text = null,
        $alert = false
    ) {
        // $this->cb_answered = true;
        $data['callback_query_id'] = $callback;
        $data['text'] = $text;
        $data['show_alert'] = $alert;
        return $this->http->apiRequest('answerCallbackQuery', $data);
    }
    public function editMessageText(
        $id,
        $messageId,
        $inlineMessage,
        $text,
        $replyMarkup = null,
        $entities = null
    ) {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['inline_message_id'] = $inlineMessage;
        $data['text'] = $this->textAdjust($text);
        // $data['parse_mode'] = $this->config->ParseMode;
        // $data['disable_web_page_preview'] = $this->config->webPagePreview;
        $data['reply_markup'] = $replyMarkup;
        $data['entities'] = $entities;
        return $this->http->apiRequest('editMessageText', $data);
    }
    public function editMessageCaption(
        $id = null,
        $messageId = null,
        $inlineMessage = null,
        $caption = null,
        $replyMarkup = null,
        $entities = null
    ) {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['inline_message_id'] = $inlineMessage;
        $data['caption'] = $this->textAdjust($caption);
        $data['reply_markup'] = $replyMarkup;
        $data['caption_entities'] = $entities;
        return $this->http->apiRequest('editMessageCaption', $data);
    }
    public function editMessageMedia(
        $id = null,
        $messageId = null,
        $inlineMessage = null,
        $media = null,
        $replyMarkup = null
    ) {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['inline_message_id'] = $inlineMessage;
        $data['media'] = $media;
        $data['reply_markup'] = $replyMarkup;
        return $this->http->apiRequest('editMessageMedia', $data);
    }
    public function editMessageReplyMarkup(
        $id = null,
        $messageId = null,
        $inlineMessage = null,
        $replyMarkup = null
    ) {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['inline_message_id'] = $inlineMessage;
        $data['reply_markup'] = $replyMarkup;
        return $this->http->apiRequest('editMessageReplyMarkup', $data);
    }
    public function deleteMessage(
        $id,
        $messageId
    ) {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        return $this->http->apiRequest('deleteMessage', $data);
    }
    public function answerInlineQuery(
        $queryId,
        $res,
        $cacheTime = null,
        $isPersonal = null,
        $nextOffset = null,
        $switchPmText = null,
        $switchPmParameter = null
    ) {
        $data['inline_query_id'] = $queryId;
        $data['results'] = $res;
        $data['cache_time'] = $cacheTime;
        $data['is_personal'] = $isPersonal;
        $data['next_offset'] = $nextOffset;
        $data['switch_pm_text'] = $switchPmText;
        $data['switch_pm_parameter'] = $switchPmParameter;
        return $this->http->apiRequest('answerInlineQuery', $data);
    }

    public function pinChatMessage(
        $id,
        $messageId,
        $disable_notification = false
    ) {
        $data['chat_id'] = $id;
        $data['message_id'] = $messageId;
        $data['disable_notification'] = $disable_notification;
        return $this->http->apiRequest('pinChatMessage', $data);
    }

    public function getStickerSet(
        $name
    ) {
        $data['name'] = $name;
        return $this->http->apiRequest('getStickerSet', $data);
    }

    public function logOut()
    {
        return $this->http->apiRequest('logOut');
    }

    public function close()
    {
        return $this->http->apiRequest('close');
    }

    public function setMyCommands(
        $commands,
        $scope = null,
        $lang = null
    ) {
        $data['commands'] = $commands;
        $data['scope'] = $scope;
        $data['language_code'] = $lang;
        return $this->http->apiRequest('setMyCommands', $data);
    }

    public function deleteMyCommands(
        $scope = null,
        $lang = null
    ) {
        $data['scope'] = $scope;
        $data['language_code'] = $lang;
        return $this->http->apiRequest('deleteMyCommands', $data);
    }

    public function getMyCommands(
        $scope = null,
        $lang = null
    ) {
        $data['scope'] = $scope;
        $data['language_code'] = $lang;
        return $this->http->apiRequest('getMyCommands', $data);
    }

    public function createNewStickerSet(
        int $user,
        string $name,
        string $title,
        $tgs_sticker,
        $stickerType,
        $emojis,
        $maskPosition = null
    ) {
        // TODO
    }

    public function approveChatJoinRequest(
        $chat,
        $user
    ) {
        $data['chat_id'] = $chat;
        $data['user_id'] = $user;
        return $this->http->apiRequest('approveChatJoinRequest', $data);
    }

    public function declineChatJoinRequest(
        $chat,
        $user
    ) {
        $data['chat_id'] = $chat;
        $data['user_id'] = $user;
        return $this->http->apiRequest('declineChatJoinRequest', $data);
    }

    public function createForumTopic(
        $chat,
        $name,
        int $icon_color,
        $icon_custom_emoji_id
    ) {
        $data['chat_id'] = $chat;
        $data['name'] = $name;
        $data['icon_color'] = $icon_color;
        $data['icon_custom_emoji_id'] = $icon_custom_emoji_id;
        return $this->http->apiRequest('createForumTopic', $data);
    }
    public function editGeneralForumTopic(
        $chat,
        $name
    ) {
        $data['chat_id'] = $chat;
        $data['name'] = $name;
        return $this->http->apiRequest('editGeneralForumTopic', $data);
    }
    public function editForumTopic(
        $chat,
        $thread,
        $newName,
        $newCustomEmoji
    ) {
        $data['chat_id'] = $chat;
        $data['message_thread_id'] = $thread;
        $data['name'] = $newName;
        $data['icon_custom_emoji_id'] = $newCustomEmoji;
        return $this->http->apiRequest('editForumTopic', $data);
    }
    public function closeForumTopic(
        $chat,
        $thread
    ) {
        $data['chat_id'] = $chat;
        $data['message_thread_id'] = $thread;
        return $this->http->apiRequest('closeForumTopic', $data);
    }
    public function reopenForumTopic(
        $chat,
        $thread
    ) {
        $data['chat_id'] = $chat;
        $data['message_thread_id'] = $thread;
        return $this->http->apiRequest('reopenForumTopic', $data);
    }
    public function deleteForumTopic(
        $chat,
        $thread
    ) {
        $data['chat_id'] = $chat;
        $data['message_thread_id'] = $thread;
        return $this->http->apiRequest('deleteForumTopic', $data);
    }
    public function unpinAllForumTopicMessages(
        $chat,
        $thread
    ) {
        $data['chat_id'] = $chat;
        $data['message_thread_id'] = $thread;
        return $this->http->apiRequest('unpinAllForumTopicMessages', $data);
    }
    public function getForumTopicIconStickers()
    {
        return $this->http->apiRequest('getForumTopicIconStickers');
    }
    public function setWebhook($url)
    {
        $data['url'] = $url;
        return $this->http->apiRequest('setWebhook', $data);
    }

    /**
     * prepare the text to avoid send errors
     */
    public function textAdjust($text)
    {
        $type = gettype($text);
        if ($type == 'array' || $type == 'object') {
            $text = json_encode($text, true | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } elseif ($type == 'NULL') {
            $text = 'NULL';
        }

        if (mb_strlen($text) > 4096) {
            $text = 'message is too long.';
            // . $this->Request('https://nbots.ga/deldog/index.php', ['data' => $text]);
        }

        // if ($this->config->ParseMode == 'markdown' && preg_match_all('/(@|(?<!\()http)\S+_\S*/', $text, $m) != 0) {
        //   foreach ($m[0] as $username) {
        //     $text = str_replace($username, str_replace('_', '\_', $username), $text);
        //   }
        // }
        return $text;
    }
}
