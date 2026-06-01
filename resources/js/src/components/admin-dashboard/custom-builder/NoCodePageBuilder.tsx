import { useMemo, useState } from 'react';
import {
  AlignCenter,
  AlignLeft,
  AlignRight,
  AlertTriangle,
  ArrowRight,
  Bell,
  Blocks,
  BookOpen,
  Calendar,
  Check,
  CheckCircle2,
  ChevronRight,
  Clipboard,
  ExternalLink,
  Eye,
  FileText,
  GripVertical,
  Home,
  Info,
  LayoutDashboard,
  Mail,
  MessageCircle,
  Plus,
  Settings,
  Star,
  Target,
  Trash2,
  TrendingUp,
  Type,
  Users,
  X,
  Zap,
} from 'lucide-react';
import {
  BUTTON_VARIANTS,
  BuilderButtonStyle,
  BuilderConfig,
  BuilderElement,
  STANDARD_BUTTON_SIZE,
} from './builderSchema';

interface NoCodePageBuilderProps {
  value: BuilderConfig;
  onChange: (next: BuilderConfig) => void;
  sidebarIconName?: string;
}

// All available icons for the visual picker
export const iconChoices = [
  { name: 'Plus', Icon: Plus },
  { name: 'ArrowRight', Icon: ArrowRight },
  { name: 'Calendar', Icon: Calendar },
  { name: 'Bell', Icon: Bell },
  { name: 'Settings', Icon: Settings },
  { name: 'Star', Icon: Star },
  { name: 'FileText', Icon: FileText },
  { name: 'Users', Icon: Users },
  { name: 'Home', Icon: Home },
  { name: 'LayoutDashboard', Icon: LayoutDashboard },
  { name: 'Clipboard', Icon: Clipboard },
  { name: 'BookOpen', Icon: BookOpen },
  { name: 'Mail', Icon: Mail },
  { name: 'Zap', Icon: Zap },
  { name: 'Check', Icon: Check },
  { name: 'Blocks', Icon: Blocks },
  { name: 'Info', Icon: Info },
  { name: 'ExternalLink', Icon: ExternalLink },
  { name: 'Target', Icon: Target },
  { name: 'TrendingUp', Icon: TrendingUp },
  { name: 'MessageCircle', Icon: MessageCircle },
  { name: 'CheckCircle2', Icon: CheckCircle2 },
  { name: 'AlertTriangle', Icon: AlertTriangle },
  { name: 'ChevronRight', Icon: ChevronRight },
];

// Button type palette - each has a different function/purpose
const BUTTON_PALETTE: {
  type: 'button' | 'icon_button';
  style: BuilderButtonStyle;
  label: string;
  desc: string;
  previewClass: string;
}[] = [
  { type: 'button', style: 'primary', label: 'Primary', desc: 'Main action', previewClass: 'bg-green-600 text-white' },
  { type: 'button', style: 'secondary', label: 'Secondary', desc: 'Alternative', previewClass: 'border border-slate-400 text-slate-700 dark:text-slate-200' },
  { type: 'button', style: 'ghost', label: 'Ghost', desc: 'Subtle', previewClass: 'text-slate-600' },
  { type: 'button', style: 'danger', label: 'Danger', desc: 'Delete / Remove', previewClass: 'bg-red-600 text-white' },
  { type: 'button', style: 'warning', label: 'Warning', desc: 'Caution', previewClass: 'bg-amber-500 text-white' },
  { type: 'button', style: 'info', label: 'Info', desc: 'Informational', previewClass: 'bg-blue-600 text-white' },
  { type: 'button', style: 'success', label: 'Success', desc: 'Confirm / Save', previewClass: 'bg-emerald-600 text-white' },
  { type: 'button', style: 'link', label: 'Link', desc: 'Navigate / URL', previewClass: 'text-blue-600 underline' },
  { type: 'icon_button', style: 'primary', label: 'Icon Primary', desc: 'With icon', previewClass: 'bg-green-600 text-white' },
  { type: 'icon_button', style: 'secondary', label: 'Icon Secondary', desc: 'With icon', previewClass: 'border border-slate-400 text-slate-700 dark:text-slate-200' },
];

const alignClass = (align: BuilderElement['align']) => {
  if (align === 'center') return 'justify-center';
  if (align === 'right') return 'justify-end';
  return 'justify-start';
};

const resolveIcon = (iconName: string | undefined) => {
  const found = iconChoices.find((c) => c.name === iconName);
  return found?.Icon || Plus;
};

