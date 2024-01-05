<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PembimbingController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $errorMessage = session('error');
            $successMessage = session('success');
            $pembimbing = Pembimbing::where('user_id', Auth::user()->id)->paginate(10);
            return view('pembimbing.pembimbing', [
                'pembimbing' => $pembimbing,
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
                    'nama_kegiatan' => 'required|string',
                    'tahun_ajaran' => 'required|min:0',
                    'waktu' => 'required|date_format:d-m-Y',
                    'no_sk' => 'required|min:0',
                    'path_foto_kegiatan' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                    'path_lampiran' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                ]);

                $waktu = Carbon::createFromFormat('d-m-Y', $request->waktu)->format('Y-m-d');

                // Upload image
                $pathLampiran = $request->file('path_lampiran')->store('public');
                $pathFotoKegiatan = $request->file('path_foto_kegiatan')->store('public');

                // Create post
                Pembimbing::create([
                    'nama_kegiatan' => $request->nama_kegiatan,
                    'tahun_ajaran' => $request->tahun_ajaran,
                    'waktu' => $waktu,
                    'no_sk' => $request->no_sk,
                    'path_lampiran' => $pathLampiran,
                    'path_foto_kegiatan' => $pathFotoKegiatan,
                    'user_id' => Auth::user()->id,
                ]);

                // Redirect to index
                return redirect('/pembimbing')->with('success', 'Data Berhasil Disimpan!');
            }
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
