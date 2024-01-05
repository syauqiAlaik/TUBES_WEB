<?php

namespace App\Http\Controllers;

use App\Models\Pendamping;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class PendampingController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $errorMessage = session('error');
            $successMessage = session('success');
            $pendamping = Pendamping::where('user_id', Auth::user()->id)->paginate(10);
            return view('pendamping.pendamping', [
                'pendamping' => $pendamping,
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
                    'jenis_kegiatan' => 'required|min:0',
                    'tanggal_dimulai' => 'required|date_format:d-m-Y',
                    'tanggal_berakhir' => 'required|date_format:d-m-Y',
                    'no_sk' => 'required|min:0',
                    'path_foto_kegiatan' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                    'path_lampiran' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                ]);

                $tanggalDimulai = Carbon::createFromFormat('d-m-Y', $request->tanggal_dimulai)->format('Y-m-d');
                $tanggalBerakhir = Carbon::createFromFormat('d-m-Y', $request->tanggal_berakhir)->format('Y-m-d');

                // Upload image
                $pathLampiran = $request->file('path_lampiran')->store('public');
                $pathFotoKegiatan = $request->file('path_foto_kegiatan')->store('public');

                // Create post
                Pendamping::create([
                    'mahasiswa_nim' => $request->mahasiswa_nim,
                    'mahasiswa_nama' => $request->mahasiswa_nama,
                    'jenis_kegiatan' => $request->jenis_kegiatan,
                    'tanggal_dimulai' => $tanggalDimulai,
                    'tanggal_berakhir' => $tanggalBerakhir,
                    'no_sk' => $request->no_sk,
                    'path_lampiran' => $pathLampiran,
                    'path_foto_kegiatan' => $pathFotoKegiatan,
                    'user_id' => Auth::user()->id,
                ]);

                // Redirect to index
                return redirect('/pendamping')->with('success', 'Data Berhasil Disimpan!');
            }
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
