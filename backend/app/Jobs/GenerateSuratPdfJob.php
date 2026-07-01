<?php

namespace App\Jobs;

use App\Models\Surat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateSuratPdfJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(protected Surat $surat) {}

    public function handle(): void
    {
        $surat = $this->surat->fresh()->load('warga.rtRw');
        $pdf = Pdf::loadView('pdf.surat', ['surat' => $surat]);
        $filename = 'surat/' . $surat->nomor_surat . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());
        $surat->update(['file_pdf_path' => $filename]);
    }
}
