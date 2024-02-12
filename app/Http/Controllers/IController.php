<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

interface IController
{
    public function index(Request $request);
    public function update(Request $request);
    public function destroy(Request $request);
    public function store(Request $request);


    //
}
