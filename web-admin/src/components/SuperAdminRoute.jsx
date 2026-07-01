import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '@/context/AuthContext'

export default function SuperAdminRoute() {
  const { role } = useAuth()

  if (role !== 'super_admin') {
    return <Navigate to="/dashboard" replace />
  }

  return <Outlet />
}
