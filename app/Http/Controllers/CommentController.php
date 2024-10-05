<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'content'=>'required','restaurantId'=>'required | exists:restaurants,id'
        ]);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],422);
        }
        try{
            $comment = new Comment();
            $comment->user_id = auth()->user()->id;
            $comment->restaurant_id = $request->restaurantId;
            $comment->content = $request->input('content');
            $comment->save();
            return response()->json($comment->id,200);
        }catch(\Exception $e){
            return response()->json(['errors'=> $e->getMessage()],400);
        }
    }

    public function comments(Request $request){
        $validator = Validator::make($request->all(),[
            'perPage'=>'required','restaurantId'=>'required | exists:restaurants,id'
        ]);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],422);
        }
    }
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'restaurantId'=>'required',
            'limit'=>'required',
            'offset' => 'required'
        ]);
        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],422);
        }
        $comments = Comment::where('restaurant_id',$request->restaurantId)
                        ->orderBy('created_at','desc')
                        ->offset($request->offset)
                        ->limit($request->limit)
                        ->with('user')
                        ->get();
        $total =Comment::where('restaurant_id',$request->restaurantId)->count();
        $commentsMapper = $comments->map( function ($comment) {
            return [
                'id'=> $comment->id,
                'content'=>$comment->content,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar'=> Storage::url($comment->user->avatar)
                ],
            ];
        });
        return response()->json(['comments'=>$commentsMapper,'total' => $total],200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
            'newContent'=> 'required'
        ]);
        if($validator->failed()){
            return response()->json(['errors'=>$validator->errors()],422);
        }
        try{
            $comment = Comment::findOrFail($request->id);
            $comment->content = $request->input('content');
            $comment->save();
            return response()->json(true,200);
        }catch(\Exception $e){
            return response()->json(['errors'=> $e->getMessage()],400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id'=> 'required'
        ]);
        if($validator->failed()){
            return response()->json(['errors'=>$validator->errors()],422);
        }
        try{
            $comment = Comment::findOrFail($request->id);
            if($comment->user_id === auth()->user()->id){
                $comment->delete();
                return response()->json(true,200);
            }
            else{
                return response()->json(['errors'=>'permission'],403);
            }
        }catch(\Exception $e){
            return response()->json(['errors'=>$e->getMessage()],400);
        }
    }
}
