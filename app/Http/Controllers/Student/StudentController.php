<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Imports\StudentImport;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class StudentController extends Controller
{
    /** get list of registered students to subject belong to specified class */
    public function registerStudentSubject($class_id, $subject_id){
        
        $result = $this->getStudentSubject($class_id, $subject_id);
        return response()->json(compact('result'));   

    }

    public function updateStudentSubject(Request $request){
        $subject_id = $request->get('subject_id');
        $student = $request->get('student');
        $class_id = $request->get('class_id');

        foreach ($student['result'] as $key => $value) {
            $id = $value['id'];
            $student_class_id = $value['student_class_id'];
            $is_registered = $value['is_registered'];
            $exist = DB::table('student_subject')
                    ->where('student_class_id', $student_class_id) 
                    ->where('subject_id', $subject_id)
                    ->get();
            if($exist->count() == 0){
               //register student to subject
               $data = array('student_class_id'=>$student_class_id, 'subject_id'=>$subject_id, 'active'=> $is_registered);
               DB::table('student_subject')->insert($data);
            }else {
                //update student registered to subject
                DB::table('student_subject')
                 ->where('student_class_id', $student_class_id) 
                 ->where('subject_id', $subject_id)
                 ->update(['active' => $is_registered]); 
            }
        }
        //return students data
        $result = $this->getStudentSubject($class_id, $subject_id);
        $message = 'Student record has been updated successfully';
        return response()->json(compact('message','result'));
    }

    public function getStudentSubject($class_id, $subject_id){
        //get class array
        $class   = Grade::where('parent_id', $class_id)->pluck('id');
        $streams = $class->isEmpty()?collect([$class_id]):$class;

        $result  = DB::table('student_class')
                   ->join('student', 'student.id','student_class.student_id')
                   ->leftJoin('student_subject',function($join) use($subject_id){
                      $join->on('student_subject.student_class_id', 'student_class.id')
                      ->where('student_subject.subject_id', $subject_id)
                      ->where('student_subject.active', true); })
                      
                   ->whereIn('student_class.class_id', $streams)
                   ->where('student_class.is_active', true)
                   ->select(DB::raw("CONCAT(student.first_name,' ',COALESCE(student.middle_name, ''),' ',student.last_name) AS name"),'student.gender',
                    'student_subject.id', DB::raw("CASE WHEN student_subject.active = 1 THEN true ELSE false END AS is_registered"), 
                    'student.id AS student_id', 'student_class.id AS student_class_id' )
                   ->get();
        return $result;
    }

    public function getStudentClass($institution_id, $class_id, $school_calendar_id){
        $data = $this->getStudentData($institution_id, $school_calendar_id, $class_id);
        return response()->json(compact('data'));
    }

    public function importStudents(Request $request){
        $school_calendar_id = $request->get('school_calendar_id');
        $class_id = $request->get('class_id');
        $students = $request->get('students');
        $institution_id = $request->get('institution_id');

        foreach($students as $item){
            $item = (object)$item;
           
            DB::transaction(function () use ($item, $school_calendar_id, $class_id, $institution_id) {
                $preferred_id = isset($item->PREFERRED_ID)?$item->PREFERRED_ID:null;
                $first_name = isset($item->FIRST_NAME)?$item->FIRST_NAME:null;
                $middle_name = isset($item->MIDDLE_NAME)?$item->MIDDLE_NAME:null;
                $last_name = isset($item->LAST_NAME)?$item->LAST_NAME:null;
                $date_of_birth = isset($item->DATE_OF_BIRTH)?$item->DATE_OF_BIRTH:null;
                $place_of_birth = isset($item->PLACE_OF_BIRTH)?$item->PLACE_OF_BIRTH:null;
                $gender = isset($item->GENDER)?$item->GENDER:null;
                $country = isset($item->COUNTRY)?$item->COUNTRY:null;
                $date_of_registration = isset($item->DATE_OF_REGISTRATION)?$item->DATE_OF_REGISTRATION:null;
                $religion = isset($item->RELIGION)?$item->RELIGION:null;
                $tribe = isset($item->TRIBE)?$item->TRIBE:null;
                $disability = isset($item->DISABILIY)?$item->DISABILIY:null;
                $comments = isset($item->COMMENTS)?$item->COMMENTS:null;
                $address = isset($item->ADDRESS)?$item->ADDRESS:null;
                $phone = isset($item->PHONE)?$item->PHONE:null;
                $email = isset($item->EMAIL)?$item->EMAIL:null;

                $data = array('preferred_id'=>$preferred_id, 'first_name'=>$first_name, 'middle_name'=>$middle_name,
                'last_name'=>$last_name, 'date_of_birth'=>$date_of_birth, 'place_of_birth'=>$place_of_birth,
                'gender'=>$gender, 'country'=>$country, 'date_of_registration'=>$date_of_registration, 'religion'=>$religion,
                'tribe'=>$tribe, 'disability'=>$disability, 'comments'=>$comments, 'address'=>$address, 'phone'=>$phone,
                'active'=>true, 'institution_id'=>$institution_id);

                $id = DB::table('student')->insertGetId($data);
                DB::table('student_class')->insert(array(
                'student_id'=>$id,
                'class_id'=>$class_id,
                'is_active'=>true,
                'school_calendar_id'=>$school_calendar_id
                ));
            
                //create as a system user
                $user = User::create([
                    'name' => $first_name,
                    'email' => $email,
                    'username' => strtolower($preferred_id),
                    'password' => Hash::make('12345'),
                    'institution_id' => $institution_id
                ]);
                //get permission
                $permission = DB::table('role')->join('role_permission', 'role_permission.role_id', 'role.id')
                               ->where('role.code', 'ST')
                               ->select('role_permission.permission_id AS id')
                               ->get();
                $user_permission = array();
                foreach($permission as $i){
                    array_push($user_permission, array('permission_id'=>$i->id, 'user_id'=>$user->id));
                }
                DB::table('permission_user')->insert($user_permission);
           });
        }
        $data = $this->getStudentData($institution_id, $school_calendar_id, $class_id);
        $message = 'Students records has been uploaded successfully';
        return response()->json(compact('data', 'message'));
    }

    public function getStudentData($institution_id, $school_calendar_id, $class_id){
        return Student::join('student_class', 'student_class.student_id', 'student.id')
                ->where('student.institution_id', $institution_id)
                ->where('student_class.school_calendar_id', $school_calendar_id)
                ->where('student_class.class_id', $class_id)
                ->select([DB::raw("COALESCE(student.middle_name, '') AS middle_name"), 'student.first_name', 'student.last_name', 'student.institution_id', 
                'student.preferred_id AS student_id', 'student.gender', 'student.phone', 'student.email', 'student.id', 'student.active'])
                ->orderBy('student.id', 'DESC')
                ->get();
    }

    //get students list
    public function getStudents($class_id, $school_calendar_id){
        $class   = Grade::where('parents_id', $class_id)->pluck('id');
        $streams = $class->isEmpty()?collect([$class_id]):$class;

        //get students
        $result = DB::table('student')
                  ->join('student_class', 'student.id', 'student_class.student_id')
                  ->where('student_class.is_active', true)
                  ->whereIn('student_class.class_id', $streams)
                  ->where('student_class.school_calendar_id', $school_calendar_id)
                  ->select([DB::raw("CONCAT(student.first_name,' ',COALESCE(student.middle_name, ''),' ', student.last_name ) AS name"), 
                  'student.preferred_id', 'student.gender', 'student.phone', 'student.email', 'student.id'])
                  ->get();
        return response()->json(compact('result'));
    }

    public function updateStudent(Request $request,  $class_id, $school_calendar_id){
       $student = [
                   'first_name'  => $request->get('first_name'), 
                   'middle_name' => $request->get('middle_name'),
                   'last_name'   => $request->get('last_name'),
                   'gender'      => $request->get('gender'),
                   'email'       => $request->get('email'),
                   'phone'       => $request->get('phone'),
                   'address'     => $request->get('address'),
                   'active'      => $request->get('active')
                ];
       $institution_id = $request->get('institution_id');
       $id = $request->get('id');
       DB::table('student')->where('id', $id)->update($student);
       $data = $this->getStudentData($institution_id, $school_calendar_id, $class_id);
       $message = 'Students records has been updated successfully';
       return response()->json(compact('data', 'message'));

    }

}
