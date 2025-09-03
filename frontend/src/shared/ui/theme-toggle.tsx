import { useTheme } from '@/shared/contexts/theme-context';

export function ThemeToggle() {
  const { theme, setTheme } = useTheme();

  return (
    <button
      onClick={() => setTheme(theme === 'light' ? 'dark' : 'light')}
      className="relative inline-flex items-center justify-center rounded-full text-sm font-medium transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-lg hover:bg-accent hover:text-accent-foreground hover:shadow-xl transform hover:scale-110 h-10 w-10 group"
      aria-label="Toggle theme"
    >
      <div className="relative overflow-hidden w-5 h-5">
        {theme === 'light' ? (
          <svg
            className="h-5 w-5 transition-all duration-300 transform group-hover:rotate-12"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
          </svg>
        ) : (
          <svg
            className="h-5 w-5 transition-all duration-300 transform group-hover:rotate-180"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
            />
          </svg>
        )}
      </div>
      <div className="absolute inset-0 rounded-full bg-gradient-to-r from-primary-500/20 to-accent/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </button>
  );
}