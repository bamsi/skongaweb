<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentResult;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use File;
use PHPJasper\PHPJasper; 
use PDF;

//include('fpdf_merge.php');

date_default_timezone_set('Africa/Dar_es_Salaam');
ini_set('max_execution_time', 2000);

class StudentResultController extends Controller
{ 
    //retur student results
    public function studentResults(Request $request){
      $exam_schedule_id = $request->get('exam_schedule_id');
      $subject_id = $request->get('subject_id');
      $class_id = $request->get('class_id');
      $student_result = $this->getResults($exam_schedule_id, $subject_id, $class_id);
      return response()->json(compact('student_result'));
    }

    public function getGrade($marks, $class_id){
      //get institution id
      $institution = DB::table('class')
                    ->where('id', $class_id)
                    ->select('institution_id')
                    ->first();
      $grade = DB::table('grade')
               ->join('level', 'level.id', 'grade.level_id')
               ->join('class', 'class.level_id', 'level.id')
               ->where('class.id', $class_id)
               ->where('grade.institution_id','=',$institution->institution_id)
               ->whereRaw('? between grade.begin and grade.end', [$marks])
               ->select(['grade.grade', 'grade.remark', 'grade.point'])
               ->first();
      return $grade;
    }

    public function updateStudentResults(Request $request){
      $exam_schedule_id = $request->get('exam_schedule_id');
      $result  = $request->get('result');

      foreach ($result['student_result'] as $key => $value) {
        $subject_id = $value['subject_id'];
        $class_id = $value['class_id'];
        //get marks grade
        $grade = array('marks' => null, 'remark'=> null);

        if($value['marks'] != null){
          $grade = $this->getGrade($value['marks'], $value['class_id']);
          
          //return response()->json(compact('grade','value'));
          //check if result exists
          $exist = DB::table('student_result')
                  ->where('exam_schedule_id', $exam_schedule_id)
                  ->where('student_subject_id', $value['student_subject_id'])
                  ->get();

          if($exist->count() > 0){
            //update result
            $item = array('marks'=>$value['marks'], 'grade'=>$grade->grade, 'remark'=>$grade->remark, 'point'=>$grade->point);
            DB::table('student_result')
              ->where('exam_schedule_id', $exam_schedule_id)
              ->where('student_subject_id', $value['student_subject_id'])
              ->update($item);

          }else {
            //record new result
            $item = array('exam_schedule_id'=>$exam_schedule_id, 'student_subject_id'=>$value['student_subject_id'],
                          'marks'=>$value['marks'], 'grade'=>$grade->grade, 'remark'=>$grade->remark,
                          'point'=>$grade->point );
            DB::table('student_result')->insert($item);
          }
        }
      }

      //return updated results
      $student_result = $this->getResults($exam_schedule_id, $subject_id, $class_id);
      $message = 'Student record has been updated successfully';
      return response()->json(compact('message','student_result'));

    }

    public function getResults($exam_schedule_id, $subject_id, $class_id){
      return DB::table('student_subject')
            ->join('student_class', 'student_class.id','=','student_subject.student_class_id')
            ->join('student','student.id','=', 'student_class.student_id')
            ->leftJoin('student_result', function($join) use($exam_schedule_id) {
              $join->on('student_result.student_subject_id','=','student_subject.id')
              ->where('student_result.exam_schedule_id', $exam_schedule_id);})
            ->where('student_subject.subject_id',  $subject_id)
            ->where('student_class.class_id', $class_id)
            ->where('student_subject.active', true)
            ->get(['student_result.id', 'student.preferred_id', 'student.first_name','student.middle_name','student.last_name','student.gender', 'student_class.class_id',
            'student_result.exam_schedule_id','student_subject.id AS student_subject_id', 'marks', 'grade', 'position', 'student_subject.subject_id']);  
    }

