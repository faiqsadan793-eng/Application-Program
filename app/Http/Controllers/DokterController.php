<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DokterController extends Controller
{
    private const POLI_LIST = [
        'Poli Umum',
        'Poli Gigi',
        'Poli Anak',
    ];

    /**
     * Menampilkan dokter beserta akun loginnya untuk staff.
     */
    public function index(): View
    {
        $dokters = Dokter::with('user')
            ->latest('id_dokter')
            ->get();

        return view('master-dokter.index', [
            'dokters' => $dokters,
            'poliList' => self::POLI_LIST,
        ]);
    }

    /**
     * Membuat akun login dan profil dokter dalam satu transaksi.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'dokter',
                'email_verified_at' => now(),
            ]);

            Dokter::create([
                'user_id' => $user->id,
                'nip_sip' => $validated['nip_sip'],
                'poli' => $validated['poli'],
            ]);
        });

        return redirect()->route('dokter.index')
            ->with('success', 'Data dokter dan akun login berhasil ditambahkan.');
    }

    /**
     * Memperbarui profil dokter serta akun login yang terhubung.
     */
    public function update(Request $request, Dokter $dokter): RedirectResponse
    {
        $dokter->load('user');
        $validated = $request->validate($this->rules($dokter));

        DB::transaction(function () use ($dokter, $validated): void {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $dokter->user->update($userData);
            $dokter->update([
                'nip_sip' => $validated['nip_sip'],
                'poli' => $validated['poli'],
            ]);
        });

        return redirect()->route('dokter.index')
            ->with('success', 'Data dokter berhasil diperbarui.');
    }

    /**
     * Menghapus akun login dokter. Foreign key akan ikut menghapus profil dokternya.
     */
    public function destroy(Dokter $dokter): RedirectResponse
    {
        $dokter->load('user');
        $namaDokter = $dokter->user->name;

        DB::transaction(function () use ($dokter): void {
            $dokter->user->delete();
        });

        return redirect()->route('dokter.index')
            ->with('success', 'Data dokter ' . $namaDokter . ' dan akun loginnya berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Dokter $dokter = null): array
    {
        $passwordRules = $dokter
            ? ['nullable', 'string', 'min:8', 'max:255']
            : ['required', 'string', 'min:8', 'max:255'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($dokter?->user_id),
            ],
            'password' => $passwordRules,
            'nip_sip' => [
                'required',
                'string',
                'max:100',
                Rule::unique('dokters', 'nip_sip')->ignore($dokter?->id_dokter, 'id_dokter'),
            ],
            'poli' => ['required', 'string', Rule::in(self::POLI_LIST)],
        ];
    }
}
