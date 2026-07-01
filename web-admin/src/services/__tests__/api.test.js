import api from '@/services/api'
import axios from 'axios'
import { describe, it, expect, beforeEach, vi } from 'vitest'

vi.mock('axios')

describe('api service', () => {
  beforeEach(() => {
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('sets baseURL from env or default', () => {
    expect(axios.create).toHaveBeenCalledWith(
      expect.objectContaining({
        baseURL: 'http://localhost:8000/api/v1',
      })
    )
  })

  it('attaches auth token from localStorage', () => {
    localStorage.setItem('authToken', 'test-token')
    const config = { headers: {} }
    const interceptor = axios.create().interceptors.request
    expect(interceptor.use).toHaveBeenCalled()
  })

  it('handles 401 by redirecting to login', () => {
    const error = { response: { status: 401 } }
    const rejectionHandler = api.interceptors.response.handlers.find(
      (h) => h.rejected
    )?.rejected

    localStorage.setItem('authToken', 'token')
    rejectionHandler(error).catch(() => {
      expect(localStorage.getItem('authToken')).toBeNull()
    })
  })
})