    public function classResults($school_calendar_id, $exam_id, $class_id){

      $class   = Grade::where('parent_id', $class_id)->pluck('id');
      $streams = $class->isEmpty()?collect([$class_id]):$class;
      //get students of that class
      $students = DB::table('student_class')
                  ->join('student', 'student.id', 'student_class.student_id')
                  ->where('school_calendar_id', $school_calendar_id)
                  ->whereIn('class_id', $streams)
                  ->select([ DB::raw("CONCAT(student.first_name,' ',COALESCE(student.middle_name, ''),' ',student.last_name) AS name"), 'student_class.id', 
                           'student.id as student_id','student_class.class_id', 'student_class.id as student_class_id'])
                  ->get();

      $subj = collect(); 
      $subj->push(['field'=>'name', 'header'=>'Name']);
      $data = collect();
      $_avg = [];
        
      foreach($students as $student){
        //get all subject assigned to student
        $subjects = DB::table('student_subject')
                    ->join('subject', 'student_subject.subject_id','subject.id')
                    ->leftJoin('student_result', function($query) use($exam_id){
                        $query->on('student_result.student_subject_id', 'student_subject.id')
                        ->where('student_result.exam_schedule_id', $exam_id);})
                    ->where('student_subject.student_class_id', $student->student_class_id)
                    ->where('student_subject.active', true)
                    ->select(['student_subject.id as student_subject_id', 'subject.name as subject_name', 'subject.code as subject_code',
                    'student_result.marks', 'student_result.grade', 'student_result.remark','student_result.point', 'subject.additional'])
                    ->orderBy('student_result.marks', 'DESC')
                    ->get();
        
        $result['name'] = $student->name;
        $points = collect();
        $total_mark = 0;
        $incomplete = false;
        $subject_counter = 0;

        foreach($subjects as $subject){
          //push subject to array
          $field = strtolower($subject->subject_code);
          $subj->push(['field'=>$field, 'header'=>strtoupper($field), 'order'=>1 ]);

          //push result to array
          $i = !empty($subject->marks)?  $subject->marks.'|'.$subject->grade : ' ';
          $result[$field] = $i;
          if(!empty($subject->marks)){
              $total_mark += $subject->marks;
              $subject_counter++;
          }else {
              $incomplete == true;
          }
          if(!$subject->additional && $subject->point != null){
            $points->push($subject->point);
          } 
        }
        
        $result['student_id'] = $student->student_id;
        $result['class_id']   = $student->class_id;
        
        $result['total']  = $total_mark;
        $subj->push(['field'=>'total', 'header'=>'Total', 'order'=>2]);

        if($incomplete == false && $subject_counter > 0 ){
          $avg = round($total_mark/$subject_counter, 0);
          
          //get grade
          $grade = $this->getGrade($avg, $student->class_id);
          
          $result['avg'] = $avg.'|'.$grade->grade;
          $result['average'] = $avg;
          $subj->push(['field'=>'avg', 'header'=>'AVG', 'order'=>3]);
         
          array_push($_avg, $avg);
           //get division
          $division = $this->getDivision($points, $class_id);
          $result['division'] = $division->division.' : '.$division->points;
          $subj->push(['field'=>'division', 'header'=>'DIV', 'order'=>4]);
          $subj->push(['field'=>'pos', 'header'=>'POS', 'order'=>5]);
        }else {
          //remark is incomplete
          $result['remark'] = 'INCOMPLETE';
        } 

        $data->push($result);
        unset($result);
      }

      arsort($_avg);
      $_avg = array_combine(range(1, count($_avg)), array_values($_avg));
      $obj = collect();
     
      foreach($data as $k=> &$item){
        //get position
        if(isset($item['average'])){
          $key = array_search($item['average'], $_avg);  
          $item['pos'] = $key;
          $data[$k] = $item;
        }
      }
      $data = ($data->sortByDesc('avg'))->values()->all();
      $header = $subj->unique()->sortBy('order')->values()->all();
      //check if results has been published yet
      $published = DB::table('published_student_result')
                   ->where('exam_schedule_id', $exam_id)
                   ->where('class_id', $class_id)
                   ->get()
                   ->count();

      return response()->json(compact('header','data', 'published')); 
    }

    public function getDivision($points, $class_id){
      //get cutoff
      $result = DB::table('class')->join('level', 'class.level_id', 'level.id')
                 ->where('class.id', $class_id)
                 ->select('level.id as level_id', 'level.cutoff')
                 ->first();
      $sorted = $points->sort();
      $cut_off = $result->cutoff;
      $total_points  = $sorted->shift($cut_off)->sum();
      $division = DB::table('division_range')
                  ->where('division_range.level_id', $result->level_id)
                  ->whereRaw('? between division_range.begin and division_range.end', [$total_points])
                  ->select(DB::raw("$total_points AS points"), 'division_range.division')
                  ->first();
      return $division;
    }

