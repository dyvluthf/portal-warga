import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function BeritaListPage() {
  const navigate = useNavigate()
  const [list, setList] = useState([])
  const [kategori, setKategori] = useState('')
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [page, setPage] = useState(1)
  const [meta, setMeta] = useState({})

  const fetchData = useCallback(async () => {
    const params = { page, per_page: 12 }
    if (kategori) params.kategori = kategori
    const res = await api.get('/berita', { params })
    setList(res.data.data.data)
    setMeta(res.data.data)
  }, [page, kategori])

  useEffect(() => { fetchData() }, [fetchData])

  const handleDelete = async () => {
    if (!deleteTarget) return
    try {
      await api.delete(`/berita/${deleteTarget.id}`)
      setDeleteTarget(null)
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menghapus')
    }
  }

  const toggleStatus = async (item) => {
    try {
      if (item.status === 'draft') {
        await api.post(`/berita/${item.id}/publish`)
      } else {
        await api.post(`/berita/${item.id}/draft`)
      }
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal mengubah status')
    }
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Berita & Pengumuman</h1>
        <Button onClick={() => navigate('/berita/tambah')}>Tulis Berita</Button>
      </div>

      <div className="mt-4">
        <select
          className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm"
          value={kategori}
          onChange={(e) => { setKategori(e.target.value); setPage(1) }}
        >
          <option value="">Semua Kategori</option>
          <option value="Informasi">Informasi</option>
          <option value="Kegiatan">Kegiatan</option>
          <option value="Pengumuman">Pengumuman</option>
        </select>
      </div>

      <div className="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {list.map((item) => (
          <div key={item.id} className="rounded-lg border bg-white shadow-sm">
            {item.gambar && (
              <img src={`http://localhost:8000/storage/${item.gambar}`} alt="" className="h-40 w-full rounded-t-lg object-cover" />
            )}
            <div className="p-4">
              <div className="flex items-center justify-between">
                <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium">{item.kategori}</span>
                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                  item.status === 'publish' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'
                }`}>
                  {item.status}
                </span>
              </div>
              <h3 className="mt-2 font-semibold text-gray-900">{item.judul}</h3>
              <p className="mt-1 text-xs text-gray-500">
                {item.tanggal_publish
                  ? new Date(item.tanggal_publish).toLocaleDateString('id-ID')
                  : 'Belum dipublish'}
              </p>
              <div className="mt-3 flex gap-2">
                <Button variant="outline" size="sm" onClick={() => navigate(`/berita/${item.id}/edit`)}>Edit</Button>
                <Button variant="ghost" size="sm" onClick={() => toggleStatus(item)}>
                  {item.status === 'draft' ? 'Publikasikan' : 'Draft'}
                </Button>
                <Button variant="ghost" size="sm" className="text-red-600" onClick={() => setDeleteTarget(item)}>Hapus</Button>
              </div>
            </div>
          </div>
        ))}
        {list.length === 0 && (
          <div className="col-span-full py-12 text-center text-gray-400">Belum ada berita</div>
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

      <ConfirmModal
        open={!!deleteTarget}
        title="Hapus Berita"
        message={`Yakin ingin menghapus "${deleteTarget?.judul}"?`}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  )
}
