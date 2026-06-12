import { cn } from '@/lib/utils';
import { Slot } from '@radix-ui/react-slot';
import { cva } from 'class-variance-authority';

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default:
                    'bg-gray-900 text-white hover:bg-gray-800',
                secondary:
                    'border border-gray-200 bg-white text-gray-900 hover:bg-gray-50',
                ghost: 'hover:bg-gray-100 text-gray-700',
                destructive:
                    'bg-red-600 text-white hover:bg-red-500',
            },
            size: {
                default: 'h-9 px-4 py-2',
                sm: 'h-8 rounded-md px-3 text-xs',
                lg: 'h-10 rounded-md px-6',
                icon: 'h-9 w-9',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

export default function Button({
    className,
    variant,
    size,
    asChild = false,
    ...props
}) {
    const Comp = asChild ? Slot : 'button';

    return (
        <Comp
            className={cn(buttonVariants({ variant, size, className }))}
            {...props}
        />
    );
}

export { buttonVariants };