    public function publishResult(Request $request){
       $exam_schedule_id = $request->get('exam_id');
       $class_id = $request->get('class_id');
       $current_user = $request->get('user_id');
       $current_date = date('Y-m-d H:i:s');
       $results = $request->get('results');
       foreach($results as $item){
         if(isset($item['average'])){
            $data = array('exam_schedule_id'=>$exam_schedule_id, 'student_id'=>$item['student_id'],
                    'class_id'=>$class_id, 'total'=>$item['total'], 'average'=>$item['avg'],
                    'position'=>$item['pos'], 'division'=>$item['division'], 'published_by'=>$current_user,
                    'published_at'=>$current_date, 'published'=>true);
            //insert data
            DB::table('published_student_result')->insert($data);
         }
       }
      //send notification message
      $notification = "Student results has been published. Please click student results menu item to view results.";
      $this->postNotification('ST', $class_id, $notification, $current_date);

      $published = DB::table('published_student_result')
                   ->where('exam_schedule_id', $exam_schedule_id)
                   ->where('class_id', $class_id)
                   ->get()
                   ->count();
                   
      $message = 'Student results has been published successfully';
      return response()->json(compact('message','published'));
    }

    public function postNotification($role, $class_id, $message, $date){
      //get role id
      $role = DB::table('role')->where('code', $role)->first('id');
      $data = array('role_id'=>$role->id, 'class_id'=>$class_id, 'message'=>$message, 'published_at'=>$date,
                    'active'=>true);
      DB::table('notification')->insert($data);
      return true;
    }

    public function getPublishedResult($user_id){
     //get student 
     $student = DB::table('users')
                ->join('student', 'users.username', 'student.preferred_id')
                ->where('users.id', $user_id)
                ->select('student.id', DB::raw("CONCAT(student.first_name,' ', SUBSTRING(COALESCE(student.middle_name, ''),1,1),' ',student.last_name) AS name"))
                ->first();
     //get result
     $result = DB::table('published_student_result')
               ->join('exam_schedule', 'exam_schedule.id', 'published_student_result.exam_schedule_id')
               ->join('exam', 'exam.id', 'exam_schedule.exam_id')
               ->join('class', 'class.id', 'published_student_result.class_id')
               ->join('term', 'term.id', 'exam_schedule.term_id')
               ->join('school_calendar', 'school_calendar.id', 'term.school_calendar_id')
               ->where('published_student_result.student_id', $student->id)
               ->select('class.name as class_name', 'term.code as term_name', 'school_calendar.name as ay', 'exam.name as exam_name',
                'published_student_result.total', 'published_student_result.average', 'published_student_result.position',
                'published_student_result.division', 'exam_schedule.id as exam_schedule_id', 'class.id as class_id')
               ->orderBy('published_student_result.published_at', 'DESC') 
               ->limit(1)
               ->get();

     if($result->count() > 0){
        $results = collect();
        foreach($result as $item){
          $student_details = array('student_name'=>$student->name, 'class'=>$item->class_name, 'exam_name'=>$item->exam_name,
                                  'term'=>$item->term_name, 'academic_year'=>$item->ay, 'class_id'=>$item->class_id, 
                                  'student_id'=>$student->id, 'exam_id'=>$item->exam_schedule_id);
          //get student subject results
          $subject_results = DB::table('student_result')
                              ->join('student_subject', 'student_subject.id', 'student_result.student_subject_id')
                              ->join('subject', 'subject.id', 'student_subject.subject_id')
                              ->join('student_class', 'student_class.id', 'student_subject.student_class_id')
                              ->where('student_result.exam_schedule_id', $item->exam_schedule_id)
                              ->where('student_class.student_id', $student->id)
                              ->where('student_subject.active', true)
                              ->select('subject.name', 'student_result.marks', 'student_result.grade', 'student_result.remark')
                              ->get();
          foreach($subject_results as $l){
                $results->push(['item'=>$l->name, 'marks'=>$l->marks, 'grade'=>$l->grade, 'remark'=>$l->remark]);
          }
          $avg = explode("|", $item->average);
          //get remark
          $_grade = $this->getGrade($avg[0], $item->class_id);
          $results->push(['item'=>'Total', 'marks'=>$item->total, 'grade'=>' ', 'remark'=>'']);
          $results->push(['item'=>'Average', 'marks'=>$avg[0], 'grade'=>$avg[1], 'remark'=>$_grade->remark]);

          $division = explode(':', $item->division);
          //get total students
          $total_students = DB::table('published_student_result')
                            ->where('exam_schedule_id', $item->exam_schedule_id)
                            ->where('class_id', $item->class_id)
                            ->get()
                            ->count();
          $result_summary = array('average_mark'=>$avg[0], 'division'=>$division[0], 'point'=>$division[1],
                                  'position'=>$item->position, 'total_students'=>$total_students);
          //get feedack if available
          $feedback = DB::table('feedback')
                      ->where('student_id', $student->id)
                      ->where('exam_schedule_id', $item->exam_schedule_id)
                      ->where('class_id', $item->class_id)
                      ->first();
        }
        return response()->json(compact('results','student_details', 'result_summary', 'feedback'));
    } else {
      return response()->json(['error' => 'No data found'], 404);   
    }    
  }

