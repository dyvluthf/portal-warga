import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '@/context/AuthContext'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import {
  LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend,
} from 'recharts'

export default function DashboardPage() {
  const { user, role } = useAuth()
  const navigate = useNavigate()
  const [data, setData] = useState(null)

  const fetchData = useCallback(async () => {
    try {
      const endpoint = role === 'super_admin' || role === 'admin_rt_rw'
        ? '/dashboard/admin'
        : '/dashboard/warga'
      const res = await api.get(endpoint)
      setData(res.data.data)
    } catch (_) {}
  }, [role])

  useEffect(() => { fetchData() }, [fetchData])

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p className="mt-1 text-gray-500">Selamat datang, {user?.name}</p>

      {data && (
        <>
          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard title="Total Warga" value={data.total_warga} color="text-blue-600" />
            <StatCard title="Pengaduan Baru (Bln Ini)" value={data.pengaduan_baru} color="text-orange-600" />
            <StatCard title="Surat Pending" value={data.surat_pending} color="text-yellow-600" />
            <StatCard title="Iuran Terkumpul (Bln Ini)" value={`Rp ${Number(data.iuran_terkumpul).toLocaleString('id-ID')}`} color="text-green-600" />
          </div>

          <div className="mt-6 grid gap-6 lg:grid-cols-2">
            <div className="rounded-lg border bg-white p-6 shadow-sm">
              <h2 className="mb-4 font-semibold text-gray-900">Tren Pengaduan (6 Bulan)</h2>
              <ResponsiveContainer width="100%" height={250}>
                <BarChart data={data.tren_pengaduan}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="bulan" />
                  <YAxis allowDecimals={false} />
                  <Tooltip />
                  <Bar dataKey="total" fill="#f97316" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>

            <div className="rounded-lg border bg-white p-6 shadow-sm">
              <h2 className="mb-4 font-semibold text-gray-900">Tren Surat (6 Bulan)</h2>
              <ResponsiveContainer width="100%" height={250}>
                <LineChart data={data.tren_surat}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="bulan" />
                  <YAxis allowDecimals={false} />
                  <Tooltip />
                  <Line type="monotone" dataKey="total" stroke="#3b82f6" strokeWidth={2} />
                </LineChart>
              </ResponsiveContainer>
            </div>
          </div>

          <div className="mt-6 grid gap-6 lg:grid-cols-2">
            <div className="rounded-lg border bg-white p-6 shadow-sm">
              <h2 className="mb-4 font-semibold text-gray-900">Agenda Hari Ini</h2>
              {data.agenda_hari_ini?.length > 0 ? (
                <ul className="space-y-2">
                  {data.agenda_hari_ini.map((a) => (
                    <li key={a.id} className="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                      <span className="font-medium">{a.nama}</span>
                      <span className="text-gray-500">{a.jam} · {a.lokasi}</span>
                    </li>
                  ))}
                </ul>
              ) : (
                <p className="text-sm text-gray-400">Tidak ada agenda hari ini</p>
              )}
            </div>

            <div className="rounded-lg border bg-white p-6 shadow-sm">
              <h2 className="mb-4 font-semibold text-gray-900">Pengaduan Terbaru</h2>
              {data.pengaduan_terbaru?.length > 0 ? (
                <ul className="space-y-2">
                  {data.pengaduan_terbaru.map((p) => (
                    <li key={p.id} className="flex cursor-pointer items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm hover:bg-gray-100"
                      onClick={() => navigate(`/pengaduan/${p.id}`)}>
                      <span className="font-medium">{p.kategori}</span>
                      <span className="text-gray-500">{p.warga?.nama} · {p.status}</span>
                    </li>
                  ))}
                </ul>
              ) : (
                <p className="text-sm text-gray-400">Belum ada pengaduan</p>
              )}
            </div>
          </div>

          <div className="mt-6 rounded-lg border bg-white p-6 shadow-sm">
            <h2 className="mb-4 font-semibold text-gray-900">Akses Cepat</h2>
            <div className="flex flex-wrap gap-2">
              <Button variant="outline" size="sm" onClick={() => navigate('/warga')}>Data Warga</Button>
              <Button variant="outline" size="sm" onClick={() => navigate('/berita/tambah')}>Tulis Berita</Button>
              <Button variant="outline" size="sm" onClick={() => navigate('/pengaduan')}>Pengaduan</Button>
              <Button variant="outline" size="sm" onClick={() => navigate('/surat')}>Surat</Button>
              <Button variant="outline" size="sm" onClick={() => navigate('/iuran')}>Iuran</Button>
              <Button variant="outline" size="sm" onClick={() => navigate('/agenda')}>Agenda</Button>
            </div>
          </div>
        </>
      )}

      {!data && (
        <div className="mt-6 text-gray-400">Memuat data...</div>
      )}
    </div>
  )
}

function StatCard({ title, value, color }) {
  return (
    <div className="rounded-lg border bg-white p-6 shadow-sm">
      <p className="text-sm text-gray-500">{title}</p>
      <p className={`mt-1 text-2xl font-bold ${color || 'text-gray-900'}`}>{value ?? '-'}</p>
    </div>
  )
}
