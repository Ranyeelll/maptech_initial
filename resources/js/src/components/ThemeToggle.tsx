import React, { useEffect, useState } from 'react';
import { Sun, Moon } from 'lucide-react';
import { getSavedTheme, toggleTheme } from '../theme';

export function ThemeToggle() {
  const [theme, setTheme] = useState<'light'|'dark'>(() => {
    const saved = getSavedTheme();
    return saved || (typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  });

  useEffect(() => {
    // keep local state in sync if theme changed elsewhere (storage or event)
    const handler = (e?: Event) => {
      const root = document.documentElement;
      setTheme(root.classList.contains('theme-dark') || root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');
    };
    window.addEventListener('themechange', handler as EventListener);
    const storageHandler = (e: StorageEvent) => {
      if (e.key === THEME_KEY) handler();
    };
    window.addEventListener('storage', storageHandler);
    return () => { window.removeEventListener('themechange', handler as EventListener); window.removeEventListener('storage', storageHandler); };
  }, []);

  const onToggle = () => {
    const next = toggleTheme();
    setTheme(next);
  };

  return (
    <button
      onClick={onToggle}
      title={theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'}
      className="p-1 rounded-md bg-white text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center"
    >
      {theme === 'dark' ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
      <span className="sr-only">Toggle theme</span>
    </button>
  );
}