  public function getFinalResult($user_id){
    //get student 
    $student = DB::table('users')
              ->join('student', 'users.username', 'student.preferred_id')
              ->where('users.id', $user_id)
              ->select('student.id')
              ->first();
    //get recent term result
    $result_summary = DB::table('final_result_summary')
                      ->join('term', 'term.id', 'final_result_summary.term_id')
                      ->join('school_calendar', 'school_calendar.id', 'term.school_calendar_id')
                      ->where('student_id', $student->id)
                      ->select('school_calendar.id as year_id', 'final_result_summary.*')
                      ->orderBy('id', 'DESC')
                      ->limit(1)
                      ->first();
    $term_id  = $result_summary->term_id;
    $class_id = $result_summary->class_id;
    $year_id  = $result_summary->year_id;
    $path =  'assets/report/'.$class_id.'/'.$year_id.'-'.$term_id.'-'.$student->id.'.pdf';
    return response()->json(compact('path'));
  }

  public function parentFeedback(Request $request){
       $student_id = $request->get('student_id');
       $class_id = $request->get('class_id');
       $message = $request->get('message');
       $exam_schedule_id = $request->get('exam_id');
       $current_date = date('Y-m-d H:i:s');
       //save data
       $exist = DB::table('feedback')
                ->where('student_id', $student_id)
                ->where('class_id', $class_id)
                ->where('exam_schedule_id', $exam_schedule_id)
                ->first();

      if(isset($exist)){
        DB::table('feedback')
        ->where('student_id', $student_id)
        ->where('class_id', $class_id)
        ->where('exam_schedule_id', $exam_schedule_id)
        ->update(['message'=>$message]);
      }else {
        //save data
        DB::table('feedback')->insert(array('student_id'=>$student_id, 'class_id'=>$class_id, 
        'exam_schedule_id'=>$exam_schedule_id, 'message'=>$message));
      }
      $feedback = DB::table('feedback')
                ->where('student_id', $student_id)
                ->where('class_id', $class_id)
                ->where('exam_schedule_id', $exam_schedule_id)
                ->first();
      $message = "Feedback has been submitted successfully!";
      return response()->json(compact('message', 'feedback'));
  }

   //temporary function to update grade points
   public function updateGradePoints(){
    //get student results
    $result = DB::table('student_result')
              ->join('student_subject', 'student_result.student_subject_id', 'student_subject.id')
              ->join('student_class', 'student_class.id', 'student_subject.student_class_id')
              ->select('student_result.id', 'student_class.class_id', 'student_result.marks')
              ->get();
    foreach($result as $item){
      $_grade = $this->getGrade($item->marks, $item->class_id);
      if(isset($_grade)){
        $point = $_grade->point;
        $grade = $_grade->grade;
        $remark = $_grade->remark;
        //update result table
        DB::table('student_result')
        ->where('id', $item->id)
        ->update(['point'=>$point, 'grade'=>$grade, 'remark'=>$remark]);
      }
    }
    return true;
   }

   function updateStudent(){
     $result = DB::table('users')
               ->join('student', 'student.preferred_id', 'users.username')
               ->select('users.id as user_id', 'student.*')
               ->get();
     foreach($result as $item){
       //update name
       DB::table('users')->where('id', $item->user_id)->update(['name'=>$item->first_name]);
       //insert role
       DB::table('permission_user')->insert(['user_id'=>$item->user_id, 'permission_id'=>10]);
       DB::table('permission_user')->insert(['user_id'=>$item->user_id, 'permission_id'=>9]);
       DB::table('permission_user')->insert(['user_id'=>$item->user_id, 'permission_id'=>4]);
     }
     return true;
   }

