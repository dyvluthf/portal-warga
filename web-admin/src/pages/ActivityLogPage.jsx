import { useState, useEffect, useCallback } from 'react'
import api from '@/services/api'

export default function ActivityLogPage() {
  const [list, setList] = useState([])
  const [page, setPage] = useState(1)
  const [meta, setMeta] = useState({})
  const [filterUser, setFilterUser] = useState('')
  const [filterModel, setFilterModel] = useState('')
  const [filterAksi, setFilterAksi] = useState('')
  const [tglMulai, setTglMulai] = useState('')
  const [tglSelesai, setTglSelesai] = useState('')

  const fetchData = useCallback(async () => {
    const params = { page, per_page: 20 }
    if (filterUser) params.user_id = filterUser
    if (filterModel) params.model = filterModel
    if (filterAksi) params.aksi = filterAksi
    if (tglMulai) params.tanggal_mulai = tglMulai
    if (tglSelesai) params.tanggal_selesai = tglSelesai
    const res = await api.get('/admin/activity-logs', { params })
    setList(res.data.data.data)
    setMeta(res.data.data)
  }, [page, filterUser, filterModel, filterAksi, tglMulai, tglSelesai])

  useEffect(() => { fetchData() }, [fetchData])

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900">Activity Log</h1>

      <div className="mt-4 flex flex-wrap gap-2">
        <input type="text" className="h-9 w-40 rounded-md border border-gray-300 bg-white px-3 text-sm" placeholder="User ID" value={filterUser} onChange={(e) => { setFilterUser(e.target.value); setPage(1) }} />
        <input type="text" className="h-9 w-40 rounded-md border border-gray-300 bg-white px-3 text-sm" placeholder="Model" value={filterModel} onChange={(e) => { setFilterModel(e.target.value); setPage(1) }} />
        <input type="text" className="h-9 w-40 rounded-md border border-gray-300 bg-white px-3 text-sm" placeholder="Aksi" value={filterAksi} onChange={(e) => { setFilterAksi(e.target.value); setPage(1) }} />
        <input type="date" className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm" value={tglMulai} onChange={(e) => { setTglMulai(e.target.value); setPage(1) }} />
        <input type="date" className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm" value={tglSelesai} onChange={(e) => { setTglSelesai(e.target.value); setPage(1) }} />
      </div>

      <div className="mt-4 overflow-x-auto rounded-lg border bg-white shadow-sm">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b bg-gray-50 text-left">
              <th className="px-4 py-3 font-medium text-gray-600">Waktu</th>
              <th className="px-4 py-3 font-medium text-gray-600">User</th>
              <th className="px-4 py-3 font-medium text-gray-600">Aksi</th>
              <th className="px-4 py-3 font-medium text-gray-600">Model</th>
              <th className="px-4 py-3 font-medium text-gray-600">Model ID</th>
              <th className="px-4 py-3 font-medium text-gray-600">Keterangan</th>
            </tr>
          </thead>
          <tbody>
            {list.map((log) => (
              <tr key={log.id} className="border-b hover:bg-gray-50">
                <td className="whitespace-nowrap px-4 py-3 text-xs text-gray-500">{new Date(log.created_at).toLocaleString('id-ID')}</td>
                <td className="px-4 py-3 text-sm">{log.user?.name || '-'}</td>
                <td className="px-4 py-3"><span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium">{log.aksi}</span></td>
                <td className="px-4 py-3 text-sm text-gray-600">{log.model}</td>
                <td className="px-4 py-3 text-xs text-gray-400">{log.model_id}</td>
                <td className="px-4 py-3 text-sm text-gray-700">{log.keterangan}</td>
              </tr>
            ))}
            {list.length === 0 && <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">Belum ada log</td></tr>}
          </tbody>
        </table>
      </div>

      {meta?.last_page > 1 && (
        <div className="mt-4 flex items-center justify-center gap-2">
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
            <button key={p} className={`rounded px-3 py-1 text-sm ${page === p ? 'bg-primary text-white' : 'bg-gray-100 hover:bg-gray-200'}`} onClick={() => setPage(p)}>{p}</button>
          ))}
        </div>
      )}
    </div>
  )
}
