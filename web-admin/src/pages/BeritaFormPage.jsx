import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

export default function BeritaFormPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const isEdit = Boolean(id)
  const [form, setForm] = useState({
    judul: '', konten: '', kategori: '', status: 'draft',
  })
  const [gambar, setGambar] = useState(null)
  const [preview, setPreview] = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (isEdit) {
      api.get(`/berita/${id}`).then((res) => {
        const b = res.data.data
        setForm({ judul: b.judul, konten: b.konten, kategori: b.kategori || '', status: b.status })
        if (b.gambar) setPreview(`http://localhost:8000/storage/${b.gambar}`)
      })
    }
  }, [id, isEdit])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    const fd = new FormData()
    fd.append('judul', form.judul)
    fd.append('konten', form.konten)
    fd.append('kategori', form.kategori)
    fd.append('status', form.status)
    if (gambar) fd.append('gambar', gambar)
    if (isEdit) fd.append('_method', 'PUT')

    try {
      if (isEdit) {
        await api.post(`/berita/${id}`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
      } else {
        await api.post('/berita', fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
      }
      navigate('/berita')
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menyimpan')
    }
    setLoading(false)
  }

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit' : 'Tulis'} Berita</h1>
      <form onSubmit={handleSubmit} className="mt-6 space-y-4">
        <div>
          <label className="mb-1 block text-sm font-medium">Judul</label>
          <Input value={form.judul} onChange={(e) => setForm({ ...form, judul: e.target.value })} required />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Kategori</label>
          <select
            className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
            value={form.kategori}
            onChange={(e) => setForm({ ...form, kategori: e.target.value })}
          >
            <option value="">Pilih kategori</option>
            <option value="Informasi">Informasi</option>
            <option value="Kegiatan">Kegiatan</option>
            <option value="Pengumuman">Pengumuman</option>
          </select>
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Konten</label>
          <textarea
            className="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm"
            rows={12}
            value={form.konten}
            onChange={(e) => setForm({ ...form, konten: e.target.value })}
            required
          />
          <p className="mt-1 text-xs text-gray-400">Gunakan teks biasa. Rich text editor akan ditambahkan di fase berikutnya.</p>
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Gambar</label>
          <input type="file" accept="image/*" className="block w-full text-sm" onChange={(e) => {
            const file = e.target.files[0]
            setGambar(file)
            if (file) setPreview(URL.createObjectURL(file))
          }} />
          {preview && <img src={preview} alt="preview" className="mt-2 h-32 rounded object-cover" />}
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Status</label>
          <select
            className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
            value={form.status}
            onChange={(e) => setForm({ ...form, status: e.target.value })}
          >
            <option value="draft">Draft</option>
            <option value="publish">Publish</option>
          </select>
        </div>
        <div className="flex gap-2">
          <Button type="button" variant="outline" onClick={() => navigate('/berita')}>Batal</Button>
          <Button type="submit" disabled={loading}>{loading ? 'Menyimpan...' : (isEdit ? 'Simpan' : 'Terbitkan')}</Button>
        </div>
      </form>
    </div>
  )
}
