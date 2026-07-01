import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function PengaduanDetailPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [data, setData] = useState(null)
  const [tanggapan, setTanggapan] = useState('')
  const [alasanTolak, setAlasanTolak] = useState('')
  const [modalProses, setModalProses] = useState('')

  useEffect(() => {
    api.get(`/pengaduan/${id}`).then((res) => setData(res.data.data))
  }, [id])

  if (!data) return <div className="py-12 text-center text-gray-400">Memuat...</div>

  const handleProses = async () => {
    await api.post(`/pengaduan/${data.id}/proses`)
    setModalProses('')
    const res = await api.get(`/pengaduan/${id}`)
    setData(res.data.data)
  }

  const handleSelesai = async () => {
    await api.post(`/pengaduan/${data.id}/selesai`, { tanggapan_admin: tanggapan })
    setTanggapan('')
    const res = await api.get(`/pengaduan/${id}`)
    setData(res.data.data)
  }

  const handleTolak = async () => {
    await api.post(`/pengaduan/${data.id}/tolak`, { alasan_penolakan: alasanTolak })
    setAlasanTolak('')
    const res = await api.get(`/pengaduan/${id}`)
    setData(res.data.data)
  }

  return (
    <div className="mx-auto max-w-3xl">
      <Button variant="ghost" className="mb-4" onClick={() => navigate('/pengaduan')}>← Kembali</Button>

      <div className="rounded-lg border bg-white p-6 shadow-sm">
        <div className="flex items-center justify-between">
          <h1 className="text-xl font-bold text-gray-900">{data.kategori}</h1>
          <span className={`rounded-full px-3 py-1 text-sm font-medium ${
            data.status === 'baru' ? 'bg-blue-100 text-blue-700' :
            data.status === 'diproses' ? 'bg-yellow-100 text-yellow-700' :
            data.status === 'selesai' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
          }`}>{data.status}</span>
        </div>

        <p className="mt-1 text-sm text-gray-500">
          Oleh: {data.warga?.nama} &middot; {new Date(data.created_at).toLocaleDateString('id-ID', { dateStyle: 'full' })}
        </p>

        <p className="mt-4 text-gray-700 whitespace-pre-wrap">{data.deskripsi}</p>

        {data.foto?.length > 0 && (
          <div className="mt-4 grid grid-cols-3 gap-2">
            {data.foto.map((f, i) => (
              <img key={i} src={`http://localhost:8000/storage/${f}`} alt="" className="h-32 w-full rounded object-cover" />
            ))}
          </div>
        )}

        {data.lokasi_lat && data.lokasi_lng && (
          <div className="mt-4">
            <iframe
              title="Lokasi"
              className="h-48 w-full rounded border"
              src={`https://www.google.com/maps/embed/v1/place?key=&q=${data.lokasi_lat},${data.lokasi_lng}&center=${data.lokasi_lat},${data.lokasi_lng}&zoom=15`}
            />
          </div>
        )}

        {data.lokasi_manual && (
          <p className="mt-2 text-sm text-gray-500">Lokasi: {data.lokasi_manual}</p>
        )}

        {data.tanggapan_admin && (
          <div className="mt-4 rounded-lg bg-blue-50 p-4">
            <p className="text-sm font-medium text-blue-800">Tanggapan Admin:</p>
            <p className="mt-1 text-sm text-blue-700">{data.tanggapan_admin}</p>
          </div>
        )}

        {data.alasan_penolakan && (
          <div className="mt-4 rounded-lg bg-red-50 p-4">
            <p className="text-sm font-medium text-red-800">Alasan Penolakan:</p>
            <p className="mt-1 text-sm text-red-700">{data.alasan_penolakan}</p>
          </div>
        )}

        {data.rating && (
          <div className="mt-4 rounded-lg bg-green-50 p-4">
            <p className="text-sm font-medium text-green-800">Rating: {'★'.repeat(data.rating)}{'☆'.repeat(5 - data.rating)}</p>
            {data.ulasan && <p className="mt-1 text-sm text-green-700">{data.ulasan}</p>}
          </div>
        )}
      </div>

      <div className="mt-4 rounded-lg border bg-white p-6 shadow-sm">
        <h2 className="font-semibold text-gray-900">Timeline</h2>
        <div className="mt-4 space-y-4">
          {data.timeline?.map((t, i) => (
            <div key={i} className="flex gap-3">
              <div className="flex flex-col items-center">
                <div className="h-3 w-3 rounded-full bg-primary" />
                {i < data.timeline.length - 1 && <div className="h-full w-0.5 bg-gray-200" />}
              </div>
              <div className="flex-1 pb-4">
                <p className="text-sm font-medium capitalize text-gray-900">{t.status}</p>
                {t.keterangan && <p className="text-xs text-gray-500">{t.keterangan}</p>}
                <p className="text-xs text-gray-400">{new Date(t.created_at).toLocaleString('id-ID')}</p>
              </div>
            </div>
          ))}
        </div>
      </div>

      {data.status === 'baru' && (
        <div className="mt-4 flex gap-2">
          <Button onClick={() => setModalProses('proses')}>Proses</Button>
          <Button variant="outline" className="text-red-600" onClick={() => setModalProses('tolak')}>Tolak</Button>
        </div>
      )}

      {data.status === 'diproses' && (
        <div className="mt-4 space-y-3 rounded-lg border bg-white p-4">
          <h3 className="font-semibold text-gray-900">Selesaikan Pengaduan</h3>
          <textarea
            className="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm"
            rows={4}
            placeholder="Tanggapan admin..."
            value={tanggapan}
            onChange={(e) => setTanggapan(e.target.value)}
          />
          <div className="flex gap-2">
            <Button onClick={handleSelesai} disabled={!tanggapan.trim()}>Selesaikan</Button>
            <Button variant="outline" className="text-red-600" onClick={() => setModalProses('tolak')}>Tolak</Button>
          </div>
        </div>
      )}

      <ConfirmModal
        open={modalProses === 'proses'}
        title="Proses Pengaduan"
        message="Tandai pengaduan ini sebagai sedang diproses?"
        onConfirm={handleProses}
        onCancel={() => setModalProses('')}
      />

      <ConfirmModal
        open={modalProses === 'tolak'}
        title="Tolak Pengaduan"
        message={
          <div>
            <p className="text-sm text-gray-600">Alasan penolakan:</p>
            <textarea
              className="mt-2 flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm"
              rows={3}
              value={alasanTolak}
              onChange={(e) => setAlasanTolak(e.target.value)}
              placeholder="Alasan penolakan..."
            />
          </div>
        }
        onConfirm={handleTolak}
        onCancel={() => setAlasanTolak('')}
        confirmText="Tolak"
      />
    </div>
  )
}
