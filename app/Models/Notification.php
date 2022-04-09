<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Notification extends Model
{
    use HasFactory;

    protected $hidden = ['pivot'];
    protected $table = 'notification';
    protected $primaryKey = 'id';


    //@data - contain text message
    //@sender - contain name of the sender
    //@receipient - array of the message receipients
    //function to send text message
    public function sendMessage($data, $sender, $receipient){
        $token = "0162b65ee02e7172bb6caf2fa9953597";
        $push  = "https://skongaweb.com/api/delivery_report";

        $data = [
            'token'   => $token,
            'sender'  => 'INFO',
            'message' => json_encode($data),
            'push'    => $push,
            'recipient' => $receipient
        ];
        
        //$response = Http::withToken($token)->post('http://login.smsmtandao.com/smsmtandaoapi/send', $data);
        if($response->successful()){
            return true;
        }else {
            return false;
        }
    }

}
