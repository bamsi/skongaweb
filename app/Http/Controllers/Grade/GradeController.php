<?php

namespace App\Http\Controllers\Grade;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Grade;
use Illuminate\Support\Facades\DB;


class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $class = Grade::leftJoin('class AS parent', 'class.parent_id', 'parent.id')
                 ->where('class.is_active', true)
                 ->select([DB::raw("CONCAT(COALESCE(parent.name,''),' ',class.name) AS name"), 'class.id'])
                 ->where('class.institution_id', $id)
                 ->get();
        return response()->json(compact('class'));   
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
/** return list of class assigned to a particular teacher */
    public function teacherGrade($subject_id){
        //get current user
       $current_user = auth()->user();
       //get teacher
      
       $teacher = DB::table('teacher')->where('email', $current_user->email)->select('id')->first(); 
       if($teacher){
         $teacher_grade = Grade::join('teacher_subject', 'teacher_subject.class_id','=','class.id')
                          ->leftJoin('class AS parent_class','parent_class.id','class.parent_id')
                          ->where('teacher_subject.teacher_id', $teacher->id)
                          ->where('teacher_subject.subject_id', $subject_id)
                          ->where('class.is_active', 1)
                          ->select([DB::raw("CONCAT(parent_class.name,' ',class.name) AS name"), 'class.id'])
                          ->get();
         return response()->json(compact('teacher_grade'));   
        }else {
            return response()->json(['error' => 'No subject assigned to this teacher'], 400);
        }         
    }

    public function getClassTeacherClass(){
        //get current user
       $current_user = auth()->user();
       //get teacher
      
       $teacher = DB::table('teacher')->where('email', $current_user->email)->select('id')->first();
       
       if($teacher){
           /**
         $class = Grade::join('teacher', 'teacher.class_id', 'class.id')
                   ->where('teacher.id', $teacher->id)
                   ->select('class.id', 'class.name')
                   ->get();
           **/
         $teacher_id = $teacher->id;
         $class = DB::table('class')
                  ->join('assign_teacher_class', 'assign_teacher_class.class_id', 'class.id')
                  ->where('assign_teacher_class.teacher_id', $teacher_id)
                  ->select('class.id', 'class.name')
                  ->get();
         return response()->json(compact('class'));   

        }else {
            return response()->json(['error' => 'No subject assigned to this teacher'], 400);
       }   
    }

}
