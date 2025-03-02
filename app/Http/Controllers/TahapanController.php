<?php

namespace App\Http\Controllers;

use App\Models\Tahapan;
use App\Models\DataAnggaran;

use Illuminate\Http\Request;

class TahapanController extends Controller
{
    public function index()
    {
        $tahapans = Tahapan::all();
        return view('tahapan.index', compact('tahapans'));
    }

    public function create()
    {
        return view('tahapan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);

        Tahapan::create($request->all());

        return redirect()->route('tahapan.index')
                         ->with('success', 'Tahapan created successfully.');
    }

    public function show(Tahapan $tahapan)
    {
        return view('tahapan.show', compact('tahapan'));
    }

    public function edit(Tahapan $tahapan)
    {
        return view('tahapan.edit', compact('tahapan'));
    }

    public function update(Request $request, Tahapan $tahapan)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);

        $tahapan->update($request->all());

        return redirect()->route('tahapan.index')
                         ->with('success', 'Tahapan updated successfully.');
    }

    public function destroy($id)
    {
        $tahapan = Tahapan::find($id);

        // Periksa apakah tahapan sudah digunakan pada data anggaran
        if (DataAnggaran::where('tahapan_id', $id)->exists()) {
            return response()->json(['error' => 'Tahapan tidak dapat dihapus karena sudah digunakan pada data anggaran.']);
        }

        $tahapan->delete();
        return response()->json(['success' => 'Tahapan berhasil dihapus.']);
    }
}
