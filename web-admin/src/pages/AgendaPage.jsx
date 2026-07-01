import { useState, useEffect, useCallback } from 'react'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function AgendaPage() {
  const [list, setList] = useState([])
  const [periode, setPeriode] = useState('')
  const [showForm, setShowForm] = useState(false)
  const [editItem, setEditItem] = useState(null)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [detail, setDetail] = useState(null)
  const [form, setForm] = useState({ nama: '', tanggal: '', jam: '', lokasi: '', deskripsi: '' })
  const [loading, setLoading] = useState(false)

  const fetchData = useCallback(async () => {
    const params = { per_page: 50 }
    if (periode) params.periode = periode
    const res = await api.get('/agenda', { params })
    setList(res.data.data.data || [])
  }, [periode])

  useEffect(() => { fetchData() }, [fetchData])

  const openEdit = (item) => {
    setForm({ nama: item.nama, tanggal: item.tanggal, jam: item.jam, lokasi: item.lokasi, deskripsi: item.deskripsi || '' })
    setEditItem(item)
    setShowForm(true)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    try {
      if (editItem) {
        await api.put(`/agenda/${editItem.id}`, form)
      } else {
        await api.post('/agenda', form)
      }
      setShowForm(false)
      setEditItem(null)
      setForm({ nama: '', tanggal: '', jam: '', lokasi: '', deskripsi: '' })
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal')
    }
    setLoading(false)
  }

  const handleDelete = async () => {
    if (!deleteTarget) return
    await api.delete(`/agenda/${deleteTarget.id}`)
    setDeleteTarget(null)
    fetchData()
  }

  const viewDetail = async (item) => {
    const res = await api.get(`/agenda/${item.id}/peserta`)
    setDetail({ ...item, ...res.data.data })
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Agenda Kegiatan</h1>
        <Button onClick={() => { setEditItem(null); setForm({ nama: '', tanggal: '', jam: '', lokasi: '', deskripsi: '' }); setShowForm(!showForm) }}>
          {showForm ? 'Batal' : '+ Agenda'}
        </Button>
      </div>

      <div className="mt-4 flex gap-2">
        {['', 'hari_ini', 'minggu_ini', 'bulan_ini'].map((p) => (
          <button
            key={p}
            className={`rounded-lg px-3 py-1.5 text-sm font-medium transition-colors ${periode === p ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
            onClick={() => { setPeriode(p); setDetail(null) }}
          >
            {p === '' ? 'Semua' : p === 'hari_ini' ? 'Hari Ini' : p === 'minggu_ini' ? 'Minggu Ini' : 'Bulan Ini'}
          </button>
        ))}
      </div>

      {showForm && (
        <form onSubmit={handleSubmit} className="mt-4 rounded-lg border bg-white p-4 shadow-sm space-y-3">
          <div className="grid gap-3 md:grid-cols-3">
            <input className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm" placeholder="Nama Kegiatan" value={form.nama} onChange={(e) => setForm({ ...form, nama: e.target.value })} required />
            <input type="date" className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm" value={form.tanggal} onChange={(e) => setForm({ ...form, tanggal: e.target.value })} required />
            <input type="time" className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm" value={form.jam} onChange={(e) => setForm({ ...form, jam: e.target.value })} required />
            <input className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm md:col-span-2" placeholder="Lokasi" value={form.lokasi} onChange={(e) => setForm({ ...form, lokasi: e.target.value })} required />
          </div>
          <textarea className="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm" rows={3} placeholder="Deskripsi (opsional)" value={form.deskripsi} onChange={(e) => setForm({ ...form, deskripsi: e.target.value })} />
          <Button type="submit" disabled={loading}>{loading ? 'Menyimpan...' : (editItem ? 'Update' : 'Buat Agenda')}</Button>
        </form>
      )}

      <div className="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        {list.map((item) => (
          <div key={item.id} className="rounded-lg border bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between">
              <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                {item.tanggal} {item.jam}
              </span>
            </div>
            <h3 className="mt-2 font-semibold text-gray-900">{item.nama}</h3>
            <p className="mt-1 text-xs text-gray-500">📍 {item.lokasi}</p>
            {item.deskripsi && <p className="mt-1 line-clamp-2 text-xs text-gray-400">{item.deskripsi}</p>}
            <div className="mt-3 flex gap-1">
              <Button size="sm" variant="outline" onClick={() => viewDetail(item)}>Peserta</Button>
              <Button size="sm" variant="outline" onClick={() => openEdit(item)}>Edit</Button>
              <Button size="sm" variant="ghost" className="text-red-600" onClick={() => setDeleteTarget(item)}>Hapus</Button>
            </div>
          </div>
        ))}
        {list.length === 0 && <div className="col-span-full py-8 text-center text-gray-400">Belum ada agenda</div>}
      </div>

      {detail && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={() => setDetail(null)}>
          <div className="max-h-[80vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6 shadow-lg" onClick={(e) => e.stopPropagation()}>
            <h2 className="font-semibold text-lg">{detail.nama}</h2>
            <p className="text-sm text-gray-500">{detail.tanggal} {detail.jam} · {detail.lokasi}</p>
            {detail.deskripsi && <p className="mt-2 text-sm text-gray-700">{detail.deskripsi}</p>}
            <h3 className="mt-4 font-medium">Peserta Hadir ({detail.hadir?.length || 0})</h3>
            <ul className="mt-1 space-y-1">
              {(detail.hadir || []).map((p) => (
                <li key={p.id} className="text-sm">✅ {p.warga?.nama || '-'}</li>
              ))}
            </ul>
            <h3 className="mt-3 font-medium">Tidak Hadir ({detail.tidak_hadir?.length || 0})</h3>
            <ul className="mt-1 space-y-1">
              {(detail.tidak_hadir || []).map((p) => (
                <li key={p.id} className="text-sm">❌ {p.warga?.nama || '-'}</li>
              ))}
            </ul>
            <Button className="mt-4" onClick={() => setDetail(null)}>Tutup</Button>
          </div>
        </div>
      )}

      <ConfirmModal
        open={!!deleteTarget}
        title="Hapus Agenda"
        message={`Yakin ingin menghapus "${deleteTarget?.nama}"?`}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  )
}
