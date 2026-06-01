import fs from 'fs';

const file = 'resources/js/src/pages/instructor/CourseDetail.tsx';
let c = fs.readFileSync(file, 'utf8');

const OLD = `        {course.modules.length > 1 && (
          <div className="px-6 pb-4 flex items-center gap-2 text-xs text-slate-500">
            <Lock className="h-3.5 w-3.5 text-amber-500" />
            Modules with a quiz gate the next module \u2014 employees must pass before proceeding.
          </div>
        )}
      </div>
      )}`;

const NEW = `        {course.modules.length > 1 && (
          <div className="px-6 pb-4 flex items-center gap-2 text-xs text-slate-500">
            <Lock className="h-3.5 w-3.5 text-amber-500" />
            Modules with a quiz gate the next module \u2014 employees must pass before proceeding.
          </div>
        )}

        {/* \u2500\u2500 Final Assessment Section \u2500\u2500 */}
        <div className="mx-6 mb-6 mt-2">
          <div className="rounded-xl border-2 border-dashed border-amber-300 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/10 p-5">
            <div className="flex items-center gap-2 mb-3">
              <div className="h-8 w-8 rounded-full bg-amber-100 dark:bg-amber-800/50 flex items-center justify-center flex-shrink-0">
                <Award className="h-4 w-4 text-amber-600 dark:text-amber-300" />
              </div>
              <div>
                <p className="text-sm font-bold text-amber-900 dark:text-amber-200">Final Assessment</p>
                <p className="text-xs text-amber-700 dark:text-amber-400">Taken after all modules \u2014 determines if the employee passes the course</p>
              </div>
            </div>

            {finalAssessmentQuizzes.length > 0 ? (
              <div className="space-y-2">
                {finalAssessmentQuizzes.map((fa) => (
                  <div key={fa.id} className="bg-white dark:bg-slate-800 rounded-lg border border-amber-200 dark:border-amber-700 p-3 flex items-center justify-between gap-3">
                    <div className="flex items-center gap-3">
                      <Award className="h-5 w-5 text-amber-500 flex-shrink-0" />
                      <div>
                        <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">{fa.title}</p>
                        {fa.description && <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{fa.description}</p>}
                        <p className="text-xs text-amber-700 dark:text-amber-300 mt-0.5">{fa.question_count} question{fa.question_count !== 1 ? 's' : ''} &middot; Pass {fa.pass_percentage}% to complete course</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-2 flex-shrink-0">
                      <button onClick={() => openQuizInManager(fa.id)} className="p-1.5 text-green-500 hover:text-green-700 hover:bg-green-100 dark:hover:bg-green-900/25 rounded" title="Add/edit questions">
                        <Pencil className="h-4 w-4" />
                      </button>
                      <button onClick={() => handleDeleteQuiz(fa.id)} disabled={deletingQuizId === fa.id} className="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-100 rounded disabled:opacity-40">
                        {deletingQuizId === fa.id ? <Loader2 className="h-4 w-4 animate-spin" /> : <Trash2 className="h-4 w-4" />}
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            ) : addingFinalAssessment ? (
              <FinalAssessmentForm
                onCreated={handleCreateFinalAssessment}
                onCancel={() => setAddingFinalAssessment(false)}
              />
            ) : (
              <button
                onClick={() => setAddingFinalAssessment(true)}
                className="w-full flex items-center justify-center gap-2 py-3 border-2 border-dashed border-amber-300 dark:border-amber-600 rounded-lg text-sm font-medium text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-800/30 transition-colors"
              >
                <Plus className="h-4 w-4" /> Add Final Assessment
              </button>
            )}
          </div>
        </div>
      </div>
      )}`;

if (!c.includes(OLD)) {
  console.error('NOT FOUND — searching for partial match...');
  // Try to find what's actually there
  const idx = c.indexOf('Modules with a quiz gate');
  if (idx >= 0) console.log('Partial context:', JSON.stringify(c.substring(idx - 50, idx + 200)));
  process.exit(1);
}
c = c.replace(OLD, NEW);
fs.writeFileSync(file, c, 'utf8');
console.log('Done \u2714');
