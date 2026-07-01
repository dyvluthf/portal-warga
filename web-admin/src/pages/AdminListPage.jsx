import { useState, useEffect, useCallback } from 'react'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function AdminListPage() {
  const [list, setList] = useState([])
  const [rtList, setRtList] = useState([])
  const [page, setPage] = useState(1)
  const [meta, setMeta] = useState({})
  const [showForm, setShowForm] = useState(false)
  const [editItem, setEditItem] = useState(null)
  const [deleteTarget, setDeleteTarget] = useState(null)
  const [createdPassword, setCreatedPassword] = useState('')
  const [form, setForm] = useState({ nama: '', email: '', role: 'admin_rt_rw', rt_rw_id: '' })
  const [filterRole, setFilterRole] = useState('')
  const [loading, setLoading] = useState(false)

  const fetchData = useCallback(async () => {
    const params = { page, per_page: 15 }
    if (filterRole) params.role = filterRole
    const res = await api.get('/admin', { params })
    setList(res.data.data.data)
    setMeta(res.data.data)
  }, [page, filterRole])

  const fetchRt = useCallback(async () => {
    try {
      const res = await api.get('/rt-rw', { params: { per_page: 100 } })
      setRtList(res.data.data.data || [])
    } catch (_) {}
  }, [])

  useEffect(() => { fetchData(); fetchRt() }, [fetchData, fetchRt])

  const openEdit = (item) => {
    setForm({ nama: item.nama, email: item.user?.email || '', role: item.role, rt_rw_id: item.rt_rw_id || '' })
    setEditItem(item)
    setShowForm(true)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    try {
      const payload = { ...form }
      if (payload.role !== 'admin_rt_rw') payload.rt_rw_id = null
      if (editItem) {
        await api.put(`/admin/${editItem.id}`, payload)
        setEditItem(null)
      } else {
        const res = await api.post('/admin', payload)
        setCreatedPassword(res.data.data?.password_default || '')
      }
      setShowForm(false)
      setForm({ nama: '', email: '', role: 'admin_rt_rw', rt_rw_id: '' })
      fetchData()
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal')
    }
    setLoading(false)
  }

  const handleDeactivate = async () => {
    if (!deleteTarget) return
    await api.delete(`/admin/${deleteTarget.id}`)
    setDeleteTarget(null)
    fetchData()
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Manajemen Admin</h1>
        <Button onClick={() => { setEditItem(null); setForm({ nama: '', email: '', role: 'admin_rt_rw', rt_rw_id: '' }); setShowForm(!showForm); setCreatedPassword('') }}>
          {showForm ? 'Batal' : '+ Admin'}
        </Button>
      </div>

      <div className="mt-4">
        <select className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm" value={filterRole} onChange={(e) => { setFilterRole(e.target.value); setPage(1) }}>
          <option value="">Semua Role</option>
          <option value="super_admin">Super Admin</option>
          <option value="admin_rt_rw">Admin RT/RW</option>
        </select>
      </div>

      {showForm && (
        <form onSubmit={handleSubmit} className="mt-4 rounded-lg border bg-white p-4 shadow-sm space-y-3">
          <div className="grid gap-3 md:grid-cols-2">
            <input className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm" placeholder="Nama" value={form.nama} onChange={(e) => setForm({ ...form, nama: e.target.value })} required />
            <input type="email" className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm" placeholder="Email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
            <select className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm" value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })}>
              <option value="admin_rt_rw">Admin RT/RW</option>
              <option value="super_admin">Super Admin</option>
            </select>
            {form.role === 'admin_rt_rw' && (
              <select className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm" value={form.rt_rw_id} onChange={(e) => setForm({ ...form, rt_rw_id: e.target.value })} required>
                <option value="">Pilih RT/RW</option>
                {rtList.map((rt) => (
                  <option key={rt.id} value={rt.id}>RT {rt.nomor_rt} / RW {rt.nomor_rw} - {rt.nama_kelurahan}</option>
                ))}
              </select>
            )}
          </div>
          <Button type="submit" disabled={loading}>{loading ? 'Menyimpan...' : (editItem ? 'Update' : 'Buat Admin')}</Button>
          {createdPassword && (
            <div className="mt-2 rounded-lg bg-blue-50 p-3 text-sm">
              <p className="font-medium text-blue-800">Password default:</p>
              <p className="mt-1 font-mono text-blue-700">{createdPassword}</p>
              <p className="mt-1 text-xs text-blue-600">Simpan password ini. Tidak akan ditampilkan lagi.</p>
            </div>
          )}
        </form>
      )}

      <div className="mt-4 overflow-x-auto rounded-lg border bg-white shadow-sm">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b bg-gray-50 text-left">
              <th className="px-4 py-3 font-medium text-gray-600">Nama</th>
              <th className="px-4 py-3 font-medium text-gray-600">Email</th>
              <th className="px-4 py-3 font-medium text-gray-600">Role</th>
              <th className="px-4 py-3 font-medium text-gray-600">RT/RW</th>
              <th className="px-4 py-3 font-medium text-gray-600">Status</th>
              <th className="px-4 py-3 font-medium text-gray-600">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {list.map((item) => (
              <tr key={item.id} className="border-b hover:bg-gray-50">
                <td className="px-4 py-3 font-medium">{item.nama}</td>
                <td className="px-4 py-3 text-gray-500">{item.user?.email}</td>
                <td className="px-4 py-3"><span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 capitalize">{item.role?.replace('_', ' ')}</span></td>
                <td className="px-4 py-3 text-gray-500">{item.rt_rw ? `RT ${item.rt_rw.nomor_rt} / RW ${item.rt_rw.nomor_rw}` : '-'}</td>
                <td className="px-4 py-3">
                  <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${item.user?.status_aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                    {item.user?.status_aktif ? 'Aktif' : 'Nonaktif'}
                  </span>
                </td>
                <td className="px-4 py-3">
                  <div className="flex gap-1">
                    <Button size="sm" variant="outline" onClick={() => openEdit(item)}>Edit</Button>
                    {item.user?.status_aktif && (
                      <Button size="sm" variant="ghost" className="text-red-600" onClick={() => setDeleteTarget(item)}>Nonaktifkan</Button>
                    )}
                  </div>
                </td>
              </tr>
            ))}
            {list.length === 0 && <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">Belum ada admin</td></tr>}
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

      <ConfirmModal
        open={!!deleteTarget}
        title="Nonaktifkan Admin"
        message={`Yakin ingin menonaktifkan "${deleteTarget?.nama}"? Admin tidak bisa login sampai diaktifkan kembali.`}
        onConfirm={handleDeactivate}
        onCancel={() => setDeleteTarget(null)}
        confirmText="Nonaktifkan"
      />
    </div>
  )
}
