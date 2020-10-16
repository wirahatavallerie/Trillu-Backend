<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\BoardMember;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }else{
            $user = $request->attributes->get('user');
            $board = new Board;
            $board->creator_id = $user->id;
            $board->name = $request->name;
            if($board->save()){
                $board_member = new BoardMember;
                $board_member->user_id = $user->id;
                $board_member->board_id = $board->id;
                if($board_member->save()){
                    return response()->json([
                        'message' => 'create board success'
                    ], 200);
                }else{
                    if(!$board->save()){
                        return response()->json([
                            'message' => 'invalid field'
                        ], 422);
                    }
                }
            }else{
                return response()->json([
                    'message' => 'invalid field'
                ], 422);
            }

        }
    }

    public function update(Request $request, $board_id){
        $validator = Validator::make($request->all(),[
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }else{
            $user = $request->attributes->get('user');
            $member_check = BoardMember::where('user_id', $user->id)
                                        ->where('board_id', $board_id)
                                        ->first();
            if($member_check){
                $board = Board::where('id', $board_id)->first();
                $board->creator_id = $user->id;
                $board->name = $request->name;
                if(!$board->save()){
                    return response()->json([
                        'message' => 'invalid field'
                    ], 422);
                }
                
                return response()->json([
                    'message' => 'update board success'
                ], 200);
            }else{
                return response()->json([
                    'message' => 'unauthorized user'
                ], 401);
            }
        }
    }

    public function get(Request $request){        
        $user = $request->attributes->get('user');
        
        $board = Board::where('user_id', $user->id)
                        ->join('board_members', 'board_members.board_id', 'boards.id')
                        ->select('boards.id', 'name', 'creator_id')
                        ->get();
        if($board){            
            return response()->json([
                'boards' => $board
            ], 200);
        }else{
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
    }

    public function open(Request $request, $board_id){        
        $user = $request->attributes->get('user');
        
        $member_check = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $board_id)
                                    ->first();
        if($member_check){
            $board = Board::where('id', $board_id)
                            ->select('id', 'name', 'creator_id')
                            ->first();
            $member = BoardMember::where('board_id', $board_id)
                                    ->join('users', 'users.id', 'board_members.user_id')
                                    ->select('users.id', 'users.first_name', 'users.last_name', 
                                        DB::raw('concat(substr(first_name, 1, 1), substr(last_name, 1, 1)) as initial'))
                                    ->get();
            $list = BoardList::where('board_id', $board_id)
                                ->select('id', 'name', 'order')
                                ->orderBy('order', 'ASC')
                                ->get();
            foreach($list as $l){
                $card = Card::where('list_id', $l->id)
                                ->select('id', 'task', 'order')
                                ->orderBy('order', 'ASC')
                                ->get();
                $l->cards = $card;
            }
            $board->members = $member;
            $board->lists = $list;
            return response()->json($board, 200);
        }else{
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
    }

    public function delete(Request $request, $board_id){
        $user = $request->attributes->get('user');
        $creator_check = Board::where('creator_id', $user->id)
                                    ->where('id', $board_id)
                                    ->delete();
        if($creator_check){ 
            return response()->json([
                'message' => 'delete board success'
            ], 200);
        }else{
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
    }

    public function add_member(Request $request, $board_id){
        $validator = Validator::make($request->all(),[
            'username' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'user did not exist'
            ], 422);
        }else{
            $user = $request->attributes->get('user');
            $board_member = BoardMember::where('user_id', $user->id)
                                        ->where('board_id', $board_id)
                                        ->first();
            if($board_member){
                $username = User::where('username', $request->username)->first();
                if($username){
                    $member_check = BoardMember::where('user_id', $username->id)
                                                ->where('board_id', $board_id)
                                                ->first();
                    if(!$member_check){
                        $member = new BoardMember;
                        $member->user_id = $username->id;
                        $member->board_id = $board_id;
                        if($member->save()){
                            return response()->json([
                                'message' => 'add member success'
                            ], 200); 
                        }
                    }else{
                        return response()->json([
                            'message' => 'user already exist'
                        ], 422);
                    }
                }else{
                    return response()->json([
                        'message' => 'user did not exist'
                    ], 422);
                }
            }else{
                return response()->json([
                    'message' => 'unauthorized user'
                ], 401);
            }
        }
    }

    public function remove_member(Request $request, $board_id, $user_id){
        $user = $request->attributes->get('user');
        $board_member = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $board_id)
                                    ->first();
        if($board_member){
            $member_check = BoardMember::where('user_id', $user_id)
                                        ->where('board_id', $board_id)
                                        ->delete();
            if($member_check){
                return response()->json([
                    'message' => 'remove member success'
                ], 200); 
            }else{
                return response()->json([
                    'message' => 'user did not exist'
                ], 422);
            }
        }else{
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
    }
}
