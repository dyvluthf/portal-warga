import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

export default function WargaFormPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const isEdit = Boolean(id)
  const [rtRwList, setRtRwList] = useState([])
  const [form, setForm] = useState({
    nik: '', nama: '', alamat: '', rt_rw_id: '', no_telp: '',
  })
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    api.get('/rt-rw').then((res) => setRtRwList(res.data.data))
    if (isEdit) {
      api.get(`/warga/${id}`).then((res) => {
        const w = res.data.data.warga
        setForm({
          nik: w.nik,
          nama: w.nama,
          alamat: w.alamat || '',
          rt_rw_id: w.rt_rw_id || '',
          no_telp: w.no_telp || '',
        })
      })
    }
  }, [id, isEdit])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors({})
    setLoading(true)
    try {
      if (isEdit) {
        await api.put(`/warga/${id}`, form)
      } else {
        await api.post('/warga', form)
      }
      navigate('/warga')
    } catch (err) {
      if (err.response?.data?.data?.errors) {
        setErrors(err.response.data.data.errors)
      } else {
        setErrors({ general: err.response?.data?.message || 'Gagal menyimpan' })
      }
    }
    setLoading(false)
  }

  return (
    <div className="mx-auto max-w-lg">
      <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit' : 'Tambah'} Warga</h1>
      {errors.general && (
        <div className="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-600">{errors.general}</div>
      )}
      <form onSubmit={handleSubmit} className="mt-6 space-y-4">
        <div>
          <label className="mb-1 block text-sm font-medium">NIK</label>
          <Input value={form.nik} onChange={(e) => setForm({ ...form, nik: e.target.value })} disabled={isEdit} maxLength={16} required={!isEdit} />
          {errors.nik && <p className="mt-1 text-xs text-red-500">{errors.nik[0]}</p>}
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Nama</label>
          <Input value={form.nama} onChange={(e) => setForm({ ...form, nama: e.target.value })} required />
          {errors.nama && <p className="mt-1 text-xs text-red-500">{errors.nama[0]}</p>}
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Alamat</label>
          <textarea
            className="flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm"
            rows={3}
            value={form.alamat}
            onChange={(e) => setForm({ ...form, alamat: e.target.value })}
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">RT/RW</label>
          <select
            className="flex h-9 w-full rounded-md border border-gray-300 bg-white px-3 py-1 text-sm shadow-sm"
            value={form.rt_rw_id}
            onChange={(e) => setForm({ ...form, rt_rw_id: e.target.value })}
          >
            <option value="">- Pilih -</option>
            {rtRwList.map((rt) => (
              <option key={rt.id} value={rt.id}>RT {rt.nomor_rt}/RW {rt.nomor_rw}</option>
            ))}
          </select>
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">No. Telepon</label>
          <Input value={form.no_telp} onChange={(e) => setForm({ ...form, no_telp: e.target.value })} />
        </div>
        <div className="flex gap-2">
          <Button type="button" variant="outline" onClick={() => navigate('/warga')}>Batal</Button>
          <Button type="submit" disabled={loading}>{loading ? 'Menyimpan...' : (isEdit ? 'Simpan' : 'Tambah')}</Button>
        </div>
      </form>
    </div>
  )
}
