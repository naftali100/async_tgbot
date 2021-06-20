<?php

namespace bot_lib;

class Update extends API{

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
        return $this->deleteMessage($this->chatId, $this->messageId);
    }

    public function edit($newMessage, $replyMarkup = null, $ent = null)
    {
        if (isset($this->update))
        if (!$this->service_message) {
            if ($this->media != null)
                return $this->editMessageCaption($this->chatId, $this->messageId, $this->inlineMessageId, $newMessage, $replyMarkup, entities: $ent);
            else
                return $this->editMessageText($this->chatId, $this->messageId, $this->inlineMessageId, $newMessage, $replyMarkup, entities: $ent);
        } else
            return $this;
    }

    public function pin($dis_notification = null)
    {
        if (isset($this->update)){
            if ($this->chatType != "private" && !$this->service_message)
                return $this->pinChatMessage($this->chatId, $this->messageId, $dis_notification);
            else
                return $this;
        }
    }

    public function forward($to, $nocredit = false, $rm = null, $replyTo = null, $caption = null, $ent = null)
    {
        if(isset($this->update)){
            if (!$this->service_message) {
                if ($nocredit) {
                    $this->copyMessage($to, $this->chatId, $this->messageId, $rm, $replyTo, $caption, $ent);
                } else
                    return $this->forwardMessage($to, $this->chatId, $this->messageId);
            } else
                return $this;
        }
       
    }

    public function reply($text, $replyMarkup = null, $ent = null)
    {
        if (isset($this->update))
        return $this->sendMessage($this->chatId ?? $this->fromId, $text, $replyMarkup, $this->messageId ?? null, entities: $ent);
    }

    public function editKeyboard($newKeyboard)
    {
        if (isset($this->update))
        return $this->editMessageReplyMarkup($this->chatId, $this->messageId, $this->inlineMessageId, $newKeyboard);
    }

    public function alert($text, $show = false)
    {
        if (isset($this->update)) {
            if ($this->data != null)
                return $this->answerCallbackQUery($this->callId, $text, $show);
            else
                return $this;
        }
    }

    /**
     * new button must be ["text" => "blabla", "callback/url/etc" => "data/url/etc"] 
     * 
     * to delete button - pass only the callback_data of the button
     */
    public function editButton($button_data, $new_button = null)
    {
        $buttons = $this->buttons;
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
        $this->editkeyboard(json_encode(["inline_keyboard" => $newkey]));
    }

    public function ban($id = null)
    {
        if ($this->chatType != "private")
            $this->kickChatMember($this->chatId, $id ?? $this->fromId);
        else
            return $this;
    }

    public function leave()
    {
        if ($this->chatType != "private")
            $this->leaveChat($this->chatId);
        else
            return $this;
    }

    public function download(){
        if($this->media != null){
            return $this->getfile($this->media["file_id"]);
        }
    }

    private function init_vars($update){
        $this->update = $update = json_decode($update, true);
    
        $this->updateType = $updateType = array_keys($update)[1];
        
        // the callback update contain message update
        if ($updateType == 'callback_query') {

            // the clicker data
            $this->callFromId = $update["callback_query"]['from']['id'];
            $this->callId = $update["callback_query"]["id"];
            $this->callback_data = $update["callback_query"]["data"];
            $this->data = $update["callback_query"]["data"];
            $this->inlineMessageId = $update["callback_query"]["inline_message_id"]      ?? null;

            // update the update to $update[updateType]{update body}
            $update['callback_query'] = $update['callback_query']['message'] ?? $update['callback_query'];
        } else {
            $this->data = null;
            $this->inlineMessageId = null;
        }

        // global vars for all kinds of updates

        // obj notation
        // $this->update = $update;
        // $this->forward = $update->$updateType->forward_from ?? $update->$updateType->forward_from_chat ?? null;
        // $this->from = $update->$updateType->sender_chat ?? $update->$updateType->from ?? null;
        // $this->chat = $update->$updateType->chat ?? null;
        // $this->reply = $update->$updateType->reply_to_message ?? null;

        $this->result = $update["result"] ?? "";

        $this->user_name = $this->userName = $update[$updateType]["chat"]["username"]     ?? null;
        $this->chatId = $update[$updateType]["chat"]["id"]                                ?? null;
        $this->FirstName = $update[$updateType]["chat"]["first_name"]                     ?? null;
        $this->LastName = $update[$updateType]["chat"]["last_name"]                       ?? null;

        
        $this->fromId = $update[$updateType]["from"]["id"]                                ?? null;
        $this->fromUserName = $update[$updateType]["from"]["username"]                    ?? null;
        $this->fromFirstName = $update[$updateType]["from"]["first_name"]                 ?? null;
        $this->fromLastName = $update[$updateType]["from"]["last_name"]                   ?? null;

        $this->chatType = $update[$updateType]["chat"]["type"]                            ?? null;
        $this->message = $update[$updateType]["text"] ?? $update[$updateType]['caption']  ?? null;
        $this->text = $update[$updateType]["text"] ?? $update[$updateType]['caption'] ?? $update[$updateType]['query'] ?? null;
        $this->messageId = $update[$updateType]['message_id']                             ?? null;
        $this->title = $update[$updateType]["chat"]["title"]                              ?? null;

        $this->cap = $update[$updateType]['caption']                                      ?? null;

        // FORWARD
        // f - for forward, ff - for forward from
        // c - for chat
        // $this->forward = $update[$updateType]['forward_from'] ?? $update[$updateType]['forward_from_chat'] ?? null;
        // $this->forward = $this->update->$updateType->forward_from;
        $this->fId = $update[$updateType]['forward_from']['id']                           ?? null;
        $this->fFN = $update[$updateType]['forward_from']['first_name']                   ?? null;
        $this->fLN = $update[$updateType]['forward_from']['last_name']                    ?? null;
        $this->fdN = $update[$updateType]['forward_from']['username']                     ?? null;
        $this->ffcId = $update[$updateType]['forward_from_chat']['id']                    ?? null;
        $this->ffcun = $update[$updateType]['forward_from_chat']['username']              ?? null;
        // $this->ffmid = forward - from - message - id

        // reply
        // r - for reply
        // m - for message
        // f - for from, ff - for forward from
        // t - for text
        // $this->update->$updateType->reply_to = $update[$updateType]['reply_to_message']  ?? null;
        $this->rfid = $update[$updateType]['reply_to_message']['from']['id']             ?? null;
        $this->rfUN = $update[$updateType]['reply_to_message']['from']['username']       ?? null;
        $this->rfFN = $update[$updateType]['reply_to_message']['from']['first_name']     ?? null;
        $this->rfLN = $update[$updateType]['reply_to_message']['from']['last_name']      ?? null;
        $this->rmid = $update[$updateType]['reply_to_message']['message_id']             ?? null;
        $this->rmt = $update[$updateType]['reply_to_message']['text']                    ?? null;
        $this->rffid = $update[$updateType]['reply_to_message']['forward_from']['id']    ?? null;


        //Inline
        $this->inlineQ = $update["inline_query"]["query"]                                 ?? null;
        $this->inlineQId = $update["inline_query"]["id"]                                  ?? null;
        $this->inlineQfromId = $update["inline_query"]["from"]["id"]                      ?? null;

        $this->ent = $update[$updateType]['entities']                                     ?? null;

        $this->buttons = $update[$updateType]["reply_markup"]["inline_keyboard"]          ?? null;


        // general data for all kind of files 
        // there is also varibals for any kind below, you can use them both or delete one of them
        $general_file = null;
        $fileTypes = ['photo', 'video', 'document', 'audio', 'sticker', 'voice', 'video_note'];
        foreach ($fileTypes as $type) {
            if (isset($update[$updateType][$type])) {
                if ($type == "photo") {
                    $general_file = $update[$updateType]['photo'][count($update[$updateType]['photo']) - 1];
                } else
                    $general_file = $update[$updateType][$type];
                $general_file["file_type"] = $type;
                break;
            }
        }
        $this->general_file = $general_file;
        $this->media = $general_file;


        //contact
        $this->conph = $update[$updateType]['contact']['phone_number']               ?? null;
        $this->conf = $update[$updateType]['contact']['first_name']                  ?? null;
        $this->conl = $update[$updateType]['contact']['last_name']                   ?? null;
        $this->conid = $update[$updateType]['contact']['user_id']                    ?? null;
        //location
        $this->locid1 = $update[$updateType]['location']['latitude']                 ?? null;
        $this->locid2 = $update[$updateType]['location']['longitude']                ?? null;
        //Venue
        $this->venLoc1 = $update[$updateType]['venue']['location']['latitude']       ?? null;
        $this->venLoc2 = $update[$updateType]['venue']['location']['longitude']      ?? null;
        $this->venTit = $update[$updateType]['venue']['title']                       ?? null;
        $this->venAdd = $update[$updateType]['venue']['address']                     ?? null;


        // if thete ent in text its revers it to markdown and add `/```/*/_ to text
        $realtext = $this->message;
        if ($this->ent != null) {
            $i = 0;
            foreach ($this->ent as $e) {
                if ($e['type'] == "code")
                    $replacment = "`";
                elseif ($e['type'] == "pre")
                    $replacment = "```";
                elseif ($e['type'] == "bold")
                    $replacment = "*";
                elseif ($e['type'] == "italic")
                    $replacment = "_";
                else
                    continue;


                $realtext = Helpers::entToRealTxt($realtext, $replacment, $e['offset'], $e['length'], $i);
                $i += strlen($replacment) * 2;
            }
        }
        $this->realtext = $realtext;

        $this->service_message = false;
        $serveiceTypes = ["new_chat_photo", "new_chat_members", "left_chat_member", "new_chat_title", "delete_chat_photo", "group_chat_created", "supergroup_chat_created", "channel_chat_created", "migrate_from_chat_id", "pinned_message"];
        if(in_array($updateType, ["chat_member", "my_chat_member"]))
            $this->service_message = true;
        else
            foreach ($serveiceTypes as $serveiceType) {
                if (isset($update[$updateType][$serveiceType])) {
                    $this->service_message = true;
                    break;
                }
            }
    }

    public function __get($value){
        if(isset($this->update->$value))
            return $this->update->$value;
    }
}