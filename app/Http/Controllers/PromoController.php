<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromoController extends Controller
{
    public function index()
    {
        return Promo::all();
    }

    public function show(Promo $promo)
    {
        return $promo;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_id' => 'required',
            'title_en' => 'required',
            'title_ru' => 'required',
            'description_id' => 'required',
            'description_en' => 'required',
            'description_ru' => 'required',
            'price' => 'required',
            'old_price' => 'nullable',
            'end_date' => 'required|date',
            'pdf_url' => 'required|url',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/promos');
            $validated['image'] = str_replace('public/', '', $path);
        }

        return Promo::create($validated);
    }

    public function update(Request $request, Promo $promo)
    {
        $validated = $request->validate([
            'title_id' => 'sometimes|required',
            'title_en' => 'sometimes|required',
            'title_ru' => 'sometimes|required',
            'description_id' => 'sometimes|required',
            'description_en' => 'sometimes|required',
            'description_ru' => 'sometimes|required',
            'price' => 'sometimes|required',
            'old_price' => 'nullable',
            'end_date' => 'sometimes|required|date',
            'pdf_url' => 'sometimes|required|url',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Update gambar jika ada
        if ($request->hasFile('image')) {
            // Hapus gambar lama
            Storage::delete('public/' . $promo->image);
            
            // Simpan gambar baru
            $path = $request->file('image')->store('public/promos');
            $validated['image'] = str_replace('public/', '', $path);
        }

        $promo->update($validated);
        return $promo;
    }

    public function destroy(Promo $promo)
    {
        // Hapus gambar terkait
        Storage::delete('public/' . $promo->image);
        
        $promo->delete();
        return response()->noContent();
    }
}