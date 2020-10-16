<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BoardMember;
use App\Models\BoardList;

class BoardListController extends Controller
{
    public function create(Request $request, $board_id){
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
                $listOrder = BoardList::where('board_id', $board_id)
                                            ->orderBy('order', 'DESC')
                                            ->first();
                $order = 1;
                if($listOrder){
                    $order = $listOrder->order + 1;
                }
                $board_list = new BoardList;
                $board_list->board_id = $board_id;
                $board_list->name = $request->name;
                $board_list->order = $order;
                if(!$board_list->save()){
                    return response()->json([
                        'message' => 'invalid field'
                    ], 422);
                }
                
                return response()->json([
                    'message' => 'create list success'
                ], 200);
            }else{
                return response()->json([
                    'message' => 'unauthorized user'
                ], 401);
            }
        }
    }

    public function update(Request $request, $board_id, $list_id){
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
                $board_list = BoardList::where('id', $list_id)
                                            ->first();
                // $board_list->board_id = $board_id;
                $board_list->name = $request->name;
                // $board_list->order = $order;
                if(!$board_list->save()){
                    return response()->json([
                        'message' => 'invalid field'
                    ], 422);
                }
                
                return response()->json([
                    'message' => 'update list success'
                ], 200);
            }else{
                return response()->json([
                    'message' => 'unauthorized user'
                ], 401);
            }
        }
    }

    public function delete(Request $request, $board_id, $list_id){
        $user = $request->attributes->get('user');
        $member_check = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $board_id)
                                    ->first();
        if($member_check){
            $board_list = BoardList::where('id', $list_id)
                                        ->delete();
            if(!$board_list){
                return response()->json([
                    'message' => 'invalid field'
                ], 422);
            }
            
            return response()->json([
                'message' => 'delete list success'
            ], 200);
        }else{
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
    }

    public function right(Request $request, $board_id, $list_id){
        $user = $request->attributes->get('user');
        $member_check = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $board_id)
                                    ->first();
        if($member_check){
            $board_list = BoardList::where('id', $list_id)->first();
            $listOrder = Boardlist::where('order', '>', $board_list->order)
                                    ->orderBy('order', 'ASC')
                                    ->first();
            $rightOrder = $board_list->order;
            $leftOrder = $listOrder->order;
            $board_list->order = $leftOrder;
            $listOrder->order = $rightOrder;
            if($board_list->save() && $listOrder->save()){
                return response()->json([
                    'message' => 'move success'
                ], 200);
            }
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }else{
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
    }

    public function left(Request $request, $board_id, $list_id){
        $user = $request->attributes->get('user');
        $member_check = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $board_id)
                                    ->first();
        if($member_check){
            $board_list = BoardList::where('id', $list_id)->first(); //id3 order2
            $listOrder = Boardlist::where('order', '<', $board_list->order)
                                    ->orderBy('order', 'DESC')
                                    ->first(); //id4 order1
            $rightOrder = $board_list->order; //2
            $leftOrder = $listOrder->order; //1
            $board_list->order = $leftOrder; //2->1
            $listOrder->order = $rightOrder; //1->2
            if($board_list->save() && $listOrder->save()){
                return response()->json([
                    'message' => 'move success'
                ], 200);
            }
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }else{
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
    }
}
