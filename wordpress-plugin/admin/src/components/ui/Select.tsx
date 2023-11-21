import * as React from "react";
import { twJoin, twMerge } from "tailwind-merge";

import ChevronDown from "../../assets/chevron-down.svg";

export interface UiSelectOwnProps {
  subLabel?: string;
  hasError?: boolean;
}

export const UiSelect = React.forwardRef<
  HTMLSelectElement,
  React.ComponentPropsWithoutRef<"select"> & UiSelectOwnProps
>(({ disabled, subLabel, hasError, className, style, ...rest }, ref) => (
  <>
    <select
      {...rest}
      disabled={disabled}
      style={{
        ...style,
        "--mwp-chevron-down-icon": `url(${ChevronDown})`,
      }}
      className={twMerge(
        "mwp-block mwp-h-14 mwp-w-full mwp-max-w-none mwp-rounded-sm mwp-border mwp-border-grayscale-100 mwp-bg-[image:var(--mwp-chevron-down-icon)] mwp-bg-6 mwp-bg-right-3 mwp-px-4 mwp-text-xs mwp-shadow-none",
        !disabled && "mwp-text-grayscale-900",
        disabled &&
          "mwp-cursor-not-allowed mwp-bg-transparent mwp-text-grayscale-600",
        hasError && "mwp-border-red-500",
        className,
      )}
      ref={ref}
    />
    {subLabel && (
      <p
        className={twJoin(
          "mwp-mt-2 mwp-text-2xs",
          hasError && "mwp-text-red-500",
        )}
      >
        {subLabel}
      </p>
    )}
  </>
));
