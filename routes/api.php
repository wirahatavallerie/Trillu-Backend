<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardListController;
use App\Http\Controllers\CardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'API'], function(){
    Route::group(['prefix' => 'v1'], function(){
        Route::group(['prefix' => 'auth'], function(){
            Route::post('/register', [UserController::class, 'register']);
            Route::post('/login', [UserController::class, 'login']);
            Route::group(['middleware' => 'auth'], function(){
                Route::get('/logout', [UserController::class, 'logout']);
            });
        });

        Route::group(['middleware' => 'auth'], function(){
            Route::group(['prefix' => 'board'], function(){
                Route::post('/', [BoardController::class, 'create']);
                Route::put('/{board_id}', [BoardController::class, 'update']);
                Route::delete('/{board_id}', [BoardController::class, 'delete']);
                Route::get('/', [BoardController::class, 'get']);
                Route::get('/{board_id}', [BoardController::class, 'open']);
                Route::post('/{board_id}/member', [BoardController::class, 'add_member']);
                Route::delete('/{board_id}/member/{user_id}', [BoardController::class, 'remove_member']);
                
                Route::group(['prefix' => '{board_id}/list'], function(){
                    Route::post('/', [BoardListController::class, 'create']);
                    Route::put('/{list_id}', [BoardListController::class, 'update']);
                    Route::delete('/{list_id}', [BoardListController::class, 'delete']);
                    Route::post('/{list_id}/right', [BoardListController::class, 'right']);
                    Route::post('/{list_id}/left', [BoardListController::class, 'left']);

                    Route::group(['prefix' => '{list_id}/card'], function(){
                        Route::post('/', [CardController::class, 'create']);
                        Route::put('/{card_id}', [CardController::class, 'update']);
                        Route::delete('/{card_id}', [CardController::class, 'delete']);
                    });
                });
            });
            
            Route::post('/card/{card_id}/up', [CardController::class, 'up']);
            Route::post('/card/{card_id}/down', [CardController::class, 'down']);
            Route::post('/card/{card_id}/move/{list_id}', [CardController::class, 'move_list']);
        });
    });
});