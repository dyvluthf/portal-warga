import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '@/context/AuthContext'
import { Button } from '@/components/ui/button'

const commonNavItems = [
  { label: 'Dashboard', path: '/dashboard', icon: '📊' },
  { label: 'Data Warga', path: '/warga', icon: '👥' },
  { label: 'RT/RW', path: '/rt-rw', icon: '🏠' },
  { label: 'Berita', path: '/berita', icon: '📰' },
  { label: 'Iuran & Keuangan', path: '/iuran', icon: '💰' },
  { label: 'Agenda', path: '/agenda', icon: '📅' },
  { label: 'Galeri', path: '/galeri', icon: '🖼️' },
  { label: 'Pengaduan', path: '/pengaduan', icon: '📝' },
  { label: 'Surat', path: '/surat', icon: '📄' },
]

const superAdminNavItems = [
  { label: 'Manajemen Admin', path: '/admin', icon: '👤' },
  { label: 'Activity Log', path: '/activity-logs', icon: '📋' },
]

export default function Layout() {
  const { user, logout, role } = useAuth()
  const navigate = useNavigate()

  const navItems = role === 'super_admin'
    ? [...commonNavItems, ...superAdminNavItems]
    : commonNavItems

  const handleLogout = () => {
    logout()
    navigate('/login')
  }

  return (
    <div className="flex min-h-screen bg-gray-50">
      <aside className="flex w-60 flex-col border-r bg-white">
        <div className="flex items-center gap-2 border-b px-6 py-4">
          <span className="text-xl">🏘️</span>
          <span className="font-bold text-gray-800">Portal Warga</span>
        </div>
        <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
          {navItems.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              className={({ isActive }) =>
                `flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors ${
                  isActive
                    ? 'bg-primary/10 font-medium text-primary'
                    : 'text-gray-600 hover:bg-gray-100'
                }`
              }
            >
              <span>{item.icon}</span>
              {item.label}
            </NavLink>
          ))}
        </nav>
        <div className="border-t px-4 py-3">
          <div className="mb-2 text-xs text-gray-500">
            {user?.name}
            <br />
            <span className="capitalize">{user?.role?.replace('_', ' ')}</span>
          </div>
          <Button variant="ghost" size="sm" className="w-full justify-start" onClick={handleLogout}>
            Logout
          </Button>
        </div>
      </aside>
      <main className="flex-1 overflow-auto p-6">
        <Outlet />
      </main>
    </div>
  )
}
