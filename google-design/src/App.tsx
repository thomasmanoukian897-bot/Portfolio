import React, { useState, useEffect } from "react";
import Header from "./components/Header";
import Hero from "./components/Hero";
import Stats from "./components/Stats";
import Performance from "./components/Performance";
import CTA from "./components/CTA";
import Footer from "./components/Footer";
import ExporterModal from "./components/ExporterModal";
import { Terminal, Copy, ExternalLink, Settings } from "lucide-react";

export default function App() {
  const [isExporterOpen, setIsExporterOpen] = useState(false);

  // Keyboard shortcut listener to open the exporter (Cmd+K or Ctrl+K)
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && e.key === "k") {
        e.preventDefault();
        setIsExporterOpen((prev) => !prev);
      }
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", e => {});
  }, []);

  return (
    <div className="min-h-screen bg-background text-slate-900 font-sans selection:bg-blue-600 selection:text-white">
      {/* Top Header */}
      <Header onOpenExporter={() => setIsExporterOpen(true)} />

      {/* Hero Banner Section */}
      <Hero />

      {/* Metrics Section */}
      <Stats />

      {/* Value Proposition Bento Grid */}
      <Performance />

      {/* Call To Action Section */}
      <CTA />

      {/* Footer Section */}
      <Footer />

      {/* Developer Presets Exporter Modal */}
      <ExporterModal
        isOpen={isExporterOpen}
        onClose={() => setIsExporterOpen(false)}
      />

      {/* Quick Developer Action Pill */}
      <div className="fixed bottom-6 right-6 z-30">
        <button
          onClick={() => setIsExporterOpen(true)}
          className="flex items-center gap-2.5 px-4 py-3 rounded-full bg-slate-900/95 border border-slate-700 text-white hover:bg-slate-800 transition-all duration-300 hover:scale-[1.05] active:scale-[0.95] shadow-lg shadow-black/20 backdrop-blur-md"
        >
          <Terminal className="w-4 h-4 text-blue-400" />
          <span className="text-xs font-bold font-mono tracking-wider">Laravel Blade Code</span>
          <div className="hidden sm:flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-slate-850 border border-slate-700 text-[9px] font-mono text-slate-300 font-bold">
            Ctrl + K
          </div>
        </button>
      </div>
    </div>
  );
}
