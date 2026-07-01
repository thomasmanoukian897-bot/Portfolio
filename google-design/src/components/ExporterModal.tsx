import React, { useState } from "react";
import { motion, AnimatePresence } from "motion/react";
import { laravelTemplates, LaravelTemplate } from "../laravelTemplates";
import { X, Copy, Check, FileCode, Terminal, HelpCircle, Download } from "lucide-react";

interface ExporterModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function ExporterModal({ isOpen, onClose }: ExporterModalProps) {
  const [selectedTemplateId, setSelectedTemplateId] = useState<string>("layout");
  const [copiedId, setCopiedId] = useState<string | null>(null);
  const [activeExporterTab, setActiveExporterTab] = useState<"code" | "guide">("code");

  const selectedTemplate = laravelTemplates.find(
    (t) => t.id === selectedTemplateId
  ) || laravelTemplates[0];

  const handleCopy = (code: string, id: string) => {
    navigator.clipboard.writeText(code);
    setCopiedId(id);
    setTimeout(() => setCopiedId(null), 2000);
  };

  const handleDownloadAll = () => {
    // Generate individual file download blocks or prompt the user on how they can download files
    // Since zip is complex in a purely client-side standard React, we can generate a prompt or single-file text downloads.
    // For extreme utility, we let them download the current active file as a .blade.php file!
    const blob = new Blob([selectedTemplate.code], { type: "text/plain;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    
    // Extract filename only
    const parts = selectedTemplate.filename.split("/");
    link.download = parts[parts.length - 1];
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-6 bg-black/80 backdrop-blur-md">
          {/* Backdrop Closer */}
          <div className="absolute inset-0" onClick={onClose}></div>

          {/* Modal Container */}
          <motion.div
            initial={{ opacity: 0, scale: 0.95, y: 20 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.95, y: 20 }}
            transition={{ type: "spring", duration: 0.4 }}
            className="relative w-full max-w-5xl h-[85vh] md:h-[80vh] bg-white border border-slate-200 rounded-3xl overflow-hidden flex flex-col shadow-xl z-10"
          >
            {/* Modal Header */}
            <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-b border-slate-200">
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center border border-blue-200">
                  <Terminal className="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900 font-display">Laravel & Blade Template Exporter</h3>
                  <p className="text-xs text-slate-500 font-mono font-medium">Convert layouts directly for your web app</p>
                </div>
              </div>
              
              <button
                onClick={onClose}
                className="p-1.5 rounded-lg hover:bg-slate-200 transition-colors text-slate-500 hover:text-slate-900"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Selection Toolbar / Mode Toggles */}
            <div className="flex flex-wrap items-center justify-between gap-4 px-6 py-3 bg-white border-b border-slate-150">
              <div className="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                <button
                  onClick={() => setActiveExporterTab("code")}
                  className={`flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${
                    activeExporterTab === "code"
                      ? "bg-slate-900 text-white shadow-xs"
                      : "text-slate-600 hover:text-slate-950"
                  }`}
                >
                  <FileCode className="w-3.5 h-3.5" />
                  Blade Templates
                </button>
                <button
                  onClick={() => setActiveExporterTab("guide")}
                  className={`flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${
                    activeExporterTab === "guide"
                      ? "bg-slate-900 text-white shadow-xs"
                      : "text-slate-600 hover:text-slate-950"
                  }`}
                >
                  <HelpCircle className="w-3.5 h-3.5" />
                  Setup Guide
                </button>
              </div>

              {activeExporterTab === "code" && (
                <div className="flex items-center gap-2">
                  <span className="text-xs text-slate-500 font-mono font-bold hidden sm:inline">Active File:</span>
                  <select
                    value={selectedTemplateId}
                    onChange={(e) => setSelectedTemplateId(e.target.value)}
                    className="bg-white border border-slate-250 text-xs text-slate-800 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-600"
                  >
                    {laravelTemplates.map((t) => (
                      <option key={t.id} value={t.id}>
                        {t.name} ({t.id === "tailwindv3" || t.id === "tailwindv4" ? "Config" : "Blade"})
                      </option>
                    ))}
                  </select>
                </div>
              )}
            </div>

            {/* Main Modal Body */}
            <div className="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50">
              {activeExporterTab === "code" ? (
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full items-stretch">
                  {/* Left-hand selection list for Desktop */}
                  <div className="hidden lg:block lg:col-span-3 space-y-2 max-h-[50vh] overflow-y-auto pr-2">
                    {laravelTemplates.map((t) => (
                      <button
                        key={t.id}
                        onClick={() => setSelectedTemplateId(t.id)}
                        className={`w-full text-left p-3 rounded-xl transition-all border ${
                          selectedTemplateId === t.id
                            ? "bg-blue-50 border-blue-200 text-blue-700"
                            : "bg-white border-slate-200 hover:bg-slate-100 text-slate-600"
                        }`}
                      >
                        <div className="font-bold text-xs text-slate-800 mb-0.5">{t.name}</div>
                        <div className="text-[10px] font-mono opacity-80 overflow-hidden text-ellipsis whitespace-nowrap">
                          {t.filename}
                        </div>
                      </button>
                    ))}
                  </div>

                  {/* Active Template Code Preview Column */}
                  <div className="lg:col-span-9 flex flex-col space-y-4">
                    <div className="bg-white p-4 rounded-2xl border border-slate-200 space-y-2 shadow-xs">
                      <div className="flex flex-wrap items-center justify-between gap-2">
                        <span className="text-xs font-mono text-blue-600 font-bold">
                          {selectedTemplate.filename}
                        </span>
                        <div className="flex items-center gap-2">
                          <button
                            onClick={handleDownloadAll}
                            className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 transition-all"
                            title="Download this file"
                          >
                            <Download className="w-3.5 h-3.5" />
                            <span>Download</span>
                          </button>
                          
                          <button
                            onClick={() => handleCopy(selectedTemplate.code, selectedTemplate.id)}
                            className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-white hover:bg-slate-850 transition-all shadow-xs"
                          >
                            {copiedId === selectedTemplate.id ? (
                              <>
                                <Check className="w-3.5 h-3.5" />
                                <span>Copied!</span>
                              </>
                            ) : (
                              <>
                                <Copy className="w-3.5 h-3.5" />
                                <span>Copy Code</span>
                              </>
                            )}
                          </button>
                        </div>
                      </div>
                      <p className="text-xs text-slate-600">{selectedTemplate.description}</p>
                    </div>

                    {/* Preformatted Code block - Dark slate preview for high developer code visibility */}
                    <div className="flex-1 bg-slate-950 rounded-2xl border border-slate-800 relative p-4 max-h-[35vh] md:max-h-[42vh] overflow-auto">
                      <pre className="text-xs text-slate-300 leading-relaxed select-all" style={{ fontFamily: "JetBrains Mono, monospace" }}>
                        <code>{selectedTemplate.code}</code>
                      </pre>
                    </div>
                  </div>
                </div>
              ) : (
                /* Laravel setup guide view */
                <div className="max-w-3xl mx-auto space-y-8 py-4">
                  <div className="space-y-2">
                    <h4 className="text-lg font-bold text-slate-900 font-display">How to Integrate with your Laravel Project</h4>
                    <p className="text-sm text-slate-600 font-medium">
                      Follow these structured instructions to copy the premium Digital Builder template assets into your local Laravel app.
                    </p>
                  </div>

                  <div className="space-y-6">
                    {/* Step 1 */}
                    <div className="flex gap-4 items-start">
                      <div className="w-8 h-8 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        1
                      </div>
                      <div className="space-y-1.5">
                        <h5 className="font-semibold text-slate-800 text-sm">Create layout & Blade files</h5>
                        <p className="text-xs text-slate-600 leading-relaxed">
                          In your Laravel directory, navigate to <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">resources/views</code>. Create the master shell file at <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">layouts/app.blade.php</code> and the core landing page view at <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">index.blade.php</code>.
                        </p>
                      </div>
                    </div>

                    {/* Step 2 */}
                    <div className="flex gap-4 items-start">
                      <div className="w-8 h-8 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        2
                      </div>
                      <div className="space-y-1.5">
                        <h5 className="font-semibold text-slate-800 text-sm">Organize Blade Components</h5>
                        <p className="text-xs text-slate-600 leading-relaxed">
                          Inside <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">resources/views/components</code>, create the partials: <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">nav.blade.php</code>, <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">hero.blade.php</code>, <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">stats.blade.php</code>, <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">performance.blade.php</code>, <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">cta.blade.php</code>, and <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">footer.blade.php</code>.
                        </p>
                      </div>
                    </div>

                    {/* Step 3 */}
                    <div className="flex gap-4 items-start">
                      <div className="w-8 h-8 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        3
                      </div>
                      <div className="space-y-1.5">
                        <h5 className="font-semibold text-slate-800 text-sm">Setup Tailwind CSS config</h5>
                        <p className="text-xs text-slate-600 leading-relaxed">
                          For Tailwind v4 (standard in Laravel 11+ with Vite presets), copy and append the theme parameters inside <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">resources/css/app.css</code>. For Tailwind v3, replace your local <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">tailwind.config.js</code> parameters.
                        </p>
                      </div>
                    </div>

                    {/* Step 4 */}
                    <div className="flex gap-4 items-start">
                      <div className="w-8 h-8 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        4
                      </div>
                      <div className="space-y-1.5">
                        <h5 className="font-semibold text-slate-800 text-sm">Configure routing</h5>
                        <p className="text-xs text-slate-600 leading-relaxed">
                          Map a route in <code className="font-mono text-blue-600 bg-blue-50 px-1 rounded border border-blue-100">routes/web.php</code> to render the view:
                        </p>
                        <div className="bg-slate-900 p-3 rounded-lg border border-slate-800 font-mono text-[11px] text-slate-300 leading-relaxed">
                          {`Route::get('/', function () {\n    return view('index');\n});`}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>

            {/* Modal Footer */}
            <div className="flex justify-between items-center px-6 py-4 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 font-medium">
              <span>All assets are designed to render cleanly using standard Blade template compilation.</span>
              <span className="hidden sm:inline">Digital Builder Exporter • Laravel presets</span>
            </div>
          </motion.div>
        </div>
      )}
    </AnimatePresence>
  );
}
