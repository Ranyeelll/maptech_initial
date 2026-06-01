import React, { useState } from 'react';
import { Users, Star, Bell } from 'lucide-react';
import { UserManagement } from './UserManagement';
import AdminFeedback from './AdminFeedback';
import { NotificationManagement } from './NotificationManagement';

type ArchivesPageProps = {
  currentUserEmail?: string;
  onLogout?: () => void | Promise<void>;
  onNavigate?: (page: string) => void;
};

  type Tab = 'users' | 'feedbacks' | 'archive';

export default function ArchivesPage({ currentUserEmail, onLogout, onNavigate }: ArchivesPageProps) {
  const [tab, setTab] = useState<Tab>('users');

  const tabBtn = (id: Tab, label: string, Icon: React.ComponentType<{ className?: string }>) => (
    <button
      type="button"
      onClick={() => setTab(id)}
      className={`inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition ${
        tab === id
          ? 'bg-slate-700 text-white shadow dark:bg-slate-200 dark:text-slate-900'
          : 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100'
      }`}
    >
      <Icon className="h-4 w-4" />
      {label}
    </button>
  );

  return (
    <div className="space-y-4">
      <div className="flex items-center rounded-lg border border-slate-200 bg-slate-50 p-1 w-fit dark:border-slate-700 dark:bg-slate-800/60">
        {tabBtn('users', 'Archived Users', Users)}
        {tabBtn('feedbacks', 'Archived Feedbacks', Star)}
        {tabBtn('archive', 'Archived Notifications', Bell)}
      </div>

      {tab === 'users' && (
        <UserManagement
          currentUserEmail={currentUserEmail}
          onLogout={onLogout}
          mode="archived"
          onNavigate={onNavigate}
        />
      )}
      {tab === 'feedbacks' && (
        <AdminFeedback mode="archived" onNavigate={onNavigate} />
      )}
      {tab === 'archive' && (
        <NotificationManagement initialTab="deleted" showOnlyDeleted />
      )}
    </div>
  );
}
