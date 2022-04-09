<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($institution_id)
    {
        $teacher = $this->getTeacher($institution_id);
        return response()->json(compact('teacher'));   
    }

    public function getTeacher($institution_id){
        return  Teacher::with('subjects')
                ->where('institution_id', $institution_id)
                ->orderByDesc('id')
                ->get();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        DB::transaction(function () use ($request) {

            $teacher = Teacher::create([
                'first_name'=> strtoupper($request->get('first_name')),
                'middle_name'=> strtoupper($request->get('middle_name')),
                'last_name'=> strtoupper($request->get('last_name')),
                'email'=> $request->get('email'),
                'phone'=> $request->get('phone'),
                'address'=> $request->get('address'),
                'active' => true,
                'institution_id' => $request->get('institution_id'),
                'gender'=> $request->get('gender'),
                'class_teacher'=>$request->get('class_teacher')
            ]);
        
            //register teacher subject
            $subjects = $request->get('subjects');
            
            foreach($subjects as $item){
                DB::table('teacher_subject')->insert([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $item['subject_id'],
                    'class_id'   => $item['class_id']
                ]);
            }
            //create as a system user
            $user = User::create([
                'name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'username' => strtolower($request->get('email')),
                'password' => Hash::make('12345'),
                'institution_id' => $request->get('institution_id')
            ]);
            //get permission
            $permission = DB::table('role')->join('role_permission', 'role_permission.role_id', 'role.id')
                           ->where('role.code', 'TC')
                           ->select('role_permission.permission_id AS id')
                           ->get();
            $user_permission = array();
            foreach($permission as $i){
                array_push($user_permission, array('permission_id'=>$i->id, 'user_id'=>$user->id));
            }
            DB::table('permission_user')->insert($user_permission);
        });
        //return list of teachers
        $teacher  = $this->getTeacher($teacher->institution_id);
        $message = 'Teacher record has been updated successfully';
        return response()->json(compact('message','teacher'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required',
            'email' => 'required|string|email',
            'phone' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $institution_id = $request->get('institution_id');

        //update teacher
        Teacher::where('id', $request->get('id'))
         ->update([
            'first_name'=> strtoupper($request->get('first_name')),
            'middle_name'=> strtoupper($request->get('middle_name')),
            'last_name'=> strtoupper($request->get('last_name')),
            'email'=> $request->get('email'),
            'phone'=> $request->get('phone'),
            'address'=> $request->get('address'),
            'active' => $request->get('active'),
            'gender'=> $request->get('gender'),
            'class_teacher'=>$request->get('class_teacher')
         ]);
          //return list of teachers
        $teacher = $this->getTeacher($institution_id);
        $message = 'Teacher record has been updated successfully';
        return response()->json(compact('message','teacher'));
    }


    //get subjects assigned to teacher
    public function getTeacherSubject($teacher_id){
        $teacher_subjects = DB::table('teacher_subject')
                            ->join('subject', 'subject.id', 'teacher_subject.subject_id')
                            ->join('class', 'class.id', 'teacher_subject.class_id')
                            ->leftJoin('class AS parent', 'class.parent_id', 'parent.id')
                            ->where('teacher_subject.teacher_id', $teacher_id)
                            ->distinct()
                            ->get(['teacher_subject.*', 'subject.name as subject', DB::raw("CONCAT(COALESCE(parent.name,''),' ',class.name) AS class")]);
          return response()->json(compact('teacher_subjects')); 
    }

    public function assignTeacherSubject(Request $request){
        $data = $request->get('teacher_subject');
        $id   = $request->get('id');
        DB::table('teacher_subject')->where('teacher_id', $id)->delete();
        //clear items
         foreach($data as $item){
            DB::table('teacher_subject')->insert([
                'teacher_id' => $item['teacher_id'],
                'subject_id' => $item['subject_id'],
                'class_id'   => $item['class_id']
            ]);
         }
         $message = 'Teacher record has been updated successfully';
         return response()->json(compact('message'));
    }
    
}
