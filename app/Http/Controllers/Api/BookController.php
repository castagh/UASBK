<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $books = Book::get();

        if (count($books) > 0) {
            foreach ($books as $book) {
                if (empty($book->image)) {
                    unset($book->image);
                } else {
                    $book->image = config('app.url') . ':8000/storage/' . $book->image;
                }
            }

            return response()->json([
                'code' => 202,
                'status' => 'success',
                'message' => 'data successfully accepted',
                'data' => $books
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

        //memvalidasi form
        $validator = validator($request->all(), [
            'name' => ['nullable','string','max:255','unique:books,name'],
            'description' => ['nullable','string'],
            'price' => ['nullable','string'],
            'stok' => ['nullable','numeric'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,svg','max:4092','dimensions:min_width=100,min_height=100']
        ]);

        //validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'message' => 'data not match with our validation',
                'data' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if (!empty($validated['image'])) {
            $file = $validated['image'];
            //untuk menghilangkan spasi pada nama image dan dilengkapki dengan tanggal upload dan username
            $fileName = preg_replace('/\s+/', '', uniqid() . '_' . date('dmY') . '_' . $request->user()->username . '.' . $file->getClientOriginalExtension());

            if (!Storage::exists('/public/'.$fileName)) {
                $file->storeAs('public', $fileName);
            }

            //untuk menyimpan nama yang sudah digenerate sistem
            $validated['image'] = $fileName;
        }

        $book = Book::create($validated);

        //cek value image
        if (empty($book->image)) {
            //hapus image apabila kosong
            unset($book->image);
        } else {
            //untuk menampilkan link gambar yang diupload
            $book->image = config('app.url') . ':8000/storage/' . $book->image;
        }

        return response()->json([
            'code' => 202,
            'status' => 'success',
            'message' => 'data successfully created',
            'data' => $book
        ], 202);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'book not found in our database'
            ], 404);
        }
        
        if (empty($book->image)) {
            unset($book->image);
        } else {
            $book->image = config('app.url') . ':8000/storage/' . $book->image;
        }

        return response()->json([
            'code' => 206,
            'status' => 'success',
            'message' => 'data successfully accepted',
            'data' => $book
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
            'name' => ['nullable','string','max:255','unique:books,name'],
            'description' => ['nullable','string'],
            'price' => ['nullable','string'],
            'stok' => ['nullable','numeric'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,svg','max:4092','dimensions:min_width=100,min_height=100']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'status' => 'error',
                'message' => 'data not match with our validation',
                'data' => $validator->errors()
            ], 422);
        }

        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'book not found in our database'
            ], 404);
        }

        $validated = $validator->validated();

        if (!empty($validated['image'])) {
            $file = $validated['image'];
            $fileName = preg_replace('/\s+/', '', uniqid() . '_' . date('dmY') . '_' . $request->user()->username . '.' . $file->getClientOriginalExtension());

            //gambar yang diupload sebelumnya akan terhapus dan diganti dengan yang baru
            if (!empty($book->image)) {
                if (Storage::exists('/public/'.$book->image)) {
                    Storage::delete('/public/'.$book->image);
                }
            }

            $file->storeAs('public', $fileName);

            $validated['image'] = $fileName;
        }

        $book->update($validated);

        if (empty($book->image)) {
            unset($book->image);
        } else {
            $book->image = config('app.url') . ':8000/storage/' . $book->image;
        }

        return response()->json([
            'code' => 202,
            'status' => 'success',
            'message' => 'data successfully updated',
            'data' => $book
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

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'book not found in our database'
            ], 404);
        }

        if (!empty($book->image)) {
            if (Storage::exists('/public/'.$book->image)) {
                Storage::delete('/public/'.$book->image);
            }
        }

        $book->delete();

        $books = Book::get();

        if (count($books) > 0) {
            foreach ($books as $book) {
                if (empty($book->image)) {
                    unset($book->image);
                } else {
                    $book->image = config('app.url') . ':8000/storage/' . $book->image;
                }
            }

            return response()->json([
                'code' => 202,
                'status' => 'success',
                'message' => 'data successfully removed',
                'data' => $books
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
