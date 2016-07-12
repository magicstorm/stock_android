<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Carbon\Carbon;

class ManagementPagesController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function applyToken(){
        return view('management.tokenGen');
    }

    public function genToken(Request $request){
        $user = auth()->user();
        $api_token = $user->genToken();
        return view('management.tokenGen', compact('api_token'));
    }
}
