import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'

export default function WargaDetailPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [data, setData] = useState(null)
  const [tab, setTab] = useState('surat')

  useEffect(() => {
    api.get(`/warga/${id}`).then((res) => setData(res.data.data))
  }, [id])

  if (!data) return <div className="text-gray-400">Memuat...</div>

  const w = data.warga

  return (
    <div className="mx-auto max-w-2xl">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Detail Warga</h1>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => navigate(`/warga/${id}/edit`)}>Edit</Button>
          <Button variant="outline" onClick={() => navigate('/warga')}>Kembali</Button>
        </div>
      </div>

      <div className="mt-6 rounded-lg border bg-white p-6 shadow-sm">
        <div className="flex items-center gap-4">
          <div className="flex h-16 w-16 items-center justify-center rounded-full bg-primary text-xl font-bold text-white">
            {w.nama?.[0]?.toUpperCase()}
          </div>
          <div>
            <h2 className="text-xl font-semibold">{w.nama}</h2>
            <p className="text-sm text-gray-500">NIK: {w.nik}</p>
          </div>
        </div>
        <div className="mt-4 grid gap-3 text-sm md:grid-cols-2">
          <div><span className="font-medium">Alamat:</span> {w.alamat || '-'}</div>
          <div><span className="font-medium">RT/RW:</span> {w.rt_rw ? `RT ${w.rt_rw.nomor_rt}/RW ${w.rt_rw.nomor_rw}` : '-'}</div>
          <div><span className="font-medium">No. Telepon:</span> {w.no_telp || '-'}</div>
          <div><span className="font-medium">Status:</span> {w.status_verifikasi}</div>
        </div>
      </div>

      <div className="mt-4">
        <div className="flex gap-4 border-b">
          <button
            className={`pb-2 text-sm font-medium ${tab === 'surat' ? 'border-b-2 border-primary text-primary' : 'text-gray-500'}`}
            onClick={() => setTab('surat')}
          >
            Riwayat Surat
          </button>
          <button
            className={`pb-2 text-sm font-medium ${tab === 'iuran' ? 'border-b-2 border-primary text-primary' : 'text-gray-500'}`}
            onClick={() => setTab('iuran')}
          >
            Riwayat Iuran
          </button>
        </div>
        <div className="mt-4 rounded-lg border bg-white p-6 text-sm text-gray-400">
          {tab === 'surat' ? 'Belum ada riwayat surat.' : 'Belum ada riwayat iuran.'}
        </div>
      </div>
    </div>
  )
}
