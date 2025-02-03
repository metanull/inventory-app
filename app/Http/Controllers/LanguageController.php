<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Language::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $language = new Language();
        $language->id = $request->input('id');
        $language->internal_name = $request->input('internal_name');
        $language->save();
        return response()->json($language, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Language $language)
    {
        return $language;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Language $language)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Language $language)
    {
        $language->update($request->all());
        return response()->json($language, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language)
    {
        $language->delete();
        return response()->json(null, 204);
    }
}
