<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna beserta relasi bagian.
     */
    public function index(Request $request)
    {
        $query = User::with('bagian', 'ruangans')
            ->withCount('ruangans');

        // Filter role (default: non-admin, tapi super bisa melihat semua)
        if (!Auth::user()->isSuper()) {
            $query->where('role', '!=', 'super');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")            // ✨ Search NIP
                  ->orWhere('jabatan', 'like', "%{$search}%")         // ✨ Search Jabatan
                  ->orWhere('role', 'like', "%{$search}%")
                  ->orWhereHas('bagian', function ($q) use ($search) {
                      $q->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter: hanya yang punya NIP (calon PJ)
        if ($request->boolean('has_nip')) {
            $query->whereNotNull('nip')->where('nip', '!=', '');
        }

        $users = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.index', compact('users'));
    }

    /**
     * Tampilkan form untuk membuat user baru.
     */
    public function create()
    {
        $bagians = Bagian::orderBy('nama')->get();
        return view('admin.create', compact('bagians'));
    }

    /**
     * Simpan user baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'            => 'required|string|max:255',
                'email'           => 'required|email|unique:users,email',
                'password'        => 'required|string|min:6',
                'bagian_id'       => 'nullable|exists:bagian,id',
                'role'            => 'required|in:super,admin,karyawan',
                'profile_photo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                // ✨ Validasi field KIR baru
                'nip'             => 'nullable|string|max:40|unique:users,nip',
                'jabatan'         => 'nullable|string|max:100',
                'gelar_depan'     => 'nullable|string|max:20',
                'gelar_belakang'  => 'nullable|string|max:30',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validated = $validator->validated();

            // Hash password
            $validated['password'] = Hash::make($validated['password']);

            // Normalisasi NIP: hilangkan spasi & titik
            if (!empty($validated['nip'])) {
                $validated['nip'] = preg_replace('/[\s\.]/', '', $validated['nip']);
            }

            // Handle upload foto
            $validated = $this->handleFileUpload($request, new User(), $validated);

            $user = User::create($validated);

            Log::info('User created successfully', [
                'action'     => 'CREATE',
                'user_id'    => $user->id,
                'email'      => $user->email,
                'nip'        => $user->nip,
                'role'       => $user->role,
                'created_by' => Auth::user()->email ?? 'system',
            ]);

            return redirect()->route('admin.index')
                ->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error creating user', [
                'action' => 'CREATE',
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
                'input'  => $request->except(['password', 'profile_photo']),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal membuat user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Tampilkan form edit data user.
     */
    public function edit(User $user)
    {
        $bagians = Bagian::orderBy('nama')->get();
        return view('admin.edit', compact('user', 'bagians'));
    }

    /**
     * Perbarui data user.
     */
    public function update(Request $request, User $user)
    {
        Log::info('🔍 [UserController@update] Request for user_id: ' . $user->id, [
            'data' => $request->except(['password', 'profile_photo']),
        ]);

        try {
            $validator = Validator::make($request->all(), [
                'name'            => 'required|string|max:255',
                'email'           => 'sometimes|email|unique:users,email,' . $user->id,
                'password'        => 'nullable|string|min:6',
                'bagian_id'       => 'nullable|exists:bagian,id',
                'profile_photo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'role'            => 'sometimes|in:super,admin,karyawan',
                'google_avatar'   => 'nullable|url',
                // ✨ Validasi field KIR baru
                'nip'             => 'nullable|string|max:40|unique:users,nip,' . $user->id,
                'jabatan'         => 'nullable|string|max:100',
                'gelar_depan'     => 'nullable|string|max:20',
                'gelar_belakang'  => 'nullable|string|max:30',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Set default role jika tidak ada
            if (empty($validated['role'])) {
                $validated['role'] = $user->role;
            }

            // Set default bagian_id jika tidak ada
            if (!array_key_exists('bagian_id', $validated)) {
                $validated['bagian_id'] = $user->bagian_id;
            }

            // Handle password
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']);
            }

            // Normalisasi NIP: hilangkan spasi & titik
            if (!empty($validated['nip'])) {
                $validated['nip'] = preg_replace('/[\s\.]/', '', $validated['nip']);
            } elseif (array_key_exists('nip', $validated)) {
                // User menghapus NIP
                $validated['nip'] = null;
            }

            // Handle file upload
            $validated = $this->handleFileUpload($request, $user, $validated);
            $user->update($validated);

            // Generate URL foto
            $user->profile_photo_url = $this->getPhotoUrl($user);

            Log::info('✅ [UserController@update] Success', [
                'user_id'   => $user->id,
                'nip'       => $user->nip,
                'photo_url' => $user->profile_photo_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui.',
                'data'    => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [UserController@update] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        try {
            // Cegah user menghapus dirinya sendiri
            if (Auth::id() === $user->id) {
                return redirect()->back()
                    ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
            }

            // Cegah penghapusan jika user masih jadi PJ ruangan
            if ($user->ruangans()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus user yang masih menjadi penanggung jawab ' . $user->ruangans()->count() . ' ruangan.');
            }

            $logData = [
                'action'     => 'DELETE',
                'user_id'    => $user->id,
                'email'      => $user->email,
                'nip'        => $user->nip,
                'role'       => $user->role,
                'deleted_by' => Auth::user()->email ?? 'system',
            ];

            // Hapus foto profil jika ada
            if ($user->profile_photo_path) {
                $path = public_path('images/profile/' . $user->profile_photo_path);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            $user->delete();

            Log::info('User deleted successfully', $logData);

            return redirect()->route('admin.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'action' => 'DELETE',
                'user_id' => $user->id,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    // ================= PRIVATE METHODS =================

    /**
     * Handle upload foto profil user.
     */
    private function handleFileUpload(Request $request, User $user, array $validated = []): array
    {
        if ($request->hasFile('profile_photo')) {
            $directory = public_path('images/profile');
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $file     = $request->file('profile_photo');
            $filename = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            // Hapus foto lama jika ada
            if ($user->profile_photo_path && File::exists($directory . '/' . $user->profile_photo_path)) {
                File::delete($directory . '/' . $user->profile_photo_path);
            }

            $validated['profile_photo_path'] = $filename;
        } elseif (!isset($validated['profile_photo_path'])) {
            $validated['profile_photo_path'] = $user->profile_photo_path;
        }

        return $validated;
    }

    /**
     * Generate URL foto profil.
     */
    private function getPhotoUrl(User $user): string
    {
        if ($user->profile_photo_path && File::exists(public_path('images/profile/' . $user->profile_photo_path))) {
            return asset('images/profile/' . $user->profile_photo_path);
        }

        return asset('images/avatar.png');
    }
}
