<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolCalendar;
use Illuminate\Support\Facades\DB;

class SchoolCalendarController extends Controller
{
    //get school calendar
    public function getSchoolCalendar($institution_id){
        $results = SchoolCalendar::where('institution_id', $institution_id)->get();
        return response()->json(compact('results'));
    }

    public function addSchoolCalendar(Request $request){
        $code = $request->get('code');
        $name = $request->get('name');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $institution_id = $request->get('institution_id');

        $data = array('code'=>$code, 'name'=>$name, 'start_date'=>$start_date, 'end_date'=>$end_date, 'institution_id'=>$institution_id);
        if(SchoolCalendar::create($data)){
            $message = 'Students records has been uploaded successfully';
            return response()->json(compact('data', 'message'));
        }else {
            $message = 'Fail to save calendar year';
            return response()->json(compact('data', 'message'));
        }
    }

    //get school terms
    public function getSchoolTerm($school_calendar_id){
        $results = DB::table('term')->where('school_calendar_id', $school_calendar_id)->get();
        return response()->json(compact('results'));
    }
}
