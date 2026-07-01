import { render, screen } from '@testing-library/react'
import { MemoryRouter, Routes, Route } from 'react-router-dom'
import { AuthProvider } from '@/context/AuthContext'
import PrivateRoute from '@/components/PrivateRoute'
import { describe, it, expect, beforeEach, vi } from 'vitest'

const mockNavigate = vi.fn()

vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom')
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  }
})

function renderWithAuth(initialRoute = '/dashboard', token = null) {
  if (token) {
    localStorage.setItem('authToken', token)
    localStorage.setItem('authUser', JSON.stringify({ name: 'Admin', role: 'super_admin' }))
    localStorage.setItem('authRole', 'super_admin')
  }

  return render(
    <MemoryRouter initialEntries={[initialRoute]}>
      <AuthProvider>
        <Routes>
          <Route element={<PrivateRoute roles={['super_admin', 'admin_rt_rw']} />}>
            <Route path="/dashboard" element={<div>Dashboard Content</div>} />
          </Route>
          <Route path="/login" element={<div>Login Page</div>} />
        </Routes>
      </AuthProvider>
    </MemoryRouter>
  )
}

describe('PrivateRoute', () => {
  beforeEach(() => {
    localStorage.clear()
    mockNavigate.mockClear()
  })

  it('renders children when authenticated', async () => {
    renderWithAuth('/dashboard', 'valid-token')
    expect(await screen.findByText('Dashboard Content')).toBeInTheDocument()
  })

  it('redirects to login when not authenticated', () => {
    renderWithAuth('/dashboard', null)
    expect(screen.getByText('Login Page')).toBeInTheDocument()
  })

  it('shows loading spinner while checking auth', () => {
    localStorage.setItem('authToken', 'token')
    renderWithAuth('/dashboard', 'token')
    const spinner = document.querySelector('.animate-spin')
    expect(spinner).toBeInTheDocument()
  })
})
