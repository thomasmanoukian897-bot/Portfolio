import React from "react";
import { Terminal, Code2 } from "lucide-react";

interface HeaderProps {
  onOpenExporter: () => void;
}

export default function Header({ onOpenExporter }: HeaderProps) {
  return (
    <nav className="sticky top-0 w-full z-40 bg-white/80 backdrop-blur-xl border-b border-slate-200 shadow-sm">
      <div className="flex justify-between items-center px-6 md:px-16 py-4 max-w-7xl mx-auto">
        {/* Logo */}
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <Code2 className="w-6 h-6 text-primary" />
          </div>
          <span className="text-xl font-bold text-slate-900 tracking-tight" style={{ fontFamily: "Space Grotesk, sans-serif" }}>
            Digital Builder
          </span>
        </div>

        {/* Links */}
        <div className="hidden md:flex items-center gap-8">
          <a className="text-sm font-semibold text-primary border-b-2 border-primary pb-1 transition-all" href="#">
            Home
          </a>
          <a className="text-sm font-semibold text-on-surface-variant hover:text-slate-900 transition-colors duration-200" href="#">
            Services
          </a>
          <a className="text-sm font-semibold text-on-surface-variant hover:text-slate-900 transition-colors duration-200" href="#">
            Portfolio
          </a>
        </div>

        {/* Action Buttons */}
        <div className="flex items-center gap-3">
          {/* Laravel Exporter button */}
          <button
            onClick={onOpenExporter}
            className="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-all hover:scale-[1.02] active:scale-[0.98]"
            title="Export Laravel Blade & Tailwind Files"
          >
            <Terminal className="w-4 h-4" />
            <span className="hidden sm:inline">Laravel Exporter</span>
            <span className="sm:hidden">Blade</span>
          </button>

          <button className="active:scale-95 transition-transform btn-gradient text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider">
            Start a Project
          </button>
        </div>
      </div>
    </nav>
  );
}
