<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat {{ $surat->jenis_surat }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5; padding: 40px; }
        .kop { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #000; padding-bottom: 10px; }
        .kop h1 { margin: 0; font-size: 16pt; }
        .kop p { margin: 2px 0; font-size: 11pt; }
        .title { text-align: center; text-decoration: underline; font-weight: bold; font-size: 14pt; margin: 20px 0; }
        .content { text-align: justify; }
        .meta { margin-bottom: 16px; }
        .meta td { padding: 2px 8px; }
        .signature { margin-top: 40px; text-align: right; }
        .signature-line { margin-top: 80px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10pt; }
    </style>
</head>
<body>
    @php $rt = $surat->warga->rtRw; @endphp
    <div class="kop">
        <h1>PEMERINTAHAN RT {{ $rt->nomor_rt ?? '...' }} / RW {{ $rt->nomor_rw ?? '...' }}</h1>
        <p>{{ $rt->nama_kelurahan ?? 'Kelurahan' }}, {{ $rt->kecamatan ?? 'Kecamatan' }}</p>
        <p>Alamat: {{ $rt->nama_kelurahan ?? '...' }}</p>
    </div>

    <div class="title">SURAT {{ strtoupper($surat->jenis_surat) }}</div>
    <p style="text-align:center;">Nomor: {{ $surat->nomor_surat }}</p>

    <div class="content">
        <table class="meta">
            <tr><td>Yang bertanda tangan di bawah ini:</td></tr>
            <tr><td>Nama</td><td>: {{ $rt->ketuaRt?->nama ?? '___________' }}</td></tr>
            <tr><td>Jabatan</td><td>: Ketua RT {{ $rt->nomor_rt }}</td></tr>
        </table>

        <p>Dengan ini menerangkan bahwa:</p>
        <table class="meta">
            <tr><td>Nama</td><td>: {{ $surat->warga->nama }}</td></tr>
            <tr><td>NIK</td><td>: {{ $surat->warga->nik }}</td></tr>
            <tr><td>Tempat, Tgl Lahir</td><td>: {{ $surat->data_form['tempat_lahir'] ?? '-' }}, {{ $surat->data_form['tanggal_lahir'] ?? '-' }}</td></tr>
            <tr><td>Jenis Kelamin</td><td>: {{ $surat->data_form['jenis_kelamin'] ?? '-' }}</td></tr>
            <tr><td>Alamat</td><td>: {{ $surat->warga->alamat }}</td></tr>
        </table>

        <p>Adalah warga RT {{ $rt->nomor_rt ?? '...' }}/RW {{ $rt->nomor_rw ?? '...' }} yang {{ $surat->data_form['keterangan'] ?? 'tersebut namanya di atas' }}.</p>
        <p>Surat ini dibuat untuk keperluan {{ $surat->jenis_surat }} dan dapat digunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature">
        <p>{{ $rt->nama_kelurahan ?? '___________' }}, {{ now()->isoFormat('D MMMM Y') }}</p>
        <p>Ketua RT {{ $rt->nomor_rt }}</p>
        <div class="signature-line"></div>
        <p><strong>{{ $rt->ketuaRt?->nama ?? '___________' }}</strong></p>
    </div>

    <div class="footer">
        <p>Dokumen ini diterbitkan secara elektronik. Berbasis Portal Warga.</p>
    </div>
</body>
</html>
