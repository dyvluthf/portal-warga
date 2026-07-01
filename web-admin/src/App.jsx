import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from '@/context/AuthContext'
import PrivateRoute from '@/components/PrivateRoute'
import SuperAdminRoute from '@/components/SuperAdminRoute'
import Layout from '@/components/Layout'
import LoginPage from '@/pages/LoginPage'
import DashboardPage from '@/pages/DashboardPage'
import RtRwPage from '@/pages/RtRwPage'
import WargaListPage from '@/pages/WargaListPage'
import WargaFormPage from '@/pages/WargaFormPage'
import WargaDetailPage from '@/pages/WargaDetailPage'
import BeritaListPage from '@/pages/BeritaListPage'
import BeritaFormPage from '@/pages/BeritaFormPage'
import PengaduanListPage from '@/pages/PengaduanListPage'
import PengaduanDetailPage from '@/pages/PengaduanDetailPage'
import SuratListPage from '@/pages/SuratListPage'
import SuratDetailPage from '@/pages/SuratDetailPage'
import IuranDashboardPage from '@/pages/IuranDashboardPage'
import IuranListPage from '@/pages/IuranListPage'
import PengeluaranPage from '@/pages/PengeluaranPage'
import AgendaPage from '@/pages/AgendaPage'
import GaleriPage from '@/pages/GaleriPage'
import AdminListPage from '@/pages/AdminListPage'
import ActivityLogPage from '@/pages/ActivityLogPage'

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route element={<PrivateRoute roles={['super_admin', 'admin_rt_rw']} />}>
            <Route element={<Layout />}>
              <Route path="/dashboard" element={<DashboardPage />} />
              <Route path="/rt-rw" element={<RtRwPage />} />
              <Route path="/warga" element={<WargaListPage />} />
              <Route path="/warga/tambah" element={<WargaFormPage />} />
              <Route path="/warga/:id" element={<WargaDetailPage />} />
              <Route path="/warga/:id/edit" element={<WargaFormPage />} />
              <Route path="/berita" element={<BeritaListPage />} />
              <Route path="/berita/tambah" element={<BeritaFormPage />} />
              <Route path="/berita/:id/edit" element={<BeritaFormPage />} />
              <Route path="/pengaduan" element={<PengaduanListPage />} />
              <Route path="/pengaduan/:id" element={<PengaduanDetailPage />} />
              <Route path="/surat" element={<SuratListPage />} />
              <Route path="/surat/:id" element={<SuratDetailPage />} />
              <Route path="/iuran" element={<IuranDashboardPage />} />
              <Route path="/iuran/warga" element={<IuranListPage />} />
              <Route path="/iuran/pengeluaran" element={<PengeluaranPage />} />
              <Route path="/agenda" element={<AgendaPage />} />
              <Route path="/galeri" element={<GaleriPage />} />
              <Route element={<SuperAdminRoute />}>
                <Route path="/admin" element={<AdminListPage />} />
                <Route path="/activity-logs" element={<ActivityLogPage />} />
              </Route>
            </Route>
          </Route>
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  )
}
