<?php

namespace bot_lib;

class Update extends API implements \ArrayAccess{
    /** update */
    public $update_arr = null;
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

    /** forwad info */
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
        if ($update != null) 
            $this->init_vars($update);
        
        if(isset($config->token)){
            // set bot info
        }
    }

    // public function reply(string $message, array $reply_markup = NULL, $to_message_id = NULL, $to_id = NULL, $rm = NULL){

    // }

    public function delete()
    {
        if(isset($this->update))
        return $this->deleteMessage($this->chat->id, $this->message_id);
    }

    public function edit($newMessage, $replyMarkup = null, $ent = null)
    {
        if (isset($this->update))
        if (!$this->service) {
            if ($this->media != null)
                return $this->editMessageCaption($this->chat->id, $this->message_id, $this->inline_message_id, $newMessage, $replyMarkup, entities: $ent);
            else
                return $this->editMessageText($this->chat->id, $this->message_id, $this->inline_message_id, $newMessage, $replyMarkup, entities: $ent);
        } else
            return $this;
    }

    public function pin($dis_notification = null)
    {
        if (isset($this->update)){
            if ($this->chatType != 'private' && !$this->service)
                return $this->pinChatMessage($this->chat->id, $this->message_id, $dis_notification);
            else
                return $this;
        }
    }

    public function forward($to, $nocredit = false, $rm = null, $replyTo = null, $caption = null, $ent = null)
    {
        if(isset($this->update)){
            if (!$this->service) {
                if ($nocredit) {
                    $this->copyMessage($to, $this->chat->id, $this->message_id, $rm, $replyTo, $caption, $ent);
                } else
                    return $this->forwardMessage($to, $this->chat->id, $this->message_id);
            } else
                return $this;
        }
       
    }

    public function reply($text, $replyMarkup = null, $ent = null)
    {
        if (isset($this->update))
        return $this->sendMessage($this->chat->id ?? $this->from->id, $text, $replyMarkup, $this->message_id ?? null, entities: $ent);
    }

    public function editKeyboard($newKeyboard)
    {
        if (isset($this->update))
        return $this->editMessageReplyMarkup($this->chat->id, $this->message_id, $this->inline_message_id, $newKeyboard);
    }

    public function alert($text, $show = false)
    {
        if (isset($this->update)) {
            if ($this->data != null)
                return $this->answerCallbackQUery($this->id, $text, $show);
            else
                return $this;
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
                    if ($new_button != null)
                        $row[] = $new_button;
                } else
                    $row[] = $button;
            }
            $newkey[] = $row;
        }
        $this->editkeyboard(json_encode(['inline_keyboard' => $newkey]));
    }

    public function ban($id = null)
    {
        if ($this->chatType != 'private')
            $this->banChatMember($this->chat->id, $id ?? $this->from->id);
        else
            return $this;
    }

    public function leave()
    {
        if ($this->chatType != 'private')
            $this->leaveChat($this->chat->id);
        else
            return $this;
    }

    public function download(){
        if($this->media != null){
            return $this->getfile($this->media['file_id']);
        }
    }

    private function init_vars($update){
        $update_obj = json_decode($update);
        $this->update_obj = $this->update = json_decode($update);
        $this->update_arr = $update = json_decode($update, true);
    
        $this->updateType = $updateType = array_keys($update)[1];
        
        if ($updateType == 'callback_query') {
            $this->callback = $update_obj->callback_query;
            $this->cb_answered = false;

            // the callback update contain message update
            // update the update to $update[updateType]{update body}
            $update['callback_query'] = $update['callback_query']['message'] ?? $update['callback_query'];
            $update_obj->callback_query = $update_obj->callback_query->message ?? $update_obj->callback_query;
        }

        $this->forward = $update_obj->$updateType->forward_from ?? $update_obj->$updateType->forward_from_chat ?? null;
        $this->chat = $this->__get('chat') ?? null;
        $this->from = $this->update_arr['callback_query']['from'] ?? $update_obj->$updateType->sender_chat ?? $update_obj->$updateType->from ?? $this->chat ?? null;
        $this->reply = $update_obj->$updateType->reply_to_message ?? null;
        $this->text = $update[$updateType]['text'] ?? $update[$updateType]['caption'] ?? $update[$updateType]['query'] ?? null;
        $this->chatType = $this->chat->type ?? null;
        
        $this->ent = $update[$updateType]['entities']                                     ?? null;

        $this->keyboard = $update[$updateType]['reply_markup']['inline_keyboard']          ?? null;


        // general data for all kind of files 
        // there is also varibals for any kind below, you can use them both or delete one of them
        $media = null;
        $fileTypes = ['photo', 'video', 'document', 'audio', 'sticker', 'voice', 'video_note'];
        foreach ($fileTypes as $type) {
            if (isset($update[$updateType][$type])) {
                if ($type == 'photo') {
                    $media = $update[$updateType]['photo'][count($update[$updateType]['photo']) - 1];
                } else
                    $media = $update[$updateType][$type];
                $media['file_type'] = $type;
                break;
            }
        }
        $this->media = $media;

         // if thete ent in text its revers it to markdown and add `/```/*/_ to text
        $realtext = $this->text;
        if ($this->ent != null) {
            $i = 0;
            foreach ($this->ent as $e) {
                if ($e['type'] == 'code')
                    $replacment = '`';
                elseif ($e['type'] == 'pre')
                    $replacment = '```';
                elseif ($e['type'] == 'bold')
                    $replacment = '*';
                elseif ($e['type'] == 'italic')
                    $replacment = '_';
                else
                    continue;

                $realtext = substr_replace($realtext, $replacment, $e['offset'] + $i, 0);
                $realtext = substr_replace($realtext, $replacment, $e['offset'] + $e['length'] + strlen($replacment) + $i, 0);
                $i += strlen($replacment) * 2;
            }
        }
        $this->mdText = $realtext;

        $this->service = false;
        $serveiceTypes = [
            'new_chat_photo',
            'new_chat_members',
            'left_chat_member',
            'new_chat_title', 
            'delete_chat_photo',
            'group_chat_created',
            'supergroup_chat_created', 
            'channel_chat_created',
            'migrate_from_chat_id',
            'pinned_message',
            'channel_chat_created',
            'group_chat_created',
            'supergroup_chat_created',
            'proximity_alert_triggered',
            'delete_chat_photo',
            'message_auto_delete_timer_changed',
            'voice_chat_ended',
            'voice_chat_started',
            'voice_chat_scheduled',
            'voice_chat_participants_invited'
        ];
        if(in_array($updateType, ['chat_member', 'my_chat_member']))
            $this->service = true;
        else{
            foreach ($serveiceTypes as $serveiceType) {
                if (isset($update[$updateType][$serveiceType])) {
                    $this->service = true;
                    break;
                }
            }
        }
    }

    public function __get($value)
    {
        if (isset($this->update_obj->$value))
            return $this->update_obj->$value;
        if (isset($this->update_obj->{$this->updateType}->$value))
            return $this->update_obj->{$this->updateType}->$value;
        // in cbq update the message is inside cbq object
        if (isset($this->message->$value)) 
            return $this->message->$value;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->update_arr[] = $value;
        } else {
            $this->update_arr[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->update_arr[$offset]) || isset($this->$offset);
    }

    public function offsetUnset($offset)
    {
        unset($this->update_arr[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->$offset))
            return $this->$offset;
        if (isset($this->update_arr[$offset]))
            return $this->update_arr[$offset];
        if (isset($this->update_arr[$this->updateType][$offset]))
            return $this->update_arr[$this->updateType][$offset];
    }
}