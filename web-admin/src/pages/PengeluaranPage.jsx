import { useState, useEffect, useCallback } from 'react'
import api from '@/services/api'
import { Button } from '@/components/ui/button'

export default function PengeluaranPage() {
  const [list, setList] = useState([])
  const [bulan, setBulan] = useState('')
  const [page, setPage] = useState(1)
  const [meta, setMeta] = useState({})
  const [form, setForm] = useState({ judul: '', jumlah: '', kategori: '', keterangan: '' })
  const [bukti, setBukti] = useState(null)
  const [showForm, setShowForm] = useState(false)
  const [loading, setLoading] = useState(false)

  const fetchData = useCallback(async () => {
    const params = { page, per_page: 15, jenis: 'pengeluaran' }
    if (bulan) params.bulan = bulan
    const res = await api.get('/keuangan', { params })
    setList(res.data.data.data)
    setMeta(res.data.data)
  }, [page, bulan])

  useEffect(() => { fetchData() }, [fetchData])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    const fd = new FormData()
    fd.append('judul', form.judul)
    fd.append('jumlah', form.jumlah)
    fd.append('kategori', form.kategori)
    fd.append('keterangan', form.keterangan)
    if (bukti) fd.append('bukti_foto', bukti)

    try {
      await api.post('/keuangan/pengeluaran', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      setShowForm(false)
      setForm({ judul: '', jumlah: '', kategori: '', keterangan: '' })
      setBukti(null)
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal')
    }
    setLoading(false)
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Pengeluaran</h1>
        <div className="flex items-center gap-2">
          <input type="month" className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm" value={bulan} onChange={(e) => { setBulan(e.target.value); setPage(1) }} />
          <Button onClick={() => setShowForm(!showForm)}>{showForm ? 'Batal' : 'Tambah Pengeluaran'}</Button>
        </div>
      </div>

      {showForm && (
        <form onSubmit={handleSubmit} className="mt-4 rounded-lg border bg-white p-4 shadow-sm space-y-3">
          <div className="grid gap-3 md:grid-cols-2">
            <input
              className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
              placeholder="Judul"
              value={form.judul}
              onChange={(e) => setForm({ ...form, judul: e.target.value })}
              required
            />
            <input
              type="number"
              className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
              placeholder="Jumlah"
              value={form.jumlah}
              onChange={(e) => setForm({ ...form, jumlah: e.target.value })}
              required
            />
            <input
              className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
              placeholder="Kategori (opsional)"
              value={form.kategori}
              onChange={(e) => setForm({ ...form, kategori: e.target.value })}
            />
            <input type="file" className="block w-full text-sm" onChange={(e) => setBukti(e.target.files[0])} accept="image/*" />
          </div>
          <textarea
            className="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm"
            rows={3}
            placeholder="Keterangan (opsional)"
            value={form.keterangan}
            onChange={(e) => setForm({ ...form, keterangan: e.target.value })}
          />
          <Button type="submit" disabled={loading}>{loading ? 'Menyimpan...' : 'Simpan Pengeluaran'}</Button>
        </form>
      )}

      <div className="mt-4 space-y-2">
        {list.map((item) => (
          <div key={item.id} className="flex items-center justify-between rounded-lg border bg-white p-4 shadow-sm">
            <div>
              <p className="font-semibold text-gray-900">{item.judul}</p>
              <p className="text-xs text-gray-500">{item.kategori && `${item.kategori} · `}Rp {Number(item.jumlah).toLocaleString('id-ID')} · {item.tanggal}</p>
              {item.keterangan && <p className="mt-1 text-xs text-gray-400">{item.keterangan}</p>}
            </div>
            <div className="flex items-center gap-2">
              <span className="text-sm font-semibold text-red-600">-Rp {Number(item.jumlah).toLocaleString('id-ID')}</span>
              {item.bukti_foto && (
                <a href={`http://localhost:8000/storage/${item.bukti_foto}`} target="_blank" rel="noopener noreferrer" className="text-xs text-blue-600 underline">Lihat</a>
              )}
            </div>
          </div>
        ))}
        {list.length === 0 && <div className="py-8 text-center text-gray-400">Belum ada pengeluaran</div>}
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
