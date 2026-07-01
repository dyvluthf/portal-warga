import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'

const statusColors = {
  pending: 'bg-blue-100 text-blue-700',
  disetujui: 'bg-yellow-100 text-yellow-700',
  diterbitkan: 'bg-green-100 text-green-700',
  ditolak: 'bg-red-100 text-red-700',
}

export default function SuratListPage() {
  const navigate = useNavigate()
  const [list, setList] = useState([])
  const [status, setStatus] = useState('')
  const [page, setPage] = useState(1)
  const [meta, setMeta] = useState({})

  const fetchData = useCallback(async () => {
    const params = { page, per_page: 15 }
    if (status) params.status = status
    const res = await api.get('/surat', { params })
    setList(res.data.data.data)
    setMeta(res.data.data)
  }, [page, status])

  useEffect(() => { fetchData() }, [fetchData])

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Surat</h1>
      </div>
      <div className="mt-4">
        <select
          className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm"
          value={status}
          onChange={(e) => { setStatus(e.target.value); setPage(1) }}
        >
          <option value="">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="disetujui">Disetujui</option>
          <option value="diterbitkan">Diterbitkan</option>
          <option value="ditolak">Ditolak</option>
        </select>
      </div>
      <div className="mt-4 space-y-3">
        {list.map((item) => (
          <div
            key={item.id}
            className="flex cursor-pointer items-start justify-between rounded-lg border bg-white p-4 shadow-sm transition-colors hover:bg-gray-50"
            onClick={() => navigate(`/surat/${item.id}`)}
          >
            <div className="flex-1">
              <div className="flex items-center gap-2">
                <span className="font-semibold text-gray-900">{item.jenis_surat}</span>
                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[item.status]}`}>
                  {item.status}
                </span>
              </div>
              {item.nomor_surat && (
                <p className="mt-1 text-xs text-gray-500">No: {item.nomor_surat}</p>
              )}
              <p className="mt-1 text-xs text-gray-400">{item.warga?.nama} &middot; {new Date(item.created_at).toLocaleDateString('id-ID')}</p>
            </div>
          </div>
        ))}
        {list.length === 0 && (
          <div className="py-12 text-center text-gray-400">Belum ada pengajuan surat</div>
        )}
      </div>
      {meta?.last_page > 1 && (
        <div className="mt-4 flex items-center justify-center gap-2">
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
            <button
              key={p}
              className={`rounded px-3 py-1 text-sm ${page === p ? 'bg-primary text-white' : 'bg-gray-100 hover:bg-gray-200'}`}
              onClick={() => setPage(p)}
            >
              {p}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
