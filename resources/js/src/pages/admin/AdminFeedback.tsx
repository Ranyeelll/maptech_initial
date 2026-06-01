import React from 'react';
import FeedbacksPage from '../../components/feedback/FeedbacksPage';

const API = '/api/admin/feedbacks';

type AdminFeedbackProps = {
  mode?: 'active' | 'archived';
  onNavigate?: (page: string) => void;
};

export function AdminFeedback({ mode = 'active', onNavigate }: AdminFeedbackProps) {
  return (
    <FeedbacksPage
      apiBase={API}
      canDelete={true}
      canArchive={true}
      mode={mode}
      onNavigate={onNavigate}
    />
  );
}

export default AdminFeedback;
