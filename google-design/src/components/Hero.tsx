import React from "react";
import { ArrowRight, Code } from "lucide-react";

export default function Hero() {
  return (
    <section className="relative min-h-[85vh] flex flex-col items-center justify-center pt-16 overflow-hidden mesh-gradient">
      <div className="absolute inset-0 animated-grid opacity-60 pointer-events-none"></div>

      <div className="relative z-10 text-center px-6 max-w-5xl mx-auto space-y-8">
        {/* Next-Gen Badge */}
        <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-slate-200 bg-slate-50/80 shadow-xs">
          <span className="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
          <span className="text-xs text-slate-700 font-bold uppercase tracking-widest font-mono">
            Next-Gen Web Infrastructure
          </span>
        </div>

        {/* Heading */}
        <h1
          className="text-4xl sm:text-5xl md:text-7xl font-bold tracking-tighter leading-tight text-slate-900"
          style={{ fontFamily: "Space Grotesk, sans-serif" }}
        >
          We Build the <span className="gradient-text">Future of the Web</span>
        </h1>

        {/* Subheading */}
        <p className="text-base sm:text-lg md:text-xl text-slate-600 max-w-2xl mx-auto leading-relaxed">
          Engineering production-ready digital products with relentless velocity. We empower startups to scale from idea to global infrastructure in record time.
        </p>

        {/* CTA Buttons */}
        <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
          <button className="w-full sm:w-auto px-10 py-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold uppercase tracking-widest flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-sm">
            Build with us
            <ArrowRight className="w-4 h-4" />
          </button>
          <button className="w-full sm:w-auto px-10 py-4 glass-card hover:bg-slate-50 transition-colors rounded-xl text-sm font-bold uppercase tracking-widest text-slate-700 hover:scale-[1.02] active:scale-[0.98]">
            View Ecosystem
          </button>
        </div>
      </div>

      {/* Floating Code Card */}
      <div className="hidden lg:block absolute bottom-12 right-16 glass-card p-6 rounded-xl w-80 shadow-md">
        <div className="flex gap-1.5 mb-4 border-b border-slate-100 pb-3">
          <div className="w-3 h-3 rounded-full bg-red-400"></div>
          <div className="w-3 h-3 rounded-full bg-amber-400"></div>
          <div className="w-3 h-3 rounded-full bg-green-400"></div>
          <span className="text-[10px] text-slate-400 font-mono ml-2">builder.ts</span>
        </div>
        <pre className="text-xs text-slate-600 leading-relaxed" style={{ fontFamily: "JetBrains Mono, monospace" }}>
          <span className="text-blue-600 font-semibold">const</span> <span className="text-slate-800 font-medium">builder</span> = <span className="text-blue-600 font-semibold">new</span> DigitalBuilder();{"\n"}
          builder.<span className="text-emerald-600 font-medium">ship</span>({"\n"}
          {"  "}velocity: <span className="text-amber-600">"maximum"</span>,{"\n"}
          {"  "}quality: <span className="text-amber-600">"enterprise"</span>,{"\n"}
          {"  "}scale: <span className="text-amber-600">"infinite"</span>{"\n"}
          {"}"});
        </pre>
      </div>
    </section>
  );
}
