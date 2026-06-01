export const actionButtonClasses = {
  primary:
    'bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white',
  success:
    'bg-green-600 hover:bg-green-700 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white',
  info:
    'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white',
} as const;

export const statIconContainerClasses = {
  blue: 'p-3 bg-blue-50 dark:bg-blue-900/30 rounded-full',
  green: 'p-3 bg-green-50 dark:bg-green-900/30 rounded-full',
  purple: 'p-3 bg-purple-50 dark:bg-purple-900/30 rounded-full',
  orange: 'p-3 bg-orange-50 dark:bg-orange-900/30 rounded-full',
  yellow: 'p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-full',
} as const;

export const statIconGlyphClasses = {
  blue: 'h-6 w-6 text-blue-600 dark:text-blue-400',
  green: 'h-6 w-6 text-green-600 dark:text-green-400',
  purple: 'h-6 w-6 text-purple-600 dark:text-purple-400',
  orange: 'h-6 w-6 text-orange-600 dark:text-orange-400',
  yellow: 'h-6 w-6 text-yellow-600 dark:text-yellow-400',
} as const;


// Centralized chart color palettes for both admin and employee dashboards
export const chartColors = ['#34b46c', '#c8a73a', '#7f90ab'];
export const popularCourseColors = ['#2ea85f', '#3abf6f', '#60ca88'];
