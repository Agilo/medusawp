import * as React from "react";
import { twMerge, twJoin } from "tailwind-merge";

export interface UiInputOwnProps {
  subLabel?: string;
  hasError?: boolean;
}

export const UiInput = React.forwardRef<
  HTMLInputElement,
  React.ComponentPropsWithoutRef<"input"> & UiInputOwnProps
>(({ disabled, subLabel, hasError, className, ...rest }, ref) => (
  <>
  <input
    {...rest}
    disabled={disabled}
    className={twMerge(
      "mwp-h-14 mwp-w-full mwp-rounded-sm mwp-border mwp-border-grayscale-100 mwp-px-4 mwp-text-xs mwp-shadow-none mwp-outline-none placeholder:mwp-text-grayscale-600",
      !disabled && "mwp-text-grayscale-900",
      disabled && "mwp-cursor-not-allowed mwp-text-grayscale-500",
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
