import React, { useEffect, useState } from 'react';

interface Certificate {
  id: number;
  certificate_data?: any;
  created_at: string;
  user?: { id?: number; fullname?: string; email?: string };
  course?: { id?: number; title?: string };
}

interface Props { onOpenEditor?: (id:number)=>void }

export default function Certificates({ onOpenEditor }: Props) {
  const [certificates, setCertificates] = useState<Certificate[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchCerts = async () => {
      try {
        const res = await fetch('/api/admin/certificates', {
          credentials: 'include',
          headers: { 'Accept': 'application/json' }
        });
        if (res.ok) {
          const data = await res.json();
          setCertificates(data);
        } else {
          console.error('Failed to fetch certificates', res.status);
        }
      } catch (err) {
        console.error('Error fetching certificates', err);
      } finally {
        setLoading(false);
      }
    };
    fetchCerts();
  }, []);

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6 flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Certificates</h1>
          <p className="text-sm text-slate-500">Manage generated certificates and preview or download as needed.</p>
        </div>
        <div className="flex items-center space-x-4">
          <div className="bg-white p-3 rounded shadow flex items-center space-x-2">
            <select className="border rounded px-3 py-2 text-sm">
              <option>Select user</option>
            </select>
            <select className="border rounded px-3 py-2 text-sm">
              <option>Select course</option>
            </select>
            <button className="bg-green-600 text-white px-4 py-2 rounded">Generate</button>
          </div>
        </div>
      </div>

      <div className="bg-white rounded shadow overflow-hidden">
        <div className="p-4 border-b flex items-center justify-between">
          <div className="text-sm text-slate-600">Showing {certificates.length} certificates</div>
          <div>
            <input type="search" placeholder="Search by user or course" className="border rounded px-2 py-1 text-sm" />
          </div>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Completed</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Issued</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificate ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {loading ? (
                <tr><td colSpan={5} className="p-6 text-center text-slate-500">Loading...</td></tr>
              ) : certificates.length === 0 ? (
                <tr><td colSpan={5} className="p-6 text-center text-slate-500">No certificates found.</td></tr>
              ) : (
                certificates.map((cert) => (
                  <tr key={cert.id}>
                    <td className="px-6 py-4 whitespace-nowrap">{cert.user?.fullname}</td>
                    <td className="px-6 py-4 whitespace-nowrap">{cert.course?.title}</td>
                    <td className="px-6 py-4 whitespace-nowrap">{(cert.certificate_data && cert.certificate_data.issued_at) || new Date(cert.created_at).toLocaleDateString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap">{(cert.certificate_data && cert.certificate_data.certificate_id) || cert.id}</td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <a href={`/admin/certificates/${cert.id}/view`} className="text-blue-600 hover:underline mr-2">View</a>
                      <a href={`/admin/certificates/${cert.id}/download`} className="text-green-600 hover:underline mr-2">Download</a>
                      <button onClick={(e)=>{ e.preventDefault(); onOpenEditor && onOpenEditor(cert.id); }} className="text-gray-600 hover:underline">Edit</button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
