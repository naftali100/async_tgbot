<?php

namespace bot_lib;

class Update extends Api implements \ArrayAccess
{
    /** update */
    public array $update_arr = [];
    public $update_obj = null;
    public $update = null;

    /**the chat that the update from */
    public $chat = null;

    /** text in update (message, caption, query) */
    public $text;

    /** the user in update from */
    public $user;

    /** type of chat in update */
    public $chatType = null;

    /** 
     * update type 
     * 
     * full list: https://core.telegram.org/bots/api#update
     */
    public $updateType;

    /** forward info */
    public $forward = null;

    /** reply info */
    public $reply = null;

    /** inline info */
    public $inline = null;

    /** keyboard in update */
    public $keyboard = null;

    /** whether the update is service message */
    public $service = null;

    /** callback info. inline keyboard input */
    public $callback = null;

    /** update entities */
    public $ent = null;

    /** media in update */
    public ?array $media = null;

    /** md parsed text */
    public $mdText = null;

    /** whether callback_query answered or not */
    public $cb_answered = true;


    public function __construct(public Config $config, string $update = null)
    {
        if ($update != null) {
            $this->init_vars($update);
        }

        // if(!$this->update_obj->ok)
        //     throw new \Error($this->update_obj);

        if (isset($config->token)) {
            // set bot info
        }
    }

    public function delete()
    {
        if (isset($this->update)) {
            return $this->deleteMessage($this->chat->id, $this->message_id);
        }
    }

    public function edit($newMessage, $replyMarkup = null, $ent = null)
    {
        if (isset($this->update)) {
            if (!$this->service) {
                if ($this->media != null) {
                    return $this->editMessageCaption($this->chat->id, $this->message_id, $this->inline_message_id, $newMessage, $replyMarkup, entities: $ent);
                } else {
                    return $this->editMessageText($this->chat->id, $this->message_id, $this->inline_message_id, $newMessage, $replyMarkup, entities: $ent);
                }
            } else {
                return $this;
            }
        }
    }

    public function pin($dis_notification = null)
    {
        if (isset($this->update)) {
            if (!$this->service) {
                return $this->pinChatMessage($this->chat->id, $this->message_id, $dis_notification);
            } else {
                return $this;
            }
        }
    }

    public function forward($to, $noCredit = false, $rm = null, $replyTo = null, $caption = null, $ent = null)
    {
        if (isset($this->update) && !$this->has_protected_content) {
            if (!$this->service) {
                if ($noCredit) {
                    return $this->copyMessage($to, $this->chat->id, $this->message_id, $rm, $replyTo, $caption, $ent);
                } else {
                    return $this->forwardMessage($to, $this->chat->id, $this->message_id);
                }
            } else {
                return $this;
            }
        }
    }

    public function reply($text, $replyMarkup = null, $ent = null)
    {
        if (isset($this->update)) {
            return $this->sendMessage($this->chat->id ?? $this->from->id, $text, $replyMarkup, $this->message_id ?? null, entities: $ent);
        }
    }

    public function getUserStatus($id = null)
    {
        if (isset($this->update)) {
            return $this->getChatMember($id ?? $this->chat->id, $this->from->id);
        }
    }

    public function editKeyboard($newKeyboard)
    {
        if (isset($this->update)) {
            return $this->editMessageReplyMarkup($this->chat->id, $this->message_id, $this->inline_message_id, $newKeyboard);
        }
    }

    public function alert($text, $show = false)
    {
        if (isset($this->update)) {
            if ($this->data != null) {
                return $this->answerCallbackQUery($this->id, $text, $show);
            } else {
                return $this;
            }
        }
    }

    /**
     * new button must be ['text' => 'blabla', 'callback/url/etc' => 'data/url/etc'] 
     * 
     * to delete button - pass only the callback_data of the button
     */
    public function editButton($button_data, $new_button = null)
    {
        $buttons = $this->keyboard;
        $newkey = [];
        foreach ($buttons as $k => $v) {
            $row = [];
            foreach ($v as $button) {
                if (isset($button['callback_data']) && $button['callback_data'] == $button_data) { //TODO: add url buttons
                    if ($new_button != null) {
                        $row[] = $new_button;
                    }
                } else {
                    $row[] = $button;
                }
            }
            $newkey[] = $row;
        }
        $this->editKeyboard(json_encode(['inline_keyboard' => $newkey]));
    }

    public function ban($id = null, $removeMessages = false)
    {
        if ($this->chatType != 'private') {
            if ($this->sender_chat == null) {
                return $this->banChatMember($this->chat->id, $id ?? $this->from->id, deleteAllMessages: $removeMessages);
            } else {
                return $this->banChatSenderChat($this->chat->id, $id ?? $this->from->id);
            }
        } else {
            return $this;
        }
    }

