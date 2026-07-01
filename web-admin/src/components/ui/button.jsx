import { forwardRef } from 'react'
import { Slot } from '@radix-ui/react-slot'
import { cn } from '@/lib/utils'

const Button = forwardRef(({ className, variant = 'default', size = 'default', asChild = false, ...props }, ref) => {
  const Comp = asChild ? Slot : 'button'
  return (
    <Comp
      className={cn(
        'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50',
        variant === 'default' && 'bg-primary text-white shadow hover:bg-primary-dark',
        variant === 'outline' && 'border border-gray-300 bg-white shadow-sm hover:bg-gray-100',
        variant === 'ghost' && 'hover:bg-gray-100',
        size === 'default' && 'h-9 px-4 py-2',
        size === 'sm' && 'h-8 rounded-md px-3 text-xs',
        size === 'lg' && 'h-10 rounded-md px-8',
        className,
      )}
      ref={ref}
      {...props}
    />
  )
})
Button.displayName = 'Button'

export { Button }
