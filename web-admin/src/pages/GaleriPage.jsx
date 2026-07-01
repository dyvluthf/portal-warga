import { useState, useEffect, useCallback } from 'react'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function GaleriPage() {
  const [albums, setAlbums] = useState([])
  const [page, setPage] = useState(1)
  const [meta, setMeta] = useState({})
  const [showForm, setShowForm] = useState(false)
  const [form, setForm] = useState({ nama_album: '', caption: '' })
  const [files, setFiles] = useState([])
  const [previews, setPreviews] = useState([])
  const [loading, setLoading] = useState(false)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [viewAlbum, setViewAlbum] = useState(null)

  const fetchData = useCallback(async () => {
    const res = await api.get('/galeri', { params: { page, per_page: 12 } })
    setAlbums(res.data.data.data || [])
    setMeta(res.data.data)
  }, [page])

  useEffect(() => { fetchData() }, [fetchData])

  const handleFiles = (e) => {
    const selected = Array.from(e.target.files).slice(0, 10)
    setFiles(selected)
    setPreviews(selected.map((f) => URL.createObjectURL(f)))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!form.nama_album || files.length === 0) return
    setLoading(true)
    const fd = new FormData()
    fd.append('nama_album', form.nama_album)
    fd.append('caption', form.caption)
    files.forEach((f) => fd.append('foto[]', f))
    try {
      await api.post('/galeri', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      setShowForm(false)
      setForm({ nama_album: '', caption: '' })
      setFiles([])
      setPreviews([])
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal')
    }
    setLoading(false)
  }

  const handleDelete = async () => {
    if (!deleteTarget) return
    await api.delete(`/galeri/${deleteTarget.id}`)
    setDeleteTarget(null)
    setViewAlbum(null)
    fetchData()
  }

  const viewAlbumDetail = async (album) => {
    const res = await api.get(`/galeri/${album.id}`)
    setViewAlbum(res.data.data)
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Galeri Foto</h1>
        <Button onClick={() => setShowForm(!showForm)}>{showForm ? 'Batal' : '+ Album'}</Button>
      </div>

      {showForm && (
        <form onSubmit={handleSubmit} className="mt-4 rounded-lg border bg-white p-4 shadow-sm space-y-3">
          <input
            className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
            placeholder="Nama Album"
            value={form.nama_album}
            onChange={(e) => setForm({ ...form, nama_album: e.target.value })}
            required
          />
          <input
            className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
            placeholder="Caption (opsional)"
            value={form.caption}
            onChange={(e) => setForm({ ...form, caption: e.target.value })}
          />
          <input type="file" multiple accept="image/*" className="block w-full text-sm" onChange={handleFiles} required />
          {previews.length > 0 && (
            <div className="flex flex-wrap gap-2">
              {previews.map((p, i) => (
                <img key={i} src={p} alt="" className="h-20 w-20 rounded object-cover" />
              ))}
            </div>
          )}
          <Button type="submit" disabled={loading}>{loading ? 'Mengupload...' : 'Buat Album'}</Button>
        </form>
      )}

      <div className="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {albums.map((album) => (
          <div key={album.id} className="cursor-pointer rounded-lg border bg-white shadow-sm transition hover:shadow-md" onClick={() => viewAlbumDetail(album)}>
            <div className="flex h-40 items-center justify-center bg-gray-100 text-4xl">🖼️</div>
            <div className="p-4">
              <h3 className="font-semibold text-gray-900">{album.nama_album}</h3>
              <p className="text-xs text-gray-500">{album.foto_count} foto</p>
              <div className="mt-2">
                <Button size="sm" variant="ghost" className="text-red-600" onClick={(e) => { e.stopPropagation(); setDeleteTarget(album) }}>Hapus</Button>
              </div>
            </div>
          </div>
        ))}
        {albums.length === 0 && <div className="col-span-full py-8 text-center text-gray-400">Belum ada album</div>}
      </div>

      {meta?.last_page > 1 && (
        <div className="mt-4 flex items-center justify-center gap-2">
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
            <button key={p} className={`rounded px-3 py-1 text-sm ${page === p ? 'bg-primary text-white' : 'bg-gray-100 hover:bg-gray-200'}`} onClick={() => setPage(p)}>{p}</button>
          ))}
        </div>
      )}

      {viewAlbum && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70" onClick={() => setViewAlbum(null)}>
          <div className="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6 shadow-lg" onClick={(e) => e.stopPropagation()}>
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-bold">{viewAlbum.nama_album}</h2>
              <Button variant="ghost" onClick={() => setViewAlbum(null)}>Tutup</Button>
            </div>
            <div className="mt-4 grid grid-cols-2 gap-2 md:grid-cols-3">
              {viewAlbum.foto?.map((f, i) => (
                <div key={f.id}>
                  <img src={`http://localhost:8000/storage/${f.path_foto}`} alt={f.caption || ''} className="h-48 w-full rounded object-cover" />
                  {f.caption && <p className="mt-1 text-xs text-gray-500">{f.caption}</p>}
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      <ConfirmModal
        open={!!deleteTarget}
        title="Hapus Album"
        message={`Yakin ingin menghapus album "${deleteTarget?.nama_album}" beserta semua fotonya?`}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  )
}
