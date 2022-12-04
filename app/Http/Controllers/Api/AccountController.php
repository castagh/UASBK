<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->is_admin == 'false') {
            return response()->json([
                'code' => 202,
                'status' => 'success',
                'message' => 'data successfully accepted',
                'data' => $request->user()
            ], 202);
        }

        $users = User::get();

        if (count($users) > 0) {
            return response()->json([
                'code' => 202,
                'status' => 'success',
                'message' => 'data successfully accepted',
                'data' => $users
            ], 202);
        }

        return response()->json([
            'code' => 202,
            'status' => 'success',
            'message' => 'data successfully accepted',
            'data' => 'no data available'
        ], 202);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->user()->is_admin == 'false') {
            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => 'unauthenticated access'
            ], 401);
        }

        //memvalidasi form yang diisi user
        $validator = validator($request->all(), [
            'username' => ['required','string','max:255','unique:users,username'],
            'email' => ['required','string','max:255','unique:users,email'],
            'password' => ['required','string','min:8','max:255','confirmed']
        ]);

        //apabila validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'message' => 'data not match with our validation',
                'data' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'code' => 202,
            'status' => 'success',
            'message' => 'data successfully created',
            'data' => $user
        ], 202);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->user()->is_admin == 'false') {
            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => 'unauthenticated access'
            ], 401);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'user not found in our database'
            ], 404);
        }

        return response()->json([
            'code' => 206,
            'status' => 'success',
            'message' => 'data successfully accepted',
            'data' => $user
        ], 206);
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
        if ($request->user()->is_admin == 'false') {
            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => 'unauthenticated access'
            ], 401);
        }

        $validator = validator($request->all(), [
            'username' => ['nullable','string','max:255','unique:users,username'],
            'email' => ['nullable','string','max:255','unique:users,email']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'message' => 'data not match with our validation',
                'data' => $validator->errors()
            ], 422);
        }

        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'user not found in our database'
            ], 404);
        }

        $user->update($validator->validated());

        return response()->json([
            'code' => 202,
            'status' => 'success',
            'message' => 'data successfully updated',
            'data' => $user
        ], 202);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->is_admin == 'false') {
            return response()->json([
                'code' => 401,
                'status' => 'error',
                'message' => 'unauthenticated access'
            ], 401);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'user not found in our database'
            ], 404);
        }

        $user->delete();

        $users = User::get();

        if (count($users) > 0) {
            return response()->json([
                'code' => 202,
                'status' => 'success',
                'message' => 'data successfully removed',
                'data' => $users
            ], 202);
        }

        return response()->json([
            'code' => 202,
            'status' => 'success',
            'message' => 'data successfully removed',
            'data' => 'no data available'
        ], 202);
    }
}