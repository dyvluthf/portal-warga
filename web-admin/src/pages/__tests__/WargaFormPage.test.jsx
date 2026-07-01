import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { AuthProvider } from '@/context/AuthContext'
import WargaFormPage from '@/pages/WargaFormPage'
import api from '@/services/api'
import { describe, it, expect, beforeEach, vi } from 'vitest'

vi.mock('@/services/api')

function renderWargaForm(route = '/warga/tambah') {
  localStorage.setItem('authToken', 'token')
  localStorage.setItem('authUser', JSON.stringify({ name: 'Admin', role: 'super_admin' }))
  localStorage.setItem('authRole', 'super_admin')

  return render(
    <MemoryRouter initialEntries={[route]}>
      <AuthProvider>
        <Routes>
          <Route path="/warga/tambah" element={<WargaFormPage />} />
          <Route path="/warga/:id/edit" element={<WargaFormPage />} />
        </Routes>
      </AuthProvider>
    </MemoryRouter>
  )
}

describe('WargaFormPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
    api.get.mockResolvedValue({ data: { data: [] } })
  })

  it('renders form with required fields', () => {
    renderWargaForm()
    expect(screen.getByLabelText(/nik/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/nama/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /simpan|tambah/i })).toBeInTheDocument()
  })

  it('shows validation error when NIK is empty', async () => {
    const user = userEvent.setup()
    renderWargaForm()
    await user.click(screen.getByRole('button'))
    await waitFor(() => {
      expect(screen.getByText(/nik harus diisi/i)).toBeInTheDocument()
    })
  })

  it('submits form successfully', async () => {
    const user = userEvent.setup()
    api.post.mockResolvedValueOnce({
      data: { success: true, message: 'Warga berhasil ditambahkan', data: {} },
    })

    renderWargaForm()

    await user.type(screen.getByLabelText(/nik/i), '3201010101010101')
    await user.type(screen.getByLabelText(/nama/i), 'Budi Test')
    await user.click(screen.getByRole('button'))

    await waitFor(() => {
      expect(api.post).toHaveBeenCalled()
    })
  })
})