   /** calculate final results **/
   function computeResult($school_calendar_id, $class_id){
      //get active term
      $term = DB::table('term')
              ->where('school_calendar_id', $school_calendar_id)
              ->where('active', true)
              ->select('id')
              ->orderBy('id', 'DESC')
              ->limit(1)
              ->first();

      $term_id = $term->id;
      //get exams
      $exam = DB::table('result_setup')
              ->where('term_id', $term_id)
              ->where('class_id', $class_id)
              ->get();
      $exam_id = $exam->pluck('exam_schedule_id');
      //get class
      $class   = Grade::where('parent_id', $class_id)->pluck('id');
      $streams = $class->isEmpty()?collect([$class_id]):$class;
      //get students of that class
      $students = DB::table('student_class')
                    ->join('student', 'student.id', 'student_class.student_id')
                    ->where('school_calendar_id', $school_calendar_id)
                    ->whereIn('class_id', $streams)
                    ->select([ DB::raw("CONCAT(student.first_name,' ',COALESCE(student.middle_name, ''),' ',student.last_name) AS name"), 'student_class.id', 
                              'student.id as student_id','student_class.class_id', 'student_class.id as student_class_id'])
                    ->get();

      $subj = collect(); 
      $data = collect();
      $_avg = [];
      $_total_students = $students->count();

      foreach($students as $student){
        //get all subject assigned to student
        $subjects = DB::table('student_subject')
                    ->join('subject', 'student_subject.subject_id','subject.id')
                    ->join('student_class', 'student_class.id', 'student_subject.student_class_id')
                    ->leftJoin('student_result', function($query) use($exam_id){
                        $query->on('student_result.student_subject_id', 'student_subject.id')
                        ->whereIn('student_result.exam_schedule_id', $exam_id);})
                    ->where('student_subject.student_class_id', $student->student_class_id)
                    ->where('student_subject.active', true)
                    ->select(['student_subject.id as student_subject_id', 'subject.name as subject_name', 'subject.code as subject_code', 'subject.id as subject_id',
                    'student_result.marks', 'student_result.grade', 'student_result.remark','student_result.point', 'subject.additional',
                    'student_result.exam_schedule_id','student_class.student_id'])
                    ->orderBy('student_subject.student_class_id', 'DESC')
                    ->orderBy('student_subject.subject_id', 'DESC')
                    ->get();
        
        $_result['name'] = $student->name;
        $subj->push(['field'=>'name', 'header'=>'Name', 'order'=>1 ]);
        $student_id = $student->student_id;
        $points = collect();
        $result = collect();
        $total_mark = 0;
        $incomplete = false;
        $subject_counter = 0; 

        $_result['student_id'] = $student_id;

        $grouped_result = $subjects->groupBy('student_subject_id')->all();
        
        
        
        foreach($grouped_result as $subject){
           $exercise = 0;
           $test_counter = 0;
           $row_test = 0;
           $item = array('subject_name'=>'', 'subject_code'=>'', 'student_subject_id'=>'', 'final'=>0,'p_final'=>0, 'test'=>0, 'total'=>0, 'grade'=>'', 'p_test');
           
           foreach($subject as $i){
             //push subject to array
              $field = strtolower($i->subject_code);
              $subj->push(['field'=>$field, 'header'=>strtoupper($field), 'order'=>2 ]);
              $item['subject_name'] = $i->subject_name;
              $item['subject_code'] = $i->subject_code;
              $item['student_subject_id'] = $i->student_subject_id;
              //get result percentage
              $setup = DB::table('result_setup')
                            ->where('class_id', $class_id)
                            ->where('exam_schedule_id', $i->exam_schedule_id)
                            ->where('term_id', $term_id)
                            ->select('percentage', 'is_final')
                            ->first();
              if($i->marks >= 0){
                $mark = ( $setup->percentage/ 100 ) * $i->marks;
                if($setup->is_final){
                  $item['final'] = $i->marks;
                  $item['p_final'] = $mark; //marks in percentage
                }else {
                  $exercise += $mark;
                  $row_test += $i->marks;
                  $test_counter += 1;
                }
              }    
           }

           $item['p_test'] = $exercise;
           $item['test'] = $test_counter > 0? round($row_test/$test_counter):0;
           $item['total'] =  round($exercise + $item['p_final'], 0);
           //get grade
           $grade = $this->getGrade($item['total'], $class_id);
           $item['grade'] = $grade;

           if(isset($signature[$i->subject_code])){
            $item['signature'] = $signature[$i->subject_code];
           }else {
              //get subject teacher signature
              $teacher = DB::table('student_class')
              ->join('teacher_subject', 'student_class.class_id', 'teacher_subject.class_id')
              ->join('teacher', 'teacher_subject.teacher_id', 'teacher.id')
              ->where('student_class.student_id', $student_id)
              ->where('student_class.class_id', $student->class_id)
              ->where('teacher_subject.subject_id', $i->subject_id)
              ->select([DB::raw("CONCAT(LEFT(teacher.first_name,1),'.',LEFT(teacher.last_name,1)) AS name")])
              ->first();

              ($teacher)?$item['signature'] = $teacher->name:'';
           }
          
           $result->push($item);
           $signature[$i->subject_code] = $item['signature'];
           $total_mark += $item['total'];
           $subject_counter += 1;
           if(!$i->additional && $i->point != null){
             $points->push($grade->point);
           } 

           //push data
           $code = $item['grade']->grade;
           $field = strtolower($i->subject_code);
           $_result[$field] = $item['total'].'|'.$code;

           unset($item); //clear array values
           $exercise = 0; //reset exercise
        }

        $_result['result'] = $result;
        $_result['grand_total'] = $total_mark;
        if($subject_counter > 0){
          $_result['average'] = round($total_mark/$subject_counter,0);
          array_push($_avg, $_result['average']);
          //get grade
          $grade = $this->getGrade($_result['average'], $class_id);
          $_result['grade'] = $grade;
          //get division
          $division = $this->getDivision($points, $class_id);
          $_result['division'] = $division->division;
          $_result['points'] = $division->points;
          $_result['class_size'] = $_total_students;
        }
        $data->push($_result);
        unset($_result);
        unset($points);
      }

      arsort($_avg);
      $_avg = array_combine(range(1, count($_avg)), array_values($_avg));
     
      foreach($data as $k=> &$item){
        //get position
        if(isset($item['average'])){
          $key = array_search($item['average'], $_avg);  
          $item['pos'] = $key;
          $data[$k] = $item;
        }
      }
      
      $subj->push(['field'=>'average', 'header'=>'AVG', 'order'=>3]);
      $subj->push(['field'=>'division', 'header'=>'DIV', 'order'=>4]);
      $subj->push(['field'=>'pos', 'header'=>'POS', 'order'=>5]);
      
      //check if result has been published
     $published = DB::table('final_result_summary')
                  ->where('class_id', $class_id)
                  ->where('term_id', $term_id)
                  ->get()
                  ->count();

      $header = $subj->unique()->sortBy('order')->values()->all();
      //return data
      return array('data'=>$data, 'header'=>$header, 'term'=>$term_id, 'published'=>$published);
   }

