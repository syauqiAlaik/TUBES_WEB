<?php

namespace App\Http\Controllers;

use App\Models\Penguji;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PengujiController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $errorMessage = session('error');
            $successMessage = session('success');
            $penguji = Penguji::where('user_id', Auth::id())->paginate(10);
            return view('penguji.penguji', [
                'penguji' => $penguji,
                'errorMessage' => $errorMessage,
                'successMessage' => $successMessage,
            ]);
        } else {
            return redirect('/login');
        }
    }
    public function store(Request $request): RedirectResponse
    {
        try {
            if (Auth::check()) {
                $this->validate($request, [
                    'mahasiswa_nim' => 'required|string',
                    'mahasiswa_nama' => 'required|string',
                    'jenis_ujian_akhir' => 'required|string',
                    'posisi_penguji' => 'required|string',
                    'tahun_ajaran' => 'required|min:0',
                    'waktu' => 'required|date',
                    'no_sk' => 'required|min:0',
                    'path_foto_kegiatan' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                    'path_lampiran' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                ]);

                // Convert waktu to Carbon instance
                $waktu = Carbon::createFromFormat('d-m-Y', $request->waktu)->format('Y-m-d');

                // Upload files
                $pathLampiran = $request->file('path_lampiran')->store('public');
                $pathFotoKegiatan = $request->file('path_foto_kegiatan')->store('public');

                // Create penguji
                Penguji::create([
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'mahasiswa_nama' => $request->mahasiswa_nama,
                    'jenis_ujian_akhir' => $request->jenis_ujian_akhir,
                    'tahun_ajaran' => $request->tahun_ajaran,
                    'posisi_penguji' => $request->posisi_penguji,
                    'waktu' => $waktu,
                    'no_sk' => $request->no_sk,
                    'path_lampiran' => $pathLampiran,
                    'path_foto_kegiatan' => $pathFotoKegiatan,
                    'user_id' => Auth::id(),
                ]);

                // Redirect to index
                return redirect('/penguji')->with('success', 'Data Berhasil Disimpan!');
            }
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
