import React, { useState } from "react";
import { Mail, Github, Twitter, Linkedin, Code } from "lucide-react";

export default function Footer() {
  const [email, setEmail] = useState("");
  const currentYear = new Date().getFullYear();

  const handleSubscribe = (e: React.FormEvent) => {
    e.preventDefault();
    if (email) {
      alert(`Thank you for subscribing, ${email}!`);
      setEmail("");
    }
  };

  return (
    <footer className="w-full mt-auto bg-slate-100 border-t border-slate-200">
      <div className="grid grid-cols-1 md:grid-cols-12 gap-8 px-6 md:px-16 py-12 max-w-7xl mx-auto">
        
        {/* Brand Column */}
        <div className="md:col-span-4 space-y-6">
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center border border-blue-200">
              <Code className="w-5 h-5 text-blue-600" />
            </div>
            <span
              className="text-lg font-bold text-slate-900 tracking-tight"
              style={{ fontFamily: "Space Grotesk, sans-serif" }}
            >
              Digital Builder
            </span>
          </div>
          <p className="text-slate-600 text-sm leading-relaxed max-w-xs">
            Engineering the digital frontier. Fast, scalable, and beautifully crafted experiences for the modern web.
          </p>
        </div>

        {/* Platform Links */}
        <div className="md:col-span-2 space-y-4">
          <h4 className="text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">Platform</h4>
          <ul className="space-y-2 text-sm">
            <li>
              <a className="text-slate-600 hover:text-blue-600 transition-all font-medium" href="#">
                Github
              </a>
            </li>
            <li>
              <a className="text-slate-600 hover:text-blue-600 transition-all font-medium" href="#">
                Docs
              </a>
            </li>
            <li>
              <a className="text-slate-600 hover:text-blue-600 transition-all font-medium" href="#">
                Security
              </a>
            </li>
          </ul>
        </div>

        {/* Company Links */}
        <div className="md:col-span-2 space-y-4">
          <h4 className="text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">Company</h4>
          <ul className="space-y-2 text-sm">
            <li>
              <a className="text-slate-600 hover:text-blue-600 transition-all font-medium" href="#">
                Twitter
              </a>
            </li>
            <li>
              <a className="text-slate-600 hover:text-blue-600 transition-all font-medium" href="#">
                Linkedin
              </a>
            </li>
            <li>
              <a className="text-slate-600 hover:text-blue-600 transition-all font-medium" href="#">
                Privacy
              </a>
            </li>
          </ul>
        </div>

        {/* Newsletter Signup */}
        <div className="md:col-span-4 space-y-4">
          <h4 className="text-xs font-bold text-slate-800 uppercase tracking-widest font-mono">Stay Connected</h4>
          <form
            onSubmit={handleSubscribe}
            className="flex gap-2 p-1 rounded-xl bg-white border border-slate-200 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-transparent transition-all shadow-xs"
          >
            <input
              className="bg-transparent border-none focus:ring-0 text-sm flex-1 px-4 text-slate-800 placeholder-slate-400 focus:outline-none outline-none"
              placeholder="Email address"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
            <button
              className="bg-slate-900 text-white hover:bg-slate-800 px-4 py-2 rounded-lg font-bold text-xs uppercase tracking-wider transition-colors"
              type="submit"
            >
              Join
            </button>
          </form>
        </div>

        {/* Bottom Bar */}
        <div className="md:col-span-12 pt-12 mt-12 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500">
          <p>© {currentYear} Digital Builder. Engineered for velocity.</p>
          <div className="flex gap-6">
            <a className="hover:text-blue-600 transition-all font-semibold" href="#">
              Terms of Service
            </a>
            <a className="hover:text-blue-600 transition-all font-semibold" href="#">
              Cookies
            </a>
          </div>
        </div>

      </div>
    </footer>
  );
}