const makeElementId = () => `el-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

// Simulated sidebar nav icons for the preview chrome
const PREVIEW_NAV_ICONS = [Home, LayoutDashboard, Users, BookOpen, Bell];

export function NoCodePageBuilder({ value, onChange, sidebarIconName }: NoCodePageBuilderProps) {
  const [draggingId, setDraggingId] = useState<string | null>(null);
  const [iconPickerFor, setIconPickerFor] = useState<string | null>(null);

  const elements = value.elements || [];

  const updateHero = (key: 'title' | 'description', heroValue: string) => {
    onChange({ ...value, hero: { ...value.hero, [key]: heroValue } });
  };

  const addElement = (type: BuilderElement['type'], style?: BuilderButtonStyle) => {
    const defaults: Record<BuilderElement['type'], Partial<BuilderElement>> = {
      text: { text: 'Add your text here', align: 'left' },
      button: { label: 'Button Label', url: '', style: style || 'primary', align: 'left' },
      icon_button: { label: 'Icon Button', url: '', style: style || 'secondary', icon: 'Plus', align: 'left' },
    };
    onChange({
      ...value,
      elements: [
        ...elements,
        { id: makeElementId(), type, ...defaults[type] } as BuilderElement,
      ],
    });
  };

  const updateElement = (id: string, patch: Partial<BuilderElement>) => {
    onChange({ ...value, elements: elements.map((el) => (el.id === id ? { ...el, ...patch } : el)) });
  };

  const removeElement = (id: string) => {
    onChange({ ...value, elements: elements.filter((el) => el.id !== id) });
  };

  const moveElement = (fromId: string, toId: string) => {
    if (fromId === toId) return;
    const fromIdx = elements.findIndex((el) => el.id === fromId);
    const toIdx = elements.findIndex((el) => el.id === toId);
    if (fromIdx < 0 || toIdx < 0) return;
    const next = [...elements];
    const [moved] = next.splice(fromIdx, 1);
    next.splice(toIdx, 0, moved);
    onChange({ ...value, elements: next });
  };

  const ActivePageIcon = useMemo(() => {
    const found = iconChoices.find((c) => c.name === sidebarIconName);
    return found?.Icon || Blocks;
  }, [sidebarIconName]);

  const preview = useMemo(
    () => (
      <div
        className="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-xl bg-white dark:bg-slate-900"
        style={{ minHeight: '340px' }}
      >
        {/* Browser top bar */}
        <div className="flex items-center gap-3 px-3 py-2 bg-slate-800 dark:bg-slate-950">
          <div className="flex gap-1.5">
            <span className="w-2.5 h-2.5 rounded-full bg-red-500/80" />
            <span className="w-2.5 h-2.5 rounded-full bg-yellow-400/80" />
            <span className="w-2.5 h-2.5 rounded-full bg-green-400/80" />
          </div>
          <span className="flex-1 text-center text-xs text-slate-400 font-mono truncate">
            /{(value.hero.title || 'custom-page').toLowerCase().replace(/\s+/g, '-')}
          </span>
        </div>

        {/* App body */}
        <div className="flex" style={{ minHeight: '300px' }}>
          {/* Mini sidebar */}
          <div className="w-12 bg-slate-900 dark:bg-slate-950 flex flex-col items-center py-3 gap-3 shrink-0">
            <div className="w-7 h-7 rounded-md bg-green-600 flex items-center justify-center shrink-0">
              <span className="text-white text-xs font-extrabold leading-none">M</span>
            </div>
            <div className="w-6 h-px bg-slate-700" />
            {PREVIEW_NAV_ICONS.map((Icon, i) => (
              <div key={i} className="w-8 h-8 rounded-lg flex items-center justify-center text-slate-600">
                <Icon className="h-3.5 w-3.5" />
              </div>
            ))}
            <div className="w-8 h-8 rounded-lg bg-green-600/20 flex items-center justify-center text-green-400">
              <ActivePageIcon className="h-3.5 w-3.5" />
            </div>
          </div>

          {/* Page content */}
          <div className="flex-1 p-3 bg-slate-50 dark:bg-slate-800 overflow-auto">
            <div className="rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 px-4 py-4 text-white mb-3">
              <h4 className="text-base font-bold leading-tight">{value.hero.title || 'Untitled Page'}</h4>
              <p className="text-xs text-cyan-100 mt-1 leading-snug">
                {value.hero.description || 'No description set'}
              </p>
            </div>

            <div className="space-y-2">
              {elements.length === 0 && (
                <div className="rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-600 p-5 text-center">
                  <p className="text-xs text-slate-400">Add components to build your page</p>
                </div>
              )}
              {elements.map((el) => {
                if (el.type === 'text') {
                  return (
                    <div key={el.id} className={`flex ${alignClass(el.align)}`}>
                      <p className="text-xs text-slate-700 dark:text-slate-200 leading-relaxed">
                        {el.text || 'Text block'}
                      </p>
                    </div>
                  );
                }
                const Icon = resolveIcon(el.icon);
                const variantClass = BUTTON_VARIANTS[el.style || 'primary'];
                const btnClass = `${STANDARD_BUTTON_SIZE} ${variantClass} text-xs h-8 min-w-0 px-3`;
                return (
                  <div key={el.id} className={`flex ${alignClass(el.align)}`}>
                    <button type="button" className={btnClass}>
                      {el.type === 'icon_button' && <Icon className="h-3.5 w-3.5 shrink-0" />}
                      <span>{el.label || 'Button'}</span>
                    </button>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    ),
    [elements, value.hero.description, value.hero.title, ActivePageIcon]
  );

  return (
    <div className="space-y-4">
      <div className="grid grid-cols-5 gap-4" style={{ minHeight: '340px' }}>

        {/* Left: settings + add elements */}
        <div className="col-span-2 space-y-3 flex flex-col">
          <div>
            <label className="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1">
              Page Title
            </label>
            <input
              type="text"
              value={value.hero.title}
              onChange={(e) => updateHero('title', e.target.value)}
              className="w-full px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-green-500 text-sm"
            />
          </div>
          <div>
            <label className="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1">
              Page Description
            </label>
            <textarea
              value={value.hero.description}
              onChange={(e) => updateHero('description', e.target.value)}
              rows={2}
              className="w-full px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-green-500 text-sm resize-none"
            />
          </div>

          <div className="flex-1">
            <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
              Add Elements
            </p>
            <button
              type="button"
              onClick={() => addElement('text')}
              className="w-full mb-2 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors"
            >
              <Type className="h-4 w-4 text-slate-400 shrink-0" />
              <span className="font-medium">Text</span>
              <span className="ml-auto text-xs text-slate-400">paragraph</span>
            </button>
            <div className="grid grid-cols-2 gap-1.5">
              {BUTTON_PALETTE.map((def) => (
                <button
                  key={`${def.type}-${def.style}`}
                  type="button"
                  onClick={() => addElement(def.type, def.style)}
                  title={def.desc}
                  className="inline-flex flex-col items-start gap-0.5 px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left"
                >
                  <span className={`text-xs font-semibold px-1.5 py-0.5 rounded ${def.previewClass}`}>
                    {def.label}
                  </span>
                  <span className="text-xs text-slate-400 leading-tight">{def.desc}</span>
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Right: live preview */}
        <div className="col-span-3 flex flex-col">
          <div className="flex items-center gap-1.5 mb-2">
            <Eye className="h-3.5 w-3.5 text-slate-400" />
            <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
              Live Preview
            </p>
          </div>
          {preview}
        </div>
      </div>

      {/* Canvas */}
      <div>
        <p className="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">
          Canvas <span className="font-normal normal-case text-slate-400">(drag to reorder)</span>
        </p>
        <div className="space-y-2">
          {elements.length === 0 && (
            <div className="rounded-lg border-2 border-dashed border-slate-200 dark:border-slate-700 p-4 text-center">
              <p className="text-sm text-slate-400">No elements yet. Use the buttons above to add some.</p>
            </div>
          )}
          {elements.map((el, index) => (
            <div
              key={el.id}
              draggable
              onDragStart={() => setDraggingId(el.id)}
              onDragOver={(e) => e.preventDefault()}
              onDrop={() => {
                if (draggingId) moveElement(draggingId, el.id);
                setDraggingId(null);
              }}
              className="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3"
            >
              <div className="flex items-center justify-between gap-2 mb-2">
                <div className="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                  <GripVertical className="h-4 w-4 text-slate-400 cursor-grab" />
                  <span>
                    {index + 1}.{' '}
                    {el.type === 'icon_button' ? 'Icon Button' : el.type === 'button' ? 'Button' : 'Text'}
                  </span>
                  {el.type !== 'text' && el.style && (
                    <span
                      className={`text-xs px-1.5 py-0.5 rounded font-medium ${
                        BUTTON_PALETTE.find((d) => d.type === el.type && d.style === el.style)
                          ?.previewClass ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'
                      }`}
                    >
                      {el.style}
                    </span>
                  )}
                </div>
                <button
                  type="button"
                  onClick={() => removeElement(el.id)}
                  className="inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-700 transition-colors"
                >
                  <Trash2 className="h-3.5 w-3.5" /> Remove
                </button>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                {el.type === 'text' ? (
                  <input
                    type="text"
                    value={el.text || ''}
                    onChange={(e) => updateElement(el.id, { text: e.target.value })}
                    className="md:col-span-2 px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm"
                    placeholder="Text content"
                  />
                ) : (
                  <>
                    <input
                      type="text"
                      value={el.label || ''}
                      onChange={(e) => updateElement(el.id, { label: e.target.value })}
                      className="px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm"
                      placeholder="Button label"
                    />
                    <input
                      type="url"
                      value={el.url || ''}
                      onChange={(e) => updateElement(el.id, { url: e.target.value })}
                      className="px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm"
                      placeholder="URL (optional)"
                    />
                    <select
                      value={el.style || 'primary'}
                      onChange={(e) => updateElement(el.id, { style: e.target.value as BuilderButtonStyle })}
                      className="px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm"
                    >
                      <option value="primary">Primary — Main action</option>
                      <option value="secondary">Secondary — Alternative</option>
                      <option value="ghost">Ghost — Subtle</option>
                      <option value="danger">Danger — Delete/Remove</option>
                      <option value="warning">Warning — Caution</option>
                      <option value="info">Info — Informational</option>
                      <option value="success">Success — Confirm/Save</option>
                      <option value="link">Link — Navigate/URL</option>
                    </select>

                    {el.type === 'icon_button' && (
                      <div className="relative">
                        <button
                          type="button"
                          onClick={() => setIconPickerFor(iconPickerFor === el.id ? null : el.id)}
                          className="w-full px-2.5 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm flex items-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors"
                        >
                          {(() => {
                            const PickIcon = resolveIcon(el.icon);
                            return <PickIcon className="h-4 w-4 shrink-0 text-slate-500" />;
                          })()}
                          <span className="flex-1 text-left">{el.icon || 'Plus'}</span>
                          <ChevronRight className="h-3.5 w-3.5 text-slate-400" />
                        </button>

                        {iconPickerFor === el.id && (
                          <div className="absolute z-20 top-full mt-1 left-0 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl p-2 min-w-[220px]">
                            <div className="flex items-center justify-between mb-2 px-1">
                              <span className="text-xs font-semibold text-slate-600 dark:text-slate-300">
                                Choose Icon
                              </span>
                              <button
                                type="button"
                                onClick={() => setIconPickerFor(null)}
                                className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                              >
                                <X className="h-3.5 w-3.5" />
                              </button>
                            </div>
                            <div className="grid grid-cols-6 gap-1">
                              {iconChoices.map((choice) => (
                                <button
                                  key={choice.name}
                                  type="button"
                                  title={choice.name}
                                  onClick={() => {
                                    updateElement(el.id, { icon: choice.name });
                                    setIconPickerFor(null);
                                  }}
                                  className={`p-2 rounded-lg flex items-center justify-center transition-colors ${
                                    el.icon === choice.name
                                      ? 'bg-green-100 dark:bg-green-900/30 text-green-600'
                                      : 'hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400'
                                  }`}
                                >
                                  <choice.Icon className="h-4 w-4" />
                                </button>
                              ))}
                            </div>
                          </div>
                        )}
                      </div>
                    )}
                  </>
                )}

                <div className="md:col-span-2 flex items-center gap-1.5">
                  <span className="text-xs text-slate-400">Align</span>
                  <button
                    type="button"
                    onClick={() => updateElement(el.id, { align: 'left' })}
                    className={`p-1.5 rounded transition-colors ${el.align === 'left' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600' : 'text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'}`}
                  ><AlignLeft className="h-3.5 w-3.5" /></button>
                  <button
                    type="button"
                    onClick={() => updateElement(el.id, { align: 'center' })}
                    className={`p-1.5 rounded transition-colors ${el.align === 'center' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600' : 'text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'}`}
                  ><AlignCenter className="h-3.5 w-3.5" /></button>
                  <button
                    type="button"
                    onClick={() => updateElement(el.id, { align: 'right' })}
                    className={`p-1.5 rounded transition-colors ${el.align === 'right' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600' : 'text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'}`}
                  ><AlignRight className="h-3.5 w-3.5" /></button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
