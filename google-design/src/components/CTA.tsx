import React from "react";

export default function CTA() {
  return (
    <section className="py-24 px-6 max-w-7xl mx-auto animate-fade-in">
      <div className="relative bg-slate-900 p-12 md:p-24 rounded-[40px] text-center border border-slate-800 overflow-hidden shadow-lg">
        {/* Subtle Background Radial Pattern */}
        <div className="absolute -top-32 -left-32 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px]"></div>
        <div className="absolute -bottom-32 -right-32 w-96 h-96 bg-slate-800/20 rounded-full blur-[100px]"></div>

        {/* Heading */}
        <h2
          className="relative z-10 text-4xl md:text-6xl font-bold mb-8 leading-tight text-white"
          style={{ fontFamily: "Space Grotesk, sans-serif" }}
        >
          Ready to build the <br className="hidden md:block" /> next big thing?
        </h2>

        {/* Subtitle */}
        <p className="relative z-10 text-base md:text-lg text-slate-300 max-w-2xl mx-auto mb-12">
          Join 50+ startups who have scaled their vision with Digital Builder. Our team is ready to deploy.
        </p>

        {/* Buttons */}
        <div className="relative z-10 flex flex-col md:flex-row items-center justify-center gap-6">
          <button className="w-full md:w-auto px-12 py-5 bg-white hover:bg-slate-100 text-slate-900 rounded-2xl text-sm font-extrabold uppercase tracking-widest shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all">
            Schedule a Consultation
          </button>
          <a
            className="text-blue-400 text-sm font-bold uppercase tracking-widest hover:text-blue-300 transition-colors"
            href="#"
          >
            View our stack →
          </a>
        </div>
      </div>
    </section>
  );
}
