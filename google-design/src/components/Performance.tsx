import React from "react";
import { Bolt, ShieldCheck, Rocket } from "lucide-react";

export default function Performance() {
  return (
    <section className="relative py-24 px-6 max-w-7xl mx-auto space-y-12">
      {/* Title */}
      <div className="text-center">
        <h2
          className="text-3xl md:text-5xl font-bold text-slate-900 mb-4 animate-fade-in"
          style={{ fontFamily: "Space Grotesk, sans-serif" }}
        >
          Engineered for <span className="text-blue-600">Performance</span>
        </h2>
        <p className="text-base md:text-lg text-slate-600 max-w-2xl mx-auto">
          Our methodology combines cutting-edge architecture with rapid iteration cycles.
        </p>
      </div>

      {/* Grid */}
      <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        {/* Velocity Card */}
        <div className="md:col-span-7 glass-card p-10 rounded-3xl flex flex-col justify-between group relative overflow-hidden">
          <div className="absolute -top-24 -right-24 w-64 h-64 bg-blue-500/5 rounded-full blur-[80px] group-hover:bg-blue-500/10 transition-all duration-500"></div>
          <div>
            <div className="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mb-8 border border-blue-100">
              <Bolt className="w-8 h-8 text-blue-600" />
            </div>
            <h3
              className="text-2xl font-bold mb-4 text-slate-900"
              style={{ fontFamily: "Space Grotesk, sans-serif" }}
            >
              Velocity
            </h3>
            <p className="text-sm text-slate-600 max-w-md leading-relaxed">
              Our internal frameworks allow us to bypass boilerplate and focus on your core business logic from day one. Shipping faster isn't a goal; it's our standard.
            </p>
          </div>
          
          <div className="pt-8 flex items-center gap-4">
            <div className="h-1 flex-1 bg-slate-100 rounded-full overflow-hidden">
              <div className="h-full bg-blue-600 w-3/4 animate-pulse"></div>
            </div>
            <span className="text-xs font-mono font-bold text-blue-600">OPTIMIZED</span>
          </div>
        </div>

        {/* Quality Card */}
        <div className="md:col-span-5 glass-card p-10 rounded-3xl flex flex-col justify-between group">
          <div>
            <div className="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-8 border border-slate-200">
              <ShieldCheck className="w-8 h-8 text-slate-800" />
            </div>
            <h3
              className="text-2xl font-bold mb-4 text-slate-900"
              style={{ fontFamily: "Space Grotesk, sans-serif" }}
            >
              Quality
            </h3>
            <p className="text-sm text-slate-600 leading-relaxed">
              Zero compromise on code integrity. Automated testing and rigorous peer review are baked into every commit.
            </p>
          </div>
          
          <div className="flex flex-wrap gap-2 pt-6">
            <span className="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">TESTED</span>
            <span className="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">SECURE</span>
            <span className="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium font-mono">CLEAN</span>
          </div>
        </div>

        {/* Scale Card */}
        <div className="md:col-span-12 glass-card p-10 rounded-3xl flex flex-col md:flex-row items-center gap-10 group relative overflow-hidden">
          <div className="flex-1 space-y-4">
            <div className="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center border border-emerald-100">
              <Rocket className="w-8 h-8 text-emerald-600" />
            </div>
            <h3
              className="text-2xl font-bold text-slate-900"
              style={{ fontFamily: "Space Grotesk, sans-serif" }}
            >
              Scale
            </h3>
            <p className="text-sm text-slate-600 max-w-xl leading-relaxed">
              From your first 100 users to your first 10 million. We build on cloud-native architectures that expand with your success, ensuring performance never drops as traffic spikes.
            </p>
          </div>
          
          {/* Active graph mockup */}
          <div className="w-full md:w-1/3 bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-xs">
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-sm text-slate-600 font-medium">Server Load</span>
                <span className="text-emerald-600 text-xs font-semibold uppercase tracking-wider font-mono bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Efficient</span>
              </div>
              <div className="grid grid-cols-6 gap-2 items-end h-16">
                <div className="h-10 bg-emerald-600/10 rounded border-t border-emerald-600/25"></div>
                <div className="h-14 bg-emerald-600/30 rounded border-t border-emerald-600/40 animate-pulse"></div>
                <div className="h-8 bg-emerald-600/20 rounded border-t border-emerald-600/30"></div>
                <div className="h-16 bg-emerald-600/50 rounded border-t border-emerald-600/60 animate-pulse"></div>
                <div className="h-12 bg-emerald-600/30 rounded border-t border-emerald-600/40"></div>
                <div className="h-6 bg-emerald-600/15 rounded border-t border-emerald-600/20"></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  );
}
