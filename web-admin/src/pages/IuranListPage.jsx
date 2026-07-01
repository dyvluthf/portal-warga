import { useState, useEffect, useCallback } from 'react'
import api from '@/services/api'
import { Button } from '@/components/ui/button'
import ConfirmModal from '@/components/ui/confirm-modal'

export default function IuranListPage() {
  const [wargaList, setWargaList] = useState([])
  const [iuranMap, setIuranMap] = useState({})
  const [bulan, setBulan] = useState(new Date().toISOString().slice(0, 7))
  const [modalVerif, setModalVerif] = useState(null)
  const [modalLunas, setModalLunas] = useState(null)
  const [nominal, setNominal] = useState(50000)

  const fetchData = useCallback(async () => {
    const [tahun, bln] = bulan.split('-')
    const wargaRes = await api.get('/warga', { params: { per_page: 100 } })
    setWargaList(wargaRes.data.data.data || [])

    const iuranRes = await api.get('/iuran', { params: { bulan, per_page: 500 } })
    const map = {}
    ;(iuranRes.data.data.data || []).forEach((i) => {
      map[i.warga_id] = i
    })
    setIuranMap(map)
  }, [bulan])

  useEffect(() => { fetchData() }, [fetchData])

  const handleTandaiLunas = async () => {
    if (!modalLunas) return
    await api.post(`/iuran/${modalLunas}/tandai-lunas`, { bulan, nominal })
    setModalLunas(null)
    fetchData()
  }

  const handleVerifikasi = async () => {
    if (!modalVerif) return
    await api.put(`/iuran/${modalVerif}/verifikasi`)
    setModalVerif(null)
    fetchData()
  }

  const statusBadge = (status) => {
    const colors = {
      lunas: 'bg-green-100 text-green-700',
      menunggu_verifikasi: 'bg-yellow-100 text-yellow-700',
      belum_bayar: 'bg-red-100 text-red-700',
    }
    return (
      <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${colors[status] || ''}`}>
        {status.replace('_', ' ')}
      </span>
    )
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Iuran Warga</h1>
        <input
          type="month"
          className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm"
          value={bulan}
          onChange={(e) => setBulan(e.target.value)}
        />
      </div>

      <div className="mt-4 overflow-x-auto rounded-lg border bg-white shadow-sm">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b bg-gray-50 text-left">
              <th className="px-4 py-3 font-medium text-gray-600">NIK</th>
              <th className="px-4 py-3 font-medium text-gray-600">Nama</th>
              <th className="px-4 py-3 font-medium text-gray-600">Alamat</th>
              <th className="px-4 py-3 font-medium text-gray-600">Status</th>
              <th className="px-4 py-3 font-medium text-gray-600">Metode</th>
              <th className="px-4 py-3 font-medium text-gray-600">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {wargaList.map((w) => {
              const iuran = iuranMap[w.id]
              return (
                <tr key={w.id} className="border-b last:border-0 hover:bg-gray-50">
                  <td className="px-4 py-3">{w.nik}</td>
                  <td className="px-4 py-3 font-medium">{w.nama}</td>
                  <td className="px-4 py-3 text-gray-500">{w.alamat}</td>
                  <td className="px-4 py-3">{iuran ? statusBadge(iuran.status) : statusBadge('belum_bayar')}</td>
                  <td className="px-4 py-3">{iuran?.metode || '-'}</td>
                  <td className="px-4 py-3">
                    <div className="flex gap-1">
                      {iuran?.status === 'menunggu_verifikasi' && (
                        <Button size="sm" onClick={() => setModalVerif(iuran.id)}>Verifikasi</Button>
                      )}
                      {(!iuran || iuran.status === 'belum_bayar') && (
                        <Button size="sm" variant="outline" onClick={() => { setModalLunas(w.id); setNominal(50000) }}>
                          Tandai Lunas
                        </Button>
                      )}
                    </div>
                  </td>
                </tr>
              )
            })}
            {wargaList.length === 0 && (
              <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">Belum ada data warga</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {modalVerif && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={() => setModalVerif(null)}>
          <div className="rounded-lg bg-white p-6 shadow-lg" onClick={(e) => e.stopPropagation()}>
            <h2 className="font-semibold">Verifikasi Pembayaran</h2>
            <p className="mt-2 text-sm text-gray-600">Setujui pembayaran iuran ini?</p>
            <div className="mt-4 flex justify-end gap-2">
              <Button variant="outline" onClick={() => setModalVerif(null)}>Batal</Button>
              <Button onClick={handleVerifikasi}>Verifikasi</Button>
            </div>
          </div>
        </div>
      )}

      <ConfirmModal
        open={!!modalLunas}
        title="Tandai Lunas Manual"
        message={
          <div>
            <p className="text-sm text-gray-600">Tandai warga ini lunas untuk bulan {bulan}?</p>
            <div className="mt-2">
              <label className="text-xs text-gray-500">Nominal Iuran:</label>
              <input
                type="number"
                className="mt-1 flex w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm"
                value={nominal}
                onChange={(e) => setNominal(Number(e.target.value))}
              />
            </div>
          </div>
        }
        onConfirm={handleTandaiLunas}
        onCancel={() => setModalLunas(null)}
        confirmText="Lunaskan"
      />
    </div>
  )
}
