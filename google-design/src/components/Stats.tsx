import React from "react";

export default function Stats() {
  const statsList = [
    { value: "120+", label: "Projects Shipped", borderGlow: "hover:border-blue-300 hover:shadow-md" },
    { value: "99.9%", label: "Uptime Record", borderGlow: "hover:border-slate-400 hover:shadow-md" },
    { value: "8yr+", label: "Expertise", borderGlow: "hover:border-blue-300 hover:shadow-md" },
    { value: "$40M+", label: "Raised by clients", borderGlow: "hover:border-slate-400 hover:shadow-md" },
  ];

  return (
    <section className="py-20 px-6 max-w-7xl mx-auto">
      <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
        {statsList.map((stat, i) => (
          <div
            key={i}
            className={`glass-card p-8 rounded-2xl text-center group transition-all duration-300 ${stat.borderGlow}`}
          >
            <div
              className="text-4xl md:text-5xl font-bold gradient-text mb-1"
              style={{ fontFamily: "Space Grotesk, sans-serif" }}
            >
              {stat.value}
            </div>
            <div className="text-xs text-on-surface-variant uppercase tracking-widest font-mono font-medium">
              {stat.label}
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}
