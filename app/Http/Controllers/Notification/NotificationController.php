<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;


class NotificationController extends Controller
{
    //send results to parents
    public function publishMessage(Request $request){
        $results  = $request->get('results');
        $exam_id  = $request->get('exam_id');
        $class_id = $request->get('class_id');

        $exam = DB::table('exam_schedule')
                ->join('exam', 'exam.id', 'exam_schedule.exam_id')
                ->where('exam_schedule.id', $exam_id)
                ->select('code')
                ->first();
        
        //get current user
       $current_user = auth()->user();
       //get institution
       $sender       = $current_user->institution->code;
        
        foreach($results as $item) {
            $message = '';
            $skip = false;
            foreach($item as $key=>$value){
                //skip empty messages
                if($key == 'remark'){
                    if($value == 'INCOMPLETE'){
                      $skip = true;  
                      continue;
                    }
                }
                if($key != 'student_id'){
                    if( !($key == 'pos' || $key == 'class_id' || $key == 'total' || $key == 'average') ){
                        if($key == 'name'){
                            $split = preg_split('/\s+/', $value);
                            if(isset($split[2])){
                            $value = $split[0].' '.$split[1][0].' '.$split[2];
                            }else {
                            $value = $split[0].' '.$split[1];
                            }
                        }

                        $_key = $key != 'name'?strtoupper($key):'';
                        if($_key == 'DIVISION'){
                            $_key = 'DIV';
                        }
                        $message = $message.' '.$_key.' '.strtoupper($value);
                    }
                }else {
                    //get recepient
                    $receipient =  DB::table('student')
                                    ->where('id', $value)
                                    ->select('phone as message_id1')
                                    ->first();
                
                    $student_id = $value;
                }
            }
            //continue with next student 
            if($skip){
                continue;
            }
            //send message
            $notify = new Notification();
            $message = $sender.' '.$exam->code.', '.$message;
            //check if message has already been sent
            $exist = DB::table('delivery_report')
                    ->where('exam_id', $exam_id)
                    ->where('class_id', $class_id)
                    ->where('student_id', $student_id)
                    ->get();
            if($exist->count() == 0){
                $status =  $notify->sendMessage($message, $sender, $receipient);
                $data = array('receipient'=>$receipient->message_id1, 'message'=>$message, 'student_id'=> $student_id,
                            'is_sent'=>$status, 'class_id'=>$class_id, 'exam_id'=>$exam_id);
                DB::table('delivery_report')->insert($data);
            }
        }
        $message = 'Message has been sent to parents';
        
        return response()->json(compact('message'));
    }

    /** not in use */
    public function deliveryReport(Request $request){
        $status = $request->get('status');
        $code  = $request->get('code');
        $description = $request->get('description');
        DB::table('delivery_report')->insert(['status'=>$status, 'code'=>$code, 'description'=>$description]);
    }

    /** publish final result */
    public function sendFinalResult(Request $request){
        $results  = $request->get('results');
        $class_id = $request->get('class_id');
        
        //get current user
       $current_user = auth()->user();
       //get institution
       $sender = $current_user->institution->code;
       foreach($results as $item){
        $message = '';
        $skip = false;
        foreach($item as $key=>$value){
            //skip empty messages
            if($key == 'average'){
                if($value == null){
                  $skip = true;  
                  continue;
                }
            }
            if($key != 'result' ){ 
                if( !($key == 'pos' || $key == 'grand_total' || $key == 'average' ||  $key == 'class_size'
                    || $key == 'grade' || $key == 'student_id') ){
                    //modify student name
                    if($key == 'name'){
                        $split = preg_split('/\s+/', $value);

                        if(isset($split[2])){
                        $value = $split[0].' '.$split[1][0].' '.$split[2];
                        }else {
                        $value = $split[0].' '.$split[1];
                        }
                    }

                    $_key = $key != 'name'?strtoupper($key):'';
                    if($_key == 'DIVISION'){
                        $_key = 'DIV';
                    }else if($_key == 'POINTS'){
                        $_key = '';
                    }
                    $message = $message.' '.$_key.' '.strtoupper($value);
                }

                if($key == 'student_id') {
                    //get recepient
                    $receipient =  DB::table('student')
                    ->where('id', $value)
                    ->select('phone as message_id1','preferred_id')
                    ->first();

                    $_receipient = array('message_id1'=>$receipient->message_id1 );
                    $preferred_id = $receipient->preferred_id;
                    $student_id = $value;
                }
            }
        }
        //send message
        $notify = new Notification();
        $log = "Tembelea www.skongaweb.com upate matokeo ya mtoto wako. Tumia akaunti USERNAME ".$preferred_id." na NENOSIRI 12345";
        $message = $sender.' TERM EXAM '.$message.' '.$log;
        //send message
        $status =  $notify->sendMessage($message, $sender, $_receipient);
       
       }
       
            
        $message = 'Message has been sent to parents';
        
        return response()->json(compact('message'));

    }
}
