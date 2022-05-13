<?php

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


Route::get('articulos', 'ArticuloController@index');
Route::get('articulos/{articulo}', 'ArticuloController@show');
Route::post('articulos', 'ArticuloController@store');
Route::put('articulos/{articulo}', 'ArticuloController@update');
Route::delete('articulos/{articulo}', 'ArticuloController@delete');


Route::get('categorias', 'CategoriaController@index');
Route::get('categorias/{categoria}', 'CategoriaController@show');
Route::post('categorias','CategoriaController@store');
Route::put('categorias/{categoria}','CategoriaController@update');
Route::delete('categorias/{categoria}','CategoriaController@delete');

/*
Route::get('articulos', 'ArticuloController@getArticulo');
Route::get('articulos/{id}', 'ArticuloController@getArticuloById');
Route::post('articulos', 'ArticuloController@addArticulo');
Route::put('articulos/{id}', 'ArticuloController@updateArticulo');
Route::delete('articulos/{id}', 'ArticuloController@deleteArticulo');*/


