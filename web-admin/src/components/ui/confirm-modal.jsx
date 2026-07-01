import { useEffect, useRef } from 'react'
import { Button } from './button'

export default function ConfirmModal({ open, title, message, onConfirm, onCancel, confirmText = 'Hapus' }) {
  const dialogRef = useRef(null)

  useEffect(() => {
    if (open) {
      dialogRef.current?.showModal()
    } else {
      dialogRef.current?.close()
    }
  }, [open])

  return (
    <dialog
      ref={dialogRef}
      className="rounded-lg border p-6 shadow-lg backdrop:bg-black/50"
      onClose={onCancel}
    >
      <h2 className="text-lg font-semibold">{title || 'Konfirmasi'}</h2>
      {typeof message === 'string' ? (
        <p className="mt-2 text-sm text-gray-600">{message || 'Yakin ingin melanjutkan?'}</p>
      ) : (
        <div className="mt-2">{message}</div>
      )}
      <div className="mt-4 flex justify-end gap-2">
        <Button variant="outline" onClick={onCancel}>
          Batal
        </Button>
        <Button variant="default" className="bg-red-600 hover:bg-red-700" onClick={onConfirm}>
          {confirmText}
        </Button>
      </div>
    </dialog>
  )
}
