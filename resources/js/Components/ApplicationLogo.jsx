export default function ApplicationLogo({ className, alt = 'Task Kitty', ...props }) {
    return (
        <img
            src="/appicon.png"
            alt={alt}
            className={className}
            {...props}
        />
    );
}
