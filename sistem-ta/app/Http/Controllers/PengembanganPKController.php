<?php

namespace App\Http\Controllers;

use App\Models\PengembanganPK;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PengembanganPKController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $errorMessage = session('error');
            $successMessage = session('success');
            $pengembangan = PengembanganPK::where('user_id', Auth::id())->paginate(10);
            return view('pengembanganpk.pengembanganpk', [
                'pengembangan' => $pengembangan,
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
                    'matkul_pengembangan' => 'required|string',
                    'deskripsi_pengembangan' => 'required|string',
                    'path_lampiran' => 'required|max:10000|mimes:pdf,jpg,png,doc,docx',
                ]);

                // Upload file
                $pathLampiran = $request->file('path_lampiran')->store('public');

                // Create post
                PengembanganPK::create([
                    'matkul_pengembangan' => $request->matkul_pengembangan,
                    'deskripsi_pengembangan' => $request->deskripsi_pengembangan,
                    'hasil_pengembangan' => $pathLampiran,
                    'user_id' => Auth::id(),
                ]);

                // Redirect to index
                return redirect('/pengembangan-pk')->with('success', 'Data Berhasil Disimpan!');
            }
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
