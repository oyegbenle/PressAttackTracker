<?php

namespace App\Http\Controllers;

use App\Report;
use Illuminate\Http\Request;
use App\Http\Requests\Report\NewRequest;
use App\Http\Requests\Report\UpdateRequest;
use App\Http\Requests\Report\DelRequest;
use App\Http\Requests\Report\UpdateStatusRequest;
use App\Http\Requests\Report\UploadEvidenceRequest;
use App\Http\Resources\ReportResource;
use Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $reports = Report::paginate();

        if(Auth::user()->hasRole('user')) {
            return view('report.view', ['reports' => $reports]);
        }

        return view('admin.report.view_all', ['reports' => $reports]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($response = []) {
        if(Auth::user()->hasRole('user')) {
            return view('report.add', $response);
        }
        
        return view('admin.report.add', $response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NewRequest $request) {
        $report = new Report();
        $report->report_type_id = $request->input('report_type_id');
        $report->user_id = Auth::user()->id;
        $report->title = $request->input('title');
        $report->description = $request->input('description');
        $report->location = $request->input('location');
        $report->victim = $request->input('victim');
        $report->affiliation = $request->input('affiliation');
        $report->assailant = $request->input('assailant');
        $report->status_id = 4;
        $report->date = $request->input('date');

        if($report->save()) {
            $success = 1;
        } else {
            $success = 0;
        }

        if(Auth::user()->hasRole('user')) {
            return redirect()->route('report', ['id'=>$report->id]);
        }

        return redirect()->action('ReportController@show', [
            'id'=>$report->id
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function show($id, $success = -1) {
        $report = Report::with('user')->findOrFail($id);

        $report->views = $report->views + 1;
        $report->save();

        if(Auth::user()->hasRole('user')) {
            return view('report.view', [
                'report' => $report
            ]);
        }

        if($success != -1) {
            return view('admin.report.view', [
                'report' => $report,
                'update_success' => $success
            ]);
        }

        return view('admin.report.view', [
            'report' => $report
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $success = -1) {
        $report = Report::findOrFail($id);

        if(Auth::user()->hasRole('user')) {
            return view('report.edit', ['report' => $report]);
        }

        if($success != -1) {
            return view('admin.report.edit', [
                'report' => $report, 
                'success' => $success
            ]);
        } 

        return view('admin.report.edit', ['report' => $report]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request) {

        $report = Report::findOrFail($request->input('id'));
        
        $report->report_type_id = $request->input('report_type_id');
        $report->title = $request->input('title');
        $report->description = $request->input('description');
        $report->location = $request->input('location');
        $report->victim = $request->input('victim');
        $report->affiliation = $request->input('affiliation');
        $report->assailant = $request->input('assailant');
        $report->date = $request->input('date');
        
        if($report->save()) {
            $success = 1;
        } else {
            $success = 0;
        }

        if(Auth::user()->hasRole('user')) {
            return redirect()->route('report', [
                'id' => $report->id,
                'success' => $success
            ]);
        }

        return redirect()->action('ReportController@show', [
            'id'=>$request->input('id'), 
            'success'=>$success
        ]);
    }

    public function verify($id) {
        $report = Report::findOrFail($id);

        $report->status_id = 5;

        if($report->save()) {
            $success = 1;
        } else {
            $success = 0;
        }

        return redirect()->action('ReportController@show', [
            'id'=>$id, 
            'success'=>$success
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $report = Report::findOrFail($id);
        
        if($report->delete()) {
            $success = 1;
        } else {
            $success = 0;
        }

        if(Auth::user()->hasRole('user')) {
            return redirect()->route('reports', [
                'delete_success' => $success
            ]);
        }

        return redirect()->action('ReportController@index', [
            'delete_success' => $success
        ]);
    }
}
