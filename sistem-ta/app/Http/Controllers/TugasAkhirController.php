<?php

namespace App\Http\Controllers;


use App\Models\TugasAkhir;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class TugasAkhirController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $errorMessage = session('error');
            $successMessage = session('success');
            $tugasakhir = TugasAkhir::where('user_id', Auth::id())->paginate(10);
            return view('tugasakhir.tugasakhir', [
                'tugasakhir' => $tugasakhir,
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
                    'jenis_bimbingan' => 'required|string',
                    'tahun_ajaran' => 'required|string',
                    'tanggal_dimulai' => 'required|date',
                    'tanggal_berakhir' => 'required|date',
                    'jenis_pembimbing' => 'required|string',
                    'no_sk' => 'required|min:0',
                    'path_foto_kegiatan' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                    'path_lampiran' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                ]);

                // Convert tanggal_dimulai and tanggal_berakhir to Carbon instances
                $tanggalDimulai = Carbon::createFromFormat('d-m-Y', $request->tanggal_dimulai)->format('Y-m-d');
                $tanggalBerakhir = Carbon::createFromFormat('d-m-Y', $request->tanggal_berakhir)->format('Y-m-d');

                // Upload files
                $pathLampiran = $request->file('path_lampiran')->store('public');
                $pathFotoKegiatan = $request->file('path_foto_kegiatan')->store('public');

                // Create tugas akhir
                TugasAkhir::create([
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'mahasiswa_nama' => $request->mahasiswa_nama,
                    'jenis_bimbingan' => $request->jenis_bimbingan,
                    'tahun_ajaran' => $request->tahun_ajaran,
                    'tanggal_dimulai' => $tanggalDimulai,
                    'tanggal_berakhir' => $tanggalBerakhir,
                    'jenis_pembimbing' => $request->jenis_pembimbing,
                    'no_sk' => $request->no_sk,
                    'path_lampiran' => $pathLampiran,
                    'path_foto_kegiatan' => $pathFotoKegiatan,
                    'user_id' => Auth::id(),
                ]);

                // Redirect to index
                return redirect('/tugas-akhir')->with('success', 'Data berhasil disimpan!');
            }
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