   public function finalResults($school_calendar_id, $class_id){
     //check if result exist

     //---end---
     $data = $this->computeResult($school_calendar_id, $class_id);
     //check if result has been published
     $published = DB::table('final_result_summary')
                  ->where('class_id', $class_id)
                  ->where('term_id', $data['term'])
                  ->get()
                  ->count();
     return response()->json(compact('data', 'published'));
   }

   public function publishFinalResult(Request $request){
    $class_id = $request->get('class_id');
    $school_calendar_id = $request->get('school_calendar_id');
    $current_date = date('Y-m-d H:i:s');
    $results = $request->get('results');

    $term = DB::table('term')
              ->where('school_calendar_id', $school_calendar_id)
              ->where('active', true)
              ->select('id')
              ->orderBy('id', 'DESC')
              ->limit(1)
              ->first();

    $term_id = $term->id;

    foreach($results as $item){
      $student_id = '';
      $result_summary = array('class_id'=>$class_id, 'term_id'=>$term_id);
      foreach($item as $key=>$value){
        switch ($key) {
          case 'grand_total': $result_summary['total'] = $value;
                break;
          case 'average': $result_summary['average'] = $value;
                break;
          case 'grade': $result_summary['grade'] = $value['grade'];
                              $result_summary['remark'] = $value['remark'];
                break;
          case 'pos': $result_summary['position'] = $value;
                break;
          case 'class_size': $result_summary['out_of'] = $value;
                break;
          case 'division': $result_summary['division'] = $value;
                break;
          case 'points': $result_summary['points'] = $value;
                break;
          case 'student_id': $result_summary['student_id'] = $value;
                             $student_id = $value;
                break;
        }
        
        if($key == 'result'){
          $subject = '';
          $subject_result = array('class_id'=>$class_id, 'term_id'=>$term_id, 'student_id'=>$student_id);
          foreach($value as $item){
            $subject_result['subject'] = $item['subject_name'];
            $subject_result['test'] = $item['test'];
            $subject_result['p_test'] = $item['p_test'];
            $subject_result['final'] = $item['final'];
            $subject_result['p_final'] = $item['p_final'];
            $subject_result['total'] = $item['total'];
            $subject_result['grade'] = $item['grade']['grade'];
            $subject_result['remark'] = $item['grade']['remark'];
            $subject_result['signature'] = $item['signature'];
            $subject = $item['subject_name'];

            //check if result exist
            $exist = DB::table('publish_final_result')
                    ->where('class_id', $class_id)
                    ->where('term_id', $term_id)
                    ->where('student_id', $student_id)
                    ->where('subject', $subject)
                    ->get();
            if($exist->count() == 0){
              //save subject result
              DB::table('publish_final_result')->insert($subject_result);
            }
          }
          
        }
      }
      //check if exist
      $row = DB::table('final_result_summary')
              ->where('class_id', $class_id)
              ->where('term_id', $term_id)
              ->where('student_id', $student_id)
              ->get();

      if($row->count() == 0){
        //save result summary
        DB::table('final_result_summary')->insert($result_summary);
      }
    }
    $message = "Student results has been published successfully!";
    return response()->json(compact('message'));
   }



  
   public function getFinalReport($class_id, $term_id){
      $class   = Grade::where('parent_id', $class_id)->pluck('id');
      $streams = $class->isEmpty()?collect([$class_id]):$class;
      //get student 
      $student = DB::table('student_class')
                ->join('student', 'student.id', 'student_class.student_id')
                ->join('class', 'class.id', 'student_class.class_id')
                ->whereIn('class.id', $streams)
                ->where('student_class.is_active', true)
                ->select('student.id', 'class.name as grade', DB::raw("CONCAT(student.first_name,' ', COALESCE(student.middle_name, ''),' ',student.last_name) AS name"))
                ->get();
      $signature = $this->getSignature($class_id);
      $data = collect();
      //get results
      foreach($student as $item){
          
          //student details
          $result_summary = DB::table('final_result_summary')
                            ->join('term', 'term.id', 'final_result_summary.term_id')
                            ->join('institution', 'institution.id', 'term.institution_id')
                            ->join('school_calendar', 'school_calendar.id', 'term.school_calendar_id')
                            ->where('final_result_summary.student_id', $item->id)
                            ->where('final_result_summary.term_id', $term_id)
                            ->select('final_result_summary.*', 'term.code as term', 'institution.title as school', 'institution.address', 
                                    'institution.logo', 'school_calendar.code as year')
                            ->get();
          //get subject results
          $result_details = DB::table('publish_final_result')
                            ->where('student_id', $item->id)
                            ->where('term_id', $term_id)
                            ->get();

          //get student performance
          $performance = DB::table('student_performance')
                        ->join('performance', 'performance.id', 'student_performance.performance_id')
                        ->where('student_performance.student_id', $item->id)
                        ->where('student_performance.term_id', $term_id)
                        ->select('performance.description', 'student_performance.grade')
                        ->get();
          
          //get teachers comments
          $remarks = DB::table('student_performance_comment')
                      ->where('student_performance_comment.student_id', $item->id)
                      ->where('student_performance_comment.term_id', $term_id)
                      ->first();
          $data->push(['student'=>$item->name, 'grade'=>$item->grade, 'summary'=>$result_summary, 'result'=> $result_details, 
                       'performance'=> $performance, 'remarks'=>$remarks, 'signature'=>$signature]);
          
      }
      return response()->json(compact('data'));
   }
   

