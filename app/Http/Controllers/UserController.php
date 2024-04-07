<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get current user
     *
     * @param Request $request
     * @return Response
     */
    public function current(Request $request)
    {
        return response($request->user());
    }
}