    public function leave()
    {
        if ($this->chatType != 'private') {
            return $this->leaveChat($this->chat->id);
        } else {
            return $this;
        }
    }

    public function download()
    {
        if ($this->media != null) {
            return $this->getFile($this->media['file_id']);
        }
    }

    private function init_vars($update_string)
    {
        $local_update_obj = json_decode($update_string);
        $local_update_arr = json_decode($update_string, true);
        if ($local_update_obj == null) {
            throw new \Error('failed to parse json object');
        }

        if ($local_update_arr == null) {
            throw new \Error('failed to parse json array');
        }

        $this->update_obj = $this->update = $local_update_obj;
        $this->update_arr = $local_update_arr;

        $this->updateType = $updateType = array_keys($local_update_arr)[1];

        if ($updateType == 'callback_query') {
            $this->cb_answered = false;
        }
        // elseif($updateType == 'my_chat_member'){
        //     $update['my_chat_member'] = $update['my_chat_member']['message'] ?? $update['my_chat_member'];
        //     $local_update_obj->my_chat_member = $local_update_obj->my_chat_member->message ?? $local_update_obj->my_chat_member;
        // }

        $this->forward = $this->__get('forward_from') ?? $this->__get('forward_from_chat');
        $this->chat = $this->__get('chat');
        $this->from = $this->__get('sender_chat') ?? $this->__get('from') ?? $this->__get('chat');
        $this->reply = $this->__get('reply_to_message');
        $this->text = $this->__get('text') ?? $this->__get('caption') ?? $this->__get('query');
        $this->chatType = $this->__get('chat')?->type ?? $this->__get('chat_type');

        // check if can be object
        $this->ent = $this->__get('entities');
        $this->keyboard = $this->offsetGet('reply_markup')['inline_keyboard'] ?? null;

        // general data for all kind of files 
        $media = null;
        $fileTypes = ['photo', 'video', 'document', 'audio', 'sticker', 'voice', 'video_note'];
        foreach ($fileTypes as $type) {
            if (isset($local_update_arr[$updateType][$type])) {
                if ($type == 'photo') {
                    $media = $local_update_arr[$updateType]['photo'][count($local_update_arr[$updateType]['photo']) - 1];
                } else {
                    $media = $local_update_arr[$updateType][$type];
                }
                $media['file_type'] = $type;
                break;
            }
        }
        $this->media = $media;

        // if there ent in text its revers it to markdown and add `/```/*/_ to text
        $realText = $this->text;
        if ($this->ent != null) {
            $i = 0;
            foreach ($this->ent as $e) {
                if ($e->type == 'code')
                    $replacement = '`';
                elseif ($e->type == 'pre')
                    $replacement = '```';
                elseif ($e->type == 'bold')
                    $replacement = '*';
                elseif ($e->type == 'italic')
                    $replacement = '_';
                elseif ($e->type == 'spoiler')
                    $replacement = '|';
                else
                    continue;

                $realText = substr_replace($realText, $replacement, $e->offset + $i, 0);
                $realText = substr_replace($realText, $replacement, $e->offset + $e->length + strlen($replacement) + $i, 0);
                $i += strlen($replacement) * 2;
            }
        }
        $this->mdText = $realText;

        $this->service = false;
        $serviceTypes = [
            'channel_chat_created',
            'delete_chat_photo',
            'group_chat_created',
            'left_chat_member',
            'message_auto_delete_timer_changed',
            'migrate_from_chat_id',
            'new_chat_members',
            'new_chat_photo',
            'new_chat_title',
            'pinned_message',
            'proximity_alert_triggered',
            'supergroup_chat_created',
            'supergroup_chat_created',
            'voice_chat_ended',
            'voice_chat_participants_invited',
            'voice_chat_scheduled',
            'voice_chat_started'
        ];
        if (in_array($updateType, ['chat_member', 'my_chat_member'])) {
            $this->service = true;
        } else {
            foreach ($serviceTypes as $serviceType) {
                if (isset($local_update_arr[$updateType][$serviceType])) {
                    $this->service = true;
                    break;
                }
            }
        }
    }

    public function __get($value)
    {
        if (isset($this->update_obj->$value)) {
            return $this->update_obj->$value;
        }
        if (isset($this->update_obj->{$this->updateType}->$value)) {
            return $this->update_obj->{$this->updateType}->$value;
        }
        // in cbq update the message is inside cbq object
        if (isset($this->update_obj->{$this->updateType}->message->$value)) {
            return $this->update_obj->{$this->updateType}->message->$value;
        }
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->update_arr[] = $value;
        } else {
            $this->$offset = $value;
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->$offset != null;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->update_arr[$offset]);
    }

    public function offsetGet(mixed $offset)
    {
        $res = $this->$offset;
        if (is_object($res)) {
            return Helpers::objectToArray($res);
        } else if ($res != null) {
            return $res;
        }
    }
}
