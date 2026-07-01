import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { AuthProvider } from '@/context/AuthContext'
import LoginPage from '@/pages/LoginPage'
import api from '@/services/api'
import { describe, it, expect, beforeEach, vi } from 'vitest'

vi.mock('@/services/api')

const renderLogin = () =>
  render(
    <MemoryRouter>
      <AuthProvider>
        <LoginPage />
      </AuthProvider>
    </MemoryRouter>
  )

describe('LoginPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
  })

  it('renders login form', () => {
    renderLogin()
    expect(screen.getByPlaceholderText(/email/i)).toBeInTheDocument()
    expect(screen.getByPlaceholderText(/password/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /login|masuk/i })).toBeInTheDocument()
  })

  it('shows error on invalid credentials', async () => {
    const user = userEvent.setup()
    api.post.mockRejectedValueOnce({
      response: { data: { message: 'Kredensial tidak valid' } },
    })

    renderLogin()

    await user.type(screen.getByPlaceholderText(/email/i), 'wrong@test.com')
    await user.type(screen.getByPlaceholderText(/password/i), 'wrong')
    await user.click(screen.getByRole('button'))

    await waitFor(() => {
      expect(screen.getByText(/kredensial tidak valid/i)).toBeInTheDocument()
    })
  })

  it('calls API on form submit', async () => {
    const user = userEvent.setup()
    api.post.mockResolvedValueOnce({
      data: {
        success: true,
        data: {
          user: { name: 'Admin', role: 'super_admin' },
          token: 'fake-token',
          role: 'super_admin',
        },
      },
    })

    renderLogin()

    await user.type(screen.getByPlaceholderText(/email/i), 'admin@test.com')
    await user.type(screen.getByPlaceholderText(/password/i), 'password')
    await user.click(screen.getByRole('button'))

    await waitFor(() => {
      expect(api.post).toHaveBeenCalledWith('/auth/login', {
        email: 'admin@test.com',
        password: 'password',
      })
    })
  })
})
