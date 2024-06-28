<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller {
    public function handleTest(Request $request){
        // Return all the request data
        return response()->json(['req' => $request->all()]);
    }
}
