<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use App\Models\Alat;
use App\Models\User;
use App\Services\HashIdService; // ✅ 1. Import HashIdService
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException; // ✅ 2. Import Exception

class RuanganController extends Controller
{
    // ✅ 3. Tambahkan Helper Method untuk resolve HashID
    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) {
            throw new ModelNotFoundException('Data ruangan tidak ditemukan.');
        }
        return $id;
    }

    public function index(Request $request)
    {
        $query = Ruangan::withCount('alats')->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_ruangan', 'like', "%{$search}%")
                  ->orWhere('nama_ruangan', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $ruangans = $query->orderBy('kode_ruangan')->paginate(12)->withQueryString();

        return view('ruangans.index', compact('ruangans'));
    }

    public function create()
    {
        $users = User::where('role', '!=', 'super')->orderBy('name')->get();
        return view('ruangans.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_ruangan' => 'required|string|max:20|unique:ruangans,kode_ruangan',
            'nama_ruangan' => 'nullable|string|max:100',
            'user_id'      => 'nullable|exists:users,id',
        ]);

        Ruangan::create($validated);

        return redirect()->route('ruangans.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    // ✅ 4. Ubah parameter dari (Ruangan $ruangan) menjadi ($hashid)
    public function show($hashid)
    {
        $id = $this->resolveHashid($hashid);
        $ruangan = Ruangan::with(['alats.kategori', 'user'])->findOrFail($id);

        $stats = [
            'total_items'     => $ruangan->alats->count(),
            'total_baik'      => $ruangan->alats->sum('jumlah_baik'),
            'total_kurang_baik'=> $ruangan->alats->sum('jumlah_kurang_baik'),
            'total_rusak_berat'=> $ruangan->alats->sum('jumlah_rusak_berat'),
        ];

        return view('ruangans.show', compact('ruangan', 'stats'));
    }

    // ✅ 5. Ubah parameter menjadi ($hashid)
    public function edit($hashid)
    {
        $id = $this->resolveHashid($hashid);
        $ruangan = Ruangan::findOrFail($id);
        $users = User::where('role', '!=', 'super')->orderBy('name')->get();

        return view('ruangans.edit', compact('ruangan', 'users'));
    }

    // ✅ 6. Ubah parameter menjadi (Request $request, $hashid)
    public function update(Request $request, $hashid)
    {
        $id = $this->resolveHashid($hashid);
        $ruangan = Ruangan::findOrFail($id);

        $validated = $request->validate([
            'kode_ruangan' => 'required|string|max:20|unique:ruangans,kode_ruangan,' . $ruangan->id,
            'nama_ruangan' => 'nullable|string|max:100',
            'user_id'      => 'nullable|exists:users,id',
        ]);

        $ruangan->update($validated);

        return redirect()->route('ruangans.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    // ✅ 7. Ubah parameter menjadi ($hashid)
    public function destroy($hashid)
    {
        $id = $this->resolveHashid($hashid);
        $ruangan = Ruangan::findOrFail($id);

        if ($ruangan->alats()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus ruangan yang masih memiliki barang.');
        }

        $ruangan->delete();

        return back()->with('success', 'Ruangan berhasil dihapus.');
    }
}
