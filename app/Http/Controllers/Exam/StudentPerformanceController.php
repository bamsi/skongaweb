<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPerformanceController extends Controller
{
    //get performance
    public function getPerformance($class_id, $student_id, $term_id){
      $result = DB::table('performance')
                ->join('class', 'class.level_id', 'performance.level_id')
                ->leftJoin('student_performance', function($join) use($student_id, $term_id){
                    $join->on('student_performance.performance_id', 'performance.id')
                    ->where('student_performance.term_id', $term_id)
                    ->where('student_performance.student_id', $student_id); })
                ->where('class.id', $class_id)
                ->select('performance.*', 'student_performance.grade')
                ->get();

      $summary = DB::table('student_performance_comment')
                ->where('class_id', $class_id)
                ->where('term_id', $term_id)
                ->where('student_id', $student_id)
                ->select('teacher_comment')
                ->first();

      return response()->json(compact('result', 'summary'));
    }

    //save student performance
    public function postStudentPerformance(Request $request){
         $class_id = $request->get('class_id');
         $student_id = $request->get('student_id');
         $term_id = $request->get('term_id');
         $apply_all = $request->get('apply_all');
         $comments = $request->get('comments');
         $performance = $request->get('performance');
    
         if($apply_all){
            //get students
            $class   = DB::table('class')->where('parent_id', $class_id)->pluck('id');
            $streams = $class->isEmpty()?collect([$class_id]):$class;
            $student = DB::table('student_class')
                       ->whereIn('class_id', $streams)
                       ->where('is_active', true)
                       ->select('student_id')
                       ->get();
           
            foreach($student as $item){
              //replace student id in performance
              $exist = $this->performanceExist($item->student_id, $term_id, $class_id);
              if(!$exist){
                $this->addComment($item->student_id, $class_id, $comments, $term_id);
                foreach($performance as $i){
                  $i['student_id'] = $item->student_id;
                  $this->addPerformance($i);
                }
               
              }
            }
         }else {
           $exist = $this->performanceExist($student_id, $term_id, $class_id);
           if($exist){
               //update performance
               foreach($performance as $item){
                 DB::table('student_performance')
                    ->where('student_id', $student_id)
                    ->where('term_id', $term_id)
                    ->where('class_id', $class_id)
                    ->where('performance_id', $item['performance_id'])
                    ->update(['grade'=> $item['grade']]);
               }
               //update comments
               DB::table('student_performance_comment')
                    ->where('student_id', $student_id)
                    ->where('term_id', $term_id)
                    ->where('class_id', $class_id)
                    ->update(['teacher_comment'=> $comments]);
           }else {
              $this->addComment($student_id, $class_id, $comments, $term_id);
              foreach($performance as $item){
                $this->addPerformance($item);
              }
          }
           
         }
         $message = 'Student performance data has been recorded successfully';
         return response()->json(compact('message'));
    }

    //performance exist
    public function performanceExist($student_id, $term_id, $class_id){
      //check if exists
      $result = DB::table('student_performance_comment')
              ->where('student_id', $student_id)
              ->where('term_id', $term_id)
              ->where('class_id', $class_id)
              ->get();
      if($result->count() > 0){
        return true;
      }else {
        return false;
      }
    }

    public function addPerformance($performance){
        //insert data
        DB::table('student_performance')->insert($performance);
    }

    public function addComment($student_id, $class_id, $comments, $term_id){
      //add comments
      DB::table('student_performance_comment')->insert(array('student_id'=>$student_id, 'term_id'=>$term_id, 'class_id'=>$class_id, 'teacher_comment'=>$comments));
    }

    //get head teacher comments
    public function getComments($class_id, $student_id, $term_id){
        $result = DB::table('student_performance_comment')
                  ->where('student_id', $student_id)
                  ->where('class_id', $class_id)
                  ->where('term_id', $term_id)
                  ->first();
        return response()->json(compact('result'));        
    }

    //post performance comments
    public function postPerformanceComment(Request $request){
      $id = $request->get('id');
      $class_id = $request->get('class_id');
      $term_id = $request->get('term_id');
      $data = $request->get('comment');

      //current date
      date_default_timezone_set("Africa/Nairobi");
      $today = date("d M, Y");
      if($data['apply_all']){
      //get students
      $class   = DB::table('class')->where('parent_id', $class_id)->pluck('id');
      $streams = $class->isEmpty()?collect([$class_id]):$class;
      $student = DB::table('student_class')
                ->whereIn('class_id', $streams)
                ->where('is_active', true)
                ->select('student_id')
                ->get();
      foreach($student as $item){
        $student_id = $item->student_id;
        //update comments
        DB::table('student_performance_comment')
          ->where('student_id', $student_id)
          ->where('class_id', $class_id)
          ->where('term_id', $term_id)
          ->update(['principal_comment'=>$data['comments'], 'date'=>$today]);
      }
      }else {
        //update comments
        DB::table('student_performance_comment')
          ->where('id', $id)
          ->update(['principal_comment'=>$data['comments'], 'date'=>$today]);

      }
      $message = 'Student performance data has been recorded successfully';
      return response()->json(compact('message'));
    }
}
