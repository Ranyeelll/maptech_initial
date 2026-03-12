export const THEME_KEY = 'maptech_theme';

export function getSavedTheme(): 'light' | 'dark' | null {
  try {
    const t = localStorage.getItem(THEME_KEY);
    if (t === 'dark' || t === 'light') return t;
  } catch (e) {}
  return null;
}

export function applyTheme(theme: 'light' | 'dark'){
  const root = document.documentElement;
  const body = document.body;
  if (theme === 'dark') {
    root.classList.add('theme-dark');
    body.classList.add('theme-dark');
    root.setAttribute('data-theme', 'dark');
  } else {
    root.classList.remove('theme-dark');
    body.classList.remove('theme-dark');
    root.setAttribute('data-theme', 'light');
  }
  try{ localStorage.setItem(THEME_KEY, theme); }catch(e){}
  // notify listeners in-page
  try{ window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } })); }catch(e){}
}

export function initTheme(){
  const saved = getSavedTheme();
  if (saved) { applyTheme(saved); return saved; }
  // fallback to prefers-color-scheme
  try{
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const def = prefersDark ? 'dark' : 'light';
    applyTheme(def);
    return def;
  }catch(e){ applyTheme('light'); return 'light'; }
}

export function toggleTheme(){
  const root = document.documentElement;
  const isDark = root.classList.contains('theme-dark');
  const next = isDark ? 'light' : 'dark';
  applyTheme(next);
  return next;
}
