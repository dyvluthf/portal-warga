import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '@/services/api'
import { Button } from '@/components/ui/button'

export default function IuranDashboardPage() {
  const navigate = useNavigate()
  const [data, setData] = useState(null)
  const [bulan, setBulan] = useState('')

  const fetchData = useCallback(async () => {
    const params = {}
    if (bulan) params.bulan = bulan
    const res = await api.get('/keuangan/dashboard', { params })
    setData(res.data.data)
  }, [bulan])

  useEffect(() => { fetchData() }, [fetchData])

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900">Iuran & Keuangan</h1>
      <div className="mt-4 flex items-center gap-3">
        <input
          type="month"
          className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm"
          value={bulan}
          onChange={(e) => setBulan(e.target.value)}
        />
        <Button variant="outline" onClick={() => navigate('/iuran/warga')}>Data Iuran Warga</Button>
        <Button variant="outline" onClick={() => navigate('/iuran/pengeluaran')}>Catat Pengeluaran</Button>
      </div>

      {data && (
        <div className="mt-6 grid gap-4 md:grid-cols-3">
          <div className="rounded-lg border bg-white p-6 shadow-sm">
            <p className="text-sm text-gray-500">Saldo</p>
            <p className={`mt-1 text-3xl font-bold ${data.saldo >= 0 ? 'text-green-600' : 'text-red-600'}`}>
              Rp {Number(data.saldo).toLocaleString('id-ID')}
            </p>
          </div>
          <div className="rounded-lg border bg-white p-6 shadow-sm">
            <p className="text-sm text-gray-500">Total Pemasukan</p>
            <p className="mt-1 text-3xl font-bold text-green-600">Rp {Number(data.total_pemasukan).toLocaleString('id-ID')}</p>
          </div>
          <div className="rounded-lg border bg-white p-6 shadow-sm">
            <p className="text-sm text-gray-500">Total Pengeluaran</p>
            <p className="mt-1 text-3xl font-bold text-red-600">Rp {Number(data.total_pengeluaran).toLocaleString('id-ID')}</p>
          </div>
        </div>
      )}

      {!data && <div className="mt-6 text-gray-400">Memuat data...</div>}

      <div className="mt-6 grid gap-4 md:grid-cols-2">
        <div className="rounded-lg border bg-white p-6 shadow-sm">
          <h2 className="font-semibold text-gray-900">Download Laporan</h2>
          <div className="mt-3 flex gap-2">
            <Button variant="outline" size="sm" onClick={() => {
              const params = bulan ? `?bulan=${bulan}` : ''
              window.open(`http://localhost:8000/api/v1/iuran/laporan${params}`, '_blank')
            }}>
              Laporan Iuran (CSV)
            </Button>
            <Button variant="outline" size="sm" onClick={() => {
              const params = bulan ? `?bulan=${bulan}` : ''
              window.open(`http://localhost:8000/api/v1/keuangan/laporan${params}`, '_blank')
            }}>
              Laporan Keuangan (CSV)
            </Button>
          </div>
        </div>
      </div>
    </div>
  )
}
