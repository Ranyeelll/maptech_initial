import React from 'react';

export const darkTooltipStyle: React.CSSProperties = {
  background: 'rgba(15, 23, 42, 0.94)',
  border: '1px solid rgba(71, 85, 105, 0.7)',
  borderRadius: 10,
  boxShadow: '0 8px 24px rgba(2, 6, 23, 0.45)',
  backdropFilter: 'blur(6px)',
  color: '#e2e8f0',
  padding: '10px 12px',
};

export const darkTooltipLabelStyle: React.CSSProperties = {
  color: '#cbd5e1',
  fontSize: 12,
  fontWeight: 500,
  marginBottom: 4,
};

export const darkTooltipItemStyle: React.CSSProperties = {
  color: '#22c55e',
  fontSize: 14,
  fontWeight: 600,
  padding: 0,
};

export const darkTooltipWrapperStyle: React.CSSProperties = {
  outline: 'none',
  zIndex: 50,
};

export const darkTooltipCursor = { fill: 'rgba(46, 168, 95, 0.08)' } as const;

type DefaultTooltipProps = {
  active?: boolean;
  payload?: any[];
  label?: any;
  valueFormatter?: (value: any, name?: string, entry?: any) => React.ReactNode;
  nameFormatter?: (name: string, entry?: any) => string;
};

export function DarkChartTooltip({
  active,
  payload,
  label,
  valueFormatter,
  nameFormatter,
}: DefaultTooltipProps) {
  if (!active || !payload || payload.length === 0) return null;

  return (
    <div style={darkTooltipStyle}>
      {label != null && <p style={darkTooltipLabelStyle}>{label}</p>}
      {payload.map((entry: any, index: number) => {
        const color = entry?.color ?? entry?.payload?.fill ?? '#22c55e';
        const name = nameFormatter ? nameFormatter(entry?.name, entry) : entry?.name;
        const value = valueFormatter ? valueFormatter(entry?.value, entry?.name, entry) : entry?.value;
        return (
          <p
            key={`${entry?.dataKey ?? entry?.name ?? index}`}
            style={{ ...darkTooltipItemStyle, color }}
          >
            {name ? `${name} : ${value}` : value}
          </p>
        );
      })}
    </div>
  );
}
