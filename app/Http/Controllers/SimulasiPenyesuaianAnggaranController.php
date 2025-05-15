<?php

namespace App\Http\Controllers;

use App\Models\SimulasiPenyesuaianAnggaran;
use Illuminate\Http\Request;

class SimulasiPenyesuaianAnggaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'kode_opd' => 'required|string',
            'kode_rekening' => 'required|string',
            'operasi' => 'required|in:+,-',
            'nilai' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        SimulasiPenyesuaianAnggaran::create($request->only(['kode_opd', 'kode_rekening', 'operasi', 'nilai', 'keterangan']));

        // Redirect with filter params so filter stays after submit
        return redirect()->route('simulasi-perubahan.index', [
            'tahapan_id' => $request->tahapan_id,
            'skpd' => $request->skpd,
        ])->with('success', 'Data simulasi penyesuaian anggaran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_opd' => 'required|string',
            'kode_rekening' => 'required|string',
            'operasi' => 'required|in:+,-',
            'nilai' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        $row = SimulasiPenyesuaianAnggaran::findOrFail($id);
        $row->update($request->only(['kode_opd', 'kode_rekening', 'operasi', 'nilai', 'keterangan']));

        return redirect()->route('simulasi-perubahan.index', [
            'tahapan_id' => $request->tahapan_id,
            'skpd' => $request->skpd,
        ])->with('success', 'Data simulasi penyesuaian anggaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        $row = SimulasiPenyesuaianAnggaran::findOrFail($id);
        $row->delete();

        // Ambil filter dari request jika ada
        $tahapanId = $request->tahapan_id ?? request('tahapan_id');
        $skpd = $request->skpd ?? request('skpd');

        return redirect()->route('simulasi-perubahan.index', [
            'tahapan_id' => $tahapanId,
            'skpd' => $skpd,
        ])->with('success', 'Data simulasi penyesuaian anggaran berhasil dihapus.');
    }
}
