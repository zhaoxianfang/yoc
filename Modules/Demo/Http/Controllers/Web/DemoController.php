<?php

namespace Modules\Demo\Http\Controllers\Web;

use Illuminate\Http\Request;
use Modules\Demo\Http\Controllers\DemoBaseController;

class DemoController extends DemoBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        trace('hello world');

        return view('demo::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('demo::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('demo::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('demo::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
