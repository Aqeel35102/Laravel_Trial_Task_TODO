<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    // Fetch all todos
    public function index(): \Illuminate\Http\JsonResponse
    {
        //Per page 10 record
        $perPage = request('perPage', 10);
        $search = request('search', '');
        $userId = auth('api')->user()->id;

        $todos = Todo::where('user_id', $userId)->where('title','LIKE',"%{$search}%")->get()->take($perPage);

        return response()->json(['todos' => $todos]);
    }


    public function store(Request $request)
    {
        //validation of title and description
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ]);
 
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors(),'status'=>422]);
        }
        $input = $request->all();
        $userId = auth('api')->user()->id;
        //create new todo
        $todo = new Todo();
        $todo->user_id = $userId;
        $todo->title = $input['title'];
        $todo->description = $input['description'];
        $todo->save(); 

        return response()->json(['success'=>true,'message'=>"Todo Created Successfully",'todo' => $todo]);
        
    }


    public function show(Request $request,$id)
    {
        // show todo by id
        $todo = Todo::find($id);
        return response()->json(['success'=>true,'todo' => $todo]);
    }

    public function update(Request $request,$id)
    {
        //validation of title and description
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
        ]);
 
        if($validator->fails()){
            return response()->json(['success'=>false,'error'=>$validator->errors(),'status'=>422]);
        }
        // find and update todo by id
        $todo = Todo::find($id);
        $input = $request->all();
        $todo->title = $input['title'];
        $todo->description = $input['description'];
        $todo->save();

        return response()->json(['success'=>true,'message'=>"Todo Updated Successfully" ,'todo' => $todo]);

    }

    public function destroy(Request $request,$id)
    {
        //Delete todo by id
        $todo = Todo::find($id);
        $todo->delete();
        return response()->json(['success'=>true,'message'=>"Todo Deleted Successfully"]);
        
    }
}
