import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function WargaListPage() {
  const navigate = useNavigate()
  const [data, setData] = useState({ data: [], current_page: 1, last_page: 1, total: 0 })
  const [rtRwList, setRtRwList] = useState([])
  const [search, setSearch] = useState('')
  const [filterRt, setFilterRt] = useState('')
  const [page, setPage] = useState(1)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [importOpen, setImportOpen] = useState(false)
  const [importFile, setImportFile] = useState(null)
  const [importResult, setImportResult] = useState(null)
  const [importLoading, setImportLoading] = useState(false)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const fetchData = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const params = { page, per_page: 15 }
      if (search) params.search = search
      if (filterRt) params.rt_rw_id = filterRt
      const [res, rtRes] = await Promise.all([
        api.get('/warga', { params }),
        api.get('/rt-rw'),
      ])
      setData(res.data.data)
      setRtRwList(rtRes.data.data)
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal memuat data warga')
    } finally {
      setLoading(false)
    }
  }, [page, search, filterRt])

  useEffect(() => { fetchData() }, [fetchData])

  const handleDelete = async () => {
    if (!deleteTarget) return
    try {
      await api.delete(`/warga/${deleteTarget.id}`)
      setDeleteTarget(null)
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menghapus')
    }
  }

  const handleExport = async () => {
    try {
      const params = {}
      if (filterRt) params.rt_rw_id = filterRt
      const res = await api.get('/warga/export', { params, responseType: 'blob' })
      const url = window.URL.createObjectURL(new Blob([res.data]))
      const a = document.createElement('a')
      a.href = url
      a.download = 'data-warga.xlsx'
      a.click()
    } catch (err) {
      alert('Gagal export')
    }
  }

  const handleImport = async () => {
    if (!importFile) return
    setImportLoading(true)
    const formData = new FormData()
    formData.append('file', importFile)
    try {
      const res = await api.post('/warga/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      setImportResult(res.data.data)
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal import')
    }
    setImportLoading(false)
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Data Warga</h1>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => setImportOpen(true)}>Import Excel</Button>
          <Button variant="outline" onClick={handleExport}>Export Excel</Button>
          <Button onClick={() => navigate('/warga/tambah')}>Tambah Warga</Button>
        </div>
      </div>

      <div className="mt-4 flex gap-3">
        <div className="w-64">
          <Input placeholder="Cari nama atau NIK..." value={search} onChange={(e) => { setSearch(e.target.value); setPage(1) }} />
        </div>
        <select
          className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm"
          value={filterRt}
          onChange={(e) => { setFilterRt(e.target.value); setPage(1) }}
        >
          <option value="">Semua RT/RW</option>
          {rtRwList.map((rt) => (
            <option key={rt.id} value={rt.id}>RT {rt.nomor_rt}/RW {rt.nomor_rw}</option>
          ))}
        </select>
      </div>

      {loading ? (
        <div className="mt-4 flex items-center justify-center py-16">
          <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
        </div>
      ) : error ? (
        <div className="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-center text-red-600">
          {error}
        </div>
      ) : (
        <div className="mt-4 overflow-x-auto rounded-lg border bg-white shadow-sm">
          <table className="w-full text-sm">
            <thead className="border-b bg-gray-50 text-left">
              <tr>
                <th className="px-4 py-3 font-medium">NIK</th>
                <th className="px-4 py-3 font-medium">Nama</th>
                <th className="px-4 py-3 font-medium">RT/RW</th>
                <th className="px-4 py-3 font-medium">No. Telp</th>
                <th className="px-4 py-3 font-medium">Status</th>
                <th className="px-4 py-3 font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {data.data.map((w) => (
                <tr key={w.id} className="border-b last:border-0 hover:bg-gray-50">
                  <td className="px-4 py-3">{w.nik}</td>
                  <td className="px-4 py-3 font-medium">{w.nama}</td>
                  <td className="px-4 py-3">{w.rt_rw ? `RT ${w.rt_rw.nomor_rt}/RW ${w.rt_rw.nomor_rw}` : '-'}</td>
                  <td className="px-4 py-3">{w.no_telp || '-'}</td>
                  <td className="px-4 py-3">
                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                      w.status_verifikasi === 'terverifikasi' ? 'bg-green-100 text-green-700' :
                      w.status_verifikasi === 'ditolak' ? 'bg-red-100 text-red-700' :
                      'bg-yellow-100 text-yellow-700'
                    }`}>
                      {w.status_verifikasi}
                    </span>
                  </td>
                  <td className="flex gap-2 px-4 py-3">
                    <Button variant="outline" size="sm" onClick={() => navigate(`/warga/${w.id}`)}>Detail</Button>
                    <Button variant="outline" size="sm" onClick={() => navigate(`/warga/${w.id}/edit`)}>Edit</Button>
                    <Button variant="ghost" size="sm" className="text-red-600" onClick={() => setDeleteTarget(w)}>Hapus</Button>
                  </td>
                </tr>
              ))}
              {data.data.length === 0 && (
                <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">Belum ada data warga</td></tr>
              )}
            </tbody>
          </table>
        </div>
      )}

      {!loading && !error && data.last_page > 1 && (
        <div className="mt-4 flex items-center justify-center gap-2">
          {Array.from({ length: data.last_page }, (_, i) => i + 1).map((p) => (
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

      <ConfirmModal
        open={!!deleteTarget}
        title="Hapus Warga"
        message={`Yakin ingin menghapus ${deleteTarget?.nama}? Data user terkait juga akan dihapus.`}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
      />

      {importOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={() => { setImportOpen(false); setImportResult(null) }}>
          <div className="w-full max-w-lg rounded-lg bg-white p-6 shadow-lg" onClick={(e) => e.stopPropagation()}>
            <h2 className="text-lg font-semibold">Import Excel Warga</h2>
            <p className="mt-1 text-xs text-gray-500">Format: NIK, Nama, Alamat, No. Telp, RT/RW ID (header baris pertama)</p>
            <input type="file" accept=".xlsx,.xls,.csv" className="mt-4 block w-full text-sm" onChange={(e) => setImportFile(e.target.files[0])} />
            <div className="mt-4 flex justify-end gap-2">
              <Button variant="outline" onClick={() => { setImportOpen(false); setImportResult(null) }}>Tutup</Button>
              <Button onClick={handleImport} disabled={!importFile || importLoading}>
                {importLoading ? 'Memproses...' : 'Import'}
              </Button>
            </div>
            {importResult && (
              <div className="mt-4 rounded-lg border p-3 text-sm">
                <p className="font-medium text-green-700">Berhasil: {importResult.success_count}</p>
                {importResult.failed_rows?.length > 0 && (
                  <div className="mt-2">
                    <p className="font-medium text-red-600">Gagal: {importResult.failed_rows.length}</p>
                    {importResult.failed_rows.slice(0, 5).map((r, i) => (
                      <p key={i} className="text-xs text-red-500">Baris {r.row}: {r.error}</p>
                    ))}
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  )
}
