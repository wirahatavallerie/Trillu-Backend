<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BoardMember;
use App\Models\Card;

class CardController extends Controller
{
    public function create(Request $request, $board_id, $list_id){
        $validator = Validator::make($request->all(),[
            'task' => 'required'
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
                $cardOrder = Card::where('list_id', $list_id)
                                    ->orderBy('order', 'DESC')
                                    ->first();
                $order = 1;
                if($cardOrder){
                    $order = $cardOrder->order + 1;
                }
                $card = new Card;
                $card->list_id = $list_id;
                $card->order = $order;
                $card->task = $request->task;
                if(!$card->save()){
                    return response()->json([
                        'message' => 'invalid field'
                    ], 422);
                }
                
                return response()->json([
                    'message' => 'create card success'
                ], 200);
            }
        }
    }
    public function update(Request $request, $board_id, $list_id, $card_id){
        $validator = Validator::make($request->all(),[
            'task' => 'required'
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
                $card = Card::where('id', $card_id)->first();
                $card->task = $request->task;
                if(!$card->save()){
                    return response()->json([
                        'message' => 'invalid field'
                    ], 422);
                }
                
                return response()->json([
                    'message' => 'update card success'
                ], 200);
            }
        }
    }

    public function delete(Request $request, $board_id, $list_id, $card_id){
        $user = $request->attributes->get('user');
        $member_check = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $board_id)
                                    ->first();
        if($member_check){
            $card = Card::where('id', $card_id)->delete();
            if(!$card){
                return response()->json([
                    'message' => 'unauthorized user'
                ], 401);
            }
            
            return response()->json([
                'message' => 'delete card success'
            ], 200);
        }
    }

    public function up(Request $request, $card_id){
        $user = $request->attributes->get('user');
        $card = Card::where('id', $card_id)->first();
        $boardList = BoardList::where('board_lists.id', $card->list_id)
                                ->join('boards', 'boards.id', 'board_lists.board_id')
                                ->first();
        $member_check = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $boardList->board_id)
                                    ->first();
        if($member_check){ 
            $cardUp = Card::where('id', $card_id)
                            ->where('list_id', $card->list_id)
                            ->first();
            $cardOrder = Card::where('order', '<', $cardUp->order)
                                ->where('list_id', $cardUp->list_id)
                                ->orderBy('order', 'DESC')
                                ->first(); 
            $down = $cardUp->order; //20
            $up = $cardOrder->order; //19
            $cardUp->order = $up;
            $cardOrder->order = $down;
            if($cardUp->save() && $cardOrder->save()){
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

    public function down(Request $request, $card_id){
        $user = $request->attributes->get('user');
        $card = Card::where('id', $card_id)->first();
        $boardList = BoardList::where('board_lists.id', $card->list_id)
                                ->join('boards', 'boards.id', 'board_lists.board_id')
                                ->first();
        $member_check = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $boardList->board_id)
                                    ->first();
        if($member_check){ 
            $cardDown = Card::where('id', $card_id)
                            ->where('list_id', $card->list_id)
                            ->first();
            $cardOrder = Card::where('order', '>', $cardDown->order)
                                ->where('list_id', $cardDown->list_id)
                                ->orderBy('order', 'ASC')
                                ->first(); 
            $down = $cardDown->order; 
            $up = $cardOrder->order;
            $cardDown->order = $up;
            $cardOrder->order = $down;
            if($cardDown->save() && $cardOrder->save()){
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

    public function move_list(Request $request, $card_id, $list_id){
        $user = $request->attributes->get('user');
        $card = Card::where('id', $card_id)->first();
        $boardList = BoardList::where('board_lists.id', $card->list_id)
                                ->join('boards', 'boards.id', 'board_lists.board_id')
                                ->first();
        $member_check = BoardMember::where('user_id', $user->id)
                                    ->where('board_id', $boardList->board_id)
                                    ->first();
        if($member_check){ 
            $list = BoardList::where('id', $list_id)->first();
            if($list->board_id === $boardList->board_id){
                $cardOrder = Card::where('list_id', $list_id)
                                        ->orderBy('order', 'DESC')
                                        ->first(); 
                $order = 1;
                if($cardOrder){
                    $order = $cardOrder->order + 1;
                }
                $card->order = $order;
                $card->list_id = $list_id;
                if($card->save()){
                    return response()->json([
                        'message' => 'move success'
                    ], 200);
                }
                return response()->json([
                    'message' => 'unauthorized user'
                ], 401);
            }
        }else{
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }
    }
}