   //get signature
   public function getSignature($class_id){
     //get signatures
     $signatures = DB::table('class_teacher')
                    ->where('class_id', $class_id)
                    ->orWhere('class_id', null)
                    ->get();
     $data = array('head_teacher'=>'', 'class_teacher'=>'');
      foreach($signatures as $item){
          if($item->class_id == null){
            $data['head_teacher'] = $item->signature;
          }else {
            $data['class_teacher'] = $item->signature;
          }
      }
     return $data;
   }


   public function getPdf($term_id, $class_id, $language){
    //path to public folder
    $public = public_path(); 
    if($language == 'sw'){
      $input  = $public.'/assets/report/report_sw.jasper';   
    }else {
      $input  = $public.'/assets/report/report_eng.jasper';   
    }
    $output = $public.'/assets/report';
    //get all students
    $class   = Grade::where('parent_id', $class_id)->pluck('id');
    $streams = $class->isEmpty()?collect([$class_id]):$class;

    //get signatures
    $signature = $this->getSignature($class_id);

    //get students
    $_student = DB::table('term')
               ->join('school_calendar', 'school_calendar.id', 'term.school_calendar_id')
               ->join('student_class', 'student_class.school_calendar_id', 'school_calendar.id')
               ->join('student', 'student.id', 'student_class.student_id')
               ->join('institution', 'institution.id', 'term.institution_id')
               ->join('class', 'class.id', 'student_class.class_id')
               ->whereIn('student_class.class_id', $streams)
               ->where('term.id', $term_id)
               ->select('student.id', DB::raw("CONCAT(student.first_name,' ', COALESCE(student.middle_name, ''),' ',student.last_name) AS name"),
                        'term.code as term', 'institution.title as school', 'institution.address', 'institution.logo', 'school_calendar.code as year', 
                        'school_calendar.id as year_id', 'class.name as stream')
               ->get();
    
    foreach($_student as $student){
      //get recent term result
      $result_summary = DB::table('final_result_summary')
                        ->join('class', 'class.id', 'final_result_summary.class_id')
                        ->where('final_result_summary.student_id', $student->id)
                        ->where('final_result_summary.term_id', $term_id)
                        ->select('final_result_summary.*', 'class.code as class')
                        ->orderBy('final_result_summary.id', 'DESC')
                        ->limit(1)
                        ->first();
      //get teachers comments
      $remarks = DB::table('student_performance_comment')
                  ->where('student_performance_comment.student_id', $student->id)
                  ->where('student_performance_comment.term_id', $term_id)
                  ->first();

      //define parameters
      $parameters = array();
      $parameters['logo'] = $public.'/'.$student->logo;
      $parameters['school_name'] = $student->school;
      $parameters['address'] = $student->address;
      $parameters['student_name'] = $student->name;
      $parameters['grade'] = $result_summary->class;
      $parameters['term'] = $student->term;
      $parameters['year'] = $student->year;
      $parameters['grandTotal'] = $result_summary->average;
      $parameters['gradeTotal'] = $result_summary->grade;
      $parameters['remarkTotal'] = $result_summary->remark;
      $parameters['position'] = $result_summary->position;
      $parameters['totalStudent'] = $result_summary->out_of;
      $parameters['teacher_comment'] = $remarks->teacher_comment;
      $parameters['principal_comment'] = $remarks->principal_comment;
      $parameters['teacher_signature'] = $public.'/'.$signature['class_teacher'];
      $parameters['principal_signature'] = $public.'/'.$signature['head_teacher'];
      $parameters['student_id'] = $student->id;
      $parameters['term_id'] = $term_id;
      $parameters['stream'] = $student->stream;

      //dd($parameters);

      $options = [
          'format' => ['pdf'],
          'locale' => 'en',
          'params' => $parameters,
          'db_connection' => [
                              'driver' => 'postgres', 
                              'username' => 'root',
                              'password' => 'bamsi',
                              'host' => 'localhost',
                              'database' => 'skongaweb',
                              'port' => '5432'
                              ]
           ];

      $jasper = new PHPJasper;

      //create output path
      $path =  $class_id.'/'.$student->year_id.'-'.$term_id.'-'.$student->id;
      $path =  $output.'/'.$path;
      $jasper->process(
      $input,
      $path,
      $options
      )->execute();
    }
   }

   /** merge pdf reports */
   public function printAllReport($term_id, $class_id){
    $merger = new FPDF_Merge();
     //get files
     $p = '/assets/report/'.$class_id;
     $path = public_path($p);
     if ($handle = opendir($path)) {

      while (false !== ($entry = readdir($handle))) {
  
          if ($entry != "." && $entry != "..") {
            $ph = $path.'/'.$entry;
            $merger->addPathToPDF($ph, 'all', 'P');
          }
      }
  
      closedir($handle);
    }
    $merger->output($path.'all.pdf');
    $merger->save($path.'all.pdf');

   }

   /** update report */
   

}
