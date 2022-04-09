<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subject = Subject::where('is_active', true)->get();
        return response()->json(compact('subject'));                    
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
        //
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

    /** return list of subject assigned to a teacher */
    public function teacherSubject(){
       //get current user
       $current_user = auth()->user();
       //get teacher
      
       $teacher = DB::table('teacher')->where('email', $current_user->email)->select('id')->first(); 
       if($teacher){
        $teacher_subjects = Subject::join('teacher_subject', 'teacher_subject.subject_id', '=','subject.id')
                            ->where('teacher_subject.teacher_id', $teacher->id)
                            ->distinct()
                            ->get(['subject.name', 'subject.id', 'teacher_subject.teacher_id']);
          return response()->json(compact('teacher_subjects'));   
       }else {
          return response()->json(['error' => 'No subject assigned to this teacher'], 400);
       }
                        
    }
}
