<?php

namespace App\Http\Controllers\App\RegisSemi;

use App\Http\Controllers\Controller;
use App\Models\BookSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegisSemiController extends Controller
{
    public function index()
    {
        $submissions = BookSubmission::with('user')
            ->where('status', '!=', 'DRAFT')
            ->orderBy('created_at', 'desc')
            ->get();

        $mappedData = $submissions->map(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->title,
                'nama_dosen' => $item->user->name ?? 'Unknown User',
                'tanggal_pengajuan' => $item->created_at->format('d M Y'),
                'status' => $item->status,
                'status_label' => $this->formatStatusLabel($item->status),
            ];
        });

        return Inertia::render('app/RegisSemi/Index', [
            'pageName' => 'Daftar Penghargaan Masuk',
            'submissions' => $mappedData,
        ]);
    }

    public function indexx()
    {
        $submissions = BookSubmission::with('user')
            ->where('status', '!=', 'DRAFT')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'judul' => $item->title,
                    'nama_dosen' => $item->user->name ?? 'Unknown User',
                    'tanggal_pengajuan' => $item->created_at->format('d M Y'),
                    'status_label' => $this->formatStatusLabel($item->status),
                ];
            });

        return Inertia::render('app/RegisSemi/Indexx', [
            'pageName' => 'Penghargaan Buku Masuk (Lppm Staff)',
            'submissions' => $submissions,
        ]);
    }

    public function show($id)
    {
        $book = BookSubmission::with(['authors', 'user'])->findOrFail($id);

        return Inertia::render('app/RegisSemi/Detail', [
            'pageName' => 'Detail Verifikasi Buku',
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'publisher' => $book->publisher,
                'drive_link' => json_decode($book->drive_link),
                'status_label' => $book->status,
                'dosen' => $book->user->name ?? 'Dosen Tidak Ditemukan',
            ],
        ]);
    }

    // === NEW METHOD UNTUK DETAiLL.JSX ===
    public function showL($id)
    {
        $book = BookSubmission::with(['authors', 'user'])->findOrFail($id);

        return Inertia::render('app/RegisSemi/Detaill', [
            'pageName' => 'Detail Lppm Staff',
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'publisher' => $book->publisher,
                'drive_link' => json_decode($book->drive_link),
                'status_label' => $book->status,
                'dosen' => $book->user->name ?? 'Dosen Tidak Ditemukan',
            ],
        ]);
    }

 public function reject(Request $request, $id)
{
    $request->validate([
        'note' => 'required|string|max:500',
    ]);

    $book = BookSubmission::findOrFail($id);
    $book->status = 'REJECTED';
    $book->reject_note = $request->note; // Pastikan kolom reject_note ada di tabel
    $book->save();

    // Redirect ke showL agar render Detaill.jsx dengan flash message
    return redirect()->route('regis-semi.showl', $id)
                     ->with('success', 'Pengajuan berhasil ditolak');
}



    private function formatStatusLabel($status)
    {
        return match ($status) {
            'SUBMITTED' => 'Menunggu Verifikasi',
            'VERIFIED_STAFF' => 'Review Ketua',
            'APPROVED_CHIEF' => 'Disetujui (Ke HRD)',
            'PAID' => 'Selesai (Cair)',
            'REJECTED' => 'Ditolak/Revisi',
            default => $status,
        };
    }
}
