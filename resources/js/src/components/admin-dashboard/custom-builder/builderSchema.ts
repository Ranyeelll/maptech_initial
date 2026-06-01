export type BuilderElementType = 'text' | 'button' | 'icon_button';

export type BuilderButtonStyle = 'primary' | 'secondary' | 'ghost' | 'danger' | 'warning' | 'info' | 'success' | 'link';

export interface BuilderElement {
  id: string;
  type: BuilderElementType;
  text?: string;
  label?: string;
  url?: string;
  style?: BuilderButtonStyle;
  icon?: string;
  align?: 'left' | 'center' | 'right';
}

export interface BuilderConfig {
  builder_version: 2;
  hero: {
    title: string;
    description: string;
  };
  elements: BuilderElement[];
}

export const STANDARD_BUTTON_SIZE = 'h-10 min-w-[120px] px-4 rounded-lg text-sm font-semibold inline-flex items-center justify-center gap-2 transition-colors';

export const BUTTON_VARIANTS: Record<BuilderButtonStyle, string> = {
  primary: 'bg-green-600 hover:bg-green-700 text-white',
  secondary: 'border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700',
  ghost: 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700',
  danger: 'bg-red-600 hover:bg-red-700 text-white',
  warning: 'bg-amber-500 hover:bg-amber-600 text-white',
  info: 'bg-blue-600 hover:bg-blue-700 text-white',
  success: 'bg-emerald-600 hover:bg-emerald-700 text-white',
  link: 'text-blue-600 hover:text-blue-700 underline bg-transparent min-w-0',
};

const makeId = () => `el-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

export const createDefaultBuilderConfig = (title = '', description = ''): BuilderConfig => ({
  builder_version: 2,
  hero: {
    title: title || 'New Custom Page',
    description: description || 'Create this page without writing code.',
  },
  elements: [
    {
      id: makeId(),
      type: 'text',
      text: 'Welcome message',
      align: 'left',
    },
    {
      id: makeId(),
      type: 'button',
      label: 'Primary Action',
      url: '',
      style: 'primary',
      align: 'left',
    },
  ],
});

export const normalizeBuilderConfig = (
  input: Record<string, any> | null | undefined,
  title = '',
  description = ''
): BuilderConfig => {
  if (input && input.builder_version === 2 && Array.isArray(input.elements)) {
    return {
      builder_version: 2,
      hero: {
        title: String(input.hero?.title || title || 'New Custom Page'),
        description: String(input.hero?.description || description || 'Create this page without writing code.'),
      },
      elements: input.elements.map((el: any) => ({
        id: typeof el.id === 'string' && el.id ? el.id : makeId(),
        type: (el.type || 'text') as BuilderElementType,
        text: typeof el.text === 'string' ? el.text : '',
        label: typeof el.label === 'string' ? el.label : '',
        url: typeof el.url === 'string' ? el.url : '',
        style: (el.style || 'primary') as BuilderButtonStyle,
        icon: typeof el.icon === 'string' ? el.icon : 'Plus',
        align: (el.align || 'left') as 'left' | 'center' | 'right',
      })),
    };
  }

  if (input && Array.isArray(input.buttons)) {
    const buttonElements: BuilderElement[] = input.buttons
      .filter((btn: any) => btn.visible !== false)
      .map((btn: any) => ({
        id: makeId(),
        type: 'button' as const,
        label: String(btn.label || 'Action'),
        url: String(btn.url || ''),
        style: (btn.style || 'primary') as BuilderButtonStyle,
        align: 'left' as const,
      }));

    return {
      builder_version: 2,
      hero: {
        title: title || 'New Custom Page',
        description: description || 'Create this page without writing code.',
      },
      elements: buttonElements.length > 0
        ? buttonElements
        : [{ id: makeId(), type: 'text', text: 'Welcome message', align: 'left' }],
    };
  }

  return createDefaultBuilderConfig(title, description);
};
