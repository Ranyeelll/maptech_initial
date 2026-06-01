import React, { useEffect, useMemo, useState } from 'react';
import { Search } from 'lucide-react';
import FeedbackList from '../FeedbackList';
import useConfirm from '../../hooks/useConfirm';

type FeedbackType = 'lesson' | 'quiz';

interface FeedbacksPageProps {
  apiBase: string;
  title?: string;
  description?: string;
  canDelete?: boolean;
  canArchive?: boolean;
  mode?: 'active' | 'archived';
  onNavigate?: (page: string) => void;
  activePage?: string;
  archivedPage?: string;
}

export function FeedbacksPage({
  apiBase,
  canDelete = false,
  canArchive = false,
  mode = 'active',
  onNavigate,
  activePage = 'feedbacks',
  archivedPage = 'feedbacks-archived',
}: FeedbacksPageProps) {
  const [listMode, setListMode] = useState<'active' | 'archived'>(mode);
  const [type, setType] = useState<FeedbackType>('lesson');
  const [searchQuery, setSearchQuery] = useState('');
  const [refreshToken, setRefreshToken] = useState(0);
  const confirm = useConfirm();

  useEffect(() => {
    // Trigger list refresh whenever feedback type changes.
    setRefreshToken((prev) => prev + 1);
  }, [type]);

  const endpoint = useMemo(() => `${apiBase}?type=${type}`, [apiBase, type]);
  const archivedEndpoint = useMemo(() => `${apiBase}?type=${type}&archived=1`, [apiBase, type]);

  const handleArchiveToggle = async (item: { id: number; type?: FeedbackType }, archived: boolean) => {
    if (!canArchive) return;

    const executeToggle = async () => {
      try {
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
        const xsrf = document.cookie.match(/(^|; )XSRF-TOKEN=([^;]+)/)?.[2];
        const res = await fetch(`${apiBase}/${item.id}/archive`, {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': xsrf ? decodeURIComponent(xsrf) : '',
          },
          body: JSON.stringify({ archived, type: item.type || type }),
        });

        if (!res.ok) {
          const data = await res.json().catch(() => null);
          throw new Error(data?.message || 'Failed to update feedback');
        }

        setRefreshToken((prev) => prev + 1);
      } catch (e: any) {
        alert(e?.message || 'Failed to update feedback');
      }
    };

    confirm.showConfirm(
      archived ? 'Archive this feedback?' : 'Restore this feedback?',
      executeToggle,
      {
        title: archived ? 'Archive feedback' : 'Restore feedback',
        confirmText: archived ? 'Archive' : 'Restore',
        variant: archived ? 'danger' : 'info',
      }
    );
  };

  return (
    <div className="space-y-6 ui-pop-grid um-shell">
      <div className="bg-white p-4 rounded-xl shadow-sm border border-slate-200 dark:bg-slate-900/80 dark:border-slate-700/80 ui-pop-in ui-force-pop um-filter-panel">
        <label className="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Feedback Type</label>
        <select
          value={type}
          onChange={(e) => setType(e.target.value as FeedbackType)}
          className="block h-10 w-full pl-3 pr-10 py-2 border border-slate-300 rounded-md leading-5 bg-white text-slate-900 focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 um-filter-select ui-select-custom-arrow"
        >
          <option value="lesson">Lesson</option>
          <option value="quiz">Quiz</option>
        </select>
      </div>

      <div className="bg-white p-4 rounded-xl shadow-sm border border-slate-200 dark:bg-slate-900/80 dark:border-slate-700/80 ui-pop-in ui-force-pop um-filter-panel">
        <label className="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-2">Search in Feedback</label>
        <div className="relative flex items-center gap-3">
          <div className="relative flex-1">
            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search comments, users, or course titles"
              className="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md leading-5 bg-white text-slate-900 placeholder-slate-500 focus:outline-none focus:placeholder-slate-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-400 um-search-input"
            />
          </div>
        </div>
      </div>

      <div className="mt-4">
        <FeedbackList
          url={listMode === 'archived' ? archivedEndpoint : endpoint}
          showSelection={false}
          searchQuery={searchQuery}
          onArchiveToggle={handleArchiveToggle}
          showArchiveAction={canArchive}
          isArchivedList={listMode === 'archived'}
          refreshToken={refreshToken}
        />
      </div>

      {confirm.ConfirmModalRenderer()}
    </div>
  );
}

export default FeedbacksPage;
