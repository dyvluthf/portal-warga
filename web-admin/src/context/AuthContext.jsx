import { createContext, useContext, useState, useEffect } from 'react'
import api from '@/services/api'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [token, setToken] = useState(localStorage.getItem('authToken'))
  const [role, setRole] = useState(localStorage.getItem('authRole'))
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (token) {
      api.get('/auth/me')
        .then((res) => {
          setUser(res.data.data.user)
          setRole(res.data.data.role)
        })
        .catch(() => {
          logout()
        })
        .finally(() => setLoading(false))
    } else {
      setLoading(false)
    }
  }, [token])

  const login = async (email, password) => {
    const res = await api.post('/auth/login', { email, password })
    const { user: userData, token: newToken, role: userRole } = res.data.data
    localStorage.setItem('authToken', newToken)
    localStorage.setItem('authUser', JSON.stringify(userData))
    localStorage.setItem('authRole', userRole)
    setToken(newToken)
    setUser(userData)
    setRole(userRole)
    return res.data
  }

  const logout = () => {
    if (token) {
      api.post('/auth/logout').catch(() => {})
    }
    localStorage.removeItem('authToken')
    localStorage.removeItem('authUser')
    localStorage.removeItem('authRole')
    setToken(null)
    setUser(null)
    setRole(null)
  }

  return (
    <AuthContext.Provider value={{ user, token, role, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}
