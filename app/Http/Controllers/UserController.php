<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::get();
        return UserResource::collection($users);
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
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'cpassword' => 'required',
            'role_id' => 'required',
            'user_id' => 'required'
        ]);
        //check authorizaton
        $user = User::find($request->input('user_id'));
        $user->authorizeRoles(['administrator', 'site manager']);
        //check if passwords match
        if($request->input('password') !== $request->input('cpassword')) {
            return response()->json(['error_msg'=>'passwords do not match'], '500');
        }
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);
        $user->roles()->attach(Role::where('id', $request->input('role_id'))->first());
        return $user;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required'
        ]);
        $user = User::find($request->input('user_id'));
        return new UserResource($user);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $user)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $this->validate($request, [
            'name' => 'required',
            'about' => 'required'
        ]);
        $user = User::find($request->input('id'));
        $user->name = $request->input('name');
        $user->about = $request->input('about');
        if($user->save()) {
            return new UserResource($user);
        }
    }
    public function adminUpdate(Request $request) {
        $this->validate($request, [
            'id' => 'required',
            'name' => 'required',
            'role_id' => 'required',
            'user_id' => 'required'
        ]);
        $user = User::findOrFail($request->input('id'));
        $user->name = $request->input('name');
        $user->save();
        $user->roles()->sync($request->input('role_id'));
        return response()->json(['success'=>1], '200');
    }
    public function changePassword(Request $request) {
        $this->validate($request, [
            'user_id' => 'required',
            'old' => 'required',
            'new' => 'required',
            'confirm' => 'required'
        ]);
        //check for password mismatch
        if($request->input('new') !== $request->input('confirm')) {
            return response()->json(['success'=>0, 'message'=>'password mismatch'], '500');
        }
        $user = User::findOrFail($request->input('user_id'));
        
        $user->password = bcrypt($request->input('new'));
        if($user->save()) {
            return response()->json(['success'=>1], '200');
        }
    }
    public function updateThumbnail(Request $request) {
        $this->validate($request, [
            'user_id' => 'required',
            'qqfile' => 'image|nullable|max:1999'
        ]);
        if($request->hasFile('qqfile')) {
            //Get filename with the extension
            $filenameWithExt = $request->file('qqfile')->getClientOriginalName();
            //get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //get just ext
            $extension = $request->file('qqfile')->getClientOriginalExtension();
            //filename to store
            $filenameToStore = $filename.'_'.time().'.'.$extension;
            // upload image
            $path = $request->file('qqfile')->storeAs('public/users/thumbnails', $filenameToStore);
        }
        $user = User::find($request->input('user_id'));
        if($request->hasFile('qqfile')) {
            //delete the previous file
            if($user->thumbnail !== "users/thumbnails/default.jpg" 
            && $user->thumbnail !== NULL && $user->thumbnail !== "") {
                //Storage::delete("/".$user->thumbnail);
                unlink(public_path("storage/".$user->thumbnail));
            }
        }
        $user->thumbnail = "users/thumbnails/".$filenameToStore;
        if($user->save()) {
            $response['id'] = $user->id;
            $response['thumbnail'] = $user->thumbnail;
            $response['success'] = 1;
            return response()->json($response, 200);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}