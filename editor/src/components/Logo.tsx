interface LogoProps {
  compact?: boolean;
}

export function LogoMark({ className }: { className?: string }) {
  return (
    <svg className={className} viewBox="0 0 20 20" fill="none" aria-hidden="true">
      <rect width="20" height="20" rx="4" fill="#6B5CE7" />
      <path
        d="M5.5 4.75h6.25l2.25 2.25v8.25a1 1 0 0 1-1 1H5.5a1 1 0 0 1-1-1V5.75a1 1 0 0 1 1-1Z"
        fill="#fff"
      />
      <path
        d="M11.75 4.75v2.25h2.25"
        stroke="#C4B5FD"
        strokeWidth="0.75"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        d="M6.75 8.25h4.75M6.75 10.25h3.25M6.75 12.25h4"
        stroke="#6B5CE7"
        strokeWidth="0.9"
        strokeLinecap="round"
      />
      <path
        d="M13.1 8.35 15.15 10.4 13.1 12.45"
        stroke="#22D3EE"
        strokeWidth="1.1"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

export function Logo({ compact = false }: LogoProps) {
  if (compact) {
    return <LogoMark className="av-web-studio-logo__mark av-web-studio-logo__mark--animated" />;
  }

  return (
    <div className="av-web-studio-logo" aria-label="AV Web Studio">
      <LogoMark className="av-web-studio-logo__mark av-web-studio-logo__mark--animated" />
      <span className="av-web-studio-logo__text">
        <span className="av-web-studio-logo__brand">AV Web Studio</span>
      </span>
    </div>
  );
}
