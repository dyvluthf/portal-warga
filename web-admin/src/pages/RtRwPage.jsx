import { useState, useEffect, useCallback } from 'react'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function RtRwPage() {
  const [list, setList] = useState([])
  const [wargaList, setWargaList] = useState([])
  const [open, setOpen] = useState(false)
  const [edit, setEdit] = useState(null)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [form, setForm] = useState({
    nomor_rt: '', nomor_rw: '', nama_kelurahan: '', kecamatan: '',
    ketua_rt_id: '', sekretaris_id: '', bendahara_id: '',
  })

  const fetchData = useCallback(async () => {
    const [rtRes, wargaRes] = await Promise.all([
      api.get('/rt-rw'),
      api.get('/warga?per_page=100'),
    ])
    setList(rtRes.data.data)
    setWargaList(wargaRes.data.data.data || [])
  }, [])

  useEffect(() => { fetchData() }, [fetchData])

  const openForm = (item) => {
    if (item) {
      setEdit(item)
      setForm({
        nomor_rt: item.nomor_rt,
        nomor_rw: item.nomor_rw,
        nama_kelurahan: item.nama_kelurahan || '',
        kecamatan: item.kecamatan || '',
        ketua_rt_id: item.ketua_rt_id || '',
        sekretaris_id: item.sekretaris_id || '',
        bendahara_id: item.bendahara_id || '',
      })
    } else {
      setEdit(null)
      setForm({ nomor_rt: '', nomor_rw: '', nama_kelurahan: '', kecamatan: '', ketua_rt_id: '', sekretaris_id: '', bendahara_id: '' })
    }
    setOpen(true)
  }

  const handleSave = async () => {
    try {
      const payload = {
        ...form,
        ketua_rt_id: form.ketua_rt_id || null,
        sekretaris_id: form.sekretaris_id || null,
        bendahara_id: form.bendahara_id || null,
      }
      if (edit) {
        await api.put(`/rt-rw/${edit.id}`, payload)
      } else {
        await api.post('/rt-rw', payload)
      }
      setOpen(false)
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menyimpan')
    }
  }

  const handleDelete = async () => {
    if (!deleteTarget) return
    try {
      await api.delete(`/rt-rw/${deleteTarget.id}`)
      setDeleteTarget(null)
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menghapus')
    }
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Kelola RT/RW</h1>
        <Button onClick={() => openForm(null)}>Tambah RT/RW</Button>
      </div>

      <div className="mt-6 overflow-x-auto rounded-lg border bg-white shadow-sm">
        <table className="w-full text-sm">
          <thead className="border-b bg-gray-50 text-left">
            <tr>
              <th className="px-4 py-3 font-medium">RT</th>
              <th className="px-4 py-3 font-medium">RW</th>
              <th className="px-4 py-3 font-medium">Ketua RT</th>
              <th className="px-4 py-3 font-medium">Sekretaris</th>
              <th className="px-4 py-3 font-medium">Bendahara</th>
              <th className="px-4 py-3 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {list.map((item) => (
              <tr key={item.id} className="border-b last:border-0 hover:bg-gray-50">
                <td className="px-4 py-3">{item.nomor_rt}</td>
                <td className="px-4 py-3">{item.nomor_rw}</td>
                <td className="px-4 py-3">{item.ketua_rt?.nama || '-'}</td>
                <td className="px-4 py-3">{item.sekretaris?.nama || '-'}</td>
                <td className="px-4 py-3">{item.bendahara?.nama || '-'}</td>
                <td className="flex gap-2 px-4 py-3">
                  <Button variant="outline" size="sm" onClick={() => openForm(item)}>Edit</Button>
                  <Button variant="ghost" size="sm" className="text-red-600" onClick={() => setDeleteTarget(item)}>Hapus</Button>
                </td>
              </tr>
            ))}
            {list.length === 0 && (
              <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">Belum ada data RT/RW</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={() => setOpen(false)}>
          <div className="w-full max-w-md rounded-lg bg-white p-6 shadow-lg" onClick={(e) => e.stopPropagation()}>
            <h2 className="text-lg font-semibold">{edit ? 'Edit' : 'Tambah'} RT/RW</h2>
            <div className="mt-4 space-y-3">
              <div className="flex gap-2">
                <div className="flex-1">
                  <label className="mb-1 block text-xs font-medium">Nomor RT</label>
                  <Input value={form.nomor_rt} onChange={(e) => setForm({ ...form, nomor_rt: e.target.value })} />
                </div>
                <div className="flex-1">
                  <label className="mb-1 block text-xs font-medium">Nomor RW</label>
                  <Input value={form.nomor_rw} onChange={(e) => setForm({ ...form, nomor_rw: e.target.value })} />
                </div>
              </div>
              <div>
                <label className="mb-1 block text-xs font-medium">Kelurahan</label>
                <Input value={form.nama_kelurahan} onChange={(e) => setForm({ ...form, nama_kelurahan: e.target.value })} />
              </div>
              <div>
                <label className="mb-1 block text-xs font-medium">Kecamatan</label>
                <Input value={form.kecamatan} onChange={(e) => setForm({ ...form, kecamatan: e.target.value })} />
              </div>
              {['ketua_rt_id', 'sekretaris_id', 'bendahara_id'].map((field) => (
                <div key={field}>
                  <label className="mb-1 block text-xs font-medium capitalize">{field.replace('_id', '').replace('_', ' ')}</label>
                  <select
                    className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
                    value={form[field]}
                    onChange={(e) => setForm({ ...form, [field]: e.target.value })}
                  >
                    <option value="">- Pilih Warga -</option>
                    {wargaList.map((w) => (
                      <option key={w.id} value={w.id}>{w.nama} ({w.nik})</option>
                    ))}
                  </select>
                </div>
              ))}
            </div>
            <div className="mt-6 flex justify-end gap-2">
              <Button variant="outline" onClick={() => setOpen(false)}>Batal</Button>
              <Button onClick={handleSave}>{edit ? 'Simpan' : 'Tambah'}</Button>
            </div>
          </div>
        </div>
      )}

      <ConfirmModal
        open={!!deleteTarget}
        title="Hapus RT/RW"
        message={`Yakin ingin menghapus RT ${deleteTarget?.nomor_rt} / RW ${deleteTarget?.nomor_rw}?`}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  )
}
