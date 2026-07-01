import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function SuratDetailPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [data, setData] = useState(null)
  const [alasanTolak, setAlasanTolak] = useState('')
  const [modalAction, setModalAction] = useState('')

  useEffect(() => {
    api.get(`/surat/${id}`).then((res) => setData(res.data.data))
  }, [id])

  if (!data) return <div className="py-12 text-center text-gray-400">Memuat...</div>

  const handleSetujui = async () => {
    await api.post(`/surat/${data.id}/setujui`)
    setModalAction('')
    const res = await api.get(`/surat/${id}`)
    setData(res.data.data)
  }

  const handleTerbitkan = async () => {
    await api.post(`/surat/${data.id}/terbitkan`)
    setModalAction('')
    const res = await api.get(`/surat/${id}`)
    setData(res.data.data)
  }

  const handleTolak = async () => {
    await api.post(`/surat/${data.id}/tolak`, { alasan_penolakan: alasanTolak })
    setAlasanTolak('')
    setModalAction('')
    const res = await api.get(`/surat/${id}`)
    setData(res.data.data)
  }

  return (
    <div className="mx-auto max-w-3xl">
      <Button variant="ghost" className="mb-4" onClick={() => navigate('/surat')}>← Kembali</Button>

      <div className="rounded-lg border bg-white p-6 shadow-sm">
        <div className="flex items-center justify-between">
          <h1 className="text-xl font-bold text-gray-900">{data.jenis_surat}</h1>
          <span className={`rounded-full px-3 py-1 text-sm font-medium ${
            data.status === 'pending' ? 'bg-blue-100 text-blue-700' :
            data.status === 'disetujui' ? 'bg-yellow-100 text-yellow-700' :
            data.status === 'diterbitkan' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
          }`}>{data.status}</span>
        </div>

        {data.nomor_surat && (
          <p className="mt-2 text-sm font-medium text-gray-700">Nomor: {data.nomor_surat}</p>
        )}

        <p className="mt-1 text-sm text-gray-500">
          Pemohon: {data.warga?.nama} (NIK: {data.warga?.nik})
        </p>

        <div className="mt-4 space-y-2">
          {Object.entries(data.data_form || {}).map(([key, val]) => (
            <div key={key} className="flex gap-2 text-sm">
              <span className="w-32 font-medium text-gray-600 capitalize">{key.replace('_', ' ')}:</span>
              <span className="text-gray-800">{String(val)}</span>
            </div>
          ))}
        </div>

        {data.dokumen_pendukung?.length > 0 && (
          <div className="mt-4">
            <h3 className="text-sm font-medium text-gray-700">Dokumen Pendukung:</h3>
            <div className="mt-1 flex gap-2">
              {data.dokumen_pendukung.map((f, i) => (
                <a
                  key={i}
                  href={`http://localhost:8000/storage/${f}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-sm text-blue-600 underline"
                >
                  Dokumen {i + 1}
                </a>
              ))}
            </div>
          </div>
        )}

        {data.alasan_penolakan && (
          <div className="mt-4 rounded-lg bg-red-50 p-4">
            <p className="text-sm font-medium text-red-800">Alasan Penolakan:</p>
            <p className="mt-1 text-sm text-red-700">{data.alasan_penolakan}</p>
          </div>
        )}

        {data.file_pdf_path && (
          <div className="mt-4">
            <iframe
              src={`http://localhost:8000/storage/${data.file_pdf_path}`}
              className="h-96 w-full rounded border"
            />
            <Button
              variant="outline"
              className="mt-2"
              onClick={() => window.open(`http://localhost:8000/api/v1/surat/${data.id}/download`)}
            >
              Download PDF
            </Button>
          </div>
        )}
      </div>

      {data.status === 'pending' && (
        <div className="mt-4 flex gap-2">
          <Button onClick={() => setModalAction('setujui')}>Setujui</Button>
          <Button variant="outline" className="text-red-600" onClick={() => setModalAction('tolak')}>Tolak</Button>
        </div>
      )}

      {data.status === 'disetujui' && (
        <div className="mt-4">
          <Button onClick={() => setModalAction('terbitkan')}>Terbitkan & Generate PDF</Button>
        </div>
      )}

      <ConfirmModal
        open={modalAction === 'setujui'}
        title="Setujui Surat"
        message="Setujui pengajuan surat ini?"
        onConfirm={handleSetujui}
        onCancel={() => setModalAction('')}
      />

      <ConfirmModal
        open={modalAction === 'terbitkan'}
        title="Terbitkan Surat"
        message="Terbitkan surat dan generate PDF?"
        onConfirm={handleTerbitkan}
        onCancel={() => setModalAction('')}
      />

      <ConfirmModal
        open={modalAction === 'tolak'}
        title="Tolak Surat"
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
